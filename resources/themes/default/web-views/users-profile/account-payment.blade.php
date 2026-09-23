@extends('layouts.front-end.app')

@section('title', translate('payment_methods'))

@section('content')
    <div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
        <div class="row">
            @include('web-views.partials._profile-aside')

            <section class="col-lg-9 __customer-profile px-0">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <h5 class="font-bold m-0 fs-16">{{ translate('payment_methods') }}</h5>
                        </div>

                        <div class="d-flex flex-column justify-content-center align-items-center text-center py-5">
                            <i class="tio-wallet-outlined" style="font-size: 42px; color: #d8dee7;"></i>
                            <p class="text-muted mt-3 mb-0">{{ translate('no_saved_payment_methods_yet') }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
