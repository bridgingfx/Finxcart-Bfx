{{--
    _fx-home-marketplace.blade.php — marketplace-style homepage mirroring finxcart.com's
    section layout with clean-room copy. Expects: $recommended, $newArrivals,
    $tileCategories (all optional collections; every section hides when empty).
--}}
@php
    $mpRec = isset($recommended) && $recommended ? $recommended : collect();
    $mpNew = isset($newArrivals) && $newArrivals ? $newArrivals : collect();
    $mpCats = isset($tileCategories) && $tileCategories ? $tileCategories : collect();
    $mpAll = $mpRec->merge($mpNew)->unique('id')->values();
    $mpSale = $mpAll->filter(function ($p) {
        try { return getProductPriceByType(product: $p, type: 'discount', result: 'value') > 0; }
        catch (\Throwable $e) { return false; }
    })->values();
    $mpHalfOff = $mpAll->filter(function ($p) {
        try {
            $unit = (float) ($p->unit_price ?? 0);
            $disc = getProductPriceByType(product: $p, type: 'discount', result: 'value');
            return $unit > 0 && $disc > 0 && ($disc / $unit) >= 0.5;
        } catch (\Throwable $e) { return false; }
    })->values();
    $isWeb = fn($p) => (bool) preg_match('/website|template|landing|theme/i', (string) ($p->name ?? ''));
    $mpWeb = $mpAll->filter($isWeb)->take(4)->values();
    $mpAddons = $mpAll->reject($isWeb)->sortBy('unit_price')->take(8)->values();
    $mpSolutions = [
        ['CRM Solutions', 'crm'], ['White Label Solutions', 'white label'],
        ['Blockchain Solutions', 'blockchain'], ['Token & Coin Services', 'token'],
        ['ICO / IEO / IDO Solutions', 'ido'], ['Compliance Solutions', 'compliance'],
        ['Business Setup', 'business setup'],
    ];
    $mpApis = [
        ['title' => 'Payment APIs', 'term' => 'payment', 'body' => 'Gateways, wallets and payout rails for your platform.'],
        ['title' => 'Trading APIs', 'term' => 'api', 'body' => 'Market data, execution and account management endpoints.'],
        ['title' => 'CRM APIs', 'term' => 'crm', 'body' => 'Sync clients, documents and communications automatically.'],
    ];
@endphp

{{-- ============ 1. Hero carousel + 3 promo cards ============ --}}
<section class="fxmp-hero" aria-label="{{ translate('Marketplace highlights') }}">
    <div class="fx-container">
        <div class="fxmp-hero__grid">
            <div class="fxmp-hero__carousel" data-fxmp-carousel>
                <div class="fxmp-hero__slides">
                    <div class="fxmp-hero__slide is-active" data-fxmp-slide>
                        <span class="fxmp-hero__eyebrow">{{ translate('Fintech marketplace') }}</span>
                        <h2 class="fxmp-hero__title">{{ translate('Buy and sell fintech services in one place') }}</h2>
                        <p class="fxmp-hero__sub">{{ translate('Trading tools, broker software and expert services — delivered instantly by verified providers.') }}</p>
                        <a class="fxmp-hero__cta" href="{{ route('products') }}">{{ translate('Browse marketplace') }}</a>
                    </div>
                    <div class="fxmp-hero__slide" data-fxmp-slide hidden>
                        <span class="fxmp-hero__eyebrow">{{ translate('For vendors') }}</span>
                        <h2 class="fxmp-hero__title">{{ translate('List your services. Reach brokers worldwide.') }}</h2>
                        <p class="fxmp-hero__sub">{{ translate('Join the marketplace and sell your fintech products to a global audience.') }}</p>
                        <a class="fxmp-hero__cta" href="{{ route('contacts') }}">{{ translate('Become a vendor') }}</a>
                    </div>
                    <div class="fxmp-hero__slide" data-fxmp-slide hidden>
                        <span class="fxmp-hero__eyebrow">{{ translate('Members') }}</span>
                        <h2 class="fxmp-hero__title">{{ translate('Sign in for faster checkout and deals') }}</h2>
                        <p class="fxmp-hero__sub">{{ translate('Track orders, save favorites and unlock member pricing.') }}</p>
                        <a class="fxmp-hero__cta" href="{{ route('customer.auth.login') }}">{{ translate('Sign in') }}</a>
                    </div>
                </div>
                <div class="fxmp-hero__dots" role="tablist" aria-label="{{ translate('Slides') }}">
                    <button type="button" class="fxmp-hero__dot is-active" data-fxmp-dot="0" aria-label="{{ translate('Slide 1') }}"></button>
                    <button type="button" class="fxmp-hero__dot" data-fxmp-dot="1" aria-label="{{ translate('Slide 2') }}"></button>
                    <button type="button" class="fxmp-hero__dot" data-fxmp-dot="2" aria-label="{{ translate('Slide 3') }}"></button>
                </div>
            </div>
            <div class="fxmp-hero__promos">
                <a class="fxmp-promo" href="{{ route('products') }}">
                    <img src="{{ theme_asset(path: 'public/assets/front-end/img/fx-promos/promo-mt5.webp') }}" alt="{{ translate('Fintech services') }}" loading="lazy">
                    <span class="fxmp-promo__overlay">
                        <span class="fxmp-promo__kicker">{{ translate('Marketplace') }}</span>
                        <span class="fxmp-promo__title">{{ translate('Explore fintech services') }}</span>
                    </span>
                </a>
                <a class="fxmp-promo" href="{{ route('contacts') }}">
                    <img src="{{ theme_asset(path: 'public/assets/front-end/img/fx-promos/promo-crypto.webp') }}" alt="{{ translate('Sell services') }}" loading="lazy">
                    <span class="fxmp-promo__overlay">
                        <span class="fxmp-promo__kicker">{{ translate('Vendors') }}</span>
                        <span class="fxmp-promo__title">{{ translate('List your services today') }}</span>
                    </span>
                </a>
                <a class="fxmp-promo" href="{{ route('customer.auth.login') }}">
                    <img src="{{ theme_asset(path: 'public/assets/front-end/img/fx-promos/promo-crm.webp') }}" alt="{{ translate('Sign in') }}" loading="lazy">
                    <span class="fxmp-promo__overlay">
                        <span class="fxmp-promo__kicker">{{ translate('Account') }}</span>
                        <span class="fxmp-promo__title">{{ translate('Sign in to FinXCart') }}</span>
                    </span>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============ 2. Product grid with tabs ============ --}}
@if($mpAll->isNotEmpty())
<section class="fxmp-products" aria-labelledby="fxmp-products-title">
    <div class="fx-container">
        <div class="fx-sec-head">
            <h2 class="fx-sec-title" id="fxmp-products-title">{{ translate('Marketplace') }}</h2>
            <a class="fx-sec-link fx-sec-link--pill" href="{{ route('products') }}">{{ translate('View more') }}</a>
        </div>
        <div class="fxmp-tabs" role="tablist" aria-label="{{ translate('Product collections') }}">
            <button type="button" class="fxmp-tab is-active" role="tab" aria-selected="true" data-fxmp-tab="best">{{ translate('Bestsellers') }}</button>
            <button type="button" class="fxmp-tab" role="tab" aria-selected="false" data-fxmp-tab="feat">{{ translate('Featured') }}</button>
            @if($mpSale->isNotEmpty())
                <button type="button" class="fxmp-tab" role="tab" aria-selected="false" data-fxmp-tab="sale">{{ translate('On sale') }}</button>
            @endif
            @if($mpHalfOff->isNotEmpty())
                <button type="button" class="fxmp-tab" role="tab" aria-selected="false" data-fxmp-tab="half">{{ translate('50% offer') }}</button>
            @endif
        </div>
        <div class="fxmp-tabpanel is-active" data-fxmp-panel="best" role="tabpanel">
            <div class="fxmp-grid">
                @foreach($mpRec->take(8) as $product)
                    @include('web-views.partials._fx-home-marketplace-card', ['product' => $product])
                @endforeach
            </div>
        </div>
        <div class="fxmp-tabpanel" data-fxmp-panel="feat" role="tabpanel" hidden>
            <div class="fxmp-grid">
                @foreach($mpNew->take(8) as $product)
                    @include('web-views.partials._fx-home-marketplace-card', ['product' => $product])
                @endforeach
            </div>
        </div>
        @if($mpSale->isNotEmpty())
            <div class="fxmp-tabpanel" data-fxmp-panel="sale" role="tabpanel" hidden>
                <div class="fxmp-grid">
                    @foreach($mpSale->take(8) as $product)
                        @include('web-views.partials._fx-home-marketplace-card', ['product' => $product])
                    @endforeach
                </div>
            </div>
        @endif
        @if($mpHalfOff->isNotEmpty())
            <div class="fxmp-tabpanel" data-fxmp-panel="half" role="tabpanel" hidden>
                <div class="fxmp-grid">
                    @foreach($mpHalfOff->take(8) as $product)
                        @include('web-views.partials._fx-home-marketplace-card', ['product' => $product])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endif

{{-- ============ 3. CTA banner ============ --}}
<section class="fxmp-cta" aria-label="{{ translate('Buy or sell') }}">
    <div class="fx-container">
        <div class="fxmp-cta__box">
            <div class="fxmp-cta__copy">
                <h2 class="fxmp-cta__title">{{ translate('Looking to buy or sell a service?') }}</h2>
                <p class="fxmp-cta__sub">{{ translate('Connect with premium providers or start selling your own fintech services today.') }}</p>
            </div>
            <div class="fxmp-cta__actions">
                <a class="fxmp-cta__btn fxmp-cta__btn--amber" href="{{ route('products') }}">{{ translate('Find a provider') }}</a>
                <a class="fxmp-cta__btn fxmp-cta__btn--ghost" href="{{ route('contacts') }}">{{ translate('Start selling') }}</a>
            </div>
        </div>
    </div>
</section>

{{-- ============ 4. Solutions subcategory links ============ --}}
<section class="fxmp-solutions" aria-labelledby="fxmp-solutions-title">
    <div class="fx-container">
        <div class="fx-sec-head">
            <h2 class="fx-sec-title" id="fxmp-solutions-title">{{ translate('Solutions') }}</h2>
            <a class="fx-sec-link" href="{{ route('products') }}">{{ translate('View all') }}</a>
        </div>
        <div class="fxmp-solutions__chips">
            @foreach($mpSolutions as [$label, $term])
                <a class="fxmp-chip" href="{{ route('products', ['name' => $term]) }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ 5. Pre-built websites ============ --}}
@if($mpWeb->isNotEmpty())
<section class="fxmp-web" aria-labelledby="fxmp-web-title">
    <div class="fx-container">
        <div class="fxmp-web__grid">
            <a class="fxmp-web__banner" href="{{ route('products', ['name' => 'website']) }}">
                <img src="{{ theme_asset(path: 'public/assets/front-end/img/fx-promos/promo-wide.webp') }}" alt="{{ translate('Pre-built websites') }}" loading="lazy">
                <span class="fxmp-web__overlay">
                    <span class="fxmp-promo__kicker">{{ translate('Ready to launch') }}</span>
                    <span class="fxmp-web__title">{{ translate('Pre-built websites') }}</span>
                    <span class="fxmp-web__cta">{{ translate('Shop now') }}</span>
                </span>
            </a>
            <div>
                <div class="fx-sec-head">
                    <h2 class="fx-sec-title" id="fxmp-web-title">{{ translate('Website development') }}</h2>
                </div>
                <div class="fxmp-grid fxmp-grid--2">
                    @foreach($mpWeb as $product)
                        @include('web-views.partials._fx-home-marketplace-card', ['product' => $product])
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ============ 6. Top categories this week ============ --}}
@if($mpCats->isNotEmpty())
<section class="fxmp-cats" aria-labelledby="fxmp-cats-title">
    <div class="fx-container">
        <div class="fx-sec-head">
            <h2 class="fx-sec-title" id="fxmp-cats-title">{{ translate('Top categories this week') }}</h2>
            <a class="fx-sec-link" href="{{ route('products') }}">{{ translate('View all') }}</a>
        </div>
        <div class="fxmp-cats__row">
            @foreach($mpCats->take(8) as $cat)
                <a class="fxmp-cat" href="{{ route('products', ['category_id' => $cat->id, 'data_from' => 'category', 'page' => 1]) }}">
                    <span class="fxmp-cat__tile" aria-hidden="true">{{ mb_substr((string) $cat->name, 0, 1) }}</span>
                    <span class="fxmp-cat__name">{{ $cat->name }}</span>
                    <span class="fxmp-cat__count">{{ (int) ($cat->product_count ?? 0) }} {{ translate('items') }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ 7. API integrations ============ --}}
<section class="fxmp-api" aria-labelledby="fxmp-api-title">
    <div class="fx-container">
        <div class="fx-sec-head">
            <h2 class="fx-sec-title" id="fxmp-api-title">{{ translate('API integrations') }}</h2>
            <a class="fx-sec-link" href="{{ route('products', ['name' => 'api']) }}">{{ translate('View all') }}</a>
        </div>
        <div class="fxmp-api__row">
            @foreach($mpApis as $api)
                <a class="fxmp-api__card" href="{{ route('products', ['name' => $api['term']]) }}">
                    <span class="fxmp-api__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </span>
                    <span class="fxmp-api__title">{{ $api['title'] }}</span>
                    <span class="fxmp-api__body">{{ $api['body'] }}</span>
                    <span class="fxmp-api__link">{{ translate('Browse') }} →</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ 8. Add ons ============ --}}
@if($mpAddons->isNotEmpty())
<section class="fxmp-addons" aria-labelledby="fxmp-addons-title">
    <div class="fx-container">
        <div class="fx-sec-head">
            <h2 class="fx-sec-title" id="fxmp-addons-title">{{ translate('Add-ons') }}</h2>
            <a class="fx-sec-link fx-sec-link--pill" href="{{ route('products') }}">{{ translate('View more') }}</a>
        </div>
        <div class="fxmp-grid">
            @foreach($mpAddons as $product)
                @include('web-views.partials._fx-home-marketplace-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif

@include('web-views.partials._fx-drawers')

<script>
(function () {
    var root = document.currentScript ? document.currentScript.parentElement : document;
    /* Hero carousel */
    var car = root.querySelector('[data-fxmp-carousel]');
    if (car) {
        var slides = Array.prototype.slice.call(car.querySelectorAll('[data-fxmp-slide]'));
        var dots = Array.prototype.slice.call(car.querySelectorAll('[data-fxmp-dot]'));
        var idx = 0, timer = null;
        function go(n) {
            idx = (n + slides.length) % slides.length;
            slides.forEach(function (s, i) { s.hidden = i !== idx; s.classList.toggle('is-active', i === idx); });
            dots.forEach(function (d, i) { d.classList.toggle('is-active', i === idx); });
        }
        function auto() { timer = setInterval(function () { go(idx + 1); }, 6000); }
        dots.forEach(function (d) {
            d.addEventListener('click', function () { clearInterval(timer); go(parseInt(d.getAttribute('data-fxmp-dot'), 10)); auto(); });
        });
        auto();
    }
    /* Tabs */
    var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-fxmp-tab]'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('[data-fxmp-panel]'));
    tabs.forEach(function (t) {
        t.addEventListener('click', function () {
            var key = t.getAttribute('data-fxmp-tab');
            tabs.forEach(function (x) { x.classList.toggle('is-active', x === t); x.setAttribute('aria-selected', x === t ? 'true' : 'false'); });
            panels.forEach(function (p) { var on = p.getAttribute('data-fxmp-panel') === key; p.hidden = !on; p.classList.toggle('is-active', on); });
        });
    });
    /* Wishlist */
    document.querySelectorAll('.fxmp-wishlist').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-product-id');
            if (typeof window.addWishlist === 'function') { window.addWishlist(id, ''); }
            var saved = btn.getAttribute('aria-pressed') === 'true';
            btn.setAttribute('aria-pressed', saved ? 'false' : 'true');
            btn.classList.toggle('is-saved', !saved);
            var g = btn.querySelector('span'); if (g) g.textContent = saved ? '♡' : '♥';
        });
    });
    /* Add to cart */
    document.querySelectorAll('.fxmp-addcart').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = btn.getAttribute('data-form');
            if (typeof window.addToCart === 'function' && form) {
                window.addToCart(form);
                var label = btn.getAttribute('data-update');
                if (label) btn.textContent = label;
            }
        });
    });
    /* Quick view drawer */
    var drawer = document.querySelector('[data-fx-qp-drawer]');
    var overlay = document.querySelector('[data-fx-qp-overlay]');
    var body = document.querySelector('[data-fx-qp-body]');
    var aborter = null;
    function openDrawer() { if (drawer) drawer.hidden = false; if (overlay) overlay.hidden = false; document.body.style.overflow = 'hidden'; }
    function closeDrawer() { if (drawer) drawer.hidden = true; if (overlay) overlay.hidden = true; document.body.style.overflow = ''; }
    if (drawer && overlay && body) {
        drawer.querySelectorAll('[data-fx-qp-close]').forEach(function (b) { b.addEventListener('click', closeDrawer); });
        overlay.addEventListener('click', closeDrawer);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDrawer(); });
        document.querySelectorAll('.fxmp-quickview').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var url = btn.getAttribute('data-fx-preview-url');
                if (!url) return;
                openDrawer();
                if (aborter) aborter.abort();
                aborter = new AbortController();
                body.innerHTML = '<div class="fx-skeleton" style="height:220px"></div><div class="fx-skeleton" style="height:20px;margin-top:12px"></div>';
                fetch(url, { signal: aborter.signal, headers: { 'Accept': 'application/json' } })
                    .then(function (r) { if (!r.ok) throw new Error('bad'); return r.json(); })
                    .then(function (d) { if (d && d.ok && d.html) { body.innerHTML = d.html; } else { throw new Error('empty'); } })
                    .catch(function (err) { if (err && err.name === 'AbortError') return; body.innerHTML = '<p>Preview could not be loaded.</p>'; });
            });
        });
    }
})();
</script>
