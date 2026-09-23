<?php

namespace App\Notifications\Freelancer;

use App\Models\FreelancerContract;
use App\Models\Seller;
use App\Notifications\Channels\BrandedMailChannel;
use App\Notifications\Channels\CustomerNotificationChannel;
use App\Notifications\Channels\VendorNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly FreelancerContract $contract)
    {
    }

    public function via(object $notifiable): array
    {
        $channel = $notifiable instanceof Seller ? VendorNotificationChannel::class : CustomerNotificationChannel::class;

        return [BrandedMailChannel::class, $channel];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(translate('contract_completed'))
            ->line(translate('all_milestones_for_this_contract_have_been_approved') . '.')
            ->line(translate('scope') . ': ' . $this->contract->scope);
    }

    public function toArray(object $notifiable): array
    {
        $route = $notifiable instanceof Seller
            ? route('freelancer.contracts.show', $this->contract->id)
            : route('hire.contracts.show', $this->contract->id);

        return [
            'title' => translate('contract_completed'),
            'message' => translate('all_milestones_for_this_contract_have_been_approved') . '.',
            'link' => $route,
            'reference_id' => $this->contract->id,
        ];
    }
}
