@extends('layouts.admin.app')

@section('title', translate('customer_edit'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('customer_edit')}}
            </h2>
        </div>

        <form action="{{route('admin.customer.update',[$customer['id']])}}" method="post" enctype="multipart/form-data"
              class="text-start" novalidate>
            @csrf
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-0 page-header-title d-flex text-capitalize align-items-center gap-2 border-bottom pb-3 mb-3">
                        <i class="fi fi-sr-user"></i>
                        {{translate('general_information')}}
                    </h3>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="f_name" class="mb-2">{{translate('first_name')}}</label>
                                <input type="text" name="f_name" class="form-control" id="f_name"
                                       placeholder="{{translate('ex')}} : John"
                                       value="{{$customer['f_name']}}" required>
                            </div>
                            <div class="form-group">
                                <label for="l_name" class="mb-2">{{translate('last_name')}}</label>
                                <input type="text" name="l_name" class="form-control" id="l_name"
                                       placeholder="{{translate('ex')}} : Doe"
                                       value="{{$customer['l_name']}}" required>
                            </div>
                            <div class="form-group">
                                <label for="phone" class="mb-2">{{translate('phone')}}</label>
                                <input class="form-control form-control-user"
                                       type="tel" value="{{$customer['phone']}}"
                                       placeholder="{{ translate('ex').': 017xxxxxxxx' }}" name="phone" id="phone" required>
                            </div>
                            <div class="form-group">
                                <label for="email" class="mb-2">{{translate('email')}}</label>
                                <input type="email" name="email" value="{{$customer['email']}}" class="form-control"
                                       id="email" placeholder="{{translate('ex').':'.'ex@gmail.com'}}" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="text-center mb-3">
                                    <img class="upload-img-view" id="viewer"
                                         src="{{ getStorageImages(path: $customer->image_full_url , type: 'backend-profile') }}"
                                         alt=""/>
                                </div>
                                <label for="custom-file-upload" class="mb-2">{{translate('customer_image')}}</label>
                                <span class="text-info">( {{translate('ratio').'1:1'}})</span>
                                <div class="form-group">
                                    <div class="custom-file text-left">
                                        <input type="file" name="image" id="custom-file-upload" class="custom-file-input image-input"
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" data-image-id="viewer">
                                        <label class="custom-file-label" for="custom-file-upload">{{translate('choose_file')}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <h3 class="mb-0 page-header-title d-flex align-items-center gap-2 border-bottom pb-3 mb-3">
                        <i class="fi fi-rr-lock"></i>
                        {{translate('change_password')}}
                    </h3>
                    <p class="text-body-light fs-13 mb-3">{{translate('leave_the_password_fields_blank_to_keep_the_current_password')}}</p>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_password" class="mb-2 d-flex gap-2 align-items-center">
                                    {{translate('password')}}
                                    <span class="input-label-secondary cursor-pointer" data-bs-toggle="tooltip" data-bs-title="{{translate('The_password_must_be_at_least_8_characters_long_and_contain_at_least_one_uppercase_letter').','.translate('_one_lowercase_letter').','.translate('_one_digit_').','.translate('_one_special_character').','.translate('_and_no_spaces').'.'}}">
                                        <i class="fi fi-rr-info"></i>
                                    </span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="js-toggle-password form-control password-check" name="password" id="user_password" placeholder="{{ translate('password_minimum_8_characters') }}">
                                    <div id="changePassTarget" class="input-group-append changePassTarget">
                                        <a class="text-body-light" href="javascript:">
                                            <i id="changePassIcon" class="fi fi-sr-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                <span class="text-danger password-error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confirm_password" class="mb-2">
                                    {{translate('confirm_password')}}
                                </label>
                                <div class="input-group">
                                    <input type="password" class="js-toggle-password form-control" name="confirm_password" id="confirm_password" placeholder="{{ translate('confirm_password') }}">
                                    <div id="changeConfirmPassTarget" class="input-group-append changePassTarget">
                                        <a class="text-body-light" href="javascript:">
                                            <i id="changeConfirmPassIcon" class="fi fi-sr-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <a href="{{route('admin.customer.view', $customer['id'])}}" class="btn btn-outline-secondary">{{translate('cancel')}}</a>
                        <button type="submit" class="btn btn-primary">{{translate('update')}}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <span id="extension-error" data-text="{{ translate("please_only_input_png_or_jpg_type_file") }}"></span>
    <span id="size-error" data-text="{{ translate("file_size_too_big") }}"></span>
@endsection
