<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FreelancerCategory;
use App\Models\FreelancerContract;
use App\Models\FreelancerContractReview;
use App\Models\FreelancerPortfolioItem;
use App\Models\FreelancerReview;
use App\Models\FreelancerService;
use App\Models\FreelancerSpecialization;
use App\Models\Seller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class HireFreelancerController extends Controller
{
    public function index(Request $request): View
    {
        if (!Schema::hasTable('freelancer_categories') || !Schema::hasTable('freelancer_specializations')) {
            return view(VIEW_FILE_NAMES['hire_freelancer'], [
                'categories' => collect(),
                'displayCategories' => collect(),
                'selectedCategory' => null,
                'selectedSpecialization' => null,
                'services' => collect(),
                'sort' => 'recent',
            ]);
        }

        $categories = $this->getMenuCategories();
        $selectedCategory = null;
        $selectedSpecialization = null;

        if ($request->filled('specialization')) {
            $selectedSpecialization = FreelancerSpecialization::active()
                ->where('slug', $request->string('specialization')->toString())
                ->whereHas('category', fn ($query) => $this->activeRootCategoryQuery($query))
                ->with('category')
                ->first();

            $selectedCategory = $selectedSpecialization?->category;
        }

        if (!$selectedCategory && $request->filled('category')) {
            $selectedCategory = FreelancerCategory::active()
                ->where('slug', $request->string('category')->toString())
                ->whereNull('parent_id')
                ->where('position', 0)
                ->first();
        }

        $displayCategories = $this->getDisplayCategories(
            categories: $categories,
            selectedCategory: $selectedCategory,
            selectedSpecialization: $selectedSpecialization
        );

        $sort = $request->get('sort', 'recent');
        $minPrice = $request->filled('min_price') ? (float) $request->get('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->get('max_price') : null;
        $maxDeliveryDays = $request->filled('max_delivery_days') ? (int) $request->get('max_delivery_days') : null;

        $services = ($selectedCategory || $selectedSpecialization)
            ? $this->getServicesForListing($selectedCategory, $selectedSpecialization, $sort, $minPrice, $maxPrice, $maxDeliveryDays)
            : collect();

        return view(VIEW_FILE_NAMES['hire_freelancer'], [
            'categories' => $categories,
            'displayCategories' => $displayCategories,
            'selectedCategory' => $selectedCategory,
            'selectedSpecialization' => $selectedSpecialization,
            'services' => $services,
            'sort' => $sort,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'maxDeliveryDays' => $maxDeliveryDays,
        ]);
    }

    public function show(int $sellerId, Request $request): View
    {
        $seller = Seller::where('id', $sellerId)
            ->where('status', 'approved')
            ->where('account_status', 'active')
            ->where('seller_type', 'freelancer')
            ->with('shop')
            ->firstOrFail();

        $services = FreelancerService::where('seller_id', $sellerId)
            ->where('is_active', true)
            ->with(['category', 'specialization', 'enabledPackages', 'images'])
            ->orderBy('freelancer_category_id')
            ->get()
            ->groupBy(fn (FreelancerService $service) => $service->category?->name ?? translate('other'));

        $skills = $services->flatten()
            ->pluck('specialization.defaultname')
            ->filter()
            ->unique()
            ->values();

        $portfolioItems = FreelancerPortfolioItem::where('seller_id', $sellerId)
            ->where('is_active', true)
            ->with('galleryItems')
            ->orderBy('priority')
            ->get();

        $portfolioReviews = FreelancerReview::where('reviewable_type', 'portfolio')
            ->whereIn('reviewable_id', $portfolioItems->pluck('id'))
            ->with('customer')
            ->latest()
            ->get()
            ->groupBy('reviewable_id');

        $customerReviewedPortfolioIds = auth('customer')->check()
            ? FreelancerReview::where('reviewable_type', 'portfolio')
                ->where('customer_id', auth('customer')->id())
                ->whereIn('reviewable_id', $portfolioItems->pluck('id'))
                ->pluck('reviewable_id')
            : collect();

        $displayName = $seller->shop?->name ?: trim($seller->f_name . ' ' . $seller->l_name);
        $imageFullUrl = $seller->shop?->image_full_url ?? $seller->image_full_url;

        $reviewSort = $request->get('sort', 'recent');
        $reviewsQuery = FreelancerContractReview::where('reviewee_type', 'seller')
            ->where('reviewee_id', $sellerId)
            ->where('reviewer_type', 'customer')
            ->whereNotNull('body')
            ->with(['contract.customer', 'contract.service.images']);

        $reviews = $reviewSort === 'highest'
            ? $reviewsQuery->orderByDesc('rating')->orderByDesc('id')->paginate(5)->withQueryString()
            : $reviewsQuery->latest()->paginate(5)->withQueryString();

        $activeOrdersCount = FreelancerContract::where('seller_id', $sellerId)
            ->where('status', 'active')
            ->count();

        ['ratingAvg' => $ratingAvg, 'ratingCount' => $ratingCount, 'jobsCompleted' => $jobsCompleted, 'level' => $freelancerLevel] = $this->computeFreelancerLevel($seller);

        return view(VIEW_FILE_NAMES['hire_freelancer_show'], compact(
            'seller',
            'services',
            'skills',
            'portfolioItems',
            'portfolioReviews',
            'customerReviewedPortfolioIds',
            'displayName',
            'imageFullUrl',
            'reviews',
            'reviewSort',
            'activeOrdersCount',
            'ratingAvg',
            'ratingCount',
            'jobsCompleted',
            'freelancerLevel'
        ));
    }

    public function showService(int $serviceId, Request $request): View
    {
        $service = FreelancerService::where('id', $serviceId)
            ->where('is_active', true)
            ->with(['category', 'specialization', 'images', 'packages' => function ($q) {
                $q->orderByRaw("FIELD(tier, 'basic', 'standard', 'premium')");
            }])
            ->whereHas('seller', fn ($q) => $q->where('status', 'approved')->where('account_status', 'active')->where('seller_type', 'freelancer'))
            ->firstOrFail();

        $seller = Seller::where('id', $service->seller_id)->with('shop')->firstOrFail();
        $displayName = $seller->shop?->name ?: trim($seller->f_name . ' ' . $seller->l_name);
        $imageFullUrl = $seller->shop?->image_full_url ?? $seller->image_full_url;

        $activeOrdersCount = FreelancerContract::where('seller_id', $seller->id)
            ->where('status', 'active')
            ->count();

        ['ratingAvg' => $sellerRatingAvg, 'ratingCount' => $sellerRatingCount, 'jobsCompleted' => $jobsCompleted, 'level' => $freelancerLevel] = $this->computeFreelancerLevel($seller);

        $contractReviews = FreelancerContractReview::where('reviewee_type', 'seller')
            ->where('reviewer_type', 'customer')
            ->whereNotNull('body')
            ->whereHas('contract', fn ($q) => $q->where('freelancer_service_id', $service->id))
            ->with('contract.customer')
            ->get()
            ->map(fn (FreelancerContractReview $review) => (object) [
                'reviewer_name' => trim(($review->contract->customer->f_name ?? '') . ' ' . ($review->contract->customer->l_name ?? '')) ?: translate('customer'),
                'rating' => $review->rating,
                'body' => $review->body,
                'created_at' => $review->created_at,
                'verified' => true,
            ]);

        $openReviews = FreelancerReview::forService($service->id)
            ->with('customer')
            ->get()
            ->map(fn (FreelancerReview $review) => (object) [
                'reviewer_name' => trim(($review->customer->f_name ?? '') . ' ' . ($review->customer->l_name ?? '')) ?: translate('customer'),
                'rating' => $review->rating,
                'body' => $review->body,
                'created_at' => $review->created_at,
                'verified' => false,
            ]);

        $allReviews = $contractReviews->concat($openReviews)->sortByDesc('created_at')->values();
        $reviews = $allReviews->paginate(5)->withQueryString();

        $ratingAvg = $allReviews->count() > 0
            ? round((float) $allReviews->avg('rating'), 1)
            : $sellerRatingAvg;
        $ratingCount = $allReviews->count() > 0 ? $allReviews->count() : $sellerRatingCount;

        $customerReviewedService = auth('customer')->check()
            && FreelancerReview::where('customer_id', auth('customer')->id())->forService($service->id)->exists();

        $otherServices = FreelancerService::where('seller_id', $seller->id)
            ->where('is_active', true)
            ->where('id', '!=', $service->id)
            ->with('images')
            ->take(4)
            ->get();

        return view(VIEW_FILE_NAMES['hire_freelancer_service_show'], compact(
            'service',
            'seller',
            'displayName',
            'imageFullUrl',
            'activeOrdersCount',
            'freelancerLevel',
            'jobsCompleted',
            'ratingAvg',
            'ratingCount',
            'reviews',
            'customerReviewedService',
            'otherServices'
        ));
    }

    private function computeFreelancerLevel(Seller $seller): array
    {
        $ratingAvg = round((float) ($seller->freelancer_rating_avg ?? 0), 1);
        $ratingCount = (int) ($seller->freelancer_rating_count ?? 0);
        $jobsCompleted = (int) ($seller->freelancer_jobs_completed ?? 0);
        $level = $seller->freelancerLevelLabel();

        return compact('ratingAvg', 'ratingCount', 'jobsCompleted', 'level');
    }

    private function getServicesForListing(
        ?FreelancerCategory $category,
        ?FreelancerSpecialization $specialization,
        string $sort = 'recent',
        ?float $minPrice = null,
        ?float $maxPrice = null,
        ?int $maxDeliveryDays = null
    ): \Illuminate\Pagination\LengthAwarePaginator {
        // A service's real "starting price"/"delivery time" is either its own
        // column or the cheapest/fastest enabled package, whichever applies —
        // same fallback the service cards already use for display.
        $effectivePrice = 'COALESCE((SELECT MIN(fsp.price) FROM freelancer_service_packages fsp WHERE fsp.freelancer_service_id = freelancer_services.id AND fsp.is_enabled = 1), freelancer_services.price)';
        $effectiveDelivery = 'COALESCE((SELECT MIN(fsp2.delivery_time_days) FROM freelancer_service_packages fsp2 WHERE fsp2.freelancer_service_id = freelancer_services.id AND fsp2.is_enabled = 1), freelancer_services.delivery_time_days)';

        $query = FreelancerService::where('is_active', true)
            ->when($specialization, fn ($q) => $q->where('freelancer_specialization_id', $specialization->id))
            ->when(!$specialization && $category, fn ($q) => $q->where('freelancer_category_id', $category->id))
            ->whereHas('seller', fn ($q) => $q->where('status', 'approved')->where('account_status', 'active'))
            ->when($minPrice !== null, fn ($q) => $q->whereRaw("{$effectivePrice} >= ?", [$minPrice]))
            ->when($maxPrice !== null, fn ($q) => $q->whereRaw("{$effectivePrice} <= ?", [$maxPrice]))
            ->when($maxDeliveryDays !== null, fn ($q) => $q->whereRaw("{$effectiveDelivery} <= ?", [$maxDeliveryDays]))
            ->with(['seller.shop', 'category', 'specialization', 'images', 'enabledPackages']);

        $query = match ($sort) {
            'price_low' => $query->orderByRaw('COALESCE(price, 999999999) asc'),
            'price_high' => $query->orderByRaw('COALESCE(price, 0) desc'),
            'rating' => $query->join('sellers', 'sellers.id', '=', 'freelancer_services.seller_id')
                ->orderByDesc('sellers.freelancer_rating_avg')
                ->select('freelancer_services.*'),
            default => $query->latest('freelancer_services.id'),
        };

        return $query->paginate(12)->withQueryString();
    }

    private function getMenuCategories(): EloquentCollection
    {
        $specializations = FreelancerSpecialization::active()
            ->whereHas('category', fn ($query) => $this->activeRootCategoryQuery($query))
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('freelancer_category_id');

        return FreelancerCategory::active()
            ->whereNull('parent_id')
            ->where('position', 0)
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($category) use ($specializations) {
                $category->setRelation('activeSpecializations', $specializations->get($category->id, collect())->values());
                return $category;
            });
    }

    private function getDisplayCategories(
        EloquentCollection $categories,
        ?FreelancerCategory $selectedCategory,
        ?FreelancerSpecialization $selectedSpecialization
    ): Collection {
        if (!$selectedCategory) {
            return $categories;
        }

        $category = $categories->firstWhere('id', $selectedCategory->id);

        if (!$category) {
            return $categories;
        }


        return collect([$category]);
    }

    private function activeRootCategoryQuery($query): mixed
    {
        return $query->active()
            ->whereNull('parent_id')
            ->where('position', 0);
    }
}

