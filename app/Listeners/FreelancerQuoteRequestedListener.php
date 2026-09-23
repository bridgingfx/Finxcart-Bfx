<?php

namespace App\Listeners;

use App\Enums\EmailTemplateKey;
use App\Events\FreelancerQuoteRequestedEvent;
use App\Models\AdminNotification;
use App\Models\VendorNotification;
use App\Traits\EmailTemplateTrait;
use App\Traits\PushNotificationTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FreelancerQuoteRequestedListener
{
    use EmailTemplateTrait, PushNotificationTrait;

    public function handle(FreelancerQuoteRequestedEvent $event): void
    {
        $quote = $event->quote->load(['customer', 'seller', 'service']);
        $freelancer = $quote->seller;
        $customerName = trim(($quote->customer?->f_name ?? '') . ' ' . ($quote->customer?->l_name ?? '')) ?: 'A customer';

        try {
            $title = 'New Quote Request from ' . $customerName;
            $message = Str::limit($quote->description, 120);

            VendorNotification::create([
                'seller_id' => $quote->seller_id,
                'title' => $title,
                'message' => $message,
                'link' => route('freelancer.quotes.view', $quote->id),
                'reference_id' => $quote->id,
                'read_at' => null,
            ]);

            $this->sendGenericPushNotification($freelancer?->cm_firebase_token, $title, $message);
        } catch (\Throwable $e) {
            Log::error('[FreelancerQuoteRequestedListener] Notification failed: ' . $e->getMessage());
        }

        try {
            $freelancerName = trim(($freelancer?->f_name ?? '') . ' ' . ($freelancer?->l_name ?? '')) ?: 'A freelancer';
            AdminNotification::create([
                'type' => 'freelancer_quote_requested',
                'title' => 'New Quote Request',
                'message' => "{$customerName} requested a quote from {$freelancerName} for \"{$quote->service?->title}\".",
                'link' => route('admin.freelancer.accounts.index'),
                'reference_id' => $quote->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('[FreelancerQuoteRequestedListener] Admin notification failed: ' . $e->getMessage());
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
                    templateName: EmailTemplateKey::NEW_QUOTE_REQUEST,
                    data: [
                        'vendorName' => trim($freelancer->f_name . ' ' . $freelancer->l_name),
                        'userName' => $customerName,
                        'message' => $quote->description,
                    ],
                );
            } catch (\Throwable $e) {
                Log::error('[FreelancerQuoteRequestedListener] Email failed: ' . $e->getMessage());
            }
        })->afterResponse();
    }
}
