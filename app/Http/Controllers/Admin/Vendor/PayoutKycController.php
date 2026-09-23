<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Seller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PayoutKycController extends Controller
{
    public function index(Request $request): View
    {
        $status     = $request->get('status', 'all');
        $search     = $request->get('search', '');
        $sellerType = $request->get('seller_type', '');

        $baseQuery = Seller::with([
                'shop:id,seller_id,name',
                'vendorVerification:id,seller_id,status,payout_kyc_document,payout_kyc_submitted_at,payout_kyc_note,sumsub_applicant_id,sumsub_review_status',
            ])
            ->whereHas('vendorVerification', fn ($q) => $q->where('status', 'approved'));

        // Status counts for tab badges (before applying status filter) — one query
        // with conditional aggregation instead of 5 separate re-runs of the same
        // whereHas('vendorVerification', ...) correlated subquery.
        $statusCounts = (clone $baseQuery)->toBase()->selectRaw("
                COUNT(*) as all_count,
                SUM(CASE WHEN kyc_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN kyc_status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN kyc_status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                SUM(CASE WHEN kyc_status IS NULL THEN 1 ELSE 0 END) as not_started_count
            ")->first();
        $counts = [
            'all'         => (int) $statusCounts->all_count,
            'pending'     => (int) $statusCounts->pending_count,
            'approved'    => (int) $statusCounts->approved_count,
            'rejected'    => (int) $statusCounts->rejected_count,
            'not_started' => (int) $statusCounts->not_started_count,
        ];

        $vendors = $baseQuery
            ->when($status !== 'all' && in_array($status, ['pending', 'approved', 'rejected', 'not_started'], true), function ($q) use ($status) {
                $status === 'not_started' ? $q->whereNull('kyc_status') : $q->where('kyc_status', $status);
            })
            ->when($sellerType, fn ($q) => $q->where('seller_type', $sellerType))
            ->when($search, fn ($q) => $q->where(function ($q2) use ($search) {
                $q2->where('f_name', 'like', "%{$search}%")
                   ->orWhere('l_name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate((int) (getWebConfig(name: 'pagination_limit') ?? 25))
            ->appends($request->only(['status', 'search', 'seller_type']));

        return view('admin-views.vendor.payout-kyc.index',
            compact('vendors', 'status', 'search', 'sellerType', 'counts'));
    }

    public function approve(int $id): RedirectResponse
    {
        $vendor = Seller::whereHas('vendorVerification', fn ($query) => $query->where('status', 'approved'))->findOrFail($id);

        if ((getWebConfig('kyc_method') ?? 'manual') === 'manual' && empty($vendor->vendorVerification?->payout_kyc_document)) {
            ToastMagic::error(translate('Payout_KYC_document_is_required_before_approval'));
            return back();
        }

        $vendor->update(['kyc_status' => 'approved']);

        $this->notifyAdmin($vendor, 'approved');

        ToastMagic::success(translate('Payout_KYC_approved_successfully'));
        return back();
    }

    public function reject(int $id): RedirectResponse
    {
        $vendor = Seller::whereHas('vendorVerification', fn ($query) => $query->where('status', 'approved'))->findOrFail($id);
        $vendor->update(['kyc_status' => 'rejected']);

        $this->notifyAdmin($vendor, 'rejected');

        ToastMagic::success(translate('Payout_KYC_rejected_successfully'));
        return back();
    }

    private function notifyAdmin(Seller $vendor, string $status): void
    {
        try {
            $name = trim(($vendor->f_name ?? '') . ' ' . ($vendor->l_name ?? '')) ?: $vendor->email;
            $reviewer = auth('admin')->user();
            $reviewerName = trim(($reviewer?->f_name ?? '') . ' ' . ($reviewer?->l_name ?? '')) ?: ($reviewer?->name ?? $reviewer?->email);

            AdminNotification::create([
                'type' => 'payout_kyc_reviewed',
                'title' => 'Payout KYC ' . ($status === 'approved' ? 'Approved' : 'Rejected'),
                'message' => "{$name}'s payout KYC was {$status} by {$reviewerName}.",
                'link' => route('admin.vendors.payout-kyc.index'),
                'reference_id' => $vendor->id,
            ]);
        } catch (\Throwable $e) {
            \Log::error('[PayoutKycController] Admin notification failed: ' . $e->getMessage());
        }
    }
}
