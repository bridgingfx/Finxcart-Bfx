@extends('layouts.admin.app')

@section('title', translate('freelancer_contracts'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0 d-flex gap-10">
                {{ translate('freelancer_contracts') }}
                <span class="badge badge-soft-dark radius-50 fz-14">{{ $contracts->total() }}</span>
            </h2>
        </div>

        <div class="card">
            <div class="card-header">
                <form action="{{ route('admin.freelancer.contracts.index') }}" method="get" class="d-flex flex-wrap gap-2" novalidate>
                    <input type="text" class="form-control" name="searchValue" value="{{ request('searchValue') }}"
                           placeholder="{{ translate('search_by_scope') }}" style="max-width: 260px;">
                    <select name="status" class="form-control" style="max-width: 200px;" onchange="this.form.submit()">
                        <option value="">{{ translate('all_status') }}</option>
                        @foreach(['active', 'submitted', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <select name="cancellation_status" class="form-control" style="max-width: 220px;" onchange="this.form.submit()">
                        <option value="">{{ translate('all_cancellation_requests') }}</option>
                        <option value="requested" {{ request('cancellation_status') === 'requested' ? 'selected' : '' }}>{{ translate('pending_review') }}</option>
                        <option value="approved" {{ request('cancellation_status') === 'approved' ? 'selected' : '' }}>{{ translate('approved') }}</option>
                        <option value="denied" {{ request('cancellation_status') === 'denied' ? 'selected' : '' }}>{{ translate('denied') }}</option>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fi fi-rr-search"></i></button>
                </form>
            </div>
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-align-middle card-table">
                    <thead class="thead-light thead-50 text-capitalize table-nowrap">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('client') }}</th>
                        <th>{{ translate('freelancer') }}</th>
                        <th>{{ translate('service') }}</th>
                        <th>{{ translate('amount') }}</th>
                        <th>{{ translate('status') }}</th>
                        <th>{{ translate('delivery_status') }}</th>
                        <th>{{ translate('created_at') }}</th>
                        <th class="text-center">{{ translate('action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($contracts as $key => $contract)
                        <tr>
                            <td>{{ $contracts->firstItem() + $key }}</td>
                            <td>{{ $contract->customer?->f_name }} {{ $contract->customer?->l_name }}</td>
                            <td>{{ $contract->freelancer?->shop?->name ?? trim($contract->freelancer?->f_name . ' ' . $contract->freelancer?->l_name) }}</td>
                            <td>{{ $contract->service?->title }}</td>
                            <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $contract->total_amount), currencyCode: getCurrencyCode()) }}</td>
                            <td>
                                <span class="badge {{ match($contract->status) {
                                    'active' => 'badge-soft--primary',
                                    'submitted' => 'badge-soft-warning',
                                    'completed' => 'badge-soft-success',
                                    'cancelled' => 'badge-soft-danger',
                                    default => 'badge-soft-secondary',
                                } }} text-capitalize">{{ str_replace('_', ' ', $contract->status) }}</span>
                                @if($contract->cancellation_status === 'requested')
                                    <span class="badge badge-soft-warning">{{ translate('cancellation_pending') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ match($contract->delivery_status?->value) {
                                    'in_progress' => 'badge-soft--primary',
                                    'submitted_for_review' => 'badge-soft-warning',
                                    'delivered' => 'badge-soft-success',
                                    default => 'badge-soft-secondary',
                                } }} text-capitalize">{{ $contract->delivery_status?->value ? str_replace('_', ' ', $contract->delivery_status->value) : '-' }}</span>
                            </td>
                            <td>{{ $contract->created_at->format('d M, Y') }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.freelancer.contracts.view', $contract->id) }}"
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
                {{ $contracts->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
