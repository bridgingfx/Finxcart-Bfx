<?php

namespace App\Mail;

use App\Models\WithdrawRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WithdrawRequestStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public WithdrawRequest $withdrawRequest;
    public string $statusLabel;

    public function __construct(WithdrawRequest $withdrawRequest)
    {
        $this->withdrawRequest = $withdrawRequest->load('seller');
        $this->statusLabel     = $withdrawRequest->approved == 1 ? 'Approved' : 'Denied';
    }

    public function envelope(): Envelope
    {
        $subject = $this->statusLabel === 'Approved'
            ? 'Your Withdrawal Request Has Been Approved'
            : 'Your Withdrawal Request Has Been Denied';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.withdraw-request-status',
            with: [
                'withdrawRequest' => $this->withdrawRequest,
                'statusLabel'     => $this->statusLabel,
            ],
        );
    }

    public function attachments(): array
    {
        if ($this->withdrawRequest->approved != 1) {
            return [];
        }

        $pdf = Pdf::loadView('emails.withdraw-request-pdf', [
            'withdrawRequest' => $this->withdrawRequest,
        ])->setPaper('a4');

        return [
            Attachment::fromData(fn () => $pdf->output(), 'withdrawal-receipt-' . $this->withdrawRequest->id . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
