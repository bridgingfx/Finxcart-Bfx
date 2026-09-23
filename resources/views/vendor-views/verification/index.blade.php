@extends('layouts.vendor.app')

@section('title', 'Vendor Verification')

@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}">
    <style>
        .verification-shell {
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 24%),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 22%),
                linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
            min-height: calc(100vh - 130px);
        }

        .verification-card {
            border: 0;
            border-radius: 28px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .10);
            overflow: hidden;
        }

        .verification-header {
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
            color: #fff;
        }

        .card-option {
            position: relative;
            border: 1px solid #d6deea;
            border-radius: 22px;
            background: #fff;
            padding: 22px;
            height: 100%;
            cursor: pointer;
            transition: all .22s ease;
            min-height: 140px;
        }

        .card-option:hover {
            transform: translateY(-2px);
            border-color: #93c5fd;
            box-shadow: 0 16px 34px rgba(15, 23, 42, .08);
        }

        .card-option.selected {
            background: #eff6ff;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .08);
        }

        .card-option .choice-check {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 28px;
            height: 28px;
            border-radius: 999px;
            background: #2563eb;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: scale(.85);
            transition: all .2s ease;
        }

        .card-option.selected .choice-check {
            opacity: 1;
            transform: scale(1);
        }

        .option-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .option-icon.company {
            color: #2563eb;
            background: rgba(37, 99, 235, .12);
        }

        .option-icon.individual {
            color: #d97706;
            background: rgba(245, 158, 11, .14);
        }

        .option-icon.freelancer {
            color: #0891b2;
            background: rgba(8, 145, 178, .14);
        }

        .upload-box {
            border: 1px dashed #cbd5e1;
            border-radius: 20px;
            background: #fff;
            padding: 18px;
            height: 100%;
            transition: all .2s ease;
        }

        .upload-box:hover {
            border-color: #94a3b8;
            box-shadow: 0 10px 26px rgba(15, 23, 42, .05);
        }

        .upload-preview {
            width: 100%;
            min-height: 180px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #f8fafc, #eef2ff);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .upload-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .upload-placeholder {
            color: #64748b;
            text-align: center;
            padding: 24px;
        }

        .file-chip {
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(148, 163, 184, 0.35);
            max-width: calc(100% - 28px);
            text-align: center;
            word-break: break-word;
        }

        .field-section {
            display: none;
        }

        .field-section.active {
            display: block;
        }

        .country-select + .select2-container {
            width: 100% !important;
        }

        .country-select + .select2-container .select2-selection--single {
            height: calc(2.75rem + 2px);
            border: 1px solid #d1d9e6;
            border-radius: 14px;
            display: flex;
            align-items: center;
        }

        .country-select + .select2-container .select2-selection__rendered {
            line-height: 1.2;
            padding-left: 14px;
            padding-right: 30px;
            color: #1f2937;
        }

        .country-select + .select2-container .select2-selection__arrow {
            height: 100%;
            right: 8px;
        }

        .verification-header {
            position: relative;
            background: linear-gradient(135deg, #073b74 0%, #0b579b 58%, #1684cf 100%);
        }

        .verification-header::after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            right: -90px;
            bottom: -150px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .09);
        }

        .verification-account-card {
            position: relative;
            z-index: 1;
            border-radius: 20px !important;
            border: 1px solid rgba(255, 255, 255, .45);
            background: #fff !important;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .verification-account-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 35px rgba(3, 37, 72, .22) !important;
        }

        .verification-progress-panel {
            padding: 18px;
            border: 1px solid #e3edf7;
            border-radius: 18px;
            background: linear-gradient(135deg, #f7fbff, #eef6ff);
        }

        .verification-progress-track {
            height: 9px;
            overflow: hidden;
            border-radius: 999px;
            background: #dce9f5;
        }

        .verification-progress-bar {
            height: 100%;
            width: 0;
            border-radius: inherit;
            background: linear-gradient(90deg, #073b74, #1684cf);
            transition: width .35s ease;
        }

        .verification-step {
            height: 100%;
            padding: 14px;
            border: 1px solid #e5edf6;
            border-radius: 15px;
            background: #fff;
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }

        .verification-step:hover {
            transform: translateY(-2px);
            border-color: rgba(7, 59, 116, .25);
            box-shadow: 0 10px 24px rgba(7, 59, 116, .07);
        }

        .verification-step-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            background: linear-gradient(135deg, #073b74, #1684cf);
            flex-shrink: 0;
        }

        .verification-step.complete {
            border-color: #b8ead1;
            background: #f2fcf7;
        }

        .verification-step.complete .verification-step-icon {
            background: linear-gradient(135deg, #07864c, #20b86a);
        }

        .verification-step.optional {
            background: #f8fafc;
        }

        .verification-step-action {
            color: #0b579b;
            font-size: 12px;
            font-weight: 700;
        }

        .card-option:disabled {
            cursor: default;
            opacity: .62;
        }

        .card-option.selected:disabled {
            opacity: 1;
        }

        .card-option.selected {
            background: #edf7ff;
            border-color: #0b579b;
            box-shadow: 0 0 0 4px rgba(7, 59, 116, .08);
        }

        .card-option .choice-check {
            background: #073b74;
        }

        .upload-box:hover {
            transform: translateY(-2px);
            border-color: #1684cf;
            box-shadow: 0 12px 28px rgba(7, 59, 116, .08);
        }

        .field-section.active {
            animation: verificationSectionIn .25s ease;
        }

        .field-section > .card {
            border: 1px solid #e4edf6 !important;
            border-radius: 20px;
            box-shadow: 0 14px 34px rgba(7, 59, 116, .07) !important;
        }

        .field-section .form-label {
            color: #28445f;
            font-weight: 700;
        }

        .field-section .form-control,
        .field-section .form-select {
            border-color: #d7e3ee;
            border-radius: 12px;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .field-section .form-control:focus,
        .field-section .form-select:focus {
            transform: translateY(-1px);
            border-color: #1684cf;
            box-shadow: 0 0 0 4px rgba(22, 132, 207, .1);
        }

        .verification-submit-panel {
            position: sticky;
            bottom: 14px;
            z-index: 5;
            padding: 14px;
            border: 1px solid rgba(7, 59, 116, .12);
            border-radius: 16px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 14px 34px rgba(7, 59, 116, .12);
            backdrop-filter: blur(10px);
        }

        .verification-submit-panel .btn {
            border: 0;
            background: linear-gradient(135deg, #073b74, #1684cf);
            box-shadow: 0 8px 20px rgba(7, 59, 116, .2);
        }

        @keyframes verificationSectionIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 767px) {
            .verification-card { border-radius: 18px; }
            .verification-header { padding: 24px !important; }
            .card-option { min-height: auto; padding: 17px; }
            .verification-submit-panel { bottom: 8px; }
            .verification-submit-panel .btn { width: 100%; }
        }
    </style>
@endpush

@section('content')
    @php($seller = auth('seller')->user())
    @php($shop = $seller?->shop)
    @php($verification = $seller?->vendorVerification)
    @php($kycMethod = 'manual')
    @php($verificationStatus = $verification?->status ?? 'unsubmitted')
    @php($initialType = old('seller_type', $seller?->seller_type ?? $verification?->seller_type ?? ''))
    @php($sellerTypeLocked = filled($seller?->seller_type))
    @php($profileComplete = filled($seller?->f_name) && filled($seller?->email) && filled($seller?->phone) && filled($seller?->image) && $seller?->image !== 'def.png')
    @php($companyVerificationComplete = $verificationStatus === 'approved')
    @php($payoutKycComplete = $kycMethod === 'disabled' || ($seller?->kyc_status ?? null) === 'approved')
    @php($bankInformationComplete = $seller?->seller_type === 'freelancer' || (filled($seller?->bank_name) && filled($seller?->account_no)))
    @php($setupCompletedCount = collect([$profileComplete, $companyVerificationComplete, $payoutKycComplete, $bankInformationComplete])->filter()->count())
    @php($setupProgress = $setupCompletedCount * 25)
    @php($companyLicenseUrl = !empty($verification?->company_license) ? dynamicStorage(path: 'storage/app/public/vendor-verifications/' . $verification->company_license) : null)
    @php($personalIdUrl = !empty($verification?->personal_id_document) ? dynamicStorage(path: 'storage/app/public/vendor-verifications/' . $verification->personal_id_document) : null)
    @php($seller->imageUrl = !empty($seller?->image) && $seller?->image !== 'def.png' ? getStorageImages(path: $seller?->image_full_url, type: 'backend-basic') : null)
    @php($shopLogoUrl = !empty($shop?->image) && $shop?->image !== 'def.png' ? getStorageImages(path: $shop?->image_full_url, type: 'backend-basic') : null)
    @php($shopBannerUrl = !empty($shop?->banner) && $shop?->banner !== 'def.png' ? getStorageImages(path: $shop?->banner_full_url, type: 'backend-basic') : null)

    <div class="verification-shell py-4 py-md-5">
        <div class="container py-2 py-md-4">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">
                    <div class="verification-card card">
                        <div class="verification-header p-4 p-md-5">
                            <div class="row g-4 align-items-center">
                                <div class="col-lg-7">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                        <div class="badge bg-white text-primary px-3 py-2">Vendor Verification</div>
                                        <div class="badge bg-white text-dark px-3 py-2">{{ ucfirst(str_replace('_', ' ', $verificationStatus)) }}</div>
                                    </div>
                                    <h1 class="mb-2 text-white">{{ $verificationStatus === 'approved' ? 'Review or update your verified company profile.' : 'Complete your company profile to get verified.' }}</h1>
                                    <p class="mb-0 text-white-50">{{ $verificationStatus === 'approved' ? 'Your current details remain visible below. Submitting changes will send them for admin review.' : 'Choose your seller type, complete your details, and upload the required documents in one place.' }}</p>
                                </div>
                                <div class="col-lg-5">
                                    <div class="verification-account-card bg-white rounded-4 p-4 shadow-sm">
                                        <div class="text-muted fs-12 mb-1">Registered email</div>
                                        <div class="fw-semibold text-dark mb-3">{{ $seller?->email }}</div>
                                        <div class="text-muted fs-12 mb-1">Phone</div>
                                        <div class="fw-semibold text-dark mb-3">{{ $seller?->phone }}</div>
                                        <a href="{{ route('vendor.profile.update', auth('seller')->id()) }}?tab=company-verification" class="btn btn-outline-primary w-100">
                                            <i class="tio-user-outlined mr-1"></i> Profile Settings
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4 p-md-5">
                            <div class="verification-progress-panel mb-4">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                    <div>
                                        <h4 class="mb-1"><i class="tio-task text-primary mr-1"></i>Account setup progress</h4>
                                        <div class="text-muted fs-12">This progress uses your real approval, profile, payout KYC, and bank-information status.</div>
                                    </div>
                                    <strong class="text-primary">{{ $setupProgress }}% complete</strong>
                                </div>
                                <div class="verification-progress-track mb-3">
                                    <div class="verification-progress-bar" style="width: {{ $setupProgress }}%"></div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="verification-step {{ $profileComplete ? 'complete' : '' }} d-flex align-items-center gap-3">
                                            <span class="verification-step-icon"><i class="tio-user-outlined"></i></span>
                                            <div class="flex-grow-1">
                                                <strong>Profile information</strong>
                                                <div class="text-muted fs-12">{{ $profileComplete ? 'Completed' : 'Add your name, phone, and profile image.' }}</div>
                                                @unless($profileComplete)<a class="verification-step-action" href="{{ route('vendor.profile.update', $seller->id) }}">Complete profile <i class="tio-chevron-right"></i></a>@endunless
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="verification-step {{ $companyVerificationComplete ? 'complete' : '' }} d-flex align-items-center gap-3">
                                            <span class="verification-step-icon"><i class="tio-verified"></i></span>
                                            <div class="flex-grow-1">
                                                <strong>Company verification</strong>
                                                <div class="text-muted fs-12">{{ $companyVerificationComplete ? 'Approved' : ucfirst(str_replace('_', ' ', $verificationStatus)) }}</div>
                                                @unless($companyVerificationComplete)<a class="verification-step-action" href="#seller-type-section">Complete verification <i class="tio-chevron-right"></i></a>@endunless
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="verification-step {{ $payoutKycComplete ? 'complete' : '' }} d-flex align-items-center gap-3">
                                            <span class="verification-step-icon"><i class="tio-security-on"></i></span>
                                            <div class="flex-grow-1">
                                                <strong>Payout KYC</strong>
                                                <div class="text-muted fs-12">{{ $kycMethod === 'disabled' ? 'Not required' : ucfirst(str_replace('_', ' ', $seller?->kyc_status ?? 'unsubmitted')) }}</div>
                                                @unless($payoutKycComplete)<a class="verification-step-action" href="{{ route('vendor.payout-kyc.index') }}">Complete payout KYC <i class="tio-chevron-right"></i></a>@endunless
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="verification-step {{ $bankInformationComplete ? 'complete' : '' }} {{ $seller?->seller_type === 'freelancer' ? 'optional' : '' }} d-flex align-items-center gap-3">
                                            <span class="verification-step-icon"><i class="tio-credit-card-outlined"></i></span>
                                            <div class="flex-grow-1">
                                                <strong>Bank information</strong>
                                                <div class="text-muted fs-12">{{ $seller?->seller_type === 'freelancer' ? 'Optional for freelancer accounts' : ($bankInformationComplete ? 'Completed' : 'Required before selecting a paid plan.') }}</div>
                                                @if(!$bankInformationComplete || $seller?->seller_type === 'freelancer')<a class="verification-step-action" href="{{ route('vendor.profile.update-bank-info', $seller->id) }}">{{ $bankInformationComplete ? 'View bank information' : 'Add bank information' }} <i class="tio-chevron-right"></i></a>@endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if($verification?->status === 'rejected' && $verification?->rejection_reason)
                                <div class="alert alert-danger border-0 shadow-sm mb-4">
                                    <strong>Rejection reason:</strong> {{ $verification->rejection_reason }}
                                    @if($verification->reviewed_at)
                                        <div class="small mt-1">Reviewed on {{ $verification->reviewed_at->format('M d, Y h:i A') }}</div>
                                    @endif
                                </div>
                            @endif

                            @if($verification?->status === 'approved' && $verification?->approval_note)
                                <div class="alert alert-success border-0 shadow-sm mb-4">
                                    <strong>Approval note:</strong> {{ $verification->approval_note }}
                                    @if($verification->reviewed_at)
                                        <div class="small mt-1">Reviewed on {{ $verification->reviewed_at->format('M d, Y h:i A') }}</div>
                                    @endif
                                </div>
                            @endif

                            @if($verificationStatus === 'approved' && $kycMethod !== 'disabled' && $kycMethod !== 'sumsub')
                                <div class="alert alert-success border-0 shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-3 mb-0">
                                    <span><i class="tio-checkmark-circle mr-1"></i>Your company profile is verified. No action needed.</span>
                                    <button type="button" class="btn btn-sm btn-outline-success" data-toggle="collapse" data-target="#verification-update-section">
                                        <i class="tio-edit mr-1"></i>Update Details
                                    </button>
                                </div>
                                <div class="collapse mt-4" id="verification-update-section">
                                    <div class="alert alert-warning border-0 shadow-sm mb-4">Submitting changes will send your profile back for admin review.</div>
                            @endif

                            @if($kycMethod === 'disabled')
                                <div class="alert alert-info border-0 shadow-sm mb-0">
                                    <strong>KYC is not required for your account.</strong>
                                    <div class="small mt-1">You can continue from your dashboard.</div>
                                </div>
                            @elseif($kycMethod === 'sumsub')
                                <div class="alert alert-info border-0 shadow-sm mb-4">
                                    <strong>Automated KYC is enabled.</strong>
                                    <div class="small mt-1">Complete the identity check in the secure verification widget below.</div>
                                </div>
                                <div id="sumsub-websdk-container" class="border rounded bg-light" style="min-height:600px;"></div>
                                <div id="sumsub-websdk-error" class="alert alert-danger border-0 shadow-sm mt-3 d-none"></div>
                            @else
                            <form action="{{ route('vendor.verification.store') }}" method="POST" enctype="multipart/form-data" id="vendor-verification-form" novalidate>
                                @csrf
                                <input type="hidden" name="return_to" value="{{ request('return_to') }}">
                                <input type="hidden" name="seller_type" id="seller-type-input" value="{{ $initialType }}">

                                <div class="mb-4" id="seller-type-section">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <i class="fi fi-rr-users-alt text-primary"></i>
                                        <div>
                                            <h4 class="mb-0">{{ $sellerTypeLocked ? 'Registered Seller Type' : 'Select Seller Type' }}</h4>
                                            @if($sellerTypeLocked)<div class="text-muted fs-12 mt-1"><i class="tio-lock-outlined mr-1"></i>Seller type is fixed after registration. Contact admin if this needs correction.</div>@endif
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <button type="button" class="card-option verification-choice w-100 text-start" data-type="company" {{ $sellerTypeLocked ? 'disabled' : '' }}>
                                                <span class="choice-check"><i class="fi fi-rr-check"></i></span>
                                                <div class="d-flex gap-3 align-items-start">
                                                    <div class="option-icon company">
                                                        <i class="fi fi-rr-building"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-1">Company</h5>
                                                        <p class="mb-0 text-muted">Best for businesses with company documents and branded storefront details.</p>
                                                    </div>
                                                </div>
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="button" class="card-option verification-choice w-100 text-start" data-type="individual" {{ $sellerTypeLocked ? 'disabled' : '' }}>
                                                <span class="choice-check"><i class="fi fi-rr-check"></i></span>
                                                <div class="d-flex gap-3 align-items-start">
                                                    <div class="option-icon individual">
                                                        <i class="fi fi-rr-user"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-1">Individual</h5>
                                                        <p class="mb-0 text-muted">Best for solo sellers who want to verify with a personal ID or passport.</p>
                                                    </div>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="company-section" class="field-section">
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center gap-2 mb-4">
                                                <i class="fi fi-rr-briefcase text-primary"></i>
                                                <h4 class="mb-0">Company Profile</h4>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-lg-6">
                                                    <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-lg" name="vendor_name" value="{{ old('vendor_name', $seller?->f_name) }}" placeholder="Vendor name">
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-lg" name="company_name" value="{{ old('company_name', $shop?->name) }}" placeholder="Company name">
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Company Address <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" rows="3" name="company_address" placeholder="Company address">{{ old('company_address', $shop?->address) }}</textarea>
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Company Email <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control form-control-lg" name="company_email" value="{{ old('company_email', $shop?->email) }}" placeholder="info@example.com">
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Company Phone <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-lg" name="company_phone" value="{{ old('company_phone', $shop?->contact) }}" placeholder="+971 50 000 0000">
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Company Website <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-lg" name="company_website" value="{{ old('company_website', $verification?->company_website) }}" placeholder="https://example.com">
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Company Registration No <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-lg" name="company_no" value="{{ old('company_no', $verification?->company_no) }}" placeholder="Registration number">
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Company Registered Country <span class="text-danger">*</span></label>
                                                    <select class="form-select country-select" name="company_registered_country" data-placeholder="Select a country" required>
                                                        <option value=""></option>
                                                        @foreach($countries as $country)
                                                            <option value="{{ $country['name'] }}"
                                                                    data-flag="{{ $country['flag'] }}"
                                                                    {{ old('company_registered_country', $verification?->company_registered_country) === $country['name'] ? 'selected' : '' }}>
                                                                {{ $country['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Company License <span class="text-danger">*</span></label>
                                                    <input type="file" class="d-none" id="company-license-input" name="company_license" accept=".pdf,.jpg,.jpeg,.png,.webp" {{ $companyLicenseUrl ? '' : 'required' }}>
                                                    <div class="upload-box">
                                                        <div class="upload-preview mb-3" data-preview="company-license">
                                                            @if($companyLicenseUrl)
                                                                @if(str_ends_with(strtolower($verification->company_license), '.pdf'))
                                                                    <div class="file-chip">
                                                                        <i class="fi fi-rr-file-pdf d-block fs-3 mb-1"></i>
                                                                        <div class="fw-semibold">{{ $verification->company_license }}</div>
                                                                    </div>
                                                                @else
                                                                    <img src="{{ $companyLicenseUrl }}" alt="Company license">
                                                                @endif
                                                            @else
                                                                <div class="upload-placeholder">
                                                                    <i class="fi fi-rr-file fs-2 d-block mb-2"></i>
                                                                    <div>No file selected</div>
                                                                    <small>PDF or image files accepted | Max 5MB</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                            <div>
                                                                <div class="fw-semibold">Upload company license</div>
                                                                <div class="text-muted fs-12">PDF or image files accepted | Max 5MB</div>
                                                            </div>
                                                            <label class="btn btn-outline-primary mb-0" for="company-license-input">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Vendor Image <span class="text-danger">*</span></label>
                                                    <input type="file" class="d-none" id="company-vendor-image-input" name="vendor_image" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff" {{ $seller->imageUrl ? '' : 'required' }}>
                                                    <div class="upload-box">
                                                        <div class="upload-preview mb-3" data-preview="company-vendor-image">
                                                            @if($seller->imageUrl)
                                                                <img src="{{ $seller->imageUrl }}" alt="Vendor image">
                                                            @else
                                                                <div class="upload-placeholder">
                                                                    <i class="fi fi-rr-user fs-2 d-block mb-2"></i>
                                                                    <div>No file selected</div>
                                                                    <small>JPG, PNG accepted | Max 5MB</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                            <div>
                                                                <div class="fw-semibold">Upload vendor image</div>
                                                                <div class="text-muted fs-12">JPG, PNG accepted | Max 5MB</div>
                                                            </div>
                                                            <label class="btn btn-outline-primary mb-0" for="company-vendor-image-input">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Logo <span class="text-danger">*</span></label>
                                                    <input type="file" class="d-none" id="company-logo-input" name="logo" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff" {{ $shopLogoUrl ? '' : 'required' }}>
                                                    <div class="upload-box">
                                                        <div class="upload-preview mb-3" data-preview="company-logo">
                                                            @if($shopLogoUrl)
                                                                <img src="{{ $shopLogoUrl }}" alt="Logo">
                                                            @else
                                                                <div class="upload-placeholder">
                                                                    <i class="fi fi-rr-image fs-2 d-block mb-2"></i>
                                                                    <div>No file selected</div>
                                                                    <small>JPG, PNG accepted | Max 5MB</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                            <div>
                                                                <div class="fw-semibold">Upload logo</div>
                                                                <div class="text-muted fs-12">PNG or JPG files accepted | Max 5MB</div>
                                                            </div>
                                                            <label class="btn btn-outline-primary mb-0" for="company-logo-input">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Banner <span class="text-danger">*</span></label>
                                                    <input type="file" class="d-none" id="company-banner-input" name="banner" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff" {{ $shopBannerUrl ? '' : 'required' }}>
                                                    <div class="upload-box">
                                                        <div class="upload-preview mb-3" data-preview="company-banner">
                                                            @if($shopBannerUrl)
                                                                <img src="{{ $shopBannerUrl }}" alt="Banner">
                                                            @else
                                                                <div class="upload-placeholder">
                                                                    <i class="fi fi-rr-panorama fs-2 d-block mb-2"></i>
                                                                    <div>No file selected</div>
                                                                    <small>JPG, PNG accepted | Max 5MB</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                            <div>
                                                                <div class="fw-semibold">Upload banner</div>
                                                                <div class="text-muted fs-12">JPG, PNG accepted | Max 5MB</div>
                                                            </div>
                                                            <label class="btn btn-outline-primary mb-0" for="company-banner-input">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="individual-section" class="field-section">
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center gap-2 mb-4">
                                                <i class="fi fi-rr-id-badge text-warning"></i>
                                                <h4 class="mb-0">Individual Profile</h4>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-lg-6">
                                                    <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-lg" name="vendor_name" value="{{ old('vendor_name', $seller?->f_name) }}" placeholder="Vendor name">
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Personal Email <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control form-control-lg" name="personal_email" value="{{ old('personal_email', $verification?->personal_email ?? $seller?->email) }}" placeholder="you@example.com">
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Personal Contact <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-lg" name="personal_contact" value="{{ old('personal_contact', $verification?->personal_contact ?? $seller?->phone) }}" placeholder="+971 50 000 0000">
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Personal ID / Passport <span class="text-danger">*</span></label>
                                                    <input type="file" class="d-none" id="personal-id-input" name="personal_id_document" accept=".pdf,.jpg,.jpeg,.png,.webp" {{ $personalIdUrl ? '' : 'required' }}>
                                                    <div class="upload-box">
                                                        <div class="upload-preview mb-3" data-preview="personal-id">
                                                            @if($personalIdUrl)
                                                                @if(str_ends_with(strtolower($verification->personal_id_document), '.pdf'))
                                                                    <div class="file-chip">
                                                                        <i class="fi fi-rr-file-pdf d-block fs-3 mb-1"></i>
                                                                        <div class="fw-semibold">{{ $verification->personal_id_document }}</div>
                                                                    </div>
                                                                @else
                                                                    <img src="{{ $personalIdUrl }}" alt="Personal ID">
                                                                @endif
                                                            @else
                                                                <div class="upload-placeholder">
                                                                    <i class="fi fi-rr-file fs-2 d-block mb-2"></i>
                                                                    <div>No file selected</div>
                                                                    <small>PDF or image accepted | Max 5MB</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                            <div>
                                                                <div class="fw-semibold">Upload personal ID</div>
                                                                <div class="text-muted fs-12">PDF or image accepted | Max 5MB</div>
                                                            </div>
                                                            <label class="btn btn-outline-primary mb-0" for="personal-id-input">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Vendor Image <span class="text-danger">*</span></label>
                                                    <input type="file" class="d-none" id="individual-vendor-image-input" name="vendor_image" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff" {{ $seller->imageUrl ? '' : 'required' }}>
                                                    <div class="upload-box">
                                                        <div class="upload-preview mb-3" data-preview="individual-vendor-image">
                                                            @if($seller->imageUrl)
                                                                <img src="{{ $seller->imageUrl }}" alt="Vendor image">
                                                            @else
                                                                <div class="upload-placeholder">
                                                                    <i class="fi fi-rr-user fs-2 d-block mb-2"></i>
                                                                    <div>No file selected</div>
                                                                    <small>JPG, PNG accepted | Max 5MB</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                            <div>
                                                                <div class="fw-semibold">Upload vendor image</div>
                                                                <div class="text-muted fs-12">JPG, PNG accepted | Max 5MB</div>
                                                            </div>
                                                            <label class="btn btn-outline-primary mb-0" for="individual-vendor-image-input">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Logo <span class="text-danger">*</span></label>
                                                    <input type="file" class="d-none" id="individual-logo-input" name="logo" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff" {{ $shopLogoUrl ? '' : 'required' }}>
                                                    <div class="upload-box">
                                                        <div class="upload-preview mb-3" data-preview="individual-logo">
                                                            @if($shopLogoUrl)
                                                                <img src="{{ $shopLogoUrl }}" alt="Logo">
                                                            @else
                                                                <div class="upload-placeholder">
                                                                    <i class="fi fi-rr-image fs-2 d-block mb-2"></i>
                                                                    <div>No file selected</div>
                                                                    <small>JPG, PNG accepted | Max 5MB</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                            <div>
                                                                <div class="fw-semibold">Upload logo</div>
                                                                <div class="text-muted fs-12">PNG or JPG files accepted | Max 5MB</div>
                                                            </div>
                                                            <label class="btn btn-outline-primary mb-0" for="individual-logo-input">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Banner <span class="text-danger">*</span></label>
                                                    <input type="file" class="d-none" id="individual-banner-input" name="banner" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff" {{ $shopBannerUrl ? '' : 'required' }}>
                                                    <div class="upload-box">
                                                        <div class="upload-preview mb-3" data-preview="individual-banner">
                                                            @if($shopBannerUrl)
                                                                <img src="{{ $shopBannerUrl }}" alt="Banner">
                                                            @else
                                                                <div class="upload-placeholder">
                                                                    <i class="fi fi-rr-panorama fs-2 d-block mb-2"></i>
                                                                    <div>No file selected</div>
                                                                    <small>JPG, PNG accepted | Max 5MB</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                            <div>
                                                                <div class="fw-semibold">Upload banner</div>
                                                                <div class="text-muted fs-12">JPG, PNG accepted | Max 5MB</div>
                                                            </div>
                                                            <label class="btn btn-outline-primary mb-0" for="individual-banner-input">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="freelancer-section" class="field-section">
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center gap-2 mb-3">
                                                <i class="fi fi-rr-briefcase text-info"></i>
                                                <h4 class="mb-0">Freelancer Profile</h4>
                                            </div>
                                            <div class="alert alert-info d-flex gap-2 align-items-start">
                                                <i class="fi fi-rr-info mt-1"></i>
                                                <div>Freelancer accounts are free, but listings stay locked until admin approves your KYC documents.</div>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-lg-6">
                                                    <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-lg" name="vendor_name" value="{{ old('vendor_name', $seller?->f_name) }}" placeholder="Vendor name">
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Personal Email <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control form-control-lg" name="personal_email" value="{{ old('personal_email', $verification?->personal_email ?? $seller?->email) }}" placeholder="you@example.com">
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Personal Contact <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-lg" name="personal_contact" value="{{ old('personal_contact', $verification?->personal_contact ?? $seller?->phone) }}" placeholder="+971 50 000 0000">
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Personal ID / Passport <span class="text-danger">*</span></label>
                                                    <input type="file" class="d-none" id="freelancer-personal-id-input" name="personal_id_document" accept=".pdf,.jpg,.jpeg,.png,.webp" {{ ($personalIdUrl && $verification?->seller_type === 'freelancer') ? '' : 'required' }}>
                                                    <div class="upload-box">
                                                        <div class="upload-preview mb-3" data-preview="freelancer-personal-id">
                                                            @if($personalIdUrl && $verification?->seller_type === 'freelancer')
                                                                @if(str_ends_with(strtolower($verification->personal_id_document), '.pdf'))
                                                                    <div class="file-chip">
                                                                        <i class="fi fi-rr-file-pdf d-block fs-3 mb-1"></i>
                                                                        <div class="fw-semibold">{{ $verification->personal_id_document }}</div>
                                                                    </div>
                                                                @else
                                                                    <img src="{{ $personalIdUrl }}" alt="Personal ID">
                                                                @endif
                                                            @else
                                                                <div class="upload-placeholder">
                                                                    <i class="fi fi-rr-file fs-2 d-block mb-2"></i>
                                                                    <div>No file selected</div>
                                                                    <small>PDF or image accepted | Max 5MB</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                            <div>
                                                                <div class="fw-semibold">Upload personal ID</div>
                                                                <div class="text-muted fs-12">PDF or image accepted | Max 5MB</div>
                                                            </div>
                                                            <label class="btn btn-outline-primary mb-0" for="freelancer-personal-id-input">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Vendor Image <span class="text-danger">*</span></label>
                                                    <input type="file" class="d-none" id="freelancer-vendor-image-input" name="vendor_image" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff" {{ $seller->imageUrl ? '' : 'required' }}>
                                                    <div class="upload-box">
                                                        <div class="upload-preview mb-3" data-preview="freelancer-vendor-image">
                                                            @if($seller->imageUrl)
                                                                <img src="{{ $seller->imageUrl }}" alt="Vendor image">
                                                            @else
                                                                <div class="upload-placeholder">
                                                                    <i class="fi fi-rr-user fs-2 d-block mb-2"></i>
                                                                    <div>No file selected</div>
                                                                    <small>JPG, PNG accepted | Max 5MB</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                            <div>
                                                                <div class="fw-semibold">Upload vendor image</div>
                                                                <div class="text-muted fs-12">JPG, PNG accepted | Max 5MB</div>
                                                            </div>
                                                            <label class="btn btn-outline-primary mb-0" for="freelancer-vendor-image-input">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Logo <span class="text-danger">*</span></label>
                                                    <input type="file" class="d-none" id="freelancer-logo-input" name="logo" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff" {{ $shopLogoUrl ? '' : 'required' }}>
                                                    <div class="upload-box">
                                                        <div class="upload-preview mb-3" data-preview="freelancer-logo">
                                                            @if($shopLogoUrl)
                                                                <img src="{{ $shopLogoUrl }}" alt="Logo">
                                                            @else
                                                                <div class="upload-placeholder">
                                                                    <i class="fi fi-rr-image fs-2 d-block mb-2"></i>
                                                                    <div>No file selected</div>
                                                                    <small>JPG, PNG accepted | Max 5MB</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                            <div>
                                                                <div class="fw-semibold">Upload logo</div>
                                                                <div class="text-muted fs-12">PNG or JPG files accepted | Max 5MB</div>
                                                            </div>
                                                            <label class="btn btn-outline-primary mb-0" for="freelancer-logo-input">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Banner <span class="text-danger">*</span></label>
                                                    <input type="file" class="d-none" id="freelancer-banner-input" name="banner" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff" {{ $shopBannerUrl ? '' : 'required' }}>
                                                    <div class="upload-box">
                                                        <div class="upload-preview mb-3" data-preview="freelancer-banner">
                                                            @if($shopBannerUrl)
                                                                <img src="{{ $shopBannerUrl }}" alt="Banner">
                                                            @else
                                                                <div class="upload-placeholder">
                                                                    <i class="fi fi-rr-panorama fs-2 d-block mb-2"></i>
                                                                    <div>No file selected</div>
                                                                    <small>JPG, PNG accepted | Max 5MB</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                            <div>
                                                                <div class="fw-semibold">Upload banner</div>
                                                                <div class="text-muted fs-12">JPG, PNG accepted | Max 5MB</div>
                                                            </div>
                                                            <label class="btn btn-outline-primary mb-0" for="freelancer-banner-input">Choose File</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="verification-submit-panel d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-2 text-muted">
                                        <i class="tio-security-on text-primary fs-4"></i>
                                        <span class="fs-12">Your documents are securely submitted for admin review.</span>
                                    </div>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="tio-send mr-1"></i>{{ $verificationStatus === 'approved' ? 'Submit Updates for Review' : 'Submit Verification' }}
                                    </button>
                                </div>
                            </form>
                            @endif

                            @if($verificationStatus === 'approved' && $kycMethod !== 'disabled' && $kycMethod !== 'sumsub')
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{ dynamicAsset(path: 'public/assets/select2/js/select2.min.js') }}"></script>
    @if($kycMethod === 'sumsub')
        <script src="https://static.sumsub.com/idensic/static/sns-websdk-builder.js"></script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sellerTypeInput = document.getElementById('seller-type-input');
            const form = document.getElementById('vendor-verification-form');
            const companySection = document.getElementById('company-section');
            const individualSection = document.getElementById('individual-section');
            const freelancerSection = document.getElementById('freelancer-section');
            const options = document.querySelectorAll('.verification-choice');

            $('.country-select').select2({
                width: '100%',
                placeholder: function () {
                    return $(this).data('placeholder') || 'Select a country';
                },
                templateResult: function (country) {
                    if (!country.id) {
                        return country.text;
                    }

                    const flag = $(country.element).data('flag') || '';
                    return $('<span class="d-flex align-items-center gap-2">' +
                        '<span class="flex-shrink-0" style="min-width: 1.25rem;">' + flag + '</span>' +
                        '<span>' + country.text + '</span>' +
                    '</span>');
                },
                templateSelection: function (country) {
                    if (!country.id) {
                        return country.text;
                    }

                    const flag = $(country.element).data('flag') || '';
                    return $('<span class="d-flex align-items-center gap-2">' +
                        '<span class="flex-shrink-0" style="min-width: 1.25rem;">' + flag + '</span>' +
                        '<span>' + country.text + '</span>' +
                    '</span>');
                },
                escapeMarkup: function (markup) {
                    return markup;
                }
            });

            @if($kycMethod === 'sumsub')
                const sumsubErrorBox = document.getElementById('sumsub-websdk-error');
                const showSumsubError = function (message) {
                    if (!sumsubErrorBox) {
                        return;
                    }

                    sumsubErrorBox.classList.remove('d-none');
                    sumsubErrorBox.textContent = message;
                };
                const getNewAccessToken = function () {
                    return fetch('{{ route('vendor.kyc.token') }}')
                        .then(response => response.json().then(data => ({ ok: response.ok, data })))
                        .then(({ ok, data }) => {
                            if (!ok || !data.token) {
                                throw new Error(data.message || 'Unable to start Sumsub verification.');
                            }

                            return data.token;
                        });
                };

                getNewAccessToken()
                    .then(function (accessToken) {
                        if (typeof snsWebSdk === 'undefined') {
                            throw new Error('Sumsub WebSDK could not be loaded.');
                        }

                        snsWebSdk
                            .init(accessToken, getNewAccessToken)
                            .withConf({
                                lang: 'en',
                                email: @json($seller?->email),
                                phone: @json($seller?->phone),
                            })
                            .withOptions({ addViewportTag: false, adaptIframeHeight: true })
                            .on('idCheck.onApplicantStatusChanged', function (payload) {
                                if (payload.reviewStatus === 'completed') {
                                    window.location.reload();
                                }
                            })
                            .on('idCheck.onError', function () {
                                showSumsubError('There was a problem loading the verification widget. Please refresh and try again.');
                            })
                            .build()
                            .launch('#sumsub-websdk-container');
                    })
                    .catch(function (error) {
                        showSumsubError(error.message || 'Unable to start Sumsub verification.');
                    });
            @endif

            if (!form || !sellerTypeInput) {
                return;
            }

            const setRequired = (section, required) => {
                if (!section) {
                    return;
                }
                section.querySelectorAll('input, textarea, select').forEach((field) => {
                    field.disabled = !required;
                    if (field.type !== 'file') {
                        field.required = required;
                    }
                });
            };

            const showSection = (type) => {
                const isCompany = type === 'company';
                const isIndividual = type === 'individual';
                const isFreelancer = type === 'freelancer';

                companySection.classList.toggle('active', isCompany);
                individualSection.classList.toggle('active', isIndividual);
                freelancerSection.classList.toggle('active', isFreelancer);

                setRequired(companySection, isCompany);
                setRequired(individualSection, isIndividual);
                setRequired(freelancerSection, isFreelancer);

                options.forEach((option) => {
                    option.classList.toggle('selected', option.dataset.type === type);
                });

            };

            options.forEach((option) => {
                option.addEventListener('click', function () {
                    sellerTypeInput.value = this.dataset.type;
                    showSection(this.dataset.type);
                });
            });

            const initialType = sellerTypeInput.value || '';
            if (initialType) {
                showSection(initialType);
            } else {
                setRequired(companySection, false);
                setRequired(individualSection, false);
                setRequired(freelancerSection, false);
            }

            const fileInputs = [
                ['company-license-input', 'company-license'],
                ['company-vendor-image-input', 'company-vendor-image'],
                ['company-logo-input', 'company-logo'],
                ['company-banner-input', 'company-banner'],
                ['personal-id-input', 'personal-id'],
                ['individual-vendor-image-input', 'individual-vendor-image'],
                ['individual-logo-input', 'individual-logo'],
                ['individual-banner-input', 'individual-banner'],
                ['freelancer-personal-id-input', 'freelancer-personal-id'],
                ['freelancer-vendor-image-input', 'freelancer-vendor-image'],
                ['freelancer-logo-input', 'freelancer-logo'],
                ['freelancer-banner-input', 'freelancer-banner'],
            ];

            // company-license/personal-id can be PDFs and are compliance documents —
            // never re-encode those. The photo fields (vendor image/logo/banner) are
            // routinely 1-2MB+ marketing graphics with no server-side resize, so a large
            // banner turns a single submit into several seconds of GD encoding plus the
            // on-disk double-write in FileManagerTrait::upload(). Downscaling them in the
            // browser before they're ever uploaded is what actually shortens that wait.
            const pdfCapableInputs = new Set(['company-license-input', 'personal-id-input', 'freelancer-personal-id-input']);
            const MAX_IMAGE_DIMENSION = 1600;

            const resizeImageFile = (file, maxDim) => new Promise((resolve) => {
                if (!file.type.startsWith('image/') || file.type === 'image/gif') {
                    resolve(file);
                    return;
                }

                const objectUrl = URL.createObjectURL(file);
                const img = new Image();
                img.onload = function () {
                    const { width, height } = img;
                    if (width <= maxDim && height <= maxDim) {
                        URL.revokeObjectURL(objectUrl);
                        resolve(file);
                        return;
                    }

                    const scale = Math.min(maxDim / width, maxDim / height);
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.round(width * scale);
                    canvas.height = Math.round(height * scale);
                    canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                    URL.revokeObjectURL(objectUrl);

                    const outputType = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
                    canvas.toBlob((blob) => {
                        if (!blob) {
                            resolve(file);
                            return;
                        }
                        resolve(new File([blob], file.name, { type: outputType, lastModified: Date.now() }));
                    }, outputType, 0.85);
                };
                img.onerror = function () {
                    URL.revokeObjectURL(objectUrl);
                    resolve(file);
                };
                img.src = objectUrl;
            });

            const renderPreview = (preview, file) => {
                const reader = new FileReader();
                reader.onload = function (event) {
                    const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
                    const fileSize = file.size > 1024 * 1024
                        ? (file.size / (1024 * 1024)).toFixed(2) + ' MB'
                        : (file.size / 1024).toFixed(1) + ' KB';

                    if (isPdf) {
                        preview.innerHTML = '<div class=\'file-chip\'><i class=\'fi fi-rr-file-pdf d-block fs-3 mb-1\'></i><div class=\'fw-semibold\'>' + file.name + '</div><small class=\'text-muted\'>' + fileSize + '</small></div>';
                        return;
                    }

                    preview.innerHTML = '<div style=\'position:relative;width:100%;height:100%;\'><img src=\'' + event.target.result + '\' alt=\'' + file.name + '\' style=\'width:100%;height:100%;object-fit:cover;\'><div style=\'position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,0.6);color:#fff;padding:2px 8px;border-radius:8px;font-size:11px;\'>' + fileSize + '</div></div>';
                };
                reader.readAsDataURL(file);
            };

            fileInputs.forEach(([inputId, previewKey]) => {
                const input = document.getElementById(inputId);
                const preview = document.querySelector(`[data-preview="${previewKey}"]`);
                if (!input || !preview) {
                    return;
                }

                input.addEventListener('change', function () {
                    const file = this.files && this.files[0];
                    if (!file) return;

                    const maxSize = 5 * 1024 * 1024;
                    if (file.size > maxSize) {
                        if (window.toastMagic) {
                            window.toastMagic.error('File ' + file.name + ' exceeds 5MB limit. Please choose a smaller file.');
                        } else {
                            alert('File exceeds 5MB limit.');
                        }
                        this.value = '';
                        return;
                    }

                    const allowedTypes = ['image/jpeg','image/png','image/webp','image/gif','application/pdf'];
                    if (!allowedTypes.includes(file.type)) {
                        if (window.toastMagic) {
                            window.toastMagic.error('Invalid file type. Only JPG, PNG, WEBP, GIF and PDF are allowed.');
                        } else {
                            alert('Invalid file type.');
                        }
                        this.value = '';
                        return;
                    }

                    if (pdfCapableInputs.has(inputId)) {
                        renderPreview(preview, file);
                        return;
                    }

                    resizeImageFile(file, MAX_IMAGE_DIMENSION).then((resizedFile) => {
                        try {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(resizedFile);
                            this.files = dataTransfer.files;
                        } catch (e) {
                            // DataTransfer reassignment unsupported — submit the original file untouched.
                        }
                        renderPreview(preview, resizedFile);
                    });
                });
            });

            if (form) {
                form.addEventListener('submit', function (event) {
                    if (!sellerTypeInput.value) {
                        event.preventDefault();
                        if (window.toastMagic && typeof window.toastMagic.error === 'function') {
                            window.toastMagic.error('Please select a seller type.');
                        } else {
                            alert('Please select a seller type.');
                        }
                        return;
                    }

                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>Submitting...';
                    }
                });
            }
        });
    </script>
@endpush





