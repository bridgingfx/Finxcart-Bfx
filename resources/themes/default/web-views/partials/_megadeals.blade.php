@php
    use App\Utils\Helpers;
@endphp
<!-- Week Deals limited -->
<div class="bg-gray-7 mb-6 py-7">
	<div class="container">
		<div class="row">
		@php			
			$dealEndDate = $flashDeal['flashDeal']->end_date ?? null;
		@endphp


			<div class="col-md-4 col-lg-3 col-wd-2">
				<div class="max-width-244">
					<div class="d-flex border-bottom border-color-1 mb-3">
						<h3 class="section-title mb-0 pb-2 font-size-22">{{ translate('week_deals_limited_just_now') }}</h3>
					</div>
					<div class="mb-3 mb-md-2 text-center text-md-left">
						<h1 class="font-size-130 font-weight-light mb-2 text-lh-1">%</h1>
						<h6 class="text-gray-2 mb-2">{{ translate('hurry_up_offer_ends_in') }}</h6>
						<div class="js-countdown d-flex mx-n2 justify-content-center justify-content-md-start"
							data-end-date="{{ $dealEndDate }}"
							data-hours-format="%H"
							data-minutes-format="%M"
							data-seconds-format="%S">
							<!-- Countdown blocks -->
							<div class="text-lh-1 px-2 text-center">
								<div class="bg-white rounded-sm border border-width-2 border-primary py-2 px-2 min-width-46">
									<div class="text-gray-2 font-size-20 mb-2">
										<span class="js-cd-hours"></span>
									</div>
									<div class="text-gray-2 font-size-8 text-center">{{ translate('hours') }}</div>
								</div>
							</div>
							<div class="text-lh-1 px-2 text-center">
								<div class="bg-white rounded-sm border border-width-2 border-primary py-2 px-2 min-width-46">
									<div class="text-gray-2 font-size-20 mb-2">
										<span class="js-cd-minutes"></span>
									</div>
									<div class="text-gray-2 font-size-8 text-center">{{ translate('mins') }}</div>
								</div>
							</div>
							<div class="text-lh-1 px-2 text-center">
								<div class="bg-white rounded-sm border border-width-2 border-primary py-2 px-2 min-width-46">
									<div class="text-gray-2 font-size-20 mb-2">
										<span class="js-cd-seconds"></span>
									</div>
									<div class="text-gray-2 font-size-8 text-center">{{ translate('secs') }}</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>


			<div class="col-md-8 col-lg-9 col-wd-10">
				<div class="">
				<div class="js-slick-carousel u-slick position-static overflow-hidden u-slick-overflow-visble pb-5 pt-2 px-1"
					data-pagi-classes="text-center right-0 bottom-1 left-0 u-slick__pagination u-slick__pagination--long mb-0 z-index-n1 mt-3 pt-1"
					data-slides-show="5"
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

					@foreach($flashDeal['flashDealProducts'] as $flashDealProduct)
						@php
							$images = json_decode($flashDealProduct->images, true);
							$imagePath = !empty($images[0]['image_name']) ? asset('storage/deal/' . $images[0]['image_name']) : '';
						@endphp

						<div class="js-slide">
							<div class="product-item mx-1 remove-divider">
								<div class="product-item__outer h-100">
									<div class="product-item__inner bg-white px-wd-4 p-2 p-md-3">
										<div class="product-item__body pb-xl-2">
											<div class="mb-2">
												<a href="#" class="font-size-12 text-gray-5">{{ $flashDealProduct->meta_title }}</a>
											</div>
											<h5 class="mb-1 product-item__title">
												<a href="#" class="text-blue font-weight-bold">{{ $flashDealProduct->name }}</a>
											</h5>
											<div class="mb-2 text-center">
												@if($imagePath)
													<img src="{{ $imagePath }}" alt="Image" loading="lazy" style="width: 200px; height: 150px; object-fit: cover;">
												@endif
											</div>
											<div class="flex-center-between mb-1">
												<div class="prodcut-price">
													<div class="text-gray-100">{{ $flashDealProduct->unit_price }}</div>
												</div>
												<div class="d-none d-xl-block prodcut-add-cart">
													<a href="#" class="btn-add-cart btn-primary transition-3d-hover">
														<i class="ec ec-add-to-cart"></i>
													</a>
												</div>
											</div>
										</div>
										<div class="product-item__footer">
											<div class="border-top pt-2 flex-center-between flex-wrap">
												<a href="#" class="text-gray-6 font-size-13"><i class="ec ec-compare mr-1 font-size-15"></i> {{ translate('compare') }}</a>
												<a href="#" class="text-gray-6 font-size-13"><i class="ec ec-favorites mr-1 font-size-15"></i> {{ translate('wishlist') }}</a>
											</div>
										</div>
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
</div>
<!-- End Week Deals limited -->

