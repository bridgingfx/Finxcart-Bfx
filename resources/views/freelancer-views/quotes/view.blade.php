@extends('layouts.freelancer.app')

@section('title', translate('quote_request'))

@push('css_or_js')
    <style>
        .quote-detail-header {
            background: linear-gradient(135deg, #17395e 0%, #1f4a78 100%); border-radius: 10px; padding: 22px 24px; margin-bottom: 18px;
            display: flex; align-items: center; gap: 16px; flex-wrap: wrap; color: #fff;
        }
        .quote-detail-avatar {
            width: 52px; height: 52px; border-radius: 50%; object-fit: cover; background: rgba(255,255,255,.15);
            flex-shrink: 0; border: 2px solid rgba(255,255,255,.4);
        }
        .quote-detail-customer { font-weight: 800; font-size: 17px; color: #fff; }
        .quote-detail-meta { font-size: 13px; color: rgba(255,255,255,.75); margin-top: 2px; }
        .quote-status-pill {
            display: inline-block; padding: 5px 14px; border-radius: 999px; font-size: 12px; font-weight: 700; margin-left: auto;
        }
        .quote-detail-header .quote-status-pill { background: rgba(255,255,255,.18); color: #fff; }
        .quote-status-pending { background: #fff4e5; color: #b76e00; }
        .quote-status-quoted { background: #e6f7ee; color: #16794f; }
        .quote-status-accepted { background: #e6f7ee; color: #16794f; }
        .quote-status-declined { background: #fdecea; color: #b3261e; }
        .quote-section { background: #fff; border: 1px solid #e8edf3; border-radius: 10px; padding: 22px 24px; margin-bottom: 18px; }
        .quote-section-title { font-weight: 800; font-size: 15px; color: #17395e; margin-bottom: 14px; }
        .quote-fact-label { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .03em; }
        .quote-fact-value { font-size: 14px; font-weight: 700; color: #1f2937; margin-top: 2px; }
        .quote-description-box { background: #f9fafb; border-radius: 8px; padding: 14px; font-size: 14px; color: #374151; white-space: pre-line; }
        .quote-reply-summary .quote-fact-value.price { color: #16794f; font-size: 20px; }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0">{{ translate('quote_request') }} #{{ $quote->id }}</h2>
            <a href="{{ route('freelancer.quotes.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="tio-chevron-left"></i> {{ translate('back') }}
            </a>
        </div>

        @php($customerName = trim(($quote->customer?->f_name ?? '') . ' ' . ($quote->customer?->l_name ?? '')) ?: translate('customer'))
        <div class="quote-detail-header">
            <img class="quote-detail-avatar" alt="" src="{{ getStorageImages(path: $quote->customer?->image_full_url, type: 'backend-profile') }}">
            <div>
                <div class="quote-detail-customer">{{ $customerName }}</div>
                <div class="quote-detail-meta">{{ $quote->service?->title ?? translate('not_available') }} &middot; {{ $quote->created_at->format('d M, Y h:i A') }}</div>
            </div>
            <span class="quote-status-pill quote-status-{{ $quote->status->value }}">
                {{ $quote->status === \App\Enums\Freelancer\QuoteStatus::Pending ? translate('awaiting_reply') : ucfirst($quote->status->value) }}
            </span>
        </div>

        <div class="quote-section">
            <div class="quote-section-title">{{ translate('request_details') }}</div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="quote-fact-label">{{ translate('budget') }}</div>
                    <div class="quote-fact-value">{{ $quote->budget !== null ? webCurrencyConverter($quote->budget) : translate('not_specified') }}</div>
                </div>
                <div class="col-md-4">
                    <div class="quote-fact-label">{{ translate('preferred_delivery') }}</div>
                    <div class="quote-fact-value">{{ $quote->delivery_label }}</div>
                </div>
                <div class="col-md-4">
                    <div class="quote-fact-label">{{ translate('received') }}</div>
                    <div class="quote-fact-value">{{ $quote->created_at->diffForHumans() }}</div>
                </div>
                <div class="col-12">
                    <div class="quote-fact-label mb-2">{{ translate('description') }}</div>
                    <div class="quote-description-box">{{ $quote->description }}</div>
                </div>
                @if(count($quote->attachment_full_url))
                    <div class="col-12">
                        <div class="quote-fact-label mb-2">{{ translate('attachments') }}</div>
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

        @if($quote->status === \App\Enums\Freelancer\QuoteStatus::Pending)
            <div class="quote-section">
                <div class="quote-section-title">{{ translate('send_your_quote') }}</div>
                <form action="{{ route('freelancer.quotes.reply', $quote->id) }}" method="post" novalidate>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('your_price') }} *</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="quoted_price" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('delivery_time_days') }} *</label>
                            <input type="number" min="1" max="365" class="form-control" name="quoted_delivery_days" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ translate('message') }} *</label>
                            <textarea class="form-control" rows="4" name="reply_message" maxlength="2500" required></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn--primary">
                            <i class="tio-send-outlined"></i> {{ translate('send_quote') }}
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="quote-section quote-reply-summary">
                <div class="quote-section-title">{{ translate('your_reply') }}</div>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="quote-fact-label">{{ translate('quoted_price') }}</div>
                        <div class="quote-fact-value price">{{ webCurrencyConverter($quote->quoted_price) }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="quote-fact-label">{{ translate('quoted_delivery_time') }}</div>
                        <div class="quote-fact-value">{{ $quote->quoted_delivery_days }} {{ translate('days') }}</div>
                    </div>
                    <div class="col-12">
                        <div class="quote-fact-label mb-2">{{ translate('message') }}</div>
                        <div class="quote-description-box">{{ $quote->reply_message }}</div>
                    </div>
                    <div class="col-12">
                        <div class="quote-fact-label">{{ translate('replied_at') }}</div>
                        <div class="quote-fact-value">{{ $quote->replied_at?->format('d M, Y h:i A') }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
