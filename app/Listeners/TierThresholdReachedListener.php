<?php

namespace App\Listeners;

use App\Events\TierThresholdReachedEvent;
use App\Models\VendorNotification;
use Illuminate\Support\Facades\Log;

class TierThresholdReachedListener
{
    public function handle(TierThresholdReachedEvent $event): void
    {
        try {
            $title = 'Listing Limit Reached';

            $exists = VendorNotification::where('seller_id', $event->sellerId)
                ->where('title', $title)
                ->where('link', $event->upgradeUrl)
                ->whereNull('read_at')
                ->exists();

            if ($exists) {
                return;
            }

            VendorNotification::create([
                'seller_id' => $event->sellerId,
                'title' => $title,
                'message' => sprintf(
                    'You have %d active product(s) on %s. Your limit is %d. Upgrade your tier to publish more products.',
                    $event->currentCount,
                    $event->tierName,
                    $event->tierLimit
                ),
                'link' => $event->upgradeUrl,
                'reference_id' => null,
                'read_at' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('[TierThresholdReachedListener] Notification failed: ' . $e->getMessage());
        }
    }
}
