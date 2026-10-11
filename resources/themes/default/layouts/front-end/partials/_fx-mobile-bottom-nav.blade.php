{{-- ============================================================
     FinXCart mobile bottom nav (SHELL worker)
     Home / Explore / Saved / Account — fixed, safe-area aware.
     Hidden on checkout routes and on desktop. Body bottom padding
     is added only while this nav is rendered.
     ============================================================ --}}
@php
    $fxbRoute      = Route::currentRouteName();
    $fxbHideRoutes = ['checkout-details', 'checkout-payment', 'checkout-complete'];
    $fxbShow       = !in_array($fxbRoute, $fxbHideRoutes, true);
    $fxbWishCount  = session()->has('wish_list') ? count(session('wish_list')) : 0;
    $fxbSignedIn   = auth('customer')->check() || auth('seller')->check() || auth('freelancer')->check();

    $fxbExploreRoutes = ['products', 'product', 'categories', 'brands', 'discounted-products', 'search-shop', 'flash-deals'];
    $fxbAccountRoutes = ['user-account', 'wallet', 'account-oder', 'customer.auth.login', 'customer.auth.sign-up'];
    $fxbActive = 'home';
    if ($fxbRoute === 'wishlists') $fxbActive = 'saved';
    elseif (in_array($fxbRoute, $fxbExploreRoutes, true)) $fxbActive = 'explore';
    elseif (in_array($fxbRoute, $fxbAccountRoutes, true)) $fxbActive = 'account';
@endphp

@if ($fxbShow)
<style>
/* ================= fx mobile bottom nav ================= */
.fx .fxb { display: none; }
@media (max-width: 1023.98px) {
    /* reserve space so the fixed nav never covers the last item */
    body.fx { padding-bottom: calc(72px + env(safe-area-inset-bottom, 0px)); }

    .fx .fxb {
        display: flex;
        position: fixed;
        left: 0; right: 0; bottom: 0;
        z-index: 70;
        background: var(--fx-surface);
        border-top: 1px solid var(--fx-border);
        padding-bottom: env(safe-area-inset-bottom, 0px);
    }
    .fx .fxb-item {
        flex: 1 1 0;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 3px;
        min-height: 64px;
        padding: 8px 4px 6px;
        color: var(--fx-muted);
        font-size: 11px; font-weight: 600; line-height: 1.2;
        text-decoration: none;
        position: relative;
    }
    .fx .fxb-item svg { width: 24px; height: 24px; }
    .fx .fxb-item[aria-current="page"] { color: var(--fx-navy); }
    .fx .fxb-item[aria-current="page"]::before {
        content: ""; position: absolute; top: 0; left: 25%; right: 25%; height: 3px;
        border-radius: 0 0 4px 4px; background: var(--fx-amber);
    }
    .fx .fxb-count {
        position: absolute; top: 6px; inset-inline-start: calc(50% + 2px);
        min-width: 20px; height: 20px; padding: 0 5px;
        border-radius: var(--fx-radius-pill);
        background: var(--fx-amber); color: #1A1A1A;
        font-size: 12px; font-weight: 700; line-height: 1;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .fx .fxb-item:focus-visible { outline: 3px solid rgba(32, 63, 96, 0.45); outline-offset: -3px; }
}
@media (prefers-reduced-motion: no-preference) {
    .fx .fxb-item { transition: color 140ms ease; }
}
</style>

<nav class="fxb" aria-label="{{ translate('primary') }}">
    <a class="fxb-item" href="{{ route('home') }}" @if ($fxbActive === 'home') aria-current="page" @endif>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
        <span>{{ translate('home') }}</span>
    </a>
    <a class="fxb-item" href="{{ route('products') }}" @if ($fxbActive === 'explore') aria-current="page" @endif>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <span>{{ translate('explore') }}</span>
    </a>
    <a class="fxb-item" href="{{ route('wishlists') }}" @if ($fxbActive === 'saved') aria-current="page" @endif>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>
        <span>{{ translate('saved') }}</span>
        <span class="fxb-count" id="fxWishCountB">{{ $fxbWishCount }}</span>
    </a>
    <a class="fxb-item" href="{{ $fxbSignedIn ? route('user-account') : route('customer.auth.login') }}" @if ($fxbActive === 'account') aria-current="page" @endif>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
        <span>{{ translate('account') }}</span>
    </a>
</nav>
@endif
