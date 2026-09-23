<?php

namespace App\Listeners;

use App\Events\DigitalProductDownloadEvent;
use App\Mail\DigitalProductDownloadMail;
use App\Traits\EmailTemplateTrait;
use Illuminate\Support\Facades\Mail;

class DigitalProductDownloadListener
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
    public function handle(DigitalProductDownloadEvent $event): void
    {
        // Mail sends synchronously (no queue worker configured) — defer past the response.
        dispatch(function () use ($event) {
            try {
                $this->sendMail($event);
            } catch (\Throwable $e) {
                \Log::warning('[DigitalProductDownloadListener] Deferred mail send failed: ' . $e->getMessage());
            }
        })->afterResponse();
    }

    private function sendMail(DigitalProductDownloadEvent $event): void
    {
        $email = $event->email;
        $data = $event->data;
        $this->sendingMail(sendMailTo: $email, userType: $data['userType'], templateName: $data['templateName'], data: $data);
    }
}
