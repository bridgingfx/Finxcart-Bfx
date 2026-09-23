<?php

namespace App\Http\Controllers\Freelancer;

use App\Contracts\Repositories\FreelancerPortfolioItemRepositoryInterface;
use App\Contracts\Repositories\FreelancerServiceRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\FreelancerContract;
use App\Models\FreelancerQuoteRequest;
use App\Models\SellerWallet;
use App\Models\VendorVerification;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly FreelancerServiceRepositoryInterface $serviceRepo,
        private readonly FreelancerPortfolioItemRepositoryInterface $portfolioRepo,
    ) {
    }

    public function index(): View
    {
        $sellerId = auth('freelancer')->id();
        $seller = auth('freelancer')->user();

        $services = $this->serviceRepo->getBySellerId($sellerId);
        $portfolioItems = $this->portfolioRepo->getBySellerId($sellerId);
        $verification = VendorVerification::where('seller_id', $sellerId)->first();

        $totalServices = $services->count();
        $activeServices = $services->where('is_active', true)->count();
        $totalPortfolioItems = $portfolioItems->count();

        $wallet = SellerWallet::where('seller_id', $sellerId)->first();
        $activeContractsCount = FreelancerContract::where('seller_id', $sellerId)->where('status', 'active')->count();
        $pendingQuotesCount = FreelancerQuoteRequest::where('seller_id', $sellerId)->where('status', 'pending')->count();
        $recentContracts = FreelancerContract::where('seller_id', $sellerId)
            ->with(['customer', 'service'])
            ->latest()
            ->take(5)
            ->get();

        return view('freelancer-views.dashboard.index', compact(
            'seller',
            'verification',
            'totalServices',
            'activeServices',
            'totalPortfolioItems',
            'wallet',
            'activeContractsCount',
            'pendingQuotesCount',
            'recentContracts'
        ));
    }
}
