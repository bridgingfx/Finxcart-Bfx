@extends('layouts.admin.app')

@section('title', translate('view_portfolio_item'))

@push('css_or_js')
    <style>
        .fp-view-image { width: 100%; max-width: 420px; border-radius: 12px; object-fit: cover; border: 1px solid #e7ebf0; }
        .fp-view-gallery { display: flex; flex-wrap: wrap; gap: 12px; }
        .fp-view-gallery img { width: 140px; height: 100px; object-fit: cover; border-radius: 10px; border: 1px solid #e7ebf0; }
        .fp-view-tag { display: inline-block; background: #eef3f8; color: #1c2b3a; border-radius: 999px; padding: 4px 10px; font-size: 13px; font-weight: 500; margin: 0 6px 6px 0; }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0">{{ translate('view_portfolio_item') }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.freelancer.portfolio.edit', $item->id) }}" class="btn btn-primary">
                    <i class="fi fi-rr-edit"></i> {{ translate('edit') }}
                </a>
                <a href="{{ route('admin.freelancer.portfolio.index') }}" class="btn btn-secondary">
                    {{ translate('back') }}
                </a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <img class="fp-view-image" alt="{{ $item->title }}" src="{{ getStorageImages(path: $item->image_full_url, type: 'backend-profile') }}">
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <h4 class="mb-1">{{ $item->title }}</h4>
                            <span class="badge {{ $item->is_active ? 'badge-success text-bg-success' : 'badge-secondary text-bg-secondary' }}">
                                {{ $item->is_active ? translate('active') : translate('inactive') }}
                            </span>
                        </div>

                        @if($item->description)
                            <p class="mb-3">{{ $item->description }}</p>
                        @endif

                        @if(!empty($item->tags))
                            <div class="mb-3">
                                @foreach($item->tags as $tag)
                                    <span class="fp-view-tag">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted mb-1 d-block">{{ translate('completed_at') }}</label>
                                {{ $item->completed_at?->format('d M, Y') ?? '-' }}
                            </div>
                            @if($item->project_url)
                                <div class="col-md-8">
                                    <label class="form-label text-muted mb-1 d-block">{{ translate('project_url') }}</label>
                                    <a href="{{ $item->project_url }}" target="_blank" rel="noopener noreferrer">{{ $item->project_url }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">{{ translate('freelancer') }}</h5>
            </div>
            <div class="card-body">
                <a class="fw-bold text-decoration-none" href="{{ route('admin.freelancer.accounts.view', $item->seller_id) }}">
                    {{ $item->seller?->shop?->name ?: trim(($item->seller?->f_name ?? '') . ' ' . ($item->seller?->l_name ?? '')) }}
                </a>
                <div class="text-muted fs-13">{{ $item->seller?->email }}</div>
            </div>
        </div>

        @if($item->galleryItems->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ translate('additional_project_images') }}</h5>
                </div>
                <div class="card-body">
                    <div class="fp-view-gallery">
                        @foreach($item->galleryItems as $galleryItem)
                            @if($galleryItem->url)
                                <a href="{{ $galleryItem->url }}" target="_blank" rel="noopener noreferrer">
                                    <img alt="" src="{{ getStorageImages(path: $galleryItem->image_full_url, type: 'backend-profile') }}">
                                </a>
                            @else
                                <img alt="" src="{{ getStorageImages(path: $galleryItem->image_full_url, type: 'backend-profile') }}">
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
