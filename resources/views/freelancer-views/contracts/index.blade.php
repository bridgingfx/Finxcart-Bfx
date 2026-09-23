@extends('layouts.freelancer.app')

@section('title', translate('contracts'))

@push('css_or_js')
    <style>
        .contract-table-card { background: #fff; border: 1px solid #e8edf3; border-radius: 10px; overflow: hidden; }
        .contract-table { margin-bottom: 0; }
        .contract-table thead th {
            background: #f6f8fb; color: #6b7280; font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .03em; border-bottom: 1px solid #e8edf3; padding: 14px 16px; white-space: nowrap;
        }
        .contract-table tbody td { padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid #f1f3f6; font-size: 13px; }
        .contract-table tbody tr:last-child td { border-bottom: none; }
        .contract-table tbody tr:hover { background: #f9fafc; }

        .contract-row-customer { display: flex; align-items: center; gap: 10px; }
        .contract-row-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; background: #eef2ff; flex-shrink: 0; }
        .contract-row-customer-name { font-weight: 700; color: #1f2937; }

        .contract-row-service { font-weight: 600; color: #17395e; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .contract-row-amount { font-weight: 800; color: #16794f; }

        .contract-status-pill {
            display: inline-block; padding: 5px 14px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; text-transform: capitalize;
        }
        .contract-status-active { background: #e7f3ff; color: #1a73e8; }
        .contract-status-submitted { background: #fff4e5; color: #b76e00; }
        .contract-status-completed { background: #e6f7ee; color: #16794f; }
        .contract-status-cancelled { background: #fde8e8; color: #c0392b; }
        .contract-status-pending_funding { background: #f1f1f1; color: #555; }

        .contract-delivery-pill {
            display: inline-block; padding: 5px 14px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; text-transform: capitalize;
        }
        .contract-delivery-pending { background: #f1f1f1; color: #555; }
        .contract-delivery-in_progress { background: #e7f3ff; color: #1a73e8; }
        .contract-delivery-submitted_for_review { background: #fff4e5; color: #b76e00; }
        .contract-delivery-delivered { background: #e6f7ee; color: #16794f; }

        .contract-table .btn-outline--primary { border-radius: 999px; font-weight: 700; }

        .contract-empty {
            background: #fff; border: 1px solid #e8edf3; border-radius: 10px; padding: 48px; text-align: center; color: #6b7280;
        }
        .contract-empty i { font-size: 34px; color: #d8dee7; display: block; margin-bottom: 10px; }

        @media (max-width: 767.98px) {
            .contract-table thead { display: none; }
            .contract-table, .contract-table tbody, .contract-table tr, .contract-table td { display: block; width: 100%; }
            .contract-table tr { border-bottom: 8px solid #f6f8fb; padding: 10px 0; }
            .contract-table td { border-bottom: none !important; padding: 6px 16px; }
            .contract-table td::before {
                content: attr(data-label); display: block; font-size: 11px; font-weight: 700; color: #9ca3af;
                text-transform: uppercase; letter-spacing: .03em; margin-bottom: 3px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex align-items-center gap-10">
            <h2 class="h1 mb-0">{{ translate('contracts') }}</h2>
            <span class="badge badge-soft-dark radius-50 fz-14">{{ $contracts->count() }}</span>
        </div>

        @if($contracts->isEmpty())
            <div class="contract-empty">
                <i class="tio-briefcase-outlined"></i>
                <p class="mb-0">{{ translate('no_contracts_yet') }}</p>
            </div>
        @else
            <div class="contract-table-card">
                <div class="table-responsive">
                    <table class="table contract-table">
                        <thead>
                            <tr>
                                <th>{{ translate('client') }}</th>
                                <th>{{ translate('service') }}</th>
                                <th>{{ translate('amount') }}</th>
                                <th>{{ translate('status') }}</th>
                                <th>{{ translate('delivery_status') }}</th>
                                <th>{{ translate('created_at') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contracts as $contract)
                                @php($customerName = trim(($contract->customer?->f_name ?? '') . ' ' . ($contract->customer?->l_name ?? '')) ?: translate('customer'))
                                <tr>
                                    <td data-label="{{ translate('client') }}">
                                        <div class="contract-row-customer">
                                            <img class="contract-row-avatar" alt="" src="{{ getStorageImages(path: $contract->customer?->image_full_url, type: 'backend-profile') }}">
                                            <span class="contract-row-customer-name">{{ $customerName }}</span>
                                        </div>
                                    </td>
                                    <td data-label="{{ translate('service') }}">
                                        <span class="contract-row-service" title="{{ $contract->service?->title }}">{{ $contract->service?->title ?? translate('not_available') }}</span>
                                    </td>
                                    <td data-label="{{ translate('amount') }}">
                                        <span class="contract-row-amount">{{ webCurrencyConverter($contract->total_amount) }}</span>
                                    </td>
                                    <td data-label="{{ translate('status') }}">
                                        <span class="contract-status-pill contract-status-{{ $contract->status }}">{{ str_replace('_', ' ', $contract->status) }}</span>
                                    </td>
                                    <td data-label="{{ translate('delivery_status') }}">
                                        <span class="contract-delivery-pill contract-delivery-{{ $contract->delivery_status->value }}">{{ str_replace('_', ' ', $contract->delivery_status->value) }}</span>
                                    </td>
                                    <td data-label="{{ translate('created_at') }}">{{ $contract->created_at->format('d M, Y') }}</td>
                                    <td data-label="{{ translate('action') }}">
                                        <a href="{{ route('freelancer.contracts.show', $contract->id) }}" class="btn btn-sm btn-outline--primary">
                                            {{ translate('view') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
