<?php

namespace App\Listeners;

use App\Events\PasswordResetEvent;
use App\Traits\EmailTemplateTrait;
use Illuminate\Support\Facades\Log;

class PasswordResetListener
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
    public function handle(PasswordResetEvent $event): void
    {
          Log::info("PasswordResetListener triggered for email: {$event->email}");

        // Mail sends synchronously (no queue worker configured) — defer past the response.
        dispatch(function () use ($event) {
            try {
                $this->sendMail($event);
            } catch (\Throwable $e) {
                \Log::warning('[PasswordResetListener] Deferred mail send failed: ' . $e->getMessage());
            }
        })->afterResponse();
    }

    private function sendMail(PasswordResetEvent $event): void
    {
        $email = $event->email;
        $data = $event->data;
        Log::info("Sending password reset email to: {$email} with template: {$data['templateName']}");

        try {
            $this->sendingMail(sendMailTo: $email, userType: $data['userType'], templateName: $data['templateName'], data: $data);
            Log::info("Password reset email sent successfully to: {$email}");
        } catch (\Throwable $e) {
            Log::error("Password reset email failed for {$email}: {$e->getMessage()}", [
                'template' => $data['templateName'] ?? null,
                'mail_config' => config('mail.mailers.smtp'),
            ]);

            throw $e;
        }
    }
}
