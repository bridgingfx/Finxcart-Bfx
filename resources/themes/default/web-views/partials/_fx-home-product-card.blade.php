{{-- FinXCart redesign: shared home product card. $product required. --}}
@php
    try {
        $priceText = \App\Utils\BackEndHelper::set_symbol($product->unit_price);
    } catch (\Throwable $e) {
        $priceText = number_format((float) $product->unit_price, 2);
    }
    $fxType = \App\Utils\FxListing::type($product);
    $typeLabel = $fxType['tag'];
    // Quote-based services have no buy-now price — never show $0.00 as a price.
    $isQuote = $fxType['key'] === 'custom_service';
    if ($isQuote) {
        $priceText = translate('request_quote');
    }
@endphp
<a href="{{ route('product', $product->slug) }}" class="fx-card fx-home-card">
    <span class="fx-home-card__media">
        <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
             alt="{{ $product->name }}" loading="lazy">
    </span>
    <span class="fx-home-card__body">
        <span class="fx-home-card__name">{{ $product->name }}</span>
        <span class="fx-home-card__row">
            <span class="fx-price{{ $isQuote ? ' fx-price--quote' : '' }}">{{ $priceText }}</span>
            <span class="fx-type-tag">{{ $typeLabel }}</span>
        </span>
    </span>
</a>
