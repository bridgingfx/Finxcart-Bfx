{{--
    fx-details.blade.php — fx product detail page.
    Same data contract + JS hooks as the legacy details view (addToCartDynamicForm,
    variant radios, qty stepper, price spans) so cart/variant behavior is preserved.
    Type tag + delivery promise come ONLY from App\Utils\FxListing (verified fields).
--}}
@extends('layouts.front-end.app')

@section('title', $product['name'])

@push('css_or_js')
    @include(VIEW_FILE_NAMES['product_seo_meta_content_partials'], ['metaContentData' => $product?->seoInfo, 'productDetails' => $product])
    <style>
        /* Scoped compensation (fx pages only): the shared header's mobile row
           (burger + 160px logo + actions) overflows viewports under ~430px.
           Cap the logo until the header owner fixes the root cause. */
        @media (max-width: 767px) {
            .fxh-logo img { max-width: 108px !important; height: auto !important; }
            body > .row { margin-left: 0; margin-right: 0; }
        }
        @media (max-width: 360px) {
            /* 360px and below: icon-only header actions so the row fits.
               Sign-in lives in the drawer + bottom nav Account tab. */
            .fxh-logo img { max-width: 84px !important; }
            .fxh-action > span:last-child { display: none !important; }
            .fxh-acct > a.fx-btn { display: none !important; }
            .fxh-acct-btn .fxh-acct-name { max-width: 56px !important; }
        }
        .fx-detail__layout { display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 6fr); gap: 32px; align-items: start; }
        .fx-backlink { display: none; }
        .fx-gallery { position: sticky; top: 16px; }
        .fx-gallery__main { position: relative; aspect-ratio: 1/1; background: var(--fx-canvas); border: 1px solid var(--fx-border); border-radius: 16px; overflow: hidden; touch-action: pan-y; }
        .fx-gallery__main img { width: 100%; height: 100%; object-fit: cover; display: block; cursor: zoom-in; }
        .fx-gallery__nav { position: absolute; top: 50%; transform: translateY(-50%); width: 44px; height: 44px; border-radius: 50%; border: 1px solid var(--fx-border); background: rgba(255,255,255,.95); color: var(--fx-text); font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .fx-gallery__nav--prev { inset-inline-start: 8px; }
        .fx-gallery__nav--next { inset-inline-end: 8px; }
        .fx-gallery__count { position: absolute; bottom: 8px; inset-inline-end: 8px; background: rgba(23,43,66,.8); color: #fff; font-size: 13px; padding: 4px 10px; border-radius: 999px; }
        .fx-gallery__thumbs { display: flex; gap: 8px; margin-top: 8px; overflow-x: auto; padding-bottom: 4px; }
        .fx-gallery__thumb { flex: none; width: 72px; height: 72px; border-radius: 8px; overflow: hidden; border: 2px solid transparent; padding: 0; cursor: pointer; background: var(--fx-canvas); }
        .fx-gallery__thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .fx-gallery__thumb[aria-current="true"] { border-color: var(--fx-amber); }
        .fx-detail__head h1 { font-size: 28px; line-height: 1.25; margin: 8px 0; letter-spacing: -0.01em; }
        .fx-detail__facts { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px 16px; margin: 16px 0; padding: 0; }
        .fx-detail__fact { background: var(--fx-surface); border: 1px solid var(--fx-border); border-radius: 12px; padding: 10px 12px; }
        .fx-detail__fact dt { font-size: 12px; color: var(--fx-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        .fx-detail__fact dd { margin: 2px 0 0; font-size: 14px; font-weight: 600; }
        .fx-price-panel { background: var(--fx-surface); border: 1px solid var(--fx-border); border-radius: 16px; padding: 20px; margin: 16px 0; }
        .fx-price-panel__price { display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
        .fx-variant-group { margin: 12px 0; }
        .fx-variant-group__label { font-size: 14px; font-weight: 600; margin-bottom: 8px; display: block; }
        .fx-variant-opts { display: flex; flex-wrap: wrap; gap: 8px; }
        .fx-variant-opt input { position: absolute; opacity: 0; width: 1px; height: 1px; }
        .fx-variant-opt label { display: inline-flex; align-items: center; min-height: 44px; padding: 8px 14px; border: 1px solid var(--fx-border); border-radius: 12px; font-size: 14px; cursor: pointer; background: var(--fx-surface); }
        .fx-variant-opt input:checked + label { border-color: var(--fx-navy); background: rgba(32,63,96,.06); font-weight: 600; }
        .fx-variant-opt input:focus-visible + label { outline: 3px solid rgba(32,63,96,.45); outline-offset: 2px; }
        .fx-color-opt label { width: 44px; height: 44px; padding: 0; border-radius: 50%; }
        .fx-qty { display: flex; align-items: center; gap: 0; border: 1px solid var(--fx-border); border-radius: 12px; overflow: hidden; width: max-content; }
        .fx-qty button { width: 44px; height: 44px; border: 0; background: var(--fx-surface); font-size: 20px; cursor: pointer; }
        .fx-qty input { width: 56px; height: 44px; border: 0; text-align: center; font-size: 16px; }
        .fx-detail__actions { display: grid; gap: 8px; margin-top: 16px; }
        .fx-detail__actions-row { display: flex; gap: 8px; }
        .fx-detail__actions-row .fx-btn { flex: 1; }
        .fx-detail__section { margin: 32px 0; }
        .fx-detail__section > h2 { font-size: 20px; margin: 0 0 12px; }
        .fx-detail__prose { font-size: 15px; line-height: 1.6; }
        .fx-detail__prose img { max-width: 100%; height: auto; }
        .fx-package-table { width: 100%; border-collapse: collapse; }
        .fx-package-table th, .fx-package-table td { padding: 12px; border-bottom: 1px solid var(--fx-border); text-align: start; font-size: 14px; }
        .fx-stickybar { position: fixed; left: 0; right: 0; bottom: 0; z-index: 94; background: var(--fx-surface); border-top: 1px solid var(--fx-border); padding: 10px 16px calc(10px + env(safe-area-inset-bottom)); display: flex; align-items: center; gap: 12px; transform: translateY(110%); transition: transform 200ms ease; }
        .fx-stickybar.is-visible { transform: translateY(0); }
        .fx-stickybar__info { flex: 1; min-width: 0; }
        .fx-stickybar__info p { margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .fx-review { border-bottom: 1px solid var(--fx-border); padding: 12px 0; }
        .fx-review__head { display: flex; align-items: center; gap: 8px; font-size: 14px; }
        .fx-alternatives { grid-template-columns: repeat(4, 1fr); }
        @media (max-width: 1023px) {
            .fx-detail__layout { grid-template-columns: 1fr; }
            .fx-gallery { position: static; }
            .fx-backlink { display: inline-flex; align-items: center; gap: 4px; margin-bottom: 8px; color: var(--fx-navy); text-decoration: none; font-weight: 600; min-height: 44px; }
            .fx-detail__head h1 { font-size: 22px; }
            .fx-alternatives { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 767px) {
            .fx-detail__facts { grid-template-columns: 1fr 1fr; }
        }
        @media (prefers-reduced-motion: reduce) {
            .fx-stickybar { transition: none; }
        }
    </style>
@endpush

@php
    $fx = \App\Utils\FxListing::type($product);
    $fxProvider = \App\Utils\FxListing::providerName($product);
    $fxRating = getOverallRating($product->reviews ?? []);
    $galleryImages = $product->images_full_url ?? [];
    if (empty($galleryImages) && ($product->thumbnail_full_url['path'] ?? null)) {
        $galleryImages = [$product->thumbnail_full_url['path']];
    }
    $minimumOrderQty = max((int)($product->minimum_order_qty ?? 1), 1);
    $colors = json_decode($product->colors ?? '[]') ?? [];
    $choices = json_decode($product->choice_options ?? '[]') ?? [];
    $digitalExts = (in_array($fx['key'], ['download', 'download_manual'], true) && $product->digital_product_extensions) ? $product->digital_product_extensions : [];
    $isBuyable = $fx['buyable'] && !$product->not_sellable;
    $sellerClosed = ($product->added_by == 'seller' && ($sellerTemporaryClose || (isset($product->seller->shop) && $product->seller->shop->vacation_status && $currentDate >= $sellerVacationStartDate && $currentDate <= $sellerVacationEndDate)))
        || ($product->added_by == 'admin' && ($inHouseTemporaryClose || ($inHouseVacationStatus && $currentDate >= $inHouseVacationStartDate && $currentDate <= $inHouseVacationEndDate)));
    $discountValue = getProductPriceByType(product: $product, type: 'discount', result: 'value');
@endphp

@section('content')
<div class="fx fx-detail">
    <div class="fx-container">
        <nav aria-label="{{ translate('breadcrumb') }}">
            <ol class="fx-breadcrumb">
                <li><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
                <li><a href="{{ route('products') }}">{{ translate('products') }}</a></li>
                @if($product->category)<li><a href="{{ route('products', ['data_from' => 'category', 'category_id' => $product->category->id]) }}">{{ $product->category->name }}</a></li>@endif
                <li aria-current="page">{{ \Illuminate\Support\Str::limit($product->name, 40) }}</li>
            </ol>
        </nav>
        <a class="fx-backlink" href="{{ route('products') }}" aria-label="{{ translate('back_to_results') }}">‹ {{ translate('back_to_results') }}</a>

        <div class="fx-detail__layout">
            {{-- Gallery --}}
            <div class="fx-gallery" data-fx-gallery>
                <div class="fx-gallery__main" data-fx-gallery-main>
                    @foreach($galleryImages as $i => $img)
                        <img src="{{ $img }}" alt="{{ $product->name }} — {{ translate('image') }} {{ $i + 1 }}"
                             data-fx-gallery-img="{{ $i }}" {{ $i > 0 ? 'hidden' : '' }}
                             width="800" height="800" {{ $i > 0 ? '' : 'fetchpriority="high"' }}>
                    @endforeach
                    @if(empty($galleryImages))
                        <img src="{{ getStorageImages(path: null, type: 'product') }}" alt="{{ $product->name }}" width="800" height="800">
                    @endif
                    @if(count($galleryImages) > 1)
                        <button type="button" class="fx-gallery__nav fx-gallery__nav--prev" data-fx-gallery-prev aria-label="{{ translate('previous_image') }}">‹</button>
                        <button type="button" class="fx-gallery__nav fx-gallery__nav--next" data-fx-gallery-next aria-label="{{ translate('next_image') }}">›</button>
                        <span class="fx-gallery__count" data-fx-gallery-count aria-live="polite">1 / {{ count($galleryImages) }}</span>
                    @endif
                </div>
                @if(count($galleryImages) > 1)
                    <div class="fx-gallery__thumbs" role="listbox" aria-label="{{ translate('product_images') }}" data-fx-gallery-thumbs>
                        @foreach($galleryImages as $i => $img)
                            <button type="button" class="fx-gallery__thumb" role="option" data-fx-gallery-thumb="{{ $i }}"
                                    aria-current="{{ $i === 0 ? 'true' : 'false' }}" aria-label="{{ translate('view_image') }} {{ $i + 1 }}">
                                <img src="{{ $img }}" alt="" loading="lazy" width="72" height="72">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Main column --}}
            <div class="fx-detail__main">
                <div class="fx-detail__head" data-fx-detail-head>
                    <span class="fx-type-tag">{{ $fx['tag'] }}</span>
                    <h1>{{ $product->name }}</h1>
                    <p class="fx-meta">{{ translate('sold_by') }}: <strong>{{ $fxProvider }}</strong></p>
                    @if($fxRating[1] > 0)
                        <p class="fx-detail__rating" aria-label="{{ translate('rated') }} {{ $fxRating[0] }} {{ translate('out_of') }} 5">
                            <span class="fx-listing-card__stars" aria-hidden="true">{{ str_repeat('★', (int) round($fxRating[0])) }}{{ str_repeat('☆', 5 - (int) round($fxRating[0])) }}</span>
                            <span class="fx-meta">{{ $fxRating[0] }} · {{ $fxRating[1] }} {{ translate('reviews') }} · {{ $countOrder ?? 0 }} {{ translate('orders') }}</span>
                        </p>
                    @else
                        <p class="fx-meta">{{ translate('no_reviews_yet') }}</p>
                    @endif
                    @if($product->details)
                        <p class="fx-detail__outcome">{{ \Illuminate\Support\Str::limit(strip_tags($product->details), 180) }}</p>
                    @endif
                </div>

                <dl class="fx-detail__facts">
                    @foreach($fx['attrs'] as $attr)
                        <div class="fx-detail__fact"><dt>{{ $attr[0] }}</dt><dd>{{ $attr[1] }}</dd></div>
                    @endforeach
                    @if($fx['delivery'])
                        <div class="fx-detail__fact"><dt>{{ translate('delivery_model') }}</dt><dd>{{ $fx['delivery'] }}</dd></div>
                    @endif
                    @if($product->category)
                        <div class="fx-detail__fact"><dt>{{ translate('category') }}</dt><dd>{{ $product->category->name }}</dd></div>
                    @endif
                    @if(!empty($productAuthorsInfo['names']))
                        <div class="fx-detail__fact"><dt>{{ translate('authors') }}</dt><dd>{{ implode(', ', $productAuthorsInfo['names']) }}</dd></div>
                    @endif
                    @if(!empty($productPublishingHouseInfo['names']))
                        <div class="fx-detail__fact"><dt>{{ translate('publishing_house') }}</dt><dd>{{ implode(', ', $productPublishingHouseInfo['names']) }}</dd></div>
                    @endif
                </dl>

                {{-- Price / package panel — same JS hooks as legacy view --}}
                <div class="fx-price-panel" data-fx-price-panel>
                    @if($isBuyable)
                        <form class="addToCartDynamicForm add-to-cart-details-form product-cart-option-container" data-product-type="{{ $product->product_type }}" novalidate>
                            @csrf
                            <input type="hidden" name="id" value="{{ $product->id }}">

                            <div class="fx-price-panel__price">
                                <span class="fx-price discounted-unit-price" style="font-size:28px">
                                    {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                                </span>
                                @if($discountValue > 0)
                                    <del class="product-total-unit-price fx-price-old">{{ webCurrencyConverter(amount: $product->unit_price) }}</del>
                                    <span class="fx-badge fx-badge--neutral discounted-badge-element">-{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}</span>
                                @else
                                    <span class="fx-badge fx-badge--neutral discounted-badge-element d-none"></span>
                                @endif
                            </div>

                            @if($fx['key'] === 'physical' && $product->current_stock > 0 && $product->current_stock < 10)
                                <p class="fx-meta" role="status">⚠ {{ translate('low_stock_warning') }}: {{ $product->current_stock }} {{ translate('items') }}</p>
                            @endif

                            @if(count($colors) > 0)
                                <div class="fx-variant-group">
                                    <span class="fx-variant-group__label" id="fx-color-label">{{ translate('color') }}</span>
                                    <div class="fx-variant-opts" role="radiogroup" aria-labelledby="fx-color-label">
                                        @foreach($colors as $key => $color)
                                            <span class="fx-variant-opt fx-color-opt">
                                                <input type="radio" id="fx-color-{{ $key }}" name="color" value="{{ $color }}" {{ $key == 0 ? 'checked' : '' }}>
                                                <label for="fx-color-{{ $key }}" style="background: {{ $color }};" title="{{ getColorNameByCode(code: $color) }}"><span class="sr-only">{{ getColorNameByCode(code: $color) }}</span></label>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(count($digitalExts) > 0)
                                @php($extensionIndex = 0)
                                @foreach($digitalExts as $extensionKey => $extensionGroup)
                                    @if(count($extensionGroup) > 0)
                                        <div class="fx-variant-group">
                                            <span class="fx-variant-group__label">{{ translate($extensionKey) }}</span>
                                            <div class="fx-variant-opts" role="radiogroup" aria-label="{{ translate($extensionKey) }}">
                                                @foreach($extensionGroup as $extension)
                                                    <span class="fx-variant-opt">
                                                        <input type="radio" id="fx-ext-{{ $extensionIndex }}" name="variant_key"
                                                               value="{{ $extensionKey . '-' . preg_replace('/\s+/', '-', $extension) }}"
                                                               {{ $extensionIndex == 0 ? 'checked' : '' }}>
                                                        <label for="fx-ext-{{ $extensionIndex }}">{{ $extension }}</label>
                                                    </span>
                                                    @php($extensionIndex++)
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            @foreach($choices as $choice)
                                <div class="fx-variant-group">
                                    <span class="fx-variant-group__label">{{ $choice->title }}</span>
                                    <div class="fx-variant-opts" role="radiogroup" aria-label="{{ $choice->title }}">
                                        @foreach($choice->options as $index => $option)
                                            <span class="fx-variant-opt">
                                                <input type="radio" id="fx-choice-{{ \Illuminate\Support\Str::slug($choice->name . '-' . $option) }}"
                                                       name="{{ $choice->name }}" value="{{ $option }}" {{ $index == 0 ? 'checked' : '' }}>
                                                <label for="fx-choice-{{ \Illuminate\Support\Str::slug($choice->name . '-' . $option) }}">{{ $option }}</label>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            @if($fx['key'] === 'physical')
                                <div class="fx-variant-group">
                                    <span class="fx-variant-group__label" id="fx-qty-label">{{ translate('quantity') }}</span>
                                    <div class="fx-qty" role="group" aria-labelledby="fx-qty-label">
                                        <button type="button" class="btn-number" data-type="minus" data-field="quantity" aria-label="{{ translate('decrease_quantity') }}">−</button>
                                        <input type="text" name="quantity" class="input-number product-details-cart-qty" value="{{ $minimumOrderQty }}" min="{{ $minimumOrderQty }}" inputmode="numeric" aria-label="{{ translate('quantity') }}">
                                        <button type="button" class="btn-number" data-type="plus" data-field="quantity" aria-label="{{ translate('increase_quantity') }}">+</button>
                                    </div>
                                </div>
                            @endif

                            <input type="hidden" class="product-generated-variation-code" name="product_variation_code" data-product-id="{{ $product->id }}">
                            <input type="hidden" value="" class="product-exist-in-cart-list" name="key">

                            <div class="product-details-chosen-price-section" style="margin:12px 0">
                                <strong>{{ translate('total_price') }}: </strong>
                                <strong class="product-details-chosen-price-amount"></strong>
                                <small class="product-details-tax-amount-container">
                                    (<small>{{ translate('tax') }}: </small><small class="product-details-tax-amount"></small>)
                                </small>
                            </div>

                            <div class="fx-detail__actions" data-fx-buy-actions>
                                @if($sellerClosed)
                                    <button class="fx-btn" type="button" disabled>{{ translate('buy_now') }}</button>
                                    <button class="fx-btn fx-btn-outline" type="button" disabled>{{ translate('add_to_cart') }}</button>
                                    <p class="fx-meta">{{ translate('seller_is_currently_unavailable') }}</p>
                                @elseif($firstVariationQuantity <= 0 && $fx['key'] === 'physical')
                                    <button type="button" class="fx-btn fx-btn-outline product-restock-request-button"
                                            data-auth="{{ auth('customer')->check() ? 'true' : 'false' }}"
                                            data-form=".addToCartDynamicForm">
                                        {{ translate('request_restock') }}
                                    </button>
                                @else
                                    <button type="button" class="fx-btn fx-btn-amber fx-btn-lg product-buy-now-button"
                                            data-form=".add-to-cart-details-form"
                                            data-auth="{{ (getWebConfig(name: 'guest_checkout') == 1 || Auth::guard('customer')->check() ? 'true' : 'false') }}"
                                            data-route="{{ route('shop-cart') }}">
                                        {{ translate('buy_now') }}
                                    </button>
                                    <div class="fx-detail__actions-row">
                                        <button type="button" class="fx-btn fx-btn-outline product-add-to-cart-button"
                                                data-form=".add-to-cart-details-form"
                                                data-product-type="{{ $product->product_type }}"
                                                data-update="{{ translate('go_to_cart') }}"
                                                data-add="{{ translate('add_to_cart') }}"
                                                data-cart-url="{{ route('shop-cart') }}">
                                            {{ translate('add_to_cart') }}
                                        </button>
                                        <button type="button" class="fx-btn fx-btn-ghost fx-save-btn{{ $wishlistStatus > 0 ? ' is-saved' : '' }}"
                                                data-product-id="{{ $product->id }}" aria-pressed="{{ $wishlistStatus > 0 ? 'true' : 'false' }}"
                                                aria-label="{{ translate('save_for_later') }}">
                                            <span aria-hidden="true">{{ $wishlistStatus > 0 ? '♥' : '♡' }}</span>
                                        </button>
                                        <button type="button" class="fx-btn fx-btn-ghost fx-compare-btn"
                                                data-product-id="{{ $product->id }}"
                                                data-fx-type="{{ $fx['key'] }}"
                                                data-product-name="{{ $product->name }}"
                                                data-product-slug="{{ $product->slug }}"
                                                aria-label="{{ translate('add_to_compare') }}">
                                            <span aria-hidden="true">⇄</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </form>
                    @elseif($fx['key'] === 'event')
                        <div class="fx-detail__actions" data-fx-buy-actions>
                            @if($product->book_tickets_url)
                                <a class="fx-btn fx-btn-amber fx-btn-lg" href="{{ $product->book_tickets_url }}" target="_blank" rel="noopener">{{ translate('book_ticket') }}</a>
                            @endif
                            <div class="fx-detail__actions-row">
                                @if($product->external_url)<a class="fx-btn fx-btn-outline" href="{{ $product->external_url }}" target="_blank" rel="noopener">{{ translate('visit_website') }}</a>@endif
                                @if($product->view_floorplan_url)<a class="fx-btn fx-btn-outline" href="{{ $product->view_floorplan_url }}" target="_blank" rel="noopener">{{ translate('view_floor_plan') }}</a>@endif
                            </div>
                            <div class="fx-detail__actions-row">
                                @if($product->brochure && ($product->brochure_full_url['path'] ?? null))<a class="fx-btn fx-btn-ghost" href="{{ $product->brochure_full_url['path'] }}" target="_blank" rel="noopener">{{ translate('download_brochure') }}</a>@endif
                                @if($product->sponsor_exhibit_url)<a class="fx-btn fx-btn-ghost" href="{{ $product->sponsor_exhibit_url }}" target="_blank" rel="noopener">{{ translate('sponsor_exhibit') }}</a>@endif
                            </div>
                        </div>
                    @elseif($fx['key'] === 'directory')
                        <div class="fx-detail__actions" data-fx-buy-actions>
                            @if($product->open_account_url)<a class="fx-btn fx-btn-amber fx-btn-lg" href="{{ $product->open_account_url }}" target="_blank" rel="noopener">{{ translate('open_account') }}</a>@endif
                            <div class="fx-detail__actions-row">
                                @if($product->contact_broker_url)<a class="fx-btn fx-btn-outline" href="{{ $product->contact_broker_url }}" target="_blank" rel="noopener">{{ translate('contact_provider') }}</a>@endif
                                @if($product->external_url)<a class="fx-btn fx-btn-outline" href="{{ $product->external_url }}" target="_blank" rel="noopener">{{ translate('visit_website') }}</a>@endif
                            </div>
                        </div>
                    @elseif($fx['key'] === 'custom_service')
                        {{-- Quote-based service: no cart/checkout — the real path is the provider. --}}
                        <div class="fx-detail__actions" data-fx-buy-actions>
                            @if($product->added_by == 'seller' && $product->seller_id)
                                <a class="fx-btn fx-btn-amber fx-btn-lg" href="{{ route('shopView', $product->seller_id) }}">{{ translate('contact_provider') }}</a>
                            @endif
                            <div class="fx-detail__actions-row">
                                <button type="button" class="fx-btn fx-btn-ghost fx-save-btn{{ $wishlistStatus > 0 ? ' is-saved' : '' }}"
                                        data-product-id="{{ $product->id }}" aria-pressed="{{ $wishlistStatus > 0 ? 'true' : 'false' }}"
                                        aria-label="{{ translate('save_for_later') }}">
                                    <span aria-hidden="true">{{ $wishlistStatus > 0 ? '♥' : '♡' }}</span>
                                </button>
                                <button type="button" class="fx-btn fx-btn-ghost fx-compare-btn"
                                        data-product-id="{{ $product->id }}"
                                        data-fx-type="{{ $fx['key'] }}"
                                        data-product-name="{{ $product->name }}"
                                        data-product-slug="{{ $product->slug }}"
                                        aria-label="{{ translate('add_to_compare') }}">
                                    <span aria-hidden="true">⇄</span>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="fx-detail__actions" data-fx-buy-actions>
                            @if($product->external_url)<a class="fx-btn fx-btn-amber fx-btn-lg" href="{{ $product->external_url }}" target="_blank" rel="noopener">{{ translate('visit_website') }}</a>@endif
                        </div>
                    @endif
                </div>

                {{-- Delivery process --}}
                <section class="fx-detail__section" aria-label="{{ translate('delivery') }}">
                    <h2>{{ translate('delivery') }}</h2>
                    @if($fx['delivery'])
                        <p>{{ $fx['delivery'] }}</p>
                    @endif
                    @if($fx['key'] === 'physical')
                        <p class="fx-meta">{{ translate('shipping_calculated_at_checkout') }}</p>
                    @elseif($fx['key'] === 'directory')
                        <p class="fx-meta">{{ translate('directory_delivery_note') }}</p>
                    @elseif($fx['key'] === 'event' && $product->event_days)
                        <p class="fx-meta">{{ translate('event_dates') }}: {{ $product->event_days }}</p>
                    @endif
                </section>
            </div>
        </div>

        {{-- What's included --}}
        @if($product->details)
            <section class="fx-detail__section" aria-label="{{ translate('whats_included') }}">
                <h2>{{ translate('whats_included') }}</h2>
                <div class="fx-card"><div class="fx-detail__prose">{!! clean_html($product['details']) !!}</div></div>
            </section>
        @endif

        {{-- Package comparison (digital variations only — real data) --}}
        @if(in_array($fx['key'], ['download', 'download_manual'], true) && $product->digitalVariation && $product->digitalVariation->count() > 0)
            <section class="fx-detail__section" aria-label="{{ translate('compare_packages') }}">
                <h2>{{ translate('compare_packages') }}</h2>
                <div class="fx-card" style="padding:0;overflow:auto">
                    <table class="fx-package-table">
                        <thead><tr><th>{{ translate('package') }}</th><th>{{ translate('price') }}</th><th><span class="sr-only">{{ translate('select') }}</span></th></tr></thead>
                        <tbody>
                        @foreach($product->digitalVariation as $variation)
                            <tr>
                                <td>{{ $variation->variant_key }}</td>
                                <td class="fx-price" style="font-size:16px">{{ webCurrencyConverter(amount: $variation->price) }}</td>
                                <td><button type="button" class="fx-btn fx-btn-sm fx-btn-outline" data-fx-select-package="{{ $variation->variant_key }}">{{ translate('select') }}</button></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- Samples --}}
        @if($product->preview_url || $product->preview_file_full_url['path'] ?? null)
            <section class="fx-detail__section" aria-label="{{ translate('samples') }}">
                <h2>{{ translate('samples') }}</h2>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    @if($product->preview_url)<a class="fx-btn fx-btn-outline" href="{{ $product->preview_url }}" target="_blank" rel="noopener">{{ translate('view_preview') }}</a>@endif
                    @if($product->preview_file_full_url['path'] ?? null)<a class="fx-btn fx-btn-outline" href="{{ $product->preview_file_full_url['path'] }}" target="_blank" rel="noopener">{{ translate('download_sample') }}</a>@endif
                </div>
            </section>
        @endif

        {{-- Compatibility (real data only: digital file extensions) --}}
        @if(count($digitalExts) > 0)
            <section class="fx-detail__section" aria-label="{{ translate('compatibility') }}">
                <h2>{{ translate('compatibility') }}</h2>
                <ul>
                    @foreach($digitalExts as $extensionKey => $extensionGroup)
                        <li><strong>{{ translate($extensionKey) }}:</strong> {{ implode(', ', $extensionGroup) }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Support / refund / license (links only — terms live on policy pages) --}}
        <section class="fx-detail__section" aria-label="{{ translate('support_and_policies') }}">
            <h2>{{ translate('support_and_policies') }}</h2>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a class="fx-chip" href="{{ url('/refund-policy') }}">{{ translate('refund_policy') }}</a>
                <a class="fx-chip" href="{{ url('/terms-and-conditions') }}">{{ translate('terms_and_conditions') }}</a>
                <a class="fx-chip" href="{{ url('/privacy-policy') }}">{{ translate('privacy_policy') }}</a>
            </div>
        </section>

        {{-- Reviews (genuine only) --}}
        <section class="fx-detail__section" aria-label="{{ translate('reviews') }}" id="fx-reviews">
            <h2>{{ translate('reviews') }}</h2>
            @if($fxRating[1] > 0)
                <p class="fx-meta">{{ translate('rated') }} {{ $fxRating[0] }} {{ translate('out_of') }} 5 · {{ $fxRating[1] }} {{ translate('reviews') }}</p>
                @foreach($productReviews as $review)
                    <article class="fx-review">
                        <div class="fx-review__head">
                            <strong>{{ $review->customer->f_name ?? translate('verified_customer') }}</strong>
                            <span class="fx-listing-card__stars" aria-label="{{ $review->rating }} / 5">{{ str_repeat('★', (int)$review->rating) }}{{ str_repeat('☆', 5 - (int)$review->rating) }}</span>
                            <span class="fx-meta">{{ $review->created_at?->format('Y-m-d') }}</span>
                        </div>
                        <p style="margin:8px 0 0">{{ $review->comment }}</p>
                    </article>
                @endforeach
            @else
                <div class="fx-empty" style="padding:32px 16px">
                    <p class="fx-empty__title">{{ translate('no_reviews_yet') }}</p>
                    <p class="fx-empty__body">{{ translate('be_the_first_to_review') }}</p>
                </div>
            @endif
        </section>

        {{-- Alternatives --}}
        @if($relatedProducts && $relatedProducts->count() > 0)
            <section class="fx-detail__section" aria-label="{{ translate('you_may_also_like') }}">
                <h2>{{ translate('you_may_also_like') }}</h2>
                <div class="fx-grid fx-alternatives">
                    @foreach($relatedProducts as $relatedProduct)
                        @include('web-views.partials._fx-listing-card', ['product' => $relatedProduct])
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- Sticky purchase bar (shown only after the main action leaves the viewport) --}}
    <div class="fx-stickybar" data-fx-stickybar hidden role="region" aria-label="{{ translate('quick_buy') }}">
        <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}" alt="" width="44" height="44" style="border-radius:8px;object-fit:cover">
        <div class="fx-stickybar__info">
            <p><strong>{{ \Illuminate\Support\Str::limit($product->name, 48) }}</strong></p>
            @if(!$product->not_sellable)
                <p class="fx-meta">{{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}</p>
            @endif
        </div>
        @if($isBuyable && !$sellerClosed)
            <button type="button" class="fx-btn fx-btn-amber product-buy-now-button"
                    data-form=".add-to-cart-details-form"
                    data-auth="{{ (getWebConfig(name: 'guest_checkout') == 1 || Auth::guard('customer')->check() ? 'true' : 'false') }}"
                    data-route="{{ route('shop-cart') }}">{{ translate('buy_now') }}</button>
        @elseif($fx['key'] === 'event' && $product->book_tickets_url)
            <a class="fx-btn fx-btn-amber" href="{{ $product->book_tickets_url }}" target="_blank" rel="noopener">{{ translate('book_ticket') }}</a>
        @elseif($fx['key'] === 'directory' && $product->open_account_url)
            <a class="fx-btn fx-btn-amber" href="{{ $product->open_account_url }}" target="_blank" rel="noopener">{{ translate('open_account') }}</a>
        @elseif($product->external_url)
            <a class="fx-btn fx-btn-amber" href="{{ $product->external_url }}" target="_blank" rel="noopener">{{ translate('visit_website') }}</a>
        @endif
    </div>
</div>

<div class="fx-overlay" data-fx-gallery-overlay hidden>
    <div class="fx-modal" role="dialog" aria-modal="true" aria-label="{{ $product->name }}" style="max-width:900px;text-align:center">
        <button type="button" class="fx-modal__close" data-fx-gallery-enlarge-close aria-label="{{ translate('close') }}">×</button>
        <img src="" alt="{{ $product->name }}" data-fx-gallery-enlarge-img style="max-width:100%;height:auto;border-radius:8px">
    </div>
</div>

@include('web-views.partials._fx-drawers')
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/fx-details.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/fx-catalog.js') }}"></script>
@endpush
