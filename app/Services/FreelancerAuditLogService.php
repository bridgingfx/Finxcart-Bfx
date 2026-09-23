<?php

namespace App\Services;

use App\Models\FreelancerAuditLog;
use Illuminate\Database\Eloquent\Model;

class FreelancerAuditLogService
{
    /**
     * @param array<string, mixed>|null $before
     * @param array<string, mixed>|null $after
     */
    public function log(
        string $actorType,
        ?int $actorId,
        Model $subject,
        string $action,
        ?array $before = null,
        ?array $after = null,
        ?string $description = null,
    ): FreelancerAuditLog {
        return FreelancerAuditLog::create([
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'action' => $action,
            'before_state' => $before,
            'after_state' => $after,
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
