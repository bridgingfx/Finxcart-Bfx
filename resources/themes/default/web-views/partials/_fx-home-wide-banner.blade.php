{{-- FinXCart: full-width promo banner between product sections --}}
<section class="fx-wide-banner" aria-label="{{ translate('Broker solutions promotion') }}">
    <div class="fx-container">
        <a class="fx-wide-banner__link" href="{{ route('products') }}">
            <img src="{{ theme_asset(path: 'public/assets/front-end/img/fx-promos/promo-wide.webp') }}" alt="{{ translate('Complete broker solution stack') }}" loading="lazy">
            <div class="fx-wide-banner__overlay">
                <span class="fx-wide-banner__tag">{{ translate('Broker Solutions') }}</span>
                <span class="fx-wide-banner__title">{{ translate('The complete stack to launch your brokerage') }}</span>
                <span class="fx-wide-banner__sub">{{ translate('CRM, KYC, payments, trading tools — instant download') }}</span>
                <span class="fx-mini-banner__cta">{{ translate('Explore now') }}</span>
            </div>
        </a>
    </div>
</section>
