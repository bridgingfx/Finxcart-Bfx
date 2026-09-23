@extends('layouts.vendor.app')

@section('title', translate('My_Tier_Plan'))

@push('css_or_js')
<style>
    .tier-hub { max-width: 1220px; margin: 0 auto; padding-bottom: 40px; }
    .tier-hub-hero { padding: 25px; margin-bottom: 24px; border-radius: 22px; color: #fff; background: linear-gradient(135deg, #073b74, #1684cf); box-shadow: 0 18px 42px rgba(7,59,116,.2); }
    .tier-hub-hero-icon { width: 52px; height: 52px; flex: 0 0 52px; display: inline-flex; align-items: center; justify-content: center; border-radius: 16px; background: rgba(255,255,255,.16); font-size: 24px; }
    .tier-stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-top: 20px; }
    .tier-stat { padding: 14px 16px; border: 1px solid rgba(255,255,255,.2); border-radius: 14px; background: rgba(255,255,255,.1); }
    .tier-stat small, .tier-stat strong { display: block; }
    .tier-stat small { color: rgba(255,255,255,.7); font-size: 11px; text-transform: uppercase; letter-spacing: .4px; }
    .tier-stat strong { margin-top: 3px; color: #fff; font-size: 20px; }
    .tier-section-card { overflow: hidden; border: 1px solid #e2eaf3; border-radius: 20px; box-shadow: 0 12px 34px rgba(20,49,78,.08); }
    .tier-section-card > .card-header { padding: 20px 22px; border-bottom: 1px solid #e8eef5; background: #fff; }
    .subscription-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; padding: 22px; background: #f8fbfe; }
    .subscription-card { position: relative; overflow: hidden; border: 1px solid #dfe8f1; border-radius: 18px; background: #fff; box-shadow: 0 8px 24px rgba(20,49,78,.06); }
    .subscription-card.is-ended { border-color: #f1c9cf; }
    .subscription-head { padding: 19px 20px; color: #fff; background: linear-gradient(135deg, #073b74, #1684cf); }
    .subscription-card.is-ended .subscription-head { background: linear-gradient(135deg, #475569, #64748b); }
    .subscription-price { font-size: 27px; font-weight: 800; }
    .subscription-body { padding: 20px; }
    .subscription-status { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 999px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .35px; }
    .status-trial { color: #9a5700; background: #fff3d6; }
    .status-active { color: #087443; background: #dff7eb; }
    .status-ended { color: #b4233b; background: #fde8ec; }
    .status-cancelled { color: #64748b; background: #edf1f5; }
    .subscription-progress { height: 7px; overflow: hidden; margin: 15px 0 6px; border-radius: 999px; background: #e8eef5; }
    .subscription-progress span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #073b74, #1684cf); }
    .subscription-card.is-ended .subscription-progress span { background: #d94b64; }
    .subscription-details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin: 17px 0; }
    .subscription-detail { padding: 11px; border: 1px solid #e7edf4; border-radius: 12px; background: #f9fbfd; }
    .subscription-detail small, .subscription-detail strong { display: block; }
    .subscription-detail small { margin-bottom: 3px; color: #8391a2; font-size: 10px; text-transform: uppercase; letter-spacing: .3px; }
    .subscription-detail strong { color: #172b43; font-size: 12px; }
    .subscription-actions { display: flex; flex-wrap: wrap; gap: 9px; }
    .subscription-actions .btn { min-height: 39px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; font-weight: 700; }
    .payment-table thead th { color: #627387; font-size: 10px; text-transform: uppercase; letter-spacing: .35px; }
    .payment-table td { vertical-align: middle; }
    .payment-method-icon { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 11px; color: #1684cf; background: #edf7ff; }
    .transaction-id { max-width: 190px; display: block; overflow: hidden; color: #52677d; font-family: monospace; font-size: 11px; text-overflow: ellipsis; white-space: nowrap; }
    .empty-tier-state { padding: 50px 20px; text-align: center; background: #fff; }
    @media(max-width:991px) { .subscription-grid { grid-template-columns: 1fr; } }
    @media(max-width:575px) { .tier-stat-grid { grid-template-columns: 1fr; } .subscription-details { grid-template-columns: 1fr; } .subscription-grid { padding: 13px; } }
</style>
@endpush

@section('content')
@php
    $now = now();
    $activeCount = 0;
    $endedCount = 0;
    $totalProducts = $subscriptions->sum('products_count');
    foreach ($subscriptions as $plan) {
        $planExpiry = $plan->status === 'trial' && $plan->trial_end_date ? $plan->trial_end_date->copy()->endOfDay() : $plan->end_date?->copy()->endOfDay();
        $isValid = in_array($plan->status, ['active', 'trial']) && $planExpiry && $planExpiry->isFuture();
        $isValid ? $activeCount++ : $endedCount++;
    }
@endphp
<div class="content container-fluid">
    <div class="tier-hub">
        <div class="tier-hub-hero">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="tier-hub-hero-icon"><i class="tio-layers-outlined"></i></span>
                    <div>
                        <h2 class="text-white mb-1">{{ translate('My_Tier_Plans') }}</h2>
                        <p class="mb-0 opacity-75">{{ translate('Manage_subscriptions,_usage,_renewals_and_payment_records.') }}</p>
                    </div>
                </div>
                <a href="{{ route('vendor.tier.index') }}" class="btn btn-light font-weight-bold"><i class="tio-add mr-1"></i>{{ translate('Add_plan') }}</a>
            </div>
            <div class="tier-stat-grid">
                <div class="tier-stat"><small>{{ translate('Active_plans') }}</small><strong>{{ $activeCount }}</strong></div>
                <div class="tier-stat"><small>{{ translate('Ended_plans') }}</small><strong>{{ $endedCount }}</strong></div>
                <div class="tier-stat"><small>{{ translate('Assigned_products') }}</small><strong>{{ $totalProducts }}</strong></div>
            </div>
        </div>

        <div class="card tier-section-card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="mb-1"><i class="tio-layers-outlined text-primary mr-2"></i>{{ translate('Subscription_overview') }}</h4>
                    <p class="text-muted small mb-0">{{ translate('Active,_trial,_cancelled_and_ended_plans_remain_visible_here.') }}</p>
                </div>
            </div>
            @if($subscriptions->count())
                <div class="subscription-grid">
                    @foreach($subscriptions as $subscription)
                        @php
                            $isTrial = $subscription->status === 'trial';
                            $effectiveDate = $isTrial && $subscription->trial_end_date ? $subscription->trial_end_date->copy()->endOfDay() : $subscription->end_date?->copy()->endOfDay();
                            $isValid = in_array($subscription->status, ['active', 'trial']) && $effectiveDate && $effectiveDate->isFuture();
                            $daysLeft = $effectiveDate ? max(0, (int) ceil(now()->diffInDays($effectiveDate, false))) : 0;
                            $totalDays = $subscription->start_date && $effectiveDate ? max(1, $subscription->start_date->diffInDays($effectiveDate)) : 1;
                            $elapsedDays = $subscription->start_date ? min($totalDays, $subscription->start_date->diffInDays(now())) : 0;
                            $progress = $isValid ? min(100, max(2, ($elapsedDays / $totalDays) * 100)) : 100;
                            $displayStatus = $isValid ? ($isTrial ? 'trial' : 'active') : (in_array($subscription->status, ['cancelled', 'suspended']) ? $subscription->status : 'ended');
                            $statusClass = match($displayStatus) { 'trial' => 'status-trial', 'active' => 'status-active', 'ended' => 'status-ended', default => 'status-cancelled' };
                            $commission = $isTrial ? ($subscription->trial_commission_rate ?? 15) : $subscription->sales_commission_rate;
                        @endphp
                        <article class="subscription-card {{ $isValid ? '' : 'is-ended' }}">
                            <div class="subscription-head d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <span class="subscription-status {{ $statusClass }}"><i class="{{ $isValid ? 'tio-checkmark-circle' : 'tio-warning' }}"></i>{{ translate(ucfirst($displayStatus)) }}</span>
                                    <h3 class="text-white mt-2 mb-0">{{ $subscription->tier->name ?? translate('Unknown_Tier') }}</h3>
                                </div>
                                <div class="text-right"><span class="subscription-price">${{ number_format($subscription->tier->monthly_fee_usd ?? $subscription->monthly_fee_usd, 0) }}</span><small class="d-block opacity-75">/{{ translate('month') }}</small></div>
                            </div>
                            <div class="subscription-body">
                                <div class="d-flex justify-content-between small">
                                    <strong>{{ $isValid ? $daysLeft.' '.translate('days_remaining') : translate('Plan_ended') }}</strong>
                                    <span class="text-muted">{{ $effectiveDate?->format('d M, Y') ?? translate('Not_available') }}</span>
                                </div>
                                <div class="subscription-progress"><span style="width:{{ $progress }}%"></span></div>
                                <div class="subscription-details">
                                    <div class="subscription-detail"><small>{{ translate('Started') }}</small><strong>{{ $subscription->start_date?->format('d M, Y') ?? '-' }}</strong></div>
                                    <div class="subscription-detail"><small>{{ $isTrial ? translate('Trial_ends') : translate('Expires') }}</small><strong>{{ $effectiveDate?->format('d M, Y') ?? '-' }}</strong></div>
                                    <div class="subscription-detail"><small>{{ translate('Products_used') }}</small><strong>{{ $subscription->products_count }} / {{ $subscription->tier->listings_per_fee ?? translate('Unlimited') }}</strong></div>
                                    <div class="subscription-detail"><small>{{ translate('Commission') }}</small><strong>{{ number_format((float)$commission, 2) }}%</strong></div>
                                    <div class="subscription-detail"><small>{{ translate('Subscription_ID') }}</small><strong>#{{ $subscription->id }}</strong></div>
                                    <div class="subscription-detail"><small>{{ translate('Renewal_mode') }}</small><strong>{{ translate('Manual_payment_required') }}</strong></div>
                                </div>
                                <div class="subscription-actions">
                                    @if(!$isValid || $daysLeft <= 7)
                                        <form action="{{ route('vendor.tier.renew') }}" method="POST" novalidate>
                                            @csrf
                                            <input type="hidden" name="tier_id" value="{{ $subscription->product_tier_id }}">
                                            <input type="hidden" name="vendor_tier_record_id" value="{{ $subscription->id }}">
                                            <button class="btn btn--primary"><i class="tio-refresh mr-1"></i>{{ $isValid ? translate('Renew_now') : translate('Pay_and_reactivate') }}</button>
                                        </form>
                                    @endif
                                    @php $currentTierFee = (float)($subscription->tier->monthly_fee_usd ?? 0); @endphp
                                    @if($currentTierFee >= ($maxTierFee ?? 0) && $currentTierFee > 0)
                                        <span class="btn btn-outline-secondary" style="opacity:.5;cursor:not-allowed;" title="{{ translate('You_are_already_on_the_highest_plan') }}">
                                            <i class="tio-trending-up mr-1"></i>{{ translate('Upgrade_plan') }}
                                        </span>
                                    @else
                                        <a href="{{ route('vendor.tier.index', ['upgrade_from' => $currentTierFee]) }}" class="btn btn-outline-primary">
                                            <i class="tio-trending-up mr-1"></i>{{ translate('Upgrade_plan') }}
                                        </a>
                                    @endif
                                    @if($isValid)
                                        <form action="{{ route('vendor.tier.cancel') }}" method="POST" onsubmit="return confirm('{{ translate('Are_you_sure_you_want_to_cancel_this_plan?_All_associated_products_will_be_disabled.') }}');" novalidate>
                                            @csrf
                                            <input type="hidden" name="vendor_tier_id" value="{{ $subscription->id }}">
                                            <button class="btn btn-outline-danger"><i class="tio-clear mr-1"></i>{{ translate('Cancel') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="empty-tier-state">
                    <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/empty-state-icon.svg') }}" alt="" width="100" class="mb-3">
                    <h4>{{ translate('No_subscription_history_found') }}</h4>
                    <a href="{{ route('vendor.tier.index') }}" class="btn btn--primary mt-2">{{ translate('Choose_a_plan') }}</a>
                </div>
            @endif
        </div>

        <div class="card tier-section-card">
            <div class="card-header">
                <h4 class="mb-1"><i class="tio-receipt-outlined text-primary mr-2"></i>{{ translate('Payment_History') }}</h4>
                <p class="text-muted small mb-0">{{ translate('Trial_activations_and_paid_transactions_are_recorded_for_audit.') }}</p>
            </div>
            @if($paymentHistory->count())
                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap card-table payment-table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('Plan') }}</th>
                                <th>{{ translate('Type_/_status') }}</th>
                                <th>{{ translate('Billing_period') }}</th>
                                <th>{{ translate('Amount') }}</th>
                                <th>{{ translate('Method') }}</th>
                                <th>{{ translate('Transaction_ID') }}</th>
                                <th>{{ translate('Subscription_Status') }}</th>
                                <th>{{ translate('Processed_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($paymentHistory as $history)
                            @php
                                $isFreeTrial = $history->payment_method === 'free_trial_access' || $history->payment_status === 'free';
                                $paymentState = $isFreeTrial ? translate('Free_trial') : ($history->payment_status ?: $history->status);
                                $paymentBadge = $isFreeTrial ? 'badge-soft-info' : ($history->status === 'completed' ? 'badge-soft-success' : 'badge-soft-danger');
                                $subStatus = $history->vendorTier?->status ?? 'unknown';
                                $subBadge = match($subStatus) { 'active' => 'badge-soft-success', 'trial' => 'badge-soft-info', 'cancelled' => 'badge-soft-danger', 'expired' => 'badge-soft-warning', default => 'badge-soft-secondary' };
                            @endphp
                            <tr>
                                <td><strong>{{ $history->vendorTier?->tier?->name ?? translate('Unknown_plan') }}</strong><small class="d-block text-muted">#{{ $history->vendor_tier_id }}</small></td>
                                <td><span class="badge {{ $paymentBadge }}">{{ ucfirst(str_replace('_', ' ', $paymentState)) }}</span><small class="d-block text-muted mt-1">{{ ucfirst($history->status) }}</small></td>
                                <td><strong>{{ $history->billing_start_date?->format('d M Y') ?? '-' }}</strong><small class="d-block text-muted">{{ translate('to') }} {{ $history->billing_end_date?->format('d M Y') ?? '-' }}</small></td>
                                <td><strong>{{ $history->currency ?? 'USD' }} {{ number_format((float)$history->amount_paid, 2) }}</strong></td>
                                <td><div class="d-flex align-items-center gap-2"><span class="payment-method-icon"><i class="tio-credit-card"></i></span><span class="text-capitalize">{{ str_replace('_', ' ', $history->payment_method ?: translate('Not_available')) }}</span></div></td>
                                <td><span class="transaction-id" title="{{ $history->transaction_id }}">{{ $history->transaction_id ?: '-' }}</span></td>
                                <td><span class="badge {{ $subBadge }}">{{ ucfirst($subStatus) }}</span></td>
                                <td>{{ $history->paid_at?->format('d M Y, h:i A') ?? $history->created_at?->format('d M Y, h:i A') ?? '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 d-flex justify-content-end">{{ $paymentHistory->links() }}</div>
            @else
                <div class="empty-tier-state"><p class="mb-0">{{ translate('No_payment_history_found.') }}</p></div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('script')
@if(session('clear_pending_product_stage'))
<script>document.addEventListener('DOMContentLoaded', () => sessionStorage.removeItem('pending_product_stage'));</script>
@endif
@endpush
