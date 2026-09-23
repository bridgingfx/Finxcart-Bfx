@extends('layouts.admin.app')

@section('title', translate('edit_service'))

@push('css_or_js')
    <style>
        .fsp-tier-card { border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; height: 100%; transition: border-color .2s ease, box-shadow .2s ease; }
        .fsp-tier-card.is-enabled { border-color: #1a2f5e; box-shadow: 0 6px 18px rgba(26,47,94,.08); }
        .fsp-tier-header { padding: 14px 16px; border-bottom: 1px solid #eef1f5; display: flex; justify-content: space-between; align-items: center; }
        .fsp-tier-body { padding: 16px; display: flex; flex-direction: column; gap: 12px; }
        .fsp-tier-body.is-disabled { opacity: .45; pointer-events: none; }
        .service-image-row { border: 1px solid #e7ebf0; border-radius: 10px; padding: 12px; margin-bottom: 10px; }
        .service-image-row .service-image-drop {
            border: 2px dashed #d7dde5; border-radius: 8px; height: 90px; width: 100%;
            display: flex; align-items: center; justify-content: center; cursor: pointer;
            background: #fafbfc; overflow: hidden;
        }
        .service-image-row .service-image-drop img { max-width: 100%; max-height: 100%; object-fit: cover; display: none; }
        .service-image-row .service-image-drop i { font-size: 22px; color: #9aa5b1; }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0">{{ translate('edit_service') }}</h2>
        </div>

        <form id="freelancer-service-form" action="{{ route('admin.freelancer.services.update', [$service->id]) }}" method="post" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ translate('freelancer_category') }} *</label>
                            <select class="form-control" id="freelancer-category-select" name="freelancer_category_id" required>
                                <option value="">{{ translate('select_freelancer_category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (int)old('freelancer_category_id', $service->freelancer_category_id) === (int)$category->id ? 'selected' : '' }}>
                                        {{ $category->defaultname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ translate('freelancer_specialization') }} *</label>
                            <select class="form-control" id="freelancer-specialization-select" name="freelancer_specialization_id" required>
                                <option value="">{{ translate('select_freelancer_specialization') }}</option>
                                @foreach($specializations as $specialization)
                                    <option value="{{ $specialization->id }}" data-category="{{ $specialization->freelancer_category_id }}" {{ (int)old('freelancer_specialization_id', $service->freelancer_specialization_id) === (int)$specialization->id ? 'selected' : '' }}>
                                        {{ $specialization->defaultname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ translate('status') }}</label>
                            <select class="form-control" name="is_active">
                                <option value="1" {{ old('is_active', (int)$service->is_active) == 1 ? 'selected' : '' }}>{{ translate('active') }}</option>
                                <option value="0" {{ old('is_active', (int)$service->is_active) == 0 ? 'selected' : '' }}>{{ translate('inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ translate('title') }} *</label>
                            <input class="form-control" name="title" maxlength="150" value="{{ old('title', $service->title) }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ translate('service') }}</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description', $service->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ translate('service_images') }}</h5>
                        <p class="text-muted small mb-0">{{ translate('add_one_or_more_images_that_showcase_this_service') }}</p>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-service-image-row">
                        <i class="fi fi-rr-plus"></i> {{ translate('add_image') }}
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger d-none service-image-validation-message mb-3">{{ translate('at_least_one_service_image_is_required') }}</div>
                    @error('images')
                        <div class="alert alert-danger mb-3">{{ $message }}</div>
                    @enderror
                    <div id="service-image-wrapper">
                    @foreach($service->images as $image)
                        <div class="row g-2 align-items-center service-image-row">
                            <div class="col-md-3">
                                <label class="service-image-drop">
                                    <img alt="" class="service-image-preview" src="{{ getStorageImages(path: $image->image_full_url, type: 'backend-profile') }}" style="display:block">
                                    <i class="fi fi-rr-picture service-image-placeholder-icon" style="display:none"></i>
                                    <input type="file" class="d-none service-image-input" name="images[{{ $loop->index }}][image]" accept=".webp,.jpg,.jpeg,.png">
                                </label>
                            </div>
                            <div class="col-md-8">
                                <span class="text-muted small">{{ translate('click_the_box_to_replace_this_image') }}</span>
                                <input type="hidden" name="images[{{ $loop->index }}][id]" value="{{ $image->id }}">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-service-image-row"><i class="fi fi-rr-trash"></i></button>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>

            @php($packagesByTier = $service->packages->keyBy('tier'))
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ translate('pricing_packages') }}</h5>
                    <div class="text-muted fs-13">{{ translate('offer_basic_standard_and_premium_packages_customers_can_choose_from') }}</div>
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
                                                   value="{{ old("packages.$tier.title", $package?->title) }}"
                                                   placeholder="{{ ucfirst($tier) }}">
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
                                                       name="packages[{{ $tier }}][price]"
                                                       value="{{ old("packages.$tier.price", $package?->price) }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">{{ translate('revisions') }}</label>
                                                <input class="form-control" type="number" min="0" max="100"
                                                       name="packages[{{ $tier }}][revisions]"
                                                       value="{{ old("packages.$tier.revisions", $package?->revisions) }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">{{ translate('delivery_time_days') }}</label>
                                                <input class="form-control" type="number" min="1" max="365"
                                                       name="packages[{{ $tier }}][delivery_time_days]"
                                                       value="{{ old("packages.$tier.delivery_time_days", $package?->delivery_time_days) }}">
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
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-feature-row flex-shrink-0"><i class="fi fi-rr-cross-small"></i></button>
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

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('admin.freelancer.services.index') }}" class="btn btn-secondary">{{ translate('cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ translate('save') }}</button>
            </div>
        </form>
    </div>

    <template id="service-image-row-template">
        <div class="row g-2 align-items-center service-image-row">
            <div class="col-md-3">
                <label class="service-image-drop">
                    <img alt="" class="service-image-preview">
                    <i class="fi fi-rr-picture service-image-placeholder-icon"></i>
                    <input type="file" class="d-none service-image-input" name="images[__INDEX__][image]" accept=".webp,.jpg,.jpeg,.png">
                </label>
            </div>
            <div class="col-md-8">
                <span class="text-muted small">{{ translate('click_the_box_to_choose_an_image') }}</span>
                <input type="hidden" name="images[__INDEX__][id]" value="">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger btn-sm remove-service-image-row"><i class="fi fi-rr-trash"></i></button>
            </div>
        </div>
    </template>

    <template id="feature-row-template">
        <div class="d-flex align-items-center gap-2 mb-1 feature-row">
            <input type="checkbox" class="form-check-input flex-shrink-0" value="1" checked name="packages[__TIER__][features][__INDEX__][included]">
            <input type="text" class="form-control form-control-sm" maxlength="100" name="packages[__TIER__][features][__INDEX__][label]" placeholder="{{ translate('e_g_responsive_design') }}">
            <button type="button" class="btn btn-sm btn-outline-danger remove-feature-row flex-shrink-0"><i class="fi fi-rr-cross-small"></i></button>
        </div>
    </template>
@endsection

@push('script')
    <script>
        (function () {
            const category = document.getElementById('freelancer-category-select');
            const specialization = document.getElementById('freelancer-specialization-select');
            function filterSpecializations() {
                const selectedCategory = category.value;
                Array.from(specialization.options).forEach(option => {
                    if (!option.value) return;
                    option.hidden = option.dataset.category !== selectedCategory;
                    if (option.hidden && option.selected) specialization.value = '';
                });
            }
            category?.addEventListener('change', filterSpecializations);
            filterSpecializations();

            document.querySelectorAll('[data-tier-card]').forEach(function (card) {
                const toggle = card.querySelector('.fsp-tier-toggle');
                const body = card.querySelector('[data-tier-body]');
                toggle?.addEventListener('change', function () {
                    card.classList.toggle('is-enabled', toggle.checked);
                    body?.classList.toggle('is-disabled', !toggle.checked);
                });
            });

            const imageWrapper = document.getElementById('service-image-wrapper');
            const imageTemplate = document.getElementById('service-image-row-template');
            let imageIndex = {{ $service->images->count() }};

            function bindImageRow(row) {
                const input = row.querySelector('.service-image-input');
                const preview = row.querySelector('.service-image-preview');
                const icon = row.querySelector('.service-image-placeholder-icon');
                input.addEventListener('change', function () {
                    const file = this.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        preview.src = event.target.result;
                        preview.style.display = 'block';
                        icon.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                });
            }

            function addImageRow() {
                const html = imageTemplate.innerHTML.replaceAll('__INDEX__', imageIndex);
                const holder = document.createElement('div');
                holder.innerHTML = html.trim();
                const row = holder.firstElementChild;
                imageWrapper.appendChild(row);
                bindImageRow(row);
                imageIndex++;
            }

            document.getElementById('add-service-image-row').addEventListener('click', addImageRow);

            imageWrapper.addEventListener('click', function (event) {
                const removeBtn = event.target.closest('.remove-service-image-row');
                if (!removeBtn) return;
                removeBtn.closest('.service-image-row').remove();
            });

            const serviceForm = document.getElementById('freelancer-service-form');
            const imageError = document.querySelector('.service-image-validation-message');

            function hasServiceImage() {
                return Array.from(imageWrapper.querySelectorAll('.service-image-row')).some(function (row) {
                    const idInput = row.querySelector('input[type="hidden"][name*="[id]"]');
                    const fileInput = row.querySelector('.service-image-input');
                    return Boolean(idInput?.value) || Boolean(fileInput?.files?.length);
                });
            }

            serviceForm?.addEventListener('submit', function (event) {
                if (hasServiceImage()) {
                    imageError?.classList.add('d-none');
                    return;
                }

                event.preventDefault();
                imageError?.classList.remove('d-none');
                imageWrapper.closest('.card')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
            const featureTemplate = document.getElementById('feature-row-template');
            const featureIndexes = {};
            document.querySelectorAll('[data-feature-list]').forEach(function (list) {
                featureIndexes[list.dataset.featureList] = list.querySelectorAll('.feature-row').length;
            });

            document.querySelectorAll('.add-feature-row').forEach(function (button) {
                button.addEventListener('click', function () {
                    const tier = button.dataset.tier;
                    const list = document.querySelector('[data-feature-list="' + tier + '"]');
                    const index = featureIndexes[tier] || 0;
                    const html = featureTemplate.innerHTML.replaceAll('__TIER__', tier).replaceAll('__INDEX__', index);
                    const holder = document.createElement('div');
                    holder.innerHTML = html.trim();
                    list.appendChild(holder.firstElementChild);
                    featureIndexes[tier] = index + 1;
                });
            });

            document.querySelectorAll('[data-feature-list]').forEach(function (list) {
                list.addEventListener('click', function (event) {
                    const removeBtn = event.target.closest('.remove-feature-row');
                    if (!removeBtn) return;
                    removeBtn.closest('.feature-row').remove();
                });
            });
        })();
    </script>
@endpush
