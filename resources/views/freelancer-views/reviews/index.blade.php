@extends('layouts.freelancer.app')

@section('title', translate('reviews'))

@push('css_or_js')
    <style>
        .fl-review-summary {
            display: grid; grid-template-columns: 220px 1fr; gap: 24px;
            background: #fff; border: 1px solid #e8edf3; border-radius: 12px; padding: 24px; margin-bottom: 20px;
        }
        .fl-review-summary-score { text-align: center; border-right: 1px solid #f1f3f6; padding-right: 24px; }
        .fl-review-summary-value { font-weight: 800; font-size: 40px; color: #17395e; line-height: 1; }
        .fl-review-summary-stars { font-size: 16px; margin: 8px 0; display: inline-flex; gap: 2px; }
        .fl-review-summary-stars i.is-filled { color: #f5a623; }
        .fl-review-summary-stars i.is-empty { color: #e2e6ec; }
        .fl-review-summary-count { font-size: 12px; color: #9ca3af; }
        .fl-review-bar-row { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; font-size: 12px; color: #6b7280; }
        .fl-review-bar-row span:first-child { width: 46px; flex-shrink: 0; }
        .fl-review-bar-track { flex: 1; height: 7px; border-radius: 999px; background: #f1f3f6; overflow: hidden; }
        .fl-review-bar-fill { height: 100%; background: linear-gradient(90deg, #f97316, #fb923c); border-radius: 999px; }
        .fl-review-bar-row span:last-child { width: 26px; text-align: right; flex-shrink: 0; }

        .fl-review-card {
            border: 1px solid #e8edf3; border-radius: 12px; padding: 18px; margin-bottom: 12px; background: #fff;
            transition: box-shadow .2s ease, transform .2s ease;
        }
        .fl-review-card:hover { box-shadow: 0 12px 26px rgba(23, 57, 94, 0.08); transform: translateY(-2px); }
        .fl-review-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, #17395e, #2d6cdf); color: #fff;
            display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0;
        }
        .fl-review-author { font-weight: 700; font-size: 13px; color: #1f2937; }
        .fl-review-reference { font-size: 12px; color: #9ca3af; }
        .fl-review-stars { display: inline-flex; gap: 1px; font-size: 12px; }
        .fl-review-stars i.is-filled { color: #f5a623; }
        .fl-review-stars i.is-empty { color: #e2e6ec; }
        .fl-review-body { font-size: 13px; color: #4b5563; margin-top: 10px; line-height: 1.55; }
        .fl-review-source-badge {
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; border-radius: 999px; padding: 3px 10px;
        }
        .fl-review-source-badge.contract { background: #e9f9f0; color: #219653; }
        .fl-review-source-badge.other { background: #eef2f7; color: #6b7280; }

        @media (max-width: 575.98px) {
            .fl-review-summary { grid-template-columns: 1fr; }
            .fl-review-summary-score { border-right: none; border-bottom: 1px solid #f1f3f6; padding-right: 0; padding-bottom: 16px; margin-bottom: 6px; }
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0 d-flex gap-10">
                {{ translate('reviews') }}
                <span class="badge badge-soft-dark radius-50 fz-14">{{ $totalReviews }}</span>
            </h2>
        </div>

        <div class="fl-review-summary">
            <div class="fl-review-summary-score">
                <div class="fl-review-summary-value">{{ $totalReviews > 0 ? $avgRating : '—' }}</div>
                <div class="fl-review-summary-stars">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="tio-star {{ $i <= round($avgRating) ? 'is-filled' : 'is-empty' }}"></i>
                    @endfor
                </div>
                <div class="fl-review-summary-count">{{ $totalReviews }} {{ translate('reviews') }}</div>
            </div>
            <div>
                @foreach($ratingBreakdown as $stars => $count)
                    <div class="fl-review-bar-row">
                        <span>{{ $stars }} {{ translate('star') }}</span>
                        <div class="fl-review-bar-track">
                            <div class="fl-review-bar-fill" style="width: {{ $totalReviews > 0 ? ($count / $totalReviews * 100) : 0 }}%;"></div>
                        </div>
                        <span>{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        @forelse($reviews as $review)
            <div class="fl-review-card">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <div class="fl-review-avatar">{{ strtoupper(substr($review->reviewer_name, 0, 1)) }}</div>
                        <div>
                            <div class="fl-review-author">{{ $review->reviewer_name }}</div>
                            <div class="fl-review-reference">{{ $review->reference }} &middot; {{ $review->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <span class="fl-review-source-badge {{ $review->source === 'contract' ? 'contract' : 'other' }}">
                        {{ $review->source === 'contract' ? translate('verified_contract') : translate($review->source) }}
                    </span>
                </div>
                <div class="fl-review-stars mt-2">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="tio-star {{ $i <= $review->rating ? 'is-filled' : 'is-empty' }}"></i>
                    @endfor
                </div>
                @if($review->body)
                    <div class="fl-review-body">{{ $review->body }}</div>
                @endif
            </div>
        @empty
            <div class="fl-review-card text-center text-muted py-4">
                {{ translate('no_reviews_yet') }}
            </div>
        @endforelse

        <div class="mt-3">
            {{ $reviews->links() }}
        </div>
    </div>
@endsection
