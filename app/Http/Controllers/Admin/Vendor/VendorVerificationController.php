<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Events\FreelancerKycStatusChangedEvent;
use App\Events\VendorRegistrationEvent;
use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\VendorVerification;
use App\Traits\FileManagerTrait;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VendorVerificationController extends Controller
{
    use FileManagerTrait;

    public function index(Request $request): View
    {
        $type = $request->get('type', 'all');
        $verifications = VendorVerification::with(['seller.shop'])
            ->whereIn('status', ['pending', 'resubmitted'])
            ->where('seller_type', '!=', 'freelancer')
            ->when(in_array($type, ['company', 'individual'], true), function ($query) use ($type) {
                $query->where('seller_type', $type);
            })
            ->latest()
            ->paginate((int) (getWebConfig(name: 'pagination_limit') ?? 25))
            ->appends(['type' => $type]);

        return view('admin-views.vendor.vendor-verification.index', compact('verifications', 'type'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $verification = VendorVerification::with(['seller.shop'])->findOrFail($id);
        $seller = $verification->seller;
        $shop = $seller?->shop;
        $sellerType = $request->input('seller_type', $verification->seller_type);

        $rules = [
            'seller_type' => ['required', 'in:company,individual'],
            'vendor_name' => ['required', 'string', 'max:255'],
            'vendor_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,bmp,tif,tiff'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,bmp,tif,tiff'],
            'banner' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,bmp,tif,tiff'],
        ];

        if ($sellerType === 'company') {
            $rules += [
                'company_name' => ['required', 'string', 'max:255'],
                'company_address' => ['required', 'string', 'max:500'],
                'company_website' => ['required', 'string', 'max:255'],
                'company_no' => ['required', 'string', 'max:255'],
                'company_phone' => ['required', 'string', 'max:20'],
                'company_registered_country' => ['required', 'string', 'max:255'],
                'company_license' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:10240'],
            ];
        } else {
            $rules += [
                'personal_id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:10240'],
            ];
        }

        $request->validate($rules);

        $companyLicense = $verification->company_license;
        $personalIdDocument = $verification->personal_id_document;
        $sellerImage = $seller?->image;
        $shopImage = $shop?->image;
        $shopBanner = $shop?->banner;

        if ($sellerType === 'company' && $request->hasFile('company_license')) {
            if (!empty($companyLicense)) {
                $this->delete('vendor-verifications/' . $companyLicense);
            }

            $companyLicense = $this->fileUpload(
                dir: 'vendor-verifications/',
                format: $request->file('company_license')->getClientOriginalExtension(),
                file: $request->file('company_license')
            );
        }

        if (in_array($sellerType, ['individual', 'freelancer'], true) && $request->hasFile('personal_id_document')) {
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

        if ($request->hasFile('logo')) {
            if (!empty($shopImage) && $shopImage !== 'def.png') {
                $this->delete('shop/' . $shopImage);
            }

            $shopImage = $this->upload(
                dir: 'shop/',
                format: 'webp',
                image: $request->file('logo')
            );
        }

        if ($request->hasFile('banner')) {
            if (!empty($shopBanner) && $shopBanner !== 'def.png') {
                $this->delete('shop/banner/' . $shopBanner);
            }

            $shopBanner = $this->upload(
                dir: 'shop/banner/',
                format: 'webp',
                image: $request->file('banner')
            );
        }

        $verificationData = [
            'seller_type' => $sellerType,
            'company_website' => $sellerType === 'company' ? $request->company_website : null,
            'company_no' => $sellerType === 'company' ? $request->company_no : null,
            'company_license' => $companyLicense,
            'company_registered_country' => $sellerType === 'company' ? $request->company_registered_country : $verification->company_registered_country,
            'personal_id_document' => $personalIdDocument,
            'personal_name' => $request->vendor_name,
            'sell_description' => in_array($sellerType, ['individual', 'freelancer'], true) ? $verification->sell_description : null,
        ];

        $shopName = $sellerType === 'company' ? $request->company_name : $request->vendor_name;
        $shopAddress = $sellerType === 'company' ? $request->company_address : ($shop?->address ?? '');
        $shopContact = $sellerType === 'company' ? $request->company_phone : $shop?->contact;

        DB::transaction(function () use ($seller, $shop, $verification, $verificationData, $sellerType, $sellerImage, $shopImage, $shopBanner, $shopName, $shopAddress, $shopContact, $request) {
            $seller->update([
                'f_name' => $request->vendor_name,
                'seller_type' => $sellerType,
                'image' => $sellerImage,
            ]);

            $verification->update($verificationData);

            $shopModel = $shop ?: new Shop([
                'seller_id' => $seller->id,
                'slug' => $shop?->slug ?: \Illuminate\Support\Str::slug(\Illuminate\Support\Str::before($seller->email, '@') ?: 'vendor', '-') . '-' . \Illuminate\Support\Str::random(6),
                'bottom_banner' => 'def.png',
                'bottom_banner_storage_type' => config('filesystems.disks.default') ?? 'public',
            ]);

            $shopModel->fill([
                'name' => $shopName,
                'address' => $shopAddress,
                'contact' => $shopContact,
                'image' => $shopImage,
                'banner' => $shopBanner,
                'image_storage_type' => config('filesystems.disks.default') ?? 'public',
                'banner_storage_type' => config('filesystems.disks.default') ?? 'public',
            ]);

            $shopModel->save();
        });

        ToastMagic::success('Vendor credentials updated successfully.');
        return back();
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'approval_note' => 'nullable|string|max:1000',
        ]);

        $verification = VendorVerification::with('seller.shop')->findOrFail($id);
        $reviewer = auth('admin')->user();
        $reviewerName = trim(($reviewer?->f_name ?? '') . ' ' . ($reviewer?->l_name ?? '')) ?: ($reviewer?->name ?? $reviewer?->email);

        $verification->update([
            'status' => 'approved',
            'rejection_reason' => null,
            'approval_note' => $request->filled('approval_note') ? $request->approval_note : null,
            'reviewed_by_name' => $reviewerName,
            'reviewed_at' => now(),
        ]);

        $sellerUpdates = [
            'status' => 'approved',
            'first_login_after_approval' => true,
            'kyc_notification_seen' => 0,
        ];

        $verification->seller->update($sellerUpdates);

        try {
            $sellerTypeLabel = ucfirst($verification->seller->seller_type ?? 'vendor');
            $eventData = [
                'seller_id' => $verification->seller->id,
                'verification_id' => $verification->id,
                'name' => $verification->seller->f_name . ' ' . $verification->seller->l_name,
                'vendorName' => $verification->seller->f_name . ' ' . $verification->seller->l_name,
                'shopName' => $verification->seller->shop?->name,
                'sellerType' => $sellerTypeLabel,
                'seller_type' => $verification->seller->seller_type,
                'reviewerName' => $reviewerName,
                'message' => 'Congratulations! You are approved as a vendor on Finxcart. You can now login and start adding your products.',
                'status' => 'approved',
                'subject' => translate('Verification_Approved'),
                'title' => translate('Verification_Approved'),
                'userType' => 'vendor',
                'templateName' => 'vendor-verification-approved',
            ];

            $event = $verification->seller->seller_type === 'freelancer'
                ? new FreelancerKycStatusChangedEvent(email: $verification->seller->email, data: $eventData)
                : new VendorRegistrationEvent(email: $verification->seller->email, data: $eventData);

            event($event);
        } catch (\Throwable $e) {
            \Log::error('Vendor verification approval email failed: ' . $e->getMessage());
        }

        ToastMagic::success(translate('vendor_verification_approved_successfully'));
        return back();
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $verification = VendorVerification::with('seller.shop')->findOrFail($id);
        $reviewer = auth('admin')->user();
        $reviewerName = trim(($reviewer?->f_name ?? '') . ' ' . ($reviewer?->l_name ?? '')) ?: ($reviewer?->name ?? $reviewer?->email);
        $verification->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approval_note' => null,
            'reviewed_by_name' => $reviewerName,
            'reviewed_at' => now(),
        ]);

        // kyc_notification_seen = 0 so the vendor's next page load shows a toast —
        // previously left at 1 ("already seen"), which silently suppressed any
        // rejection notice. See layouts/vendor/app.blade.php.
        $verification->seller->update(['status' => 'rejected', 'kyc_notification_seen' => 0]);

        try {
            $sellerTypeLabel = ucfirst($verification->seller->seller_type ?? 'vendor');
            $eventData = [
                'seller_id' => $verification->seller->id,
                'verification_id' => $verification->id,
                'name' => $verification->seller->f_name . ' ' . $verification->seller->l_name,
                'vendorName' => $verification->seller->f_name . ' ' . $verification->seller->l_name,
                'shopName' => $verification->seller->shop?->name,
                'sellerType' => $sellerTypeLabel,
                'seller_type' => $verification->seller->seller_type,
                'reviewerName' => $reviewerName,
                'message' => 'Admin has reviewed your credentials. Please login to see the status.',
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'rejectionReason' => $request->rejection_reason,
                'subject' => translate('Verification_Rejected'),
                'title' => translate('Verification_Rejected'),
                'userType' => 'vendor',
                'templateName' => 'vendor-verification-denied',
            ];

            $event = $verification->seller->seller_type === 'freelancer'
                ? new FreelancerKycStatusChangedEvent(email: $verification->seller->email, data: $eventData)
                : new VendorRegistrationEvent(email: $verification->seller->email, data: $eventData);

            event($event);
        } catch (\Throwable $e) {
            \Log::error('Vendor verification rejection email failed: ' . $e->getMessage());
        }

        ToastMagic::error(translate('vendor_verification_rejected_successfully'));
        return back();
    }
}
