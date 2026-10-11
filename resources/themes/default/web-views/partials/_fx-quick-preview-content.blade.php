{{--
    _fx-quick-preview-content.blade.php — rendered by WebController::fxQuickPreview.
    Expects: $product (active, with reviews)
--}}
@php
    $fx = \App\Utils\FxListing::type($product);
    [$fxPrice, $fxOldPrice] = \App\Utils\FxListing::priceStrings($product);
    $fxProvider = \App\Utils\FxListing::providerName($product);
    $fxRating = getOverallRating($product->reviews ?? []);
    $fxDetailUrl = route('product', $product->slug);
    $colors = json_decode($product->colors ?? '[]') ?? [];
    $choices = json_decode($product->choice_options ?? '[]') ?? [];
    $hasRealVariants = count($colors) > 0 || count($choices) > 0;
@endphp
<div class="fx-qp">
    <div class="fx-qp__media">
        <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
             alt="{{ $product->name }}" width="400" height="400">
        <span class="fx-type-tag">{{ $fx['tag'] }}</span>
    </div>
    <h3 class="fx-qp__name">{{ $product->name }}</h3>
    <p class="fx-meta">{{ $fxProvider }}</p>

    @if($fxRating[1] > 0)
        <p class="fx-qp__rating" aria-label="{{ translate('rated') }} {{ $fxRating[0] }} {{ translate('out_of') }} 5">
            <span aria-hidden="true">{{ str_repeat('★', (int) round($fxRating[0])) }}{{ str_repeat('☆', 5 - (int) round($fxRating[0])) }}</span>
            <span class="fx-meta">{{ $fxRating[0] }} ({{ $fxRating[1] }} {{ translate('reviews') }})</span>
        </p>
    @else
        <p class="fx-meta">{{ translate('no_reviews_yet') }}</p>
    @endif

    @if($fx['key'] === 'custom_service')
        {{-- Quote-based service: no buy-now price — never render $0.00 as a price. --}}
        <p class="fx-qp__price"><span class="fx-price">{{ translate('request_quote') }}</span></p>
    @elseif($fxPrice)
        <p class="fx-qp__price"><span class="fx-price">{{ $fxPrice }}</span>
            @if($fxOldPrice)<span class="fx-price-old">{{ $fxOldPrice }}</span>@endif
        </p>
    @endif

    @if($fx['delivery'])
        <p class="fx-qp__delivery"><strong>{{ translate('delivery_model') }}:</strong> {{ $fx['delivery'] }}</p>
    @endif

    <dl class="fx-qp__facts">
        @foreach($fx['attrs'] as $attr)
            <div class="fx-qp__fact"><dt>{{ $attr[0] }}</dt><dd>{{ $attr[1] }}</dd></div>
        @endforeach
        @if($product->category)
            <div class="fx-qp__fact"><dt>{{ translate('category') }}</dt><dd>{{ $product->category->name }}</dd></div>
        @endif
    </dl>

    <div class="fx-qp__actions product-cart-option-container">
        @if($fx['buyable'] && !$hasRealVariants)
            <form class="addToCartDynamicForm fx-qp__form" data-product-type="{{ $product->product_type }}" novalidate>
                @csrf
                <input type="hidden" name="id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" class="product-generated-variation-code" name="product_variation_code" data-product-id="{{ $product->id }}">
                <input type="hidden" value="" class="product-exist-in-cart-list" name="key">
            </form>
            <button type="button" class="fx-btn fx-btn-amber product-buy-now-button"
                    data-form=".fx-qp__form"
                    data-auth="{{ (getWebConfig(name: 'guest_checkout') == 1 || Auth::guard('customer')->check() ? 'true' : 'false') }}"
                    data-route="{{ route('shop-cart') }}">
                {{ translate('buy_now') }}
            </button>
            <button type="button" class="fx-btn fx-btn-outline product-add-to-cart-button"
                    data-form=".fx-qp__form"
                    data-product-type="{{ $product->product_type }}"
                    data-update="{{ translate('go_to_cart') }}"
                    data-add="{{ translate('add_to_cart') }}"
                    data-cart-url="{{ route('shop-cart') }}">
                {{ translate('add_to_cart') }}
            </button>
        @elseif($fx['buyable'] && $hasRealVariants)
            <a class="fx-btn fx-btn-amber" href="{{ $fxDetailUrl }}">{{ translate('choose_options') }}</a>
        @elseif($fx['key'] === 'directory' && !empty($fx['primary']['url']))
            <a class="fx-btn fx-btn-amber" href="{{ $fx['primary']['url'] }}" target="_blank" rel="noopener">
                {{ translate('view_provider') }}
            </a>
        @endif
        <a class="fx-btn fx-btn-ghost" href="{{ $fxDetailUrl }}">{{ translate('view_full_details') }}</a>
    </div>
</div>
