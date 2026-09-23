@include('web-views.products.partials._write-review-card')

<div class="review-list-card">
    @if(count($product->reviews) == 0 && $productReviews->total() == 0)
        <div class="review-empty-state">
            <img class="mw-100" src="{{ theme_asset(path: 'public/assets/front-end/img/icons/empty-review.svg') }}" alt="">
            <p class="text-capitalize mt-2">
                <small>{{ translate('No_review_given_yet') }}!</small>
            </p>
        </div>
    @else
        <div class="row pb-4">
            <div class="col-12" id="product-review-list">
                @include('web-views.partials._product-reviews')
            </div>
            @if(count($product->reviews) > 2)
                <div class="col-12">
                    <div class="card-footer d-flex justify-content-center align-items-center">
                        <button class="btn text-white view_more_button web--bg-primary">
                            {{ translate('view_more') }}
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
