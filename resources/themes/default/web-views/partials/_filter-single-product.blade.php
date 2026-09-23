@php($overallRating = getOverallRating($product->reviews))

<div class="product-single-hover style--card h-100" {{ ($enableAos ?? true) ? 'data-aos=fade-up' : '' }}>
    <div class="overflow-hidden position-relative">
        <div class=" inline_product clickable d-flex justify-content-center">
            @if(!$product->not_sellable && getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                <span class="for-discount-value p-1 pl-2 pr-2 font-bold fs-13">
                    <span class="direction-ltr d-block">
                        -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
                    </span>
                </span>
            @else
                <div class="d-flex justify-content-end">
                    <span class="for-discount-value-null"></span>
                </div>
            @endif
            <div class="p-10px pb-0">
                <a href="{{route('product',$product->slug)}}" class="w-100">
                    <img alt="" loading="lazy" src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}">
                </a>
            </div>

            <div class="quick-view">
                <a class="btn-circle stopPropagation action-product-quick-view" href="javascript:" data-product-id="{{ $product->id }}">
                    <i class="fas fa-shopping-cart align-middle"></i>
                </a>
            </div>
            <button type="button" data-product-id="{{ $product->id }}"
                    class="wishlist-card-btn stopPropagation product-action-add-wishlist">
                <i class="{{ ($product->wish_list_count ?? 0) > 0 ? 'fas' : 'far' }} fa-heart wishlist_icon_{{ $product->id }}"
                   aria-hidden="true"></i>
            </button>
            @if($product->product_type == 'physical' && $product->current_stock <= 0)
                <span class="out_fo_stock">{{translate('out_of_stock')}}</span>
            @endif
        </div>
        <div class="single-product-details">
            @if($overallRating[0] != 0 )
            <div class="rating-show justify-content-between text-center">
                <span class="d-inline-block font-size-sm text-body">
                    @for($inc=1;$inc<=5;$inc++)
                        @if ($inc <= (int)$overallRating[0])
                            <i class="tio-star text-warning"></i>
                        @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0] > ((int)$overallRating[0]))
                            <i class="tio-star-half text-warning"></i>
                        @else
                            <i class="tio-star-outlined text-warning"></i>
                        @endif
                    @endfor
                    <label class="badge-style">( {{ count($product->reviews) }} )</label>
                </span>
            </div>
            @endif
            <h4 class="text-center mb-1 lh-1 letter-spacing-0">
                <a href="{{route('product',$product->slug)}}">
                    {{ $product['name'] }}
                </a>
            </h4>
            <div class="justify-content-between text-center mb-3">
                @if($product->not_sellable)
                    <h5 class="product-price text-center d-flex flex-wrap justify-content-center align-items-baseline gap-8 mb-0 lh-1 letter-spacing-0">
                        <a href="{{ $product->external_url }}" target="_blank" rel="noopener" class="visit-website-badge stopPropagation">{{ translate('visit_website') }}</a>
                    </h5>
                @else
                    <h5 class="product-price text-center d-flex flex-wrap justify-content-center align-items-baseline gap-8 mb-0 lh-1 letter-spacing-0">
                        @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                            <del class="category-single-product-price">
                                {{ webCurrencyConverter(amount: $product->unit_price) }}
                            </del>
                            <br>
                        @endif
                        <span class="text-accent text-dark">
                            {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                        </span>
                    </h5>
                @endif
            </div>
        </div>
    </div>
</div>
