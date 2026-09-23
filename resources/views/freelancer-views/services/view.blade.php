@extends('layouts.freelancer.app')

@section('title', translate('view_service'))

@push('css_or_js')
    <style>
        .fsv-hero {
            background: linear-gradient(135deg, #17395e 0%, #1f4a78 100%); border-radius: 14px; padding: 24px 26px; margin-bottom: 20px;
            color: #fff; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;
        }
        .fsv-hero h1 { color: #fff; font-weight: 800; font-size: 20px; margin-bottom: 8px; }
        .fsv-hero .badge { font-weight: 700; }
        .fsv-hero-meta { display: flex; gap: 8px; flex-wrap: wrap; }
        .fsv-hero-meta .badge { background: rgba(255,255,255,.15); color: #fff; }

        .fsv-section { background: #fff; border: 1px solid #e8edf3; border-radius: 12px; padding: 22px 24px; margin-bottom: 18px; }
        .fsv-section-title { font-weight: 800; font-size: 15px; color: #17395e; margin-bottom: 14px; }
        .fsv-fact-label { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .03em; }
        .fsv-fact-value { font-size: 14px; font-weight: 700; color: #1f2937; margin-top: 2px; }
        .fsv-fact-value.price { color: #16794f; font-size: 20px; }
        .fsv-description-box { background: #f9fafb; border-radius: 8px; padding: 14px; font-size: 14px; color: #374151; white-space: pre-line; }

        .fsv-gallery { display: flex; gap: 10px; flex-wrap: wrap; }
        .fsv-gallery img { width: 130px; height: 96px; object-fit: cover; border-radius: 10px; background: #eef2f7; border: 1px solid #e7ebf0; }

        .fsv-tier-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
        .fsv-tier-card { border: 1px solid #e8edf3; border-radius: 12px; padding: 18px; transition: box-shadow .2s ease, transform .2s ease; }
        .fsv-tier-card:hover { box-shadow: 0 12px 26px rgba(23, 57, 94, 0.08); transform: translateY(-2px); }
        .fsv-tier-name { font-weight: 800; text-transform: uppercase; font-size: 12px; letter-spacing: .04em; color: #f97316; }
        .fsv-tier-price { font-weight: 800; font-size: 22px; color: #17395e; margin: 6px 0 8px; }
        .fsv-tier-meta { font-size: 12px; color: #6b7280; margin-top: 10px; display: flex; gap: 12px; flex-wrap: wrap; }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <a href="{{ route('freelancer.services.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="tio-chevron-left"></i> {{ translate('back') }}
            </a>
            <a href="{{ route('freelancer.services.edit', [$service->id]) }}" class="btn btn-outline--primary">
                <i class="tio-edit"></i> {{ translate('edit') }}
            </a>
        </div>

        <div class="fsv-hero">
            <div>
                <h1>{{ $service->title }}</h1>
                <div class="fsv-hero-meta">
                    <span class="badge">{{ $service->category?->defaultname ?? '-' }}</span>
                    @if($service->specialization)
                        <span class="badge">{{ $service->specialization->defaultname }}</span>
                    @endif
                </div>
            </div>
            @if($service->is_active)
                <span class="badge badge-soft-success">{{ translate('active') }}</span>
            @else
                <span class="badge badge-soft-danger">{{ translate('inactive') }}</span>
            @endif
        </div>

        @if($service->images->count())
            <div class="fsv-section">
                <div class="fsv-section-title">{{ translate('gallery') }}</div>
                <div class="fsv-gallery">
                    @foreach($service->images as $image)
                        <img alt="" src="{{ getStorageImages(path: $image->image_full_url, type: 'backend-profile') }}">
                    @endforeach
                </div>
            </div>
        @endif

        <div class="fsv-section">
            <div class="fsv-section-title">{{ translate('service_details') }}</div>
            <div class="row g-4">
                @if($service->description)
                    <div class="col-12">
                        <div class="fsv-fact-label mb-2">{{ translate('service') }}</div>
                        <div class="fsv-description-box">{{ $service->description }}</div>
                    </div>
                @endif
                @unless($service->packages->count())
                    <div class="col-md-4">
                        <div class="fsv-fact-label">{{ translate('price') }}</div>
                        <div class="fsv-fact-value price">{{ $service->price !== null ? webCurrencyConverter($service->price) : '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="fsv-fact-label">{{ translate('delivery_time_days') }}</div>
                        <div class="fsv-fact-value">{{ $service->delivery_time_days ? $service->delivery_time_days . ' ' . translate('days') : '-' }}</div>
                    </div>
                @endunless
                <div class="col-md-4">
                    <div class="fsv-fact-label">{{ translate('created_at') }}</div>
                    <div class="fsv-fact-value">{{ $service->created_at?->format('d M, Y h:i A') }}</div>
                </div>
            </div>
        </div>

        @if($service->packages->count())
            <div class="fsv-section">
                <div class="fsv-section-title">{{ translate('pricing_packages') }}</div>
                <div class="fsv-tier-grid">
                    @foreach($service->packages as $package)
                        <div class="fsv-tier-card">
                            <div class="fsv-tier-name">{{ $package->title ?: translate($package->tier) }}</div>
                            @if($package->price !== null)
                                <div class="fsv-tier-price">{{ webCurrencyConverter($package->price) }}</div>
                            @endif
                            @if($package->description)
                                <div class="text-muted small">{{ $package->description }}</div>
                            @endif
                            <div class="fsv-tier-meta">
                                @if($package->delivery_time_days)
                                    <span><i class="tio-time"></i> {{ $package->delivery_time_days }} {{ translate('days_delivery') }}</span>
                                @endif
                                @if($package->revisions !== null)
                                    <span><i class="tio-refresh"></i> {{ $package->revisions }} {{ translate('revisions') }}</span>
                                @endif
                                @if(!$package->is_enabled)
                                    <span class="text-danger">{{ translate('disabled') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
