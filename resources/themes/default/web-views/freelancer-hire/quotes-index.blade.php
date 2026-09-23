@extends('layouts.front-end.app')

@section('title', translate('my_quote_requests'))

@push('css_or_js')
    <style>
        .quotes-page { background: #f6f8fb; padding: 38px 0 54px; }

        .quotes-page-header {
            display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; flex-wrap: wrap;
            margin-bottom: 24px;
        }
        .quotes-heading { color: #17395e; font-weight: 800; font-size: 28px; margin-bottom: 4px; }
        .quotes-subtitle { color: #6b7280; margin: 0; }
        .quotes-count-badge {
            display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #e7ebf0;
            color: #17395e; font-weight: 700; font-size: 13px; padding: 8px 16px; border-radius: 999px; white-space: nowrap;
        }
        .quotes-count-badge i { color: #f97316; }

        .quotes-table-card { background: #fff; border: 1px solid #e7ebf0; border-radius: 10px; overflow: hidden; }
        .quotes-table { margin-bottom: 0; }
        .quotes-table thead th {
            background: #f6f8fb; color: #6b7280; font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .03em; border-bottom: 1px solid #e7ebf0; padding: 14px 16px; white-space: nowrap;
        }
        .quotes-table tbody td { padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid #f1f3f6; font-size: 13px; }
        .quotes-table tbody tr:last-child td { border-bottom: none; }
        .quotes-table tbody tr:hover { background: #f9fafc; }
        .quotes-table tr.quote-detail-row td { border-bottom: 1px solid #f1f3f6; background: #f9fafb; }
        .quotes-table tr.quote-detail-row:last-child td { border-bottom: none; }

        .quotes-row-service { display: flex; align-items: center; gap: 10px; }
        .quotes-row-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; background: #eef2ff; flex-shrink: 0; }
        .quotes-row-title { font-weight: 700; color: #1f2937; }
        .quotes-row-seller { font-size: 12px; color: #6b7280; }

        .status-pill {
            display: inline-flex; align-items: center; gap: 6px; padding: 5px 13px; border-radius: 999px;
            font-size: 11px; font-weight: 700; text-transform: capitalize; white-space: nowrap;
        }
        .status-pill i { font-size: 13px; }
        .status-pending { background: #fff4e5; color: #b76e00; }
        .status-quoted { background: #e6f7ee; color: #16794f; }
        .status-accepted { background: #e6f7ee; color: #16794f; }
        .status-declined { background: #fdecea; color: #b3261e; }

        .quotes-filter select {
            border: 1px solid #e7ebf0; border-radius: 8px; padding: 8px 14px; font-size: 13px; color: #374151;
        }

        .quotes-row-price { font-weight: 800; color: #16794f; }
        .quotes-row-muted { color: #9ca3af; }
        .quotes-table .btn-outline--primary { border-radius: 999px; font-weight: 700; }

        .quote-detail-label { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .03em; font-weight: 700; }
        .quote-detail-text { font-size: 13px; color: #374151; line-height: 1.6; margin-top: 4px; }

        .quotes-empty {
            background: #fff; border: 1px solid #e7ebf0; border-radius: 12px; padding: 54px 24px; text-align: center;
        }
        .quotes-empty i { font-size: 40px; color: #d8dee7; margin-bottom: 14px; display: block; }
        .quotes-empty p { color: #6b7280; margin-bottom: 18px; }

        @media (max-width: 767.98px) {
            .quotes-table thead { display: none; }
            .quotes-table, .quotes-table tbody, .quotes-table tr, .quotes-table td { display: block; width: 100%; }
            .quotes-table tr:not(.quote-detail-row) { border-bottom: 8px solid #f6f8fb; padding: 10px 0; }
            .quotes-table td { border-bottom: none !important; padding: 6px 16px; }
            .quotes-table td::before {
                content: attr(data-label); display: block; font-size: 11px; font-weight: 700; color: #9ca3af;
                text-transform: uppercase; letter-spacing: .03em; margin-bottom: 3px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="quotes-page">
        <div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
            <div class="row">
                @include('web-views.partials._profile-aside')
                <section class="col-lg-9 __customer-profile px-0">
            <div class="quotes-page-header">
                <div>
                    <h1 class="quotes-heading">{{ translate('my_quote_requests') }}</h1>
                    <p class="quotes-subtitle">{{ translate('track_the_quotes_you_have_requested_from_freelancers') }}</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form action="{{ route('hire.quotes.index') }}" method="GET" class="quotes-filter" novalidate>
                        <select name="status" onchange="this.form.submit()">
                            <option value="">{{ translate('all_statuses') }}</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ translate('pending') }}</option>
                            <option value="quoted" {{ request('status') === 'quoted' ? 'selected' : '' }}>{{ translate('quoted') }}</option>
                            <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>{{ translate('accepted') }}</option>
                            <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>{{ translate('declined') }}</option>
                        </select>
                    </form>
                    @if($quotes->total())
                        <span class="quotes-count-badge"><i class="tio-receipt-outlined"></i> {{ $quotes->total() }} {{ translate('requests') }}</span>
                    @endif
                </div>
            </div>

            @if($quotes->isEmpty())
                <div class="quotes-empty">
                    <i class="tio-receipt-outlined"></i>
                    <p>{{ translate('you_have_not_requested_any_quotes_yet') }}</p>
                    <a href="{{ route('hire-freelancer') }}" class="btn btn--primary">{{ translate('browse_freelancer_services') }}</a>
                </div>
            @else
                <div class="quotes-table-card">
                    <div class="table-responsive">
                        <table class="table quotes-table">
                            <thead>
                                <tr>
                                    <th>{{ translate('service') }}</th>
                                    <th>{{ translate('requested') }}</th>
                                    <th>{{ translate('quoted_price') }}</th>
                                    <th>{{ translate('delivery_time_days') }}</th>
                                    <th>{{ translate('status') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotes as $quote)
                                    @php($sellerName = $quote->seller?->shop?->name ?: trim(($quote->seller?->f_name ?? '') . ' ' . ($quote->seller?->l_name ?? '')))
                                    <tr>
                                        <td data-label="{{ translate('service') }}">
                                            <div class="quotes-row-service">
                                                <img class="quotes-row-avatar" alt="" src="{{ getStorageImages(path: $quote->seller?->image_full_url, type: 'backend-profile') }}">
                                                <div>
                                                    <div class="quotes-row-title">{{ $quote->service?->title ?? translate('not_available') }}</div>
                                                    <div class="quotes-row-seller">{{ $sellerName }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="{{ translate('requested') }}">{{ $quote->created_at->format('d M, Y') }}</td>
                                        <td data-label="{{ translate('quoted_price') }}">
                                            @if($quote->status === \App\Enums\Freelancer\QuoteStatus::Quoted)
                                                <span class="quotes-row-price">{{ webCurrencyConverter($quote->quoted_price) }}</span>
                                            @else
                                                <span class="quotes-row-muted">&mdash;</span>
                                            @endif
                                        </td>
                                        <td data-label="{{ translate('delivery_time_days') }}">
                                            @if($quote->status === \App\Enums\Freelancer\QuoteStatus::Quoted)
                                                {{ $quote->quoted_delivery_days }} {{ translate('days') }}
                                            @else
                                                <span class="quotes-row-muted">&mdash;</span>
                                            @endif
                                        </td>
                                        <td data-label="{{ translate('status') }}">
                                            <span class="status-pill status-{{ $quote->status->value }}">
                                                <i class="tio-{{ $quote->status === \App\Enums\Freelancer\QuoteStatus::Pending ? 'time-outlined' : 'checkmark-circle' }}"></i>
                                                {{ ucfirst($quote->status->value) }}
                                            </span>
                                        </td>
                                        <td data-label="{{ translate('action') }}">
                                            <a href="{{ route('hire.quotes.show', $quote->id) }}" class="btn btn-sm btn-outline--primary">
                                                {{ translate('view') }}
                                            </a>
                                            @if($quote->status === \App\Enums\Freelancer\QuoteStatus::Quoted)
                                                <a href="{{ route('hire.create', ['service' => $quote->freelancer_service_id, 'quote_request_id' => $quote->id]) }}" class="btn btn-sm btn--primary">
                                                    {{ translate('accept_and_hire') }}
                                                </a>
                                                <form action="{{ route('hire.quotes.decline', $quote->id) }}" method="POST" class="d-inline" novalidate>
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ translate('decline') }}</button>
                                                </form>
                                            @endif
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
                </section>
            </div>
        </div>
    </div>
@endsection
