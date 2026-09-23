<?php

namespace App\Http\Middleware;

use App\Services\VendorTierService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorTier;
use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class CheckSellerPlan
{
    public function handle(Request $request, Closure $next)
    {
        $vendorTierService = app(VendorTierService::class);

        // 1. Check if the authenticated user is a Seller
        $sellerId = auth('seller')->id(); // This gets the Integer ID
        if (!$sellerId) {
            return $next($request);
        }

        $seller = auth('seller')->user();
        if ($seller && $seller->status !== 'approved') {
            $blockedRoute = Str::contains($request->path(), [
                'product/add',
                'products/add',
                'product/store',
                'products/store',
                'product/edit',
                'products/edit',
                'product/update',
                'products/update',
                'upload',
                'coupon/add',
                'coupon/update',
                'coupon/delete',
                'clearance-sale/status-update',
                'clearance-sale/update-config',
                'clearance-sale/add-clearance-product',
                'clearance-sale/clearance-product-status-update',
                'clearance-sale/clearance-delete',
                'clearance-sale/update-discount',
                'clearance-sale/clearance-products-delete',
            ]);

            if ($blockedRoute) {
                if ($request->ajax()) {
                    return response()->json([
                        'redirect' => true,
                        'redirectRoute' => route('vendor.products.list', ['type' => 'approved']),
                        'error' => translate('Your_account_is_pending_admin_approval._You_can_add_products_once_approved.'),
                    ], 403);
                }
                return redirect()->route('vendor.products.list', ['type' => 'approved'])
                    ->with('show_approval_pending_modal', true);
            }

            $verification = $seller->vendorVerification;
            $verificationSubmitted = $verification && in_array($verification->status, ['pending', 'rejected']);

            View::share('seller_not_approved', true);
            View::share('verification_submitted', $verificationSubmitted);
            return $next($request);
        }

        // 2. Find ALL valid subscriptions
        //
        // This middleware runs on essentially every vendor-panel request (the whole
        // 'auth.seller.plan' route group), so the tier/subscription/product-count
        // queries below previously ran fresh on every single page load and form
        // submit — up to 4 uncached DB round trips per request, sitewide for the
        // vendor panel. Cached per-seller for a short window; a purchase/product
        // change becoming visible up to a minute late is an acceptable tradeoff
        // for a limits-check, matching the TTL already used for the admin header
        // badge counts (CACHE_FOR_1_MINUTE).
        $today = Carbon::today()->toDateString();

        $activeSubscriptions = Cache::remember(
            "vendor_active_subscriptions_{$sellerId}",
            CACHE_FOR_1_MINUTE,
            fn () => VendorTier::with('tier')
                ->where('seller_id', $sellerId)
                ->whereIn('status', ['active', 'trial'])
                ->where(function($query) use ($today) {
                    $query->whereDate('end_date', '>=', $today)
                          ->orWhere(function($q) use ($today) {
                              $q->where('status', 'trial')
                                ->whereDate('trial_end_date', '>=', $today);
                          });
                })
                ->get()
        );

        // 3. Handle "No Valid Plan" Scenario
        if ($activeSubscriptions->isEmpty()) {
            // Approved vendors without a tier can browse, add, and edit products.
            // Only block toggling a product to active (status=1) — tier is required to publish.
            $isActivating = $request->routeIs('vendor.products.status-update') && $request->input('status') == 1;
            if ($isActivating) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => translate('Purchase_a_tier_plan_to_publish_products.'),
                        'reset_toggle' => true,
                    ], 403);
                }
                return redirect()->route('vendor.products.list', ['type' => 'approved'])
                    ->with('warning', translate('Purchase_a_tier_plan_to_publish_products.'));
            }

            View::share('no_active_tier', true);
            return $next($request);
        }

        // 4. Calculate Total Listings Allowed
        $totalMaxListings = 0;
        $hasUnlimitedPlan = false;
        $activePlanNames = [];

        foreach ($activeSubscriptions as $subscription) {
            $planFeatures = $subscription->tier;
            if (!$planFeatures) continue;

            $activePlanNames[] = $planFeatures->name;
            $limit_str = $planFeatures->listings_per_fee ?? '0';

            if (strtolower($limit_str) === 'unlimited') {
                $hasUnlimitedPlan = true;
                break; 
            }
            
            preg_match('/\d+/', $limit_str, $matches);
            $totalMaxListings += (int)($matches[0] ?? 0);
        }

        if ($hasUnlimitedPlan) {
            $totalMaxListings = PHP_INT_MAX;
        }

        // 5. Get Total Product Count
        $totalProductCount = Cache::remember(
            "vendor_product_count_{$sellerId}",
            CACHE_FOR_1_MINUTE,
            fn () => Product::where('user_id', $sellerId)
                ->where('added_by', 'seller')
                ->where('request_status', '!=', 3)
                ->count()
        );

        // 6. Get Available Tiers (Service)
        $availableTiers = [];
        try {
            // --- FIX IS HERE ---
            // Previously: $vendorTierService->getAvailableTiers(auth('seller')->user());
            // Fixed: Pass $sellerId (int)
            $availableTiers = Cache::remember(
                "vendor_available_tiers_{$sellerId}",
                CACHE_FOR_1_MINUTE,
                fn () => $vendorTierService->getAvailableTiers($sellerId)
            );

        } catch (\Exception $e) {
            // Handle service missing or errors silently to avoid crashing the page
            \Illuminate\Support\Facades\Log::error("Middleware Service Error: " . $e->getMessage());
        }

        // 7. Share data with views
        View::share('current_product_count', $totalProductCount);
        View::share('total_max_listings', $totalMaxListings);
        View::share('active_plan_names', $activePlanNames);
        View::share('availableTiers', $availableTiers);

        // 8. Enforce Activation Limit (has active tier but at product cap)
        $isActivating = $request->routeIs('vendor.products.status-update') && $request->input('status') == 1;

        if ($isActivating && $totalMaxListings != PHP_INT_MAX && $totalProductCount >= $totalMaxListings) {
            $errorMessage = translate('Limit_Reached:_You_have') . ' ' . $totalProductCount . ' ' . translate('products._Your_limit_is') . ' ' . $totalMaxListings . '.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'reset_toggle' => true,
                ], 403);
            }

            return redirect()->route('vendor.products.list', ['type' => 'approved'])->with('error', $errorMessage);
        }

        // 9. Check Suspension
        if ($activeSubscriptions->where('status', 'suspended')->isNotEmpty()) {
             return redirect()->route('vendor.tier.index')->with('error', translate('Your_plan_is_suspended.'));
        }

        return $next($request);
    }
}


