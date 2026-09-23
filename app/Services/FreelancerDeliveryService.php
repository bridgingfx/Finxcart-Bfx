<?php

namespace App\Services;

use App\Enums\Freelancer\DeliveryStatus;
use App\Models\FreelancerContract;
use App\Notifications\Freelancer\DeliveryStatusUpdatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FreelancerDeliveryService
{
    public function __construct(private readonly FreelancerAuditLogService $auditLog)
    {
    }

    public function updateDeliveryStatus(FreelancerContract $contract, int $sellerId, DeliveryStatus $newStatus): FreelancerContract
    {
        if ((int) $contract->seller_id !== $sellerId) {
            throw new RuntimeException('This contract does not belong to this freelancer.');
        }

        if (in_array($contract->status, ['completed', 'cancelled'], true)) {
            throw new RuntimeException('This contract is no longer active.');
        }

        $current = $contract->delivery_status;

        if (!$current->canTransitionTo($newStatus)) {
            throw new RuntimeException("Cannot move delivery status from \"{$current->value}\" to \"{$newStatus->value}\".");
        }

        DB::transaction(function () use ($contract, $newStatus) {
            $before = ['delivery_status' => $contract->delivery_status->value];

            $contract->update([
                'delivery_status' => $newStatus,
                'delivered_at' => $newStatus === DeliveryStatus::Delivered ? now() : $contract->delivered_at,
            ]);

            $this->auditLog->log(
                actorType: 'seller',
                actorId: $contract->seller_id,
                subject: $contract,
                action: 'delivery_status_updated',
                before: $before,
                after: ['delivery_status' => $newStatus->value],
            );
        });

        // Mail sends synchronously (no queue configured) — defer it past the response
        // so the freelancer isn't stuck waiting on an SMTP round-trip after clicking Update.
        dispatch(function () use ($contract) {
            try {
                $contract->customer->notify(new DeliveryStatusUpdatedNotification($contract));
            } catch (\Throwable $e) {
                Log::error('[FreelancerDeliveryService] Customer notification failed for contract ' . $contract->id . ': ' . $e->getMessage());
            }
        })->afterResponse();

        return $contract->fresh();
    }
}
