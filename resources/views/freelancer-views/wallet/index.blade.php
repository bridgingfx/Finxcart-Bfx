@extends('layouts.freelancer.app')

@section('title', translate('wallet'))

@push('css_or_js')
    <style>
        .wallet-stat-row { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 20px; }
        .wallet-stat-card { background: #fff; border: 1px solid #e8edf3; border-radius: 10px; padding: 18px 20px; }
        .wallet-stat-label { font-size: 12px; color: #9ca3af; text-transform: uppercase; letter-spacing: .03em; font-weight: 700; }
        .wallet-stat-value { font-weight: 800; font-size: 22px; color: #1f2937; margin-top: 6px; }
        .wallet-stat-value.earning { color: #16794f; }
        .wallet-stat-card.is-primary { background: linear-gradient(135deg, #17395e 0%, #1f4a78 100%); border: none; }
        .wallet-stat-card.is-primary .wallet-stat-label { color: rgba(255,255,255,.7); }
        .wallet-stat-card.is-primary .wallet-stat-value { color: #fff; }

        .wallet-table-card { background: #fff; border: 1px solid #e8edf3; border-radius: 10px; overflow: hidden; }
        .wallet-table { margin-bottom: 0; }
        .wallet-table thead th {
            background: #f6f8fb; color: #6b7280; font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .03em; border-bottom: 1px solid #e8edf3; padding: 14px 16px; white-space: nowrap;
        }
        .wallet-table tbody td { padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid #f1f3f6; font-size: 13px; }
        .wallet-table tbody tr:last-child td { border-bottom: none; }
        .wallet-table tbody tr:hover { background: #f9fafc; }
        .wallet-ref-pill {
            display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700;
            text-transform: capitalize; background: #eef2ff; color: #3730a3; white-space: nowrap;
        }
        .wallet-amount-positive { color: #16794f; font-weight: 800; }
        .wallet-amount-muted { color: #9ca3af; }

        .wallet-empty {
            background: #fff; border: 1px solid #e8edf3; border-radius: 10px; padding: 48px; text-align: center; color: #6b7280;
        }
        .wallet-empty i { font-size: 34px; color: #d8dee7; display: block; margin-bottom: 10px; }

        @media (max-width: 991.98px) {
            .wallet-stat-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 767.98px) {
            .wallet-table thead { display: none; }
            .wallet-table, .wallet-table tbody, .wallet-table tr, .wallet-table td { display: block; width: 100%; }
            .wallet-table tr { border-bottom: 8px solid #f6f8fb; padding: 10px 0; }
            .wallet-table td { border-bottom: none !important; padding: 6px 16px; }
            .wallet-table td::before {
                content: attr(data-label); display: block; font-size: 11px; font-weight: 700; color: #9ca3af;
                text-transform: uppercase; letter-spacing: .03em; margin-bottom: 3px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0">{{ translate('wallet') }}</h2>
            <p class="text-muted mb-0">{{ translate('track_your_earnings_from_hired_contracts') }}</p>
        </div>

        <div class="wallet-stat-row">
            <div class="wallet-stat-card is-primary">
                <div class="wallet-stat-label">{{ translate('total_earning') }}</div>
                <div class="wallet-stat-value">{{ webCurrencyConverter($wallet->total_earning) }}</div>
            </div>
            <div class="wallet-stat-card">
                <div class="wallet-stat-label">{{ translate('pending_withdraw') }}</div>
                <div class="wallet-stat-value">{{ webCurrencyConverter($wallet->pending_withdraw) }}</div>
            </div>
            <div class="wallet-stat-card">
                <div class="wallet-stat-label">{{ translate('withdrawn') }}</div>
                <div class="wallet-stat-value">{{ webCurrencyConverter($wallet->withdrawn) }}</div>
            </div>
            <div class="wallet-stat-card">
                <div class="wallet-stat-label">{{ translate('commission_given') }}</div>
                <div class="wallet-stat-value">{{ webCurrencyConverter($wallet->commission_given) }}</div>
            </div>
        </div>

        @if($transactions->isEmpty())
            <div class="wallet-empty">
                <i class="tio-wallet-outlined"></i>
                <p class="mb-0">{{ translate('no_earnings_yet') }}</p>
            </div>
        @else
            <div class="wallet-table-card">
                <div class="table-responsive">
                    <table class="table wallet-table">
                        <thead>
                            <tr>
                                <th>{{ translate('date') }}</th>
                                <th>{{ translate('source') }}</th>
                                <th>{{ translate('gross_amount') }}</th>
                                <th>{{ translate('platform_fee') }}</th>
                                <th>{{ translate('net_payout') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                <tr>
                                    <td data-label="{{ translate('date') }}">{{ $transaction->created_at->format('d M, Y h:i A') }}</td>
                                    <td data-label="{{ translate('source') }}">
                                        <span class="wallet-ref-pill">{{ str_replace('_', ' ', $transaction->reference_type) }}</span>
                                    </td>
                                    <td data-label="{{ translate('gross_amount') }}">{{ webCurrencyConverter($transaction->gross_amount) }}</td>
                                    <td data-label="{{ translate('platform_fee') }}">
                                        <span class="wallet-amount-muted">- {{ webCurrencyConverter($transaction->company_share_amount) }}</span>
                                    </td>
                                    <td data-label="{{ translate('net_payout') }}">
                                        <span class="wallet-amount-positive">+ {{ webCurrencyConverter($transaction->net_vendor_payout) }}</span>
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
