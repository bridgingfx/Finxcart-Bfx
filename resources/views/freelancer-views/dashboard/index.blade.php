@extends('layouts.freelancer.app')

@section('title', translate('dashboard'))

@push('css_or_js')
    <style>
        .fl-dash-hero {
            background: linear-gradient(120deg, #17395e 0%, #1f4a78 60%, #f97316 160%);
            border-radius: 14px; padding: 28px 30px; margin-bottom: 20px; color: #fff;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;
        }
        .fl-dash-hero h2 { font-weight: 800; font-size: 24px; margin-bottom: 4px; color: #fff; }
        .fl-dash-hero p { color: rgba(255,255,255,.8); margin: 0; font-size: 14px; }
        .fl-dash-hero .btn-light { font-weight: 700; border-radius: 8px; }

        .fl-dash-stat-row { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 20px; }
        .fl-dash-stat-card {
            background: #fff; border: 1px solid #e8edf3; border-radius: 12px; padding: 18px 20px;
            display: flex; align-items: center; gap: 14px;
            transition: box-shadow .2s ease, transform .2s ease;
        }
        .fl-dash-stat-card:hover { box-shadow: 0 12px 26px rgba(23, 57, 94, 0.08); transform: translateY(-2px); }
        .fl-dash-stat-card.is-primary { background: linear-gradient(135deg, #17395e 0%, #1f4a78 100%); border: none; }
        .fl-dash-stat-card.is-primary .fl-dash-stat-label { color: rgba(255,255,255,.7); }
        .fl-dash-stat-card.is-primary .fl-dash-stat-value { color: #fff; }
        .fl-dash-stat-icon {
            width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0; background: #eef2ff; color: #3730a3;
        }
        .fl-dash-stat-card.is-primary .fl-dash-stat-icon { background: rgba(255,255,255,.15); color: #fff; }
        .fl-dash-stat-label { font-size: 12px; color: #9ca3af; text-transform: uppercase; letter-spacing: .03em; font-weight: 700; }
        .fl-dash-stat-value { font-weight: 800; font-size: 22px; color: #1f2937; margin-top: 2px; }

        .fl-dash-section { background: #fff; border: 1px solid #e8edf3; border-radius: 12px; padding: 20px; }
        .fl-dash-section-title { font-weight: 800; font-size: 16px; color: #17395e; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
        .fl-dash-section-title::before {
            content: ''; width: 5px; height: 16px; border-radius: 4px;
            background: linear-gradient(180deg, #f97316, #fb923c); display: inline-block;
        }
        .fl-dash-activity-row {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 0; border-bottom: 1px solid #f1f3f6; flex-wrap: wrap;
        }
        .fl-dash-activity-row:last-child { border-bottom: none; }
        .fl-dash-activity-title { font-weight: 700; font-size: 13px; color: #1f2937; }
        .fl-dash-activity-meta { font-size: 12px; color: #9ca3af; }
        .fl-dash-status-pill {
            font-size: 11px; font-weight: 700; text-transform: capitalize; border-radius: 999px; padding: 3px 12px;
        }
        .fl-dash-status-pill.status-active { background: #eef2ff; color: #3730a3; }
        .fl-dash-status-pill.status-completed { background: #e9f9f0; color: #219653; }
        .fl-dash-status-pill.status-cancelled { background: #fde8e8; color: #c0392b; }
        .fl-dash-empty { text-align: center; color: #9ca3af; padding: 24px 0; font-size: 13px; }

        @media (max-width: 991.98px) {
            .fl-dash-stat-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .fl-dash-stat-row { grid-template-columns: 1fr; }
            .fl-dash-hero { flex-direction: column; align-items: flex-start; }
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="fl-dash-hero">
            <div>
                <h2>{{ translate('welcome') }}, {{ $seller->f_name ?? $seller->name ?? translate('freelancer') }}</h2>
                <p>{{ translate('heres_whats_happening_with_your_freelance_business_today') }}</p>
            </div>
            <a href="{{ route('freelancer.services.create') }}" class="btn btn-light">
                <i class="tio-add"></i> {{ translate('add_new_service') }}
            </a>
        </div>

        @if($verification && in_array($verification->status, ['pending', 'resubmitted'], true))
            <div class="alert alert-warning">
                {{ translate('your_verification_is_under_review_please_wait_for_admin_approval') }}
            </div>
        @elseif(!$verification || $verification->status === 'rejected')
            <div class="alert alert-danger d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>
                    {{ $verification?->status === 'rejected'
                        ? translate('your_verification_was_rejected_please_resubmit_your_documents')
                        : translate('please_complete_your_verification_to_start_adding_services') }}
                </span>
                <a href="{{ route('freelancer.verification.form') }}" class="btn btn-sm btn--primary">
                    {{ translate('verify_now') }}
                </a>
            </div>
        @endif

        <div class="fl-dash-stat-row">
            <div class="fl-dash-stat-card is-primary">
                <div class="fl-dash-stat-icon"><i class="tio-wallet-outlined"></i></div>
                <div>
                    <div class="fl-dash-stat-label">{{ translate('total_earning') }}</div>
                    <div class="fl-dash-stat-value">{{ webCurrencyConverter($wallet->total_earning ?? 0) }}</div>
                </div>
            </div>
            <div class="fl-dash-stat-card">
                <div class="fl-dash-stat-icon"><i class="tio-briefcase"></i></div>
                <div>
                    <div class="fl-dash-stat-label">{{ translate('active_contracts') }}</div>
                    <div class="fl-dash-stat-value">{{ $activeContractsCount }}</div>
                </div>
            </div>
            <div class="fl-dash-stat-card">
                <div class="fl-dash-stat-icon"><i class="tio-file-text-outlined"></i></div>
                <div>
                    <div class="fl-dash-stat-label">{{ translate('pending_quotes') }}</div>
                    <div class="fl-dash-stat-value">{{ $pendingQuotesCount }}</div>
                </div>
            </div>
            <div class="fl-dash-stat-card">
                <div class="fl-dash-stat-icon"><i class="tio-checkmark-circle-outlined"></i></div>
                <div>
                    <div class="fl-dash-stat-label">{{ translate('active_services') }}</div>
                    <div class="fl-dash-stat-value">{{ $activeServices }} / {{ $totalServices }}</div>
                </div>
            </div>
            <div class="fl-dash-stat-card">
                <div class="fl-dash-stat-icon"><i class="tio-photo-gallery-outlined"></i></div>
                <div>
                    <div class="fl-dash-stat-label">{{ translate('portfolio_items') }}</div>
                    <div class="fl-dash-stat-value">{{ $totalPortfolioItems }}</div>
                </div>
            </div>
            <div class="fl-dash-stat-card">
                <div class="fl-dash-stat-icon"><i class="tio-star-outlined"></i></div>
                <div>
                    <div class="fl-dash-stat-label">{{ translate('rating') }}</div>
                    <div class="fl-dash-stat-value">
                        {{ (float)($seller->freelancer_rating_avg ?? 0) > 0 ? round($seller->freelancer_rating_avg, 1) : translate('n_a') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="fl-dash-section">
            <h3 class="fl-dash-section-title">{{ translate('recent_contracts') }}</h3>
            @forelse($recentContracts as $contract)
                <div class="fl-dash-activity-row">
                    <div>
                        <div class="fl-dash-activity-title">{{ $contract->service?->title ?? translate('service') }}</div>
                        <div class="fl-dash-activity-meta">
                            {{ trim(($contract->customer?->f_name ?? '') . ' ' . ($contract->customer?->l_name ?? '')) ?: translate('customer') }}
                            &middot; {{ $contract->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <span class="fl-dash-status-pill status-{{ $contract->status }}">{{ translate($contract->status) }}</span>
                </div>
            @empty
                <div class="fl-dash-empty">{{ translate('no_contracts_yet') }}</div>
            @endforelse
        </div>
    </div>
@endsection
