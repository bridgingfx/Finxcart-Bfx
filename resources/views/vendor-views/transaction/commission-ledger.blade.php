@extends('layouts.vendor.app')
@section('title', translate('commission_Ledger'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ asset('public/assets/back-end/img/order_report.png') }}" alt="">
                {{ translate('commission_Ledger') }}
            </h2>
        </div>

        @include('vendor-views.transaction.transaction-report-inline-menu')

        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('vendor.transaction.commission-ledger') }}" method="GET" novalidate>
                    <h4 class="mb-3">{{ translate('filter_Data') }}</h4>
                    <div class="row gy-2 align-items-center text-{{ Session::get('direction') === 'rtl' ? 'right' : 'left' }}">
                        <div class="col-sm-6 col-md-3">
                            <select class="form-control __form-control" name="date_type" id="date_type">
                                <option value="this_year" {{ $date_type == 'this_year' ? 'selected' : '' }}>{{ translate('this_year') }}</option>
                                <option value="this_month" {{ $date_type == 'this_month' ? 'selected' : '' }}>{{ translate('this_month') }}</option>
                                <option value="this_week" {{ $date_type == 'this_week' ? 'selected' : '' }}>{{ translate('this_week') }}</option>
                                <option value="today" {{ $date_type == 'today' ? 'selected' : '' }}>{{ translate('today') }}</option>
                                <option value="custom_date" {{ $date_type == 'custom_date' ? 'selected' : '' }}>{{ translate('custom_date') }}</option>
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-3" id="from_div">
                            <div class="form-floating">
                                <input type="date" name="from" value="{{ $from }}" id="from_date" class="form-control __form-control">
                                <label>{{ translate('start_Date') }}</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3" id="to_div">
                            <div class="form-floating">
                                <input type="date" value="{{ $to }}" name="to" id="to_date" class="form-control __form-control">
                                <label>{{ translate('end_Date') }}</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <button type="submit" class="btn btn--primary px-4 w-100">{{ translate('filter') }}</button>
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
            <div class="card-header border-0">
                <div class="w-100 d-flex flex-wrap gap-3 align-items-center">
                    <h4 class="mb-0 mr-auto">
                        {{ translate('total_Transactions') }}
                        <span class="badge badge-soft-dark radius-50 fs-12">{{ $commission_ledgers->total() }}</span>
                    </h4>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <a class="btn btn-outline--primary text-nowrap" href="{{ route('vendor.transaction.commission-ledger-export-excel', ['search'=>$search, 'date_type'=>$date_type, 'from'=>$from, 'to'=>$to]) }}">
                            {{ translate('excel') }}
                        </a>
                        <a class="btn btn-outline-info text-nowrap" href="{{ route('vendor.transaction.commission-ledger-summary-pdf', ['search'=>$search, 'date_type'=>$date_type, 'from'=>$from, 'to'=>$to]) }}">
                            {{ translate('pdf') }}
                        </a>
                        <form action="{{ route('vendor.transaction.commission-ledger') }}" method="GET" class="mb-0" novalidate>
                            <div class="input-group input-group-merge input-group-custom">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="tio-search"></i>
                                    </div>
                                </div>
                                <input type="hidden" name="date_type" value="{{ $date_type }}">
                                <input type="hidden" name="from" value="{{ $from }}">
                                <input type="hidden" name="to" value="{{ $to }}">
                                <input type="search" name="search" class="form-control"
                                       placeholder="{{ translate('search_by_Order_ID_or_Transaction_ID') }}"
                                       value="{{ $search }}">
                                <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table __table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('SL') }}</th>
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
                                    @if($ledger->order_id)
                                        <a class="title-color" href="{{ route('vendor.orders.details', ['id' => $ledger->order_id]) }}">{{ $ledger->order_id }}</a>
                                    @else
                                        <span class="text-muted">{{ translate('not_available') }}</span>
                                    @endif
                                </td>
                                <td>{{ $ledger->order?->orderTransaction?->transaction_id ?? $ledger->reference_id ?? translate('not_available') }}</td>
                                <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $ledger->gross_amount), currencyCode: getCurrencyCode()) }}</td>
                                <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $ledger->company_share_amount), currencyCode: getCurrencyCode()) }}</td>
                                <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $ledger->service_charge_amount), currencyCode: getCurrencyCode()) }}</td>
                                <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $ledger->net_vendor_payout), currencyCode: getCurrencyCode()) }}</td>
                                <td>{{ $ledger->created_at?->format('d F Y, h:i:s a') }}</td>
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
                    @include('layouts.vendor.partials._empty-state', ['text' => 'no_data_found'], ['image' => 'default'])
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/expense-report.js') }}"></script>
@endpush
