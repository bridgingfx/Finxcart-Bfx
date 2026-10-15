{{-- FinXCart: 3 mini promo banners row (e-commerce style) --}}
<section class="fx-mini-banners" aria-label="{{ translate('Promotions') }}">
    <div class="fx-container">
        <div class="fx-mini-banners__row">
            <a class="fx-mini-banner" href="{{ route('products') }}">
                <img src="{{ theme_asset(path: 'public/assets/front-end/img/fx-promos/promo-mt5.webp') }}" alt="{{ translate('MT5 trading solutions') }}" loading="lazy">
                <div class="fx-mini-banner__overlay">
                    <span class="fx-mini-banner__tag">{{ translate('Trading Tools') }}</span>
                    <span class="fx-mini-banner__title">{{ translate('MT5 Solutions') }}</span>
                    <span class="fx-mini-banner__cta">{{ translate('Shop now') }}</span>
                </div>
            </a>
            <a class="fx-mini-banner" href="{{ route('products') }}">
                <img src="{{ theme_asset(path: 'public/assets/front-end/img/fx-promos/promo-crypto.webp') }}" alt="{{ translate('Crypto payments') }}" loading="lazy">
                <div class="fx-mini-banner__overlay">
                    <span class="fx-mini-banner__tag">{{ translate('Payments') }}</span>
                    <span class="fx-mini-banner__title">{{ translate('Crypto Gateways') }}</span>
                    <span class="fx-mini-banner__cta">{{ translate('Shop now') }}</span>
                </div>
            </a>
            <a class="fx-mini-banner" href="{{ route('products') }}">
                <img src="{{ theme_asset(path: 'public/assets/front-end/img/fx-promos/promo-crm.webp') }}" alt="{{ translate('Broker CRM and KYC') }}" loading="lazy">
                <div class="fx-mini-banner__overlay">
                    <span class="fx-mini-banner__tag">{{ translate('Broker Solutions') }}</span>
                    <span class="fx-mini-banner__title">{{ translate('CRM & KYC') }}</span>
                    <span class="fx-mini-banner__cta">{{ translate('Shop now') }}</span>
                </div>
            </a>
        </div>
    </div>
</section>
