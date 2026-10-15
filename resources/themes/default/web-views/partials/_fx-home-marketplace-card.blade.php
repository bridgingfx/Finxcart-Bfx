{{--
    _fx-home-marketplace-card.blade.php — marketplace grid card.
    Wishlist heart, quick view, add-to-cart. Expects: $product.
    Action buttons are siblings of links (no nested interactive elements).
--}}
@php
    $fx = \App\Utils\FxListing::type($product);
    [$fxPrice, $fxOldPrice] = \App\Utils\FxListing::priceStrings($product);
    $fxSaved = ($product->wish_list_count ?? 0) > 0;
    $fxDetailUrl = route('product', $product->slug);
    $discountVal = 0;
    try { $discountVal = getProductPriceByType(product: $product, type: 'discount', result: 'value'); } catch (\Throwable $e) {}
    $formId = 'fxmp-form-' . $product->id;
@endphp
<article class="fxmp-card" data-product-id="{{ $product->id }}">
    <div class="fxmp-card__media">
        <a class="fxmp-card__imglink" href="{{ $fxDetailUrl }}" aria-label="{{ translate('view_details_for') }} {{ $product->name }}">
            <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                 alt="{{ $product->name }}" loading="lazy" width="400" height="300">
        </a>
        @if($discountVal > 0)
            <span class="fxmp-card__badge">-{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}</span>
        @endif
        <button type="button" class="fxmp-card__icon fxmp-wishlist{{ $fxSaved ? ' is-saved' : '' }}"
                data-product-id="{{ $product->id }}" aria-pressed="{{ $fxSaved ? 'true' : 'false' }}"
                aria-label="{{ $fxSaved ? translate('remove_from_saved') : translate('save_for_later') }}: {{ $product->name }}">
            <span aria-hidden="true">{{ $fxSaved ? '♥' : '♡' }}</span>
        </button>
        <button type="button" class="fxmp-card__quick fxmp-quickview"
                data-fx-quick-preview="{{ $product->id }}"
                data-fx-preview-url="{{ route('fx.product.quick-preview', $product->id) }}"
                aria-label="{{ translate('quick_view') }}: {{ $product->name }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <span>{{ translate('quick_view') }}</span>
        </button>
    </div>
    <div class="fxmp-card__body">
        <h3 class="fxmp-card__title"><a href="{{ $fxDetailUrl }}">{{ $product->name }}</a></h3>
        <p class="fxmp-card__tag">{{ $fx['tag'] }}</p>
        <div class="fxmp-card__price">
            @if($fx['key'] === 'custom_service')
                <span class="fx-price fx-price--quote">{{ translate('request_quote') }}</span>
            @elseif($fxPrice)
                <span class="fx-price">{{ $fxPrice }}</span>
                @if($fxOldPrice)<span class="fx-price-old">{{ $fxOldPrice }}</span>@endif
            @elseif($fx['buyable'])
                <span class="fx-price">{{ translate('request_quote') }}</span>
            @endif
        </div>
        @if($fx['buyable'] && $fx['key'] !== 'custom_service')
            <form class="addToCartDynamicForm" id="{{ $formId }}" data-product-type="{{ $product->product_type }}" novalidate>
                @csrf
                <input type="hidden" name="id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" class="product-generated-variation-code" name="product_variation_code" data-product-id="{{ $product->id }}">
                <input type="hidden" value="" class="product-exist-in-cart-list" name="key">
            </form>
            <button type="button" class="fxmp-card__add fxmp-addcart"
                    data-form="#{{ $formId }}"
                    data-product-type="{{ $product->product_type }}"
                    data-update="{{ translate('go_to_cart') }}"
                    data-add="{{ translate('add_to_cart') }}"
                    data-cart-url="{{ route('shop-cart') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                {{ translate('add_to_cart') }}
            </button>
        @else
            <a class="fxmp-card__add fxmp-card__add--link" href="{{ $fxDetailUrl }}">{{ translate('view_details') }}</a>
        @endif
    </div>
</article>
