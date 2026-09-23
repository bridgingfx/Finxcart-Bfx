<div class="container ">
    <!-- Music Headphones -->
    <div class="mb-6">
        <!-- Nav nav-pills -->
        <div class="position-relative text-center z-index-2">
            <div class="d-flex justify-content-between border-bottom border-color-1 flex-lg-nowrap flex-wrap border-md-down-top-0 border-md-down-bottom-0">
                <h3 class="section-title mb-0 pb-2 font-size-22">{{ translate('add_ons') }}</h3>
            </div>
        </div>
        <!-- End Nav Pills -->

        <div class="row">
@php
    $firstProduct = $AddOns->first();
@endphp

@if ($firstProduct)
            <!-- Static Highlighted Product -->
    <div class="col-md-5 col-xl-3gdot9 min-height-630">
        <div class="products-group bg-white h-100">
            <div class="product-item remove-divider">
                <div class="product-item__outer h-100 w-100">
                    <div class="product-item__inner bg-white p-3">
                        <div class="product-item__body d-flex flex-column">
                            <div class="mb-1">
                                <h5 class="mb-0 product-item__title">
                                    <a href="{{route('product',$firstProduct->slug)}}" class="text-blue font-weight-bold">{{ $firstProduct->name }}</a>
                                </h5>
                            </div>
                            @php
                                $images = json_decode($firstProduct->images, true);
                            @endphp

                            <div class="mb-1 min-height-xl-450">
                                <a href="{{route('product',$firstProduct->slug)}}" class="d-block text-center my-4">
                                    <img class="img-fluid " loading="lazy" src="{{ (substr($firstProduct->thumbnail ?? '', 0, 4) === 'http') ? $firstProduct->thumbnail : asset('storage/app/public/product/thumbnail/' . $firstProduct->thumbnail) }}" alt="Image Description">
                                </a>
                                @if($images)
                                <div class="row mx-gutters-2 mb-3">
                                    @foreach ($images as $image)
                                        <div class="col-auto">
                                            <a class="js-fancybox max-width-60 u-media-viewer h-100"
                                            href="javascript:;" data-src="{{ asset('storage/app/public/product/'.$image['image_name']) }}"
                                            data-fancybox="fancyboxGallery6"
                                            data-caption="Product Gallery - image {{ $loop->iteration }}"
                                            data-speed="700" data-is-infinite="true">
                                                <img class="img-fluid border" loading="lazy" src="{{ asset('storage/app/public/product/'.$image['image_name']) }}" alt="Image Description">
                                                <span class="u-media-viewer__container">
                                                    <span class="u-media-viewer__icon">
                                                        <span class="fas fa-plus u-media-viewer__icon-inner"></span>
                                                    </span>
                                                </span>
                                            </a>
                                        </div>
                                    @endforeach
                                    <div class="col"></div>
                                </div>
                                @endif
                                <!-- End Gallery -->
                            </div>

                            <div class="flex-center-between mb-2">
                                <div class="prodcut-price price-pdt">
                                    @if($firstProduct->not_sellable)
                                        <h3 class="font-weight-normal text-accent d-flex align-items-end gap-2 pt-1">
                                            <a href="{{ $firstProduct->external_url }}" target="_blank" rel="noopener" class="visit-website-badge stopPropagation">{{ translate('visit_website') }}</a>
                                        </h3>
                                    @else
                                        <h3 class="font-weight-normal text-accent d-flex align-items-end gap-2 pt-1">
                                            <span class="discounted-unit-price fs-18 font-bold">
                                                {{ getProductPriceByType(product: $firstProduct, type: 'discounted_unit_price', result: 'string') }}
                                            </span>
                                            @if(getProductPriceByType(product: $firstProduct, type: 'discount', result: 'value') > 0)
                                                <del class="product-total-unit-price align-middle text-muted fs-16 font-semibold">
                                                    {{ webCurrencyConverter(amount: $firstProduct->unit_price) }}
                                                </del>
                                            @endif
                                        </h3>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if ($AddOns->count() > 1)
    <div class="col-md pl-md-0">
        <div class="tab-content" id="Qpills-tabContent">
            <div class="tab-pane fade pt-2 show active" id="tab-custom" role="tabpanel">
                <ul class="row list-unstyled products-group mb-0">
                    @foreach ($AddOns->skip(1) as $product)
                        <li class="col-6 col-md-4 col-wd-3 product-item mb-2">
                            <div class="product-item__outer h-100 w-100">
                                <div class="product-item__inner bg-white p-3">
                                    <div class="product-item__body pb-xl-2">
                                        <h5 class="mb-1 product-item__title title-pdt">
                                            <a href="{{route('product',$product->slug)}}" class="text-blue font-weight-bold">{{ $product->name }}</a>
                                        </h5>
                                        <div class="mb-2 ">
                                            <a href="{{route('product',$product->slug)}}" class="d-block text-center">
                                                <img class="img-fluid webinars-img"
                                                     src="{{ (substr($product->thumbnail ?? '', 0, 4) === 'http') ? $product->thumbnail : asset('storage/app/public/product/thumbnail/' . $product->thumbnail) }}"
                                                     alt="Image Description">
                                            </a>
                                        </div>
                                        <div class="flex-center-between mb-1">
                                            <div class="prodcut-price price-pdt">
                                                @if($product->not_sellable)
                                                    <h3 class="font-weight-normal text-accent d-flex align-items-end gap-2 pt-1">
                                                        <a href="{{ $product->external_url }}" target="_blank" rel="noopener" class="visit-website-badge stopPropagation">{{ translate('visit_website') }}</a>
                                                    </h3>
                                                @else
                                                    <h3 class="font-weight-normal text-accent d-flex align-items-end gap-2 pt-1">
                                                        <span class="discounted-unit-price fs-18 font-bold">
                                                            {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                                                        </span>
                                                        @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                                            <del class="product-total-unit-price align-middle text-muted fs-16 font-semibold">
                                                                {{ webCurrencyConverter(amount: $product->unit_price) }}
                                                            </del>
                                                        @endif
                                                    </h3>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
            <!-- End Tab Content -->
        </div>
    </div>
</div>
