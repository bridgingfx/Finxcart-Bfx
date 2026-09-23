@extends('layouts.front-end.app')

@section('title', translate('freelancer_registration'))

@push('css_or_js')
<link href="{{theme_asset(path: 'public/assets/back-end/css/select2.min.css')}}" rel="stylesheet"/>
<link href="{{theme_asset(path: 'public/assets/back-end/css/croppie.css')}}" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
<style>
    .vendor-reg-terms-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .vendor-reg-terms-link {
        color: #2563eb;
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 2px;
        transition: color .25s ease;
    }

    .vendor-reg-terms-link.is-agreed {
        color: #16a34a;
        text-decoration: none;
    }

    .otp-shake {
        animation: otpShake .45s ease;
    }

    @keyframes otpShake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-6px); }
        40%, 80% { transform: translateX(6px); }
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

    .vendor-reg-terms-modal .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
    }

    .vendor-reg-terms-modal .modal-header {
        background: #1a2f5e;
        border: 0;
        padding: 20px 28px;
        align-items: center;
        gap: 12px;
        border-radius: 14px 14px 0 0;
    }

    .vendor-reg-terms-modal .vendor-reg-terms-modal-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(255, 255, 255, .16);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 18px;
        flex: 0 0 36px;
    }

    .vendor-reg-terms-modal .modal-title {
        color: #fff;
        font-weight: 700;
        line-height: 1.2;
    }

    .vendor-reg-terms-modal .modal-body {
        background: #fff;
        max-height: 55vh;
        overflow-y: auto;
        padding: 24px;
        border-top: 1px solid #e2e8f0;
        color: #334155;
        font-size: 1rem;
        line-height: 1.7;
    }

    .vendor-reg-terms-modal .modal-body p,
    .vendor-reg-terms-modal .modal-body ul,
    .vendor-reg-terms-modal .modal-body ol {
        margin-bottom: .9rem;
    }

    .vendor-reg-terms-modal .modal-body p:empty,
    .vendor-reg-terms-modal .modal-body p:has(> br:only-child) {
        display: none;
    }

    .vendor-reg-terms-modal .modal-body p:last-child {
        margin-bottom: 0;
    }

    .vendor-reg-terms-modal .modal-body h1,
    .vendor-reg-terms-modal .modal-body h2,
    .vendor-reg-terms-modal .modal-body h3,
    .vendor-reg-terms-modal .modal-body h4,
    .vendor-reg-terms-modal .modal-body h5,
    .vendor-reg-terms-modal .modal-body h6 {
        color: #1e293b;
        font-weight: 700;
        margin-top: 1.25rem;
        margin-bottom: .75rem;
    }

    .vendor-reg-terms-modal .modal-body li + li {
        margin-top: .45rem;
    }

    .vendor-reg-terms-modal .modal-body .tc-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1a2f5e;
        margin-bottom: .35rem;
    }

    .vendor-reg-terms-modal .modal-body .tc-effective-date {
        font-size: .85rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 1.25rem;
    }

    .vendor-reg-terms-modal .modal-body .tc-heading {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        margin-top: 1.5rem;
        margin-bottom: .6rem;
    }

    .vendor-reg-terms-modal .modal-body .tc-title.tc-heading:first-child {
        margin-top: 0;
    }

    .vendor-reg-terms-modal .modal-body .tc-list {
        list-style: disc;
        margin: 0 0 1rem;
        padding-left: 1.4rem;
        display: flex;
        flex-direction: column;
        gap: .5rem;
    }

    .vendor-reg-terms-modal .modal-body .tc-list li {
        line-height: 1.6;
        margin: 0;
    }

    .vendor-reg-terms-modal .modal-footer {
        background: #f0f4ff;
        border-top: 1px solid #dbe4ff;
        padding: 16px 24px;
        gap: 10px;
    }

    .vendor-reg-terms-modal .btn-vendor-terms-cancel {
        border-radius: 8px;
        border: 1px solid #1a2f5e;
        background: #fff;
        color: #1a2f5e;
        font-weight: 600;
        padding: 10px 28px;
        min-width: 110px;
        box-shadow: none;
        transition: all .2s ease;
    }

    .vendor-reg-terms-modal .btn-vendor-terms-agree {
        border: none;
        border-radius: 8px;
        font-weight: 700;
        padding: 10px 28px;
        min-width: 120px;
        background: #1a2f5e;
        box-shadow: 0 12px 24px rgba(26, 47, 94, .25);
        transition: all .2s ease;
    }

    .vendor-reg-terms-modal .btn-vendor-terms-cancel:hover {
        background: #1a2f5e;
        border-color: #1a2f5e;
        color: #fff;
    }

    .vendor-reg-terms-modal .btn-vendor-terms-agree:hover {
        background: #243f79;
        box-shadow: 0 14px 28px rgba(26, 47, 94, .3);
        transform: translateY(-1px);
    }

    .vendor-reg-terms-modal .btn-vendor-terms-cancel:focus,
    .vendor-reg-terms-modal .btn-vendor-terms-agree:focus {
        box-shadow: none;
    }

    .vendor-reg-terms-modal .modal-footer .btn + .btn {
        margin-left: 0;
    }

    @media (max-width: 575.98px) {
        .vendor-reg-terms-modal .modal-header,
        .vendor-reg-terms-modal .modal-body,
        .vendor-reg-terms-modal .modal-footer {
            padding-left: 16px;
            padding-right: 16px;
        }

        .vendor-reg-terms-modal .modal-footer {
            flex-direction: column;
        }

        .vendor-reg-terms-modal .btn-vendor-terms-cancel,
        .vendor-reg-terms-modal .btn-vendor-terms-agree {
            width: 100%;
            min-width: 0;
        }
    }
</style>
@endpush


@section('content')
    <form id="seller-registration" action="{{route('freelancer.auth.registration.index')}}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <div class="py-5">
            <div class="first-el">
                <section>
                    <div class="container">
                        <div class="create-an-account p-3 p-sm-4">
                            <img src="{{theme_asset('public/assets/front-end/img/media/form-bg.png')}}" alt="" class="create-an-accout-bg-img">
                            <div class="row">
                                <div class="col-lg-4">
                                    <h3 class="mb-3">{{ translate('become_a_freelancer') }}</h3>
                                    <p class="text-muted">{{ translate('offer_your_services_and_manage_your_own_portfolio_on') }} {{ getWebConfig(name: 'company_name') }}.</p>
                                    <div class="my-4 text-center">
                                        <img width="308" src="{{ theme_asset('public/assets/front-end/img/media/seller-registration.png') }}" alt="" class="dark-support">
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="bg-white p-3 p-sm-4 rounded">
                                        <h4 class="mb-4 text text-capitalize">{{translate('create_an_account')}}</h4>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="mb-4">
                                                    <label for="vendor_name">
                                                        {{translate('name')}}
                                                        <span class="text-danger">*</span>
                                                        <span class="text-danger fs-12 vendor_name-error">@error('vendor_name') {{ $message }} @enderror</span>
                                                    </label>
                                                    <input class="form-control @error('vendor_name') is-invalid @enderror" type="text" id="vendor_name" name="vendor_name" value="{{ old('vendor_name') }}" placeholder="{{translate('Ex: John Doe')}}" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="mb-4">
                                                    <label for="email">
                                                        {{translate('email')}}
                                                        <span class="text-danger">*</span>
                                                        <span class="text-danger fs-12 mail-error">@error('email') {{ $message }} @enderror</span>
                                                    </label>
                                                    <input class="form-control @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="{{translate('Ex: example@gmail.com')}}" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="mb-4">
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
                                                <div class="mb-4">
                                                    <label for="password">
                                                        {{translate('password')}}
                                                        <span class="text-danger fs-12 password-error">@error('password') {{ $message }} @enderror</span>
                                                    </label>
                                                    <div class="password-toggle rtl" style="position: relative;">
                                                        <input class="form-control text-align-direction password-check @error('password') is-invalid @enderror" name="password" type="password" id="password"
                                                            placeholder="{{ translate('minimum_8_characters_long') }}" required
                                                            style="padding-right: 2.5rem;">
                                                        <label class="password-toggle-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); margin: 0; cursor: pointer;">
                                                            <input class="custom-control-input" type="checkbox">
                                                            <i class="tio-hidden password-toggle-indicator"></i>
                                                            <span class="sr-only">{{ translate('show_password') }}</span>
                                                        </label>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="mb-4">
                                                    <label for="confirm_password" class="text-capitalize">
                                                        {{translate('confirm_password')}}
                                                        <span class="text-danger fs-12 confirm-password-error"></span>
                                                    </label>
                                                    <div class="password-toggle rtl" style="position: relative;">
                                                        <input class="form-control text-align-direction" name="password_confirmation" type="password" id="confirm_password"
                                                               placeholder="{{ translate('confirm_password') }}" required
                                                               style="padding-right: 2.5rem;">
                                                        <label class="password-toggle-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); margin: 0; cursor: pointer;">
                                                            <input class="custom-control-input" type="checkbox">
                                                            <i class="tio-hidden password-toggle-indicator"></i>
                                                            <span class="sr-only">{{ translate('show_password') }}</span>
                                                        </label>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex align-items-start mt-2" style="gap:10px;">
                                                    <input type="hidden" name="agree_to_terms" id="agree-to-terms-input" value="0">
                                                    <input type="checkbox" id="terms-checkbox"
                                                           style="width:18px;height:18px;margin-top:2px;cursor:pointer;flex-shrink:0;accent-color:#1a2f5e;">
                                                    <label for="terms-checkbox" style="font-size:14px;color:#374151;margin:0;line-height:1.5;cursor:pointer;">
                                                        {{ translate('I_have_read_and_agree_to_the') }}
                                                        <a href="javascript:void(0)" id="terms-link"
                                                           style="color:#2563eb;font-weight:600;text-decoration:underline;text-underline-offset:2px;">
                                                            {{ translate('terms_&_conditions') }}
                                                        </a>
                                                    </label>
                                                </div>
                                                <div id="terms-error" class="text-danger fs-12 d-none mt-1">
                                                    {{ translate('Please_read_and_agree_to_Terms') }}
                                                </div>
                                            </div>
                                            <div class="col-12 mt-3">
                                                <div class="d-flex justify-content-end">
                                                    <button type="submit"
                                                            class="btn btn--primary"
                                                            id="vendor-apply-submit">
                                                        {{translate('submit')}}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </form>

    <div class="modal fade vendor-reg-terms-modal" id="termsAndConditionsModal" tabindex="-1" role="dialog"
         aria-labelledby="termsModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="vendor-reg-terms-modal-icon" aria-hidden="true">
                        <i class="tio-file-text"></i>
                    </span>
                    <h5 class="modal-title mb-0" id="termsModalLabel">{{ translate('Terms_&_Conditions') }}</h5>
                </div>
                <div class="modal-body">
                    {!! optional($termsPage ?? null)->description ?? '<p class="text-muted">'.translate('No_terms_available.').'</p>' !!}
                </div>
                <div class="modal-footer">
                    <button type="button" id="terms-cancel-btn" class="btn btn-vendor-terms-cancel">
                        {{ translate('cancel') }}
                    </button>
                    <button type="button" id="terms-agree-btn" class="btn btn-primary btn-vendor-terms-agree">
                        {{ translate('I_Agree') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade otp-modal" id="otpVerificationModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:460px;">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header" style="justify-content:space-between;">
                    <div>
                        <h5 class="text-white fw-bold mb-1">Email Verification</h5>
                        <p class="mb-0 fs-13" style="color:rgba(255,255,255,0.7);">Enter the 6-digit code sent to your email</p>
                    </div>
                    <button type="button" id="otp-cancel-btn"
                            style="background:rgba(255,255,255,0.15);border:none;border-radius:8px;color:#fff;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:18px;line-height:1;flex-shrink:0;"
                            title="Cancel and go back">
                        &times;
                    </button>
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
                        <div id="otp-resend-wrap">
                            <span class="text-muted" style="font-size:13px;">Didn't receive the code?</span>
                            <button type="button" id="otp-resend-btn" class="btn btn-link p-0 fw-bold ms-1 otp-resend-link" disabled>Resend OTP</button>
                        </div>
                    </div>

                    <button type="button" id="otp-verify-btn" class="btn w-100 text-white fw-bold py-3" style="background:#1a2f5e;border-radius:10px;font-size:15px;border:none;">
                        Verify & Complete Registration
                    </button>

                    <button type="button" id="otp-back-btn" class="btn w-100 fw-bold py-2 mt-2 d-none" style="background:#f1f5f9;color:#1a2f5e;border-radius:10px;font-size:14px;border:none;">
                        {{ translate('Back_to_Registration') }}
                    </button>
                </div>
            </div>
        </div>
    </div>


    <span id="get-confirm-and-cancel-button-text" data-sure ="{{translate('are_you_sure').'?'}}"
      data-message="{{translate('want_to_apply_as_a_freelancer').'?'}}"
      data-confirm="{{translate('yes')}}" data-cancel="{{translate('no')}}"></span>
    <span id="proceed-to-next-validation-message"
          data-mail-error="{{translate('please_enter_your_email').'.'}}"
          data-phone-error="{{translate('please_enter_your_phone_number').'.'}}"
          data-valid-mail="{{translate('please_enter_a_valid_email_address').'.'}}"
          data-enter-password="{{translate('please_enter_your_password').'.'}}"
    >
    </span>
@endsection

@push('script')
<script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>

<script>
    document.querySelectorAll('.password-toggle').forEach(wrapper => {
        const checkbox = wrapper.querySelector('input[type="checkbox"]');
        const passwordInput = wrapper.querySelector('input[type="password"], input[type="text"]');
        const icon = wrapper.querySelector('.password-toggle-indicator');

        checkbox.addEventListener('change', function () {
            if (checkbox.checked) {
                passwordInput.type = 'text';
                icon.classList.remove('tio-hidden');
                icon.classList.add('tio-visible');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('tio-visible');
                icon.classList.add('tio-hidden');
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        var otpCountdownTimer = null;
        var otpExpiresAt = null;

        function enhanceTermsContent() {
            var $body = $('.vendor-reg-terms-modal .modal-body');
            if ($body.data('enhanced')) {
                return;
            }

            var $paragraphs = $body.find('p').filter(function () {
                return $.trim($(this).text()) !== '';
            });

            if (!$paragraphs.length) {
                $body.data('enhanced', true);
                return;
            }

            $paragraphs.first().addClass('tc-title');

            var bulletBuffer = [];

            function flushBullets() {
                if (!bulletBuffer.length) {
                    return;
                }
                var $list = $('<ul class="tc-list"></ul>');
                bulletBuffer.forEach(function ($p) {
                    var html = $p.html().replace(/^\s*-\s*/, '');
                    $('<li></li>').html(html).appendTo($list);
                });
                bulletBuffer[0].before($list);
                bulletBuffer.forEach(function ($p) { $p.remove(); });
                bulletBuffer = [];
            }

            $paragraphs.each(function (index) {
                var $p = $(this);
                var text = $.trim($p.text());

                if (/^-\s+/.test(text)) {
                    bulletBuffer.push($p);
                    return;
                }

                flushBullets();

                if (index === 0) {
                    return;
                }

                if (/^Effective Date:/i.test(text)) {
                    $p.addClass('tc-effective-date');
                } else if (/^\d+\.\s+\S/.test(text) && text.length < 80) {
                    $p.addClass('tc-heading');
                }
            });

            flushBullets();
            $body.data('enhanced', true);
        }

        function openTermsModal() {
            enhanceTermsContent();
            $('#termsAndConditionsModal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#termsAndConditionsModal').modal('show');
        }

        function closeTermsModal() {
            $('#termsAndConditionsModal').modal('hide');
        }

        function syncTermsState(accepted) {
            $('#agree-to-terms-input').val(accepted ? '1' : '0');
            $('#terms-checkbox').prop('checked', accepted);
        }

        function resetOtpModalState(expiresAt) {
            clearTimeout(otpCountdownTimer);
            otpExpiresAt = parseInt(expiresAt, 10) || (Date.now() + 300000);
            $('#otp-timer').text('05:00').css('color', '#1a2f5e');
            $('#otp-timer-wrap').show();
            $('#otp-resend-wrap').show();
            $('#otp-resend-btn').prop('disabled', true).text('Resend OTP');
            $('#otp-error').addClass('d-none').text('');
            $('#otp-attempts-info').addClass('d-none').text('');
            $('#otp-verify-btn').show().prop('disabled', false).text('Verify & Complete Registration');
            $('#otp-back-btn').addClass('d-none');
            $('.otp-digit').val('');
        }

        function renderOtpTimer() {
            var remaining = Math.max(0, Math.ceil((otpExpiresAt - Date.now()) / 1000));
            var minutes = Math.floor(remaining / 60);
            var seconds = remaining % 60;
            $('#otp-timer').text(
                (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds
            );

            if (remaining <= 60) {
                $('#otp-timer').css('color', '#dc3545');
            }

            if (remaining <= 0) {
                clearTimeout(otpCountdownTimer);
                $('#otp-timer-wrap').hide();
                $('#otp-resend-wrap').show();
                $('#otp-resend-btn').prop('disabled', false).text('Resend OTP');
                return;
            }

            $('#otp-timer-wrap').show();
            $('#otp-resend-wrap').show();
            $('#otp-resend-btn').prop('disabled', true).text('Resend OTP');
            otpCountdownTimer = setTimeout(renderOtpTimer, 1000);
        }

        function startOtpTimer(expiresAt) {
            clearTimeout(otpCountdownTimer);
            otpExpiresAt = parseInt(expiresAt, 10) || (Date.now() + 300000);
            $('#otp-timer').css('color', '#1a2f5e');
            renderOtpTimer();
        }

        function focusFirstOtpDigit() {
            setTimeout(function () {
                $('.otp-digit').first().trigger('focus');
            }, 300);
        }

        $('#terms-checkbox').on('click', function (e) {
            e.preventDefault();
            if ($('#agree-to-terms-input').val() === '1') {
                syncTermsState(false);
            } else {
                openTermsModal();
            }
        });

        $('#terms-link').on('click', function (e) {
            e.preventDefault();
            openTermsModal();
        });

        $('#terms-agree-btn').on('click', function () {
            syncTermsState(true);
            $('#terms-error').addClass('d-none');
            closeTermsModal();
        });

        $('#terms-cancel-btn').on('click', function () {
            syncTermsState(false);
            closeTermsModal();
        });

        $('#seller-registration').on('submit', function (e) {
            e.preventDefault();

            if ($('#agree-to-terms-input').val() !== '1') {
                $('#terms-error').removeClass('d-none');
                $('#terms-checkbox').addClass('is-invalid').css('outline', '2px solid #dc3545');
                toastr.warning('{{ translate("Please_read_and_agree_to_Terms") }}');
                $('html, body').animate({ scrollTop: $('#terms-checkbox').offset().top - 100 }, 300, function () {
                    openTermsModal();
                });
                return false;
            }
            $('#terms-checkbox').css('outline', '');

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
                success: function (response) {
                    submitBtn.prop('disabled', false).text('{{ translate("submit") }}');

                    if (response.status) {
                        resetOtpModalState(response.otp_expires_at);
                        $('#otp-email-display').text(email);
                        $('#otpVerificationModal').modal({
                            backdrop: 'static',
                            keyboard: false
                        });
                        $('#otpVerificationModal').modal('show');
                        startOtpTimer(response.otp_expires_at);
                        focusFirstOtpDigit();
                        toastr.success(response.message || 'OTP sent to ' + email);
                        if (response.otp) {
                            // Local dev only — the backend only ever sends this field when
                            // app()->environment('local'), never in production.
                            toastr.info('Local dev OTP: ' + response.otp, '', {timeOut: 15000});
                        }
                    } else {
                        toastr.error(response.message || '{{ translate("Registration_failed") }}');
                    }
                },
                error: function (xhr) {
                    submitBtn.prop('disabled', false).text('{{ translate("submit") }}');
                    var payload = xhr.responseJSON;

                    if (payload && payload.errors) {
                        var firstError = Object.values(payload.errors)[0][0];
                        if (firstError && firstError.indexOf('already registered') !== -1) {
                            toastr.error(
                                firstError + ' <a href="/freelancer/auth/login" style="color:#fff;font-weight:700;text-decoration:underline;">Login here</a>',
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

        $(document).on('keyup', '.otp-digit', function (e) {
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

        $(document).on('keypress', '.otp-digit', function (e) {
            if (e.which < 48 || e.which > 57) {
                e.preventDefault();
            }
        });

        $(document).on('paste', '.otp-digit', function (e) {
            var paste = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
            paste = paste.replace(/\D/g, '').substring(0, 6);

            if (paste.length === 6) {
                $('.otp-digit').each(function (i) {
                    $(this).val(paste[i] || '');
                });
            }
        });

        $('#otp-verify-btn').on('click', function () {
            var otp = '';
            $('.otp-digit').each(function () {
                otp += $(this).val();
            });

            if (otp.length !== 6) {
                $('#otp-error').removeClass('d-none').text('Please enter all 6 digits.');
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).text('Verifying...');

            $.ajax({
                url: '{{ route("freelancer.auth.verify-registration-otp") }}',
                method: 'POST',
                data: {
                    otp: otp,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.status) {
                        clearInterval(otpCountdownTimer);
                        $('#otp-error').addClass('d-none');
                        toastr.success(response.message || 'Registration successful');
                        setTimeout(function () {
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
                        $('#otp-resend-wrap').show();
            $('#otp-resend-btn').prop('disabled', true).text('Resend OTP');
                        return;
                    }

                    if (response.expired) {
                        clearInterval(otpCountdownTimer);
                        $('#otp-error').removeClass('d-none').text(response.message);
                        btn.prop('disabled', false).text('Verify & Complete Registration');
                        $('#otp-timer-wrap').hide();
                        $('#otp-resend-wrap').show();
                    $('#otp-resend-btn').prop('disabled', false).text('Resend OTP');
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
                    setTimeout(function () {
                        $('#otp-inputs').removeClass('otp-shake');
                        $('.otp-digit').val('');
                        $('.otp-digit').first().trigger('focus');
                    }, 450);
                },
                error: function (xhr) {
                    btn.prop('disabled', false).text('Verify & Complete Registration');
                    var payload = xhr.responseJSON;

                    if (payload && payload.message) {
                        $('#otp-error').removeClass('d-none').text(payload.message);
                    } else {
                        $('#otp-error').removeClass('d-none').text('{{ translate("Verification_failed_Try_again") }}');
                    }
                }
            });
        });

        $('#otp-resend-btn').on('click', function () {
            if ($(this).prop('disabled')) return;
            var resendBtn = $(this);
            resendBtn.prop('disabled', true).text('Sending...');
            $.ajax({
                url: '{{ route("freelancer.auth.resend-registration-otp") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (response) {
                    if (response.status) {
                        resetOtpModalState(response.otp_expires_at);
                        startOtpTimer(response.otp_expires_at);
                        $('#otp-email-display').text($('#email').val());
                        toastr.success(response.message || 'OTP has been sent again');
                        if (response.otp) {
                            toastr.info('Local dev OTP: ' + response.otp, '', {timeOut: 15000});
                        }
                        resendBtn.text('Resend OTP');
                        focusFirstOtpDigit();
                    } else {
                        resendBtn.prop('disabled', false).text('Resend OTP');
                        toastr.error(response.message || 'Failed to resend OTP');
                    }
                },
                error: function (xhr) {
                    var payload = xhr.responseJSON;
                    resendBtn.prop('disabled', false).text('Resend OTP');
                    toastr.error((payload && payload.message) ? payload.message : 'Failed to resend OTP');
                }
            });
        });
        function cancelPendingOtpAndReload() {
            // Clearing this server-side is what actually closes the modal — the
            // page re-shows it on every load as long as a pending OTP exists
            // (see RegisterController::index()), so a bare reload here just
            // reopens the same modal with the same timer still running.
            $.ajax({
                url: '{{ route("freelancer.auth.cancel-registration-otp") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                complete: function () {
                    location.reload();
                }
            });
        }
        $('#otp-cancel-btn, #otp-back-btn').on('click', cancelPendingOtpAndReload);

        var pendingOtpEmail = @json($pendingOtpEmail ?? null);
        var pendingOtpExpiresAt = @json($pendingOtpExpiresAt ?? null);
        if (pendingOtpEmail && pendingOtpExpiresAt) {
            resetOtpModalState(pendingOtpExpiresAt);
            $('#otp-email-display').text(pendingOtpEmail);
            $('#otpVerificationModal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#otpVerificationModal').modal('show');
            startOtpTimer(pendingOtpExpiresAt);
            focusFirstOtpDigit();
        }
    });
</script>
@endpush
