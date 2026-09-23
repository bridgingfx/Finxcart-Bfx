<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('admin_product_tiers')
            ->where('monthly_fee_usd', '>', 0)
            ->update(['sales_commission_rate' => 15]);

        DB::table('admin_product_tiers')->updateOrInsert(
            ['id' => 6],
            [
                'name' => 'Freelancer — Free',
                'target_category' => 'Freelancer sellers',
                'ideal_product_types' => 'Freelancer service listings and digital products after KYC approval.',
                'monthly_fee_usd' => 0,
                'sales_commission_rate' => 15,
                'is_commission_only' => false,
                'price_threshold_min_usd' => null,
                'price_threshold_max_usd' => null,
                'is_recurring_focus' => false,
                'is_free_first_month' => false,
                'images_videos_allowed' => 'Unlimited product images per listing',
                'search_ranking' => 'Standard',
                'buyer_interaction' => 'Email',
                'analytics' => 'Views only',
                'billing_tools' => 'None',
                'api_integrations' => false,
                'listings_per_fee' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('admin_product_tiers')
            ->where('id', 6)
            ->where('name', 'Freelancer — Free')
            ->delete();
    }
};
