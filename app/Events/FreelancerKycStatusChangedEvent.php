<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FreelancerKycStatusChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $email,
        public readonly array $data,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('vendor.' . ($this->data['seller_id'] ?? 'unknown')),
        ];
    }
}
