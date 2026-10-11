@php
    $routeName = \Illuminate\Support\Facades\Route::currentRouteName();
    $checkoutButtonText = match($routeName) {
        'checkout-details' => translate('proceed_to_payment'),
        'checkout-payment' => translate('pay_now'),
        default => translate('proceed_to_checkout'),
    };
@endphp

<div class="fx-summary-wrap">
    <aside class="fx-summary" aria-label="{{ translate('order_summary') }}">
        <h2>{{ translate('order_summary') }}</h2>

        @php
            $shippingMethod=getWebConfig(name: 'shipping_method')
        @endphp
        @php
            $subTotal=0
        @endphp
        @php
            $totalTax=0
        @endphp
        @php
            $totalShippingCost=0
        @endphp
        @php
            $orderWiseShippingDiscount=\App\Utils\CartManager::order_wise_shipping_discount()
        @endphp
        @php
            $totalDiscountOnProduct=0
        @endphp
        @php
            $cart=\App\Utils\CartManager::getCartListQuery(type: 'checked')
        @endphp
        @php
            $cartGroupIds=\App\Utils\CartManager::get_cart_group_ids()
        @endphp
        @php
            $getShippingCost=\App\Utils\CartManager::get_shipping_cost(type: 'checked')
        @endphp
        @php
            $getShippingCostSavedForFreeDelivery=\App\Utils\CartManager::getShippingCostSavedForFreeDelivery(type: 'checked')
        @endphp
        @php
            $hasPhysicalProducts = false
        @endphp
        @if($cart->count() > 0)
            @foreach($cart as $key => $cartItem)
                @php
                    $subTotal+=$cartItem['price']*$cartItem['quantity']
                @endphp
                @php
                    $totalTax+=$cartItem['tax_model']=='exclude' ? ($cartItem['tax']*$cartItem['quantity']):0
                @endphp
                @php
                    $totalDiscountOnProduct+=$cartItem['discount']*$cartItem['quantity']
                @endphp
                @if(\App\Enums\TransactionType::forProduct($cartItem->product) === \App\Enums\TransactionType::Physical && $cartItem->is_checked)
                    @php
                        $hasPhysicalProducts = true
                    @endphp
                @endif
            @endforeach

            @if(session()->missing('coupon_type') || session('coupon_type') !='free_delivery')
                @php
                    $totalShippingCost=$getShippingCost - $getShippingCostSavedForFreeDelivery
                @endphp
            @else
                @php
                    $totalShippingCost=$getShippingCost
                @endphp
            @endif
        @endif

        @php
            $totalSavedAmount = $totalDiscountOnProduct
        @endphp
        @if(session()->has('coupon_discount') && session('coupon_discount') > 0 && session('coupon_type') !='free_delivery')
            @php
                $totalSavedAmount += session('coupon_discount')
            @endphp
        @endif
        @if($getShippingCostSavedForFreeDelivery > 0)
            @php
                $totalSavedAmount += $getShippingCostSavedForFreeDelivery
            @endphp
        @endif

        @if($totalSavedAmount > 0)
            <p class="fx-saved">
                <img src="{{ theme_asset(path: 'public/assets/front-end/img/icons/offer.svg') }}" alt="" width="20" height="20">
                {{ translate('you_have_Saved') }}
                <strong>{{ webCurrencyConverter(amount: $totalSavedAmount) }}!</strong>
            </p>
        @endif

        <dl>
            <div class="fx-summary-row">
                <dt>{{ translate('sub_total') }}</dt>
                <dd>{{ webCurrencyConverter(amount: $subTotal) }}</dd>
            </div>
            <div class="fx-summary-row">
                <dt>{{ translate('tax') }}</dt>
                <dd>{{ webCurrencyConverter(amount: $totalTax) }}</dd>
            </div>
            @if($hasPhysicalProducts)
                <div class="fx-summary-row">
                    <dt>{{ translate('shipping') }}</dt>
                    <dd>{{ webCurrencyConverter(amount: $totalShippingCost) }}</dd>
                </div>
            @endif
            <div class="fx-summary-row">
                <dt>{{ translate('discount_on_product') }}</dt>
                <dd>- {{ webCurrencyConverter(amount: $totalDiscountOnProduct) }}</dd>
            </div>
            @php
                $coupon_dis=0
            @endphp
            @if(auth('customer')->check())
                @if(session()->has('coupon_discount'))
                    @php
                        $couponDiscount = session()->has('coupon_discount')?session('coupon_discount'):0
                    @endphp
                    <div class="fx-summary-row">
                        <dt>{{ translate('coupon_discount') }}</dt>
                        <dd>- {{ webCurrencyConverter(amount: $couponDiscount + $orderWiseShippingDiscount) }}</dd>
                    </div>
                    <div class="fx-coupon-applied">
                        <img width="24" height="24" src="{{ theme_asset(path: 'public/assets/front-end/img/icons/coupon.svg') }}" alt="">
                        <div class="fx-coupon-applied__code">
                            {{ session('coupon_code') }}
                            <span class="text-primary small">( -{{ webCurrencyConverter(amount: $couponDiscount + $orderWiseShippingDiscount) }} )</span>
                        </div>
                        <div class="text-danger cursor-pointer px-2 get-view-by-onclick" data-link="{{ route('coupon.remove') }}" role="button" aria-label="{{ translate('remove') }}">x</div>
                    </div>
                    @php
                        $coupon_dis=session('coupon_discount')
                    @endphp
                @else
                    <form class="needs-validation coupon-code-form fx-coupon" action="javascript:" method="post" novalidate id="coupon-code-ajax">
                        <img width="24" src="{{ theme_asset(path: 'public/assets/front-end/img/icons/coupon.svg') }}" alt="">
                        <label class="fx-sr-only" for="coupon_code">{{ translate('coupon_code') }}</label>
                        <input class="input_code" type="text" name="code" id="coupon_code"
                               placeholder="{{ translate('coupon_code') }}" autocomplete="off" required>
                        <button class="fx-btn apply_coupon" type="button" id="apply-coupon-code">{{ translate('apply') }}</button>
                        <div class="invalid-feedback">{{ translate('please_provide_coupon_code') }}</div>
                    </form>
                    @php
                        $coupon_dis=0
                    @endphp
                @endif
            @endif
            <div class="fx-summary-row fx-summary-row--total">
                <dt>{{ translate('total') }}</dt>
                <dd>{{ webCurrencyConverter(amount: $subTotal+$totalTax+$totalShippingCost-$coupon_dis-$totalDiscountOnProduct-$orderWiseShippingDiscount) }}</dd>
            </div>
        </dl>

        <p class="fx-currency-note">{{ translate('currency') }}: {{ session('currency_code') }}</p>

        @php
            $guestCheckoutAllowed = (getWebConfig(name: 'guest_checkout')['status'] ?? 0) == 1
        @endphp
        <div class="pt-2">
            @if(auth('customer')->check() || session('guest_id') || $guestCheckoutAllowed)
                <button class="fx-btn fx-btn-lg proceed_to_next_button w-100 {{ $cart->count() <= 0 ? 'custom-disabled' : '' }} action-checkout-function" type="button">{{ $checkoutButtonText }}</button>
            @else
                <a href="{{ route('customer.auth.login') }}"
                   onclick="localStorage.setItem('redirect_after_login', '{{ route('shop-cart') }}')"
                   class="fx-btn fx-btn-lg w-100 {{ $cart->count() <= 0 ? 'custom-disabled' : '' }}">
                    <i class="tio-lock-outlined me-1" aria-hidden="true"></i> {{ translate('login_register_to_checkout') }}
                </a>
                <p class="fx-currency-note text-center mt-2 mb-0">{{ translate('sign_in_to_continue_checkout') }}</p>
            @endif
        </div>

        <div class="d-flex justify-content-center mt-3">
            <a href="{{ route('home') }}" class="fx-link d-flex align-items-center gap-2">
                <i class="tio-back-ui fs-12" aria-hidden="true"></i> {{ translate('continue_Shopping') }}
            </a>
        </div>
    </aside>

    <div class="fx-mobile-bar d-lg-none">
        <div class="fx-mobile-bar__row">
            <span>{{ translate('total_price') }}:</span>
            <strong class="fx-mobile-bar__total">{{ webCurrencyConverter(amount: $subTotal+$totalTax+$totalShippingCost-$coupon_dis-$totalDiscountOnProduct-$orderWiseShippingDiscount) }}</strong>
        </div>
        @if(auth('customer')->check() || session('guest_id') || $guestCheckoutAllowed)
            <button data-route="{{ $routeName }}" type="button"
               class="fx-btn fx-btn-lg proceed_to_next_button w-100 {{ $cart->count() <= 0 ? 'custom-disabled' : '' }} action-checkout-function">{{ translate('proceed_to_checkout') }}</button>
        @else
            <a href="{{ route('customer.auth.login') }}"
               class="fx-btn fx-btn-lg w-100 {{ $cart->count() <= 0 ? 'custom-disabled' : '' }}">
                <i class="tio-lock-outlined me-1" aria-hidden="true"></i> {{ translate('login_register_to_checkout') }}
            </a>
        @endif
    </div>
</div>

@push('script')
    <script>
        "use strict";
        $(document).ready(function () {
            orderSummaryStickyFunction()
        });
    </script>
@endpush
