<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Contracts\Repositories\FreelancerCategoryRepositoryInterface;
use App\Contracts\Repositories\FreelancerSpecializationRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\FreelancerSpecializationAddRequest;
use App\Http\Requests\Admin\FreelancerSpecializationUpdateRequest;
use App\Models\FreelancerSpecialization;
use App\Services\FreelancerOwnerNotificationService;
use App\Services\FreelancerSpecializationService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FreelancerSpecializationController extends BaseController
{
    public function __construct(
        private readonly FreelancerSpecializationRepositoryInterface $freelancerSpecializationRepo,
        private readonly FreelancerCategoryRepositoryInterface $freelancerCategoryRepo,
        private readonly FreelancerSpecializationService $freelancerSpecializationService,
        private readonly TranslationRepositoryInterface $translationRepo,
        private readonly FreelancerOwnerNotificationService $ownerNotificationService,
    ) {
    }

    public function index(Request|null $request, string $type = null): View
    {
        $specializations = $this->freelancerSpecializationRepo->getListWhere(
            orderBy: ['priority' => 'asc', 'id' => 'desc'],
            searchValue: $request?->get('searchValue'),
            filters: [],
            relations: ['translations', 'storage', 'seller', 'category.translations', 'category.seller'],
            dataLimit: getWebConfig(name: 'pagination_limit')
        );
        $categories = $this->freelancerCategoryRepo->getListWhere(
            orderBy: ['priority' => 'asc', 'id' => 'desc'],
            filters: ['parent_id' => null, 'position' => 0, 'is_active' => 1, 'seller_id' => null],
            dataLimit: 'all'
        );
        $languages = getWebConfig(name: 'pnc_language') ?? [getDefaultLanguage()];
        $defaultLanguage = $languages[0];

        return view('admin-views.freelancer.specializations.index', compact('specializations', 'categories', 'languages', 'defaultLanguage'));
    }

    public function create(): View
    {
        $categories = $this->freelancerCategoryRepo->getListWhere(
            orderBy: ['priority' => 'asc', 'id' => 'desc'],
            filters: ['parent_id' => null, 'position' => 0, 'is_active' => 1, 'seller_id' => null],
            dataLimit: 'all'
        );
        $languages = getWebConfig(name: 'pnc_language') ?? [getDefaultLanguage()];
        $defaultLanguage = $languages[0];

        return view('admin-views.freelancer.specializations.create', compact('categories', 'languages', 'defaultLanguage'));
    }

    public function store(FreelancerSpecializationAddRequest $request): RedirectResponse
    {
        $createdCount = 0;

        foreach ($request->input('name.0', []) as $rowIndex => $name) {
            $specialization = $this->freelancerSpecializationRepo->add(
                data: $this->freelancerSpecializationService->getAddData(request: $request, rowIndex: (int)$rowIndex)
            );

            $translationRequest = new Request([
                'lang' => $request->input('lang', []),
                'name' => $this->freelancerSpecializationService->getTranslationNamesForRow(request: $request, rowIndex: (int)$rowIndex),
                'description' => $this->freelancerSpecializationService->getTranslationDescriptionsForRow(request: $request, rowIndex: (int)$rowIndex),
            ]);

            $this->translationRepo->add(request: $translationRequest, model: FreelancerSpecialization::class, id: $specialization->id);
            $createdCount++;
        }

        ToastMagic::success(
            $createdCount > 1
                ? translate('freelancer_specializations_added_successfully')
                : translate('freelancer_specialization_added_successfully')
        );
        return redirect()->route('admin.freelancer.specializations.index');
    }

    public function edit(string|int $id): View|RedirectResponse
    {
        $specialization = $this->freelancerSpecializationRepo->getFirstWhere(params: ['id' => $id], relations: ['translations', 'storage', 'seller', 'category']);

        if (!$specialization) {
            ToastMagic::error(translate('freelancer_specialization_not_found'));
            return redirect()->route('admin.freelancer.specializations.index');
        }

        $categories = $this->freelancerCategoryRepo->getListWhere(
            orderBy: ['priority' => 'asc', 'id' => 'desc'],
            filters: ['parent_id' => null, 'position' => 0, 'is_active' => 1, 'visible_to_seller_id' => $specialization->seller_id],
            dataLimit: 'all'
        );
        $languages = getWebConfig(name: 'pnc_language') ?? [getDefaultLanguage()];
        $defaultLanguage = $languages[0];

        return view('admin-views.freelancer.specializations.edit', compact('specialization', 'categories', 'languages', 'defaultLanguage'));
    }

    public function update(FreelancerSpecializationUpdateRequest $request, string|int $id): RedirectResponse
    {
        $specialization = $this->freelancerSpecializationRepo->getFirstWhere(params: ['id' => $id], relations: ['storage', 'seller']);

        if (!$specialization) {
            ToastMagic::error(translate('freelancer_specialization_not_found'));
            return redirect()->route('admin.freelancer.specializations.index');
        }

        $this->freelancerSpecializationRepo->update(
            id: $id,
            data: $this->freelancerSpecializationService->getUpdateData(request: $request, data: $specialization)
        );
        $this->translationRepo->update(request: $request, model: FreelancerSpecialization::class, id: $id);
        $this->notifyOwner($specialization, 'updated');

        ToastMagic::success(translate('freelancer_specialization_updated_successfully'));
        return redirect()->route('admin.freelancer.specializations.index');
    }

    public function status(Request $request): JsonResponse
    {
        $specialization = $this->freelancerSpecializationRepo->getFirstWhere(params: ['id' => $request['id']], relations: ['seller']);
        $status = (int)$request->get('is_active', 0);

        $this->freelancerSpecializationRepo->updateStatus(id: $request['id'], status: $status);

        if ($specialization) {
            $this->notifyOwner($specialization, $status ? 'enabled' : 'disabled');
        }

        return response()->json([
            'success' => 1,
            'message' => translate('freelancer_specialization_status_updated_successfully'),
        ]);
    }

    public function delete(Request $request): RedirectResponse
    {
        $specialization = $this->freelancerSpecializationRepo->getFirstWhere(params: ['id' => $request['id']], relations: ['seller']);

        if (!$specialization) {
            ToastMagic::error(translate('freelancer_specialization_cannot_be_deleted'));
            return redirect()->back();
        }

        $this->freelancerSpecializationService->deleteImage(data: $specialization);
        $this->translationRepo->delete(model: FreelancerSpecialization::class, id: $request['id']);
        $this->freelancerSpecializationRepo->delete(params: ['id' => $request['id']]);
        $this->notifyOwner($specialization, 'deleted');

        ToastMagic::success(translate('freelancer_specialization_deleted_successfully'));
        return redirect()->back();
    }

    private function notifyOwner(object $specialization, string $action): void
    {
        if (empty($specialization->seller_id)) {
            return;
        }

        $name = $specialization->defaultname ?? $specialization->name ?? 'Specialization';
        $subject = 'Freelancer specialization ' . $action;
        $message = 'Your freelancer specialization "' . $name . '" was ' . $action . ' by admin.';

        $this->ownerNotificationService->notify($specialization->seller, $subject, $message);
    }
}
