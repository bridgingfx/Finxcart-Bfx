@php
    $service = $service ?? null;
    $packagesByTier = $service ? $service->packages->keyBy('tier') : collect();
@endphp
<div class="card fsp-section-card mb-3">
    <div class="fsp-section-header">
        <i class="tio-pricetag-outlined"></i>
        <div>
            <h5>{{ translate('pricing_packages') }}</h5>
            <p>{{ translate('offer_basic_standard_and_premium_packages_customers_can_choose_from') }}</p>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach(\App\Services\FreelancerServiceService::PACKAGE_TIERS as $tier)
                @php($package = $packagesByTier->get($tier))
                @php($isEnabled = old("packages.$tier.is_enabled", $package?->is_enabled))
                <div class="col-md-4">
                    <div class="fsp-tier-card {{ $isEnabled ? 'is-enabled' : '' }}" data-tier-card>
                        <div class="fsp-tier-header">
                            <span class="fw-bold text-capitalize">{{ translate($tier) }}</span>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input fsp-tier-toggle" type="checkbox"
                                       name="packages[{{ $tier }}][is_enabled]" value="1"
                                       {{ $isEnabled ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="fsp-tier-body {{ $isEnabled ? '' : 'is-disabled' }}" data-tier-body>
                            <div>
                                <label class="form-label">{{ translate('package_title') }}</label>
                                <input class="form-control" name="packages[{{ $tier }}][title]" maxlength="100"
                                       value="{{ old("packages.$tier.title", $package?->title) }}" placeholder="{{ ucfirst($tier) }}">
                            </div>
                            <div>
                                <label class="form-label">{{ translate('whats_included') }}</label>
                                <textarea class="form-control" rows="3" maxlength="1000"
                                          name="packages[{{ $tier }}][description]">{{ old("packages.$tier.description", $package?->description) }}</textarea>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label">{{ translate('price') }}</label>
                                    <input class="form-control" type="number" step="0.01" min="0"
                                           name="packages[{{ $tier }}][price]" value="{{ old("packages.$tier.price", $package?->price) }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">{{ translate('revisions') }}</label>
                                    <input class="form-control" type="number" min="0" max="100"
                                           name="packages[{{ $tier }}][revisions]" value="{{ old("packages.$tier.revisions", $package?->revisions) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ translate('delivery_time_days') }}</label>
                                    <input class="form-control" type="number" min="1" max="365"
                                           name="packages[{{ $tier }}][delivery_time_days]" value="{{ old("packages.$tier.delivery_time_days", $package?->delivery_time_days) }}">
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label mb-0">{{ translate('features') }}</label>
                                    <button type="button" class="btn btn-link btn-sm p-0 add-feature-row" data-tier="{{ $tier }}">
                                        + {{ translate('add_feature') }}
                                    </button>
                                </div>
                                <div class="feature-list" data-feature-list="{{ $tier }}">
                                    @foreach(($package?->features ?? []) as $fIndex => $feature)
                                        <div class="d-flex align-items-center gap-2 mb-1 feature-row">
                                            <input type="checkbox" class="form-check-input flex-shrink-0" value="1"
                                                   name="packages[{{ $tier }}][features][{{ $fIndex }}][included]"
                                                   {{ !empty($feature['included']) ? 'checked' : '' }}>
                                            <input type="text" class="form-control form-control-sm" maxlength="100"
                                                   name="packages[{{ $tier }}][features][{{ $fIndex }}][label]"
                                                   value="{{ $feature['label'] ?? '' }}"
                                                   placeholder="{{ translate('e_g_responsive_design') }}">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-feature-row flex-shrink-0"><i class="tio-clear"></i></button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
