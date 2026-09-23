<?php

namespace App\Console\Commands;

use App\Models\ShipmentItem;
use App\Models\VendorNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDigitalDeliverySlaReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-digital-delivery-sla-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remind vendors about manual digital items still pending 12h/24h after order placement';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sent = $this->remindStage(stage: 0, hours: 12, nextStage: 1);
        $sent += $this->remindStage(stage: 1, hours: 24, nextStage: 2);

        $this->info("SendDigitalDeliverySlaReminders: sent {$sent} reminder(s).");
        return 0;
    }

    private function remindStage(int $stage, int $hours, int $nextStage): int
    {
        $items = ShipmentItem::with('shipment')
            ->where('type', 'digital')
            ->where('delivery_mode', 'manual')
            ->where('status', 'pending')
            ->where('sla_stage', $stage)
            ->where('created_at', '<=', now()->subHours($hours))
            ->get();

        $sentCount = 0;

        foreach ($items as $item) {
            if (!$item->shipment) {
                continue;
            }

            try {
                VendorNotification::create([
                    'seller_id' => $item->shipment->vendor_id,
                    'title' => translate('digital_delivery_reminder'),
                    'message' => translate('a_manual_digital_item_is_still_pending_after') . ' ' . $hours . ' ' . translate('hours') . '. #' . $item->shipment->source_order_id,
                    'link' => route('vendor.orders.details', $item->shipment->source_order_id),
                    'reference_id' => $item->shipment->source_order_id,
                    'read_at' => null,
                ]);
                $sentCount++;
            } catch (\Throwable $e) {
                Log::error('[SendDigitalDeliverySlaReminders] Notification failed: ' . $e->getMessage(), [
                    'shipment_item_id' => $item->id,
                ]);
                continue;
            }

            $item->update(['sla_stage' => $nextStage]);
        }

        return $sentCount;
    }
}
