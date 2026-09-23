<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreelancerAuditLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'subject_type',
        'subject_id',
        'action',
        'before_state',
        'after_state',
        'description',
    ];

    protected $casts = [
        'actor_id' => 'integer',
        'subject_id' => 'integer',
        'before_state' => 'array',
        'after_state' => 'array',
        'created_at' => 'datetime',
    ];
}
