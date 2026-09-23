<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerServiceImage extends Model
{
    use StorageTrait;

    protected $fillable = [
        'freelancer_service_id',
        'image',
        'image_storage_type',
        'priority',
    ];

    protected $casts = [
        'freelancer_service_id' => 'integer',
        'priority' => 'integer',
    ];

    protected $appends = ['image_full_url'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(FreelancerService::class, 'freelancer_service_id');
    }

    public function getImageFullUrlAttribute(): array
    {
        return $this->storageLink('freelancer-services', $this->image, $this->image_storage_type ?? 'public');
    }
}
