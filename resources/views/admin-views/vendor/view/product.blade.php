@extends('layouts.admin.app')

@section('title', $seller?->shop->name ?? translate("shop_name_not_found"))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('vendor_details')}}
            </h2>
        </div>

        <div class="flex-between d-sm-flex row align-items-center justify-content-between mb-2 mx-1">
            <div>
                @include('admin-views.vendor.view.partials._status-actions')
            </div>
        </div>
        <div class="page-header">
            <div class="flex-between row mx-1">
                <div>
                    <h2 class="page-header-title mb-3">{{ $seller?->shop->name ?? translate("shop_Name")." : ".translate("update_Please") }}</h2>
                </div>
            </div>

            <div class="position-relative nav--tab-wrapper">
                <ul class="nav nav-pills nav--tab">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.vendors.view',$seller->id) }}">{{translate('shop')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.vendors.view',['id'=>$seller->id, 'tab'=>'order']) }}">{{translate('order')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.vendors.view',['id'=>$seller->id, 'tab'=>'product']) }}">{{translate('product')}}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.vendors.view',['id'=>$seller['id'], 'tab'=>'tier_plan']) }}">{{translate('Vendor_Tier_Plans')}}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.vendors.view',['id'=>$seller['id'], 'tab'=>'clearance_sale']) }}">{{translate('clearance_sale_products')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.vendors.view',['id'=>$seller->id, 'tab'=>'setting']) }}">{{translate('setting')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.vendors.view',['id'=>$seller->id, 'tab'=>'transaction']) }}">{{translate('transaction')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.vendors.view',['id'=>$seller->id, 'tab'=>'review']) }}">{{translate('review')}}</a>
                    </li>
                </ul>
                <div class="nav--tab__prev">
                    <button class="btn btn-circle border-0 bg-white text-primary">
                        <i class="fi fi-sr-angle-left"></i>
                    </button>
                </div>
                <div class="nav--tab__next">
                    <button class="btn btn-circle border-0 bg-white text-primary">
                        <i class="fi fi-sr-angle-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="px-3 py-4">
                <h3 class="mb-0 d-flex align-items-center gap-2 mb-4">
                    {{translate('products')}}
                    <span class="badge badge-info text-bg-info">{{$products->total()}}</span>
                </h3>

                <div class="table-responsive datatable-custom">
                    <table id="columnSearchDatatable" class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('product Name')}}</th>
                                <th>{{translate('Tier_Plan')}}</th> {{-- NEW COLUMN HEADER --}}
                                <th class="text-center">{{translate('product_Type')}}</th>
                                <th class="text-center">{{translate('selling_price')}}</th>
                                <th class="text-center">{{translate('featured')}}</th>
                                <th class="text-center">{{translate('active_status')}}</th>
                                <th class="text-center">{{translate('action')}}</th>
                            </tr>
                        </thead>

                        <tbody id="set-rows">
                        @foreach($products as $k=>$product)
                            <tr>
                                <td>{{$products->firstItem()+$k}}</td>
                                <td>
                                    <a href="{{ route('admin.products.view', ['addedBy' => ($product['added_by'] == 'seller' ? 'vendor' : 'in-house'), 'id' => $product['id']]) }}"
                                       class="text-dark text-hover-primary gap-3 d-flex align-items-center">
                                        <img width="42" src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'backend-basic') }}"
                                             class="aspect-1 border object-fit-cover rounded" alt="">
                                        <div>
                                            <div class="fw-bold text-truncate">
                                                {{ substr($product['name'], 0, 20) }}{{ strlen($product['name']) > 20 ? '...' : '' }}
                                            </div>
                                            @if($product?->clearanceSale)
                                                <div class="badge badge-warning text-bg-warning user-select-none mt-1">
                                                    {{ translate('Clearance_Sale') }}
                                                </div>
                                            @endif
                                        </div>
                                    </a>
                                </td>

                                {{-- --- NEW TIER NAME COLUMN DATA --- --}}
                                <td>
                                    @if($product->vendorTier && $product->vendorTier->tier)
                                        <span class="badge badge-soft-info">
                                            {{ $product->vendorTier->tier->name }}
                                        </span>
                                    @else
                                        <span class="badge badge-soft-secondary">
                                            {{ translate('No_Tier') }}
                                        </span>
                                    @endif
                                </td>
                                {{-- --- END NEW COLUMN --- --}}

                                <td class="text-center">
                                    {{translate(str_replace('_',' ',$product['product_type']))}}
                                </td>
                                <td class="text-center">
                                    {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $product['unit_price']))}}
                                </td>
                                <td>
                                    @php($product_name = str_replace("'",'`',$product['name']))
                                    <form action="{{route('admin.products.featured-status')}}" method="post"
                                          id="product-featured{{$product['id']}}-form"
                                          class="no-reload-form" novalidate>
                                        @csrf
                                        <input type="hidden" name="id" value="{{$product['id']}}">
                                        <label class="switcher mx-auto" for="product-featured{{$product['id']}}">
                                            <input
                                                class="switcher_input custom-modal-plugin"
                                                type="checkbox" value="1" name="status"
                                                id="product-featured{{$product['id']}}"
                                                {{ $product['featured'] == 1 ? 'checked':'' }}
                                                data-modal-type="input-change-form"
                                                data-modal-form="#product-featured{{$product['id']}}-form"
                                                data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/product-status-on.png') }}"
                                                data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/product-status-off.png') }}"
                                                data-on-title = "{{translate('Want_to_Add').' '.$product_name.' '.translate('to_the_featured_section').'?'}}"
                                                data-off-title = "{{translate('Want_to_Remove').' '.$product_name.' '.translate('to_the_featured_section').'?'}}"
                                                data-on-message = "<p>{{translate('if_enabled_this_product_will_be_shown_in_the_featured_product_on_the_website_and_customer_app')}}</p>"
                                                data-off-message = "<p>{{translate('if_disabled_this_product_will_be_removed_from_the_featured_product_section_of_the_website_and_customer_app')}}</p>"
                                                data-on-button-text="{{ translate('turn_on') }}"
                                                data-off-button-text="{{ translate('turn_off') }}">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{route('admin.products.status-update')}}" method="post"
                                          id="product-status{{$product['id']}}-form" class="no-reload-form" novalidate>
                                        @csrf
                                        <input type="hidden" name="id" value="{{$product['id']}}">
                                        <label class="switcher mx-auto" for="product-status{{$product['id']}}">
                                            <input
                                                class="switcher_input custom-modal-plugin"
                                                type="checkbox" value="1" name="status"
                                                id="product-status{{$product['id']}}"
                                                {{ $product['status'] == 1 ? 'checked':'' }}
                                                data-modal-type="input-change-form"
                                                data-modal-form="#product-status{{$product['id']}}-form"
                                                data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/product-status-on.png') }}"
                                                data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/product-status-off.png') }}"
                                                data-on-title = "{{translate('Want_to_Turn_ON').' '.$product_name.' '.translate('status').'?'}}"
                                                data-off-title = "{{translate('Want_to_Turn_OFF').' '.$product_name.' '.translate('status').'?'}}"
                                                data-on-message = "<p>{{translate('if_enabled_this_product_will_be_available_on_the_website_and_customer_app')}}</p>"
                                                data-off-message = "<p>{{translate('if_disabled_this_product_will_be_hidden_from_the_website_and_customer_app')}}</p>"
                                                data-on-button-text="{{ translate('turn_on') }}"
                                                data-off-button-text="{{ translate('turn_off') }}">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </form>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-10">
                                        <a class="btn btn-outline-info icon-btn" title="{{ translate('barcode') }}" href="{{ route('admin.products.barcode', [$product['id']]) }}">
                                            <i class="fi fi-rr-barcode-read"></i>
                                        </a>
                                        <a class="btn btn-outline-info icon-btn" title="View" href="{{route('admin.products.view',['addedBy'=>($product['added_by']=='seller'?'vendor' : 'in-house'),'id'=>$product['id']])}}">
                                            <i class="fi fi-rr-eye"></i>
                                        </a>
                                        <a class="btn btn-outline-primary icon-btn" href="{{route('admin.products.update',[$product['id']])}}">
                                            <i class="fi fi-rr-pencil"></i>
                                        </a>
                                        <a class="btn btn-outline-danger icon-btn delete-data" href="javascript:" data-id="product-{{$product['id']}}">
                                            <i class="fi fi-rr-trash"></i>
                                        </a>
                                    </div>
                                    <form action="{{route('admin.products.delete',[$product['id']])}}"
                                          method="post" id="product-{{$product['id']}}" novalidate>
                                        @csrf @method('delete')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive mt-4">
                    <div class="px-4 d-flex justify-content-end">
                        {{$products->links()}}
                    </div>
                </div>
                @if(count($products)==0)
                    @include('layouts.admin.partials._empty-state',['text'=>'no_product_found'],['image'=>'default'])
                @endif
            </div>
        </div>
    </div>
@endsection
