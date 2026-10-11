@extends('layouts.front-end.app')

@section('title', $service->title . ' — ' . translate('hire_freelancer'))

@push('css_or_js')
<style>
    /* Service detail — fx light-first skin.
       JS hooks preserved: #gig-gallery-main, #gig-gallery-prev, #gig-gallery-next,
       #gig-thumb-strip, .is-active, [data-tab-index], [data-tab-panel],
       .gig-quote-trigger, #gig-contact-btn, #inline-chat-message, #inline-chat-send-btn,
       #getQuoteModal, #get-quote-form, .quote-delivery-option, #quote-delivery-preference,
       #quote-custom-delivery, #fl-chat-fab, #fl-chat-panel, #fl-chat-fab-btn,
       #fl-chat-close, #fl-chat-message, #fl-chat-form, #fl-chat-thread,
       #fl-chat-fab-badge, #fl-chat-file, #fl-chat-file-name */
    .fx-gig-layout { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: var(--fx-s6); align-items: start; }
    @media (max-width: 991px) { .fx-gig-layout { grid-template-columns: 1fr; } }
    .fx-gig-seller { display: flex; gap: var(--fx-s3); align-items: center; }
    .fx-gig-seller img { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 2px solid var(--fx-border); }
    .fx-gig-gallery-main { width: 100%; aspect-ratio: 16 / 9; object-fit: cover; border-radius: var(--fx-radius-md); background: var(--fx-canvas); display: block; }
    .fx-gig-gallery-wrap { position: relative; }
    .gig-gallery-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 50%; border: 1px solid var(--fx-border); background: var(--fx-surface); color: var(--fx-navy); cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .gig-gallery-nav.prev { inset-inline-start: var(--fx-s3); }
    .gig-gallery-nav.next { inset-inline-end: var(--fx-s3); }
    #gig-thumb-strip { display: flex; gap: var(--fx-s2); margin-top: var(--fx-s3); overflow-x: auto; }
    #gig-thumb-strip img { width: 88px; height: 60px; object-fit: cover; border-radius: var(--fx-radius-sm); border: 2px solid transparent; cursor: pointer; }
    #gig-thumb-strip img.is-active { border-color: var(--fx-navy); }
    .fx-loved-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: var(--fx-s4); }
    .fx-pricing-tabs { display: flex; gap: var(--fx-s1); margin-bottom: var(--fx-s4); flex-wrap: wrap; }
    .gig-pricing-tab { border: 1px solid var(--fx-border); background: var(--fx-canvas); border-radius: var(--fx-radius-md); padding: var(--fx-s2) var(--fx-s3); font-size: 13px; font-weight: 700; cursor: pointer; color: var(--fx-muted); }
    .gig-pricing-tab.is-active { border-color: var(--fx-navy); background: var(--fx-navy); color: #fff; }
    .fx-tier-feature-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: var(--fx-s1); font-size: 14px; }
    .fx-tier-feature-list .is-included { color: var(--fx-text); }
    .fx-tier-feature-list .is-excluded { color: var(--fx-muted); text-decoration: line-through; }
    .fx-pricing-meta { display: flex; gap: var(--fx-s3); flex-wrap: wrap; font-size: 13px; color: var(--fx-muted); }
    .fx-help-option { display: flex; align-items: center; gap: var(--fx-s3); width: 100%; text-align: start; border: 1px solid var(--fx-border); background: var(--fx-surface); border-radius: var(--fx-radius-md); padding: var(--fx-s3) var(--fx-s4); cursor: pointer; font: inherit; font-size: 14px; font-weight: 600; color: var(--fx-text); }
    .fx-help-option:hover { border-color: var(--fx-navy); }
    .fx-help-option .fx-help-arrow { margin-inline-start: auto; color: var(--fx-muted); }
    /* Reuse shared fx styles from profile page for chat + quote modal */
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
    .fl-chat-compose { display: flex; gap: var(--fx-s2); align-items: flex-end; }
    .fl-chat-compose textarea { flex: 1; border: 1px solid var(--fx-border); border-radius: var(--fx-radius-md); padding: var(--fx-s2) var(--fx-s3); font: inherit; font-size: 14px; resize: vertical; min-height: 64px; }
    .fl-chat-attach-btn { cursor: pointer; border: 1px solid var(--fx-border); border-radius: var(--fx-radius-md); padding: var(--fx-s2); background: var(--fx-surface); }
    .fl-chat-file-name { font-size: 12px; color: var(--fx-muted); }
    .fl-chat-panel-footer { padding: var(--fx-s4); border-top: 1px solid var(--fx-border); }
    #getQuoteModal .modal-content { border-radius: var(--fx-radius-lg); border: 1px solid var(--fx-border); }
    .quote-delivery-options { display: flex; gap: var(--fx-s2); flex-wrap: wrap; }
    .quote-delivery-option { border: 1px solid var(--fx-border); border-radius: var(--fx-radius-pill); background: var(--fx-surface); padding: var(--fx-s2) var(--fx-s3); font-size: 13px; font-weight: 700; cursor: pointer; }
    .quote-delivery-option.is-selected { border-color: var(--fx-navy); background: rgba(32, 63, 96, .08); color: var(--fx-navy); }
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
        $html = '<span class="fx-stars" aria-hidden="true" style="color:var(--fx-amber); letter-spacing:.1em;">';
        for ($i = 1; $i <= 5; $i++) {
            $html .= '<span style="' . ($i <= $filled ? '' : 'color:var(--fx-border);') . '">★</span>';
        }
        return $html . '</span>';
    };
@endphp
<div class="fx-container" style="padding-top: var(--fx-s6); padding-bottom: var(--fx-s12);">
    <nav aria-label="{{ translate('breadcrumb') }}">
        <ol class="fx-breadcrumb">
            <li><a href="{{ route('hire-freelancer') }}">{{ translate('hire_freelancer') }}</a></li>
            @if($service->category)
                <li><a href="{{ route('hire-freelancer', ['category' => $service->category->slug]) }}">{{ $service->category->name }}</a></li>
            @endif
            @if($service->specialization)
                <li><a href="{{ route('hire-freelancer', ['specialization' => $service->specialization->slug]) }}">{{ $service->specialization->name }}</a></li>
            @endif
            <li aria-current="page">{{ \Illuminate\Support\Str::limit($service->title, 60) }}</li>
        </ol>
    </nav>

    <div class="fx-gig-layout">
        <div style="display:flex; flex-direction:column; gap: var(--fx-s6); min-width:0;">
            <h1 style="font-size:30px; font-weight:700; letter-spacing:-0.02em; margin:0; line-height:1.2;">{{ $service->title }}</h1>

            <div class="fx-card">
                <div class="fx-gig-seller">
                    <a href="{{ route('hire-freelancer.show', $seller->id) }}" title="{{ $displayName }}">
                        <img alt="{{ $displayName }}" src="{{ getStorageImages(path: $imageFullUrl, type: 'backend-profile') }}">
                    </a>
                    <div>
                        <a href="{{ route('hire-freelancer.show', $seller->id) }}" style="font-weight:700; font-size:16px; color:inherit; text-decoration:none;">{{ $displayName }}</a>
                        <div style="display:flex; align-items:center; gap: var(--fx-s2); flex-wrap:wrap; margin-top: var(--fx-s1);">
                            <span class="fx-badge fx-badge--neutral">{{ $freelancerLevel }}</span>
                            @if($activeOrdersCount > 0)
                                <span class="fx-meta" style="font-size:13px;">{{ $activeOrdersCount }} {{ translate('orders_in_queue') }}</span>
                            @endif
                            @if($ratingCount > 0)
                                <span style="font-size:13px;">{!! $renderStars((int) round($ratingAvg)) !!} <strong>{{ $ratingAvg }}</strong> <span class="fx-meta">({{ $ratingCount }} {{ translate('reviews') }})</span></span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($service->images->count())
                <div>
                    <div class="fx-gig-gallery-wrap">
                        <img class="fx-gig-gallery-main" id="gig-gallery-main" alt="{{ $service->title }}"
                             src="{{ getStorageImages(path: $service->images->first()->image_full_url, type: 'backend-profile') }}">
                        @if($service->images->count() > 1)
                            <button type="button" class="gig-gallery-nav prev" id="gig-gallery-prev" aria-label="{{ translate('previous_image') }}">‹</button>
                            <button type="button" class="gig-gallery-nav next" id="gig-gallery-next" aria-label="{{ translate('next_image') }}">›</button>
                        @endif
                    </div>
                    @if($service->images->count() > 1)
                        <div id="gig-thumb-strip" role="list">
                            @foreach($service->images as $index => $image)
                                <img alt="" role="listitem" data-index="{{ $index }}" class="{{ $index === 0 ? 'is-active' : '' }}"
                                     src="{{ getStorageImages(path: $image->image_full_url, type: 'backend-profile') }}">
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            @if($service->description)
                <section class="fx-card" aria-labelledby="fx-about-gig">
                    <h2 class="fx-section-title" id="fx-about-gig">{{ translate('about_this_gig') }}</h2>
                    <p style="margin:0; white-space:pre-line;">{{ $service->description }}</p>
                </section>
            @endif

            @php
                $lovedReviews = $reviews->sortByDesc('rating')->take(3);
            @endphp
            @if($lovedReviews->count())
                <section class="fx-card" aria-labelledby="fx-loved">
                    <h2 class="fx-section-title" id="fx-loved">{{ translate('what_people_loved_about_this_freelancer') }}</h2>
                    <div class="fx-loved-grid">
                        @foreach($lovedReviews as $review)
                            <article class="fx-card" style="background:var(--fx-canvas);">
                                <div style="display:flex; align-items:center; gap: var(--fx-s2); margin-bottom: var(--fx-s2);">
                                    <span class="fx-avatar fx-avatar--sm">{{ strtoupper(substr($review->reviewer_name, 0, 1)) }}</span>
                                    <div>
                                        <div style="font-weight:700; font-size:14px;">{{ $review->reviewer_name }}</div>
                                        <div>{!! $renderStars((int) $review->rating) !!}</div>
                                    </div>
                                </div>
                                @if($review->body)
                                    <p style="margin:0; font-size:14px;">{{ $review->body }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="fx-card" id="reviews" aria-labelledby="fx-reviews-heading">
                <h2 class="fx-section-title" id="fx-reviews-heading">{{ translate('reviews') }} ({{ $reviews->total() }})</h2>

                @auth('customer')
                    @if($customerReviewedService)
                        <p class="fx-meta">{{ translate('you_have_already_reviewed_this_service') }}</p>
                    @else
                        <form action="{{ route('hire.reviews.store', ['type' => 'service', 'id' => $service->id]) }}" method="post" class="fx-form" style="margin-bottom: var(--fx-s5);" novalidate>
                            @csrf
                            <div class="fx-field">
                                <label>{{ translate('your_rating') }}</label>
                                <div class="rating-picker">
                                    @for($ratingOption = 5; $ratingOption >= 1; $ratingOption--)
                                        <input type="radio" name="rating" id="service-rating-{{ $ratingOption }}" value="{{ $ratingOption }}" required>
                                        <label for="service-rating-{{ $ratingOption }}"><i>★</i>{{ $ratingOption }}</label>
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

                @forelse($reviews as $review)
                    <article class="fx-card" style="margin-bottom: var(--fx-s4);">
                        <div style="display:flex; align-items:center; gap: var(--fx-s2); margin-bottom: var(--fx-s2);">
                            <span class="fx-avatar fx-avatar--sm">{{ strtoupper(substr($review->reviewer_name, 0, 1)) }}</span>
                            <div>
                                <div style="font-weight:700;">
                                    {{ $review->reviewer_name }}
                                    @if($review->verified)
                                        <span class="fx-badge fx-badge--neutral">{{ translate('verified_client') }}</span>
                                    @endif
                                </div>
                                <div style="display:flex; align-items:center; gap: var(--fx-s2);">
                                    {!! $renderStars((int) $review->rating) !!}
                                    <span class="fx-meta" style="font-size:13px;">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        <p style="margin:0;">{{ $review->body }}</p>
                    </article>
                @empty
                    <p class="fx-meta" style="margin:0;">{{ translate('no_reviews_yet') }}</p>
                @endforelse
                <div style="display:flex; justify-content:center;">
                    @include('web-views.partials._fx-pagination', ['paginator' => $reviews])
                </div>
            </section>

            <section class="fx-card" aria-labelledby="fx-message-heading">
                <div style="display:flex; gap: var(--fx-s3); align-items:center; margin-bottom: var(--fx-s3);">
                    <img alt="{{ $displayName }}" src="{{ getStorageImages(path: $imageFullUrl, type: 'backend-profile') }}"
                         style="width:48px; height:48px; border-radius:50%; object-fit:cover; border:1px solid var(--fx-border);">
                    <div>
                        <h2 class="fx-section-title" id="fx-message-heading" style="margin-bottom:2px;">{{ translate('send_a_message_to') }} {{ $displayName }}</h2>
                        <p class="fx-meta" style="margin:0; font-size:13px;">{{ translate('have_a_question_about_this_service_send_a_message_to') }} {{ $displayName }}</p>
                    </div>
                </div>
                @auth('customer')
                    <div class="fx-form">
                        <div class="fx-field">
                            <textarea id="inline-chat-message" rows="4" placeholder="{{ translate('Write_here') }}..."></textarea>
                        </div>
                        <div style="display:flex; justify-content:flex-end;">
                            <button type="button" class="fx-btn" id="inline-chat-send-btn">
                                {{ translate('send_message') }} →
                            </button>
                        </div>
                    </div>
                @else
                    <p class="fx-meta">{{ translate('please_login_as_a_customer_to_message') }} {{ $displayName }}.</p>
                    <a href="{{ route('customer.auth.login') }}" class="fx-btn">{{ translate('login_to_continue') }}</a>
                @endauth
            </section>
        </div>

        <aside style="display:flex; flex-direction:column; gap: var(--fx-s5);">
            @php
                $enabledPackages = $service->packages->where('is_enabled', true)->whereNotNull('price')->values();
                $isCustomService = $service->transactionType() === \App\Enums\TransactionType::CustomService;
            @endphp
            <div class="fx-card" style="position: sticky; top: 90px;">
                <h3 style="font-size:17px; font-weight:700; margin:0 0 var(--fx-s1);">{{ translate('choose_how_to_start') }}</h3>
                <p class="fx-meta" style="margin:0 0 var(--fx-s4); font-size:13px;">{{ $isCustomService ? translate('this_service_is_priced_by_custom_quote') : translate('buy_a_package_or_request_a_custom_quote') }}</p>

                @if($enabledPackages->count())
                    <div class="fx-pricing-tabs" role="tablist">
                        @foreach($enabledPackages as $index => $package)
                            <button type="button" class="gig-pricing-tab {{ $index === 0 ? 'is-active' : '' }}" data-tab-index="{{ $index }}" role="tab" aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                {{ translate($package->tier) }}
                            </button>
                        @endforeach
                    </div>
                    @foreach($enabledPackages as $index => $package)
                        <div data-tab-panel="{{ $index }}" style="{{ $index === 0 ? '' : 'display:none' }}">
                            <div class="fx-meta" style="font-size:12px; text-transform:uppercase; letter-spacing:.05em;">{{ $package->title ?: translate($package->tier) }}</div>
                            <div class="fx-price" style="font-size:26px; margin: var(--fx-s1) 0 var(--fx-s2);">{{ webCurrencyConverter($package->price) }}</div>
                            @if($package->description)
                                <p class="fx-meta" style="font-size:14px;">{{ $package->description }}</p>
                            @endif
                            @if(!empty($package->features))
                                <ul class="fx-tier-feature-list" style="margin-bottom: var(--fx-s3);">
                                    @foreach($package->features as $feature)
                                        <li class="{{ !empty($feature['included']) ? 'is-included' : 'is-excluded' }}">
                                            {{ !empty($feature['included']) ? '✓' : '✕' }} {{ $feature['label'] }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            <div class="fx-pricing-meta" style="margin-bottom: var(--fx-s4);">
                                @if($package->delivery_time_days)
                                    <span>{{ $package->delivery_time_days }} {{ translate('day_delivery') }}</span>
                                @endif
                                @if($package->revisions !== null)
                                    <span>{{ $package->revisions }} {{ translate('revisions') }}</span>
                                @endif
                            </div>
                            <a href="{{ route('hire.create', $service->id) }}?package={{ $package->tier }}" class="fx-btn" style="width:100%;">
                                {{ translate('buy_now') }} →
                            </a>
                        </div>
                    @endforeach
                @elseif($service->price !== null)
                    <div>
                        <div class="fx-meta" style="font-size:12px; text-transform:uppercase; letter-spacing:.05em;">{{ translate('fixed_price') }}</div>
                        <div class="fx-price" style="font-size:26px; margin: var(--fx-s1) 0 var(--fx-s2);">{{ webCurrencyConverter($service->price) }}</div>
                        @if($service->delivery_time_days)
                            <div class="fx-pricing-meta" style="margin-bottom: var(--fx-s4);">
                                <span>{{ $service->delivery_time_days }} {{ translate('day_delivery') }}</span>
                            </div>
                        @endif
                        <a href="{{ route('hire.create', $service->id) }}" class="fx-btn" style="width:100%;">
                            {{ translate('buy_now') }} →
                        </a>
                    </div>
                @elseif($isCustomService)
                    <p class="fx-meta">{{ translate('tell_the_freelancer_what_you_need_and_you_will_receive_a_custom_price') }}</p>
                @else
                    <p class="fx-meta">{{ translate('package_prices_are_not_configured_yet') }}</p>
                @endif

                <div class="fx-card" style="background:var(--fx-canvas); margin-top: var(--fx-s5);">
                    <div style="font-weight:700; margin-bottom: var(--fx-s1);">{{ translate('request_a_custom_quote') }}</div>
                    <p class="fx-meta" style="font-size:13px; margin:0 0 var(--fx-s3);">{{ translate('share_your_requirements_and_get_a_custom_price_from_the_freelancer') }}</p>
                    <button type="button" class="fx-btn fx-btn-outline gig-quote-trigger" style="width:100%;">
                        {{ translate('request_a_quote') }} →
                    </button>
                </div>
            </div>

            <div class="fx-card">
                <div style="font-weight:700; margin-bottom: var(--fx-s3);">{{ translate('how_can_i_help') }}</div>
                <div style="display:flex; flex-direction:column; gap: var(--fx-s2);">
                    <button type="button" class="fx-help-option" id="gig-contact-btn">
                        <span>{{ translate('ask_a_question') }}</span>
                        <span class="fx-help-arrow">›</span>
                    </button>
                    <button type="button" class="fx-help-option gig-quote-trigger">
                        <span>{{ translate('request_a_quote') }}</span>
                        <span class="fx-help-arrow">›</span>
                    </button>
                </div>
            </div>

            @if($otherServices->count())
                <div class="fx-card">
                    <h3 style="font-size:16px; font-weight:700; margin:0 0 var(--fx-s3);">{{ translate('more_from_this_freelancer') }}</h3>
                    <div style="display:flex; flex-direction:column; gap: var(--fx-s2);">
                        @foreach($otherServices as $other)
                            <a href="{{ route('hire-freelancer.service-show', $other->id) }}" style="display:flex; align-items:center; gap: var(--fx-s2); text-decoration:none; color:inherit;">
                                @if($other->images->first())
                                    <img src="{{ getStorageImages(path: $other->images->first()->image_full_url, type: 'backend-profile') }}" alt=""
                                         style="width:64px; height:48px; object-fit:cover; border-radius: var(--fx-radius-sm); border:1px solid var(--fx-border);">
                                @endif
                                <span style="font-size:14px; font-weight:600;">{{ \Illuminate\Support\Str::limit($other->title, 60) }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
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
            <div class="fl-chat-login-hint fx-meta">
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
                <form action="{{ route('hire.quotes.store', $service->id) }}" method="post" enctype="multipart/form-data" id="get-quote-form" class="fx-form" novalidate>
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
        const fab = document.getElementById('fl-chat-fab');
        const panel = document.getElementById('fl-chat-panel');
        const closeBtn = document.getElementById('fl-chat-close');
        const chatForm = document.getElementById('fl-chat-form');
        const textarea = document.getElementById('fl-chat-message');
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

        function openChatPanel() {
            panel.classList.add('is-open');
            if (fab) fab.style.display = 'none';
            clearUnreadBadge();
            textarea?.focus();
            loadChatThread();
            if (chatThread && !chatPollTimer) {
                chatPollTimer = setInterval(loadChatThread, 8000);
            }
        }

        function closeChatPanel() {
            panel.classList.remove('is-open');
            if (fab) fab.style.display = 'flex';
            if (chatPollTimer) {
                clearInterval(chatPollTimer);
                chatPollTimer = null;
            }
        }

        fab?.addEventListener('click', openChatPanel);
        document.getElementById('gig-contact-btn')?.addEventListener('click', openChatPanel);
        closeBtn?.addEventListener('click', closeChatPanel);

        const inlineMessage = document.getElementById('inline-chat-message');
        const inlineSendBtn = document.getElementById('inline-chat-send-btn');
        inlineSendBtn?.addEventListener('click', function () {
            const value = inlineMessage.value.trim();
            if (!value) {
                inlineMessage.focus();
                return;
            }
            textarea.value = value;
            openChatPanel();
            $(chatForm).trigger('submit');
            inlineMessage.value = '';
        });

        fileInput?.addEventListener('change', function () {
            var file = this.files && this.files[0];
            if (!file) {
                fileName?.classList.add('d-none');
                return;
            }
            if (file.size > maxChatFileSize) {
                toastr.error('{{ translate('file_maximum_size_') }}2 MB');
                this.value = '';
                fileName?.classList.add('d-none');
                return;
            }
            if (fileName) {
                fileName.textContent = file.name;
                fileName.classList.remove('d-none');
            }
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

        const quoteModal = document.getElementById('getQuoteModal');
        const quoteForm = document.getElementById('get-quote-form');
        const deliveryOptions = document.querySelectorAll('.quote-delivery-option');
        const deliveryInput = document.getElementById('quote-delivery-preference');
        const customDeliveryInput = document.getElementById('quote-custom-delivery');

        document.querySelectorAll('.gig-quote-trigger').forEach(function (button) {
            button.addEventListener('click', function () {
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

        const tabs = document.querySelectorAll('[data-tab-index]');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) { t.classList.remove('is-active'); });
                tab.classList.add('is-active');
                document.querySelectorAll('[data-tab-panel]').forEach(function (panel) {
                    panel.style.display = panel.dataset.tabPanel === tab.dataset.tabIndex ? '' : 'none';
                });
            });
        });

        const images = @json($service->images->map(fn($i) => getStorageImages(path: $i->image_full_url, type: 'backend-profile')));
        let currentIndex = 0;
        const mainImg = document.getElementById('gig-gallery-main');
        const thumbs = document.querySelectorAll('#gig-thumb-strip img');

        function showImage(index) {
            if (!images.length) return;
            currentIndex = (index + images.length) % images.length;
            mainImg.src = images[currentIndex];
            thumbs.forEach(function (thumb) {
                thumb.classList.toggle('is-active', Number(thumb.dataset.index) === currentIndex);
            });
        }

        thumbs.forEach(function (thumb) {
            thumb.addEventListener('click', function () { showImage(Number(thumb.dataset.index)); });
        });
        document.getElementById('gig-gallery-prev')?.addEventListener('click', function () { showImage(currentIndex - 1); });
        document.getElementById('gig-gallery-next')?.addEventListener('click', function () { showImage(currentIndex + 1); });
    })();
</script>
@endpush
@endsection
