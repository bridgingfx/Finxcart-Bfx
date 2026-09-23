<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\VendorVerification;
use App\Services\FreelancerKycService;
use App\Traits\FileManagerTrait;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FreelancerVerificationController extends Controller
{
    use FileManagerTrait;

    public function __construct(private readonly FreelancerKycService $kycService)
    {
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $verification = VendorVerification::with(['seller.shop'])
            ->where('seller_type', 'freelancer')
            ->findOrFail($id);
        $seller = $verification->seller;
        $shop = $seller?->shop;

        $request->validate([
            'vendor_name' => ['required', 'string', 'max:255'],
            'vendor_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,bmp,tif,tiff'],
            'personal_id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:10240'],
        ]);

        $personalIdDocument = $verification->personal_id_document;
        $sellerImage = $seller?->image;

        if ($request->hasFile('personal_id_document')) {
            if (!empty($personalIdDocument)) {
                $this->delete('vendor-verifications/' . $personalIdDocument);
            }

            $personalIdDocument = $this->fileUpload(
                dir: 'vendor-verifications/',
                format: $request->file('personal_id_document')->getClientOriginalExtension(),
                file: $request->file('personal_id_document')
            );
        }

        if ($request->hasFile('vendor_image')) {
            if (!empty($sellerImage) && $sellerImage !== 'def.png') {
                $this->delete('seller/' . $sellerImage);
            }

            $sellerImage = $this->fileUpload(
                dir: 'seller/',
                format: $request->file('vendor_image')->getClientOriginalExtension(),
                file: $request->file('vendor_image')
            );
        }

        DB::transaction(function () use ($seller, $shop, $verification, $sellerImage, $personalIdDocument, $request) {
            $seller->update([
                'f_name' => $request->vendor_name,
                'image' => $sellerImage,
            ]);

            $verification->update([
                'personal_id_document' => $personalIdDocument,
                'personal_name' => $request->vendor_name,
            ]);

            $shopModel = $shop ?: new Shop([
                'seller_id' => $seller->id,
                'slug' => Str::slug(Str::before($seller->email, '@') ?: 'freelancer', '-') . '-' . Str::random(6),
                'bottom_banner' => 'def.png',
                'bottom_banner_storage_type' => config('filesystems.disks.default') ?? 'public',
            ]);

            $shopModel->fill([
                'name' => $request->vendor_name,
                'image_storage_type' => config('filesystems.disks.default') ?? 'public',
                'banner_storage_type' => config('filesystems.disks.default') ?? 'public',
            ]);

            $shopModel->save();
        });

        ToastMagic::success(translate('freelancer_credentials_updated_successfully'));
        return back();
    }

    public function approveView(int $id): RedirectResponse
    {
        $verification = VendorVerification::where('seller_type', 'freelancer')->findOrFail($id);

        return redirect()->route('admin.freelancer.accounts.view', [
            'id' => $verification->seller_id,
        ]);
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'approval_note' => 'nullable|string|max:1000',
        ]);

        $verification = VendorVerification::with('seller.shop')
            ->where('seller_type', 'freelancer')
            ->findOrFail($id);

        $this->kycService->approve(
            verification: $verification,
            reviewer: auth('admin')->user(),
            approvalNote: $request->filled('approval_note') ? $request->approval_note : null,
        );

        ToastMagic::success(translate('freelancer_verification_approved_successfully'));
        return back();
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $verification = VendorVerification::with('seller.shop')
            ->where('seller_type', 'freelancer')
            ->findOrFail($id);

        $this->kycService->reject(
            verification: $verification,
            reviewer: auth('admin')->user(),
            rejectionReason: $request->rejection_reason,
        );

        ToastMagic::error(translate('freelancer_verification_rejected_successfully'));
        return back();
    }
}
