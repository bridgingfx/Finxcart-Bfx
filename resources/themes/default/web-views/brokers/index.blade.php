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
<div class="container py-5">
    <div class="row align-items-center mb-4 border-bottom pb-3">
        <div class="col-md-8">
            <h1 class="font-weight-bold mb-0">{{ translate('regulated_broker_rankings') }}</h1>
            <p class="text-muted mb-0">{{ translate('compare_authority_scores_and_user_reviews') }}</p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0">
            <form method="GET" action="{{ route('brokers.index') }}" novalidate>
                <div class="d-flex align-items-center justify-content-end">
                    <label class="mr-2 mb-0 font-weight-bold text-nowrap">{{ translate('sort_by') }}</label>
                    <select name="sort" class="form-control" onchange="this.form.submit()">
                        <option value="authority_score" {{ request('sort') == 'authority_score' ? 'selected' : '' }}>
                            Authority Score (High to Low)
                        </option>
                        <option value="user_rating" {{ request('sort') == 'user_rating' ? 'selected' : '' }}>
                            User Rating (High to Low)
                        </option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>
                            Name (A - Z)
                        </option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>
                            Newest Added
                        </option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        @forelse($brokers as $broker)
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center border-right">
                            <img src="{{ asset($broker->logo) }}" 
                                 onerror="this.src='{{ asset('public/'.$broker->logo) }}'"
                                 class="img-fluid mb-3" style="max-height: 70px;">
                            
                            <a href="{{ $broker->affiliate_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-block font-weight-bold shadow-sm">
                                {{ translate('Open_Account') }}
                            </a>
                        </div>

                        <div class="col-md-7 px-md-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 class="font-weight-bold mb-1">
                                        <a href="{{ route('brokers.show', $broker->slug) }}" class="text-dark">{{ $broker->name }}</a>
                                    </h3>
                                    <span class="badge badge-soft-info">{{ $broker->regulations }}</span>
                                    <span class="badge badge-soft-dark">{{ $broker->platforms }}</span>
                                </div>
                            </div>
                            
                            <div class="row mt-3 text-center">
                                <div class="col-6 col-md-4">
                                    <div class="bg-light p-2 rounded {{ request('sort') == 'authority_score' || !request('sort') ? 'border border-primary' : '' }}">
                                        <small class="text-muted d-block text-uppercase" style="font-size: 10px;">{{ translate('authority_score') }}</small>
                                        <span class="h2 font-weight-bold text-primary">{{ $broker->system_rating }}</span>
                                        <span class="small text-muted">/ 10</span>
                                    </div>
                                </div>
                                
                                <div class="col-6 col-md-4">
                                    <div class="bg-light p-2 rounded {{ request('sort') == 'user_rating' ? 'border border-warning' : '' }}">
                                        <small class="text-muted d-block text-uppercase" style="font-size: 10px;">{{ translate('user_rating') }}</small>
                                        <span class="h2 font-weight-bold text-warning">{{ $broker->user_rating }}</span>
                                        <span class="small text-muted">/ 5.0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 border-left">
                            <ul class="list-unstyled mb-0 small">
                                <li class="d-flex justify-content-between mb-2">
                                    <span>{{ translate('license') }}</span> 
                                    <span class="font-weight-bold">{{ $broker->score_license }}/10</span>
                                </li>
                                <li class="d-flex justify-content-between mb-2">
                                    <span>{{ translate('software') }}</span> 
                                    <span class="font-weight-bold">{{ $broker->score_software }}/10</span>
                                </li>
                                <li class="d-flex justify-content-between mb-2">
                                    <span>{{ translate('stability') }}</span> 
                                    <span class="font-weight-bold">{{ $broker->score_stability }}/10</span>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span>{{ translate('min_dep') }}</span> 
                                    <span class="font-weight-bold">{{ $broker->min_deposit }}</span>
                                </li>
                            </ul>
                            <a href="{{ route('brokers.show', $broker->slug) }}" class="btn btn-outline-secondary btn-sm btn-block mt-3">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
            <div class="col-12 text-center py-5">
                <h5 class="text-muted">{{ translate('No_brokers_found') }}</h5>
                <p class="text-muted">{{ translate('Try_adjusting_your_search_or_filters.') }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $brokers->appends(request()->query())->links() }}
    </div>
</div>
@endsection