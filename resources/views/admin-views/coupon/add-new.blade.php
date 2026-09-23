@extends('layouts.admin.app')

@section('title', translate('coupon_Add'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/coupon_setup.png') }}" alt="">
                {{translate('coupon_setup')}}
            </h2>
        </div>

        <div class="row">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.coupon.add')}}" method="POST" novalidate>
                            @csrf

                            @include('admin-views.coupon.partials._form')

                            <div class="d-flex align-items-center justify-content-end flex-wrap gap-3">
                                <button type="reset" class="btn btn-secondary px-4">{{translate('reset')}}</button>
                                <button type="submit" class="btn btn-primary px-4">{{translate('submit')}}</button>
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
                                {{translate('coupon_list')}}
                                <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">{{ $coupons->total() }}</span>
                            </h3>
                            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-sm-end flex-grow-1">
                                <div class="flex-grow-1 max-w-280">
                                    <form action="{{ url()->current() }}" method="GET" novalidate>
                                        <div class="input-group flex-grow-1 max-w-280">
                                            <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                           placeholder="{{translate('search_by_Title_or_Code_or_Discount_Type')}}"
                                           value="{{ request('searchValue') }}" aria-label="Search orders" required>
                                            <div class="input-group-append search-submit">
                                                <button type="submit">
                                                    <i class="fi fi-rr-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                 <div class="dropdown">
                                    <a type="button" class="btn btn-outline-primary text-nowrap" href="{{ route('admin.coupon.export',['searchValue'=>request('searchValue')]) }}">
                                        <img width="14" src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/excel.png')}}" class="excel" alt="">
                                        <span class="ps-2">{{ translate('export') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="datatable"
                                   class="table table-hover table-borderless table-thead-bordered align-middle">
                                <thead class="text-capitalize">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('coupon')}}</th>
                                    <th>{{translate('coupon_type')}}</th>
                                    <th>{{translate('duration')}}</th>
                                    <th>{{translate('user_limit')}}</th>
                                    <th class="text-center">{{translate('discount_bearer')}}</th>
                                    <th>{{translate('status')}}</th>
                                    <th class="text-center">{{translate('action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($coupons as $key => $coupon )
                                    <tr>
                                        <td>{{$coupons->firstItem() + $key }}</td>
                                        <td>
                                            <div>{{substr($coupon['title'],0,20)}}</div>
                                            <strong>{{translate('code')}}: {{$coupon['code'] }}</strong>
                                        </td>
                                        <td class="text-capitalize">{{translate(str_replace('_',' ',$coupon['coupon_type']))}}</td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <span>{{date('d M, y',strtotime($coupon['start_date']))}} - </span>
                                                <span>{{date('d M, y',strtotime($coupon['expire_date']))}}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span>{{translate('limit')}}:
                                                <strong>{{ $coupon['limit'] }},</strong>
                                            </span>

                                            <span class="ml-1">{{translate('used')}}:
                                                <strong>{{ $coupon['order_count'] }}</strong>
                                            </span>
                                        </td>
                                        <td class="text-center">{{ translate($coupon['coupon_bearer'] == 'inhouse' ? 'admin':$coupon['coupon_bearer']) }}</td>
                                        <td>
                                            <form action="{{route('admin.coupon.status',[$coupon['id'],$coupon['status']?0:1])}}"
                                                method="GET" id="coupon_status{{$coupon['id'] }}-form"
                                                class="coupon_status_form" novalidate>
                                                <label class="switcher mx-auto" for="coupon_status{{$coupon['id'] }}">
                                                    <input
                                                        class="switcher_input custom-modal-plugin"
                                                        type="checkbox" value="1" name="status"
                                                        id="coupon_status{{$coupon['id'] }}"
                                                        {{ $coupon['status'] == 1 ? 'checked':'' }}
                                                        data-modal-type="input-change-form"
                                                        data-modal-form="#coupon_status{{$coupon['id'] }}-form"
                                                        data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/coupon-status-on.png') }}"
                                                        data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/coupon-status-off.png') }}"
                                                        data-on-title="{{translate('Want_to_Turn_ON_Coupon_Status').'?' }}"
                                                        data-off-title="{{translate('Want_to_Turn_OFF_Coupon_Status').'?' }}"
                                                        data-on-message="<p>{{translate('if_enabled_this_coupon_will_be_available_on_the_website_and_customer_app')}}</p>"
                                                        data-off-message="<p>{{translate('if_disabled_this_coupon_will_be_hidden_from_the_website_and_customer_app')}}</p>"
                                                        data-on-button-text="{{ translate('turn_on') }}"
                                                        data-off-button-text="{{ translate('turn_off') }}">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-3 justify-content-center">
                                                <button class="btn btn-outline-info btn-outline-info-dark icon-btn get-quick-view" data-id="{{ $coupon['id'] }}">
                                                    <i class="fi fi-sr-eye"></i>
                                                </button>
                                                <a class="btn btn-outline-info icon-btn edit"
                                                   href="{{route('admin.coupon.update',[$coupon['id']])}}"
                                                   title="{{ translate('edit')}}"
                                                >
                                                    <i class="fi fi-sr-pencil"></i>
                                                </a>
                                                <a class="btn btn-outline-danger icon-btn delete delete-data"
                                                   href="javascript:"
                                                   data-id="coupon-{{$coupon['id'] }}"
                                                   title="{{translate('delete')}}"
                                                >
                                                    <i class="fi fi-rr-trash"></i>
                                                </a>
                                                <form action="{{route('admin.coupon.delete',[$coupon['id']])}}"
                                                      method="post" id="coupon-{{$coupon['id'] }}" novalidate>
                                                    @csrf @method('delete')
                                                </form>
                                            </div>

                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <div class="modal fade" id="quick-view" tabindex="-1" role="dialog"
                                 aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered coupon-details" role="document">
                                    <div class="modal-content border-0" id="quick-view-modal">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <div class="px-4 d-flex justify-content-lg-end">
                                {{$coupons->links()}}
                            </div>
                        </div>

                        @if(count($coupons)==0)
                            @include('layouts.admin.partials._empty-state',['text'=>'no_coupon_found'],['image'=>'default'])
                        @endif
                    </div>
                    {{-- old --}}


                </div>
            </div>
        </div>
    </div>

    <span id="coupon-bearer-url" data-url="{{route('admin.coupon.ajax-get-vendor')}}"></span>
    <span id="get-detail-url" data-url="{{ route('admin.coupon.quick-view-details') }}"></span>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/coupon.js')}}"></script>
@endpush
