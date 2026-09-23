@extends('layouts.front-end.app')

@section('title', 'My Account')

@push('css_or_js')
    <style>
        .support-hero {
            background:
                radial-gradient(circle at top right, rgba(245, 157, 35, .18), transparent 34%),
                linear-gradient(135deg, #17325e 0%, #20467f 55%, #2c5aa0 100%);
            border-radius: 24px;
            color: #fff;
            overflow: hidden;
            position: relative;
        }

        .support-hero::after {
            content: "";
            position: absolute;
            right: -60px;
            bottom: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, .08);
            border-radius: 50%;
        }

        .support-card {
            border: 0;
            border-radius: 20px;
            box-shadow: 0 18px 50px rgba(20, 44, 84, .08);
            height: 100%;
        }

        .support-icon {
            width: 52px;
            height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: rgba(245, 157, 35, .14);
            color: #ef8b14;
            font-size: 20px;
        }

        .support-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .support-list li {
            position: relative;
            padding-left: 24px;
            margin-bottom: 12px;
            color: #54627a;
        }

        .support-list li::before {
            content: "";
            position: absolute;
            left: 0;
            top: 10px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #f59d23;
            box-shadow: 0 0 0 4px rgba(245, 157, 35, .16);
        }

        .support-link-card {
            border-radius: 18px;
            border: 1px solid #e9eef7;
            padding: 20px;
            background: #fff;
            transition: .2s ease;
        }

        .support-link-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(24, 47, 87, .08);
        }

        .support-secondary-text {
            color: #5f6f86 !important;
        }
    </style>
@endpush

@section('content')
    <div class="container py-5">
        <section class="support-hero px-4 px-md-5 py-5 mb-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="text-uppercase font-weight-bold d-inline-block mb-3" style="letter-spacing:.12em;color:rgba(255,255,255,.72);">
                        Customer Account
                    </span>
                    <h1 class="mb-3 text-white">{{ translate('everything_you_need_to_manage_your_finxcart_account') }}</h1>
                    <p class="mb-0" style="max-width: 760px; color: rgba(255,255,255,.84); font-size: 1.05rem;">
                        Access orders, update profile details, manage addresses, and stay on top of your marketplace activity
                        from one secure place.
                    </p>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card support-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="support-icon mb-3">
                            <i class="fa fa-user"></i>
                        </div>
                        <h2 class="h4 mb-3">{{ translate('what_you_can_do_in_your_account') }}</h2>
                        <ul class="support-list">
                            <li>{{ translate('update_your_profile_information_and_contact_details') }}</li>
                            <li>{{ translate('review_recent_orders_invoices_and_account_activity') }}</li>
                            <li>{{ translate('manage_saved_addresses_preferences_and_account_settings') }}</li>
                            <li>{{ translate('track_order_progress_and_monitor_support_related_updates') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card support-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="support-icon mb-3">
                            <i class="fa fa-lock"></i>
                        </div>
                        <h2 class="h4 mb-3">{{ translate('need_sign_in_help') }}</h2>
                        <p class="support-secondary-text mb-4">
                            If you are locked out of your account or need help accessing customer features, our team can help
                            guide you quickly.
                        </p>

                        <a href="{{ route('customer.auth.login') }}" class="btn btn-warning px-4 py-2 mb-3">
                            Sign In
                        </a>

                        <div class="support-link-card">
                            <div class="font-weight-bold mb-2">{{ translate('need_direct_assistance') }}</div>
                            <p class="support-secondary-text mb-3">
                                Reach out through our contact page for account recovery, sign-in support, or order-related help.
                            </p>
                            <a href="{{ route('contacts') }}" class="text-primary font-weight-bold">
                                Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="support-link-card h-100">
                            <div class="h5 mb-2">{{ translate('secure_access') }}</div>
                            <p class="support-secondary-text mb-0">
                                Your customer area keeps your orders, addresses, and profile details organized in one place.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="support-link-card h-100">
                            <div class="h5 mb-2">{{ translate('order_visibility') }}</div>
                            <p class="support-secondary-text mb-0">
                                Stay informed with quick access to order history, progress, and account-related actions.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="support-link-card h-100">
                            <div class="h5 mb-2">{{ translate('fast_support') }}</div>
                            <p class="support-secondary-text mb-0">
                                When something needs attention, our support channels help move things forward faster.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
