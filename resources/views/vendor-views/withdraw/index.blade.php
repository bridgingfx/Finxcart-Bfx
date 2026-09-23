@extends('layouts.vendor.app')

@section('title', translate('withdraw_Request'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .vendor-withdraw-page .card,
        .vendor-withdraw-page .card-table {
            width: 100%;
        }

        .vendor-withdraw-page .table-responsive {
            max-width: 100%;
        }

        .vendor-withdraw-page .table th,
        .vendor-withdraw-page .table td {
            vertical-align: middle;
        }

        .vendor-withdraw-page .table td {
            white-space: normal;
            word-break: break-word;
        }

        .vendor-withdraw-page .table th:nth-child(3),
        .vendor-withdraw-page .table td:nth-child(3) {
            max-width: 360px;
        }

        .vendor-withdraw-page .transaction-cell {
            min-width: 260px;
            max-width: 360px;
        }

        .vendor-withdraw-page .status-cell {
            min-width: 130px;
        }

        .vendor-withdraw-page .billing-cell {
            min-width: 180px;
        }

        @media (max-width: 767.98px) {
            .vendor-withdraw-page .withdraw-table-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .vendor-withdraw-page .withdraw-table-actions .custom-select,
            .vendor-withdraw-page .withdraw-table-actions .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid vendor-withdraw-page">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/withdraw-icon.png')}}" alt="">
                {{translate('withdraw')}}
            </h2>
        </div>

        @php
            $kycRequiredForPayout = (bool) getWebConfig('kyc_required_for_payout') && (getWebConfig('kyc_method') ?? 'manual') !== 'disabled';
            $seller = auth('seller')->user();
            $kycApproved = ($seller?->kyc_status ?? null) === 'approved';
            $hasWithdrawalMethods = $withdrawalMethods->count() > 0;
            $minWithdraw = (float) (getWebConfig('minimum_withdrawal_amount') ?? 20);
            $availableBalance = (float) usdToDefaultCurrency(amount: $wallet->total_earning ?? 0);
            $canWithdraw = $availableBalance >= $minWithdraw;
        @endphp
        @if($kycRequiredForPayout && !$kycApproved)
            <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <span>{{ translate('Complete_your_KYC_verification_before_your_first_payout_can_be_processed') }}</span>
                <a href="{{ route('vendor.payout-kyc.index') }}" class="btn btn-sm btn-danger font-semibold">
                    <i class="tio-warning-outlined me-1"></i>{{ translate('Complete_KYC') }}
                </a>
            </div>
        @endif

        {{-- Balance Summary --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fs-12">{{translate('available_balance')}}</p>
                            <h4 class="mb-0 text-success">
                                {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $wallet->total_earning ?? 0), currencyCode: getCurrencyCode(type: 'default'))}}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fs-12">{{translate('pending_withdraw')}}</p>
                            <h4 class="mb-0 text-warning">
                                {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $wallet->pending_withdraw ?? 0), currencyCode: getCurrencyCode(type: 'default'))}}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 fs-12">{{translate('total_withdrawn')}}</p>
                            <h4 class="mb-0">
                                {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $wallet->withdrawn ?? 0), currencyCode: getCurrencyCode(type: 'default'))}}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <button type="button"
                                class="btn btn--primary w-100 text-nowrap"
                                data-toggle="modal" data-target="#withdraw-request-modal">
                            <i class="tio-money-vs mr-1"></i>
                            {{ translate('request_payout') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">

                    <div class="p-3">
                        <div class="row gy-1 align-items-center justify-content-between">
                            <div class="col-auto">
                                <h5 class="text-capitalize">
                                    {{ translate('withdraw_request_table')}}
                                    <span class="badge badge-soft-dark radius-50 fs-12 ml-1" id="withdraw-requests-count">{{ $withdrawRequests->total() }}</span>
                                </h5>
                            </div>
                            <div class="d-flex col-auto gap-3 withdraw-table-actions flex-wrap">
                                <div class="col-auto">
                                    <select name="status" class="custom-select max-w-200 status-filter" >
                                        <option value="all">{{translate('all')}}</option>
                                        <option value="approved">{{translate('approved')}}</option>
                                        <option value="denied">{{translate('denied')}}</option>
                                        <option value="pending">{{translate('pending')}}</option>
                                    </select>
                                </div>
                                <div class="dropdown">
                                    <a type="button" class="btn btn-outline--primary text-nowrap" href="{{route('vendor.business-settings.withdraw.export-withdraw-list',['searchValue'=> request('searchValue')??''])}}">
                                        <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                                        <span class="ps-2">{{ translate('export') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="status-wise-view">
                        @include('vendor-views.withdraw._table')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <span id="get-status-filter-route" data-action="{{route('vendor.business-settings.withdraw.index')}}"></span>

    {{-- Tier Payments from Wallet --}}
    @php
        $tierWalletPayments = \App\Models\VendorTierPayment::with('vendorTier.tier')
            ->whereHas('vendorTier', fn($q) => $q->where('seller_id', auth('seller')->id()))
            ->where('payment_method', 'seller_wallet')
            ->orderByDesc('paid_at')
            ->get();
    @endphp
    @if($tierWalletPayments->count() > 0)
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="tio-wallet-outlined text-primary"></i>
                    <h5 class="mb-0 text-capitalize">{{ translate('Tier_Payments_from_Wallet') }}</h5>
                    <span class="badge badge-soft-primary ml-1">{{ $tierWalletPayments->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('Date') }}</th>
                                <th>{{ translate('Plan') }}</th>
                                <th>{{ translate('Transaction_ID') }}</th>
                                <th>{{ translate('Amount_Paid') }}</th>
                                <th>{{ translate('Billing_Period') }}</th>
                                <th>{{ translate('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tierWalletPayments as $tp)
                            <tr>
                                <td>{{ $tp->paid_at ? date('d M Y', strtotime($tp->paid_at)) : '-' }}</td>
                                <td>{{ $tp->vendorTier?->tier?->name ?? '-' }}</td>
                                <td><small class="text-muted">{{ $tp->transaction_id }}</small></td>
                                <td><strong class="text-danger">- ${{ number_format($tp->amount_paid, 2) }}</strong></td>
                                <td class="small text-muted">
                                    {{ date('d M Y', strtotime($tp->billing_start_date)) }} – {{ date('d M Y', strtotime($tp->billing_end_date)) }}
                                </td>
                                <td><span class="badge badge-soft-success">{{ translate('Paid_from_Wallet') }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Request Payout Modal --}}
    <div class="modal fade" id="withdraw-request-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('withdraw_Request') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('vendor.dashboard.withdraw-request') }}" method="post" enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="modal-body">
                        @if($hasWithdrawalMethods)
                            <div class="form-group">
                                <label class="title-color">{{ translate('withdrawal_method') }}</label>
                                <select class="form-control" id="withdraw_method_select"
                                        name="withdraw_method" required>
                                    @foreach($withdrawalMethods as $method)
                                        <option value="{{ $method['id'] }}"
                                            {{ $method['is_default'] ? 'selected' : '' }}>
                                            {{ $method['method_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="withdraw-method-fields"></div>
                            <div class="form-group mt-2">
                                <label class="title-color">
                                    {{ translate('amount') }}
                                    <small class="text-muted">
                                        ({{ translate('available') }}:
                                        <strong>{{ setCurrencySymbol(amount: $availableBalance, currencyCode: getCurrencyCode(type: 'default')) }}</strong>)
                                    </small>
                                </label>
                                @if($canWithdraw)
                                    <input type="number" name="amount" step="0.01"
                                           min="{{ $minWithdraw }}"
                                           max="{{ $availableBalance }}"
                                           class="form-control"
                                           placeholder="{{ translate('enter_amount') }}"
                                           required>
                                    <small class="text-muted">
                                        {{ translate('minimum') }}: {{ setCurrencySymbol(amount: $minWithdraw) }}
                                        &nbsp;|&nbsp;
                                        {{ translate('maximum') }}: {{ setCurrencySymbol(amount: $availableBalance, currencyCode: getCurrencyCode(type: 'default')) }}
                                    </small>
                                @else
                                    <div class="alert alert-warning mb-0 mt-1 py-2">
                                        <i class="tio-warning-outlined me-1"></i>
                                        {{ translate('insufficient_balance_for_withdrawal') }}.
                                        {{ translate('minimum_required') }}: <strong>{{ setCurrencySymbol(amount: $minWithdraw) }}</strong>,
                                        {{ translate('your_balance') }}: <strong>{{ setCurrencySymbol(amount: $availableBalance, currencyCode: getCurrencyCode(type: 'default')) }}</strong>.
                                    </div>
                                    <input type="hidden" name="amount" value="0">
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                {{ translate('No_withdrawal_method_available_Please_contact_admin') }}
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ translate('close') }}</button>
                        @if($hasWithdrawalMethods)
                            <button type="submit" class="btn btn--primary" {{ !$canWithdraw ? 'disabled' : '' }}>
                                {{ translate('request') }}
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@push('script')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor/withdraw.js')}}"></script>
    <script>
        $(document).ready(function () {
            function loadMethodFields(methodId) {
                if (!methodId) return;
                $.get('{{ route('vendor.dashboard.method-list') }}', { method_id: methodId }, function (data) {
                    let fields = data.content && data.content.method_fields ? data.content.method_fields : [];
                    let container = $('#withdraw-method-fields');
                    container.empty();

                    fields.forEach(function (field) {
                        let label = String(field.input_name || '').replaceAll('_', ' ');
                        let input;
                        if (field.input_type === 'file') {
                            input = $('<input>', {
                                type: 'file',
                                class: 'form-control',
                                name: field.input_name,
                                accept: 'image/*,.pdf'
                            });
                        } else {
                            input = $('<input>', {
                                type: field.input_type || 'text',
                                class: 'form-control',
                                name: field.input_name,
                                placeholder: field.placeholder || ''
                            });
                        }

                        if (Number(field.is_required) === 1) {
                            input.attr('required', true);
                        }

                        container.append(
                            $('<div>', { class: 'form-group mt-3' })
                                .append($('<label>', { class: 'title-color text-capitalize', text: label }))
                                .append(input)
                        );
                    });
                });
            }

            $('#withdraw_method_select').on('change', function () {
                loadMethodFields($(this).val());
            }).trigger('change');
        });
    </script>
@endpush
