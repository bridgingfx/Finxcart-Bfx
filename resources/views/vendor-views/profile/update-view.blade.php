@extends('layouts.vendor.app')

@section('title', translate('profile_Settings'))
@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/css/intlTelInput.css') }}">
    <style>
        .verification-profile-card {
            border: 1px solid #e5edf6;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .verification-profile-card:hover {
            transform: translateY(-2px);
            border-color: rgba(7, 59, 116, .28);
            box-shadow: 0 14px 32px rgba(7, 59, 116, .08);
        }
        .verification-profile-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, #073b74, #1268b3);
            font-size: 22px;
            flex-shrink: 0;
        }
        .verification-detail {
            height: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            background: #f7faff;
            border: 1px solid #e8eff7;
        }
        .verification-detail-label {
            color: #7b8ba1;
            font-size: 12px;
            margin-bottom: 4px;
        }
        .verification-detail-value {
            color: #183153;
            font-weight: 600;
            word-break: break-word;
        }
        .verification-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 12px;
        }
        .verification-status-badge.approved { color: #087443; background: #e6f8ef; }
        .verification-status-badge.pending,
        .verification-status-badge.resubmitted { color: #8a5700; background: #fff3d6; }
        .verification-status-badge.rejected { color: #b42318; background: #feeceb; }
        .verification-status-badge.unsubmitted,
        .verification-status-badge.not_started { color: #52677d; background: #edf2f7; }
    </style>
@endpush
@section('content')
    @php($verification = $vendor?->vendorVerification)
    @php($shop = $vendor?->shop)
    @php($companyStatus = $verification?->status ?? ($vendor?->status === 'approved' ? 'approved' : 'unsubmitted'))
    @php($payoutKycStatus = $vendor?->kyc_status ?? 'unsubmitted')
    @php($kycMethod = getWebConfig('kyc_method') ?? 'manual')
    @php($companyDocument = $verification?->company_license ?: $verification?->personal_id_document)
    @php($companyDocumentUrl = $companyDocument ? dynamicStorage(path: 'storage/app/public/vendor-verifications/' . $companyDocument) : null)
    @php($payoutDocumentLink = $verification?->payout_kyc_document ? storageLink('vendor-verifications', $verification->payout_kyc_document, getWebConfig(name: 'storage_connection_type') ?? 'public') : null)
    <div class="content container-fluid">
        <div class="mb-3">
            <div class="row gy-2 align-items-center">
                <div class="col-sm">
                    <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                        <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/profile_setting.png') }}" alt="">
                        {{ translate('Profile_Information') }}
                    </h2>
                </div>
                <div class="col-sm-auto">
                    <a class="btn btn--primary" href="{{ route('vendor.dashboard.index') }}">
                        <i class="tio-home mr-1"></i> {{ translate('dashboard') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <div class="navbar-vertical navbar-expand-lg mb-3 mb-lg-5">
                    <button type="button" class="navbar-toggler btn btn-block btn-white mb-3"
                            aria-label="Toggle navigation" aria-expanded="false" aria-controls="navbarVerticalNavMenu"
                            data-toggle="collapse" data-target="#navbarVerticalNavMenu">
                        <span class="d-flex justify-content-between align-items-center">
                          <span class="h5 mb-0">{{ translate('nav_menu') }}</span>
                          <span class="navbar-toggle-default">
                            <i class="tio-menu-hamburger"></i>
                          </span>
                          <span class="navbar-toggle-toggled">
                            <i class="tio-clear"></i>
                          </span>
                        </span>
                    </button>

                    <div id="navbarVerticalNavMenu" class="collapse navbar-collapse">
                        <ul id="navbarSettings"
                            class="js-sticky-block js-scrollspy navbar-nav navbar-nav-lg nav-tabs card card-navbar-nav p-3">
                            <li class="nav-item">
                                <a class="nav-link active d-flex align-items-center gap-2 m-0 py-3" href="javascript:" id="general-section">
                                    <i class="tio-user-outlined nav-icon"></i>{{ translate('basic_Information') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-2 m-0 py-3" href="javascript:" id="password-section">
                                    <i class="tio-lock-outlined nav-icon"></i> {{ translate('password') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-2 m-0 py-3" href="#company-verification-div" id="company-verification-section">
                                    <i class="tio-verified nav-icon"></i> {{ translate('Company_Verification') }}
                                    <span class="badge badge-{{ $companyStatus === 'approved' ? 'success' : 'warning' }} ml-auto">{{ ucfirst($companyStatus) }}</span>
                                </a>
                            </li>
                            @if($kycMethod !== 'disabled')
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center gap-2 m-0 py-3" href="#payout-kyc-div" id="payout-kyc-section">
                                        <i class="tio-security-on nav-icon"></i> {{ translate('Payout_KYC') }}
                                        <span class="badge badge-{{ $payoutKycStatus === 'approved' ? 'success' : 'warning' }} ml-auto">{{ ucfirst($payoutKycStatus) }}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <form action="{{ route('vendor.profile.update',[$vendor->id]) }}" method="post"
                      enctype="multipart/form-data" id="update-profile-form" novalidate>
                @csrf
                    <div class="card mb-3 mb-lg-5" id="general-div">
                        <div class="profile-cover">
                            @php($banner = dynamicAsset(path: 'public/assets/back-end/img/media/admin-profile-bg.png'))
                            <div class="profile-cover-img-wrapper profile-bg" style="background-image: url({{ $banner }})"></div>
                        </div>
                        <div
                            class="avatar avatar-xxl avatar-circle avatar-border-lg avatar-uploader profile-cover-avatar"
                            >
                            <img id="viewer"    class="avatar-img"
                                 src="{{ getStorageImages(path:$vendor->image_full_url, type:'backend-profile') }}"
                                 alt="{{ translate('image') }}">
                            <label class="change-profile-image-icon" for="custom-file-upload">
                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/add-photo.png') }}" alt="">
                            </label>
                        </div>

                        <div class="card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div><img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/user-1.svg') }}" alt=""></div>
                                <h4 class="card-title m-0 fs-16">{{translate('basic_Information')}}</h4>
                            </div>
                        </div>
                        <div class="card-body">

                            <div class="row">

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <label for="firstNameLabel" class="input-label mb-0">
                                            {{translate('first_Name')}}
                                            <span class="text-danger px-1">*</span>
                                        </label>
                                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip" data-placement="right" title="" data-original-title="{{ translate('this_will_be_displayed_as_your_profile_name') }}">
                                            <img alt="" width="16" src="{{dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                        </span>
                                    </div>

                                    <div class="mb-3">
                                        <div class="input-group input-group-sm-down-break">
                                            <input type="text" class="form-control" name="f_name" id="firstNameLabel"
                                                   placeholder="{{ translate('ex') }}: {{ translate('ABC') }}" aria-label=" {{ translate('ABC') }}"
                                                   value="{{ $vendor->f_name }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <label for="lastNameLabel" class="input-label mb-0">
                                            {{translate('last_Name')}}
                                        </label>
                                    </div>

                                    <div class="mb-3">
                                        <div class="input-group input-group-sm-down-break">
                                            <input type="text" class="form-control" name="l_name" id="lastNameLabel"
                                                   placeholder="{{ translate('ex') }}: {{ translate('ABC') }}" aria-label=" {{ translate('ABC') }}"
                                                   value="{{ $vendor->l_name }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <label for="phoneLabel" class="input-label mb-0">
                                            {{translate('phone_Number')}}
                                            <span class="input-label-secondary">
                                                ({{translate('optional')}})
                                            </span>
                                        </label>
                                    </div>

                                    <div class="mb-3">
                                        <input class="form-control form-control-user phone-input-with-country-picker"
                                               type="tel" id="phoneLabel" value="{{$vendor->phone ?? old('phone')}}"
                                               placeholder="{{ translate('ex') }}: {{ translate('123456789') }}" required>
                                        <input type="hidden" class="country-picker-phone-number w-50" value="{{$vendor->phone}}" name="phone" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <label for="newEmailLabel" class="input-label mb-0">
                                            {{translate('email')}}
                                            <span class="text-danger px-1">*</span>
                                        </label>

                                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip" data-placement="right" title="" data-original-title="{{ translate('you_can_login_to_your_panel_by_using_this_email') }}">
                                            <img alt="" width="16" src="{{dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <input type="email" class="form-control" name="email" id="newEmailLabel"
                                               value="{{$vendor->email}}" readonly
                                               placeholder="{{ translate('ex') }}: {{ 'admin@admin.com' }}">
                                    </div>
                                </div>

                            </div>

                            <div class="d-none" id="select-img">
                                <input type="file" name="image" id="custom-file-upload" class="custom-file-input image-input"
                                       data-image-id="viewer"
                                       accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" data-form-id="update-profile-form" data-message="{{ translate('want_to_update_vendor_info').'?'}}" class="btn btn--primary {{env('APP_MODE')!='demo'?'form-submit':'call-demo-alert'}}">{{ translate('save_Changes') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="password-div" class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-3">
                            <div><img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/password-lock.svg') }}" alt=""></div>
                            <h4 class="card-title m-0 fs-16">{{translate('change_Password')}}</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <form id="update-password-form" action="{{ route('vendor.profile.update',[auth('seller')->id()]) }}" method="POST"
                              enctype="multipart/form-data" novalidate>
                            @method('PATCH')
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <label for="newPassword" class="input-label mb-0">
                                            {{translate('new_password')}}
                                            <span class="text-danger px-1">*</span>
                                        </label>
                                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip" data-placement="right" title="" data-original-title="{{translate('The_password_must_be_at_least_8_characters_long_and_contain_at_least_one_uppercase_letter').','.translate('_one_lowercase_letter').','.translate('_one_digit_').','.translate('_one_special_character').','.translate('_and_no_spaces').'.'}}">
                                            <img alt="" width="16" src="{{dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="" class="m-1">
                                        </span>
                                    </div>
                                    <div class="">
                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control password-check" id="newPassword"
                                                   autocomplete="off" name="password" required minlength="8" placeholder="{{ translate('enter_new_password') }}"
                                                   data-hs-toggle-password-options='{
                                                         "target": "#changePassTarget",
                                                        "defaultClass": "tio-hidden-outlined",
                                                        "showClass": "tio-visible-outlined",
                                                        "classChangeTarget": "#changePassIcon"
                                                }'>
                                            <div id="changePassTarget" class="input-group-append">
                                                <a class="input-group-text" href="javascript:">
                                                    <i id="changePassIcon" class="tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <span class="text-danger pt-1 min-h-20 d-block password-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <label for="confirmNewPasswordLabel" class="input-label mb-1">
                                            {{translate('confirm_password')}}
                                            <span class="text-danger px-1">*</span>
                                        </label>
                                    </div>
                                    <div class="">
                                        <div class="mb-3">
                                            <div class="input-group input-group-merge">
                                                <input type="password" class="js-toggle-password form-control"
                                                       name="confirm_password" required id="confirmNewPasswordLabel"
                                                       placeholder="{{ translate('enter_confirm_password') }}"
                                                       autocomplete="off"
                                                       data-hs-toggle-password-options='{
                                                         "target": "#changeConfirmPassTarget",
                                                        "defaultClass": "tio-hidden-outlined",
                                                        "showClass": "tio-visible-outlined",
                                                        "classChangeTarget": "#changeConfirmPassIcon"
                                                }'>
                                                <div id="changeConfirmPassTarget" class="input-group-append">
                                                    <a class="input-group-text" href="javascript:">
                                                        <i id="changeConfirmPassIcon" class="tio-visible-outlined"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" data-form-id="update-password-form" data-message="{{ translate('want_to_update_vendor_password').'?'}}" class="btn btn--primary {{env('APP_MODE')!='demo'?'form-submit':'call-demo-alert'}}" >{{ translate('save_Changes') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="company-verification-div" class="card verification-profile-card mb-3 mb-lg-5">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="verification-profile-icon"><i class="tio-verified"></i></span>
                            <div>
                                <h4 class="card-title mb-1 fs-16">{{ translate('Company_Verification') }}</h4>
                                <div class="text-muted">{{ translate('Review_your_submitted_business_and_identity_details.') }}</div>
                            </div>
                        </div>
                        <span class="verification-status-badge {{ $companyStatus }}">
                            <i class="tio-{{ $companyStatus === 'approved' ? 'checkmark-circle' : ($companyStatus === 'rejected' ? 'clear-circle' : 'time') }}"></i>
                            {{ ucfirst(str_replace('_', ' ', $companyStatus)) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6 col-xl-4">
                                <div class="verification-detail">
                                    <div class="verification-detail-label">{{ translate('Seller_Type') }}</div>
                                    <div class="verification-detail-value">{{ ucfirst($verification?->seller_type ?? $vendor?->seller_type ?? translate('Not_submitted')) }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="verification-detail">
                                    <div class="verification-detail-label">{{ translate('Business_Name') }}</div>
                                    <div class="verification-detail-value">{{ $shop?->name ?: translate('Not_submitted') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="verification-detail">
                                    <div class="verification-detail-label">{{ translate('Registered_Country') }}</div>
                                    <div class="verification-detail-value">{{ $verification?->company_registered_country ?: translate('Not_submitted') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="verification-detail">
                                    <div class="verification-detail-label">{{ translate('Registration_Number') }}</div>
                                    <div class="verification-detail-value">{{ $verification?->company_no ?: translate('Not_submitted') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="verification-detail">
                                    <div class="verification-detail-label">{{ translate('Website') }}</div>
                                    <div class="verification-detail-value">{{ $verification?->company_website ?: translate('Not_submitted') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="verification-detail">
                                    <div class="verification-detail-label">{{ translate('Verification_Document') }}</div>
                                    <div class="verification-detail-value">
                                        @if($companyDocumentUrl)
                                            <a href="{{ $companyDocumentUrl }}" target="_blank"><i class="tio-open-in-new mr-1"></i>{{ translate('View_Document') }}</a>
                                        @else
                                            {{ translate('Not_submitted') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($verification?->rejection_reason)
                            <div class="alert alert-danger mt-3 mb-0"><strong>{{ translate('Rejection_reason') }}:</strong> {{ $verification->rejection_reason }}</div>
                        @endif
                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('vendor.verification.form', ['return_to' => 'profile']) }}" class="btn btn--primary">
                                <i class="tio-edit mr-1"></i>{{ $verification ? translate('View_or_Update_Verification') : translate('Complete_Verification') }}
                            </a>
                        </div>
                    </div>
                </div>

                @if($kycMethod !== 'disabled')
                    <div id="payout-kyc-div" class="card verification-profile-card mb-3 mb-lg-5">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="verification-profile-icon"><i class="tio-security-on"></i></span>
                                <div>
                                    <h4 class="card-title mb-1 fs-16">{{ translate('Payout_KYC') }}</h4>
                                    <div class="text-muted">{{ translate('Manage_the_identity_check_used_for_wallet_withdrawals.') }}</div>
                                </div>
                            </div>
                            <span class="verification-status-badge {{ $payoutKycStatus }}">
                                <i class="tio-{{ $payoutKycStatus === 'approved' ? 'checkmark-circle' : ($payoutKycStatus === 'rejected' ? 'clear-circle' : 'time') }}"></i>
                                {{ ucfirst(str_replace('_', ' ', $payoutKycStatus)) }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="verification-detail">
                                        <div class="verification-detail-label">{{ translate('Verification_Method') }}</div>
                                        <div class="verification-detail-value">{{ ucfirst($kycMethod) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="verification-detail">
                                        <div class="verification-detail-label">{{ translate('Submitted_Document') }}</div>
                                        <div class="verification-detail-value">
                                            @if($payoutDocumentLink)
                                                <a href="{{ $payoutDocumentLink['path'] ?? '#' }}" target="_blank"><i class="tio-open-in-new mr-1"></i>{{ translate('View_Document') }}</a>
                                            @else
                                                {{ translate('Not_submitted') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="verification-detail">
                                        <div class="verification-detail-label">{{ translate('Submitted_At') }}</div>
                                        <div class="verification-detail-value">{{ $verification?->payout_kyc_submitted_at?->format('M d, Y h:i A') ?? translate('Not_submitted') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="verification-detail">
                                        <div class="verification-detail-label">{{ translate('Note') }}</div>
                                        <div class="verification-detail-value">{{ $verification?->payout_kyc_note ?: translate('No_note_added') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('vendor.payout-kyc.index') }}" class="btn btn--primary">
                                    <i class="tio-edit mr-1"></i>{{ translate('View_or_Update_Payout_KYC') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/js/intlTelInput.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/country-picker-init.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const links = document.querySelectorAll('#navbarSettings .nav-link');
            ['company-verification', 'payout-kyc'].forEach(function (section) {
                document.getElementById(section + '-section')?.addEventListener('click', function (event) {
                    event.preventDefault();
                    links.forEach(link => link.classList.remove('active'));
                    this.classList.add('active');
                    document.getElementById(section + '-div')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            const requestedTab = new URLSearchParams(window.location.search).get('tab');
            document.getElementById(requestedTab + '-section')?.click();
        });
    </script>
    <script>
        window.formValidators = window.formValidators || {};

        window.formValidators['update-profile-form'] = function () {
            let isValid = true;
            if (!FormValidators.required('#firstNameLabel', "{{ translate('first_name_field_is_required') }}")) isValid = false;
            if (!FormValidators.required('.country-picker-phone-number', "{{ translate('phone_field_is_required') }}")) isValid = false;
            if (!isValid) FormValidators.focusFirstError("{{ translate('please_fix_the_highlighted_fields') }}");
            return isValid;
        };

        window.formValidators['update-password-form'] = function () {
            let isValid = true;
            if (!FormValidators.required('#newPassword', "{{ translate('password_field_is_required') }}")) isValid = false;
            else if (!FormValidators.minLength('#newPassword', 8, "{{ translate('password_must_be_at_least_8_characters') }}")) isValid = false;
            if (!FormValidators.required('#confirmNewPasswordLabel', "{{ translate('confirm_password_field_is_required') }}")) isValid = false;
            else if (!FormValidators.passwordsMatch('#newPassword', '#confirmNewPasswordLabel', "{{ translate('password_and_confirm_password_must_match') }}")) isValid = false;
            if (!isValid) FormValidators.focusFirstError("{{ translate('please_fix_the_highlighted_fields') }}");
            return isValid;
        };
    </script>
@endpush
