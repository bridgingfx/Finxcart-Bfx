<div class="mb-6 container finx-solutions">
    <!-- Nav nav-pills -->
    <div class="position-relative text-center z-index-2">
        <div class="d-flex justify-content-between border-bottom border-color-1 flex-lg-nowrap flex-wrap border-md-down-top-0 border-md-down-bottom-0">
            <h3 class="section-title mb-0 pb-2 font-size-22">{{ translate('pre_built_websites') }}</h3>
        </div>
    </div>
    <!-- End Nav Pills -->

    <div class="row">
        <!-- Spacer column: keeps the banner aligned to the same column as the
             Solutions section's banner above it, which has a sidebar here. -->
        <div class="col-12 col-xl-auto pr-lg-2 d-none d-xl-block">
            <div class="min-width-200"></div>
        </div>

        <!-- Banner Column -->
        <div class="col-md-6 col-lg-auto align-self-start">
            <div class="banner-bg finx-banner-card" style="background-image: url({{ theme_asset(path: 'public/assets/front-end/img/banner3.jpg') }});">
                <div class="px-2 text-center">
                    <img style="max-width:200px;" class="img-fluid" src="{{ theme_asset(path: 'public/assets/front-end/img/banner5.png') }}" alt="Creative Assets Banner">
                    <div class="mt-4 text-center">
                        <div class="mb-1 font-size-18 font-weight-light">
                            MILLIONS OF <strong>{{ translate('creative_assets') }}</strong>
                        </div>
                        <div class="mb-3 font-size-18 font-weight-light">{{ translate('unlimited_downloads') }}</div>
                        <div class="link text-gray-90 font-weight-bold font-size-16">{{ translate('one_low_cost_subscription') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-md pl-md-0 mt-5">
            <ul class="row list-unstyled products-group mb-0">
                @foreach($PreBuiltWebsites as $pdtval)
                    @php
                        $hasDiscount = !$pdtval->not_sellable && getProductPriceByType(product: $pdtval, type: 'discount', result: 'value') > 0;
                        $discountPercent = ($hasDiscount && $pdtval->unit_price > 0)
                            ? round((($pdtval->unit_price - getProductPriceByType(product: $pdtval, type: 'discounted_unit_price', result: 'value')) / $pdtval->unit_price) * 100)
                            : 0;
                    @endphp
                    <li class="col-6 col-md-4 col-wd-3 product-item mb-4" data-aos="fade-up">
                            <div class="product-item__outer h-100 w-100">
                                <div class="product-item__inner finx-product-card bg-white">
                                    <div class="product-item__body">
                                        <div class="product-item__thumb">
                                            @if($discountPercent > 0)
                                                <span class="discount-badge">-{{ $discountPercent }}%</span>
                                            @endif
                                            <a href="{{route('product',$pdtval->slug)}}" class="d-block h-100">
                                                <img class="img-fluid webinars-img"
                                                     src="{{ (substr($pdtval['thumbnail'] ?? '', 0, 4) === 'http') ? $pdtval['thumbnail'] : asset('storage/app/public/product/thumbnail/' . $pdtval['thumbnail']) }}"
                                                     alt="{{ $pdtval['name'] }}"
                                                     onerror="this.onerror=null;this.src='{{ theme_asset(path: 'public/assets/front-end/img/placeholder.png') }}';this.classList.add('img-fallback');">
                                            </a>
                                        </div>
                                        <div class="finx-card-content">
                                            <h5 class="mb-1 product-item__title">
                                                <a href="{{route('product',$pdtval->slug)}}">{{ $pdtval['name'] }}</a>
                                            </h5>
                                            <div class="finx-price-row">
                                                @if($pdtval->not_sellable)
                                                    <a href="{{ $pdtval->external_url }}" target="_blank" rel="noopener" class="finx-visit-btn stopPropagation">{{ translate('visit_website') }}</a>
                                                @else
                                                    <span class="discounted-unit-price">
                                                        {{ getProductPriceByType(product: $pdtval, type: 'discounted_unit_price', result: 'string') }}
                                                    </span>
                                                    @if(getProductPriceByType(product: $pdtval, type: 'discount', result: 'value') > 0)
                                                        <del class="finx-old-price">
                                                            {{ webCurrencyConverter(amount: $pdtval->unit_price) }}
                                                        </del>
                                                    @endif
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

@push('css_or_js')
    <style>
        .finx-solutions .section-title {
            color: var(--theme-color, #213f65);
            font-weight: 700;
        }

        /* Banner card */
        .finx-solutions .finx-banner-card {
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(17, 38, 92, .08);
            padding: 32px 0 28px;
        }

        /* Product card */
        .finx-product-card {
            border: 1px solid #edeff3 !important;
            border-radius: 14px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: #fff;
            transition: box-shadow .25s ease, transform .25s ease, border-color .25s ease;
        }
        .finx-product-card:hover {
            box-shadow: 0 14px 28px rgba(17, 38, 92, 0.12);
            transform: translateY(-4px);
            border-color: #dfe4ee !important;
        }
        .finx-product-card .product-item__body {
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .finx-product-card .product-item__thumb {
            position: relative;
            background: #f6f7fb;
            overflow: hidden;
            height: 160px;
            width: 100%;
        }
        .finx-product-card .webinars-img {
            display: block;
            width: 100%;
            height: 160px;
            object-fit: cover;
            transition: transform .35s ease;
        }
        .finx-product-card:hover .webinars-img {
            transform: scale(1.07);
        }
        .finx-product-card .webinars-img.img-fallback {
            object-fit: contain;
            padding: 28px;
            background: #eef0f4;
        }
        .finx-product-card:hover .webinars-img.img-fallback {
            transform: none;
        }
        .finx-product-card .discount-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: linear-gradient(135deg, #ff5b6c, #ff2d55);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            padding: 5px 9px;
            border-radius: 20px;
            z-index: 2;
            box-shadow: 0 4px 10px rgba(255, 45, 85, .35);
        }
        .finx-product-card .finx-card-content {
            padding: 14px 14px 16px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .finx-product-card .product-item__title {
            min-height: 40px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        .finx-product-card .product-item__title a {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            line-height: 1.35;
            transition: color .2s ease;
        }
        .finx-product-card:hover .product-item__title a {
            color: var(--theme-color2, #f18b32);
        }
        .finx-product-card .finx-price-row {
            margin-top: auto;
            min-height: 34px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .finx-product-card .discounted-unit-price {
            font-size: 17px;
            font-weight: 700;
            color: var(--theme-color, #213f65);
        }
        .finx-product-card .finx-old-price {
            font-size: 13px;
            color: #9aa2b1;
            font-weight: 500;
        }
        .finx-product-card .finx-visit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 7px 10px;
            border-radius: 20px;
            background: rgba(33, 63, 101, .08);
            color: var(--theme-color, #213f65);
            font-size: 13px;
            font-weight: 600;
            transition: all .2s ease;
        }
        .finx-product-card .finx-visit-btn:hover {
            background: var(--theme-color, #213f65);
            color: #fff;
            text-decoration: none;
        }
    </style>
@endpush
