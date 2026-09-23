@extends('layouts.vendor.app')

@section('title', translate(request('product-gallery')==1 ?'product_Add' :'product_Edit'))

@push('css_or_js')
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
    <link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
    <style>
        /* Validation Error Styles */
        .is-invalid {
            border-color: #dc3545 !important;
        }

        .is-invalid:focus {
            border-color: #dc3545 !important;
        }

        .validation-error {
            font-size: 12px;
            margin-top: 0.25rem;
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
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
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
                {{ translate(request('product-gallery')==1 ?'product_Add' :'product_Edit') }}
            </h2>
        </div>

        <form class="product-form text-start js-skip-auto-validate" action="{{request('product-gallery')==1?route('vendor.products.add') : route('vendor.products.update',$product->id)}}" method="post"
              enctype="multipart/form-data" id="product_form" novalidate>
            @csrf
            @php($currentListingType = old('listing_type', in_array($product['product_type'], ['event', 'broker']) ? $product['product_type'] : 'product'))

            <div class="card">
                <div class="px-4 pt-3">
                    <ul class="nav nav-tabs w-fit-content mb-4">
                        @foreach($languages as $language)
                            <li class="nav-item text-capitalize">
                                <a class="nav-link form-system-language-tab  {{ $language == $defaultLanguage? 'active':''}}" href="#"
                                   id="{{ $language}}-link">{{getLanguageName($language).'('.strtoupper($language).')'}}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="card-body">
                    {{-- All products are Digital-only now; Product Type is a fixed hidden field
                         (legacy physical products keep their stored type so existing data isn't disturbed). --}}
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
                                       {{ $currentListingType == 'product' ? 'checked' : '' }}>
                                <label class="form-check-label" for="listing_type_product">{{ translate('product') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input listing-type-input" type="radio" name="listing_type"
                                       id="listing_type_event" value="event"
                                       {{ $currentListingType == 'event' ? 'checked' : '' }}>
                                <label class="form-check-label" for="listing_type_event">{{ translate('event') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input listing-type-input" type="radio" name="listing_type"
                                       id="listing_type_broker" value="broker"
                                       {{ $currentListingType == 'broker' ? 'checked' : '' }}>
                                <label class="form-check-label" for="listing_type_broker">{{ translate('broker') }}</label>
                            </div>
                        </div>
                    </div>
                    @foreach($languages as $language)
                            <?php
                            if (count($product['translations'])) {
                                $translate = [];
                                foreach ($product['translations'] as $translation) {
                                    if ($translation->locale == $language && $translation->key == "name") {
                                        $translate[$language]['name'] = $translation->value;
                                    }
                                    if ($translation->locale == $language && $translation->key == "description") {
                                        $translate[$language]['description'] = $translation->value;
                                    }
                                }
                            }
                            ?>
                        <div class="{{ $language != 'en'? 'd-none':''}} form-system-language-form" id="{{ $language}}-form">
                            <div class="form-group">
                                <label class="title-color" for="{{ $language}}_name">
                                    {{ translate('product_name') }}
                                    ({{strtoupper($language) }})
                                    @if($language == 'en')
                                        <span class="input-required-icon">*</span>
                                    @endif
                                </label>
                                <input type="text" {{ $language == 'en'? 'required':''}} name="name[]"
                                    id="{{ $language}}_name"
                                    value="{{ $translate[$language]['name']??$product['name']}}"
                                    class="form-control {{ $language == $defaultLanguage ? 'product-title-default-language' : '' }}"
                                    placeholder="{{ translate('new_Product') }}" required>
                            </div>
                            <input type="hidden" name="lang[]" value="{{ $language}}">
                            <div class="form-group pt-4">
                                <label class="title-color">{{ translate('description') }}
                                    ({{strtoupper($language) }})</label>
                                <textarea name="description[]" class="summernote"
                                >{!! $translate[$language]['description']??$product['details'] !!}</textarea>
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
                        <div class="col-md-12 show-for-event-or-broker-product {{ $currentListingType == 'product' ? 'd-none' : '' }}">
                            <div class="form-group">
                                <label for="external_url" class="title-color">
                                    {{ translate('website_url') }}
                                    <span class="input-required-icon">*</span>
                                </label>
                                <input type="url" id="external_url" name="external_url" class="form-control"
                                       value="{{ old('external_url', $product->external_url) }}"
                                       placeholder="{{ translate('ex') }}: https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product {{ $currentListingType == 'event' ? '' : 'd-none' }}">
                            <div class="form-group">
                                <label for="book_tickets_url" class="title-color">{{ translate('book_tickets_url') }}</label>
                                <input type="url" id="book_tickets_url" name="book_tickets_url" class="form-control"
                                       value="{{ old('book_tickets_url', $product->book_tickets_url) }}"
                                       placeholder="{{ translate('ex') }}: https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product {{ $currentListingType == 'event' ? '' : 'd-none' }}">
                            <div class="form-group">
                                <label for="view_floorplan_url" class="title-color">{{ translate('view_floorplan_url') }}</label>
                                <input type="url" id="view_floorplan_url" name="view_floorplan_url" class="form-control"
                                       value="{{ old('view_floorplan_url', $product->view_floorplan_url) }}"
                                       placeholder="{{ translate('ex') }}: https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product {{ $currentListingType == 'event' ? '' : 'd-none' }}">
                            <div class="form-group">
                                <label for="sponsor_exhibit_url" class="title-color">{{ translate('sponsor_exhibit_url') }}</label>
                                <input type="url" id="sponsor_exhibit_url" name="sponsor_exhibit_url" class="form-control"
                                       value="{{ old('sponsor_exhibit_url', $product->sponsor_exhibit_url) }}"
                                       placeholder="{{ translate('ex') }}: https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product {{ $currentListingType == 'event' ? '' : 'd-none' }}">
                            <div class="form-group">
                                <label for="brochure_event" class="title-color">{{ translate('brochure') }}</label>
                                <input type="file" id="brochure_event" name="brochure" class="form-control"
                                       accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png">
                                @if($product->brochure)
                                    <div class="mt-1">
                                        <a href="{{ $product->brochure_full_url['path'] ?? '#' }}" target="_blank">{{ translate('view_current_file') }}</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product {{ $currentListingType == 'event' ? '' : 'd-none' }}">
                            <div class="form-group">
                                <label for="event_days" class="title-color">{{ translate('event_days') }}</label>
                                <input type="text" id="event_days" name="event_days" class="form-control"
                                       value="{{ old('event_days', $product->event_days) }}" placeholder="{{ translate('ex') }}: 2">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product {{ $currentListingType == 'event' ? '' : 'd-none' }}">
                            <div class="form-group">
                                <label for="event_industry_brands" class="title-color">{{ translate('industry_brands') }}</label>
                                <input type="text" id="event_industry_brands" name="event_industry_brands" class="form-control"
                                       value="{{ old('event_industry_brands', $product->event_industry_brands) }}" placeholder="{{ translate('ex') }}: 100+">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product {{ $currentListingType == 'event' ? '' : 'd-none' }}">
                            <div class="form-group">
                                <label for="event_speakers_count" class="title-color">{{ translate('speakers') }}</label>
                                <input type="text" id="event_speakers_count" name="event_speakers_count" class="form-control"
                                       value="{{ old('event_speakers_count', $product->event_speakers_count) }}" placeholder="{{ translate('ex') }}: 50+">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product {{ $currentListingType == 'event' ? '' : 'd-none' }}">
                            <div class="form-group">
                                <label for="event_audience" class="title-color">{{ translate('audience') }}</label>
                                <input type="text" id="event_audience" name="event_audience" class="form-control"
                                       value="{{ old('event_audience', $product->event_audience) }}" placeholder="{{ translate('ex') }}: Global">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-broker-product {{ $currentListingType == 'broker' ? '' : 'd-none' }}">
                            <div class="form-group">
                                <label for="open_account_url" class="title-color">
                                    {{ translate('open_account_url') }}
                                    <span class="input-required-icon">*</span>
                                </label>
                                <input type="url" id="open_account_url" name="open_account_url" class="form-control"
                                       value="{{ old('open_account_url', $product->open_account_url) }}"
                                       placeholder="{{ translate('ex') }}: https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-broker-product {{ $currentListingType == 'broker' ? '' : 'd-none' }}">
                            <div class="form-group">
                                <label for="contact_broker_url" class="title-color">
                                    {{ translate('contact_broker_url') }}
                                    <span class="input-required-icon">*</span>
                                </label>
                                <input type="url" id="contact_broker_url" name="contact_broker_url" class="form-control"
                                       value="{{ old('contact_broker_url', $product->contact_broker_url) }}"
                                       placeholder="{{ translate('ex') }}: https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 show-for-broker-product {{ $currentListingType == 'broker' ? '' : 'd-none' }}">
                            <div class="form-group">
                                <label for="brochure_broker" class="title-color">{{ translate('download_brochure') }}</label>
                                <input type="file" id="brochure_broker" name="brochure" class="form-control"
                                       accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png">
                                @if($product->brochure)
                                    <div class="mt-1">
                                        <a href="{{ $product->brochure_full_url['path'] ?? '#' }}" target="_blank">{{ translate('view_current_file') }}</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if($brandSetting)
                            <div class="col-md-6 col-lg-4 col-xl-3 physical_product_show">
                                <div class="form-group">
                                    <label class="title-color">
                                        {{ translate('brand') }}
                                        <span class="input-required-icon">*</span>
                                    </label>
                                    <select
                                        class="js-example-basic-multiple js-states js-example-responsive form-control"
                                        name="brand_id">
                                        <option value="{{null}}" selected disabled>---{{ translate('select') }}---
                                        </option>
                                        @foreach($brands as $brand)
                                            <option
                                                value="{{ $brand['id']}}" {{ $brand['id']==$product['brand_id'] ? 'selected' : ''}} >{{ $brand['defaultName']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        {{-- Product Type is fixed (Digital-only for new selections); legacy physical
                             products keep their stored value here so unrelated edits don't change it. --}}
                        <input type="hidden" id="product_type" name="product_type"
                               value="{{ old('product_type', $product['product_type']) }}"
                               {{ $currentListingType == 'product' ? '' : 'disabled' }}>
                        <input type="hidden" id="product_type_override" name="product_type"
                               value="{{ $currentListingType != 'product' ? $currentListingType : '' }}"
                               {{ $currentListingType == 'product' ? 'disabled' : '' }}>

                        <div class="col-md-6 col-lg-4 col-xl-3 digital-product-sections-show">
                            <div class="form-group">
                                <label class="title-color">
                                    {{ translate("Author") }}/{{ translate("Creator") }}/{{ translate("Artist") }}
                                </label>
                                <select class="multiple-select2 form-control" name="authors[]" multiple="multiple" id="mySelect">
                                    @foreach($digitalProductAuthors as $authors)
                                        <option value="{{ $authors['name'] }}" {{ in_array($authors['id'], $productAuthorIds) ? 'selected' : '' }}>{{ $authors['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 col-xl-3 digital-product-sections-show">
                            <div class="form-group">
                                <label class="title-color">{{ translate("Publishing_House") }}</label>
                                <select class="multiple-select2 form-control" name="publishing_house[]" multiple="multiple">
                                    @foreach($publishingHouseList as $publishingHouse)
                                        <option value="{{ $publishingHouse['name'] }}"
                                            {{ in_array($publishingHouse['id'], $productPublishingHouseIds) ? 'selected' : '' }}>{{ $publishingHouse['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 col-xl-3" id="digital_product_type_show">
                            <div class="form-group">
                                <label for="digital_product_type"
                                       class="title-color">{{ translate("delivery_type") }}</label>
                                <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                      title="{{
                                      translate('for_Ready_Product_deliveries,_customers_can_pay_&_instantly_download_pre-uploaded_digital_products').' '.
                                      translate('For_Ready_After_Sale_deliveries,_customers_pay_first_then_vendor_uploads_the_digital_products_that_become_available_to_customers_for_download') }}">
                                    <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                </span>
                                <select name="digital_product_type" id="digital_product_type" class="form-control"
                                        required>
                                    <option value="{{ old('category_id') }}"
                                            {{ !$product['digital_product_type'] ? 'selected' : ''}} disabled>
                                        ---{{ translate('select') }}---
                                    </option>
                                    <option
                                        value="ready_after_sell" {{ $product['digital_product_type'] == 'ready_after_sell' ? 'selected' : ''}}>{{ translate("ready_After_Sell") }}</option>
                                    <option
                                        value="ready_product" {{ $product['digital_product_type'] == 'ready_product' ? 'selected' : ''}}>{{ translate("ready_Product") }}</option>
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
                                    <option value="manual" {{ ($product['delivery_mode'] ?? 'manual') == 'manual' ? 'selected' : '' }}>{{ translate("manual") }}</option>
                                    <option value="auto" {{ ($product['delivery_mode'] ?? 'manual') == 'auto' ? 'selected' : '' }}>{{ translate("auto") }}</option>
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
                                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                        </span>
                                    </span>
                                    <span class="style-one-pro cursor-pointer user-select-none text--primary action-onclick-generate-number" data-input="#generate_number">
                                        {{ translate('generate_code') }}
                                    </span>
                                </label>

                                <input type="text" id="generate_number" name="code" class="form-control"
                                       value="{{request('product-gallery') ? ' ':$product->code}}" placeholder="{{translate('4FOITO')}}" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3 physical_product_show">
                            <div class="form-group">
                                <label class="title-color">{{ translate('unit') }}</label>
                                <select
                                    class="js-example-basic-multiple js-states js-example-responsive form-control"
                                    name="unit">
                                    @foreach(units() as $unit)
                                        <option
                                            value={{ $unit}} {{ $product['unit'] == $unit ? 'selected' : ''}}>{{ $unit }}</option>
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
                                <input type="text" class="form-control" name="tags"
                                       value="@foreach($product['tags'] as $tag) {{$tag->tag.','}} @endforeach"
                                       data-role="tagsinput">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3 rest-part show-for-product-radio {{ $currentListingType == 'product' ? '' : 'd-none' }}">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="tio-user-big"></i>
                        <h4 class="mb-0">{{ translate('Pricing_&_others') }}</h4>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-6 col-lg-4 col-xl-3 d-none">
                            <div class="form-group">
                                <div class="d-flex gap-2">
                                    <label class="title-color">
                                        {{ translate('purchase_price') }}
                                        ({{ getCurrencySymbol(currencyCode: getCurrencyCode()) }})
                                    </label>

                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('add_the_purchase_price_for_this_product') }}.">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </div>

                                <input type="number" min="0" step="0.01"
                                       placeholder="{{ translate('purchase_price') }}"
                                       name="purchase_price" class="form-control"
                                       value={{ usdToDefaultCurrency($product['purchase_price']) }} required>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <div class="d-flex gap-2">
                                    <label class="title-color">
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
                                       placeholder="{{ translate('unit_price') }}"
                                       name="unit_price" class="form-control"
                                       value={{ usdToDefaultCurrency($product['unit_price']) }} required>
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

                                <input type="number" min="1" value="{{ $product['minimum_order_qty'] ?: 1 }}" step="1"
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
                                <input type="number" min="0" value="{{ $product['current_stock'] == 999999999 ? 0 : $product['current_stock'] }}" step="1"
                                       placeholder="{{ translate('quantity') }}"
                                       name="current_stock" id="current_stock" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <div class="d-flex gap-2">
                                    <label class="title-color" for="discount_Type">
                                        {{ translate('discount_Type') }}
                                    </label>

                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('if_Flat,_discount_amount_will_be_set_as_fixed_amount._If_Percentage,_discount_amount_will_be_set_as_percentage.') }}">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </div>

                                <select class="form-control" name="discount_type" id="discount_type">
                                    <option
                                        value="flat" {{ $product['discount_type']=='flat'?'selected':''}}>{{ translate('flat') }}</option>
                                    <option
                                        value="percent" {{ $product['discount_type']=='percent'?'selected':''}}>{{ translate('percent') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <div class="d-flex gap-2">
                                    <label class="title-color" for="discount">
                                        {{ translate('discount_amount') }}
                                        <span class="discount_amount_symbol">({{ $product->discount_type=='flat'? getCurrencySymbol(currencyCode: getCurrencyCode()) : '%' }})</span>
                                    </label>

                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('add_the_discount_amount_in_percentage_or_a_fixed_value_here') }}.">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </div>

                                <input type="number" min="0"
                                       value="{{ $product->discount_type=='flat'?usdToDefaultCurrency($product->discount): $product->discount}}" step="0.01"
                                       placeholder="{{ translate('ex: 5') }}" name="discount" id="discount"
                                       class="form-control" required>
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

                                <input type="number" min="0" value={{ $product->tax ?? 0 }} step="0.01"
                                       placeholder="{{ translate('tax') }}" name="tax" id="tax"
                                       class="form-control" required>
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
                                    <option
                                        value="include" {{ $product->tax_model == 'include' ? 'selected':'' }}>{{ translate("include_with_product") }}</option>
                                    <option
                                        value="exclude" {{ $product->tax_model == 'exclude' ? 'selected':'' }}>{{ translate("exclude_with_product") }}</option>
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

                                <input type="number" min="0" value="{{ usdToDefaultCurrency($product->shipping_cost) }}"
                                       step="0.01"
                                       placeholder="{{ translate('shipping_cost') }}"
                                       name="shipping_cost" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6 physical_product_show" id="shipping_cost_multi">
                            <div class="form-group">
                                <div
                                    class="form-control h-auto min-form-control-height d-flex align-items-center flex-wrap justify-content-between gap-2">
                                    <div class="d-flex gap-2">
                                        <label class="title-color text-capitalize"
                                               for="shipping_cost">{{ translate('shipping_cost_multiply_with_quantity') }}</label>

                                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                              title="{{ translate('if_enabled,_the_shipping_charge_will_increase_with_the_product_quantity') }}">
                                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                        </span>
                                    </div>

                                    <div>
                                        <label class="switcher">
                                            <input class="switcher_input" type="checkbox" name="multiply_qty"
                                                   id="" {{ $product['multiply_qty'] == 1?'checked':'' }}>
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
                                        @if($product->digital_product_file_types)
                                            <option value="{{ $FileType }}" {{ in_array($FileType, $product->digital_product_file_types) ? 'selected':'' }}>
                                                {{ translate($FileType) }}
                                            </option>
                                        @else
                                            <option value="{{ $FileType }}">{{ translate($FileType) }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if($product->digital_product_file_types && count($product->digital_product_file_types) > 0)
                            @foreach($product->digital_product_file_types as $digitalProductFileTypes)
                                <div class="col-sm-6 col-md-4 col-xxl-3 extension-choice-section">
                                    <div class="form-group">
                                        <input type="hidden" name="extensions_type[]" value="{{ $digitalProductFileTypes }}">
                                        <label class="title-color">
                                            {{ $digitalProductFileTypes }}
                                        </label>
                                        <input type="text" name="extensions[]" value="{{ $digitalProductFileTypes }}" hidden>
                                        <div class="">
                                            @if($product->digital_product_extensions && isset($product->digital_product_extensions[$digitalProductFileTypes]))
                                                <input type="text" class="form-control" name="extensions_options_{{ $digitalProductFileTypes }}[]"
                                                       placeholder="{{ translate('enter_choice_values') }}" data-role="tagsinput"
                                                       value="@foreach($product->digital_product_extensions[$digitalProductFileTypes] as $extensions){{$extensions.','}}@endforeach"
                                                       onchange="getUpdateDigitalVariationFunctionality()">
                                            @else
                                                <input type="text" class="form-control" name="extensions_options_{{ $digitalProductFileTypes }}[]"
                                                       placeholder="{{ translate('enter_choice_values') }}" data-role="tagsinput"
                                                       onchange="getUpdateDigitalVariationFunctionality()">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                    </div>
                </div>
            </div>

            <div class="card mt-3 rest-part" id="digital-product-variation-section"></div>

                <div class="card mt-3 rest-part physical_product_show">
                    <div class="card-header">
                        <div class="d-flex gap-2">
                            <i class="tio-user-big"></i>
                            <h4 class="mb-0">{{ translate('product_variation_setup') }}</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="mb-3 d-flex align-items-center gap-2">
                                        <label class="mb-0 title-color">
                                            {{ translate('select_colors') }} :
                                        </label>
                                        <label class="switcher">
                                            <input type="checkbox" class="switcher_input" id="product-color-switcher"
                                                   name="colors_active" {{count($product['colors'])>0?'checked':''}}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>

                                    <select
                                        class="js-example-basic-multiple js-states js-example-responsive form-control color-var-select"
                                        name="colors[]" multiple="multiple"
                                        id="colors-selector" {{ count($product['colors'])>0?'':'disabled' }}>
                                        @foreach ($colors as $key => $color)
                                            <option
                                                value={{ $color->code }} {{in_array($color->code,$product['colors'])?'selected':''}}>
                                                {{ $color['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="choice_attributes" class="pb-1 title-color">
                                        {{ translate('select_attributes') }} :
                                    </label>
                                    <select
                                        class="js-example-basic-multiple js-states js-example-responsive form-control"
                                        name="choice_attributes[]" id="choice_attributes" multiple="multiple">
                                        @php($productAttributeIds = json_decode($product['attributes'], true) ?? [])
                                        @foreach ($attributes as $key => $attribute)
                                            @if(count($productAttributeIds) > 0)
                                                <option value="{{ $attribute['id'] }}" {{ in_array($attribute->id, $productAttributeIds) ? 'selected' : '' }}>
                                                    {{ $attribute['name']}}
                                                </option>
                                            @else
                                                <option value="{{ $attribute['id']}}">{{ $attribute['name']}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12 mt-2 mb-2">
                                <div class="row customer_choice_options mt-2" id="customer_choice_options">
                                    @include('vendor-views.product.partials._choices',['choice_no'=>json_decode($product['attributes']) ?? [],'choice_options'=>json_decode($product['choice_options'],true) ?? []])
                                </div>

                                <div class="sku_combination table-responsive form-group mt-2" id="sku_combination">
                                    @include('vendor-views.product.partials._edit-sku-combinations', ['combinations'=>json_decode($product['variation'],true) ?? []])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <div class="mt-3 rest-part">
                <div class="product-image-wrapper">
                    <div class="item-1">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                    <div>
                                        <label for="name" class="title-color text-capitalize font-weight-bold mb-0">
                                            {{ translate('product_thumbnail') }}
                                            <span class="input-required-icon">*</span>
                                        </label>
                                        <span
                                            class="badge badge-soft-info">{{ THEME_RATIO[theme_root_path()]['Product Image'] }}</span>
                                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                              title="{{ translate('add_your_products_thumbnail_in') }} {{ "JPG, PNG or JPEG" }} {{ translate('format_within') }} 2MB">
                                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <div class="custom_upload_input">
                                        <input type="file" name="image" class="custom-upload-input-file action-upload-color-image" id=""
                                               data-imgpreview="pre_img_viewer"
                                               accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">

                                        @if ($product->thumbnail_full_url['path'])
                                            <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d-flex">
                                                <i class="tio-delete"></i>
                                            </span>
                                        @else
                                            <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d--none">
                                                <i class="tio-delete"></i>
                                            </span>
                                        @endif

                                        <div class="img_area_with_preview position-absolute z-index-2">
                                            <img id="pre_img_viewer" class="h-auto aspect-1 bg-white  " alt=""
                                                 src="{{ getStorageImages(path:$product->thumbnail_full_url,type:'backend-product') }}">
                                        </div>
                                        <div
                                            class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                            <div class="d-flex flex-column justify-content-center align-items-center">
                                                <img alt=""
                                                     src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}"
                                                     class="w-75">
                                                <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-muted mt-2">{{ translate('image_format') }} : {{ "Jpg, png, jpeg, webp" }} <br>
                                        {{ translate('image_size') }} : {{ translate('max') }} {{ "2 MB" }}</p>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="item-2 color_image_column d-none">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <label for="name"
                                           class="title-color text-capitalize font-weight-bold mb-0">{{ translate('colour_wise_product_image') }}</label>
                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                          title="{{ translate('add_color_wise_product_images_here') }}.">
                                    <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                </span>
                                </div>
                                <p class="text-muted">{{ translate('must_upload_colour_wise_images_first') }} {{ translate('colour_is_shown_in_the_image_section_top_right.') }} </p>

                                <div id="color-wise-image-area" class="row g-2 mb-4">
                                    <div class="col-12">
                                        <div class="row g-2" id="color_wise_existing_image"></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="row g-2" id="color-wise-image-section"></div>
                                    </div>
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

                                <div class="coba-area">

                                    <div class="row g-2" id="additional_Image_Section">

                                        @if(count($product->colors) == 0)
                                            @foreach ($product->images_full_url as $key => $photo)
                                                @php($unique_id = rand(1111,9999))

                                                <div class="col-sm-12 col-md-4" id="addition-image-section-{{$key}}">
                                                    <div
                                                        class="custom_upload_input custom-upload-input-file-area position-relative border-dashed-2 aspect-1">
                                                        @if(request('product-gallery'))
                                                            <button type="button" class="delete_file_input_css btn btn-outline-danger btn-sm square-btn remove-addition-image-for-product-gallery" data-section-remove-id="addition-image-section-{{$key}}">
                                                                <i class="tio-delete"></i>
                                                            </button>
                                                        @else
                                                            <a class="delete_file_input_css btn btn-outline-danger btn-sm square-btn"
                                                               href="{{ route('vendor.products.delete-image',['id'=>$product['id'],'name'=>$photo['key']]) }}">
                                                                <i class="tio-delete"></i>
                                                            </a>
                                                        @endif

                                                        <div
                                                            class="img_area_with_preview position-absolute z-index-2 border-0">
                                                            <img id="additional_Image_{{ $unique_id }}" alt=""
                                                                 class="h-auto aspect-1 bg-white  "
                                                                 src="{{ getStorageImages(path:$photo,type: 'backend-product') }}">
                                                            @if(request('product-gallery'))
                                                                <input type="text" name="existing_images[]" value="{{$photo['key']}}" hidden>
                                                            @endif
                                                        </div>
                                                        <div
                                                            class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                                            <div
                                                                class="d-flex flex-column justify-content-center align-items-center">
                                                                <img alt="" src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}"
                                                                     class="w-75">
                                                                <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            @if($product->color_image)
                                                @foreach ($product->color_images_full_url as $photo)
                                                    @if($photo['color'] == null)
                                                        @php($unique_id = rand(1111,9999))
                                                        <div class="col-sm-12 col-md-4"  id="addition-image-section-{{$key}}">
                                                            <div
                                                                class="custom_upload_input custom-upload-input-file-area position-relative border-dashed-2 aspect-1">
                                                                @if(request('product-gallery'))
                                                                    <button type="button" class="delete_file_input_css btn btn-outline-danger btn-sm square-btn remove-addition-image-for-product-gallery" data-section-remove-id="addition-image-section-{{$key}}">
                                                                        <i class="tio-delete"></i>
                                                                    </button>
                                                                @else
                                                                    <a class="delete_file_input_css btn btn-outline-danger btn-sm square-btn"
                                                                       href="{{ route('vendor.products.delete-image',['id'=>$product['id'],'name'=>$photo['image_name']['key'],'color'=>'null']) }}">
                                                                        <i class="tio-delete"></i>
                                                                    </a>
                                                                @endif

                                                                <div
                                                                    class="img_area_with_preview position-absolute z-index-2 border-0">
                                                                    <img id="additional_Image_{{ $unique_id }}" alt=""
                                                                         class="h-auto aspect-1 bg-white  "
                                                                         src="{{ getStorageImages(path: $photo['image_name'],type:'backend-product')}}">
                                                                    @if(request('product-gallery'))
                                                                        <input type="text" name="existing_images[]" value="{{$photo['image_name']['key']}}" hidden>
                                                                    @endif
                                                                </div>
                                                                <div
                                                                    class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                                                    <div
                                                                        class="d-flex flex-column justify-content-center align-items-center">
                                                                        <img alt="" src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}"
                                                                             class="w-75">
                                                                        <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                @foreach ($product->images_full_url as $key => $photo)
                                                    @php($unique_id = rand(1111,9999))
                                                    <div class="col-sm-12 col-md-4"  id="addition-image-section-{{$key}}">
                                                        <div class="custom_upload_input custom-upload-input-file-area position-relative border-dashed-2 aspect-1">
                                                            @if(request('product-gallery'))
                                                                <button type="button" class="delete_file_input_css btn btn-outline-danger btn-sm square-btn remove-addition-image-for-product-gallery" data-section-remove-id="addition-image-section-{{$key}}">
                                                                    <i class="tio-delete"></i>
                                                                </button>
                                                            @else
                                                                <a class="delete_file_input_css btn btn-outline-danger btn-sm square-btn"
                                                                   href="{{ route('vendor.products.delete-image',['id'=>$product['id'],'name'=>$photo['key']]) }}">
                                                                    <i class="tio-delete"></i>
                                                                </a>
                                                            @endif
                                                            <div
                                                                class="img_area_with_preview position-absolute z-index-2 border-0">
                                                                <img id="additional_Image_{{ $unique_id }}" alt=""
                                                                     class="h-auto aspect-1 bg-white  "
                                                                     src="{{ getStorageImages(path:$photo ,type: 'backend-product')}}">
                                                                @if(request('product-gallery'))
                                                                    <input type="text" name="existing_images[]" value="{{$photo['key']}}" hidden>
                                                                @endif
                                                            </div>
                                                            <div
                                                                class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                                                <div class="d-flex flex-column justify-content-center align-items-center">
                                                                    <img alt="" class="w-75"
                                                                         src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}">
                                                                    <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        @endif
                                        <div class="col-sm-12 col-md-4">
                                            <div class="custom_upload_input position-relative border-dashed-2 aspect-1">
                                                <input type="file" name="images[]" class="custom-upload-input-file action-add-more-image"
                                                       data-index="1" data-imgpreview="additional_Image_1"
                                                       accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*"
                                                       data-target-section="#additional_Image_Section">

                                                <span class="delete_file_input delete_file_input_section btn btn-outline-danger btn-sm square-btn d-none">
                                                    <i class="tio-delete"></i>
                                                </span>

                                                <div class="img_area_with_preview position-absolute z-index-2 border-0">
                                                    <img id="additional_Image_1" class="h-auto aspect-1 bg-white d-none" alt=""
                                                         src="">
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
                                        <div class="image-uploader__title line--limit-2" data-default="{{ translate('Upload_File') }}">
                                            @if ($product->preview_file_full_url['path'])
                                                {{ $product->preview_file }}
                                            @elseif(request('product-gallery') && $product?->preview_file)
                                                {{ translate('Upload_File') }}
                                            @else
                                                {{ translate('Upload_File') }}
                                            @endif

                                            @if(request('product-gallery'))
                                                <input type="hidden" name="existing_preview_file" value="{{ $product?->preview_file }}">
                                                <input type="hidden" name="existing_preview_file_storage_type" value="{{ $product?->preview_file_storage_type }}">
                                            @endif
                                        </div>
                                    </div>

                                    @if ($product->preview_file_full_url['path'])
                                        <span class="btn btn-outline-danger btn-sm square-btn collapse show zip-remove-btn delete_preview_file_input"
                                              data-route="{{ route('vendor.products.delete-preview-file') }}">
                                            <i class="tio-delete"></i>
                                        </span>
                                    @else
                                        <span class="btn btn-outline-danger btn-sm square-btn collapse zip-remove-btn">
                                            <i class="tio-delete"></i>
                                        </span>
                                    @endif
                                </div>
                                <p class="text-muted mt-2 fs-12">
                                    {{ translate('Format') }} : {{ " pdf, mp4, mp3" }}
                                    <br>
                                    {{ translate('image_size') }} : {{ translate('max') }} {{ "10 MB" }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="color_image" value="{{ json_encode($product->color_images_full_url) }}">
                <input type="hidden" id="images" value="{{ json_encode($product->images_full_url) }}">
                <input type="hidden" id="product_id" name="product_id" value="{{ $product['id'] }}">
                <input type="hidden" id="remove_url" value="{{ route('vendor.products.delete-image') }}">
            </div>

            <div class="card mt-3 rest-part">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="tio-globe"></i>
                        <h4 class="mb-0">{{ translate('product_preview_url') }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="title-color mb-0">{{ translate('preview_url') }}</label>
                        <span class="text-info"> ( {{ translate('optional') }} )</span>
                    </div>
                    <input type="text" value="{{ $product['preview_url'] }}" name="preview_url"
                           placeholder="{{ translate('ex') }} : https://themeatelier.net/site-templates/wonted/index.html"
                           class="form-control">
                </div>
            </div>
            {{-- END: New Preview URL Section --}}

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
                        <span class="text-info"> ( {{ translate('optional_please_provide_embed_link_not_direct_link') }}. )</span>
                    </div>
                    <input type="text" value="{{ $product['video_url']}}" name="video_url"
                           placeholder="{{ translate('ex') }} : https://www.youtube.com/embed/5R06LRdUCSE"
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
                                <input type="text" name="meta_title" value="{{ $product?->seoInfo?->title ?? $product->meta_title }}" placeholder=""
                                       class="form-control">
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
                                <textarea rows="4" type="text" name="meta_description"
                                          class="form-control">{{$product?->seoInfo?->description ?? $product->meta_description}}</textarea>
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
                                                   class="custom-upload-input-file meta-img action-upload-color-image" id=""
                                                   data-imgpreview="pre_meta_image_viewer"
                                                   accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">

                                            @if ($product?->seoInfo?->image_full_url['path'] || $product->meta_image_full_url['path'])
                                                <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d-flex">
                                                    <i class="tio-delete"></i>
                                                </span>
                                            @else
                                                <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d--none">
                                                    <i class="tio-delete"></i>
                                                </span>
                                            @endif

                                            <div class="img_area_with_preview position-absolute z-index-2 d-flex">
                                                <img id="pre_meta_image_viewer" class="h-auto aspect-1 bg-white" alt=""
                                                     src="{{ getStorageImages(path: $product?->seoInfo?->image_full_url['path'] ? $product?->seoInfo?->image_full_url : $product->meta_image_full_url,type: 'backend-banner') }}">
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

                    @include('vendor-views.product.partials._seo-update-section')
                </div>
            </div>
            <input type="hidden" name="vendor_tier_id" value="{{ $selectedTier?->id }}">
            <div class="d-flex justify-content-end mt-3">
                {{-- CRITICAL FIX: type="button" prevents default submission so JS can handle it --}}
                <button type="button" class="btn btn--primary px-5 product-add-requirements-check">
                    @if($product->request_status == 2)
                        {{ translate('resubmit') }}
                    @else
                        {{ translate(request('product-gallery') ? 'submit' : 'update') }}
                    @endif
                </button>
            </div>
            @if(request('product-gallery'))
                <input hidden name="existing_thumbnail" value="{{$product->thumbnail_full_url['key']}}">
                <input hidden name="existing_meta_image" value="{{$product?->seoInfo?->image_full_url['key'] ?? $product->meta_image_full_url['key']}}">
            @endif

        </form>
    </div>

    <span id="route-vendor-products-sku-combination" data-url="{{ route('vendor.products.sku-combination') }}"></span>
    <span id="route-vendor-products-digital-variation-combination" data-url="{{ route('vendor.products.digital-variation-combination') }}"></span>
    <span id="route-vendor-products-digital-variation-file-delete" data-url="{{ route('vendor.products.digital-variation-file-delete') }}"></span>
    <span id="image-path-of-product-upload-icon" data-path="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}"></span>
    <span id="image-path-of-product-upload-icon-two" data-path="{{ dynamicAsset(path: 'public/assets/back-end/img/400x400/img2.jpg') }}"></span>
    <span id="message-enter-choice-values" data-text="{{ translate('enter_choice_values') }}"></span>
    <span id="message-upload-image" data-text="{{ translate('upload_Image') }}"></span>
    <span id="message-are-you-sure" data-text="{{ translate('are_you_sure') }}"></span>
    <span id="message-yes-word" data-text="{{ translate('yes') }}"></span>
    <span id="message-no-word" data-text="{{ translate('no') }}"></span>
    <span id="message-want-to-add-or-update-this-product" data-text="{{ translate('want_to_update_this_product') }}"></span>
    <span id="message-please-only-input-png-or-jpg" data-text="{{ translate('please_only_input_png_or_jpg_type_file') }}"></span>
    <span id="message-product-added-successfully" data-text="{{ translate('product_added_successfully') }}"></span>
    <span id="message-discount-will-not-larger-then-variant-price" data-text="{{ translate('the_discount_price_will_not_larger_then_Variant_Price') }}"></span>
    <span id="system-currency-code" data-value="{{ getCurrencySymbol(currencyCode: getCurrencyCode()) }}"></span>
    <span id="system-session-direction" data-value="{{ Session::get('direction') }}"></span>

    <span id="message-file-size-too-big" data-text="{{ translate('file_size_too_big') }}"></span>

    {{-- Tier Plan Data for Frontend Validation --}}
    <span id="tier-plan-name" data-value="{{ $tierDetails->name ?? '' }}"></span>
    <span id="tier-plan-id" data-value="{{ $tierDetails->id ?? '' }}"></span>
    <span id="has-active-tier" data-value="{{ ($selectedTier?->id) ? 'true' : 'false' }}"></span>
    <span id="tier-price-min" data-value="{{ $tierDetails->price_threshold_min_usd ?? '' }}"></span>
    <span id="tier-price-max" data-value="{{ $tierDetails->price_threshold_max_usd ?? '' }}"></span>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/tags-input.min.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/spartan-multi-image-picker.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.js') }}"></script>
    {{-- Product Validation Service (must load before product-add-update.js) --}}
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/vendor/product-add-update-validation.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/shared/image-resize.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/vendor/product-add-update.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/vendor/category-repeater.js') }}"></script>

    <script>
        "use strict";

        let colors = {{ count($product->colors) }};
        let imageCount = {{15-count(json_decode($product->images)) }};
        let thumbnail = '{{productImagePath('thumbnail').'/'.$product->thumbnail ?? dynamicAsset(path: 'public/assets/back-end/img/400x400/img2.jpg') }}';
        $(function () {
            if (imageCount > 0) {
                $("#coba").spartanMultiImagePicker({
                    fieldName: 'images[]',
                    maxCount: colors === 0 ? 15 : imageCount,
                    rowHeight: 'auto',
                    groupClassName: 'col-6 col-md-4 col-xl-3 col-xxl-2',
                    maxFileSize: '',
                    placeholderImage: {
                        image: '{{ dynamicAsset(path: "public/assets/back-end/img/400x400/img2.jpg") }}',
                        width: '100%',
                    },
                    dropFileLabel: "Drop Here",
                    onAddRow: function (index, file) {
                    },
                    onRenderedPreview: function (index) {
                    },
                    onRemoveRow: function (index) {
                    },
                    onExtensionErr: function () {
                        toastr.error(messagePleaseOnlyInputPNGOrJPG, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    },
                    onSizeErr: function () {
                        toastr.error(messageFileSizeTooBig, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                });
            }

            $("#thumbnail").spartanMultiImagePicker({
                fieldName: 'image',
                maxCount: 1,
                rowHeight: 'auto',
                groupClassName: 'col-12',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{ productImagePath('thumbnail').'/'. $product->thumbnail ?? dynamicAsset(path: 'public/assets/back-end/img/400x400/img2.jpg') }}',
                    width: '100%',
                },
                dropFileLabel: "Drop Here",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function () {
                    toastr.error(messagePleaseOnlyInputPNGOrJPG, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function () {
                    toastr.error(messageFileSizeTooBig, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });

        });

        setTimeout(function () {
            $('.call-update-sku').on('change', function () {
                getUpdateSKUFunctionality();
            });
        }, 2000)

        function colorWiseImageFunctionality(t) {
            let colors = t.val();
            let color_image = $('#color_image').val() ? $.parseJSON($('#color_image').val()) : [];
            let images = $.parseJSON($('#images').val());
            let product_id = $('#product_id').val();
            let remove_url = $('#remove_url').val();

            let color_image_value = $.map(color_image, function (item) {
                return item.color;
            });

            $('#color_wise_existing_image').html('')
            $('#color-wise-image-section').html('')

            $.each(colors, function (key, value) {
                let value_id = value.replace('#', '');
                let in_array_image = $.inArray(value_id, color_image_value);
                let input_image_name = "color_image_" + value_id;
                @if(request('product-gallery'))
                $.each(color_image, function (color_key, color_value) {
                    if ((in_array_image !== -1) && (color_value['color'] === value_id)) {
                        let image_name = color_value['image_name'];
                        let exist_image_html = `
                            <div class="col-6 col-md-4 col-xl-4 color-image-`+color_value['color']+`">
                                <div class="position-relative p-2 border-dashed-2">
                                    <div class="upload--icon-btns d-flex gap-2 position-absolute z-index-2 p-2" >
                                        <button type="button" class="btn btn-square text-white btn-sm" style="background: #${color_value['color']}"><i class="tio-done"></i></button>
                                        <button type="button" class="btn btn-outline-danger btn-sm square-btn remove-color-image-for-product-gallery" data-color="`+color_value['color']+`"><i class="tio-delete"></i></button>
                                    </div>
                                    <img class="w-100 aspect-ratio-1-img" height="auto"
                                        onerror="this.src='{{ dynamicAsset(path: 'public/assets/front-end/img/image-place-holder.png') }}'"
                                        src="${image_name['path']}"
                                        alt="Product image">
                                        <input type="text" name="color_image_`+color_value['color']+`[]" value="`+image_name['key']+`" hidden>
                                </div>
                            </div>`;
                        $('#color_wise_existing_image').append(exist_image_html)
                    }
                });
                @else
                $.each(color_image, function (color_key, color_value) {
                    if ((in_array_image !== -1) && (color_value['color'] === value_id)) {
                        let image_name = color_value['image_name'];
                        let exist_image_html = `
                            <div class="col-6 col-md-4 col-xl-4">
                                <div class="position-relative p-2 border-dashed-2">
                                    <div class="upload--icon-btns d-flex gap-2 position-absolute z-index-2 p-2" >
                                        <button type="button" class="btn btn-square text-white btn-sm" style="background: #${color_value['color']}"><i class="tio-done"></i></button>
                                        <a href="` + remove_url + `?id=` + product_id + `&name=` + image_name['key'] + `&color=` + color_value['color'] + `"
                                    class="btn btn-outline-danger btn-sm square-btn"><i class="tio-delete"></i></a>
                                    </div>
                                    <img class="w-100 aspect-ratio-1-img" height="auto"
                                        onerror="this.src='{{ dynamicAsset(path: 'public/assets/front-end/img/image-place-holder.png') }}'"
                                        src="${image_name['path']}"
                                        alt="Product image">
                                </div>
                            </div>`;
                        $('#color_wise_existing_image').append(exist_image_html)
                    }
                });
                @endif
            });

            $.each(colors, function (key, value) {
                let value_id = value.replace('#', '');
                let in_array_image = $.inArray(value_id, color_image_value);
                let input_image_name = "color_image_" + value_id;

                if (in_array_image === -1) {
                    let html = `<div class='col-6 col-md-4 col-xl-4'>
                                        <div class="position-relative p-2 border-dashed-2">
                                            <label style='border-radius: 3px; cursor: pointer; text-align: center; overflow: hidden; position : relative; display: flex; align-items: center; margin: auto; justify-content: center; flex-direction: column;'>
                                            <span class="upload--icon" style="background: ${value}">
                                            <i class="tio-edit"></i>
                                                <input type="file" name="` + input_image_name + `" id="` + value_id + `" class="d-none" accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required="">
                                            </span>

                                            <div class="h-100 top-0 aspect-1 w-100 d-flex align-content-center justify-content-center overflow-hidden">
                                                <div class="d-flex flex-column justify-content-center align-items-center">
                                                    <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}" class="w-100 aspect-ratio-1-img">
                                                    <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                                </div>
                                            </div>
                                        </label>
                                        </div>
                                        </div>`;
                    $('#color-wise-image-section').append(html)

                    $("#color-wise-image-section input[type='file']").each(function () {

                        let thisElement = $(this).closest('label');

                        function proPicURL(input) {
                            if (input.files && input.files[0]) {
                                let uploadedFile = new FileReader();
                                uploadedFile.onload = function (e) {
                                    thisElement.find('img').attr('src', e.target.result);
                                    thisElement.fadeIn(300);
                                    thisElement.find('h3').hide();
                                };
                                uploadedFile.readAsDataURL(input.files[0]);
                            }
                        }

                        $(this).on("change", function () {
                            proPicURL(this);
                        });
                    });
                }
            });
        }

        $(document).on('click', '.remove-color-image-for-product-gallery', function(event) {
            event.preventDefault();
            let value_id = $(this).data('color');
            let value = '#'+value_id;
            let color = "color_image_" + value_id;
            let html =  `<div class="position-relative p-2 border-dashed-2">
                            <label style='border-radius: 3px; cursor: pointer; text-align: center; overflow: hidden; position : relative; display: flex; align-items: center; margin: auto; justify-content: center; flex-direction: column;'>
                                <span class="upload--icon" style="background: ${value}">
                                <i class="tio-edit"></i>
                                    <input type="file" name="` + color + `" id="` + value_id + `" class="d-none" accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required="">
                                </span>

                                <div class="h-100 top-0 aspect-1 w-100 d-flex align-content-center justify-content-center overflow-hidden">
                                    <div class="d-flex flex-column justify-content-center align-items-center">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}" class="w-100 aspect-ratio-1-img">
                                        <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                                    </div>
                                </div>
                            </label>
                        </div>`;
            $('.color-image-'+value_id).empty().append(html);
            $("#color-wise-image-area input[type='file']").each(function () {

                let thisElement = $(this).closest('label');

                function proPicURL(input) {
                    if (input.files && input.files[0]) {
                        let uploadedFile = new FileReader();
                        uploadedFile.onload = function (e) {
                            thisElement.find('img').attr('src', e.target.result);
                            thisElement.fadeIn(300);
                            thisElement.find('h3').hide();
                        };
                        uploadedFile.readAsDataURL(input.files[0]);
                    }
                }

                $(this).on("change", function () {
                    proPicURL(this);
                });
            });
        })
        $(document).on('click', '.remove-addition-image-for-product-gallery', function(event){
            // This is a bare <button> with no type="" attribute inside #product_form,
            // which HTML defaults to type="submit" — without preventDefault() here,
            // clicking "remove" on an existing gallery image natively submits the
            // whole form (bypassing all AJAX/validation/compression logic), which is
            // what caused "too much loading" and a raw JSON page on save.
            event.preventDefault();
            $('#'+$(this).data('section-remove-id')).remove();
        })


        updateProductQuantity();
    </script>

@endpush
