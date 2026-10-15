{{-- ============================================================
     FinXCart light-first header (SHELL worker — quinn/finxcart-light-redesign)
     Scope: .fx .fxh-* (header), .fx .fxd-* (drawer). Foundation
     primitives come from fx-redesign.css (NOT edited here).
     Backend behavior preserved: #user_cart_count / #wish_count IDs
     (updated by cart-details.js & custom.js), customer notification
     poll (same IDs), currency AJAX (.get-currency-change-function /
     currencyChangeFunction), announcement bar, search route/params.
     ============================================================ --}}
@php
    $fxAnnouncement = getWebConfig(name: 'announcement');
    $fxCategories   = \App\Utils\CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting();
    $fxCart         = \App\Utils\CartManager::getCartListQuery();
    $fxWishCount    = session()->has('wish_list') ? count(session('wish_list')) : 0;
    $fxRoute        = Route::currentRouteName();
    $fxCurrencyModel = getWebConfig(name: 'currency_model');
    $fxCurrencies    = class_exists('\App\Models\Currency')
        ? Cache::remember(CACHE_FOR_ACTIVE_CURRENCY_LIST, CACHE_FOR_3_HOURS, fn() => \App\Models\Currency::where('status', 1)->get())
        : collect();
    $fxIsCustomer   = auth('customer')->check();
    $fxIsSeller     = auth('seller')->check();
    $fxIsFreelancer = auth('freelancer')->check();
    // Signed-in = any of the three guards (matches legacy header's guest check:
    // !customer && !seller && !freelancer)
    $fxSignedIn     = $fxIsCustomer || $fxIsSeller || $fxIsFreelancer;
    $fxCompany      = $web_config['company_name'] ?? 'FinXCart';

    // 5-destination nav: resolve category landing pages by slug (no hardcoded IDs)
    $fxSolutionsCat = $fxCategories->firstWhere('slug', 'solutions');
    $fxProvidersCat = $fxCategories->firstWhere('slug', 'providers');
    $fxEventsCat    = $fxCategories->firstWhere('slug', 'events');

    // Mirrors the legacy product-filter URL shapes exactly
    $fxCatUrl = function ($cat, $level = 'root') {
        if (($cat->slug ?? null) === 'latest-listings') {
            return route('products', ['data_from' => 'latest', 'page' => 1]);
        }
        $key = $level === 'sub' ? 'sub_category_id' : ($level === 'subsub' ? 'sub_sub_category_id' : 'category_id');
        return route('products', [$key => $cat->id, 'data_from' => 'category', 'page' => 1]);
    };

    $fxExploreRoutes  = ['products', 'product', 'categories', 'brands', 'discounted-products', 'search-shop', 'flash-deals'];
    $fxExploreActive  = in_array($fxRoute, $fxExploreRoutes, true);
    $fxActiveCatId    = (int) (request('category_id') ?? request('sub_category_id') ?? request('sub_sub_category_id') ?? 0);

    // Mobile full-width search: discovery pages only (kept off checkout / account / auth)
    $fxDiscoveryRoutes   = ['home', 'products', 'product', 'categories', 'brands', 'vendors', 'brokers.index', 'brokers.show', 'influencers.index', 'influencers.show', 'hire-freelancer', 'search-shop', 'discounted-products', 'flash-deals'];
    $fxShowMobileSearch  = in_array($fxRoute, $fxDiscoveryRoutes, true);
@endphp

<style>
/* ================= fx header — navy marketplace ================= */
.fx .fxh { position: sticky; top: 0; z-index: 80; background: #1d2a7a; border-bottom: 0; }
/* Sticky header is in normal flow: cancel the old fixed-header compensation. */
body.fx main.main-content { margin-top: 0 !important; }

.fx .fxh-bar-inner { display: flex; align-items: center; gap: var(--fx-s3); padding-top: var(--fx-s3); padding-bottom: var(--fx-s3); }
.fx .fxh-logo { display: inline-flex; align-items: center; flex-shrink: 0; background: #fff; border-radius: 12px; padding: 6px 14px; line-height: 0; }
.fx .fxh-logo img { display: block; height: 34px; width: auto; max-width: 180px; object-fit: contain; }

/* desktop search (flexes between logo and actions) */
.fx .fxh-search { display: none; flex: 1 1 auto; min-width: 0; }
@media (min-width: 1024px) { .fx .fxh-search { display: flex; } }
.fx .fxh-search .fx-search__input { min-width: 0; }

/* mobile search row: full-width beneath the compact bar */
.fx .fxh-msearch { display: none; padding-bottom: var(--fx-s3); }
.fx .fxh-msearch .fx-search { max-width: none; }
@media (max-width: 1023.98px) { .fx .fxh-msearch--on { display: block; } }

/* icon actions */
.fx .fxh-actions { display: flex; align-items: center; gap: var(--fx-s1); margin-left: auto; flex-shrink: 0; }
.fx .fxh-action { position: relative; display: inline-flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px; min-width: 48px; min-height: 48px; padding: 4px 8px; border: 0; border-radius: var(--fx-radius-md); background: none; color: rgba(255,255,255,.94); font-size: 11px; font-weight: 600; line-height: 1.2; text-decoration: none; cursor: pointer; }
.fx .fxh-action svg { width: 24px; height: 24px; }
.fx .fxh-action:hover { background: rgba(255,255,255,.14); color: #fff; }
.fx .fxh-count { position: absolute; top: 1px; inset-inline-end: 4px; min-width: 20px; height: 20px; padding: 0 5px; border-radius: var(--fx-radius-pill); background: var(--fx-amber); color: #1A1A1A; font-size: 12px; font-weight: 700; line-height: 1; display: inline-flex; align-items: center; justify-content: center; }

/* burger (mobile) */
.fx .fxh-burger { display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border: 0; border-radius: var(--fx-radius-md); background: none; color: #fff; cursor: pointer; flex-shrink: 0; }
.fx .fxh-burger svg { width: 26px; height: 26px; }
.fx .fxh-burger:hover { background: rgba(255,255,255,.14); }
@media (min-width: 1024px) { .fx .fxh-burger { display: none; } }

/* account dropdown */
.fx .fxh-acct { position: relative; }
.fx .fxh-acct-btn { display: inline-flex; align-items: center; gap: var(--fx-s2); min-height: 48px; padding: 4px 8px; border: 0; border-radius: var(--fx-radius-md); background: none; color: #fff; font-size: 14px; font-weight: 600; cursor: pointer; max-width: 168px; }
.fx .fxh-acct-btn:hover { background: rgba(255,255,255,.14); }
.fx .fxh-acct-btn .fxh-acct-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fx .fxh-acct-btn svg { width: 24px; height: 24px; flex-shrink: 0; }
.fx .fxh-acct-menu { position: absolute; top: calc(100% + 8px); inset-inline-end: 0; z-index: 85; min-width: 248px; max-width: calc(100vw - 32px); padding: var(--fx-s2); border: 1px solid var(--fx-border); border-radius: var(--fx-radius-md); background: var(--fx-surface); box-shadow: 0 12px 32px rgba(23, 43, 66, 0.14); }
.fx .fxh-acct-menu[hidden] { display: none; }
.fx .fxh-acct-head { padding: var(--fx-s3); border-bottom: 1px solid var(--fx-border); margin-bottom: var(--fx-s1); }
.fx .fxh-acct-head strong { display: block; font-size: 15px; overflow: hidden; text-overflow: ellipsis; }
.fx .fxh-acct-head span { font-size: 13px; color: var(--fx-muted); }
.fx .fxh-acct-link { display: flex; align-items: center; gap: var(--fx-s3); min-height: 48px; padding: var(--fx-s2) var(--fx-s3); border-radius: var(--fx-radius-sm); color: var(--fx-text); font-size: 15px; text-decoration: none; }
.fx .fxh-acct-link:hover { background: rgba(32, 63, 96, 0.06); }
.fx .fxh-acct-link svg { width: 20px; height: 20px; flex-shrink: 0; color: var(--fx-muted); }
.fx .fxh-acct-link--danger { color: var(--fx-danger); }
.fx .fxh-acct-link--danger svg { color: var(--fx-danger); }
.fx .fxh-acct-link .fxh-acct-badge { margin-inline-start: auto; font-size: 13px; font-weight: 700; color: var(--fx-navy); }

/* notification bell dropdown (same IDs as legacy: poll script depends on them) */
.fx .fxh-notif-menu { max-height: 400px; overflow-y: auto; }

.fx .fxh-acct .fx-btn { background: #fff; color: #1d2a7a; font-weight: 700; }
.fx .fxh-acct .fx-btn:hover { background: #f0f2ff; color: #1d2a7a; }

/* ================= secondary nav (desktop) — darker navy ================= */
.fx .fxh-nav { display: none; border-top: 1px solid rgba(255,255,255,.14); background: #16205e; position: relative; }
@media (min-width: 1024px) { .fx .fxh-nav { display: block; } }
.fx .fxh-navlist { display: flex; align-items: stretch; gap: var(--fx-s1); list-style: none; margin: 0; padding: 0; }
.fx .fxh-navitem { display: flex; align-items: center; position: static; }
.fx .fxh-navlink { display: inline-flex; align-items: center; min-height: 52px; padding: 0 var(--fx-s3); border-bottom: 3px solid transparent; color: rgba(255,255,255,.88); font-size: 16px; font-weight: 600; text-decoration: none; white-space: nowrap; }
.fx .fxh-navlink:hover { color: #fff; }
.fx .fxh-navlink[aria-current="page"] { color: #fff; border-bottom-color: var(--fx-amber); }
.fx .fxh-navtoggle { display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; margin-inline-start: -8px; border: 0; border-radius: var(--fx-radius-sm); background: none; color: rgba(255,255,255,.72); cursor: pointer; }
.fx .fxh-navtoggle svg { width: 18px; height: 18px; }
.fx .fxh-navtoggle:hover { background: rgba(255,255,255,.14); color: #fff; }
.fx .fxh-navtoggle[aria-expanded="true"] svg { transform: rotate(180deg); }

/* dropdown + mega panels */
.fx .fxh-drop, .fx .fxh-mega { position: absolute; top: 100%; left: 0; right: 0; z-index: 84; background: var(--fx-surface); border-bottom: 1px solid var(--fx-border); box-shadow: 0 16px 32px rgba(23, 43, 66, 0.10); }
.fx .fxh-drop[hidden], .fx .fxh-mega[hidden] { display: none; }
.fx .fxh-drop-inner { padding: var(--fx-s3) 0 var(--fx-s4); max-width: 720px; }
.fx .fxh-drop a { display: flex; align-items: center; min-height: 44px; padding: var(--fx-s2) var(--fx-s1); color: var(--fx-text); font-size: 15px; text-decoration: none; border-radius: var(--fx-radius-sm); }
.fx .fxh-drop a:hover { background: rgba(32, 63, 96, 0.06); color: var(--fx-navy); }
.fx .fxh-drop-all { font-weight: 700 !important; color: var(--fx-navy) !important; }
.fx .fxh-drop hr { border: 0; border-top: 1px solid var(--fx-border); margin: var(--fx-s2) 0; }

.fx .fxh-mega-inner { padding: var(--fx-s4) 0 var(--fx-s6); max-height: min(68vh, 600px); overflow-y: auto; }
.fx .fxh-mega-quick { display: flex; flex-wrap: wrap; gap: var(--fx-s2); margin-bottom: var(--fx-s4); }
.fx .fxh-mega-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--fx-s6) var(--fx-s4); }
.fx .fxh-mega-head { display: block; margin-bottom: var(--fx-s2); color: var(--fx-navy); font-size: 15px; font-weight: 700; text-decoration: none; }
.fx .fxh-mega-head:hover { text-decoration: underline; }
.fx .fxh-mega-col ul { list-style: none; margin: 0; padding: 0; }
.fx .fxh-mega-col li a { display: block; padding: 6px 0; color: var(--fx-muted); font-size: 14px; text-decoration: none; }
.fx .fxh-mega-col li a:hover { color: var(--fx-navy); }
.fx .fxh-mega-all { display: inline-block; margin-top: var(--fx-s2); font-size: 14px; font-weight: 700; color: var(--fx-navy); text-decoration: none; }
.fx .fxh-mega-all:hover { text-decoration: underline; }

/* announcement bar */
.fx .fxh-announce { position: relative; text-align: center; padding: 8px 44px 8px 16px; font-size: 14px; }
.fx .fxh-announce-close { position: absolute; inset-inline-end: 12px; top: 50%; transform: translateY(-50%); display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border: 0; background: none; color: inherit; font-size: 18px; font-weight: 700; cursor: pointer; }

/* screen-reader text */
.fx .fx-sr { position: absolute !important; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; border: 0; }

/* focus visibility */
.fx .fxh a:focus-visible, .fx .fxh button:focus-visible, .fx .fxh input:focus-visible, .fx .fxh select:focus-visible {
    outline: 3px solid rgba(32, 63, 96, 0.45); outline-offset: 2px;
}

/* ================= drawer ================= */
.fx .fxd-scrim { position: fixed; inset: 0; z-index: 90; background: rgba(23, 43, 66, 0.5); }
.fx .fxd-scrim[hidden] { display: none; }
.fx .fxd { position: fixed; top: 0; bottom: 0; inset-inline-start: 0; z-index: 91; display: flex; flex-direction: column; width: min(400px, 92vw); background: var(--fx-surface); border-start-end-radius: var(--fx-radius-lg); border-end-end-radius: var(--fx-radius-lg); }
.fx .fxd[hidden] { display: none; }
.fx .fxd-head { display: flex; align-items: center; gap: var(--fx-s2); padding: var(--fx-s3) var(--fx-s4); border-bottom: 1px solid var(--fx-border); }
.fx .fxd-title { flex: 1 1 auto; margin: 0; font-size: 18px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fx .fxd-iconbtn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-width: 48px; min-height: 48px; padding: 4px 8px; border: 0; border-radius: var(--fx-radius-md); background: none; color: var(--fx-text); font-size: 14px; font-weight: 600; cursor: pointer; }
.fx .fxd-iconbtn:hover { background: rgba(32, 63, 96, 0.06); }
.fx .fxd-iconbtn svg { width: 22px; height: 22px; }
.fx .fxd-body { flex: 1 1 auto; overflow-y: auto; padding: var(--fx-s2) var(--fx-s4) var(--fx-s6); -webkit-overflow-scrolling: touch; }
.fx .fxd-panel[hidden] { display: none; }
.fx .fxd-sec { margin: 0 0 var(--fx-s4); padding: 0 0 var(--fx-s4); border-bottom: 1px solid var(--fx-border); }
.fx .fxd-sec:last-child { border-bottom: 0; }
.fx .fxd-sec-title { margin: 0 0 var(--fx-s2); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--fx-muted); }
.fx .fxd-row { display: flex; align-items: center; gap: var(--fx-s3); width: 100%; min-height: 52px; padding: var(--fx-s2) var(--fx-s1); border: 0; border-radius: var(--fx-radius-sm); background: none; color: var(--fx-text); font-size: 16px; font-weight: 600; text-align: start; text-decoration: none; cursor: pointer; }
.fx .fxd-row:hover { background: rgba(32, 63, 96, 0.06); }
.fx .fxd-row svg { width: 22px; height: 22px; flex-shrink: 0; color: var(--fx-muted); }
.fx .fxd-row .fxd-chev { margin-inline-start: auto; }
.fx .fxd-row .fxd-count { margin-inline-start: auto; min-width: 24px; height: 24px; padding: 0 7px; border-radius: var(--fx-radius-pill); background: var(--fx-amber); color: #1A1A1A; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
.fx .fxd-viewall { display: flex; align-items: center; min-height: 48px; padding: var(--fx-s2) var(--fx-s1); font-size: 15px; font-weight: 700; color: var(--fx-navy); text-decoration: none; }
.fx .fxd-select { width: 100%; min-height: 48px; font-size: 16px; padding: var(--fx-s2) var(--fx-s3); border: 1px solid var(--fx-border); border-radius: var(--fx-radius-md); background: var(--fx-surface); color: var(--fx-text); }
.fx .fxd-lang { margin-top: var(--fx-s2); }

/* scroll lock while drawer open */
html.fx-lock { overflow: hidden; }

@keyframes fxd-in { from { opacity: 0; transform: translateX(-24px); } to { opacity: 1; transform: translateX(0); } }
html[dir="rtl"] .fx .fxd { animation-name: fxd-in-rtl; }
@keyframes fxd-in-rtl { from { opacity: 0; transform: translateX(24px); } to { opacity: 1; transform: translateX(0); } }

@media (prefers-reduced-motion: no-preference) {
    .fx .fxh-action, .fx .fxh-burger, .fx .fxh-navlink, .fx .fxh-navtoggle,
    .fx .fxh-acct-link, .fx .fxd-row, .fx .fxd-iconbtn, .fx .fxh-mega-col li a {
        transition: color 140ms ease, background-color 140ms ease, border-color 140ms ease;
    }
    .fx .fxd:not([hidden]) { animation: fxd-in 220ms ease; }
    html[dir="rtl"] .fx .fxd:not([hidden]) { animation-name: fxd-in-rtl; }
    .fx .fxd-scrim:not([hidden]) { animation: fx-fade 200ms ease; }
}
</style>

{{-- ============ HEADER ============ --}}
<header class="fxh" id="fxHeader">

    {{-- announcement bar (kept: same id/classes the close script expects) --}}
    @if (isset($fxAnnouncement) && ($fxAnnouncement['status'] ?? 0) == 1)
        <div class="fxh-announce" id="announcement"
             style="background-color: {{ $fxAnnouncement['color'] }}; color: {{ $fxAnnouncement['text_color'] }};">
            <span>{{ str_ireplace(['FinXCart', 'FINXCART'], $fxCompany, $fxAnnouncement['announcement']) }}</span>
            <button type="button" class="fxh-announce-close __close-announcement" aria-label="{{ translate('close') }}">&times;</button>
        </div>
    @endif

    <div class="fx-container fxh-bar-inner">
        {{-- mobile: drawer opener --}}
        <button type="button" class="fxh-burger" id="fxDrawerOpen"
                aria-label="{{ translate('open_menu') }}" aria-controls="fxDrawer" aria-expanded="false"
                aria-haspopup="dialog">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>

        {{-- logo (existing asset; clear space only) --}}
        <a class="fxh-logo" href="{{ route('home') }}" aria-label="{{ $fxCompany }} — {{ translate('home') }}">
            <img src="{{ getStorageImages(path: $web_config['web_logo'], type: 'logo') }}" alt="{{ $fxCompany }}">
        </a>

        {{-- desktop search --}}
        <form class="fx-search fxh-search" role="search" action="{{ route('products') }}" method="GET">
            <label class="fx-sr" for="fxSearchDesktop">{{ translate('search_for_products') }}</label>
            <input class="fx-search__input" id="fxSearchDesktop" type="search" name="name"
                   placeholder="{{ translate('search_for_products') }}" value="{{ request('name') }}"
                   autocomplete="off" aria-label="{{ translate('search_for_products') }}">
            <input type="hidden" name="global_search_input" value="1">
            <button class="fx-search__btn" type="submit">{{ translate('search') }}</button>
        </form>

        {{-- actions --}}
        <div class="fxh-actions">
            @if ($fxIsCustomer)
                {{-- notifications (IDs preserved for the live poll script below) --}}
                <div class="fxh-acct">
                    <a href="javascript:void(0)" class="fxh-action" id="customerNotificationDropdown"
                       role="button" tabindex="0" aria-haspopup="true" aria-expanded="false"
                       aria-label="{{ translate('notifications') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                        @if (($customerUnreadNotificationCount ?? 0) > 0)
                            <span class="fxh-count notification-badge">{{ $customerUnreadNotificationCount }}</span>
                        @endif
                    </a>
                    <div class="fxh-acct-menu fxh-notif-menu" id="fxNotifMenu" hidden role="menu" aria-label="{{ translate('notifications') }}">
                        <div id="customerNotificationItems">
                            @include('web-views.partials._customer-notification-items')
                        </div>
                    </div>
                </div>
            @endif

            {{-- saved / wishlist --}}
            <a class="fxh-action" href="{{ route('wishlists') }}" aria-label="{{ translate('saved_items') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>
                @if ($fxIsCustomer)
                    <span class="fxh-count" id="wish_count">{{ $fxWishCount }}</span>
                @endif
                <span aria-hidden="true">{{ translate('saved') }}</span>
            </a>

            {{-- cart --}}
            <a class="fxh-action" href="{{ route('shop-cart') }}" aria-label="{{ translate('cart') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h15l-1.5 9h-12z"/><path d="M6 7l-1-4H2"/><circle cx="9" cy="20" r="1.5"/><circle cx="17" cy="20" r="1.5"/></svg>
                <span class="fxh-count" id="user_cart_count">{{ $fxCart->count() }}</span>
                <span aria-hidden="true">{{ translate('cart') }}</span>
            </a>

            {{-- account --}}
            <div class="fxh-acct">
                @if ($fxSignedIn)
                    <button type="button" class="fxh-acct-btn" id="fxAcctBtn" aria-haspopup="true" aria-expanded="false" aria-controls="fxAcctMenu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
                        <span class="fxh-acct-name">
                            @if ($fxIsCustomer){{ ucwords(auth('customer')->user()->f_name ?? '') }}
                            @elseif ($fxIsSeller){{ optional(auth('seller')->user()->shop)->name ?? ucwords(auth('seller')->user()->f_name ?? '') }}
                            @else{{ ucwords(auth('freelancer')->user()->f_name ?? '') }}
                            @endif
                        </span>
                    </button>
                    <div class="fxh-acct-menu" id="fxAcctMenu" hidden role="menu" aria-label="{{ translate('account') }}">
                        @if ($fxIsCustomer)
                            <div class="fxh-acct-head">
                                <strong>{{ ucwords(auth('customer')->user()->f_name ?? '') }} {{ ucwords(auth('customer')->user()->l_name ?? '') }}</strong>
                                @if (!empty(auth('customer')->user()->phone))<span>{{ auth('customer')->user()->phone }}</span>@endif
                            </div>
                            <a class="fxh-acct-link" href="{{ route('wallet') }}" role="menuitem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="2" y="6" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                                {{ translate('my_wallet') }}
                                <span class="fxh-acct-badge">{{ webCurrencyConverter(auth('customer')->user()->wallet_balance ?? 0) }}</span>
                            </a>
                            <a class="fxh-acct-link" href="{{ route('account-oder') }}" role="menuitem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M21 8l-9-5-9 5v8l9 5 9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/></svg>
                                {{ translate('my_Order') }}
                            </a>
                            <a class="fxh-acct-link" href="{{ route('user-account') }}" role="menuitem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
                                {{ translate('my_Profile') }}
                            </a>
                            <a class="fxh-acct-link fxh-acct-link--danger" href="{{ route('customer.auth.logout') }}" role="menuitem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                                {{ translate('logout') }}
                            </a>
                        @endif
                        @if ($fxIsSeller)
                            <div class="fxh-acct-head">
                                <strong>{{ optional(auth('seller')->user()->shop)->name ?? ucwords(auth('seller')->user()->f_name ?? '') }}</strong>
                            </div>
                            <a class="fxh-acct-link" href="{{ route('vendor.dashboard.index') }}" role="menuitem">{{ translate('vendor_dashboard') }}</a>
                            <a class="fxh-acct-link fxh-acct-link--danger" href="{{ route('vendor.auth.logout') }}" role="menuitem">{{ translate('logout') }}</a>
                        @endif
                        @if ($fxIsFreelancer)
                            <div class="fxh-acct-head">
                                <strong>{{ ucwords(auth('freelancer')->user()->f_name ?? '') }}</strong>
                            </div>
                            <a class="fxh-acct-link" href="{{ route('freelancer.dashboard.index') }}" role="menuitem">{{ translate('freelancer_dashboard') }}</a>
                            <a class="fxh-acct-link fxh-acct-link--danger" href="{{ route('freelancer.auth.logout') }}" role="menuitem">{{ translate('logout') }}</a>
                        @endif
                    </div>
                @else
                    <a class="fx-btn fx-btn-sm" href="{{ route('customer.auth.login') }}">{{ translate('sign_in') }}</a>
                @endif
            </div>
        </div>
    </div>

    {{-- mobile full-width search (discovery pages only) --}}
    @if ($fxShowMobileSearch)
        <div class="fx-container fxh-msearch fxh-msearch--on">
            <form class="fx-search" role="search" action="{{ route('products') }}" method="GET">
                <label class="fx-sr" for="fxSearchMobile">{{ translate('search_for_products') }}</label>
                <input class="fx-search__input" id="fxSearchMobile" type="search" name="name"
                       placeholder="{{ translate('search_for_products') }}" value="{{ request('name') }}"
                       autocomplete="off" aria-label="{{ translate('search_for_products') }}">
                <input type="hidden" name="global_search_input" value="1">
                <button class="fx-search__btn" type="submit">{{ translate('search') }}</button>
            </form>
        </div>
    @endif

    {{-- ============ SECONDARY NAV: 5 destinations (desktop) ============ --}}
    @php($fxSolutionsActive = $fxSolutionsCat && $fxActiveCatId === (int) $fxSolutionsCat->id)
    @php($fxProvidersActive = $fxProvidersCat && $fxActiveCatId === (int) $fxProvidersCat->id)
    <nav class="fxh-nav" aria-label="{{ translate('primary_navigation') }}">
        <div class="fx-container">
            <ul class="fxh-navlist">

                {{-- Explore — grouped-categories mega menu --}}
                <li class="fxh-navitem">
                    <a class="fxh-navlink" href="{{ route('products') }}" @if ($fxExploreActive) aria-current="page" @endif>{{ translate('explore') }}</a>
                    @if ($fxCategories->count())
                        <button type="button" class="fxh-navtoggle" aria-expanded="false" aria-controls="fxMegaExplore" aria-label="{{ translate('browse_categories') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div class="fxh-mega" id="fxMegaExplore" hidden>
                            <div class="fx-container fxh-mega-inner">
                                <div class="fxh-mega-quick">
                                    <a class="fx-chip" href="{{ route('categories') }}">{{ translate('all_categories') }}</a>
                                    <a class="fx-chip" href="{{ route('products', ['data_from' => 'latest', 'page' => 1]) }}">{{ translate('latest_listings') }}</a>
                                    <a class="fx-chip" href="{{ route('discounted-products') }}">{{ translate('discounted') }}</a>
                                    <a class="fx-chip" href="{{ route('brands') }}">{{ translate('brands') }}</a>
                                </div>
                                <div class="fxh-mega-grid">
                                    @foreach ($fxCategories->take(8) as $fxCat)
                                        <div class="fxh-mega-col">
                                            <a class="fxh-mega-head" href="{{ $fxCatUrl($fxCat) }}">{{ $fxCat->name }}</a>
                                            @if ($fxCat->childes->count())
                                                <ul>
                                                    @foreach ($fxCat->childes->take(5) as $fxSub)
                                                        <li><a href="{{ $fxCatUrl($fxSub, 'sub') }}">{{ $fxSub->name }}</a></li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            <a class="fxh-mega-all" href="{{ $fxCatUrl($fxCat) }}">{{ translate('view_all_in') }} {{ $fxCat->name }} &rarr;</a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </li>

                {{-- Solutions --}}
                <li class="fxh-navitem">
                    <a class="fxh-navlink" href="{{ $fxSolutionsCat ? $fxCatUrl($fxSolutionsCat) : route('products') }}" @if ($fxSolutionsActive) aria-current="page" @endif>{{ translate('solutions') }}</a>
                    @if ($fxSolutionsCat && $fxSolutionsCat->childes->count())
                        <button type="button" class="fxh-navtoggle" aria-expanded="false" aria-controls="fxDropSolutions" aria-label="{{ translate('browse_solutions') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div class="fxh-drop" id="fxDropSolutions" hidden>
                            <div class="fx-container fxh-drop-inner">
                                @foreach ($fxSolutionsCat->childes->take(8) as $fxSub)
                                    <a href="{{ $fxCatUrl($fxSub, 'sub') }}">{{ $fxSub->name }}</a>
                                @endforeach
                                <a class="fxh-drop-all" href="{{ $fxCatUrl($fxSolutionsCat) }}">{{ translate('view_all_solutions') }} &rarr;</a>
                            </div>
                        </div>
                    @endif
                </li>

                {{-- Providers --}}
                <li class="fxh-navitem">
                    <a class="fxh-navlink" href="{{ $fxProvidersCat ? $fxCatUrl($fxProvidersCat) : route('vendors') }}" @if ($fxProvidersActive) aria-current="page" @endif>{{ translate('providers') }}</a>
                    <button type="button" class="fxh-navtoggle" aria-expanded="false" aria-controls="fxDropProviders" aria-label="{{ translate('browse_providers') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="fxh-drop" id="fxDropProviders" hidden>
                        <div class="fx-container fxh-drop-inner">
                            @if ($fxProvidersCat)
                                @foreach ($fxProvidersCat->childes->take(6) as $fxSub)
                                    <a href="{{ $fxCatUrl($fxSub, 'sub') }}">{{ $fxSub->name }}</a>
                                @endforeach
                                <a class="fxh-drop-all" href="{{ $fxCatUrl($fxProvidersCat) }}">{{ translate('view_all_providers') }} &rarr;</a>
                                <hr>
                            @endif
                            <a href="{{ route('vendors') }}">{{ translate('all_vendors') }}</a>
                            <a href="{{ route('brokers.index') }}">{{ translate('brokers') }}</a>
                            <a href="{{ route('influencers.index') }}">{{ translate('influencers') }}</a>
                            <a href="{{ route('vendor.auth.registration.index') }}">{{ translate('become_a_provider') }}</a>
                        </div>
                    </div>
                </li>

                {{-- Freelancers --}}
                <li class="fxh-navitem">
                    <a class="fxh-navlink" href="{{ route('hire-freelancer') }}" @if ($fxRoute === 'hire-freelancer' || $fxRoute === 'hire-freelancer.show' || $fxRoute === 'hire-freelancer.service-show') aria-current="page" @endif>{{ translate('freelancers') }}</a>
                    @if (isset($hireFreelancerMenu) && $hireFreelancerMenu->count())
                        <button type="button" class="fxh-navtoggle" aria-expanded="false" aria-controls="fxDropFreelancers" aria-label="{{ translate('browse_freelancers') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div class="fxh-drop" id="fxDropFreelancers" hidden>
                            <div class="fx-container fxh-drop-inner">
                                @foreach ($hireFreelancerMenu->take(8) as $fxFc)
                                    <a href="{{ route('hire-freelancer', ['category' => $fxFc->slug]) }}">{{ $fxFc->name }}</a>
                                @endforeach
                                <a class="fxh-drop-all" href="{{ route('hire-freelancer') }}">{{ translate('view_all_freelancers') }} &rarr;</a>
                            </div>
                        </div>
                    @endif
                </li>

                {{-- Resources (Events lives here) --}}
                <li class="fxh-navitem">
                    <a class="fxh-navlink" href="{{ route('helpTopic') }}" @if (in_array($fxRoute, ['helpTopic', 'contacts', 'my.account.help', 'customer.service', 'product.support'], true)) aria-current="page" @endif>{{ translate('resources') }}</a>
                    <button type="button" class="fxh-navtoggle" aria-expanded="false" aria-controls="fxDropResources" aria-label="{{ translate('browse_resources') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="fxh-drop" id="fxDropResources" hidden>
                        <div class="fx-container fxh-drop-inner">
                            @if ($fxEventsCat)
                                <a href="{{ $fxCatUrl($fxEventsCat) }}">{{ translate('events') }}</a>
                            @endif
                            <a href="{{ route('helpTopic') }}">{{ translate('help_topics') }}</a>
                            <a href="{{ route('contacts') }}">{{ translate('contact_us') }}</a>
                        </div>
                    </div>
                </li>

            </ul>
        </div>
    </nav>
</header>
{{-- ============ /HEADER ============ --}}

{{-- ============ MOBILE DRAWER ============ --}}
<div class="fxd-scrim" id="fxDrawerScrim" hidden></div>
<aside class="fxd" id="fxDrawer" role="dialog" aria-modal="true" aria-labelledby="fxDrawerTitle" hidden>
    <div class="fxd-head">
        <button type="button" class="fxd-iconbtn" id="fxDrawerBack" hidden>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            <span>{{ translate('back') }}</span>
        </button>
        <h2 class="fxd-title" id="fxDrawerTitle" tabindex="-1">{{ translate('menu') }}</h2>
        <button type="button" class="fxd-iconbtn" id="fxDrawerClose" aria-label="{{ translate('close_menu') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
    </div>

    <div class="fxd-body" id="fxDrawerBody">
        {{-- LEVEL: root --}}
        <div class="fxd-panel" data-fxd-panel="root">
            <div class="fxd-sec">
                <p class="fxd-sec-title">{{ translate('account') }}</p>
                @if ($fxSignedIn)
                    @if ($fxIsCustomer)
                        <a class="fxd-row" href="{{ route('wallet') }}">{{ translate('my_wallet') }}</a>
                        <a class="fxd-row" href="{{ route('account-oder') }}">{{ translate('my_Order') }}</a>
                        <a class="fxd-row" href="{{ route('user-account') }}">{{ translate('my_Profile') }}</a>
                        <a class="fxd-row" href="{{ route('customer.auth.logout') }}">{{ translate('logout') }}</a>
                    @elseif ($fxIsSeller)
                        <a class="fxd-row" href="{{ route('vendor.dashboard.index') }}">{{ translate('vendor_dashboard') }}</a>
                        <a class="fxd-row" href="{{ route('vendor.auth.logout') }}">{{ translate('logout') }}</a>
                    @else
                        <a class="fxd-row" href="{{ route('freelancer.dashboard.index') }}">{{ translate('freelancer_dashboard') }}</a>
                        <a class="fxd-row" href="{{ route('freelancer.auth.logout') }}">{{ translate('logout') }}</a>
                    @endif
                @else
                    <a class="fx-btn" href="{{ route('customer.auth.login') }}" style="width:100%;">{{ translate('sign_in') }}</a>
                    <a class="fxd-row" href="{{ route('vendor.auth.login.index') }}">{{ translate('vendor_login') }}</a>
                    <a class="fxd-row" href="{{ route('freelancer.auth.login.index') }}">{{ translate('freelancer_login') }}</a>
                @endif
            </div>

            <div class="fxd-sec">
                <p class="fxd-sec-title">{{ translate('browse') }}</p>
                <button type="button" class="fxd-row" data-fxd-go="p-explore">
                    <span>{{ translate('explore') }}</span>
                    <svg class="fxd-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </button>
                <button type="button" class="fxd-row" data-fxd-go="p-solutions">
                    <span>{{ translate('solutions') }}</span>
                    <svg class="fxd-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </button>
                <button type="button" class="fxd-row" data-fxd-go="p-providers">
                    <span>{{ translate('providers') }}</span>
                    <svg class="fxd-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </button>
                <button type="button" class="fxd-row" data-fxd-go="p-freelancers">
                    <span>{{ translate('freelancers') }}</span>
                    <svg class="fxd-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </button>
                <button type="button" class="fxd-row" data-fxd-go="p-resources">
                    <span>{{ translate('resources') }}</span>
                    <svg class="fxd-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </button>
            </div>

            <div class="fxd-sec">
                <p class="fxd-sec-title">{{ translate('saved') }}</p>
                <a class="fxd-row" href="{{ route('wishlists') }}">
                    <span>{{ translate('saved_items') }}</span>
                    <span class="fxd-count" id="fxWishCountM" @if (!$fxIsCustomer) hidden @endif>{{ $fxWishCount }}</span>
                </a>
                <a class="fxd-row" href="{{ route('shop-cart') }}">
                    <span>{{ translate('cart') }}</span>
                    <span class="fxd-count" id="fxCartCountM">{{ $fxCart->count() }}</span>
                </a>
            </div>
        </div>

        {{-- LEVEL: explore --}}
        <div class="fxd-panel" data-fxd-panel="p-explore" data-fxd-title="{{ translate('explore') }}" hidden>
            <div class="fxd-sec">
                <a class="fxd-row" href="{{ route('categories') }}">{{ translate('all_categories') }}</a>
                <a class="fxd-row" href="{{ route('products', ['data_from' => 'latest', 'page' => 1]) }}">{{ translate('latest_listings') }}</a>
                <a class="fxd-row" href="{{ route('discounted-products') }}">{{ translate('discounted') }}</a>
                <a class="fxd-row" href="{{ route('brands') }}">{{ translate('brands') }}</a>
            </div>
            @foreach ($fxCategories as $fxCat)
                <div class="fxd-sec">
                    <a class="fxd-row" href="{{ $fxCatUrl($fxCat) }}"><span>{{ $fxCat->name }}</span></a>
                    @foreach ($fxCat->childes->take(4) as $fxSub)
                        <a class="fxd-row" href="{{ $fxCatUrl($fxSub, 'sub') }}" style="padding-inline-start: var(--fx-s6); font-weight:400;"><span>{{ $fxSub->name }}</span></a>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- LEVEL: solutions --}}
        <div class="fxd-panel" data-fxd-panel="p-solutions" data-fxd-title="{{ translate('solutions') }}" hidden>
            <div class="fxd-sec">
                @if ($fxSolutionsCat)
                    @foreach ($fxSolutionsCat->childes as $fxSub)
                        <a class="fxd-row" href="{{ $fxCatUrl($fxSub, 'sub') }}"><span>{{ $fxSub->name }}</span></a>
                    @endforeach
                    <a class="fxd-viewall" href="{{ $fxCatUrl($fxSolutionsCat) }}">{{ translate('view_all_solutions') }} &rarr;</a>
                @else
                    <a class="fxd-row" href="{{ route('products') }}"><span>{{ translate('all_products') }}</span></a>
                @endif
            </div>
        </div>

        {{-- LEVEL: providers --}}
        <div class="fxd-panel" data-fxd-panel="p-providers" data-fxd-title="{{ translate('providers') }}" hidden>
            <div class="fxd-sec">
                @if ($fxProvidersCat)
                    @foreach ($fxProvidersCat->childes->take(8) as $fxSub)
                        <a class="fxd-row" href="{{ $fxCatUrl($fxSub, 'sub') }}"><span>{{ $fxSub->name }}</span></a>
                    @endforeach
                    <a class="fxd-viewall" href="{{ $fxCatUrl($fxProvidersCat) }}">{{ translate('view_all_providers') }} &rarr;</a>
                @endif
                <a class="fxd-row" href="{{ route('vendors') }}"><span>{{ translate('all_vendors') }}</span></a>
                <a class="fxd-row" href="{{ route('brokers.index') }}"><span>{{ translate('brokers') }}</span></a>
                <a class="fxd-row" href="{{ route('influencers.index') }}"><span>{{ translate('influencers') }}</span></a>
                <a class="fxd-row" href="{{ route('vendor.auth.registration.index') }}"><span>{{ translate('become_a_provider') }}</span></a>
            </div>
        </div>

        {{-- LEVEL: freelancers --}}
        <div class="fxd-panel" data-fxd-panel="p-freelancers" data-fxd-title="{{ translate('freelancers') }}" hidden>
            <div class="fxd-sec">
                @if (isset($hireFreelancerMenu) && $hireFreelancerMenu->count())
                    @foreach ($hireFreelancerMenu as $fxFc)
                        <a class="fxd-row" href="{{ route('hire-freelancer', ['category' => $fxFc->slug]) }}"><span>{{ $fxFc->name }}</span></a>
                    @endforeach
                @endif
                <a class="fxd-viewall" href="{{ route('hire-freelancer') }}">{{ translate('view_all_freelancers') }} &rarr;</a>
                <a class="fxd-row" href="{{ route('freelancer.auth.login.index') }}"><span>{{ translate('join_as_freelancer') }}</span></a>
            </div>
        </div>

        {{-- LEVEL: resources --}}
        <div class="fxd-panel" data-fxd-panel="p-resources" data-fxd-title="{{ translate('resources') }}" hidden>
            <div class="fxd-sec">
                @if ($fxEventsCat)
                    <a class="fxd-row" href="{{ $fxCatUrl($fxEventsCat) }}"><span>{{ translate('events') }}</span></a>
                @endif
                <a class="fxd-row" href="{{ route('helpTopic') }}"><span>{{ translate('help_topics') }}</span></a>
                <a class="fxd-row" href="{{ route('contacts') }}"><span>{{ translate('contact_us') }}</span></a>
            </div>
            <div class="fxd-sec">
                <p class="fxd-sec-title">{{ translate('currency') }}</p>
                <label class="fx-sr" for="fxDrawerCurrency">{{ translate('currency') }}</label>
                <select class="fxd-select js-fx-currency" id="fxDrawerCurrency">
                    @foreach ($fxCurrencies ?? [] as $fxCur)
                        <option value="{{ $fxCur->code }}" {{ session('currency_code') == $fxCur->code ? 'selected' : '' }}>{{ $fxCur->name }} ({{ $fxCur->code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="fxd-sec">
                <p class="fxd-sec-title">{{ translate('language') }}</p>
                <div class="fxd-lang">@include('layouts.front-end.partials.gtranslater')</div>
            </div>
        </div>
    </div>
</aside>
{{-- ============ /MOBILE DRAWER ============ --}}

{{-- ============ HEADER SCRIPTS ============ --}}
<script>
(function () {
    'use strict';

    /* ---- announcement close ---- */
    var annClose = document.querySelector('#fxHeader .__close-announcement');
    if (annClose) {
        annClose.addEventListener('click', function () {
            var bar = document.getElementById('announcement');
            if (bar) bar.style.display = 'none';
        });
    }

    /* ---- generic disclosure helper (account menu, notification bell) ---- */
    function wireDisclosure(btnId, menuId) {
        var btn = document.getElementById(btnId);
        var menu = document.getElementById(menuId);
        if (!btn || !menu) return;
        function close() { menu.hidden = true; btn.setAttribute('aria-expanded', 'false'); }
        function open() { menu.hidden = false; btn.setAttribute('aria-expanded', 'true'); }
        function toggle(e) {
            e.preventDefault(); e.stopPropagation();
            menu.hidden ? open() : close();
        }
        btn.addEventListener('click', toggle);
        btn.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') toggle(e);
        });
        document.addEventListener('click', function (e) {
            if (!menu.hidden && !menu.contains(e.target) && e.target !== btn && !btn.contains(e.target)) close();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !menu.hidden) { close(); btn.focus(); }
        });
    }
    wireDisclosure('fxAcctBtn', 'fxAcctMenu');
    wireDisclosure('customerNotificationDropdown', 'fxNotifMenu');

    /* ---- desktop mega menu toggles (click/tap; keyboard-native buttons) ---- */
    var navToggles = document.querySelectorAll('.fxh-navtoggle');
    function closeAllMegas(except) {
        navToggles.forEach(function (t) {
            if (t === except) return;
            t.setAttribute('aria-expanded', 'false');
            var panel = document.getElementById(t.getAttribute('aria-controls'));
            if (panel) panel.hidden = true;
        });
    }
    navToggles.forEach(function (toggle) {
        var panel = document.getElementById(toggle.getAttribute('aria-controls'));
        if (!panel) return;
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var willOpen = panel.hidden;
            closeAllMegas(toggle);
            panel.hidden = !willOpen;
            toggle.setAttribute('aria-expanded', String(willOpen));
        });
        toggle.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                panel.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
                toggle.focus();
            }
        });
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.fxh-navitem')) closeAllMegas(null);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllMegas(null);
    });

    /* ---- mobile drawer: open/close, drill-down, focus trap, scroll lock ---- */
    var drawer = document.getElementById('fxDrawer');
    var scrim = document.getElementById('fxDrawerScrim');
    var openBtn = document.getElementById('fxDrawerOpen');
    var closeBtn = document.getElementById('fxDrawerClose');
    var backBtn = document.getElementById('fxDrawerBack');
    var titleEl = document.getElementById('fxDrawerTitle');
    var defaultTitle = titleEl ? titleEl.textContent : 'Menu';
    var lastFocused = null;

    function focusables() {
        if (!drawer) return [];
        var els = drawer.querySelectorAll('a[href], button:not([disabled]), input, select, [tabindex]:not([tabindex="-1"])');
        return Array.prototype.filter.call(els, function (el) {
            return el.getAttribute('aria-hidden') !== 'true' && el.offsetParent !== null;
        });
    }
    function goPanel(name) {
        drawer.querySelectorAll('[data-fxd-panel]').forEach(function (p) {
            p.hidden = p.getAttribute('data-fxd-panel') !== name;
        });
        var isRoot = name === 'root';
        backBtn.hidden = isRoot;
        var active = drawer.querySelector('[data-fxd-panel="' + name + '"]');
        titleEl.textContent = (!isRoot && active && active.getAttribute('data-fxd-title')) || defaultTitle;
        drawer.querySelector('.fxd-body').scrollTop = 0;
        (isRoot ? closeBtn : backBtn).focus();
    }
    function openDrawer() {
        lastFocused = document.activeElement;
        drawer.hidden = false;
        scrim.hidden = false;
        document.documentElement.classList.add('fx-lock');
        if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
        goPanel('root');
    }
    function closeDrawer() {
        drawer.hidden = true;
        scrim.hidden = true;
        document.documentElement.classList.remove('fx-lock');
        if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
        if (lastFocused && document.contains(lastFocused)) lastFocused.focus();
    }
    if (drawer && openBtn) {
        openBtn.addEventListener('click', openDrawer);
        closeBtn.addEventListener('click', closeDrawer);
        scrim.addEventListener('click', closeDrawer);
        backBtn.addEventListener('click', function () { goPanel('root'); });
        drawer.querySelectorAll('[data-fxd-go]').forEach(function (btn) {
            btn.addEventListener('click', function () { goPanel(btn.getAttribute('data-fxd-go')); });
        });
        drawer.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { closeDrawer(); return; }
            if (e.key !== 'Tab') return;
            var f = focusables();
            if (!f.length) return;
            var first = f[0], last = f[f.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        });
    }

    /* ---- currency selects (footer + drawer) reuse the existing AJAX path ---- */
    document.querySelectorAll('.js-fx-currency').forEach(function (sel) {
        sel.addEventListener('change', function () {
            if (typeof currencyChangeFunction === 'function') currencyChangeFunction(sel.value);
        });
    });

    /* ---- mirror live cart/wishlist counts into mobile badges ----
       cart-details.js / custom.js update #user_cart_count and #wish_count
       (single canonical IDs kept in the desktop header). The mobile header
       and bottom nav show mirrors kept in sync via MutationObserver. */
    function mirrorCount(sourceId, mirrorIds) {
        var src = document.getElementById(sourceId);
        if (!src) return;
        var sync = function () {
            mirrorIds.forEach(function (id) {
                var m = document.getElementById(id);
                if (m) m.textContent = src.textContent;
            });
        };
        new MutationObserver(sync).observe(src, { childList: true, characterData: true, subtree: true });
        sync();
    }
    mirrorCount('user_cart_count', ['fxCartCountM', 'fxCartCountB']);
    mirrorCount('wish_count', ['fxWishCountM', 'fxWishCountB']);
})();
</script>

@auth('customer')
<script>
/* Live notification poll — same endpoints/IDs as before, restyled header. */
(function () {
    var badgeParent = document.getElementById('customerNotificationDropdown');
    var itemsContainer = document.getElementById('customerNotificationItems');
    if (!badgeParent || !itemsContainer) return;

    function renderBadge(count) {
        var existing = badgeParent.querySelector('.fxh-count');
        if (count > 0) {
            if (existing) { existing.textContent = count; }
            else {
                var span = document.createElement('span');
                span.className = 'fxh-count notification-badge';
                span.textContent = count;
                badgeParent.appendChild(span);
            }
        } else if (existing) { existing.remove(); }
    }

    function pollCustomerNotifications() {
        fetch('{{ route('customer-notifications.poll') }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            cache: 'no-store'
        }).then(function (r) { return r.ok ? r.json() : null; })
          .then(function (data) {
              if (!data) return;
              renderBadge(data.count);
              itemsContainer.innerHTML = data.html;
          }).catch(function () { /* silent: next tick retries */ });
    }

    setInterval(pollCustomerNotifications, 8000);
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') pollCustomerNotifications();
    });

    itemsContainer.addEventListener('submit', function (e) {
        var form = e.target.closest('.customer-notification-mark-all-form');
        if (!form) return;
        e.preventDefault();
        var tokenInput = form.querySelector('input[name="_token"]');
        fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': tokenInput ? tokenInput.value : ''
            },
            credentials: 'same-origin'
        }).then(function (r) {
            if (!r.ok) { window.location.reload(); return; }
            pollCustomerNotifications();
        }).catch(function () { /* leave list as-is */ });
    });
})();
</script>
@endauth
