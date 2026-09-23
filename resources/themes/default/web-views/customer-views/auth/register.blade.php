@extends('layouts.front-end.app')

@section('title',  translate('register'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
    <style>
        #custTermsModal .modal-body p {
            margin: 0 0 .9rem;
        }

        #custTermsModal .modal-body p:empty,
        #custTermsModal .modal-body p:has(> br:only-child) {
            display: none;
        }

        #custTermsModal .modal-body ul,
        #custTermsModal .modal-body ol {
            margin: 0 0 .9rem;
        }

        #custTermsModal .modal-body p:last-child {
            margin-bottom: 0;
        }

        #custTermsModal .modal-body .tc-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a2f5e;
            margin-bottom: .35rem;
        }

        #custTermsModal .modal-body .tc-effective-date {
            font-size: .85rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 1.25rem;
        }

        #custTermsModal .modal-body .tc-heading {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
            margin-top: 1.5rem;
            margin-bottom: .6rem;
        }

        #custTermsModal .modal-body .tc-title.tc-heading:first-child {
            margin-top: 0;
        }

        #custTermsModal .modal-body .tc-list {
            list-style: disc;
            margin: 0 0 1rem;
            padding-left: 1.4rem;
            display: flex;
            flex-direction: column;
            gap: .5rem;
        }

        #custTermsModal .modal-body .tc-list li {
            line-height: 1.6;
            margin: 0;
        }
    </style>
@endpush

@section('content')
    <div class="container py-4 __inline-7 text-align-direction">
        <div class="login-card">
            <div class="mx-auto __max-w-760">
                <h2 class="text-center h4 mb-4 font-bold text-capitalize fs-18-mobile">{{ translate('sign_up')}}</h2>
                <form class="needs-validation_" id="customer-register-form" action="{{ route('customer.auth.sign-up')}}"
                        data-action="{{ route('customer.auth.sign-up')}}"
                        method="post" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="form-label font-semibold">
                                    {{ translate('first_name')}}
                                    <span class="input-required-icon">*</span>
                                </label>
                                <input class="form-control text-align-direction" value="{{ old('f_name')}}" type="text" name="f_name"
                                        placeholder="{{ translate('Ex') }}: {{ translate('Jhone') }}"
                                        required >
                                <div class="invalid-feedback">{{ translate('please_enter_your_first_name')}}!</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="form-label font-semibold">
                                    {{ translate('last_name') }}
                                    <span class="input-required-icon">*</span>
                                </label>
                                <input class="form-control text-align-direction" type="text" value="{{old('l_name') }}" name="l_name"
                                        placeholder="{{ translate('ex') }}: {{ translate('Doe') }}" required>
                                <div class="invalid-feedback">{{ translate('please_enter_your_last_name') }}!</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="form-label font-semibold">
                                    {{ translate('email_address') }}
                                    <span class="input-required-icon">*</span>
                                </label>
                                <input class="form-control text-align-direction" type="email" value="{{old('email') }}" name="email"
                                     placeholder="{{ translate('enter_email_address') }}" autocomplete="off"
                                        required>
                                <div class="invalid-feedback">{{ translate('please_enter_valid_email_address') }}!</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="form-label font-semibold">
                                    {{ translate('phone_number') }}
                                    <span class="input-required-icon">*</span>
                                </label>
                                <input class="form-control text-align-direction phone-input-with-country-picker"
                                       type="tel"  value="{{ old('phone') }}"
                                       placeholder="{{ translate('enter_phone_number') }}" required>

                                <input type="hidden" class="country-picker-phone-number w-50" name="phone" readonly>

                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                               <label for="password">
                                                        {{translate('password')}}
                                                        <span class="text-danger fs-12 password-error"></span>
                                                    </label>
                                                    <div class="password-toggle rtl" style="position: relative;">
                                                        <input class="form-control text-align-direction password-check" name="password" type="password" id="password"
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
                            <div class="form-group">
                                <label for="password" class="text-capitalize">
                                                        {{translate('confirm_password')}}
                                                        <span class="text-danger fs-12 confirm-password-error"></span>
                                                    </label>
                                                    <div class="password-toggle rtl" style="position: relative;">
                                                        <input class="form-control text-align-direction" name="confirm_password" type="password" id="confirm_password"
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

                        @if ($web_config['ref_earning_status'])
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="form-label font-semibold">{{ translate('refer_code') }} <small class="text-muted">({{ translate('optional') }})</small></label>
                                <input type="text" id="referral_code" class="form-control"
                                name="referral_code" placeholder="{{ translate('use_referral_code') }}">
                            </div>
                        </div>
                        @endif

                    </div>
                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start" style="gap:10px;">
                                    <input type="checkbox" name="remember" id="inputChecked"
                                           style="width:18px;height:18px;margin-top:2px;cursor:pointer;flex-shrink:0;accent-color:#1a2f5e;">
                                    <label for="inputChecked" style="font-size:14px;color:#374151;margin:0;line-height:1.5;cursor:pointer;">
                                        {{ translate('i_agree_to_Your') }}
                                        <a href="javascript:void(0)" id="cust-terms-link"
                                           style="color:#2563eb;font-weight:600;text-decoration:underline;text-underline-offset:2px;">
                                            {{ translate('terms_and_condition') }}
                                        </a>
                                    </label>
                                </div>
                                <div id="cust-terms-error" class="text-danger d-none mt-1" style="font-size:12px;">
                                    {{ translate('Please_read_and_agree_to_Terms_&_Conditions') }}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                @php($recaptcha = getWebConfig(name: 'recaptcha'))
                                @if(isset($recaptcha) && $recaptcha['status'] == 1)
                                    <div id="recaptcha_element" class="w-100" data-type="image"></div>
                                @else
                                <div class="row">
                                    <div class="col-6 pr-2">
                                        <input type="text" class="form-control border __h-40" name="default_recaptcha_value_customer_regi" value=""
                                               id="customer-register-recaptcha-input"
                                                placeholder="{{ translate('enter_captcha_value') }}" autocomplete="off">
                                    </div>
                                    <div class="col-6 input-icons mb-2 w-100 rounded bg-white">
                                        <a href="javascript:"
                                           class="d-flex align-items-center align-items-center get-regi-recaptcha-verify get-session-recaptcha-auto-fill"
                                           data-link="{{ URL('/customer/auth/code/captcha') }}"
                                           data-session="{{ 'default_recaptcha_id_customer_regi' }}"
                                           data-input="#customer-register-recaptcha-input"
                                        >
                                            <img alt="" src="{{ URL('/customer/auth/code/captcha/1?captcha_session_id=default_recaptcha_id_customer_regi') }}" class="input-field rounded __h-40" id="default_recaptcha_id">
                                            <i class="tio-refresh icon cursor-pointer p-2"></i>
                                        </a>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="web-direction">
                        <div class="mx-auto mt-4 __max-w-356">
                            <button class="w-100 btn btn--primary" id="sign-up" type="submit" disabled>
                                {{ translate('sign_up') }}
                            </button>
                        </div>
                        @if($web_config['social_login_text'])
                            <div class="text-center m-3 text-black-50">
                                <small>{{ translate('or_continue_with') }}</small>
                            </div>
                        @endif
                        <div class="d-flex justify-content-center my-3 gap-2">
                            @if(isset($web_config['customer_login_options']['social_login']) && $web_config['customer_login_options']['social_login'])
                                @foreach ($web_config['customer_social_login_options'] as $socialLoginServiceKey => $socialLoginService)
                                    @if ($socialLoginService && $socialLoginServiceKey != 'apple')
                                        <a class="d-block" href="{{ route('customer.auth.service-login', $socialLoginServiceKey) }}">
                                            <img src="{{theme_asset(path: 'public/assets/front-end/img/icons/'.$socialLoginServiceKey.'.png') }}" alt="{{ translate($socialLoginServiceKey) }}">
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <div class="text-black-50 mt-3 text-center">
                            <small>
                                {{  translate('Already_have_account ') }}?
                                <a class="text-primary text-underline" href="{{ route('customer.auth.login') }}">
                                    {{ translate('sign_in') }}
                                </a>
                            </small>
                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="custTermsModal" tabindex="-1" role="dialog"
         data-backdrop="static" data-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
            <div class="modal-content border-0"
                 style="border-radius:14px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
                <div class="modal-header border-0 d-flex justify-content-between align-items-center"
                     style="background:#1a2f5e;padding:20px 28px;gap:12px;">
                    <h5 class="modal-title text-white mb-0" style="font-weight:700;">
                        {{ translate('terms_and_condition') }}
                    </h5>
                    <button type="button" id="cust-terms-close-btn" class="close"
                            aria-label="{{ translate('close') }}" style="opacity:.9;margin:0;padding:0;">
                        <span aria-hidden="true" style="color:#fff;font-size:28px;line-height:1;">&times;</span>
                    </button>
                </div>
                <div class="modal-body"
                     style="max-height:55vh;overflow-y:auto;background:#fff;padding:24px;color:#334155;font-size:1rem;line-height:1.7;">
                    {!! optional($termsPage)->description ?? '<p class="text-muted">'.translate('No_terms_available.').'</p>' !!}
                </div>
                <div class="modal-footer border-0"
                     style="background:#f0f4ff;padding:16px 24px;gap:10px;">
                    <button type="button" id="cust-terms-cancel-btn" class="btn"
                            style="border:1px solid #1a2f5e;background:#fff;color:#1a2f5e;font-weight:600;border-radius:8px;padding:10px 28px;">
                        {{ translate('cancel') }}
                    </button>
                    <button type="button" id="cust-terms-agree-btn" class="btn text-white"
                            style="background:#1a2f5e;border:none;font-weight:700;border-radius:8px;padding:10px 28px;">
                        {{ translate('I_Agree') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
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

    <script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>
    <script>
        $(document).ready(function () {
            function syncCustTerms(agreed) {
                $('#inputChecked').prop('checked', agreed).trigger('change');
            }

            function enhanceTermsContent() {
                var $body = $('#custTermsModal .modal-body');
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

            function openCustTermsModal() {
                enhanceTermsContent();
                $('#custTermsModal').modal({ backdrop: 'static', keyboard: false });
                $('#custTermsModal').modal('show');
            }

            // The checkbox is toggled by the browser *before* a "click"
            // handler runs (clicking the <label> text also triggers this,
            // with no mousedown on the input itself), so calling
            // preventDefault() inside a click handler is too late/unreliable
            // to stop it. Instead we let the native toggle happen and then,
            // on the very next tick, force the box back to whatever it was
            // and drive the actual state via syncCustTerms() only.
            $('#inputChecked').on('click', function () {
                var $checkbox = $('#inputChecked');
                var wasCheckedBeforeClick = !$checkbox.is(':checked');

                // Revert the native toggle; state changes only happen
                // through syncCustTerms() below.
                $checkbox.prop('checked', wasCheckedBeforeClick);

                if (wasCheckedBeforeClick) {
                    // Box was already agreed -> this click is an uncheck.
                    syncCustTerms(false);
                } else {
                    // Box was unchecked -> user wants to agree, show terms first.
                    openCustTermsModal();
                }
            });

            $('#cust-terms-link').on('click', function (e) {
                e.preventDefault();
                openCustTermsModal();
            });

            $('#cust-terms-agree-btn').on('click', function () {
                syncCustTerms(true);
                $('#cust-terms-error').addClass('d-none');
                $('#custTermsModal').modal('hide');
            });

            $('#cust-terms-cancel-btn, #cust-terms-close-btn').on('click', function () {
                syncCustTerms(false);
                $('#custTermsModal').modal('hide');
            });

            $('#customer-register-form').on('submit', function (e) {
                if (!$('#inputChecked').is(':checked')) {
                    e.preventDefault();
                    $('#cust-terms-error').removeClass('d-none');
                    enhanceTermsContent();
                    $('#custTermsModal').modal({ backdrop: 'static', keyboard: false });
                    setTimeout(function () { $('#custTermsModal').modal('show'); }, 300);
                    return false;
                }
            });
        });
    </script>
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
@endpush
