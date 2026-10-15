@extends('payment.layouts.master')

@section('content')
    <div class="nowpayments-processing-container">
        <h1 class="text-center">{{ __('Your payment is processing') }}</h1>
        <p class="text-center">{{ __('Please wait while we confirm your transaction.') }}</p>
        <div class="spinner-border text-success mt-3" role="status">
            <span class="visually-hidden">{{ __('Loading...') }}</span>
        </div>
    </div>

    <script type="text/javascript">
        "use strict";

        const paymentIdHash = @json($payment_id_hash);
        const statusUrl = @json(route('now-payments.status'));
        const successUrl = @json(url('/payment/now-payments/payment')) + '?payment_id=' + paymentIdHash + '&status=success';
        const pollIntervalMs = 3000;

        function checkPaymentStatus() {
            fetch(statusUrl + '?payment_id=' + encodeURIComponent(paymentIdHash), {
                headers: { 'Accept': 'application/json' },
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.is_paid) {
                        window.location.href = successUrl;
                    }
                })
                .catch(function () {});
        }

        setInterval(checkPaymentStatus, pollIntervalMs);
        checkPaymentStatus();
    </script>
@endsection

@push('script')
    <style>
        .nowpayments-processing-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
            gap: 0.5rem;
            padding: 1rem;
        }
    </style>
@endpush
