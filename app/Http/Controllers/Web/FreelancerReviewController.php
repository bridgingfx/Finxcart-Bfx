<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FreelancerPortfolioItem;
use App\Models\FreelancerReview;
use App\Models\FreelancerService;
use App\Models\Seller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FreelancerReviewController extends Controller
{
    public function store(Request $request, string $type, int $id): RedirectResponse
    {
        if (!in_array($type, ['service', 'portfolio'], true)) {
            abort(404);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5|regex:/^\d+$/',
            'body' => 'nullable|string|max:2000',
        ]);

        $sellerId = $type === 'service'
            ? FreelancerService::where('id', $id)->where('is_active', true)->value('seller_id')
            : FreelancerPortfolioItem::where('id', $id)->where('is_active', true)->value('seller_id');

        if (!$sellerId || !Seller::where('id', $sellerId)->where('status', 'approved')->where('account_status', 'active')->exists()) {
            abort(404);
        }

        $customerId = auth('customer')->id();

        $alreadyReviewed = FreelancerReview::where('customer_id', $customerId)
            ->where('reviewable_type', $type)
            ->where('reviewable_id', $id)
            ->exists();

        if ($alreadyReviewed) {
            Toastr::error(translate('you_have_already_reviewed_this'));
            return back();
        }

        FreelancerReview::create([
            'customer_id' => $customerId,
            'seller_id' => $sellerId,
            'reviewable_type' => $type,
            'reviewable_id' => $id,
            'rating' => $request->input('rating'),
            'body' => $request->input('body'),
        ]);

        Toastr::success(translate('review_submitted_successfully'));
        return back();
    }
}
