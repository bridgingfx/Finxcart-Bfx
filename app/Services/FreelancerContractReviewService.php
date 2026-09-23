<?php

namespace App\Services;

use App\Models\FreelancerContract;
use App\Models\FreelancerContractReview;
use App\Models\Seller;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FreelancerContractReviewService
{
    public function store(FreelancerContract $contract, string $reviewerType, int $reviewerId, int $rating, ?string $body): FreelancerContractReview
    {
        if ($contract->status !== 'completed') {
            throw new RuntimeException('Reviews are only allowed once the contract is completed.');
        }

        if ($contract->reviews()->where('reviewer_type', $reviewerType)->exists()) {
            throw new RuntimeException('You have already reviewed this contract.');
        }

        return DB::transaction(function () use ($contract, $reviewerType, $reviewerId, $rating, $body) {
            $revieweeType = $reviewerType === 'customer' ? 'seller' : 'customer';
            $revieweeId = $reviewerType === 'customer' ? $contract->seller_id : $contract->customer_id;

            $review = $contract->reviews()->create([
                'reviewer_type' => $reviewerType,
                'reviewer_id' => $reviewerId,
                'reviewee_type' => $revieweeType,
                'reviewee_id' => $revieweeId,
                'rating' => $rating,
                'body' => $body,
            ]);

            if ($revieweeType === 'seller') {
                $this->recalculateSellerRating($contract->seller_id);
            }

            return $review;
        });
    }

    private function recalculateSellerRating(int $sellerId): void
    {
        $stats = FreelancerContractReview::query()
            ->whereHas('contract', fn ($query) => $query->where('seller_id', $sellerId))
            ->where('reviewee_type', 'seller')
            ->selectRaw('avg(rating) as avg_rating, count(*) as total')
            ->first();

        Seller::whereKey($sellerId)->update([
            'freelancer_rating_avg' => $stats->avg_rating,
            'freelancer_rating_count' => $stats->total,
        ]);
    }
}
