@extends('layouts.front-end.app')

@section('title', translate('hire') . ' - ' . $service->title)

@push('css_or_js')
    <style>
        .hire-page { background: linear-gradient(180deg, #f8fafc 0%, #eef3f8 100%); padding: 28px 0 64px; }
        .hire-shell { max-width: 1120px; margin: 0 auto; }
        .hire-hero {
            display: flex; justify-content: space-between; align-items: flex-end; gap: 18px; flex-wrap: wrap;
            margin-bottom: 22px; padding: 26px; border-radius: 20px;
            background: linear-gradient(120deg, #17395e, #0f766e); color: #fff;
            box-shadow: 0 24px 58px rgba(15, 23, 42, .18);
        }
        .hire-page-title { color: #fff; font-weight: 900; font-size: 30px; margin: 0 0 6px; }
        .hire-page-subtitle { color: rgba(255,255,255,.78); margin: 0; max-width: 620px; }
        .hire-secure-pill { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; padding: 8px 14px; border-radius: 999px; background: rgba(255,255,255,.14); color: #fff; font-weight: 800; }
        .hire-layout { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 22px; align-items: start; }
        .hire-card { background: #fff; border: 1px solid #e7ebf0; border-radius: 18px; padding: 26px; box-shadow: 0 16px 36px rgba(15, 23, 42, .08); }
        .hire-card-title { font-weight: 900; font-size: 20px; color: #17395e; margin-bottom: 16px; }
        .hire-freelancer-row { display: flex; align-items: center; gap: 14px; padding: 14px; border: 1px solid #e7ebf0; border-radius: 16px; background: #f8fafc; margin-bottom: 18px; }
        .hire-freelancer-avatar { width: 58px; height: 58px; border-radius: 50%; object-fit: cover; background: #eef2ff; flex-shrink: 0; border: 3px solid #fff; box-shadow: 0 0 0 1px #e7ebf0; }
        .hire-freelancer-name { font-weight: 900; color: #1f2937; font-size: 16px; }
        .hire-freelancer-meta { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #64748b; margin-top: 4px; flex-wrap: wrap; }
        .hire-freelancer-level { background: #eef2ff; color: #3730a3; font-weight: 800; border-radius: 999px; padding: 3px 9px; font-size: 10px; }
        .hire-freelancer-rating { color: #f59e0b; font-weight: 900; }
        .scope-card textarea { border-radius: 14px; border-color: #dbe3ee; min-height: 190px; padding: 16px; }
        .scope-card textarea:focus { border-color: #17395e; box-shadow: 0 0 0 4px rgba(23, 57, 94, .08); }
        .hire-form-label { font-weight: 800; font-size: 13px; color: #1f2937; margin-bottom: 8px; display: block; }
        .checkout-summary { position: sticky; top: 20px; overflow: hidden; padding: 0; }
        .checkout-summary-head { padding: 20px; background: #102a43; color: #fff; }
        .checkout-summary-head h2 { color: #fff; margin: 0; font-size: 18px; font-weight: 900; }
        .checkout-summary-body { padding: 20px; }
        .service-summary-title { font-weight: 900; color: #1f2937; line-height: 1.35; margin-bottom: 10px; }
        .service-summary-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 18px; }
        .service-summary-chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 999px; background: #f1f5f9; color: #475569; font-size: 11px; font-weight: 800; }
        .checkout-total { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; padding: 16px 0; border-top: 1px solid #edf1f6; border-bottom: 1px solid #edf1f6; margin-bottom: 18px; }
        .checkout-total-label { color: #64748b; font-size: 12px; font-weight: 800; text-transform: uppercase; }
        .checkout-total-price { color: #0f766e; font-size: 28px; font-weight: 900; white-space: nowrap; }
        .hire-payment-method { display: flex; align-items: center; gap: 12px; background: #f8faff; border: 1px solid #c7d2fe; border-radius: 14px; padding: 14px; box-shadow: 0 0 0 4px rgba(99, 102, 241, .06); }
        .hire-payment-radio { width: 22px; height: 22px; border-radius: 50%; border: 2px solid #4f46e5; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
        .hire-payment-radio::after { content: ''; width: 10px; height: 10px; border-radius: 50%; background: #4f46e5; }
        .stripe-badge { display: inline-flex; align-items: center; gap: 7px; font-weight: 900; color: #4f46e5; font-size: 15px; }
        .hire-payment-method-desc { font-size: 12px; color: #64748b; margin-top: 2px; }
        .hire-payment-note { display: flex; align-items: flex-start; gap: 10px; background: #ecfdf5; border: 1px solid #bbf7d0; border-radius: 14px; padding: 12px; font-size: 12px; color: #166534; font-weight: 700; margin-top: 14px; }
        .hire-actions { display: flex; gap: 10px; margin-top: 20px; }
        .hire-actions .btn { border-radius: 12px; font-weight: 900; padding: 12px 18px; flex: 1; display: inline-flex; justify-content: center; align-items: center; gap: 8px; }
        .hire-actions .btn--primary { box-shadow: 0 12px 24px rgba(23, 57, 94, .22); }
        @media (max-width: 991.98px) { .hire-layout { grid-template-columns: 1fr; } .checkout-summary { position: static; } }
        @media (max-width: 575.98px) { .hire-hero, .hire-card { padding: 18px; border-radius: 16px; } .hire-page-title { font-size: 24px; } .hire-actions { flex-direction: column-reverse; } .checkout-total { align-items: flex-start; flex-direction: column; } }
    
        /* Extra checkout redesign pass */
        .hire-page, .hire-page * { box-sizing: border-box; }
        .hire-page { overflow-x: hidden; background: linear-gradient(180deg, #f4f8ff 0%, #fff7ed 48%, #f8fafc 100%); }
        .hire-shell { max-width: 1180px; width: 100%; }
        .hire-hero { background: linear-gradient(120deg, #17395e, #2563eb 48%, #f97316); box-shadow: 0 24px 58px rgba(15, 23, 42, .18); }
        .hire-card { border-color: rgba(148, 163, 184, .22); border-radius: 20px; box-shadow: 0 18px 44px rgba(15, 23, 42, .09); }
        .hire-freelancer-row { background: linear-gradient(135deg, #eff6ff, #fff7ed); border-color: rgba(148, 163, 184, .22); }
        .hire-freelancer-avatar-wrap { position: relative; width: 58px; height: 58px; flex: 0 0 auto; }
        .hire-freelancer-avatar-wrap .hire-freelancer-avatar { width: 58px; height: 58px; }
        .freelancer-online-dot { position: absolute; right: -1px; bottom: -1px; width: 15px; height: 15px; border-radius: 50%; background: #22c55e; border: 2px solid #fff; box-shadow: 0 0 0 4px rgba(34,197,94,.18); }
        .hire-online-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 999px; background: #f1f5f9; color: #64748b; font-weight: 900; font-size: 10px; }
        .hire-online-pill span { width: 7px; height: 7px; border-radius: 50%; background: #cbd5e1; }
        .hire-online-pill.is-online { background: #ecfdf5; color: #15803d; }
        .hire-online-pill.is-online span { background: #22c55e; }
        .service-summary-chip:nth-child(3n+1) { background: #eff6ff; color: #1d4ed8; }
        .service-summary-chip:nth-child(3n+2) { background: #fff7ed; color: #c2410c; }
        .service-summary-chip:nth-child(3n+3) { background: #ecfdf5; color: #047857; }
        .checkout-summary-head { background: linear-gradient(135deg, #17395e, #2563eb); }
        .checkout-total-price { color: #f97316; }
        @media (max-width: 767.98px) {
            .hire-page { padding: 16px 0 42px; }
            .hire-layout { gap: 16px; }
            .hire-freelancer-row { align-items: flex-start; }
            .hire-freelancer-meta { gap: 6px; }
        }
    </style>
@endpush

@section('content')
    <div class="hire-page">
        <div class="container hire-shell">
            <div class="hire-hero">
                <div>
                    <h1 class="hire-page-title">{{ translate('checkout') }}</h1>
                    <p class="hire-page-subtitle">Review your project scope and complete secure payment.</p>
                </div>
                <span class="hire-secure-pill"><i class="fas fa-shield-alt"></i> Secure checkout</span>
            </div>

            <form action="{{ route('hire.store', $service->id) }}" method="POST" id="hire-form" class="hire-layout" novalidate>
                @csrf
                @if($quoteRequestId)
                    <input type="hidden" name="quote_request_id" value="{{ $quoteRequestId }}">
                @endif
                @if($packageTier)
                    <input type="hidden" name="package" value="{{ $packageTier }}">
                @endif

                <div class="hire-card scope-card">
                    @php($freelancerName = $service->seller->shop?->name ?: trim($service->seller->f_name . ' ' . $service->seller->l_name))
                    @php($isFreelancerOnline = $service->seller && ($service->seller->status === 'approved') && (($service->seller->account_status ?? 'active') === 'active'))
                    <div class="hire-freelancer-row">
                        <span class="hire-freelancer-avatar-wrap" title="{{ $freelancerName }}">
                            <img class="hire-freelancer-avatar" alt="" src="{{ getStorageImages(path: $service->seller?->image_full_url, type: 'backend-profile') }}">
                            @if($isFreelancerOnline)
                                <span class="freelancer-online-dot" title="Online now"></span>
                            @endif
                        </span>
                        <div>
                            <div class="hire-freelancer-name">{{ $freelancerName }}</div>
                            <div class="hire-freelancer-meta">
                                <span class="hire-freelancer-level">{{ $service->seller->freelancerLevelLabel() }}</span>
                                <span class="hire-online-pill {{ $isFreelancerOnline ? 'is-online' : '' }}"><span></span>{{ $isFreelancerOnline ? 'Online now' : translate('available') }}</span>
                                @if((int) $service->seller->freelancer_rating_count > 0)
                                    <span class="hire-freelancer-rating"><i class="fas fa-star"></i> {{ round((float) $service->seller->freelancer_rating_avg, 1) }}</span>
                                    <span>({{ $service->seller->freelancer_rating_count }} {{ translate('reviews') }})</span>
                                @else
                                    <span>{{ translate('no_reviews_yet') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="hire-card-title">Project scope</div>
                    <div class="form-group mb-0">
                        <label class="hire-form-label">{{ translate('describe_the_scope_of_work') }}</label>
                        <textarea name="scope" class="form-control @error('scope') is-invalid @enderror" rows="7" minlength="20" maxlength="5000" required placeholder="Share goals, deliverables, timeline, and any reference links">{{ old('scope') }}</textarea>
                        @error('scope')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>

                <aside class="hire-card checkout-summary">
                    <div class="checkout-summary-head">
                        <h2>Order summary</h2>
                    </div>
                    <div class="checkout-summary-body">
                        <div class="service-summary-title">{{ $service->title }}</div>
                        <div class="service-summary-meta">
                            @if($selectedPackage)
                                <span class="service-summary-chip"><i class="fas fa-box-open"></i>{{ translate('selected_package') }}: {{ translate($selectedPackage->tier) }}</span>
                            @elseif($quoteRequestId)
                                <span class="service-summary-chip"><i class="fas fa-file-invoice-dollar"></i>{{ translate('accepted_quote') }}</span>
                            @endif
                            @if($service->delivery_time_days)
                                <span class="service-summary-chip"><i class="fas fa-clock"></i>{{ $service->delivery_time_days }} {{ translate('days') }}</span>
                            @endif
                        </div>

                        <div class="checkout-total">
                            <div>
                                <div class="checkout-total-label">{{ translate('total') }}</div>
                                <div class="small text-muted">{{ translate('pay_securely_with_credit_or_debit_card') }}</div>
                            </div>
                            <div class="checkout-total-price">{{ webCurrencyConverter($displayPrice) }}</div>
                        </div>

                        <label class="hire-form-label">{{ translate('payment_method') }}</label>
                        <div class="hire-payment-method">
                            <span class="hire-payment-radio" aria-hidden="true"></span>
                            <div>
                                <span class="stripe-badge"><i class="fas fa-lock"></i> Stripe</span>
                                <div class="hire-payment-method-desc">{{ translate('selected') }}</div>
                            </div>
                        </div>
                        <div class="hire-payment-note">
                            <i class="fas fa-info-circle"></i>
                            <span>{{ translate('you_will_be_redirected_to') }} Stripe {{ translate('to_securely_complete_payment_the_freelancer_is_hired_only_after_payment_succeeds') }}</span>
                        </div>

                        <div class="hire-actions">
                            <a href="{{ route('hire-freelancer.show', $service->seller_id) }}" class="btn btn-outline-secondary">{{ translate('cancel') }}</a>
                            <button type="submit" class="btn btn--primary"><i class="fas fa-lock"></i>{{ translate('pay_and_hire') }}</button>
                        </div>
                    </div>
                </aside>
            </form>
        </div>
    </div>
@endsection





