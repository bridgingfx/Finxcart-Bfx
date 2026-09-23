<?php

namespace App\Listeners;

use App\Enums\EmailTemplateKey;
use App\Events\OrderStatusEvent;
use App\Traits\EmailTemplateTrait;
use App\Traits\FileManagerTrait;
use App\Traits\PushNotificationTrait;
use Illuminate\Support\Facades\Log;

class OrderStatusListener
{
    use PushNotificationTrait;
    use EmailTemplateTrait;
    use FileManagerTrait;

    public function __construct()
    {
        //
    }

    public function handle(OrderStatusEvent $event): void
    {
        // FCM push (JWT sign + OAuth round-trip + send) is a synchronous outbound
        // HTTP call — defer it past the response like the mail below, so order-status
        // updates (admin/vendor) don't block on Google's endpoints.
        dispatch(function () use ($event) {
            try {
                $this->sendNotification($event);
            } catch (\Throwable $e) {
                \Log::warning('[OrderStatusListener] Deferred push notification failed: ' . $e->getMessage());
            }
        })->afterResponse();

        // Mail sends synchronously (no queue worker configured) — defer it past the
        // response so order-status updates (admin/vendor) don't block on SMTP/Brevo.
        dispatch(function () use ($event) {
            try {
                $this->sendStatusEmail($event);
            } catch (\Throwable $e) {
                \Log::warning('[OrderStatusListener] Deferred mail send failed: ' . $e->getMessage());
            }
        })->afterResponse();
    }

    private function sendNotification(OrderStatusEvent $event): void
    {
        $this->sendOrderNotification(key: $event->key, type: $event->type, order: $event->order);
    }

    private function sendStatusEmail(OrderStatusEvent $event): void
    {
        try {
            $order  = $event->order;
            $status = $event->key;

            $customerEmail = $order->customer?->email;
            if (!$customerEmail) {
                return;
            }

            // Detect if order is digital-only to skip out_for_delivery for digital orders
            $isDigitalOnly = true;
            foreach ($order->orderDetails ?? [] as $detail) {
                $product = json_decode($detail->product_details ?? '{}');
                if (($product->product_type ?? '') !== 'digital') {
                    $isDigitalOnly = false;
                    break;
                }
            }

            $physicalStatuses = ['confirmed', 'processing', 'out_for_delivery', 'delivered', 'canceled', 'returned', 'failed'];
            $digitalStatuses  = ['confirmed', 'processing', 'delivered', 'canceled', 'failed'];
            $allowedStatuses  = $isDigitalOnly ? $digitalStatuses : $physicalStatuses;

            if (!in_array($status, $allowedStatuses)) {
                return;
            }

            $statusLabel  = ucwords(str_replace('_', ' ', $status));
            $customerName = trim(($order->customer?->f_name ?? '') . ' ' . ($order->customer?->l_name ?? ''));

            $this->getEmailTemplateDataForUpdate('customer');
            $this->sendingMail(
                sendMailTo: $customerEmail,
                userType:   'customer',
                templateName: EmailTemplateKey::ORDER_STATUS_UPDATE,
                data: [
                    'userName' => $customerName ?: 'Customer',
                    'orderId'  => $order->id,
                    'message'  => $statusLabel,
                ]
            );

            if ($status === 'confirmed') {
                foreach ($order->orderDetails ?? [] as $detail) {
                    if (($detail->digital_delivery_type ?? null) === 'email' && !empty($detail->digital_delivery_value)) {
                        try {
                            $orderUrl = route('account-order-details', ['id' => $order->id]);
                            $this->sendingMail(
                                sendMailTo: $detail->digital_delivery_value,
                                userType:   'customer',
                                templateName: EmailTemplateKey::ORDER_STATUS_UPDATE,
                                data: [
                                    'userName' => $customerName ?: 'Customer',
                                    'orderId'  => $order->id,
                                    'message'  => 'Your digital product order is confirmed. <a href="' . $orderUrl . '">Click here to download it</a>.',
                                ]
                            );
                        } catch (\Throwable $e) {
                            Log::error('Digital delivery confirmed email failed', [
                                'order_id' => $order->id,
                                'email'    => $detail->digital_delivery_value,
                                'error'    => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Order status email failed', [
                'order_id' => $event->order->id ?? null,
                'status'   => $event->key,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    public function sendDigitalReadyMail(string $email, string $customerName, int $orderId): void
    {
        $this->getEmailTemplateDataForUpdate('customer');
        $this->sendingMail(
            sendMailTo: $email,
            userType:   'customer',
            templateName: EmailTemplateKey::ORDER_STATUS_UPDATE,
            data: [
                'userName' => $customerName ?: 'Customer',
                'orderId'  => $orderId,
                'message'  => 'Your digital file is ready. <a href="' . route('account-order-details', ['id' => $orderId]) . '">Click here to download it</a>.',
            ]
        );
    }
}
