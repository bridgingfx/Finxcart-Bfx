@extends('payment.layouts.master')

@section('content')


    <div class="nowpayments-container">
        <h1 class="text-center">{{ __('Please do not refresh this page...') }}</h1>

        <div class="nowpayments-button-container">
            <button type="button" id="nowpay-button">Pay</button>
            <button type="button" class="nowpayments-cancel-button"
                id="nowpay-cancel-button">Cancel</button>
        </div>

    </div>

    <script type="text/javascript">
        "use strict";



            document.getElementById('nowpay-cancel-button').onclick = function() {
                window.location.href = "{{ route('now-payments.cancel') }}?payment_id={{ $payment->id }}";
            };

        // Auto-click Pay button after slight delay
        setTimeout(function() {
            let payButton = document.getElementById("nowpay-button");
            if (payButton) {
                payButton.click();
            }
        }, 500);

        // Pay button logic (redirect to NOWPayments invoice)
        document.getElementById("nowpay-button").onclick = function() {
            window.location.href = "{{ $invoice_url }}";
        };
    </script>
@endsection

@push('script')
    <style>
        .nowpayments-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
            gap: 1rem;
        }

        .nowpayments-button-container {
            display: flex;
            gap: 1rem;
        }

        .nowpayments-button-container button {
            background-color: rgba(69, 160, 73, 0.8);
            color: white;
            border: none;
            padding: .5rem 2rem;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .nowpayments-button-container button:hover {
            background-color: rgba(69, 160, 73, 1);
        }

        .nowpayments-button-container .nowpayments-cancel-button {
            background-color: rgba(235, 20, 20, 0.8);
        }

        .nowpayments-button-container .nowpayments-cancel-button:hover {
            background-color: rgba(235, 20, 20, 1);
        }
    </style>
@endpush
