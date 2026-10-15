<?php

namespace App\Notifications\Freelancer;

use App\Models\FreelancerContract;
use App\Notifications\Channels\BrandedMailChannel;
use App\Notifications\Channels\VendorNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly FreelancerContract $contract)
    {
    }

    public function via(object $notifiable): array
    {
        return [BrandedMailChannel::class, VendorNotificationChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(translate('you_have_a_new_hire'))
            ->line(translate('a_client_has_hired_you_for_a_service') . '.')
            ->line(translate('scope') . ': ' . $this->contract->scope)
            ->line(translate('total_amount') . ': ' . currencyConverter($this->contract->total_amount))
            ->action(translate('view_contract'), route('freelancer.contracts.show', $this->contract->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => translate('you_have_a_new_hire'),
            'message' => translate('a_client_has_hired_you_for_a_service') . '.',
            'link' => route('freelancer.contracts.show', $this->contract->id),
            'reference_id' => $this->contract->id,
        ];
    }
}
