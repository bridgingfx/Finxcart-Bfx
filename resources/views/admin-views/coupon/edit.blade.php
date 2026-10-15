@extends('layouts.admin.app')

@section('title', translate('coupon_Edit'))

@section('content')
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/coupon_setup.png') }}" alt="">
            {{translate('coupon_update') }}
        </h2>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">

                <div class="card-body">
                    <form action="{{route('admin.coupon.update',[$coupon['id']]) }}" method="post" novalidate>
                        @csrf

                        @include('admin-views.coupon.partials._form')

                        <div class="d-flex align-items-center justify-content-end flex-wrap gap-3">
                            <button type="reset" class="btn btn-secondary px-4">{{translate('reset')}}</button>
                            <button type="submit" class="btn btn-primary px-4">{{translate('Update')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<span id="coupon-bearer-url" data-url="{{route('admin.coupon.ajax-get-vendor')}}"></span>
@endsection
@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/coupon.js')}}"></script>
@endpush
