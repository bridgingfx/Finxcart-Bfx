@extends('layouts.vendor.app')

@section('title', translate('Choose_Your_Seller_Plan'))

@push('css_or_js')
<style>
/* ─── Pricing page wrapper ─────────────────────────────── */
.pricing-wrap {
    max-width: 1120px;
    margin: 0 auto;
    padding: 0 1rem 3rem;
}

/* ─── Hero header ──────────────────────────────────────── */
.pricing-hero {
    text-align: center;
    padding: 2rem 0 2.5rem;
}
.pricing-hero h1 {
    font-size: clamp(1.6rem, 3vw, 2.4rem);
    font-weight: 800;
    color: #0f172a;
    margin-bottom: .4rem;
}
.pricing-hero p {
    color: #64748b;
    font-size: 1rem;
    margin: 0;
}
.trial-status-card {
    max-width: 760px;
    margin: 1.25rem auto 0;
    padding: .85rem 1rem;
    display: flex;
    align-items: center;
    gap: .8rem;
    border: 1px solid #dbe7f3;
    border-radius: 14px;
    text-align: left;
    background: #fff;
    box-shadow: 0 8px 22px rgba(15,23,42,.06);
}
.trial-status-card i {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #fff;
    background: #1684cf;
}
.trial-status-card.is-used i { background: #64748b; }
.trial-status-card strong { display: block; color: #172b43; font-size: .86rem; }
.trial-status-card span { color: #64748b; font-size: .78rem; }

/* ─── Banner ───────────────────────────────────────────── */
.pricing-banner {
    margin-top: 20px;
    border-radius: 14px;
    margin-bottom: 2rem;
    padding: 1rem 1.25rem;
    background: #eff6ff;
    border-left: 4px solid #2563eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .75rem;
}

/* ─── Card grid ────────────────────────────────────────── */
.pricing-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    align-items: stretch;
    padding-top: 14px;
}
@media (max-width: 991px) {
    .pricing-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 575px) {
    .pricing-grid { grid-template-columns: 1fr; }
}

/* ─── Individual card ──────────────────────────────────── */
.plan-card {
    position: relative;
    background: #fff;
    border-radius: 22px;
    border: 2px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    overflow: visible;
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
}
.plan-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 48px rgba(0,0,0,.10);
}
.plan-card.is-popular {
    border-color: #16a34a;
    box-shadow: 0 0 0 4px rgba(22,163,74,.12);
}
.plan-card.is-best {
    border-color: #d97706;
    box-shadow: 0 0 0 4px rgba(217,119,6,.12);
}
.plan-card.is-current {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 4px rgba(14,165,233,.15);
}
.plan-card.is-flagship {
    border-color: #7c3aed;
    box-shadow: 0 0 0 4px rgba(124,58,237,.12);
}

/* floating badge above card */
.plan-badge {
    position: absolute;
    top: -14px;
    left: 50%;
    transform: translateX(-50%);
    white-space: nowrap;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .5px;
    padding: 4px 14px;
    border-radius: 50px;
    z-index: 2;
}
.plan-badge.badge-popular  { background:#16a34a; color:#fff; }
.plan-badge.badge-best     { background:#d97706; color:#fff; }
.plan-badge.badge-current  { background:#0ea5e9; color:#fff; }
.plan-badge.badge-flagship { background:#7c3aed; color:#fff; }

/* ─── Card header band ─────────────────────────────────── */
.plan-header {
    border-radius: 20px 20px 0 0;
    padding: 1.5rem 1.5rem 1rem;
    text-align: center;
}
.plan-header.hdr-starter  { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); }
.plan-header.hdr-growth   { background: linear-gradient(135deg, #14532d 0%, #16a34a 100%); }
.plan-header.hdr-pro      { background: linear-gradient(135deg, #78350f 0%, #d97706 100%); }
.plan-header.hdr-elite    { background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 100%); }
.plan-header.hdr-default  { background: linear-gradient(135deg, #374151 0%, #6b7280 100%); }
.plan-header.hdr-current  { background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%); }

.plan-header h3 {
    color: #fff;
    font-size: 1.15rem;
    font-weight: 700;
    margin: 0 0 .2rem;
    letter-spacing: .2px;
}
.plan-header .plan-subtitle {
    color: rgba(255,255,255,.75);
    font-size: .78rem;
    margin: 0;
}

/* ─── Card body ────────────────────────────────────────── */
.plan-body {
    padding: 1.5rem 1.5rem 1.25rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    flex: 1;
}

/* price block */
.plan-price {
    margin-bottom: .25rem;
}
.plan-price .amount {
    font-size: 2.8rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1;
}
.plan-price .currency {
    font-size: 1.4rem;
    font-weight: 700;
    color: #0f172a;
    vertical-align: top;
    margin-top: .5rem;
    display: inline-block;
}
.plan-price .period {
    font-size: .85rem;
    color: #94a3b8;
}
.plan-free-trial {
    display: inline-block;
    background: rgba(22,163,74,.1);
    color: #15803d;
    font-size: .72rem;
    font-weight: 600;
    padding: 2px 10px;
    border-radius: 50px;
    margin-bottom: .5rem;
}

/* commission pill */
.plan-commission {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: .5rem .9rem;
    margin: .75rem 0 1rem;
    width: 100%;
    text-align: center;
}
.plan-commission .keep { color: #16a34a; font-weight: 700; font-size: .9rem; }
.plan-commission .fee  { color: #64748b; font-size: .78rem; margin: 0; }

/* slots pill */
.plan-slots {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .4rem;
    background: #eff6ff;
    border-radius: 50px;
    padding: .35rem .9rem;
    margin-bottom: 1rem;
    font-size: .82rem;
    color: #1e40af;
    font-weight: 600;
    width: 100%;
}
.plan-slots i { font-size: .9rem; }

/* features list */
.plan-features {
    list-style: none;
    padding: 0;
    margin: 0 0 1.25rem;
    text-align: left;
    width: 100%;
}
.plan-features li {
    font-size: .82rem;
    color: #475569;
    padding: .28rem 0;
    display: flex;
    align-items: flex-start;
    gap: .5rem;
    border-bottom: 1px solid #f1f5f9;
}
.plan-features li:last-child { border-bottom: none; }
.plan-features li::before {
    content: '✓';
    color: #16a34a;
    font-weight: 700;
    flex-shrink: 0;
    margin-top: 1px;
}

/* CTA button */
.plan-btn {
    display: block;
    width: 100%;
    padding: .7rem 1rem;
    border-radius: 12px;
    font-size: .9rem;
    font-weight: 700;
    text-align: center;
    border: none;
    cursor: pointer;
    transition: filter .18s, transform .12s;
    margin-top: auto;
}
.plan-btn:hover   { filter: brightness(1.08); transform: scale(1.02); }
.plan-btn:active  { transform: scale(.98); }
.plan-btn.btn-starter  { background: linear-gradient(90deg,#1e40af,#3b82f6); color:#fff; }
.plan-btn.btn-growth   { background: linear-gradient(90deg,#15803d,#22c55e); color:#fff; }
.plan-btn.btn-pro      { background: linear-gradient(90deg,#b45309,#f59e0b); color:#fff; }
.plan-btn.btn-elite    { background: linear-gradient(90deg,#4c1d95,#8b5cf6); color:#fff; }
.plan-btn.btn-current  { background: #0ea5e9; color:#fff; cursor:default; }
.plan-btn.btn-default  { background: #e2e8f0; color:#475569; }
.plan-btn[disabled]    { opacity:.85; cursor:not-allowed; transform:none!important; }

/* ─── Freelancer row ───────────────────────────────────── */
.freelancer-row {
    margin-top: 2rem;
    border-radius: 16px;
    border: 2px dashed #cbd5e1;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    background: #f8fafc;
}
.freelancer-row h5 { font-weight: 700; margin-bottom: .2rem; color:#0f172a; }

/* ─── Footer note ──────────────────────────────────────── */
.pricing-note {
    text-align: center;
    color: #94a3b8;
    font-size: .78rem;
    margin-top: 1.5rem;
}
</style>
@endpush

@section('content')
<div class="pricing-wrap">

    {{-- Pending approval notice --}}
    @if(auth('seller')->user()?->status !== 'approved')
        <div class="alert alert-warning d-flex align-items-center gap-3 mt-3" style="border-radius:10px;border:1.5px solid #f59e0b;">
            <i class="tio-warning-outlined fs-3 text-warning"></i>
            <div>
                <strong>{{ translate('Account_Pending_Approval') }}</strong>
                <p class="mb-0 text-muted" style="font-size:13px;">{{ translate('Your_account_is_awaiting_admin_approval._You_will_be_able_to_purchase_a_tier_once_your_account_is_approved.') }}</p>
            </div>
        </div>
    @endif

    {{-- Pending product banner --}}
    @if(session('pending_product_session'))
        <div class="pricing-banner">
            <div>
                <strong>{{ translate('Almost_there!_Your_product_is_ready.') }}</strong>
                <span class="text-muted ms-2">{{ translate('Select_a_tier_below_to_publish_it_on_the_marketplace.') }}</span>
            </div>
            <a href="{{ route('vendor.products.add') }}" class="btn btn-sm btn-outline-primary">
                {{ translate('Back_to_Product_Form') }}
            </a>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success rounded-3 mb-3">{{ session('success') }}</div>
        @php
            session()->forget('success');
        @endphp
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-3 mb-3">{{ session('error') }}</div>
        @php
            session()->forget('error');
        @endphp
    @endif
    @if(session('warning'))
        <div class="alert alert-warning rounded-3 mb-3">{{ session('warning') }}</div>
        @php
            session()->forget('warning');
        @endphp
    @endif

    @if($tierExpired)
        <div class="alert alert-danger d-flex align-items-center gap-3 mb-4 rounded-3">
            <i class="tio-warning fs-24"></i>
            <div>
                <strong>{{ translate('Your_subscription_has_ended.') }}</strong>
                {{ translate('Product_listing_is_currently_blocked._Subscribe_below_to_continue.') }}
            </div>
        </div>
    @endif

    @if($sellerType === 'freelancer')
        <div class="card border-success mb-4 shadow-sm" style="border-radius:16px;">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3 py-3">
                <div>
                    <span class="badge bg-success mb-1">{{ translate('Active_Plan') }}</span>
                    <h5 class="mb-1 fw-bold">{{ translate('Freelancer_—_Free_Forever') }}</h5>
                    <p class="text-muted mb-0 small">
                        {{ translate('Unlimited_listings_·_No_monthly_fee_·_15%_commission_per_sale') }}
                    </p>
                </div>
                <span class="badge bg-success fs-14 px-3 py-2">{{ translate('Active') }}</span>
            </div>
        </div>
        <p class="text-muted small mb-3 fw-semibold text-uppercase">{{ translate('Optional_Upgrade_Plans') }}</p>
    @endif

    {{-- Hero --}}
    <div class="pricing-hero">
        <h1>Choose your seller plan</h1>
        <p>Pay once a month &middot; List your products &middot; Keep 85% of every sale</p>
        <div class="trial-status-card {{ $trialEligible ? '' : 'is-used' }}">
            <i class="{{ $trialEligible ? 'tio-gift' : 'tio-checkmark-circle' }}"></i>
            <div>
                @if($trialEligible)
                    <strong>{{ translate('Your_one-time_60-day_trial_is_available.') }}</strong>
                    <span>{{ translate('Claim_it_once_on_your_first_paid_tier._Other_tiers_require_payment.') }}</span>
                @elseif(isset($activeTier) && $activeTier->status === 'trial')
                    <strong>{{ translate('Your_one-time_60-day_trial_is_active_on') }} {{ $activeTier->tier->name ?? translate('your_current_tier') }}.</strong>
                    <span>{{ translate('Additional_paid_tiers_require_payment.') }}</span>
                @else
                    <strong>{{ translate('Your_60-day_trial_has_already_been_used.') }}</strong>
                    <span>{{ translate('Selecting_any_paid_tier_now_requires_payment.') }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Tier cards --}}
    <div class="pricing-grid">
        @forelse($tiers as $tier)
            @php
                $isCurrentPlan = isset($activeTier) && $activeTier->product_tier_id == $tier->id;
                $slots         = (int) $tier->listings_per_fee;
                $price         = (float) $tier->monthly_fee_usd;
                $isFeaturedVendorTier = (bool) ($tier->is_featured_vendor ?? false);
                $featuredQuota = (int) ($tier->featured_product_quota ?? 0);
                $isMostPopular = $slots === 3;
                $isBestValue   = $slots === 6;
                $isStarter     = $slots === 1 && $price <= 50;
                // Any tier flagged "Featured Vendor" in the admin gets the flagship treatment,
                // regardless of what it's named.
                $isElite       = $isFeaturedVendorTier || stripos($tier->name, 'elite') !== false;

                // header colour class
                if ($isCurrentPlan)   $hdrClass = 'hdr-current';
                elseif ($isStarter)   $hdrClass = 'hdr-starter';
                elseif ($isMostPopular) $hdrClass = 'hdr-growth';
                elseif ($isBestValue)   $hdrClass = 'hdr-pro';
                elseif ($isElite)       $hdrClass = 'hdr-elite';
                else                    $hdrClass = 'hdr-default';

                // card state class
                $cardClass = '';
                if ($isCurrentPlan) $cardClass = 'is-current';
                elseif ($isElite) $cardClass = 'is-flagship';
                elseif ($isMostPopular) $cardClass = 'is-popular';
                elseif ($isBestValue)   $cardClass = 'is-best';

                // button class
                if ($isCurrentPlan)     $btnClass = 'btn-current';
                elseif ($isStarter)     $btnClass = 'btn-starter';
                elseif ($isMostPopular) $btnClass = 'btn-growth';
                elseif ($isBestValue)   $btnClass = 'btn-pro';
                elseif ($isElite)       $btnClass = 'btn-elite';
                else                    $btnClass = 'btn-default';

                // button label
                if ($isCurrentPlan)     $btnText = translate('Add_Plan');
                elseif ($isStarter)     $btnText = 'Start with Starter';
                elseif ($isMostPopular) $btnText = 'Go Growth';
                elseif ($isBestValue)   $btnText = 'Go Pro';
                elseif ($isElite)       $btnText = 'Go Elite';
                elseif ($price == 0)    $btnText = 'Your Free Plan';
                else                    $btnText = translate('Select_This_Tier');

                // slot label
                $slotLabel = $slots > 0
                    ? $slots . ' listing slot' . ($slots != 1 ? 's' : '')
                    : 'Unlimited listings';

                // features
                $features = [
                    'Unlimited product images',
                    'Full product description editor',
                    'Order management dashboard',
                    'Buyer email communication',
                ];
                if ($isStarter) {
                    array_unshift($features, '1 active listing slot');
                    $features[] = 'Sales analytics (views & clicks)';
                } elseif ($isMostPopular) {
                    array_unshift($features, '3 active listing slots');
                    $features[] = 'Analytics: views, clicks & conversions';
                    $features[] = 'Product variants (size, colour)';
                } elseif ($isBestValue) {
                    array_unshift($features, '6 active listing slots');
                    $features[] = 'Full analytics suite';
                    $features[] = 'Advanced order management';
                    $features[] = 'Product variants + bulk pricing';
                    $features[] = 'Featured placement eligibility';
                    $features[] = 'Priority support + account review';
                } elseif ($isElite) {
                    array_unshift($features, $slotLabel);
                    $features[] = 'Full analytics suite';
                    $features[] = 'Priority support + account review';
                } else {
                    array_unshift($features, $slotLabel);
                    $features[] = 'Analytics: views only';
                    $features[] = 'API access + integrations';
                }

                // Featured Vendor perks — driven by the real tier flags, shown on any tier that has them.
                if ($isFeaturedVendorTier) {
                    $features[] = 'Featured Vendor — shop badge + top placement in vendor directory';
                }
                if ($featuredQuota > 0) {
                    $features[] = $featuredQuota . ' of your products automatically featured on the homepage';
                }
            @endphp

            <div class="plan-card {{ $cardClass }}">

                {{-- Floating badge --}}
                @if($isCurrentPlan)
                    <div class="plan-badge badge-current">✓ Current Plan</div>
                @elseif($isElite)
                    <div class="plan-badge badge-flagship">★ Flagship</div>
                @elseif($isMostPopular)
                    <div class="plan-badge badge-popular">★ Most Popular</div>
                @elseif($isBestValue)
                    <div class="plan-badge badge-best">★ Best Value</div>
                @endif

                {{-- Header --}}
                <div class="plan-header {{ $hdrClass }}">
                    <h3>{{ $tier->name }}</h3>
                    <p class="plan-subtitle">{{ $tier->ideal_product_types ?? 'General purpose sellers' }}</p>
                </div>

                {{-- Body --}}
                <div class="plan-body">

                    {{-- Price --}}
                    <div class="plan-price mt-2">
                        @if($price > 0)
                            <span class="currency">$</span><span class="amount">{{ (int)$price }}</span>
                            <span class="period">/month</span>
                        @else
                            <span class="amount" style="font-size:2rem;">Free</span>
                        @endif
                    </div>

                    @if($isCurrentPlan && isset($activeTier) && $activeTier->status === 'trial')
                        <span class="plan-free-trial">60-day trial active</span>
                    @elseif($price > 0 && $trialEligible)
                        <span class="plan-free-trial">One-time 60-day trial available</span>
                    @elseif($price > 0)
                        <span class="plan-free-trial" style="background:#f1f5f9;color:#64748b;">Trial claimed · payment required</span>
                    @else
                        <div style="height:20px;"></div>
                    @endif

                    {{-- Commission --}}
                    <div class="plan-commission">
                        <span class="keep">You keep 85%</span> of every sale
                        <p class="fee">15% platform commission deducted automatically</p>
                    </div>

                    {{-- Slots --}}
                    <div class="plan-slots">
                        <i class="tio-layers-outlined"></i>
                        {{ $slotLabel }}
                    </div>

                    {{-- Features --}}
                    <ul class="plan-features">
                        @foreach($features as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    </ul>

                    {{-- CTA --}}
                    <form action="{{ route('vendor.tier.select') }}" method="POST" style="width:100%" novalidate>
                        @csrf
                        <input type="hidden" name="tier_id" value="{{ $tier->id }}">
                        <input type="hidden" name="payment_method" value="Online Gateway">
                        <button type="submit" class="plan-btn {{ $btnClass }}">{{ $btnText }}</button>
                    </form>
                    @if($price > 0 && $trialEligible)
                        <p class="text-muted small mt-2 mb-0">Your first paid tier includes the one-time 60-day trial.</p>
                    @elseif($price > 0)
                        <p class="text-muted small mt-2 mb-0">Your one-time trial was already claimed. Payment starts now.</p>
                    @endif

                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center rounded-3">
                    {{ translate('No_available_tier_plans_found.') }}
                </div>
            </div>
        @endforelse
    </div>

    {{-- Footer note --}}
    <p class="pricing-note">
        A 15% platform commission is deducted automatically on each completed order.<br>
        No hidden fees. Cancel or upgrade at any time.
    </p>

</div>
@endsection
