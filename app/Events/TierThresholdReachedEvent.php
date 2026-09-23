<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TierThresholdReachedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $sellerId,
        public readonly string $email,
        public readonly int $currentCount,
        public readonly int $tierLimit,
        public readonly string $tierName,
        public readonly string $upgradeUrl,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('vendor.' . $this->sellerId),
        ];
    }
}
