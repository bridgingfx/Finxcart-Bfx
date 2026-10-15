{{-- FinXCart hero v7: Buffer-style floating fintech icon cloud. Centered premium
     layout, real search, real stats, scattered icon tiles around headline. --}}
@php
    $chips = isset($heroChips) ? $heroChips : collect();
    $stats = isset($fxStats) && is_array($fxStats) ? $fxStats : null;
    $catCount = isset($populatedCategoryCount) ? (int) $populatedCategoryCount : 0;
    $tickerCats = $chips->count() ? $chips : collect();

    // Fintech icon tiles (left cluster, right cluster) — Buffer-style floating cloud
    $iconTilesLeft = [
        ['y' => 8,  'd' => 0,    'svg' => '<path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/>', 'label' => 'Trading'],
        ['y' => 96, 'd' => -2.5, 'svg' => '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/>', 'label' => 'Wallet'],
        ['y' => 188,'d' => -5,   'svg' => '<path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z"/>', 'label' => 'Security'],
        ['y' => 60, 'd' => 5,    'svg' => '<path d="M8 6l-6 6 6 6M16 6l6 6-6 6"/>', 'label' => 'API'],
        ['y' => 150,'d' => 2.5,  'svg' => '<circle cx="12" cy="12" r="9"/><path d="M9 9.5v5M9 9.5h2.5a2 2 0 010 4H9"/>', 'label' => 'Crypto'],
        ['y' => 230,'d' => -1,   'svg' => '<path d="M3 12V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2v-1"/><path d="M3 12h18"/>', 'label' => 'Banking'],
        ['y' => 40, 'd' => -4,   'svg' => '<path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/>', 'label' => 'Fast'],
    ];
    $iconTilesRight = [
        ['y' => 20, 'd' => 4,    'svg' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>', 'label' => 'Signals'],
        ['y' => 110,'d' => -3,   'svg' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>', 'label' => 'CRM'],
        ['y' => 200,'d' => 5,    'svg' => '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/>', 'label' => 'Mobile'],
        ['y' => 70, 'd' => -5,   'svg' => '<path d="M18 20V10M12 20V4M6 20v-6"/>', 'label' => 'Analytics'],
        ['y' => 160,'d' => 3,    'svg' => '<circle cx="12" cy="12" r="9"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>', 'label' => 'Global'],
        ['y' => 240,'d' => -2,   'svg' => '<rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/>', 'label' => 'Secure'],
        ['y' => 12, 'd' => 2,    'svg' => '<path d="M23 6l-9.5 9.5-5-5L1 18"/><path d="M17 6h6v6"/>', 'label' => 'Growth'],
    ];
@endphp
<section class="fx-hero7" aria-label="{{ translate('Featured') }}">
    <div class="fx-hero7__bg" aria-hidden="true">
        <span class="fx-hero7__grid"></span>
        {{-- Left floating icon cluster --}}
        <div class="fx-hero7__cluster fx-hero7__cluster--left" aria-hidden="true">
            @foreach($iconTilesLeft as $i => $tile)
                <span class="fx-hero7__tile" style="--ty: {{ $tile['y'] }}px; --td: {{ $tile['d'] }}s;" title="{{ $tile['label'] }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $tile['svg'] !!}</svg>
                </span>
            @endforeach
        </div>
        {{-- Right floating icon cluster --}}
        <div class="fx-hero7__cluster fx-hero7__cluster--right" aria-hidden="true">
            @foreach($iconTilesRight as $i => $tile)
                <span class="fx-hero7__tile" style="--ty: {{ $tile['y'] }}px; --td: {{ $tile['d'] }}s;" title="{{ $tile['label'] }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $tile['svg'] !!}</svg>
                </span>
            @endforeach
        </div>
    </div>

    <div class="fx-hero7__inner">
        <h1 class="fx-hero7__title">{{ translate('The app store for') }} <span class="fx-hero7__grad">{{ translate('fintech.') }}</span></h1>
        <p class="fx-hero7__sub">{{ translate('Trading tools, broker solutions and expert services — listed by real vendors, ready to deploy.') }}</p>

        <form class="fx-hero7__search" role="search" action="{{ route('products') }}" method="GET">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            <input type="search" name="name" placeholder="{{ translate('Search trading tools, broker CRM, APIs…') }}" aria-label="{{ translate('Search products') }}" autocomplete="off">
            <button type="submit">{{ translate('Search') }}</button>
        </form>

        @if($chips->count())
            <div class="fx-hero7__chips" aria-label="{{ translate('Popular categories') }}">
                @foreach($chips as $chipCat)
                    <a class="fx-hero7__chip" href="{{ route('products', ['category_id' => $chipCat->id, 'data_from' => 'category', 'page' => 1]) }}">{{ $chipCat->name }}</a>
                @endforeach
            </div>
        @endif

        @if($stats && ($stats['products'] > 0 || $stats['vendors'] > 0))
            <dl class="fx-hero7__stats">
                <div class="fx-hero7__stat"><dd>{{ number_format((int) $stats['products']) }}</dd><dt>{{ translate('Products listed') }}</dt></div>
                <div class="fx-hero7__stat"><dd>{{ number_format((int) $stats['vendors']) }}</dd><dt>{{ translate('Vendors') }}</dt></div>
                @if($catCount > 0)
                    <div class="fx-hero7__stat"><dd>{{ number_format($catCount) }}</dd><dt>{{ translate('Categories') }}</dt></div>
                @endif
            </dl>
        @endif

        <a class="fx-hero7__vendor" href="{{ route('contacts') }}">{{ translate('Sell on FinXCart') }}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>

    @if($tickerCats->count())
        <div class="fx-hero7__ticker">
            <div class="fx-wcard fx-wcard--img fx-wcard--t1" data-depth="14">
                <img src="{{ asset('storage/company/hero-terminal.webp') }}" alt="Trading terminal" loading="lazy">
                <div class="fx-wcard__label"><span>Pro Trading Terminal</span><em>$299</em></div>
            </div>
            <div class="fx-wcard fx-wcard--img fx-wcard--t2" data-depth="22">
                <img src="{{ asset('storage/company/hero-copier.webp') }}" alt="Trade copier" loading="lazy">
                <div class="fx-wcard__label"><span>Smart Trade Copier</span><em>$149</em></div>
            </div>
            <div class="fx-wcard fx-wcard--img fx-wcard--t3" data-depth="18">
                <img src="{{ asset('storage/company/hero-crm.webp') }}" alt="Broker CRM" loading="lazy">
    @if($tickerCats->count())
        <div class="fx-hero7__ticker">
            <div class="fx-hero7__ticker-track">
                @foreach($tickerCats as $tickCat)
                    <a class="fx-hero7__tick" href="{{ route('products', ['category_id' => $tickCat->id, 'data_from' => 'category', 'page' => 1]) }}"><span>{{ $tickCat->name }}</span><i>✦</i></a>
                @endforeach
                @foreach($tickerCats as $tickCat)
                    <span class="fx-hero7__tick" aria-hidden="true"><span>{{ $tickCat->name }}</span><i>✦</i></span>
                @endforeach
            </div>
        </div>
    @endif
</section>

<script>
(function () {
    // Gentle parallax on floating tiles (desktop only)
    var hero = document.querySelector('.fx-hero7');
    if (hero && window.matchMedia('(pointer: fine)').matches) {
        var tiles = hero.querySelectorAll('.fx-hero7__tile');
        var raf = null, tx = 0, ty = 0;
        hero.addEventListener('mousemove', function (e) {
            var r = hero.getBoundingClientRect();
            tx = (e.clientX - r.left) / r.width - 0.5;
            ty = (e.clientY - r.top) / r.height - 0.5;
            if (!raf) raf = requestAnimationFrame(apply);
        });
        function apply() {
            raf = null;
            tiles.forEach(function (el, i) {
                var depth = 6 + (i % 4) * 4;
                el.style.translate = (-tx * depth) + 'px ' + (-ty * depth) + 'px';
            });
        }
        hero.addEventListener('mouseleave', function () {
            tx = 0; ty = 0;
            if (!raf) raf = requestAnimationFrame(apply);
        });
    }
})();
</script>
