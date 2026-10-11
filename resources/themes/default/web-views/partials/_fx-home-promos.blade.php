{{-- FinXCart redesign: promo tiles for two real categories (no fake discounts). Expects $promoTiles (array). --}}
@if(!empty($promoTiles))
    <section class="fx-promos fx-reveal" aria-label="{{ translate('Featured categories') }}">
        <div class="fx-container">
            <div class="fx-promos__grid">
                @foreach($promoTiles as $tile)
                    <a class="fx-promos__tile" href="{{ $tile['url'] }}">
                        <span class="fx-promos__media"><img src="{{ $tile['img'] }}" alt="" loading="lazy"></span>
                        <span class="fx-promos__copy">
                            <span class="fx-promos__title">{{ $tile['title'] }}</span>
                            <span class="fx-promos__cta">{{ translate('Shop now') }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
