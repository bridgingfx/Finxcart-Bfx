<div class="col-lg-6 px-max-md-0">
    <div class="card card __shadow h-100">
        <div class="card-body p-xl-35">
            <div class="row d-flex justify-content-between align-items-center mx-1 mb-3">
                <div class="d-flex gap-1 align-items-center">
                    <img class="size-30" src="{{theme_asset(path: "public/assets/front-end/png/top-rated.png")}}"
                         alt="">
                    <h2 class="font-bold pl-1 mb-0 fs-16">{{ translate('top_rated')}}</h2>
                </div>
                <div>
                    <a class="text-capitalize view-all-text web-text-primary"
                       href="{{route('products',['data_from'=>'top-rated','page'=>1])}}">{{ translate('view_all')}}
                        <i class="czi-arrow-{{Session::get('direction') === "rtl" ? 'left mr-1 ml-n1 mt-1 float-left' : 'right ml-1 mr-n1'}}"></i>
                    </a>
                </div>
            </div>
            <div class="row g-3" id="top-rated-grid">
                @foreach($topRatedProducts as $key => $product)
                    @if($key < 6)
                        @include('web-views.partials._top-rated-single-item', ['product' => $product])
                    @endif
                @endforeach
            </div>
            @if(($topRatedProducts->total() ?? $topRatedProducts->count()) > 6)
                <div class="d-flex justify-content-center gap-2 pt-3">
                    <button type="button" id="top-rated-load-more-btn" class="btn btn-outline-primary btn-sm">
                        {{ translate('view_more') }}
                    </button>
                    <button type="button" id="top-rated-load-less-btn" class="btn btn-outline-secondary btn-sm d-none">
                        {{ translate('view_less') }}
                    </button>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        if (typeof initLoadMoreSection === 'function') {
                            initLoadMoreSection({
                                moreBtnId: 'top-rated-load-more-btn',
                                lessBtnId: 'top-rated-load-less-btn',
                                containerId: 'top-rated-grid',
                                url: '{{ route('home.load-more-top-rated') }}',
                                paramName: 'offset',
                                startValue: 6,
                                step: 6,
                                htmlField: 'html'
                            });
                        }
                    });
                </script>
            @endif
        </div>
    </div>
</div>
