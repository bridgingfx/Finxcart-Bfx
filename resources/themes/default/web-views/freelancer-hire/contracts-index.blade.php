@extends('layouts.front-end.app')

@section('title', translate('my_hires'))

@push('css_or_js')
    <style>
        .hires-page { background: #f6f8fb; padding: 38px 0 54px; }
        .hires-heading { color: #17395e; font-weight: 800; font-size: 28px; margin-bottom: 4px; }
        .hires-subtitle { color: #6b7280; margin-bottom: 24px; }

        .hires-table-card { background: #fff; border: 1px solid #e7ebf0; border-radius: 10px; overflow: hidden; }
        .hires-table { margin-bottom: 0; }
        .hires-table thead th {
            background: #f6f8fb; color: #6b7280; font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .03em; border-bottom: 1px solid #e7ebf0; padding: 14px 16px; white-space: nowrap;
        }
        .hires-table tbody td { padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid #f1f3f6; font-size: 13px; }
        .hires-table tbody tr:last-child td { border-bottom: none; }
        .hires-table tbody tr:hover { background: #f9fafc; }

        .hires-row-service { display: flex; align-items: center; gap: 12px; }
        .hires-row-cover { width: 46px; height: 46px; border-radius: 8px; object-fit: cover; background: #eef2f7; flex-shrink: 0; }
        .hires-row-cover-fallback {
            width: 46px; height: 46px; border-radius: 8px; background: #eef2ff; color: #3730a3;
            display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 17px; flex-shrink: 0;
        }
        .hires-row-title { font-weight: 700; color: #1f2937; }
        .hires-row-title a { color: inherit; text-decoration: none; }
        .hires-row-title a:hover { color: #17395e; }
        .hires-row-hint { font-size: 11px; font-weight: 700; color: #b76e00; margin-top: 2px; }

        .hires-row-freelancer { display: flex; align-items: center; gap: 8px; }
        .hires-row-freelancer img { width: 26px; height: 26px; border-radius: 50%; object-fit: cover; }
        .hires-row-freelancer span { font-size: 13px; color: #4b5563; font-weight: 600; }

        .hires-row-price { font-weight: 800; color: #16794f; }
        .hires-row-muted { color: #9ca3af; }

        .status-pill { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: capitalize; white-space: nowrap; }
        .status-active { background: #e7f3ff; color: #1a73e8; }
        .status-submitted { background: #fff4e5; color: #b76e00; }
        .status-completed { background: #e6f7ee; color: #16794f; }
        .status-cancelled { background: #fde8e8; color: #c0392b; }
        .status-pending_funding { background: #f1f1f1; color: #555; }

        .delivery-cell { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .delivery-pill { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: capitalize; white-space: nowrap; }
        .delivery-pending { background: #f1f3f6; color: #6b7280; }
        .delivery-in_progress { background: #e7f3ff; color: #1a73e8; }
        .delivery-submitted_for_review { background: #fff4e5; color: #b76e00; }
        .delivery-delivered { background: #e6f7ee; color: #16794f; }
        .delivery-approve-btn { border-radius: 999px; font-weight: 700; padding: 4px 14px; font-size: 11px; line-height: 1.4; }

        .hires-table .btn-outline--primary { border-radius: 999px; font-weight: 700; }

        .hires-empty {
            background: #fff; border: 1px solid #e7ebf0; border-radius: 12px; padding: 54px 24px; text-align: center;
        }
        .hires-empty i { font-size: 40px; color: #d8dee7; margin-bottom: 14px; display: block; }
        .hires-empty p { color: #6b7280; margin-bottom: 18px; }

        @media (max-width: 767.98px) {
            .hires-table thead { display: none; }
            .hires-table, .hires-table tbody, .hires-table tr, .hires-table td { display: block; width: 100%; }
            .hires-table tr { border-bottom: 8px solid #f6f8fb; padding: 10px 0; }
            .hires-table td { border-bottom: none !important; padding: 6px 16px; }
            .hires-table td::before {
                content: attr(data-label); display: block; font-size: 11px; font-weight: 700; color: #9ca3af;
                text-transform: uppercase; letter-spacing: .03em; margin-bottom: 3px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="hires-page">
        <div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
            <div class="row">
                @include('web-views.partials._profile-aside')
                <section class="col-lg-9 __customer-profile px-0">
                <h1 class="hires-heading">{{ translate('my_hires') }}</h1>
            <p class="hires-subtitle">{{ translate('track_the_freelancers_you_have_hired_and_their_delivery_status') }}</p>

            @if($contracts->isEmpty())
                <div class="hires-empty">
                    <i class="tio-briefcase-outlined"></i>
                    <p>{{ translate('you_have_not_hired_any_freelancers_yet') }}</p>
                    <a href="{{ route('hire-freelancer') }}" class="btn btn--primary">{{ translate('browse_freelancer_services') }}</a>
                </div>
            @else
                <div class="hires-table-card">
                    <div class="table-responsive">
                        <table class="table hires-table">
                            <thead>
                                <tr>
                                    <th>{{ translate('service') }}</th>
                                    <th>{{ translate('freelancer') }}</th>
                                    <th>{{ translate('amount') }}</th>
                                    <th>{{ translate('status') }}</th>
                                    <th>{{ translate('delivery') }}</th>
                                    <th>{{ translate('hired_on') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contracts as $contract)
                                    @php($freelancerName = $contract->freelancer->shop?->name ?: trim($contract->freelancer->f_name . ' ' . $contract->freelancer->l_name))
                                    @php($cover = $contract->service?->images->first())
                                    @php($delivery = $contract->milestones->first())
                                    <tr>
                                        <td data-label="{{ translate('service') }}">
                                            <div class="hires-row-service">
                                                @if($cover)
                                                    <img class="hires-row-cover" alt="{{ $contract->service?->title }}" src="{{ getStorageImages(path: $cover->image_full_url, type: 'backend-profile') }}">
                                                @else
                                                    <div class="hires-row-cover-fallback">{{ strtoupper(substr($freelancerName, 0, 1)) }}</div>
                                                @endif
                                                <div>
                                                    <div class="hires-row-title">
                                                        <a href="{{ route('hire.contracts.show', $contract->id) }}">{{ $contract->service?->title }}</a>
                                                    </div>
                                                    @if($delivery && $delivery->status === 'submitted')
                                                        <div class="hires-row-hint">{{ translate('delivery_ready_for_your_review') }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="{{ translate('freelancer') }}">
                                            <div class="hires-row-freelancer">
                                                <img alt="" src="{{ getStorageImages(path: $contract->freelancer->image_full_url, type: 'backend-profile') }}">
                                                <span>{{ $freelancerName }}</span>
                                            </div>
                                        </td>
                                        <td data-label="{{ translate('amount') }}">
                                            <span class="hires-row-price">{{ webCurrencyConverter($contract->total_amount) }}</span>
                                        </td>
                                        <td data-label="{{ translate('status') }}">
                                            <span class="status-pill status-{{ $contract->status }}">{{ str_replace('_', ' ', $contract->status) }}</span>
                                        </td>
                                        <td data-label="{{ translate('delivery') }}">
                                            @if($delivery && $delivery->status === 'submitted')
                                                <div class="delivery-cell">
                                                    <span class="delivery-pill delivery-{{ $contract->delivery_status->value }}">{{ str_replace('_', ' ', $contract->delivery_status->value) }}</span>
                                                    <form action="{{ route('hire.contracts.milestones.approve', [$contract->id, $delivery->id]) }}" method="POST" novalidate>
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn--primary delivery-approve-btn">{{ translate('approve') }}</button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="delivery-pill delivery-{{ $contract->delivery_status->value }}">
                                                    {{ str_replace('_', ' ', $contract->delivery_status->value) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td data-label="{{ translate('hired_on') }}">{{ $contract->created_at->format('d M, Y') }}</td>
                                        <td data-label="{{ translate('action') }}">
                                            <a href="{{ route('hire.contracts.show', $contract->id) }}" class="btn btn-sm btn-outline--primary">
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
                </section>
            </div>
        </div>
    </div>
@endsection
