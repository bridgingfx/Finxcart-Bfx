@extends('layouts.admin.app')


@section('title', 'Manage Influencers')
@push('css_or_js')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush
@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <i class="fi fi-rr-user"></i> Manage Influencers
        </h2>
        <a href="{{ route('admin.influencers.create') }}" class="btn btn-primary">
            <i class="fi fi-rr-plus"></i> Add New Influencer
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                    <thead class="thead-light">
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Social Stats</th>
                            <th>Rating</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($influencers as $inf)
                        <tr>
                            <td>
                                <img src="{{ asset('public/' . $inf->profile_image) }}" 
                                    onerror="this.src='{{ asset($inf->profile_image) }}'" 
                                    alt="{{ $inf->name }}"
                                     width="60" height="60" 
                                     class="rounded border" 
                                     style="object-fit: cover;">
                            </td>
                            <td class="text-capitalize font-weight-bold">
                                {{ $inf->name }}
                            </td>
                            <td>
                                <div class="d-flex gap-3 align-items-center">
                                    {{-- Instagram --}}
                                    @if($inf->instagram_followers > 0)
                                    <div class="text-center mx-2" title="Instagram">
                                        <i class="fab fa-instagram fa-2x text-danger mb-1"></i><br>
                                        <span class="font-weight-bold" style="font-size: 12px">
                                            {{ $inf->formattedFollowers('instagram_followers') }}
                                        </span>
                                    </div>
                                    @endif
                        
                                    {{-- YouTube --}}
                                    @if($inf->youtube_subscribers > 0)
                                    <div class="text-center mx-2" title="YouTube">
                                        <i class="fab fa-youtube fa-2x text-danger mb-1"></i><br>
                                        <span class="font-weight-bold" style="font-size: 12px">
                                            {{ $inf->formattedFollowers('youtube_subscribers') }}
                                        </span>
                                    </div>
                                    @endif
                        
                                    {{-- TikTok --}}
                                    @if($inf->tiktok_followers > 0)
                                    <div class="text-center mx-2" title="TikTok">
                                        <i class="fab fa-tiktok fa-2x text-dark mb-1"></i><br>
                                        <span class="font-weight-bold" style="font-size: 12px">
                                            {{ $inf->formattedFollowers('tiktok_followers') }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-soft-warning p-2" style="font-size: 14px">
                                    <i class="fi fi-rr-star"></i> {{ $inf->avg_rating }} 
                                    <span class="text-dark ml-1">({{ $inf->rating_count }})</span>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.influencers.edit', $inf->id) }}" class="btn btn-outline-info btn-sm">
                                    <i class="fi fi-sr-pencil"></i>
                                </a>
                                <form action="{{ route('admin.influencers.destroy', $inf->id) }}" method="POST" class="d-inline" novalidate>
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this influencer?')">
                                        <i class="fi fi-rr-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer">
                {{ $influencers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection