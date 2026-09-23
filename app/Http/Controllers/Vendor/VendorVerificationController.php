<?php

namespace App\Http\Controllers\Vendor;

use App\Events\VendorRegistrationEvent;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Seller;
use App\Models\VendorVerification;
use App\Traits\FileManagerTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class VendorVerificationController extends Controller
{
    use FileManagerTrait;

    public function index(): View|RedirectResponse
    {
        $seller = auth('seller')->user();
        $verification = $this->getCurrentVerification();
        $shop = $seller?->shop;

        if ($verification && in_array($verification->status, ['pending', 'resubmitted'], true)) {
            return redirect()->route('vendor.dashboard.index');
        }

        // Once approved, this page is a read-only status summary — editing and
        // resubmitting documents for an already-verified vendor isn't offered
        // here (there's no path back to this page's editable form after
        // approval, by design).
        if ($verification && $verification->status === 'approved') {
            return view('vendor-views.verification.approved', compact('verification', 'seller', 'shop'));
        }

        $countries = $this->getCountryOptions();

        return view('vendor-views.verification.index', compact('verification', 'seller', 'shop', 'countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $seller = auth('seller')->user();
        if (!empty($seller?->seller_type)) {
            $request->merge(['seller_type' => $seller->seller_type]);
        }

        $verification = $this->getCurrentVerification();

        $this->validateProfileRequest($request, $seller, $verification);
        $this->saveProfileAndVerification($seller, $request, $verification);

        session()->flash('success', 'Application submitted successfully. Please wait for admin approval.');
        if ($request->input('return_to') === 'profile') {
            return redirect()->route('vendor.profile.update', auth('seller')->id())
                ->with('success', 'Verification details submitted for review.')
                ->withFragment('company-verification-div');
        }

        return redirect()->route('vendor.dashboard.index');
    }

    private function validateProfileRequest(Request $request, Seller $seller, ?VendorVerification $verification): void
    {
        $shop = $seller?->shop;
        $hasSellerImage = !empty($seller?->image) && $seller->image !== 'def.png';
        $hasShopLogo = !empty($shop?->image) && $shop->image !== 'def.png';
        $hasShopBanner = !empty($shop?->banner) && $shop->banner !== 'def.png';

        $rules = [
            'seller_type' => ['required', 'in:company,individual,freelancer'],
            'vendor_name' => ['required', 'string', 'max:255'],
            'vendor_image' => [$hasSellerImage ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,webp,gif,bmp,tif,tiff'],
            'logo' => [$hasShopLogo ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,webp,gif,bmp,tif,tiff'],
            'banner' => [$hasShopBanner ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,webp,gif,bmp,tif,tiff'],
        ];

        if ($request->seller_type === 'company') {
            $rules += [
                'company_name' => ['required', 'string', 'max:255'],
                'company_address' => ['required', 'string', 'max:500'],
                'company_email' => ['required', 'email', 'max:255'],
                'company_phone' => ['required', 'string', 'max:20'],
                'company_website' => ['required', 'string', 'max:255'],
                'company_no' => ['required', 'string', 'max:255'],
                'company_registered_country' => ['required', 'string', 'max:255'],
                'company_license' => [$verification?->company_license ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:10240'],
            ];
        } else {
            $rules += [
                'personal_email' => ['required', 'email', 'max:255'],
                'personal_contact' => ['required', 'string', 'max:20'],
                'personal_id_document' => [$verification?->personal_id_document ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:10240'],
            ];
        }

        $request->validate($rules);
    }

    private function getCurrentVerification(): ?VendorVerification
    {
        return VendorVerification::where('seller_id', auth('seller')->id())->first();
    }

    private function saveProfileAndVerification(Seller $seller, Request $request, ?VendorVerification $verification): void
    {
        $shop = $seller->shop;
        $storage = config('filesystems.disks.default') ?? 'public';
        $verificationStatus = $verification?->status === 'rejected' ? 'resubmitted' : 'pending';
        $sellerImage = $seller->image;
        $country = $request['seller_type'] === 'company'
            ? $request['company_registered_country']
            : $verification?->company_registered_country;

        $companyLicense = $verification?->company_license;
        $personalIdDocument = $verification?->personal_id_document;

        if ($request['seller_type'] === 'company' && $request->hasFile('company_license')) {
            if (!empty($personalIdDocument)) {
                $this->delete('vendor-verifications/' . $personalIdDocument);
                $personalIdDocument = null;
            }

            if (!empty($companyLicense)) {
                $this->delete('vendor-verifications/' . $companyLicense);
            }

            $companyLicense = $this->fileUpload(
                dir: 'vendor-verifications/',
                format: $this->safeMimeExtension($request->file('company_license')),
                file: $request->file('company_license')
            );
        }
        if (in_array($request['seller_type'], ['individual', 'freelancer'], true) && $request->hasFile('personal_id_document')) {
            if (!empty($companyLicense)) {
                $this->delete('vendor-verifications/' . $companyLicense);
                $companyLicense = null;
            }

            if (!empty($personalIdDocument)) {
                $this->delete('vendor-verifications/' . $personalIdDocument);
            }

            $personalIdDocument = $this->fileUpload(
                dir: 'vendor-verifications/',
                format: $this->safeMimeExtension($request->file('personal_id_document')),
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

        $shopImage = $shop?->image;
        $shopBanner = $shop?->banner;

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

        $shopName = $request['seller_type'] === 'company'
            ? $request['company_name']
            : $request['vendor_name'];
        $shopAddress = $request['seller_type'] === 'company'
            ? $request['company_address']
            : ($shop?->address ?? '');
        $shopContact = $request['seller_type'] === 'company'
            ? $request['company_phone']
            : $request['personal_contact'];
        $shopEmail = $request['seller_type'] === 'company'
            ? $request['company_email']
            : $request['personal_email'];

        $verificationData = [
            'seller_type' => $request['seller_type'],
            'company_website' => $request['seller_type'] === 'company' ? $request['company_website'] : null,
            'company_no' => $request['seller_type'] === 'company' ? $request['company_no'] : null,
            'company_license' => $companyLicense,
            'company_registered_country' => $country,
            'personal_id_document' => $personalIdDocument,
            'personal_name' => $request['vendor_name'],
            'personal_email' => in_array($request['seller_type'], ['individual', 'freelancer'], true) ? $request['personal_email'] : null,
            'personal_contact' => in_array($request['seller_type'], ['individual', 'freelancer'], true) ? $request['personal_contact'] : null,
            'sell_description' => in_array($request['seller_type'], ['individual', 'freelancer'], true) ? $verification?->sell_description : null,
            'status' => $verificationStatus,
            'rejection_reason' => null,
        ];

        DB::transaction(function () use ($seller, $shop, $shopName, $shopAddress, $shopContact, $shopEmail, $shopImage, $shopBanner, $verificationData, $request, $storage, $sellerImage) {
            $sellerUpdateData = [
                'f_name' => $request['vendor_name'],
                'seller_type' => $request['seller_type'],
                'image' => $sellerImage,
                'status' => 'pending',
                'first_login_after_approval' => false,
            ];

            $seller->update($sellerUpdateData);

            VendorVerification::updateOrCreate(
                ['seller_id' => $seller->id],
                $verificationData
            );

            $shopModel = $shop ?: new Shop([
                'seller_id' => $seller->id,
                'slug' => Str::slug(Str::before($seller->email, '@') ?: 'vendor', '-') . '-' . Str::random(6),
                'bottom_banner' => 'def.png',
                'bottom_banner_storage_type' => $storage,
            ]);

            $shopModel->fill([
                'name' => $shopName,
                'address' => $shopAddress,
                'contact' => $shopContact,
                'email' => $shopEmail,
                'image' => $shopImage,
                'image_storage_type' => $storage,
                'banner' => $shopBanner,
                'banner_storage_type' => $storage,
            ]);

            $shopModel->save();
        });

        try {
            $sellerTypeLabel = ucfirst($request['seller_type']);
            event(new VendorRegistrationEvent(
                email: $seller->email,
                data: [
                    'name' => $seller->f_name,
                    'vendorName' => $seller->f_name,
                    'sellerType' => $sellerTypeLabel,
                    'seller_type' => $request['seller_type'],
                    'message' => 'Your documents have been submitted. Please wait for admin review.',
                    'status' => $verificationStatus,
                    'subject' => 'Documents Submitted',
                    'title' => 'Documents Submitted',
                    'userType' => 'vendor',
                    'templateName' => 'vendor-verification-submitted',
                ]
            ));
        } catch (\Throwable $e) {
            \Log::error('Vendor verification email failed: ' . $e->getMessage());
        }
    }

    private function getCountryOptions(): array
    {
        if (Schema::hasTable('countries')) {
            $nameColumn = Schema::hasColumn('countries', 'name')
                ? 'name'
                : (Schema::hasColumn('countries', 'country_name') ? 'country_name' : null);
            $codeColumn = Schema::hasColumn('countries', 'code')
                ? 'code'
                : (Schema::hasColumn('countries', 'country_code') ? 'country_code' : null);

            if ($nameColumn && $codeColumn) {
                return DB::table('countries')
                    ->select([
                        $nameColumn . ' as name',
                        $codeColumn . ' as code',
                    ])
                    ->orderBy($nameColumn)
                    ->get()
                    ->map(fn ($country) => [
                        'name' => $country->name,
                        'code' => strtoupper((string) $country->code),
                        'flag' => $this->getCountryFlag((string) $country->code),
                    ])
                    ->toArray();
            }

            return DB::table('countries')
                ->get()
                ->map(fn ($country) => [
                    'name' => $country->name ?? $country->country_name ?? '',
                    'code' => strtoupper((string) ($country->code ?? $country->country_code ?? '')),
                    'flag' => $this->getCountryFlag((string) ($country->code ?? $country->country_code ?? '')),
                ])
                ->filter(fn ($country) => !empty($country['name']))
                ->values()
                ->toArray();
        }

        return collect(COUNTRIES)
            ->map(fn (array $country) => [
                'name' => $country['name'],
                'code' => strtoupper((string) $country['code']),
                'flag' => $this->getCountryFlag((string) $country['code']),
            ])
            ->toArray();
    }

    private function safeMimeExtension(\Illuminate\Http\UploadedFile $file): string
    {
        $allowed = [
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/webp'      => 'webp',
            'application/pdf' => 'pdf',
        ];
        $mime = $file->getMimeType();
        return $allowed[$mime] ?? 'bin';
    }

    private function getCountryFlag(string $code): string
    {
        $code = strtoupper(substr($code, 0, 2));

        if (strlen($code) !== 2) {
            return '';
        }

        $flag = '';
        foreach (str_split($code) as $character) {
            $flag .= mb_chr(127397 + ord($character), 'UTF-8');
        }

        return $flag;
    }
}
