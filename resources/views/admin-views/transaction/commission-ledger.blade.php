@extends('layouts.admin.app')
@section('title', translate('commission_Ledger'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/order_report.png') }}" alt="">
                {{ translate('commission_Ledger') }}
            </h2>
        </div>

        @include('admin-views.report.transaction-report-inline-menu')

        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.transaction.commission-ledger') }}" method="GET" novalidate>
                    <h3 class="mb-3">{{ translate('filter_Data') }}</h3>
                    <div class="row g-3 align-items-end">
                        <div class="col-sm-6 col-md-3">
                            <label class="mb-2">{{ translate('vendor') }}</label>
                            <div class="select-wrapper">
                                <select class="form-select" name="seller_id">
                                    <option value="all" {{ $seller_id == 'all' ? 'selected' : '' }}>{{ translate('all') }}</option>
                                    @foreach($sellers as $seller)
                                        <option value="{{ $seller->id }}" {{ (string) $seller_id === (string) $seller->id ? 'selected' : '' }}>
                                            {{ $seller?->shop?->name ?? trim($seller->f_name . ' ' . $seller->l_name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <label class="mb-2">{{ translate('select_Date') }}</label>
                            <div class="select-wrapper">
                                <select class="form-select" name="date_type" id="date_type">
                                    <option value="this_year" {{ $date_type == 'this_year' ? 'selected' : '' }}>{{ translate('this_year') }}</option>
                                    <option value="this_month" {{ $date_type == 'this_month' ? 'selected' : '' }}>{{ translate('this_month') }}</option>
                                    <option value="this_week" {{ $date_type == 'this_week' ? 'selected' : '' }}>{{ translate('this_week') }}</option>
                                    <option value="today" {{ $date_type == 'today' ? 'selected' : '' }}>{{ translate('today') }}</option>
                                    <option value="custom_date" {{ $date_type == 'custom_date' ? 'selected' : '' }}>{{ translate('custom_date') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-2" id="from_div">
                            <label class="mb-2">{{ translate('start Date') }}</label>
                            <input type="date" name="from" value="{{ $from }}" id="from_date" class="form-control __form-control">
                        </div>
                        <div class="col-sm-6 col-md-2" id="to_div">
                            <label class="mb-2">{{ translate('end Date') }}</label>
                            <input type="date" value="{{ $to }}" name="to" id="to_date" class="form-control __form-control">
                        </div>
                        <div class="col-sm-6 col-md-2">
                            <button type="submit" class="btn btn-primary w-100">{{ translate('filter') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.transaction.commission-ledger.service-charge') }}" method="POST" novalidate>
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-sm-6 col-md-4">
                            <label class="mb-2">{{ translate('service_Charge') }}</label>
                            <input type="number" step="0.01" min="0" name="commission_service_charge_amount"
                                   value="{{ $service_charge_setting }}"
                                   class="form-control"
                                   placeholder="0.00">
                        </div>
                        <div class="col-sm-6 col-md-2">
                            <button type="submit" class="btn btn-primary w-100">{{ translate('save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">{{ translate('gross_Amount') }}</h6>
                        <h3 class="mb-0">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $total_gross), currencyCode: getCurrencyCode()) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">{{ translate('platform_commission') }}</h6>
                        <h3 class="mb-0">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $total_platform_commission), currencyCode: getCurrencyCode()) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">{{ translate('service_Charge') }}</h6>
                        <h3 class="mb-0">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $total_service_charge), currencyCode: getCurrencyCode()) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">{{ translate('net_Vendor_Payout') }}</h6>
                        <h3 class="mb-0">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $total_net_payout), currencyCode: getCurrencyCode()) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-4">
                    <h4 class="mb-0">
                        {{ translate('total_Transactions') }}
                        <span class="badge badge-info text-bg-info">{{ $commission_ledgers->total() }}</span>
                    </h4>

                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <a class="btn btn-outline-primary text-nowrap" href="{{ route('admin.transaction.commission-ledger-export-excel', ['search'=>$search, 'seller_id'=>$seller_id, 'date_type'=>$date_type, 'from'=>$from, 'to'=>$to]) }}">
                            {{ translate('excel') }}
                        </a>
                        <a class="btn btn-outline-info text-nowrap" href="{{ route('admin.transaction.commission-ledger-summary-pdf', ['search'=>$search, 'seller_id'=>$seller_id, 'date_type'=>$date_type, 'from'=>$from, 'to'=>$to]) }}">
                            {{ translate('pdf') }}
                        </a>
                        <form action="{{ route('admin.transaction.commission-ledger') }}" method="GET" class="mb-0" novalidate>
                            <div class="form-group mb-0">
                                <div class="input-group">
                                    <input type="hidden" name="seller_id" value="{{ $seller_id }}">
                                    <input type="hidden" name="date_type" value="{{ $date_type }}">
                                    <input type="hidden" name="from" value="{{ $from }}">
                                    <input type="hidden" name="to" value="{{ $to }}">
                                    <input type="search" name="search" class="form-control"
                                           value="{{ $search }}"
                                           placeholder="{{ translate('search_by_Order_ID_or_Transaction_ID') }}">
                                    <div class="input-group-append search-submit">
                                        <button type="submit">
                                            <i class="fi fi-rr-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table __table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('vendor') }}</th>
                            <th>{{ translate('order_ID') }}</th>
                            <th>{{ translate('transaction_ID') }}</th>
                            <th>{{ translate('gross_Amount') }}</th>
                            <th>{{ translate('platform_commission') }}</th>
                            <th>{{ translate('service_Charge') }}</th>
                            <th>{{ translate('net_Vendor_Payout') }}</th>
                            <th>{{ translate('date') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($commission_ledgers as $key => $ledger)
                            <tr>
                                <td>{{ $commission_ledgers->firstItem() + $key }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $ledger->seller?->shop?->name ?? trim(($ledger->seller?->f_name ?? '') . ' ' . ($ledger->seller?->l_name ?? '')) }}</div>
                                    <small class="text-muted">{{ $ledger->seller?->email }}</small>
                                </td>
                                <td>
                                    @if($ledger->order_id)
                                        <a class="title-color" href="{{ route('admin.orders.details', ['id' => $ledger->order_id]) }}">{{ $ledger->order_id }}</a>
                                    @else
                                        <span class="text-muted">{{ translate('not_available') }}</span>
                                    @endif
                                </td>
                                <td>{{ $ledger->order?->orderTransaction?->transaction_id ?? $ledger->reference_id ?? translate('not_available') }}</td>
                                <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $ledger->gross_amount), currencyCode: getCurrencyCode()) }}</td>
                                <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $ledger->company_share_amount), currencyCode: getCurrencyCode()) }}</td>
                                <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $ledger->service_charge_amount), currencyCode: getCurrencyCode()) }}</td>
                                <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $ledger->net_vendor_payout), currencyCode: getCurrencyCode()) }}</td>
                                <td>{{ $ledger->created_at?->format('d F Y h:i:s a') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive mt-4">
                    <div class="px-4 d-flex justify-content-lg-end">
                        {{ $commission_ledgers->links() }}
                    </div>
                </div>
                @if(count($commission_ledgers) == 0)
                    @include('layouts.admin.partials._empty-state', ['text' => 'no_data_found'], ['image' => 'default'])
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/new/back-end/js/admin/expense-report.js') }}"></script>
@endpush
