<div class="container">
    <div class="row">
        <div class="col-12 col-lg-6 mb-4">
            <div class="position-relative text-center z-index-2">
                <div class="d-flex justify-content-between border-bottom border-color-1 flex-lg-nowrap flex-wrap border-md-down-top-0 border-md-down-bottom-0">
                    <h3 class="section-title mb-0 pb-2 font-size-22">{{ translate('api_integrations') }}</h3>
                </div>
            </div>

            <div class="row">
                @foreach ($APIIntegrations as $product)
                    <div class="col-6 col-md-4">
                        @include('web-views.partials._filter-single-product', ['product' => $product])
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-12 col-lg-6 mb-4">
            <div class="position-relative text-center z-index-2">
                <div class="d-flex justify-content-between border-bottom border-color-1 flex-lg-nowrap flex-wrap border-md-down-top-0 border-md-down-bottom-0">
                    <h3 class="section-title mb-0 pb-2 font-size-22">{{ translate('add_ons') }}</h3>
                </div>
            </div>

            <div class="row">
                @foreach ($AddOns as $product)
                    <div class="col-6 col-md-4">
                        @include('web-views.partials._filter-single-product', ['product' => $product])
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
