@extends('layouts.vendor.app')
@section('title', translate('dashboard'))
@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">

        @php $seller = auth('seller')->user(); @endphp

        @if($seller->status === 'rejected' && $seller->admin_remark)
            <div class="alert alert-danger d-flex align-items-start gap-3 mb-3" role="alert">
                <i class="tio-clear-circle fs-24 mt-1 flex-shrink-0"></i>
                <div>
                    <strong>{{ translate('account_rejected') }}:</strong>
                    {{ $seller->admin_remark }}
                    @if($seller->admin_remark_at)
                        <span class="text-muted small ms-2">- {{ \Carbon\Carbon::parse($seller->admin_remark_at)->diffForHumans() }}</span>
                    @endif
                    <div class="mt-2">
                        <a href="{{ route('vendor.verification.form') }}" class="btn btn-sm btn-outline-danger">
                            {{ translate('resubmit_credentials') }}
                        </a>
                    </div>
                </div>
            </div>
        @elseif($seller->status === 'pending' && $seller->admin_remark)
            <div class="alert alert-warning d-flex align-items-start gap-3 mb-3" role="alert">
                <i class="tio-info fs-24 mt-1 flex-shrink-0"></i>
                <div>
                    <strong>{{ translate('note_from_admin') }}:</strong> {{ $seller->admin_remark }}
                    @if($seller->admin_remark_at)
                        <span class="text-muted small ms-2">- {{ \Carbon\Carbon::parse($seller->admin_remark_at)->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
        @endif

        @php
            $activeTierRecord = \App\Models\VendorTier::where('seller_id', $seller->id)
                ->whereIn('status', ['active', 'trial'])
                ->latest()
                ->first();
            $tierExpiryDate = $activeTierRecord?->status === 'trial'
                ? $activeTierRecord?->trial_end_date
                : $activeTierRecord?->end_date;
            $daysLeft = $tierExpiryDate ? now()->diffInDays($tierExpiryDate, false) : null;
        @endphp
        @if($activeTierRecord && $daysLeft !== null && $daysLeft <= 7)
            <div class="alert {{ $daysLeft < 0 ? 'alert-danger' : 'alert-warning' }} d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <span>
                    @if($daysLeft < 0)
                        <strong>{{ translate('Your_subscription_has_expired.') }}</strong>
                        {{ translate('Product_listing_is_now_blocked.') }}
                    @else
                        {{ translate('Your_plan_ends_in') }}
                        <strong>{{ $daysLeft }} {{ $daysLeft === 1 ? translate('day') : translate('days') }}</strong>.
                        {{ translate('Renew_now_to_keep_listing_products.') }}
                    @endif
                </span>
                <a href="{{ route('vendor.tier.index') }}" class="btn btn-sm {{ $daysLeft < 0 ? 'btn-danger' : 'btn-warning' }}">
                    {{ $daysLeft < 0 ? translate('Subscribe_Now') : translate('Renew_Plan') }}
                </a>
            </div>
        @endif

        @php
            $kycMethod = getWebConfig('kyc_method') ?? 'manual';
            $verification = $seller?->vendorVerification;
            $kycStatus = $kycMethod === 'disabled'
                ? 'disabled'
                : ($seller->kyc_status ?? 'unsubmitted');
            $kycBadgeClass = match ($kycStatus) {
                'approved' => 'badge-success',
                'pending', 'resubmitted' => 'badge-warning text-dark',
                'rejected' => 'badge-danger',
                'disabled' => 'badge-info',
                default => 'badge-secondary',
            };
            $kycLabel = match ($kycStatus) {
                'approved' => 'Approved',
                'pending', 'resubmitted' => 'Pending Review',
                'rejected' => 'Rejected',
                'disabled' => 'Not Required',
                default => 'Not Started',
            };
        @endphp

        @if(!in_array($kycStatus, ['approved', 'disabled'], true))
        <div class="alert border d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3"
             style="background:rgba(7,59,116,.05);border-color:rgba(7,59,116,.16)!important;">
            <div>
                <div class="fw-semibold mb-1" style="color:#073b74;">
                    <i class="tio-shield-security mr-1"></i> {{ translate('KYC Verification') }}
                </div>
                <div class="text-muted fs-12">
                    @if($kycStatus === 'rejected' && $verification?->rejection_reason)
                        {{ $verification->rejection_reason }}
                    @elseif($kycStatus === 'pending' || $kycStatus === 'resubmitted')
                        {{ translate('Your documents are under admin review.') }}
                    @elseif($kycStatus === 'disabled')
                        {{ translate('KYC is not required for your account.') }}
                    @else
                        {{ translate('Complete your KYC verification to unlock vendor actions.') }}
                    @endif
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge {{ in_array($kycStatus, ['pending', 'resubmitted']) ? 'badge-warning text-dark' : ($kycStatus === 'rejected' ? 'badge-danger' : 'badge-primary') }}">{{ $kycLabel }}</span>
                @if(!in_array($kycStatus, ['approved', 'disabled', 'pending', 'resubmitted'], true))
                    <a href="{{ route('vendor.payout-kyc.index') }}" class="btn btn-sm btn--primary">
                        {{ translate('Complete KYC') }}
                    </a>
                @endif
            </div>
        </div>
        @endif

        <div class="page-header pb-0 border-0 mb-3">
            <div class="flex-between row align-items-center mx-1">
                <div>
                    <h1 class="page-header-title text-capitalize">{{translate('welcome').' '.auth('seller')->user()->f_name.' '.auth('seller')->user()->l_name}}</h1>
                    <p>{{ translate('monitor_your_business_analytics_and_statistics').'.'}}</p>
                </div>

                <div>
                    @if(auth('seller')->user()->status == 'pending')
                        <button class="btn btn--primary" disabled title="{{ translate('Account_pending_approval') }}">
                            <i class="tio-premium-outlined mr-1"></i> {{ translate('products') }}
                        </button>
                    @else
                        <a class="btn btn--primary" href="{{ route('vendor.products.list', ['type' => 'all']) }}">
                            <i class="tio-premium-outlined mr-1"></i> {{ translate('products') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="card mb-3 remove-card-shadow">
            <div class="card-body">
                <div class="row justify-content-between align-items-center g-2 mb-3">
                    <div class="col-sm-6">
                        <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business_analytics.png')}}" alt="">
                            {{translate('order_analytics')}}
                        </h4>
                    </div>
                    <div class="col-sm-6 d-flex justify-content-sm-end">
                        <select class="custom-select w-auto" id="statistics_type" name="statistics_type">
                            <option value="overall">
                                {{translate('overall_Statistics')}}
                            </option>
                            <option value="today">
                                {{translate('todays_Statistics')}}
                            </option>
                            <option value="thisMonth">
                                {{translate('this_Months_Statistics')}}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="row g-2" id="order_stats">
                    @include('vendor-views.partials._dashboard-order-status',['orderStatus'=>$dashboardData['orderStatus']])
                </div>
            </div>
        </div>
        <div class="card mb-3 remove-card-shadow">
            <div class="card-body">
                <div class="row justify-content-between align-items-center g-2 mb-3">
                    <div class="col-sm-6">
                        <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                            <img width="20" class="mb-1" src="{{dynamicAsset(path: 'public/assets/back-end/img/admin-wallet.png')}}" alt="">
                            {{translate('vendor_Wallet')}}
                        </h4>
                    </div>
                </div>
                <div class="row g-2" id="order_stats">
                    @include('vendor-views.partials._dashboard-wallet-status',['dashboardData'=>$dashboardData])
                </div>
            </div>
        </div>

        <div class="modal fade" id="balance-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{translate('withdraw_Request')}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{route('vendor.dashboard.withdraw-request')}}" method="post" novalidate>
                        <div class="modal-body">
                            @csrf
                            <div class="">
                                <select class="form-control" id="withdraw_method" name="withdraw_method" required>
                                    @foreach($withdrawalMethods as $method)
                                        <option value="{{$method['id']}}"
                                                data-is-bank="{{ str_contains(strtolower($method['method_name']), 'bank') ? '1' : '0' }}"
                                                {{ $method['is_default'] ? 'selected':'' }}>{{$method['method_name']}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="" id="method-filed__div">

                            </div>

                            <div class="mt-3 d-none" id="withdraw-bank-select-div">
                                @if($sellerBanks->isEmpty())
                                    <div class="alert alert-warning mb-0">
                                        {{translate('You_have_no_saved_bank_accounts')}}.
                                        <a href="{{route('vendor.business-settings.bank.index')}}">{{translate('Add_a_bank')}}</a>
                                    </div>
                                @else
                                    <label class="fz-16 c1 mb-2" style="color: #5b6777 !important;">{{translate('select_bank')}}</label>
                                    <select class="form-control" id="withdraw_bank_id" name="withdraw_bank_id">
                                        @foreach($sellerBanks as $bank)
                                            <option value="{{$bank['id']}}" {{ $bank['is_active'] ? 'selected' : '' }}>
                                                {{$bank['bank_name']}} - {{$bank['holder_name']}} {{ $bank['is_active'] ? '('.translate('active').')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2 p-2 border rounded" id="withdraw-bank-preview"></div>
                                @endif
                            </div>

                            <div class="mt-1">
                                <label for="recipient-name" class="col-form-label fz-16">{{translate('amount')}}
                                    :</label>
                                <input type="number" name="amount" step=".01"
                                       value="{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $dashboardData['totalEarning']), currencyCode: getCurrencyCode(type: 'default'))}}"
                                       class="form-control" id="">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                    data-dismiss="modal">{{translate('close')}}</button>
                                <button type="submit"
                                        class="btn btn--primary">{{translate('request')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row g-2">
            @php( $shippingMethod = getWebConfig('shipping_method'))
            <div class="col-12" id="earn-statistics-div">
                @include('vendor-views.dashboard.partials.earning-statistics')
            </div>
            <div class="col-lg-{{ $shippingMethod != 'sellerwise_shipping' ? '6':'4' }}">
                <div class="card h-100 remove-card-shadow">
                    @include('vendor-views.partials._top-rated-products',['topRatedProducts'=>$dashboardData['topRatedProducts']])
                </div>
            </div>
            <div class="col-lg-{{ $shippingMethod != 'sellerwise_shipping' ? '6':'4' }}">
                <div class="card h-100 remove-card-shadow">
                    @include('vendor-views.partials._top-selling-products',['topSell'=>$dashboardData['topSell']])
                </div>
            </div>
            @if($shippingMethod=='sellerwise_shipping')
                <div class="col-lg-4">
                    <div class="card h-100 remove-card-shadow">
                        @include('vendor-views.partials._top-rated-delivery-man',['topRatedDeliveryMan'=>$dashboardData['topRatedDeliveryMan']])
                    </div>
                </div>
           @endif
        </div>
    </div>
    <span id="withdraw-method-url" data-url="{{ route('vendor.dashboard.method-list') }}"></span>
    <span id="withdraw-bank-preview-url" data-url="{{ route('vendor.business-settings.bank.preview', ['id' => ':id']) }}"></span>
    <span id="order-status-url" data-url="{{ route('vendor.dashboard.order-status', ['type' => ':type']) }}"></span>
    <span id="seller-text" data-text="{{ translate('vendor')}}"></span>
    <span id="in-house-text" data-text="{{ translate('In-house')}}"></span>
    <span id="customer-text" data-text="{{ translate('customer')}}"></span>
    <span id="store-text" data-text="{{ translate('store')}}"></span>
    <span id="product-text" data-text="{{ translate('product')}}"></span>
    <span id="order-text" data-text="{{ translate('order')}}"></span>
    <span id="brand-text" data-text="{{ translate('brand')}}"></span>
    <span id="business-text" data-text="{{ translate('business')}}"></span>
    <span id="customers-text" data-text="{{ $dashboardData['customers'] }}"></span>
    <span id="products-text" data-text="{{ $dashboardData['products'] }}"></span>
    <span id="orders-text" data-text="{{ $dashboardData['orders'] }}"></span>
    <span id="brands-text" data-text="{{ $dashboardData['brands'] }}"></span>
@endsection

@push('script_2')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/apexcharts.js')}}"></script>
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor/dashboard.js')}}"></script>
@endpush
