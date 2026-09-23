@php
    $eCommerceLogo = getWebConfig(name: 'company_footer_logo');
    $sidebarSeller = auth('freelancer')->user();
    $sidebarVerification = $sidebarSeller?->vendorVerification;
    $sidebarKycMethod = getWebConfig('kyc_method') ?? 'manual';
    $sidebarProfileIncomplete = ($sidebarSeller?->status ?? null) !== 'approved' && ($sidebarVerification?->status ?? null) !== 'approved';
    $sidebarPayoutKycIncomplete = $sidebarKycMethod !== 'disabled' && ($sidebarSeller?->kyc_status ?? null) !== 'approved';
@endphp
<style>
    .vendor-verification-menu-link {
        display: flex !important;
        align-items: center;
    }
    .vendor-verification-menu-text {
        min-width: 0;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .vendor-sidebar-required-badge {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border: 1px solid rgba(255, 255, 255, .22);
        border-radius: 999px;
        color: #fff;
        background: rgba(255, 255, 255, .15);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .14);
        font-size: 9px;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: .35px;
        text-transform: uppercase;
    }
</style>
<div id="sidebarMain" class="d-none">
    <aside style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
           class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered  ">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between side-logo dashboard-navbar-side-logo-wrapper">
                <a class="navbar-brand" href="{{route('freelancer.dashboard.index')}}" aria-label="Front">
                    <img class="navbar-brand-logo-mini for-seller-logo"
                         src="{{ getStorageImages(path: $eCommerceLogo, type: 'backend-logo') }}"
                         alt="{{ translate('logo') }}">
                </a>
                <button type="button"
                        class="d-none js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
                    <i class="tio-clear tio-lg"></i>
                </button>
                <button type="button" class="js-navbar-vertical-aside-toggle-invoker close me-3">
                    <i class="tio-first-page navbar-vertical-aside-toggle-short-align"></i>
                    <i class="tio-last-page navbar-vertical-aside-toggle-full-align"></i>
                </button>
            </div>
            <div class="navbar-vertical-footer-offset pb-0">
                <div class="navbar-vertical-content">
                    <div class="sidebar--search-form pb-3 pt-4 mx-3">
                        <div class="search--form-group">
                            <button type="button" class="btn"><i class="tio-search"></i></button>
                            <input type="text" class="js-form-search form-control form--control" id="search-bar-input"
                                   placeholder="{{translate('search_menu').'...'}}">
                        </div>
                    </div>
                    <ul class="navbar-nav navbar-nav-lg nav-tabs">
                        <li class="navbar-vertical-aside-has-menu {{Request::is('freelancer/dashboard*')?'show active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('freelancer.dashboard.index')}}" title="{{translate('dashboard')}}">
                                <i class="tio-home-vs-1-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('dashboard')}}
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <small class="nav-subtitle">{{translate('service_management')}}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{Request::is('freelancer/portfolio*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('freelancer.portfolio.index')}}" title="{{translate('manage_portfolio')}}">
                                <i class="tio-photo-gallery-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('manage_portfolio')}}
                                </span>
                            </a>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{Request::is('freelancer/services*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('freelancer.services.index')}}" title="{{translate('manage_services')}}">
                                <i class="tio-briefcase nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('manage_services')}}
                                </span>
                            </a>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{Request::is('freelancer/quotes*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('freelancer.quotes.index')}}" title="{{translate('quote_requests')}}">
                                <i class="tio-receipt-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate d-flex align-items-center justify-content-between gap-2">
                                    <span>{{translate('quote_requests')}}</span>
                                    @if(($pendingQuoteCount ?? 0) > 0)
                                        <span class="vendor-sidebar-required-badge">{{ $pendingQuoteCount }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{Request::is('freelancer/contracts*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('freelancer.contracts.index')}}" title="{{translate('contracts')}}">
                                <i class="tio-document-text-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('contracts')}}
                                </span>
                            </a>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{Request::is('freelancer/wallet*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('freelancer.wallet.index')}}" title="{{translate('wallet')}}">
                                <i class="tio-wallet-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('wallet')}}
                                </span>
                            </a>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{Request::is('freelancer/ledger*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('freelancer.ledger.index')}}" title="{{translate('ledger')}}">
                                <i class="tio-receipt-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('ledger')}}
                                </span>
                            </a>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{Request::is('freelancer/reviews*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('freelancer.reviews.index')}}" title="{{translate('reviews')}}">
                                <i class="tio-star-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('reviews')}}
                                </span>
                            </a>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{Request::is('freelancer/messages/index*')?'active':''}}">
                            <a id="freelancerSidebarChatCustomerToggle" class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('freelancer.messages.index', ['type' => 'customer'])}}" title="{{translate('messages')}}">
                                <i class="tio-chat nav-icon"></i>
                                <span id="freelancerSidebarChatCustomerToggleInner" class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate d-flex align-items-center justify-content-between gap-2">
                                    <span>{{translate('messages')}}</span>
                                    @php($unseenMessageCount = (int) ($chattingCounts->unseen_customer ?? 0))
                                    @if($unseenMessageCount > 0)
                                        <span id="freelancerSidebarChatCustomerBadge" class="vendor-sidebar-required-badge">{{ $unseenMessageCount }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{Request::is('freelancer/messages/admin')?'active':''}}">
                            <a id="freelancerSidebarChatAdminToggle" class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('freelancer.messages.admin')}}" title="{{translate('messages_from_admin')}}">
                                <i class="tio-verified nav-icon"></i>
                                <span id="freelancerSidebarChatAdminToggleInner" class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate d-flex align-items-center justify-content-between gap-2">
                                    <span>{{translate('messages_from_admin')}}</span>
                                    @php($unseenAdminMessageCount = (int) ($chattingCounts->unseen_admin ?? 0))
                                    @if($unseenAdminMessageCount > 0)
                                        <span id="freelancerSidebarChatAdminBadge" class="vendor-sidebar-required-badge">{{ $unseenAdminMessageCount }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <small class="nav-subtitle">{{translate('account')}}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{Request::is('freelancer/profile*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('freelancer.profile.index')}}" title="{{translate('profile')}}">
                                <i class="tio-user-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('profile')}}
                                </span>
                            </a>
                        </li>
                        @if($sidebarProfileIncomplete)
                            <li class="navbar-vertical-aside-has-menu {{Request::is('freelancer/verification*')?'active':''}}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link vendor-verification-menu-link"
                                   href="{{ route('freelancer.verification.form') }}" title="{{translate('verification')}}">
                                    <i class="tio-verified nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements vendor-verification-menu-text d-flex align-items-center justify-content-between gap-2">
                                        <span class="d-block text-truncate text-capitalize">{{translate('verification')}}</span>
                                        <span class="vendor-sidebar-required-badge">Required</span>
                                    </span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </aside>
</div>
