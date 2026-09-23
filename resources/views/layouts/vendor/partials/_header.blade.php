
@php
    use Illuminate\Support\Facades\Session;
    use Illuminate\Support\Str;

    // SAFE AUTH CHECK
    $sellerId = auth('seller')->id();
    $eCommerceLogo = getWebConfig(name: 'company_footer_logo');

    // Reuses the guard's already-resolved (and relation-preloaded, via the
    // layouts.vendor.app composer) instance instead of re-querying.
    $vendor = $sellerId ? auth('seller')->user() : null;
    $shop   = $vendor?->shop;

    // FALLBACK VALUES
    $vendorName  = $vendor?->name ?? 'Vendor';
    $shopName    = $shop?->name ?? '';
    $vendorImage = $vendor?->image_full_url ? getStorageImages(path:$vendor->image_full_url,type:'backend-profile') : dynamicAsset(path:'public/default.png');
    $vendorVerification = $vendor?->vendorVerification;
    $kycMethod = getWebConfig('kyc_method') ?? 'manual';
    $isKycDisabled = $kycMethod === 'disabled';
    $isVendorProfileApproved = ($vendor?->status ?? null) === 'approved' || ($vendorVerification?->status ?? null) === 'approved';
    $isVendorKycApproved = ($vendor?->kyc_status ?? null) === 'approved';
    $showKycHeaderBadge = !$isKycDisabled && (!$isVendorProfileApproved || !$isVendorKycApproved);
    $kycHeaderBadgeText = !$isVendorProfileApproved ? translate('Verify_Company') : translate('Payout_KYC');
    $kycHeaderBadgeRoute = !$isVendorProfileApproved ? route('vendor.verification.form') : route('vendor.payout-kyc.index');
@endphp
@php($direction = Session::get('direction'))
@if($showKycHeaderBadge)
    <style>
        .vendor-kyc-pulse-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.55rem;
            border-radius: 999px;
            background: #fff3cd;
            color: #8a5a00;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
            box-shadow: 0 0 0 rgba(255, 193, 7, 0.55);
            animation: vendorKycPulse 1.35s infinite;
        }
        .vendor-kyc-pulse-badge:hover {
            color: #6d4500;
            text-decoration: none;
        }
        .vendor-kyc-pulse-dot {
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 999px;
            background: #f59e0b;
        }
        @keyframes vendorKycPulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.55); }
            70% { box-shadow: 0 0 0 8px rgba(255, 193, 7, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
        }
    </style>
@endif
<style>
    .vendor-dashboard-header .navbar-nav {
        flex-wrap: nowrap;
    }
    .vendor-dashboard-header .nav-item .btn-icon,
    .vendor-dashboard-header .vendor-profile-trigger {
        transition: background-color .2s ease, transform .2s ease;
    }
    .vendor-dashboard-header .nav-item .btn-icon:hover,
    .vendor-dashboard-header .vendor-profile-trigger:hover {
        transform: translateY(-1px);
    }
    .vendor-dashboard-header .vendor-profile-trigger {
        padding: 4px 6px;
        border-radius: 24px;
        max-width: 250px;
    }
    .vendor-dashboard-header .vendor-profile-trigger::after {
        display: none !important;
    }
    .vendor-dashboard-header .vendor-profile-trigger .media-body {
        min-width: 0;
    }
    .vendor-dashboard-header .vendor-profile-trigger .profile-name,
    .vendor-dashboard-header .vendor-profile-trigger .media-body span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .vendor-dashboard-header .vendor-profile-trigger:hover {
        background: rgba(7, 59, 116, .08);
    }
    @media (max-width: 575.98px) {
        .vendor-dashboard-header .navbar-nav {
            gap: 4px !important;
        }
        .vendor-dashboard-header .navbar-nav-wrap-content-right {
            min-width: 0;
        }
        .vendor-dashboard-header .nav-item .btn-icon,
        .vendor-dashboard-header .gtranslate-dashboard-button {
            width: 34px;
            height: 34px;
        }
    }
    @media (max-width: 479.98px) {
        .vendor-dashboard-header .vendor-header-optional {
            display: none;
        }
    }
</style>
<div id="headerMain" class="d-none">
    <header id="header"
            class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container navbar-bordered vendor-dashboard-header">
        <div class="navbar-nav-wrap">
            <div class="navbar-brand-wrapper d-none d-sm-block d-xl-none">
                <a class="navbar-brand" href="{{route('vendor.dashboard.index')}}" aria-label="">
                    <img class="navbar-brand-logo"
                         src="{{ getStorageImages(path: $eCommerceLogo, type: 'backend-logo') }}"
                         alt="{{ translate('logo') }}"
                         height="40">
                </a>
            </div>
            <div class="navbar-nav-wrap-content-left">
                <button type="button" class="js-navbar-vertical-aside-toggle-invoker close mr-sm-3 d-xl-none">
                    <i class="tio-first-page navbar-vertical-aside-toggle-short-align"></i>
                    <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                       data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                       data-toggle="tooltip" data-placement="right" title="Expand"></i>
                </button>
                <div class="d-none">
                    <form class="position-relative" novalidate>
                    </form>
                </div>
            </div>
            <div class="navbar-nav-wrap-content-right"
                 style="{{$direction === "rtl" ? 'margin-left:unset; margin-right: auto' : 'margin-right:unset; margin-left: auto'}}">
                <ul class="navbar-nav align-items-center flex-row gap-xl-16px">

                    <li class="nav-item">
                        @includeIf('layouts.front-end.partials.gtranslater', [
                            'gtranslateBootstrapVersion' => 4,
                            'gtranslateDashboard' => true,
                        ])
                    </li>

                    @if($showKycHeaderBadge)
                        <li class="nav-item d-none d-sm-flex align-items-center">
                            <a href="{{ $kycHeaderBadgeRoute }}"
                               class="vendor-kyc-pulse-badge">
                                <span class="vendor-kyc-pulse-dot"></span>
                                <span>{{ $kycHeaderBadgeText }}</span>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item vendor-header-optional">
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle"
                               href="{{route('shopView',['id'=>auth('seller')->id()])}}" target="_blank"
                               data-toggle="tooltip" data-placement="bottom"
                               title="{{ translate('view_my_store') }}"
                               data-custom-class="header-icon-title">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.25 2.5H17.5V8.75" stroke="#073B74" stroke-width="1.75"
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M17.5 2.5L9.16667 10.8333" stroke="#073B74" stroke-width="1.75"
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8.33333 4.16699H4.16667C3.24619 4.16699 2.5 4.91318 2.5 5.83366V15.8337C2.5 16.7541 3.24619 17.5003 4.16667 17.5003H14.1667C15.0871 17.5003 15.8333 16.7541 15.8333 15.8337V11.667"
                                          stroke="#073B74" stroke-width="1.75" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </li>

                    <li class="nav-item">
                        <div class="hs-unfold">
                            @php($customNotificationCount=$unreadNotificationCount ?? (isset($unreadNotifications) ? $unreadNotifications->count() : 0))
                            @php($totalNotificationCount=($systemNotificationCount ?? 0) + $customNotificationCount)
                            <a
                                class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle media align-items-center gap-3 navbar-dropdown-account-wrapper dropdown-toggle-left-arrow dropdown-toggle-empty"
                                href="javascript:"
                                id="vendorNotificationToggle"
                                data-hs-unfold-options='{
                                     "target": "#notificationDropdown",
                                     "type": "css-animation"
                                   }'>
                                <i class="tio-notifications-on-outlined"></i>
                                @if($totalNotificationCount != 0)
                                    <span class="btn-status btn-sm-status btn-status-danger notification_data_new_count">{{ $totalNotificationCount }}</span>
                                @endif
                            </a>
                            <div id="notificationDropdown"
                                 class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account py-0 overflow-hidden width--20rem">
                                <div id="vendorNotificationItems">
                                    @include('layouts.vendor.partials._notification-items')
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item vendor-header-optional">
                        <div class="hs-unfold">
                            <a
                                id="vendorChatToggle"
                                class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle media align-items-center gap-3 navbar-dropdown-account-wrapper dropdown-toggle-left-arrow dropdown-toggle-empty"
                                href="javascript:"
                                data-hs-unfold-options='{
                                     "target": "#messageDropdown",
                                     "type": "css-animation"
                                   }'
                            >
                                <svg width="20" height="21" viewBox="0 0 20 21" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_5926_1152)">
                                        <path
                                            d="M16.6666 2.16699H3.33329C2.41663 2.16699 1.67496 2.91699 1.67496 3.83366L1.66663 18.8337L4.99996 15.5003H16.6666C17.5833 15.5003 18.3333 14.7503 18.3333 13.8337V3.83366C18.3333 2.91699 17.5833 2.16699 16.6666 2.16699ZM4.99996 8.00033H15V9.66699H4.99996V8.00033ZM11.6666 12.167H4.99996V10.5003H11.6666V12.167ZM15 7.16699H4.99996V5.50033H15V7.16699Z"
                                            fill="#073B74"/>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_5926_1152">
                                            <rect width="20" height="20" fill="white" transform="translate(0 0.5)"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                                @php($message=$chattingCounts->unseen_total ?? 0)
                                @if($message!=0)
                                    <span id="vendorChatUnseenTotalBadge" class="btn-status btn-sm-status btn-status-danger">{{ $message }}</span>
                                @endif
                            </a>
                            <div id="messageDropdown"
                                 class="hs-unfold-content width--16rem dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account">
                                <a id="vendorChatCustomerDropdownItem" class="dropdown-item position-relative"
                                   href="{{route('vendor.messages.index', ['type' => 'customer'])}}">
                                    <span class="text-truncate pr-2"
                                          title="Settings">{{translate('customer')}}</span>
                                    @php($messageCustomer=$chattingCounts->unseen_customer ?? 0)
                                    @if($messageCustomer > 0)
                                        <span id="vendorChatUnseenCustomerBadge"
                                            class="btn-status btn-sm-status-custom btn-status-danger">{{$messageCustomer}}</span>
                                    @endif
                                </a>
                                {{-- Delivery-man messaging is not active; the HTML was previously
                                     commented out but its @php(...) query still ran on every load. --}}
                            </div>
                        </div>
                    </li>
                    <li class="nav-item vendor-header-optional">
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle"
                               href="{{route('vendor.orders.list',['pending'])}}"
                               title="{{translate('pending_Orders')}}" data-toggle="tooltip"
                               data-custom-class="header-icon-title">
                                <svg width="20" height="21" viewBox="0 0 20 21" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_5926_1157)">
                                        <path
                                            d="M15.148 15.1201C13.8635 15.1189 12.8212 16.1592 12.8199 17.4437C12.8187 18.7282 13.859 19.7705 15.1435 19.7717C16.428 19.773 17.4703 18.7327 17.4716 17.4482C17.4716 17.4474 17.4716 17.4467 17.4716 17.4459C17.4703 16.1628 16.4311 15.1226 15.148 15.1201Z"
                                            fill="#073B74"/>
                                        <path
                                            d="M19.2731 3.98349C19.2175 3.97271 19.161 3.96724 19.1043 3.96715H4.94317L4.71889 2.4667C4.57915 1.47022 3.7268 0.728822 2.72055 0.728516H0.897126C0.401648 0.728516 0 1.13016 0 1.62564C0 2.12112 0.401648 2.52277 0.897126 2.52277H2.72279C2.83685 2.52194 2.9334 2.60687 2.94707 2.72015L4.32863 12.1893C4.51805 13.3925 5.55303 14.2802 6.77107 14.2841H16.1034C17.2761 14.2856 18.2878 13.4614 18.5234 12.3127L19.9835 5.03472C20.0776 4.54827 19.7596 4.07763 19.2731 3.98349Z"
                                            fill="#073B74"/>
                                        <path
                                            d="M9.45041 17.3461C9.39578 16.0992 8.3668 15.1177 7.11875 15.1221C5.83531 15.1739 4.83691 16.2565 4.88877 17.5399C4.93854 18.7714 5.94031 19.7502 7.17259 19.7715H7.22866C8.51193 19.7152 9.50661 18.6293 9.45041 17.3461Z"
                                            fill="#073B74"/>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_5926_1157">
                                            <rect width="20" height="20" fill="white" transform="translate(0 0.25)"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                                @php($order=($orderStatusCounts['pending'] ?? 0))
                                @if($order!=0)
                                    <span class="btn-status btn-sm-status btn-status-danger">{{ $order }}</span>
                                @endif
                            </a>
                        </div>
                    </li>

                    <li class="nav-item">
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker media align-items-center gap-3 navbar-dropdown-account-wrapper vendor-profile-trigger"
                               href="javascript:"
                               data-hs-unfold-options='{
                                     "target": "#accountNavbarDropdown",
                                     "type": "css-animation"
                                   }'>
                                <div class="d-none d-md-block media-body text-right">
                                    <h5 class="profile-name mb-0">{{$vendorName}}</h5>
                                    <span class="fs-12">{{ Str::limit($shopName, 20) }}</span>
                                </div>
                                <div class="avatar avatar-sm avatar-circle">
                                    <img class="avatar-img"
                                         src="{{$vendorImage}}"
                                         alt="{{translate('image_description')}}">
                                    <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                </div>
                            </a>
                            <div id="accountNavbarDropdown"
                                 class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account __w-16rem">
                                <div class="dropdown-item-text">
                                    <div class="media align-items-center text-break">
                                        <div class="avatar avatar-sm avatar-circle mr-2">
                                            <img class="avatar-img"
                                                 src="{{$vendorImage}}"
                                                 alt="{{translate('image_description')}}">
                                        </div>
                                        <div class="media-body">
                                            <span class="card-title h5">{{$vendor?->f_name}}</span>

                                            <span class="card-text">{{$vendor?->email}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item"
                                   href="{{route('vendor.profile.update',[auth('seller')->id()])}}">
                                    <span class="text-truncate pr-2" title="Settings">{{translate('settings')}}</span>
                                </a>
                                @if($showKycHeaderBadge)
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item"
                                       href="{{ $kycHeaderBadgeRoute }}">
                                        <span class="text-truncate pr-2">{{ $kycHeaderBadgeText }}</span>
                                    </a>
                                @endif
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:" data-toggle="modal"
                                   data-target="#sign-out-modal">
                                    <span class="text-truncate pr-2"
                                          title="{{translate('logout')}}">{{translate('logout')}}</span>
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div id="website_info" class="bg-secondary w-100 d-none">
            <div class="p-3">
                <div class="bg-white p-1 rounded">
                    <a title="{{ translate('view_my_store') }}" class="p-2 title-color"
                       href="{{route('shopView',['id'=>auth('seller')->id()])}}" target="_blank" data-toggle="tooltip"
                       data-custom-class="header-icon-title">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.25 2.5H17.5V8.75" stroke="#073B74" stroke-width="1.75"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M17.5 2.5L9.16667 10.8333" stroke="#073B74" stroke-width="1.75"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8.33333 4.16699H4.16667C3.24619 4.16699 2.5 4.91318 2.5 5.83366V15.8337C2.5 16.7541 3.24619 17.5003 4.16667 17.5003H14.1667C15.0871 17.5003 15.8333 16.7541 15.8333 15.8337V11.667"
                                  stroke="#073B74" stroke-width="1.75" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                        {{translate('view_my_store')}}
                    </a>
                </div>
                <div class="bg-white p-1 rounded mt-2">
                    <a class="p-2  title-color"
                       href="{{route('vendor.messages.index', ['type' => 'customer'])}}"
                       title="{{translate('message')}}" data-toggle="tooltip" data-custom-class="header-icon-title">
                        <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_5926_1152)">
                                <path
                                    d="M16.6666 2.16699H3.33329C2.41663 2.16699 1.67496 2.91699 1.67496 3.83366L1.66663 18.8337L4.99996 15.5003H16.6666C17.5833 15.5003 18.3333 14.7503 18.3333 13.8337V3.83366C18.3333 2.91699 17.5833 2.16699 16.6666 2.16699ZM4.99996 8.00033H15V9.66699H4.99996V8.00033ZM11.6666 12.167H4.99996V10.5003H11.6666V12.167ZM15 7.16699H4.99996V5.50033H15V7.16699Z"
                                    fill="#073B74"/>
                            </g>
                            <defs>
                                <clipPath id="clip0_5926_1152">
                                    <rect width="20" height="20" fill="white" transform="translate(0 0.5)"/>
                                </clipPath>
                            </defs>
                        </svg>
                        {{translate('message')}}
                        @php($message=$chattingCounts->seen_total ?? 0)
                        @if($message!=0)
                            <span>({{ $message }})</span>
                        @endif
                    </a>
                </div>
                <div class="bg-white p-1 rounded mt-2">
                    <a class="p-2 title-color"
                       href="{{route('vendor.orders.list',['pending'])}}" title="{{translate('Shopping Cart')}}"
                       data-toggle="tooltip" data-custom-class="header-icon-title">
                        <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_5926_1157)">
                                <path
                                    d="M15.148 15.1201C13.8635 15.1189 12.8212 16.1592 12.8199 17.4437C12.8187 18.7282 13.859 19.7705 15.1435 19.7717C16.428 19.773 17.4703 18.7327 17.4716 17.4482C17.4716 17.4474 17.4716 17.4467 17.4716 17.4459C17.4703 16.1628 16.4311 15.1226 15.148 15.1201Z"
                                    fill="#073B74"/>
                                <path
                                    d="M19.2731 3.98349C19.2175 3.97271 19.161 3.96724 19.1043 3.96715H4.94317L4.71889 2.4667C4.57915 1.47022 3.7268 0.728822 2.72055 0.728516H0.897126C0.401648 0.728516 0 1.13016 0 1.62564C0 2.12112 0.401648 2.52277 0.897126 2.52277H2.72279C2.83685 2.52194 2.9334 2.60687 2.94707 2.72015L4.32863 12.1893C4.51805 13.3925 5.55303 14.2802 6.77107 14.2841H16.1034C17.2761 14.2856 18.2878 13.4614 18.5234 12.3127L19.9835 5.03472C20.0776 4.54827 19.7596 4.07763 19.2731 3.98349Z"
                                    fill="#073B74"/>
                                <path
                                    d="M9.45041 17.3461C9.39578 16.0992 8.3668 15.1177 7.11875 15.1221C5.83531 15.1739 4.83691 16.2565 4.88877 17.5399C4.93854 18.7714 5.94031 19.7502 7.17259 19.7715H7.22866C8.51193 19.7152 9.50661 18.6293 9.45041 17.3461Z"
                                    fill="#073B74"/>
                            </g>
                            <defs>
                                <clipPath id="clip0_5926_1157">
                                    <rect width="20" height="20" fill="white" transform="translate(0 0.25)"/>
                                </clipPath>
                            </defs>
                        </svg>
                        {{translate('order_list')}}
                    </a>
                </div>
            </div>
        </div>
    </header>
</div>
<div id="headerFluid" class="d-none"></div>
<div id="headerDouble" class="d-none"></div>

<script>
    // Poll for new notifications so the bell badge and dropdown list update
    // live in the background — no full page reload needed to see a new
    // order, message, or plan-expiry notice come in.
    (function () {
        var toggle = document.getElementById('vendorNotificationToggle');
        var itemsContainer = document.getElementById('vendorNotificationItems');
        if (!toggle || !itemsContainer) return;

        function renderBadge(count) {
            var existing = toggle.querySelector('.notification_data_new_count');
            if (count > 0) {
                if (existing) {
                    existing.textContent = count;
                } else {
                    var span = document.createElement('span');
                    span.className = 'btn-status btn-sm-status btn-status-danger notification_data_new_count';
                    span.textContent = count;
                    toggle.appendChild(span);
                }
            } else if (existing) {
                existing.remove();
            }
        }

        // The "Messages" unseen-chat badges (header icon + dropdown item + sidebar
        // inbox link) are a separate counter from the notification bell above — they
        // were only ever set once per page load by the layout's view composer, so
        // they went stale between reloads just like the bell used to. Update them
        // from the same poll response instead of adding a second poll cycle.
        function renderChatBadge(badgeId, parentId, className, count) {
            var parent = document.getElementById(parentId);
            var existing = document.getElementById(badgeId);
            if (count > 0) {
                if (existing) {
                    existing.textContent = count;
                } else if (parent) {
                    var span = document.createElement('span');
                    span.id = badgeId;
                    span.className = className;
                    span.textContent = count;
                    parent.appendChild(span);
                }
            } else if (existing) {
                existing.remove();
            }
        }

        function pollVendorNotifications() {
            fetch('{{ route('vendor.notifications.poll') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store',
            }).then(function (response) {
                return response.ok ? response.json() : null;
            }).then(function (data) {
                if (!data) return;
                renderBadge(data.count);
                itemsContainer.innerHTML = data.html;
                renderChatBadge('vendorChatUnseenTotalBadge', 'vendorChatToggle', 'btn-status btn-sm-status btn-status-danger', data.chat_unseen_total || 0);
                renderChatBadge('vendorChatUnseenCustomerBadge', 'vendorChatCustomerDropdownItem', 'btn-status btn-sm-status-custom btn-status-danger', data.chat_unseen_customer || 0);
                renderChatBadge('vendorSidebarChatUnseenBadge', 'vendorSidebarChatToggleInner', 'vendor-sidebar-required-badge', data.chat_unseen_total || 0);
            }).catch(function () {
                // Silent — a missed poll just tries again next interval.
            });
        }

        window.finxcartSyncNotifications = pollVendorNotifications;
        pollVendorNotifications();
        setInterval(pollVendorNotifications, 3000);

        // Re-sync immediately on returning to this tab instead of waiting for the
        // next interval tick — a background tab throttles setInterval anyway, so
        // without this a badge can sit stale for well over 8s until you switch back.
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                pollVendorNotifications();
            }
        });
    })();
</script>

