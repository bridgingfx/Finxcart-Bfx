@extends('layouts.admin.app')

@section('title', translate('quote_request') . ' #' . $quote->id)

@section('content')
    @php
        $statusBadge = match($quote->status?->value) {
            'pending' => 'badge-soft-warning',
            'quoted' => 'badge-soft--primary',
            'accepted' => 'badge-soft-success',
            'declined' => 'badge-soft-danger',
            default => 'badge-soft-secondary',
        };
    @endphp
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0">{{ translate('quote_request') }} #{{ $quote->id }}</h2>
            <span class="badge {{ $statusBadge }} text-capitalize fz-14">{{ $quote->status?->value }}</span>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-muted small">{{ translate('customer') }}</div>
                        <div class="fw-bold">{{ $quote->customer?->f_name }} {{ $quote->customer?->l_name }} ({{ $quote->customer?->email }})</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">{{ translate('freelancer') }}</div>
                        <div class="fw-bold">{{ $quote->seller?->shop?->name ?? trim($quote->seller?->f_name . ' ' . $quote->seller?->l_name) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">{{ translate('service') }}</div>
                        @if($quote->service)
                            <a href="{{ route('admin.freelancer.services.show', $quote->service->id) }}" class="fw-bold text-decoration-none text-hover-primary">
                                {{ $quote->service->title }}
                            </a>
                        @else
                            <div class="fw-bold">-</div>
                        @endif
                    </div>

                    <div class="col-md-12">
                        <div class="text-muted small">{{ translate('description') }}</div>
                        <div>{{ $quote->description }}</div>
                    </div>

                    @if(!empty($quote->attachment_full_url))
                        <div class="col-md-12">
                            <div class="text-muted small mb-1">{{ translate('attachments') }}</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($quote->attachment_full_url as $file)
                                    <a href="{{ $file['path'] ?? '#' }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="fi fi-rr-paperclip"></i> {{ $file['key'] ?? translate('file') . ' ' . $loop->iteration }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="col-md-4">
                        <div class="text-muted small">{{ translate('delivery_preference') }}</div>
                        <div class="fw-bold">{{ $quote->delivery_label }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">{{ translate('budget') }}</div>
                        <div class="fw-bold">{{ $quote->budget ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $quote->budget), currencyCode: getCurrencyCode()) : '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">{{ translate('created_at') }}</div>
                        <div class="fw-bold">{{ $quote->created_at->format('d M, Y h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($quote->status?->value !== 'pending')
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ translate('freelancer_reply') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="text-muted small">{{ translate('reply_message') }}</div>
                            <div>{{ $quote->reply_message ?: '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">{{ translate('quoted_price') }}</div>
                            <div class="fw-bold">{{ $quote->quoted_price ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $quote->quoted_price), currencyCode: getCurrencyCode()) : '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">{{ translate('quoted_delivery_days') }}</div>
                            <div class="fw-bold">{{ $quote->quoted_delivery_days ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">{{ translate('replied_at') }}</div>
                            <div class="fw-bold">{{ $quote->replied_at?->format('d M, Y h:i A') ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">{{ translate('accepted_at') }}</div>
                            <div class="fw-bold">{{ $quote->accepted_at?->format('d M, Y h:i A') ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">{{ translate('declined_at') }}</div>
                            <div class="fw-bold">{{ $quote->declined_at?->format('d M, Y h:i A') ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($quote->contract)
            <div class="card mb-3">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="text-muted small">{{ translate('resulting_contract') }}</div>
                        <div class="fw-bold">{{ translate('contract') }} #{{ $quote->contract->id }}</div>
                    </div>
                    <a href="{{ route('admin.freelancer.contracts.view', $quote->contract->id) }}" class="btn btn-sm btn-primary">
                        {{ translate('view_contract') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
