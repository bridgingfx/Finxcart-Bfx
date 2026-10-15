<?php

namespace App\Notifications\Freelancer;

use App\Models\FreelancerContract;
use App\Notifications\Channels\BrandedMailChannel;
use App\Notifications\Channels\CustomerNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HireConfirmedNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject(translate('payment_successful_freelancer_hired'))
            ->line(translate('your_payment_was_successful_and_the_freelancer_has_been_hired') . '.')
            ->line(translate('total_amount') . ': ' . currencyConverter($this->contract->total_amount))
            ->action(translate('view_contract'), route('hire.contracts.show', $this->contract->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => translate('payment_successful_freelancer_hired'),
            'message' => translate('your_payment_was_successful_and_the_freelancer_has_been_hired') . '.',
            'link' => route('hire.contracts.show', $this->contract->id),
            'reference_id' => $this->contract->id,
        ];
    }
}
