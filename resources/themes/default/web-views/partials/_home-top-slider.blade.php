@php
    use App\Utils\Helpers;
@endphp
@php($productCatWithProducts = \App\Utils\CategoryManager::getCategoryWithProducts())
@php($megacategorie = \App\Utils\CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting(dataLimit: 8))

<!-- Slider & Banner Section -->
<div class="mb-4 main_banner_top">
    <div class="container overflow-hidden">
        <div class="row">
            <!-- Slider -->
            <div class="col-xl pr-xl-2 mb-4 mb-xl-0">
                <div class="bg-img-banner bg-img-hero mr-xl-1 height-410-xl max-width-1060-wd max-width-830-xl overflow-hidden" style="background-image: url({{ theme_asset(path: 'public/assets/front-end/img/banImg.jpg') }})">
                    <div class="js-slick-carousel u-slick"
                        data-autoplay="true"
                        data-speed="7000"
                        data-pagi-classes="text-center position-absolute right-0 bottom-0 left-0 u-slick__pagination u-slick__pagination--long justify-content-start ml-9 mb-3 mb-md-5">

                        @if(!empty($bannerTypeMainBanner) && count($bannerTypeMainBanner) > 0)
                            <div class="js-slick-carousel u-slick"
                                data-autoplay="true"
                                data-speed="7000"
                                data-pagi-classes="text-center position-absolute right-0 bottom-0 left-0 u-slick__pagination u-slick__pagination--long justify-content-start ml-9 mb-3 mb-md-5">

                                @foreach($bannerTypeMainBanner as $banner)
                                    <div class="js-slide bg-img-hero-center">
                                        <div class="row height-410-xl py-7 py-md-0 mx-0">
                                            <div class="d-none d-wd-block offset-1"></div>
                                            <div class="col-xl col-6 col-md-6">
                                                <h1 class="font-size-55 text-lh-57 font-weight-light banner_title" data-scs-animation-in="fadeInUp" style="letter-spacing: -0.144px;">
                                                    {{ $banner['title'] ?? 'Banner Title' }}
                                                </h1>
                                                <h6 class="font-size-15 font-weight-bold mb-3" data-scs-animation-in="fadeInUp" data-scs-animation-delay="200">
                                                    {{ $banner['sub_title'] ?? 'Banner Subtitle' }}
                                                </h6>
                                            </div>
                                            <div class="col-xl-7 col-6 d-flex align-items-center ml-auto ml-md-0"
                                                data-scs-animation-in="zoomIn"
                                                data-scs-animation-delay="500">
                                                <img class="img-fluid banner_image_slider" src="{{ asset('storage/app/public/banner/' . $banner['photo']) }}" alt="{{ $banner['title'] ?? 'Banner Image' }}">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        @else
                            <div class="js-slide bg-img-hero-center">
                                <div class="row height-410-xl py-7 py-md-0 mx-0">
                                    <div class="d-none d-wd-block offset-1"></div>
                                    <div class="col-xl col-6 col-md-6">
                                        <h1 class="font-size-64 text-lh-57 font-weight-light"
                                            data-scs-animation-in="slideInLeft">
                                            DISCOVER TOP <span class="d-block font-size-55">FINTECH SERVICES</span>
                                        </h1>
                                        <h6 class="font-size-15 font-weight-bold mb-3"
                                            data-scs-animation-in="slideInLeft"
                                            data-scs-animation-delay="200">
                                            FOREX BROKERS, CRYPTO EXCHANGES & MORE
                                        </h6>
                                        @auth('customer')
                                            <a href="#finxcart_products" class="btn btn-primary transition-3d-hover rounded-lg font-weight-normal py-2 px-md-7 px-3 font-size-16"
                                                data-scs-animation-in="fadeInUp"
                                                data-scs-animation-delay="400">
                                                Explore Marketplace
                                            </a>
                                        @else
                                            <a href="{{ route('customer.auth.login') }}" class="btn btn-primary transition-3d-hover rounded-lg font-weight-normal py-2 px-md-7 px-3 font-size-16"
                                                data-scs-animation-in="fadeInUp"
                                                data-scs-animation-delay="400">
                                                Explore Marketplace
                                            </a>
                                        @endauth
                                    </div>
                                    <div class="col-xl-7 col-6 d-flex align-items-center ml-auto ml-md-0"
                                        data-scs-animation-in="slideInRight"
                                        data-scs-animation-delay="800">
                                        <img class="img-fluid banner_image_slider" src="{{ theme_asset(path: 'public/assets/front-end/img/slider3.png') }}" alt="Finxcart - B2B Fintech Marketplace">
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
            <!-- End Slider -->

            <!-- Banner -->
            <div class="col-xl-auto pl-xl-2">
                <div class="overflow-hidden">
                    <ul class="list-unstyled row flex-nowrap flex-xl-wrap overflow-auto overflow-lg-visible mx-n2 mx-xl-0 d-xl-block mb-0">

                        <!-- Banner 1: Explore Marketplace -->
                        <li class="px-2 px-xl-0 flex-shrink-0 flex-xl-shrink-1 mb-3">
                            <a href="{{ route('products') }}" class="min-height-126 max-width-320 py-1 py-xl-2 py-wd-1 banner-bg d-flex align-items-center text-gray-90">
                                <div class="col col-lg-6 col-xl-5 col-wd-6 mb-3 mb-lg-0 pr-lg-0">
                                    <img class="img-fluid" src="{{ theme_asset(path: 'public/assets/front-end/img/offer1.png') }}" alt="Explore Marketplace">
                                </div>
                                <div class="col col-lg-6 col-xl-7 col-wd-6 pr-xl-4 pr-wd-3">
                                    <div class="mb-2 pb-1 font-size-18 font-weight-light text-ls-n1 text-lh-23">
                                        EXPLORE <strong>500+</strong> FINTECH SERVICES
                                    </div>
                                    <div class="link text-gray-90 font-weight-bold font-size-15">
                                        Browse Marketplace
                                        <span class="link__icon ml-1">
                                            <span class="link__icon-inner"><i class="ec ec-arrow-right-categproes"></i></span>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </li>

                        <!-- Banner 2: Become a Vendor -->
                        <li class="px-2 px-xl-0 flex-shrink-0 flex-xl-shrink-1 mb-3">
                            <a href="{{ route('vendor.auth.login') }}" class="min-height-126 max-width-320 py-1 py-xl-2 py-wd-1 banner-bg d-flex align-items-center text-gray-90">
                                <div class="col col-lg-6 col-xl-5 col-wd-6 mb-3 mb-lg-0 pr-lg-0">
                                    <img class="img-fluid" src="{{ theme_asset(path: 'public/assets/front-end/img/offer2.png') }}" alt="List Your Services">
                                </div>
                                <div class="col col-lg-6 col-xl-7 col-wd-6 pr-xl-4 pr-wd-3">
                                    <div class="mb-2 pb-1 font-size-18 font-weight-light text-ls-n1 text-lh-23">
                                        LIST YOUR <strong>SERVICES</strong> TODAY
                                    </div>
                                    <div class="link text-gray-90 font-weight-bold font-size-15">
                                        Become a Vendor
                                        <span class="link__icon ml-1">
                                            <span class="link__icon-inner"><i class="ec ec-arrow-right-categproes"></i></span>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </li>

                        <!-- Banner 3: Sign In -->
                        <li class="px-2 px-xl-0 flex-shrink-0 flex-xl-shrink-1 mb-3">
                            <a href="{{ route('customer.auth.login') }}" class="min-height-126 max-width-320 py-1 py-xl-2 py-wd-1 banner-bg d-flex align-items-center text-gray-90">
                                <div class="col col-lg-6 col-xl-5 col-wd-6 mb-3 mb-lg-0 pr-lg-0">
                                    <img class="img-fluid" src="{{ theme_asset(path: 'public/assets/front-end/img/offer3.png') }}" alt="Sign In to Finxcart">
                                </div>
                                <div class="col col-lg-6 col-xl-7 col-wd-6 pr-xl-4 pr-wd-3">
                                    <div class="mb-2 pb-1 font-size-18 font-weight-light text-ls-n1 text-lh-23">
                                        SIGN IN TO <strong>FINXCART</strong>
                                    </div>
                                    <div class="link text-gray-90 font-weight-bold font-size-15">
                                        Sign In
                                        <span class="link__icon ml-1">
                                            <span class="link__icon-inner"><i class="ec ec-arrow-right-categproes"></i></span>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </li>

                    </ul>
                </div>
            </div>
            <!-- End Banner -->

        </div>
    </div>
</div>
<!-- End Slider & Banner Section -->