<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ProductTier;
use App\Models\Seller;
use App\Models\VendorTier;
use App\Models\VendorTierPayment;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorSubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status', 'all');
        $baseQuery = VendorTier::query()->whereHas('seller')->whereHas('tier');
        $subscriptions = (clone $baseQuery)
            ->with(['seller', 'tier'])
            ->withCount(['products', 'payments'])
            ->when($status === 'ended', fn ($query) => $query->whereIn('status', ['ended', 'endende', 'expired']))
            ->when(!in_array($status, ['all', 'ended'], true), fn ($query) => $query->where('status', $status))
            ->when($request->filled('tier_id'), fn ($query) => $query->where('product_tier_id', $request->tier_id))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $tiers = ProductTier::orderBy('name')->get();
        $counts = [
            'all' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('status', 'active')->count(),
            'trial' => (clone $baseQuery)->where('status', 'trial')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            'ended' => (clone $baseQuery)->whereIn('status', ['ended', 'endende', 'expired'])->count(),
        ];

        return view('admin-views.vendor.subscriptions.index', compact('subscriptions', 'tiers', 'status', 'counts'));
    }

    public function cancel(int $id): RedirectResponse
    {
        $subscription = VendorTier::findOrFail($id);
        $subscription->update(['status' => 'cancelled']);
        $subscription->products()->update(['status' => 0, 'featured_status' => 0]);
        cacheRemoveByType(type: 'products');

        ToastMagic::success(translate('Subscription_cancelled_successfully.'));
        return back();
    }

    public function payments(int $vendorId): View
    {
        $seller = Seller::findOrFail($vendorId);
        $payments = VendorTierPayment::whereHas(
            'vendorTier',
            fn ($query) => $query->where('seller_id', $vendorId)
        )
            ->with('vendorTier.tier')
            ->latest('paid_at')
            ->paginate(25);

        return view('admin-views.vendor.subscriptions.payments', compact('payments', 'seller'));
    }
}
