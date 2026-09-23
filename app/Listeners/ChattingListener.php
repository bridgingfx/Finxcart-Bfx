<?php

namespace App\Listeners;

use App\Enums\EmailTemplateKey;
use App\Events\ChattingEvent;
use App\Models\CustomerNotification;
use App\Models\Seller;
use App\Models\VendorNotification;
use App\Traits\EmailTemplateTrait;
use App\Traits\PushNotificationTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChattingListener
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
    public function handle(ChattingEvent $event): void
    {
        // FCM push (JWT sign + OAuth round-trip + send) is a synchronous outbound
        // HTTP call — defer it past the response so sending a chat message doesn't
        // block on Google's endpoints.
        dispatch(function () use ($event) {
            try {
                $this->sendNotification($event);
            } catch (\Throwable $e) {
                Log::warning('[ChattingListener] Deferred push notification failed: ' . $e->getMessage());
            }
        })->afterResponse();

        $this->notifyFreelancerOfNewMessage($event);
        $this->notifyFreelancerOfAdminMessage($event);
        $this->notifyCustomerOfNewMessage($event);
    }

    private function sendNotification(ChattingEvent $event): void
    {
        $key = $event->key;
        $type = $event->type;
        $userData = $event->userData;
        $messageForm = $event->messageForm;
        $this->chattingNotification(key: $key, type: $type, userData: $userData, messageForm: $messageForm);
    }

    private function notifyFreelancerOfNewMessage(ChattingEvent $event): void
    {
        if ($event->key !== 'message_from_customer' || $event->type !== 'seller') {
            return;
        }

        $freelancer = $event->userData;
        if (!($freelancer instanceof Seller) || $freelancer->seller_type !== 'freelancer') {
            return;
        }

        $customer = $event->messageForm;
        $customerName = trim(($customer->f_name ?? '') . ' ' . ($customer->l_name ?? '')) ?: 'A customer';
        $messageText = trim((string) $event->messageText);
        $preview = $messageText !== '' ? Str::limit($messageText, 120) : 'sent you an attachment.';

        try {
            $title = 'New Message from ' . $customerName;

            VendorNotification::create([
                'seller_id' => $freelancer->id,
                'title' => $title,
                'message' => $preview,
                'link' => route('freelancer.messages.index', ['type' => 'customer']),
                'reference_id' => $customer->id ?? null,
                'read_at' => null,
            ]);

            $fcmToken = $freelancer->cm_firebase_token;
            dispatch(function () use ($fcmToken, $title, $preview) {
                try {
                    $this->sendGenericPushNotification($fcmToken, $title, $preview);
                } catch (\Throwable $e) {
                    Log::warning('[ChattingListener] Deferred freelancer push failed: ' . $e->getMessage());
                }
            })->afterResponse();
        } catch (\Throwable $e) {
            Log::error('[ChattingListener] Freelancer chat notification failed: ' . $e->getMessage());
        }

        if (empty($freelancer->email)) {
            return;
        }

        // Mail sends synchronously (no queue worker configured) — defer past the response
        // so sending a chat message doesn't block on an SMTP/Brevo round-trip.
        dispatch(function () use ($freelancer, $customerName, $messageText) {
            try {
                $this->getEmailTemplateDataForUpdate('vendor');
                $this->sendingMail(
                    sendMailTo: $freelancer->email,
                    userType: 'vendor',
                    templateName: EmailTemplateKey::NEW_CHAT_MESSAGE,
                    data: [
                        'vendorName' => trim($freelancer->f_name . ' ' . $freelancer->l_name),
                        'userName' => $customerName,
                        'message' => $messageText !== '' ? $messageText : 'Sent you an attachment.',
                    ],
                );
            } catch (\Throwable $e) {
                Log::error('[ChattingListener] Freelancer chat email failed: ' . $e->getMessage());
            }
        })->afterResponse();
    }

    private function notifyFreelancerOfAdminMessage(ChattingEvent $event): void
    {
        if ($event->key !== 'message_from_admin' || $event->type !== 'freelancer') {
            return;
        }

        $freelancer = $event->userData;
        if (!($freelancer instanceof Seller) || $freelancer->seller_type !== 'freelancer') {
            return;
        }

        $messageText = trim((string) $event->messageText);
        $preview = $messageText !== '' ? Str::limit($messageText, 120) : 'sent you an attachment.';

        try {
            $title = 'New Message from Admin';

            VendorNotification::create([
                'seller_id' => $freelancer->id,
                'title' => $title,
                'message' => $preview,
                'link' => route('freelancer.messages.admin'),
                'reference_id' => null,
                'read_at' => null,
            ]);

            $fcmToken = $freelancer->cm_firebase_token;
            dispatch(function () use ($fcmToken, $title, $preview) {
                try {
                    $this->sendGenericPushNotification($fcmToken, $title, $preview);
                } catch (\Throwable $e) {
                    Log::warning('[ChattingListener] Deferred freelancer admin-message push failed: ' . $e->getMessage());
                }
            })->afterResponse();
        } catch (\Throwable $e) {
            Log::error('[ChattingListener] Freelancer admin message notification failed: ' . $e->getMessage());
        }
    }

    private function notifyCustomerOfNewMessage(ChattingEvent $event): void
    {
        if ($event->key !== 'message_from_seller' || $event->type !== 'customer') {
            return;
        }

        $customer = $event->userData;
        $customerId = is_object($customer) ? ($customer->id ?? null) : null;
        if (!$customerId) {
            return;
        }

        $seller = $event->messageForm;
        $sellerName = trim(($seller->f_name ?? '') . ' ' . ($seller->l_name ?? '')) ?: 'A seller';
        $messageText = trim((string) $event->messageText);
        $preview = $messageText !== '' ? Str::limit($messageText, 120) : 'sent you an attachment.';

        try {
            $title = 'New Message from ' . $sellerName;

            CustomerNotification::create([
                'customer_id' => $customerId,
                'title' => $title,
                'message' => $preview,
                'link' => route('chat', ['type' => 'vendor']),
                'reference_id' => $seller->id ?? null,
                'read_at' => null,
            ]);

            $fcmToken = $customer->cm_firebase_token ?? null;
            dispatch(function () use ($fcmToken, $title, $preview) {
                try {
                    $this->sendGenericPushNotification($fcmToken, $title, $preview);
                } catch (\Throwable $e) {
                    Log::warning('[ChattingListener] Deferred customer push failed: ' . $e->getMessage());
                }
            })->afterResponse();
        } catch (\Throwable $e) {
            Log::error('[ChattingListener] Customer chat notification failed: ' . $e->getMessage());
        }

        if (empty($customer->email)) {
            return;
        }

        // Mail sends synchronously (no queue worker configured) — defer past the response
        // so sending a chat message doesn't block on an SMTP/Brevo round-trip.
        dispatch(function () use ($customer, $sellerName, $messageText) {
            try {
                $this->getEmailTemplateDataForUpdate('customer');
                $this->sendingMail(
                    sendMailTo: $customer->email,
                    userType: 'customer',
                    templateName: EmailTemplateKey::NEW_CHAT_MESSAGE_CUSTOMER,
                    data: [
                        'userName' => trim(($customer->f_name ?? '') . ' ' . ($customer->l_name ?? '')),
                        'vendorName' => $sellerName,
                        'message' => $messageText !== '' ? $messageText : 'Sent you an attachment.',
                    ],
                );
            } catch (\Throwable $e) {
                Log::error('[ChattingListener] Customer chat email failed: ' . $e->getMessage());
            }
        })->afterResponse();
    }
}
