<?php

namespace App\Services;

use App\Models\FreelancerContract;
use App\Models\FreelancerContractMilestone;
use App\Models\FreelancerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FreelancerContractService
{
    /**
     * @param array<int, array{title: string, amount: float|string}> $milestones
     */
    public function hire(FreelancerService $service, int $customerId, string $scope, float $amount, array $milestones, ?string $paymentRequestId = null): FreelancerContract
    {
        return DB::transaction(function () use ($service, $customerId, $scope, $amount, $milestones, $paymentRequestId) {
            // Idempotency: if this exact payment already produced a contract (e.g. the
            // customer reloaded the gateway's success URL), return it instead of
            // creating — and paying the freelancer for — a second one.
            if ($paymentRequestId) {
                $existing = FreelancerContract::where('payment_request_id', $paymentRequestId)->first();
                if ($existing) {
                    return $existing->load('milestones');
                }
            }

            $contract = FreelancerContract::create([
                'customer_id' => $customerId,
                'seller_id' => $service->seller_id,
                'freelancer_service_id' => $service->id,
                'payment_request_id' => $paymentRequestId,
                'scope' => $scope,
                'total_amount' => $amount,
                'status' => 'active',
            ]);

            foreach (array_values($milestones) as $position => $milestone) {
                $contract->milestones()->create([
                    'title' => $milestone['title'],
                    'amount' => $milestone['amount'],
                    'status' => 'pending',
                    'position' => $position,
                ]);
            }

            return $contract->load('milestones');
        });
    }

    /**
     * @param UploadedFile[] $files
     */
    public function submitMilestone(int $milestoneId, string $sellerId, ?string $note, array $files = []): FreelancerContractMilestone
    {
        return DB::transaction(function () use ($milestoneId, $sellerId, $note, $files) {
            $milestone = FreelancerContractMilestone::with('contract')->lockForUpdate()->findOrFail($milestoneId);

            if ((int)$milestone->contract->seller_id !== (int)$sellerId) {
                throw new RuntimeException('This milestone does not belong to this freelancer.');
            }

            if ($milestone->status === 'completed') {
                throw new RuntimeException('This milestone has already been completed.');
            }

            $deliverable = $milestone->deliverables()->create([
                'seller_id' => $sellerId,
                'note' => $note,
            ]);

            foreach ($files as $file) {
                $diskPath = $file->store("freelancer-contracts/{$milestone->freelancer_contract_id}/deliverables", 'local');

                $deliverable->attachments()->create([
                    'disk_path' => $diskPath,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }

            $milestone->update(['status' => 'submitted', 'submitted_at' => now()]);
            $milestone->contract->update(['status' => 'submitted']);

            return $milestone->fresh(['deliverables.attachments']);
        });
    }

    public function approveMilestone(int $milestoneId, int $customerId): FreelancerContract
    {
        return DB::transaction(function () use ($milestoneId, $customerId) {
            $milestone = FreelancerContractMilestone::with('contract')->lockForUpdate()->findOrFail($milestoneId);
            $contract = $milestone->contract;

            if ((int)$contract->customer_id !== $customerId) {
                throw new RuntimeException('This milestone does not belong to this customer.');
            }

            if ($milestone->status !== 'submitted') {
                throw new RuntimeException('Only submitted milestones can be approved.');
            }

            $milestone->update(['status' => 'completed', 'approved_at' => now()]);

            $remaining = $contract->milestones()->where('status', '!=', 'completed')->count();

            if ($remaining === 0) {
                $contract->update(['status' => 'completed', 'completed_at' => now()]);
                $contract->freelancer()->increment('freelancer_jobs_completed');
            } else {
                $contract->update(['status' => 'active']);
            }

            return $contract->fresh(['milestones']);
        });
    }

    public function cancel(FreelancerContract $contract): FreelancerContract
    {
        $contract->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return $contract->fresh();
    }

    /**
     * Customer-initiated request to end a contract that isn't going anywhere.
     * Cancellation itself is admin-mediated (no refund mechanism exists for
     * freelancer contracts — see FreelancerOrderService, the full amount is
     * captured and paid out to the freelancer at hire time), so this only
     * flags the contract for review; nothing financial changes here.
     */
    public function requestCancellation(FreelancerContract $contract, int $customerId, string $reason): FreelancerContract
    {
        if ((int) $contract->customer_id !== $customerId) {
            throw new RuntimeException('This contract does not belong to this customer.');
        }

        if (!in_array($contract->status, ['active', 'submitted'], true)) {
            throw new RuntimeException('This contract can no longer be cancelled.');
        }

        if ($contract->cancellation_status === 'requested') {
            throw new RuntimeException('A cancellation request is already pending review.');
        }

        $contract->update([
            'cancellation_status' => 'requested',
            'cancellation_reason' => $reason,
            'cancellation_admin_note' => null,
            'cancellation_requested_at' => now(),
            'cancellation_decided_at' => null,
        ]);

        return $contract->fresh();
    }

    public function approveCancellation(FreelancerContract $contract, ?string $adminNote = null): FreelancerContract
    {
        if ($contract->cancellation_status !== 'requested') {
            throw new RuntimeException('This contract has no pending cancellation request.');
        }

        $this->cancel($contract);

        $contract->update([
            'cancellation_status' => 'approved',
            'cancellation_admin_note' => $adminNote,
            'cancellation_decided_at' => now(),
        ]);

        return $contract->fresh();
    }

    public function denyCancellation(FreelancerContract $contract, ?string $adminNote = null): FreelancerContract
    {
        if ($contract->cancellation_status !== 'requested') {
            throw new RuntimeException('This contract has no pending cancellation request.');
        }

        $contract->update([
            'cancellation_status' => 'denied',
            'cancellation_admin_note' => $adminNote,
            'cancellation_decided_at' => now(),
        ]);

        return $contract->fresh();
    }
}
