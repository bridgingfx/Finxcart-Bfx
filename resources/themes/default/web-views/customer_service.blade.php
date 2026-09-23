@extends('layouts.front-end.app')

@section('title', 'Customer Service')

@push('css_or_js')
    <style>
        .service-shell {
            background: linear-gradient(180deg, #f7f9fc 0%, #ffffff 100%);
        }

        .service-hero {
            background:
                radial-gradient(circle at top left, rgba(245, 157, 35, .18), transparent 28%),
                linear-gradient(135deg, #162f59 0%, #1c4478 56%, #2560a4 100%);
            color: #fff;
            border-radius: 26px;
            overflow: hidden;
        }

        .service-panel,
        .service-tile {
            background: #fff;
            border: 1px solid #ebf0f7;
            border-radius: 20px;
            box-shadow: 0 14px 36px rgba(20, 44, 84, .06);
        }

        .service-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: rgba(245, 157, 35, .14);
            color: #ef8b14;
            font-size: 21px;
        }

        .service-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .service-list li {
            padding: 0 0 14px 24px;
            position: relative;
            color: #56657e;
        }

        .service-list li::before {
            content: "";
            position: absolute;
            left: 0;
            top: 8px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #f59d23;
            box-shadow: 0 0 0 4px rgba(245, 157, 35, .14);
        }

        .service-secondary-text {
            color: #5f6f86 !important;
        }
    </style>
@endpush

@section('content')
    <div class="service-shell py-5">
        <div class="container">
            <section class="service-hero p-4 p-md-5 mb-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="text-uppercase font-weight-bold d-inline-block mb-3" style="letter-spacing:.12em;color:rgba(255,255,255,.72);">
                            FinxCart Support
                        </span>
                        <h1 class="text-white mb-3">{{ translate('got_questions_call_us_24_7_2') }}</h1>
                        <p class="mb-4" style="max-width: 760px; color: rgba(255,255,255,.84); font-size: 1.05rem;">
                            Our customer service team is here to help with orders, marketplace guidance, account questions,
                            and general support whenever you need us.
                        </p>
                        <a href="tel:{{ getWebConfig(name: 'company_phone') }}" class="btn btn-warning px-4 py-2 mr-3">
                            {{ getWebConfig(name: 'company_phone') }}
                        </a>
                        <a href="{{ route('contacts') }}" class="btn btn-outline-light px-4 py-2">
                            Contact Us
                        </a>
                    </div>
                </div>
            </section>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="service-panel p-4 p-md-5 h-100">
                        <div class="service-badge mb-3">
                            <i class="fa fa-headphones"></i>
                        </div>
                        <h2 class="h4 mb-3">{{ translate('how_we_can_help') }}</h2>
                        <ul class="service-list">
                            <li>{{ translate('order_payment_and_transaction_related_questions') }}</li>
                            <li>{{ translate('guidance_on_marketplace_pages_policies_and_account_support') }}</li>
                            <li>{{ translate('help_with_returns_refunds_warranty_and_customer_rights_infor') }}</li>
                            <li>{{ translate('assistance_connecting_you_to_the_right_team_for_product_or_v') }}</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="service-panel p-4 p-md-5 h-100">
                        <div class="service-badge mb-3">
                            <i class="fa fa-map-marker"></i>
                        </div>
                        <h2 class="h4 mb-3">{{ translate('contact info') }}</h2>
                        <p class="service-secondary-text mb-3">
                            Reach our team directly through phone or use our website contact options for detailed help.
                        </p>
                        <div class="mb-3">
                            <div class="font-weight-bold text-dark mb-1">{{ translate('phone') }}</div>
                            <a href="tel:{{ getWebConfig(name: 'company_phone') }}" class="text-primary">
                                {{ getWebConfig(name: 'company_phone') }}
                            </a>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark mb-1">{{ translate('office') }}</div>
                            <div class="service-secondary-text">{{ getWebConfig(name: 'shop_address') }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-tile p-4 h-100">
                        <div class="font-weight-bold mb-2">{{ translate('quick_response') }}</div>
                        <p class="service-secondary-text mb-0">
                            We aim to help customers quickly with clear, practical next steps.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-tile p-4 h-100">
                        <div class="font-weight-bold mb-2">{{ translate('real_marketplace_guidance') }}</div>
                        <p class="service-secondary-text mb-0">
                            From policy questions to purchase concerns, we help customers navigate the platform with confidence.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-tile p-4 h-100">
                        <div class="font-weight-bold mb-2">{{ translate('always_connected') }}</div>
                        <p class="service-secondary-text mb-0">
                            Use phone, contact forms, or page resources to reach the support path that fits your issue best.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
