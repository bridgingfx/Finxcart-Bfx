@extends('layouts.front-end.app')

@section('title', $broker->name . ' - ' . translate('brokers'))

@section('content')
<div class="fx-container" style="padding-top: var(--fx-s6); padding-bottom: var(--fx-s12);">
    <nav aria-label="{{ translate('breadcrumb') }}">
        <ol class="fx-breadcrumb">
            <li><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
            <li><a href="{{ route('brokers.index') }}">{{ translate('brokers') }}</a></li>
            <li aria-current="page">{{ $broker->name }}</li>
        </ol>
    </nav>

    <div class="fx-broker-profile">
        <aside class="fx-card fx-card-lg fx-broker-profile__side">
            <div style="text-align:center; margin-bottom: var(--fx-s4);">
                @if($broker->logo)
                    <img src="{{ asset($broker->logo) }}" alt="{{ $broker->name }}" class="fx-broker-profile__logo"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <span class="fx-broker-profile__initials" style="display:none;" aria-hidden="true">{{ strtoupper(substr($broker->name, 0, 2)) }}</span>
                @else
                    <span class="fx-broker-profile__initials" aria-hidden="true">{{ strtoupper(substr($broker->name, 0, 2)) }}</span>
                @endif
            </div>

            <p class="fx-meta" style="text-align:center; text-transform:uppercase; font-size:12px; font-weight:600; letter-spacing:0.04em; margin:0 0 var(--fx-s1);">{{ translate('authority_score') }}</p>
            <p style="text-align:center; font-size:36px; font-weight:700; letter-spacing:-0.02em; margin:0 0 var(--fx-s4); color:var(--fx-navy);">
                {{ number_format((float) $broker->system_rating, 2) }} <span class="fx-meta" style="font-size:16px; font-weight:400;">/10</span>
            </p>

            <a href="{{ $broker->affiliate_url }}" target="_blank" rel="noopener noreferrer"
               class="fx-btn fx-btn-amber fx-btn-lg" style="width:100%; margin-bottom: var(--fx-s2);"
               aria-label="{{ translate('open_account') }} — {{ $broker->name }} ({{ translate('opens_in_new_tab') }})">
                {{ translate('open_account') }} <span aria-hidden="true">↗</span>
            </a>
            <p class="fx-meta" style="text-align:center; font-size:12px; margin:0 0 var(--fx-s6);">{{ translate('external_partner_link') }}</p>

            <h2 style="font-size:16px; font-weight:700; margin:0 0 var(--fx-s3);">{{ translate('score_breakdown') }}</h2>
            <dl class="fx-broker-profile__scores">
                <div><dt>{{ translate('license_reg') }}</dt><dd>{{ number_format((float) $broker->score_license, 1) }}/10</dd></div>
                <div><dt>{{ translate('software') }}</dt><dd>{{ number_format((float) $broker->score_software, 1) }}/10</dd></div>
                <div><dt>{{ translate('business_stability') }}</dt><dd>{{ number_format((float) $broker->score_stability, 1) }}/10</dd></div>
            </dl>
            <p class="fx-meta" style="font-size:12px; margin: var(--fx-s3) 0 0;">{{ translate('authority_score_formula_hint') }}</p>
        </aside>

        <div class="fx-broker-profile__main">
            <section class="fx-card fx-card-lg" style="margin-bottom: var(--fx-s6);">
                <h1 style="font-size:28px; font-weight:700; letter-spacing:-0.02em; margin:0 0 var(--fx-s3);">{{ translate('about') }} {{ $broker->name }}</h1>
                @if($broker->description)
                    <p style="margin:0 0 var(--fx-s4); line-height:1.7;">{{ $broker->description }}</p>
                @endif
                <dl class="fx-broker-profile__facts">
                    @if($broker->regulations)
                        <div><dt>{{ translate('regulation') }}</dt><dd>{{ $broker->regulations }}</dd></div>
                    @endif
                    @if($broker->max_leverage)
                        <div><dt>{{ translate('max_leverage') }}</dt><dd>{{ $broker->max_leverage }}</dd></div>
                    @endif
                    @if($broker->min_deposit)
                        <div><dt>{{ translate('min_dep') }}</dt><dd>{{ $broker->min_deposit }}</dd></div>
                    @endif
                    @if($broker->platforms)
                        <div class="is-wide"><dt>{{ translate('platforms') }}</dt><dd>{{ $broker->platforms }}</dd></div>
                    @endif
                </dl>
                @if((int) $broker->review_count > 0)
                    <p style="margin: var(--fx-s4) 0 0;">
                        <span class="fx-badge fx-badge--neutral">★ {{ number_format((float) $broker->user_rating, 1) }} / 5</span>
                        <span class="fx-meta">{{ translate('based_on') }} {{ $broker->review_count }} {{ translate('approved_reviews') }}</span>
                    </p>
                @else
                    <p class="fx-meta" style="margin: var(--fx-s4) 0 0;">{{ translate('no_reviews_yet') }}</p>
                @endif
            </section>

            <section class="fx-card fx-card-lg">
                <h2 style="font-size:20px; font-weight:700; margin:0 0 var(--fx-s4);">{{ translate('user_reviews') }} ({{ $broker->review_count }})</h2>

                <div class="fx-broker-profile__review-form">
                    @if(Auth::guard('customer')->check())
                        <h3 style="font-size:16px; font-weight:700; margin:0 0 var(--fx-s3);">{{ translate('write_a_review') }}</h3>
                        <form action="{{ route('brokers.review', $broker->id) }}" method="POST" class="fx-form" novalidate>
                            @csrf
                            <div class="fx-field">
                                <label>{{ translate('your_rating') }}</label>
                                <div class="fx-rate" role="radiogroup" aria-label="{{ translate('your_rating') }}">
                                    @for($s = 5; $s >= 1; $s--)
                                        <input type="radio" id="fx-star{{ $s }}" name="rating" value="{{ $s }}" required>
                                        <label for="fx-star{{ $s }}" title="{{ $s }} {{ translate('stars') }}">{{ $s }} {{ translate('stars') }}</label>
                                    @endfor
                                </div>
                            </div>
                            <div class="fx-field">
                                <label for="fx-review-comment">{{ translate('your_review') }}</label>
                                <textarea id="fx-review-comment" name="comment" rows="3" required placeholder="{{ translate('share_your_experience') }}"></textarea>
                            </div>
                            <button class="fx-btn" type="submit">{{ translate('submit_review') }}</button>
                            <p class="fx-help">{{ translate('reviews_require_admin_approval') }}</p>
                        </form>
                    @else
                        <p style="margin:0 0 var(--fx-s3);">{{ translate('login_to_write_a_review_about') }} {{ $broker->name }}</p>
                        <a href="{{ route('customer.auth.login') }}" class="fx-btn fx-btn-outline">{{ translate('login_now') }}</a>
                    @endif
                </div>

                <div style="margin-top: var(--fx-s6);">
                    @forelse($broker->reviews as $review)
                        <div class="fx-broker-profile__review">
                            <div class="fx-broker-profile__review-head">
                                <div>
                                    <p style="font-weight:700; margin:0;">{{ $review->reviewer_name }}</p>
                                    <p class="fx-meta" style="margin:0;">{{ $review->created_at->diffForHumans() }}</p>
                                </div>
                                <span class="fx-badge fx-badge--neutral" aria-label="{{ translate('rated') }} {{ $review->rating }} {{ translate('out_of') }} 5">
                                    ★ {{ $review->rating }}/5
                                </span>
                            </div>
                            <p style="margin: var(--fx-s2) 0 0;">{{ $review->comment }}</p>
                        </div>
                    @empty
                        <div class="fx-empty">
                            <h3 class="fx-empty__title">{{ translate('no_reviews_yet') }}</h3>
                            <p class="fx-empty__body">{{ translate('no_approved_reviews_yet_be_the_first') }}</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('css_or_js')
<style>
    .fx-broker-profile { display: grid; grid-template-columns: 340px minmax(0, 1fr); gap: var(--fx-s6); align-items: start; }
    .fx-broker-profile__side { position: sticky; top: var(--fx-s4); }
    .fx-broker-profile__logo { max-height: 110px; max-width: 100%; object-fit: contain; }
    .fx-broker-profile__initials {
        display: inline-flex; align-items: center; justify-content: center;
        width: 110px; height: 110px; border-radius: var(--fx-radius-lg);
        background: var(--fx-canvas); border: 1px solid var(--fx-border);
        font-size: 40px; font-weight: 700; color: var(--fx-navy);
    }
    .fx-broker-profile__scores { margin: 0; display: grid; gap: var(--fx-s2); font-size: 14px; }
    .fx-broker-profile__scores div { display: flex; justify-content: space-between; gap: var(--fx-s2); padding: var(--fx-s2) 0; border-bottom: 1px solid var(--fx-border); }
    .fx-broker-profile__scores div:last-child { border-bottom: 0; }
    .fx-broker-profile__scores dt { color: var(--fx-muted); }
    .fx-broker-profile__scores dd { margin: 0; font-weight: 700; }
    .fx-broker-profile__facts { margin: 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--fx-s3); }
    .fx-broker-profile__facts div { background: var(--fx-canvas); border: 1px solid var(--fx-border); border-radius: var(--fx-radius-md); padding: var(--fx-s3); }
    .fx-broker-profile__facts div.is-wide { grid-column: 1 / -1; }
    .fx-broker-profile__facts dt { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--fx-muted); margin-bottom: var(--fx-s1); }
    .fx-broker-profile__facts dd { margin: 0; font-weight: 600; }
    .fx-broker-profile__review-form { background: var(--fx-canvas); border: 1px solid var(--fx-border); border-radius: var(--fx-radius-lg); padding: var(--fx-s4); }
    .fx-rate { display: inline-flex; flex-direction: row-reverse; gap: var(--fx-s1); }
    .fx-rate input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .fx-rate label { font-size: 30px; line-height: 1; color: #CBD2D9; cursor: pointer; }
    .fx-rate label::before { content: '★'; }
    .fx-rate input:checked ~ label, .fx-rate label:hover, .fx-rate label:hover ~ label { color: var(--fx-amber); }
    .fx-rate input:focus-visible + label { outline: 2px solid var(--fx-navy); outline-offset: 2px; border-radius: 4px; }
    .fx-broker-profile__review { border-top: 1px solid var(--fx-border); padding: var(--fx-s4) 0; }
    .fx-broker-profile__review-head { display: flex; justify-content: space-between; align-items: flex-start; gap: var(--fx-s3); }
    @media (max-width: 1023px) {
        .fx-broker-profile { grid-template-columns: 1fr; }
        .fx-broker-profile__side { position: static; }
    }
    @media (max-width: 767px) {
        .fx-broker-profile__facts { grid-template-columns: 1fr 1fr; }
    }
</style>
@endpush
