@extends('layouts.vendor.app')

@section('title', 'Vendor Verification')

@push('css_or_js')
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

        .approved-detail-row {
            border-bottom: 1px solid #eef2f7;
            padding: 14px 0;
        }

        .approved-detail-row:last-child {
            border-bottom: 0;
        }
    </style>
@endpush

@section('content')
@php($shopLogoUrl = !empty($shop?->image) && $shop?->image !== 'def.png' ? getStorageImages(path: $shop?->image_full_url, type: 'backend-basic') : null)
<div class="content container-fluid verification-shell py-4">
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="card verification-card mb-4">
                <div class="verification-header p-4 p-md-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="badge bg-white text-dark px-3 py-2">{{ translate('vendor_Verification') }}</div>
                                <div class="badge bg-success px-3 py-2">
                                    <i class="tio-checkmark-circle mr-1"></i>{{ translate('approved') }}
                                </div>
                            </div>
                            <h1 class="mb-2 text-white">{{ translate('Your_company_is_verified') }}</h1>
                            <p class="mb-0 text-white-50">{{ translate('Your_verification_details_are_shown_below_for_reference') }}</p>
                        </div>
                        <div class="col-lg-4">
                            <div class="bg-white rounded-4 p-3 p-md-4 shadow-sm text-dark">
                                @if($shopLogoUrl)
                                    <img src="{{ $shopLogoUrl }}" alt="{{ $shop?->name }}" class="rounded-circle mb-3" width="56" height="56" style="object-fit: cover;">
                                @endif
                                <div class="text-muted fs-12 mb-1">{{ translate('registered_email') }}</div>
                                <div class="fw-semibold mb-3">{{ $verification->personal_email ?? $seller?->email }}</div>
                                <div class="text-muted fs-12 mb-1">{{ translate('phone') }}</div>
                                <div class="fw-semibold">{{ $verification->personal_contact ?? $seller?->phone }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">
                    @if(!empty($verification->approval_note))
                        <div class="alert alert-success d-flex align-items-start gap-2 mb-4">
                            <i class="tio-checkmark-circle mt-1"></i>
                            <div>
                                <strong>{{ translate('approval_note') }}:</strong> {{ $verification->approval_note }}
                            </div>
                        </div>
                    @endif

                    <h5 class="mb-3">{{ translate('verified_details') }}</h5>

                    <div class="approved-detail-row d-flex justify-content-between flex-wrap gap-2">
                        <span class="text-muted">{{ translate('seller_type') }}</span>
                        <span class="fw-semibold text-capitalize">{{ $verification->seller_type }}</span>
                    </div>

                    @if($verification->seller_type === 'company')
                        <div class="approved-detail-row d-flex justify-content-between flex-wrap gap-2">
                            <span class="text-muted">{{ translate('company_name') }}</span>
                            <span class="fw-semibold">{{ $shop?->name }}</span>
                        </div>
                        <div class="approved-detail-row d-flex justify-content-between flex-wrap gap-2">
                            <span class="text-muted">{{ translate('company_address') }}</span>
                            <span class="fw-semibold">{{ $shop?->address }}</span>
                        </div>
                        <div class="approved-detail-row d-flex justify-content-between flex-wrap gap-2">
                            <span class="text-muted">{{ translate('company_email') }}</span>
                            <span class="fw-semibold">{{ $shop?->email }}</span>
                        </div>
                        <div class="approved-detail-row d-flex justify-content-between flex-wrap gap-2">
                            <span class="text-muted">{{ translate('company_phone') }}</span>
                            <span class="fw-semibold">{{ $shop?->contact }}</span>
                        </div>
                        @if(!empty($verification->company_website))
                            <div class="approved-detail-row d-flex justify-content-between flex-wrap gap-2">
                                <span class="text-muted">{{ translate('company_website') }}</span>
                                <span class="fw-semibold">{{ $verification->company_website }}</span>
                            </div>
                        @endif
                        @if(!empty($verification->company_registered_country))
                            <div class="approved-detail-row d-flex justify-content-between flex-wrap gap-2">
                                <span class="text-muted">{{ translate('registered_country') }}</span>
                                <span class="fw-semibold">{{ $verification->company_registered_country }}</span>
                            </div>
                        @endif
                    @else
                        <div class="approved-detail-row d-flex justify-content-between flex-wrap gap-2">
                            <span class="text-muted">{{ translate('full_name') }}</span>
                            <span class="fw-semibold">{{ $verification->personal_name }}</span>
                        </div>
                        <div class="approved-detail-row d-flex justify-content-between flex-wrap gap-2">
                            <span class="text-muted">{{ translate('personal_email') }}</span>
                            <span class="fw-semibold">{{ $verification->personal_email }}</span>
                        </div>
                        <div class="approved-detail-row d-flex justify-content-between flex-wrap gap-2">
                            <span class="text-muted">{{ translate('personal_contact') }}</span>
                            <span class="fw-semibold">{{ $verification->personal_contact }}</span>
                        </div>
                    @endif

                    @if($verification->reviewed_at)
                        <div class="approved-detail-row d-flex justify-content-between flex-wrap gap-2">
                            <span class="text-muted">{{ translate('reviewed_on') }}</span>
                            <span class="fw-semibold">{{ $verification->reviewed_at->format('d M Y, h:i A') }}</span>
                        </div>
                    @endif

                    <div class="mt-4 pt-3 border-top d-flex flex-wrap gap-2">
                        <a href="{{ route('vendor.profile.index') }}" class="btn btn-outline-primary">
                            <i class="tio-account-circle-outlined mr-1"></i>{{ translate('go_to_profile_settings') }}
                        </a>
                        <a href="{{ route('vendor.dashboard.index') }}" class="btn btn-outline-secondary">
                            <i class="tio-home-vs-1-outlined mr-1"></i>{{ translate('back_to_dashboard') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
