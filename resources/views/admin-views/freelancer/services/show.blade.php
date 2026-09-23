@extends('layouts.admin.app')

@section('title', translate('view_service'))

@push('css_or_js')
    <style>
        .fs-view-image-strip { display: flex; flex-wrap: wrap; gap: 12px; }
        .fs-view-image-strip img { width: 140px; height: 100px; object-fit: cover; border-radius: 10px; border: 1px solid #e7ebf0; }
        .fs-view-tier-card { border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; height: 100%; }
        .fs-view-tier-card.is-enabled { border-color: #1a2f5e; box-shadow: 0 6px 18px rgba(26,47,94,.08); }
        .fs-view-tier-header { padding: 14px 16px; border-bottom: 1px solid #eef1f5; display: flex; justify-content: space-between; align-items: center; }
        .fs-view-tier-body { padding: 16px; display: flex; flex-direction: column; gap: 10px; }
        .fs-view-tier-price { font-weight: 800; font-size: 20px; color: #1a2f5e; }
        .fs-view-feature-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 6px; }
        .fs-view-feature-list li.is-included i { color: #16a34a; }
        .fs-view-feature-list li.is-excluded { color: #9ca3af; }
        .fs-view-feature-list li.is-excluded i { color: #d1d5db; }
        .fs-view-seller-row { display: flex; align-items: center; gap: 12px; }
        .fs-view-seller-row img { width: 52px; height: 52px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 0 0 1px #e7ebf0; }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0">{{ translate('view_service') }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.freelancer.services.edit', $service->id) }}" class="btn btn-primary">
                    <i class="fi fi-rr-edit"></i> {{ translate('edit') }}
                </a>
                <a href="{{ route('admin.freelancer.services.index') }}" class="btn btn-secondary">
                    {{ translate('back') }}
                </a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <h4 class="mb-1">{{ $service->title }}</h4>
                        <div class="text-muted fs-13">
                            {{ $service->category?->defaultname ?? $service->category?->name ?? '-' }}
                            @if($service->specialization)
                                / {{ $service->specialization?->defaultname ?? $service->specialization?->name }}
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="badge {{ $service->is_active ? 'badge-success text-bg-success' : 'badge-secondary text-bg-secondary' }} fs-13">
                            {{ $service->is_active ? translate('active') : translate('inactive') }}
                        </span>
                    </div>

                    @if($service->description)
                        <div class="col-12">
                            <label class="form-label text-muted mb-1">{{ translate('service') }}</label>
                            <div>{{ $service->description }}</div>
                        </div>
                    @endif

                    <div class="col-md-3">
                        <label class="form-label text-muted mb-1 d-block">{{ translate('pricing_type') }}</label>
                        {{ $service->pricing_type?->value === 'quote' ? translate('quote_first_customer_requests_a_quote') : translate('fixed_price_buy_now') }}
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted mb-1 d-block">{{ translate('price') }}</label>
                        {{ $service->price !== null ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $service->price), currencyCode: getCurrencyCode()) : '-' }}
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted mb-1 d-block">{{ translate('delivery_time_days') }}</label>
                        {{ $service->delivery_time_days ? $service->delivery_time_days . ' ' . translate('days') : '-' }}
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted mb-1 d-block">{{ translate('service_offerings') }}</label>
                        <div class="d-flex flex-column gap-1 fs-13">
                            <span>{{ $service->offers_subscription ? '✓' : '✗' }} {{ translate('offers_subscriptions') }}</span>
                            <span>{{ $service->offers_video_consultation ? '✓' : '✗' }} {{ translate('paid_video_consultations') }}</span>
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
                <div class="fs-view-seller-row">
                    <img alt="" src="{{ getStorageImages(path: $service->seller?->shop?->image_full_url ?? $service->seller?->image_full_url, type: 'backend-profile') }}">
                    <div>
                        <a href="{{ route('admin.freelancer.accounts.view', $service->seller_id) }}" class="fw-bold text-decoration-none">
                            {{ $service->seller?->shop?->name ?: trim(($service->seller?->f_name ?? '') . ' ' . ($service->seller?->l_name ?? '')) }}
                        </a>
                        <div class="text-muted fs-13">{{ $service->seller?->email }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($service->images->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ translate('service_images') }}</h5>
                </div>
                <div class="card-body">
                    <div class="fs-view-image-strip">
                        @foreach($service->images as $image)
                            <img alt="" src="{{ getStorageImages(path: $image->image_full_url, type: 'backend-profile') }}">
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($service->packages->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ translate('pricing_packages') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($service->packages as $package)
                            <div class="col-md-4">
                                <div class="fs-view-tier-card {{ $package->is_enabled ? 'is-enabled' : '' }}">
                                    <div class="fs-view-tier-header">
                                        <span class="fw-bold text-capitalize">{{ $package->title ?: translate($package->tier) }}</span>
                                        <span class="badge {{ $package->is_enabled ? 'badge-success text-bg-success' : 'badge-secondary text-bg-secondary' }}">
                                            {{ $package->is_enabled ? translate('active') : translate('inactive') }}
                                        </span>
                                    </div>
                                    <div class="fs-view-tier-body">
                                        @if($package->description)
                                            <div class="text-muted fs-13">{{ $package->description }}</div>
                                        @endif
                                        <div class="fs-view-tier-price">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $package->price ?? 0), currencyCode: getCurrencyCode()) }}</div>
                                        <div class="d-flex gap-3 fs-13 text-muted">
                                            @if($package->delivery_time_days)
                                                <span><i class="fi fi-rr-time-fast"></i> {{ $package->delivery_time_days }} {{ translate('days') }}</span>
                                            @endif
                                            @if($package->revisions !== null)
                                                <span><i class="fi fi-rr-refresh"></i> {{ $package->revisions }} {{ translate('revisions') }}</span>
                                            @endif
                                        </div>
                                        @if(!empty($package->features))
                                            <ul class="fs-view-feature-list">
                                                @foreach($package->features as $feature)
                                                    <li class="{{ !empty($feature['included']) ? 'is-included' : 'is-excluded' }}">
                                                        <i class="fi {{ !empty($feature['included']) ? 'fi-rr-check' : 'fi-rr-cross-small' }}"></i>
                                                        {{ $feature['label'] ?? '' }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
