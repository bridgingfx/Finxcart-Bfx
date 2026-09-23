<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerServicePackage extends Model
{
    protected $fillable = [
        'freelancer_service_id',
        'tier',
        'is_enabled',
        'title',
        'description',
        'price',
        'delivery_time_days',
        'revisions',
        'features',
    ];

    protected $casts = [
        'freelancer_service_id' => 'integer',
        'is_enabled' => 'boolean',
        'price' => 'decimal:2',
        'delivery_time_days' => 'integer',
        'revisions' => 'integer',
        'features' => 'array',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(FreelancerService::class, 'freelancer_service_id');
    }
}
