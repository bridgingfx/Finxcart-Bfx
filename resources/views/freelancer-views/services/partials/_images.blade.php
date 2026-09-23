@php
    $service = $service ?? null;
@endphp
<div class="card fsp-section-card mb-3">
    <div class="fsp-section-header justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="tio-photo-gallery-outlined"></i>
            <div>
                <h5>{{ translate('service_images') }}</h5>
                <p>{{ translate('add_one_or_more_images_that_showcase_this_service') }}</p>
            </div>
        </div>
        <button type="button" class="btn btn-light btn-sm fsp-add-new-btn" id="add-service-image-row">
            <i class="tio-add"></i> {{ translate('add_image') }}
        </button>
    </div>
    <div class="card-body">
        <div class="alert alert-danger d-none service-image-validation-message mb-3">{{ translate('at_least_one_service_image_is_required') }}</div>
        @error('images')
            <div class="alert alert-danger mb-3">{{ $message }}</div>
        @enderror
        <div id="service-image-wrapper">
        @if($service)
            @foreach($service->images as $image)
                <div class="row g-2 align-items-center service-image-row">
                    <div class="col-md-3">
                        <label class="service-image-drop">
                            <img alt="" class="service-image-preview" src="{{ getStorageImages(path: $image->image_full_url, type: 'backend-profile') }}" style="display:block">
                            <i class="tio-add-photo service-image-placeholder-icon" style="display:none"></i>
                            <input type="file" class="d-none service-image-input" name="images[{{ $loop->index }}][image]" accept=".webp,.jpg,.jpeg,.png">
                        </label>
                    </div>
                    <div class="col-md-8">
                        <span class="text-muted small">{{ translate('click_the_box_to_replace_this_image') }}</span>
                        <input type="hidden" name="images[{{ $loop->index }}][id]" value="{{ $image->id }}">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-service-image-row"><i class="tio-delete"></i></button>
                    </div>
                </div>
            @endforeach
        @endif
        </div>
    </div>
</div>

<template id="service-image-row-template">
    <div class="row g-2 align-items-center service-image-row">
        <div class="col-md-3">
            <label class="service-image-drop">
                <img alt="" class="service-image-preview">
                <i class="tio-add-photo service-image-placeholder-icon"></i>
                <input type="file" class="d-none service-image-input" name="images[__INDEX__][image]" accept=".webp,.jpg,.jpeg,.png">
            </label>
        </div>
        <div class="col-md-8">
            <span class="text-muted small">{{ translate('click_the_box_to_choose_an_image') }}</span>
            <input type="hidden" name="images[__INDEX__][id]" value="">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-outline-danger btn-sm remove-service-image-row"><i class="tio-delete"></i></button>
        </div>
    </div>
</template>

<template id="feature-row-template">
    <div class="d-flex align-items-center gap-2 mb-1 feature-row">
        <input type="checkbox" class="form-check-input flex-shrink-0" value="1" checked name="packages[__TIER__][features][__INDEX__][included]">
        <input type="text" class="form-control form-control-sm" maxlength="100" name="packages[__TIER__][features][__INDEX__][label]" placeholder="{{ translate('e_g_responsive_design') }}">
        <button type="button" class="btn btn-sm btn-outline-danger remove-feature-row flex-shrink-0"><i class="tio-clear"></i></button>
    </div>
</template>
