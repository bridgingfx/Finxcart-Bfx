@extends('layouts.front-end.app')

@section('title', translate('my_Wallet'))

@push('css_or_js')
    <link rel="stylesheet" href="{{theme_asset(path: 'public/assets/front-end/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{theme_asset(path: 'public/assets/front-end/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/daterangepicker.css')}}">
    <style>
        /* ── Wallet card ── */
        .wallet-hero-card {
            background: linear-gradient(135deg, var(--web-primary) 0%, #0d3b7a 100%);
            border-radius: 18px;
            overflow: hidden;
            position: relative;
            min-height: 190px;
            box-shadow: 0 8px 32px rgba(20, 85, 172, .28);
            border: none;
        }
        .wallet-hero-card::before {
            content: '';
            position: absolute;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            top: -80px;
            right: -60px;
            pointer-events: none;
        }
        .wallet-hero-card::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            bottom: -60px;
            left: -40px;
            pointer-events: none;
        }
        .wallet-balance-label {
            font-size: 13px;
            letter-spacing: .6px;
            text-transform: uppercase;
            opacity: .75;
            color: #fff;
        }
        .wallet-balance-amount {
            font-size: 2.6rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -1px;
            line-height: 1.1;
        }
        .wallet-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.15);
            backdrop-filter: blur(4px);
            border-radius: 30px;
            padding: 4px 14px;
            font-size: 12px;
            color: #fff;
            border: 1px solid rgba(255,255,255,.2);
        }

        /* ── Stat cards ── */
        .wallet-stat-card {
            border-radius: 14px;
            border: 1px solid #f0f0f0;
            padding: 16px 20px;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,.05);
            transition: box-shadow .2s;
        }
        .wallet-stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.09); }
        .wallet-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .stat-credit .wallet-stat-icon { background: rgba(25,185,84,.12); color: #19b954; }
        .stat-debit  .wallet-stat-icon { background: rgba(220,53,69,.12);  color: #dc3545; }
        .wallet-stat-label { font-size: 11px; color: #9098b1; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 2px; }
        .wallet-stat-amount { font-size: 1.25rem; font-weight: 700; color: #1a1d23; }

        /* ── Transaction items ── */
        .txn-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border-radius: 12px;
            background: #f8f9fc;
            border: 1px solid #eef0f6;
            transition: background .15s, box-shadow .15s;
        }
        .txn-item:hover { background: #f0f3fb; box-shadow: 0 2px 8px rgba(20,85,172,.07); }
        .txn-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
        }
        .txn-icon.credit { background: rgba(25,185,84,.12); color: #19b954; }
        .txn-icon.debit  { background: rgba(220,53,69,.12);  color: #dc3545; }
        .txn-icon.bonus  { background: rgba(255,193,7,.15);  color: #f0a500; }
        .txn-amount-credit { font-size: 1rem; font-weight: 700; color: #19b954; }
        .txn-amount-debit  { font-size: 1rem; font-weight: 700; color: #dc3545; }
        .txn-type-badge {
            display: inline-block;
            font-size: 11px;
            padding: 2px 10px;
            border-radius: 20px;
            font-weight: 500;
            background: #eef0f6;
            color: #6c757d;
            margin-top: 3px;
        }
        .txn-date { font-size: 12px; color: #9098b1; white-space: nowrap; }
        .txn-label { font-size: 13px; font-weight: 600; color: #333; margin-bottom: 2px; }

        /* ── Section card ── */
        .wallet-section-card {
            border-radius: 16px;
            border: 1px solid #eef0f6;
            box-shadow: 0 2px 16px rgba(0,0,0,.05);
        }

        /* ── Add Fund button ── */
        .btn-add-fund {
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.35);
            color: #fff;
            border-radius: 30px;
            padding: 6px 18px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background .2s;
        }
        .btn-add-fund:hover {
            background: rgba(255,255,255,.28);
            color: #fff;
            text-decoration: none;
        }

        /* ── Bonus carousel card ── */
        .bonus-card {
            border-radius: 14px;
            border: 1.5px solid var(--web-primary);
            background: linear-gradient(135deg, rgba(20,85,172,.04), rgba(20,85,172,.01));
            padding: 18px;
            position: relative;
            overflow: hidden;
            min-height: 140px;
        }
        .bonus-card-bg {
            position: absolute;
            bottom: 8px; right: 8px;
            width: 70px; opacity: .12;
        }

        /* ── Empty state ── */
        .empty-wallet-state { padding: 48px 0; }
        .empty-wallet-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #f0f3fb;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            color: #c0c8df;
        }

        /* ── Filter btn ── */
        #transactionFilterBtn {
            border-radius: 10px !important;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* ── Scrollable history ── */
        .txn-scroll { max-height: 520px; overflow-y: auto; padding-right: 2px; }
        .txn-scroll::-webkit-scrollbar { width: 4px; }
        .txn-scroll::-webkit-scrollbar-thumb { background: #d0d5e8; border-radius: 4px; }
    </style>
@endpush

@section('content')
    <div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
        <div class="row">
            @include('web-views.partials._profile-aside')

            <section class="col-lg-9 __customer-profile px-0">
                <div class="card wallet-section-card">
                    <div class="card-body p-3 p-md-4">

                        {{-- Header --}}
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-4">
                            <h5 class="font-bold m-0 fs-16">{{ translate('my_Wallet') }}</h5>
                            <button class="profile-aside-btn btn btn--primary px-2 rounded px-2 py-1 d-lg-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M7 9.81219C7 9.41419 6.842 9.03269 6.5605 8.75169C6.2795 8.47019 5.898 8.31219 5.5 8.31219C4.507 8.31219 2.993 8.31219 2 8.31219C1.602 8.31219 1.2205 8.47019 0.939499 8.75169C0.657999 9.03269 0.5 9.41419 0.5 9.81219V13.3122C0.5 13.7102 0.657999 14.0917 0.939499 14.3727C1.2205 14.6542 1.602 14.8122 2 14.8122H5.5C5.898 14.8122 6.2795 14.6542 6.5605 14.3727C6.842 14.0917 7 13.7102 7 13.3122V9.81219ZM14.5 9.81219C14.5 9.41419 14.342 9.03269 14.0605 8.75169C13.7795 8.47019 13.398 8.31219 13 8.31219C12.007 8.31219 10.493 8.31219 9.5 8.31219C9.102 8.31219 8.7205 8.47019 8.4395 8.75169C8.158 9.03269 8 9.41419 8 9.81219V13.3122C8 13.7102 8.158 14.0917 8.4395 14.3727C8.7205 14.6542 9.102 14.8122 9.5 14.8122H13C13.398 14.8122 13.7795 14.6542 14.0605 14.3727C14.342 14.0917 14.5 13.7102 14.5 13.3122V9.81219ZM12.3105 7.20869L14.3965 5.12269C14.982 4.53719 14.982 3.58719 14.3965 3.00169L12.3105 0.915687C11.725 0.330188 10.775 0.330188 10.1895 0.915687L8.1035 3.00169C7.518 3.58719 7.518 4.53719 8.1035 5.12269L10.1895 7.20869C10.775 7.79419 11.725 7.79419 12.3105 7.20869ZM7 2.31219C7 1.91419 6.842 1.53269 6.5605 1.25169C6.2795 0.970186 5.898 0.812187 5.5 0.812187C4.507 0.812187 2.993 0.812187 2 0.812187C1.602 0.812187 1.2205 0.970186 0.939499 1.25169C0.657999 1.53269 0.5 1.91419 0.5 2.31219V5.81219C0.5 6.21019 0.657999 6.59169 0.939499 6.87269C1.2205 7.15419 1.602 7.31219 2 7.31219H5.5C5.898 7.31219 6.2795 7.15419 6.5605 6.87269C6.842 6.59169 7 6.21019 7 5.81219V2.31219Z" fill="white"/>
                                </svg>
                            </button>
                        </div>

                        @php($addFundsToWallet = getWebConfig(name: 'add_funds_to_wallet'))

                        {{-- Wallet Hero Card --}}
                        <div class="wallet-hero-card p-4 mb-3">
                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 position-relative" style="z-index:2">
                                <div>
                                    <div class="wallet-balance-label mb-1">{{ translate('wallet_balance') }}</div>
                                    <div class="wallet-balance-amount">{{ webCurrencyConverter(amount: $totalWalletBalance ?? 0) }}</div>
                                    <div class="mt-2">
                                        <span class="wallet-chip">
                                            <i class="fa fa-shield" style="font-size:11px"></i>
                                            {{ translate('secured_wallet') }}
                                        </span>
                                    </div>
                                </div>
                                @if ($addFundsToWallet)
                                    <button class="btn-add-fund mt-1" data-toggle="modal" data-target="#addFundToWallet">
                                        <i class="fa fa-plus-circle"></i>
                                        {{ translate('add_Fund') }}
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Stat Cards --}}
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="wallet-stat-card stat-credit d-flex align-items-center gap-3">
                                    <div class="wallet-stat-icon"><i class="fa fa-arrow-down"></i></div>
                                    <div>
                                        <div class="wallet-stat-label">{{ translate('total_credited') }}</div>
                                        <div class="wallet-stat-amount text-success">{{ webCurrencyConverter(amount: $totalCredit) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="wallet-stat-card stat-debit d-flex align-items-center gap-3">
                                    <div class="wallet-stat-icon"><i class="fa fa-arrow-up"></i></div>
                                    <div>
                                        <div class="wallet-stat-label">{{ translate('total_debited') }}</div>
                                        <div class="wallet-stat-amount text-danger">{{ webCurrencyConverter(amount: $totalDebit) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Add Fund Bonus Carousel --}}
                        @if($addFundsToWallet && count($addFundBonusList) > 0)
                            <div class="mb-4">
                                <div class="{{ count($addFundBonusList) > 1 ? 'owl-carousel add-fund-carousel' : '' }}">
                                    @foreach ($addFundBonusList as $bonus)
                                        <div class="item">
                                            <div class="bonus-card">
                                                <h6 class="mb-1 font-bold text-primary">{{ $bonus->title }}</h6>
                                                <p class="mb-2 text-muted" style="font-size:12px">
                                                    {{ translate('valid_till') }} {{ date('d M, Y', strtotime($bonus->end_date_time)) }}
                                                </p>
                                                @if ($bonus->bonus_type == 'percentage')
                                                    <p class="mb-1" style="font-size:13px">
                                                        {{ translate('add_fund_to_wallet') }} <strong>{{ webCurrencyConverter(amount: $bonus->min_add_money_amount) }}</strong>
                                                        {{ translate('and_enjoy') }} <strong>{{ $bonus->bonus_amount }}%</strong> {{ translate('bonus') }}
                                                    </p>
                                                @else
                                                    <p class="mb-1" style="font-size:13px">
                                                        {{ translate('add_fund_to_wallet') }} <strong>{{ webCurrencyConverter(amount: $bonus->min_add_money_amount) }}</strong>
                                                        {{ translate('and_enjoy') }} <strong>{{ webCurrencyConverter(amount: $bonus->bonus_amount) }}</strong> {{ translate('bonus') }}
                                                    </p>
                                                @endif
                                                @if($bonus->description)
                                                    <p class="mb-0 text-primary font-bold" style="font-size:12px">{{ Str::limit($bonus->description, 50) }}</p>
                                                @endif
                                                <img class="bonus-card-bg" src="{{ theme_asset(path: 'public/assets/front-end/img/icons/add_fund_vector.png') }}" alt="">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Add Fund Modal --}}
                        <div class="modal fade" id="addFundToWallet" tabindex="-1" aria-labelledby="addFundToWalletModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-md">
                                <div class="modal-content" style="border-radius:18px; border:none; overflow:hidden;">
                                    <div class="modal-header border-0 pb-0">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body px-4 px-md-5 pb-4">
                                        <div class="text-center mb-4">
                                            <div style="width:56px;height:56px;border-radius:50%;background:rgba(20,85,172,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;color:var(--web-primary);">
                                                <i class="fa fa-credit-card"></i>
                                            </div>
                                            <h5 class="font-bold mb-1">{{ translate('add_Fund_to_Wallet') }}</h5>
                                            <p class="text-muted" style="font-size:13px">{{ translate('add_fund_by_from_secured_digital_payment_gateways') }}</p>
                                        </div>
                                        <form action="{{ route('customer.add-fund-request') }}" method="post" novalidate>
                                            @csrf
                                            <div class="mb-4">
                                                <input type="number"
                                                       class="h-70 form-control text-center rounded-10 fs-25-important font-bold"
                                                       id="add-fund-amount-input" name="amount" required
                                                       placeholder="{{ translate('ex') }}: {{ webCurrencyConverter(amount: 500) }}">
                                                <input type="hidden" value="web" name="payment_platform" required>
                                                <input type="hidden" value="{{ request()->url() }}" name="external_redirect_link" required>
                                            </div>

                                            <div id="add-fund-list-area">
                                                @if(count($paymentGatewayList) > 0)
                                                    <p class="font-bold mb-2" style="font-size:13px">
                                                        {{ translate('payment_Methods') }}
                                                        <small class="text-muted font-normal">({{ translate('faster_&_secure_way_to_pay_bill') }})</small>
                                                    </p>
                                                    <div class="gateways_list">
                                                        @forelse ($paymentGatewayList as $gateway)
                                                            <label class="form-check form--check rounded">
                                                                <input type="radio" class="form-check-input d-none" name="payment_method" value="{{ $gateway->key_name }}" required>
                                                                <div class="check-icon">
                                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <circle cx="8" cy="8" r="8" fill="#1455AC"/>
                                                                        <path d="M9.18475 6.49574C10.0715 5.45157 11.4612 4.98049 12.8001 5.27019L7.05943 11.1996L3.7334 7.91114C4.68634 7.27184 5.98266 7.59088 6.53004 8.59942L6.86856 9.22314L9.18475 6.49574Z" fill="white"/>
                                                                    </svg>
                                                                </div>
                                                                @php( $payment_method_title = !empty($gateway->additional_data) ? (json_decode($gateway->additional_data)->gateway_title ?? ucwords(str_replace('_',' ', $gateway->key_name))) : ucwords(str_replace('_',' ', $gateway->key_name)) )
                                                                @php( $payment_method_img = !empty($gateway->additional_data) ? json_decode($gateway->additional_data)->gateway_image : '' )
                                                                <div class="form-check-label d-flex align-items-center">
                                                                    <img width="60" alt="{{ translate('payment') }}" src="{{ getValidImage(path: 'storage/app/public/payment_modules/gateway_image/'.$payment_method_img, type:'banner') }}">
                                                                    <span class="ml-3">{{ $payment_method_title }}</span>
                                                                </div>
                                                            </label>
                                                        @empty
                                                        @endforelse
                                                    </div>
                                                    <div class="d-flex justify-content-center pt-3 pb-1">
                                                        <button type="submit" class="btn btn--primary w-100 rounded-10" id="add_fund_to_wallet_form_btn">
                                                            <i class="fa fa-lock mr-1"></i> {{ translate('add_Fund') }}
                                                        </button>
                                                    </div>
                                                @else
                                                    <p class="text-muted text-center" style="font-size:13px">{{ translate('no_Payment_Methods_Gateway_found') }}</p>
                                                @endif
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Transaction History --}}
                        <div>
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                <h6 class="font-bold fs-16 mb-0">{{ translate('Transaction_History') }}</h6>
                                <div class="dropdown">
                                    <form action="{{ route('wallet') }}" method="get" novalidate>
                                        <button type="button" id="transactionFilterBtn"
                                                class="btn border px-3 py-2 text-dark fs-14 d-inline-flex align-items-center gap-2"
                                                data-toggle="dropdown" aria-expanded="false">
                                            <i class="fa fa-filter text--primary {{ $filterCount > 0 ? '' : '' }}"></i>
                                            {{ translate('filter') }}
                                            @if($filterCount > 0)
                                                <span class="badge badge-danger ml-1" style="font-size:11px;">{{ $filterCount }}</span>
                                            @endif
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end shadow transaction-filter_dropdown dropdown-menu-end-0">
                                            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                                <h6 class="font-bold fs-14 m-0">{{ translate('filter_data') }}</h6>
                                                <button id="filterCloseBtn" type="button" class="btn text-absolute-white border-0 rounded-circle lh-1 p-0 m-0 pt-1 d-flex justify-content-center align-items-center">
                                                    <i class="fa fa-close"></i>
                                                </button>
                                            </div>
                                            <div class="p-3 overflow-auto max-h-290px">
                                                <div class="mb-4">
                                                    <h6 class="mb-3 font-bold fs-13 text-muted text-uppercase" style="letter-spacing:.5px">{{ translate('filter_by') }}</h6>
                                                    <div class="d-flex gap-3 transaction_filter_by">
                                                        <label type="button" class="btn p-2 min-w-60px {{ $filterBy == '' || $filterBy == 'all' ? 'btn-outline-primary' : 'btn-outline-secondary' }}">
                                                            {{ translate('all') }}
                                                            <input type="radio" name="filter_by" value="all" {{ $filterBy == '' || $filterBy == 'all' ? 'checked' : '' }}>
                                                        </label>
                                                        <label type="button" class="btn p-2 min-w-60px {{ $filterBy == 'debit' ? 'btn-outline-primary' : 'btn-outline-secondary' }}">
                                                            {{ translate('debit') }}
                                                            <input type="radio" name="filter_by" value="debit" {{ $filterBy == 'debit' ? 'checked' : '' }}>
                                                        </label>
                                                        <label type="button" class="btn p-2 min-w-60px {{ $filterBy == 'credit' ? 'btn-outline-primary' : 'btn-outline-secondary' }}">
                                                            {{ translate('credit') }}
                                                            <input type="radio" name="filter_by" value="credit" {{ $filterBy == 'credit' ? 'checked' : '' }}>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="mb-4">
                                                    <h6 class="mb-3 font-bold fs-13 text-muted text-uppercase" style="letter-spacing:.5px">{{ translate('date_range') }}</h6>
                                                    <div class="position-relative">
                                                        <span class="fa fa-calendar icon-absolute-on-right"></span>
                                                        <input type="text" id="dateRangeInput" name="transaction_range" class="form-control" placeholder="{{ translate('Select_Date') }}" value="{{ $transactionRange ?? '' }}"/>
                                                    </div>
                                                </div>
                                                <div class="mb-4">
                                                    <h6 class="mb-3 font-bold fs-13 text-muted text-uppercase" style="letter-spacing:.5px">{{ translate('earn_by') }}</h6>
                                                    <div class="d-flex flex-column gap-3 transaction_earn_by">
                                                        @foreach([
                                                            'order_place'       => 'Order_Transactions',
                                                            'order_refund'      => 'Order_Refund',
                                                            'loyalty_point'     => 'Converted_from_Loyalty_Point',
                                                            'add_fund'          => 'Added_via_Payment_Method',
                                                            'add_fund_by_admin' => 'add_fund_by_admin',
                                                            'earned_by_referral'=> 'earned_by_referral',
                                                        ] as $val => $label)
                                                            <label class="d-flex justify-content-between align-items-center">
                                                                <span style="font-size:13px">{{ translate($label) }}</span>
                                                                <input type="checkbox" class="earn-checkbox" name="types[]" value="{{ $val }}" {{ in_array($val, $transactionTypes) ? 'checked' : '' }}>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card shadow-lg p-3 d-flex flex-row gap-3 mb-0">
                                                <a href="{{ route('wallet') }}" class="btn btn-outline-secondary w-100">{{ translate('clear_filter') }}</a>
                                                <button type="submit" class="btn btn--primary w-100">{{ translate('filter') }}</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="txn-scroll">
                                <div class="d-flex flex-column gap-2">
                                    @foreach($walletTransactionList as $item)
                                        {{-- Admin Bonus row --}}
                                        @if ($item['admin_bonus'] > 0)
                                            <div class="txn-item">
                                                <div class="txn-icon bonus">
                                                    <i class="fa fa-gift"></i>
                                                </div>
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="txn-label">{{ translate('admin_bonus') }}</div>
                                                    <span class="txn-type-badge">{{ translate('bonus') }}</span>
                                                </div>
                                                <div class="text-end">
                                                    <div class="txn-amount-credit">+ {{ webCurrencyConverter(amount: $item['admin_bonus']) }}</div>
                                                    <div class="txn-date">{{ date('d M Y, h:i A', strtotime($item['created_at'])) }}</div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Main transaction row --}}
                                        <div class="txn-item">
                                            <div class="txn-icon {{ $item['debit'] != 0 ? 'debit' : 'credit' }}">
                                                <i class="fa {{ $item['debit'] != 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="txn-label">
                                                    {{ $item['debit'] != 0 ? webCurrencyConverter(amount: $item['debit']) : webCurrencyConverter(amount: $item['credit']) }}
                                                </div>
                                                <span class="txn-type-badge">
                                                    @if ($item['transaction_type'] == 'add_fund_by_admin')
                                                        {{ translate('add_fund_by_admin') }}{{ $item['reference'] == 'earned_by_referral' ? ' (' . translate($item['reference']) . ')' : '' }}
                                                    @elseif($item['transaction_type'] == 'order_place')
                                                        {{ translate('order_place') }}
                                                    @elseif($item['transaction_type'] == 'loyalty_point')
                                                        {{ translate('converted_from_loyalty_point') }}
                                                    @elseif($item['transaction_type'] == 'add_fund')
                                                        {{ translate('added_via_payment_method') }}
                                                    @else
                                                        {{ ucwords(str_replace('_', ' ', translate($item['transaction_type']))) }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="text-end">
                                                <div class="{{ $item['debit'] != 0 ? 'txn-amount-debit' : 'txn-amount-credit' }}">
                                                    {{ $item['debit'] != 0 ? '- ' . webCurrencyConverter(amount: $item['debit']) : '+ ' . webCurrencyConverter(amount: $item['credit']) }}
                                                </div>
                                                <div class="txn-date">{{ date('d M Y, h:i A', strtotime($item['created_at'])) }}</div>
                                                <span class="badge {{ $item['debit'] != 0 ? 'badge-danger' : 'badge-info' }}" style="font-size:10px;margin-top:3px;">
                                                    {{ $item['debit'] != 0 ? translate('debit') : translate('credit') }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if($walletTransactionList->count() == 0)
                                    <div class="empty-wallet-state text-center">
                                        <div class="empty-wallet-icon">
                                            <i class="fa fa-exchange"></i>
                                        </div>
                                        <h6 class="text-muted">{{ translate('you_do_not_have_any') }}</h6>
                                        <p class="text-muted" style="font-size:13px">
                                            {{ request('type') != 'all' ? ucwords(translate(request('type'))) : '' }}
                                            {{ translate('transaction_yet') }}
                                        </p>
                                        @if($addFundsToWallet)
                                            <button class="btn btn--primary btn-sm mt-2" data-toggle="modal" data-target="#addFundToWallet">
                                                <i class="fa fa-plus mr-1"></i> {{ translate('add_Fund') }}
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="mt-3">
                                @if (request()->has('type'))
                                    @php($paginationLinks = $walletTransactionList->links())
                                    @php($modifiedLinks = preg_replace('/href="([^"]*)"/', 'href="$1&type='.request('type').'"', $paginationLinks))
                                @else
                                    @php($modifiedLinks = $walletTransactionList->links())
                                @endif
                                {!! $modifiedLinks !!}
                            </div>
                        </div>

                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/owl.carousel.min.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/moment.min.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/daterangepicker.min.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/user-wallet.js') }}"></script>
@endpush
