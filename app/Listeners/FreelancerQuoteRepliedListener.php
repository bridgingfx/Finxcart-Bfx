<?php

namespace App\Listeners;

use App\Enums\EmailTemplateKey;
use App\Events\FreelancerQuoteRepliedEvent;
use App\Models\AdminNotification;
use App\Models\CustomerNotification;
use App\Traits\EmailTemplateTrait;
use App\Traits\PushNotificationTrait;
use Illuminate\Support\Facades\Log;

class FreelancerQuoteRepliedListener
{
    use EmailTemplateTrait, PushNotificationTrait;

    public function handle(FreelancerQuoteRepliedEvent $event): void
    {
        $quote = $event->quote->load(['customer', 'seller', 'service']);
        $customer = $quote->customer;
        $freelancer = $quote->seller;

        if (!empty($customer?->email)) {
            // Mail sends synchronously (no queue worker configured) — defer past the response.
            dispatch(function () use ($customer, $freelancer, $quote) {
                try {
                    $this->getEmailTemplateDataForUpdate('customer');
                    $this->sendingMail(
                        sendMailTo: $customer->email,
                        userType: 'customer',
                        templateName: EmailTemplateKey::QUOTE_REQUEST_REPLIED,
                        data: [
                            'userName' => trim($customer->f_name . ' ' . $customer->l_name),
                            'vendorName' => trim(($freelancer?->f_name ?? '') . ' ' . ($freelancer?->l_name ?? '')),
                            'message' => $quote->reply_message,
                        ],
                    );
                } catch (\Throwable $e) {
                    Log::error('[FreelancerQuoteRepliedListener] Email failed: ' . $e->getMessage());
                }
            })->afterResponse();
        }

        $customerName = trim(($customer?->f_name ?? '') . ' ' . ($customer?->l_name ?? '')) ?: 'A customer';
        $freelancerName = trim(($freelancer?->f_name ?? '') . ' ' . ($freelancer?->l_name ?? '')) ?: 'A freelancer';

        if ($customer) {
            try {
                $title = $freelancerName . ' sent you a quote';
                $message = 'Your quote request for "' . $quote->service?->title . '" has a reply.';

                CustomerNotification::create([
                    'customer_id' => $customer->id,
                    'title' => $title,
                    'message' => $message,
                    'link' => route('hire.quotes.show', $quote->id),
                    'reference_id' => $quote->id,
                    'read_at' => null,
                ]);

                $this->sendGenericPushNotification($customer->cm_firebase_token, $title, $message);
            } catch (\Throwable $e) {
                Log::error('[FreelancerQuoteRepliedListener] Customer notification failed: ' . $e->getMessage());
            }
        }

        try {
            AdminNotification::create([
                'type' => 'freelancer_quote_replied',
                'title' => 'Freelancer Replied to Quote Request',
                'message' => "{$freelancerName} sent a quote response to {$customerName} for \"{$quote->service?->title}\".",
                'link' => route('admin.freelancer.accounts.index'),
                'reference_id' => $quote->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('[FreelancerQuoteRepliedListener] Admin notification failed: ' . $e->getMessage());
        }
    }
}
