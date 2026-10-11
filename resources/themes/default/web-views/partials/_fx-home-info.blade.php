{{-- FinXCart redesign: how it works + vendor recruitment. Factual, no promises. --}}
<section class="fx-home__section fx-reveal" aria-labelledby="fx-home-how">
    <div class="fx-container fx-home__duo">
        <div>
            <h2 class="fx-home__section-title" id="fx-home-how">{{ translate('How FinXCart works') }}</h2>
            <ol class="fx-home__steps">
                <li class="fx-home__step">
                    <span class="fx-home__step-num" aria-hidden="true">1</span>
                    <div>
                        <h3 class="fx-home__step-title">{{ translate('Browse and compare') }}</h3>
                        <p class="fx-home__step-body">{{ translate('Search the catalog and compare software, integrations and expert services side by side.') }}</p>
                    </div>
                </li>
                <li class="fx-home__step">
                    <span class="fx-home__step-num" aria-hidden="true">2</span>
                    <div>
                        <h3 class="fx-home__step-title">{{ translate('Request a quote or buy') }}</h3>
                        <p class="fx-home__step-body">{{ translate('Each listing shows its price and delivery terms, so you know what you are getting before you commit.') }}</p>
                    </div>
                </li>
                <li class="fx-home__step">
                    <span class="fx-home__step-num" aria-hidden="true">3</span>
                    <div>
                        <h3 class="fx-home__step-title">{{ translate('Receive as listed') }}</h3>
                        <p class="fx-home__step-body">{{ translate('Delivery follows the listing — download, setup call or scheduled service, per the provider’s stated terms.') }}</p>
                    </div>
                </li>
            </ol>
        </div>
        <div class="fx-home__vendor">
            <h2>{{ translate('Sell on FinXCart') }}</h2>
            <p>{{ translate('List your software, integrations or expert services where businesses are already looking. Tell us about your business and we will help you get listed.') }}</p>
            <a class="fx-btn fx-btn-amber" href="{{ route('contacts') }}">{{ translate('Contact us to become a vendor') }}</a>
        </div>
    </div>
</section>
