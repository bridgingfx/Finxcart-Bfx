@if ($featuredProductsList->count() > 0 )
            <div class="container pt-4 rtl px-0 px-md-3">
                <div class="__inline-62 pt-3">
                    <h2 class="feature-product-title mt-0 web-text-primary mb-0 letter-spacing-0">
                        {{ translate('featured_products') }}
                    </h2>
                    <div class="text-end px-3 d-none d-md-block">
                        <a class="text-capitalize view-all-text web-text-primary" href="{{route('products',['data_from'=>'featured','page'=>1])}}">
                            {{ translate('view_all')}}
                            <i class="czi-arrow-{{Session::get('direction') === 'rtl' ? 'left mr-1 ml-n1 mt-1' : 'right ml-1'}}"></i>
                        </a>
                    </div>
                    <div class="feature-product">
                        <div class="carousel-wrap p-1">
                            <div class="owl-carousel owl-theme" id="featured_products_list"
                                 data-loop="{{ count($featuredProductsList) > 6 ? 'true' : 'false' }}">
                                @foreach($featuredProductsList as $product)
                                    <div>
                                        @include('web-views.partials._feature-product',['product'=>$product, 'decimal_point_settings'=>$decimalPointSettings])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="text-center pt-2 d-md-none">
                            <a class="text-capitalize view-all-text web-text-primary" href="{{route('products',['data_from'=>'featured','page'=>1])}}">
                                {{ translate('view_all')}}
                                <i class="czi-arrow-{{Session::get('direction') === "rtl" ? 'left mr-1 ml-n1 mt-1' : 'right ml-1'}}"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif