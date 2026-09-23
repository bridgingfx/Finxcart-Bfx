<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Seller;
use App\Models\VendorTier;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckVendorPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-vendor-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily check for expired vendor plans and disable/manage product listings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('CheckVendorPlans: Starting daily vendor plan check...');

        // ================================================================
        // --- NEW STEP: Clean up expired plan statuses ---
        $this->info('Cleaning up expired tier statuses...');
        $expiredCount = 0;
        VendorTier::whereIn('status', ['active', 'trial'])
            ->get()
            ->each(function (VendorTier $tier) use (&$expiredCount) {
                $expiryDate = $tier->status === 'trial' && $tier->trial_end_date
                    ? $tier->trial_end_date
                    : $tier->end_date;

                if ($expiryDate && $expiryDate->isPast()) {
                    $tier->update(['status' => 'endende']);
                    $tier->products()->update(['status' => 0, 'featured_status' => 0]);
                    $expiredCount++;
                }
            });

        if ($expiredCount > 0) {
            Log::info("CheckVendorPlans: Marked $expiredCount tier(s) as 'expired'.");
        }
        // --- END OF NEW STEP ---
        // ================================================================


        // Get all sellers who have products, using your singular relationship name
        $sellers = Seller::whereHas('product')->get();

        foreach ($sellers as $seller) {
            // 1. Find ALL active subscriptions for this seller
            // This query is now even more accurate as it can rely on 'status'
            $activeSubscriptions = VendorTier::with('tier')
                ->where('seller_id', $seller->id)
                ->whereIn('status', ['active', 'trial']) // 'expired' plans are now automatically excluded
                ->where('end_date', '>=', Carbon::today()->toDateString()) // This check is still good
                ->get();

            // 2. Calculate their TOTAL max listings
            $totalMaxListings = 0;
            $hasUnlimitedPlan = false;

            if ($activeSubscriptions->isEmpty()) {
                // No active plans at all
                $totalMaxListings = 0;
            } else {
                foreach ($activeSubscriptions as $subscription) {
                    if (!$subscription->tier) continue;

                    $listings_per_fee = $subscription->tier->listings_per_fee;

                    if (strtolower($listings_per_fee) === 'unlimited') {
                        $hasUnlimitedPlan = true;
                        break;
                    }
                    preg_match('/\d+/', $listings_per_fee, $matches);
                    $totalMaxListings += (int)($matches[0] ?? 0);
                }
            }

            if ($hasUnlimitedPlan) {
                $totalMaxListings = PHP_INT_MAX;
            }

            // 3. Get their current ACTIVE product count
            $currentActiveProductCount = Product::where('user_id', $seller->id)
                ->where('added_by', 'seller')
                ->where('status', 1) // Only count active products
                ->count();

            // 4. Enforce the rules
            if ($totalMaxListings == 0 && $currentActiveProductCount > 0) {
                // CASE 1: No active plans. Disable ALL active products.
                Log::info("CheckVendorPlans: Seller {$seller->id} has no active plans. Disabling {$currentActiveProductCount} products.");
                Product::where('user_id', $seller->id)
                       ->where('added_by', 'seller')
                       ->where('status', 1)
                       ->update(['status' => 0]);

            } elseif ($totalMaxListings != PHP_INT_MAX && $currentActiveProductCount > $totalMaxListings) {
                // CASE 2: They are over-limit. Disable the oldest products.
                $productsToDisable = $currentActiveProductCount - $totalMaxListings;
                Log::info("CheckVendorPlans: Seller {$seller->id} is over limit (Limit: $totalMaxListings, Active: $currentActiveProductCount). Disabling $productsToDisable oldest products.");

                $productIdsToDisable = Product::where('user_id', $seller->id)
                    ->where('added_by', 'seller')
                    ->where('status', 1)
                    ->orderBy('created_at', 'asc') // Disable the OLDEST ones first
                    ->limit($productsToDisable)
                    ->pluck('id');

                Product::whereIn('id', $productIdsToDisable)->update(['status' => 0]);
            }

            // 5. Sync tier-based auto-featured products (Featured Vendor tiers only).
            // Only ever touches products this same sync previously auto-featured
            // (tracked via 'tier_featured'), so admin-curated featured picks are untouched.
            $featuredQuota = 0;
            foreach ($activeSubscriptions as $subscription) {
                if ($subscription->tier) {
                    $featuredQuota = max($featuredQuota, (int) ($subscription->tier->featured_product_quota ?? 0));
                }
            }

            $currentlyTierFeaturedIds = Product::where('user_id', $seller->id)
                ->where('added_by', 'seller')
                ->where('tier_featured', 1)
                ->pluck('id');

            if ($featuredQuota > 0) {
                $freshFeaturedIds = Product::where('user_id', $seller->id)
                    ->where('added_by', 'seller')
                    ->where('status', 1)
                    ->orderBy('created_at', 'desc')
                    ->limit($featuredQuota)
                    ->pluck('id');

                $idsToUnfeature = $currentlyTierFeaturedIds->diff($freshFeaturedIds);
                if ($idsToUnfeature->isNotEmpty()) {
                    Product::whereIn('id', $idsToUnfeature)->update(['featured' => 0, 'tier_featured' => 0]);
                }

                if ($freshFeaturedIds->isNotEmpty()) {
                    Product::whereIn('id', $freshFeaturedIds)->update(['featured' => 1, 'tier_featured' => 1]);
                    Log::info("CheckVendorPlans: Seller {$seller->id} has {$freshFeaturedIds->count()} tier-auto-featured product(s).");
                }
            } elseif ($currentlyTierFeaturedIds->isNotEmpty()) {
                Product::whereIn('id', $currentlyTierFeaturedIds)->update(['featured' => 0, 'tier_featured' => 0]);
                Log::info("CheckVendorPlans: Seller {$seller->id} lost featured-vendor quota. Un-featured {$currentlyTierFeaturedIds->count()} product(s).");
            }
        }

        Log::info('CheckVendorPlans: Daily vendor plan check complete.');
        $this->info('Vendor plans and product listings have been synchronized.');
        return 0;
    }
}
