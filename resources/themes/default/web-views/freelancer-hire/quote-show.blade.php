@extends('layouts.front-end.app')

@section('title', translate('quote_request') . ' #' . $quote->id)

@push('css_or_js')
    <style>
        .status-pill {
            display: inline-flex; align-items: center; gap: 6px; padding: 5px 13px; border-radius: 999px;
            font-size: 11px; font-weight: 700; text-transform: capitalize; white-space: nowrap;
        }
        .status-pending { background: #fff4e5; color: #b76e00; }
        .status-quoted { background: #e6f7ee; color: #16794f; }
        .status-accepted { background: #e6f7ee; color: #16794f; }
        .status-declined { background: #fdecea; color: #b3261e; }
    </style>
@endpush

@section('content')
    <div class="container my-4 my-md-5">
        <div class="row">
            @include('web-views.partials._profile-aside')

            <section class="col-lg-9 __customer-profile px-0">
                <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h2 class="h1 mb-0">{{ translate('quote_request') }} #{{ $quote->id }}</h2>
                    <a href="{{ route('hire.quotes.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="tio-chevron-left"></i> {{ translate('back') }}
                    </a>
                </div>

                @php($freelancerName = $quote->seller?->shop?->name ?: trim(($quote->seller?->f_name ?? '') . ' ' . ($quote->seller?->l_name ?? '')))
                <div class="border rounded-10 p-4 mb-3 d-flex align-items-center gap-3 flex-wrap">
                    <img class="rounded-circle" style="width: 52px; height: 52px; object-fit: cover;" alt=""
                         src="{{ getStorageImages(path: $quote->seller?->shop?->image_full_url ?? $quote->seller?->image_full_url, type: 'backend-profile') }}">
                    <div>
                        <div class="fw-bold">{{ $freelancerName ?: translate('freelancer') }}</div>
                        <div class="text-muted small">{{ $quote->service?->title ?? translate('not_available') }} &middot; {{ $quote->created_at->format('d M, Y h:i A') }}</div>
                    </div>
                    <span class="status-pill status-{{ $quote->status->value }} ms-auto">
                        {{ $quote->status === \App\Enums\Freelancer\QuoteStatus::Pending ? translate('awaiting_reply') : ucfirst($quote->status->value) }}
                    </span>
                </div>

                <div class="border rounded-10 p-4 mb-3">
                    <h5 class="mb-3">{{ translate('request_details') }}</h5>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-muted small text-uppercase">{{ translate('budget') }}</div>
                            <div class="fw-bold">{{ $quote->budget !== null ? webCurrencyConverter($quote->budget) : translate('not_specified') }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small text-uppercase">{{ translate('preferred_delivery') }}</div>
                            <div class="fw-bold">{{ $quote->delivery_label }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small text-uppercase">{{ translate('requested') }}</div>
                            <div class="fw-bold">{{ $quote->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small text-uppercase mb-2">{{ translate('description') }}</div>
                            <div class="bg-light rounded p-3" style="white-space: pre-line;">{{ $quote->description }}</div>
                        </div>
                        @if(count($quote->attachment_full_url))
                            <div class="col-12">
                                <div class="text-muted small text-uppercase mb-2">{{ translate('attachments') }}</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($quote->attachment_full_url as $attachment)
                                        <a href="{{ $attachment['path'] ?? '#' }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="tio-attachment"></i> {{ $attachment['key'] ?? translate('attachment') }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if($quote->status === \App\Enums\Freelancer\QuoteStatus::Quoted)
                    <div class="border rounded-10 p-4 mb-3">
                        <h5 class="mb-3">{{ translate('freelancers_reply') }}</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="text-muted small text-uppercase">{{ translate('quoted_price') }}</div>
                                <div class="fw-bold text-success fs-4">{{ webCurrencyConverter($quote->quoted_price) }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small text-uppercase">{{ translate('quoted_delivery_time') }}</div>
                                <div class="fw-bold">{{ $quote->quoted_delivery_days }} {{ translate('days') }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small text-uppercase mb-2">{{ translate('message') }}</div>
                                <div class="bg-light rounded p-3" style="white-space: pre-line;">{{ $quote->reply_message }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('hire.create', ['service' => $quote->freelancer_service_id, 'quote_request_id' => $quote->id]) }}" class="btn btn--primary">
                            {{ translate('accept_and_hire') }}
                        </a>
                        <form action="{{ route('hire.quotes.decline', $quote->id) }}" method="POST" onsubmit="return confirm('{{ translate('are_you_sure_you_want_to_decline_this_quote') }}');" novalidate>
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">{{ translate('decline') }}</button>
                        </form>
                    </div>
                @elseif($quote->status === \App\Enums\Freelancer\QuoteStatus::Pending)
                    <div class="alert alert-info mb-0">{{ translate('waiting_for_the_freelancer_to_reply_to_this_request') }}</div>
                @elseif($quote->status === \App\Enums\Freelancer\QuoteStatus::Accepted)
                    <div class="alert alert-success mb-0">{{ translate('you_accepted_this_quote_and_hired_the_freelancer') }}</div>
                @elseif($quote->status === \App\Enums\Freelancer\QuoteStatus::Declined)
                    <div class="alert alert-secondary mb-0">{{ translate('you_declined_this_quote') }}</div>
                @endif
            </section>
        </div>
    </div>
@endsection
