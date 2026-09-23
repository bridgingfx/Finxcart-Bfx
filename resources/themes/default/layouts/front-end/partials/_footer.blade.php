@php
    $footerBanner = (isset($bannerTypeFooterBanner) && count($bannerTypeFooterBanner) > 0) ? $bannerTypeFooterBanner[0] : null;
    $footerBannerImage = $footerBanner ? getStorageImages(path: $footerBanner->photo_full_url, type: 'banner') : null;
    $supportEmail = 'support@finxcart.com';
@endphp

@push('styles')
<style>
    .footer-link {
        text-decoration: underline;
        color: #000;
        transition: all 0.2s ease;
    }
    .footer-link:hover {
        text-decoration: none;
        color: #000;
    }
</style>
@endpush


<footer>
	
	<div class="pt-8 pb-4 {{ $footerBannerImage ? '' : 'bg-gray-13' }}"
         @if($footerBannerImage)
             style="background-image: linear-gradient(rgba(19, 37, 71, 0.82), rgba(19, 37, 71, 0.9)), url('{{ $footerBannerImage }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"
         @endif>
		<div class="container mt-1">
			<div class="row">
				<div class="col-lg-5">
					<div class="mb-6">
						<a class="d-inline-block" href="{{ route('home') }}">
                            <img class="{{Session::get('direction') === "rtl" ? 'right-align' : ''}}"
                                 src="{{ getStorageImages(path: $web_config['footer_logo'], type: 'logo') }}"
                                 alt="{{ $web_config['company_name'] }}" style="max-width:140px" />
                        </a>
					</div>
					<div class="mb-4">
						<div class="row no-gutters">
							<div class="col-auto">
								<i class="ec ec-support text-primary font-size-56"></i>
							</div>
							<div class="col pl-3">
								<div class="font-size-13 font-weight-light">{{ translate('got_questions_call_us_24_7') }}</div>
                                <a href="{{ 'tel:'.$web_config['phone'] }}" class="font-size-20 text-gray-90">{{ getWebConfig(name: 'company_phone') }}</a>
                                {{-- <a href="tel:+218600874548" class="font-size-20 text-gray-90">+218 60 087 4548</a> --}}
							</div>
						</div>
					</div>
					<div class="mb-4">
						<h6 class="mb-1 font-weight-bold">{{ translate('contact info') }}</h6>
						<address class="">
							{{ getWebConfig(name: 'shop_address') }}
						</address>
                        <a href="mailto:{{ $supportEmail }}" class="footer-link">{{ $supportEmail }}</a>
					</div>
					<div class="my-4 my-md-4">
						<ul class="list-inline mb-0 opacity-7">
						 @if($web_config['social_media'])
                            @foreach ($web_config['social_media'] as $item)
							<li class="list-inline-item mr-0">
								<a class="btn font-size-20 btn-icon btn-soft-dark btn-bg-transparent rounded-circle" target="_blank" href="{{ $item->link }}">
									<span class="{{ $item->icon }} btn-icon__inner"></span>
								</a>
							</li>
							@endforeach
                        @endif
							{{-- <li class="list-inline-item mr-0">
								<a class="btn font-size-20 btn-icon btn-soft-dark btn-bg-transparent rounded-circle" href="#">
									<span class="fab fa-google btn-icon__inner"></span>
								</a>
							</li>
							<li class="list-inline-item mr-0">
								<a class="btn font-size-20 btn-icon btn-soft-dark btn-bg-transparent rounded-circle" href="#">
									<span class="fab fa-twitter btn-icon__inner"></span>
								</a>
							</li>
							<li class="list-inline-item mr-0">
								<a class="btn font-size-20 btn-icon btn-soft-dark btn-bg-transparent rounded-circle" href="#">
									<span class="fab fa-github btn-icon__inner"></span>
								</a>
							</li> --}}
						</ul>
					</div>
				</div>
				<div class="col-lg-7">
					<div class="row">
						<div class="col-12 col-md mb-4 mb-md-0">
							<h6 class="mb-3 font-weight-bold">{{ translate('find_it_fast') }}</h6>
							<!-- List Group -->
							@php($megacategorie = \App\Utils\CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting(dataLimit: 8))
							<ul class="list-group list-group-flush list-group-borderless mb-0 list-group-transparent">
								@foreach($megacategorie as $megamaincat)
								<li><a class="list-group-item list-group-item-action footer-link" href="{{route('products',['category_id'=> $megamaincat->id,'data_from'=>'category','page'=>1])}}">{{ $megamaincat->name }}</a></li>
								@endforeach
							</ul>
							<!-- End List Group -->
						</div>

						<div class="col-12 col-md mb-4 mb-md-0">
						    <h6 class="mb-3 font-weight-bold">{{ translate('useful_links') }}</h6>
							<!-- List Group -->
							<ul class="list-group list-group-flush list-group-borderless mb-0 list-group-transparent">
								<li><a class="list-group-item list-group-item-action footer-link" href="{{ route('business-page.view', ['slug' => 'about-us']) }}">About Us</a></li>
								<li><a class="list-group-item list-group-item-action footer-link" href="{{ route('contacts') }}">Contact Us</a></li>
								<li><a class="list-group-item list-group-item-action footer-link" href="{{ route('helpTopic') }}">FAQs</a></li>
								<li><a class="list-group-item list-group-item-action footer-link" href="{{ route('business-page.view', ['slug' => 'privacy-policy']) }}">Privacy Policy</a></li>
								<li><a class="list-group-item list-group-item-action footer-link" href="{{ route('business-page.view', ['slug' => 'terms-and-conditions']) }}">Terms & Conditions</a></li>
								<li><a class="list-group-item list-group-item-action footer-link" href="{{ route('business-page.view', ['slug' => 'vendor-policy']) }}">Vendor Policy</a></li>
								<li><a class="list-group-item list-group-item-action footer-link" href="{{ route('business-page.view', ['slug' => 'vendor-terms']) }}">Vendor Terms</a></li>
							</ul>
							<!-- End List Group -->
						</div>

						<div class="col-12 col-md mb-4 mb-md-0">
							<h6 class="mb-3 font-weight-bold">{{ translate('customer_care') }}</h6>
							<!-- List Group -->
							<ul class="list-group list-group-flush list-group-borderless mb-0 list-group-transparent">
								<li><a class="list-group-item list-group-item-action footer-link" href="{{ route('my.account.help') }}">My Account</a></li>
								<li><a class="list-group-item list-group-item-action footer-link" href="{{ route('customer.service') }}">Customer Service</a></li>
								<li><a class="list-group-item list-group-item-action footer-link" href="{{ route('product.support') }}">Product Support</a></li>
							</ul>
							<!-- End List Group -->
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Footer-bottom-widgets -->
	<!-- Footer-copy-right -->
	<div class="{{ $footerBannerImage ? '' : 'bg-gray-14' }} py-2" @if($footerBannerImage) style="background-color: rgba(0, 0, 0, 0.22);" @endif>
		<div class="container">
			<div class="flex-center-between d-block d-md-flex">
				<div class="mb-3 mb-md-0">© <a href="#" class="font-weight-bold ">{{ translate('finxcart') }}</a> - All rights Reserved</div>
				<div class="text-md-right">
					<span class="d-inline-block bg-white border rounded p-1">
						<img class="max-width-5" src="{{ theme_asset(path: 'public/assets/front-end/img/visa.jpg') }}" alt="Visa">
					</span>
					<span class="d-inline-block bg-white border rounded p-1">
						<img class="max-width-5" src="{{ theme_asset(path: 'public/assets/front-end/img/mastercard.jpg') }}" alt="Mastercard">
					</span>
					<span class="d-inline-block bg-white border rounded p-1">
						<img class="max-width-5" src="{{ theme_asset(path: 'public/assets/front-end/img/discover.jpg') }}" alt="Discover">
					</span>
					<span class="d-inline-block bg-white border rounded p-1">
						<img class="max-width-5" src="{{ theme_asset(path: 'public/assets/front-end/img/skrill.jpg') }}" alt="Skrill">
					</span>
					<span class="d-inline-block bg-white border rounded p-1">
						<img class="max-width-5" src="{{ theme_asset(path: 'public/assets/front-end/img/paypal.jpg') }}" alt="PayPal">
					</span>
					<span class="d-inline-block bg-white border rounded p-1">
						<img class="max-width-5" src="{{ theme_asset(path: 'public/assets/front-end/img/razor.png') }}" alt="razor">
					</span>
				</div>
			</div>
			<div class="d-flex flex-wrap gap-3 justify-content-start py-3 text-secondary pb-2">

				<a href="https://www.linkedin.com/company/finxcart/jobs/"
				class="footer-link"
				target="_blank"
				rel="noopener noreferrer">
					Careers
				</a>

				<a href="{{ route('warranty.policy') }}" class="footer-link">
					Warranty Policy
				</a>

				<a href="{{ route('vendor.auth.registration.index') }}" class="footer-link">
					Sell with us
				</a>

				<a href="{{ route('terms.of.use') }}" class="footer-link">
					Terms of Use
				</a>

				<a href="{{ route('terms.of.sale') }}" class="footer-link">
					Terms of Sale
				</a>

				<a href="{{ route('privacy.policy') }}" class="footer-link">
					Privacy Policy
				</a>

				<a href="{{ route('consumer.rights') }}" class="footer-link">
					Consumer Rights
				</a>

			</div>

		</div>
	</div>
	<!-- End Footer-copy-right -->
</footer>
