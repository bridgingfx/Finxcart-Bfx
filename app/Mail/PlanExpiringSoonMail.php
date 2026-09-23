<?php

namespace App\Mail;

use App\Models\VendorTier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlanExpiringSoonMail extends Mailable
{
    use Queueable, SerializesModels;

    public $vendorTier;
    public $paymentLink;
    public $isTrial;
    public $daysLeft;

    /**
     * Create a new message instance.
     */
    public function __construct(VendorTier $vendorTier, $paymentLink, $daysLeft)
    {
        $this->vendorTier = $vendorTier;
        $this->paymentLink = $paymentLink;
        $this->daysLeft = $daysLeft;
        $this->isTrial = $vendorTier->status === 'trial';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isTrial 
            ? "Action Required: Your Free Trial Ends in {$this->daysLeft} Days" 
            : "Reminder: Your Seller Plan Expires in {$this->daysLeft} Days";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor.plan_expiring_soon',
            with: [
                'planName' => $this->vendorTier->tier->name,
                'endDate' => $this->vendorTier->end_date->format('F d, Y'),
                'sellerName' => $this->vendorTier->seller->f_name,
                'paymentLink' => $this->paymentLink,
                'isTrial' => $this->isTrial,
                'daysLeft' => $this->daysLeft,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}