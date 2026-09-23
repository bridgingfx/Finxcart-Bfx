@extends('layouts.admin.app')
@section('title', translate('Vendor_Subscriptions'))

@push('css_or_js')
<style>
    .subscription-stat {
        border: 1px solid rgba(7, 59, 116, .1);
        border-radius: 14px;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .subscription-stat:hover, .subscription-stat.active {
        border-color: #073b74;
        box-shadow: 0 10px 24px rgba(7, 59, 116, .12);
        transform: translateY(-2px);
    }
    .subscription-table tbody tr { transition: background-color .15s ease; }
    .subscription-table tbody tr:hover { background: rgba(7, 59, 116, .035); }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h2 class="h1 mb-1">{{ translate('Vendor_Subscriptions') }}</h2>
            <p class="text-body-light mb-0">{{ translate('Monitor_trials,_active_plans,_renewals_and_payment_history.') }}</p>
        </div>
        <a href="{{ route('admin.tiers.index') }}" class="btn btn-outline-primary">
            <i class="fi fi-rr-settings-sliders"></i> {{ translate('Manage_Tiers') }}
        </a>
    </div>

    <div class="row g-3 mb-4">
        @foreach(['all' => 'All', 'active' => 'Active', 'trial' => 'Trial', 'cancelled' => 'Cancelled', 'ended' => 'Ended'] as $key => $label)
            <div class="col-6 col-md">
                <a href="{{ route('admin.vendor-subscriptions.index', ['status' => $key]) }}"
                   class="subscription-stat {{ $status === $key ? 'active' : '' }} card h-100 text-dark">
                    <div class="card-body py-3">
                        <div class="text-body-light fs-12 mb-1">{{ translate($label) }}</div>
                        <div class="h3 mb-0">{{ $counts[$key] ?? 0 }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header border-0 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <h3 class="mb-0">{{ translate('Subscriptions') }}</h3>
            <form method="GET" class="d-flex align-items-center gap-2" novalidate>
                <input type="hidden" name="status" value="{{ $status }}">
                <select name="tier_id" class="form-select" onchange="this.form.submit()">
                    <option value="">{{ translate('All_Tiers') }}</option>
                    @foreach($tiers as $tier)
                        <option value="{{ $tier->id }}" @selected((string) request('tier_id') === (string) $tier->id)>
                            {{ $tier->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 subscription-table">
                <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>{{ translate('vendor') }}</th>
                    <th>{{ translate('type') }}</th>
                    <th>{{ translate('tier') }}</th>
                    <th class="text-center">{{ translate('status') }}</th>
                    <th class="text-center">{{ translate('end_date') }}</th>
                    <th class="text-center">{{ translate('fee') }}</th>
                    <th class="text-center">{{ translate('usage') }}</th>
                    <th class="text-center">{{ translate('action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($subscriptions as $key => $subscription)
                    @php
                        $sellerType = $subscription->seller?->seller_type ?? 'unknown';
                        $effectiveEnd = $subscription->status === 'trial' ? $subscription->trial_end_date : $subscription->end_date;
                        $effectiveStatus = in_array($subscription->status, ['active', 'trial']) && $effectiveEnd?->isPast()
                            ? 'ended'
                            : $subscription->status;
                    @endphp
                    <tr>
                        <td>{{ $subscriptions->firstItem() + $key }}</td>
                        <td>
                            <a class="fw-semibold text-dark" href="{{ route('admin.vendors.view', $subscription->seller_id) }}">
                                {{ trim(($subscription->seller?->f_name ?? '') . ' ' . ($subscription->seller?->l_name ?? '')) ?: '—' }}
                            </a>
                            <div class="small text-body-light">{{ $subscription->seller?->email ?? '—' }}</div>
                            <div class="small text-body-light">{{ translate('Subscription_ID') }}: #{{ $subscription->id }}</div>
                        </td>
                        <td><span class="badge text-capitalize {{ $sellerType === 'freelancer' ? 'bg-success' : ($sellerType === 'company' ? 'bg-primary' : 'bg-warning text-dark') }}">{{ $sellerType }}</span></td>
                        <td>{{ $subscription->tier?->name ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $effectiveStatus === 'active' ? 'bg-success' : ($effectiveStatus === 'trial' ? 'bg-info' : ($effectiveStatus === 'cancelled' ? 'bg-danger' : 'bg-secondary')) }}">
                                {{ ucfirst($effectiveStatus) }}
                            </span>
                        </td>
                        <td class="text-center small">
                            {{ ($subscription->status === 'trial' ? $subscription->trial_end_date : $subscription->end_date)?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="text-center">${{ number_format((float) $subscription->monthly_fee_usd, 2) }}</td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark">{{ $subscription->products_count }} {{ translate('products') }}</span>
                            <span class="badge bg-light text-dark">{{ $subscription->payments_count }} {{ translate('payments') }}</span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.vendor-subscriptions.payments', $subscription->seller_id) }}" class="btn btn-outline-info icon-btn" title="{{ translate('Payment_Logs') }}">
                                    <i class="fi fi-rr-receipt"></i>
                                </a>
                                @if(in_array($subscription->status, ['active', 'trial']))
                                    <form action="{{ route('admin.vendor-subscriptions.cancel', $subscription->id) }}" method="POST" onsubmit="return confirm('{{ translate('Cancel_this_subscription?') }}')" novalidate>
                                        @csrf @method('PATCH')
                                        <button class="btn btn-outline-danger icon-btn" title="{{ translate('cancel') }}"><i class="fi fi-rr-cross-circle"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center py-5">{{ translate('no_data_to_show') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end p-3">{!! $subscriptions->links() !!}</div>
    </div>
</div>
@endsection
