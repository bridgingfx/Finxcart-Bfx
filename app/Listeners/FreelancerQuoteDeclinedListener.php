<?php

namespace App\Listeners;

use App\Enums\EmailTemplateKey;
use App\Events\FreelancerQuoteDeclinedEvent;
use App\Models\AdminNotification;
use App\Models\VendorNotification;
use App\Traits\EmailTemplateTrait;
use App\Traits\PushNotificationTrait;
use Illuminate\Support\Facades\Log;

class FreelancerQuoteDeclinedListener
{
    use EmailTemplateTrait, PushNotificationTrait;

    public function handle(FreelancerQuoteDeclinedEvent $event): void
    {
        $quote = $event->quote->load(['customer', 'seller', 'service']);
        $freelancer = $quote->seller;
        $customerName = trim(($quote->customer?->f_name ?? '') . ' ' . ($quote->customer?->l_name ?? '')) ?: 'A customer';

        try {
            $title = $customerName . ' declined your quote';
            $message = 'Your quote for "' . $quote->service?->title . '" was declined.';

            VendorNotification::create([
                'seller_id' => $quote->seller_id,
                'title' => $title,
                'message' => $message,
                'link' => route('freelancer.quotes.view', $quote->id),
                'reference_id' => $quote->id,
                'read_at' => null,
            ]);

            $this->sendGenericPushNotification($quote->seller?->cm_firebase_token, $title, $message);
        } catch (\Throwable $e) {
            Log::error('[FreelancerQuoteDeclinedListener] Notification failed: ' . $e->getMessage());
        }

        try {
            AdminNotification::create([
                'type' => 'freelancer_quote_declined',
                'title' => 'Quote Request Declined',
                'message' => "{$customerName} declined a quote for \"{$quote->service?->title}\".",
                'link' => route('admin.freelancer.accounts.index'),
                'reference_id' => $quote->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('[FreelancerQuoteDeclinedListener] Admin notification failed: ' . $e->getMessage());
        }

        if (empty($freelancer?->email)) {
            return;
        }

        // Mail sends synchronously (no queue worker configured) — defer past the response.
        dispatch(function () use ($freelancer, $customerName, $quote) {
            try {
                $this->getEmailTemplateDataForUpdate('vendor');
                $this->sendingMail(
                    sendMailTo: $freelancer->email,
                    userType: 'vendor',
                    templateName: EmailTemplateKey::QUOTE_REQUEST_DECLINED,
                    data: [
                        'vendorName' => trim($freelancer->f_name . ' ' . $freelancer->l_name),
                        'userName' => $customerName,
                        'message' => $quote->service?->title,
                    ],
                );
            } catch (\Throwable $e) {
                Log::error('[FreelancerQuoteDeclinedListener] Email failed: ' . $e->getMessage());
            }
        })->afterResponse();
    }
}
