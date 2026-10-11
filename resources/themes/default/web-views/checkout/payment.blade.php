@extends('layouts.front-end.app')

@section('title', translate('choose_Payment_Method'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/fx-commerce.css') }}?v=20260929">
    <script src="https://js.stripe.com/v3/"></script>
@endpush

@push('css_or_js')
    <style>
        button[disabled] {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
@endpush

@section('content')
    <div class="container fx fx-page">
        <nav class="fx-breadcrumb" aria-label="{{ translate('breadcrumb') }}">
            <ol>
                <li><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
                <li><a href="{{ route('shop-cart') }}">{{ translate('shopping_cart') }}</a></li>
                <li aria-current="page">{{ translate('payment') }}</li>
            </ol>
        </nav>

        <ol class="fx-steps" aria-label="{{ translate('checkout') }}">
            <li class="is-done"><span class="fx-steps__n">&#10003;</span><span class="fx-steps__label">{{ translate('shopping_cart') }}</span></li>
            <li class="is-done"><span class="fx-steps__n">&#10003;</span><span class="fx-steps__label">{{ translate('shipping_address') }}</span></li>
            <li class="is-current"><span class="fx-steps__n">3</span><span class="fx-steps__label">{{ translate('payment') }}</span></li>
        </ol>

        <div class="fx-page-head">
            <div>
                <h1>{{ translate('choose_Payment_Method') }}</h1>
                <p class="fx-sub">{{ translate('select_a_payment_method_to_complete_your_order') }}</p>
            </div>
        </div>

        <div class="fx-checkout-grid">
            <div class="fx-checkout-main">
                <section class="fx-section-card checkout_details" aria-labelledby="fx-pay-heading">
                    @if(!$activeMinimumMethods)
                        <div class="fx-empty">
                            <img src="{{ theme_asset(path: 'public/assets/front-end/img/icons/nodata.svg') }}" alt="" width="70">
                            <p class="fx-sub">{{ translate('payment_methods_are_not_available_at_this_time.') }}</p>
                        </div>
                    @else
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 id="fx-pay-heading" class="mb-0">{{ translate('payment_method') }}</h2>
                            <a href="{{ route('checkout-details') }}" class="fx-link">&larr; {{ translate('go_back') }}</a>
                        </div>

                        @if(($cashOnDeliveryBtnShow && $cash_on_delivery['status']) || (auth('customer')->check() && $wallet_status==1))
                            <p class="fx-sub mt-0">{{ translate('select_a_payment_method_to_proceed') }}</p>

                            <div class="fx-pay-options" role="radiogroup" aria-label="{{ translate('payment_method') }}">
                                @if($cashOnDeliveryBtnShow && $cash_on_delivery['status'])
                                    <div id="cod-for-cart" class="fx-pay-option-wrap">
                                        <form action="{{ route('checkout-complete') }}" method="post" class="needs-validation" id="cash_on_delivery_form" novalidate>
                                            @csrf
                                            <label class="fx-pay-option m-0">
                                                <input type="hidden" name="payment_method" value="cash_on_delivery">
                                                <input type="hidden" class="form-control" name="bring_change_amount" id="bring_change_amount_value">
                                                <span class="fx-pay-option__inner click-if-alone d-flex align-items-center gap-2 cursor-pointer">
                                                    <input type="radio" id="cash_on_delivery" class="custom-radio" name="online_payment" value="cash_on_delivery">
                                                    <img width="30" src="{{ theme_asset(path: 'public/assets/front-end/img/icons/money.png') }}" alt="">
                                                    <span>
                                                        <span class="fx-pay-option__name d-block">{{ translate('cash_on_Delivery') }}</span>
                                                        <span class="fx-pay-option__desc d-block">{{ translate('pay_in_cash_when_your_order_arrives') }}</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </form>
                                    </div>
                                @endif

                                @if(auth('customer')->check() && $wallet_status==1)
                                    <div class="fx-pay-option-wrap">
                                        <button class="fx-pay-option fx-pay-option--button w-100 text-left" type="button"
                                                data-toggle="modal" data-target="#wallet_submit_button">
                                            <img width="30" src="{{ theme_asset(path: 'public/assets/front-end/img/icons/wallet-sm.png') }}" alt=""/>
                                            <span>
                                                <span class="fx-pay-option__name d-block">{{ translate('pay_via_Wallet') }}</span>
                                                <span class="fx-pay-option__desc d-block">
                                                    {{ translate('balance') }}: {{ webCurrencyConverter(amount: auth('customer')->user()->wallet_balance ?? 0) }}
                                                </span>
                                            </span>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            @if($cashOnDeliveryBtnShow && $cash_on_delivery['status'])
                                <div class="bring_change_amount_section d--none">
                                    <div class="collapse mb-10px" id="bring_change_amount" data-more="{{ translate('See_More') }}" data-less="{{ translate('See_Less') }}">
                                        <div class="fx-bring-change row justify-content-start align-items-center g-2 px-3 py-12">
                                            <div class="col-sm-6">
                                                <h6 class="fs-12 mb-1 fw-bold">{{ translate('Bring_Change_Instruction') }}</h6>
                                                <p class="mb-0 fs-12 opacity-50">{{ translate('Insert_amount_if_you_need_deliveryman_to_bring') }}</p>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="fs-12 fw-bold" for="bring_change_amount_input">
                                                    {{ translate('Change_Amount') }} ({{ getCurrencySymbol(type: 'web') }})
                                                </label>
                                                <input type="text" class="form-control max-w-210px only-integer-input-field" id="bring_change_amount_input" placeholder="{{ translate('Amount') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center mb-10px">
                                        <a id="bring_change_amount_btn" class="fx-link fs-12" data-toggle="collapse" href="#bring_change_amount" role="button" aria-expanded="false" aria-controls="change_amount">
                                            {{ translate('see_more') }}
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if ($digital_payment['status']==1)
                            @if(count($payment_gateways_list) > 0 || (isset($offline_payment) && $offline_payment['status'] && count($offline_payment_methods)>0))
                                <div class="d-flex align-items-end gap-2 mb-3">
                                    <h3 class="mb-0 fx-section-title">{{ translate('pay_via_online') }}</h3>
                                    <span class="fx-sub fs-10">({{ translate('faster_&_secure_way_to_pay') }})</span>
                                </div>
                            @endif

                            <div class="fx-pay-options" role="radiogroup" aria-label="{{ translate('pay_via_online') }}">
                                @foreach ($payment_gateways_list as $payment_gateway)
                                    <div class="fx-pay-option-wrap">
                                        <form method="post" class="digital_payment" id="{{($payment_gateway->key_name)}}_form" action="{{ route('customer.web-payment-request') }}" novalidate>
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ auth('customer')->check() ? auth('customer')->user()->id : session('guest_id') }}">
                                            <input type="hidden" name="customer_id" value="{{ auth('customer')->check() ? auth('customer')->user()->id : session('guest_id') }}">
                                            <input type="hidden" name="payment_method" value="{{ $payment_gateway->key_name }}">
                                            <input type="hidden" name="payment_platform" value="web">

                                            @if ($payment_gateway->mode == 'live' && isset($payment_gateway->live_values['callback_url']))
                                                <input type="hidden" name="callback" value="{{ $payment_gateway->live_values['callback_url'] }}">
                                            @elseif ($payment_gateway->mode == 'test' && isset($payment_gateway->test_values['callback_url']))
                                                <input type="hidden" name="callback" value="{{ $payment_gateway->test_values['callback_url'] }}">
                                            @else
                                                <input type="hidden" name="callback" value="">
                                            @endif

                                            <input type="hidden" name="external_redirect_link" value="{{ route('web-payment-success') }}">
                                            <label class="fx-pay-option d-flex align-items-center gap-2 mb-0 cursor-pointer">
                                                <input type="radio" id="{{($payment_gateway->key_name)}}" name="online_payment" class="custom-radio" value="{{($payment_gateway->key_name)}}" {{ $payment_gateway->key_name === 'stripe' ? 'checked' : '' }}>
                                                <img width="30"
                                                     src="{{dynamicStorage(path: 'storage/app/public/payment_modules/gateway_image')}}/{{ $payment_gateway->additional_data && (json_decode($payment_gateway->additional_data)->gateway_image) != null ? (json_decode($payment_gateway->additional_data)->gateway_image) : ''}}" alt="">
                                                <span class="fx-pay-option__name text-capitalize">
                                                    @if($payment_gateway->additional_data && json_decode($payment_gateway->additional_data)->gateway_title != null)
                                                        {{ json_decode($payment_gateway->additional_data)->gateway_title }}
                                                    @else
                                                        {{ str_replace('_', ' ',$payment_gateway->key_name) }}
                                                    @endif
                                                </span>
                                            </label>
                                        </form>
                                    </div>
                                @endforeach
                            </div>

                            @if(isset($offline_payment) && $offline_payment['status'] && count($offline_payment_methods)>0)
                                <div class="fx-offline-pay mt-3">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <label class="fx-pay-option d-flex align-items-center gap-2 mb-0 cursor-pointer" for="pay_offline">
                                            <input type="radio" id="pay_offline" name="online_payment" class="custom-radio" value="pay_offline">
                                            <span class="fx-pay-option__name text-capitalize">{{ translate('pay_offline') }}</span>
                                        </label>
                                        <span data-toggle="tooltip" title="{{ translate('for_offline_payment_options,_please_follow_the_steps_below') }}">
                                            <i class="tio-info text-primary" aria-hidden="true"></i>
                                        </span>
                                    </div>

                                    <div class="mt-3 pay_offline_card d-none">
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($offline_payment_methods as $method)
                                                <button type="button" class="fx-btn fx-btn-ghost offline_payment_button text-capitalize" id="{{ $method->id }}">{{ $method->method_name }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <div class="fx-checkout-nav mt-4">
                            <a href="{{ route('checkout-details') }}" class="fx-btn fx-btn-ghost">&larr; {{ translate('back_to_shipping') }}</a>
                            <button type="button" class="fx-btn fx-btn-lg proceed_to_next_button action-checkout-function" id="payment_submit_button">
                                {{ translate('pay_now') }}
                            </button>
                        </div>
                        <p class="fx-currency-note mt-2">{{ translate('you_will_be_charged') }} {{ webCurrencyConverter(amount: $amount) }} ({{ translate('currency') }}: {{ session('currency_code') }})</p>
                    @endif
                </section>
            </div>

            <div class="fx-checkout-side">
                @include('web-views.partials._order-summary')
            </div>
        </div>
    </div>

    {{-- Offline payment modal --}}
    @if(isset($offline_payment) && $offline_payment['status'])
        <div class="modal fade" id="selectPaymentMethod" tabindex="-1" aria-labelledby="selectPaymentMethodLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content fx-modal-content">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('offline-payment-checkout-complete') }}" method="post" class="needs-validation form-loading-button-form" novalidate>
                            @csrf
                            <div class="d-flex justify-content-center mb-4">
                                <img width="52" src="{{ theme_asset(path: 'public/assets/front-end/img/select-payment-method.png') }}" alt="">
                            </div>
                            <p class="fs-14 text-center">{{ translate('pay_your_bill_using_any_of_the_payment_method_below_and_input_the_required_information_in_the_form') }}</p>

                            <select class="form-control mx-xl-5 max-width-661" id="pay_offline_method" name="payment_by" required>
                                <option value="" disabled>{{ translate('select_Payment_Method') }}</option>
                                @foreach ($offline_payment_methods as $method)
                                    <option value="{{ $method->id }}">{{ translate('payment_Method') }} : {{ $method->method_name }}</option>
                                @endforeach
                            </select>
                            <div class="" id="payment_method_field"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Wallet confirm modal --}}
    @if(auth('customer')->check() && $wallet_status==1)
        @php
            $customer_balance = auth('customer')->user()->wallet_balance
        @endphp
        @php
            $remain_balance = $customer_balance - $amount
        @endphp
        <div class="modal fade" id="wallet_submit_button" aria-labelledby="walletModalTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content fx-modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="walletModalTitle">{{ translate('wallet_payment') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('checkout-complete-wallet') }}" method="post" class="needs-validation" novalidate>
                        @csrf
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <label>{{ translate('your_current_balance') }}</label>
                                    <input class="form-control" type="text" value="{{ webCurrencyConverter(amount: $customer_balance ?? 0) }}" readonly>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <label>{{ translate('order_amount') }}</label>
                                    <input class="form-control" type="text" value="{{ webCurrencyConverter(amount: $amount ?? 0) }}" readonly>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <label>{{ translate('remaining_balance') }}</label>
                                    <input class="form-control" type="text" value="{{ webCurrencyConverter(amount: max(0, $remain_balance)) }}" readonly>
                                    @if ($customer_balance < $amount)
                                        <label class="__color-crimson mt-1">{{ translate('you_do_not_have_sufficient_balance_for_pay_this_order!!') }}</label>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="fx-btn fx-btn-ghost" data-dismiss="modal">{{ translate('close') }}</button>
                            <button type="submit" class="fx-btn" id="submitWallet" {{ $customer_balance >= $amount ? '' : 'disabled' }}>{{ translate('submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <span id="route-action-checkout-function" data-route="checkout-payment"></span>
@endsection

@push('script')
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector('form[action*="checkout-complete-wallet"]');
        const submitBtn = document.getElementById('submitWallet');

        if (form && submitBtn) {
            form.addEventListener('submit', function (e) {
                // Check if the submit button is disabled
                if (submitBtn.disabled) {
                    e.preventDefault(); // Stop form from submitting
                    return false;
                }

                // Disable the button and show "Processing..."
                submitBtn.disabled = true;
                submitBtn.innerText = 'Processing...';
            });
        }
    });
    </script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/payment.js') }}"></script>
    <script>
        // Stripe is pre-checked via the `checked` attribute in the blade template
        // above, but payment.js only enables the Proceed button in response to a
        // radio "change" event — a pre-checked radio never fires one on page load.
        // Dispatch it manually so Proceed starts enabled to match the visible state.
        // Deferred with setTimeout(0) so this always runs after payment.js's own
        // $(function(){ $(".proceed_to_next_button").addClass("disabled") })
        // ready-handler, regardless of exactly when jQuery fires it relative to
        // this synchronous script — without the defer, that handler can re-add
        // "disabled" after this dispatch already removed it, leaving Proceed
        // stuck unclickable even though Stripe shows visually selected.
        setTimeout(function () {
            var preselected = document.getElementById('stripe');
            if (preselected && preselected.checked) {
                preselected.dispatchEvent(new Event('change'));
            }
        }, 0);
    </script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/fx-commerce.js') }}?v=20260929"></script>
@endpush
