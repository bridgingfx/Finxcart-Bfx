@extends('layouts.vendor.app')

@section('title', translate('choose_Payment_Method'))

@push('css_or_js')
<style>
    .tier-checkout-wrap { max-width: 1120px; margin: 0 auto; padding: 18px 0 42px; }
    .tier-checkout-hero { padding: 24px; margin-bottom: 22px; border-radius: 20px; color: #fff; background: linear-gradient(135deg, #073b74, #1684cf); box-shadow: 0 16px 36px rgba(7,59,116,.2); }
    .tier-checkout-hero i { width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; border-radius: 15px; background: rgba(255,255,255,.16); font-size: 22px; }
    .tier-checkout-card { border: 1px solid #e2eaf3; border-radius: 20px; box-shadow: 0 12px 32px rgba(20,49,78,.08); overflow: hidden; }
    .tier-checkout-card .card-header { padding: 20px 22px; border-bottom: 1px solid #e9eff5; background: #fff; }
    .tier-checkout-card .card-body { padding: 22px; }
    .gateway-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px; }
    .gateway-option { position: relative; display: block; margin: 0; cursor: pointer; }
    .gateway-option input { position: absolute; opacity: 0; pointer-events: none; }
    .gateway-card { min-height: 105px; display: flex; align-items: center; gap: 14px; padding: 17px; border: 2px solid #e3ebf4; border-radius: 16px; background: #fff; transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease; }
    .gateway-option:hover .gateway-card { transform: translateY(-3px); border-color: rgba(7,59,116,.28); box-shadow: 0 10px 24px rgba(20,49,78,.1); }
    .gateway-option input:checked + .gateway-card { border-color: #1684cf; background: #f1f8ff; box-shadow: 0 0 0 4px rgba(22,132,207,.12); }
    .gateway-logo { width: 54px; height: 54px; flex: 0 0 54px; display: flex; align-items: center; justify-content: center; padding: 7px; border: 1px solid #e5edf5; border-radius: 14px; background: #fff; }
    .gateway-logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .gateway-check { margin-left: auto; color: #1684cf; opacity: 0; font-size: 21px; }
    .gateway-option input:checked + .gateway-card .gateway-check { opacity: 1; }
    .plan-summary { position: sticky; top: 92px; border: 0; border-radius: 20px; overflow: hidden; box-shadow: 0 14px 36px rgba(20,49,78,.12); }
    .plan-summary-head { padding: 22px; color: #fff; background: linear-gradient(135deg, #073b74, #1684cf); }
    .plan-summary-price { font-size: 35px; font-weight: 800; line-height: 1; }
    .plan-summary-row { display: flex; justify-content: space-between; gap: 15px; padding: 11px 0; border-bottom: 1px solid #edf1f5; color: #607287; }
    .plan-summary-row strong { color: #172b43; }
    .checkout-secure { display: flex; align-items: center; gap: 8px; margin-top: 15px; color: #087443; font-size: 12px; font-weight: 700; }
    .checkout-action { min-height: 46px; border-radius: 12px; font-weight: 700; }
    @media(max-width:767px) { .gateway-grid { grid-template-columns: 1fr; } .tier-checkout-wrap { padding-top: 8px; } }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <div class="tier-checkout-wrap">
        <div class="tier-checkout-hero d-flex align-items-center gap-3">
            <i class="tio-credit-card"></i>
            <div>
                <h2 class="text-white mb-1">{{ translate('Complete_your_tier_payment') }}</h2>
                <p class="mb-0 opacity-75">{{ translate('Choose_a_secure_payment_method_to_activate_your_plan.') }}</p>
            </div>
        </div>

        {{-- Tab nav --}}
        <ul class="nav nav-tabs mb-4" id="paymentTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="online-tab" data-toggle="tab" href="#online-payment" role="tab">
                    <i class="tio-credit-card mr-1"></i>{{ translate('Online_Payment') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ !$walletSufficient ? 'disabled' : '' }}" id="wallet-tab"
                   @if($walletSufficient) data-toggle="tab" href="#wallet-payment" @else href="javascript:void(0)" @endif
                   role="tab" style="{{ !$walletSufficient ? 'opacity:.5;cursor:not-allowed;pointer-events:none;' : '' }}">
                    <i class="tio-wallet-outlined mr-1"></i>{{ translate('Seller_Wallet') }}
                    @if($walletSufficient)
                        <span class="badge badge-soft-success ml-1">{{ translate('available') }}</span>
                    @else
                        <span class="badge badge-soft-danger ml-1">{{ translate('low_balance') }}</span>
                    @endif
                </a>
            </li>
        </ul>

        <div class="tab-content">
            {{-- Online Payment Tab --}}
            <div class="tab-pane fade show active" id="online-payment" role="tabpanel">
                <form method="post" action="{{ route('vendor.tier.web-payment-request') }}" id="tierPaymentForm" novalidate>
                    @csrf
                    <input type="hidden" name="tier_id" value="{{ $tier->id }}">
                    <input type="hidden" name="seller_id" value="{{ auth('seller')->id() }}">
                    <input type="hidden" name="payment_platform" value="web">
                    <input type="hidden" name="callback" id="selectedGatewayCallback" value="">
                    <input type="hidden" name="external_redirect_link" value="{{ route('web-payment-success') }}">

                    <div class="row g-4">
                        <section class="col-lg-8">
                            <div class="card tier-checkout-card">
                                <div class="card-header">
                                    <h4 class="mb-1"><i class="tio-wallet-outlined mr-2 text-primary"></i>{{ translate('Select_payment_method') }}</h4>
                                    <p class="text-muted mb-0 small">{{ translate('Your_payment_details_are_processed_securely_by_the_selected_gateway.') }}</p>
                                </div>
                                <div class="card-body">
                                    @if(!$activeMinimumMethods || $digital_payment['status'] != 1 || count($payment_gateways_list) === 0)
                                        <div class="text-center py-5">
                                            <img src="{{ theme_asset(path: 'public/assets/front-end/img/icons/nodata.svg') }}" alt="" width="72" class="mb-3">
                                            <h5>{{ translate('payment_methods_are_not_available_at_this_time.') }}</h5>
                                        </div>
                                    @else
                                        <div class="gateway-grid">
                                            @foreach($payment_gateways_list as $payment_gateway)
                                                @php($gatewayData = $payment_gateway->additional_data ? json_decode($payment_gateway->additional_data) : null)
                                                @php($gatewayTitle = $gatewayData?->gateway_title ?: str_replace('_', ' ', $payment_gateway->key_name))
                                                @php($gatewayImage = $gatewayData?->gateway_image)
                                                @php($gatewayCallback = $payment_gateway->mode === 'live' ? ($payment_gateway->live_values['callback_url'] ?? '') : ($payment_gateway->test_values['callback_url'] ?? ''))
                                                <label class="gateway-option">
                                                    <input type="radio" name="online_payment" value="{{ $payment_gateway->key_name }}"
                                                           data-callback="{{ $gatewayCallback }}" {{ $loop->first ? 'checked' : '' }} required>
                                                    <span class="gateway-card">
                                                        <span class="gateway-logo">
                                                            @if($gatewayImage)
                                                                <img src="{{ dynamicStorage(path: 'storage/app/public/payment_modules/gateway_image/'.$gatewayImage) }}" alt="{{ $gatewayTitle }}">
                                                            @else
                                                                <i class="tio-credit-card fs-24 text-primary"></i>
                                                            @endif
                                                        </span>
                                                        <span>
                                                            <strong class="d-block text-capitalize">{{ $gatewayTitle }}</strong>
                                                            <small class="text-muted">{{ translate('Secure_online_payment') }}</small>
                                                        </span>
                                                        <i class="tio-checkmark-circle gateway-check"></i>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </section>

                        <aside class="col-lg-4">
                            <div class="card plan-summary">
                                <div class="plan-summary-head">
                                    <small class="text-uppercase opacity-75">{{ translate('Selected_plan') }}</small>
                                    <h3 class="text-white mt-1 mb-3">{{ $tier->name }}</h3>
                                    <div><span class="plan-summary-price">${{ number_format($tier->monthly_fee_usd, 2) }}</span> <span class="opacity-75">/ {{ translate('month') }}</span></div>
                                </div>
                                <div class="card-body p-4">
                                    <div class="plan-summary-row"><span>{{ translate('Monthly_fee') }}</span><strong>${{ number_format($tier->monthly_fee_usd, 2) }}</strong></div>
                                    <div class="plan-summary-row"><span>{{ translate('Platform_commission') }}</span><strong>{{ $tier->sales_commission_rate }}%</strong></div>
                                    <div class="plan-summary-row"><span>{{ translate('Due_now') }}</span><strong>${{ number_format($amount, 2) }}</strong></div>
                                    <div class="checkout-secure"><i class="tio-verified-outlined"></i>{{ translate('Secure_payment_and_instant_activation') }}</div>
                                    <button type="submit" class="btn btn--primary w-100 mt-4 checkout-action" {{ !$activeMinimumMethods ? 'disabled' : '' }}>
                                        <i class="tio-lock-outlined mr-1"></i>{{ translate('Pay_and_activate_plan') }}
                                    </button>
                                    <a href="{{ route('vendor.tier.index') }}" class="btn btn-outline-secondary w-100 mt-2 checkout-action d-flex align-items-center justify-content-center">
                                        {{ translate('Change_plan') }}
                                    </a>
                                </div>
                            </div>
                        </aside>
                    </div>
                </form>
            </div>

            {{-- Seller Wallet Tab --}}
            <div class="tab-pane fade" id="wallet-payment" role="tabpanel">
                <div class="row g-4">
                    <section class="col-lg-8">
                        <div class="card tier-checkout-card">
                            <div class="card-header">
                                <h4 class="mb-1"><i class="tio-wallet-outlined mr-2 text-primary"></i>{{ translate('Pay_with_Seller_Wallet') }}</h4>
                                <p class="text-muted mb-0 small">{{ translate('Instantly_deducted_from_your_available_wallet_balance.') }}</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-sm-4 text-center">
                                        <div class="p-3 rounded bg-light border">
                                            <p class="mb-1 fs-12 text-muted">{{ translate('Wallet_Balance') }}</p>
                                            <h4 class="mb-0 {{ $walletSufficient ? 'text-success' : 'text-danger' }}">${{ number_format($walletBalance, 2) }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 text-center">
                                        <div class="p-3 rounded bg-light border">
                                            <p class="mb-1 fs-12 text-muted">{{ translate('Plan_Cost') }}</p>
                                            <h4 class="mb-0">${{ number_format($amount, 2) }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 text-center">
                                        <div class="p-3 rounded bg-light border">
                                            <p class="mb-1 fs-12 text-muted">{{ translate('After_Payment') }}</p>
                                            <h4 class="mb-0 {{ $walletSufficient ? 'text-success' : 'text-danger' }}">${{ number_format(max(0, $walletBalance - $amount), 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                @if(!$walletSufficient)
                                    <div class="alert alert-warning">
                                        <i class="tio-warning-outlined mr-1"></i>
                                        {{ translate('Insufficient_wallet_balance._Your_balance') }}: <strong>${{ number_format($walletBalance, 2) }}</strong>,
                                        {{ translate('required') }}: <strong>${{ number_format($amount, 2) }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>

                    <aside class="col-lg-4">
                        <div class="card plan-summary">
                            <div class="plan-summary-head">
                                <small class="text-uppercase opacity-75">{{ translate('Selected_plan') }}</small>
                                <h3 class="text-white mt-1 mb-3">{{ $tier->name }}</h3>
                                <div><span class="plan-summary-price">${{ number_format($tier->monthly_fee_usd, 2) }}</span> <span class="opacity-75">/ {{ translate('month') }}</span></div>
                            </div>
                            <div class="card-body p-4">
                                <div class="plan-summary-row"><span>{{ translate('Wallet_balance') }}</span><strong>${{ number_format($walletBalance, 2) }}</strong></div>
                                <div class="plan-summary-row"><span>{{ translate('Due_now') }}</span><strong>${{ number_format($amount, 2) }}</strong></div>
                                <div class="plan-summary-row"><span>{{ translate('Balance_after') }}</span><strong>${{ number_format(max(0, $walletBalance - $amount), 2) }}</strong></div>
                                <form method="post" action="{{ route('vendor.tier.wallet-payment') }}" novalidate>
                                    @csrf
                                    <input type="hidden" name="tier_id" value="{{ $tier->id }}">
                                    <button type="submit" class="btn btn--primary w-100 mt-4 checkout-action" {{ !$walletSufficient ? 'disabled' : '' }}>
                                        <i class="tio-wallet-outlined mr-1"></i>{{ translate('Pay_with_Wallet') }}
                                    </button>
                                </form>
                                <a href="{{ route('vendor.tier.index') }}" class="btn btn-outline-secondary w-100 mt-2 checkout-action d-flex align-items-center justify-content-center">
                                    {{ translate('Change_plan') }}
                                </a>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    "use strict";
    document.addEventListener('DOMContentLoaded', function () {
        const callbackInput = document.getElementById('selectedGatewayCallback');
        const gatewayInputs = document.querySelectorAll('input[name="online_payment"]');
        const syncCallback = () => {
            const selected = document.querySelector('input[name="online_payment"]:checked');
            if (selected && callbackInput) callbackInput.value = selected.dataset.callback || '';
        };
        gatewayInputs.forEach(input => input.addEventListener('change', syncCallback));
        syncCallback();
    });
</script>
@endpush
