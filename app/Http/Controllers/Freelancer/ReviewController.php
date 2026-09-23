<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\FreelancerContractReview;
use App\Models\FreelancerPortfolioItem;
use App\Models\FreelancerReview;
use App\Models\FreelancerService;
use Illuminate\Contracts\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        $sellerId = auth('freelancer')->id();

        $contractReviews = FreelancerContractReview::where('reviewee_type', 'seller')
            ->where('reviewer_type', 'customer')
            ->whereHas('contract', fn ($query) => $query->where('seller_id', $sellerId))
            ->with('contract.customer')
            ->get()
            ->map(fn (FreelancerContractReview $review) => (object) [
                'source' => 'contract',
                'reviewer_name' => trim(($review->contract?->customer?->f_name ?? '') . ' ' . ($review->contract?->customer?->l_name ?? '')) ?: translate('customer'),
                'reference' => translate('contract') . ' #' . $review->freelancer_contract_id,
                'rating' => $review->rating,
                'body' => $review->body,
                'created_at' => $review->created_at,
            ]);

        $openReviews = FreelancerReview::where('seller_id', $sellerId)->with('customer')->get();
        $serviceTitles = FreelancerService::whereIn('id', $openReviews->where('reviewable_type', 'service')->pluck('reviewable_id'))->pluck('title', 'id');
        $portfolioTitles = FreelancerPortfolioItem::whereIn('id', $openReviews->where('reviewable_type', 'portfolio')->pluck('reviewable_id'))->pluck('title', 'id');

        $openReviews = $openReviews->map(function (FreelancerReview $review) use ($serviceTitles, $portfolioTitles) {
            $title = $review->reviewable_type === 'service'
                ? ($serviceTitles[$review->reviewable_id] ?? translate('deleted_service'))
                : ($portfolioTitles[$review->reviewable_id] ?? translate('deleted_portfolio_item'));

            return (object) [
                'source' => $review->reviewable_type,
                'reviewer_name' => trim(($review->customer?->f_name ?? '') . ' ' . ($review->customer?->l_name ?? '')) ?: translate('customer'),
                'reference' => $title,
                'rating' => $review->rating,
                'body' => $review->body,
                'created_at' => $review->created_at,
            ];
        });

        $allReviews = $contractReviews->concat($openReviews)->sortByDesc('created_at')->values();

        $totalReviews = $allReviews->count();
        $avgRating = $totalReviews > 0 ? round($allReviews->avg('rating'), 1) : 0;
        $ratingBreakdown = collect(range(5, 1))->mapWithKeys(
            fn ($stars) => [$stars => $allReviews->where('rating', $stars)->count()]
        );

        $reviews = $allReviews->paginate(getWebConfig(name: 'pagination_limit'))->withQueryString();

        return view('freelancer-views.reviews.index', compact('reviews', 'totalReviews', 'avgRating', 'ratingBreakdown'));
    }
}
