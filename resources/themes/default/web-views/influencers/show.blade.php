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
    /* Star Rating CSS */
    .rate { float: left; height: 46px; padding: 0 10px; }
    .rate:not(:checked) > input { position:absolute; top:-9999px; }
    .rate:not(:checked) > label { float:right; width:1em; overflow:hidden; white-space:nowrap; cursor:pointer; font-size:30px; color:#ccc; }
    .rate:not(:checked) > label:before { content: '★ '; }
    .rate > input:checked ~ label { color: #ffc700; }
    .rate:not(:checked) > label:hover, .rate:not(:checked) > label:hover ~ label { color: #deb217; } 
    .rate > input:checked + label:hover, .rate > input:checked + label:hover ~ label, .rate > input:checked ~ label:hover, .rate > input:checked ~ label:hover ~ label { color: #c59b08; }
</style>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow border-0 text-center p-4">

                <img src="{{ asset('public/' . $influencer->profile_image) }}" 
                    onerror="this.src='{{ asset($influencer->profile_image) }}'" 
                    alt="{{ $influencer->name }}"  class="rounded-circle mx-auto mb-3" style="width: 180px; height: 180px; object-fit: cover; border: 5px solid #f8f9fa;">
                <h3 class="fw-bold">{{ $influencer->name }}</h3>
                <div class="mb-3">
                    <span class="badge bg-warning text-dark fs-6">★ {{ $influencer->avg_rating }} / 5.0</span>
                </div>
                
                <ul class="list-group list-group-flush text-start mb-4">
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fab fa-instagram"></i> {{ translate('instagram') }}</span>
                        <span class="fw-bold">{{$influencer->formattedFollowers('instagram_followers') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fab fa-youtube"></i> {{ translate('youtube') }}</span>
                        <span class="fw-bold">{{$influencer->formattedFollowers('youtube_subscribers')  }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fab fa-tiktok"></i> {{ translate('tiktok') }}</span>
                        <span class="fw-bold">{{$influencer->formattedFollowers('tiktok_followers')}}</span>
                    </li>
                </ul>

                <button class="btn btn-primary w-100 py-2" data-toggle="modal" 
                         data-target="#contactModal" 
                        data-id="{{ $influencer->id }}" data-name="{{ $influencer->name }}">
                    <i class="fas fa-paper-plane"></i> Contact {{ $influencer->name }}
                </button>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="fw-bold">{{ translate('about') }}</h4>
                    <p class="text-muted" style="line-height: 1.8;">{{ $influencer->bio }}</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold">{{ translate('rate_this_influencer') }}</h5>
                    <form action="{{ route('influencers.rate', $influencer->id) }}" method="POST" novalidate>
                        @csrf
                        <div class="rate">
                            <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="5 stars">5 stars</label>
                            <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars">4 stars</label>
                            <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars">3 stars</label>
                            <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars">2 stars</label>
                            <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star">1 star</label>
                        </div>
                        <div class="clearfix"></div>
                        <textarea name="comment" class="form-control mt-2" placeholder="{{ translate('write_a_review_2') }}" rows="3"></textarea>
                        <button class="btn btn-success mt-3">{{ translate('submit_review') }}</button>
                    </form>
                </div>
            </div>

            <h5 class="fw-bold mb-3">{{ translate('community_reviews') }}</h5>
            @foreach($influencer->ratings as $review)
                <div class="card mb-3 border-0 bg-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $review->user->name ?? 'User' }}</strong>
                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="text-warning small mb-2">
                            @for($i=1; $i<=5; $i++)
                                <i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-muted' }}"></i>
                            @endfor
                        </div>
                        <p class="mb-0">{{ $review->comment }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@include('web-views.influencers.contact_modal')
@endsection