<?php

namespace App\Notifications\Freelancer;

use App\Models\FreelancerContract;
use App\Notifications\Channels\CustomerNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ContractCancellationDeniedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly FreelancerContract $contract)
    {
    }

    public function via(object $notifiable): array
    {
        return [CustomerNotificationChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        $message = translate('your_request_to_cancel_this_contract_was_denied');

        if ($this->contract->cancellation_admin_note) {
            $message .= ': ' . $this->contract->cancellation_admin_note;
        }

        return [
            'title' => translate('cancellation_denied'),
            'message' => $message,
            'link' => route('hire.contracts.show', $this->contract->id),
            'reference_id' => $this->contract->id,
        ];
    }
}
