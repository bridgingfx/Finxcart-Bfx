<?php

namespace App\Mail;

use App\Models\WithdrawRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewWithdrawRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public WithdrawRequest $withdrawRequest;

    public function __construct(WithdrawRequest $withdrawRequest)
    {
        $this->withdrawRequest = $withdrawRequest->load('seller', 'seller.shop');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Vendor Withdrawal Request Received');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-withdraw-request',
            with: [
                'withdrawRequest' => $this->withdrawRequest,
            ],
        );
    }
}
