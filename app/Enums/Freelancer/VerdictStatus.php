<?php

namespace App\Enums\Freelancer;

enum VerdictStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
