@extends('layouts.admin.app')

@section('title', translate('Edit_Tier') . ': ' . $tier->name)

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
                {{ translate('Edit_Product_Tier') }}
            </h2>
        </div>
        <form class="product-form text-start" action="{{ route('admin.tiers.update', ['id' => $tier->id]) }}" method="POST"
              id="tier_form" novalidate>
            @csrf
            @method('PUT') {{-- IMPORTANT: Laravel uses PUT method for updates --}}

            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="fi fi-sr-box"></i>
                        <h3 class="mb-0">{{ translate('Tier_Information') }}</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row gy-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="tier_name">{{ translate('Tier_Name') }}
                                    <span class="input-required-icon text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="tier_name" class="form-control"
                                       placeholder="{{ translate('Ex') }}: {{ translate('Premium_Tier') }}"
                                       required
                                       value="{{ old('name', $tier->name) }}">
                            </div>
                        </div>

                        {{-- Target Category Dropdown --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="target_category" class="form-label">{{ translate('Target_Category') }}
                                    <span class="input-required-icon text-danger">*</span>
                                    <span class="tooltip-icon cursor-pointer" data-bs-toggle="tooltip"
                                          title="{{ translate('The_primary_category_this_tier_is_designed_for') }}">
                                        <i class="fi fi-sr-info"></i>
                                    </span>
                                </label>
                                <select class="custom-select" name="target_category" id="target_category" required>
                                    <option value="" disabled>{{ translate('Select_Target_Category') }}</option>
                                    @foreach ($productCategories as $category)
                                        <option value="{{ $category->name }}"
                                            {{ old('target_category', $tier->target_category) == $category->name ? 'selected' : '' }}>
                                            {{ translate($category->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ translate('Ideal_Product_Types') }}
                                    <span class="input-required-icon text-danger">*</span>
                                    <span class="tooltip-icon cursor-pointer" data-bs-toggle="tooltip"
                                          title="{{ translate('Comma_separated_list_of_suitable_product_types') }}">
                                        <i class="fi fi-sr-info"></i>
                                    </span>
                                </label>
                                <input type="text" class="form-control" placeholder="{{ translate('Ex') }}: {{ translate('subscription,one-time-purchase') }}"
                                       name="ideal_product_types" data-role="tagsinput" required
                                       value="{{ old('ideal_product_types', $tier->ideal_product_types) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="fi fi-sr-dollar"></i>
                        <h3 class="mb-0">{{ translate('Pricing_&_Commission_Setup') }}</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row gy-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">
                                    {{ translate('Monthly_Fee') }} ({{ getCurrencySymbol(currencyCode: 'USD') }})
                                </label>
                                <input type="number" min="0" step="0.01"
                                       placeholder="{{ translate('Ex: 10.00') }}"
                                       value="{{ old('monthly_fee_usd', $tier->monthly_fee_usd) ?? 0 }}" name="monthly_fee_usd"
                                       class="form-control">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">
                                    {{ translate('Sales_Commission_Rate') }} (%)
                                    <span class="input-required-icon text-danger">*</span>
                                </label>
                                {{-- Display value as 0-100% in the input field --}}
                                <input type="number" min="0" max="100" step="0.01"
                                       placeholder="{{ translate('Ex: 15 for 15%') }}"
                                       value="{{ old('sales_commission_rate', $tier->sales_commission_rate) ?? 0 }}" name="sales_commission_rate"
                                       class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">
                                    {{ translate('Commission_Only_Tier') }}
                                </label>
                                <div class="d-flex align-items-center form-control min-h-40">
                                    <label class="switcher mb-0">
                                        <input type="checkbox" class="switcher_input" name="is_commission_only" value="1"
                                               {{ old('is_commission_only', $tier->is_commission_only) ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ translate('Minimum_Price_Threshold') }} ({{ getCurrencySymbol(currencyCode: 'USD') }})</label>
                                <input type="number" min="0" step="0.01"
                                       placeholder="{{ translate('Ex: 5.00') }}"
                                       value="{{ old('price_threshold_min_usd', $tier->price_threshold_min_usd) ?? 0 }}" name="price_threshold_min_usd"
                                       class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ translate('Maximum_Price_Threshold') }} ({{ getCurrencySymbol(currencyCode: 'USD') }})</label>
                                <input type="number" min="0" step="0.01"
                                       placeholder="{{ translate('Ex: 50.00') }}"
                                       value="{{ old('price_threshold_max_usd', $tier->price_threshold_max_usd) }}" name="price_threshold_max_usd"
                                       class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <div class="d-flex gap-2">
                        <i class="fi fi-sr-list-check"></i>
                        <h3 class="mb-0">{{ translate('Features_&_Benefits') }}</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row gy-4">

                        {{-- 1. Images/Videos Allowed --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label" for="images_videos_allowed">{{ translate('Images/Videos_Allowed') }}</label>
                                <select name="images_videos_allowed" id="images_videos_allowed" class="form-select" required>
                                    @foreach ($imagesOptions as $option)
                                        <option value="{{ $option }}"
                                            {{ old('images_videos_allowed', $tier->images_videos_allowed) == $option ? 'selected' : '' }}>
                                            {{ translate($option) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- 2. Search Ranking --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label" for="search_ranking">{{ translate('Search_Ranking') }}</label>
                                <select name="search_ranking" id="search_ranking" class="form-select" required>
                                    @foreach ($rankingOptions as $option)
                                        <option value="{{ $option }}"
                                            {{ old('search_ranking', $tier->search_ranking) == $option ? 'selected' : '' }}>
                                            {{ translate($option) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- 3. Buyer Interaction --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label" for="buyer_interaction">{{ translate('Buyer_Interaction') }}</label>
                                <select name="buyer_interaction" id="buyer_interaction" class="form-select" required>
                                    @foreach ($interactionOptions as $option)
                                        <option value="{{ $option }}"
                                            {{ old('buyer_interaction', $tier->buyer_interaction) == $option ? 'selected' : '' }}>
                                            {{ translate($option) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- 4. Analytics --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label" for="analytics">{{ translate('Analytics') }}</label>
                                <select name="analytics" id="analytics" class="form-select" required>
                                    @foreach ($analyticsOptions as $option)
                                        <option value="{{ $option }}"
                                            {{ old('analytics', $tier->analytics) == $option ? 'selected' : '' }}>
                                            {{ translate($option) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- 5. Billing Tools --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label" for="billing_tools">{{ translate('Billing_Tools') }}</label>
                                <select name="billing_tools" id="billing_tools" class="form-select" required>
                                    @foreach ($billingOptions as $option)
                                        <option value="{{ $option }}"
                                            {{ old('billing_tools', $tier->billing_tools) == $option ? 'selected' : '' }}>
                                            {{ translate($option) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- 6. Listings Per Fee --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label" for="listings_per_fee">{{ translate('Listings_Per_Fee') }}</label>
                                <input type="text" name="listings_per_fee" id="listings_per_fee" class="form-control"
                                       value="{{ old('listings_per_fee', $tier->listings_per_fee) ?? '1 product' }}"
                                       placeholder="{{ translate('Ex: 1 product') }}" required>
                            </div>
                        </div>

                        {{-- 7. API/Integrations (Yes/No Toggle) --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">{{ translate('API/Integrations_Access') }}</label>
                                <div class="d-flex align-items-center form-control min-h-40">
                                    <label class="switcher mb-0">
                                        <input type="checkbox" class="switcher_input" name="api_integrations" value="1"
                                               {{ old('api_integrations', $tier->api_integrations) ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Featured Product Quota --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label" for="featured_product_quota">{{ translate('Featured_Product_Quota') }}
                                    <span class="tooltip-icon cursor-pointer" data-bs-toggle="tooltip"
                                          title="{{ translate('How_many_of_this_vendors_products_get_automatically_featured_on_the_homepage.__0_means_none') }}">
                                        <i class="fi fi-sr-info"></i>
                                    </span>
                                </label>
                                <input type="number" min="0" step="1" name="featured_product_quota" id="featured_product_quota"
                                       class="form-control" placeholder="{{ translate('Ex: 3') }}"
                                       value="{{ old('featured_product_quota', $tier->featured_product_quota) ?? 0 }}">
                            </div>
                        </div>

                        {{-- Featured Vendor --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">{{ translate('Featured_Vendor') }}
                                    <span class="tooltip-icon cursor-pointer" data-bs-toggle="tooltip"
                                          title="{{ translate('Highlights_the_vendors_shop_as_a_featured_company_on_the_shop_page_and_vendor_list') }}">
                                        <i class="fi fi-sr-info"></i>
                                    </span>
                                </label>
                                <div class="d-flex align-items-center form-control min-h-40">
                                    <label class="switcher mb-0">
                                        <input type="checkbox" class="switcher_input" name="is_featured_vendor" value="1"
                                               {{ old('is_featured_vendor', $tier->is_featured_vendor) ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Retaining the original recurring and free month toggles --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">{{ translate('Is_Recurring_Focus') }}</label>
                                <div class="d-flex align-items-center form-control min-h-40">
                                    <label class="switcher mb-0">
                                        <input type="checkbox" class="switcher_input" name="is_recurring_focus" value="1"
                                               {{ old('is_recurring_focus', $tier->is_recurring_focus) ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">{{ translate('Is_Free_First_Month') }}</label>
                                <div class="d-flex align-items-center form-control min-h-40">
                                    <label class="switcher mb-0">
                                        <input type="checkbox" class="switcher_input" name="is_free_first_month" value="1"
                                               {{ old('is_free_first_month', $tier->is_free_first_month) ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label d-flex align-items-center gap-2">
                                    {{ translate('Active') }}
                                    <span class="badge bg-success-soft text-success small">{{ translate('visible_on_vendor_tier_page') }}</span>
                                </label>
                                <div class="d-flex align-items-center form-control min-h-40">
                                    <label class="switcher mb-0">
                                        <input type="checkbox" class="switcher_input" name="is_active" value="1"
                                               {{ old('is_active', $tier->is_active ?? true) ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                    <span class="ms-2 small text-muted" id="is_active_label">
                                        {{ ($tier->is_active ?? true) ? translate('Active') : translate('Inactive') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end flex-wrap gap-3 mt-3 mx-1">
                <button type="reset" class="btn btn-secondary px-5">{{ translate('reset') }}</button>
                <button type="submit" class="btn btn-primary px-5">
                    {{ translate('Update_Tier') }}
                </button>
            </div>
        </form>
    </div>
@endsection

@push('script')
    {{-- Include your necessary scripts here (for tagsinput, tooltips, etc.) --}}
@endpush
