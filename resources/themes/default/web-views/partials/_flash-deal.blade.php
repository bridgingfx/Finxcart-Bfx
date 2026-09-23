<!-- Week Deals limited -->
<div class="bg-gray-7 mb-6 py-7">
    <div class="container">
        <div class="row">
            @php
                use Carbon\Carbon;

                $startDate = Carbon::parse($web_config['flash_deals']['start_date']);
                $endDate = Carbon::parse($web_config['flash_deals']['end_date'])->endOfDay();
                $now = Carbon::now();
                $totalDuration = $endDate->diffInSeconds($startDate);
                $elapsedDuration = $now->diffInSeconds($startDate);
                $percentageDone = ($elapsedDuration > 0 && $totalDuration > 0)
                    ? min(100, max(0, ($elapsedDuration / $totalDuration) * 100))
                    : 0;

                $endDateString = $endDate->format('Y-m-d H:i:s');
                $dealStatus = 'running';
                if ($now->lt($startDate)) {
                    $dealStatus = 'upcoming';
                } elseif ($now->gt($endDate)) {
                    $dealStatus = 'expired';
                }
            @endphp

            <div class="col-md-4 col-lg-3 col-wd-2">
                <div class="max-width-244">
                    <div class="d-flex border-bottom border-color-1 mb-3">
                        <h3 class="section-title mb-0 pb-2 font-size-22">{{ $web_config['flash_deals']->title }}</h3>
                    </div>
                    <div class="mb-3 mb-md-2 text-center text-md-left">

                        <h1 class="font-size-130 flashdeal_value font-weight-light mb-2 text-lh-1">50%</h1>

                        @if ($dealStatus === 'upcoming')
                            <h6 class="text-warning mb-2">{{ translate('coming_soon') }}</h6>
                        @elseif ($dealStatus === 'expired')
                            <h6 class="text-danger mb-2">{{ translate('expired') }}</h6>
                        @else
                            <h6 class="text-gray-2 mb-2">{{ translate('hurry_up_offer_ends_in') }}</h6>
                            <div id="flash-deal-countdown" data-end-date="{{ $endDateString }}">
                                <div class="d-flex mx-n2 justify-content-center justify-content-md-start">
                                    <div class="text-lh-1 px-2 text-center">
                                        <div class="bg-white rounded-sm border border-width-2 border-primary py-2 px-2 min-width-46">
                                            <div class="text-gray-2 font-size-20 mb-2" id="countdown-days">00</div>
                                            <div class="text-gray-2 font-size-8 text-center">{{ translate('days') }}</div>
                                        </div>
                                    </div>
                                    <div class="text-lh-1 px-2 text-center">
                                        <div class="bg-white rounded-sm border border-width-2 border-primary py-2 px-2 min-width-46">
                                            <div class="text-gray-2 font-size-20 mb-2" id="countdown-hours">00</div>
                                            <div class="text-gray-2 font-size-8 text-center">{{ translate('hours') }}</div>
                                        </div>
                                    </div>
                                    <div class="text-lh-1 px-2 text-center">
                                        <div class="bg-white rounded-sm border border-width-2 border-primary py-2 px-2 min-width-46">
                                            <div class="text-gray-2 font-size-20 mb-2" id="countdown-minutes">00</div>
                                            <div class="text-gray-2 font-size-8 text-center">{{ translate('mins') }}</div>
                                        </div>
                                    </div>
                                    <div class="text-lh-1 px-2 text-center">
                                        <div class="bg-white rounded-sm border border-width-2 border-primary py-2 px-2 min-width-46">
                                            <div class="text-gray-2 font-size-20 mb-2" id="countdown-seconds">00</div>
                                            <div class="text-gray-2 font-size-8 text-center">{{ translate('secs') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-8 col-lg-9 col-wd-10">
                <div class="js-slick-carousel u-slick position-static overflow-hidden u-slick-overflow-visble pb-5 pt-2 px-1"
                     data-pagi-classes="text-center right-0 bottom-1 left-0 u-slick__pagination u-slick__pagination--long mb-0 z-index-n1 mt-3 pt-1"
                     data-slides-show="4"
                     data-slides-scroll="1"
                     data-responsive='[{
                         "breakpoint": 1400,
                         "settings": { "slidesToShow": 4 }
                     }, {
                         "breakpoint": 1200,
                         "settings": { "slidesToShow": 3 }
                     }, {
                         "breakpoint": 992,
                         "settings": { "slidesToShow": 2 }
                     }, {
                         "breakpoint": 768,
                         "settings": { "slidesToShow": 2 }
                     }, {
                         "breakpoint": 554,
                         "settings": { "slidesToShow": 1 }
                     }]'>
                     {{-- {{ dd($flashDeal['flashDealProducts']) }} --}}
                    @foreach($flashDeal['flashDealProducts'] as $flashDealProduct)




                        <div class="js-slide">
                            <div class="product-item mx-1 remove-divider d-flex justify-content-center">
                                <div class="product-item__outer h-100 ">
                                    <div class="product-item__inner bg-white px-wd-4 p-2 p-md-3">
                                        <form action="{{ route('shop-cart') }}" method="POST" novalidate>
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $flashDealProduct->id }}">
                                            <input type="hidden" name="quantity" value="1">

                                            <div class="product-item__body pb-xl-2">
                                                <div class="mb-2">
                                                    <a href="{{ route('product',$flashDealProduct->slug) }}" class="font-size-12 text-gray-5">{{ $flashDealProduct->meta_title }}</a>
                                                </div>
                                                <h5 class="mb-1 product-item__title">
                                                    <a href="{{ route('product',$flashDealProduct->slug) }}" class="text-blue font-weight-bold">{{ $flashDealProduct->name }}</a>
                                                </h5>
                                                <div class="mb-2 text-center">

                                                        <a href="{{ route('product',$flashDealProduct->slug) }}">
                                                       <img src="{{ asset('storage/app/public/product/thumbnail/' . $flashDealProduct->thumbnail) }}" alt="Image" loading="lazy" style="width: 200px; height: 150px; object-fit: cover;">
                                                       </a>

                                                </div>
                                                <div class="flex-center-between mb-1">
                                                    <div class="prodcut-price">
                                                        @if($flashDealProduct->not_sellable)
                                                            <h3 class="font-weight-normal text-accent d-flex align-items-end gap-2 pt-1">
                                                                <a href="{{ $flashDealProduct->external_url }}" target="_blank" rel="noopener" class="visit-website-badge stopPropagation">{{ translate('visit_website') }}</a>
                                                            </h3>
                                                        @else
                                                            <h3 class="font-weight-normal text-accent d-flex align-items-end gap-2 pt-1">
                                                                <span class="discounted-unit-price fs-18 font-bold">
                                                                    {{ getProductPriceByType(product: $flashDealProduct, type: 'discounted_unit_price', result: 'string') }}
                                                                </span>
                                                                @if(getProductPriceByType(product: $flashDealProduct, type: 'discount', result: 'value') > 0)
                                                                    <del class="product-total-unit-price align-middle text-muted fs-16 font-semibold">
                                                                        {{ webCurrencyConverter(amount: $flashDealProduct->unit_price) }}
                                                                    </del>
                                                                @endif
                                                            </h3>
                                                        @endif
                                                    </div>
                                                    <!--<div class="d-none d-xl-block prodcut-add-cart">
                                                        <button type="submit" class="btn btn-primary transition-3d-hover">
                                                            <i class="ec ec-add-to-cart"></i>
                                                        </button>
                                                    </div>-->
                                                </div>
                                            </div>

                                            <!--<div class="product-item__footer">
                                                <div class="border-top pt-2 flex-center-between flex-wrap">
                                                    <a href="#" class="text-gray-6 font-size-13"><i class="ec ec-compare mr-1 font-size-15"></i> {{ translate('compare') }}</a>
                                                    <a href="#" class="text-gray-6 font-size-13"><i class="ec ec-favorites mr-1 font-size-15"></i> {{ translate('wishlist') }}</a>
                                                </div>
                                            </div>-->
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Week Deals limited -->

<!-- Countdown Timer Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const countdownEl = document.getElementById('flash-deal-countdown');
        if (!countdownEl) return;

        const endDateStr = countdownEl.getAttribute('data-end-date');
        const endDate = new Date(endDateStr).getTime();

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = endDate - now;

            if (distance < 0) {
                document.getElementById('countdown-days').innerText = '00';
                document.getElementById('countdown-hours').innerText = '00';
                document.getElementById('countdown-minutes').innerText = '00';
                document.getElementById('countdown-seconds').innerText = '00';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('countdown-days').innerText = String(days).padStart(2, '0');
            document.getElementById('countdown-hours').innerText = String(hours).padStart(2, '0');
            document.getElementById('countdown-minutes').innerText = String(minutes).padStart(2, '0');
            document.getElementById('countdown-seconds').innerText = String(seconds).padStart(2, '0');
        }

        updateCountdown(); // initial
        setInterval(updateCountdown, 1000); // every second
    });
</script>
