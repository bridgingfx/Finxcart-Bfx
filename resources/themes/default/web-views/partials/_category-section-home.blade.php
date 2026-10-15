@if ($categories->count() > 0 )
{{-- {{ dd($categories) }} --}}
<div class="mb-6 bg-gray-7 py-6 topcategories-section">
	<div class="container">
		<div class="d-flex justify-content-between border-bottom border-color-1 flex-lg-nowrap flex-wrap border-md-down-top-0 border-md-down-bottom-0 mb-5">
			<h3 class="section-title mb-0 pb-2 font-size-22">{{ translate('top_categories_this_week') }}</h3>
		</div>
		<div class="row flex-nowrap flex-md-wrap overflow-auto overflow-md-visible">
			@foreach($categories as $topcat)
			<div class="col-md-4 col-lg-3 col-xl-3 mb-3 flex-shrink-0 flex-md-shrink-1">
				<div class="bg-white overflow-hidden shadow-on-hover h-100 d-flex align-items-center">
					<a href="{{route('products',['category_id'=> $topcat['id'],'data_from'=>'category','page'=>1])}}" class="d-block pr-2 pr-wd-6">
						<div class="media align-items-center">
							<div class="">
								<img class="img-fluid" style="width:100px" loading="lazy" src="{{ getStorageImages(path:$topcat->icon_full_url, type: 'category') }}" alt="{{ $topcat->name }}">

							</div>
							<div class="ml-3 media-body">
								<h6 class="mb-0 text-gray-90">{{Str::limit($topcat->name, 15)}}</h6>
							</div>
						</div>
					</a>
				</div>
			</div>
			@endforeach
		</div>
	</div>
</div>
@endif
