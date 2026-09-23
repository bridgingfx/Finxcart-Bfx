@extends('layouts.admin.app')

@section('title', translate('brand_Add'))

@section('content')
    <div class="content container-fluid">

        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/brand.png') }}" alt="">
                {{ translate('brand_Setup') }}
            </h2>
        </div>

        <div class="row g-3">
            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-body text-start">
                        <form action="{{ route('admin.brand.add-new') }}" method="post" enctype="multipart/form-data"
                              class="brand-setup-form" novalidate>
                            @csrf
                            @include('admin-views.brand.partials._form')

                            <div class="d-flex gap-3 justify-content-end">
                                <button type="reset" id="reset" class="btn btn-secondary px-4">
                                    {{ translate('reset') }}
                                </button>
                                <button type="submit" class="btn btn-primary px-4">
                                    {{ translate('submit') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include("layouts.admin.partials.offcanvas._brand-setup")
@endsection

@push('script')
    <script>
        $('.brand-setup-form').on('reset', function () {
            $(this).find('#pre_img_viewer').addClass('d-none');
            $(this).find('.placeholder-image').css('opacity', '1');
        });
    </script>
    <script src="{{ dynamicAsset(path: 'public/assets/backend/admin/js/products/products-management.js') }}"></script>
@endpush
