@extends('layouts.admin.app')

@section('title', $seller?->shop->name ?? translate("shop_Name"))

@section('content')
    @php
        $verification = $seller->vendorVerification;
        $displaySellerType = $verification?->seller_type ?? $seller?->seller_type;
        $companyLicenseUrl = !empty($verification?->company_license) ? dynamicStorage(path: 'storage/app/public/vendor-verifications/' . $verification->company_license) : null;
        $personalIdUrl = !empty($verification?->personal_id_document) ? dynamicStorage(path: 'storage/app/public/vendor-verifications/' . $verification->personal_id_document) : null;
        $sellerImageUrl = !empty($seller?->image) && $seller?->image !== 'def.png' ? getStorageImages(path: $seller?->image_full_url, type: 'backend-basic') : null;
        $shopLogoUrl = !empty($seller?->shop?->image) && $seller?->shop?->image !== 'def.png' ? getStorageImages(path: $seller?->shop?->image_full_url, type: 'backend-basic') : null;
        $shopBannerUrl = !empty($seller?->shop?->banner) && $seller?->shop?->banner !== 'def.png' ? getStorageImages(path: $seller?->shop?->banner_full_url, type: 'backend-basic') : null;
    @endphp

    <style>
        .document-tile {
            display: block;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
            transition: transform .2s ease, box-shadow .2s ease;
            text-decoration: none;
        }

        a.document-tile:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, .12);
        }

        .document-tile-preview {
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            overflow: hidden;
        }

        .document-tile-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: transform .3s ease;
        }

        a.document-tile:hover .document-tile-preview img {
            transform: scale(1.06);
        }

        .document-tile-preview .fi {
            font-size: 48px;
            color: #adb5bd;
        }

        .document-tile-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 12px 14px;
            border-top: 1px solid #e5e7eb;
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
        }

        .document-tile-empty {
            border: 1px dashed #d1d5db;
            border-radius: 12px;
            min-height: 262px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #9ca3af;
            background: #f9fafb;
        }

        .document-tile-empty .fi {
            font-size: 36px;
        }
    </style>

    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png') }}" alt="">
                {{ translate('vendor_details') }}
            </h2>
        </div>

        <div class="page-header border-0 mb-4">
            <div class="position-relative nav--tab-wrapper">
                <ul class="nav nav-pills nav--tab">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.vendors.view', $seller['id']) }}">{{ translate('shop_overview') }}</a>
                    </li>
                    @if ($seller['status'] != "pending")
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.vendors.view', ['id' => $seller['id'], 'tab' => 'order']) }}">{{ translate('order') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.vendors.view', ['id' => $seller['id'], 'tab' => 'product']) }}">{{ translate('product') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.vendors.view', ['id' => $seller['id'], 'tab' => 'clearance_sale']) }}">{{ translate('discount_sale') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.vendors.view', ['id' => $seller['id'], 'tab' => 'setting']) }}">{{ translate('setting') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.vendors.view', ['id' => $seller['id'], 'tab' => 'transaction']) }}">{{ translate('transaction') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.vendors.view', ['id' => $seller['id'], 'tab' => 'review']) }}">{{ translate('review') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('tab') == 'tier_plan' ? 'active' : '' }}" href="{{ route('admin.vendors.view', ['id' => $seller['id'], 'tab' => 'tier_plan']) }}">{{ translate('Vendor_Tier_Plan') }}</a>
                        </li>
                    @endif
                </ul>
                <div class="nav--tab__prev">
                    <button class="btn btn-circle border-0 bg-white text-primary">
                        <i class="fi fi-sr-angle-left"></i>
                    </button>
                </div>
                <div class="nav--tab__next">
                    <button class="btn btn-circle border-0 bg-white text-primary">
                        <i class="fi fi-sr-angle-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="card card-top-bg-element mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3 justify-content-between align-items-start">
                    <div class="media flex-column flex-sm-row gap-3">
                        <img width="170" class="rounded-circle object-fit-cover aspect-1" src="{{ getStorageImages(path: $seller?->shop->image_full_url, type: 'backend-basic') }}" alt="{{ translate('image') }}">
                        <div class="media-body">
                            <div class="d-flex gap-2 flex-wrap mb-3">
                                @if($seller?->shop->temporary_close)
                                    <span class="badge badge-soft-danger px-3 py-2 fs-12">
                                        <i class="fi fi-rr-lock me-1"></i>{{ translate('temporarily_closed') }}
                                    </span>
                                @elseif($seller?->shop->vacation_status && $current_date >= date('Y-m-d', strtotime($seller?->shop->vacation_start_date)) && $current_date <= date('Y-m-d', strtotime($seller?->shop->vacation_end_date)))
                                    <span class="badge badge-soft-warning px-3 py-2 fs-12">
                                        <i class="fi fi-rr-clock me-1"></i>{{ translate('on_vacation') }}
                                    </span>
                                @else
                                    <span class="badge badge-soft-success px-3 py-2 fs-12">
                                        <i class="fi fi-rr-check-circle me-1"></i>{{ translate('shop_open') }}
                                    </span>
                                @endif
                            </div>

                            <div class="d-block">
                                <h2 class="mb-2 pb-1">{{ $seller->shop ? $seller->shop->name : translate("shop_Name") . ": " . translate("update_Please") }}</h2>

                                <div class="d-flex gap-3 flex-wrap mb-3 lh-1">
                                    <div class="review-hover position-relative cursor-pointer d-flex gap-2 align-items-center">
                                        <i class="fi fi-sr-star"></i>
                                        <span>{{ round($seller->average_rating, 1) }}</span>
                                        <div class="review-details-popup">
                                            <h6 class="mb-2">{{ translate('rating') }}</h6>
                                            <ul class="list-unstyled list-unstyled-py-2 mb-0">
                                                <li class="d-flex align-items-center font-size-sm">
                                                    <span class="me-3">{{ '5' . ' ' . translate('star') }}</span>
                                                    <div class="progress flex-grow-1">
                                                        <div class="progress-bar width--100" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="ml-3">{{ $seller->single_rating_5 }}</span>
                                                </li>
                                                <li class="d-flex align-items-center font-size-sm">
                                                    <span class="me-3">{{ '4' . ' ' . translate('star') }}</span>
                                                    <div class="progress flex-grow-1">
                                                        <div class="progress-bar width--80" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="ml-3">{{ $seller->single_rating_4 }}</span>
                                                </li>
                                                <li class="d-flex align-items-center font-size-sm">
                                                    <span class="me-3">{{ '3' . ' ' . translate('star') }}</span>
                                                    <div class="progress flex-grow-1">
                                                        <div class="progress-bar width--60" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="ml-3">{{ $seller->single_rating_3 }}</span>
                                                </li>
                                                <li class="d-flex align-items-center font-size-sm">
                                                    <span class="me-3">{{ '2' . ' ' . translate('star') }}</span>
                                                    <div class="progress flex-grow-1">
                                                        <div class="progress-bar width--40" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="ml-3">{{ $seller->single_rating_2 }}</span>
                                                </li>
                                                <li class="d-flex align-items-center font-size-sm">
                                                    <span class="me-3">{{ '1' . ' ' . translate('star') }}</span>
                                                    <div class="progress flex-grow-1">
                                                        <div class="progress-bar width--20" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="ml-3">{{ $seller->single_rating_1 }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <span class="border-start"></span>
                                    <a href="javascript:" class="text-dark">{{ $seller->total_rating }} {{ translate('ratings') }}</a>
                                    <span class="border-start"></span>
                                    <a href="{{ $seller['status'] != "pending" ? route('admin.vendors.view', ['id' => $seller['id'], 'tab' => 'review']) : 'javascript:' }}" class="text-dark">{{ $seller->rating_count }} {{ translate('reviews') }}</a>
                                </div>

                                @if($seller['status'] != "pending" && $seller['status'] != "rejected" && $seller->account_status === 'active' && $seller?->shop)
                                    <a href="{{ route('shopView', ['id' => $seller?->shop['id']]) }}" class="btn btn-outline-primary" target="_blank">
                                        <i class="fi fi-rr-globe"></i>
                                        {{ translate('view_live') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-sm-end flex-wrap gap-2 mb-3">
                        @if($seller->vendorVerification)
                            <div class="d-flex flex-column gap-2 align-items-sm-end">
                                <div>
                                    @if($verification->status === 'pending')
                                        <span class="badge badge-warning text-dark">Pending</span>
                                    @elseif($verification->status === 'resubmitted')
                                        <span class="badge bg-warning text-dark">Resubmitted</span>
                                    @elseif($verification->status === 'approved')
                                        <span class="badge badge-success text-bg-success">Approved</span>
                                    @else
                                        <span class="badge badge-danger text-bg-danger">Rejected</span>
                                    @endif
                                </div>
                                <div class="d-flex gap-2 flex-wrap justify-content-sm-end">
                                    @if($verification->status !== 'approved')
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#vendorVerificationApproveModal">
                                            {{ translate('approve') }}
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#vendorVerificationRejectModal">
                                        {{ translate('reject') }}
                                    </button>
                                </div>
                            </div>
                        @else
                            <span class="badge badge-warning text-dark">Verification not submitted</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="mb-3 text-capitalize">{{ translate('shop_information') }}</h4>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted mb-1">{{ translate('shop_name') }}</div>
                                    <div class="fw-semibold">{{ $seller?->shop->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted mb-1">{{ translate('phone') }}</div>
                                    <div class="fw-semibold">{{ $seller?->shop->contact }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted mb-1">{{ translate('address') }}</div>
                                    <div class="fw-semibold">{{ $seller?->shop->address }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted mb-1">{{ translate('status') }}</div>
                                    <div>
                                        <span class="badge badge-{{ $seller['status'] == 'approved' ? 'info' : 'danger' }} text-bg-{{ $seller['status'] == 'approved' ? 'info' : 'danger' }}">
                                            {{ $seller['status'] == 'approved' ? translate('active') : translate('inactive') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted mb-1">{{ translate('account_suspension') }}</div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge badge-{{ $seller->account_status == 'active' ? 'success' : 'danger' }} text-bg-{{ $seller->account_status == 'active' ? 'success' : 'danger' }}">
                                            {{ $seller->account_status == 'active' ? translate('unsuspend') : translate('suspended') }}
                                        </span>
                                        <form action="{{ route('admin.vendors.account-status') }}" method="POST" class="d-inline vendor-status-toggle-form" novalidate>
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $seller->id }}">
                                            <input type="hidden" name="account_status" value="{{ $seller->account_status === 'active' ? 'inactive' : 'active' }}">
                                            <button type="submit" class="btn btn-sm {{ $seller->account_status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                {{ $seller->account_status === 'active' ? translate('suspend') : translate('reactivate') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted mb-1">{{ translate('shop_image') }}</div>
                                    <img class="img-fluid rounded" src="{{ getStorageImages(path: $seller?->shop->image_full_url, type: 'backend-basic') }}" alt="{{ translate('image') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
                            <h4 class="mb-0 text-capitalize">{{ translate('verification_information') }}</h4>
                            @if($verification)
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editVendorCredentialsModal">
                                    Edit Credentials
                                </button>
                            @endif
                        </div>

                        @if($verification)
                            <div class="row g-3">
                                <div class="col-md-6 col-xl-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted mb-1">{{ translate('status') }}</div>
                                        <div>
                                            <span class="badge badge-{{ $verification->status === 'approved' ? 'success' : ($verification->status === 'rejected' ? 'danger' : 'warning text-dark') }}">
                                                {{ ucfirst($verification->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted mb-1">{{ translate('seller_type') }}</div>
                                        <div class="fw-semibold text-capitalize">
                                            {{ $displaySellerType === 'company' ? 'Company' : ($displaySellerType === 'individual' ? 'Individual' : ($displaySellerType === 'freelancer' ? 'Freelancer' : translate('not_specified'))) }}
                                        </div>
                                    </div>
                                </div>

                                @if($displaySellerType === 'company')
                                    <div class="col-12">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-header bg-white border-0 pb-0">
                                                <h5 class="mb-0 text-capitalize">Company Information</h5>
                                            </div>
                                            <div class="card-body pt-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <div class="border rounded p-3 h-100">
                                                            <div class="text-muted mb-1">{{ translate('company_website') }}</div>
                                                            <div class="fw-semibold text-break">{{ $verification->company_website ?? translate('no_data_found') }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="border rounded p-3 h-100">
                                                            <div class="text-muted mb-1">{{ translate('company_registration_no') }}</div>
                                                            <div class="fw-semibold text-break">{{ $verification->company_no ?? translate('no_data_found') }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="border rounded p-3 h-100">
                                                            <div class="text-muted mb-1">{{ translate('company_registered_country') }}</div>
                                                            <div class="fw-semibold text-break">{{ $verification->company_registered_country ?? translate('no_data_found') }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <hr class="my-2">
                                                        <h6 class="text-uppercase text-muted fw-bold mb-3">{{ translate('documents') }}</h6>
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        @include('admin-views.vendor.view.partials._document-tile', [
                                                            'label' => translate('company_license'),
                                                            'required' => true,
                                                            'url' => $companyLicenseUrl,
                                                            'rawFilename' => $verification->company_license ?? null,
                                                        ])
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        @include('admin-views.vendor.view.partials._document-tile', [
                                                            'label' => 'Vendor Image',
                                                            'url' => $sellerImageUrl,
                                                        ])
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        @include('admin-views.vendor.view.partials._document-tile', [
                                                            'label' => 'Logo',
                                                            'url' => $shopLogoUrl,
                                                        ])
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        @include('admin-views.vendor.view.partials._document-tile', [
                                                            'label' => 'Banner',
                                                            'url' => $shopBannerUrl,
                                                        ])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif(in_array($displaySellerType, ['individual', 'freelancer'], true))
                                    <div class="col-12">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-header bg-white border-0 pb-0">
                                                <h5 class="mb-0 text-capitalize">Individual Information</h5>
                                            </div>
                                            <div class="card-body pt-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6 mb-4">
                                                        @include('admin-views.vendor.view.partials._document-tile', [
                                                            'label' => 'Personal ID / Passport',
                                                            'required' => true,
                                                            'url' => $personalIdUrl,
                                                            'rawFilename' => $verification->personal_id_document ?? null,
                                                        ])
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        @include('admin-views.vendor.view.partials._document-tile', [
                                                            'label' => 'Vendor Image',
                                                            'url' => $sellerImageUrl,
                                                        ])
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        @include('admin-views.vendor.view.partials._document-tile', [
                                                            'label' => 'Logo',
                                                            'url' => $shopLogoUrl,
                                                        ])
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        @include('admin-views.vendor.view.partials._document-tile', [
                                                            'label' => 'Banner',
                                                            'url' => $shopBannerUrl,
                                                        ])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="col-12">
                                        <div class="alert alert-warning mb-0">
                                            {{ translate('seller_type_is_not_specified') }}
                                        </div>
                                    </div>
                                @endif

                                @if($verification?->status === 'rejected' && $verification?->rejection_reason)
                                    <div class="col-12">
                                        <div class="alert alert-danger mb-0">
                                            <strong>{{ translate('rejection_reason') }}:</strong> {{ $verification->rejection_reason }}
                                            @if($verification->reviewed_at)
                                                <div class="small mt-1">{{ translate('reviewed_at') }}: {{ $verification->reviewed_at->format('M d, Y h:i A') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($verification?->status === 'approved' && $verification?->approval_note)
                                    <div class="col-12">
                                        <div class="alert alert-success mb-0">
                                            <strong>{{ translate('approval_note') }}:</strong> {{ $verification->approval_note }}
                                            @if($verification->reviewed_at)
                                                <div class="small mt-1">{{ translate('reviewed_at') }}: {{ $verification->reviewed_at->format('M d, Y h:i A') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                Verification not submitted.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="mb-3 text-capitalize">{{ translate('vendor_information') }}</h4>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted mb-1">{{ translate('name') }}</div>
                                    <div class="fw-semibold text-capitalize">{{ $seller['f_name'] . ' ' . $seller['l_name'] }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted mb-1">{{ translate('email') }}</div>
                                    <div class="fw-semibold">{{ $seller['email'] }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted mb-1">{{ translate('phone') }}</div>
                                    <div class="fw-semibold">{{ $seller['phone'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($seller['status'] != "pending")
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-body">
                            <h4 class="mb-3 text-capitalize">{{ translate('bank_information') }}</h4>
                            <div class="row g-3">
                                <div class="col-md-6 col-xl-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted mb-1">{{ translate('bank_name') }}</div>
                                        <div class="fw-semibold">{{ $seller['bank_name'] ?? translate('no_data_found') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted mb-1">{{ translate('branch') }}</div>
                                        <div class="fw-semibold">{{ $seller['branch'] ?? translate('no_data_found') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted mb-1">{{ translate('holder_name') }}</div>
                                        <div class="fw-semibold">{{ $seller['holder_name'] ?? translate('no_data_found') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="text-muted mb-1">{{ translate('A/C_No') }}</div>
                                        <div class="fw-semibold">{{ $seller['account_no'] ?? translate('no_data_found') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if ($seller['status'] != "pending")
            <div class="card mt-3">
                <div class="card-body">
                    <div class="row justify-content-between align-items-center g-2 mb-3">
                        <div class="col-sm-6">
                            <h3 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                                <img width="20" class="mb-1" src="{{ dynamicAsset(path: 'public/assets/back-end/img/admin-wallet.png') }}" alt="">
                                {{ translate('vendor_Wallet') }}
                            </h3>
                        </div>
                    </div>

                    <div class="row g-2" id="order_stats">
                        <div class="col-lg-4">
                            <div class="border rounded h-100 d-flex justify-content-center align-items-center">
                                <div class="card-body d-flex flex-column gap-10 align-items-center justify-content-center">
                                    <img width="48" class="mb-2" src="{{ dynamicAsset(path: 'public/assets/back-end/img/withdraw.png') }}" alt="">
                                    <h3 class="for-card-count mb-0 fz-24">{{ $seller->wallet ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $seller->wallet->total_earning)) : 0 }}</h3>
                                    <div class="font-weight-bold text-capitalize mb-30">{{ translate('withdrawable_balance') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="border rounded card-body h-100 justify-content-center">
                                        <div class="d-flex gap-2 justify-content-between align-items-center">
                                            <div class="d-flex flex-column align-items-start">
                                                <h3 class="mb-1 fz-24">{{ $seller->wallet ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $seller->wallet->pending_withdraw)) : 0 }}</h3>
                                                <div class="text-capitalize mb-0">{{ translate('pending_Withdraw') }}</div>
                                            </div>
                                            <div>
                                                <img width="40" class="mb-2" src="{{ dynamicAsset(path: 'public/assets/back-end/img/pw.png') }}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded card-body h-100 justify-content-center">
                                        <div class="d-flex gap-2 justify-content-between align-items-center">
                                            <div class="d-flex flex-column align-items-start">
                                                <h3 class="mb-1 fz-24">{{ $seller->wallet ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $seller->wallet->commission_given)) : 0 }}</h3>
                                                <div class="text-capitalize mb-0">{{ translate('total_Commission_given') }}</div>
                                            </div>
                                            <div>
                                                <img width="40" src="{{ dynamicAsset(path: 'public/assets/back-end/img/tcg.png') }}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded card-body h-100 justify-content-center">
                                        <div class="d-flex gap-2 justify-content-between align-items-center">
                                            <div class="d-flex flex-column align-items-start">
                                                <h3 class="mb-1 fz-24">{{ $seller->wallet ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $seller->wallet->withdrawn)) : 0 }}</h3>
                                                <div class="text-capitalize mb-0">{{ translate('already_Withdrawn') }}</div>
                                            </div>
                                            <div>
                                                <img width="40" src="{{ dynamicAsset(path: 'public/assets/back-end/img/aw.png') }}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded card-body h-100 justify-content-center">
                                        <div class="d-flex gap-2 justify-content-between align-items-center">
                                            <div class="d-flex flex-column align-items-start">
                                                <h3 class="mb-1 fz-24">{{ $seller->wallet ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $seller->wallet->delivery_charge_earned)) : 0 }}</h3>
                                                <div class="text-capitalize mb-0">{{ translate('total_delivery_charge_earned') }}</div>
                                            </div>
                                            <div>
                                                <img width="40" src="{{ dynamicAsset(path: 'public/assets/back-end/img/tdce.png') }}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded card-body h-100 justify-content-center">
                                        <div class="d-flex gap-2 justify-content-between align-items-center">
                                            <div class="d-flex flex-column align-items-start">
                                                <h3 class="mb-1 fz-24">{{ $seller->wallet ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $seller->wallet->total_tax_collected)) : 0 }}</h3>
                                                <div class="text-capitalize mb-0">{{ translate('total_tax_given') }}</div>
                                            </div>
                                            <div>
                                                <img width="40" src="{{ dynamicAsset(path: 'public/assets/back-end/img/ttg.png') }}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded card-body h-100 justify-content-center">
                                        <div class="d-flex gap-2 justify-content-between align-items-center">
                                            <div class="d-flex flex-column align-items-start">
                                                <h3 class="mb-1 fz-24">{{ $seller->wallet ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $seller->wallet->collected_cash)) : 0 }}</h3>
                                                <div class="text-capitalize mb-0">{{ translate('collected_cash') }}</div>
                                            </div>
                                            <div>
                                                <img width="40" src="{{ dynamicAsset(path: 'public/assets/back-end/img/cc.png') }}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        </div>

        @if($verification)
            @php($currentSellerType = old('seller_type', $verification->seller_type ?? $seller->seller_type))
            <div class="modal fade" id="editVendorCredentialsModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <form action="{{ route('admin.vendors.vendor-verifications.update', $verification->id) }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column" style="overflow: hidden; min-height: 0;" novalidate>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="open_edit_credentials" value="1">

                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title mb-1">Edit Credentials</h5>
                                    <div class="text-muted fs-12">Update vendor profile details and documents without changing the review history.</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="px-4 pt-3">
                                <label class="form-label fw-semibold">{{ translate('seller_type') }}</label>
                                <select name="seller_type" class="form-select">
                                    <option value="">-- {{ translate('select_type') }} --</option>
                                    <option value="freelancer" {{ $currentSellerType === 'freelancer' ? 'selected' : '' }}>{{ translate('Freelancer') }}</option>
                                    <option value="individual" {{ $currentSellerType === 'individual' ? 'selected' : '' }}>{{ translate('Individual') }}</option>
                                    <option value="company" {{ $currentSellerType === 'company' ? 'selected' : '' }}>{{ translate('Company') }}</option>
                                </select>
                            </div>

                            <div class="modal-body" style="min-height: 0; overflow-y: auto;">
                                @if($currentSellerType === 'company')
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                                            <input type="text" name="vendor_name" class="form-control" value="{{ old('vendor_name', $seller?->f_name) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $seller?->shop?->name) }}" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Company Address <span class="text-danger">*</span></label>
                                            <textarea name="company_address" class="form-control" rows="3" required>{{ old('company_address', $seller?->shop?->address) }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Company Website <span class="text-danger">*</span></label>
                                            <input type="text" name="company_website" class="form-control" value="{{ old('company_website', $verification?->company_website) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Company Registration No <span class="text-danger">*</span></label>
                                            <input type="text" name="company_no" class="form-control" value="{{ old('company_no', $verification?->company_no) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Company Phone <span class="text-danger">*</span></label>
                                            <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $seller?->shop?->contact) }}" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Company Registered Country <span class="text-danger">*</span></label>
                                            <select name="company_registered_country" class="form-select js-country-select" data-placeholder="Select a country" required>
                                                <option value=""></option>
                                                @foreach(COUNTRIES as $country)
                                                    <option value="{{ $country['name'] }}" data-code="{{ $country['code'] }}" {{ old('company_registered_country', $verification?->company_registered_country) === $country['name'] ? 'selected' : '' }}>
                                                        {{ $country['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Company License <span class="text-danger">*</span></label>
                                            <input type="file" name="company_license" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" {{ $companyLicenseUrl ? '' : 'required' }}>
                                            <div class="mt-3">
                                                @if($companyLicenseUrl)
                                                    @if(str_ends_with(strtolower($verification->company_license), '.pdf'))
                                                        <a href="{{ $companyLicenseUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                                            Download PDF
                                                        </a>
                                                    @else
                                                        <a href="{{ $companyLicenseUrl }}" target="_blank" rel="noopener noreferrer">
                                                            <div class="rounded border d-inline-flex align-items-center justify-content-center bg-light" style="width: 120px; height: 120px; overflow: hidden;">
                                                                <img src="{{ $companyLicenseUrl }}" alt="Company License" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                            </div>
                                                        </a>
                                                    @endif
                                                @else
                                                    <div class="border rounded p-3 text-muted">No image selected</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Vendor Image</label>
                                            <input type="file" name="vendor_image" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff">
                                            <div class="mt-3">
                                                @if($sellerImageUrl)
                                                    <div class="rounded border d-inline-flex align-items-center justify-content-center bg-light" style="width: 120px; height: 120px; overflow: hidden;">
                                                        <img src="{{ $sellerImageUrl }}" alt="Vendor image" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                    </div>
                                                @else
                                                    <div class="border rounded p-3 text-muted">No image selected</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Logo</label>
                                            <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff">
                                            <div class="mt-3">
                                                @if($shopLogoUrl)
                                                    <div class="rounded border d-inline-flex align-items-center justify-content-center bg-light" style="width: 120px; height: 120px; overflow: hidden;">
                                                        <img src="{{ $shopLogoUrl }}" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                    </div>
                                                @else
                                                    <div class="border rounded p-3 text-muted">No image selected</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Banner</label>
                                            <input type="file" name="banner" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff">
                                            <div class="mt-3">
                                                @if($shopBannerUrl)
                                                    <div class="rounded border d-inline-flex align-items-center justify-content-center bg-light" style="width: 100%; max-width: 240px; height: 120px; overflow: hidden;">
                                                        <img src="{{ $shopBannerUrl }}" alt="Banner" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                    </div>
                                                @else
                                                    <div class="border rounded p-3 text-muted">No image selected</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @elseif(in_array($currentSellerType, ['individual', 'freelancer'], true))
                                    <div class="row g-3">
                                        <input type="hidden" name="vendor_name" value="{{ old('vendor_name', $seller?->f_name) }}">
                                        <div class="col-md-6">
                                            <label class="form-label">Personal ID / Passport <span class="text-danger">*</span></label>
                                            <input type="file" name="personal_id_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" {{ $personalIdUrl ? '' : 'required' }}>
                                            <div class="mt-3">
                                                @if($personalIdUrl)
                                                    @if(str_ends_with(strtolower($verification->personal_id_document), '.pdf'))
                                                        <a href="{{ $personalIdUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                                            Download PDF
                                                        </a>
                                                    @else
                                                        <a href="{{ $personalIdUrl }}" target="_blank" rel="noopener noreferrer">
                                                            <div class="rounded border d-inline-flex align-items-center justify-content-center bg-light" style="width: 120px; height: 120px; overflow: hidden;">
                                                                <img src="{{ $personalIdUrl }}" alt="Personal ID" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                            </div>
                                                        </a>
                                                    @endif
                                                @else
                                                    <div class="border rounded p-3 text-muted">No image selected</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Vendor Image</label>
                                            <input type="file" name="vendor_image" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff">
                                            <div class="mt-3">
                                                @if($sellerImageUrl)
                                                    <div class="rounded border d-inline-flex align-items-center justify-content-center bg-light" style="width: 120px; height: 120px; overflow: hidden;">
                                                        <img src="{{ $sellerImageUrl }}" alt="Vendor image" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                    </div>
                                                @else
                                                    <div class="border rounded p-3 text-muted">No image selected</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Logo</label>
                                            <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff">
                                            <div class="mt-3">
                                                @if($shopLogoUrl)
                                                    <div class="rounded border d-inline-flex align-items-center justify-content-center bg-light" style="width: 120px; height: 120px; overflow: hidden;">
                                                        <img src="{{ $shopLogoUrl }}" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                    </div>
                                                @else
                                                    <div class="border rounded p-3 text-muted">No image selected</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Banner</label>
                                            <input type="file" name="banner" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff">
                                            <div class="mt-3">
                                                @if($shopBannerUrl)
                                                    <div class="rounded border d-inline-flex align-items-center justify-content-center bg-light" style="width: 100%; max-width: 240px; height: 120px; overflow: hidden;">
                                                        <img src="{{ $shopBannerUrl }}" alt="Banner" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                    </div>
                                                @else
                                                    <div class="border rounded p-3 text-muted">No image selected</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        Seller type is not specified.
                                    </div>
                                @endif
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Update Credentials</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    @if($verification && $verification->status !== 'approved')
        <div class="modal fade" id="vendorVerificationApproveModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('admin.vendors.vendor-verifications.approve', $verification->id) }}" method="POST" novalidate>
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ translate('want_to_approve_this_vendor') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">{{ translate('approval_note') }}</label>
                            <textarea name="approval_note" class="form-control" rows="4" maxlength="1000" placeholder="{{ translate('optional_note_for_vendor') }}"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('cancel') }}</button>
                            <button type="submit" class="btn btn-success">{{ translate('approve') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($verification)
        <div class="modal fade" id="vendorVerificationRejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('admin.vendors.vendor-verifications.reject', $verification->id) }}" method="POST" novalidate>
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ translate('want_to_reject_this_vendor') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">{{ translate('rejection_reason') }}</label>
                            <textarea name="rejection_reason" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('cancel') }}</button>
                            <button type="submit" class="btn btn-danger">{{ translate('reject') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @push('script')
        <script>
            (function () {
                const codeToFlag = (code) => {
                    const normalized = (code || '').toString().toUpperCase().slice(0, 2);
                    if (normalized.length !== 2) {
                        return '';
                    }

                    return normalized.split('').map((char) => String.fromCodePoint(127397 + char.charCodeAt(0))).join('');
                };

                function initCountrySelect() {
                    if (typeof $.fn.select2 !== 'function') {
                        return;
                    }

                    $('.js-country-select').each(function () {
                        const $select = $(this);
                        if ($select.hasClass('select2-hidden-accessible')) {
                            $select.select2('destroy');
                        }

                        $select.select2({
                            width: '100%',
                            dropdownParent: $select.closest('.modal').length ? $select.closest('.modal') : $(document.body),
                            placeholder: $select.data('placeholder') || 'Select country',
                            templateResult: function (country) {
                                if (!country.id) {
                                    return country.text;
                                }

                                const code = $(country.element).data('code');
                                const flag = codeToFlag(code);
                                return $('<span class="d-flex align-items-center gap-2"><span style="min-width: 1.5rem;">' + flag + '</span><span>' + country.text + '</span></span>');
                            },
                            templateSelection: function (country) {
                                if (!country.id) {
                                    return country.text;
                                }

                                const code = $(country.element).data('code');
                                const flag = codeToFlag(code);
                                return $('<span class="d-flex align-items-center gap-2"><span style="min-width: 1.5rem;">' + flag + '</span><span>' + country.text + '</span></span>');
                            },
                            escapeMarkup: function (markup) {
                                return markup;
                            }
                        });
                    });
                }

                document.addEventListener('DOMContentLoaded', function () {
                    initCountrySelect();

                    const editModal = document.getElementById('editVendorCredentialsModal');
                    if (editModal && {{ $errors->any() && old('open_edit_credentials') ? 'true' : 'false' }}) {
                        $(editModal).modal('show');
                    }
                });
            })();
        </script>
        <script>
            $(".vendor-status-toggle-form").on("submit", function (e) {
                e.preventDefault();
                let form = this;
                let activating = $(form).find('input[name="account_status"]').val() === 'active';
                Swal.fire({
                    title: activating ? "Reactivate this vendor's account?" : "Suspend this vendor's account?",
                    text: activating
                        ? "The vendor will be able to log in and their products will show on the site again."
                        : "The vendor will not be able to log in or do anything, and their products will be hidden from the site until reactivated.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: activating ? "#198754" : "#dc3545",
                    cancelButtonColor: "#d33",
                    reverseButtons: true,
                }).then((result) => {
                    if (result.value) {
                        form.submit();
                    }
                });
            });
        </script>
    @endpush
@endsection
