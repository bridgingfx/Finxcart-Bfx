@extends('layouts.admin.app')

@section('title', translate('freelancer_ledger'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0 d-flex gap-10">
                {{ translate('freelancer_ledger') }}
                <span class="badge badge-soft-dark radius-50 fz-14">{{ $transactions->total() }}</span>
            </h2>
        </div>

        <div class="card">
            <div class="card-header">
                <form action="{{ route('admin.freelancer.ledger.index') }}" method="get" class="d-flex flex-wrap gap-2" novalidate>
                    <input type="text" class="form-control" name="searchValue" value="{{ request('searchValue') }}"
                           placeholder="{{ translate('search_by_customer_freelancer_or_service') }}" style="max-width: 300px;">
                    <select name="delivery_status" class="form-control" style="max-width: 220px;" onchange="this.form.submit()">
                        <option value="">{{ translate('all_delivery_statuses') }}</option>
                        @foreach(\App\Enums\Freelancer\DeliveryStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ request('delivery_status') === $status->value ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $status->value)) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fi fi-rr-search"></i></button>
                </form>
            </div>
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-align-middle card-table">
                    <thead class="thead-light thead-50 text-capitalize table-nowrap">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('date') }}</th>
                        <th>{{ translate('customer') }}</th>
                        <th>{{ translate('freelancer') }}</th>
                        <th>{{ translate('service') }}</th>
                        <th>{{ translate('gross_amount') }}</th>
                        <th>{{ translate('platform_commission') }}</th>
                        <th>{{ translate('net_payout') }}</th>
                        <th>{{ translate('delivery_status') }}</th>
                        <th class="text-center">{{ translate('action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($transactions as $key => $transaction)
                        @php($contract = $transaction->contract)
                        <tr>
                            <td>{{ $transactions->firstItem() + $key }}</td>
                            <td>{{ $transaction->created_at->format('d M, Y') }}</td>
                            <td>
                                {{ trim(($contract?->customer?->f_name ?? '') . ' ' . ($contract?->customer?->l_name ?? '')) ?: '-' }}
                                <div class="text-muted fs-12">{{ $contract?->customer?->email }}</div>
                            </td>
                            <td>
                                <a class="text-dark text-hover-primary fw-semibold" href="{{ route('admin.freelancer.accounts.view', $transaction->seller_id) }}">
                                    {{ $transaction->seller?->shop?->name ?? trim(($transaction->seller?->f_name ?? '') . ' ' . ($transaction->seller?->l_name ?? '')) }}
                                </a>
                            </td>
                            <td>{{ $contract?->service?->title ?? '-' }}</td>
                            <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction->gross_amount), currencyCode: getCurrencyCode()) }}</td>
                            <td class="text-muted">- {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction->company_share_amount), currencyCode: getCurrencyCode()) }}</td>
                            <td class="fw-bold text-success">+ {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transaction->net_vendor_payout), currencyCode: getCurrencyCode()) }}</td>
                            <td>
                                <span class="badge {{ match($contract?->delivery_status?->value) {
                                    'in_progress' => 'badge-soft--primary',
                                    'submitted_for_review' => 'badge-soft-warning',
                                    'delivered' => 'badge-soft-success',
                                    default => 'badge-soft-secondary',
                                } }} text-capitalize">{{ $contract?->delivery_status?->value ? str_replace('_', ' ', $contract->delivery_status->value) : '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if($contract)
                                    <a href="{{ route('admin.freelancer.contracts.view', $contract->id) }}"
                                       class="btn btn-sm btn-outline-info icon-btn" title="{{ translate('view') }}">
                                        <i class="fi fi-rr-eye"></i>
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">{{ translate('no_data_found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $transactions->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
