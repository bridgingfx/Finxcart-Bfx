<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Contracts\Repositories\FreelancerContractRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Models\FreelancerContract;
use App\Notifications\Freelancer\ContractCancellationApprovedNotification;
use App\Notifications\Freelancer\ContractCancellationDeniedNotification;
use App\Services\FreelancerContractService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FreelancerContractController extends BaseController
{
    public function __construct(
        private readonly FreelancerContractRepositoryInterface $contractRepo,
        private readonly FreelancerContractService $contractService,
    ) {
    }

    public function index(?Request $request, string $type = null): View
    {
        $contracts = $this->contractRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: [
                'status' => $request['status'] ?? '',
                'cancellation_status' => $request['cancellation_status'] ?? '',
            ],
            relations: ['customer', 'freelancer.shop', 'service'],
            dataLimit: getWebConfig(name: 'pagination_limit')
        );

        return view('admin-views.freelancer.contracts.index', compact('contracts'));
    }

    public function show(FreelancerContract $contract): View
    {
        $contract->load(['milestones.deliverables.attachments', 'messages.attachments', 'reviews', 'customer', 'freelancer.shop', 'service']);

        return view('admin-views.freelancer.contracts.view', compact('contract'));
    }

    public function approveCancellation(Request $request, FreelancerContract $contract): RedirectResponse
    {
        try {
            $this->contractService->approveCancellation($contract, $request->input('admin_note'));
        } catch (RuntimeException $exception) {
            ToastMagic::error($exception->getMessage());
            return back();
        }

        try {
            $contract->customer->notify(new ContractCancellationApprovedNotification($contract));
        } catch (\Throwable $e) {
            Log::error('[Admin\\FreelancerContractController] Cancellation-approved notification failed: ' . $e->getMessage());
        }

        ToastMagic::success(translate('contract_cancelled_the_customer_can_now_re_hire_this_service'));
        return back();
    }

    public function denyCancellation(Request $request, FreelancerContract $contract): RedirectResponse
    {
        try {
            $this->contractService->denyCancellation($contract, $request->input('admin_note'));
        } catch (RuntimeException $exception) {
            ToastMagic::error($exception->getMessage());
            return back();
        }

        try {
            $contract->customer->notify(new ContractCancellationDeniedNotification($contract));
        } catch (\Throwable $e) {
            Log::error('[Admin\\FreelancerContractController] Cancellation-denied notification failed: ' . $e->getMessage());
        }

        ToastMagic::success(translate('cancellation_request_denied'));
        return back();
    }
}
