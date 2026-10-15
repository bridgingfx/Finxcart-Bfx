{{-- FinXCart redesign v6: light "app store for fintech" hero. Centered premium
     layout, real search, real stats, floating light-glass product cards. --}}
@php
    $chips = isset($heroChips) ? $heroChips : collect();
    $stats = isset($fxStats) && is_array($fxStats) ? $fxStats : null;
    $catCount = isset($populatedCategoryCount) ? (int) $populatedCategoryCount : 0;
    $tickerCats = $chips->count() ? $chips : collect();
@endphp
<section class="fx-hero6" aria-label="{{ translate('Featured') }}">
    <div class="fx-hero6__bg" aria-hidden="true">
        <span class="fx-hero6__wash fx-hero6__wash--a"></span>
        <span class="fx-hero6__wash fx-hero6__wash--b"></span>
        <span class="fx-hero6__wash fx-hero6__wash--c"></span>
        <span class="fx-hero6__grid"></span>
    </div>

    <div class="fx-hero6__inner">
        <span class="fx-hero6__badge"><span class="fx-hero6__dot"></span>{{ translate('Fintech marketplace') }}</span>
        <h1 class="fx-hero6__title">{{ translate('The app store for') }} <span class="fx-hero6__grad">{{ translate('fintech.') }}</span></h1>
        <p class="fx-hero6__sub">{{ translate('Trading tools, broker solutions and expert services — listed by real vendors, ready to deploy.') }}</p>

        <form class="fx-hero6__search" role="search" action="{{ route('products') }}" method="GET">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            <input type="search" name="name" placeholder="{{ translate('Search trading tools, broker CRM, APIs…') }}" aria-label="{{ translate('Search products') }}" autocomplete="off">
            <button type="submit">{{ translate('Search') }}</button>
        </form>

        @if($chips->count())
            <div class="fx-hero6__chips" aria-label="{{ translate('Popular categories') }}">
                @foreach($chips as $chipCat)
                    <a class="fx-hero6__chip" href="{{ route('products', ['category_id' => $chipCat->id, 'data_from' => 'category', 'page' => 1]) }}">{{ $chipCat->name }}</a>
                @endforeach
            </div>
        @endif

        @if($stats && ($stats['products'] > 0 || $stats['vendors'] > 0))
            <dl class="fx-hero6__stats">
                <div class="fx-hero6__stat"><dd>{{ number_format((int) $stats['products']) }}</dd><dt>{{ translate('Products listed') }}</dt></div>
                <div class="fx-hero6__stat"><dd>{{ number_format((int) $stats['vendors']) }}</dd><dt>{{ translate('Vendors') }}</dt></div>
                @if($catCount > 0)
                    <div class="fx-hero6__stat"><dd>{{ number_format($catCount) }}</dd><dt>{{ translate('Categories') }}</dt></div>
                @endif
            </dl>
        @endif

        <div class="fx-hero6__banner">
            <img src="{{ asset('storage/company/finxcart-hero-banner.webp') }}" alt="FinXCart fintech products on laptop and phone" loading="eager" fetchpriority="high">
        </div>

        <a class="fx-hero6__vendor" href="{{ route('contacts') }}">{{ translate('Sell on FinXCart') }}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>

    @if($tickerCats->count())
        <div class="fx-hero6__ticker">
            <div class="fx-hero6__ticker-track">
                @foreach($tickerCats as $tickCat)
                    <a class="fx-hero6__tick" href="{{ route('products', ['category_id' => $tickCat->id, 'data_from' => 'category', 'page' => 1]) }}"><span>{{ $tickCat->name }}</span><i>✦</i></a>
                @endforeach
                @foreach($tickerCats as $tickCat)
                    <span class="fx-hero6__tick" aria-hidden="true"><span>{{ $tickCat->name }}</span><i>✦</i></span>
                @endforeach
            </div>
        </div>
    @endif
</section>

<script>
(function () {
    var hero = document.querySelector('.fx-hero6');
    var wall = document.getElementById('fxHeroWall');
    if (hero && wall && window.matchMedia('(pointer: fine)').matches) {
        var layers = wall.querySelectorAll('[data-depth]');
        var raf = null, tx = 0, ty = 0;
        hero.addEventListener('mousemove', function (e) {
            var r = hero.getBoundingClientRect();
            tx = (e.clientX - r.left) / r.width - 0.5;
            ty = (e.clientY - r.top) / r.height - 0.5;
            if (!raf) raf = requestAnimationFrame(apply);
        });
        function apply() {
            raf = null;
            layers.forEach(function (el) {
                var d = parseFloat(el.getAttribute('data-depth')) || 20;
                el.style.translate = (-tx * d) + 'px ' + (-ty * d) + 'px';
            });
        }
    }
    function fxInitReveal() {
        var io = ('IntersectionObserver' in window) ? new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
            });
        }, { threshold: 0.12 }) : null;
        document.querySelectorAll('.fx-reveal').forEach(function (el) {
            if (io) io.observe(el); else el.classList.add('is-in');
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fxInitReveal);
    } else {
        fxInitReveal();
    }
})();
</script>
