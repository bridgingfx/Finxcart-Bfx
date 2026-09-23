<?php

namespace App\Notifications\Freelancer;

use App\Models\FreelancerContractMessage;
use App\Notifications\Channels\BrandedMailChannel;
use App\Notifications\Channels\CustomerNotificationChannel;
use App\Notifications\Channels\VendorNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContractMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly FreelancerContractMessage $message,
        private readonly string $recipientType,
    ) {
    }

    public function via(object $notifiable): array
    {
        $channel = $this->recipientType === 'customer' ? CustomerNotificationChannel::class : VendorNotificationChannel::class;

        return [BrandedMailChannel::class, $channel];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $contract = $this->message->contract;
        $senderName = $this->message->sender_type === 'seller'
            ? ($contract->freelancer->shop?->name ?: trim($contract->freelancer->f_name . ' ' . $contract->freelancer->l_name))
            : trim($contract->customer->f_name . ' ' . $contract->customer->l_name);

        $route = $this->recipientType === 'customer'
            ? route('hire.contracts.show', $contract->id)
            : route('freelancer.contracts.show', $contract->id);

        return (new MailMessage)
            ->subject(translate('new_message_on_your_contract'))
            ->line(translate('you_have_a_new_message_from') . ' ' . $senderName . '.')
            ->line($this->message->body ?: translate('sent_an_attachment'))
            ->action(translate('view_conversation'), $route);
    }

    public function toArray(object $notifiable): array
    {
        $route = $this->recipientType === 'customer'
            ? route('hire.contracts.show', $this->message->freelancer_contract_id)
            : route('freelancer.contracts.show', $this->message->freelancer_contract_id);

        return [
            'title' => translate('new_message_on_your_contract'),
            'message' => translate('you_have_a_new_message_on_your_contract'),
            'link' => $route,
            'reference_id' => $this->message->freelancer_contract_id,
        ];
    }
}
