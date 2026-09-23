<?php

namespace App\Notifications\Freelancer;

use App\Models\FreelancerContractMilestone;
use App\Notifications\Channels\BrandedMailChannel;
use App\Notifications\Channels\VendorNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MilestoneApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly FreelancerContractMilestone $milestone)
    {
    }

    public function via(object $notifiable): array
    {
        return [BrandedMailChannel::class, VendorNotificationChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(translate('delivery_approved'))
            ->line(translate('the_client_approved_your_delivery'))
            ->action(translate('view_contract'), route('freelancer.contracts.show', $this->milestone->freelancer_contract_id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => translate('delivery_approved'),
            'message' => translate('the_client_approved_your_delivery'),
            'link' => route('freelancer.contracts.show', $this->milestone->freelancer_contract_id),
            'reference_id' => $this->milestone->freelancer_contract_id,
        ];
    }
}
