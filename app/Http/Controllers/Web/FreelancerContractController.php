<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\FreelancerContractRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\FreelancerContractMessageRequest;
use App\Http\Requests\Web\FreelancerContractReviewRequest;
use App\Http\Requests\Web\FreelancerVerdictRejectRequest;
use App\Models\AdminNotification;
use App\Models\FreelancerContract;
use App\Models\FreelancerContractMilestone;
use App\Notifications\Freelancer\ContractCompletedNotification;
use App\Notifications\Freelancer\MilestoneApprovedNotification;
use App\Notifications\Freelancer\NewContractMessageNotification;
use App\Services\FreelancerContractMessageService;
use App\Services\FreelancerContractReviewService;
use App\Services\FreelancerContractService;
use App\Services\FreelancerVerdictService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FreelancerContractController extends Controller
{
    public function __construct(
        private readonly FreelancerContractRepositoryInterface $contractRepo,
        private readonly FreelancerContractService $contractService,
        private readonly FreelancerContractMessageService $messageService,
        private readonly FreelancerContractReviewService $reviewService,
        private readonly FreelancerVerdictService $verdictService,
    ) {
    }

    public function index(): View
    {
        $contracts = $this->contractRepo->getByCustomerId(auth('customer')->id(), ['freelancer.shop', 'service.images', 'milestones']);

        return view(VIEW_FILE_NAMES['freelancer_contracts_index'], compact('contracts'));
    }

    public function show(FreelancerContract $contract): View
    {
        $this->authorizeCustomer($contract);

        $contract->load(['milestones.deliverables.attachments', 'messages.attachments', 'reviews', 'freelancer.shop', 'service']);

        return view(VIEW_FILE_NAMES['freelancer_contract_show'], compact('contract'));
    }

    public function approveMilestone(FreelancerContract $contract, FreelancerContractMilestone $milestone): RedirectResponse
    {
        abort_unless((int)$milestone->freelancer_contract_id === (int)$contract->id, 404);
        $this->authorizeCustomer($contract);

        try {
            $contract = $this->contractService->approveMilestone($milestone->id, auth('customer')->id());
        } catch (RuntimeException $exception) {
            Toastr::error($exception->getMessage());
            return back();
        }

        $milestone->refresh();

        // Same reasoning as the hire flow: mail sends synchronously (no queue configured),
        // so defer it past the response instead of making the customer wait on SMTP.
        dispatch(function () use ($contract, $milestone) {
            try {
                $contract->freelancer->notify(new MilestoneApprovedNotification($milestone));

                if ($contract->status === 'completed') {
                    $contract->customer->notify(new ContractCompletedNotification($contract));
                    $contract->freelancer->notify(new ContractCompletedNotification($contract));
                }
            } catch (\Throwable $e) {
                Log::error('[FreelancerContractController] Milestone approval notification failed: ' . $e->getMessage());
            }
        })->afterResponse();

        Toastr::success(translate('delivery_approved_successfully'));
        return back();
    }

    public function approveMilestoneFallback(FreelancerContract $contract): RedirectResponse
    {
        return redirect()->route('hire.contracts.show', $contract->id);
    }

    public function submitVerdict(Request $request, FreelancerContract $contract): RedirectResponse
    {
        $this->authorizeCustomer($contract);

        $validated = $request->validate([
            'verdict' => 'required|string|in:approved,rejected,revision',
            'rejection_reason' => 'required_if:verdict,rejected,revision|nullable|string|min:5|max:2500',
        ]);

        try {
            if ($validated['verdict'] === 'approved') {
                $this->verdictService->approveDelivery($contract, auth('customer')->id());
                Toastr::success(translate('service_approved_successfully'));
            } else {
                $this->verdictService->rejectDelivery(
                    $contract,
                    auth('customer')->id(),
                    $validated['rejection_reason'] ?? ''
                );

                $message = $validated['verdict'] === 'revision'
                    ? translate('revision_request_sent_to_the_freelancer')
                    : translate('delivery_rejected_feedback_sent_to_the_freelancer');

                Toastr::success($message);
            }
        } catch (RuntimeException $exception) {
            Toastr::error($exception->getMessage());
            return back()->withInput();
        }

        return back();
    }

    public function approveDelivery(FreelancerContract $contract): RedirectResponse
    {
        $this->authorizeCustomer($contract);

        try {
            $this->verdictService->approveDelivery($contract, auth('customer')->id());
        } catch (RuntimeException $exception) {
            Toastr::error($exception->getMessage());
            return back();
        }

        Toastr::success(translate('service_approved_successfully'));
        return back();
    }

    public function rejectDelivery(FreelancerVerdictRejectRequest $request, FreelancerContract $contract): RedirectResponse
    {
        $this->authorizeCustomer($contract);

        try {
            $this->verdictService->rejectDelivery($contract, auth('customer')->id(), $request->input('rejection_reason'));
        } catch (RuntimeException $exception) {
            Toastr::error($exception->getMessage());
            return back();
        }

        Toastr::success(translate('feedback_sent_the_freelancer_will_revise_the_delivery'));
        return back();
    }

    public function requestCancellation(FreelancerContract $contract): RedirectResponse
    {
        $this->authorizeCustomer($contract);

        request()->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->contractService->requestCancellation($contract, auth('customer')->id(), request()->input('reason'));
        } catch (RuntimeException $exception) {
            Toastr::error($exception->getMessage());
            return back();
        }

        try {
            AdminNotification::create([
                'type' => 'freelancer_contract_cancellation_requested',
                'title' => 'Contract Cancellation Requested',
                'message' => 'A customer requested to cancel contract #' . $contract->id . '.',
                'link' => route('admin.freelancer.contracts.view', $contract->id),
                'reference_id' => $contract->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('[FreelancerContractController] Admin cancellation notification failed: ' . $e->getMessage());
        }

        Toastr::success(translate('cancellation_request_submitted_an_admin_will_review_it_shortly'));
        return back();
    }

    public function messages(FreelancerContract $contract): JsonResponse
    {
        $this->authorizeCustomer($contract);

        $afterId = (int)request('after_id', 0);
        $messages = $contract->messages()->with('attachments')->where('id', '>', $afterId)->orderBy('id')->get();

        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(FreelancerContractMessageRequest $request, FreelancerContract $contract): RedirectResponse
    {
        $this->authorizeCustomer($contract);

        $message = $this->messageService->send(
            contract: $contract,
            senderType: 'customer',
            senderId: auth('customer')->id(),
            body: $request->input('body'),
            files: $request->file('attachments', []),
        );

        dispatch(function () use ($contract, $message) {
            try {
                $contract->freelancer->notify(new NewContractMessageNotification($message, 'freelancer'));
            } catch (\Throwable $e) {
                Log::error('[FreelancerContractController] New message notification failed: ' . $e->getMessage());
            }
        })->afterResponse();

        return back();
    }

    public function storeReview(FreelancerContractReviewRequest $request, FreelancerContract $contract): RedirectResponse
    {
        $this->authorizeCustomer($contract);

        try {
            $this->reviewService->store(
                contract: $contract,
                reviewerType: 'customer',
                reviewerId: auth('customer')->id(),
                rating: (int)$request->input('rating'),
                body: $request->input('body'),
            );
        } catch (RuntimeException $exception) {
            Toastr::error($exception->getMessage());
            return back();
        }

        Toastr::success(translate('review_submitted_successfully'));
        return back();
    }

    private function authorizeCustomer(FreelancerContract $contract): void
    {
        abort_unless($contract->customer_id === auth('customer')->id(), 403);
    }
}
