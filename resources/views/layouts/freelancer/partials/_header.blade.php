@php
    use Illuminate\Support\Facades\Session;
    use Illuminate\Support\Str;

    $sellerId = auth('freelancer')->id();
    $eCommerceLogo = getWebConfig(name: 'company_footer_logo');

    $seller = $sellerId ? auth('freelancer')->user() : null;

    $sellerName  = $seller->name ?? 'Freelancer';
    $sellerImage = $seller?->image_full_url ? getStorageImages(path:$seller->image_full_url,type:'backend-profile') : dynamicAsset(path:'public/default.png');
    $verification = $seller?->vendorVerification;
    $kycMethod = getWebConfig('kyc_method') ?? 'manual';
    $isKycDisabled = $kycMethod === 'disabled';
    $isProfileApproved = ($seller?->status ?? null) === 'approved' || ($verification?->status ?? null) === 'approved';
    $isKycApproved = ($seller?->kyc_status ?? null) === 'approved';
    $showKycHeaderBadge = !$isKycDisabled && (!$isProfileApproved || !$isKycApproved);
    $kycHeaderBadgeText = !$isProfileApproved ? translate('Verify_Account') : translate('Payout_KYC');
    $kycHeaderBadgeRoute = route('freelancer.verification.form');
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
                <a class="navbar-brand" href="{{route('freelancer.dashboard.index')}}" aria-label="">
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

                    <li class="nav-item">
                        <div class="hs-unfold">
                            @php($customNotificationCount=$unreadNotificationCount ?? (isset($unreadNotifications) ? $unreadNotifications->count() : 0))
                            @php($totalNotificationCount=($systemNotificationCount ?? 0) + $customNotificationCount)
                            <a
                                class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle media align-items-center gap-3 navbar-dropdown-account-wrapper dropdown-toggle-left-arrow dropdown-toggle-empty"
                                href="javascript:"
                                id="freelancerNotificationToggle"
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
                                <div id="freelancerNotificationItems">
                                    @include('layouts.freelancer.partials._notification-items')
                                </div>
                            </div>
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
                                    <h5 class="profile-name mb-0">{{ $sellerName }}</h5>
                                    <span class="fs-12">{{ translate('freelancer') }}</span>
                                </div>
                                <div class="avatar avatar-sm avatar-circle">
                                    <img class="avatar-img" src="{{ $sellerImage }}" alt="{{translate('image_description')}}">
                                    <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                </div>
                            </a>
                            <div id="accountNavbarDropdown"
                                 class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account __w-16rem">
                                <div class="dropdown-item-text">
                                    <div class="media align-items-center text-break">
                                        <div class="avatar avatar-sm avatar-circle mr-2">
                                            <img class="avatar-img" src="{{ $sellerImage }}" alt="{{translate('image_description')}}">
                                        </div>
                                        <div class="media-body">
                                            <span class="card-title h5">{{ $seller->f_name ?? $sellerName }}</span>

                                            <span class="card-text">{{ $seller->email ?? '' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{route('freelancer.profile.index')}}">
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
    </header>
</div>
<div id="headerFluid" class="d-none"></div>

<script>
    // Poll for new notifications so the bell badge and dropdown list update
    // live in the background — no full page reload needed to see a new
    // quote, message, or delivery status update come in.
    (function () {
        var toggle = document.getElementById('freelancerNotificationToggle');
        var itemsContainer = document.getElementById('freelancerNotificationItems');
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

        // The sidebar's "Messages"/"Messages from Admin" unseen-chat badges are a
        // separate counter from the notification bell above — they were only ever
        // set once per page load by the layout's view composer, so they went stale
        // between reloads just like the bell used to. Update them from the same
        // poll response instead of adding a second poll cycle.
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

        function pollFreelancerNotifications() {
            fetch('{{ route('freelancer.notifications.poll') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store',
            }).then(function (response) {
                return response.ok ? response.json() : null;
            }).then(function (data) {
                if (!data) return;
                renderBadge(data.count);
                itemsContainer.innerHTML = data.html;
                renderChatBadge('freelancerSidebarChatCustomerBadge', 'freelancerSidebarChatCustomerToggleInner', 'vendor-sidebar-required-badge', data.chat_unseen_customer || 0);
                renderChatBadge('freelancerSidebarChatAdminBadge', 'freelancerSidebarChatAdminToggleInner', 'vendor-sidebar-required-badge', data.chat_unseen_admin || 0);
            }).catch(function () {
                // Silent — a missed poll just tries again next interval.
            });
        }

        window.finxcartSyncNotifications = pollFreelancerNotifications;
        pollFreelancerNotifications();
        setInterval(pollFreelancerNotifications, 3000);

        // Re-sync immediately on returning to this tab instead of waiting for the
        // next interval tick — a background tab throttles setInterval anyway, so
        // without this a badge can sit stale for well over 8s until you switch back.
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                pollFreelancerNotifications();
            }
        });
    })();
</script>
<div id="headerDouble" class="d-none"></div>
