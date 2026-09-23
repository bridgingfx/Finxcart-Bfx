<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FreelancerPortfolioItem extends Model
{
    use StorageTrait;

    protected $fillable = [
        'seller_id',
        'title',
        'description',
        'tags',
        'image',
        'image_storage_type',
        'project_url',
        'completed_at',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'seller_id' => 'integer',
        'tags' => 'array',
        'completed_at' => 'date',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_full_url'];

    protected static function booted(): void
    {
        // A freelancer can only have one "active" (used) portfolio item at a
        // time — activating one silently deactivates the seller's other items
        // rather than requiring every caller to remember to do it.
        static::saved(function (FreelancerPortfolioItem $item) {
            if (!$item->is_active) {
                return;
            }

            static::where('seller_id', $item->seller_id)
                ->where('id', '!=', $item->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        });
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(FreelancerPortfolioGallery::class, 'freelancer_portfolio_item_id')->orderBy('priority');
    }

    public function getImageFullUrlAttribute(): array
    {
        return $this->storageLink('freelancer-portfolio', $this->image, $this->image_storage_type ?? 'public');
    }
}
