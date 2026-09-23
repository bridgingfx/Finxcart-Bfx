@extends('layouts.freelancer.app')

@section('title', translate('ledger'))

@push('css_or_js')
    <style>
        .ledger-table-card { background: #fff; border: 1px solid #e8edf3; border-radius: 10px; overflow: hidden; }
        .ledger-table { margin-bottom: 0; }
        .ledger-table thead th {
            background: #f6f8fb; color: #6b7280; font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .03em; border-bottom: 1px solid #e8edf3; padding: 14px 16px; white-space: nowrap;
        }
        .ledger-table tbody td { padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid #f1f3f6; font-size: 13px; }
        .ledger-table tbody tr:last-child td { border-bottom: none; }
        .ledger-table tbody tr:hover { background: #f9fafc; }

        .ledger-row-service { font-weight: 600; color: #17395e; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ledger-amount-muted { color: #9ca3af; }
        .ledger-amount-positive { color: #16794f; font-weight: 800; }

        .ledger-delivery-pill {
            display: inline-block; padding: 5px 14px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; text-transform: capitalize;
        }
        .ledger-delivery-pending { background: #f1f1f1; color: #555; }
        .ledger-delivery-in_progress { background: #e7f3ff; color: #1a73e8; }
        .ledger-delivery-submitted_for_review { background: #fff4e5; color: #b76e00; }
        .ledger-delivery-delivered { background: #e6f7ee; color: #16794f; }

        .ledger-table .btn-outline--primary { border-radius: 999px; font-weight: 700; }

        .ledger-empty {
            background: #fff; border: 1px solid #e8edf3; border-radius: 10px; padding: 48px; text-align: center; color: #6b7280;
        }
        .ledger-empty i { font-size: 34px; color: #d8dee7; display: block; margin-bottom: 10px; }

        @media (max-width: 767.98px) {
            .ledger-table thead { display: none; }
            .ledger-table, .ledger-table tbody, .ledger-table tr, .ledger-table td { display: block; width: 100%; }
            .ledger-table tr { border-bottom: 8px solid #f6f8fb; padding: 10px 0; }
            .ledger-table td { border-bottom: none !important; padding: 6px 16px; }
            .ledger-table td::before {
                content: attr(data-label); display: block; font-size: 11px; font-weight: 700; color: #9ca3af;
                text-transform: uppercase; letter-spacing: .03em; margin-bottom: 3px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0">{{ translate('ledger') }}</h2>
            <p class="text-muted mb-0">{{ translate('every_service_you_sold_with_delivery_status_and_payout_breakdown') }}</p>
        </div>

        @if($transactions->isEmpty())
            <div class="ledger-empty">
                <i class="tio-receipt-outlined"></i>
                <p class="mb-0">{{ translate('no_transactions_yet') }}</p>
            </div>
        @else
            <div class="ledger-table-card">
                <div class="table-responsive">
                    <table class="table ledger-table">
                        <thead>
                            <tr>
                                <th>{{ translate('date') }}</th>
                                <th>{{ translate('customer') }}</th>
                                <th>{{ translate('service') }}</th>
                                <th>{{ translate('gross_amount') }}</th>
                                <th>{{ translate('platform_fee') }}</th>
                                <th>{{ translate('net_payout') }}</th>
                                <th>{{ translate('delivery_status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                @php($contract = $transaction->contract)
                                @php($customerName = trim(($contract?->customer?->f_name ?? '') . ' ' . ($contract?->customer?->l_name ?? '')) ?: translate('customer'))
                                <tr>
                                    <td data-label="{{ translate('date') }}">{{ $transaction->created_at->format('d M, Y h:i A') }}</td>
                                    <td data-label="{{ translate('customer') }}">{{ $customerName }}</td>
                                    <td data-label="{{ translate('service') }}">
                                        <span class="ledger-row-service" title="{{ $contract?->service?->title }}">{{ $contract?->service?->title ?? translate('not_available') }}</span>
                                    </td>
                                    <td data-label="{{ translate('gross_amount') }}">{{ webCurrencyConverter($transaction->gross_amount) }}</td>
                                    <td data-label="{{ translate('platform_fee') }}">
                                        <span class="ledger-amount-muted">- {{ webCurrencyConverter($transaction->company_share_amount) }}</span>
                                    </td>
                                    <td data-label="{{ translate('net_payout') }}">
                                        <span class="ledger-amount-positive">+ {{ webCurrencyConverter($transaction->net_vendor_payout) }}</span>
                                    </td>
                                    <td data-label="{{ translate('delivery_status') }}">
                                        @if($contract)
                                            <span class="ledger-delivery-pill ledger-delivery-{{ $contract->delivery_status->value }}">{{ str_replace('_', ' ', $contract->delivery_status->value) }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td data-label="{{ translate('action') }}">
                                        @if($contract)
                                            <a href="{{ route('freelancer.contracts.show', $contract->id) }}" class="btn btn-sm btn-outline--primary">
                                                {{ translate('view') }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
@endsection
