@extends('layouts.vendor.app')

@section('title', translate('product_Add'))

@push('css_or_js')
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
    <link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
    <style>
        /* Validation Error Styles */
        .is-invalid {
            border-color: #dc3545 !important;
            /* box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important; */
        }

        .is-invalid:focus {
            border-color: #dc3545 !important;
            /* box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important; */
        }

        .validation-error {
            font-size: 12px;
            margin-top: 0.25rem;
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Select2 validation styling */
        .is-invalid + .select2-container .select2-selection {
            border-color: #dc3545 !important;
        }

        /* Summernote validation styling */
        .is-invalid + .note-editor {
            border-color: #dc3545 !important;
        }

        /* Force button to be clickable */
        .product-add-requirements-check {
            position: relative !important;
            z-index: 9999 !important;
            cursor: pointer !important;
        }

        /* Ensure the container is also touchable */
        .d-flex.justify-content-end {
            position: relative;
            z-index: 9998;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0 d-flex gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
                {{ translate('add_New_Product') }}
            </h2>
        </div>

        @include('vendor-views.product.partials._slot-counter')

        @php
            $slotFull = ($totalSlots ?? 0) > 0 && ($usedSlots ?? 0) >= ($totalSlots ?? 0);
        @endphp
        @if(!$slotFull)
        <form class="product-form text-start js-skip-auto-validate" action="{{ route('vendor.products.add') }}"
              method="POST" enctype="multipart/form-data" id="product_form" novalidate>
            @csrf
            {{-- --- THIS IS THE NEW HIDDEN INPUT --- --}}
            <input type="hidden" name="vendor_tier_id" value="{{ $selectedTier->id ?? '' }}">

            <input type="hidden" name="tier_id" value="{{ $tierDetails->id ?? '' }}">
            {{-- --- END NEW HIDDEN INPUT --- --}}
            <div class="card">
                <div class="px-4 pt-3 d-flex justify-content-between">
                    <ul class="nav nav-tabs w-fit-content mb-4">
                        @foreach ($languages as $lang)
                            <li class="nav-item">
                                <span class="nav-link text-capitalize form-system-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }} cursor-pointer"
                                      id="{{ $lang }}-link">{{ getLanguageName($lang) . '(' . strtoupper($lang) . ')' }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a class="btn btn--primary btn-sm text-capitalize h-100" href="{{route('vendor.products.product-gallery') }}">
                        {{translate('add_info_from_gallery')}}
                    </a>
                </div>

                <div class="card-body">
                    {{-- All products are Digital-only now; Product Type is a fixed hidden field. --}}
                    {{-- Listing Type only applies to digital listings (event/broker aren't physical stock items) --}}
                    <div class="form-group" id="listing-type-section">
                        <label class="title-color d-block">
                            {{ translate('listing_type') }}
                            <span class="input-required-icon">*</span>
                        </label>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check">
                                <input class="form-check-input listing-type-input" type="radio" name="listing_type"
                                       id="listing_type_product" value="product"
                                       {{ old('listing_type', 'product') == 'product' ? 'checked' : '' }}>
                                <label class="form-check-label" for="listing_type_product">{{ translate('product') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input listing-type-input" type="radio" name="listing_type"
                                       id="listing_type_event" value="event"
                                       {{ old('listing_type') == 'event' ? 'checked' : '' }}>
                                <label class="form-check-label" for="listing_type_event">{{ translate('event') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input listing-type-input" type="radio" name="listing_type"
                                       id="listing_type_broker" value="broker"
                                       {{ old('listing_type') == 'broker' ? 'checked' : '' }}>
                                <label class="form-check-label" for="listing_type_broker">{{ translate('broker') }}</label>
                            </div>
                        </div>
                    </div>
                    @foreach ($languages as $lang)
                        <div class="{{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form"
                             id="{{ $lang }}-form">
                            <div class="form-group">
                                <label class="title-color"
                                       for="{{ $lang }}_name">{{ translate('product_name') }}
                                    ({{ strtoupper($lang) }})
                                    @if($lang == $defaultLanguage)
                                        <span class="input-required-icon">*</span>
                                    @endif
                                </label>
                                <input type="text" {{ $lang == $defaultLanguage ? 'required' : '' }} name="name[]"
                                       id="{{ $lang }}_name" class="form-control {{ $lang == $defaultLanguage ? 'product-title-default-language' : '' }}"
                                       placeholder="{{ translate('new_product') }}">
                            </div>
                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                            <div class="form-group pt-4">
                                <label class="title-color"
                                       for="{{ $lang }}_description">{{ translate('description') }}
                                    ({{ strtoupper($lang) }})</label>
                                <textarea name="description[]" class="summernote {{ $lang == $defaultLanguage ? 'product-description-default-language' : '' }}">{{ old('details') }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card mt-3 rest-part">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="tio-user-big"></i>
                        <h4 class="mb-0">{{ translate('general_setup') }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <input type="hidden" id="product_type" name="product_type" value="{{ old('product_type', 'digital') }}">
                        <input type="hidden" id="product_type_override" name="product_type" value="" disabled>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name" class="title-color">
                                    {{ translate('categories') }}
                                    <span class="input-required-icon">*</span>
                                </label>

                                <div class="category-row-template d-none">
                                    <div class="row gy-2 align-items-center category-row mb-2">
                                        <div class="col-md-4">
                                            <select class="form-control category-level-select" data-level="category">
                                                <option value="">{{ translate('select_category') }}</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category['id'] }}">{{ $category['defaultName'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-control category-level-select" data-level="sub_category" disabled>
                                                <option value="">{{ translate('select_Sub_Category') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-control category-level-select" data-level="sub_sub_category" disabled>
                                                <option value="">{{ translate('select_Sub_Sub_Category') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-category-row">
                                                <i class="tio-delete"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="category-rows-wrapper"
                                     data-get-categories-url="{{ route('vendor.products.get-categories') }}"
                                     data-get-all-categories-url="{{ route('vendor.products.get-all-categories-flat') }}"
                                     data-placeholder-category="{{ translate('select_category') }}"
                                     data-placeholder-sub-category="{{ translate('select_Sub_Category') }}"
                                     data-placeholder-sub-sub-category="{{ translate('select_Sub_Sub_Category') }}"
                                     data-placeholder-loading="{{ translate('loading') }}..."
                                     data-placeholder-select="{{ translate('Select') }}">
                                </div>

                                <button type="button" id="add-category-row-btn" class="btn btn-sm btn-outline-primary mt-1">
                                    + {{ translate('add_another_category') }}
                                </button>

                                <script type="application/json" id="selected-category-rows-data">{!! json_encode($selectedCategoryRows ?? []) !!}</script>
                            </div>
                        </div>
                        <div class="col-md-12 show-for-event-or-broker-product d-none">
                            <div class="form-group">
                                <label for="external_url" class="title-color">
                                    {{ translate('website_url') }}
                                    <span class="input-required-icon">*</span>
                                </label>
                                <input type="url" id="external_url" name="external_url" class="form-control"
                                       value="{{ old('external_url') }}"
                                       placeholder="{{ translate('ex') }}: https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                            <div class="form-group">
                                <label for="book_tickets_url" class="title-color">{{ translate('book_tickets_url') }}</label>
                                <input type="url" id="book_tickets_url" name="book_tickets_url" class="form-control"
                                       value="{{ old('book_tickets_url') }}"
                                       placeholder="{{ translate('ex') }}: https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                            <div class="form-group">
                                <label for="view_floorplan_url" class="title-color">{{ translate('view_floorplan_url') }}</label>
                                <input type="url" id="view_floorplan_url" name="view_floorplan_url" class="form-control"
                                       value="{{ old('view_floorplan_url') }}"
                                       placeholder="{{ translate('ex') }}: https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                            <div class="form-group">
                                <label for="sponsor_exhibit_url" class="title-color">{{ translate('sponsor_exhibit_url') }}</label>
                                <input type="url" id="sponsor_exhibit_url" name="sponsor_exhibit_url" class="form-control"
                                       value="{{ old('sponsor_exhibit_url') }}"
                                       placeholder="{{ translate('ex') }}: https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                            <div class="form-group">
                                <label for="brochure_event" class="title-color">{{ translate('brochure') }}</label>
                                <input type="file" id="brochure_event" name="brochure" class="form-control"
                                       accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                            <div class="form-group">
                                <label for="event_days" class="title-color">{{ translate('event_days') }}</label>
                                <input type="text" id="event_days" name="event_days" class="form-control"
                                       value="{{ old('event_days') }}" placeholder="{{ translate('ex') }}: 2">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                            <div class="form-group">
                                <label for="event_industry_brands" class="title-color">{{ translate('industry_brands') }}</label>
                                <input type="text" id="event_industry_brands" name="event_industry_brands" class="form-control"
                                       value="{{ old('event_industry_brands') }}" placeholder="{{ translate('ex') }}: 100+">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                            <div class="form-group">
                                <label for="event_speakers_count" class="title-color">{{ translate('speakers') }}</label>
                                <input type="text" id="event_speakers_count" name="event_speakers_count" class="form-control"
                                       value="{{ old('event_speakers_count') }}" placeholder="{{ translate('ex') }}: 50+">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                            <div class="form-group">
                                <label for="event_audience" class="title-color">{{ translate('audience') }}</label>
                                <input type="text" id="event_audience" name="event_audience" class="form-control"
                                       value="{{ old('event_audience') }}" placeholder="{{ translate('ex') }}: Global">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-broker-product d-none">
                            <div class="form-group">
                                <label for="open_account_url" class="title-color">
                                    {{ translate('open_account_url') }}
                                    <span class="input-required-icon">*</span>
                                </label>
                                <input type="url" id="open_account_url" name="open_account_url" class="form-control"
                                       value="{{ old('open_account_url') }}"
                                       placeholder="{{ translate('ex') }}: https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-broker-product d-none">
                            <div class="form-group">
                                <label for="contact_broker_url" class="title-color">
                                    {{ translate('contact_broker_url') }}
                                    <span class="input-required-icon">*</span>
                                </label>
                                <input type="url" id="contact_broker_url" name="contact_broker_url" class="form-control"
                                       value="{{ old('contact_broker_url') }}"
                                       placeholder="{{ translate('ex') }}: https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-broker-product d-none">
                            <div class="form-group">
                                <label for="brochure_broker" class="title-color">{{ translate('download_brochure') }}</label>
                                <input type="file" id="brochure_broker" name="brochure" class="form-control"
                                       accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 digital-product-sections-show">
                            <div class="form-group">
                                <label class="title-color">
                                    {{ translate("Author") }}/{{ translate("Creator") }}/{{ translate("Artist") }}
                                </label>
                                <select class="multiple-select2 form-control" name="authors[]" multiple="multiple" id="mySelect">
                                    @foreach($digitalProductAuthors as $authors)
                                        <option value="{{ $authors['name'] }}">{{ $authors['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 col-xl-3 digital-product-sections-show">
                            <div class="form-group">
                                <label class="title-color">{{ translate("Publishing_House") }}</label>
                                <select class="multiple-select2 form-control" name="publishing_house[]" multiple="multiple">
                                    @foreach($publishingHouseList as $publishingHouse)
                                        <option value="{{ $publishingHouse['name'] }}">{{ $publishingHouse['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 col-xl-3" id="digital_product_type_show">
                            <div class="form-group">
                                <label for="digital_product_type"
                                       class="title-color">{{ translate("delivery_type") }}</label>
                                <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                      title="{{ translate('for_Ready_Product_deliveries,_customers_can_pay_&_instantly_download_pre-uploaded_digital_products.') }} {{ translate('For_Ready_After_Sale_deliveries,_customers_pay_first_then_vendor_uploads_the_digital_products_that_become_available_to_customers_for_download') }}">
                                    <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                </span>
                                <select name="digital_product_type" id="digital_product_type" class="form-control"
                                        required>
                                    {{-- <option value="{{ old('category_id') }}" selected disabled>
                                        ---{{ translate('select') }}---
                                    </option> --}}
                                    <option value="ready_after_sell">{{ translate("ready_After_Sell") }}</option>
                                    <option value="ready_product">{{ translate("ready_Product") }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 col-xl-3 digital-product-sections-show">
                            <div class="form-group">
                                <label for="delivery_mode" class="title-color">{{ translate("delivery_mode") }}</label>
                                <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                      title="{{ translate('auto_marks_the_order_delivered_instantly_after_payment._manual_requires_you_to_mark_it_delivered_from_your_panel.') }}">
                                    <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                </span>
                                <select name="delivery_mode" id="delivery_mode" class="form-control">
                                    <option value="manual" selected>{{ translate("manual") }}</option>
                                    <option value="auto">{{ translate("auto") }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <label class="title-color d-flex justify-content-between gap-2">
                                    <span class="d-flex align-items-center gap-2">
                                        {{ translate('product_SKU') }}
                                        <span class="input-required-icon">*</span>
                                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                              title="{{ translate('create_a_unique_product_code_by_clicking_on_the_Generate_Code_button') }}">
                                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}"
                                                 alt="">
                                        </span>
                                    </span>
                                    <span class="style-one-pro cursor-pointer user-select-none text--primary action-onclick-generate-number" data-input="#generate_number">
                                        {{ translate('generate_code') }}
                                    </span>
                                </label>
                                <input type="text" minlength="6" id="generate_number" name="code"
                                       class="form-control" value="{{ old('code') }}"
                                       placeholder="{{ translate('123412') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 physical_product_show">
                            <div class="form-group">
                                <label class="title-color">{{ translate('unit') }}</label>
                                <select class="form-control" name="unit">
                                    @foreach(units() as $unit)
                                        <option value="{{ $unit }}" {{ old('unit') == $unit ? 'selected' : '' }}>
                                            {{ $unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group">
                                <label class="title-color d-flex align-items-center gap-2">
                                    {{ translate('search_tags') }}
                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('add_the_product_search_tag_for_this_product_that_customers_can_use_to_search_quickly') }}">
                                        <img width="16" src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}"
                                             alt="">
                                    </span>
                                </label>
                                <input type="text" class="form-control" placeholder="{{ translate('enter_tag') }}"
                                       name="tags" data-role="tagsinput">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3 rest-part show-for-product-radio">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="tio-user-big"></i>
                        <h4 class="mb-0">{{ translate('pricing_&_others') }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-6 col-lg-4 col-xl-3 d-none">
                            <div class="form-group">
                                <div class="d-flex gap-2 mb-2">
                                    <label class="title-color mb-0">{{ translate('purchase_price') }}
                                        ({{ getCurrencySymbol(currencyCode: getCurrencyCode()) }})
                                    </label>
                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('add_the_purchase_price_for_this_product') }}.">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </div>
                                <input type="number" min="0" step="0.01"
                                       placeholder="{{ translate('purchase_price') }}"
                                       value="{{ old('purchase_price') }}" name="purchase_price"
                                       class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <div class="d-flex gap-2 mb-2">
                                    <label class="title-color mb-0">
                                        {{ translate('unit_price') }}
                                        ({{ getCurrencySymbol(currencyCode: getCurrencyCode()) }})
                                        <span class="input-required-icon">*</span>
                                    </label>

                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('set_the_selling_price_for_each_unit_of_this_product._This_Unit_Price_section_would_not_be_applied_if_you_set_a_variation_wise_price') }}.">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </div>
                                <input type="number" min="0" step="0.01"
                                       placeholder="{{ translate('unit_price') }}" name="unit_price"
                                       value="{{ old('unit_price') }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 physical_product_show" id="minimum_order_qty_wrap">
                            <div class="form-group">
                                <div class="d-flex gap-2">
                                    <label class="title-color" for="minimum_order_qty">
                                        {{ translate('minimum_order_qty') }}
                                        <span class="input-required-icon">*</span>
                                    </label>

                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('set_the_minimum_order_quantity_that_customers_must_choose._Otherwise,_the_checkout_process_would_not_start') }}.">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </div>

                                <input type="number" min="1" value="{{ old('minimum_order_qty', 1) }}" step="1"
                                       placeholder="{{ translate('minimum_order_quantity') }}"
                                       name="minimum_order_qty" id="minimum_order_qty" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 physical_product_show" id="quantity">
                            <div class="form-group">
                                <div class="d-flex gap-2">
                                    <label class="title-color" for="current_stock">
                                        {{ translate('current_stock_qty') }}
                                        <span class="input-required-icon">*</span>
                                    </label>

                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('add_the_Stock_Quantity_of_this_product_that_will_be_visible_to_customers') }}.">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </div>
                                <input type="number" min="0" value="{{ old('current_stock', 0) }}" step="1"
                                       placeholder="{{ translate('quantity') }}"
                                       name="current_stock" id="current_stock" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <div class="d-flex gap-2 mb-2">
                                    <label class="title-color mb-0" for="discount_Type">{{ translate('discount_Type') }}</label>
                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('if_Flat,_discount_amount_will_be_set_as_fixed_amount._If_Percentage,_discount_amount_will_be_set_as_percentage.') }}">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </div>

                                <select class="form-control" name="discount_type" id="discount_type">
                                    <option value="flat">{{ translate('flat') }}</option>
                                    <option value="percent">{{ translate('percent') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <div class="d-flex gap-2">
                                    <label class="title-color" for="discount">{{ translate('discount_amount') }} <span
                                            class="discount_amount_symbol">({{ getCurrencySymbol(currencyCode: getCurrencyCode()) }})</span></label>

                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('add_the_discount_amount_in_percentage_or_a_fixed_value_here') }}.">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </div>
                                <input type="number" min="0" value="0" step="0.01"
                                       placeholder="{{ translate('ex: 5') }}"
                                       name="discount" id="discount" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <div class="d-flex gap-2">
                                    <label class="title-color" for="tax">
                                        {{ translate('tax_amount') }}(%)
                                        <span class="input-required-icon">*</span>
                                    </label>

                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('set_the_Tax_Amount_in_percentage_here') }}">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </div>

                                <input type="number" min="0" step="0.01"
                                       placeholder="{{ translate('ex: 5') }}" name="tax" id="tax"
                                       value="{{ old('tax') ?? 0 }}" class="form-control">
                                <input name="tax_type" value="percent" class="d-none">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <div class="d-flex gap-2">
                                    <label class="title-color" for="tax_model">
                                        {{ translate('tax_calculation') }}
                                        <span class="input-required-icon">*</span>
                                    </label>

                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('set_the_tax_calculation_method_from_here.').' '.translate('select_Include_with_product_to_combine_product_price_and_tax_on_the_checkout.').' '.translate('pick_Exclude_from_product_to_display_product_price_and_tax_amount_separately.') }}">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </div>
                                <select name="tax_model" id="tax_model" class="form-control" required>
                                    <option value="include">{{ translate("include_with_product") }}</option>
                                    <option value="exclude">{{ translate("exclude_with_product") }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 physical_product_show" id="shipping_cost">
                            <div class="form-group">
                                <div class="d-flex gap-2">
                                    <label class="title-color">
                                        {{ translate('shipping_cost') }}
                                        ({{ getCurrencySymbol(currencyCode: getCurrencyCode()) }})
                                        <span class="input-required-icon">*</span>
                                    </label>

                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('set_the_shipping_cost_for_this_product_here._Shipping_cost_will_only_be_applicable_if_product-wise_shipping_is_enabled.') }}">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </div>

                                <input type="number" min="0" value="{{ old('shipping_cost', 0) }}" step="0.01"
                                       placeholder="{{ translate('shipping_cost') }}"
                                       name="shipping_cost" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6 physical_product_show" id="shipping_cost_multi">
                            <div class="form-group">
                                <div class="form-control h-auto min-form-control-height d-flex align-items-center flex-wrap justify-content-between gap-2">
                                    <div class="d-flex gap-2">
                                        <label class="title-color text-capitalize" for="shipping_cost">{{ translate('shipping_cost_multiply_with_quantity') }}</label>

                                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                              title="{{ translate('if_enabled,_the_shipping_charge_will_increase_with_the_product_quantity') }}">
                                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                        </span>
                                    </div>

                                    <div>
                                        <label class="switcher">
                                            <input class="switcher_input" type="checkbox" name="multiply_qty">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3 rest-part digitalProductVariationSetupSection">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="tio-user-big"></i>
                        <h4 class="mb-0">{{ translate('product_variation_setup') }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-2" id="digital-product-type-choice-section">
                        <div class="col-sm-6 col-md-4 col-xxl-3">
                            <div class="multi--select">
                                <label class="title-color">{{ translate('File_Type') }}</label>
                                <select class="js-example-basic-multiple js-select2-custom form-control" name="file-type" multiple id="digital-product-type-select">
                                    @foreach($digitalProductFileTypes as $FileType)
                                        <option value="{{ $FileType }}">{{ translate($FileType) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3 rest-part" id="digital-product-variation-section"></div>

            <div class="mt-3 rest-part">
                <div class="product-image-wrapper">
                    <div class="item-1">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                        <div>
                                            <label for="name" class="title-color text-capitalize font-weight-bold mb-0">
                                                {{ translate('product_thumbnail') }}
                                                <span class="input-required-icon">*</span>
                                            </label>
                                            <span
                                                class="badge badge-soft-info">{{ THEME_RATIO[theme_root_path()]['Product Image'] }}</span>
                                            <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                                  title="{{ translate('add_your_products_thumbnail_in') }} JPG, PNG or JPEG {{ translate('format_within') }} 2MB">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}"
                                                     alt="">
                                            </span>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="custom_upload_input">
                                            <input type="file" name="image" class="custom-upload-input-file action-upload-color-image" id=""
                                                   data-imgpreview="pre_img_viewer"
                                                   accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">

                                            <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d--none">
                                                <i class="tio-delete"></i>
                                            </span>

                                            <div class="img_area_with_preview position-absolute z-index-2">
                                                <img id="pre_img_viewer" class="h-auto aspect-1 bg-white d-none"
                                                     src="" alt="">
                                            </div>
                                            <div
                                                class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                                <div
                                                    class="d-flex flex-column justify-content-center align-items-center">
                                                    <img alt="" class="w-75"
                                                         src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}">
                                                    <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                                </div>
                                            </div>
                                        </div>

                                        <p class="text-muted mt-2">
                                            {{ translate('image_format') }} : {{ "Jpg, png, jpeg, webp," }}
                                            <br>
                                            {{ translate('image_size') }} : {{ translate('max') }} {{ "2 MB" }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="color_image_column item-2 d-none">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                        <div>
                                            <label for="name"
                                                   class="title-color text-capitalize font-weight-bold mb-0">{{ translate('colour_wise_product_image') }}</label>
                                            <span
                                                class="badge badge-soft-info">{{ THEME_RATIO[theme_root_path()]['Product Image'] }}</span>
                                            <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                                  title="{{ translate('add_color-wise_product_images_here') }}.">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}"
                                                     alt="">
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-muted">
                                        {{ translate('must_upload_colour_wise_images_first.') }}
                                        {{ translate('Colour_is_shown_in_the_image_section_top_right') }}
                                    </p>

                                    <div id="color-wise-image-section" class="row g-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="additional_image_column item-2">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <div>
                                        <label for="name"
                                               class="title-color text-capitalize font-weight-bold mb-0">{{ translate('upload_additional_image') }}</label>
                                        <span
                                            class="badge badge-soft-info">{{ THEME_RATIO[theme_root_path()]['Product Image'] }}</span>
                                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                              title="{{ translate('upload_any_additional_images_for_this_product_from_here') }}.">
                                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                        </span>
                                    </div>

                                </div>
                                <p class="text-muted">{{ translate('upload_additional_product_images') }}</p>

                                <div class="row g-2" id="additional_Image_Section">
                                    <div class="col-sm-12 col-md-4">
                                        <div class="custom_upload_input position-relative border-dashed-2 aspect-1">
                                            <input type="file" name="images[]" class="custom-upload-input-file action-add-more-image"
                                                   data-index="1" data-imgpreview="additional_Image_1"
                                                   accept=".jpg, .png, .webp, .jpeg, .gif, .bmp, .tif, .tiff|image/*"
                                                   data-target-section="#additional_Image_Section"
                                            >

                                            <span class="delete_file_input delete_file_input_section btn btn-outline-danger btn-sm square-btn d-none">
                                                <i class="tio-delete"></i>
                                            </span>

                                            <div class="img_area_with_preview position-absolute z-index-2 border-0">
                                                <img id="additional_Image_1" class="h-auto aspect-1 bg-white d-none "
                                                     src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg-dummy') }}" alt="">
                                            </div>
                                            <div
                                                class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                                <div
                                                    class="d-flex flex-column justify-content-center align-items-center">
                                                    <img alt=""
                                                         src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}"
                                                         class="w-75">
                                                    <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="item-1 digital-product-sections-show">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                        <div>
                                            <label for="name" class="title-color text-capitalize font-weight-bold mb-0">{{ translate('Product_Preview_File') }}</label>
                                            <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                                  title="{{ translate('upload_a_suitable_file_for_a_short_product_preview.') }} {{ translate('this_preview_will_be_common_for_all_variations.') }}">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-muted">{{ translate('Upload_a_short_preview') }}.</p>
                                </div>
                                <div class="image-uploader">
                                    <input type="file" name="preview_file" class="image-uploader__zip" id="input-file" accept=".pdf,.mp4,.mp3,application/pdf,video/mp4,audio/mpeg,audio/mp3">
                                    <div class="image-uploader__zip-preview">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}" class="mx-auto" width="50" alt="">
                                        <div class="image-uploader__title line--limit-2">
                                            {{ translate('Upload_File') }}
                                        </div>
                                    </div>
                                    <span class="btn btn-outline-danger btn-sm square-btn collapse zip-remove-btn">
                                        <i class="tio-delete"></i>
                                    </span>
                                </div>
                                <p class="text-muted mt-2 fs-12">
                                    {{ translate('Format') }} : {{ " pdf, mp4, mp3" }}
                                    <br>
                                    {{ translate('image_size') }} : {{ translate('max') }} {{ "10 MB" }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3 rest-part">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="tio-user-big"></i>
                        <h4 class="mb-0">{{ translate('product_video') }}</h4>
                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                              title="{{ translate('add_the_YouTube_video_link_here._Only_the_YouTube-embedded_link_is_supported') }}.">
                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="title-color mb-0">{{ translate('youtube_video_link') }}</label>
                        <span class="text-info"> ({{ translate('optional_please_provide_embed_link_not_direct_link') }}.)</span>
                    </div>
                    <input type="text" name="video_url"
                           placeholder="{{ translate('ex') }} : {{ 'https://www.youtube.com/embed/5R06LRdUCSE' }}"
                           class="form-control">
                </div>
            </div>

            {{-- Added Product Preview URL Section --}}
            <div class="card mt-3 rest-part">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="tio-link"></i>
                        <h4 class="mb-0">{{ translate('product_preview_url') }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="title-color mb-0">{{ translate('preview_url') }}</label>
                        <span class="text-info"> ({{ translate('optional') }}.)</span>
                    </div>
                    <input type="url" name="preview_url"
                           placeholder="{{ translate('ex') }} : https://example.com/preview"
                           class="form-control">
                </div>
            </div>

            <div class="card mt-3 rest-part">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="tio-user-big"></i>
                        <h4 class="mb-0">
                            {{ translate('seo_section') }}
                            <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                  data-placement="top"
                                  title="{{ translate('add_meta_titles_descriptions_and_images_for_products').', '.translate('this_will_help_more_people_to_find_them_on_search_engines_and_see_the_right_details_while_sharing_on_other_social_platforms') }}">
                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                            </span>
                        </h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="title-color">
                                    {{ translate('meta_Title') }}
                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          data-placement="top"
                                          title="{{ translate('add_the_products_title_name_taglines_etc_here').' '.translate('this_title_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms') .' [ '. translate('character_Limit') }} : 100 ]">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </label>
                                <input type="text" name="meta_title" placeholder="{{ translate('meta_Title') }}"
                                       class="form-control" id="meta_title">
                            </div>
                            <div class="form-group">
                                <label class="title-color">
                                    {{ translate('meta_Description') }}
                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          data-placement="top"
                                          title="{{ translate('write_a_short_description_of_this_shop_product').' '.translate('this_description_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms') .' [ '. translate('character_Limit') }} : 100 ]">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </label>
                                <textarea rows="4" type="text" name="meta_description" id="meta_description" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="d-flex justify-content-center">
                                <div class="form-group w-100">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div>
                                            <label class="title-color" for="meta_Image">
                                                {{ translate('meta_Image') }}
                                            </label>
                                            <span
                                                class="badge badge-soft-info">{{ THEME_RATIO[theme_root_path()]['Meta Thumbnail'] }}</span>
                                            <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                                  title="{{ translate('add_Meta_Image_in') }} JPG, PNG or JPEG {{ translate('format_within') }} 2MB, {{ translate('which_will_be_shown_in_search_engine_results') }}.">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}"
                                                     alt="">
                                            </span>
                                        </div>

                                    </div>

                                    <div>
                                        <div class="custom_upload_input">
                                            <input type="file" name="meta_image"
                                                   class="custom-upload-input-file meta-img action-upload-color-image"
                                                   data-imgpreview="pre_meta_image_viewer"
                                                   id="meta_image_input"
                                                   accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">

                                            <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d--none">
                                                <i class="tio-delete"></i>
                                            </span>

                                            <div class="img_area_with_preview position-absolute z-index-2 d-flex align-items-center justify-content-center">
                                                <img id="pre_meta_image_viewer" class="h-auto bg-white onerror-add-class-d-none pre-meta-image-viewer" alt=""
                                                     src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg-dummy') }}">
                                            </div>
                                            <div
                                                class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center overflow-hidden">
                                                <div
                                                    class="d-flex flex-column justify-content-center align-items-center">
                                                    <img alt="" class="w-75"
                                                         src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}">
                                                    <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('vendor-views.product.partials._seo-section')
                </div>
            </div>

            <div class="row justify-content-end gap-3 mt-3 mx-1">
                <button type="reset" class="btn btn-secondary px-5">{{ translate('reset') }}</button>
                <button type="button" class="btn btn--primary px-5 product-add-requirements-check">{{ translate('submit') }}</button>
            </div>
        </form>
    </div>

    <span id="route-vendor-products-sku-combination" data-url="{{ route('vendor.products.sku-combination') }}"></span>
    <span id="route-vendor-products-digital-variation-combination" data-url="{{ route('vendor.products.digital-variation-combination') }}"></span>
    <span id="image-path-of-product-upload-icon" data-path="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}"></span>
    <span id="image-path-of-product-upload-icon-two" data-path="{{ dynamicAsset(path: 'public/assets/back-end/img/400x400/img2.jpg') }}"></span>
    <span id="message-enter-choice-values" data-text="{{ translate('enter_choice_values') }}"></span>
    <span id="message-upload-image" data-text="{{ translate('upload_Image') }}"></span>
    <span id="message-file-size-too-big" data-text="{{ translate('file_size_too_big') }}"></span>
    <span id="message-are-you-sure" data-text="{{ translate('are_you_sure') }}"></span>
    <span id="message-yes-word" data-text="{{ translate('yes') }}"></span>
    <span id="message-no-word" data-text="{{ translate('no') }}"></span>
    <span id="message-want-to-add-or-update-this-product" data-text="{{ translate('want_to_add_this_product') }}"></span>
    <span id="message-please-only-input-png-or-jpg" data-text="{{ translate('please_only_input_png_or_jpg_type_file') }}"></span>
    <span id="message-product-added-successfully" data-text="{{ translate('product_added_successfully') }}"></span>
    <span id="message-discount-will-not-larger-then-variant-price" data-text="{{ translate('the_discount_price_will_not_larger_then_Variant_Price') }}"></span>
    <span id="system-currency-code" data-value="{{ getCurrencySymbol(currencyCode: getCurrencyCode()) }}"></span>
    <span id="system-session-direction" data-value="{{ Session::get('direction') }}"></span>

    {{-- Tier Plan Data for Frontend Validation --}}
    <span id="tier-plan-name" data-value="{{ $tierDetails->name ?? '' }}"></span>
    <span id="tier-plan-id" data-value="{{ $tierDetails->id ?? '' }}"></span>
    <span id="has-active-tier" data-value="{{$selectedTier ? 'true' : 'false' }}"></span>
    <span id="tier-price-min" data-value="{{ $tierDetails->price_threshold_min_usd ?? '' }}"></span>
    <span id="tier-price-max" data-value="{{ $tierDetails->price_threshold_max_usd ?? '' }}"></span>
        @endif {{-- end !$slotFull --}}
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/tags-input.min.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/spartan-multi-image-picker.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.js') }}"></script>
    {{-- Product Validation Service (must load before product-add-update.js) --}}
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/vendor/product-add-update-validation.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/shared/image-resize.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/vendor/product-add-update.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/vendor/product-add-colors-img.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/vendor/category-repeater.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const hasPendingProductSession = @json(session()->has('pending_product'));
            const stagedProduct = sessionStorage.getItem('pending_product_stage');

            if (stagedProduct && !hasPendingProductSession) {
                sessionStorage.removeItem('pending_product_stage');

                setTimeout(function () {
                    const message = "{{ translate('Session_expired._Please_add_your_product_again.') }}";
                    if (typeof window.toastr !== 'undefined') {
                        window.toastr.error(message);
                    } else if (typeof window.toastMagic !== 'undefined') {
                        window.toastMagic.error(message);
                    }
                }, 400);
            }
        });
    </script>
@endpush
