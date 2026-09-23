@extends('theme-views.layouts.app')
@section('title', translate('vendor_Apply').' | '.$web_config['company_name'].' '.translate('ecommerce'))
@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset(path: 'assets/plugins/intl-tel-input/css/intlTelInput.css') }}">
    <style>
        .otp-digit {
            width: 50px;
            height: 56px;
            font-size: 24px;
            border: 2px solid #d1d5db;
            border-radius: 10px;
            padding: 0;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .otp-digit:focus {
            border-color: #1a2f5e !important;
            box-shadow: 0 0 0 3px rgba(26, 47, 94, .15) !important;
            transform: translateY(-1px);
        }

        .otp-modal .modal-content {
            border: 0;
            overflow: hidden;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .2);
        }

        .otp-modal .modal-header {
            background: #1a2f5e;
            border: 0;
            padding: 24px 28px;
        }

        .otp-modal .modal-body {
            padding: 24px 20px 28px;
        }

        .otp-resend-link {
            color: #2563eb;
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .otp-resend-link:hover {
            color: #1d4ed8;
        }

        @keyframes otpShake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-8px); }
            40% { transform: translateX(8px); }
            60% { transform: translateX(-6px); }
            80% { transform: translateX(6px); }
        }

        .otp-shake {
            animation: otpShake .45s ease;
        }

        #otp-inputs {
            display: flex !important;
            justify-content: center !important;
            gap: 10px !important;
            flex-wrap: nowrap !important;
        }

        .otp-digit {
            width: 52px !important;
            min-width: 52px !important;
            max-width: 52px !important;
            height: 58px !important;
            font-size: 22px !important;
            font-weight: 700 !important;
            text-align: center !important;
            border: 2px solid #d1d5db !important;
            border-radius: 10px !important;
            padding: 0 !important;
            flex: 0 0 52px !important;
        }

        .otp-digit:focus {
            border-color: #1a2f5e !important;
            box-shadow: 0 0 0 3px rgba(26,47,94,0.15) !important;
            outline: none !important;
        }
    </style>
    @if(request('message'))
    <script>
        'use strict';
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                if (typeof toastr !== 'undefined') {
                    toastr.error('{{ request('message') }}');
                } else if (typeof toastMagic !== 'undefined') {
                    toastMagic.error('{{ request('message') }}');
                }
            }, 500);
        });
    </script>
    @endif
@endpush

@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3 mb-sm-5">
        <form id="seller-registration" action="{{route('vendor.auth.registration.index')}}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="first-el">
                <section>
                    <div class="container">
                        <div class="create-an-account p-3 p-sm-4">
                            <img src="{{theme_asset('assets/img/media/form-bg1.png')}}" alt="" class="create-an-accout-bg-img">
                            <div class="row">
                                @include('theme-views.seller-views.auth.partial.header')
                                <div class="col-lg-8">
                                    <div class="bg-white p-3 p-sm-4 rounded">
                                        <h4 class="mb-4">{{translate('Create_an_Account')}}</h4>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group mb-4">
                                                    <label for="vendor_name">
                                                        {{translate('name')}}
                                                        <span class="text-danger">*</span>
                                                        <span class="text-danger fs-12 vendor_name-error">@error('vendor_name') {{ $message }} @enderror</span>
                                                    </label>
                                                    <input class="form-control @error('vendor_name') is-invalid @enderror" type="text" id="vendor_name" name="vendor_name" value="{{ old('vendor_name') }}" placeholder="{{translate('Ex: John Doe')}}" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group mb-4">
                                                    <label for="email">
                                                        {{translate('email')}}
                                                        <span class="text-danger">*</span>
                                                        <span class="text-danger fs-12 mail-error">@error('email') {{ $message }} @enderror</span>
                                                    </label>
                                                    <input class="form-control @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="{{translate('Ex: example@gmail.com')}}" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group mb-4">
                                                    <label for="tel">
                                                        {{translate('phone')}}
                                                        <span class="text-danger">*</span>
                                                        <span class="text-danger fs-12 phone-error">@error('phone') {{ $message }} @enderror</span>
                                                    </label>
                                                    <div>
                                                        <input class="form-control form-control-user phone-input-with-country-picker @error('phone') is-invalid @enderror"
                                                               type="tel"
                                                               placeholder="{{ translate('enter_phone_number') }}" required>
                                                        <input type="hidden" class="country-picker-phone-number w-50" name="phone" value="{{ old('phone') }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group mb-4">
                                                    <label for="password">
                                                        {{translate('password')}}
                                                        <span class="text-danger">*</span>
                                                        <span class="text-danger fs-12 password-error">@error('password') {{ $message }} @enderror</span>
                                                    </label>
                                                    <div class="input-inner-end-ele">
                                                        <input class="form-control password-check @error('password') is-invalid @enderror" type="password" id="password" name="password" placeholder="{{translate('enter_password')}}" required>
                                                        <i class="bi bi-eye-slash-fill togglePassword"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group mb-4">
                                                    <label for="confirm_password">
                                                        {{translate('confirm_password')}}
                                                        <span class="text-danger">*</span>
                                                        <span class="text-danger fs-12 confirm_password-error"></span>
                                                    </label>
                                                    <div class="input-inner-end-ele">
                                                        <input class="form-control" type="password" id="confirm_password" name="password_confirmation" placeholder="{{translate('confirm_password')}}" required>
                                                        <i class="bi bi-eye-slash-fill togglePassword"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($web_config['recaptcha']['status'] == 1)
                                                <div class="col-12">
                                                    <div id="recaptcha-element-vendor-register" class="w-100 pt-2" data-type="image"></div>
                                                </div>
                                            @else
                                                <div class="col-12">
                                                    <div class="row py-2 mt-4">
                                                        <div class="col-6 pr-2">
                                                            <input type="text" class="form-control border __h-40" id="default-recaptcha-id-vendor-register" name="default_recaptcha_id_seller_regi" value=""
                                                                   placeholder="{{ translate('enter_captcha_value') }}" autocomplete="off" required>
                                                        </div>
                                                        <div class="col-6 input-icons mb-2 rounded bg-white">
                                                            <a id="re-captcha-vendor-register" class="d-flex align-items-center align-items-center">
                                                                <img src="{{ route('vendor.auth.recaptcha', ['tmp'=>1]).'?captcha_session_id=vendorRecaptchaSessionKey' }}" class="input-field rounded __h-40" alt="" id="default_recaptcha_id_regi">
                                                                <i class="bi bi-arrow-repeat icon cursor-pointer p-2"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-12 mb-3">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <input type="hidden" name="agree_to_terms" id="agree-to-terms-input" value="0">
                                                    <span id="terms-unchecked-icon"
                                                          role="button" tabindex="0"
                                                          id="terms-link-icon"
                                                          style="color:#dc3545;font-size:20px;cursor:pointer;transition:all .2s;line-height:1;">
                                                        &#9744;
                                                    </span>
                                                    <span id="terms-check-icon"
                                                          style="display:none;color:#16a34a;font-size:20px;font-weight:700;line-height:1;transition:all .2s;">
                                                        &#10003;
                                                    </span>
                                                    <span class="fs-14">
                                                        {{ translate('I_agree_with_the') }}
                                                        <a href="javascript:void(0)"
                                                           id="terms-link"
                                                           style="color:#2563eb;font-weight:700;text-decoration:underline;text-underline-offset:3px;">
                                                            {{ translate('Terms_&_Conditions') }}
                                                        </a>
                                                    </span>
                                                </div>
                                                <span id="terms-error" class="text-danger fs-12 d-none mt-1 d-block">
                                                    {{ translate('Please_read_and_agree_to_Terms_&_Conditions') }}
                                                </span>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex justify-content-end">
                                                    <button type="submit" class="btn btn-primary" id="vendor-apply-submit">{{translate('submit')}}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                @include('theme-views.seller-views.auth.partial.why-with-us')
                @include('theme-views.seller-views.auth.partial.business-process')
                @include('theme-views.seller-views.auth.partial.download-app')
                @include('theme-views.seller-views.auth.partial.faq')
            </div>
        </form>

        {{-- Terms & Conditions Modal --}}
        <div class="modal fade" id="termsAndConditionsModal"
             tabindex="-1" role="dialog"
             data-backdrop="static" data-keyboard="false"
             data-bs-backdrop="static" data-bs-keyboard="false"
             aria-labelledby="termsModalLabel"
             aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
                <div class="modal-content border-0"
                     style="border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.18);">

                    {{-- Header: navy-blue gradient, no X button --}}
                    <div class="modal-header border-0"
                         style="background:linear-gradient(135deg,#1a2f5e,#2563eb);padding:22px 28px;">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <polyline points="14 2 14 8 20 8" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="16" y1="13" x2="8" y2="13" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="16" y1="17" x2="8" y2="17" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                                    <polyline points="10 9 9 9 8 9" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <h5 class="modal-title text-white mb-0"
                                id="termsModalLabel"
                                style="font-weight:700;font-size:1.1rem;letter-spacing:0.3px;">
                                {{ translate('Terms_&_Conditions') }}
                            </h5>
                        </div>
                    </div>

                    {{-- Body: clean white, scrollable --}}
                    <div class="modal-body" id="terms-modal-body"
                         style="max-height:55vh;overflow-y:auto;background:#fff;padding:24px 28px;font-size:0.9rem;line-height:1.7;color:#374151;">
                        <style>
                            #termsAndConditionsModal .modal-body h1,
                            #termsAndConditionsModal .modal-body h2,
                            #termsAndConditionsModal .modal-body h3 { color:#1a2f5e;margin-top:1rem;margin-bottom:.5rem; }
                            #termsAndConditionsModal .modal-body h1:first-child,
                            #termsAndConditionsModal .modal-body h2:first-child,
                            #termsAndConditionsModal .modal-body h3:first-child { margin-top:0; }
                            #termsAndConditionsModal .modal-body p  { margin-bottom:.8rem; }
                            #termsAndConditionsModal .modal-body p:empty,
                            #termsAndConditionsModal .modal-body p.terms-empty-line { display:none; }
                            #termsAndConditionsModal .modal-body ul,
                            #termsAndConditionsModal .modal-body ol  { padding-left:1.4rem;margin-bottom:.8rem; }
                            #termsAndConditionsModal .modal-body::-webkit-scrollbar { width:5px; }
                            #termsAndConditionsModal .modal-body::-webkit-scrollbar-track { background:#f1f5f9; }
                            #termsAndConditionsModal .modal-body::-webkit-scrollbar-thumb { background:#cbd5e1;border-radius:4px; }
                        </style>
                        {!! optional($termsPage)->description ?? '<p class="text-muted">'.translate('No_terms_available.').'</p>' !!}
                    </div>

                    {{-- Footer: light grey, two styled buttons --}}
                    <div class="modal-footer border-0 d-flex align-items-center justify-content-end"
                         style="background:#f8fafc;padding:16px 24px;gap:10px;">
                        <button type="button"
                                id="terms-cancel-btn"
                                class="btn d-inline-flex align-items-center justify-content-center"
                                data-dismiss="modal" data-bs-dismiss="modal"
                                style="border:2px solid #94a3b8;color:#64748b;border-radius:10px;font-weight:700;line-height:1;height:42px;padding:0 24px;">
                            {{ translate('cancel') }}
                        </button>
                        <button type="button"
                                id="terms-agree-btn"
                                class="btn d-inline-flex align-items-center justify-content-center"
                                style="background:linear-gradient(135deg,#1a2f5e,#2563eb);color:#fff;border:none;border-radius:10px;font-weight:700;line-height:1;height:42px;padding:0 32px;box-shadow:0 4px 14px rgba(37,99,235,.35);">
                            {{ translate('I_Agree') }}
                        </button>
                    </div>

            </div>
        </div>
    </div>

    <div class="modal fade otp-modal" id="otpVerificationModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:460px;">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="text-white mb-1" style="font-weight:700;">Email Verification</h5>
                        <p class="mb-0 fs-13" style="color:rgba(255,255,255,0.7);">Enter the 6-digit code sent to your email</p>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div style="background:#f0f4ff;border-radius:10px;padding:12px 20px;display:inline-block;">
                            <span class="text-muted" style="font-size:13px;">Code sent to:</span>
                            <span id="otp-email-display" class="fw-bold" style="color:#1a2f5e;"></span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center mb-3" id="otp-inputs" style="gap:8px;flex-wrap:wrap;">
                        <input type="text" maxlength="1" class="otp-digit form-control text-center fw-bold">
                        <input type="text" maxlength="1" class="otp-digit form-control text-center fw-bold">
                        <input type="text" maxlength="1" class="otp-digit form-control text-center fw-bold">
                        <input type="text" maxlength="1" class="otp-digit form-control text-center fw-bold">
                        <input type="text" maxlength="1" class="otp-digit form-control text-center fw-bold">
                        <input type="text" maxlength="1" class="otp-digit form-control text-center fw-bold">
                    </div>

                    <div id="otp-error" class="alert alert-danger d-none text-center py-2 mb-3" style="border-radius:8px;font-size:13px;border:none;"></div>
                    <div id="otp-attempts-info" class="text-center mb-3 d-none" style="font-size:13px;"></div>

                    <div class="text-center mb-4">
                        <div id="otp-timer-wrap">
                            <span class="text-muted" style="font-size:13px;">Code expires in</span>
                            <span id="otp-timer" class="fw-bold" style="color:#1a2f5e;font-size:15px;">05:00</span>
                        </div>
                        <div id="otp-resend-wrap" class="d-none">
                            <span class="text-muted" style="font-size:13px;">Didn't receive the code?</span>
                            <a href="javascript:void(0)" id="otp-resend-btn" class="fw-bold ms-1 otp-resend-link">Resend OTP</a>
                        </div>
                    </div>

                    <button type="button" id="otp-verify-btn" class="btn btn-primary w-100 fw-bold py-3" style="border-radius:10px;font-size:15px;border:none;background:#1a2f5e;">
                        Verify & Complete Registration
                    </button>

                    <button type="button" id="otp-back-btn" class="btn w-100 fw-bold py-2 mt-2 d-none" style="background:#f1f5f9;color:#1a2f5e;border-radius:10px;font-size:14px;border:none;" onclick="location.reload();">
                        Back to Registration
                    </button>
                </div>
            </div>
        </div>
    </div>

        <span id="get-confirm-and-cancel-button-text" data-sure ="{{translate('are_you_sure').'?'}}"
              data-message="{{translate('want_to_apply_as_a_vendor').'?'}}"
              data-confirm="{{translate('yes')}}" data-cancel="{{translate('no')}}"></span>
        <span id="proceed-to-next-validation-message"
              data-mail-error="{{translate('please_enter_your_email').'.'}}"
              data-phone-error="{{translate('please_enter_your_phone_number').'.'}}"
              data-valid-mail="{{translate('please_enter_a_valid_email_address').'.'}}"
              data-enter-password="{{translate('please_enter_your_password').'.'}}"
        ></span>
    </main>
@endsection

@push('script')
    @if($web_config['recaptcha']['status'] == '1')
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
        <script>
            "use strict";
            var onloadCallback = function () {
                var reg_id = grecaptcha.render('recaptcha-element-vendor-register', {'sitekey': '{{ $web_config['recaptcha']['site_key'] }}'});

                $('#recaptcha-element-vendor-register').attr('data-reg-id', reg_id);
            };
        </script>
    @else
        <script>
            "use strict";
            $('#re-captcha-vendor-register').on('click',function () {
                let genUrl = "{{ route('vendor.auth.recaptcha', ['tmp'=>':dummy-id']) }}";
                genUrl = genUrl.replace(":dummy-id", Math.random());
                genUrl = genUrl + '?captcha_session_id=vendorRecaptchaSessionKey';
                document.getElementById('default_recaptcha_id_regi').src = genUrl;
            })
        </script>
    @endif
    <script src="{{ theme_asset('assets/plugins/intl-tel-input/js/intlTelInput.js') }}"></script>
    <script src="{{ theme_asset('assets/js/country-picker-init.js') }}"></script>
    <script src="{{theme_asset('assets/js/password-strength.js')}}"></script>
    <script>
        'use strict';
        $(document).ready(function() {
            let otpCountdownTimer = null;
            let timeLeft = 300;

            (function removeEmptyTermsLines() {
                var body = document.getElementById('terms-modal-body');
                if (!body) {
                    return;
                }
                body.querySelectorAll('p').forEach(function (p) {
                    var text = p.textContent.replace(new RegExp('[\s\u00A0]', 'g'), '');
                    var hasMedia = p.querySelector('img, br + br');
                    if (!text && !hasMedia) {
                        p.classList.add('terms-empty-line');
                    }
                });
            })();

            function openTermsModal() {
                var modalEl = document.getElementById('termsAndConditionsModal');
                if (window.bootstrap && window.bootstrap.Modal && modalEl) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl, {
                        backdrop: 'static',
                        keyboard: false
                    }).show();
                } else {
                    $('#termsAndConditionsModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $('#termsAndConditionsModal').modal('show');
                }
            }

            function resetOtpModalState() {
                clearInterval(otpCountdownTimer);
                timeLeft = 300;
                $('#otp-timer').text('05:00').css('color', '#1a2f5e');
                $('#otp-timer-wrap').show();
                $('#otp-resend-wrap').addClass('d-none');
                $('#otp-error').addClass('d-none').text('');
                $('#otp-attempts-info').addClass('d-none').text('');
                $('#otp-verify-btn').show().prop('disabled', false).text('Verify & Complete Registration');
                $('#otp-back-btn').addClass('d-none');
                $('.otp-digit').val('');
            }

            function startOtpTimer() {
                timeLeft = 300;
                clearInterval(otpCountdownTimer);
                $('#otp-timer-wrap').show();
                $('#otp-resend-wrap').addClass('d-none');
                $('#otp-timer').css('color', '#1a2f5e').text('05:00');

                otpCountdownTimer = setInterval(function() {
                    timeLeft--;
                    var minutes = Math.floor(timeLeft / 60);
                    var seconds = timeLeft % 60;
                    $('#otp-timer').text(
                        (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds
                    );

                    if (timeLeft <= 60) {
                        $('#otp-timer').css('color', '#dc3545');
                    }

                    if (timeLeft <= 0) {
                        clearInterval(otpCountdownTimer);
                        $('#otp-timer-wrap').hide();
                        $('#otp-resend-wrap').removeClass('d-none');
                    }
                }, 1000);
            }

            function focusFirstOtpDigit() {
                setTimeout(function() {
                    $('.otp-digit').first().trigger('focus');
                }, 300);
            }

            function closeTermsModal() {
                var modalEl = document.getElementById('termsAndConditionsModal');
                if (window.bootstrap && window.bootstrap.Modal && modalEl) {
                    var instance = window.bootstrap.Modal.getInstance(modalEl);
                    if (instance) {
                        instance.hide();
                    }
                } else {
                    $('#termsAndConditionsModal').modal('hide');
                }
            }

            $('#terms-link, #terms-unchecked-icon').on('click keypress', function(e) {
                if (e.type === 'keypress' && e.key !== 'Enter' && e.key !== ' ') {
                    return;
                }
                e.preventDefault();
                openTermsModal();
            });

            $('#terms-agree-btn').on('click', function() {
                $('#agree-to-terms-input').val('1');
                $('#terms-check-icon').show();
                $('#terms-unchecked-icon').hide();
                $('#terms-error').addClass('d-none');
                closeTermsModal();
            });

            $('#terms-cancel-btn').on('click', function() {
                $('#agree-to-terms-input').val('0');
                $('#terms-check-icon').hide();
                $('#terms-unchecked-icon').show();
                closeTermsModal();
            });

            $('#seller-registration').on('submit', function(e) {
                if ($('#agree-to-terms-input').val() !== '1') {
                    e.preventDefault();
                    $('#terms-error').removeClass('d-none');
                    toastr.error(
                        '{{ translate("Please_read_and_agree_to_Terms") }}'
                    );
                    setTimeout(function() {
                        $('#terms-link').click();
                    }, 500);
                    return false;
                }

                e.preventDefault();

                var form = $(this);
                var email = $('#email').val();
                var submitBtn = $('#vendor-apply-submit');

                submitBtn.prop('disabled', true).text('Sending OTP...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        submitBtn.prop('disabled', false).text('{{ translate("submit") }}');

                        if (response.status) {
                            resetOtpModalState();
                            $('#otp-email-display').text(email);
                            if (window.bootstrap && window.bootstrap.Modal) {
                                window.bootstrap.Modal.getOrCreateInstance(document.getElementById('otpVerificationModal'), {
                                    backdrop: 'static',
                                    keyboard: false
                                }).show();
                            } else {
                                $('#otpVerificationModal').modal({
                                    backdrop: 'static',
                                    keyboard: false
                                });
                                $('#otpVerificationModal').modal('show');
                            }
                            startOtpTimer();
                            focusFirstOtpDigit();
                            toastr.success(response.message || 'OTP sent to ' + email);
                        } else {
                            toastr.error(response.message || '{{ translate("Registration_failed") }}');
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).text('{{ translate("submit") }}');
                        var payload = xhr.responseJSON;

                        if (payload && payload.errors) {
                            var firstError = Object.values(payload.errors)[0][0];
                            if (firstError && firstError.indexOf('already registered') !== -1) {
                                toastr.error(
                                    firstError + ' <a href="/vendor/auth/login" style="color:#fff;font-weight:700;text-decoration:underline;">Login here</a>',
                                    '',
                                    {allowHtml: true, timeOut: 8000}
                                );
                            } else {
                                toastr.error(firstError);
                            }
                        } else if (payload && payload.message) {
                            toastr.error(payload.message);
                        } else {
                            toastr.error('{{ translate("Something_went_wrong") }}');
                        }
                    }
                });
            });

            $(document).on('keyup', '.otp-digit', function(e) {
                var index = $('.otp-digit').index(this);

                if (e.key >= '0' && e.key <= '9') {
                    $(this).val(e.key);
                    if (index < 5) {
                        $('.otp-digit').eq(index + 1).trigger('focus');
                    }
                } else if (e.key === 'Backspace') {
                    $(this).val('');
                    if (index > 0) {
                        $('.otp-digit').eq(index - 1).trigger('focus');
                    }
                }
            });

            $(document).on('keypress', '.otp-digit', function(e) {
                if (e.which < 48 || e.which > 57) {
                    e.preventDefault();
                }
            });

            $(document).on('paste', '.otp-digit', function(e) {
                var paste = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
                paste = paste.replace(/\D/g, '').substring(0, 6);

                if (paste.length === 6) {
                    $('.otp-digit').each(function(i) {
                        $(this).val(paste[i] || '');
                    });
                }
            });

            $('#otp-verify-btn').on('click', function() {
                var otp = '';
                $('.otp-digit').each(function() {
                    otp += $(this).val();
                });

                if (otp.length !== 6) {
                    $('#otp-error').removeClass('d-none').text('Please enter all 6 digits.');
                    return;
                }

                var btn = $(this);
                btn.prop('disabled', true).text('Verifying...');

                $.ajax({
                    url: '{{ route("vendor.auth.verify-registration-otp") }}',
                    method: 'POST',
                    data: {
                        otp: otp,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {
                            clearInterval(otpCountdownTimer);
                            $('#otp-error').addClass('d-none');
                            toastr.success(response.message || 'Registration successful');
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1200);
                            return;
                        }

                        if (response.blocked) {
                            clearInterval(otpCountdownTimer);
                            $('#otp-error').removeClass('d-none').text(response.message);
                            $('#otp-attempts-info').addClass('d-none');
                            btn.hide();
                            $('#otp-back-btn').removeClass('d-none');
                            $('#otp-timer-wrap').hide();
                            $('#otp-resend-wrap').addClass('d-none');
                            return;
                        }

                        if (response.expired) {
                            clearInterval(otpCountdownTimer);
                            $('#otp-error').removeClass('d-none').text(response.message);
                            btn.prop('disabled', false).text('Verify & Complete Registration');
                            $('#otp-timer-wrap').hide();
                            $('#otp-resend-wrap').removeClass('d-none');
                            $('.otp-digit').val('');
                            $('.otp-digit').first().trigger('focus');
                            return;
                        }

                        $('#otp-error').removeClass('d-none').text(response.message);
                        btn.prop('disabled', false).text('Verify & Complete Registration');

                        if (response.attempts_left !== undefined) {
                            $('#otp-attempts-info')
                                .removeClass('d-none')
                                .css('color', response.attempts_left === 1 ? '#dc3545' : '#f59e0b')
                                .html(
                                    response.attempts_left === 1
                                        ? '<strong>Last attempt. After this your session will be reset.</strong>'
                                        : 'Attempts remaining: ' + response.attempts_left
                                );
                        }

                        $('#otp-inputs').addClass('otp-shake');
                        setTimeout(function() {
                            $('#otp-inputs').removeClass('otp-shake');
                            $('.otp-digit').val('');
                            $('.otp-digit').first().trigger('focus');
                        }, 450);
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text('Verify & Complete Registration');
                        var payload = xhr.responseJSON;

                        if (payload && payload.message) {
                            $('#otp-error').removeClass('d-none').text(payload.message);
                        } else {
                            $('#otp-error').removeClass('d-none').text('Verification failed. Try again.');
                        }
                    }
                });
            });

            $('#otp-resend-btn').on('click', function() {
                $.ajax({
                    url: '{{ route("vendor.auth.resend-registration-otp") }}',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.status) {
                            resetOtpModalState();
                            startOtpTimer();
                            $('#otp-email-display').text($('#email').val());
                            toastr.success(response.message || 'OTP has been sent again');
                            focusFirstOtpDigit();
                        } else {
                            toastr.error(response.message || 'Failed to resend OTP');
                        }
                    },
                    error: function(xhr) {
                        var payload = xhr.responseJSON;
                        toastr.error((payload && payload.message) ? payload.message : 'Failed to resend OTP');
                    }
                });
            });
        });
    </script>
    @if(request('message'))
    <script>
        'use strict';
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                if (typeof toastr !== 'undefined') {
                    toastr.error('{{ request('message') }}');
                } else if (typeof toastMagic !== 'undefined') {
                    toastMagic.error('{{ request('message') }}');
                }
            }, 500);
        });
    </script>
    @endif
    @if(session('registration_error') || request('reg_error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 5000
                };
                toastr.error("{{ session('registration_error') ?: request('reg_error') }}");
            }, 800);
        });
    </script>
    @endif
@endpush
