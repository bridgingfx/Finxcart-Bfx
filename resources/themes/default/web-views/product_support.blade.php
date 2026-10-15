@extends('layouts.front-end.app')

@section('title', 'Product Support')

@push('css_or_js')
    <style>
        .product-support-wrap {
            background:
                linear-gradient(180deg, #fbfcfe 0%, #ffffff 100%);
        }

        .product-support-hero {
            border-radius: 26px;
            overflow: hidden;
            background:
                radial-gradient(circle at right top, rgba(245, 157, 35, .16), transparent 30%),
                linear-gradient(135deg, #101f3c 0%, #17325e 50%, #255f9d 100%);
            color: #fff;
        }

        .product-panel {
            background: #fff;
            border: 1px solid #e8eef6;
            border-radius: 20px;
            box-shadow: 0 16px 40px rgba(20, 44, 84, .06);
        }

        .topic-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .topic-list li {
            border-bottom: 1px solid #eef2f8;
            padding: 14px 0;
            color: #52627a;
        }

        .topic-list li:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .product-secondary-text {
            color: #5f6f86 !important;
        }
    </style>
@endpush

@section('content')
    <div class="product-support-wrap py-5">
        <div class="container">
            <section class="product-support-hero p-4 p-md-5 mb-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="text-uppercase font-weight-bold d-inline-block mb-3" style="letter-spacing:.12em;color:rgba(255,255,255,.72);">
                            Product Assistance
                        </span>
                        <h1 class="text-white mb-3">{{ translate('support_for_the_products_and_services_you_buy_on_finxcart') }}</h1>
                        <p class="mb-0" style="max-width: 760px; color: rgba(255,255,255,.84); font-size: 1.05rem;">
                            Whether you need setup help, access guidance, compatibility clarification, or support before a
                            warranty request, this page points you in the right direction.
                        </p>
                    </div>
                </div>
            </section>

            <div class="row g-4 mb-4">
                <div class="col-lg-7">
                    <div class="product-panel p-4 p-md-5 h-100">
                        <h2 class="h4 mb-3">{{ translate('common_product_support_topics') }}</h2>
                        <ul class="topic-list">
                            <li>{{ translate('installation_setup_and_onboarding_questions') }}</li>
                            <li>{{ translate('access_issues_for_digital_products_dashboards_or_delivered_s') }}</li>
                            <li>{{ translate('feature_update_and_compatibility_clarification_before_or_aft') }}</li>
                            <li>{{ translate('guidance_before_submitting_a_warranty_refund_or_service_conc') }}</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="product-panel p-4 p-md-5 h-100">
                        <h2 class="h4 mb-3">{{ translate('best_way_to_get_faster_help') }}</h2>
                        <p class="product-secondary-text mb-3">
                            Preparing a few details before reaching support helps resolve issues much faster.
                        </p>
                        <ul class="topic-list">
                            <li>{{ translate('order_reference_or_transaction_details') }}</li>
                            <li>{{ translate('screenshots_logs_or_visible_error_messages') }}</li>
                            <li>{{ translate('short_description_of_what_happened_and_when') }}</li>
                            <li>{{ translate('device_browser_or_platform_information_when_relevant') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="product-panel p-4 p-md-5 mt-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h2 class="h4 mb-2">{{ translate('need_direct_assistance') }}</h2>
                        <p class="product-secondary-text mb-0">
                            If your issue is time-sensitive or related to product access, contact our support team with your
                            order information for a smoother resolution process.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                        <a href="{{ route('contacts') }}" class="btn btn-warning px-4 py-2">
                            Contact Product Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
