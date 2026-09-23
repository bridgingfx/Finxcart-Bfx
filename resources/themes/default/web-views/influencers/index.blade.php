@extends('layouts.front-end.app')
@section('content')
@push('css_or_js')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush
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
<div class="container py-5" style="margin-top: 20px;">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h1 class="font-weight-bold"><i class="fas fa-star text-warning"></i> {{ translate('find_top_influencers') }}</h1>
        </div>
        <div class="col-md-4">
            <form method="GET" novalidate>
                <select name="sort" class="form-control" onchange="this.form.submit()">
                    <option value="">{{ translate('sort_by_2') }}</option>
                    <option value="rating_desc" {{ request('sort') == 'rating_desc' ? 'selected' : '' }}>Highest Rated</option>
                    <option value="followers_desc" {{ request('sort') == 'followers_desc' ? 'selected' : '' }}>Most Popular</option>
                </select>
            </form>
        </div>
    </div>

    <div class="row">
        @forelse($influencers as $inf)
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="position-relative">
                    <img src="{{ asset($inf->profile_image) }}" 
                         onerror="this.src='{{ asset('public/'.$inf->profile_image) }}'"
                         class="card-img-top" 
                         style="height: 250px; object-fit: cover;">
                         
                    <div class="position-absolute w-100 p-2 text-center text-white" style="bottom:0; background: rgba(0,0,0,0.7);">
                        <span class="text-warning">★ {{ $inf->avg_rating }}</span> ({{ $inf->rating_count }} Reviews)
                    </div>
                </div>
                
                <div class="card-body text-center">
                    <h5 class="card-title font-weight-bold">{{ $inf->name }}</h5>
                    
                    <div class="d-flex justify-content-center mb-3" style="gap: 15px;">
                        @if($inf->instagram_followers) 
                            <span title="Instagram" class="text-muted small">
                                <i class="fab fa-instagram text-danger"></i> {{ $inf->formattedFollowers('instagram_followers') }}
                            </span>
                        @endif
                        @if($inf->youtube_subscribers) 
                            <span title="YouTube" class="text-muted small">
                                <i class="fab fa-youtube text-danger"></i> {{ $inf->formattedFollowers('youtube_subscribers') }}
                            </span>
                        @endif
                        @if($inf->tiktok_followers) 
                            <span title="TikTok" class="text-muted small">
                                <i class="fab fa-tiktok text-dark"></i> {{ $inf->formattedFollowers('tiktok_followers') }}
                            </span>
                        @endif
                    </div>
                    
                    <div class="d-flex justify-content-center" style="gap: 10px;">
                        <a href="{{ route('influencers.show', $inf->slug) }}" class="btn btn-outline-dark btn-sm">Profile</a>
                        
                        <button class="btn btn-primary btn-sm" 
                                type="button"
                                data-toggle="modal" 
                                data-target="#contactModal"
                                data-bs-toggle="modal" 
                                data-bs-target="#contactModal"
                                data-id="{{ $inf->id }}" 
                                data-name="{{ $inf->name }}">
                            Contact
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
            <div class="col-12 text-center py-5">
                <h5 class="text-muted">{{ translate('No_influencers_found') }}</h5>
                <p class="text-muted">{{ translate('Try_adjusting_your_search_or_filters.') }}</p>
            </div>
        @endforelse
    </div>
    
    <div class="mt-4">
        {{ $influencers->appends(request()->query())->links() }}
    </div>
</div>

{{-- MODAL INCLUDE --}}
@include('web-views.influencers.contact_modal')
@endsection