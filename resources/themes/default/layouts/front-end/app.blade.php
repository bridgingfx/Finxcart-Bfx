<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session()->get('direction') ?? 'ltr' }}">

<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <meta name="_token" content="{{csrf_token()}}">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <meta name="robots" content="index, follow">
    <meta property="og:site_name" content="{{ $web_config['company_name'] }}" />

    <meta name="google-site-verification" content="{{getWebConfig('google_search_console_code')}}">
    <meta name="msvalidate.01" content="{{getWebConfig('bing_webmaster_code')}}">
    <meta name="baidu-site-verification" content="{{getWebConfig('baidu_webmaster_code')}}">
    <meta name="yandex-verification" content="{{getWebConfig('yandex_webmaster_code')}}">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $web_config['fav_icon']['path'] }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $web_config['fav_icon']['path'] }}">
    {{-- Non-critical CSS (custom scrollbars, blog slider, image-zoom, lightbox/gallery modals):
         not needed for first paint and only affects UI that's hidden/inactive at load time
         (dropdowns, hover-zoom, click-to-open modals), so loaded non-render-blocking via the
         standard preload+swap technique. Falls back to a normal blocking <link> with JS off. --}}
    <link rel="preload" as="style" href="{{ theme_asset(path: 'public/assets/front-end/vendor/simplebar/dist/simplebar.min.css') }}" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" as="style" href="{{ theme_asset(path: 'public/assets/front-end/vendor/tiny-slider/dist/tiny-slider.css') }}" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" as="style" href="{{ theme_asset(path: 'public/assets/front-end/vendor/drift-zoom/dist/drift-basic.min.css') }}" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" as="style" href="{{ theme_asset(path: 'public/assets/front-end/vendor/lightgallery.js/dist/css/lightgallery.min.css') }}" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/vendor/simplebar/dist/simplebar.min.css') }}">
        <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/vendor/tiny-slider/dist/tiny-slider.css') }}">
        <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/vendor/drift-zoom/dist/drift-basic.min.css') }}">
        <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/vendor/lightgallery.js/dist/css/lightgallery.min.css') }}">
    </noscript>
    <!--<link rel="stylesheet" media="screen" href="{{ theme_asset(path: 'public/assets/front-end/css/theme.css') }}">-->
    <link rel="stylesheet" media="screen" href="{{ theme_asset(path: 'public/assets/front-end/css/slick.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/backend/webfonts/uicons-regular-rounded.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/backend/webfonts/uicons-solid-rounded.css') }}">
    <link rel="preload" as="style" href="{{ theme_asset(path: 'public/assets/back-end/css/toastr.css') }}" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" as="style" href="{{ dynamicAsset(path: 'public/assets/back-end/css/premium-toasts.css') }}" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/back-end/css/toastr.css') }}">
        <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/premium-toasts.css') }}">
    </noscript>
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/master.css') }}"/>
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/roboto-font.css')  }}">
    <link rel="preload" as="style" href="{{ theme_asset(path: 'public/css/lightbox.css') }}" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ theme_asset(path: 'public/css/lightbox.css') }}"></noscript>
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/back-end/vendor/icon-set/style.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/owl.carousel.min.css') }}">

    @stack('css_or_js')

    @include(VIEW_FILE_NAMES['robots_meta_content_partials'])

    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/home.css') }}"/>
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/responsive1.css') }}"/>
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/style.css?v-3') }}">

	<!-- Custom Theme Style here -->
	{{-- Google Fonts is an external round trip that doesn't affect layout (fallback font is
	     shown immediately, display=swap was already set) - safe to load non-render-blocking. --}}
	<link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i&amp;display=swap" onload="this.onload=null;this.rel='stylesheet'">
	<noscript><link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i&amp;display=swap" rel="stylesheet"></noscript>

	<!-- CSS Implementing Plugins -->
	<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/font-awesome/css/fontawesome-all.min.css') }}">
	<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/customtheme/css/font-electro.css') }}">
	{{-- animate.css only defines opacity/transform keyframe utility classes (no box-size
	     changes), so deferring it cannot cause layout shift - safe non-render-blocking load. --}}
	<link rel="preload" as="style" href="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/animate.css/animate.min.css') }}" onload="this.onload=null;this.rel='stylesheet'">
	<noscript><link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/animate.css/animate.min.css') }}"></noscript>
	<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/hs-megamenu/src/hs.megamenu.css') }}" />
	<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css') }}" />

    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/fancybox/jquery.fancybox.css') }}" />
   <!-- <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/slick-carousel/slick/slick.css') }}" />-->
	<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" />
	<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/customtheme/css/theme.css') }}" />

    <style>
        :root {
            --base: {{ $web_config['primary_color'] }};
            --bs-base-rgb: {{ getHexToRGBColorCode($web_config['primary_color']) }};
            --base-2: {{ $web_config['secondary_color'] }};
            --web-primary: {{ $web_config['primary_color'] }};
            --web-primary-10: {{ $web_config['primary_color'] }}10;
            --web-primary-20: {{ $web_config['primary_color'] }}20;
            --web-primary-40: {{ $web_config['primary_color'] }}40;
            --web-secondary: {{ $web_config['secondary_color'] }};
            --web-direction: {{ Session::get('direction') }};
            --text-align-direction: {{ Session::get('direction') === "rtl" ? 'right' : 'left' }};
            --text-align-direction-alt: {{ Session::get('direction') === "rtl" ? 'left' : 'right'}};
        }

        .dropdown-menu:not(.m-0) {
            margin-{{ Session::get('direction') === "rtl" ? 'right' : 'left' }}: -8px !important;
        }

        @media (max-width: 767px) {
            .navbar-expand-md .dropdown-menu > .dropdown > .dropdown-toggle {
                padding-{{ Session::get('direction') === "rtl" ? 'left' : 'right'}}: 1.95rem;
            }
        }

        .floating-chat-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>

    <link rel="stylesheet" href="{{theme_asset(path: 'public/assets/front-end/css/custom.css?v-12')}}">
    <link rel="stylesheet" href="{{theme_asset(path: 'public/assets/front-end/vendor/aos/dist/aos.css')}}">

    {!! getSystemDynamicPartials(type: 'analytics_script') !!}
</head>

<body class="toolbar-enabled">
@auth
    @if(Auth::id() !== 36)
        <div class="floating-chat-button">
            <a href="{{ route('chatify', ['id' => 36])}}" class="btn btn-circle btn-lg btn-primary">
                <i class="fas fa-comment-dots"></i>
            </a>
        </div>
    @endif
@endauth
@include('layouts.front-end.partials._modals')


@include('layouts.front-end.partials._quick-view-modal')
@include('layouts.front-end.partials.modal._buy-now')
@include('layouts.front-end.partials._vendor-blocked-modal')

@include('layouts.front-end.partials._header')
@include('layouts.front-end.partials._alert-message')

<span id="authentication-status" data-auth="{{ auth('customer')->check() ? 'true' : 'false' }}"></span>

<div class="row">
    <div class="col-12 loading-parent">
        <div id="loading" class="d--none">
           <div class="text-center">
            <img width="300" alt=""
                 src="{{ getStorageImages(path: getWebConfig(name: 'loader_gif'), type: 'source', source: theme_asset(path: 'public/assets/front-end/img/Loading_icon.gif')) }}">
            </div>
        </div>
    </div>
</div>
<main class="main-content d-flex flex-column mb-sm-5">
@yield('content')
</main>

<span id="message-otp-sent-again" data-text="{{ translate('OTP_has_been_sent_again.') }}"></span>
<span id="message-wait-for-new-code" data-text="{{ translate('please_wait_for_new_code.') }}"></span>
<span id="message-please-check-recaptcha" data-text="{{ translate('please_check_the_recaptcha.') }}"></span>
<span id="message-please-retype-password" data-text="{{ translate('please_ReType_Password') }}"></span>
<span id="message-password-not-match" data-text="{{ translate('password_do_not_match') }}"></span>
<span id="message-password-match" data-text="{{ translate('password_match') }}"></span>
<span id="message-password-need-longest" data-text="{{ translate('password_Must_Be_6_Character') }}"></span>
<span id="message-send-successfully" data-text="{{ translate('send_successfully') }}"></span>
<span id="message-update-successfully" data-text="{{ translate('update_successfully') }}"></span>
<span id="message-successfully-copied" data-text="{{ translate('successfully_copied') }}"></span>
<span id="message-copied-failed" data-text="{{ translate('copied_failed') }}"></span>
<span id="message-select-payment-method" data-text="{{ translate('please_select_a_payment_Methods') }}"></span>
<span id="message-please-choose-all-options" data-text="{{ translate('please_choose_all_the_options') }}"></span>
<span id="message-cannot-input-minus-value" data-text="{{ translate('cannot_input_minus_value') }}"></span>
<span id="message-all-input-field-required" data-text="{{ translate('all_input_field_required') }}"></span>
<span id="message-no-data-found" data-text="{{ translate('no_data_found') }}"></span>
<span id="message-minimum-order-quantity-cannot-less-than" data-text="{{ translate('minimum_order_quantity_cannot_be_less_than_') }}"></span>
<span id="message-item-has-been-removed-from-cart" data-text="{{ translate('item_has_been_removed_from_cart') }}"></span>
<span id="message-sorry-stock-limit-exceeded" data-text="{{ translate('sorry_stock_limit_exceeded') }}"></span>
<span id="message-sorry-the-minimum-order-quantity-not-match" data-text="{{ translate('sorry_the_minimum_order_quantity_does_not_match') }}"></span>
<span id="message-cart" data-text="{{ translate('cart') }}"></span>

<span id="route-messages-store" data-url="{{ route('messages') }}"></span>
<span id="route-address-update" data-url="{{ route('address-update') }}"></span>
<span id="route-coupon-apply" data-url="{{ route('coupon.apply') }}"></span>
<span id="route-cart-add" data-url="{{ route('cart.add') }}"></span>
<span id="route-cart-remove" data-url="{{ route('cart.remove') }}"></span>
<span id="route-cart-variant-price" data-url="{{ route('cart.variant_price') }}"></span>
<span id="route-cart-nav-cart" data-url="{{ route('cart.nav-cart') }}"></span>
<span id="route-cart-order-again" data-url="{{ route('cart.order-again') }}"></span>
<span id="route-cart-updateQuantity" data-url="{{route('cart.updateQuantity')}}"></span>
<span id="route-cart-updateQuantity-guest" data-url="{{route('cart.updateQuantity.guest')}}"></span>
<span id="route-pay-offline-method-list" data-url="{{ route('pay-offline-method-list') }}"></span>
<span id="route-customer-auth-sign-up" data-url="{{ route('customer.auth.sign-up') }}"></span>
<span id="route-searched-products" data-url="{{ url('/searched-products') }}"></span>
<span id="route-currency-change" data-url="{{ route('currency.change') }}"></span>
<span id="route-store-wishlist" data-url="{{ route('store-wishlist') }}"></span>
<span id="route-delete-wishlist" data-url="{{ route('delete-wishlist') }}"></span>
<span id="route-wishlists" data-url="{{ route('wishlists') }}"></span>
<span id="route-quick-view" data-url="{{ route('quick-view') }}"></span>
<span id="route-checkout-details" data-url="{{ route('checkout-details') }}"></span>
<span id="route-checkout-payment" data-url="{{ route('checkout-payment') }}"></span>
<span id="route-set-shipping-id" data-url="{{ route('customer.set-shipping-method') }}"></span>
<span id="route-order-note" data-url="{{ route('order_note') }}"></span>
<span id="route-product-restock-request" data-url="{{ route('cart.product-restock-request') }}"></span>
<span id="route-get-session-recaptcha-code"
      data-route="{{ route('get-session-recaptcha-code') }}"
      data-mode="{{ env('APP_MODE') }}"
></span>
<span id="password-error-message" data-max-character="{{translate('at_least_8_characters').'.'}}" data-uppercase-character="{{translate('at_least_one_uppercase_letter_').'(A...Z)'.'.'}}" data-lowercase-character="{{translate('at_least_one_uppercase_letter_').'(a...z)'.'.'}}"
      data-number="{{translate('at_least_one_number').'(0...9)'.'.'}}" data-symbol="{{translate('at_least_one_symbol').'(!...%)'.'.'}}"></span>
<span class="system-default-country-code" data-value="{{ getWebConfig(name: 'country_code') ?? 'us' }}"></span>
<span id="system-session-direction" data-value="{{ session()->get('direction') ?? 'ltr' }}"></span>

<span id="is-request-customer-auth-sign-up" data-value="{{ Request::is('customer/auth/sign-up*') ? 1:0 }}"></span>
<span id="is-customer-auth-active" data-value="{{ auth('customer')->check() ? 1:0 }}"></span>

<span id="storage-flash-deals" data-value="{{ $web_config['flash_deals']['start_date'] ?? '' }}"></span>
<span id="exceeds10MBSizeLimit" data-text="{{ translate('File_exceeds_10MB_size_limit') }}"></span>

@include('layouts.front-end.partials._footer')
@include('layouts.front-end.partials.modal._dynamic-modals')

<div class="floating-btn-grp jkl mb-3">
    <div class="__floating-btn">
        @php($whatsapp = getWebConfig(name: 'whatsapp'))
        @if(isset($whatsapp['status']) && $whatsapp['status'] == 1 )
            <div class="wa-widget-send-button">
                <a href="https://wa.me/{{ $whatsapp['phone'] }}?text=Hello%20there!" target="_blank">
                    <img src="{{theme_asset(path: 'public/assets/front-end/img/whatsapp.svg')}}" class="wa-messenger-svg-whatsapp wh-svg-icon" alt="{{ translate('Chat_with_us_on_WhatsApp') }}">
                </a>
            </div>
        @endif
    </div>

</div>

<a class="js-go-to u-go-to animated js-animation-was-fired slideInUp" href="#" data-position='{"bottom": 15, "right": 15}' data-type="fixed" data-offset-top="400" data-compensation="#header" data-show-effect="slideInUp" data-hide-effect="slideOutDown" style="display: inline-block; position: fixed; bottom: 15px; right: 15px;">
    <span class="fas fa-arrow-up u-go-to__inner"></span>
</a>

<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/jquery-migrate/dist/jquery-migrate.min.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/popper.js/dist/umd/popper.min.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/bootstrap/bootstrap.min.js') }}"></script>

<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/appear.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/jquery.countdown.min.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/jquery-validation/dist/jquery.validate.min.js') }}"></script>

<script src="{{ theme_asset(path: 'public/assets/front-end/vendor/bs-custom-file-input/dist/bs-custom-file-input.min.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/vendor/simplebar/dist/simplebar.min.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/vendor/tiny-slider/dist/min/tiny-slider.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/vendor/smooth-scroll/dist/smooth-scroll.polyfills.min.js') }}"></script>
<!--<script src="{{ theme_asset(path: 'public/js/lightbox.min.js') }}"></script>-->
<script src="{{ theme_asset(path: 'public/assets/front-end/vendor/drift-zoom/dist/Drift.min.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/vendor/lightgallery.js/dist/js/lightgallery.min.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/vendor/lg-video.js/dist/lg-video.min.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/owl.carousel.min.js')}}"></script>
<!--<script src="{{ theme_asset(path: 'public/assets/front-end/js/theme.js') }}"></script>-->
<!--<script src="{{ theme_asset(path: 'public/assets/front-end/js/slick.js') }}"></script>-->
<script src="{{ theme_asset(path: 'public/assets/front-end/js/sweet_alert.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/back-end/js/toastr.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/vendor/aos/dist/aos.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.AOS) {
            AOS.init({ once: true, duration: 600, offset: 80 });
        }
    });

    (function () {
        // The header is position: fixed and overlays the page, so main content
        // needs top spacing matching its real (responsive) rendered height.
        // The announcement bar (when configured) is also made fixed, stacked
        // above the nav, so both heights need measuring and combining.
        function syncHeaderOffset() {
            var section = document.querySelector('.u-header__section');
            if (!section) {
                return;
            }
            var announcement = document.querySelector('#announcement');
            var announcementHeight = announcement ? announcement.getBoundingClientRect().height : 0;
            var sectionHeight = section.getBoundingClientRect().height;
            document.documentElement.style.setProperty('--announcement-offset', announcementHeight + 'px');
            document.documentElement.style.setProperty('--header-offset', (announcementHeight + sectionHeight) + 'px');
        }
        syncHeaderOffset();
        window.addEventListener('resize', syncHeaderOffset);
        window.addEventListener('load', syncHeaderOffset);
    })();
</script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/custom.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/front-end/js/form-validation-core.js') }}"></script>

 <!-- JS Electro -->
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/hs-megamenu/src/hs.megamenu.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/fancybox/jquery.fancybox.min.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/slick-carousel/slick/slick.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>




	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/hs.core.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/components/hs.countdown.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/components/hs.header.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/components/hs.hamburgers.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/components/hs.unfold.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/components/hs.focus-state.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/components/hs.malihu-scrollbar.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/components/hs.validation.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/components/hs.fancybox.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/components/hs.onscroll-animation.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/components/hs.slick-carousel.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/components/hs.show-animation.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/components/hs.svg-injector.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/components/hs.go-to.js') }}"></script>
	<script src="{{ theme_asset(path: 'public/assets/front-end/customtheme/js/components/hs.selectpicker.js') }}"></script>

        <!-- JS Plugins Init. -->
        <script>
            $(window).on('load', function () {
                // initialization of HSMegaMenu component
                $('.js-mega-menu').HSMegaMenu({
                    event: 'hover',
                    direction: 'horizontal',
                    pageContainer: $('.container'),
                    breakpoint: 767.98,
                    hideTimeOut: 0
                });
            });

            $(document).on('ready', function () {
                // initialization of header
                $.HSCore.components.HSHeader.init($('#header'));

                // initialization of animation
                $.HSCore.components.HSOnScrollAnimation.init('[data-animation]');

                // initialization of unfold component
                $.HSCore.components.HSUnfold.init($('[data-unfold-target]'), {
                    afterOpen: function () {
                        $(this).find('input[type="search"]').focus();
                    }
                });

                // initialization of popups
                $.HSCore.components.HSFancyBox.init('.js-fancybox');

                // initialization of countdowns
                var countdowns = $.HSCore.components.HSCountdown.init('.js-countdown', {
                    yearsElSelector: '.js-cd-years',
                    monthsElSelector: '.js-cd-months',
                    daysElSelector: '.js-cd-days',
                    hoursElSelector: '.js-cd-hours',
                    minutesElSelector: '.js-cd-minutes',
                    secondsElSelector: '.js-cd-seconds'
                });

                // initialization of malihu scrollbar
                $.HSCore.components.HSMalihuScrollBar.init($('.js-scrollbar'));

                // initialization of forms
                $.HSCore.components.HSFocusState.init();

                // initialization of form validation
                $.HSCore.components.HSValidation.init('.js-validate', {
                    rules: {
                        confirmPassword: {
                            equalTo: '#signupPassword'
                        }
                    }
                });

                // initialization of show animations
                $.HSCore.components.HSShowAnimation.init('.js-animation-link');

                // initialization of fancybox
                $.HSCore.components.HSFancyBox.init('.js-fancybox');

                // initialization of slick carousel
                $.HSCore.components.HSSlickCarousel.init('.js-slick-carousel');

                // initialization of go to
                $.HSCore.components.HSGoTo.init('.js-go-to');

                // initialization of hamburgers
                $.HSCore.components.HSHamburgers.init('#hamburgerTrigger');

                // initialization of unfold component
                $.HSCore.components.HSUnfold.init($('[data-unfold-target]'), {
                    beforeClose: function () {
                        $('#hamburgerTrigger').removeClass('is-active');
                    },
                    afterClose: function() {
                        $('#headerSidebarList .collapse.show').collapse('hide');
                    }
                });

                $('#headerSidebarList [data-toggle="collapse"]').on('click', function (e) {
                    e.preventDefault();

                    var target = $(this).data('target');

                    if($(this).attr('aria-expanded') === "true") {
                        $(target).collapse('hide');
                    } else {
                        $(target).collapse('show');
                    }
                });

                // initialization of unfold component
                $.HSCore.components.HSUnfold.init($('[data-unfold-target]'));

                // initialization of select picker
                $.HSCore.components.HSSelectPicker.init('.js-select');
            });
        </script>

{!! Toastr::message() !!}

<script>
    "use strict";

    @if(Request::is('/') &&  \Illuminate\Support\Facades\Cookie::has('popup_banner')==false)
    $(document).ready(function () {
        $('#popup-modal').modal('show');
    });
    @php(\Illuminate\Support\Facades\Cookie::queue('popup_banner', 'off', 1))
    @endif

    @if ($errors->any())
    @foreach($errors->all() as $error)
    toastr.error('{{$error}}', '', {
        CloseButton: true,
        ProgressBar: true
    });
    @endforeach
    @endif

    $(document).mouseup(function (e) {
        let container = $(".search-card");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.hide();
        }
    });

    function route_alert(route, message) {
        Swal.fire({
            title: '{{ translate("are_you_sure")}}?',
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '{{$web_config['primary_color']}}',
            cancelButtonText: '{{ translate("no")}}',
            confirmButtonText: '{{ translate("yes")}}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                location.href = route;
            }
        })
    }

    @php($cookie = $web_config['cookie_setting'] ? json_decode($web_config['cookie_setting']['value'], true):null)
    let cookie_content = `
        <div class="cookie-section">
            <div class="container">
                <div class="d-flex flex-wrap align-items-center justify-content-between column-gap-4 row-gap-3">
                    <div class="text-wrapper">
                        <h5 class="title">{{ translate("Your_Privacy_Matter")}}</h5>
                        <div>{{ $cookie ? $cookie['cookie_text'] : '' }}</div>
                    </div>
                    <div class="btn-wrapper">
                        <button class="btn bg-dark text-white cursor-pointer" id="cookie-reject">{{ translate("no_thanks")}}</button>
                        <button class="btn btn-success cookie-accept" id="cookie-accept">{{ translate('i_Accept')}}</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    $(document).on('click','#cookie-accept',function() {
        document.cookie = '6valley_cookie_consent=accepted; max-age=' + 60 * 60 * 24 * 30;
        $('#cookie-section').hide();
    });
    $(document).on('click','#cookie-reject',function() {
        document.cookie = '6valley_cookie_consent=reject; max-age=' + 60 * 60 * 24;
        $('#cookie-section').hide();
    });

    $(document).ready(function() {
        if (document.cookie.indexOf("6valley_cookie_consent=accepted") !== -1) {
            $('#cookie-section').hide();
        }else{
            $('#cookie-section').html(cookie_content).show();
        }
    });
</script>
@if(env('APP_MODE') == 'demo')
    <script>
        'use strict'
        function checkDemoResetTime() {
            let currentMinute = new Date().getMinutes();
            if (currentMinute > 55 && currentMinute <= 60) {
                $('#demo-reset-warning').addClass('active');
            } else {
                $('#demo-reset-warning').removeClass('active');
            }
        }
        checkDemoResetTime();
        setInterval(checkDemoResetTime, 60000);
    </script>
@endif

@stack('script')
{{-- This one file contains Echo and Pusher. Deferred: nothing earlier in the page depends
     on it synchronously, and the chat widget below only checks window.Echo inside its own
     DOMContentLoaded handler, which fires after deferred scripts have run. --}}
<script src="{{ asset('public/js/app.js') }}" defer></script>

{{-- The chat widget is included AFTER app.js, so it can find window.Echo --}}
@include('partials.chat-widget')

{{-- Firebase (FCM push notifications + OTP recaptcha) is not needed for first paint or any
     other script on this page, and nothing here calls it synchronously — safe to load last. --}}
@include('layouts.front-end.partials._firebase-script')
</html>
