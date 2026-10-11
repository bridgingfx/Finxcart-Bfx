@extends('layouts.front-end.app')

@section('title', translate('influencers'))

@section('content')
<div class="fx-container" style="padding-top: var(--fx-s6); padding-bottom: var(--fx-s12);">
    <nav aria-label="{{ translate('breadcrumb') }}">
        <ol class="fx-breadcrumb">
            <li><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
            <li aria-current="page">{{ translate('influencers') }}</li>
        </ol>
    </nav>

    <div style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap: var(--fx-s4); margin-bottom: var(--fx-s6);">
        <div>
            <h1 class="fx-hero" style="margin:0 0 var(--fx-s2);">{{ translate('find_top_influencers') }}</h1>
            <p class="fx-meta" style="margin:0;">{{ translate('discover_creators_by_rating_and_reach') }}</p>
        </div>
        <form method="GET" class="fx-form" style="display:flex; align-items:center; gap: var(--fx-s2);" novalidate>
            <label for="fx-inf-sort" style="font-size:14px; font-weight:600; white-space:nowrap;">{{ translate('sort_by') }}</label>
            <select name="sort" id="fx-inf-sort" onchange="this.form.submit()" style="min-width:200px;">
                <option value="">{{ translate('sort_by_2') }}</option>
                <option value="rating_desc" {{ request('sort') == 'rating_desc' ? 'selected' : '' }}>{{ translate('highest_rated') }}</option>
                <option value="followers_desc" {{ request('sort') == 'followers_desc' ? 'selected' : '' }}>{{ translate('most_popular') }}</option>
            </select>
        </form>
    </div>

    <div class="fx-grid fx-grid-4">
        @forelse($influencers as $inf)
            @php
                $profileUrl = route('influencers.show', $inf->slug);
                $imgSrc = $inf->profile_image ? asset('public/' . ltrim($inf->profile_image, '/')) : null;
            @endphp
            <article class="fx-card fx-inf-card">
                <a class="fx-inf-card__media" href="{{ $profileUrl }}" aria-label="{{ translate('view_profile_of') }} {{ $inf->name }}">
                    @if($imgSrc)
                        <img src="{{ $imgSrc }}" alt="{{ $inf->name }}" loading="lazy"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <span class="fx-inf-card__initials" style="display:none;" aria-hidden="true">{{ strtoupper(substr($inf->name, 0, 1)) }}</span>
                    @else
                        <span class="fx-inf-card__initials" aria-hidden="true">{{ strtoupper(substr($inf->name, 0, 1)) }}</span>
                    @endif
                    <span class="fx-badge fx-badge--neutral fx-inf-card__rating">
                        @if((int) $inf->rating_count > 0)
                            ★ {{ number_format((float) $inf->avg_rating, 1) }} ({{ $inf->rating_count }})
                        @else
                            {{ translate('no_reviews_yet') }}
                        @endif
                    </span>
                </a>
                <div class="fx-inf-card__body">
                    <h2 class="fx-inf-card__name"><a href="{{ $profileUrl }}">{{ $inf->name }}</a></h2>
                    <dl class="fx-inf-card__reach">
                        @if($inf->instagram_followers)
                            <div><dt>Instagram</dt><dd>{{ $inf->formattedFollowers('instagram_followers') }}</dd></div>
                        @endif
                        @if($inf->youtube_subscribers)
                            <div><dt>YouTube</dt><dd>{{ $inf->formattedFollowers('youtube_subscribers') }}</dd></div>
                        @endif
                        @if($inf->tiktok_followers)
                            <div><dt>TikTok</dt><dd>{{ $inf->formattedFollowers('tiktok_followers') }}</dd></div>
                        @endif
                    </dl>
                    <div class="fx-inf-card__actions">
                        <a href="{{ $profileUrl }}" class="fx-btn fx-btn-outline fx-btn-sm">{{ translate('profile') }}</a>
                        <button type="button" class="fx-btn fx-btn-sm" data-fx-contact-influencer
                                data-id="{{ $inf->id }}" data-name="{{ $inf->name }}">{{ translate('contact') }}</button>
                    </div>
                </div>
            </article>
        @empty
            <div class="fx-empty" style="grid-column: 1 / -1;">
                <h2 class="fx-empty__title">{{ translate('no_influencers_found') }}</h2>
                <p class="fx-empty__body">{{ translate('try_adjusting_your_search_or_filters') }}</p>
            </div>
        @endforelse
    </div>

    <div style="display:flex; justify-content:center;">
        @include('web-views.partials._fx-pagination', ['paginator' => $influencers->appends(request()->query())])
    </div>
</div>

@include('web-views.influencers.contact_modal')
@endsection

@push('css_or_js')
<style>
    .fx-inf-card { padding: 0; overflow: hidden; display: flex; flex-direction: column; }
    .fx-inf-card__media { position: relative; display: block; aspect-ratio: 4 / 3; background: var(--fx-canvas); }
    .fx-inf-card__media img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .fx-inf-card__initials {
        position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
        font-size: 56px; font-weight: 700; color: var(--fx-navy); background: var(--fx-canvas);
    }
    .fx-inf-card__rating { position: absolute; left: var(--fx-s3); bottom: var(--fx-s3); background: rgba(255,255,255,.95); }
    .fx-inf-card__body { padding: var(--fx-s4); display: flex; flex-direction: column; gap: var(--fx-s3); flex: 1; }
    .fx-inf-card__name { font-size: 17px; font-weight: 700; margin: 0; letter-spacing: -0.01em; }
    .fx-inf-card__name a { color: inherit; text-decoration: none; }
    .fx-inf-card__name a:hover { color: var(--fx-navy); }
    .fx-inf-card__reach { margin: 0; display: flex; flex-wrap: wrap; gap: var(--fx-s2); }
    .fx-inf-card__reach div { display: flex; gap: var(--fx-s1); align-items: baseline; font-size: 13px; background: var(--fx-canvas); border: 1px solid var(--fx-border); border-radius: var(--fx-radius-sm); padding: var(--fx-s1) var(--fx-s2); }
    .fx-inf-card__reach dt { color: var(--fx-muted); font-size: 12px; }
    .fx-inf-card__reach dd { margin: 0; font-weight: 700; }
    .fx-inf-card__actions { display: flex; gap: var(--fx-s2); margin-top: auto; }
    .fx-inf-card__actions .fx-btn { flex: 1; }
</style>
@endpush
