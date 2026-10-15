@extends('layouts.admin.app')

@section('title', translate('freelancer_category_setup'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 d-flex gap-10">
                <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/brand-setup.png') }}" alt="">
                {{ translate('freelancer_category_setup') }}
            </h2>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-start">
                        <form action="{{ route('admin.freelancer.categories.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                            @csrf
                            <input type="hidden" name="is_active" value="1">
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

                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <div class="h-100">
                                        <div class="tab-content" id="pills-tabContent">
                                            @foreach($languages as $lang)
                                                <div class="form-group tab-pane fade {{ $lang == $defaultLanguage ? 'show active' : '' }}" id="{{ $lang }}-form" aria-labelledby="{{ $lang }}-link" role="tabpanel">
                                                    <label class="form-label">{{ translate('freelancer_category') }}<span class="text-danger">*</span> ({{ strtoupper($lang) }})</label>
                                                    <input type="text" name="name[]" class="form-control" maxlength="100" placeholder="{{ translate('add_freelancer_category') }}" {{ $lang == $defaultLanguage ? 'required' : '' }}>
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
                                                    <option disabled selected>{{ translate('set_Priority') }}</option>
                                                    @for ($i = 0; $i <= 20; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
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
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <h4 class="mb-0"><span class="text-info-dark">1:1</span></h4>
                                            </div>
                                            <div class="upload-file">
                                                <input type="file" name="image" id="freelancer-category-image" class="upload-file__input single_file_input"
                                                       accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                                <label class="upload-file__wrapper">
                                                    <div class="upload-file-textbox text-center">
                                                        <img width="34" height="34" class="svg" src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/svg/image-upload.svg') }}" alt="image upload">
                                                        <h6 class="mt-1 fw-medium lh-base text-center">
                                                            <span class="text-info">{{ translate('Click to upload') }}</span>
                                                            <br>
                                                            {{ translate('or drag and drop') }}
                                                        </h6>
                                                    </div>
                                                    <img class="upload-file-img" loading="lazy" src="" data-default-src="" alt="">
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
                                <button type="reset" class="btn btn-secondary">{{ translate('reset') }}</button>
                                <button type="submit" class="btn btn-primary">{{ translate('submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-20">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body d-flex flex-column gap-20">
                        <div class="d-flex justify-content-between align-items-center gap-20 flex-wrap">
                            <h3 class="mb-0">
                                {{ translate('freelancer_categories') }}
                                <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">{{ $categories->total() }}</span>
                            </h3>
                            <form action="{{ url()->current() }}" method="GET" novalidate>
                                <div class="input-group flex-grow-1 max-w-280">
                                    <input type="search" name="searchValue" class="form-control"
                                           placeholder="{{ translate('search_by_category_name') }}"
                                           value="{{ request('searchValue') }}">
                                    <div class="input-group-append search-submit">
                                        <button type="submit">
                                            <i class="fi fi-rr-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-borderless align-middle">
                                <thead class="text-capitalize">
                                    <tr>
                                        <th>{{ translate('ID') }}</th>
                                        <th class="text-center">{{ translate('image') }}</th>
                                        <th>{{ translate('name') }}</th>
                                        <th class="text-center">{{ translate('priority') }}</th>
                                        <th class="text-center">{{ translate('status') }}</th>
                                        <th class="text-center">{{ translate('action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categories as $category)
                                        <tr>
                                            <td>{{ $category['id'] }}</td>
                                            <td class="d-flex justify-content-center">
                                                <div class="avatar-60 d-flex align-items-center rounded overflow-hidden">
                                                    <img class="w-100 h-100 object-fit-cover" alt=""
                                                         src="{{ getStorageImages(path: $category->image_full_url, type: 'backend-category') }}">
                                                </div>
                                            </td>
                                            <td>{{ $category['defaultname'] }}</td>
                                            <td class="text-center">{{ $category['priority'] }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('admin.freelancer.categories.status') }}" method="post"
                                                      id="freelancer-category-status{{ $category['id'] }}-form" class="no-reload-form" novalidate>
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $category['id'] }}">
                                                    <label class="switcher mx-auto" for="freelancer-category-status{{ $category['id'] }}">
                                                        <input
                                                            class="switcher_input custom-modal-plugin"
                                                            type="checkbox" value="1" name="is_active"
                                                            id="freelancer-category-status{{ $category['id'] }}"
                                                            {{ $category['is_active'] ? 'checked' : '' }}
                                                            data-modal-type="input-change-form"
                                                            data-modal-form="#freelancer-category-status{{ $category['id'] }}-form"
                                                            data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/category-status-on.png') }}"
                                                            data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/category-status-off.png') }}"
                                                            data-on-title="{{ translate('Want_to_Turn_ON').' '.$category['defaultname'].' '. translate('status') }}"
                                                            data-off-title="{{ translate('Want_to_Turn_OFF').' '.$category['defaultname'].' '.translate('status') }}"
                                                            data-on-message="<p>{{ translate('freelancer_category_enabled_message') }}</p>"
                                                            data-off-message="<p>{{ translate('freelancer_category_disabled_message') }}</p>"
                                                            data-on-button-text="{{ translate('turn_on') }}"
                                                            data-off-button-text="{{ translate('turn_off') }}">
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </form>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-3">
                                                    <a class="btn btn-outline-info icon-btn" title="{{ translate('edit') }}"
                                                       href="{{ route('admin.freelancer.categories.edit', [$category['id']]) }}">
                                                        <i class="fi fi-sr-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger icon-btn"
                                                            title="{{ translate('delete') }}" data-bs-toggle="modal"
                                                            data-bs-target="#deleteFreelancerCategoryModal{{ $category['id'] }}">
                                                        <i class="fi fi-rr-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="modal fade" id="deleteFreelancerCategoryModal{{ $category['id'] }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-body text-center p-30">
                                                                <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/delete.png') }}" width="80" class="mb-20" alt="">
                                                                <h3 class="mb-3">{{ translate('want_to_delete_this_freelancer_category') }}?</h3>
                                                                <p class="mb-4">{{ translate('you_will_not_be_able_to_revert_this_once_it_is_deleted.') }}</p>
                                                                <form action="{{ route('admin.freelancer.categories.delete') }}" method="post" novalidate>
                                                                    @csrf
                                                                    <input type="hidden" name="id" value="{{ $category['id'] }}">
                                                                    <div class="d-flex justify-content-center gap-3">
                                                                        <button type="button" class="btn btn-secondary min-w-120" data-bs-dismiss="modal">{{ translate('cancel') }}</button>
                                                                        <button type="submit" class="btn btn-danger min-w-120">{{ translate('Yes,_Delete') }}</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive mt-4">
                            <div class="d-flex justify-content-lg-end">
                                {{ $categories->links() }}
                            </div>
                        </div>

                        @if(count($categories) == 0)
                            @include('layouts.admin.partials._empty-state',['text'=>'no_freelancer_category_found'],['image'=>'default'])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
