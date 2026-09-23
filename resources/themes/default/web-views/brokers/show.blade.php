@extends('layouts.front-end.app')
@push('script')
    <script>
        // Check if there is a success session and trigger Toastr
        @if(session('success'))
            // Try using the theme's default toastr if available
            if (typeof toastr !== 'undefined') {
                toastr.success("{{ session('success') }}");
            } else {
                // Fallback to standard browser alert if toastr isn't loaded
                alert("{{ session('success') }}");
            }
        @endif
        
        // Check for errors (e.g. validation failed)
        @if($errors->any())
            @foreach($errors->all() as $error)
                if (typeof toastr !== 'undefined') {
                    toastr.error("{{ $error }}");
                }
            @endforeach
        @endif
    </script>
@endpush
@section('content')
<style>
    /* Interactive Star Rating CSS */
    .rate { float: left; height: 46px; padding: 0 10px; }
    .rate:not(:checked) > input { position:absolute; top:-9999px; }
    .rate:not(:checked) > label { float:right; width:1em; overflow:hidden; white-space:nowrap; cursor:pointer; font-size:30px; color:#ccc; }
    .rate:not(:checked) > label:before { content: '★ '; }
    .rate > input:checked ~ label { color: #ffc700; }
    .rate:not(:checked) > label:hover, .rate:not(:checked) > label:hover ~ label { color: #deb217; }
    .rate > input:checked + label:hover, .rate > input:checked + label:hover ~ label, .rate > input:checked ~ label:hover, .rate > input:checked ~ label:hover ~ label, .rate > input:checked ~ label:hover ~ input:checked ~ label { color: #c59b08; }
</style>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <img src="{{ asset($broker->logo) }}" 
                         onerror="this.src='{{ asset('public/'.$broker->logo) }}'"
                         class="img-fluid mb-3" style="max-height: 100px;">
                    
                    <h2 class="font-weight-bold text-primary">{{ $broker->system_rating }} <small class="text-muted text-sm">/10</small></h2>
                    <p class="text-muted small text-uppercase font-weight-bold">{{ translate('authority_score') }}</p>
                    
                    <hr>
                    
                    <a href="{{ $broker->affiliate_url }}" target="_blank" class="btn btn-success btn-lg btn-block mb-2 font-weight-bold shadow-sm">
                        Open Trading Account
                    </a>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">{{ translate('score_breakdown') }}</h5>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ translate('license_reg') }}</span>
                        <span class="font-weight-bold">{{ $broker->score_license }}/10</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ translate('software') }}</span>
                        <span class="font-weight-bold">{{ $broker->score_software }}/10</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ translate('business_stability') }}</span>
                        <span class="font-weight-bold">{{ $broker->score_stability }}/10</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body p-4">
                    <h3 class="font-weight-bold">About {{ $broker->name }}</h3>
                    <p class="text-muted">{{ $broker->description }}</p>
                    
                    <div class="row mt-4 p-3 bg-light rounded mx-1">
                        <div class="col-md-4 mb-2"><strong>Regulation:</strong><br>{{ $broker->regulations }}</div>
                        <div class="col-md-4 mb-2"><strong>Max Leverage:</strong><br>{{ $broker->max_leverage }}</div>
                        <div class="col-md-4 mb-2"><strong>Min Deposit:</strong><br>{{ $broker->min_deposit }}</div>
                        <div class="col-md-12 mt-2"><strong>Platforms:</strong><br>{{ $broker->platforms }}</div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                    <h4 class="mb-0">User Reviews ({{ $broker->review_count }})</h4>
                </div>
                <div class="card-body px-4">
                    
                    <div class="mb-5 p-4 bg-light rounded border">
                        @if(Auth::guard('customer')->check())
                            <h5 class="font-weight-bold mb-3">{{ translate('write_a_review') }}</h5>
                            <form action="{{ route('brokers.review', $broker->id) }}" method="POST" novalidate>
                                @csrf
                                <div class="form-group mb-0">
                                    <label class="mb-0">{{ translate('your_rating') }}</label>
                                    <div class="clearfix"></div>
                                    
                                    <div class="rate">
                                        <input type="radio" id="star5" name="rating" value="5" />
                                        <label for="star5" title="5 stars">5 stars</label>
                                        <input type="radio" id="star4" name="rating" value="4" />
                                        <label for="star4" title="4 stars">4 stars</label>
                                        <input type="radio" id="star3" name="rating" value="3" />
                                        <label for="star3" title="3 stars">3 stars</label>
                                        <input type="radio" id="star2" name="rating" value="2" />
                                        <label for="star2" title="2 stars">2 stars</label>
                                        <input type="radio" id="star1" name="rating" value="1" />
                                        <label for="star1" title="1 star">1 star</label>
                                    </div>
                                </div>
                                <div class="clearfix"></div>

                                <div class="form-group mt-2">
                                    <textarea name="comment" class="form-control" rows="3" placeholder="{{ translate('share_your_experience') }}" required></textarea>
                                </div>
                                <button class="btn btn-primary px-4">{{ translate('submit_review') }}</button>
                            </form>
                        @else
                            <div class="text-center py-3">
                                <p class="mb-2">Please login to write a review about {{ $broker->name }}</p>
                                <a href="{{ route('customer.auth.login') }}" class="btn btn-outline-primary">Login Now</a>
                            </div>
                        @endif
                    </div>

                    @forelse($broker->reviews as $review)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="font-weight-bold mb-0">{{ $review->reviewer_name }}</h6>
                                <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="text-warning">
                                @for($i=1; $i<=5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-muted' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="text-muted mb-0">{{ $review->comment }}</p>
                    </div>
                    @empty
                        <div class="alert alert-info">{{ translate('no_approved_reviews_yet_be_the_first') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection