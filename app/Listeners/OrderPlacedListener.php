<?php

namespace App\Listeners;

use App\Events\OrderPlacedEvent;
use App\Models\Seller;
use App\Models\VendorNotification;
use App\Traits\EmailTemplateTrait;
use App\Traits\PushNotificationTrait;
use App\Utils\OrderManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// NOTE: deliberately NOT implementing ShouldQueue. There is no queue worker
// running in this deployment (QUEUE_CONNECTION is unset, defaulting to
// 'sync'), so a ShouldQueue listener is invoked synchronously via
// CallQueuedListener anyway — but it does so from inside whatever DB
// transaction/request context fired the event (generate_order() fires
// OrderPlacedEvent from inside a DB::transaction() during checkout), which
// made checkout-complete-wallet hang for 60-100s+ with no error logged.
// This listener already defers its own slow work (mail, FCM push) via
// dispatch(...)->afterResponse() inside handle() — matching the sibling
// OrderStatusListener, which never had ShouldQueue and does not exhibit
// this hang. Keep both listeners plain (non-queued) for consistency unless
// a real queue worker is provisioned.
class OrderPlacedListener
{
    use PushNotificationTrait, EmailTemplateTrait;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPlacedEvent $event): void
    {
        if ($event->email) {
            // Mail sends synchronously (no queue worker configured) — defer it past the
            // response so checkout doesn't block on an SMTP/Brevo round-trip.
            dispatch(function () use ($event) {
                try {
                    $this->sendMail($event);
                } catch (\Throwable $e) {
                    \Log::warning('[OrderPlacedListener] Deferred mail send failed: ' . $e->getMessage());
                }
            })->afterResponse();
        }
        if ($event->notification) {
            // FCM push (JWT sign + OAuth round-trip + send) is a synchronous outbound
            // HTTP call — defer it past the response like the mail above, so checkout
            // doesn't block on Google's endpoints. This fires once per order per
            // recipient (customer + seller), so it was doubling the blocking cost.
            dispatch(function () use ($event) {
                try {
                    $this->sendNotification($event);
                } catch (\Throwable $e) {
                    Log::warning('[OrderPlacedListener] Deferred push notification failed: ' . $e->getMessage());
                }
            })->afterResponse();
        }

    }

    private function sendMail(OrderPlacedEvent $event): void
    {
        $email = $event->email;
        $data = $event->data;

        if (!empty($data['invoiceOrderId'])) {
            try {
                $data['attachmentPath'] = OrderManager::storeInvoice($data['invoiceOrderId']);
            } catch (\Throwable $e) {
                Log::warning('[OrderPlacedListener] Invoice PDF generation failed: ' . $e->getMessage(), [
                    'order_id' => $data['invoiceOrderId'],
                ]);
            }
        }

        try {
            $this->getEmailTemplateDataForUpdate($data['userType']);
            $this->sendingMail(sendMailTo: $email, userType: $data['userType'], templateName: $data['templateName'], data: $data);
        } catch (\Throwable $exception) {
            Log::warning('[OrderPlacedListener] sendMail failed: ' . $exception->getMessage(), [
                'template' => $data['templateName'] ?? null,
                'userType' => $data['userType'] ?? null,
            ]);
        }
    }

    private function sendNotification(OrderPlacedEvent $event): void
    {
        $key = $event->notification->key;
        $type = $event->notification->type;
        $order = $event->notification->order;
        $this->sendOrderNotification(key: $key, type: $type, order: $order);

        if ($type === 'seller' && $order?->seller_id) {
            $this->createVendorNotification($order);
        }
    }

    private function createVendorNotification($order): void
    {
        try {
            $isFreelancer = Seller::where('id', $order->seller_id)->value('seller_type') === 'freelancer';

            VendorNotification::create([
                'seller_id' => $order->seller_id,
                'title' => translate('new_order_received'),
                'message' => translate('you_have_received_a_new_order') . ' #' . $order->id,
                'link' => $isFreelancer ? route('freelancer.dashboard.index') : route('vendor.orders.details', $order->id),
                'reference_id' => $order->id,
                'read_at' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('[OrderPlacedListener] Vendor notification failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
            ]);
        }
    }
}
