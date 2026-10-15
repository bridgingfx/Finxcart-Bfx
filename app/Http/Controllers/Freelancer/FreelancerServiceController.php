<?php

namespace App\Http\Controllers\Freelancer;

use App\Contracts\Repositories\FreelancerCategoryRepositoryInterface;
use App\Contracts\Repositories\FreelancerServiceRepositoryInterface;
use App\Contracts\Repositories\FreelancerSpecializationRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Freelancer\FreelancerServiceCreateRequest;
use App\Http\Requests\Freelancer\FreelancerServiceUpdateRequest;
use App\Models\AdminNotification;
use App\Models\FreelancerCategory;
use App\Models\FreelancerPortfolioItem;
use App\Models\FreelancerService;
use App\Models\FreelancerSpecialization;
use App\Services\FreelancerServiceService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FreelancerServiceController extends Controller
{
    public function __construct(
        private readonly FreelancerServiceRepositoryInterface $serviceRepo,
        private readonly FreelancerCategoryRepositoryInterface $categoryRepo,
        private readonly FreelancerSpecializationRepositoryInterface $specializationRepo,
        private readonly FreelancerServiceService $freelancerServiceService,
    ) {
    }

    public function index(): View
    {
        $services = $this->serviceRepo->getBySellerId(auth('freelancer')->id(), ['category', 'specialization', 'images']);

        return view('freelancer-views.services.index', compact('services'));
    }

    public function create(): View|RedirectResponse
    {
        if (!$this->isApproved()) {
            session()->flash('freelancer_kyc_popup_message', translate('your_documents_must_be_approved_by_admin_before_adding_services'));
            return redirect()->route('freelancer.services.index');
        }

        if (!$this->hasActivePortfolio()) {
            ToastMagic::warning(translate('please_create_and_activate_at_least_one_portfolio_item_before_creating_a_service'));
            return redirect()->route('freelancer.portfolio.create');
        }

        return view('freelancer-views.services.create', [
            'categories' => $this->visibleCategories(),
            'specializations' => $this->visibleSpecializations(),
        ]);
    }

    public function store(FreelancerServiceCreateRequest $request): RedirectResponse
    {
        if (!$this->isApproved()) {
            abort(403);
        }

        if (!$this->hasActivePortfolio()) {
            ToastMagic::warning(translate('please_create_and_activate_at_least_one_portfolio_item_before_creating_a_service'));
            return redirect()->route('freelancer.portfolio.create');
        }

        $sellerId = auth('freelancer')->id();

        $service = $this->serviceRepo->add($this->freelancerServiceService->getCreateData($request, $sellerId));
        $this->freelancerServiceService->syncImages($service, $request);
        $this->freelancerServiceService->syncPackages($service, $request);

        $this->notifyAdminOfServiceCreation($service);

        ToastMagic::success(translate('service_added_successfully'));
        return redirect()->route('freelancer.services.index');
    }

    private function notifyAdminOfServiceCreation(FreelancerService $service): void
    {
        try {
            $seller = auth('freelancer')->user();
            $name = trim(($seller?->f_name ?? '') . ' ' . ($seller?->l_name ?? '')) ?: 'A freelancer';
            AdminNotification::create([
                'type' => 'freelancer_service_created',
                'title' => 'New Freelancer Service Added',
                'message' => "{$name} added a new service: \"{$service->title}\".",
                'link' => route('admin.freelancer.accounts.index'),
                'reference_id' => $service->id,
            ]);
        } catch (\Throwable $e) {
            \Log::error('[FreelancerServiceController] Admin notification failed: ' . $e->getMessage());
        }
    }

    public function show(int $id): View
    {
        $service = $this->ownedService($id, ['category', 'specialization', 'images', 'packages']);
        if (!$service) {
            abort(404);
        }

        return view('freelancer-views.services.view', compact('service'));
    }

    public function edit(int $id): View
    {
        $service = $this->ownedService($id, ['category', 'specialization', 'packages', 'images']);
        if (!$service) {
            abort(404);
        }

        return view('freelancer-views.services.edit', [
            'service' => $service,
            'categories' => $this->visibleCategories(),
            'specializations' => $this->visibleSpecializations(),
        ]);
    }

    public function update(int $id, FreelancerServiceUpdateRequest $request): RedirectResponse
    {
        $service = $this->ownedService($id);
        if (!$service) {
            abort(404);
        }

        $this->serviceRepo->update((string)$id, $this->freelancerServiceService->getUpdateData($request));
        $this->freelancerServiceService->syncImages($service, $request);
        $this->freelancerServiceService->syncPackages($service, $request);

        ToastMagic::success(translate('service_updated_successfully'));
        return redirect()->route('freelancer.services.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $service = $this->ownedService($id, ['images']);
        if (!$service) {
            abort(404);
        }

        $this->freelancerServiceService->deleteImages($service);
        $this->serviceRepo->delete(['id' => $id]);

        ToastMagic::success(translate('service_deleted_successfully'));
        return redirect()->route('freelancer.services.index');
    }

    public function statusUpdate(int $id): RedirectResponse
    {
        $service = $this->ownedService($id);
        if (!$service) {
            abort(404);
        }

        $this->serviceRepo->updateActiveStatus($id, $service->is_active ? 0 : 1);

        ToastMagic::success(translate('status_updated_successfully'));
        return redirect()->back();
    }

    public function storeCategory(Request $request): JsonResponse
    {
        if (!$this->isApproved()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $category = $this->categoryRepo->add([
            'seller_id' => auth('freelancer')->id(),
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug(FreelancerCategory::class, $validated['name']),
            'image' => 'def.png',
            'image_storage_type' => 'public',
            'parent_id' => null,
            'position' => 0,
            'priority' => 0,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'category' => ['id' => $category->id, 'name' => $category->name],
        ]);
    }

    public function storeSpecialization(Request $request): JsonResponse
    {
        if (!$this->isApproved()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'freelancer_category_id' => 'required|exists:freelancer_categories,id',
        ]);

        $sellerId = auth('freelancer')->id();
        $categoryVisible = FreelancerCategory::withoutGlobalScope('translate')
            ->where('id', $validated['freelancer_category_id'])
            ->where(function ($query) use ($sellerId) {
                $query->whereNull('seller_id')->orWhere('seller_id', $sellerId);
            })
            ->exists();

        if (!$categoryVisible) {
            return response()->json([
                'success' => false,
                'message' => translate('selected_freelancer_category_is_invalid'),
            ], 422);
        }

        $specialization = $this->specializationRepo->add([
            'seller_id' => $sellerId,
            'freelancer_category_id' => $validated['freelancer_category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'slug' => $this->uniqueSlug(\App\Models\FreelancerSpecialization::class, $validated['name']),
            'image' => 'def.png',
            'image_storage_type' => 'public',
            'priority' => 0,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'specialization' => [
                'id' => $specialization->id,
                'name' => $specialization->name,
                'description' => $specialization->description,
                'freelancer_category_id' => $specialization->freelancer_category_id,
            ],
        ]);
    }

    public function destroyCategory(int $id): JsonResponse
    {
        if (!$this->isApproved()) {
            abort(403);
        }

        if (!$this->hasActivePortfolio()) {
            ToastMagic::warning(translate('please_create_and_activate_at_least_one_portfolio_item_before_creating_a_service'));
            return redirect()->route('freelancer.portfolio.create');
        }

        $sellerId = auth('freelancer')->id();
        $category = FreelancerCategory::withoutGlobalScope('translate')
            ->where('id', $id)
            ->where('seller_id', $sellerId)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => translate('freelancer_category_not_found'),
            ], 404);
        }

        $hasServices = FreelancerService::where('seller_id', $sellerId)
            ->where('freelancer_category_id', $id)
            ->exists();
        $hasSpecializations = FreelancerSpecialization::withoutGlobalScope('translate')
            ->where('freelancer_category_id', $id)
            ->exists();

        if ($hasServices || $hasSpecializations) {
            return response()->json([
                'success' => false,
                'message' => translate('freelancer_category_cannot_be_deleted'),
            ], 422);
        }

        $category->delete();

        return response()->json(['success' => true]);
    }

    public function destroySpecialization(int $id): JsonResponse
    {
        if (!$this->isApproved()) {
            abort(403);
        }

        if (!$this->hasActivePortfolio()) {
            ToastMagic::warning(translate('please_create_and_activate_at_least_one_portfolio_item_before_creating_a_service'));
            return redirect()->route('freelancer.portfolio.create');
        }

        $sellerId = auth('freelancer')->id();
        $specialization = FreelancerSpecialization::withoutGlobalScope('translate')
            ->where('id', $id)
            ->where('seller_id', $sellerId)
            ->first();

        if (!$specialization) {
            return response()->json([
                'success' => false,
                'message' => translate('freelancer_specialization_not_found'),
            ], 404);
        }

        $hasServices = FreelancerService::where('seller_id', $sellerId)
            ->where('freelancer_specialization_id', $id)
            ->exists();

        if ($hasServices) {
            return response()->json([
                'success' => false,
                'message' => translate('freelancer_specialization_cannot_be_deleted'),
            ], 422);
        }

        $specialization->delete();

        return response()->json(['success' => true]);
    }

    private function visibleCategories()
    {
        return $this->categoryRepo->getListWhere(
            orderBy: ['priority' => 'asc', 'id' => 'desc'],
            filters: ['parent_id' => null, 'position' => 0, 'is_active' => 1, 'visible_to_seller_id' => auth('freelancer')->id()],
            dataLimit: 'all'
        );
    }

    private function visibleSpecializations()
    {
        return $this->specializationRepo->getListWhere(
            orderBy: ['priority' => 'asc', 'id' => 'desc'],
            filters: ['is_active' => 1, 'visible_to_seller_id' => auth('freelancer')->id()],
            dataLimit: 'all'
        );
    }

    private function uniqueSlug(string $modelClass, string $name): string
    {
        $slug = Str::slug($name) ?: Str::random(8);
        $baseSlug = $slug;
        $index = 1;

        while ($modelClass::withoutGlobalScope('translate')->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $index++;
        }

        return $slug;
    }

    private function ownedService(int $id, array $relations = []): ?FreelancerService
    {
        $service = $this->serviceRepo->getFirstWhere(['id' => $id], $relations);
        if (!$service || $service->seller_id !== auth('freelancer')->id()) {
            return null;
        }

        return $service;
    }

    private function hasActivePortfolio(): bool
    {
        return FreelancerPortfolioItem::where('seller_id', auth('freelancer')->id())
            ->where('is_active', true)
            ->exists();
    }

    private function isApproved(): bool
    {
        return auth('freelancer')->user()?->status === 'approved';
    }
}
