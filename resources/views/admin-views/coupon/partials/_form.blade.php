@php
    $coupon = $coupon ?? null;
@endphp
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-4">
        <label for="name" class="form-label d-flex">{{ translate('coupon_type') }}</label>
        <div class="select-wrapper">
            <select class="form-select" id="coupon_type" name="coupon_type" required>
                <option disabled selected>{{ translate('select_coupon_type') }}</option>
                <option value="discount_on_purchase" {{ $coupon && $coupon['coupon_type'] == 'discount_on_purchase' ? 'selected' : '' }}>{{ translate('discount_on_Purchase') }}</option>
                <option value="free_delivery" {{ $coupon && $coupon['coupon_type'] == 'free_delivery' ? 'selected' : '' }}>{{ translate('free_Delivery') }}</option>
                <option value="first_order" {{ $coupon && $coupon['coupon_type'] == 'first_order' ? 'selected' : '' }}>{{ translate('first_Order') }}</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <label for="name" class="form-label d-flex">{{ translate('coupon_title') }}</label>
        <input type="text" name="title" class="form-control" id="title"
               value="{{ $coupon ? $coupon['title'] : old('title') }}"
               placeholder="{{ translate('title') }}" required>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="d-flex justify-content-between">
            <label for="name" class="form-label text-capitalize">{{ translate('coupon_code') }}</label>
            <a href="javascript:" class="text-primary fs-12" id="generateCode">{{ translate('generate_code') }}</a>
        </div>
        <input type="text" name="code" value="{{ $coupon ? $coupon['code'] : '' }}"
               class="form-control" id="code"
               placeholder="{{ translate('ex') }}: EID100" required>
    </div>
    <div class="col-md-6 col-lg-4 first_order">
        <label for="name" class="form-label d-flex">{{ translate('coupon_bearer') }}</label>
        <div class="select-wrapper">
            <select class="form-select" name="coupon_bearer" id="coupon_bearer">
                <option disabled selected>{{ translate('select_coupon_bearer') }}</option>
                <option value="seller" {{ $coupon && $coupon['coupon_bearer'] == 'seller' ? 'selected' : '' }}>{{ translate('vendor') }}</option>
                <option value="inhouse" {{ $coupon && $coupon['coupon_bearer'] == 'inhouse' ? 'selected' : '' }}>{{ translate('admin') }}</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 coupon_by first_order">
        <label for="name" class="form-label d-flex">{{ translate('vendor') }}</label>
        <select class="custom-select" name="seller_id" id="vendor_wise_coupon">
            <option disabled selected>{{ translate('select_vendor') }}</option>
            @if($coupon)
                <option value="0" {{ $coupon['seller_id'] == '0' ? 'selected' : '' }}>{{ translate('all_Vendor') }}</option>
                @if($coupon['coupon_bearer'] == 'inhouse')
                    <option value="inhouse" {{ is_null($coupon['seller_id']) ? 'selected' : '' }}>{{ translate('inhouse') }}</option>
                @endif
                @foreach($sellers as $seller)
                    <option value="{{ $seller->id }}" {{ $coupon['seller_id'] == $seller->id ? 'selected' : '' }}>{{ $seller->shop->name ?? $seller->f_name ?? $seller->email }}</option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="col-md-6 col-lg-4 coupon_type first_order">
        <label for="name" class="form-label d-flex">{{ translate('customer') }}</label>
        <select class="custom-select" name="customer_id">
            <option disabled selected>{{ translate('select_customer') }}</option>
            <option value="0" {{ $coupon && $coupon['customer_id'] == '0' ? 'selected' : '' }}>{{ translate('all_customer') }}</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ $coupon && $coupon['customer_id'] == $customer->id ? 'selected' : '' }}>{{ $customer->f_name . ' ' . $customer->l_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 col-lg-4 first_order">
        <label for="exampleFormControlInput1" class="form-label d-flex">{{ translate('limit_for_same_user') }}</label>
        <input type="number" name="limit" min="0" value="{{ $coupon ? $coupon['limit'] : old('limit') }}"
               id="coupon_limit" class="form-control"
               placeholder="{{ translate('ex') }}: 10">
    </div>
    <div class="col-md-6 col-lg-4 free_delivery">
        <label for="name" class="form-label d-flex">{{ translate('discount_type') }}</label>
        <div class="select-wrapper">
            <select id="discount_type" class="form-select" name="discount_type">
                <option value="amount" {{ $coupon && $coupon['discount_type'] == 'amount' ? 'selected' : '' }}>{{ translate('amount') }}</option>
                <option value="percentage" {{ $coupon && $coupon['discount_type'] == 'percentage' ? 'selected' : '' }}>{{ translate('percentage') }} (%)</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 free_delivery">
        <label for="name" class="form-label d-flex">{{ translate('discount_Amount') }}
            <span id="discount_percent"> (%)</span></label>
        <input type="number" min="{{ $coupon ? 0 : 1 }}" max="1000000" {{ $coupon ? 'step=.01' : '' }} name="discount"
               value="{{ $coupon ? ($coupon['discount_type'] == 'amount' ? usdToDefaultCurrency(amount: $coupon['discount']) : $coupon['discount']) : old('discount') }}"
               class="form-control" id="discount"
               placeholder="{{ translate('ex') }} : 500" {{ $coupon ? 'required' : '' }}>
    </div>
    <div class="col-md-6 col-lg-4">
        <label for="name" class="form-label d-flex">{{ translate('minimum_purchase') }}
            @if(!$coupon) ($) @endif
        </label>
        <input type="number" min="{{ $coupon ? 0 : 1 }}" max="1000000" {{ $coupon ? 'step=.01' : '' }} name="min_purchase"
               value="{{ $coupon ? usdToDefaultCurrency(amount: $coupon['min_purchase']) : old('min_purchase') }}"
               class="form-control" id="minimum purchase"
               placeholder="{{ $coupon ? translate('minimum_purchase') : translate('ex') . ' : 100' }}" required>
    </div>
    <div class="col-md-6 col-lg-4 free_delivery" id="max-discount">
        <label for="name" class="form-label d-flex">{{ translate('maximum_discount') }}
            @if(!$coupon) ($) @endif
        </label>
        <input type="number" min="{{ $coupon ? 0 : 1 }}" max="1000000" {{ $coupon ? 'step=.01' : '' }} name="max_discount"
               value="{{ $coupon ? usdToDefaultCurrency(amount: $coupon['max_discount']) : old('max_discount') }}"
               class="form-control" id="maximum discount"
               placeholder="{{ $coupon ? translate('maximum_discount') : translate('ex') . ' : 5000' }}">
    </div>
    <div class="col-md-6 col-lg-4">
        <label for="name" class="form-label d-flex">{{ translate('start_date') }}</label>
        <input id="start_date" type="date" name="start_date"
               value="{{ $coupon ? date('Y-m-d', strtotime($coupon['start_date'])) : old('start_date') }}"
               class="form-control"
               placeholder="{{ translate('start_date') }}" required>
    </div>
    <div class="col-md-6 col-lg-4">
        <label for="name" class="form-label d-flex">{{ translate('expire_date') }}</label>
        <input id="expire_date" type="date" name="expire_date"
               value="{{ $coupon ? date('Y-m-d', strtotime($coupon['expire_date'])) : old('expire_date') }}"
               class="form-control"
               placeholder="{{ translate('expire_date') }}" required>
    </div>
</div>
