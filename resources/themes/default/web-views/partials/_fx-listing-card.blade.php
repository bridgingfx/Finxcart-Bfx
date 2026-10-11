{{--
    _fx-listing-card.blade.php — shared catalog listing card.
    Type tag + delivery promise come ONLY from App\Utils\FxListing (verified fields).
    No nested interactive elements: image/title are links, action buttons are siblings.
    Expects: $product (with reviews, wish_list_count where available)
--}}
@php
    $fx = \App\Utils\FxListing::type($product);
    [$fxPrice, $fxOldPrice] = \App\Utils\FxListing::priceStrings($product);
    $fxProvider = \App\Utils\FxListing::providerName($product);
    $fxRating = getOverallRating($product->reviews ?? []);
    $fxSaved = ($product->wish_list_count ?? 0) > 0;
    $fxPrimary = $fx['primary'];
    if ($fxPrimary['kind'] === 'external' && empty($fxPrimary['url'])) {
        $fxPrimary = ['label' => translate('view_details'), 'kind' => 'details', 'url' => null];
    }
    $fxDetailUrl = route('product', $product->slug);
@endphp
<article class="fx-card fx-listing-card{{ in_array($fx['key'], ['event', 'directory'], true) ? ' fx-listing-card--wide' : '' }}"
         data-fx-card
         data-product-id="{{ $product->id }}"
         data-fx-type="{{ $fx['canonical'] ?? $fx['key'] }}"
         data-product-name="{{ $product->name }}"
         data-product-slug="{{ $product->slug }}">
    <div class="fx-listing-card__media">
        <a class="fx-listing-card__imglink" href="{{ $fxDetailUrl }}" tabindex="0"
           aria-label="{{ translate('view_details_for') }} {{ $product->name }}">
            <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                 alt="{{ $product->name }}" loading="lazy" width="400" height="400">
        </a>
        <span class="fx-type-tag fx-listing-card__typetag">{{ $fx['tag'] }}</span>
        @if(!$product->not_sellable && getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
            <span class="fx-badge fx-badge--neutral fx-listing-card__discount" aria-label="{{ translate('discount') }}">
                -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
            </span>
        @endif
        @if($fx['key'] === 'physical' && (int)($product->current_stock ?? 0) <= 0)
            <span class="fx-badge fx-listing-card__stock">{{ translate('out_of_stock') }}</span>
        @endif
    </div>

    <div class="fx-listing-card__body">
        <h3 class="fx-listing-card__title">
            <a href="{{ $fxDetailUrl }}">{{ $product->name }}</a>
        </h3>
        <p class="fx-meta fx-listing-card__provider">{{ $fxProvider }}</p>

        @if($fxRating[1] > 0)
            <p class="fx-listing-card__rating" aria-label="{{ translate('rated') }} {{ $fxRating[0] }} {{ translate('out_of') }} 5">
                <span class="fx-listing-card__stars" aria-hidden="true">{{ str_repeat('★', (int) round($fxRating[0])) }}{{ str_repeat('☆', 5 - (int) round($fxRating[0])) }}</span>
                <span class="fx-meta">({{ $fxRating[1] }})</span>
            </p>
        @else
            <p class="fx-meta fx-listing-card__rating">{{ translate('no_reviews_yet') }}</p>
        @endif

        <div class="fx-listing-card__price">
            @if($fx['key'] === 'custom_service')
                {{-- Quote-based service: no buy-now price — never render $0.00 as a price. --}}
                <span class="fx-price">{{ translate('request_quote') }}</span>
            @elseif($fxPrice)
                <span class="fx-price">{{ $fxPrice }}</span>
                @if($fxOldPrice)<span class="fx-price-old">{{ $fxOldPrice }}</span>@endif
            @elseif($fx['buyable'])
                <span class="fx-price">{{ translate('request_quote') }}</span>
            @endif
        </div>

        <dl class="fx-listing-card__attrs">
            @foreach($fx['attrs'] as $attr)
                <div class="fx-listing-card__attr">
                    <dt>{{ $attr[0] }}</dt>
                    <dd>{{ $attr[1] }}</dd>
                </div>
            @endforeach
        </dl>

        @if($fx['delivery'])
            <p class="fx-meta fx-listing-card__delivery">{{ $fx['delivery'] }}</p>
        @endif

        <div class="fx-listing-card__actions">
            @if($fxPrimary['kind'] === 'drawer')
                <button type="button" class="fx-btn fx-btn-sm fx-btn-amber fx-quick-buy-btn"
                        data-product-id="{{ $product->id }}"
                        data-fx-quick-preview="{{ $product->id }}"
                        data-fx-preview-url="{{ route('fx.product.quick-preview', $product->id) }}"
                        aria-label="{{ $fxPrimary['label'] }}: {{ $product->name }}">
                    {{ $fxPrimary['label'] }}
                </button>
            @elseif($fxPrimary['kind'] === 'external')
                <a class="fx-btn fx-btn-sm fx-btn-amber" href="{{ $fxPrimary['url'] }}"
                   target="_blank" rel="noopener"
                   aria-label="{{ $fxPrimary['label'] }}: {{ $product->name }} ({{ translate('opens_in_new_tab') }})">
                    {{ $fxPrimary['label'] }}
                </a>
            @else
                <a class="fx-btn fx-btn-sm fx-btn-outline" href="{{ $fxDetailUrl }}"
                   aria-label="{{ $fxPrimary['label'] }}: {{ $product->name }}">
                    {{ $fxPrimary['label'] }}
                </a>
            @endif
            <button type="button" class="fx-btn fx-btn-sm fx-btn-ghost fx-save-btn{{ $fxSaved ? ' is-saved' : '' }}"
                    data-product-id="{{ $product->id }}"
                    aria-pressed="{{ $fxSaved ? 'true' : 'false' }}"
                    aria-label="{{ $fxSaved ? translate('remove_from_saved') : translate('save_for_later') }}: {{ $product->name }}">
                <span aria-hidden="true">{{ $fxSaved ? '♥' : '♡' }}</span>
            </button>
            <button type="button" class="fx-btn fx-btn-sm fx-btn-ghost fx-compare-btn"
                    data-product-id="{{ $product->id }}"
                    aria-label="{{ translate('add_to_compare') }}: {{ $product->name }}">
                <span aria-hidden="true">⇄</span>
            </button>
        </div>
    </div>
</article>
