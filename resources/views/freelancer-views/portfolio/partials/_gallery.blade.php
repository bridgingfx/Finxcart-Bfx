@php
    $item = $item ?? null;
@endphp
<div class="card portfolio-form-card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0">{{ translate('additional_project_images') }}</h4>
            <p class="text-muted small mb-0">{{ translate('add_more_screenshots_each_with_its_own_optional_link') }}</p>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="add-gallery-row">
            <i class="tio-add"></i> {{ translate('add_image') }}
        </button>
    </div>
    <div class="card-body" id="gallery-wrapper">
        @if($item)
            @foreach($item->galleryItems as $galleryItem)
                <div class="row g-2 align-items-center gallery-row">
                    <div class="col-md-3">
                        <label class="gallery-thumb-drop">
                            <img alt="" class="gallery-preview" src="{{ getStorageImages(path: $galleryItem->image_full_url, type: 'backend-profile') }}" style="display:block">
                            <i class="tio-add-photo gallery-placeholder-icon" style="display:none"></i>
                            <input type="file" class="d-none gallery-image-input" name="gallery[{{ $loop->index }}][image]" accept=".webp,.jpg,.jpeg,.png">
                        </label>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label mb-1">{{ translate('project_url') }}</label>
                        <input type="url" class="form-control" name="gallery[{{ $loop->index }}][url]" value="{{ $galleryItem->url }}" placeholder="https://">
                        <input type="hidden" name="gallery[{{ $loop->index }}][id]" value="{{ $galleryItem->id }}">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-gallery-row"><i class="tio-delete"></i></button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<template id="gallery-row-template">
    <div class="row g-2 align-items-center gallery-row">
        <div class="col-md-3">
            <label class="gallery-thumb-drop">
                <img alt="" class="gallery-preview">
                <i class="tio-add-photo gallery-placeholder-icon"></i>
                <input type="file" class="d-none gallery-image-input" name="gallery[__INDEX__][image]" accept=".webp,.jpg,.jpeg,.png">
            </label>
        </div>
        <div class="col-md-8">
            <label class="form-label mb-1">{{ translate('project_url') }}</label>
            <input type="url" class="form-control" name="gallery[__INDEX__][url]" placeholder="https://">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-outline-danger btn-sm remove-gallery-row"><i class="tio-delete"></i></button>
        </div>
    </div>
</template>
