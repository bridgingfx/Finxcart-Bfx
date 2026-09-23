@extends('layouts.front-end.app')

@section('title', translate('payment_result'))

@push('css_or_js')
    <style>
        .payment-result-page { background: #f6f8fb; padding: 60px 0; }
        .payment-result-card { background: #fff; border: 1px solid #e7ebf0; border-radius: 12px; padding: 44px 30px; text-align: center; max-width: 480px; margin: 0 auto; }
        .payment-result-icon { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 30px; }
        .payment-result-icon.is-success { background: #e6f7ee; color: #16794f; }
        .payment-result-icon.is-fail { background: #fde8e8; color: #c0392b; }
        .payment-result-title { font-weight: 800; font-size: 20px; color: #17395e; margin-bottom: 8px; }
        .payment-result-subtitle { color: #6b7280; margin-bottom: 24px; }
    </style>
@endpush

@section('content')
    <div class="payment-result-page">
        <div class="container">
            <div class="payment-result-card">
                @if($flag === 'success' && $contract)
                    <div class="payment-result-icon is-success"><i class="tio-checkmark-circle"></i></div>
                    <div class="payment-result-title">{{ translate('payment_successful') }}</div>
                    <p class="payment-result-subtitle">{{ translate('the_freelancer_has_been_hired_and_notified') }}</p>
                    <a href="{{ route('hire.contracts.show', $contract->id) }}" class="btn btn--primary">{{ translate('view_contract') }}</a>
                @elseif($flag === 'success')
                    <div class="payment-result-icon is-success"><i class="tio-checkmark-circle"></i></div>
                    <div class="payment-result-title">{{ translate('payment_successful') }}</div>
                    <p class="payment-result-subtitle">{{ translate('we_are_finalizing_your_hire_check_my_hires_shortly') }}</p>
                    <a href="{{ route('hire.contracts.index') }}" class="btn btn--primary">{{ translate('my_hires') }}</a>
                @else
                    <div class="payment-result-icon is-fail"><i class="tio-clear"></i></div>
                    <div class="payment-result-title">{{ translate('payment_failed') }}</div>
                    <p class="payment-result-subtitle">{{ translate('your_payment_could_not_be_completed_the_freelancer_was_not_hired') }}</p>
                    <a href="{{ route('hire-freelancer') }}" class="btn btn--primary">{{ translate('browse_freelancer_services') }}</a>
                @endif
            </div>
        </div>
    </div>
@endsection
