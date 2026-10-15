@extends('payment.layouts.master')

@section('content')
    <div>
        <h1 class="text-center">{{ "Please do not refresh this page..." }}</h1>
    </div>

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function () {
            fetch("{{ url("payment/stripe/token/?payment_id={$data->id}") }}", {
                method: "GET",
            }).then(function (response) {
                if (!response.ok) {
                    throw new Error('Server returned ' + response.status);
                }
                return response.json();
            }).then(function (session) {
                if (!session.url) {
                    throw new Error('No checkout URL in response');
                }
                // Stripe.js's redirectToCheckout(sessionId) is deprecated and
                // rejected by newer Stripe.js versions; the Checkout Session
                // already carries its own hosted URL to redirect to directly.
                window.location.href = session.url;
            }).catch(function (error) {
                console.error('Stripe payment initialization error:', error);
                alert('Payment initialization failed. Please try again.');
            });
        });
    </script>
@endsection
