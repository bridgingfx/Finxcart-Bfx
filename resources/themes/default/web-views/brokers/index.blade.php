@extends('layouts.front-end.app')

@section('title', translate('brokers'))

@section('content')
<div class="fx-container" style="padding-top: var(--fx-s6); padding-bottom: var(--fx-s12);">
    <nav aria-label="{{ translate('breadcrumb') }}">
        <ol class="fx-breadcrumb">
            <li><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
            <li aria-current="page">{{ translate('brokers') }}</li>
        </ol>
    </nav>

    <div style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap: var(--fx-s4); margin-bottom: var(--fx-s6);">
        <div>
            <h1 class="fx-hero" style="margin:0 0 var(--fx-s2);">{{ translate('regulated_broker_rankings') }}</h1>
            <p class="fx-meta" style="margin:0;">{{ translate('compare_authority_scores_and_user_reviews') }}</p>
        </div>
        <form method="GET" action="{{ route('brokers.index') }}" class="fx-form" style="display:flex; align-items:center; gap: var(--fx-s2);" novalidate>
            <label for="fx-broker-sort" style="font-size:14px; font-weight:600; white-space:nowrap;">{{ translate('sort_by') }}</label>
            <select name="sort" id="fx-broker-sort" onchange="this.form.submit()" style="min-width:220px;">
                <option value="authority_score" {{ request('sort', 'authority_score') == 'authority_score' ? 'selected' : '' }}>{{ translate('authority_score') }} ({{ translate('high_to_low') }})</option>
                <option value="user_rating" {{ request('sort') == 'user_rating' ? 'selected' : '' }}>{{ translate('user_rating') }} ({{ translate('high_to_low') }})</option>
                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>{{ translate('name') }} (A - Z)</option>
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>{{ translate('newest_added') }}</option>
            </select>
        </form>
    </div>

    @if($brokers->count())
        <p class="fx-meta" style="margin:0 0 var(--fx-s4);">{{ $brokers->total() }} {{ translate('brokers_found') }}</p>
    @endif

    <div class="fx-grid" style="grid-template-columns: 1fr;">
        @forelse($brokers as $broker)
            @php
                $brokerDetailUrl = route('brokers.show', $broker->slug);
                $logoPath = $broker->logo ? asset($broker->logo) : null;
            @endphp
            <article class="fx-card fx-card-lg fx-broker-card">
                <div class="fx-broker-card__grid">
                    <div class="fx-broker-card__brand">
                        @if($logoPath)
                            <a href="{{ $brokerDetailUrl }}" aria-label="{{ translate('view_details_for') }} {{ $broker->name }}">
                                <img src="{{ $logoPath }}" alt="{{ $broker->name }}" loading="lazy"
                                     class="fx-broker-card__logo" onerror="this.closest('.fx-broker-card__brand').classList.add('is-fallback'); this.remove();">
                            </a>
                        @endif
                        <span class="fx-broker-card__initials" aria-hidden="true">{{ strtoupper(substr($broker->name, 0, 2)) }}</span>
                    </div>

                    <div class="fx-broker-card__main">
                        <h2 class="fx-broker-card__name">
                            <a href="{{ $brokerDetailUrl }}">{{ $broker->name }}</a>
                        </h2>
                        <div class="fx-broker-card__chips">
                            @if($broker->regulations)
                                <span class="fx-chip" title="{{ translate('regulation') }}">{{ $broker->regulations }}</span>
                            @endif
                            @if($broker->platforms)
                                <span class="fx-chip">{{ $broker->platforms }}</span>
                            @endif
                        </div>
                        <dl class="fx-broker-card__scores">
                            <div class="fx-broker-card__score">
                                <dt>{{ translate('authority_score') }}</dt>
                                <dd><strong>{{ number_format((float) $broker->system_rating, 2) }}</strong><span class="fx-meta">/10</span></dd>
                            </div>
                            <div class="fx-broker-card__score">
                                <dt>{{ translate('user_rating') }}</dt>
                                <dd>
                                    @if((int) $broker->review_count > 0)
                                        <strong>{{ number_format((float) $broker->user_rating, 1) }}</strong><span class="fx-meta">/5</span>
                                        <span class="fx-meta">({{ $broker->review_count }} {{ translate('reviews') }})</span>
                                    @else
                                        <span class="fx-meta">{{ translate('no_reviews_yet') }}</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <dl class="fx-broker-card__facts">
                        @if($broker->min_deposit)
                            <div><dt>{{ translate('min_dep') }}</dt><dd>{{ $broker->min_deposit }}</dd></div>
                        @endif
                        @if($broker->max_leverage)
                            <div><dt>{{ translate('max_leverage') }}</dt><dd>{{ $broker->max_leverage }}</dd></div>
                        @endif
                        <div><dt>{{ translate('license') }}</dt><dd>{{ number_format((float) $broker->score_license, 1) }}/10</dd></div>
                        <div><dt>{{ translate('software') }}</dt><dd>{{ number_format((float) $broker->score_software, 1) }}/10</dd></div>
                    </dl>

                    <div class="fx-broker-card__actions">
                        <a href="{{ $brokerDetailUrl }}" class="fx-btn fx-btn-outline fx-btn-sm">{{ translate('view_details') }}</a>
                        <a href="{{ $broker->affiliate_url }}" target="_blank" rel="noopener noreferrer"
                           class="fx-btn fx-btn-amber fx-btn-sm"
                           aria-label="{{ translate('open_account') }} — {{ $broker->name }} ({{ translate('opens_in_new_tab') }})">
                            {{ translate('open_account') }} <span aria-hidden="true">↗</span>
                        </a>
                        <p class="fx-meta" style="font-size:12px; margin: var(--fx-s1) 0 0;">{{ translate('external_partner_link') }}</p>
                    </div>
                </div>
            </article>
        @empty
            <div class="fx-empty">
                <div class="fx-empty__slot" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4"/></svg>
                </div>
                <h2 class="fx-empty__title">{{ translate('no_brokers_found') }}</h2>
                <p class="fx-empty__body">{{ translate('try_adjusting_your_search_or_filters') }}</p>
            </div>
        @endforelse
    </div>

    <div style="display:flex; justify-content:center;">
        @include('web-views.partials._fx-pagination', ['paginator' => $brokers->appends(request()->query())])
    </div>
</div>
@endsection

@push('css_or_js')
<style>
    .fx-broker-card__grid { display: grid; grid-template-columns: 120px minmax(0, 1fr) 200px 190px; gap: var(--fx-s6); align-items: start; }
    .fx-broker-card__brand { position: relative; display: flex; align-items: center; justify-content: center; min-height: 96px; }
    .fx-broker-card__logo { max-height: 80px; max-width: 100%; object-fit: contain; }
    .fx-broker-card__initials {
        position: absolute; inset: 0; display: none; align-items: center; justify-content: center;
        font-size: 28px; font-weight: 700; color: var(--fx-navy);
        background: var(--fx-canvas); border: 1px solid var(--fx-border); border-radius: var(--fx-radius-md);
    }
    .fx-broker-card__brand.is-fallback .fx-broker-card__initials,
    .fx-broker-card__brand:not(:has(img)) .fx-broker-card__initials { display: flex; }
    .fx-broker-card__name { font-size: 20px; font-weight: 700; margin: 0 0 var(--fx-s2); letter-spacing: -0.01em; }
    .fx-broker-card__name a { color: inherit; text-decoration: none; }
    .fx-broker-card__name a:hover { color: var(--fx-navy); }
    .fx-broker-card__chips { display: flex; flex-wrap: wrap; gap: var(--fx-s2); margin-bottom: var(--fx-s3); }
    .fx-broker-card__scores { display: flex; gap: var(--fx-s6); margin: 0; }
    .fx-broker-card__score dt { font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: var(--fx-muted); font-weight: 600; }
    .fx-broker-card__score dd { margin: var(--fx-s1) 0 0; font-size: 18px; }
    .fx-broker-card__facts { margin: 0; display: grid; gap: var(--fx-s2); font-size: 14px; border-inline-start: 1px solid var(--fx-border); padding-inline-start: var(--fx-s6); }
    .fx-broker-card__facts div { display: flex; justify-content: space-between; gap: var(--fx-s2); }
    .fx-broker-card__facts dt { color: var(--fx-muted); }
    .fx-broker-card__facts dd { margin: 0; font-weight: 600; text-align: end; }
    .fx-broker-card__actions { display: flex; flex-direction: column; gap: var(--fx-s2); }
    .fx-broker-card__actions .fx-btn { width: 100%; }
    @media (max-width: 1023px) {
        .fx-broker-card__grid { grid-template-columns: 96px minmax(0, 1fr); }
        .fx-broker-card__facts { grid-column: 1 / -1; border-inline-start: 0; padding-inline-start: 0; border-top: 1px solid var(--fx-border); padding-top: var(--fx-s4); grid-template-columns: repeat(2, 1fr); }
        .fx-broker-card__actions { grid-column: 1 / -1; flex-direction: row; }
        .fx-broker-card__actions .fx-meta { display: none; }
    }
    @media (max-width: 767px) {
        .fx-broker-card__scores { gap: var(--fx-s4); flex-wrap: wrap; }
        .fx-broker-card__facts { grid-template-columns: 1fr; }
    }
</style>
@endpush
