<?php

namespace App\Listeners;

use App\Events\AddFundToWalletEvent;
use App\Traits\EmailTemplateTrait;

class AddFundToWalletListener
{
    use EmailTemplateTrait;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param AddFundToWalletEvent $event
     * @return void
     */
    public function handle(AddFundToWalletEvent $event): void
    {
        // Mail sends synchronously (no queue worker configured) — defer past the response.
        dispatch(function () use ($event) {
            try {
                $this->sendMail($event);
            } catch (\Throwable $e) {
                \Log::warning('[AddFundToWalletListener] Deferred mail send failed: ' . $e->getMessage());
            }
        })->afterResponse();
    }

    private function sendMail(AddFundToWalletEvent $event):void{
        $email = $event->email;
        $data = $event->data;
        $this->sendingMail(sendMailTo: $email,userType: $data['userType'],templateName: $data['templateName'],data: $data);
    }
}
