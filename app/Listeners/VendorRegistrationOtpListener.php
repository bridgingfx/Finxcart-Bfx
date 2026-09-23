<?php

namespace App\Listeners;

use App\Events\VendorRegistrationOtpEvent;
use App\Traits\EmailTemplateTrait;
use Illuminate\Support\Str;

class VendorRegistrationOtpListener
{
    use EmailTemplateTrait;

    public function __construct()
    {
        //
    }

    public function handle(VendorRegistrationOtpEvent $event): void
    {
        $this->getEmailTemplateDataForUpdate($event->data['userType']);

        // Mail sends synchronously (no queue worker configured) — defer past the response.
        dispatch(function () use ($event) {
            // afterResponse() only truly detaches from the connection on servers that
            // support fastcgi_finish_request() (PHP-FPM). On `php artisan serve` (no
            // such support) the connection stays open, so an uncaught exception here
            // still overwrites/corrupts the response the browser already received.
            try {
                $this->sendMail($event);
            } catch (\Throwable $e) {
                \Log::warning('[VendorRegistrationOtpListener] Deferred mail send failed: ' . $e->getMessage());
            }
        })->afterResponse();
    }

    private function sendMail(VendorRegistrationOtpEvent $event): void
    {
        $email = $event->email;
        $data = $event->data;
        $data['vendorName'] = $data['vendorName'] ?? Str::before($email, '@');
        $this->sendingMail(
            sendMailTo: $email,
            userType: $data['userType'],
            templateName: $data['templateName'],
            data: $data
        );
    }
}
