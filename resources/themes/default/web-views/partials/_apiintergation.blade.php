<div class="container">
    <!-- Music Headphones -->

    <div class="mb-6">
        <!-- Nav nav-pills -->
        <div class="position-relative text-center z-index-2">
            <div class="d-flex justify-content-between border-bottom border-color-1 flex-lg-nowrap flex-wrap border-md-down-top-0 border-md-down-bottom-0">
                <h3 class="section-title mb-0 pb-2 font-size-22">{{ translate('api_integrations') }}</h3>

                <ul class="w-100 w-lg-auto nav nav-pills nav-tab-pill mb-2 pt-3 pt-lg-0 mb-0 border-top border-color-1 border-lg-top-0 align-items-center font-size-15 font-size-15-lg flex-nowrap flex-lg-wrap overflow-auto overflow-lg-visble pr-0"
                    id="pills-tab-4" role="tablist">
                    <li class="nav-item flex-shrink-0 flex-lg-shrink-1">
                        <a class="nav-link rounded-pill active" id="Qpills-one-example1-tab" data-toggle="pill"
                            href="#Qpills-one-example1" role="tab" aria-controls="Qpills-one-example1"
                            aria-selected="true">{{ translate('Bestsellers') }}</a>
                    </li>
                    <li class="nav-item flex-shrink-0 flex-lg-shrink-1">
                        <a class="nav-link rounded-pill" id="Qpills-two-example1-tab" data-toggle="pill"
                            href="#Qpills-two-example1" role="tab" aria-controls="Qpills-two-example1"
                            aria-selected="false">{{ translate('newly_added') }}</a>
                    </li>
                    <li class="nav-item flex-shrink-0 flex-lg-shrink-1">
                        <a class="nav-link rounded-pill" id="Qpills-three-example1-tab" data-toggle="pill"
                            href="#Qpills-three-example1" role="tab" aria-controls="Qpills-three-example1"
                            aria-selected="false">{{ translate('top_rated') }}</a>
                    </li>
                    <li class="nav-item flex-shrink-0 flex-lg-shrink-1">
                        <a class="nav-link rounded-pill" id="Qpills-four-example1-tab" data-toggle="pill"
                            href="#Qpills-four-example1" role="tab" aria-controls="Qpills-four-example1"
                            aria-selected="false">{{ translate('bundle_deals') }}</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- End Nav Pills -->

        <div class="row">
@php
    $firstProduct = $APIIntegrations->first();
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
                                <img class="img-fluid" loading="lazy" src="{{ (substr($firstProduct->thumbnail ?? '', 0, 4) === 'http') ? $firstProduct->thumbnail : asset('storage/app/public/product/thumbnail/' . $firstProduct->thumbnail) }}" alt="Image Description">
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
                                    <div class="text-gray-100">${{ $firstProduct->unit_price }}</div>
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
                            <!--<div class="d-none d-xl-block prodcut-add-cart">
                                <a class="btn-add-cart btn-add-cart__wide btn-primary transition-3d-hover">
                                    <i class="ec ec-add-to-cart mr-2"></i> Add to Cart
                                </a>
                            </div>-->
                        </div>
                    </div>
                    <!--<div class="product-item__footer">
                        <div class="border-top pt-2 flex-center-between flex-wrap">
                            <a class="text-gray-6 font-size-13"><i class="ec ec-compare mr-1 font-size-15"></i> {{ translate('compare') }}</a>
                            <a class="text-gray-6 font-size-13"><i class="ec ec-favorites mr-1 font-size-15"></i> {{ translate('add_to_wishlist') }}</a>
                        </div>
                    </div>-->
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@if ($APIIntegrations->count() > 1)
    <div class="col-md pl-md-0">
        <div class="tab-content" id="Qpills-tabContent">
            <div class="tab-pane fade pt-2 show active" id="tab-custom" role="tabpanel">
                <ul class="row list-unstyled products-group mb-0">
                    @foreach ($APIIntegrations->skip(1) as $product)
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
                                            <!--<div class="d-none d-xl-block prodcut-add-cart">
                                                <a class="btn-add-cart btn-primary transition-3d-hover">
                                                    <i class="ec ec-add-to-cart"></i>
                                                </a>
                                            </div>-->
                                        </div>
                                    </div>
                                    <!--<div class="product-item__footer">
                                        <div class="border-top pt-2 flex-center-between flex-wrap">
                                            <a class="text-gray-6 font-size-13"><i class="ec ec-compare mr-1 font-size-15"></i> {{ translate('compare') }}</a>
                                            <a class="text-gray-6 font-size-13"><i class="ec ec-favorites mr-1 font-size-15"></i> {{ translate('add_to_wishlist') }}</a>
                                        </div>
                                    </div>-->
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
