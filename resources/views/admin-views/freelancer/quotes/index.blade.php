@extends('layouts.admin.app')

@section('title', translate('freelancer_quote_requests'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0 d-flex gap-10">
                {{ translate('freelancer_quote_requests') }}
                <span class="badge badge-soft-dark radius-50 fz-14">{{ $quotes->total() }}</span>
            </h2>
        </div>

        <div class="card">
            <div class="card-header">
                <form action="{{ route('admin.freelancer.quotes.index') }}" method="get" class="d-flex flex-wrap gap-2" novalidate>
                    <input type="text" class="form-control" name="searchValue" value="{{ request('searchValue') }}"
                           placeholder="{{ translate('search_by_customer_freelancer_or_service') }}" style="max-width: 280px;">
                    <select name="status" class="form-control" style="max-width: 200px;" onchange="this.form.submit()">
                        <option value="">{{ translate('all_status') }}</option>
                        @foreach(['pending', 'quoted', 'accepted', 'declined'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fi fi-rr-search"></i></button>
                    <a href="{{ route('admin.freelancer.quotes.index') }}" class="btn btn-outline-secondary">{{ translate('reset') }}</a>
                </form>
            </div>
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-align-middle card-table">
                    <thead class="thead-light thead-50 text-capitalize table-nowrap">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('customer') }}</th>
                        <th>{{ translate('freelancer') }}</th>
                        <th>{{ translate('service') }}</th>
                        <th>{{ translate('budget') }}</th>
                        <th>{{ translate('quoted_price') }}</th>
                        <th>{{ translate('status') }}</th>
                        <th>{{ translate('created_at') }}</th>
                        <th class="text-center">{{ translate('action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($quotes as $key => $quote)
                        <tr>
                            <td>{{ $quotes->firstItem() + $key }}</td>
                            <td>{{ $quote->customer?->f_name }} {{ $quote->customer?->l_name }}</td>
                            <td>{{ $quote->seller?->shop?->name ?? trim($quote->seller?->f_name . ' ' . $quote->seller?->l_name) }}</td>
                            <td>{{ $quote->service?->title ?? '-' }}</td>
                            <td>{{ $quote->budget ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $quote->budget), currencyCode: getCurrencyCode()) : '-' }}</td>
                            <td>{{ $quote->quoted_price ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $quote->quoted_price), currencyCode: getCurrencyCode()) : '-' }}</td>
                            <td>
                                <span class="badge {{ match($quote->status?->value) {
                                    'pending' => 'badge-soft-warning',
                                    'quoted' => 'badge-soft--primary',
                                    'accepted' => 'badge-soft-success',
                                    'declined' => 'badge-soft-danger',
                                    default => 'badge-soft-secondary',
                                } }} text-capitalize">{{ $quote->status?->value ?? '-' }}</span>
                            </td>
                            <td>{{ $quote->created_at->format('d M, Y') }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.freelancer.quotes.view', $quote->id) }}"
                                   class="btn btn-sm btn-outline-info icon-btn" title="{{ translate('view') }}">
                                    <i class="fi fi-rr-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">{{ translate('no_data_found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $quotes->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
