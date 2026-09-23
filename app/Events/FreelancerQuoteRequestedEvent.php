<?php

namespace App\Events;

use App\Models\FreelancerQuoteRequest;
use Illuminate\Foundation\Events\Dispatchable;

class FreelancerQuoteRequestedEvent
{
    use Dispatchable;

    public function __construct(public FreelancerQuoteRequest $quote)
    {
    }
}
