<?php

namespace App\Http\Controllers\Freelancer;

use App\Contracts\Repositories\FreelancerContractRepositoryInterface;
use App\Enums\Freelancer\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Freelancer\FreelancerDeliveryStatusUpdateRequest;
use App\Http\Requests\Freelancer\FreelancerMilestoneSubmitRequest;
use App\Http\Requests\Web\FreelancerContractMessageRequest;
use App\Http\Requests\Web\FreelancerContractReviewRequest;
use App\Models\FreelancerContract;
use App\Models\FreelancerContractMilestone;
use App\Notifications\Freelancer\MilestoneSubmittedNotification;
use App\Notifications\Freelancer\NewContractMessageNotification;
use App\Services\FreelancerContractMessageService;
use App\Services\FreelancerContractReviewService;
use App\Services\FreelancerContractService;
use App\Services\FreelancerDeliveryService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FreelancerContractController extends Controller
{
    public function __construct(
        private readonly FreelancerContractRepositoryInterface $contractRepo,
        private readonly FreelancerContractService $contractService,
        private readonly FreelancerContractMessageService $messageService,
        private readonly FreelancerContractReviewService $reviewService,
        private readonly FreelancerDeliveryService $deliveryService,
    ) {
    }

    public function index(): View
    {
        $contracts = $this->contractRepo->getBySellerId(auth('freelancer')->id(), ['customer', 'service']);

        return view('freelancer-views.contracts.index', compact('contracts'));
    }

    public function show(FreelancerContract $contract): View
    {
        $this->authorizeFreelancer($contract);

        $contract->load(['milestones.deliverables.attachments', 'messages.attachments', 'reviews', 'customer', 'service', 'quoteRequests']);

        return view('freelancer-views.contracts.show', compact('contract'));
    }

    public function updateDeliveryStatus(FreelancerDeliveryStatusUpdateRequest $request, FreelancerContract $contract): RedirectResponse|JsonResponse
    {
        $wantsJson = $request->ajax() || $request->wantsJson();

        try {
            $contract = $this->deliveryService->updateDeliveryStatus(
                contract: $contract,
                sellerId: auth('freelancer')->id(),
                newStatus: DeliveryStatus::from($request->input('delivery_status')),
            );
        } catch (RuntimeException $exception) {
            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
            }
            ToastMagic::error($exception->getMessage());
            return back();
        }

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'message' => translate('delivery_status_updated_successfully'),
                'html' => view('freelancer-views.contracts._delivery-status-panel', compact('contract'))->render(),
            ]);
        }

        ToastMagic::success(translate('delivery_status_updated_successfully'));
        return back();
    }

    public function submitMilestone(FreelancerMilestoneSubmitRequest $request, FreelancerContractMilestone $milestone): RedirectResponse|JsonResponse
    {
        $this->authorizeFreelancer($milestone->contract);
        $wantsJson = $request->ajax() || $request->wantsJson();

        try {
            $this->contractService->submitMilestone(
                milestoneId: $milestone->id,
                sellerId: (string)auth('freelancer')->id(),
                note: $request->input('note'),
                files: $request->file('attachments', []),
            );
        } catch (RuntimeException $exception) {
            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
            }
            ToastMagic::error($exception->getMessage());
            return back();
        }

        $milestone->refresh()->load('deliverables.attachments');

        // Mail sends synchronously (no queue configured) — defer it past the response
        // so the freelancer isn't stuck waiting on an SMTP round-trip after submitting.
        dispatch(function () use ($milestone) {
            try {
                $milestone->contract->customer->notify(new MilestoneSubmittedNotification($milestone));
            } catch (\Throwable $e) {
                Log::error('[Freelancer\\FreelancerContractController] Milestone submitted notification failed: ' . $e->getMessage());
            }
        })->afterResponse();

        if ($wantsJson) {
            $delivery = $milestone;
            return response()->json([
                'success' => true,
                'message' => translate('deliverable_submitted_successfully'),
                'html' => view('freelancer-views.contracts._delivery-submission-panel', compact('delivery'))->render(),
            ]);
        }

        ToastMagic::success(translate('deliverable_submitted_successfully'));
        return back();
    }

    public function messages(FreelancerContract $contract): JsonResponse
    {
        $this->authorizeFreelancer($contract);

        $afterId = (int)request('after_id', 0);
        $messages = $contract->messages()->with('attachments')->where('id', '>', $afterId)->orderBy('id')->get();

        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(FreelancerContractMessageRequest $request, FreelancerContract $contract): RedirectResponse|JsonResponse
    {
        $this->authorizeFreelancer($contract);

        $message = $this->messageService->send(
            contract: $contract,
            senderType: 'seller',
            senderId: auth('freelancer')->id(),
            body: $request->input('body'),
            files: $request->file('attachments', []),
        );

        dispatch(function () use ($contract, $message) {
            try {
                $contract->customer->notify(new NewContractMessageNotification($message, 'customer'));
            } catch (\Throwable $e) {
                Log::error('[Freelancer\\FreelancerContractController] New message notification failed: ' . $e->getMessage());
            }
        })->afterResponse();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $message]);
        }

        return back();
    }

    public function storeReview(FreelancerContractReviewRequest $request, FreelancerContract $contract): RedirectResponse
    {
        $this->authorizeFreelancer($contract);

        try {
            $this->reviewService->store(
                contract: $contract,
                reviewerType: 'seller',
                reviewerId: auth('freelancer')->id(),
                rating: (int)$request->input('rating'),
                body: $request->input('body'),
            );
        } catch (RuntimeException $exception) {
            ToastMagic::error($exception->getMessage());
            return back();
        }

        ToastMagic::success(translate('review_submitted_successfully'));
        return back();
    }

    private function authorizeFreelancer(FreelancerContract $contract): void
    {
        abort_unless($contract->seller_id === auth('freelancer')->id(), 403);
    }
}
