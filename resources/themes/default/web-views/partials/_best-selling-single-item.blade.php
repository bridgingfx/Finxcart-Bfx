<div class="col-sm-6">
    <a class="__best-selling" href="{{route('product',$bestSellItem->slug)}}">
        @if(getProductPriceByType(product: $bestSellItem, type: 'discount', result: 'value') > 0)
            <div class="d-flex">
                <span class="for-discount-value p-1 pl-2 pr-2 font-bold fs-13">
                    <span class="direction-ltr d-block">
                        -{{ getProductPriceByType(product: $bestSellItem, type: 'discount', result: 'string') }}
                    </span>
                </span>
            </div>
        @endif
        <div class="d-flex flex-wrap">
            <div class="best-selleing-image">
                <img class="rounded" loading="lazy"
                     src="{{ getStorageImages(path: $bestSellItem?->thumbnail_full_url, type: 'product') }}"
                     alt="{{ translate('product') }}"/>
            </div>
            <div class="best-selling-details">
                <h3 class="widget-product-title h6">
                <span class="ptr fw-semibold">
                    {{ Str::limit($bestSellItem['name'],100) }}
                </span>
                </h3>
                @php($overallRating = getOverallRating($bestSellItem['reviews']))
                @if($overallRating[0] != 0 )
                    <div class="rating-show">
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
                        <label class="badge-style">( {{ count($bestSellItem['reviews']) }} )</label>
                    </span>
                    </div>
                @endif
                <h4 class="widget-product-meta d-flex flex-wrap gap-8 align-items-center row-gap-0 mb-0 letter-spacing-0">
                    <span>
                        @if(getProductPriceByType(product: $bestSellItem, type: 'discount', result: 'value') > 0)
                            <del class="__color-9B9B9B __text-12px">
                                {{ webCurrencyConverter(amount: $bestSellItem->unit_price) }}
                            </del>
                        @endif
                    </span>
                    <span class="text-accent text-dark">
                        {{ getProductPriceByType(product: $bestSellItem, type: 'discounted_unit_price', result: 'string') }}
                    </span>
                </h4>
            </div>
        </div>
    </a>
</div>
