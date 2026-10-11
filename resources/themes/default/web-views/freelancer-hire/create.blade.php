@extends('layouts.front-end.app')

@section('title', translate('hire') . ' - ' . $service->title)

@push('css_or_js')
<style>
    /* Project brief / checkout — fx light-first skin */
    .fx-hire-layout { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: var(--fx-s6); align-items: start; }
    @media (max-width: 991px) { .fx-hire-layout { grid-template-columns: 1fr; } }
    .fx-hire-freelancer { display: flex; align-items: center; gap: var(--fx-s3); padding: var(--fx-s4); border: 1px solid var(--fx-border); border-radius: var(--fx-radius-md); background: var(--fx-canvas); margin-bottom: var(--fx-s5); }
    .fx-hire-freelancer img { width: 58px; height: 58px; border-radius: 50%; object-fit: cover; border: 2px solid var(--fx-surface); flex-shrink: 0; }
    .fx-hire-actions { display: flex; gap: var(--fx-s2); margin-top: var(--fx-s5); }
    .fx-hire-actions .fx-btn { flex: 1; }
    .fx-payment-method { display: flex; align-items: center; gap: var(--fx-s3); background: var(--fx-canvas); border: 1px solid var(--fx-border); border-radius: var(--fx-radius-md); padding: var(--fx-s4); }
    .fx-payment-radio { width: 22px; height: 22px; border-radius: 50%; border: 2px solid var(--fx-navy); flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
    .fx-payment-radio::after { content: ''; width: 10px; height: 10px; border-radius: 50%; background: var(--fx-navy); }
    @media (max-width: 575px) { .fx-hire-actions { flex-direction: column-reverse; } }
</style>
@endpush

@section('content')
<div class="fx-container" style="padding-top: var(--fx-s6); padding-bottom: var(--fx-s12);">
    <nav aria-label="{{ translate('breadcrumb') }}">
        <ol class="fx-breadcrumb">
            <li><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
            <li><a href="{{ route('hire-freelancer') }}">{{ translate('hire_freelancer') }}</a></li>
            <li><a href="{{ route('hire-freelancer.service-show', $service->id) }}">{{ \Illuminate\Support\Str::limit($service->title, 60) }}</a></li>
            <li aria-current="page">{{ translate('checkout') }}</li>
        </ol>
    </nav>

    <h1 class="fx-hero" style="margin:0 0 var(--fx-s2);">{{ translate('checkout') }}</h1>
    <p class="fx-meta" style="margin:0 0 var(--fx-s6);">{{ translate('review_your_project_scope_and_complete_secure_payment') }}</p>

    <form action="{{ route('hire.store', $service->id) }}" method="POST" id="hire-form" class="fx-hire-layout" novalidate>
        @csrf
        @if($quoteRequestId)
            <input type="hidden" name="quote_request_id" value="{{ $quoteRequestId }}">
        @endif
        @if($packageTier)
            <input type="hidden" name="package" value="{{ $packageTier }}">
        @endif

        <div class="fx-card">
            @php($freelancerName = $service->seller->shop?->name ?: trim($service->seller->f_name . ' ' . $service->seller->l_name))
            <div class="fx-hire-freelancer">
                <img alt="{{ $freelancerName }}" src="{{ getStorageImages(path: $service->seller?->image_full_url, type: 'backend-profile') }}">
                <div>
                    <div style="font-weight:700; font-size:16px;">{{ $freelancerName }}</div>
                    <div style="display:flex; align-items:center; gap: var(--fx-s2); font-size:13px; color:var(--fx-muted); margin-top:4px; flex-wrap:wrap;">
                        <span class="fx-badge fx-badge--neutral">{{ $service->seller->freelancerLevelLabel() }}</span>
                        @if((int) $service->seller->freelancer_rating_count > 0)
                            <span><span style="color:var(--fx-amber);" aria-hidden="true">★</span> {{ round((float) $service->seller->freelancer_rating_avg, 1) }}
                            ({{ $service->seller->freelancer_rating_count }} {{ translate('reviews') }})</span>
                        @else
                            <span>{{ translate('no_reviews_yet') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <h2 class="fx-section-title">{{ translate('project_scope') }}</h2>
            <div class="fx-form">
                <div class="fx-field">
                    <label for="fx-hire-scope">{{ translate('describe_the_scope_of_work') }}</label>
                    <textarea name="scope" id="fx-hire-scope" rows="7" minlength="20" maxlength="5000" required
                              placeholder="{{ translate('share_goals_deliverables_timeline_and_any_reference_links') }}"
                              class="@error('scope') is-invalid @enderror">{{ old('scope') }}</textarea>
                    <span class="fx-hint">{{ translate('minimum') }} 20 {{ translate('characters') }} · {{ translate('maximum') }} 5000 {{ translate('characters') }}</span>
                    @error('scope')<span class="fx-error" role="alert">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <aside class="fx-card" style="position: sticky; top: 90px;">
            <h2 class="fx-section-title">{{ translate('order_summary') }}</h2>
            <div style="font-weight:700; line-height:1.35; margin-bottom: var(--fx-s3);">{{ $service->title }}</div>
            <div style="display:flex; flex-wrap:wrap; gap: var(--fx-s1); margin-bottom: var(--fx-s4);">
                @if($selectedPackage)
                    <span class="fx-type-tag" style="text-transform:none; letter-spacing:0;">{{ translate('selected_package') }}: {{ translate($selectedPackage->tier) }}</span>
                @elseif($quoteRequestId)
                    <span class="fx-type-tag" style="text-transform:none; letter-spacing:0;">{{ translate('accepted_quote') }}</span>
                @endif
                @if($service->delivery_time_days)
                    <span class="fx-type-tag" style="text-transform:none; letter-spacing:0;">{{ $service->delivery_time_days }} {{ translate('days') }}</span>
                @endif
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; gap: var(--fx-s3); padding: var(--fx-s4) 0; border-top:1px solid var(--fx-border); border-bottom:1px solid var(--fx-border); margin-bottom: var(--fx-s4);">
                <div>
                    <div class="fx-meta" style="text-transform:uppercase; font-size:11px; letter-spacing:.05em;">{{ translate('total') }}</div>
                    <div class="fx-meta" style="font-size:12px;">{{ translate('pay_securely_with_credit_or_debit_card') }}</div>
                </div>
                <div class="fx-price" style="font-size:26px;">{{ webCurrencyConverter($displayPrice) }}</div>
            </div>

            <div class="fx-field">
                <label>{{ translate('payment_method') }}</label>
                <div class="fx-payment-method">
                    <span class="fx-payment-radio" aria-hidden="true"></span>
                    <div>
                        <div style="font-weight:800; color:var(--fx-navy); font-size:15px;">Stripe</div>
                        <div class="fx-meta" style="font-size:12px;">{{ translate('selected') }}</div>
                    </div>
                </div>
            </div>
            <p class="fx-meta" style="font-size:13px; background:var(--fx-canvas); border:1px solid var(--fx-border); border-radius: var(--fx-radius-md); padding: var(--fx-s3);">
                {{ translate('you_will_be_redirected_to') }} Stripe {{ translate('to_securely_complete_payment_the_freelancer_is_hired_only_after_payment_succeeds') }}
            </p>

            <div class="fx-hire-actions">
                <a href="{{ route('hire-freelancer.show', $service->seller_id) }}" class="fx-btn fx-btn-outline">{{ translate('cancel') }}</a>
                <button type="submit" class="fx-btn">{{ translate('pay_and_hire') }}</button>
            </div>
        </aside>
    </form>
</div>
@endsection
