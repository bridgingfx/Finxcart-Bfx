<!-- Webinars Section -->
<div class="container mt-5 finx-solutions">
    <div class="mb-6">
        <!-- Section Header -->
        <div class="position-relative text-center z-index-2">
            <div class="d-flex justify-content-between border-bottom border-color-1 flex-lg-nowrap flex-wrap">
                <h3 class="section-title mb-0 pb-2 font-size-22">{{ translate('solutions') }}</h3>
                <ul class="w-100 w-lg-auto nav nav-pills nav-tab-pill mb-2 pt-3 pt-lg-0 mb-0 border-top border-color-1 border-lg-top-0 align-items-center font-size-15 flex-nowrap flex-lg-wrap overflow-auto">
                   <li class="flex-lg-shrink-1 flex-shrink-0 nav-item">
                        <a class="nav-link rounded-pill active" data-toggle="pill" href="#tab-solutions-bestsellers" role="tab">{{ translate('Bestsellers') }}</a>
                    </li>
                    <li class="flex-lg-shrink-1 flex-shrink-0 nav-item">
                        <a class="nav-link rounded-pill" data-toggle="pill" href="#tab-solutions-featured" role="tab">{{ translate('featured') }}</a>
                    </li>
                    <li class="flex-lg-shrink-1 flex-shrink-0 nav-item">
                        <a class="nav-link rounded-pill" data-toggle="pill" href="#tab-solutions-on-sale" role="tab">{{ translate('on_sale') }}</a>
                    </li>
                    <li class="flex-lg-shrink-1 flex-shrink-0 nav-item">
                        <a class="nav-link rounded-pill" data-toggle="pill" href="#tab-solutions-half-off" role="tab">50% {{ translate('Offer') }}</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar Subcategories: dropdown on mobile/tablet, list sidebar on desktop -->
            <div class="col-12 col-xl-auto pr-lg-2">
                <div class="d-xl-none mb-3">
                    <select class="custom-select" onchange="if(this.value) window.location.href=this.value;">
                        <option value="" selected>{{ translate('select_category') }}</option>
                        @foreach($solutioncategoryNames as $item)
                            <option value="{{ route('products',['sub_category_id'=> $item['id'],'data_from'=>'category','page'=>1]) }}">{{ $item['name'] ?? 'Unnamed' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-width-200 mt-xl-5 d-none d-xl-block">
                    <ul class="list-group list-group-flush flex-xl-wrap flex-xl-column mb-3 mb-xl-0 finx-cat-list">

                        @foreach($solutioncategoryNames as $item)
                            <li class="border-color-1 list-group-item border-lg-down-0 flex-shrink-0">
                                <a href="{{route('products',['sub_category_id'=> $item['id'],'data_from'=>'category','page'=>1])}}" class="d-block">{{ $item['name'] ?? 'Unnamed' }}</a>
                            </li>


                        @endforeach
                    </ul>
                </div>
            </div>


			<div class="col-md-6 col-lg-auto align-self-start">
				<div class="banner-bg finx-banner-card"
					style="background-image: url({{ theme_asset(path: 'public/assets/front-end/img/banner3.jpg') }});">
					<div>
						<div class="px-2">
							<img style="max-width:200px;" class="img-fluid" loading="lazy"
								src="{{ theme_asset(path: 'public/assets/front-end/img/banner4.png') }}" alt="Image Description">
						</div>
						<div class="mt-4 px-2 text-center">
							<div class="mb-1 font-size-18 font-weight-light">
								CATCH BEST <strong>{{ translate('deals') }}</strong>
							</div>
							<div class="mb-4 font-size-18 font-weight-light"> IN THEME CUSTOMIZATION
							</div>
							<a href="{{ route('products', ['offer_type' => 'discounted', 'data_from' => 'latest']) }}"
							   class="link text-gray-90 font-weight-bold font-size-16">
								{{ translate('explore_more') }}
								<span class="link__icon ml-1">
									<span class="link__icon-inner"><i
											class="ec ec-arrow-right-categproes"></i></span>
								</span>
							</a>
						</div>
					</div>
				</div>
			</div>
<div class="col-md pl-md-0">
    <div class="tab-content" id="tabContentWebinars">
    @php
        $solutionsTabs = [
            'tab-solutions-bestsellers' => $solutionsBestsellers ?? collect(),
            'tab-solutions-featured' => $solutionsFeatured ?? collect(),
            'tab-solutions-on-sale' => $solutionsOnSale ?? collect(),
            'tab-solutions-half-off' => $solutionsHalfOffProducts ?? collect(),
        ];
    @endphp

    @foreach($solutionsTabs as $tabId => $tabProducts)

        <div class="tab-pane fade pt-2 {{ $loop->first ? 'show active' : '' }}"
            id="{{ $tabId }}"
            role="tabpanel"
            style="margin-top:20px;">

            <ul class="row list-unstyled products-group mb-0"
                style="
                    margin-left:-8px;
                    margin-right:-8px;
                ">

                @forelse($tabProducts as $product)

                    @php
                        $hasDiscount = !$product->not_sellable &&
                            getProductPriceByType(
                                product: $product,
                                type: 'discount',
                                result: 'value'
                            ) > 0;

                        $discountPercent = ($hasDiscount && $product->unit_price > 0)
                            ? round(
                                (
                                    (
                                        $product->unit_price -
                                        getProductPriceByType(
                                            product: $product,
                                            type: 'discounted_unit_price',
                                            result: 'value'
                                        )
                                    )
                                    / $product->unit_price
                                ) * 100
                            )
                            : 0;
                    @endphp

                    <li class="col-6 col-md-4 col-wd-3 product-item mb-4 {{ $loop->iteration > 10 ? 'd-none solutions-extra-item' : '' }}"
                        style="
                            padding-left:8px;
                            padding-right:8px;
                            display:flex;
                        ">

                        <div class="product-item__outer"
                            style="
                                width:100%;
                                height:320px;
                            ">

                            <div class="product-item__inner finx-product-card bg-white"
                                style="
                                    width:100%;
                                    height:320px;
                                    border-radius:12px;
                                    overflow:hidden;
                                    border:1px solid #eeeeee;
                                    display:flex;
                                    flex-direction:column;
                                    box-sizing:border-box;
                                ">

                                <div class="product-item__body"
                                    style="
                                        width:100%;
                                        height:100%;
                                        display:flex;
                                        flex-direction:column;
                                        box-sizing:border-box;
                                    ">

                                    {{-- IMAGE --}}
                                    <div class="product-item__thumb"
                                        style="
                                            width:100%;
                                            height:200px;
                                            min-height:200px;
                                            max-height:200px;
                                            overflow:hidden;
                                            position:relative;
                                            background:#f7f7f7;
                                        ">

                                        @if($discountPercent > 0)

                                            <span class="discount-badge"
                                                style="
                                                    position:absolute;
                                                    top:10px;
                                                    left:10px;
                                                    z-index:5;
                                                    padding:5px 9px;
                                                    border-radius:5px;
                                                    font-size:12px;
                                                    font-weight:600;
                                                    line-height:1;
                                                ">
                                                -{{ $discountPercent }}%
                                            </span>

                                        @endif

                                        <a href="{{ route('product', $product->slug) }}"
                                            style="
                                                display:block;
                                                width:100%;
                                                height:200px;
                                                max-height:200px;
                                            ">

                                            <img
                                                class="img-fluid webinars-img"
                                                loading="lazy"
                                                src="{{ (substr($product->thumbnail ?? '', 0, 4) === 'http')
                                                    ? $product->thumbnail
                                                    : asset('storage/app/public/product/thumbnail/' . $product->thumbnail) }}"
                                                alt="{{ $product->name }}"
                                                style="
                                                    width:100%;
                                                    height:200px;
                                                    min-height:200px;
                                                    max-height:200px;
                                                    display:block;
                                                    object-fit:cover;
                                                    object-position:center;
                                                "
                                                onerror="this.onerror=null;this.src='{{ theme_asset(path: 'public/assets/front-end/img/placeholder.png') }}';this.classList.add('img-fallback');"
                                            >

                                        </a>

                                    </div>


                                    {{-- CONTENT --}}
                                    <div class="finx-card-content"
                                        style="
                                            width:100%;
                                            height:160px;
                                            min-height:160px;
                                            max-height:160px;
                                            padding:12px 16px;
                                            display:flex;
                                            flex-direction:column;
                                            justify-content:flex-start;
                                            box-sizing:border-box;
                                        ">

                                        {{-- TITLE --}}
                                        <h5 class="product-item__title"
                                            style="
                                                width:100%;
                                                height:40px;
                                                min-height:40px;
                                                max-height:40px;
                                                margin:0 0 6px 0;
                                                padding:0;
                                                overflow:hidden;
                                                line-height:20px;
                                                display:-webkit-box;
                                                -webkit-line-clamp:2;
                                                -webkit-box-orient:vertical;
                                                box-sizing:border-box;
                                            ">

                                            <a href="{{ route('product', $product->slug) }}"
                                                style="
                                                    display:-webkit-box;
                                                    width:100%;
                                                    height:40px;
                                                    max-height:40px;
                                                    margin:0;
                                                    padding:0;
                                                    -webkit-line-clamp:2;
                                                    -webkit-box-orient:vertical;
                                                    overflow:hidden;
                                                    line-height:20px;
                                                    text-decoration:none;
                                                ">

                                                {{ $product->name }}

                                            </a>

                                        </h5>


                                        {{-- PRICE --}}
                                        <div class="finx-price-row"
                                            style="
                                                width:100%;
                                                height:40px;
                                                min-height:40px;
                                                max-height:40px;
                                                margin:0;
                                                padding:0;
                                                display:flex;
                                                align-items:center;
                                                gap:8px;
                                                overflow:hidden;
                                                box-sizing:border-box;
                                            ">

                                            @if($product->not_sellable)

                                                <a href="{{ $product->external_url }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="finx-visit-btn stopPropagation"
                                                    style="
                                                        width:100%;
                                                        height:40px;
                                                        min-height:40px;
                                                        display:flex;
                                                        align-items:center;
                                                        justify-content:center;
                                                        border-radius:6px;
                                                        text-decoration:none;
                                                        box-sizing:border-box;
                                                    ">

                                                    {{ translate('visit_website') }}

                                                </a>

                                            @else

                                                {{-- CURRENT PRICE --}}
                                                <span class="discounted-unit-price"
                                                    style="
                                                        display:block;
                                                        max-width:65%;
                                                        line-height:20px;
                                                        white-space:nowrap;
                                                        overflow:hidden;
                                                        text-overflow:ellipsis;
                                                        margin:0;
                                                        padding:0;
                                                    ">

                                                    {{ getProductPriceByType(
                                                        product: $product,
                                                        type: 'discounted_unit_price',
                                                        result: 'string'
                                                    ) }}

                                                </span>


                                                {{-- OLD PRICE --}}
                                                @if(getProductPriceByType(
                                                    product: $product,
                                                    type: 'discount',
                                                    result: 'value'
                                                ) > 0)

                                                    <del class="finx-old-price"
                                                        style="
                                                            display:block;
                                                            line-height:18px;
                                                            font-size:13px;
                                                            white-space:nowrap;
                                                            overflow:hidden;
                                                            text-overflow:ellipsis;
                                                            margin:0;
                                                            padding:0;
                                                        ">

                                                        {{ webCurrencyConverter(amount: $product->unit_price) }}

                                                    </del>

                                                @endif

                                            @endif

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </li>

                @empty

                    <li class="col-12">
                        <p>{{ translate('no_products_found') }}</p>
                    </li>

                @endforelse

            </ul>


            {{-- VIEW MORE / VIEW LESS --}}
            @if($tabProducts->count() > 10)

                <div class="d-flex justify-content-center gap-2 mt-3">

                    <button type="button"
                        class="btn solutions-view-more"
                        data-pane="{{ $tabId }}">

                        {{ translate('view_more') }}

                    </button>

                    <button type="button"
                        class="btn solutions-view-less d-none"
                        data-pane="{{ $tabId }}">

                        {{ translate('view_less') }}

                    </button>

                </div>

            @endif

        </div>

    @endforeach

</div>

</div>

        </div>
    </div>
</div>
<!-- End Webinars Section -->

@push('css_or_js')
    <style>
        .finx-solutions .section-title {
            color: var(--theme-color, #213f65);
            font-weight: 700;
        }

        /* Tabs */
        .finx-solutions .nav-tab-pill .nav-link {
            border-radius: 30px;
            padding: 8px 18px;
            font-weight: 600;
            color: #5b6472;
            background: transparent;
            transition: all .2s ease;
            white-space: nowrap;
        }
        .finx-solutions .nav-tab-pill .nav-link:hover:not(.active) {
            background: #f1f3f9;
            color: var(--theme-color, #213f65);
        }
        .finx-solutions .nav-tab-pill .nav-link.active {
            background: var(--theme-color2, #f18b32) !important;
            color: #fff !important;
            box-shadow: 0 6px 14px rgba(241, 139, 50, .28);
        }

        /* Sidebar categories */
        .finx-solutions .finx-cat-list {
            background: #fff;
            border: 1px solid #edeff3;
            border-radius: 12px;
            padding: 6px;
            overflow: hidden;
        }
        .finx-solutions .finx-cat-list .list-group-item {
            border: none !important;
            border-radius: 8px !important;
            padding: 0;
            margin-bottom: 2px;
            transition: background .2s ease;
        }
        .finx-solutions .finx-cat-list .list-group-item:hover {
            background: #f7f8fb;
        }
        .finx-solutions .finx-cat-list .list-group-item a {
            color: #384153;
            font-weight: 500;
            font-size: 14px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: color .2s ease;
        }
        .finx-solutions .finx-cat-list .list-group-item a::after {
            content: "\203A";
            color: var(--theme-color2, #f18b32);
            font-weight: 700;
            opacity: 0;
            transform: translateX(-4px);
            transition: all .2s ease;
        }
        .finx-solutions .finx-cat-list .list-group-item:hover a {
            color: var(--theme-color2, #f18b32);
        }
        .finx-solutions .finx-cat-list .list-group-item:hover a::after {
            opacity: 1;
            transform: translateX(0);
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

        /* View more / less buttons */
        .finx-solutions .solutions-view-more,
        .finx-solutions .solutions-view-less {
            border: 1px solid var(--theme-color, #213f65);
            color: var(--theme-color, #213f65);
            background: #fff;
            border-radius: 30px;
            padding: 8px 26px;
            font-weight: 600;
            font-size: 14px;
            transition: all .2s ease;
        }
        .finx-solutions .solutions-view-more:hover,
        .finx-solutions .solutions-view-less:hover {
            background: var(--theme-color, #213f65);
            color: #fff;
        }
    </style>
@endpush

@push('script')
    <script>
        (function () {
            document.querySelectorAll('.solutions-view-more').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var pane = document.getElementById(btn.getAttribute('data-pane'));
                    if (!pane) return;
                    pane.querySelectorAll('.solutions-extra-item').forEach(function (item) {
                        item.classList.remove('d-none');
                    });
                    btn.classList.add('d-none');
                    var lessBtn = pane.parentElement.querySelector('.solutions-view-less');
                    if (lessBtn) lessBtn.classList.remove('d-none');
                });
            });

            document.querySelectorAll('.solutions-view-less').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var pane = document.getElementById(btn.getAttribute('data-pane'));
                    if (!pane) return;
                    pane.querySelectorAll('.solutions-extra-item').forEach(function (item) {
                        item.classList.add('d-none');
                    });
                    btn.classList.add('d-none');
                    var moreBtn = pane.parentElement.querySelector('.solutions-view-more');
                    if (moreBtn) moreBtn.classList.remove('d-none');
                });
            });
        })();
    </script>
@endpush
