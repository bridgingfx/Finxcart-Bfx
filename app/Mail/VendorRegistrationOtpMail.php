<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorRegistrationOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(protected string $otp)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your FinXCart Vendor Registration OTP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email-templates.vendor-registration-otp',
            with: [
                'otp' => $this->otp,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
