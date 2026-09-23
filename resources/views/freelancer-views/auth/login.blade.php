<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ translate('Freelancer_Login')}}</title>
    <link rel="shortcut icon" href="{{getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type:'backend-logo')}}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/google-fonts.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/vendor.min.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/vendor/icon-set/style.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/theme.minc619.css?v=1.0') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/style.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/toastr.css') }}">

    <style>
        :root {
            --c1: {{ $web_config['primary_color'] }};
        }
    </style>

    {!! ToastMagic::styles() !!}
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/premium-toasts.css') }}">
</head>

<body>
@php($registrationSuccess = request('registered') == 1)
<main id="content" role="main" class="main">
    <div class="auth-wrapper">
        <div class="auth-wrapper-left" style="background: url('{{ dynamicAsset(path: 'public/assets/back-end/img/login_page_banner.png') }}') no-repeat center center / cover"></div>
        @php($eCommerceLogo = getWebConfig(name: 'company_web_logo'))
        <div class="auth-wrapper-right">
            <div class="auth-wrapper-form">
                @if($registrationSuccess)
                    <div id="registration-success-banner" class="alert alert-success border-0 shadow-lg rounded-4 mb-4 py-4 px-4 d-flex align-items-start gap-3 text-white" style="background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle bg-white text-success" style="width: 56px; height: 56px;">
                            <i class="tio-done fs-3"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="h4 mb-1 text-white">Registration Successful! &#127881;</div>
                            <div class="text-white-50">Welcome to Finxcart! Please login to complete your profile and get verified.</div>
                        </div>
                    </div>
                @endif

                <div class="d-block d-lg-none">
                    <a class="d-inline-flex mb-3" href="{{ route('home') }}">
                        <img width="100" src="{{ getStorageImages(path: $eCommerceLogo, type:'backend-logo') }}"
                             alt="Logo">
                    </a>
                </div>

                <form id="form-id" action="{{route('freelancer.auth.login')}}" method="post" novalidate>
                    @csrf
                    <div>
                        <div class="mb-4">
                            <h1 class="h2 mb-2">{{translate('sign_in')}}</h1>
                            <h1 class="h5 text-gray-900 fw-normal mb-0">
                                {{translate('welcome_back_to')}} {{translate('Freelancer_Login')}}
                            </h1>
                        </div>
                    </div>

                    <div class="js-form-message form-group">
                        <label class="input-label" for="signingVendorEmail">{{translate('your_email')}}</label>

                        <input type="email" class="form-control form-control-lg" name="email" id="signingVendorEmail"
                               tabindex="1" placeholder="email@address.com" aria-label="email@address.com"
                               required data-msg="Please enter a valid email address.">
                    </div>
                    <div class="js-form-message form-group">
                        <label class="input-label" for="signingVendorPassword" tabindex="0">
                            <span class="d-flex justify-content-between align-items-center">
                              {{translate('password')}}
                                    <a href="{{route('freelancer.auth.forgot-password.index')}}">
                                        {{translate('forgot_password')}}
                                    </a>
                            </span>
                        </label>

                        <div class="input-group input-group-merge">
                            <input type="password" class="js-toggle-password form-control form-control-lg"
                                   name="password" id="signingVendorPassword"
                                   placeholder="{{ translate('8+_characters_required') }}"
                                   aria-label="8+ characters required" required
                                   data-msg="Your password is invalid. Please try again."
                                   data-hs-toggle-password-options='{
                                                "target": "#changePassTarget",
                                    "defaultClass": "tio-hidden-outlined",
                                    "showClass": "tio-visible-outlined",
                                    "classChangeTarget": "#changePassIcon"
                                    }'>
                            <div id="changePassTarget" class="input-group-append">
                                <a class="input-group-text" href="javascript:">
                                    <i id="changePassIcon" class="tio-visible-outlined"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-1">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="termsCheckbox"
                                   name="remember">
                            <label class="custom-control-label text-muted" for="termsCheckbox">
                                {{translate('remember_me')}}
                            </label>
                        </div>
                    </div>
                    @if(isset($recaptcha) && $recaptcha['status'] == 1)
                        <div id="recaptcha_element" class="w-100;" data-type="image"></div>
                        <br/>
                    @else
                        <div class="row p-2">
                            <div class="col-6 pr-0">
                                <input type="text" class="form-control form-control-lg form-control-focus-none h-50px"
                                       id="vendor-login-recaptcha-input"
                                       name="vendorRecaptchaKey" value="" required
                                       placeholder="{{translate('enter_captcha_value')}}">
                            </div>
                            <div class="col-6 input-icons bg-white rounded">
                                <a class="get-login-recaptcha-verify cursor-pointer get-session-recaptcha-auto-fill"
                                   data-link="{{ URL('freelancer/auth/recaptcha/') }}"
                                   data-session="{{ 'vendorRecaptchaSessionKey' }}"
                                   data-input="#vendor-login-recaptcha-input"
                                >
                                    <img src="{{ URL('/freelancer/auth/recaptcha/1?captcha_session_id=vendorRecaptchaSessionKey') }}"
                                         class="input-field w-90 h-50px img-fit p-0 rounded" id="default_recaptcha_id" alt="">
                                    <i class="tio-refresh icon"></i>
                                </a>
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-lg btn-block btn--primary">
                        {{ translate('sign_in')}}
                    </button>

                    <p class="text-center mt-3 mb-0" style="font-size:14px;color:#6b7280;">
                        {{ translate('no_account') }}
                        <a href="{{ route('freelancer.auth.registration.index') }}" style="font-weight:600;">
                            {{ translate('register_now') }}
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</main>

<span id="message-please-check-recaptcha" data-text="{{ translate('please_check_the_recaptcha') }}"></span>
<span id="message-copied_success" data-text="{{ translate('copied_successfully') }}"></span>
<span id="route-get-session-recaptcha-code"
      data-route="{{ route('get-session-recaptcha-code') }}"
      data-mode="{{ env('APP_MODE') }}"
></span>

<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/theme.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/toastr.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor/login.js')}}"></script>

{!! ToastMagic::scripts() !!}

@if ($errors->any() && !$registrationSuccess)
    <script>
        'use strict';
        @foreach($errors->all() as $error)
        toastMagic.error('{{ $error }}');
        @endforeach
    </script>
@endif

@if(request('message') && !$registrationSuccess)
    <script>
        'use strict';
        toastMagic.info(@json(request('message')));
    </script>
@endif

@if(isset($recaptcha) && $recaptcha['status'] == 1)
    <script type="text/javascript">
        "use strict";
        var onloadCallback = function () {
            grecaptcha.render('recaptcha_element', {
                'sitekey': '{{ getWebConfig(name: 'recaptcha')['site_key'] }}'
            });
        };
    </script>
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
@endif
@if($registrationSuccess)
    <script>
        $(document).ready(function () {
            const banner = $('#registration-success-banner');
            if (!banner.length) {
                return;
            }

            setTimeout(function () {
                banner.fadeTo(500, 0).slideUp(300, function () {
                    $(this).remove();
                });
            }, 6000);
        });
    </script>
@endif

</body>
</html>
