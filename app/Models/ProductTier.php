<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTier extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Corresponds to the 'product_tiers' table created by the migration.
     */
    protected $table = 'admin_product_tiers';

    /**
     * The attributes that are mass assignable.
     * These align directly with the columns created in the migration file.
     */
    protected $fillable = [
        'name',
        'target_category',
        'ideal_product_types',
        'monthly_fee_usd',
        'sales_commission_rate',
        'is_commission_only',
        'price_threshold_min_usd',
        'price_threshold_max_usd',
        'is_recurring_focus',
        'is_free_first_month',
        // Add the new columns:
        'images_videos_allowed',
        'search_ranking',
        'buyer_interaction',
        'analytics',
        'billing_tools',
        'api_integrations',
        'listings_per_fee',
        'is_active',
        'featured_product_quota',
        'is_featured_vendor',
    ];

    protected $casts = [
        'is_commission_only' => 'boolean',
        'is_recurring_focus' => 'boolean',
        'is_free_first_month' => 'boolean',
        'is_active' => 'boolean',
        'api_integrations' => 'boolean', // Cast the Yes/No field to boolean
        'featured_product_quota' => 'integer',
        'is_featured_vendor' => 'boolean',
        // 'features' => 'json', // Remove this line
    ];
    // --- Relationships ---

    /**
     * Define the relationship to the Products model.
     * A Tier can be selected by multiple Products.
     */
    public function products()
    {
        // Assuming you will create a 'Product' model and link it via 'product_tier_id'
        return $this->hasMany(Product::class);
    }
}
