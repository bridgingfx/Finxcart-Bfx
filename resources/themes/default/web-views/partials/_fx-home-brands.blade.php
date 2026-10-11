{{-- FinXCart redesign: "Shop by brand" row from real brands with products. Expects $brandList. --}}
@if($brandList->isNotEmpty())
    <section class="fx-brands fx-reveal" aria-labelledby="fx-brands-title">
        <div class="fx-container">
            <div class="fx-sec-head">
                <h2 class="fx-sec-title" id="fx-brands-title">{{ translate('Shop by brand') }}</h2>
                <a class="fx-sec-link" href="{{ route('products') }}">{{ translate('View all') }}</a>
            </div>
            <div class="fx-brands__row">
                @foreach($brandList as $brand)
                    <a class="fx-card fx-brands__item" href="{{ route('products', ['brand_id' => $brand->id, 'data_from' => 'brand', 'page' => 1]) }}">
                        <span class="fx-brands__logo">
                            @if(!empty($brand->getRawOriginal('image')))
                                <img src="{{ getStorageImages(path: $brand->image_full_url, type: 'brand') }}" alt="" loading="lazy" onerror="this.remove()">
                            @else
                                {{ mb_strtoupper(mb_substr($brand->name, 0, 1)) }}
                            @endif
                        </span>
                        <span class="fx-brands__name">{{ $brand->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
