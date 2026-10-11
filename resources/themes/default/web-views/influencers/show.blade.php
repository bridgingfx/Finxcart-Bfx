@extends('layouts.front-end.app')

@section('title', $influencer->name . ' - ' . translate('influencers'))

@section('content')
<div class="fx-container" style="padding-top: var(--fx-s6); padding-bottom: var(--fx-s12);">
    <nav aria-label="{{ translate('breadcrumb') }}">
        <ol class="fx-breadcrumb">
            <li><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
            <li><a href="{{ route('influencers.index') }}">{{ translate('influencers') }}</a></li>
            <li aria-current="page">{{ $influencer->name }}</li>
        </ol>
    </nav>

    <div class="fx-inf-profile">
        <aside class="fx-card fx-card-lg" style="text-align:center;">
            @php($imgSrc = $influencer->profile_image ? asset('public/' . ltrim($influencer->profile_image, '/')) : null)
            @if($imgSrc)
                <img src="{{ $imgSrc }}" alt="{{ $influencer->name }}" class="fx-inf-profile__avatar"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                <span class="fx-inf-profile__avatar fx-inf-profile__initials" style="display:none;" aria-hidden="true">{{ strtoupper(substr($influencer->name, 0, 1)) }}</span>
            @else
                <span class="fx-inf-profile__avatar fx-inf-profile__initials" aria-hidden="true">{{ strtoupper(substr($influencer->name, 0, 1)) }}</span>
            @endif
            <h1 style="font-size:24px; font-weight:700; letter-spacing:-0.02em; margin: var(--fx-s3) 0 var(--fx-s2);">{{ $influencer->name }}</h1>
            @if((int) $influencer->rating_count > 0)
                <p style="margin:0 0 var(--fx-s4);"><span class="fx-badge fx-badge--neutral">★ {{ number_format((float) $influencer->avg_rating, 1) }} / 5.0</span>
                <span class="fx-meta">({{ $influencer->rating_count }} {{ translate('reviews') }})</span></p>
            @else
                <p class="fx-meta" style="margin:0 0 var(--fx-s4);">{{ translate('no_reviews_yet') }}</p>
            @endif

            <dl class="fx-inf-profile__reach">
                <div><dt>Instagram</dt><dd>{{ $influencer->formattedFollowers('instagram_followers') }}</dd></div>
                <div><dt>YouTube</dt><dd>{{ $influencer->formattedFollowers('youtube_subscribers') }}</dd></div>
                <div><dt>TikTok</dt><dd>{{ $influencer->formattedFollowers('tiktok_followers') }}</dd></div>
            </dl>

            <button type="button" class="fx-btn fx-btn-lg" style="width:100%;" data-fx-contact-influencer
                    data-id="{{ $influencer->id }}" data-name="{{ $influencer->name }}">
                {{ translate('contact') }} {{ $influencer->name }}
            </button>
            <p class="fx-meta" style="font-size:12px; margin: var(--fx-s2) 0 0;">{{ translate('inquiry_goes_to_admin_team') }}</p>
        </aside>

        <div>
            <section class="fx-card fx-card-lg" style="margin-bottom: var(--fx-s6);">
                <h2 style="font-size:20px; font-weight:700; margin:0 0 var(--fx-s3);">{{ translate('about') }}</h2>
                @if($influencer->bio)
                    <p style="margin:0; line-height:1.7;">{{ $influencer->bio }}</p>
                @else
                    <p class="fx-meta" style="margin:0;">{{ translate('no_bio_provided_yet') }}</p>
                @endif
            </section>

            <section class="fx-card fx-card-lg" style="margin-bottom: var(--fx-s6);">
                <h2 style="font-size:20px; font-weight:700; margin:0 0 var(--fx-s4);">{{ translate('rate_this_influencer') }}</h2>
                @if(Auth::guard('customer')->check())
                    <form action="{{ route('influencers.rate', $influencer->id) }}" method="POST" class="fx-form" novalidate>
                        @csrf
                        <div class="fx-field">
                            <label>{{ translate('your_rating') }}</label>
                            <div class="fx-rate" role="radiogroup" aria-label="{{ translate('your_rating') }}">
                                @for($s = 5; $s >= 1; $s--)
                                    <input type="radio" id="fx-inf-star{{ $s }}" name="rating" value="{{ $s }}" required>
                                    <label for="fx-inf-star{{ $s }}" title="{{ $s }} {{ translate('stars') }}">{{ $s }} {{ translate('stars') }}</label>
                                @endfor
                            </div>
                        </div>
                        <div class="fx-field">
                            <label for="fx-inf-comment">{{ translate('your_review') }}</label>
                            <textarea id="fx-inf-comment" name="comment" rows="3" placeholder="{{ translate('write_a_review_2') }}"></textarea>
                        </div>
                        <button class="fx-btn" type="submit">{{ translate('submit_review') }}</button>
                    </form>
                @else
                    <p style="margin:0 0 var(--fx-s3);">{{ translate('login_to_rate_this_influencer') }}</p>
                    <a href="{{ route('customer.auth.login') }}" class="fx-btn fx-btn-outline">{{ translate('login_now') }}</a>
                @endif
            </section>

            <section>
                <h2 style="font-size:20px; font-weight:700; margin:0 0 var(--fx-s4);">{{ translate('community_reviews') }}</h2>
                @forelse($influencer->ratings as $review)
                    <div class="fx-card" style="margin-bottom: var(--fx-s3);">
                        <div style="display:flex; justify-content:space-between; gap: var(--fx-s3); align-items:flex-start; margin-bottom: var(--fx-s2);">
                            <strong>{{ $review->user->name ?? translate('user') }}</strong>
                            <span class="fx-meta">{{ $review->created_at->diffForHumans() }}</span>
                        </div>
                        <span class="fx-badge fx-badge--neutral" aria-label="{{ translate('rated') }} {{ $review->rating }} {{ translate('out_of') }} 5">
                            ★ {{ $review->rating }}/5
                        </span>
                        @if($review->comment)
                            <p style="margin: var(--fx-s2) 0 0;">{{ $review->comment }}</p>
                        @endif
                    </div>
                @empty
                    <div class="fx-empty">
                        <h3 class="fx-empty__title">{{ translate('no_reviews_yet') }}</h3>
                        <p class="fx-empty__body">{{ translate('be_the_first_to_rate_this_influencer') }}</p>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
</div>

@include('web-views.influencers.contact_modal')
@endsection

@push('css_or_js')
<style>
    .fx-inf-profile { display: grid; grid-template-columns: 340px minmax(0, 1fr); gap: var(--fx-s6); align-items: start; }
    .fx-inf-profile__avatar {
        width: 168px; height: 168px; border-radius: 50%; object-fit: cover; margin: 0 auto;
        border: 4px solid var(--fx-canvas); outline: 1px solid var(--fx-border);
    }
    .fx-inf-profile__initials {
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 64px; font-weight: 700; color: var(--fx-navy); background: var(--fx-canvas);
    }
    .fx-inf-profile__reach { margin: 0 0 var(--fx-s4); display: grid; gap: var(--fx-s2); font-size: 14px; text-align: start; }
    .fx-inf-profile__reach div { display: flex; justify-content: space-between; gap: var(--fx-s2); padding: var(--fx-s2) 0; border-bottom: 1px solid var(--fx-border); }
    .fx-inf-profile__reach div:last-child { border-bottom: 0; }
    .fx-inf-profile__reach dt { color: var(--fx-muted); }
    .fx-inf-profile__reach dd { margin: 0; font-weight: 700; }
    .fx-rate { display: inline-flex; flex-direction: row-reverse; gap: var(--fx-s1); }
    .fx-rate input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .fx-rate label { font-size: 30px; line-height: 1; color: #CBD2D9; cursor: pointer; }
    .fx-rate label::before { content: '★'; }
    .fx-rate input:checked ~ label, .fx-rate label:hover, .fx-rate label:hover ~ label { color: var(--fx-amber); }
    .fx-rate input:focus-visible + label { outline: 2px solid var(--fx-navy); outline-offset: 2px; border-radius: 4px; }
    @media (max-width: 1023px) { .fx-inf-profile { grid-template-columns: 1fr; } }
</style>
@endpush
