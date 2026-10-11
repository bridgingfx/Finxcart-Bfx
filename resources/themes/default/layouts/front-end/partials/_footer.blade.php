{{-- ============================================================
     FinXCart light-first footer (SHELL worker — quinn/finxcart-light-redesign)
     Scope: .fx .fxf-* .  Policy links point at the dedicated,
     content-verified blade routes (privacy.policy, terms.of.use,
     terms.of.sale, warranty.policy, consumer.rights); admin-managed
     business-page slugs (about-us, vendor-policy, vendor-terms,
     terms-and-conditions) are kept since their DB content cannot be
     verified from the repo. No invented claims or badges.
     ============================================================ --}}
@php
    $fxfCompany   = $web_config['company_name'] ?? 'FinXCart';
    $fxfSupportEmail = 'support@finxcart.com';
    $fxfCurrencyModel = getWebConfig(name: 'currency_model');
    $fxfCurrencies = class_exists('\App\Models\Currency')
        ? Cache::remember(CACHE_FOR_ACTIVE_CURRENCY_LIST, CACHE_FOR_3_HOURS, fn() => \App\Models\Currency::where('status', 1)->get())
        : collect();
@endphp

<style>
/* ================= fx footer ================= */
.fx .fxf { background: var(--fx-navy); color: #FFFFFF; margin-top: var(--fx-s12); }
.fx .fxf-grid { display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr; gap: var(--fx-s8); padding: var(--fx-s12) 0 var(--fx-s8); }
@media (max-width: 1023px) { .fx .fxf-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 560px) { .fx .fxf-grid { grid-template-columns: 1fr; gap: var(--fx-s6); } }

.fx .fxf-logo { display: inline-block; background: #FFFFFF; border-radius: 12px; padding: 10px 16px; margin-bottom: var(--fx-s4); }
.fx .fxf-logo img { display: block; max-width: 150px; height: auto; }
.fx .fxf-contact { margin: 0 0 var(--fx-s4); font-size: 14px; line-height: 1.7; color: rgba(255, 255, 255, 0.82); font-style: normal; }
.fx .fxf-contact a { color: #FFFFFF; text-decoration: none; font-weight: 600; }
.fx .fxf-contact a:hover { text-decoration: underline; }

.fx .fxf-social { display: flex; gap: var(--fx-s2); list-style: none; margin: var(--fx-s4) 0 0; padding: 0; }
.fx .fxf-social a { display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 50%; background: rgba(255, 255, 255, 0.10); color: #FFFFFF; font-size: 18px; text-decoration: none; }
.fx .fxf-social a:hover { background: rgba(255, 255, 255, 0.22); }

.fx .fxf-col h3 { margin: 0 0 var(--fx-s3); font-size: 15px; font-weight: 700; color: #FFFFFF; }
.fx .fxf-col ul { list-style: none; margin: 0; padding: 0; }
.fx .fxf-col li a { display: inline-flex; align-items: center; min-height: 40px; color: rgba(255, 255, 255, 0.82); font-size: 14px; text-decoration: none; }
.fx .fxf-col li a:hover { color: #FFFFFF; text-decoration: underline; }

.fx .fxf-bottom { border-top: 1px solid rgba(255, 255, 255, 0.14); padding: var(--fx-s4) 0; }
.fx .fxf-bottom-inner { display: flex; flex-wrap: wrap; align-items: center; gap: var(--fx-s3) var(--fx-s6); }
.fx .fxf-legal { font-size: 13px; color: rgba(255, 255, 255, 0.70); margin-inline-end: auto; }
.fx .fxf-legal a { color: rgba(255, 255, 255, 0.85); }
.fx .fxf-tools { display: flex; align-items: center; gap: var(--fx-s3); }
.fx .fxf-select { min-height: 44px; font-size: 16px; padding: 8px 12px; border-radius: var(--fx-radius-md); border: 1px solid rgba(255, 255, 255, 0.25); background: rgba(255, 255, 255, 0.08); color: #FFFFFF; max-width: 180px; }
.fx .fxf-select option { color: #172B42; }
.fx .fxf-select:focus-visible { outline: 3px solid rgba(255, 255, 255, 0.5); outline-offset: 2px; }
.fx .fxf-pay { display: flex; align-items: center; gap: var(--fx-s1); }
.fx .fxf-pay img { height: 24px; width: auto; border-radius: 4px; background: #fff; padding: 2px 4px; }

/* gtranslater sits compactly in the footer tool row */
.fx .fxf-tools .gtranslate_wrapper .btn-head-menus { color: #fff !important; border-color: rgba(255,255,255,.25) !important; background: rgba(255,255,255,.08) !important; }

.fx .fxf a:focus-visible { outline: 3px solid rgba(255, 255, 255, 0.55); outline-offset: 2px; border-radius: 4px; }

@media (prefers-reduced-motion: no-preference) {
    .fx .fxf a, .fx .fxf-social a { transition: color 140ms ease, background-color 140ms ease; }
}
</style>

<footer class="fxf">
    <div class="fx-container">
        <div class="fxf-grid">
            {{-- company details --}}
            <div class="fxf-col">
                <a class="fxf-logo" href="{{ route('home') }}" aria-label="{{ $fxfCompany }}">
                    <img src="{{ getStorageImages(path: $web_config['footer_logo'], type: 'logo') }}" alt="{{ $fxfCompany }}">
                </a>
                <address class="fxf-contact">
                    {{ getWebConfig(name: 'shop_address') }}<br>
                    <a href="{{ 'tel:' . ($web_config['phone'] ?? '') }}">{{ getWebConfig(name: 'company_phone') }}</a><br>
                    <a href="mailto:{{ $fxfSupportEmail }}">{{ $fxfSupportEmail }}</a>
                </address>
                @if (!empty($web_config['social_media']))
                    <ul class="fxf-social">
                        @foreach ($web_config['social_media'] as $fxfItem)
                            <li>
                                <a href="{{ $fxfItem->link }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $fxfItem->name ?? 'Social link' }}">
                                    <span class="{{ $fxfItem->icon }}" aria-hidden="true"></span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- support --}}
            <nav class="fxf-col" aria-label="{{ translate('support') }}">
                <h3>{{ translate('support') }}</h3>
                <ul>
                    <li><a href="{{ route('contacts') }}">{{ translate('contact_us') }}</a></li>
                    <li><a href="{{ route('helpTopic') }}">{{ translate('faqs') }}</a></li>
                    <li><a href="{{ route('customer.service') }}">{{ translate('customer_service') }}</a></li>
                    <li><a href="{{ route('product.support') }}">{{ translate('product_support') }}</a></li>
                    <li><a href="{{ route('my.account.help') }}">{{ translate('my_account') }}</a></li>
                </ul>
            </nav>

            {{-- buyer policies (dedicated, content-verified pages) --}}
            <nav class="fxf-col" aria-label="{{ translate('buyer_policies') }}">
                <h3>{{ translate('buyer_policies') }}</h3>
                <ul>
                    <li><a href="{{ route('privacy.policy') }}">{{ translate('privacy_policy') }}</a></li>
                    <li><a href="{{ route('terms.of.use') }}">{{ translate('terms_of_use') }}</a></li>
                    <li><a href="{{ route('terms.of.sale') }}">{{ translate('terms_of_sale') }}</a></li>
                    <li><a href="{{ route('warranty.policy') }}">{{ translate('warranty_policy') }}</a></li>
                    <li><a href="{{ route('consumer.rights') }}">{{ translate('consumer_rights') }}</a></li>
                </ul>
            </nav>

            {{-- vendor entry --}}
            <nav class="fxf-col" aria-label="{{ translate('vendors') }}">
                <h3>{{ translate('vendors') }}</h3>
                <ul>
                    <li><a href="{{ route('vendor.auth.registration.index') }}">{{ translate('sell_with_us') }}</a></li>
                    <li><a href="{{ route('vendor.auth.login.index') }}">{{ translate('vendor_login') }}</a></li>
                    <li><a href="{{ route('business-page.view', ['slug' => 'vendor-policy']) }}">{{ translate('vendor_policy') }}</a></li>
                    <li><a href="{{ route('business-page.view', ['slug' => 'vendor-terms']) }}">{{ translate('vendor_terms') }}</a></li>
                    <li><a href="{{ route('hire-freelancer') }}">{{ translate('freelancers') }}</a></li>
                </ul>
            </nav>

            {{-- company / legal --}}
            <nav class="fxf-col" aria-label="{{ translate('company') }}">
                <h3>{{ translate('company') }}</h3>
                <ul>
                    <li><a href="{{ route('business-page.view', ['slug' => 'about-us']) }}">{{ translate('about_us') }}</a></li>
                    <li><a href="{{ route('business-page.view', ['slug' => 'terms-and-conditions']) }}">{{ translate('terms_and_conditions') }}</a></li>
                    <li><a href="https://www.linkedin.com/company/finxcart/jobs/" target="_blank" rel="noopener noreferrer">{{ translate('careers') }}</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="fxf-bottom">
        <div class="fx-container fxf-bottom-inner">
            <p class="fxf-legal">&copy; {{ date('Y') }} {{ $fxfCompany }} — {{ translate('all_rights_reserved') }}</p>
            <div class="fxf-tools">
                @if ($fxfCurrencyModel == 'multi_currency' && $fxfCurrencies->count())
                    <label class="fx-sr" for="fxFooterCurrency">{{ translate('currency') }}</label>
                    <select class="fxf-select js-fx-currency" id="fxFooterCurrency" aria-label="{{ translate('currency') }}">
                        @foreach ($fxfCurrencies as $fxfCur)
                            <option value="{{ $fxfCur->code }}" {{ session('currency_code') == $fxfCur->code ? 'selected' : '' }}>{{ $fxfCur->code }}</option>
                        @endforeach
                    </select>
                @endif
                @include('layouts.front-end.partials.gtranslater')
            </div>
            <div class="fxf-pay" aria-label="{{ translate('accepted_payments') }}">
                <img src="{{ theme_asset(path: 'public/assets/front-end/img/visa.jpg') }}" alt="Visa">
                <img src="{{ theme_asset(path: 'public/assets/front-end/img/mastercard.jpg') }}" alt="Mastercard">
                <img src="{{ theme_asset(path: 'public/assets/front-end/img/paypal.jpg') }}" alt="PayPal">
                <img src="{{ theme_asset(path: 'public/assets/front-end/img/skrill.jpg') }}" alt="Skrill">
            </div>
        </div>
    </div>
</footer>

@include('layouts.front-end.partials._fx-mobile-bottom-nav')
