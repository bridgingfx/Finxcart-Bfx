<?php

namespace App\Listeners;

use App\Events\DeliverymanPasswordResetEvent;
use App\Traits\EmailTemplateTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class DeliverymanPasswordResetListener
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
    public function handle(DeliverymanPasswordResetEvent $event): void
    {
        // Mail sends synchronously (no queue worker configured) — defer past the response.
        dispatch(function () use ($event) {
            try {
                $this->sendMail($event);
            } catch (\Throwable $e) {
                \Log::warning('[DeliverymanPasswordResetListener] Deferred mail send failed: ' . $e->getMessage());
            }
        })->afterResponse();
    }

    private function sendMail(DeliverymanPasswordResetEvent $event):void{
        $email = $event->email;
        $data = $event->data;
        $this->sendingMail(sendMailTo: $email,userType: $data['userType'],templateName: $data['templateName'],data: $data);
    }
}
