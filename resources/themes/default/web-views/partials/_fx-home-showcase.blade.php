{{-- FinXCart: Direct product showcase grid — bypasses complex caching, shows live inventory --}}
@php
    $showcaseProducts = \App\Models\Product::active()->with('seller.shop')->orderBy('id', 'desc')->take(12)->get();
@endphp
@if($showcaseProducts->isNotEmpty())
    <section class="fx-showcase" aria-labelledby="fx-showcase-title">
        <div class="fx-container">
            <div class="fx-sec-head">
                <h2 class="fx-sec-title" id="fx-showcase-title">{{ translate('Featured products') }}</h2>
                <a class="fx-sec-link fx-sec-link--pill" href="{{ route('products') }}">{{ translate('View all') }}</a>
            </div>
            <div class="fx-showcase__grid">
                @foreach($showcaseProducts as $product)
                    @php
                        $img = $product->thumbnail_full_url;
                        $imgPath = is_array($img) ? ($img['path'] ?? null) : $img;
                        try {
                            $priceText = \App\Utils\BackEndHelper::set_symbol($product->unit_price);
                        } catch (\Throwable $e) {
                            $priceText = number_format((float) $product->unit_price, 2);
                        }
                    @endphp
                    <a class="fx-showcase__card" href="{{ route('product', $product->slug) }}">
                        <div class="fx-showcase__img">
                            @if($imgPath)
                                <img src="{{ $imgPath }}" alt="{{ $product->name }}" loading="lazy">
                            @endif
                            <span class="fx-showcase__badge">{{ translate('Download') }}</span>
                        </div>
                        <div class="fx-showcase__body">
                            <h3 class="fx-showcase__name">{{ $product->name }}</h3>
                            <div class="fx-showcase__price">{{ $priceText }}</div>
                            <div class="fx-showcase__delivery">{{ translate('Instant download after payment') }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
