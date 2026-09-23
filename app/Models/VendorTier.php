<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorTier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seller_id',
        'product_tier_id',
        'monthly_fee_usd',
        'sales_commission_rate',
        'trial_commission_rate', // <-- ADDED
        'start_date',
        'trial_end_date',
        'end_date',
        'status',
        'last_payment_method',
        'is_auto_renew',
        'renewal_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'trial_end_date' => 'datetime',
        'end_date' => 'datetime',
        'renewal_date' => 'datetime',
        'is_auto_renew' => 'boolean',
        'monthly_fee_usd' => 'decimal:2',
        'sales_commission_rate' => 'decimal:2',
        'trial_commission_rate' => 'decimal:2', // <-- ADDED
    ];

    /**
     * Get the base tier details (from product_tiers table).
     */
    public function tier(): BelongsTo
    {
        return $this->belongsTo(ProductTier::class, 'product_tier_id');
    }

    /**
     * Get the seller (vendor).
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    /**
     * Get all products associated with this specific tier subscription.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'vendor_tier_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(VendorTierPayment::class, 'vendor_tier_id');
    }
}
