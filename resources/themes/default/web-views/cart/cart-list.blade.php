@extends('layouts.front-end.app')

@section('title', translate('My_Shopping_Cart'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/fx-commerce.css') }}?v=20260929">
@endpush

@section('content')
    <div class="container fx fx-page">
        <nav class="fx-breadcrumb" aria-label="{{ translate('breadcrumb') }}">
            <ol>
                <li><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
                <li aria-current="page">{{ translate('shopping_cart') }}</li>
            </ol>
        </nav>

        <span id="get-cart-select-cart-items" data-route="{{ route('cart.select-cart-items') }}"></span>

        <div id="cart-summary">
            @include('web-views.cart._cart-details')
        </div>
    </div>
@endsection
