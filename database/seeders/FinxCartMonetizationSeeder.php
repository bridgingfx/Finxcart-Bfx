<?php

namespace Database\Seeders;

use App\Models\CreditPackage;
use App\Models\ProductTier;
use App\Models\PromotionActivity;
use Illuminate\Database\Seeder;

/**
 * Seeds the FinXCart monetization catalog (2026-10-10 plan):
 *   - 4 vendor subscription tiers (Starter $99 / Professional $199 /
 *     Business $399 / Enterprise $999, with monthly promo credits
 *     0 / 20 / 75 / 250)
 *   - 7 promotional activities vendors can spend credits on
 *   - 4 purchasable credit packages
 *
 * Idempotent: safe to re-run. Matches on unique keys (name / slug) and
 * updates the values, so re-running never duplicates rows.
 *
 * All prices, credit costs and feature summaries come from the approved
 * monetization document — nothing here is invented.
 */
class FinxCartMonetizationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTiers();
        $this->seedActivities();
        $this->seedPackages();
    }

    /**
     * Vendor subscription tiers -> admin_product_tiers.
     */
    protected function seedTiers(): void
    {
        $tiers = [
            [
                'name' => 'Starter',
                'monthly_fee_usd' => 99.00,
                'monthly_credits' => 0,
                'ideal_product_types' => 'Company profile, 5 product listings, direct inquiry form, website links, basic analytics and vendor dashboard.',
            ],
            [
                'name' => 'Professional',
                'monthly_fee_usd' => 199.00,
                'monthly_credits' => 20,
                'ideal_product_types' => 'Recommended. 20 listings, enhanced storefront, inquiry management, detailed analytics and priority category visibility.',
            ],
            [
                'name' => 'Business',
                'monthly_fee_usd' => 399.00,
                'monthly_credits' => 75,
                'ideal_product_types' => '50 listings, multiple team members, promotional credits, advanced analytics and featured placement opportunities.',
            ],
            [
                'name' => 'Enterprise',
                'monthly_fee_usd' => 999.00,
                'monthly_credits' => 250,
                'ideal_product_types' => 'Multi-brand storefronts, custom integrations, campaign management, account support and tailored promotion packages. Custom annual contract available.',
            ],
        ];

        foreach ($tiers as $tier) {
            ProductTier::updateOrCreate(
                ['name' => $tier['name']],
                array_merge($tier, [
                    'target_category' => 'Vendor subscription',
                    'is_active' => true,
                ])
            );
        }
    }

    /**
     * Promotional activity catalog -> promotion_activities.
     */
    protected function seedActivities(): void
    {
        $activities = [
            [
                'name' => 'Boost a product in its category',
                'slug' => 'boost-product-category',
                'description' => 'Push one product higher in its category listing.',
                'credit_cost' => 10,
                'duration_days' => 3,
                'duration_label' => '3 days',
            ],
            [
                'name' => 'Featured product placement',
                'slug' => 'featured-product-placement',
                'description' => 'Show one product in the featured placements.',
                'credit_cost' => 25,
                'duration_days' => 7,
                'duration_label' => '7 days',
            ],
            [
                'name' => 'Featured vendor placement',
                'slug' => 'featured-vendor-placement',
                'description' => 'Show the vendor storefront in the featured vendors area.',
                'credit_cost' => 50,
                'duration_days' => 7,
                'duration_label' => '7 days',
            ],
            [
                'name' => 'Homepage promotional slot',
                'slug' => 'homepage-promotional-slot',
                'description' => 'A promotional slot on the FinXCart homepage, subject to availability.',
                'credit_cost' => 100,
                'duration_days' => 7,
                'duration_label' => '7 days',
            ],
            [
                'name' => 'Sponsored newsletter inclusion',
                'slug' => 'sponsored-newsletter-inclusion',
                'description' => 'Include the vendor in one newsletter edition, clearly labeled as sponsored.',
                'credit_cost' => 75,
                'duration_days' => null,
                'duration_label' => '1 edition',
            ],
            [
                'name' => 'Social media promotional post',
                'slug' => 'social-media-promotional-post',
                'description' => 'One promotional post on FinXCart social channels, clearly labeled as sponsored.',
                'credit_cost' => 100,
                'duration_days' => null,
                'duration_label' => '1 post',
            ],
            [
                'name' => 'Priority category placement',
                'slug' => 'priority-category-placement',
                'description' => 'Priority placement inside a category, subject to availability.',
                'credit_cost' => 150,
                'duration_days' => 30,
                'duration_label' => '30 days',
            ],
        ];

        foreach ($activities as $activity) {
            PromotionActivity::updateOrCreate(
                ['slug' => $activity['slug']],
                array_merge($activity, ['is_active' => true])
            );
        }
    }

    /**
     * Purchasable credit bundles -> credit_packages.
     */
    protected function seedPackages(): void
    {
        $packages = [
            [
                'name' => '10 Credits',
                'slug' => 'credits-10',
                'credits' => 10,
                'price_usd' => 20.00,
                'description' => 'Short-term listing boost',
                'sort_order' => 1,
            ],
            [
                'name' => '30 Credits',
                'slug' => 'credits-30',
                'credits' => 30,
                'price_usd' => 50.00,
                'description' => 'Category promotion',
                'sort_order' => 2,
            ],
            [
                'name' => '75 Credits',
                'slug' => 'credits-75',
                'credits' => 75,
                'price_usd' => 100.00,
                'description' => 'Multiple promotional placements',
                'sort_order' => 3,
            ],
            [
                'name' => '200 Credits',
                'slug' => 'credits-200',
                'credits' => 200,
                'price_usd' => 250.00,
                'description' => 'Larger campaign allocation',
                'sort_order' => 4,
            ],
        ];

        foreach ($packages as $package) {
            CreditPackage::updateOrCreate(
                ['slug' => $package['slug']],
                array_merge($package, ['is_active' => true])
            );
        }
    }
}
