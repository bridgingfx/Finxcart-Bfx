<?php

namespace App\Events;

use App\Models\FreelancerQuoteRequest;
use Illuminate\Foundation\Events\Dispatchable;

class FreelancerQuoteDeclinedEvent
{
    use Dispatchable;

    public function __construct(public FreelancerQuoteRequest $quote)
    {
    }
}
