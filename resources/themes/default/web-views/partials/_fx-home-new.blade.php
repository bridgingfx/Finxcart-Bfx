{{-- FinXCart redesign: "New arrivals" grid. Expects $newArrivals (collection of products). --}}
@if($newArrivals->isNotEmpty())
    <section class="fx-new fx-reveal" aria-labelledby="fx-new-title">
        <div class="fx-container">
            <div class="fx-sec-head">
                <h2 class="fx-sec-title" id="fx-new-title">{{ translate('New arrivals') }}</h2>
                <a class="fx-sec-link" href="{{ route('products') }}">{{ translate('View all') }}</a>
            </div>
            <div class="fx-new__grid">
                @foreach($newArrivals as $product)
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
                    <a class="fx-card fx-new__card" href="{{ route('product', $product->slug) }}">
                        <span class="fx-new__media">
                            <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                                 alt="{{ $product->name }}" loading="lazy">
                        </span>
                        <span class="fx-new__body">
                            <span class="fx-new__name">{{ $product->name }}</span>
                            <span class="fx-new__row">
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
