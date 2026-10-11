{{-- FinXCart redesign: "Recommended for you" horizontal product row. Expects $recommended (collection of products). --}}
@if($recommended->isNotEmpty())
    <section class="fx-rec fx-reveal" aria-labelledby="fx-rec-title">
        <div class="fx-container">
            <div class="fx-sec-head">
                <h2 class="fx-sec-title" id="fx-rec-title">{{ translate('Recommended for you') }}</h2>
                <a class="fx-sec-link fx-sec-link--pill" href="{{ route('products') }}">{{ translate('Explore more') }}</a>
            </div>
            <div class="fx-rec__row" tabindex="0">
                @foreach($recommended as $product)
                    @php
                        try {
                            $priceText = \App\Utils\BackEndHelper::set_symbol($product->unit_price);
                        } catch (\Throwable $e) {
                            $priceText = number_format((float) $product->unit_price, 2);
                        }
                        $fxType = \App\Utils\FxListing::type($product);
                        $isQuote = $fxType['key'] === 'custom_service';
                        if ($isQuote) {
                            $priceText = translate('request_quote');
                        }
                    @endphp
                    <a class="fx-card fx-rec__card" href="{{ route('product', $product->slug) }}">
                        <span class="fx-rec__media">
                            <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                                 alt="{{ $product->name }}" loading="lazy">
                        </span>
                        <span class="fx-rec__body">
                            <span class="fx-rec__name">{{ $product->name }}</span>
                            <span class="fx-rec__row2">
                                <span class="fx-price{{ $isQuote ? ' fx-price--quote' : '' }}">{{ $priceText }}</span>
                                <span class="fx-type-tag">{{ $fxType['tag'] }}</span>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
