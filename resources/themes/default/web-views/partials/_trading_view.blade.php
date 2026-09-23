<section class="bg-gray-7">    
    <!-- Category Slider Section -->
    @if (!empty($fullCategoryStructure))
    <div class="mt-5 marketplace-categories ">
        <div class="bg-img-hero">
            <div class="container">
                <div class="position-relative">
                    <div class="js-slick-carousel u-slick position-static overflow-hidden u-slick-overflow-visble pb-5 pt-2 px-1"
                     data-pagi-classes="text-center right-0 bottom-1 left-0 u-slick__pagination u-slick__pagination--long mb-0 z-index-n1 mt-3 pt-1"
                     data-arrows-classes="u-slick__arrow u-slick__arrow--flat u-slick__arrow-centered--y rounded-circle"
                     data-arrow-left-classes="fas fa-arrow-left u-slick__arrow-inner u-slick__arrow-inner--left ml-lg-2 ml-xl-n3"
                     data-arrow-right-classes="fas fa-arrow-right u-slick__arrow-inner u-slick__arrow-inner--right mr-lg-2 mr-xl-n3"
                     data-slides-show="7"
                     data-slides-scroll="1"
                        data-responsive='[
                            { "breakpoint": 1400, "settings": { "slidesToShow": 6 } },
                            { "breakpoint": 1200, "settings": { "slidesToShow": 4 } },
                            { "breakpoint": 992, "settings": { "slidesToShow": 3 } },
                            { "breakpoint": 768, "settings": { "slidesToShow": 2 } },
                            { "breakpoint": 554, "settings": { "slidesToShow": 1 } }
                        ]'>
                        
                          @include('web-views.partials._category_trading_recursive', ['fullCategoryStructure' => $fullCategoryStructure])

                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</section>



