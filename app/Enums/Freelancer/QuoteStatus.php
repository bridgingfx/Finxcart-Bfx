<?php

namespace App\Enums\Freelancer;

enum QuoteStatus: string
{
    case Pending = 'pending';
    case Quoted = 'quoted';
    case Accepted = 'accepted';
    case Declined = 'declined';

    /**
     * @return QuoteStatus[]
     */
    public function allowedNextStates(): array
    {
        return match ($this) {
            self::Pending => [self::Quoted, self::Declined],
            self::Quoted => [self::Accepted, self::Declined],
            self::Accepted, self::Declined => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedNextStates(), true);
    }
}
