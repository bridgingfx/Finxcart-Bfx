@extends('layouts.front-end.app')

@section('title',translate('shipping_Address'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
    <style>
        .digital-delivery-card {
            border: 1px solid #d9e4f2;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 20px;
            box-shadow: 0 8px 24px rgba(28, 56, 96, 0.05);
        }

        .digital-delivery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 12px;
        }

        .digital-delivery-option {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #d9e4f2;
            border-radius: 14px;
            background: #fff;
            cursor: pointer;
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease, background-color .18s ease;
        }

        .digital-delivery-option:hover {
            border-color: #2b4d80;
            box-shadow: 0 10px 24px rgba(43, 77, 128, 0.08);
            transform: translateY(-1px);
        }

        .digital-delivery-option.is-active {
            border-color: #2b4d80;
            background: #eef4ff;
            box-shadow: 0 12px 28px rgba(43, 77, 128, 0.12);
        }

        .digital-delivery-option .form-check-input {
            margin-top: 0.25rem;
            flex-shrink: 0;
        }

        .digital-delivery-option-content {
            min-width: 0;
        }

        .digital-delivery-option-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.2;
            color: #13233b;
        }

        .digital-delivery-option-desc {
            margin: 6px 0 0;
            font-size: 13px;
            line-height: 1.45;
            color: #516073;
        }

        .digital-delivery-field-wrap {
            margin-top: 18px;
        }

        .digital-delivery-field-wrap label {
            color: #13233b;
            font-weight: 600;
        }

        .digital-delivery-help {
            margin-top: 8px;
            font-size: 12px;
            line-height: 1.45;
            color: #637489;
        }
    </style>
@endpush

@section('content')

@php($billingInputByCustomer=getWebConfig(name: 'billing_input_by_customer'))
@php($customer = auth('customer')->user())

    <div class="container py-4 rtl __inline-56 px-0 px-md-3 text-align-direction">
        <div class="row mx-max-md-0">
            <div class="col-md-12 mb-3">
                <h3 class="font-weight-bold text-center text-lg-left">{{translate('checkout')}}</h3>
            </div>
            <section class="col-lg-8 px-max-md-0">
                <div class="checkout_details">
                <div class="px-3 px-md-3">
                    {{-- @include('web-views.partials._checkout-steps',['step'=>2]) --}}
                </div>
                    @php($defaultLocation = getWebConfig(name: 'default_location'))

                    <input type="hidden" id="physical_product" name="physical_product" value="{{ $physical_product_view ? 'yes':'no'}}">

                    @if($physical_product_view)
                        <div class="px-3 px-md-0">
                            <h4 class="pb-2 mt-4 fs-18 text-capitalize">{{ translate('billing_address')}}</h4>
                        </div>

                        @php($shippingAddresses= \App\Models\ShippingAddress::where(['customer_id'=>auth('customer')->id(), 'is_guest'=>0])->get())

                        <form method="post" class="card __card" id="address-form" novalidate>
                            <div class="card-body p-0">
                                <ul class="list-group">
                                    <li class="list-group-item add-another-address">
                                        @if ($shippingAddresses->count() >0)
                                            <div class="d-flex align-items-center justify-content-end gap-3">
                                                <div class="dropdown">
                                                    <button class="form-control dropdown-toggle text-capitalize" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        {{translate('saved_address')}}
                                                    </button>

                                                    <div class="dropdown-menu dropdown-menu-right saved-address-dropdown scroll-bar-saved-address" aria-labelledby="dropdownMenuButton">
                                                        @foreach($shippingAddresses as $key => $address)
                                                        <div class="dropdown-item select_shipping_address {{$key == 0 ? 'active' : ''}}" id="shippingAddress{{$key}}">
                                                            <input type="hidden" class="selected_shippingAddress{{$key}}" value="{{$address}}">
                                                            <input type="hidden" name="shipping_method_id" value="{{$address['id']}}">
                                                            {{-- <input type="hidden" name="shipping_method_id" value="{{$address['id']}}"> --}}

                                                            <div class="media gap-2">
                                                                <div class="">
                                                                    <i class="tio-briefcase"></i>
                                                                </div>
                                                                <div class="media-body">
                                                                    <div class="mb-1 text-capitalize">{{$address->address_type}}</div>
                                                                    <div class="text-muted fs-12 text-capitalize text-wrap">{{$address->address}}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div id="accordion">
                                            <div class="">
                                                <div class="mt-3">
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label>{{ translate('contact_person_name')}}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="text" class="form-control" name="contact_person_name" {{$shippingAddresses->count()==0?'required':''}} id="name">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label>{{ translate('phone')}}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="tel" class="form-control phone-input-with-country-picker-3" id="phone" {{$shippingAddresses->count()==0?'required':''}}>
                                                                <input type="hidden" id="shipping_phone_view" class="country-picker-phone-number-3 w-50" name="phone" readonly>
                                                            </div>
                                                        </div>
                                                        @if(!auth('customer')->check())
                                                            <div class="col-sm-12">
                                                                <div class="form-group">
                                                                    <label for="exampleInputEmail1">
                                                                        {{ translate('email')}}
                                                                        <span class="text-danger">*</span>
                                                                    </label>
                                                                    <input type="email" class="form-control"  name="email" id="email" {{$shippingAddresses->count()==0?'required':''}}>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <div class="col-12">
                                                            <div class="form-group">
                                                                <label>{{ translate('address_type')}}</label>
                                                                <select class="form-control" name="address_type" id="address_type">
                                                                    <option value="permanent">{{ translate('permanent')}}</option>
                                                                    <option value="home">{{ translate('home')}}</option>
                                                                    <option value="others">{{ translate('others')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="form-group">
                                                                <label>{{ translate('country')}}
                                                                    <span class="text-danger">*</span></label>
                                                                <select name="country" id="country" class="form-control selectpicker" data-live-search="true" required>
                                                                    @forelse($countries as $country)
                                                                        <option value="{{ $country['name'] }}">{{ $country['name'] }}</option>
                                                                    @empty
                                                                        <option value="">{{ translate('no_country_to_deliver') }}</option>
                                                                    @endforelse
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label>{{ translate('city')}}<span  class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" name="city" id="city" {{$shippingAddresses->count()==0?'required':''}}>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label>{{ translate('zip_code')}}
                                                                    <span class="text-danger">*</span></label>
                                                                @if($zip_restrict_status == 1)
                                                                    <select name="zip" class="form-control selectpicker" data-live-search="true" id="select2-zip-container" required>
                                                                        @forelse($zip_codes as $code)
                                                                        <option value="{{ $code->zipcode }}">{{ $code->zipcode }}</option>
                                                                        @empty
                                                                            <option value="">{{ translate('no_zip_to_deliver') }}</option>
                                                                        @endforelse
                                                                    </select>
                                                                @else
                                                                <input type="text" class="form-control"
                                                                       name="zip" id="zip" {{$shippingAddresses->count()==0?'required':''}}>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="form-group mb-1">
                                                                <label>{{ translate('address')}}<span class="text-danger">*</span></label>
                                                                <textarea class="form-control" id="address" type="text" name="address" {{$shippingAddresses->count()==0?'required':''}}></textarea>
                                                                <span class="fs-14 text-danger font-semi-bold opacity-0 map-address-alert">
                                                                    {{ translate('note') }}: {{ translate('you_need_to_select_address_from_your_selected_country') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{-- @if(getWebConfig('map_api_status') ==1 )
                                                        <div class="form-group location-map-canvas-area map-area-alert-border">
                                                            <input id="pac-input" class="controls rounded __inline-46 location-search-input-field" title="{{translate('search_your_location_here')}}" type="text" placeholder="{{translate('search_here')}}"/>
                                                            <div class="__h-200px" id="location_map_canvas"></div>
                                                        </div>
                                                    @endif --}}
                                                    <div class="col-12">
                                                        @include('web-views.partials._address-map-search', ['mapQuery' => $defaultLocation ? $defaultLocation['lat'] . ',' . $defaultLocation['lng'] : 'Dubai'])
                                                    </div>

                                                    <div class="d-flex gap-3 align-items-center">
                                                        <label class="form-check-label d-flex gap-2 align-items-center" id="save_address_label">
                                                            <input type="hidden" name="shipping_method_id" id="shipping_method_id" value="0">
                                                            @if(auth('customer')->check())
                                                                <input type="checkbox" name="save_address" id="save_address">
                                                                {{ translate('save_this_Address') }}
                                                            @endif
                                                        </label>
                                                    </div>

                                                    {{-- <input type="hidden" id="latitude"
                                                           name="latitude" class="form-control d-inline"
                                                           placeholder="{{ translate('ex')}} : -94.22213"
                                                           value="{{$defaultLocation?$defaultLocation['lat']:0}}" required
                                                           readonly>
                                                    <input type="hidden"
                                                           name="longitude" class="form-control"
                                                           placeholder="{{ translate('ex')}} : 103.344322" id="longitude"
                                                           value="{{$defaultLocation?$defaultLocation['lng']:0}}" required
                                                           readonly> --}}

                                                           <input type="hidden" id="latitude" name="latitude" value="{{ $defaultLocation ? $defaultLocation['lat'] : 0 }}" required readonly>
                                                            <input type="hidden" id="longitude" name="longitude" value="{{ $defaultLocation ? $defaultLocation['lng'] : 0 }}" required readonly>


                                                    <button type="submit" class="btn btn--primary d--none" id="address_submit"></button>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </form>

                        @if(!Auth::guard('customer')->check() && $web_config['guest_checkout_status'])
                        <div class="card __card mt-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center flex-wrap justify-content-between gap-3">
                                    <div class="min-h-45 form-check d-flex gap-3 align-items-center cursor-pointer user-select-none">
                                        <input type="checkbox" id="is_check_create_account" name="is_check_create_account" class="form-check-input mt-0" value="1">
                                        <label class="form-check-label font-weight-bold fs-13" for="is_check_create_account">
                                            {{translate('Create_an_account_with_the_above_info')}}
                                        </label>
                                    </div>

                                    <div class="is_check_create_account_password_group d--none">
                                        <div class="d-flex gap-3 flex-wrap flex-sm-nowrap">
                                            <div class="w-100">
                                                <div class="password-toggle rtl">
                                                    <input class="form-control text-align-direction" name="customer_password" type="password" id="customer_password" placeholder="{{ translate('new_Password') }}" required>
                                                    <label class="password-toggle-btn">
                                                        <input class="custom-control-input" type="checkbox">
                                                        <i class="tio-hidden password-toggle-indicator"></i>
                                                        <span class="sr-only">{{ translate('show_password') }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="w-100">
                                                <div class="password-toggle rtl">
                                                    <input class="form-control text-align-direction w-100" name="customer_confirm_password" type="password" id="customer_confirm_password" placeholder="{{ translate('confirm_Password') }}" required>
                                                    <label class="password-toggle-btn">
                                                        <input class="custom-control-input" type="checkbox">
                                                        <i class="tio-hidden password-toggle-indicator"></i>
                                                        <span class="sr-only">{{ translate('show_password') }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endif

                    @if($billingInputByCustomer)
                    {{-- <div class="d-none">
                        <div class="billing-methods_label d-flex flex-wrap justify-content-between gap-2 mt-4 pb-3 px-3 px-md-0">
                            <h4 class="mb-0 fs-18 text-capitalize">{{ translate('billing_address')}}</h4>

                            @php($billingAddresses=\App\Models\ShippingAddress::where(['customer_id'=>auth('customer')->id(), 'is_guest'=>'0'])->get())
                            @if($physical_product_view)
                                <div class="form-check d-flex gap-3 align-items-center">
                                    <input type="checkbox" id="same_as_shipping_address" name="same_as_shipping_address"
                                        class="form-check-input action-hide-billing-address mt-0" {{$billingInputByCustomer==1?'':'checked'}}>
                                    <label class="form-check-label user-select-none" for="same_as_shipping_address">
                                        {{ translate('same_as_shipping_address')}}
                                    </label>
                                </div>
                            @endif
                        </div>

                        @if(!$physical_product_view)
                            <div class="mb-3 alert--info">
                                <div class="d-flex align-items-center gap-2">
                                    <img class="mb-1" src="{{ theme_asset('public/assets/front-end/img/icons/info-light.svg') }}" alt="Info">
                                    <span>{{ translate('When_you_input_all_the_required_information_for_this_billing_address_it_will_be_stored_for_future_purchases') }}</span>
                                </div>
                            </div>
                        @endif

                        <form method="post" class="card __card" id="billing-address-form" novalidate>
                            <div id="hide_billing_address" class="">
                                <ul class="list-group">

                                    <li class="list-group-item action-billing-address-hide">
                                        @if ($billingAddresses->count() >0)
                                            <div class="d-flex align-items-center justify-content-end gap-3">

                                                <div class="dropdown">
                                                    <button class="form-control dropdown-toggle text-capitalize" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        {{translate('saved_address')}}
                                                    </button>

                                                    <div class="dropdown-menu dropdown-menu-right saved-address-dropdown scroll-bar-saved-address" aria-labelledby="dropdownMenuButton">
                                                        @foreach($billingAddresses as $key=>$address)
                                                            <div class="dropdown-item select_billing_address {{$key == 0 ? 'active' : ''}}" id="billingAddress{{$key}}">
                                                                <input type="hidden" class="selected_billingAddress{{$key}}" value="{{$address}}">
                                                                <input type="hidden" name="billing_method_id" value="{{$address['id']}}">
                                                                <div class="media gap-2">
                                                                    <div class="">
                                                                        <i class="tio-briefcase"></i>
                                                                    </div>
                                                                    <div class="media-body">
                                                                        <div class="mb-1 text-capitalize">{{$address->address_type}}</div>
                                                                        <div class="text-muted fs-12 text-capitalize text-wrap">{{$address->address}}</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div id="accordion">
                                            <div class="">
                                                <div class="">
                                                    <div class="row">
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label>{{ translate('contact_person_name')}}<span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control"
                                                                    name="billing_contact_person_name" id="billing_contact_person_name"  {{$billingAddresses->count()==0?'required':''}}>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label>{{ translate('phone')}}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="text" class="form-control phone-input-with-country-picker-2"
                                                                    id="billing_phone" {{ $billingAddresses->count()==0 ? 'required' : '' }}>
                                                                <input type="hidden" class="country-picker-phone-number-2 w-50" name="billing_phone" readonly>
                                                            </div>
                                                        </div>
                                                        @if(!auth('customer')->check())
                                                            <div class="col-sm-12">
                                                                <div class="form-group">
                                                                    <label
                                                                        for="exampleInputEmail1">{{ translate('email')}}
                                                                        <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control"
                                                                        name="billing_contact_email" id="billing_contact_email" id {{$billingAddresses->count()==0?'required':''}}>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <div class="col-12">
                                                            <div class="form-group">
                                                                <label>{{ translate('address_type')}}</label>
                                                                <select class="form-control" name="billing_address_type" id="billing_address_type">
                                                                    <option value="permanent">{{ translate('permanent')}}</option>
                                                                    <option value="home">{{ translate('home')}}</option>
                                                                    <option value="others">{{ translate('others')}}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="form-group">
                                                                <label>{{ translate('country')}}<span class="text-danger">*</span></label>
                                                                <select name="billing_country" class="form-control selectpicker" data-live-search="true" id="billing_country">
                                                                    @foreach($countries as $country)
                                                                        <option value="{{ $country['name'] }}">{{ $country['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label for="exampleInputEmail1">{{ translate('city')}}<span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" id="billing_city"
                                                                    name="billing_city" {{$billingAddresses->count()==0?'required':''}}>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label>{{ translate('zip_code')}}
                                                                    <span class="text-danger">*</span></label>
                                                                @if($zip_restrict_status)
                                                                    <select name="billing_zip" class="form-control selectpicker" data-live-search="true" id="billing_zip">
                                                                        @foreach($zip_codes as $code)
                                                                            <option value="{{ $code->zipcode }}">{{ $code->zipcode }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                @else
                                                                    <input type="text" class="form-control" id="billing_zip"
                                                                           name="billing_zip" {{ $billingAddresses->count()==0 ? 'required' : '' }}>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group mb-1">
                                                        <label>{{ translate('address')}}<span class="text-danger">*</span></label>
                                                        <textarea class="form-control" id="billing_address" type="billing_text" name="billing_address" id="billing_address" {{$billingAddresses->count()==0?'required':''}}></textarea>

                                                        <span class="fs-14 text-danger font-semi-bold opacity-0 map-address-alert">
                                                            {{ translate('note') }}: {{ translate('you_need_to_select_address_from_your_selected_country') }}
                                                        </span>
                                                    </div>
                                                    @if(getWebConfig('map_api_status') ==1 )
                                                    <div class="form-group map-area-alert-border location-map-billing-canvas-area">
                                                        <input id="pac-input-billing" class="controls rounded __inline-46 location-search-input-field"
                                                            title="{{translate('search_your_location_here')}}"
                                                            type="text"
                                                            placeholder="{{translate('search_here')}}"/>
                                                        <div class="__h-200px" id="location_map_canvas_billing"></div>
                                                    </div>
                                                    @endif

                                                    <input type="hidden" name="billing_method_id" id="billing_method_id" value="0">
                                                    @if(auth('customer')->check())
                                                    <div class=" d-flex gap-3 align-items-center">
                                                        <label class="form-check-label d-flex gap-2 align-items-center" id="save-billing-address-label">
                                                            <input type="checkbox" name="save_address_billing" id="save_address_billing">
                                                            {{ translate('save_this_Address') }}
                                                        </label>
                                                    </div>
                                                    @endif

                                                    <input type="hidden" id="billing_latitude"
                                                        name="billing_latitude" class="form-control d-inline"
                                                        placeholder="{{ translate('ex')}} : -94.22213"
                                                        value="{{$defaultLocation?$defaultLocation['lat']:0}}" required
                                                        readonly>
                                                    <input type="hidden"
                                                        name="billing_longitude" class="form-control"
                                                        placeholder="{{ translate('ex')}} : 103.344322" id="billing_longitude"
                                                        value="{{$defaultLocation?$defaultLocation['lng']:0}}" required
                                                        readonly>

                                                    <button type="submit" class="btn btn--primary d--none" id="address_submit"></button>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </form>
                    </div> --}}

                        @if(!Auth::guard('customer')->check() && $web_config['guest_checkout_status'] && !$physical_product_view)
                            <div class="card __card mt-3">
                                <div class="card-body">
                                    <div class="d-flex align-items-center flex-wrap justify-content-between gap-3">
                                        <div class="min-h-45 form-check d-flex gap-3 align-items-center cursor-pointer user-select-none">
                                            <input type="checkbox" id="is_check_create_account" name="is_check_create_account" class="form-check-input mt-0" value="1">
                                            <label class="form-check-label font-weight-bold fs-13" for="is_check_create_account">
                                                {{translate('Create_an_account_with_the_above_info')}}
                                            </label>
                                        </div>

                                        <div class="is_check_create_account_password_group d--none">
                                            <div class="d-flex gap-3 flex-wrap flex-sm-nowrap">
                                                <div class="w-100">
                                                    <div class="password-toggle rtl">
                                                        <input class="form-control text-align-direction" name="customer_password" type="password" id="customer_password" placeholder="{{ translate('new_Password')}}" required>
                                                        <label class="password-toggle-btn">
                                                            <input class="custom-control-input" type="checkbox">
                                                            <i class="tio-hidden password-toggle-indicator"></i>
                                                            <span class="sr-only">{{ translate('show_password') }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="w-100">
                                                    <div class="password-toggle rtl">
                                                        <input class="form-control text-align-direction" name="customer_confirm_password" type="password" id="customer_confirm_password" placeholder="{{ translate('confirm_Password')}}" required>
                                                        <label class="password-toggle-btn">
                                                            <input class="custom-control-input" type="checkbox">
                                                            <i class="tio-hidden password-toggle-indicator"></i>
                                                            <span class="sr-only">{{ translate('show_password') }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                @if($has_digital_products)
                <div class="card __card mt-4 mx-3 mx-md-0">
                    <div class="card-body">
                        <div class="digital-delivery-card">
                            <div class="d-flex flex-column gap-1 mb-3">
                                <h5 class="mb-0 fs-18 text-capitalize text-dark">{{ translate('digital_delivery') }}</h5>
                                <p class="mb-0 fs-13" style="color:#516073;">{{ translate('how_would_you_like_to_receive_your_digital_products') }}</p>
                            </div>

                            <div class="digital-delivery-grid mb-3">
                                <label class="digital-delivery-option" for="delivery_type_email">
                                    <input type="radio" name="digital_delivery_type_radio" value="email" id="delivery_type_email" class="form-check-input mt-0 digital-delivery-radio" checked>
                                    <span class="digital-delivery-option-content">
                                        <span class="digital-delivery-option-title">Email</span>
                                        <span class="digital-delivery-option-desc">Send the order link to your email inbox.</span>
                                    </span>
                                </label>

                                <label class="digital-delivery-option" for="delivery_type_whatsapp">
                                    <input type="radio" name="digital_delivery_type_radio" value="whatsapp" id="delivery_type_whatsapp" class="form-check-input mt-0 digital-delivery-radio">
                                    <span class="digital-delivery-option-content">
                                        <span class="digital-delivery-option-title">WhatsApp</span>
                                        <span class="digital-delivery-option-desc">Send the order link to your WhatsApp number.</span>
                                    </span>
                                </label>

                                <label class="digital-delivery-option" for="delivery_type_other">
                                    <input type="radio" name="digital_delivery_type_radio" value="other" id="delivery_type_other" class="form-check-input mt-0 digital-delivery-radio">
                                    <span class="digital-delivery-option-content">
                                        <span class="digital-delivery-option-title">Other</span>
                                        <span class="digital-delivery-option-desc">Type your preferred delivery method or contact option.</span>
                                    </span>
                                </label>
                            </div>

                            <div class="digital-delivery-field-wrap">
                                <label id="digital_delivery_value_label" class="fs-14 text-dark">Email address</label>
                                <input type="text" id="digital_delivery_value_input" class="form-control"
                                    value="{{ auth('customer')->check() ? ($customer?->getAttribute('email') ?? '') : '' }}"
                                    placeholder="Enter your email">
                                <input type="hidden" id="digital_delivery_type" name="digital_delivery_type" value="email">
                                <input type="hidden" id="digital_delivery_value" name="digital_delivery_value"
                                    value="{{ auth('customer')->check() ? ($customer?->getAttribute('email') ?? '') : '' }}">
                                <div class="digital-delivery-help" id="digital_delivery_help_text">
                                    Choose how you want to receive your digital product.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                </div>
            </section>
            @include('web-views.partials._order-summary')
        </div>
    </div>

    <span id="message-update-this-address" data-text="{{ translate('Update_this_Address') }}"></span>
    <span id="route-customer-choose-shipping-address-other" data-url="{{ route('customer.choose-shipping-address-other') }}"></span>
    <span id="default-latitude-address" data-value="{{ $defaultLocation ? $defaultLocation['lat']:'-33.8688' }}"></span>
    <span id="default-longitude-address" data-value="{{ $defaultLocation ? $defaultLocation['lng']:'151.2195' }}"></span>
    <span id="route-action-checkout-function" data-route="checkout-details"></span>
    <span id="system-country-restrict-status" data-value="{{ $country_restrict_status }}"></span>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>
    @include('web-views.partials._address-map-search-scripts')
    <script>
        "use strict";
        $('#is_check_create_account').on('change', function() {
            if($(this).is(':checked')) {
                $('.is_check_create_account_password_group').fadeIn();
            } else {
                $('.is_check_create_account_password_group').fadeOut();
            }
        });

        (function () {
            var userEmail = @json($customer?->getAttribute('email') ?? '');
            var userPhone = @json($customer?->getAttribute('phone') ?? '');
            var digitalDeliveryOptions = {
                email: {
                    label: 'Email address',
                    placeholder: 'Enter your email',
                    help: 'Send the order link to your email inbox.',
                    value: userEmail
                },
                whatsapp: {
                    label: 'WhatsApp number',
                    placeholder: 'Enter your WhatsApp number',
                    help: 'Send the order link to your WhatsApp number.',
                    value: userPhone
                },
                other: {
                    label: 'Other delivery option',
                    placeholder: 'Type your preferred delivery option',
                    help: 'Examples: Telegram, Signal, iMessage, email, call',
                    value: ''
                }
            };

            function syncDigitalDelivery() {
                var type = $('input[name="digital_delivery_type_radio"]:checked').val();
                var config = digitalDeliveryOptions[type] || digitalDeliveryOptions.email;
                $('.digital-delivery-option').removeClass('is-active');
                $('input[name="digital_delivery_type_radio"]:checked').closest('.digital-delivery-option').addClass('is-active');
                $('#digital_delivery_type').val(type);
                $('#digital_delivery_value_label').text(config.label);
                $('#digital_delivery_value_input').attr('placeholder', config.placeholder);
                $('#digital_delivery_help_text').text(config.help);
                if (!$('#digital_delivery_value_input').data('manual')) {
                    $('#digital_delivery_value_input').val(config.value);
                }
                $('#digital_delivery_value').val($('#digital_delivery_value_input').val());
            }

            $('input[name="digital_delivery_type_radio"]').on('change', function () {
                $('#digital_delivery_value_input').removeData('manual');
                syncDigitalDelivery();
            });

            $('#digital_delivery_value_input').on('input', function () {
                $(this).data('manual', true);
                $('#digital_delivery_value').val($(this).val());
            });

            syncDigitalDelivery();
        })();
    </script>

    <script src="{{ theme_asset(path: 'public/assets/front-end/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/shipping.js?v=1') }}"></script>



    @if(getWebConfig('map_api_status') ==1 )
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{getWebConfig('map_api_key')}}&callback=mapsShopping&loading=async&libraries=places&v=3.56"
            defer>
        </script>
    @endif

@endpush

