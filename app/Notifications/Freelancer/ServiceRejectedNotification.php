<?php

namespace App\Notifications\Freelancer;

use App\Models\FreelancerContract;
use App\Notifications\Channels\VendorNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ServiceRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly FreelancerContract $contract,
        private readonly string $reason,
    ) {
    }

    /**
     * Add 'mail' here (with a toMail() method) to extend delivery to email later.
     */
    public function via(object $notifiable): array
    {
        return [VendorNotificationChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => translate('service_rejected'),
            'message' => translate('the_client_requested_revisions') . ': ' . $this->reason,
            'link' => route('freelancer.contracts.show', $this->contract->id),
            'reference_id' => $this->contract->id,
        ];
    }
}
