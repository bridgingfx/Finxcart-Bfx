<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SplitTransactionCompleteEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $sellerId,
        public readonly float $grossAmount,
        public readonly float $companyShareAmount,
        public readonly float $serviceChargeAmount,
        public readonly float $netVendorPayout,
        public readonly string $currency,
        public readonly int $ledgerId,
        public readonly string $referenceType = 'order',
        public readonly ?int $referenceId = null,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('vendor.' . $this->sellerId),
        ];
    }
}
