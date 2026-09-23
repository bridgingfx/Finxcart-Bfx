<?php

namespace App\Listeners;

use App\Events\SplitTransactionCompleteEvent;
use App\Models\Seller;
use App\Models\VendorNotification;
use Illuminate\Support\Facades\Log;

class SplitTransactionCompleteListener
{
    public function handle(SplitTransactionCompleteEvent $event): void
    {
        $isService = $event->referenceType === 'freelancer_contract';
        $isFreelancer = Seller::where('id', $event->sellerId)->value('seller_type') === 'freelancer';

        try {
            VendorNotification::create([
                'seller_id' => $event->sellerId,
                'title' => $isService
                    ? '🎉 You have received a new service order. A customer has purchased your service.'
                    : '🎉 You have received a new product order. A customer has purchased your product.',
                'message' => sprintf(
                    'Gross: %s %.2f | Platform: %s %.2f | Service charge: %s %.2f | Net payout: %s %.2f',
                    $event->currency,
                    $event->grossAmount,
                    $event->currency,
                    $event->companyShareAmount,
                    $event->currency,
                    $event->serviceChargeAmount,
                    $event->currency,
                    $event->netVendorPayout
                ),
                'link' => $isService
                    ? ($event->referenceId
                        ? route('freelancer.contracts.show', $event->referenceId)
                        : route('freelancer.ledger.index'))
                    : ($isFreelancer ? route('freelancer.ledger.index') : route('vendor.transaction.expense-list')),
                'reference_id' => $event->ledgerId,
                'read_at' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('[SplitTransactionCompleteListener] Notification failed: ' . $e->getMessage());
        }
    }
}
