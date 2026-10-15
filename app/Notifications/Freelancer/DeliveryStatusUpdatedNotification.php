<?php

namespace App\Notifications\Freelancer;

use App\Models\FreelancerContract;
use App\Notifications\Channels\BrandedMailChannel;
use App\Notifications\Channels\CustomerNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly FreelancerContract $contract)
    {
    }

    public function via(object $notifiable): array
    {
        return [BrandedMailChannel::class, CustomerNotificationChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = ucwords(str_replace('_', ' ', $this->contract->delivery_status->value));

        return (new MailMessage)
            ->subject(translate('delivery_status_updated'))
            ->line(translate('the_freelancer_updated_the_delivery_status_to') . ' ' . $statusLabel . '.')
            ->action(translate('view_contract'), route('hire.contracts.show', $this->contract->id));
    }

    public function toArray(object $notifiable): array
    {
        $statusLabel = ucwords(str_replace('_', ' ', $this->contract->delivery_status->value));

        return [
            'title' => translate('delivery_status_updated'),
            'message' => translate('the_freelancer_updated_the_delivery_status_to') . ' ' . $statusLabel . '.',
            'link' => route('hire.contracts.show', $this->contract->id),
            'reference_id' => $this->contract->id,
        ];
    }
}
