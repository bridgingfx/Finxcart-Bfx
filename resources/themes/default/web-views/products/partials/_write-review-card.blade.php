<div class="review-write-card">
    <h3><i class="tio-edit"></i>{{ translate('write_a_review') }}</h3>
    <p class="review-write-subtitle">{{ translate('share_your_experience_to_help_other_traders') }}</p>
    @guest('customer')
        <div class="review-login-cta">
            <a href="{{ route('customer.auth.login') }}" class="btn {{ $product->product_type === 'broker' ? 'btn-broker-primary' : 'btn-event-primary' }} element-center">
                <i class="tio-user-outlined"></i><span>{{ translate('login_to_write_a_review') }}</span>
            </a>
        </div>
    @else
        <form action="{{ route('review.store') }}" method="post" id="reviewForm{{ $product->id }}" novalidate>
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="order_id" value="">
            <input type="hidden" name="review_id" value="">
            <div class="star-rating-form">
                <div class="star-wrap">
                    <input class="rating" name="rating" value="" hidden>
                    <input class="star" checked type="radio" value="-1" id="skip-star-pr{{ $product->id }}" name="star-radio-pr{{ $product->id }}" autocomplete="off"/>
                    <label class="star-label hidden"></label>
                    @for($index = 1; $index <= 5; $index++)
                        <input class="star" type="radio" id="st-pr{{ $product->id }}-{{ $index }}" value="{{ $index }}" name="star-radio-pr{{ $product->id }}" autocomplete="off">
                        <label class="star-label" for="st-pr{{ $product->id }}-{{ $index }}">
                            <div class="star-shape"></div>
                        </label>
                    @endfor
                </div>
            </div>
            <textarea rows="3" class="form-control review-comment-box" name="comment" placeholder="{{ translate('write_your_review_here') }}" required></textarea>
            <button type="submit" class="btn {{ $product->product_type === 'broker' ? 'btn-broker-primary' : 'btn-event-primary' }} element-center">
                <i class="tio-checkmark-circle"></i><span>{{ translate('submit_review') }}</span>
            </button>
        </form>
        @push('script')
            <script>
                (function () {
                    var form = document.getElementById('reviewForm{{ $product->id }}');
                    if (!form) { return; }
                    var stars = form.querySelectorAll('input[name="star-radio-pr{{ $product->id }}"]');
                    var ratingInput = form.querySelector('input[name="rating"]');
                    stars.forEach(function (star) {
                        star.addEventListener('change', function () {
                            var value = parseInt(this.value, 10);
                            if (value >= 1) {
                                ratingInput.value = value;
                            }
                        });
                    });
                    form.addEventListener('submit', function (e) {
                        if (!ratingInput.value) {
                            e.preventDefault();
                            toastr.error('{{ translate('please_rate_the_quality') }}!', '', { CloseButton: true, ProgressBar: true });
                        }
                    });
                })();
            </script>
        @endpush
    @endguest
</div>
