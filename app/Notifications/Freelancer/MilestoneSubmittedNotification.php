<?php

namespace App\Notifications\Freelancer;

use App\Models\FreelancerContractMilestone;
use App\Notifications\Channels\BrandedMailChannel;
use App\Notifications\Channels\CustomerNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MilestoneSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly FreelancerContractMilestone $milestone)
    {
    }

    public function via(object $notifiable): array
    {
        return [BrandedMailChannel::class, CustomerNotificationChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(translate('delivery_submitted_for_review'))
            ->line(translate('the_freelancer_submitted_the_delivery'))
            ->action(translate('review_deliverable'), route('hire.contracts.show', $this->milestone->freelancer_contract_id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => translate('delivery_submitted_for_review'),
            'message' => translate('the_freelancer_submitted_the_delivery'),
            'link' => route('hire.contracts.show', $this->milestone->freelancer_contract_id),
            'reference_id' => $this->milestone->freelancer_contract_id,
        ];
    }
}
