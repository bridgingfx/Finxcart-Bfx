<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\BaseController;
use App\Models\FreelancerCategory;
use App\Models\FreelancerPortfolioItem;
use App\Models\FreelancerService;
use App\Models\FreelancerSpecialization;
use App\Models\Seller;
use App\Services\FreelancerServiceService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FreelancerServiceManageController extends BaseController
{
    public function __construct(private readonly FreelancerServiceService $freelancerServiceService)
    {
    }

    public function index(?Request $request, string $type = null): View
    {
        $searchValue = $request['searchValue'];

        $services = FreelancerService::with(['seller.shop', 'category', 'specialization'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request['status'] === 'active');
            })
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->where(function ($query) use ($searchValue) {
                    $query->where('title', 'like', "%{$searchValue}%")
                        ->orWhere('description', 'like', "%{$searchValue}%")
                        ->orWhereHas('seller', function ($sellerQuery) use ($searchValue) {
                            $sellerQuery->where('f_name', 'like', "%{$searchValue}%")
                                ->orWhere('l_name', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%");
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate(getWebConfig(name: 'pagination_limit'))
            ->appends($request->query());

        return view('admin-views.freelancer.services.index', compact('services'));
    }

    public function create(): View
    {
        $freelancers = Seller::where('seller_type', 'freelancer')
            ->where('status', 'approved')
            ->orderBy('f_name')
            ->get();

        return view('admin-views.freelancer.services.create', compact('freelancers'));
    }

    public function sellerOptions(int $sellerId): JsonResponse
    {
        return response()->json([
            'categories' => $this->visibleCategories($sellerId)->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->defaultname,
            ])->values(),
            'specializations' => $this->visibleSpecializations($sellerId)->map(fn ($specialization) => [
                'id' => $specialization->id,
                'name' => $specialization->defaultname,
                'category_id' => $specialization->freelancer_category_id,
            ])->values(),
        ]);

    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'seller_id' => [
                'required',
                Rule::exists('sellers', 'id')->where(fn ($query) => $query->where('seller_type', 'freelancer')),
            ],
            'freelancer_category_id' => [
                'required',
                Rule::exists('freelancer_categories', 'id')->where(function ($query) use ($request) {
                    $query->whereNull('seller_id')->orWhere('seller_id', $request['seller_id']);
                }),
            ],
            'freelancer_specialization_id' => [
                'required',
                Rule::exists('freelancer_specializations', 'id')->where(function ($query) use ($request) {
                    $query->where('freelancer_category_id', $request['freelancer_category_id'])
                        ->where(function ($query) use ($request) {
                            $query->whereNull('seller_id')->orWhere('seller_id', $request['seller_id']);
                        });
                }),
            ],
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'images' => 'required|array|min:1',
            'images.*.image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'packages' => 'nullable|array',
            'packages.*.is_enabled' => 'nullable|boolean',
            'packages.*.title' => 'nullable|string|max:100',
            'packages.*.description' => 'nullable|string|max:1000',
            'packages.*.price' => 'nullable|numeric|min:0|regex:/^\d+(\.\d+)?$/',
            'packages.*.delivery_time_days' => 'nullable|integer|min:1|max:365|regex:/^\d+$/',
            'packages.*.revisions' => 'nullable|integer|min:0|max:100|regex:/^\d+$/',
            'packages.*.features' => 'nullable|array',
            'packages.*.features.*.label' => 'nullable|string|max:100',
            'packages.*.features.*.included' => 'nullable|boolean',
        ]);

        $sellerId = (int) $request['seller_id'];

        if (!$this->sellerHasActivePortfolio($sellerId)) {
            throw ValidationException::withMessages([
                'seller_id' => translate('please_create_and_activate_at_least_one_portfolio_item_before_creating_a_service'),
            ]);
        }

        $service = FreelancerService::create($this->freelancerServiceService->getCreateData($request, $sellerId));
        $this->freelancerServiceService->syncImages($service, $request);
        $this->freelancerServiceService->syncPackages($service, $request);

        ToastMagic::success(translate('service_added_successfully'));
        return redirect()->route('admin.freelancer.services.index');
    }

    public function show(FreelancerService $service): View
    {
        $service->load(['seller.shop', 'category', 'specialization', 'packages', 'images']);

        return view('admin-views.freelancer.services.show', [
            'service' => $service,
        ]);

    }

    public function edit(FreelancerService $service): View
    {
        $service->load(['seller', 'category', 'specialization', 'packages', 'images']);

        return view('admin-views.freelancer.services.edit', [
            'service' => $service,
            'categories' => $this->visibleCategories($service->seller_id),
            'specializations' => $this->visibleSpecializations($service->seller_id),
        ]);

    }

    public function update(Request $request, FreelancerService $service): RedirectResponse
    {
        $service->loadMissing('seller');
        $this->validateService($request, $service);

        $service->update($this->getUpdateData($request));
        $this->freelancerServiceService->syncImages($service, $request);
        $this->freelancerServiceService->syncPackages($service, $request);

        ToastMagic::success(translate('service_updated_successfully'));
        return redirect()->route('admin.freelancer.services.index');
    }

    private function validateService(Request $request, FreelancerService $service): void
    {
        $request->validate([
            'freelancer_category_id' => [
                'required',
                Rule::exists('freelancer_categories', 'id')->where(function ($query) use ($service) {
                    $query->whereNull('seller_id')->orWhere('seller_id', $service->seller_id);
                }),
            ],
            'freelancer_specialization_id' => [
                'required',
                Rule::exists('freelancer_specializations', 'id')->where(function ($query) use ($request, $service) {
                    $query->where('freelancer_category_id', $request['freelancer_category_id'])
                        ->where(function ($query) use ($service) {
                            $query->whereNull('seller_id')->orWhere('seller_id', $service->seller_id);
                        });
                }),
            ],
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*.id' => 'nullable|integer|regex:/^\d+$/',
            'images.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'packages' => 'nullable|array',
            'packages.*.is_enabled' => 'nullable|boolean',
            'packages.*.title' => 'nullable|string|max:100',
            'packages.*.description' => 'nullable|string|max:1000',
            'packages.*.price' => 'nullable|numeric|min:0|regex:/^\d+(\.\d+)?$/',
            'packages.*.delivery_time_days' => 'nullable|integer|min:1|max:365|regex:/^\d+$/',
            'packages.*.revisions' => 'nullable|integer|min:0|max:100|regex:/^\d+$/',
            'packages.*.features' => 'nullable|array',
            'packages.*.features.*.label' => 'nullable|string|max:100',
            'packages.*.features.*.included' => 'nullable|boolean',
        ]);

        if (!$this->requestHasSubmittedImage($request)) {
            throw ValidationException::withMessages([
                'images' => translate('at_least_one_service_image_is_required'),
            ]);
        }
    }

    private function sellerHasActivePortfolio(int $sellerId): bool
    {
        return FreelancerPortfolioItem::where('seller_id', $sellerId)
            ->where('is_active', true)
            ->exists();
    }

    private function requestHasSubmittedImage(Request $request): bool
    {
        foreach ($request->input('images', []) as $index => $row) {
            if (!empty($row['id']) || $request->hasFile("images.$index.image")) {
                return true;
            }
        }

        return false;
    }

    private function getUpdateData(Request $request): array
    {
        return [
            'freelancer_category_id' => $request['freelancer_category_id'],
            'freelancer_specialization_id' => $request['freelancer_specialization_id'],
            'title' => $request['title'],
            'description' => $request['description'] ?? null,
            'is_active' => $request->has('is_active') ? (bool)$request['is_active'] : true,
        ];
    }

    private function visibleCategories(int $sellerId)
    {
        return FreelancerCategory::withoutGlobalScope('translate')
            ->whereNull('parent_id')
            ->where('position', 0)
            ->where(function ($query) use ($sellerId) {
                $query->whereNull('seller_id')->orWhere('seller_id', $sellerId);
            })
            ->orderBy('priority')
            ->orderByDesc('id')
            ->get();
    }

    private function visibleSpecializations(int $sellerId)
    {
        return FreelancerSpecialization::withoutGlobalScope('translate')
            ->where(function ($query) use ($sellerId) {
                $query->whereNull('seller_id')->orWhere('seller_id', $sellerId);
            })
            ->orderBy('priority')
            ->orderByDesc('id')
            ->get();
    }
}
