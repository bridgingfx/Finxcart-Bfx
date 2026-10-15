<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductTier;

class ProductTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Populates the product_tiers table with the 6 tiers defined in the proposal
     * and ensures column mappings match the provided schema.
     */
    public function run(): void
    {
        $tiers = [
            // Basic Tier (One-time, low value, Free Month)
            [
                'name' => 'Basic',
                'target_category' => 'One-time sales (low-value)',
                'ideal_product_types' => 'Basic freelance gigs, digital downloads',
                'monthly_fee_usd' => 49.00,
                'sales_commission_rate' => 0.0000,
                'is_commission_only' => 0,
                'price_threshold_min_usd' => 0.00,
                'price_threshold_max_usd' => 499.99,
                'is_recurring_focus' => 0,
                'is_free_first_month' => 1,
                'features' => json_encode([
                    'images_videos_allowed' => '1 image',
                    'search_ranking' => 'Standard',
                    'buyer_interaction' => 'Email',
                    'analytics' => 'Views only',
                    'billing_tools' => 'None',
                    'api_integrations' => 'No',
                ])
            ],
            // Standard Tier (One-time, mid value, Free Month)
            [
                'name' => 'Standard',
                'target_category' => 'One-time sales (mid-value)',
                'ideal_product_types' => 'Custom websites, consulting services',
                'monthly_fee_usd' => 99.00,
                'sales_commission_rate' => 0.0000,
                'is_commission_only' => 0,
                'price_threshold_min_usd' => 500.00,
                'price_threshold_max_usd' => 1499.00,
                'is_recurring_focus' => 0,
                'is_free_first_month' => 1,
                'features' => json_encode([
                    'images_videos_allowed' => 'Up to 5',
                    'search_ranking' => 'Priority (top 10)',
                    'buyer_interaction' => 'Chat',
                    'analytics' => 'Views, conversions',
                    'billing_tools' => 'None',
                    'api_integrations' => 'No',
                ])
            ],
            // Premium Tier (One-time, high value, No Free Month)
            [
                'name' => 'Premium',
                'target_category' => 'One-time sales (high-value)',
                'ideal_product_types' => 'Premium projects, e-commerce setups',
                'monthly_fee_usd' => 149.00,
                'sales_commission_rate' => 0.0000,
                'is_commission_only' => 0,
                'price_threshold_min_usd' => 1500.00,
                'price_threshold_max_usd' => null, // 1500+ (NULL indicates no max limit)
                'is_recurring_focus' => 0,
                'is_free_first_month' => 0,
                'features' => json_encode([
                    'images_videos_allowed' => 'Unlimited',
                    'search_ranking' => 'Top + Homepage',
                    'buyer_interaction' => 'Chat + Priority Support',
                    'analytics' => 'Sources, demographics',
                    'billing_tools' => 'None',
                    'api_integrations' => 'No',
                ])
            ],
            // Elite Tier (Recurring, 15% Commission)
            [
                'name' => 'Elite',
                'target_category' => 'Recurring/monthly rentals',
                'ideal_product_types' => 'CRM subscriptions, SaaS platforms',
                'monthly_fee_usd' => 149.00,
                'sales_commission_rate' => 0.1500, // 15%
                'is_commission_only' => 0,
                'price_threshold_min_usd' => null, // N/A for Recurring
                'price_threshold_max_usd' => null, // N/A for Recurring
                'is_recurring_focus' => 1,
                'is_free_first_month' => 0,
                'features' => json_encode([
                    'images_videos_allowed' => 'Unlimited',
                    'search_ranking' => 'Top + Homepage',
                    'buyer_interaction' => 'Chat + Priority Support',
                    'analytics' => 'Churn, lifetime value',
                    'billing_tools' => 'Auto-renewals + Invoicing',
                    'api_integrations' => 'Yes',
                ])
            ],
            // Enterprise Tier (Recurring, 10% Commission)
            [
                'name' => 'Enterprise',
                'target_category' => 'High-value recurring rentals',
                'ideal_product_types' => 'Advanced SaaS, enterprise subscriptions',
                'monthly_fee_usd' => 249.00,
                'sales_commission_rate' => 0.1000, // 10%
                'is_commission_only' => 0,
                'price_threshold_min_usd' => null, // N/A for Recurring
                'price_threshold_max_usd' => null, // N/A for Recurring
                'is_recurring_focus' => 1,
                'is_free_first_month' => 0,
                'features' => json_encode([
                    'images_videos_allowed' => 'Unlimited',
                    'search_ranking' => 'Top + Homepage',
                    'buyer_interaction' => 'Chat + Dedicated Manager',
                    'analytics' => 'Predictive insights',
                    'billing_tools' => 'Auto-renewals + Invoicing',
                    'api_integrations' => 'Enhanced',
                ])
            ],
            // One-Time Tier (Niche Products, 30% Commission Only)
            [
                'name' => 'One-Time',
                'target_category' => 'One-time niche products',
                'ideal_product_types' => 'Website templates, logos',
                'monthly_fee_usd' => null, // NULL for no monthly fee
                'sales_commission_rate' => 0.3000, // 30%
                'is_commission_only' => 1,
                'price_threshold_min_usd' => null, // Any price
                'price_threshold_max_usd' => null, // Any price
                'is_recurring_focus' => 0,
                'is_free_first_month' => 0,
                'features' => json_encode([
                    'images_videos_allowed' => 'Unlimited',
                    'search_ranking' => 'Top + Homepage',
                    'buyer_interaction' => 'Chat + Priority Support',
                    'analytics' => 'Sources, demographics',
                    'billing_tools' => 'None',
                    'api_integrations' => 'No',
                ])
            ],
        ];

        foreach ($tiers as $tier) {
            // We use name as the unique identifier for updateOrCreate
            ProductTier::updateOrCreate(['name' => $tier['name']], $tier);
        }
    }
}
