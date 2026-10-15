@extends('layouts.front-end.app')

@section('title', ($web_config['company_name'] ?? 'FinXCart').' | '.translate('Business tools, software and expert services'))

@push('css_or_js')
    <meta name="robots" content="index, follow">
    <meta name="description" content="{{ translate('FinXCart is a marketplace for business tools and services: fintech solutions, software, integrations and expert providers. Browse the catalog and compare listings.') }}">
    <meta property="og:image" content="{{$web_config['web_logo']['path']}}"/>
    <meta property="og:url" content="{{url('/')}}">
    <meta name="twitter:url" content="{{url('/')}}">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "{{ $web_config['company_name'] }}",
        "url": "{{ url('/') }}",
        "logo": "{{ $web_config['web_logo']['path'] }}",
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer support",
            "email": "support@finxcart.com"
        },
        "sameAs": []
    }
    </script>
    <link rel="stylesheet" href="{{theme_asset(path: 'public/assets/front-end/css/fx-home.css')}}"/>
@endpush

@section('content')
    @php
        // All data comes from the controller (HomeController::default_theme). No model
        // queries here and no fixture-specific slugs — sections render only when
        // real inventory exists, with an honest empty state otherwise.
        $categoryPool = isset($categories) ? $categories : collect();
        if ($categoryPool instanceof \Illuminate\Pagination\AbstractPaginator) {
            $categoryPool = $categoryPool->getCollection();
        }
        $tileCategories = collect($categoryPool)
            ->filter(fn($c) => (int)($c->product_count ?? 0) > 0)
            ->sortBy('priority')->values()->take(8);
        $populatedCategoryCount = collect($categoryPool)
            ->filter(fn($c) => (int)($c->product_count ?? 0) > 0)->count();

        $recommended = (function ($value) {
            if ($value instanceof \Illuminate\Pagination\AbstractPaginator) {
                return collect($value->items());
            }
            return collect($value ?? []);
        })(isset($latestProductsList) ? $latestProductsList : collect());
        $recommended = $recommended->take(8);
        $newArrivalsRaw = (function ($value) {
            if ($value instanceof \Illuminate\Pagination\AbstractPaginator) {
                return collect($value->items());
            }
            return collect($value ?? []);
        })(isset($newArrivalProducts) ? $newArrivalProducts : null);
        $newArrivals = $newArrivalsRaw->isNotEmpty() ? $newArrivalsRaw->take(8) : $recommended->take(8);
        $brandList = isset($brands)
            ? collect($brands)->filter(fn($b) => (int)($b->products_count ?? $b->product_count ?? 1) > 0)->take(8)
            : collect();

        // Promo tiles come from HomeController::default_theme() — no queries here.
        $promoTiles = isset($promoTiles) ? $promoTiles : [];

        $hasInventory = $tileCategories->isNotEmpty() || $recommended->isNotEmpty();
    @endphp

    <div class="fx-market">
        @include('web-views.partials._fx-home-hero', ['heroChips' => $tileCategories->take(6), 'fxStats' => isset($fxStats) ? $fxStats : null, 'populatedCategoryCount' => $populatedCategoryCount])

        @if($hasInventory)
            @include('web-views.partials._fx-home-categories', ['tileCategories' => $tileCategories])
            @include('web-views.partials._fx-home-recommended', ['recommended' => $recommended])
            @include('web-views.partials._fx-home-promos', ['promoTiles' => $promoTiles])
            @include('web-views.partials._fx-home-showcase')
            @include('web-views.partials._fx-home-new', ['newArrivals' => $newArrivals])
            @include('web-views.partials._fx-home-brands', ['brandList' => $brandList])
            @include('web-views.partials._fx-home-providers')
        @else
            <section class="fx-home__section">
                <div class="fx-container">
                    <div class="fx-empty">
                        <div class="fx-empty__slot" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 7h12l1.5 13.5a1 1 0 0 1-1 1.1H5.5a1 1 0 0 1-1-1.1L6 7z" /><path d="M9 10V6a3 3 0 0 1 6 0v4" /></svg>
                        </div>
                        <h2 class="fx-empty__title">{{ translate('The catalog is being prepared') }}</h2>
                        <p class="fx-empty__body">{{ translate('New listings are on the way. Check back soon, or contact us if you would like to list your business.') }}</p>
                        <a class="fx-btn" href="{{ route('contacts') }}">{{ translate('Contact us') }}</a>
                    </div>
                </div>
            </section>
        @endif

        @include('web-views.partials._fx-home-info')
    </div>

    <span id="direction-from-session" data-value="{{ session()->get('direction') }}"></span>
@endsection
