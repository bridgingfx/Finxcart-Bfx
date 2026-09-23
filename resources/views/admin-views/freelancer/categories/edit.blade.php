@extends('layouts.admin.app')

@section('title', translate('edit_freelancer_category'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.freelancer.categories.index') }}" class="btn btn-primary btn-outline-primary btn-sm">
                    <i class="fi fi-sr-arrow-left"></i>
                </a>
                <h2 class="h1 mb-0 d-flex gap-10">
                    <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/brand-setup.png') }}" alt="">
                    {{ translate('edit_freelancer_category') }}
                </h2>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-start">
                        <form action="{{ route('admin.freelancer.categories.update', [$category['id']]) }}" method="POST" enctype="multipart/form-data" novalidate>
                            @csrf
                            <div class="table-responsive w-auto overflow-y-hidden mb-4">
                                <div class="position-relative nav--tab-wrapper">
                                    <ul class="nav nav-pills nav--tab lang_tab" id="pills-tab" role="tablist">
                                        @foreach($languages as $lang)
                                            <li class="nav-item px-0">
                                                <a data-bs-toggle="pill" data-bs-target="#{{ $lang }}-form" role="tab" class="nav-link px-2 {{ $lang == $defaultLanguage ? 'active' : '' }}" id="{{ $lang }}-link">
                                                    {{ ucfirst(getLanguageName($lang)) . ' (' . strtoupper($lang) . ')' }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="nav--tab__prev">
                                        <button type="button" class="btn btn-circle border-0 bg-white text-primary">
                                            <i class="fi fi-sr-angle-left"></i>
                                        </button>
                                    </div>
                                    <div class="nav--tab__next">
                                        <button type="button" class="btn btn-circle border-0 bg-white text-primary">
                                            <i class="fi fi-sr-angle-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @php
                                $translatedNames = [];
                                foreach ($category['translations'] as $translation) {
                                    if ($translation->key === 'name') {
                                        $translatedNames[$translation->locale] = $translation->value;
                                    }
                                }
                            @endphp

                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <div class="h-100">
                                        <div class="tab-content" id="pills-tabContent">
                                            @foreach($languages as $lang)
                                                <div class="form-group tab-pane fade {{ $lang == $defaultLanguage ? 'show active' : '' }}" id="{{ $lang }}-form" aria-labelledby="{{ $lang }}-link" role="tabpanel">
                                                    <label class="form-label">{{ translate('freelancer_category') }}<span class="text-danger">*</span> ({{ strtoupper($lang) }})</label>
                                                    <input type="text" name="name[]" class="form-control" maxlength="100"
                                                           value="{{ $lang == $defaultLanguage ? $category['name'] : ($translatedNames[$lang] ?? '') }}"
                                                           placeholder="{{ translate('add_freelancer_category') }}" {{ $lang == $defaultLanguage ? 'required' : '' }}>
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                            @endforeach
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="priority">{{ translate('priority') }}
                                                <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                                                      aria-label="{{ translate('the_lowest_number_will_get_the_highest_priority') }}"
                                                      data-bs-title="{{ translate('the_lowest_number_will_get_the_highest_priority') }}">
                                                    <i class="fi fi-sr-info"></i>
                                                </span>
                                            </label>
                                            <div class="select-wrapper">
                                                <select class="form-select" name="priority" id="priority" required>
                                                    @for ($i = 0; $i <= 20; $i++)
                                                        <option value="{{ $i }}" {{ (int)$category['priority'] === $i ? 'selected' : '' }}>{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="is_active">{{ translate('status') }}</label>
                                            <div class="select-wrapper">
                                                <select class="form-select" name="is_active" id="is_active">
                                                    <option value="1" {{ $category['is_active'] ? 'selected' : '' }}>{{ translate('active') }}</option>
                                                    <option value="0" {{ !$category['is_active'] ? 'selected' : '' }}>{{ translate('inactive') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 mt-4 mt-lg-0 from_part_2">
                                    <div class="d-flex justify-content-center align-items-center bg-section rounded-8 p-20 w-100 h-100">
                                        <div class="d-flex flex-column gap-30">
                                            <div class="text-center">
                                                <label class="form-label fw-semibold mb-1">
                                                    {{ translate('category_image') }}
                                                </label>
                                                <h4 class="mb-0"><span class="text-info-dark">1:1</span></h4>
                                            </div>
                                            <div class="upload-file">
                                                <input type="file" name="image" id="freelancer-category-image" class="upload-file__input single_file_input"
                                                       accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                <label class="upload-file__wrapper">
                                                    <div class="upload-file-textbox text-center">
                                                        <img width="34" height="34" class="svg" src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/svg/image-upload.svg') }}" alt="image upload">
                                                        <h6 class="mt-1 fw-medium lh-base text-center">
                                                            <span class="text-info">{{ translate('Click to upload') }}</span>
                                                            <br>
                                                            {{ translate('or drag and drop') }}
                                                        </h6>
                                                    </div>
                                                    <img class="upload-file-img" loading="lazy"
                                                         src="{{ getStorageImages(path: $category->image_full_url, type: 'backend-category') }}"
                                                         data-default-src="{{ getStorageImages(path: $category->image_full_url, type: 'backend-category') }}" alt="">
                                                </label>
                                                <div class="overlay">
                                                    <div class="d-flex gap-10 justify-content-center align-items-center h-100">
                                                        <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                            <i class="fi fi-rr-camera"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="fs-12 mb-0 text-center">{{ translate('image_size') }} : {{ translate('max') }} 2 MB</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <a href="{{ route('admin.freelancer.categories.index') }}" class="btn btn-secondary">{{ translate('back') }}</a>
                                <button type="submit" class="btn btn-primary">{{ translate('update') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
