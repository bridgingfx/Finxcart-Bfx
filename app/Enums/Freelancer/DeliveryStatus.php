<?php

namespace App\Enums\Freelancer;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case SubmittedForReview = 'submitted_for_review';
    case Delivered = 'delivered';

    /**
     * Any forward state is allowed (skipping stages is permitted), never backward.
     * Reverting to InProgress after a rejected verdict is done explicitly by
     * FreelancerVerdictService, not through this transition table.
     */
    public function allowedNextStates(): array
    {
        $stages = self::cases();
        $currentIndex = array_search($this, $stages, true);

        return array_slice($stages, $currentIndex + 1);
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedNextStates(), true);
    }
}
