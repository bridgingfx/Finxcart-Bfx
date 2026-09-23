<?php

namespace App\Models;

use App\Enums\Freelancer\ServicePricingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FreelancerService extends Model
{
    protected $fillable = [
        'seller_id',
        'freelancer_category_id',
        'freelancer_specialization_id',
        'title',
        'description',
        'price',
        'pricing_type',
        'delivery_time_days',
        'offers_subscription',
        'offers_video_consultation',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'seller_id' => 'integer',
        'freelancer_category_id' => 'integer',
        'freelancer_specialization_id' => 'integer',
        'price' => 'decimal:2',
        'pricing_type' => ServicePricingType::class,
        'delivery_time_days' => 'integer',
        'offers_subscription' => 'boolean',
        'offers_video_consultation' => 'boolean',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FreelancerCategory::class, 'freelancer_category_id');
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(FreelancerSpecialization::class, 'freelancer_specialization_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(FreelancerServicePackage::class, 'freelancer_service_id');
    }

    public function enabledPackages(): HasMany
    {
        return $this->packages()->where('is_enabled', true)->orderByRaw("FIELD(tier, 'basic', 'standard', 'premium')");
    }

    public function images(): HasMany
    {
        return $this->hasMany(FreelancerServiceImage::class, 'freelancer_service_id')->orderBy('priority');
    }
}
