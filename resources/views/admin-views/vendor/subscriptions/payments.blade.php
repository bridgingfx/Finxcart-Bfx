@extends('layouts.admin.app')
@section('title', translate('Payment_Logs'))

@section('content')
<div class="content container-fluid">
    <div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h2 class="h1 mb-1">{{ translate('Payment_Logs') }}</h2>
            <p class="text-body-light mb-0">{{ trim($seller->f_name . ' ' . $seller->l_name) }} · {{ $seller->email }}</p>
        </div>
        <a href="{{ route('admin.vendor-subscriptions.index') }}" class="btn btn-outline-secondary">
            <i class="fi fi-rr-arrow-small-left"></i> {{ translate('Back_to_Subscriptions') }}
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>{{ translate('tier') }}</th>
                    <th class="text-center">{{ translate('billing_period') }}</th>
                    <th class="text-center">{{ translate('amount') }}</th>
                    <th class="text-center">{{ translate('payment_method') }}</th>
                    <th class="text-center">{{ translate('transaction_id') }}</th>
                    <th class="text-center">{{ translate('status') }}</th>
                    <th class="text-center">{{ translate('paid_at') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($payments as $key => $payment)
                    @php($paymentStatus = $payment->payment_status ?? $payment->status ?? 'pending')
                    <tr>
                        <td>{{ $payments->firstItem() + $key }}</td>
                        <td>{{ $payment->vendorTier?->tier?->name ?? '—' }}</td>
                        <td class="text-center small">{{ $payment->billing_start_date?->format('d M Y') }} - {{ $payment->billing_end_date?->format('d M Y') }}</td>
                        <td class="text-center">${{ number_format((float) $payment->amount_paid, 2) }} {{ $payment->currency }}</td>
                        <td class="text-center text-capitalize">{{ str_replace('_', ' ', $payment->payment_method ?? '—') }}</td>
                        <td class="text-center"><small>{{ $payment->transaction_id ?? '—' }}</small></td>
                        <td class="text-center"><span class="badge {{ in_array($paymentStatus, ['completed', 'paid', 'free']) ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($paymentStatus) }}</span></td>
                        <td class="text-center small">{{ $payment->paid_at?->format('d M Y, h:i A') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5">{{ translate('no_data_to_show') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end p-3">{!! $payments->links() !!}</div>
    </div>
</div>
@endsection
