<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerReview extends Model
{
    protected $fillable = [
        'customer_id',
        'seller_id',
        'reviewable_type',
        'reviewable_id',
        'rating',
        'body',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'seller_id' => 'integer',
        'reviewable_id' => 'integer',
        'rating' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function scopeForService($query, int $serviceId)
    {
        return $query->where('reviewable_type', 'service')->where('reviewable_id', $serviceId);
    }

    public function scopeForPortfolio($query, int $portfolioItemId)
    {
        return $query->where('reviewable_type', 'portfolio')->where('reviewable_id', $portfolioItemId);
    }
}
