<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Contracts\Repositories\FreelancerCategoryRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\FreelancerCategoryAddRequest;
use App\Http\Requests\Admin\FreelancerCategoryUpdateRequest;
use App\Models\FreelancerCategory;
use App\Models\FreelancerSpecialization;
use App\Services\FreelancerCategoryService;
use App\Services\FreelancerOwnerNotificationService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FreelancerCategoryController extends BaseController
{
    public function __construct(
        private readonly FreelancerCategoryRepositoryInterface $freelancerCategoryRepo,
        private readonly FreelancerCategoryService $freelancerCategoryService,
        private readonly TranslationRepositoryInterface $translationRepo,
        private readonly FreelancerOwnerNotificationService $ownerNotificationService,
    ) {
    }

    public function index(Request|null $request, string $type = null): View
    {
        $categories = $this->freelancerCategoryRepo->getListWhere(
            orderBy: ['priority' => 'asc', 'id' => 'desc'],
            searchValue: $request?->get('searchValue'),
            filters: ['parent_id' => null, 'position' => 0],
            relations: ['translations', 'storage', 'seller'],
            dataLimit: getWebConfig(name: 'pagination_limit')
        );
        $languages = getWebConfig(name: 'pnc_language') ?? [getDefaultLanguage()];
        $defaultLanguage = $languages[0];

        return view('admin-views.freelancer.categories.index', compact('categories', 'languages', 'defaultLanguage'));
    }

    public function store(FreelancerCategoryAddRequest $request): RedirectResponse
    {
        $category = $this->freelancerCategoryRepo->add(
            data: $this->freelancerCategoryService->getAddData(request: $request)
        );
        $this->translationRepo->add(request: $request, model: FreelancerCategory::class, id: $category->id);

        ToastMagic::success(translate('freelancer_category_added_successfully'));
        return redirect()->route('admin.freelancer.categories.index');
    }

    public function edit(string|int $id): View|RedirectResponse
    {
        $category = $this->freelancerCategoryRepo->getFirstWhere(params: ['id' => $id], relations: ['translations', 'storage', 'seller']);

        if (!$category) {
            ToastMagic::error(translate('freelancer_category_not_found'));
            return redirect()->route('admin.freelancer.categories.index');
        }

        $languages = getWebConfig(name: 'pnc_language') ?? [getDefaultLanguage()];
        $defaultLanguage = $languages[0];

        return view('admin-views.freelancer.categories.edit', compact('category', 'languages', 'defaultLanguage'));
    }

    public function update(FreelancerCategoryUpdateRequest $request, string|int $id): RedirectResponse
    {
        $category = $this->freelancerCategoryRepo->getFirstWhere(params: ['id' => $id], relations: ['storage', 'seller']);

        if (!$category) {
            ToastMagic::error(translate('freelancer_category_not_found'));
            return redirect()->route('admin.freelancer.categories.index');
        }

        $this->freelancerCategoryRepo->update(
            id: $id,
            data: $this->freelancerCategoryService->getUpdateData(request: $request, data: $category)
        );
        $this->translationRepo->update(request: $request, model: FreelancerCategory::class, id: $id);
        $this->notifyOwner($category, 'updated');

        ToastMagic::success(translate('freelancer_category_updated_successfully'));
        return redirect()->route('admin.freelancer.categories.index');
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $category = $this->freelancerCategoryRepo->getFirstWhere(params: ['id' => $request['id']], relations: ['seller']);
        $status = (int)$request->get('is_active', 0);

        $this->freelancerCategoryRepo->updateStatus(id: $request['id'], status: $status);

        if ($category) {
            $this->notifyOwner($category, $status ? 'enabled' : 'disabled');
        }

        return response()->json([
            'success' => 1,
            'message' => translate('freelancer_category_status_updated_successfully'),
        ]);
    }

    public function delete(Request $request): RedirectResponse
    {
        $category = $this->freelancerCategoryRepo->getFirstWhere(params: ['id' => $request['id']], relations: ['seller']);

        if (!$category || $this->freelancerCategoryRepo->hasChildren(id: $request['id']) || FreelancerSpecialization::withoutGlobalScope('translate')->where('freelancer_category_id', $request['id'])->exists()) {
            ToastMagic::error(translate('freelancer_category_cannot_be_deleted'));
            return redirect()->back();
        }

        $this->freelancerCategoryService->deleteImage(data: $category);
        $this->translationRepo->delete(model: FreelancerCategory::class, id: $request['id']);
        $this->freelancerCategoryRepo->delete(params: ['id' => $request['id']]);
        $this->notifyOwner($category, 'deleted');

        ToastMagic::success(translate('freelancer_category_deleted_successfully'));
        return redirect()->back();
    }

    private function notifyOwner(object $category, string $action): void
    {
        if (empty($category->seller_id)) {
            return;
        }

        $name = $category->defaultname ?? $category->name ?? 'Category';
        $subject = 'Freelancer category ' . $action;
        $message = 'Your freelancer category "' . $name . '" was ' . $action . ' by admin.';

        $this->ownerNotificationService->notify($category->seller, $subject, $message);
    }
}
