@extends('layouts.admin.app')

@section('title', translate('add_service'))

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
            <h2 class="h1 mb-0">{{ translate('add_service') }}</h2>
        </div>

        <form id="freelancer-service-form" action="{{ route('admin.freelancer.services.store') }}" method="post" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">{{ translate('freelancer') }} *</label>
                            <select class="form-control" id="freelancer-seller-select" name="seller_id" required>
                                <option value="">{{ translate('select_freelancer') }}</option>
                                @foreach($freelancers as $freelancer)
                                    <option value="{{ $freelancer->id }}" {{ (int)old('seller_id') === (int)$freelancer->id ? 'selected' : '' }}>
                                        {{ $freelancer->shop?->name ?: trim($freelancer->f_name . ' ' . $freelancer->l_name) }} ({{ $freelancer->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('freelancer_category') }} *</label>
                            <select class="form-control" id="freelancer-category-select" name="freelancer_category_id" required disabled>
                                <option value="">{{ translate('select_freelancer_first') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('freelancer_specialization') }} *</label>
                            <select class="form-control" id="freelancer-specialization-select" name="freelancer_specialization_id" required disabled>
                                <option value="">{{ translate('select_freelancer_first') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ translate('title') }} *</label>
                            <input class="form-control" name="title" maxlength="150" value="{{ old('title') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">{{ translate('service') }}</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
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
                    <div id="service-image-wrapper"></div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ translate('pricing_packages') }}</h5>
                    <div class="text-muted fs-13">{{ translate('offer_basic_standard_and_premium_packages_customers_can_choose_from') }}</div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach(\App\Services\FreelancerServiceService::PACKAGE_TIERS as $tier)
                            <div class="col-md-4">
                                <div class="fsp-tier-card" data-tier-card>
                                    <div class="fsp-tier-header">
                                        <span class="fw-bold text-capitalize">{{ translate($tier) }}</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input fsp-tier-toggle" type="checkbox"
                                                   name="packages[{{ $tier }}][is_enabled]" value="1"
                                                   {{ old("packages.$tier.is_enabled") ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="fsp-tier-body {{ old("packages.$tier.is_enabled") ? '' : 'is-disabled' }}" data-tier-body>
                                        <div>
                                            <label class="form-label">{{ translate('package_title') }}</label>
                                            <input class="form-control" name="packages[{{ $tier }}][title]" maxlength="100"
                                                   value="{{ old("packages.$tier.title") }}" placeholder="{{ ucfirst($tier) }}">
                                        </div>
                                        <div>
                                            <label class="form-label">{{ translate('whats_included') }}</label>
                                            <textarea class="form-control" rows="3" maxlength="1000"
                                                      name="packages[{{ $tier }}][description]">{{ old("packages.$tier.description") }}</textarea>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label">{{ translate('price') }}</label>
                                                <input class="form-control" type="number" step="0.01" min="0"
                                                       name="packages[{{ $tier }}][price]" value="{{ old("packages.$tier.price") }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">{{ translate('revisions') }}</label>
                                                <input class="form-control" type="number" min="0" max="100"
                                                       name="packages[{{ $tier }}][revisions]" value="{{ old("packages.$tier.revisions") }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">{{ translate('delivery_time_days') }}</label>
                                                <input class="form-control" type="number" min="1" max="365"
                                                       name="packages[{{ $tier }}][delivery_time_days]" value="{{ old("packages.$tier.delivery_time_days") }}">
                                            </div>
                                        </div>
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label mb-0">{{ translate('features') }}</label>
                                                <button type="button" class="btn btn-link btn-sm p-0 add-feature-row" data-tier="{{ $tier }}">
                                                    + {{ translate('add_feature') }}
                                                </button>
                                            </div>
                                            <div class="feature-list" data-feature-list="{{ $tier }}"></div>
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
            const sellerSelect = document.getElementById('freelancer-seller-select');
            const categorySelect = document.getElementById('freelancer-category-select');
            const specializationSelect = document.getElementById('freelancer-specialization-select');
            let allSpecializations = [];

            function renderSpecializations(categoryId) {
                specializationSelect.innerHTML = '<option value="">{{ translate('select_freelancer_specialization') }}</option>';
                allSpecializations
                    .filter(function (specialization) { return String(specialization.category_id) === String(categoryId); })
                    .forEach(function (specialization) {
                        const option = document.createElement('option');
                        option.value = specialization.id;
                        option.textContent = specialization.name;
                        specializationSelect.appendChild(option);
                    });
            }

            sellerSelect.addEventListener('change', function () {
                const sellerId = this.value;
                categorySelect.innerHTML = '<option value="">{{ translate('select_freelancer_category') }}</option>';
                specializationSelect.innerHTML = '<option value="">{{ translate('select_freelancer_first') }}</option>';
                allSpecializations = [];

                if (!sellerId) {
                    categorySelect.disabled = true;
                    specializationSelect.disabled = true;
                    return;
                }

                fetch('{{ url('admin/freelancer/services/seller-options') }}/' + sellerId, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        (data.categories || []).forEach(function (category) {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.name;
                            categorySelect.appendChild(option);
                        });
                        allSpecializations = data.specializations || [];
                        categorySelect.disabled = false;
                        specializationSelect.disabled = false;
                    })
                    .catch(function () {
                        categorySelect.disabled = true;
                        specializationSelect.disabled = true;
                    });
            });

            categorySelect.addEventListener('change', function () {
                renderSpecializations(this.value);
            });

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
            let imageIndex = 0;

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

            addImageRow();

            const serviceForm = document.getElementById('freelancer-service-form');
            const imageError = document.querySelector('.service-image-validation-message');

            function hasServiceImage() {
                return Array.from(imageWrapper.querySelectorAll('.service-image-input')).some(function (input) {
                    return Boolean(input.files?.length);
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
