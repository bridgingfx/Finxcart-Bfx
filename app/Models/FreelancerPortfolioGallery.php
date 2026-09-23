<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerPortfolioGallery extends Model
{
    use StorageTrait;

    protected $fillable = [
        'freelancer_portfolio_item_id',
        'image',
        'image_storage_type',
        'url',
        'priority',
    ];

    protected $casts = [
        'freelancer_portfolio_item_id' => 'integer',
        'priority' => 'integer',
    ];

    protected $appends = ['image_full_url'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(FreelancerPortfolioItem::class, 'freelancer_portfolio_item_id');
    }

    public function getImageFullUrlAttribute(): array
    {
        return $this->storageLink('freelancer-portfolio', $this->image, $this->image_storage_type ?? 'public');
    }
}
