@extends('layouts.freelancer.app')

@section('title', translate('view_portfolio_item'))

@push('css_or_js')
    <style>
        .fpv-hero {
            background: linear-gradient(135deg, #17395e 0%, #1f4a78 100%); border-radius: 14px; padding: 24px 26px; margin-bottom: 20px;
            color: #fff; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;
        }
        .fpv-hero h1 { color: #fff; font-weight: 800; font-size: 20px; margin-bottom: 6px; }
        .fpv-hero-meta { font-size: 13px; color: rgba(255,255,255,.75); }

        .fpv-cover { width: 100%; border-radius: 12px; object-fit: cover; max-height: 320px; background: #eef2f7; }

        .fpv-section { background: #fff; border: 1px solid #e8edf3; border-radius: 12px; padding: 22px 24px; margin-bottom: 18px; }
        .fpv-section-title { font-weight: 800; font-size: 15px; color: #17395e; margin-bottom: 14px; }
        .fpv-fact-label { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .03em; }
        .fpv-fact-value { font-size: 14px; font-weight: 700; color: #1f2937; margin-top: 2px; }
        .fpv-description-box { background: #f9fafb; border-radius: 8px; padding: 14px; font-size: 14px; color: #374151; white-space: pre-line; }

        .fpv-tag-chip {
            display: inline-flex; align-items: center; background: #eef3f8; color: #1c2b3a;
            border-radius: 999px; padding: 4px 12px; font-size: 12px; font-weight: 600; margin: 0 6px 6px 0;
        }

        .fpv-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
        .fpv-gallery img { width: 100%; height: 120px; object-fit: cover; border-radius: 10px; background: #eef2f7; border: 1px solid #e7ebf0; }
        .fpv-gallery a { display: block; transition: transform .15s ease; }
        .fpv-gallery a:hover { transform: translateY(-2px); }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <a href="{{ route('freelancer.portfolio.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="tio-chevron-left"></i> {{ translate('back') }}
            </a>
            <a href="{{ route('freelancer.portfolio.edit', [$item->id]) }}" class="btn btn-outline--primary">
                <i class="tio-edit"></i> {{ translate('edit') }}
            </a>
        </div>

        <div class="fpv-hero">
            <div>
                <h1>{{ $item->title }}</h1>
                @if($item->completed_at)
                    <div class="fpv-hero-meta"><i class="tio-calendar"></i> {{ $item->completed_at->format('d M, Y') }}</div>
                @endif
            </div>
            @if($item->is_active)
                <span class="badge badge-soft-success">{{ translate('active') }}</span>
            @else
                <span class="badge badge-soft-danger">{{ translate('inactive') }}</span>
            @endif
        </div>

        <div class="fpv-section">
            <div class="row g-4">
                <div class="col-md-4">
                    <img class="fpv-cover" alt="" src="{{ getStorageImages(path: $item->image_full_url, type: 'backend-profile') }}">
                </div>
                <div class="col-md-8">
                    @if($item->description)
                        <div class="fpv-fact-label mb-2">{{ translate('description') }}</div>
                        <div class="fpv-description-box mb-3">{{ $item->description }}</div>
                    @endif
                    <div class="row g-3">
                        @if(!empty($item->tags))
                            <div class="col-12">
                                <div class="fpv-fact-label mb-2">{{ translate('skills') }} / {{ translate('tags') }}</div>
                                <div>
                                    @foreach($item->tags as $tag)
                                        <span class="fpv-tag-chip">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <div class="fpv-fact-label">{{ translate('project_url') }}</div>
                            <div class="fpv-fact-value">
                                @if($item->project_url)
                                    <a href="{{ $item->project_url }}" target="_blank" rel="noopener">{{ $item->project_url }}</a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fpv-fact-label">{{ translate('completed_at') }}</div>
                            <div class="fpv-fact-value">{{ $item->completed_at?->format('d M, Y') ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($item->galleryItems->count())
            <div class="fpv-section">
                <div class="fpv-section-title">{{ translate('additional_project_images') }}</div>
                <div class="fpv-gallery">
                    @foreach($item->galleryItems as $galleryItem)
                        @if($galleryItem->url)
                            <a href="{{ $galleryItem->url }}" target="_blank" rel="noopener">
                                <img alt="" loading="lazy" src="{{ getStorageImages(path: $galleryItem->image_full_url, type: 'backend-profile') }}">
                            </a>
                        @else
                            <img alt="" loading="lazy" src="{{ getStorageImages(path: $galleryItem->image_full_url, type: 'backend-profile') }}">
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
