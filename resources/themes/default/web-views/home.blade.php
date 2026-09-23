@extends('layouts.front-end.app')

@section('title', $web_config['company_name'].' '.translate('online_Shopping').' | '.$web_config['company_name'].' '.translate('ecommerce'))

@push('css_or_js')
    <meta name="robots" content="index, follow">
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
    <link rel="stylesheet" href="{{theme_asset(path: 'public/assets/front-end/css/home.css')}}"/>
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/owl.theme.default.min.css') }}">
@endpush

@section('content')
    <div class="__inline-61">
        @php($decimalPointSettings = !empty(getWebConfig(name: 'decimal_point_settings')) ? getWebConfig(name: 'decimal_point_settings') : 0)

        @include('web-views.partials._home-top-slider',['bannerTypeMainBanner'=>$bannerTypeMainBanner])

        @include('web-views.partials._trading_view')

        @include('web-views.partials._products')

        @include('web-views.partials._buy_sell_service_section')

        @include('web-views.partials._featured_products')

        @if ($flashDeal['flashDeal'] && $flashDeal['flashDealProducts'] && count($flashDeal['flashDealProducts']) > 0)
            @include('web-views.partials._flash-deal', ['decimal_point_settings'=>$decimalPointSettings])
        @endif

        @include('web-views.partials._webiners_products')

		@include('web-views.partials._prebuiltwebsites')

        @include('web-views.partials._category-section-home')
        
        @include('web-views.partials.modified_addons_apiintegration')

        {{-- @include('web-views.partials._apiintergation') --}}

        {{-- @include('web-views.partials._addons') --}}

		{{-- @include('web-views.partials._sellers') --}}

		@include('web-views.partials._footertopfeaturesale')

    </div>

    <span id="direction-from-session" data-value="{{ session()->get('direction') }}"></span>
@endsection

@push('script')
    <script src="{{theme_asset(path: 'public/assets/front-end/js/owl.carousel.min.js')}}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/load-more-section.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/home.js') }}"></script>
@endpush

