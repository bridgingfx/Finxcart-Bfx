<div class="card mt-3 rest-part">
    <div class="card-header">
        <div class="d-flex gap-2">
            <i class="fi fi-sr-user"></i>
            <h3 class="mb-0">{{ translate('general_setup') }}</h3>
        </div>
    </div>
    <div class="card-body">
        <div class="row gy-4">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="name" class="form-label">
                        {{ translate('categories') }}
                        <span class="input-required-icon">*</span>
                    </label>

                    <div class="category-row-template d-none">
                        <div class="row gy-2 align-items-center category-row mb-2">
                            <div class="col-md-4">
                                <select class="form-select category-level-select" data-level="category">
                                    <option value="">{{ translate('select_category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category['id'] }}">{{ $category['defaultName'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select category-level-select" data-level="sub_category" disabled>
                                    <option value="">{{ translate('select_Sub_Category') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select category-level-select" data-level="sub_sub_category" disabled>
                                    <option value="">{{ translate('select_Sub_Sub_Category') }}</option>
                                </select>
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-category-row">
                                    <i class="fi fi-rr-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="category-rows-wrapper"
                         data-get-categories-url="{{ url('/admin/products/get-categories') }}"
                         data-get-all-categories-url="{{ route('admin.products.get-all-categories-flat') }}"
                         data-placeholder-category="{{ translate('select_category') }}"
                         data-placeholder-sub-category="{{ translate('select_Sub_Category') }}"
                         data-placeholder-sub-sub-category="{{ translate('select_Sub_Sub_Category') }}"
                         data-placeholder-loading="{{ translate('loading') }}..."
                         data-placeholder-select="{{ translate('Select') }}">
                    </div>

                    <button type="button" id="add-category-row-btn" class="btn btn-sm btn-outline-primary mt-1">
                        <i class="fi fi-rr-plus"></i> {{ translate('add_another_category') }}
                    </button>

                    <script type="application/json" id="selected-category-rows-data">{!! json_encode($selectedCategoryRows ?? []) !!}</script>
                </div>
            </div>
            <div class="col-md-12 show-for-event-or-broker-product d-none">
                <div class="form-group">
                    <label for="external_url" class="form-label">
                        {{ translate('website_url') }}
                        <span class="input-required-icon">*</span>
                    </label>
                    <input type="url" id="external_url" name="external_url" class="form-control"
                           value="{{ old('external_url') }}" required
                           placeholder="{{ translate('ex') }}: https://example.com">
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                <div class="form-group">
                    <label for="book_tickets_url" class="form-label">{{ translate('book_tickets_url') }}</label>
                    <input type="url" id="book_tickets_url" name="book_tickets_url" class="form-control"
                           value="{{ old('book_tickets_url') }}"
                           placeholder="{{ translate('ex') }}: https://example.com">
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                <div class="form-group">
                    <label for="view_floorplan_url" class="form-label">{{ translate('view_floorplan_url') }}</label>
                    <input type="url" id="view_floorplan_url" name="view_floorplan_url" class="form-control"
                           value="{{ old('view_floorplan_url') }}"
                           placeholder="{{ translate('ex') }}: https://example.com">
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                <div class="form-group">
                    <label for="sponsor_exhibit_url" class="form-label">{{ translate('sponsor_exhibit_url') }}</label>
                    <input type="url" id="sponsor_exhibit_url" name="sponsor_exhibit_url" class="form-control"
                           value="{{ old('sponsor_exhibit_url') }}"
                           placeholder="{{ translate('ex') }}: https://example.com">
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                <div class="form-group">
                    <label for="brochure_event" class="form-label">{{ translate('brochure') }}</label>
                    <input type="file" id="brochure_event" name="brochure" class="form-control"
                           accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png">
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3 show-for-broker-product d-none">
                <div class="form-group">
                    <label for="open_account_url" class="form-label">
                        {{ translate('open_account_url') }}
                        <span class="input-required-icon">*</span>
                    </label>
                    <input type="url" id="open_account_url" name="open_account_url" class="form-control"
                           value="{{ old('open_account_url') }}" required
                           placeholder="{{ translate('ex') }}: https://example.com">
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3 show-for-broker-product d-none">
                <div class="form-group">
                    <label for="contact_broker_url" class="form-label">
                        {{ translate('contact_broker_url') }}
                        <span class="input-required-icon">*</span>
                    </label>
                    <input type="url" id="contact_broker_url" name="contact_broker_url" class="form-control"
                           value="{{ old('contact_broker_url') }}" required
                           placeholder="{{ translate('ex') }}: https://example.com">
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3 show-for-broker-product d-none">
                <div class="form-group">
                    <label for="brochure_broker" class="form-label">{{ translate('download_brochure') }}</label>
                    <input type="file" id="brochure_broker" name="brochure" class="form-control"
                           accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png">
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                <div class="form-group">
                    <label for="event_days" class="form-label">{{ translate('event_days') }}</label>
                    <input type="text" id="event_days" name="event_days" class="form-control"
                           value="{{ old('event_days') }}" placeholder="{{ translate('ex') }}: 2">
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                <div class="form-group">
                    <label for="event_industry_brands" class="form-label">{{ translate('industry_brands') }}</label>
                    <input type="text" id="event_industry_brands" name="event_industry_brands" class="form-control"
                           value="{{ old('event_industry_brands') }}" placeholder="{{ translate('ex') }}: 100+">
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                <div class="form-group">
                    <label for="event_speakers_count" class="form-label">{{ translate('speakers') }}</label>
                    <input type="text" id="event_speakers_count" name="event_speakers_count" class="form-control"
                           value="{{ old('event_speakers_count') }}" placeholder="{{ translate('ex') }}: 50+">
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3 show-for-event-product d-none">
                <div class="form-group">
                    <label for="event_audience" class="form-label">{{ translate('audience') }}</label>
                    <input type="text" id="event_audience" name="event_audience" class="form-control"
                           value="{{ old('event_audience') }}" placeholder="{{ translate('ex') }}: Global">
                </div>
            </div>

            <input type="hidden" id="product_type" name="product_type" value="digital">
            <input type="hidden" id="product_type_override" name="product_type" value="" disabled>

            <div class="col-md-6 col-lg-4 col-xl-3 show-for-digital-product">
                <div class="form-group">
                    <label class="form-label">
                        {{ translate("Author") }}/{{ translate("Creator") }}/{{ translate("Artist") }}
                    </label>
                    <select class="custom-select tags" name="authors[]" multiple="multiple" id="mySelect">
                        @foreach($digitalProductAuthors as $authors)
                            <option value="{{ $authors['name'] }}">{{ $authors['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 col-xl-3 show-for-digital-product">
                <div class="form-group">
                    <label class="form-label">{{ translate("Publishing_House") }}</label>
                    <select class="custom-select tags" name="publishing_house[]" multiple="multiple">

                        @foreach($publishingHouseList as $publishingHouse)
                            <option value="{{ $publishingHouse['name'] }}">
                                {{ $publishingHouse['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 col-xl-3 show-for-digital-product">
                <div class="form-group">
                    <label for="digital-product-type-input" class="form-label">
                        {{ translate("delivery_type") }}
                        <span class="input-required-icon">*</span>
                        <span class="tooltip-icon cursor-pointer" data-bs-toggle="tooltip"
                              aria-label="{{
                                        translate('for_Ready_Product_deliveries,_customers_can_pay_&_instantly_download_pre-uploaded_digital_products').' '.
                                        translate('For_Ready_After_Sale_deliveries,_customers_pay_first_then_admin_uploads_the_digital_products_that_become_available_to_customers_for_download') }}"
                              data-bs-title="{{
                                        translate('for_Ready_Product_deliveries,_customers_can_pay_&_instantly_download_pre-uploaded_digital_products').' '.
                                        translate('For_Ready_After_Sale_deliveries,_customers_pay_first_then_admin_uploads_the_digital_products_that_become_available_to_customers_for_download') }}">
                                        <i class="fi fi-sr-info"></i>
                                    </span>
                    </label>
                    <div class="select-wrapper">
                        <select name="digital_product_type" id="digital-product-type-input" class="form-select"
                                required>
                            <option value="ready_after_sell">{{ translate("ready_After_Sell") }}</option>
                            <option value="ready_product">{{ translate("ready_Product") }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 col-xl-3 show-for-digital-product">
                <div class="form-group">
                    <label for="delivery-mode-input" class="form-label">
                        {{ translate("delivery_mode") }}
                        <span class="tooltip-icon cursor-pointer" data-bs-toggle="tooltip"
                              aria-label="{{ translate('auto_marks_the_order_delivered_instantly_after_payment._manual_requires_the_vendor_to_mark_it_delivered_from_their_panel.') }}"
                              data-bs-title="{{ translate('auto_marks_the_order_delivered_instantly_after_payment._manual_requires_the_vendor_to_mark_it_delivered_from_their_panel.') }}">
                            <i class="fi fi-sr-info"></i>
                        </span>
                    </label>
                    <div class="select-wrapper">
                        <select name="delivery_mode" id="delivery-mode-input" class="form-select">
                            <option value="manual" selected>{{ translate("manual") }}</option>
                            <option value="auto">{{ translate("auto") }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="form-group">
                    <label class="form-label d-flex justify-content-between gap-2">
                        <span class="d-flex align-items-center gap-2">
                            {{ translate('product_SKU') }}
                            <span class="input-required-icon">*</span>
                            <span class="tooltip-icon cursor-pointer" data-bs-toggle="tooltip"
                                  aria-label="{{ translate('create_a_unique_product_code_by_clicking_on_the_Generate_Code_button') }}"
                                  data-bs-title="{{ translate('create_a_unique_product_code_by_clicking_on_the_Generate_Code_button') }}">
                                <i class="fi fi-sr-info"></i>
                            </span>
                        </span>
                        <span
                            class="style-one-pro cursor-pointer user-select-none text-primary action-onclick-generate-number"
                            data-input="#generate-sku-code">
                            {{ translate('generate_code') }}
                        </span>
                    </label>
                    <input type="text" minlength="6" id="generate-sku-code" name="code"
                           class="form-control" value="{{ old('code') }}"
                           placeholder="{{ translate('ex').': 161183'}}" required>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3 show-for-physical-product">
                <div class="form-group">
                    <label class="form-label">
                        {{ translate('unit') }}
                        <span class="input-required-icon">*</span>
                    </label>
                    <div class="select-wrapper">
                        <select class="form-select" name="unit" required>
                            @foreach (units() as $unit)
                                <option value="{{ $unit }}" {{ old('unit') == $unit ? 'selected' : '' }}>
                                    {{ $unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="form-group">
                    <label class="form-label d-flex align-items-center gap-2">
                        {{ translate('search_tags') }}
                        <span class="tooltip-icon cursor-pointer" data-bs-toggle="tooltip"
                              aria-label="{{ translate('add_the_product_search_tag_for_this_product_that_customers_can_use_to_search_quickly') }}"
                              data-bs-title="{{ translate('add_the_product_search_tag_for_this_product_that_customers_can_use_to_search_quickly') }}">
                              <i class="fi fi-sr-info"></i>
                        </span>
                    </label>
                    <input type="text" class="form-control" placeholder="{{ translate('enter_tag') }}"
                           name="tags" data-role="tagsinput">
                </div>
            </div>
        </div>
    </div>
</div>
