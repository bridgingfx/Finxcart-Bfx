@extends('layouts.admin.app')

@section('title', translate('brand_Update'))

@section('content')
    <div class="content container-fluid">

        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0 align-items-center d-flex gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/brand.png') }}" alt="">
                {{ translate('brand_Update') }}
            </h2>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-start">
                        <form action="{{ route('admin.brand.update', [$brand['id']]) }}" method="post"
                              enctype="multipart/form-data" class="brand-setup-form" novalidate>
                            @csrf
                            @include('admin-views.brand.partials._form')

                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" id="reset" class="btn btn-secondary px-4">
                                    {{ translate('reset') }}
                                </button>
                                <button type="submit" class="btn btn-primary px-4">
                                    {{ translate('update') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $('.brand-setup-form').on('reset', function () {
            window.location.reload()
        });
    </script>
    <script src="{{ dynamicAsset(path: 'public/assets/backend/admin/js/products/products-management.js') }}"></script>
@endpush
