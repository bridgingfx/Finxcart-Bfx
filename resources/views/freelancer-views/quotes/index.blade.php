@extends('layouts.freelancer.app')

@section('title', translate('quote_requests'))

@push('css_or_js')
    <style>
        .quote-table-card { background: #fff; border: 1px solid #e8edf3; border-radius: 10px; overflow: hidden; }
        .quote-table { margin-bottom: 0; }
        .quote-table thead th {
            background: #f6f8fb; color: #6b7280; font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .03em; border-bottom: 1px solid #e8edf3; padding: 14px 16px; white-space: nowrap;
        }
        .quote-table tbody td { padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid #f1f3f6; font-size: 13px; }
        .quote-table tbody tr:last-child td { border-bottom: none; }
        .quote-table tbody tr:hover { background: #f9fafc; }

        .quote-row-customer { display: flex; align-items: center; gap: 10px; }
        .quote-row-avatar {
            width: 36px; height: 36px; border-radius: 50%; object-fit: cover; background: #eef2ff; flex-shrink: 0;
        }
        .quote-row-customer-name { font-weight: 700; color: #1f2937; }

        .quote-row-service { font-weight: 600; color: #17395e; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .quote-status-pill {
            display: inline-block; padding: 5px 14px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap;
        }
        .quote-status-pending { background: #fff4e5; color: #b76e00; }
        .quote-status-quoted { background: #e6f7ee; color: #16794f; }
        .quote-status-accepted { background: #e6f7ee; color: #16794f; }
        .quote-status-declined { background: #fdecea; color: #b3261e; }

        .quote-table .btn-outline-primary { border-radius: 999px; font-weight: 700; }

        .quote-empty {
            background: #fff; border: 1px solid #e8edf3; border-radius: 10px; padding: 48px; text-align: center; color: #6b7280;
        }
        .quote-empty i { font-size: 34px; color: #d8dee7; display: block; margin-bottom: 10px; }

        @media (max-width: 767.98px) {
            .quote-table thead { display: none; }
            .quote-table, .quote-table tbody, .quote-table tr, .quote-table td { display: block; width: 100%; }
            .quote-table tr { border-bottom: 8px solid #f6f8fb; padding: 10px 0; }
            .quote-table td { border-bottom: none !important; padding: 6px 16px; }
            .quote-table td::before {
                content: attr(data-label); display: block; font-size: 11px; font-weight: 700; color: #9ca3af;
                text-transform: uppercase; letter-spacing: .03em; margin-bottom: 3px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex align-items-center gap-10">
            <h2 class="h1 mb-0">{{ translate('quote_requests') }}</h2>
            <span class="badge badge-soft-dark radius-50 fz-14">{{ $quotes->total() }}</span>
        </div>

        @if($quotes->isEmpty())
            <div class="quote-empty">
                <i class="tio-receipt-outlined"></i>
                <p class="mb-0">{{ translate('no_quote_requests_yet') }}</p>
            </div>
        @else
            <div class="quote-table-card">
                <div class="table-responsive">
                    <table class="table quote-table">
                        <thead>
                            <tr>
                                <th>{{ translate('customer') }}</th>
                                <th>{{ translate('service') }}</th>
                                <th>{{ translate('budget') }}</th>
                                <th>{{ translate('preferred_delivery') }}</th>
                                <th>{{ translate('requested') }}</th>
                                <th>{{ translate('status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quotes as $quote)
                                @php($customerName = trim(($quote->customer?->f_name ?? '') . ' ' . ($quote->customer?->l_name ?? '')) ?: translate('customer'))
                                <tr>
                                    <td data-label="{{ translate('customer') }}">
                                        <div class="quote-row-customer">
                                            <img class="quote-row-avatar" alt="" src="{{ getStorageImages(path: $quote->customer?->image_full_url, type: 'backend-profile') }}">
                                            <span class="quote-row-customer-name">{{ $customerName }}</span>
                                        </div>
                                    </td>
                                    <td data-label="{{ translate('service') }}">
                                        <span class="quote-row-service" title="{{ $quote->service?->title }}">{{ $quote->service?->title ?? translate('not_available') }}</span>
                                    </td>
                                    <td data-label="{{ translate('budget') }}">{{ $quote->budget !== null ? webCurrencyConverter($quote->budget) : translate('not_specified') }}</td>
                                    <td data-label="{{ translate('preferred_delivery') }}">{{ $quote->delivery_label }}</td>
                                    <td data-label="{{ translate('requested') }}">{{ $quote->created_at->diffForHumans() }}</td>
                                    <td data-label="{{ translate('status') }}">
                                        <span class="quote-status-pill quote-status-{{ $quote->status->value }}">
                                            {{ $quote->status === \App\Enums\Freelancer\QuoteStatus::Pending ? translate('awaiting_reply') : ucfirst($quote->status->value) }}
                                        </span>
                                    </td>
                                    <td data-label="{{ translate('action') }}">
                                        <a href="{{ route('freelancer.quotes.view', $quote->id) }}" class="btn btn-sm btn-outline-primary">
                                            {{ translate('view') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                {{ $quotes->links() }}
            </div>
        @endif
    </div>
@endsection
