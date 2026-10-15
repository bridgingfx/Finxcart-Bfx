<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\BaseController;
use App\Models\FreelancerContractReview;
use App\Models\FreelancerPortfolioItem;
use App\Models\FreelancerReview;
use App\Models\FreelancerService;
use App\Models\Seller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FreelancerReviewController extends BaseController
{
    // Cross-source pagination (contract reviews + open service/portfolio reviews)
    // is merged in PHP after fetch, so each source is capped rather than loaded
    // unbounded — otherwise this page's memory use grows without limit as the
    // review tables grow. 1000 is generous headroom for an admin moderation
    // queue; a true DB-level UNION would be needed to support deep pagination
    // past that many reviews per source.
    private const MAX_REVIEWS_PER_SOURCE = 1000;

    public function index(?Request $request, string $type = null): View
    {
        $contractReviews = FreelancerContractReview::with(['contract.customer', 'contract.freelancer.shop'])
            ->latest()
            ->limit(self::MAX_REVIEWS_PER_SOURCE)
            ->get()
            ->map(fn (FreelancerContractReview $review) => (object) [
                'id' => $review->id,
                'source' => 'contract',
                'reviewer_name' => trim(($review->contract?->customer?->f_name ?? '') . ' ' . ($review->contract?->customer?->l_name ?? '')) ?: translate('customer'),
                'rating' => $review->rating,
                'body' => $review->body,
                'created_at' => $review->created_at,
                'link' => route('admin.freelancer.contracts.view', $review->freelancer_contract_id),
                'link_label' => translate('contract') . ' #' . $review->freelancer_contract_id,
                'delete_route' => route('admin.freelancer.reviews.delete', $review->id),
            ]);

        $openReviews = FreelancerReview::with('customer')->latest()->limit(self::MAX_REVIEWS_PER_SOURCE)->get();
        $serviceTitles = FreelancerService::whereIn('id', $openReviews->where('reviewable_type', 'service')->pluck('reviewable_id'))->pluck('title', 'id');
        $portfolioTitles = FreelancerPortfolioItem::whereIn('id', $openReviews->where('reviewable_type', 'portfolio')->pluck('reviewable_id'))->pluck('title', 'id');

        $openReviews = $openReviews->map(function (FreelancerReview $review) use ($serviceTitles, $portfolioTitles) {
            $title = $review->reviewable_type === 'service'
                ? ($serviceTitles[$review->reviewable_id] ?? translate('deleted_service'))
                : ($portfolioTitles[$review->reviewable_id] ?? translate('deleted_portfolio_item'));

            return (object) [
                'id' => $review->id,
                'source' => $review->reviewable_type,
                'reviewer_name' => trim(($review->customer?->f_name ?? '') . ' ' . ($review->customer?->l_name ?? '')) ?: translate('customer'),
                'rating' => $review->rating,
                'body' => $review->body,
                'created_at' => $review->created_at,
                'link' => null,
                'link_label' => $title,
                'delete_route' => route('admin.freelancer.reviews.delete-open', $review->id),
            ];
        });

        $reviews = $contractReviews->concat($openReviews)
            ->sortByDesc('created_at')
            ->values()
            ->paginate(getWebConfig(name: 'pagination_limit'))
            ->withQueryString();

        return view('admin-views.freelancer.reviews.index', compact('reviews'));
    }

    public function destroy(FreelancerContractReview $review): RedirectResponse
    {
        $sellerId = $review->reviewee_type === 'seller' ? $review->reviewee_id : null;
        $review->delete();

        if ($sellerId) {
            $this->recalculateSellerRating($sellerId);
        }

        ToastMagic::success(translate('review_deleted_successfully'));
        return back();
    }

    public function destroyOpen(FreelancerReview $review): RedirectResponse
    {
        $review->delete();

        ToastMagic::success(translate('review_deleted_successfully'));
        return back();
    }

    private function recalculateSellerRating(int $sellerId): void
    {
        $stats = FreelancerContractReview::query()
            ->whereHas('contract', fn ($query) => $query->where('seller_id', $sellerId))
            ->where('reviewee_type', 'seller')
            ->selectRaw('avg(rating) as avg_rating, count(*) as total')
            ->first();

        Seller::whereKey($sellerId)->update([
            'freelancer_rating_avg' => $stats->total > 0 ? $stats->avg_rating : null,
            'freelancer_rating_count' => $stats->total,
        ]);
    }
}
