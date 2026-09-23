<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateProductTiersSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'old_name'               => 'Basic',
                'name'                   => 'Tier 1 - Starter',
                'monthly_fee_usd'        => 50.00,
                'sales_commission_rate'  => 15.00,
                'listings_per_fee'       => 1,
                'ideal_product_types'    => 'Individual sellers, first-time vendors, part-time sellers.',
                'images_videos_allowed'  => 'Unlimited',
                'search_ranking'         => 'Standard',
                'buyer_interaction'      => 'Email',
                'analytics'              => 'Views & clicks',
                'billing_tools'          => null,
                'api_integrations'       => false,
                'is_free_first_month'    => 1,
                'price_threshold_min_usd'=> 0.00,
                'price_threshold_max_usd'=> null,
            ],
            [
                'old_name'               => 'Standard',
                'name'                   => 'Tier 2 - Growth',
                'monthly_fee_usd'        => 100.00,
                'sales_commission_rate'  => 15.00,
                'listings_per_fee'       => 3,
                'ideal_product_types'    => 'Active individual or corporate sellers with multiple products.',
                'images_videos_allowed'  => 'Unlimited',
                'search_ranking'         => 'Standard + conversion stats',
                'buyer_interaction'      => 'Email',
                'analytics'              => 'Views, clicks & conversion rate',
                'billing_tools'          => null,
                'api_integrations'       => false,
                'is_free_first_month'    => 1,
                'price_threshold_min_usd'=> 0.00,
                'price_threshold_max_usd'=> null,
            ],
            [
                'old_name'               => 'Premium',
                'name'                   => 'Tier 3 - Pro',
                'monthly_fee_usd'        => 150.00,
                'sales_commission_rate'  => 15.00,
                'listings_per_fee'       => 6,
                'ideal_product_types'    => 'Corporate accounts, agencies, and high-volume vendors.',
                'images_videos_allowed'  => 'Unlimited',
                'search_ranking'         => 'Full analytics + featured placement eligibility',
                'buyer_interaction'      => 'Email + priority response tools',
                'analytics'              => 'Full analytics suite',
                'billing_tools'          => null,
                'api_integrations'       => false,
                'is_free_first_month'    => 1,
                'price_threshold_min_usd'=> 0.00,
                'price_threshold_max_usd'=> null,
            ],
        ];

        foreach ($tiers as $tier) {
            $oldName = $tier['old_name'];
            unset($tier['old_name']);

            $updated = DB::table('admin_product_tiers')
                ->where('name', $oldName)
                ->update($tier);

            if ($updated === 0) {
                // Insert if not found by old name (idempotent)
                $exists = DB::table('admin_product_tiers')->where('name', $tier['name'])->exists();
                if (!$exists) {
                    DB::table('admin_product_tiers')->insert(array_merge($tier, [
                        'is_commission_only' => 0,
                        'is_recurring_focus' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                } else {
                    DB::table('admin_product_tiers')->where('name', $tier['name'])->update($tier);
                }
            }
        }

        $this->command->info('✅ Product tiers updated: Tier 1 Starter / Tier 2 Growth / Tier 3 Pro');
    }
}
