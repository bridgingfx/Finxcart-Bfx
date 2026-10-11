@extends('layouts.front-end.app')

@section('title',translate('contact_us'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
    <style>
        .fx-page { padding: 32px 0 56px; }
        .fx-page-header { text-align: center; max-width: 640px; margin: 0 auto 8px; }
        .fx-eyebrow {
            display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: .08em;
            text-transform: uppercase; color: #F3A329; margin: 0 0 8px;
        }
        .fx-page-title {
            font-size: clamp(28px, 4vw, 40px); font-weight: 800; letter-spacing: -0.02em;
            color: #203F60; margin: 0 0 8px;
        }
        .fx-page-sub { color: #5B6674; font-size: 16px; margin: 0; }
        @media (max-width: 767px) {
            .fx-contact-submit { width: 100%; min-height: 48px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
    </style>
@endpush

@section('content')
@php($supportEmail = getWebConfig(name: 'company_email') ?: 'support@finxcart.com')
<div class="fx-page">
    <div class="fx-container">
        <div class="fx-page-header">
            <p class="fx-eyebrow">{{ translate('contact_us') }}</p>
            <h1 class="fx-page-title">{{ translate('send_us_a_message') }}</h1>
            <p class="fx-page-sub">{{ translate('we_usually_reply_within_one_business_day') }}</p>
        </div>

        <div class="row py-4 gy-3">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="tio-call text-primary fs-24"></i>
                        <div>
                            <h6 class="mb-1">{{ translate('call_us') }}</h6>
                            @php($companyPhone = getWebConfig(name: 'company_phone'))
                            @if($companyPhone)
                                <a href="{{ 'tel:'.$companyPhone }}">{{ $companyPhone }}</a>
                            @else
                                <span class="text-muted">{{ translate('phone_number_not_listed') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="tio-email text-primary fs-24"></i>
                        <div>
                            <h6 class="mb-1">{{ translate('mail_us') }}</h6>
                            <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="tio-poi text-primary fs-24"></i>
                        <div>
                            <h6 class="mb-1">{{ translate('Find_us') }}</h6>
                            @php($shopAddress = getWebConfig(name: 'shop_address'))
                            @if($shopAddress)
                                <p class="mb-0">{{ $shopAddress }}</p>
                            @else
                                <p class="mb-0 text-muted">{{ translate('address_not_listed') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row no-gutters py-5">
            <div class="col-lg-6 iframe-full-height-wrap ">
                <img class="for-contact-image" src="{{theme_asset(path: "public/assets/front-end/png/contact.png")}}" alt="">
            </div>
            <div class="col-lg-6">
                <div class="card fx-form-card">
                    <div class="card-body for-send-message">
                        <h2 class="h4 mb-4 text-center font-semibold text-black">{{translate('send_us_a_message')}}</h2>
                        <form action="{{route('contact.store')}}" method="POST" id="getResponse" novalidate>
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label >{{translate('your_name')}}</label>
                                        <input class="form-control name" name="name" type="text"
                                               value="{{ old('name') }}" placeholder="{{ translate('John_Doe') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="cf-email">{{translate('email_address')}}</label>
                                        <input class="form-control email" name="email" type="email"
                                               value="{{ old('email') }}"
                                               placeholder="{{ translate('enter_email_address') }}" required >

                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="cf-phone">{{translate('your_phone')}}</label>
                                        <input class="form-control mobile_number phone-input-with-country-picker" type="number"
                                               value="{{ old('mobile_number') }}" placeholder="{{translate('contact_number')}}" required>

                                        <div class="">
                                            <input type="hidden" class="country-picker-country-code w-50" name="country_code" readonly>
                                            <input type="hidden" class="country-picker-phone-number w-50" name="mobile_number" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="cf-subject">{{translate('subject')}}:</label>
                                        <input class="form-control subject" type="text" name="subject"
                                               value="{{ old('subject') }}" placeholder="{{translate('short_title')}}" required>

                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="cf-message">{{translate('message')}}</label>
                                        <textarea class="form-control message" name="message" rows="6" required>{{ old('subject') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            @php($recaptcha = getWebConfig(name: 'recaptcha'))
                            @if(isset($recaptcha) && $recaptcha['status'] == 1)
                                <div id="recaptcha_element" class="w-100" data-type="image"></div>
                                <br/>
                            @else
                                <div class="row mb-3 mt-1">
                                    <div class="col-6 pr-0">
                                        <input type="text" class="form-control" name="default_captcha_value" value=""
                                               placeholder="{{translate('enter_captcha_value')}}" autocomplete="off">
                                    </div>
                                    <div class="col-6 input-icons rounded">
                                        <a href="javascript:" class="get-contact-recaptcha-verify" data-link="{{ URL('/contact/code/captcha') }}">
                                            <img src="{{ URL('/contact/code/captcha/1') }}" class="input-field __h-44 rounded" id="default_recaptcha_id" alt="">
                                            <i class="tio-refresh icon"></i>
                                        </a>
                                    </div>
                                </div>
                            @endif
                            <div class=" ">
                                <button class="btn btn--primary fx-contact-submit" type="submit" >{{translate('send')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
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
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async
            defer></script>
    <script>
        "use strict";
        $("#getResponse").on('submit', function (e) {
            var response = grecaptcha.getResponse();
            if (response.length === 0) {
                e.preventDefault();
                toastr.error($('#message-please-check-recaptcha').data('text'));
            }
        });
    </script>
@endif

<script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>
@endpush
