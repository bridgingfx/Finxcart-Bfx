<?php

namespace App\Notifications\Freelancer;

use App\Models\FreelancerContract;
use App\Notifications\Channels\CustomerNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ContractCancellationApprovedNotification extends Notification implements ShouldQueue
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
        return [
            'title' => translate('cancellation_approved'),
            'message' => translate('your_request_to_cancel_this_contract_was_approved') . '.',
            'link' => route('hire.contracts.show', $this->contract->id),
            'reference_id' => $this->contract->id,
        ];
    }
}
