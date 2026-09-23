<?php

namespace App\Services;

use App\Enums\Freelancer\DeliveryStatus;
use App\Enums\Freelancer\VerdictStatus;
use App\Models\FreelancerContract;
use App\Notifications\Freelancer\ServiceApprovedNotification;
use App\Notifications\Freelancer\ServiceRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FreelancerVerdictService
{
    public function __construct(private readonly FreelancerAuditLogService $auditLog)
    {
    }

    public function approveDelivery(FreelancerContract $contract, int $customerId): FreelancerContract
    {
        $this->assertReviewable($contract, $customerId);

        DB::transaction(function () use ($contract) {
            $before = [
                'delivery_status' => $contract->delivery_status->value,
                'verdict_status' => $contract->verdict_status->value,
            ];

            $contract->update([
                'verdict_status' => VerdictStatus::Approved,
                'status' => 'completed',
                'completed_at' => now(),
                'verdict_at' => now(),
            ]);

            $contract->freelancer()->increment('freelancer_jobs_completed');

            $this->auditLog->log(
                actorType: 'customer',
                actorId: $contract->customer_id,
                subject: $contract,
                action: 'verdict_approved',
                before: $before,
                after: ['status' => 'completed', 'verdict_status' => VerdictStatus::Approved->value],
            );
        });

        try {
            $contract->freelancer->notify(new ServiceApprovedNotification($contract));
        } catch (\Throwable $e) {
            Log::error('[FreelancerVerdictService] Approval notification failed for contract ' . $contract->id . ': ' . $e->getMessage());
        }

        return $contract->fresh();
    }

    public function rejectDelivery(FreelancerContract $contract, int $customerId, string $reason): FreelancerContract
    {
        $this->assertReviewable($contract, $customerId);

        $reason = trim($reason);
        if ($reason === '') {
            throw new RuntimeException('A rejection reason is required.');
        }

        DB::transaction(function () use ($contract, $reason) {
            $before = [
                'delivery_status' => $contract->delivery_status->value,
                'verdict_status' => $contract->verdict_status->value,
            ];

            // The verdict is recorded as rejected in the audit trail below, but the
            // live columns reset immediately (delivery back to in_progress, verdict
            // back to pending) so the freelancer's revision can go through this same
            // approve/reject gate again once redelivered.
            $contract->update([
                'delivery_status' => DeliveryStatus::InProgress,
                'verdict_status' => VerdictStatus::Pending,
                'rejection_reason' => $reason,
                'verdict_at' => now(),
            ]);

            $this->auditLog->log(
                actorType: 'customer',
                actorId: $contract->customer_id,
                subject: $contract,
                action: 'verdict_rejected',
                before: $before,
                after: [
                    'verdict_decision' => 'rejected',
                    'rejection_reason' => $reason,
                    'delivery_status' => DeliveryStatus::InProgress->value,
                    'verdict_status' => VerdictStatus::Pending->value,
                ],
            );
        });

        try {
            $contract->freelancer->notify(new ServiceRejectedNotification($contract, $reason));
        } catch (\Throwable $e) {
            Log::error('[FreelancerVerdictService] Rejection notification failed for contract ' . $contract->id . ': ' . $e->getMessage());
        }

        return $contract->fresh();
    }

    private function assertReviewable(FreelancerContract $contract, int $customerId): void
    {
        if ((int) $contract->customer_id !== $customerId) {
            throw new RuntimeException('This contract does not belong to this customer.');
        }

        if ($contract->delivery_status !== DeliveryStatus::Delivered) {
            throw new RuntimeException('A verdict can only be given once the work has been delivered.');
        }

        if ($contract->verdict_status !== VerdictStatus::Pending) {
            throw new RuntimeException('A verdict has already been recorded for this delivery.');
        }
    }
}
