<?php

namespace App\Services;

use App\Models\Seller;
use App\Models\VendorTier;
use App\Models\ProductTier;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class VendorTierService
{
    // Tier name constants
    const TIER_BASIC = 'Basic';
    const TIER_STANDARD = 'Standard';
    const TIER_PREMIUM = 'Premium';
    const TIER_ELITE = 'Elite';
    const TIER_ENTERPRISE = 'Enterprise';
    const TIER_ONE_TIME = 'One-Time';

    /**
     * Get authenticated seller information
     *
     * @return Seller|null
     */
    public function getSeller(): ?Seller
    {
        return auth('seller')->user();
    }

    /**
     * Get all of a seller's active (or trial) tier subscriptions.
     *
     * @param int|null $sellerId
     * @return Collection
     */
    public function getActiveTiers(?int $sellerId = null): Collection
    {
        $seller = $sellerId ? Seller::find($sellerId) : $this->getSeller();

        if (!$seller) {
            return new Collection(); // Return an empty collection
        }

        return VendorTier::with('tier')
            ->where('seller_id', $seller->id)
            ->whereIn('status', ['active', 'trial'])
            ->where(function ($query) {
                $today = Carbon::today()->toDateString();
                $query->where(fn ($active) => $active->where('status', 'active')->whereDate('end_date', '>=', $today))
                    ->orWhere(fn ($trial) => $trial->where('status', 'trial')->whereDate('trial_end_date', '>=', $today));
            })
            ->latest()
            ->get();
    }

    /**
     * Get the first active (or trial) VendorTier for a seller, with its tier relation loaded.
     */
    public function getActiveTierForSeller(int $sellerId): ?VendorTier
    {
        $today = Carbon::today()->toDateString();
        return VendorTier::with('tier')
            ->where('seller_id', $sellerId)
            ->whereIn('status', ['active', 'trial'])
            ->where(function ($query) use ($today) {
                $query->where(fn ($q) => $q->where('status', 'active')->whereDate('end_date', '>=', $today))
                      ->orWhere(fn ($q) => $q->where('status', 'trial')->whereDate('trial_end_date', '>=', $today));
            })
            ->latest()
            ->first();
    }

    /**
     * Whether this seller's current active/trial tier includes "featured vendor" placement
     * (shop badge + priority in the vendor list).
     *
     * @param int $sellerId
     * @return bool
     */
    public function isFeaturedVendor(int $sellerId): bool
    {
        return $this->getActiveTiers($sellerId)
            ->contains(fn ($vendorTier) => (bool) ($vendorTier->tier?->is_featured_vendor ?? false));
    }

    /**
     * How many of this seller's products should be automatically featured on the homepage,
     * based on their current active/trial tier(s). If a seller somehow has multiple active
     * subscriptions, the highest quota among them wins.
     *
     * @param int $sellerId
     * @return int
     */
    public function getFeaturedProductQuota(int $sellerId): int
    {
        return (int) $this->getActiveTiers($sellerId)
            ->map(fn ($vendorTier) => (int) ($vendorTier->tier?->featured_product_quota ?? 0))
            ->max();
    }

    /**
     * Get all active tier subscriptions that are not yet "full" (have not reached their product limit).
     * Assumes 'product_limit' is a column on your 'product_tiers' table.
     *
     * @param int|null $sellerId
     * @return Collection
     */
    public function getAvailableTiers(?int $sellerId = null): Collection
    {
        $activeTiers = $this->getActiveTiers($sellerId);

        // Batched once instead of one Product::count() query per tier subscription —
        // a vendor can hold several concurrent tiers, so this was a genuine N+1.
        $productCounts = Product::whereIn('vendor_tier_id', $activeTiers->pluck('id'))
            ->selectRaw('vendor_tier_id, count(*) as total')
            ->groupBy('vendor_tier_id')
            ->pluck('total', 'vendor_tier_id');

        return $activeTiers->filter(function ($vendorTier) use ($productCounts) {
            if (!$vendorTier->tier) {
                return false; // Skip if tier details (ProductTier) are missing
            }

            $productLimit = $this->getTierListingLimit($vendorTier->tier);
            $productCount = (int) ($productCounts->get($vendorTier->id) ?? 0);

            // Return true (keep in list) if the tier is not yet full
            return $productLimit === PHP_INT_MAX || $productCount < $productLimit;
        });
    }

    public function getTierListingLimit(?ProductTier $tier): int
    {
        if (!$tier) {
            return 1;
        }

        $listingText = strtolower((string) ($tier->listings_per_fee ?? '1'));
        if ($listingText === 'unlimited' || str_contains($listingText, 'unlimited')) {
            return PHP_INT_MAX;
        }

        preg_match('/\d+/', $listingText, $matches);
        return max(1, (int) ($matches[0] ?? 1));
    }

    public function getSellerProductCount(int $sellerId): int
    {
        return Product::where('user_id', $sellerId)
            ->where('added_by', 'seller')
            ->where('request_status', '!=', 3)
            ->count();
    }

    /**
     * Check if seller has any active tier plan
     *
     * @param int|null $sellerId
     * @return bool
     */
    public function hasActiveTierPlan(?int $sellerId = null): bool
    {
        return $this->getActiveTiers($sellerId)->isNotEmpty();
    }

    /**
     * Validate tier plan for product operations based on the *selected* vendor_tier_id.
     *
     * @param string $operation
     * @param array $productData
     * @param int|null $sellerId
     * @return array
     */
    public function validateTierPlanForOperation(string $operation, array $productData = [], ?int $sellerId = null): array
    {
          
        if (auth('admin')->check()) {
            return [
                'is_valid' => true,
                'errors' => [],
                'message' => 'Admin bypass: Validation skipped.'
            ];
        }

        $seller = $sellerId ? Seller::find($sellerId) : $this->getSeller();
        if (!$seller) {
            return $this->buildErrorResponse('tier_plan', trans('new-messages.no_active_tier_plan'));
        }

        // Get the specific tier subscription selected by the user
        $vendorTierId = $productData['vendor_tier_id'] ?? null;
        if (!$vendorTierId) {
            return $this->buildErrorResponse('vendor_tier_id', 'Please select an available tier plan for this product.');
        }

        $vendorTier = VendorTier::with('tier')
            ->where('id', $vendorTierId)
            ->where('seller_id', $seller->id)
            ->whereIn('status', ['active', 'trial'])
            ->where(function ($query) {
                $today = Carbon::today()->toDateString();
                $query->where(fn ($active) => $active->where('status', 'active')->whereDate('end_date', '>=', $today))
                    ->orWhere(fn ($trial) => $trial->where('status', 'trial')->whereDate('trial_end_date', '>=', $today));
            })
            ->first();

        if (!$vendorTier) {
            return $this->buildErrorResponse('vendor_tier_id', 'The selected tier plan is invalid or does not belong to you.');
        }

        // Check if this tier is full
        $tierDetails = $vendorTier->tier;
        if (!$tierDetails) {
            return $this->buildErrorResponse('tier_plan', trans('new-messages.tier_plan_details_not_found'));
        }

        // On "add" operations, check if the tier is full.
        // On "update", we only check if the tier ID *changed* to a new one that is full.
        $productLimit = $this->getTierListingLimit($tierDetails);
        $productCount = $vendorTier->products()->count();

        if ($operation === 'add_product') {
            if ($productCount >= $productLimit) {
                 return $this->buildErrorResponse('vendor_tier_id', 'This tier plan has already reached its product limit (' . $productLimit . ').');
            }
        } elseif ($operation === 'update_product') {
            // Get the product ID from the request
            $productId = $productData['id'] ?? null;
            // withoutGlobalScope: only vendor_tier_id is read below — Product's
            // "translate" global scope eager-loads translations+reviews on every
            // fetch otherwise, which is wasted work for one column.
            $currentVendorTierId = $productId
                ? Product::withoutGlobalScope('translate')->where('id', $productId)->value('vendor_tier_id')
                : null;

            // Check if the tier was changed *and* the new tier is full
            if ($productId && $currentVendorTierId != $vendorTierId && $productCount >= $productLimit) {
                 return $this->buildErrorResponse('vendor_tier_id', 'This new tier plan has already reached its product limit (' . $productLimit . ').');
            }
        }

        // Perform validation based on tier rules
        $validationErrors = $this->validateProductByTier($tierDetails, $productData);

        return [
            'is_valid' => empty($validationErrors),
            'errors' => $validationErrors,
            'message' => empty($validationErrors) ? 'Validation passed' : 'Validation failed',
        ];
    }

    /**
     * Helper to build a standardized error response array.
     */
    private function buildErrorResponse(string $errorCode, string $message): array
    {
        return [
            'is_valid' => false,
            'errors' => [
                ['error_code' => $errorCode, 'message' => $message]
            ],
            'message' => $message,
        ];
    }

    /**
     * Validate product data based on tier rules
     *
     * @param ProductTier $tier
     * @param array $productData
     * @return array
     */
    private function validateProductByTier(ProductTier $tier, array $productData): array
    {
        $errors = [];
        $tierName = $tier->name;

        $priceErrors = $this->validatePrice($tier, $productData);
        if (!empty($priceErrors)) {
            $errors = array_merge($errors, $priceErrors);
        }

        $descriptionErrors = $this->validateDescription($tierName, $productData);
        if (!empty($descriptionErrors)) {
            $errors = array_merge($errors, $descriptionErrors);
        }

        if ($tierName === self::TIER_ONE_TIME) {
            $categoryErrors = $this->validateCategory($productData);
            if (!empty($categoryErrors)) {
                $errors = array_merge($errors, $categoryErrors);
            }
        }

        return $errors;
    }

    /**
     * Validate price based on tier
     *
     * @param ProductTier $tier
     * @param array $productData
     * @return array
     */
    private function validatePrice(ProductTier $tier, array $productData): array
    {
        if (in_array($productData['product_type'] ?? null, ['event', 'broker'], true)) {
            return []; // Event/Broker listings have no sellable price — tier price thresholds don't apply.
        }

        $errors = [];
        $price = $productData['unit_price'] ?? $productData['price'] ?? null;

        if ($price === null) {
            return [['error_code' => 'unit_price', 'message' => trans('new-messages.price_is_required')]];
        }

        $price = (float) $price;
        $priceMin = $tier->price_threshold_min_usd;
        $priceMax = $tier->price_threshold_max_usd;

        if ($priceMin !== null && $priceMax !== null) {
            if ($price < $priceMin || $price > $priceMax) {
                $errors[] = [
                    'error_code' => 'unit_price',
                    'message' => trans('new-messages.price_must_be_between', [
                        'min' => '$' . number_format($priceMin, 2),
                        'max' => '$' . number_format($priceMax, 2),
                        'tier' => $tier->name
                    ])
                ];
            }
        }
        elseif ($priceMin !== null && $priceMax === null) {
            if ($price < $priceMin) {
                $errors[] = [
                    'error_code' => 'unit_price',
                    'message' => trans('new-messages.price_must_be_minimum', [
                        'min' => '$' . number_format($priceMin, 2),
                        'tier' => $tier->name
                    ])
                ];
            }
        }
        elseif ($priceMin === null && $priceMax !== null) {
            if ($price > $priceMax) {
                $errors[] = [
                    'error_code' => 'unit_price',
                    'message' => trans('new-messages.price_must_be_under', [
                        'max' => '$' . number_format($priceMax, 2),
                        'tier' => $tier->name
                    ])
                ];
            }
        }
        else {
            if ($price <= 0) {
                $errors[] = [
                    'error_code' => 'unit_price',
                    'message' => trans('new-messages.price_must_be_positive', [
                        'tier' => $tier->name
                    ])
                ];
            }
        }

        return $errors;
    }

    /**
     * Validate media (images/videos) based on tier
     *
     * @param string $tierName
     * @param array $productData
     * @return array
     */
    private function validateMedia(string $tierName, array $productData): array
    {
        $errors = [];

        // Count images from various sources
        $imageCount = 0;
        $videoCount = 0;

        // Count uploaded images
        if (isset($productData['images']) && is_array($productData['images'])) {
            $imageCount += count($productData['images']);
        }

        // Count existing images
        if (isset($productData['existing_images']) && is_array($productData['existing_images'])) {
            $imageCount += count($productData['existing_images']);
        }

        // Count color images
        if (isset($productData['colors_active']) && isset($productData['colors'])) {
            foreach ($productData['colors'] as $color) {
                $colorKey = 'color_image_' . str_replace('#', '', $color);
                if (isset($productData[$colorKey])) {
                    $imageCount++;
                }
            }
        }

        // Count videos (if digital product with videos)
        if (isset($productData['digital_files']) && is_array($productData['digital_files'])) {
            foreach ($productData['digital_files'] as $file) {
                if (isset($file) && is_object($file)) {
                    $extension = $file->getClientOriginalExtension();
                    if (in_array(strtolower($extension), ['mp4', 'avi', 'mov', 'wmv'])) {
                        $videoCount++;
                    }
                }
            }
        }

        $totalMedia = $imageCount + $videoCount;

        switch ($tierName) {
            case self::TIER_BASIC:
                if ($totalMedia > 1) {
                    $errors[] = ['error_code' => 'media', 'message' => trans('new-messages.basic_tier_media_limit')];
                }
                if ($videoCount > 0) {
                    $errors[] = ['error_code' => 'media', 'message' => trans('new-messages.basic_tier_no_videos')];
                }
                break;

            case self::TIER_STANDARD:
                if ($totalMedia > 5) {
                    $errors[] = ['error_code' => 'media', 'message' => trans('new-messages.standard_tier_media_limit')];
                }
                break;

            case self::TIER_PREMIUM:
            case self::TIER_ELITE:
            case self::TIER_ENTERPRISE:
            case self::TIER_ONE_TIME:
                // Unlimited media, but validate file types
                // This validation can be added if needed
                break;
        }

        return $errors;
    }

    /**
     * Validate description based on tier
     *
     * @param string $tierName
     * @param array $productData
     * @return array
     */
    private function validateDescription(string $tierName, array $productData): array
    {
        $errors = [];

        // Get description (could be array for multi-language or single string)
        $description = $productData['description'] ?? null;

        if ($description === null) {
            return [['error_code' => 'description', 'message' => trans('new-messages.description_is_required')]];
        }

        // If description is array (multi-language), check the first/default language
        if (is_array($description)) {
            $description = reset($description);
        }

        // Strip HTML tags and count characters
        $plainDescription = strip_tags($description);
        $descriptionLength = mb_strlen($plainDescription);

        switch ($tierName) {
            case self::TIER_BASIC:
                if ($descriptionLength > 500) {
                    $errors[] = ['error_code' => 'description', 'message' => trans('new-messages.basic_tier_description_limit', ['current' => $descriptionLength])];
                }
                break;

            // All other tiers allow unlimited description
            case self::TIER_STANDARD:
            case self::TIER_PREMIUM:
            case self::TIER_ELITE:
            case self::TIER_ENTERPRISE:
            case self::TIER_ONE_TIME:
                // No limit
                break;
        }

        return $errors;
    }

    /**
     * Validate category (for One-Time tier)
     *
     * @param array $productData
     * @return array
     */
    private function validateCategory(array $productData): array
    {
        $errors = [];
        $categoryId = $productData['category_id'] ?? null;

        if (!$categoryId) {
            return [['error_code' => 'category', 'message' => trans('new-messages.category_is_required')]];
        }

        // Get category name
        // You might need to inject CategoryRepository or use model directly
        $category = \App\Models\Category::find($categoryId);

        if ($category) {
            $allowedCategories = ['Templates', 'Logos', 'Digital Assets'];
            if (!in_array($category->name, $allowedCategories)) {
                $errors[] = ['error_code' => 'category', 'message' => trans('new-messages.one_time_tier_category_limit')];
            }
        }

        return $errors;
    }
}
