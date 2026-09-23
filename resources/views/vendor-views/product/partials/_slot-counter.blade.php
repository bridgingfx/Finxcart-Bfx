@php
    $totalSlots   = $totalSlots ?? 0;
    $usedSlots    = $usedSlots  ?? 0;
    $tierName     = $tierName   ?? '';
    $isFull       = $totalSlots > 0 && $usedSlots >= $totalSlots;
@endphp

@if(isset($tierExpired) && $tierExpired)
    @if(isset($hasEverHadTier) && $hasEverHadTier)
        <div class="alert alert-danger d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <span>{{ translate('Your_subscription_has_expired._You_cannot_add_new_products_until_you_renew.') }}</span>
            <a href="{{ route('vendor.tier.index') }}" class="btn btn-sm btn-danger">{{ translate('Renew_Now') }}</a>
        </div>
    @else
        <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <span>{{ translate("You_don't_have_a_tier_plan_yet._Purchase_a_plan_to_start_listing_products.") }}</span>
            <a href="{{ route('vendor.tier.index') }}" class="btn btn-sm btn-warning">{{ translate('Buy_Plan') }}</a>
        </div>
    @endif
@elseif($totalSlots > 0)
    <div class="card mb-3 border-{{ $isFull ? 'danger' : 'primary' }}">
        <div class="card-body py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <strong>{{ translate('listing_slots') }}</strong>
                <span class="text-muted ms-2 small">{{ $tierName }}</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span>
                    <strong class="text-{{ $isFull ? 'danger' : 'primary' }}">{{ $usedSlots }} {{ translate('used') }}</strong>
                    / {{ $totalSlots }} {{ translate('total') }}
                </span>
                <div class="progress d-none d-sm-flex" style="width:100px;height:8px;">
                    <div class="progress-bar bg-{{ $isFull ? 'danger' : 'primary' }}"
                         style="width:{{ min(($usedSlots / $totalSlots) * 100, 100) }}%"></div>
                </div>
                <span class="text-muted small">{{ max($totalSlots - $usedSlots, 0) }} {{ translate('remaining') }}</span>
            </div>
        </div>
    </div>

    @if($isFull)
        <div class="alert alert-danger d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <span>{{ translate('youve_used_all') }} {{ $totalSlots }} {{ translate('listing_slots_upgrade_to_add_more') }}</span>
            <a href="{{ route('vendor.tier.index') }}" class="btn btn-sm btn-danger">{{ translate('upgrade_plan') }}</a>
        </div>
    @endif
@endif
