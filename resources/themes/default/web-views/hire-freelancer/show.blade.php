@extends('layouts.front-end.app')

@section('title', ($displayName ?? translate('freelancer')) . ' — ' . translate('hire_freelancer'))

@push('css_or_js')
<style>
    /* Freelancer profile — fx light-first skin.
       JS hooks preserved: [data-toggle-tiers], [data-tier-grid], .get-quote-btn,
       #getQuoteModal, #get-quote-form, .quote-delivery-option, #quote-delivery-preference,
       #quote-custom-delivery, #fl-chat-fab, #fl-chat-panel, #fl-chat-fab-btn,
       #fl-chat-fab-btn-sidebar, #fl-chat-close, #fl-chat-message, #fl-chat-form,
       #fl-chat-thread, #fl-chat-fab-badge, #fl-chat-file, #fl-chat-file-name,
       .fl-chat-trigger, .fl-chat-suggestion */
    .fx-profile-hero { display: flex; gap: var(--fx-s5); align-items: flex-start; flex-wrap: wrap; }
    .fx-profile-avatar { width: 104px; height: 104px; border-radius: 50%; object-fit: cover; border: 2px solid var(--fx-border); background: var(--fx-canvas); flex-shrink: 0; }
    .fx-profile-name { font-size: 28px; font-weight: 700; letter-spacing: -0.02em; margin: 0; }
    .fx-profile-handle { color: var(--fx-muted); font-weight: 500; font-size: 16px; }
    .fx-stars { color: var(--fx-amber); letter-spacing: .1em; font-size: 15px; }
    .fx-stars .is-empty { color: var(--fx-border); }
    .fx-profile-stats { display: flex; gap: var(--fx-s3); flex-wrap: wrap; margin-top: var(--fx-s4); }
    .fx-stat { border: 1px solid var(--fx-border); border-radius: var(--fx-radius-md); padding: var(--fx-s3) var(--fx-s4); background: var(--fx-surface); min-width: 120px; }
    .fx-stat__value { font-size: 18px; font-weight: 700; }
    .fx-stat__label { font-size: 12px; color: var(--fx-muted); text-transform: uppercase; letter-spacing: .05em; font-weight: 600; margin-top: 2px; }
    .fx-profile-layout { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: var(--fx-s6); align-items: start; margin-top: var(--fx-s6); }
    @media (max-width: 991px) { .fx-profile-layout { grid-template-columns: 1fr; } }

    .fx-service-card { padding: 0; overflow: hidden; }
    .fx-service-card__cover { aspect-ratio: 16 / 9; background: var(--fx-canvas); }
    .fx-service-card__cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .service-card-body { padding: var(--fx-s5); display: flex; flex-direction: column; gap: var(--fx-s3); }
    .fx-service-card__title { font-size: 18px; font-weight: 700; letter-spacing: -0.01em; margin: 0; }
    [data-tier-grid] { display: none; }
    [data-tier-grid].is-open { display: grid; }
    .fx-tier-grid { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--fx-s3); margin-top: var(--fx-s3); }
    .fx-tier-card { border: 1px solid var(--fx-border); border-radius: var(--fx-radius-md); padding: var(--fx-s4); background: var(--fx-canvas); display: flex; flex-direction: column; gap: var(--fx-s2); }
    .fx-tier-card__name { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--fx-navy); }
    .fx-tier-card__price { font-size: 20px; font-weight: 700; }
    .fx-tier-feature-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: var(--fx-s1); font-size: 13px; }
    .fx-tier-feature-list .is-included { color: var(--fx-text); }
    .fx-tier-feature-list .is-excluded { color: var(--fx-muted); text-decoration: line-through; }
    .fx-service-actions { display: flex; gap: var(--fx-s2); flex-wrap: wrap; margin-top: var(--fx-s1); }

    .fx-portfolio-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: var(--fx-s5); }
    .fx-portfolio-card { padding: 0; overflow: hidden; }
    .fx-portfolio-card__cover { aspect-ratio: 16 / 10; background: var(--fx-canvas); position: relative; }
    .fx-portfolio-card__cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .fx-portfolio-card__date { position: absolute; top: var(--fx-s3); inset-inline-end: var(--fx-s3); }
    .fx-portfolio-card__body { padding: var(--fx-s4); display: flex; flex-direction: column; gap: var(--fx-s2); }
    .fx-portfolio-card__title { font-size: 16px; font-weight: 700; margin: 0; }

    .fx-review-card { display: flex; flex-direction: column; gap: var(--fx-s2); }
    .fx-review-head { display: flex; align-items: center; gap: var(--fx-s3); }
    .fx-review-avatar { width: 44px; height: 44px; border-radius: 50%; background: var(--fx-navy); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
    .fx-review-facts { display: flex; gap: var(--fx-s4); flex-wrap: wrap; margin-top: var(--fx-s2); }
    .fx-review-fact-label { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: var(--fx-muted); font-weight: 600; }

    /* Floating chat */
    .fl-chat-fab { position: fixed; bottom: 24px; inset-inline-end: 24px; z-index: 90; display: flex; align-items: center; gap: var(--fx-s2); background: var(--fx-navy); color: #fff; border: 0; border-radius: var(--fx-radius-pill); padding: var(--fx-s2) var(--fx-s4) var(--fx-s2) var(--fx-s2); cursor: pointer; box-shadow: 0 12px 32px rgba(23, 43, 66, .35); font-weight: 700; font-size: 14px; }
    .fl-chat-fab img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
    .fl-chat-fab-badge { display: none; min-width: 22px; height: 22px; border-radius: 11px; background: #dc2626; color: #fff; font-size: 12px; font-weight: 800; align-items: center; justify-content: center; padding: 0 6px; }
    .fl-chat-fab-badge.is-visible { display: inline-flex; }
    .fl-chat-panel { display: none; position: fixed; bottom: 24px; inset-inline-end: 24px; z-index: 95; width: min(380px, calc(100vw - 32px)); max-height: min(560px, calc(100vh - 48px)); background: var(--fx-surface); border: 1px solid var(--fx-border); border-radius: var(--fx-radius-lg); box-shadow: 0 24px 64px rgba(23, 43, 66, .25); flex-direction: column; overflow: hidden; }
    .fl-chat-panel.is-open { display: flex; }
    .fl-chat-panel-header { display: flex; align-items: center; gap: var(--fx-s3); padding: var(--fx-s4); border-bottom: 1px solid var(--fx-border); background: var(--fx-canvas); }
    .fl-chat-panel-header img { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; }
    .fl-chat-title { font-weight: 700; font-size: 15px; display: block; }
    .fl-chat-subtitle { font-size: 12px; color: var(--fx-muted); display: block; }
    .fl-chat-close { margin-inline-start: auto; border: 0; background: none; font-size: 24px; line-height: 1; cursor: pointer; color: var(--fx-muted); }
    .fl-chat-panel-body { padding: var(--fx-s4); overflow-y: auto; display: flex; flex-direction: column; gap: var(--fx-s3); }
    .fl-chat-thread { display: flex; flex-direction: column; gap: var(--fx-s2); max-height: 240px; overflow-y: auto; }
    .fl-chat-thread img { width: 26px; height: 26px; border-radius: 50%; object-fit: cover; }
    .fl-chat-suggestions { display: flex; flex-direction: column; gap: var(--fx-s1); }
    .fl-chat-suggestion { text-align: start; border: 1px solid var(--fx-border); background: var(--fx-canvas); border-radius: var(--fx-radius-md); padding: var(--fx-s2) var(--fx-s3); font-size: 13px; cursor: pointer; color: var(--fx-text); }
    .fl-chat-suggestion:hover { border-color: var(--fx-navy); }
    .fl-chat-compose { display: flex; gap: var(--fx-s2); align-items: flex-end; }
    .fl-chat-compose textarea { flex: 1; border: 1px solid var(--fx-border); border-radius: var(--fx-radius-md); padding: var(--fx-s2) var(--fx-s3); font: inherit; font-size: 14px; resize: vertical; min-height: 64px; }
    .fl-chat-attach-btn { cursor: pointer; border: 1px solid var(--fx-border); border-radius: var(--fx-radius-md); padding: var(--fx-s2); background: var(--fx-surface); }
    .fl-chat-file-name { font-size: 12px; color: var(--fx-muted); }
    .fl-chat-panel-footer { padding: var(--fx-s4); border-top: 1px solid var(--fx-border); }
    .fl-chat-hint { font-size: 13px; color: var(--fx-muted); }
    .fl-chat-login-hint { font-size: 14px; }

    /* Quote modal */
    #getQuoteModal .modal-content { border-radius: var(--fx-radius-lg); border: 1px solid var(--fx-border); }
    .quote-delivery-options { display: flex; gap: var(--fx-s2); flex-wrap: wrap; }
    .quote-delivery-option { border: 1px solid var(--fx-border); border-radius: var(--fx-radius-pill); background: var(--fx-surface); padding: var(--fx-s2) var(--fx-s3); font-size: 13px; font-weight: 700; cursor: pointer; }
    .quote-delivery-option.is-selected { border-color: var(--fx-navy); background: rgba(32, 63, 96, .08); color: var(--fx-navy); }

    /* Review rating picker */
    .rating-picker { display: flex; flex-wrap: wrap; gap: var(--fx-s2); }
    .rating-picker input { position: absolute; opacity: 0; pointer-events: none; }
    .rating-picker label { display: inline-flex; align-items: center; gap: 5px; min-height: 38px; padding: 8px 13px; border: 1px solid var(--fx-border); border-radius: var(--fx-radius-pill); background: var(--fx-surface); font-size: 13px; font-weight: 700; cursor: pointer; }
    .rating-picker label i { color: var(--fx-amber); font-style: normal; }
    .rating-picker input:checked + label, .rating-picker label:hover { border-color: var(--fx-navy); background: rgba(32, 63, 96, .06); }
</style>
@endpush

@section('content')
@php
    $sellerLastActive = $seller?->updated_at;
    $renderStars = function (int $filled) {
        $html = '<span class="fx-stars" aria-hidden="true">';
        for ($i = 1; $i <= 5; $i++) {
            $html .= '<span class="' . ($i <= $filled ? '' : 'is-empty') . '">★</span>';
        }
        return $html . '</span>';
    };
@endphp
<div class="fx-container" style="padding-top: var(--fx-s6); padding-bottom: var(--fx-s12);">
    <nav aria-label="{{ translate('breadcrumb') }}">
        <ol class="fx-breadcrumb">
            <li><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
            <li><a href="{{ route('hire-freelancer') }}">{{ translate('hire_freelancer') }}</a></li>
            <li aria-current="page">{{ $displayName }}</li>
        </ol>
    </nav>

    <section class="fx-card" aria-labelledby="fx-freelancer-name">
        <div class="fx-profile-hero">
            <img class="fx-profile-avatar" alt="{{ $displayName }}" src="{{ getStorageImages(path: $imageFullUrl, type: 'backend-profile') }}">
            <div style="flex:1; min-width:240px;">
                <h1 class="fx-profile-name" id="fx-freelancer-name">
                    {{ $displayName }}
                    @if($seller->shop?->slug)
                        <span class="fx-profile-handle">{{ '@' . $seller->shop->slug }}</span>
                    @endif
                </h1>
                <p style="margin: var(--fx-s2) 0 0; display:flex; align-items:center; gap: var(--fx-s2); flex-wrap:wrap;">
                    @if($ratingCount > 0)
                        {!! $renderStars((int) round($ratingAvg)) !!}
                        <strong>{{ $ratingAvg }}</strong>
                        <span class="fx-meta">({{ $ratingCount }} {{ translate('reviews') }})</span>
                    @else
                        <span class="fx-meta">{{ translate('no_reviews_yet') }}</span>
                    @endif
                    <span class="fx-badge fx-badge--neutral">{{ $freelancerLevel }}</span>
                </p>
                @if($skills->count())
                    <div style="display:flex; flex-wrap:wrap; gap: var(--fx-s1); margin-top: var(--fx-s3);">
                        @foreach($skills as $skill)
                            <span class="fx-type-tag" style="text-transform:none; letter-spacing:0;">{{ $skill }}</span>
                        @endforeach
                    </div>
                @endif
                <div class="fx-profile-stats">
                    <div class="fx-stat">
                        <div class="fx-stat__value">{{ $jobsCompleted }}</div>
                        <div class="fx-stat__label">{{ translate('jobs_completed') }}</div>
                    </div>
                    @if($activeOrdersCount > 0)
                        <div class="fx-stat">
                            <div class="fx-stat__value">{{ $activeOrdersCount }}</div>
                            <div class="fx-stat__label">{{ translate('orders_in_queue') }}</div>
                        </div>
                    @endif
                    <div class="fx-stat">
                        <div class="fx-stat__value">{{ $seller->created_at?->format('M Y') }}</div>
                        <div class="fx-stat__label">{{ translate('member_since') }}</div>
                    </div>
                    <div class="fx-stat">
                        <div class="fx-stat__value" style="font-size:15px;">{{ $sellerLastActive?->diffForHumans() ?? translate('not_available') }}</div>
                        <div class="fx-stat__label">{{ translate('last_active') }}</div>
                    </div>
                </div>
            </div>
            <div style="display:flex; flex-direction:column; gap: var(--fx-s2);">
                <button type="button" class="fx-btn" id="fl-chat-fab-btn">
                    {{ translate('contact_me') }}
                </button>
            </div>
        </div>
    </section>

    <div class="fx-profile-layout">
        <div style="display:flex; flex-direction:column; gap: var(--fx-s6); min-width:0;">
            <section class="fx-card" aria-labelledby="fx-services-heading">
                <h2 class="fx-section-title" id="fx-services-heading">{{ translate('services') }}</h2>
                @forelse($services as $categoryName => $categoryServices)
                    <h3 style="font-size:15px; font-weight:700; margin: var(--fx-s5) 0 var(--fx-s3);">{{ $categoryName }}</h3>
                    <div class="fx-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
                        @foreach($categoryServices as $service)
                            @php
                                $cover = $service->images->first();
                            @endphp
                            <article class="fx-card fx-service-card">
                                @if($cover)
                                    <div class="fx-service-card__cover">
                                        <img alt="{{ $service->title }}" loading="lazy" src="{{ getStorageImages(path: $cover->image_full_url, type: 'backend-profile') }}">
                                    </div>
                                @endif
                                <div class="service-card-body">
                                    <h4 class="fx-service-card__title">{{ $service->title }}</h4>
                                    @if($service->specialization?->name)
                                        <div><span class="fx-type-tag">{{ $service->specialization->name }}</span></div>
                                    @endif
                                    @if($service->description)
                                        <p class="fx-meta" style="margin:0;">{{ \Illuminate\Support\Str::limit($service->description, 110) }}</p>
                                    @endif
                                    @if($service->enabledPackages->count())
                                        <div style="display:flex; justify-content:space-between; align-items:center; gap: var(--fx-s2);">
                                            <div>
                                                <div class="fx-meta" style="text-transform:uppercase; font-size:11px; letter-spacing:.05em;">{{ translate('from') }}</div>
                                                <div class="fx-price">{{ webCurrencyConverter($service->enabledPackages->min('price') ?? 0) }}</div>
                                            </div>
                                            <button type="button" class="fx-btn fx-btn-outline fx-btn-sm" data-toggle-tiers>{{ translate('more_details') }}</button>
                                        </div>
                                        <div class="fx-tier-grid" data-tier-grid>
                                            @foreach($service->enabledPackages as $package)
                                                <div class="fx-tier-card">
                                                    <div class="fx-tier-card__name">{{ $package->title ?: translate($package->tier) }}</div>
                                                    @if($package->price !== null)
                                                        <div class="fx-tier-card__price">{{ webCurrencyConverter($package->price) }}</div>
                                                    @endif
                                                    @if($package->description)
                                                        <p class="fx-meta" style="margin:0; font-size:13px;">{{ $package->description }}</p>
                                                    @endif
                                                    @if(!empty($package->features))
                                                        <ul class="fx-tier-feature-list">
                                                            @foreach($package->features as $feature)
                                                                <li class="{{ !empty($feature['included']) ? 'is-included' : 'is-excluded' }}">
                                                                    {{ !empty($feature['included']) ? '✓' : '✕' }} {{ $feature['label'] }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                    <p class="fx-meta" style="margin:0; font-size:12px;">
                                                        @if($package->delivery_time_days)
                                                            {{ $package->delivery_time_days }} {{ translate('days_delivery') }}
                                                        @endif
                                                        @if($package->revisions !== null)
                                                            · {{ $package->revisions }} {{ translate('revisions') }}
                                                        @endif
                                                    </p>
                                                    <a href="{{ route('hire.create', $service->id) }}?package={{ $package->tier }}" class="fx-btn fx-btn-sm" style="margin-top:auto;">
                                                        {{ translate('hire') }}
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        @php
                                            $rowTxn = $service->transactionType();
                                        @endphp
                                        <div style="display:flex; justify-content:space-between; align-items:center; gap: var(--fx-s2);">
                                            <div>
                                                @if($service->price !== null)
                                                    <div class="fx-price">{{ webCurrencyConverter($service->price) }}</div>
                                                @elseif($rowTxn === \App\Enums\TransactionType::CustomService)
                                                    <div class="fx-meta" style="font-size:12px;">{{ translate('priced_by_custom_quote') }}</div>
                                                @endif
                                                @if($service->delivery_time_days)
                                                    <div class="fx-meta" style="font-size:12px;">{{ $service->delivery_time_days }} {{ translate('days') }}</div>
                                                @endif
                                            </div>
                                            @if($service->price !== null)
                                                <a href="{{ route('hire.create', $service->id) }}" class="fx-btn fx-btn-sm">
                                                    {{ translate('hire') }}
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="fx-service-actions">
                                        <button type="button" class="fx-btn fx-btn-outline fx-btn-sm get-quote-btn" data-service-id="{{ $service->id }}">
                                            {{ translate('get_a_quote') }}
                                        </button>
                                        <button type="button" class="fx-btn fx-btn-outline fx-btn-sm fl-chat-trigger">
                                            {{ translate('message') }}
                                        </button>
                                        <a href="{{ route('hire-freelancer.service-show', $service->id) }}#reviews" class="fx-btn fx-btn-outline fx-btn-sm">
                                            {{ translate('reviews') }}
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @empty
                    <p class="fx-meta" style="margin:0;">{{ translate('no_services_added_yet') }}</p>
                @endforelse
            </section>

            @if($portfolioItems->count())
                <section class="fx-card" aria-labelledby="fx-portfolio-heading">
                    <h2 class="fx-section-title" id="fx-portfolio-heading">{{ translate('portfolio') }}</h2>
                    <div class="fx-portfolio-grid">
                        @foreach($portfolioItems as $item)
                            @php
                                $itemReviews = $portfolioReviews->get($item->id, collect());
                            @endphp
                            <article class="fx-card fx-portfolio-card">
                                <div class="fx-portfolio-card__cover">
                                    <img alt="{{ $item->title }}" loading="lazy" src="{{ getStorageImages(path: $item->image_full_url, type: 'backend-profile') }}">
                                    @if($item->completed_at)
                                        <span class="fx-badge fx-badge--neutral fx-portfolio-card__date">{{ $item->completed_at->format('F Y') }}</span>
                                    @endif
                                </div>
                                <div class="fx-portfolio-card__body">
                                    <h3 class="fx-portfolio-card__title">{{ $item->title }}</h3>
                                    @if($item->description)
                                        <p class="fx-meta" style="margin:0;">{{ \Illuminate\Support\Str::limit($item->description, 160) }}</p>
                                    @endif
                                    @if(!empty($item->tags))
                                        <div style="display:flex; flex-wrap:wrap; gap: var(--fx-s1);">
                                            @foreach($item->tags as $tag)
                                                <span class="fx-type-tag" style="text-transform:none; letter-spacing:0;">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($item->project_url)
                                        <a href="{{ $item->project_url }}" target="_blank" rel="noopener">{{ translate('view_link') }} ↗</a>
                                    @endif
                                    @if($itemReviews->count())
                                        <p style="margin:0; font-size:14px;">
                                            {!! $renderStars((int) round($itemReviews->avg('rating'))) !!}
                                            <strong>{{ round($itemReviews->avg('rating'), 1) }}</strong>
                                            <span class="fx-meta">({{ $itemReviews->count() }} {{ translate('reviews') }})</span>
                                        </p>
                                    @endif
                                    <div class="fx-service-actions">
                                        <button type="button" class="fx-btn fx-btn-outline fx-btn-sm fl-chat-trigger">
                                            {{ translate('message') }}
                                        </button>
                                        <button type="button" class="fx-btn fx-btn-outline fx-btn-sm" data-bs-toggle="modal" data-toggle="modal"
                                                data-bs-target="#portfolio-review-modal-{{ $item->id }}" data-target="#portfolio-review-modal-{{ $item->id }}">
                                            {{ translate('reviews') }} ({{ $itemReviews->count() }})
                                        </button>
                                    </div>
                                </div>
                                @if($item->galleryItems->count())
                                    <div style="display:flex; gap: var(--fx-s1); padding: 0 var(--fx-s4) var(--fx-s4);">
                                        @foreach($item->galleryItems->take(4) as $galleryItem)
                                            <img alt="" loading="lazy" src="{{ getStorageImages(path: $galleryItem->image_full_url, type: 'backend-profile') }}"
                                                 style="width:64px; height:64px; object-fit:cover; border-radius: var(--fx-radius-sm); border:1px solid var(--fx-border);">
                                        @endforeach
                                    </div>
                                @endif
                            </article>

                            <div class="modal fade" id="portfolio-review-modal-{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ translate('reviews') }} &mdash; {{ $item->title }}</h5>
                                            <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            @auth('customer')
                                                @if($customerReviewedPortfolioIds->contains($item->id))
                                                    <p class="fx-meta">{{ translate('you_have_already_reviewed_this') }}</p>
                                                @else
                                                    <form action="{{ route('hire.reviews.store', ['type' => 'portfolio', 'id' => $item->id]) }}" method="post" class="fx-form" style="margin-bottom: var(--fx-s4);" novalidate>
                                                        @csrf
                                                        <div class="fx-field">
                                                            <label>{{ translate('your_rating') }}</label>
                                                            <div class="rating-picker">
                                                                @for($ratingOption = 5; $ratingOption >= 1; $ratingOption--)
                                                                    <input type="radio" name="rating" id="portfolio-rating-{{ $item->id }}-{{ $ratingOption }}" value="{{ $ratingOption }}" required>
                                                                    <label for="portfolio-rating-{{ $item->id }}-{{ $ratingOption }}"><i>★</i>{{ $ratingOption }}</label>
                                                                @endfor
                                                            </div>
                                                        </div>
                                                        <div class="fx-field">
                                                            <textarea name="body" rows="3" maxlength="2000" placeholder="{{ translate('share_your_experience') }}..."></textarea>
                                                        </div>
                                                        <button type="submit" class="fx-btn fx-btn-sm">{{ translate('submit_review') }}</button>
                                                    </form>
                                                @endif
                                            @endauth
                                            @forelse($itemReviews as $review)
                                                @php
                                                    $reviewerName = trim(($review->customer?->f_name ?? '') . ' ' . ($review->customer?->l_name ?? '')) ?: translate('customer');
                                                @endphp
                                                <div class="fx-card fx-review-card" style="margin-bottom: var(--fx-s3);">
                                                    <div class="fx-review-head">
                                                        <div class="fx-review-avatar">{{ strtoupper(substr($reviewerName, 0, 1)) }}</div>
                                                        <div class="fx-review-author" style="font-weight:700;">{{ $reviewerName }}</div>
                                                    </div>
                                                    <div style="display:flex; align-items:center; gap: var(--fx-s2);">
                                                        {!! $renderStars((int) $review->rating) !!}
                                                        <span class="fx-meta">{{ $review->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    @if($review->body)
                                                        <p style="margin:0;">{{ $review->body }}</p>
                                                    @endif
                                                </div>
                                            @empty
                                                <p class="fx-meta" style="margin:0;">{{ translate('no_reviews_yet') }}</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="fx-card" aria-labelledby="fx-reviews-heading">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap: var(--fx-s3); flex-wrap:wrap; margin-bottom: var(--fx-s4);">
                    <div>
                        <h2 class="fx-section-title" id="fx-reviews-heading" style="margin-bottom: var(--fx-s1);">{{ translate('reviews') }}</h2>
                        @if($reviews->total() > 0)
                            <p class="fx-meta" style="margin:0;">{{ $reviews->firstItem() }}–{{ $reviews->lastItem() }} {{ translate('out_of') }} {{ $reviews->total() }} {{ translate('reviews') }}</p>
                        @endif
                    </div>
                    <div class="fx-chip-group" role="group" aria-label="{{ translate('sort_by') }}">
                        <a class="fx-chip {{ $reviewSort === 'recent' ? 'fx-chip--active' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'recent']) }}">{{ translate('most_recent') }}</a>
                        <a class="fx-chip {{ $reviewSort === 'highest' ? 'fx-chip--active' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'highest']) }}">{{ translate('highest_rated') }}</a>
                    </div>
                </div>
                @forelse($reviews as $review)
                    @php
                        $reviewerName = trim(($review->contract?->customer?->f_name ?? '') . ' ' . ($review->contract?->customer?->l_name ?? '')) ?: translate('customer');
                        $reviewCover = $review->contract?->service?->images->first();
                    @endphp
                    <article class="fx-card fx-review-card" style="margin-bottom: var(--fx-s4);">
                        <div class="fx-review-head">
                            <div class="fx-review-avatar">{{ strtoupper(substr($reviewerName, 0, 1)) }}</div>
                            <div>
                                <div style="font-weight:700;">{{ $reviewerName }}
                                    <span class="fx-badge fx-badge--neutral">{{ translate('verified_client') }}</span>
                                </div>
                                @if($review->contract?->customer?->country)
                                    <div class="fx-meta" style="font-size:13px;">{{ $review->contract->customer->country }}</div>
                                @endif
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap: var(--fx-s2);">
                            {!! $renderStars((int) $review->rating) !!}
                            <span class="fx-meta">{{ $review->created_at->diffForHumans() }}</span>
                        </div>
                        <p style="margin:0;">{{ $review->body }}</p>
                        <div class="fx-review-facts">
                            @if($review->contract?->total_amount)
                                <div>
                                    <div class="fx-review-fact-label">{{ translate('price') }}</div>
                                    <div style="font-weight:700;">{{ webCurrencyConverter($review->contract->total_amount) }}</div>
                                </div>
                            @endif
                            @if($review->contract?->completed_at && $review->contract?->created_at)
                                <div>
                                    <div class="fx-review-fact-label">{{ translate('duration') }}</div>
                                    <div style="font-weight:700;">{{ $review->contract->created_at->diffInDays($review->contract->completed_at) }} {{ translate('days') }}</div>
                                </div>
                            @endif
                            @if($review->contract?->service)
                                <div style="display:flex; align-items:center; gap: var(--fx-s2);">
                                    @if($reviewCover)
                                        <img alt="" loading="lazy" src="{{ getStorageImages(path: $reviewCover->image_full_url, type: 'backend-profile') }}"
                                             style="width:44px; height:44px; object-fit:cover; border-radius: var(--fx-radius-sm); border:1px solid var(--fx-border);">
                                    @endif
                                    <span style="font-weight:600; font-size:14px;">{{ $review->contract->service->title }}</span>
                                </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="fx-meta" style="margin:0;">{{ translate('no_reviews_yet') }}</p>
                @endforelse
                <div style="display:flex; justify-content:center;">
                    @include('web-views.partials._fx-pagination', ['paginator' => $reviews])
                </div>
            </section>
        </div>

        <aside>
            <div class="fx-card" style="position: sticky; top: 90px; display:flex; flex-direction:column; gap: var(--fx-s3); align-items:center; text-align:center;">
                <img class="fx-profile-avatar" alt="{{ $displayName }}" src="{{ getStorageImages(path: $imageFullUrl, type: 'backend-profile') }}">
                <div>
                    <div style="font-weight:700; font-size:17px;">{{ $displayName }}</div>
                    @if($activeOrdersCount > 0)
                        <div class="fx-meta" style="font-size:13px;">{{ $activeOrdersCount }} {{ translate('orders_in_queue') }}</div>
                    @endif
                </div>
                <button type="button" class="fx-btn" id="fl-chat-fab-btn-sidebar" style="width:100%;">
                    {{ translate('contact_me') }}
                </button>
            </div>
        </aside>
    </div>
</div>

<button type="button" class="fl-chat-fab" id="fl-chat-fab">
    <img alt="" src="{{ getStorageImages(path: $imageFullUrl, type: 'backend-profile') }}">
    <span class="fl-chat-fab-text">{{ translate('message') }} {{ $displayName }}</span>
    @auth('customer')
        <span class="fl-chat-fab-badge" id="fl-chat-fab-badge"></span>
    @endauth
</button>

<div class="fl-chat-panel" id="fl-chat-panel" data-seller-id="{{ $seller->id }}" role="dialog" aria-label="{{ translate('message') }} {{ $displayName }}">
    <div class="fl-chat-panel-header">
        <img alt="" src="{{ getStorageImages(path: $imageFullUrl, type: 'backend-profile') }}">
        <span>
            <span class="fl-chat-title">{{ translate('message') }} {{ $displayName }}</span>
            <span class="fl-chat-subtitle">{{ translate('last_active') }} {{ $sellerLastActive?->diffForHumans() ?? translate('not_available') }}</span>
        </span>
        <button type="button" class="fl-chat-close" id="fl-chat-close" aria-label="{{ translate('close') }}">&times;</button>
    </div>
    <div class="fl-chat-panel-body">
        @auth('customer')
            <div class="fl-chat-thread" id="fl-chat-thread"></div>
            <div class="fl-chat-hint">{{ translate('ask') }} {{ $displayName }} {{ translate('a_question_or_share_your_project_details') }}</div>
            <div class="fl-chat-suggestions">
                <button type="button" class="fl-chat-suggestion" data-suggestion="{{ translate('hey') }} {{ $displayName }}, {{ translate('can_you_help_me_with') }}...">
                    💬 {{ translate('hey') }} {{ $displayName }}, {{ translate('can_you_help_me_with') }}...
                </button>
                <button type="button" class="fl-chat-suggestion" data-suggestion="{{ translate('can_you_provide_your_rate_for') }}...">
                    💰 {{ translate('can_you_provide_your_rate_for') }}...
                </button>
                <button type="button" class="fl-chat-suggestion" data-suggestion="{{ translate('do_you_think_you_can_deliver_an_order_by') }}...">
                    ⏱ {{ translate('do_you_think_you_can_deliver_an_order_by') }}...
                </button>
            </div>
            <form action="{{ route('messages') }}" method="post" id="fl-chat-form" enctype="multipart/form-data" novalidate>
                @csrf
                <input value="{{ $seller->id }}" name="vendor_id" hidden>
                <div class="fl-chat-compose">
                    <label class="fl-chat-attach-btn" for="fl-chat-file" title="{{ translate('file') }}">
                        <svg width="20" height="18" viewBox="0 0 20 18" fill="none" aria-hidden="true">
                            <path d="M5.6 17.3C4.7 17.3 3.7 17 3 16.5c-.8-.5-1.4-1.3-1.8-2.2-.4-.9-.5-1.8-.3-2.8.2-.9.6-1.8 1.3-2.4L9.3 2c.1-.1.3-.2.4-.2s.3.1.4.2c.1.1.2.3.2.4s-.1.3-.2.4L3.1 10c-.3.3-.6.7-.8 1.1-.2.4-.3.9-.3 1.4s.1 1 .3 1.4c.2.4.4.8.8 1.1.3.4.7.6 1.2.8.4.2.9.3 1.4.3.5 0 1-.1 1.4-.3.4-.2.8-.5 1.1-.8l9.2-9.1c.4-.5.6-1 .6-1.6s-.3-1.2-.7-1.6-1-.7-1.6-.7-1.2.2-1.6.6L6.6 10c-.2.2-.3.4-.3.7s.1.6.3.7c.2.2.5.3.7.3s.6-.1.7-.3l4.7-4.7c.1-.1.2-.1.3-.1s.2 0 .3.1c.1 0 .1.1.2.2 0 .1.1.1.1.2s0 .2-.1.3l-4.7 4.7c-.4.4-1 .6-1.6.6s-1.2-.2-1.6-.6-.6-1-.6-1.6.2-1.2.7-1.6l7.3-7.4c.7-.7 1.6-1 2.5-1s1.9.3 2.5 1c.7.6 1 1.5 1 2.5s-.3 1.8-1 2.5l-9.1 9.1c-.5.5-1 1-1.6 1.2-.6.2-1.2.4-1.9.4z" fill="#203f60"/>
                        </svg>
                    </label>
                    <input type="file" class="d-none" id="fl-chat-file" name="file[]" accept=".doc,.docx,.txt,.csv,.xls,.xlsx,.rar,.tar,.zip,.pdf">
                    <textarea name="message" id="fl-chat-message" rows="2" placeholder="{{ translate('Write_here') }}..."></textarea>
                </div>
                <div class="fl-chat-file-name d-none" id="fl-chat-file-name"></div>
            </form>
        @else
            <div class="fl-chat-login-hint">
                {{ translate('please_login_as_a_customer_to_message') }} {{ $displayName }}.
            </div>
            <a href="{{ route('customer.auth.login') }}" class="fx-btn fx-btn-sm" style="width:100%;">{{ translate('login_to_continue') }}</a>
        @endauth
    </div>
    <div class="fl-chat-panel-footer">
        @auth('customer')
            <button type="submit" form="fl-chat-form" class="fx-btn fx-btn-sm" style="width:100%;">{{ translate('send_message') }}</button>
        @endauth
    </div>
</div>

<div class="modal fade" id="getQuoteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ translate('request_a_quote') }}</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="post" enctype="multipart/form-data" id="get-quote-form" class="fx-form" novalidate>
                    @csrf
                    <div class="fx-field">
                        <label>{{ translate('describe_the_service_you_are_looking_to_purchase') }}</label>
                        <textarea name="description" rows="4" maxlength="2500" required
                                  placeholder="{{ translate('i_am_looking_for') }}..."></textarea>
                    </div>
                    <div class="fx-field">
                        <label>{{ translate('attach_files') }}</label>
                        <input type="file" name="attachments[]" multiple
                               accept=".jpg,.jpeg,.png,.pdf,.webp,.doc,.docx,.zip">
                        <span class="fx-hint">{{ translate('accepted_formats') }}: JPG, PNG, PDF, WebP, DOC, DOCX, ZIP · {{ translate('max') }} 10MB {{ translate('each') }}</span>
                    </div>
                    <div class="fx-field">
                        <label>{{ translate('when_would_you_like_your_service_delivered') }}</label>
                        <div class="quote-delivery-options">
                            <div class="quote-delivery-option" data-value="24_hours">{{ translate('24_hours') }}</div>
                            <div class="quote-delivery-option" data-value="3_days">{{ translate('3_days') }}</div>
                            <div class="quote-delivery-option" data-value="7_days">{{ translate('7_days') }}</div>
                            <div class="quote-delivery-option" data-value="custom">{{ translate('other') }}</div>
                        </div>
                        <input type="hidden" name="delivery_preference" id="quote-delivery-preference" required>
                        <input type="text" name="custom_delivery_text" id="quote-custom-delivery" class="d-none"
                               placeholder="{{ translate('describe_your_preferred_timeline') }}" maxlength="100">
                    </div>
                    <div class="fx-field">
                        <label>{{ translate('what_is_your_budget_for_this_service') }}</label>
                        <input type="number" step="0.01" min="0" name="budget">
                    </div>
                    <div style="display:flex; justify-content:flex-end;">
                        <button type="submit" class="fx-btn">{{ translate('submit_request') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
    (function () {
        const quoteModal = document.getElementById('getQuoteModal');
        const quoteForm = document.getElementById('get-quote-form');
        const deliveryOptions = document.querySelectorAll('.quote-delivery-option');
        const deliveryInput = document.getElementById('quote-delivery-preference');
        const customDeliveryInput = document.getElementById('quote-custom-delivery');

        document.querySelectorAll('.get-quote-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                quoteForm.action = '{{ url('hire/quotes') }}/' + button.dataset.serviceId;
                $(quoteModal).modal('show');
            });
        });

        deliveryOptions.forEach(function (option) {
            option.addEventListener('click', function () {
                deliveryOptions.forEach(function (o) { o.classList.remove('is-selected'); });
                option.classList.add('is-selected');
                deliveryInput.value = option.dataset.value;
                customDeliveryInput.classList.toggle('d-none', option.dataset.value !== 'custom');
            });
        });

        $(quoteForm).on('submit', function (e) {
            e.preventDefault();

            if (typeof FormValidators !== 'undefined' && !FormValidators.autoValidateForm(quoteForm)) {
                return;
            }

            var submitBtn = quoteForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            $.ajax({
                url: quoteForm.action,
                method: 'POST',
                data: new FormData(quoteForm),
                processData: false,
                contentType: false,
            }).done(function () {
                toastr.success('{{ translate('your_quote_request_has_been_sent') }}');
                $(quoteModal).modal('hide');
                quoteForm.reset();
                deliveryOptions.forEach(function (o) { o.classList.remove('is-selected'); });
                customDeliveryInput.classList.add('d-none');
            }).fail(function (xhr) {
                var message = xhr.responseJSON?.errors
                    ? Object.values(xhr.responseJSON.errors)[0][0]
                    : '{{ translate('something_went_wrong') }}';
                toastr.error(message);
            }).always(function () {
                submitBtn.disabled = false;
            });
        });

        document.querySelectorAll('[data-toggle-tiers]').forEach(function (button) {
            button.addEventListener('click', function () {
                const grid = button.closest('.service-card-body').querySelector('[data-tier-grid]');
                grid?.classList.toggle('is-open');
                button.textContent = grid?.classList.contains('is-open') ? '{{ translate('hide_details') }}' : '{{ translate('more_details') }}';
            });
        });
    })();
</script>
@endpush

@push('script')
<script>
    (function () {
        const fab = document.getElementById('fl-chat-fab');
        const panel = document.getElementById('fl-chat-panel');
        const fabBtn = document.getElementById('fl-chat-fab-btn');
        const fabBtnSidebar = document.getElementById('fl-chat-fab-btn-sidebar');
        const closeBtn = document.getElementById('fl-chat-close');
        const textarea = document.getElementById('fl-chat-message');
        const chatForm = document.getElementById('fl-chat-form');
        const chatThread = document.getElementById('fl-chat-thread');
        const fabBadge = document.getElementById('fl-chat-fab-badge');
        const fileInput = document.getElementById('fl-chat-file');
        const fileName = document.getElementById('fl-chat-file-name');
        const maxChatFileSize = 2 * 1024 * 1024;
        const chatSellerId = panel.dataset.sellerId;
        let chatPollTimer = null;
        let unreadPollTimer = null;

        function loadChatThread() {
            if (!chatThread) return;
            $.get('{{ route('messages') }}', { vendor_id: chatSellerId }).done(function (response) {
                if (response && typeof response.chattingMessages === 'string') {
                    chatThread.innerHTML = response.chattingMessages;
                    normalizeFloatingChatOrder();
                    chatThread.scrollTop = chatThread.scrollHeight;
                }
            });
        }

        function normalizeFloatingChatOrder() {
            if (!chatThread) return;
            Array.from(chatThread.children)
                .sort(function (a, b) {
                    return parseInt(a.dataset.messageId || '0', 10) - parseInt(b.dataset.messageId || '0', 10);
                })
                .forEach(function (item) { chatThread.appendChild(item); });
        }

        function refreshUnreadBadge() {
            if (!fabBadge || panel.classList.contains('is-open')) return;
            $.get('{{ route('messages.unread-count') }}', { vendor_id: chatSellerId }).done(function (response) {
                var count = response && response.count ? parseInt(response.count, 10) : 0;
                if (count > 0) {
                    fabBadge.textContent = count > 9 ? '9+' : count;
                    fabBadge.classList.add('is-visible');
                } else {
                    fabBadge.classList.remove('is-visible');
                }
            });
        }

        function clearUnreadBadge() {
            if (fabBadge) fabBadge.classList.remove('is-visible');
        }

        function openPanel() {
            panel.classList.add('is-open');
            fab.style.display = 'none';
            textarea?.focus();
            clearUnreadBadge();
            loadChatThread();
            if (chatThread && !chatPollTimer) {
                chatPollTimer = setInterval(loadChatThread, 8000);
            }
        }

        function closePanel() {
            panel.classList.remove('is-open');
            fab.style.display = 'flex';
            if (chatPollTimer) {
                clearInterval(chatPollTimer);
                chatPollTimer = null;
            }
        }

        fab?.addEventListener('click', openPanel);
        fabBtn?.addEventListener('click', openPanel);
        fabBtnSidebar?.addEventListener('click', openPanel);
        closeBtn?.addEventListener('click', closePanel);

        document.querySelectorAll('.fl-chat-trigger').forEach(function (button) {
            button.addEventListener('click', openPanel);
        });

        document.querySelectorAll('.fl-chat-suggestion').forEach(function (button) {
            button.addEventListener('click', function () {
                textarea.value = button.dataset.suggestion.replace(/\.\.\.$/, ' ');
                textarea.focus();
            });
        });

        fileInput?.addEventListener('change', function () {
            var file = this.files && this.files[0];
            if (!file) { fileName?.classList.add('d-none'); return; }
            if (file.size > maxChatFileSize) {
                toastr.error('{{ translate('file_maximum_size_') }}2 MB');
                this.value = '';
                fileName?.classList.add('d-none');
                return;
            }
            if (fileName) { fileName.textContent = file.name; fileName.classList.remove('d-none'); }
        });
        if (fabBadge) {
            refreshUnreadBadge();
            unreadPollTimer = setInterval(refreshUnreadBadge, 20000);
        }

        if (chatForm) {
            $(chatForm).on('submit', function (e) {
                e.preventDefault();
                var submitBtn = document.querySelector('button[form="fl-chat-form"]');
                var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                if (!textarea.value.trim() && !hasFile) {
                    textarea.focus();
                    return;
                }
                if (hasFile && fileInput.files[0].size > maxChatFileSize) {
                    toastr.error('{{ translate('file_maximum_size_') }}2 MB');
                    return;
                }
                submitBtn.disabled = true;
                $.ajax({
                    url: chatForm.action,
                    method: 'POST',
                    data: new FormData(chatForm),
                    processData: false,
                    contentType: false,
                }).done(function (response) {
                    textarea.value = '';
                    if (fileInput) fileInput.value = '';
                    if (fileName) { fileName.textContent = ''; fileName.classList.add('d-none'); }
                    if (chatThread && response && typeof response.chattingMessages === 'string') {
                        chatThread.innerHTML = response.chattingMessages;
                        normalizeFloatingChatOrder();
                        chatThread.scrollTop = chatThread.scrollHeight;
                    }
                }).fail(function (xhr) {
                    var message = xhr.responseJSON?.errors?.[0]?.message
                        || '{{ translate('something_went_wrong') }}';
                    toastr.error(message);
                }).always(function () {
                    submitBtn.disabled = false;
                });
            });
        }
    })();
</script>
@endpush
@endsection
