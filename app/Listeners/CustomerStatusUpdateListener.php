<?php

namespace App\Listeners;

use App\Events\CustomerStatusUpdateEvent;
use App\Traits\EmailTemplateTrait;

class CustomerStatusUpdateListener
{
    use EmailTemplateTrait;
    public function __construct()
    {
        //
    }

    public function handle(CustomerStatusUpdateEvent $event): void
    {
        // Mail sends synchronously (no queue worker configured) — defer past the response.
        dispatch(function () use ($event) {
            try {
                $this->sendMail($event);
            } catch (\Throwable $e) {
                \Log::warning('[CustomerStatusUpdateListener] Deferred mail send failed: ' . $e->getMessage());
            }
        })->afterResponse();
    }

    private function sendMail(CustomerStatusUpdateEvent $event):void{
        $email = $event->email;
        $data = $event->data;
        $this->sendingMail(sendMailTo: $email,userType: $data['userType'],templateName: $data['templateName'],data: $data);
    }
}
