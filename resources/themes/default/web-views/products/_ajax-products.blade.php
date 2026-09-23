@if(count($products) > 0)
    @php($decimal_point_settings = getWebConfig(name: 'decimal_point_settings'))
    @if($isInitialLoad ?? false)
        <div class="row" id="ajax-products-grid">
    @endif
        @foreach($products as $product)
            @if(!empty($product['product_id']))
                @php($product=$product->product)
            @endif

            <div class="col-lg-3 col-md-3 col-sm-4 col-6 p-2">
                @if(!empty($product))
                    @include('web-views.partials._filter-single-product',['product'=>$product, 'decimal_point_settings'=>$decimal_point_settings])
                @endif
            </div>
        @endforeach
    @if($isInitialLoad ?? false)
        </div>

        @if($products->hasMorePages() || $products->currentPage() > 1)
            <div class="col-12 d-flex justify-content-center gap-2 pt-3" id="ajax-products-load-more-controls">
                <button type="button" id="ajax-products-load-more-btn"
                        class="btn btn-outline-primary {{ $products->hasMorePages() ? '' : 'd-none' }}"
                        data-total="{{ $products->total() }}">
                    {{ translate('view_more') }}
                </button>
                <button type="button" id="ajax-products-load-less-btn"
                        class="btn btn-outline-secondary {{ $products->currentPage() > 1 ? '' : 'd-none' }}">
                    {{ translate('view_less') }}
                </button>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (typeof initLoadMoreSection === 'function') {
                        initLoadMoreSection({
                            moreBtnId: 'ajax-products-load-more-btn',
                            lessBtnId: 'ajax-products-load-less-btn',
                            containerId: 'ajax-products-grid',
                            url: document.getElementById('products-search-data-backup').getAttribute('data-url'),
                            paramName: 'page',
                            startValue: 2,
                            htmlField: 'html_products',
                            totalField: 'total_product',
                            extraParams: function () {
                                return (typeof productListPageData !== 'undefined') ? productListPageData : {};
                            }
                        });
                    }
                });
            </script>
        @endif
    @endif
@else
    <div class="d-flex justify-content-center align-items-center w-100 py-5">
        <div>
            <img src="{{ theme_asset(path: 'public/assets/front-end/img/media/product.svg') }}" class="img-fluid" alt="">
            <h6 class="text-muted">{{ translate('no_product_found') }}</h6>
        </div>
    </div>
@endif
