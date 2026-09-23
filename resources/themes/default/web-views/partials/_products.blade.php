{{-- <ul class="row list-unstyled products-group no-gutters mx-3 d-flex justify-content-center my-5">

    @foreach ($latestProductsList as $pdtkey => $pdtval)

        <li class="col-sm-6 col-6 col-wd-3 p-1 col-xl-2 col-lg-3 col-md-4 product-item ">

            <div class="product-item__outer h-100 w-100">

                <div class="product-item__inner px-xl-4 p-3">

                    <div class="product-item__body pb-xl-2">

                        <h5 class="mb-1 product-item__title">

                            <span class="loop-product-categories"><a href="https://electro.madrasthemes.com/product-category/tv-audio/audio-speakers/" rel="tag">{{ $pdtval->subCategory?->name }}</a></span>

                            <a href="{{route('product',$pdtval->slug)}}"

                                class="text-blue font-weight-bold">{{ $pdtval->name }}</a>

                        </h5>



                        <div class="mb-2 img-container">

                            <a href="{{route('product',$pdtval->slug)}}" class="d-block text-center">

                                <img class="img-fluid" loading="lazy" src="{{ (substr($pdtval->thumbnail ?? '', 0, 4) === 'http') ? $pdtval->thumbnail : asset('storage/app/public/product/thumbnail/' . $pdtval->thumbnail) }}" alt="{{ $pdtval->name }}">

                            </a>

                        </div>



                        <div class="flex-center-between mb-1">

                            <div class="prodcut-price">

                                <div class="text-gray-100">${{ number_format($pdtval->unit_price, 2) }}</div>

                            </div>

                        </div>

                    </div>



                    <div class="product-item__footer">

                        <div class="border-top pt-2 flex-center-between flex-wrap">

                            <div class="d-xl-block prodcut-add-cart float-right">

                                <button type="button" data-toggle="tooltip" title="Add To Cart"

                                        class="btn-add-to-cart btn btn-2xs btn-theme1 cart-icons add-to-cart"

                                        data-product-id="{{ base64_encode($pdtval->id) }}"

                                        data-product-price="{{ $pdtval->unit_price }}"

                                        data-product-name="{{ $pdtval->name }}"

                                        data-token="{{ csrf_token() }}">

                                    <i class="fas fa-shopping-cart" title="Add to cart"></i>

                                </button>

                                <button type="button" class="add-to-wishlist btn btn-2xs btn-theme2 cart-icons"

                                        data-product-id="{{ $pdtval->id }}" data-toggle="tooltip"

                                        title="Add to Wishlist">

                                    <i class="fas fa-heart"></i>

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </li>

    @endforeach

</ul> --}}





<div class="container mt-5" id="finxcart_products">

    <div class="row" id="latest-products-row">
        @foreach ($latestProductsList as $pdtkey => $pdtval)
            <div class="col-lg-3 col-md-3 col-sm-4 col-6 p-2">
                @include('web-views.partials._filter-single-product', ['product' => $pdtval])
            </div>
        @endforeach
    </div>

    @if (($latestProductsListTotalCount ?? 0) > count($latestProductsList))
        <div class="d-flex justify-content-center gap-2 mt-3">
            <button type="button" id="latest-products-view-more"
                    class="btn btn-outline-primary"
                    data-initial-count="{{ count($latestProductsList) }}"
                    data-offset="{{ count($latestProductsList) }}"
                    data-url="{{ route('home.load-more-latest-products') }}">
                {{ translate('view_more') }}
            </button>
            <button type="button" id="latest-products-view-less"
                    class="btn btn-outline-secondary d-none">
                {{ translate('view_less') }}
            </button>
        </div>
    @endif

</div>

@push('script')
    <script>
        (function () {
            var viewMoreBtn = document.getElementById('latest-products-view-more');
            var viewLessBtn = document.getElementById('latest-products-view-less');
            if (!viewMoreBtn || !viewLessBtn) {
                return;
            }

            var initialCount = parseInt(viewMoreBtn.getAttribute('data-initial-count'), 10) || 12;

            function bindNewCards(cards) {
                cards.forEach(function (card) {
                    card.querySelectorAll('.product-action-add-wishlist').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            if (typeof addWishlist === 'function') {
                                addWishlist(btn.getAttribute('data-product-id'));
                            }
                        });
                    });
                    card.querySelectorAll('.stopPropagation').forEach(function (el) {
                        el.addEventListener('click', function (e) {
                            e.stopPropagation();
                        });
                    });
                    card.querySelectorAll('.action-product-quick-view').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            if (typeof productQuickView === 'function') {
                                productQuickView(btn.getAttribute('data-product-id'));
                            }
                        });
                    });
                    card.querySelectorAll('.clickable').forEach(function (el) {
                        el.addEventListener('click', function () {
                            var link = el.querySelector('a');
                            if (link) {
                                window.location = link.getAttribute('href');
                            }
                        });
                    });
                });
            }

            viewMoreBtn.addEventListener('click', function () {
                var offset = parseInt(viewMoreBtn.getAttribute('data-offset'), 10) || initialCount;
                var url = viewMoreBtn.getAttribute('data-url');

                viewMoreBtn.disabled = true;

                fetch(url + '?offset=' + offset, {
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        var row = document.getElementById('latest-products-row');
                        var beforeCount = row.children.length;
                        row.insertAdjacentHTML('beforeend', data.html);
                        bindNewCards(Array.prototype.slice.call(row.children, beforeCount));
                        if (window.AOS) {
                            AOS.refresh();
                        }

                        viewMoreBtn.setAttribute('data-offset', offset + 12);
                        viewMoreBtn.disabled = false;
                        viewLessBtn.classList.remove('d-none');

                        if (!data.hasMore) {
                            viewMoreBtn.classList.add('d-none');
                        }
                    })
                    .catch(function () {
                        viewMoreBtn.disabled = false;
                    });
            });

            viewLessBtn.addEventListener('click', function () {
                var row = document.getElementById('latest-products-row');
                Array.prototype.slice.call(row.children, initialCount).forEach(function (card) {
                    card.remove();
                });

                viewMoreBtn.setAttribute('data-offset', initialCount);
                viewMoreBtn.classList.remove('d-none');
                viewLessBtn.classList.add('d-none');
            });
        })();
    </script>
@endpush