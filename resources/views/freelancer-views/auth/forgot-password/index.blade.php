<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ translate('forgot_password')}}</title>
    <link rel="shortcut icon"
          href="{{getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type:'backend-logo')}}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/google-fonts.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/vendor.min.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/vendor/icon-set/style.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/theme.minc619.css?v=1.0') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/style.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/toastr.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/premium-toasts.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/css/intlTelInput.css') }}">

    <style>
        :root {
            --c1: {{ $web_config['primary_color'] }};
        }
    </style>

</head>

<body>
<main id="content" role="main" class="main">
    <div class="auth-wrapper">
        <div class="auth-wrapper-left"
             style="background: url('{{ dynamicAsset(path: 'public/assets/back-end/img/login_page_banner.png') }}') no-repeat center center / cover">
        </div>
        @php($eCommerceLogo = getWebConfig(name: 'company_web_logo'))

        <div class="auth-wrapper-right">
            <div class="auth-wrapper-form">
                <div>
                    <div class="d-block d-lg-none">
                        <a class="d-inline-flex mb-3" href="{{ route('home') }}">
                            <img width="100" src="{{ getStorageImages(path: $eCommerceLogo, type:'backend-logo') }}"
                                 alt="Logo">
                        </a>
                    </div>

                    <div class="mb-4">
                        <h1 class="h2 mb-2">{{ translate('forgot_password').'?' }}</h1>
                        <h1 class="h5 text-gray-900 fw-normal mb-0">
                            {{ translate('Follow_steps_to_reset_vendor_password') }}
                        </h1>
                    </div>

                    @php($verificationBy = getWebConfig('vendor_forgot_password_method'))
                    @if ($verificationBy == 'email')
                        <ol class="list-unstyled font-size-md text-start">
                            <li>
                                <span class="text-primary mr-2">1.</span>
                                {{ translate('enter_your_email_address_below') . '.' }}
                            </li>
                            <li>
                                <span class="text-primary mr-2">2.</span>
                                {{ translate('we_will_send_you_a_temporary_link_via_email') . '.' }}
                            </li>
                            <li>
                                <span class="text-primary mr-2">3.</span>
                                {{ translate('by_clicking_the_link_to_change_your_password_on_our_secure_website') . '.' }}
                            </li>
                        </ol>

                        <form id="form-id" action="{{route('freelancer.auth.forgot-password.index')}}" method="post"
                              id="admin-login-form" class="forget-password-form" novalidate>
                            @csrf
                            <div class="js-form-message form-group mt-5">
                                <label class="input-label" for="signingVendorPassword" tabindex="0">
                                    <span class="d-flex justify-content-between align-items-center">
                                            {{translate('Email_Address')}}
                                            <a href="{{route('freelancer.auth.login')}}">
                                                {{translate('back_to_login')}}
                                            </a>
                                    </span>
                                </label>

                                <input type="email" class="form-control form-control-lg" name="identity"
                                       value="{{ old('identity') }}"
                                       tabindex="1" placeholder="{{ translate('enter_email_address') }}"
                                       aria-label="{{ translate('enter_email_address') }}"
                                       required>
                            </div>
                            <button type="submit" class="btn btn-lg btn-block btn--primary">
                                {{ translate('Send')}}
                            </button>
                        </form>
                    @else
                        <ol class="list-unstyled font-size-md text-start">
                            <li>
                                <span class="text-primary mr-2">1.</span>
                                {{ translate('fill_in_your_phone_number_below') . '.' }}
                            </li>
                            <li>
                                <span class="text-primary mr-2">2.</span>
                                {{ translate('we_will_send_you_a_temporary_OTP_via_phone') . '.' }}
                            </li>
                            <li>
                                <span class="text-primary mr-2">3.</span>
                                {{ translate('use_the_OTP_to_change_your_password_on_our_secure_website') . '.' }}
                            </li>
                        </ol>

                        <form id="form-id" action="{{route('freelancer.auth.forgot-password.index')}}" method="post"
                              id="admin-login-form" class="forget-password-form" novalidate>
                            @csrf
                            <div class="js-form-message form-group mt-5">
                                <label class="input-label" for="forgotVendorPassword" tabindex="0">
                                    <span class="d-flex justify-content-between align-items-center">
                                            {{translate('phone')}}
                                            <a href="{{route('freelancer.auth.login')}}">
                                                {{translate('back_to_login')}}
                                            </a>
                                    </span>
                                </label>

                                <div class="form-group mb-3">
                                    <input
                                        type="tel"
                                        id="forgotVendorPassword"
                                        value="{{old('identity')}}"
                                        class="form-control phone-input-with-country-picker-forgot-password"
                                        placeholder="{{ translate('enter_phone_number') }}"
                                    />
                                    <input type="hidden" class="country-picker-phone-number-forgot-password w-100" name="identity" readonly>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-lg btn-block btn--primary">
                                {{ translate('Get_OTP')}}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>

{{-- Non-dismissible success modal: shown after reset link is emailed --}}
<div class="modal fade" id="email-reset-success-modal" tabindex="-1"
     aria-labelledby="emailResetSuccessLabel" aria-hidden="true"
     data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-body px-4 px-sm-5 py-5">
                <div class="d-flex flex-column align-items-center text-center">
                    <div class="mb-4" style="width:80px;height:80px;border-radius:50%;background:#e6f4ea;display:flex;align-items:center;justify-content:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none">
                            <path d="M5 12L10 17L19 8" stroke="#1B8A4B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h4 class="font-weight-bold mb-3" id="emailResetSuccessLabel" style="color:var(--c1);">
                        {{ translate('Check_Your_Email') }}!
                    </h4>
                    <p class="text-muted mb-0">
                        {{ translate('A_password_reset_link_has_been_sent_to_your_email_address') }}.
                        {{ translate('Please_check_your_inbox') }}.
                    </p>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <a href="{{ url('/') }}" class="btn btn--primary btn-lg px-5">
                    {{ translate('Go_to_Home') }}
                </a>
            </div>
        </div>
    </div>
</div>

<span class="system-default-country-code" data-value="{{ getWebConfig(name: 'country_code') ?? 'us' }}"></span>
<span id="message-please-check-recaptcha" data-text="{{ translate('please_check_the_recaptcha') }}"></span>
<span id="message-copied_success" data-text="{{ translate('copied_successfully') }}"></span>
<span id="route-get-session-recaptcha-code"
      data-route="{{ route('get-session-recaptcha-code') }}"
      data-mode="{{ env('APP_MODE') }}"
></span>

<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/theme.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/toastr.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor/forgot-password.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/backend/vendor/js/auth.js')}}"></script>
{!! Toastr::message() !!}

@if ($errors->any())
    <script>
        "use strict";
        @foreach($errors->all() as $error)
        toastr.error('{{$error}}', '', {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
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

<script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/country-picker-init.js') }}"></script>

<script>
    'use strict';
    try {
        initializePhoneInput(".phone-input-with-country-picker-forgot-password", ".country-picker-phone-number-forgot-password");
    } catch (e) {
    }
</script>

@if(session('show_email_reset_modal'))
    <script>
        "use strict";
        function showEmailResetSuccessModal() {
            var modalElement = document.getElementById('email-reset-success-modal');
            if (!modalElement) {
                return;
            }

            if (window.bootstrap && window.bootstrap.Modal) {
                var modal = new window.bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
                modal.show();
                return;
            }

            if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
                window.jQuery(modalElement).modal({
                    backdrop: 'static',
                    keyboard: false
                });
                window.jQuery(modalElement).modal('show');
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', showEmailResetSuccessModal);
        } else {
            showEmailResetSuccessModal();
        }
    </script>
@endif

</body>
</html>
