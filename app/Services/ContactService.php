<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class ContactService
{
    public function getAddData(object $request): array
    {
        return [
            'name' => $request['name'],
            'email' => $request['email'],
            'mobile_number' => $request['mobile_number'],
            'subject' => $request['subject'],
            'message' => $request['message'],
        ];
    }
    public function getMailData(object $request): array
    {
        return [
            'reply' => json_encode([
                'subject' => $request['subject'],
                'body' => $request['mail_body']
            ])
        ];
    }

    public function sendContactReplyMail(string $subject, array $data, string $contactEmail, string $companyName): void
    {
        Mail::send('email-templates.customer-message', $data, function ($message) use ($contactEmail, $subject, $companyName) {
            $message->to($contactEmail, $companyName)
                ->subject($subject);
        });
    }

}
