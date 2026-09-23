<?php

namespace App\Listeners;

use App\Events\EmailVerificationEvent;
use App\Traits\EmailTemplateTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class EmailVerificationListener
{
    use EmailTemplateTrait;
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
    public function handle(EmailVerificationEvent $event): void
    {
        // Mail sends synchronously (no queue worker configured) — defer past the response.
        dispatch(function () use ($event) {
            // afterResponse() only truly detaches from the connection on servers that
            // support fastcgi_finish_request() (PHP-FPM). On `php artisan serve` (no
            // such support) the connection stays open, so an uncaught exception here
            // still overwrites/corrupts the response the browser already received.
            try {
                $this->sendMail($event);
            } catch (\Throwable $e) {
                \Log::warning('[EmailVerificationListener] Deferred mail send failed: ' . $e->getMessage());
            }
        })->afterResponse();
    }

    private function sendMail(EmailVerificationEvent $event):void{
        $email = $event->email;
        $data = $event->data;
        $this->sendingMail(sendMailTo: $email,userType: $data['userType'],templateName: $data['templateName'],data: $data);
    }
}
