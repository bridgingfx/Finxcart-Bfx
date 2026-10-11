@extends('layouts.front-end.app')

@section('title', translate('sign_in'))

@push('css_or_js')
    <link rel="stylesheet"
          href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/fx-commerce.css') }}?v=20260929">
@endpush

@section('content')

    <?php
    $customerManualLogin = $web_config['customer_login_options']['manual_login'] ?? 0;
    $customerOTPLogin = $web_config['customer_login_options']['otp_login'] ?? 0;
    $customerSocialLogin = $web_config['customer_login_options']['social_login'] ?? 0;

    if (!$customerOTPLogin && $customerManualLogin && $customerSocialLogin) {
        $multiColumn = 1;
    } elseif ($customerOTPLogin && !$customerManualLogin && $customerSocialLogin) {
        $multiColumn = 1;
    } elseif ($customerOTPLogin && $customerManualLogin && !$customerSocialLogin) {
        $multiColumn = 1;
    } elseif ($customerOTPLogin && $customerManualLogin && $customerSocialLogin) {
        $multiColumn = 1;
    } else {
        $multiColumn = 0;
    }
    ?>

    <div class="fx fx-page">
        <div class="fx-auth-wrap">
            <nav class="fx-breadcrumb" aria-label="{{ translate('breadcrumb') }}">
                <a href="{{ route('home') }}">{{ translate('home') }}</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ translate('sign_in') }}</span>
            </nav>

            <div class="fx-auth-card">
                <div class="fx-auth-head">
                    <h1 class="fx-auth-title">{{ translate('Sign_In') }}</h1>
                    <p class="fx-auth-sub">{{ translate('welcome_back_sign_in_to_continue') }}</p>
                </div>

                <div class="fx-auth-grid {{ $multiColumn ? 'fx-auth-grid--two' : '' }}">
                    @if($customerOTPLogin && !$customerManualLogin && !$customerSocialLogin)
                        <div class="fx-auth-col">
                            <form autocomplete="off"
                                action="{{ route('customer.auth.login') }}"
                                method="post"
                                data-recaptcha="skip"
                                class="customer-centralize-login-form fx-form"
                                data-firebase-auth="{{ $web_config['firebase_otp_verification_status'] ? 'active': 'deactivate' }}" novalidate>
                                @csrf
                                <input type="hidden" name="login_type" value="otp-login">
                                @include("web-views.customer-views.auth.partials._phone")

                                @include("web-views.customer-views.auth.partials._recaptcha")

                                <button class="fx-btn fx-btn--primary fx-btn--block" type="submit">
                                    {{ translate('Get_OTP') }}
                                </button>
                            </form>
                        </div>
                    @elseif(!$customerOTPLogin && $customerManualLogin && !$customerSocialLogin)
                        <div class="fx-auth-col">
                            <form autocomplete="off"
                                class="customer-centralize-login-form fx-form"
                                action="{{ route('customer.auth.login') }}"
                                method="post" id="customer-login-form" novalidate>
                                @csrf
                                <input type="hidden" name="login_type" value="manual-login">
                                @include("web-views.customer-views.auth.partials._email")
                                @include("web-views.customer-views.auth.partials._password")
                                @include("web-views.customer-views.auth.partials._remember-me", ['forgotPassword' => true])
                                @include("web-views.customer-views.auth.partials._recaptcha")
                                <button class="fx-btn fx-btn--primary fx-btn--block" type="submit">
                                    {{ translate('sign_in') }}
                                </button>
                                @if(!$multiColumn)
                                    @include("web-views.customer-views.auth.partials._sign-up-instruction")
                                @endif
                            </form>
                        </div>
                    @elseif(!$customerOTPLogin && $customerManualLogin && $customerSocialLogin)
                        <div class="fx-auth-col">
                            <form autocomplete="off"
                                class="customer-centralize-login-form fx-form"
                                action="{{ route('customer.auth.login') }}"
                                method="post" id="customer-login-form" novalidate>
                                @csrf
                                <input type="hidden" name="login_type" value="manual-login">
                                @include("web-views.customer-views.auth.partials._email")
                                @include("web-views.customer-views.auth.partials._password")
                                @include("web-views.customer-views.auth.partials._remember-me", ['forgotPassword' => true])
                                @include("web-views.customer-views.auth.partials._recaptcha")
                                <button class="fx-btn fx-btn--primary fx-btn--block" type="submit">
                                    {{ translate('sign_in') }}
                                </button>
                                @if(!$multiColumn)
                                    @include("web-views.customer-views.auth.partials._sign-up-instruction")
                                @endif
                            </form>
                        </div>
                    @elseif($customerOTPLogin && !$customerManualLogin && $customerSocialLogin)
                        <div class="fx-auth-col">
                            <form autocomplete="off"
                                class="customer-centralize-login-form fx-form"
                                action="{{ route('customer.auth.login') }}"
                                method="post"
                                data-recaptcha="skip"
                                id="{{ $web_config['firebase_otp_verification_status'] ? 'customer-firebase-login-form': 'customer-login-form' }}"
                                data-firebase-auth="{{ $web_config['firebase_otp_verification_status'] ? 'active': 'deactivate' }}" novalidate>
                                @csrf
                                <input type="hidden" name="login_type" value="otp-login">
                                @include("web-views.customer-views.auth.partials._phone")
                                @include("web-views.customer-views.auth.partials._recaptcha")

                                <button class="fx-btn fx-btn--primary fx-btn--block" type="submit">
                                    {{ translate('Get_OTP') }}
                                </button>
                            </form>
                        </div>
                    @elseif($customerOTPLogin && $customerManualLogin)
                        <div class="fx-auth-col">
                            <div class="manual-login-container">
                                <form autocomplete="off"
                                    class="customer-centralize-login-form fx-form"
                                    action="{{ route('customer.auth.login') }}"
                                    method="post" id="customer-login-form" novalidate>
                                    @csrf

                                    <input type="hidden" name="login_type" class="auth-login-type-input" value="manual-login">

                                    <div class="manual-login-items">
                                        @include("web-views.customer-views.auth.partials._email")
                                        @include("web-views.customer-views.auth.partials._password")
                                        @include("web-views.customer-views.auth.partials._remember-me", ['forgotPassword' => true])
                                    </div>

                                    <div class="otp-login-items d-none">
                                        @include("web-views.customer-views.auth.partials._phone")
                                    </div>

                                    @include("web-views.customer-views.auth.partials._recaptcha")

                                    <div class="manual-login-items">
                                        <button class="fx-btn fx-btn--primary fx-btn--block" type="submit">
                                            {{ translate('sign_in') }}
                                        </button>
                                    </div>

                                    <div class="otp-login-items d-none">
                                        <button class="fx-btn fx-btn--primary fx-btn--block" type="submit">
                                            {{ translate('Get_OTP') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($multiColumn || $customerSocialLogin)
                        <div class="fx-auth-col fx-auth-col--side">
                            @if($multiColumn)
                                <div class="fx-auth-divider"><span>{{ translate('Or Sign in with') }}</span></div>
                            @endif
                            <div class="fx-social-login">
                                @if($customerSocialLogin)
                                    @foreach ($web_config['customer_social_login_options'] as $socialLoginServiceKey => $socialLoginService)
                                        @if ($socialLoginService && $socialLoginServiceKey != 'apple')
                                            <a class="social-media-login-btn fx-social-btn"
                                            href="{{ route('customer.auth.service-login', $socialLoginServiceKey) }}">
                                                <img alt=""
                                                    src="{{theme_asset(path: 'public/assets/front-end/img/icons/'.$socialLoginServiceKey.'.png') }}">
                                                <span class="text">
                                                    {{ translate($socialLoginServiceKey) }}
                                                </span>
                                            </a>
                                        @endif
                                    @endforeach
                                @endif
                                @if($customerOTPLogin && $customerManualLogin)
                                    <a class="social-media-login-btn otp-login-btn fx-social-btn" href="javascript:">
                                        <img alt=""
                                            src="{{theme_asset(path: 'public/assets/front-end/img/icons/otp-login-icon.svg') }}">
                                        <span class="text">{{ translate('OTP_Sign_in') }}</span>
                                    </a>

                                    <a class="social-media-login-btn manual-login-btn d-none fx-social-btn" href="javascript:">
                                        <img alt=""
                                            src="{{theme_asset(path: 'public/assets/front-end/img/icons/otp-login-icon.svg') }}">
                                        <span class="text">{{ translate('Manual_Login') }}</span>
                                    </a>
                                @endif
                            </div>
                            @if($multiColumn)
                                @include("web-views.customer-views.auth.partials._sign-up-instruction")
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    @php
        $recaptcha = getWebConfig(name: 'recaptcha')
    @endphp
    @if($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification_status'])
        <script type="text/javascript">
            "use strict";
        </script>
    @elseif(isset($recaptcha) && $recaptcha['status'] == 1)
        <script type="text/javascript">
            "use strict";
            var onloadCallback = function () {
                let loginId = grecaptcha.render('recaptcha_element', {
                    'sitekey': '{{ getWebConfig(name: 'recaptcha')['site_key'] }}'
                });
                $('#recaptcha_element').attr('data-login-id', loginId);
            };
        </script>
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
                async defer></script>
    @endif

    @if($web_config['firebase_otp_verification_status'])
        <script>
            $('.fx-auth-divider').css('width', $('.fx-auth-grid--two').height())
        </script>
    @endif

    <script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>
@endpush
