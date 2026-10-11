<div class="fx-page-head">
    <div>
        <h1>{{ translate('shopping_cart')}}</h1>
    </div>
</div>

@php
    $shippingMethod = getWebConfig(name: 'shipping_method');
    $cart = \App\Utils\CartManager::getCartListGroupQuery();

    // Batch-load shipping-type/shop/chosen-shipping lookups once per render pass.
    $cartSellerIdsForLookup = collect($cart)
        ->map(fn($group) => $group->first()->seller_id ?? null)
        ->filter()
        ->push(0)
        ->unique()
        ->values();
    $cartGroupIdsForLookup = collect($cart)->flatten(1)->pluck('cart_group_id')->filter()->unique()->values();

    $shippingTypesBySeller = \App\Models\ShippingType::whereIn('seller_id', $cartSellerIdsForLookup)->get()->keyBy('seller_id');
    $shopsBySeller = \App\Models\Shop::whereIn('seller_id', $cartSellerIdsForLookup)->get()->keyBy('seller_id');
    $cartShippingsByGroup = \App\Models\CartShipping::whereIn('cart_group_id', $cartGroupIdsForLookup)->get()->keyBy('cart_group_id');
@endphp

<div class="fx-cart-grid">
    <div class="fx-cart-items">
        @if(count($cart)==0)
            <div class="fx-empty" role="status">
                <img src="{{ theme_asset(path: 'public/assets/front-end/img/icons/empty-cart.svg') }}" alt="" width="120" height="120">
                <h2>{{ translate('Your_Cart_is_Empty') }}!</h2>
                <p>{{ translate('looks_like_you_haven_not_added_anything_to_your_cart_yet') }}</p>
                <a href="{{ route('home') }}" class="fx-btn fx-btn-lg">{{ translate('continue_shopping') }}</a>
            </div>
        @endif

        @foreach($cart as $group_key=>$group)
            <section class="fx-cart-group cart_information" aria-label="{{ translate('cart_items') }}">
                <?php
                $isPhysicalProductExist = false;
                $total_shipping_cost = 0;
                foreach ($group as $row) {
                    if ($row->product_type == 'physical' && $row->is_checked) {
                        $isPhysicalProductExist = true;
                    }
                    if ($row->product_type == 'physical' && $row->is_checked && $row->shipping_type != "order_wise") {
                        $total_shipping_cost += $row->shipping_cost;
                    }
                }

                // Every item in a group shares the same seller, so the shipping type
                // only needs to be resolved once per group, not once per item.
                $groupSeller = $group->first();
                if ($shippingMethod=='inhouse_shipping' || $groupSeller->seller_is == 'admin') {
                    $admin_shipping = $shippingTypesBySeller->get(0);
                    $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
                } else {
                    $seller_shipping = $shippingTypesBySeller->get($groupSeller->seller_id);
                    $shipping_type = isset($seller_shipping) == true ? $seller_shipping->shipping_type : 'order_wise';
                }
                $verify_status = \App\Utils\OrderManager::verifyCartListMinimumOrderAmount($request, $group_key);
                $firstItem = $group->first();
                $chosenShipping = $cartShippingsByGroup->get($firstItem['cart_group_id']);
                ?>

                <div class="fx-cart-group__head">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if($firstItem->seller_is=='admin')
                            <a href="{{ route('shopView',['id'=>0]) }}" class="fx-vendor">
                                {{ getWebConfig(name: 'company_name') }}
                            </a>
                        @else
                            @php
                                $shopIdentity = $shopsBySeller->get($firstItem['seller_id'])
                            @endphp
                            @if($shopIdentity)
                                <a href="{{ route('shopView',['id' => $shopIdentity->id]) }}" class="fx-vendor">
                                    {{ $shopIdentity->name }}
                                </a>
                            @else
                                <span class="fx-vendor" style="color: var(--fx-danger)">{{ translate('vendor_not_available') }}</span>
                            @endif
                        @endif
                        @if ($verify_status['minimum_order_amount'] > $verify_status['amount'])
                            <span class="fx-min-order">
                                <i class="czi-security-announcement"></i>
                                {{ translate('minimum_Order_Amount') }} {{ webCurrencyConverter(amount: $verify_status['minimum_order_amount']) }}
                            </span>
                        @endif
                    </div>

                    <div class="fx-cart-group__ship">
                        @if($isPhysicalProductExist && $shippingMethod=='sellerwise_shipping' && $shipping_type == 'order_wise')
                            @php
                                $shippings = \App\Utils\Helpers::getShippingMethods($firstItem['seller_id'], $firstItem['seller_is'])
                            @endphp
                            @if(count($shippings) > 0)
                                @if(isset($chosenShipping)==false)
                                    @php
                                        $chosenShipping = ['shipping_method_id' => 0]
                                    @endphp
                                @endif
                                <label class="fx-sr-only" for="ship-{{ $firstItem['cart_group_id'] }}">{{ translate('shipping_method') }}</label>
                                <select class="form-control action-set-shipping-id" id="ship-{{ $firstItem['cart_group_id'] }}"
                                        data-product-id="{{ $firstItem['cart_group_id'] }}" style="min-height:44px">
                                    <option value="">{{ translate('choose_shipping_method')}}</option>
                                    @foreach($shippings as $shipping)
                                        <option value="{{ $shipping['id'] }}" {{ ($chosenShipping['shipping_method_id'] ?? 0)==$shipping['id']?'selected':'' }}>
                                            {{ ucfirst($shipping['title']).' ( '.$shipping['duration'].' ) '.webCurrencyConverter($shipping['cost']) }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <span class="fx-min-order">
                                    <i class="czi-security-announcement"></i> {{ translate('shipping_Not_Available') }}
                                </span>
                            @endif
                        @else
                            @if ($isPhysicalProductExist && $shipping_type != 'order_wise')
                                <span class="fs-14"><strong>{{ translate('total_shipping_cost')}}</strong>: {{ webCurrencyConverter(amount: $total_shipping_cost)}}</span>
                            @elseif($isPhysicalProductExist && $shipping_type == 'order_wise' && $chosenShipping)
                                <span class="fs-14"><strong>{{ translate('total_shipping_cost')}}</strong>: {{ webCurrencyConverter(amount: $chosenShipping->shipping_cost)}}</span>
                            @endif
                        @endif
                    </div>
                </div>

                @foreach($group as $cart_key=>$cartItem)
                    @php
                        $product = $cartItem->allProducts;
                        $minimumOrderQty = max((int)($product->minimum_order_qty ?? 1), 1);
                        $getProductCurrentStock = $product->current_stock;
                        if(!empty($product->variation)) {
                            foreach(json_decode($product->variation, true) as $productVariantSingle) {
                                if($productVariantSingle['type'] == $cartItem->variant) {
                                    $getProductCurrentStock = $productVariantSingle['qty'];
                                }
                            }
                        }
                        $checkProductStatus = $cartItem->allProducts?->status ?? 0;
                        if($cartItem->seller_is == 'admin') {
                            $inhouseTemporaryClose = getWebConfig(name: 'temporary_close') ? getWebConfig(name: 'temporary_close')['status'] : 0;
                            $inhouseVacation = getWebConfig(name: 'vacation_add');
                            $vacationStartDate = $inhouseVacation['vacation_start_date'] ? date('Y-m-d', strtotime($inhouseVacation['vacation_start_date'])) : null;
                            $vacationEndDate = $inhouseVacation['vacation_end_date'] ? date('Y-m-d', strtotime($inhouseVacation['vacation_end_date'])) : null;
                            $vacationStatus = $inhouseVacation['status'] ?? 0;
                            if ($inhouseTemporaryClose || ($vacationStatus && (date('Y-m-d') >= $vacationStartDate) && (date('Y-m-d') <= $vacationEndDate))) {
                                $checkProductStatus = 0;
                            }
                        } else {
                            if (!isset($cartItem->allProducts->seller) || (isset($cartItem->allProducts->seller) && $cartItem->allProducts->seller->status != 'approved')) {
                                $checkProductStatus = 0;
                            }
                            if (!isset($cartItem->allProducts->seller->shop) || $cartItem->allProducts->seller->shop->temporary_close) {
                                $checkProductStatus = 0;
                            }
                            if(isset($cartItem->allProducts->seller->shop) && ($cartItem->allProducts->seller->shop->vacation_status && (date('Y-m-d') >= $cartItem->allProducts->seller->shop->vacation_start_date) && (date('Y-m-d') <= $cartItem->allProducts->seller->shop->vacation_end_date))) {
                                $checkProductStatus = 0;
                            }
                        }
                        $displayQty = max((int)$cartItem['quantity'], (int)$minimumOrderQty);
                        $transactionType = \App\Enums\TransactionType::forProduct($product);
                        $isDigital = $transactionType === \App\Enums\TransactionType::Download;
                        $isPhysical = $transactionType === \App\Enums\TransactionType::Physical;
                    @endphp

                    <div class="fx-cart-item" data-cart-key="{{ $cartItem['id'] }}"
                         data-product-id="{{ $cartItem['product_id'] }}"
                         data-variant="{{ $cartItem['variant'] }}"
                         data-quantity="{{ $cartItem['quantity'] }}">
                        <div class="fx-cart-item__check">
                            <input type="checkbox" class="shop-item-check shop-item-check-desktop"
                                   value="{{ $cartItem['id'] }}"
                                   aria-label="{{ translate('select_item') }}: {{ $cartItem['name'] }}"
                                   data-product-id="{{ $cartItem['product_id'] }}"
                                   data-seller-id="{{ $cartItem['seller_id'] }}"
                                   data-shipping-method="{{ $cartItem['shipping_method_id'] }}"
                                   data-check="{{ $cartItem['is_checked'] }}"
                                   {{ $cartItem['is_checked'] ? 'checked' : '' }}>
                        </div>

                        <div class="fx-cart-item__thumb {{ $checkProductStatus == 0 ? 'is-unavailable' : '' }}">
                            <a href="{{ $checkProductStatus == 1 ? route('product', $cartItem['slug']) : 'javascript:' }}" tabindex="-1" aria-hidden="true">
                                <img src="{{ getStorageImages(path: $cartItem?->product?->thumbnail_full_url, type: 'product') }}" alt="">
                            </a>
                        </div>

                        <div class="fx-cart-item__info">
                            <h3 class="fx-cart-item__name">
                                <a href="{{ $checkProductStatus == 1 ? route('product', $cartItem['slug']) : 'javascript:' }}">
                                    {{ $cartItem['name'] }}
                                </a>
                                @if ($checkProductStatus == 0)
                                    <span class="fx-status fx-status--neutral">{{ translate('N/A') }}</span>
                                @endif
                            </h3>
                            <div class="fx-cart-item__meta">
                                @if(!empty($cartItem['variant']))
                                    <span>{{ translate('variant') }}: <b>{{ $cartItem['variant'] }}</b></span>
                                @endif
                                <span>{{ webCurrencyConverter(amount: $cartItem['price']-$cartItem['discount']) }}
                                    @if ($cartItem->tax_model === "exclude")
                                        ({{ translate('tax') }}: {{ webCurrencyConverter(amount: $cartItem['tax']*$cartItem['quantity']) }})
                                    @else
                                        ({{ translate('tax_included')}})
                                    @endif
                                </span>
                            </div>
                            <div class="fx-cart-item__flags">
                                @if($isDigital)
                                    <span class="fx-status fx-status--info">{{ translate('digital') }}</span>
                                    <span class="fs-13 text-success font-semi-bold">{{ translate($product->deliveryPromiseKey()) }}</span>
                                @else
                                    <span class="fx-status fx-status--neutral">{{ translate('physical') }}</span>
                                @endif
                            </div>
                            @if($isPhysical && $shipping_type != 'order_wise')
                                <div class="fx-cart-item__meta mt-1">
                                    <span>{{ translate('shipping_cost') }}: <b>{{ webCurrencyConverter(amount: $cartItem['shipping_cost']) }}</b></span>
                                </div>
                            @endif
                            @if($isPhysical && $getProductCurrentStock < $cartItem['quantity'])
                                <div class="fx-min-order mt-1">{{ translate('Out_Of_Stock') }}</div>
                            @endif
                            @if($minimumOrderQty > 1)
                                <div class="fx-qty-note">{{ translate('minimum_buy_amount') }}: {{ $minimumOrderQty }}</div>
                            @endif
                        </div>

                        <div class="fx-cart-item__actions">
                            @if($isDigital)
                                <div class="fx-qty" role="group" aria-label="{{ translate('qty') }}">
                                    <span class="d-inline-flex align-items-center px-3 fw-bold fs-16">{{ $cartItem['quantity'] }}</span>
                                    <button type="button" class="action-update-cart-quantity-list fx-qty__danger"
                                            data-minimum-order="1"
                                            data-cart-id="{{ $cartItem['id'] }}"
                                            data-increment="-1"
                                            data-event="delete"
                                            aria-label="{{ translate('remove') }}">
                                        <i class="tio-delete"></i>
                                    </button>
                                </div>
                            @elseif($checkProductStatus == 1)
                                <div class="fx-qty" role="group" aria-label="{{ translate('qty') }}">
                                    <button type="button"
                                            class="qty_minus action-update-cart-quantity-list {{ $displayQty <= $minimumOrderQty ? 'fx-qty__danger' : '' }}"
                                            data-minimum-order="{{ $minimumOrderQty }}"
                                            data-cart-id="{{ $cartItem['id'] }}"
                                            data-increment="-1"
                                            data-event="{{ $displayQty <= $minimumOrderQty ? 'delete' : 'minus' }}"
                                            aria-label="{{ $displayQty <= $minimumOrderQty ? translate('remove') : translate('decrease_quantity') }}">
                                        @if($displayQty <= $minimumOrderQty)
                                            <i class="tio-delete"></i>
                                        @else
                                            <i class="tio-remove"></i>
                                        @endif
                                    </button>
                                    <input type="text"
                                           class="qty_input cartQuantity{{ $cartItem['id'] }} action-change-update-cart-quantity-list"
                                           value="{{ $displayQty }}"
                                           name="quantity[{{ $cartItem['id'] }}]"
                                           id="cart_quantity_web{{ $cartItem['id'] }}"
                                           data-current-stock="{{ $getProductCurrentStock }}"
                                           data-minimum-order="{{ $minimumOrderQty }}"
                                           data-cart-id="{{ $cartItem['id'] }}"
                                           data-increment="0"
                                           data-min="{{ $minimumOrderQty }}"
                                           data-guest-status="{{ !auth('customer')->check() && session('guest_id') ? 1 : 0 }}"
                                           inputmode="numeric"
                                           aria-label="{{ translate('qty') }}: {{ $cartItem['name'] }}"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    <button type="button"
                                            class="qty_plus action-update-cart-quantity-list"
                                            data-minimum-order="{{ $minimumOrderQty }}"
                                            data-cart-id="{{ $cartItem['id'] }}"
                                            data-increment="1"
                                            aria-label="{{ translate('increase_quantity') }}">
                                        <i class="tio-add"></i>
                                    </button>
                                </div>
                            @else
                                <button type="button" class="fx-remove action-update-cart-quantity-list"
                                        data-minimum-order="{{ $minimumOrderQty }}"
                                        data-cart-id="{{ $cartItem['id'] }}"
                                        data-increment="-{{ $cartItem['quantity'] }}"
                                        data-event="delete">
                                    <i class="tio-delete"></i> {{ translate('remove') }}
                                </button>
                            @endif

                            <div class="fx-line-total">
                                {{ webCurrencyConverter(amount: ($cartItem['price']-$cartItem['discount'])*$cartItem['quantity']) }}
                            </div>

                            <button type="button" class="fx-remove fx-remove-key" data-remove-key="{{ $cartItem['id'] }}">
                                <i class="tio-delete"></i> {{ translate('remove') }}
                            </button>
                        </div>
                    </div>
                @endforeach

                @php
                    $free_delivery_status = \App\Utils\OrderManager::getFreeDeliveryOrderAmountArray($group[0]->cart_group_id);
                @endphp
                @if ($free_delivery_status['status'] && (session()->missing('coupon_type') || session('coupon_type') !='free_delivery'))
                    <div class="fx-free-delivery">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ theme_asset(path: 'public/assets/front-end/img/icons/free-shipping.png') }}" alt="" width="24" height="24">
                            @if ($free_delivery_status['amount_need'] <= 0)
                                <span>{{ translate('you_Get_Free_Delivery_Bonus') }}</span>
                            @else
                                <strong class="text-primary">{{ webCurrencyConverter(amount: $free_delivery_status['amount_need']) }}</strong>
                                <span>{{ translate('add_more_for_free_delivery') }}</span>
                            @endif
                        </div>
                        <div class="fx-free-delivery__bar" role="progressbar"
                             aria-valuenow="{{ $free_delivery_status['percentage'] }}" aria-valuemin="0" aria-valuemax="100"
                             aria-label="{{ translate('free_delivery') }}">
                            <span style="width: {{ $free_delivery_status['percentage'] }}%"></span>
                        </div>
                    </div>
                @endif
            </section>
        @endforeach
    </div>

    <div class="fx-cart-summary-col">
        @include('web-views.partials._order-summary')
    </div>
</div>

@if($shippingMethod=='inhouse_shipping')
    @php
        $isPhysicalProductExist = false;
        foreach ($cart as $group_key => $group) {
            foreach ($group as $row) {
                if ($row->product_type == 'physical' && $row->is_checked) {
                    $isPhysicalProductExist = true;
                }
            }
        }
        $admin_shipping = $shippingTypesBySeller->get(0);
        $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
    @endphp
    @if ($shipping_type == 'order_wise' && $isPhysicalProductExist)
        <input type="hidden" class="action-set-shipping-id" name="shipping_method_id" data-product-id="all_cart_group" value="1">
    @endif
@endif

<span id="route-customer-set-shipping-method" data-url="{{ url('/customer/set-shipping-method') }}"></span>
<span id="route-cart-add" data-url="{{ route('cart.add') }}"></span>
<span id="route-action-checkout-function" data-route="shop-cart"></span>

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/cart-details.js') }}"></script>
    <script>
        window.__fxRemoveConfirmText = "{{ translate('remove_this_item_from_your_cart') }}";
    </script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/fx-commerce.js') }}?v=20260929"></script>
@endpush
