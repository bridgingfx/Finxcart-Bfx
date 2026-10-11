@extends('layouts.front-end.app')

@section('title',translate('shipping_Address'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/fx-commerce.css') }}?v=20260929">
@endpush

@section('content')

@php
    $billingInputByCustomer=getWebConfig(name: 'billing_input_by_customer')
@endphp
@php
    $customer = auth('customer')->user()
@endphp

    <div class="container fx fx-page">
        <nav class="fx-breadcrumb" aria-label="{{ translate('breadcrumb') }}">
            <ol>
                <li><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
                <li><a href="{{ route('shop-cart') }}">{{ translate('shopping_cart') }}</a></li>
                <li aria-current="page">{{ translate('shipping_address') }}</li>
            </ol>
        </nav>

        <ol class="fx-steps" aria-label="{{ translate('checkout') }}">
            <li class="is-done"><span class="fx-steps__n">&#10003;</span><span class="fx-steps__label">{{ translate('shopping_cart') }}</span></li>
            <li class="is-current"><span class="fx-steps__n">2</span><span class="fx-steps__label">{{ translate('shipping_address') }}</span></li>
            <li><span class="fx-steps__n">3</span><span class="fx-steps__label">{{ translate('payment') }}</span></li>
        </ol>

        <div class="fx-page-head">
            <div>
                <h1>{{ translate('checkout') }}</h1>
                <p class="fx-sub">{{ translate('where_should_we_send_your_order') }}</p>
            </div>
        </div>

        <div class="fx-checkout-grid">
            <div class="fx-checkout-main">
                @php
                    $defaultLocation = getWebConfig(name: 'default_location')
                @endphp

                <input type="hidden" id="physical_product" name="physical_product" value="{{ $physical_product_view ? 'yes':'no'}}">

                @if($physical_product_view)
                    <section class="fx-section-card" aria-labelledby="fx-ship-heading">
                        <h2 id="fx-ship-heading">{{ translate('shipping_address')}}</h2>

                        @php
                            $shippingAddresses = $shipping_addresses ?? collect()
                        @endphp

                        @if ($shippingAddresses->count() > 0)
                            <p class="fx-sub fs-14 mb-2">{{ translate('saved_address') }}</p>
                            <div class="fx-address-grid mb-3" role="group" aria-label="{{ translate('saved_address') }}">
                                @foreach($shippingAddresses as $key => $address)
                                    <button type="button"
                                            class="fx-address-card select_shipping_address {{ $key == 0 ? 'active' : '' }}"
                                            id="shippingAddress{{ $key }}">
                                        <input type="hidden" class="selected_shippingAddress{{ $key }}" value="{{ $address }}">
                                        <input type="hidden" name="shipping_method_id" value="{{ $address['id'] }}">
                                        <span class="fx-address-card__type">{{ $address->address_type }}</span>
                                        <span class="d-block fw-semi-bold">{{ $address->contact_person_name }}</span>
                                        <span class="d-block text-muted fs-13 text-break">{{ $address->address }}, {{ $address->city }}{{ $address->zip ? ', '.$address->zip : '' }}</span>
                                        <span class="d-block text-muted fs-13">{{ $address->phone }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <form method="post" id="address-form" class="fx-form" novalidate>
                            <div class="fx-row">
                                <div class="fx-field">
                                    <label for="name">{{ translate('contact_person_name')}} <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_person_name" id="name" autocomplete="name"
                                           value="{{ old('contact_person_name') }}"
                                           {{ $shippingAddresses->count()==0 ? 'required' : '' }}>
                                    <div class="fx-error">{{ translate('please_provide_valid_name') }}</div>
                                </div>
                                <div class="fx-field">
                                    <label for="phone">{{ translate('phone')}} <span class="text-danger">*</span></label>
                                    <input type="tel" class="phone-input-with-country-picker-3" id="phone" autocomplete="tel"
                                           value="{{ old('phone') }}"
                                           {{ $shippingAddresses->count()==0 ? 'required' : '' }}>
                                    <input type="hidden" id="shipping_phone_view" class="country-picker-phone-number-3" name="phone" readonly>
                                    <div class="fx-error">{{ translate('please_provide_valid_phone_number') }}</div>
                                </div>
                            </div>

                            @if(!auth('customer')->check())
                                <div class="fx-row">
                                    <div class="fx-field fx-field--full">
                                        <label for="email">{{ translate('email')}} <span class="text-danger">*</span></label>
                                        <input type="email" name="email" id="email" autocomplete="email"
                                               value="{{ old('email') }}"
                                               {{ $shippingAddresses->count()==0 ? 'required' : '' }}>
                                        <div class="fx-error">{{ translate('please_provide_valid_email') }}</div>
                                    </div>
                                </div>
                            @endif

                            <div class="fx-row">
                                <div class="fx-field">
                                    <label for="address_type">{{ translate('address_type')}}</label>
                                    <select name="address_type" id="address_type">
                                        <option value="permanent">{{ translate('permanent')}}</option>
                                        <option value="home">{{ translate('home')}}</option>
                                        <option value="others">{{ translate('others')}}</option>
                                    </select>
                                </div>
                                <div class="fx-field">
                                    <label for="country">{{ translate('country')}} <span class="text-danger">*</span></label>
                                    <select name="country" id="country" class="selectpicker" data-live-search="true" required>
                                        @forelse($countries as $country)
                                            <option value="{{ $country['name'] }}" {{ old('country')==$country['name']?'selected':'' }}>{{ $country['name'] }}</option>
                                        @empty
                                            <option value="">{{ translate('no_country_to_deliver') }}</option>
                                        @endforelse
                                    </select>
                                </div>
                            </div>

                            <div class="fx-row">
                                <div class="fx-field">
                                    <label for="city">{{ translate('city')}} <span class="text-danger">*</span></label>
                                    <input type="text" name="city" id="city" autocomplete="address-level2"
                                           value="{{ old('city') }}"
                                           {{ $shippingAddresses->count()==0 ? 'required' : '' }}>
                                </div>
                                <div class="fx-field">
                                    <label for="zip">{{ translate('zip_code')}} <span class="text-danger">*</span></label>
                                    @if($zip_restrict_status == 1)
                                        <select name="zip" class="selectpicker" data-live-search="true" id="select2-zip-container" required>
                                            @forelse($zip_codes as $code)
                                                <option value="{{ $code->zipcode }}" {{ old('zip')==$code->zipcode?'selected':'' }}>{{ $code->zipcode }}</option>
                                            @empty
                                                <option value="">{{ translate('no_zip_to_deliver') }}</option>
                                            @endforelse
                                        </select>
                                    @else
                                        <input type="text" name="zip" id="zip" autocomplete="postal-code"
                                               value="{{ old('zip') }}"
                                               {{ $shippingAddresses->count()==0 ? 'required' : '' }}>
                                    @endif
                                </div>
                            </div>

                            <div class="fx-row">
                                <div class="fx-field fx-field--full">
                                    <label for="address">{{ translate('address')}} <span class="text-danger">*</span></label>
                                    <textarea name="address" id="address" rows="2" autocomplete="street-address"
                                              {{ $shippingAddresses->count()==0 ? 'required' : '' }}>{{ old('address') }}</textarea>
                                    <span class="fs-14 text-danger font-semi-bold opacity-0 map-address-alert d-block">
                                        {{ translate('note') }}: {{ translate('you_need_to_select_address_from_your_selected_country') }}
                                    </span>
                                </div>
                            </div>

                            <div class="fx-row">
                                <div class="fx-field fx-field--full">
                                    @include('web-views.partials._address-map-search', ['mapQuery' => $defaultLocation ? $defaultLocation['lat'] . ',' . $defaultLocation['lng'] : 'Dubai'])
                                </div>
                            </div>

                            <div class="fx-check" id="save_address_label">
                                <input type="hidden" name="shipping_method_id" id="shipping_method_id" value="0">
                                @if(auth('customer')->check())
                                    <input type="checkbox" name="save_address" id="save_address">
                                    <label for="save_address">{{ translate('save_this_Address') }}</label>
                                @endif
                            </div>

                            <input type="hidden" id="latitude" name="latitude" value="{{ $defaultLocation ? $defaultLocation['lat'] : 0 }}" required readonly>
                            <input type="hidden" id="longitude" name="longitude" value="{{ $defaultLocation ? $defaultLocation['lng'] : 0 }}" required readonly>

                            <button type="submit" class="d--none" id="address_submit" aria-hidden="true" tabindex="-1"></button>
                        </form>
                    </section>

                    @if(!Auth::guard('customer')->check() && $web_config['guest_checkout_status'])
                        <section class="fx-section-card" aria-labelledby="fx-guest-account-heading">
                            <h2 id="fx-guest-account-heading" class="fs-16">{{ translate('Create_an_account_with_the_above_info')}}</h2>
                            <label class="fx-check" for="is_check_create_account">
                                <input type="checkbox" id="is_check_create_account" name="is_check_create_account" value="1">
                                <span>{{ translate('Create_an_account_with_the_above_info')}}</span>
                            </label>
                            <div class="is_check_create_account_password_group d--none mt-2">
                                <div class="fx-form">
                                    <div class="fx-row">
                                        <div class="fx-field">
                                            <div class="password-toggle rtl">
                                                <label class="fx-sr-only" for="customer_password">{{ translate('new_Password')}}</label>
                                                <input type="password" name="customer_password" id="customer_password"
                                                       placeholder="{{ translate('new_Password')}}" autocomplete="new-password" required>
                                                <label class="password-toggle-btn">
                                                    <input class="custom-control-input" type="checkbox" tabindex="-1" aria-hidden="true">
                                                    <i class="tio-hidden password-toggle-indicator"></i>
                                                    <span class="sr-only">{{ translate('show_password') }}</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="fx-field">
                                            <div class="password-toggle rtl">
                                                <label class="fx-sr-only" for="customer_confirm_password">{{ translate('confirm_Password')}}</label>
                                                <input type="password" name="customer_confirm_password" id="customer_confirm_password"
                                                       placeholder="{{ translate('confirm_Password')}}" autocomplete="new-password" required>
                                                <label class="password-toggle-btn">
                                                    <input class="custom-control-input" type="checkbox" tabindex="-1" aria-hidden="true">
                                                    <i class="tio-hidden password-toggle-indicator"></i>
                                                    <span class="sr-only">{{ translate('show_password') }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="fs-13 text-muted mb-0">{{ translate('password_must_be_at_least_8_characters') }}</p>
                                </div>
                            </div>
                        </section>
                    @endif
                @else
                    <div class="fx-notice fx-notice--info" role="status">
                        <span class="fx-notice__icon" aria-hidden="true">i</span>
                        <div>
                            <p><strong>{{ translate('digital_order') }}.</strong> {{ translate('digital_products_do_not_require_shipping') }}</p>
                        </div>
                    </div>

                    @if($billingInputByCustomer && !Auth::guard('customer')->check() && $web_config['guest_checkout_status'])
                        <section class="fx-section-card" aria-labelledby="fx-guest-account-heading-digital">
                            <h2 id="fx-guest-account-heading-digital" class="fs-16">{{ translate('Create_an_account_with_the_above_info')}}</h2>
                            <label class="fx-check" for="is_check_create_account">
                                <input type="checkbox" id="is_check_create_account" name="is_check_create_account" value="1">
                                <span>{{ translate('Create_an_account_with_the_above_info')}}</span>
                            </label>
                            <div class="is_check_create_account_password_group d--none mt-2">
                                <div class="fx-form">
                                    <div class="fx-row">
                                        <div class="fx-field">
                                            <div class="password-toggle rtl">
                                                <label class="fx-sr-only" for="customer_password">{{ translate('new_Password')}}</label>
                                                <input type="password" name="customer_password" id="customer_password"
                                                       placeholder="{{ translate('new_Password')}}" autocomplete="new-password" required>
                                                <label class="password-toggle-btn">
                                                    <input class="custom-control-input" type="checkbox" tabindex="-1" aria-hidden="true">
                                                    <i class="tio-hidden password-toggle-indicator"></i>
                                                    <span class="sr-only">{{ translate('show_password') }}</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="fx-field">
                                            <div class="password-toggle rtl">
                                                <label class="fx-sr-only" for="customer_confirm_password">{{ translate('confirm_Password')}}</label>
                                                <input type="password" name="customer_confirm_password" id="customer_confirm_password"
                                                       placeholder="{{ translate('confirm_Password')}}" autocomplete="new-password" required>
                                                <label class="password-toggle-btn">
                                                    <input class="custom-control-input" type="checkbox" tabindex="-1" aria-hidden="true">
                                                    <i class="tio-hidden password-toggle-indicator"></i>
                                                    <span class="sr-only">{{ translate('show_password') }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    @endif
                @endif

                @if($has_digital_products)
                    <section class="fx-section-card" aria-labelledby="fx-digital-heading">
                        <h2 id="fx-digital-heading">{{ translate('digital_delivery') }}</h2>
                        <p class="fx-sub fs-14 mb-3">{{ translate('how_would_you_like_to_receive_your_digital_products') }}</p>

                        <div class="fx-ship-options mb-3" role="radiogroup" aria-label="{{ translate('digital_delivery') }}">
                            <label class="fx-ship-option digital-delivery-option" for="delivery_type_email">
                                <input type="radio" name="digital_delivery_type_radio" value="email" id="delivery_type_email" class="digital-delivery-radio" checked>
                                <span>
                                    <span class="d-block fw-bold">{{ translate('email') }}</span>
                                    <span class="d-block fs-13 text-muted">{{ translate('send_the_order_link_to_your_email_inbox') }}</span>
                                </span>
                            </label>
                            <label class="fx-ship-option digital-delivery-option" for="delivery_type_whatsapp">
                                <input type="radio" name="digital_delivery_type_radio" value="whatsapp" id="delivery_type_whatsapp" class="digital-delivery-radio">
                                <span>
                                    <span class="d-block fw-bold">{{ translate('whatsapp') }}</span>
                                    <span class="d-block fs-13 text-muted">{{ translate('send_the_order_link_to_your_whatsapp_number') }}</span>
                                </span>
                            </label>
                            <label class="fx-ship-option digital-delivery-option" for="delivery_type_other">
                                <input type="radio" name="digital_delivery_type_radio" value="other" id="delivery_type_other" class="digital-delivery-radio">
                                <span>
                                    <span class="d-block fw-bold">{{ translate('other') }}</span>
                                    <span class="d-block fs-13 text-muted">{{ translate('type_your_preferred_delivery_method_or_contact_option') }}</span>
                                </span>
                            </label>
                        </div>

                        <div class="fx-form">
                            <div class="fx-field">
                                <label id="digital_delivery_value_label" for="digital_delivery_value_input">{{ translate('email_address') }}</label>
                                <input type="text" id="digital_delivery_value_input" autocomplete="email"
                                    value="{{ auth('customer')->check() ? ($customer?->getAttribute('email') ?? '') : '' }}"
                                    placeholder="{{ translate('enter_your_email') }}">
                                <input type="hidden" id="digital_delivery_type" name="digital_delivery_type" value="email">
                                <input type="hidden" id="digital_delivery_value" name="digital_delivery_value"
                                    value="{{ auth('customer')->check() ? ($customer?->getAttribute('email') ?? '') : '' }}">
                                <div class="fx-hint" id="digital_delivery_help_text">{{ translate('choose_how_you_want_to_receive_your_digital_product') }}</div>
                            </div>
                        </div>
                    </section>
                @endif

                <div class="fx-checkout-nav">
                    <a href="{{ route('shop-cart') }}" class="fx-btn fx-btn-ghost">&larr; {{ translate('back_to_cart') }}</a>
                    <button type="button" class="fx-btn fx-btn-lg proceed_to_next_button action-checkout-function">
                        {{ translate('proceed_to_payment') }}
                    </button>
                </div>
            </div>

            <div class="fx-checkout-side">
                @include('web-views.partials._order-summary')
            </div>
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
                    label: @json(translate('email_address')),
                    placeholder: @json(translate('enter_your_email')),
                    help: @json(translate('send_the_order_link_to_your_email_inbox')),
                    value: userEmail
                },
                whatsapp: {
                    label: @json(translate('whatsapp_number')),
                    placeholder: @json(translate('enter_your_whatsapp_number')),
                    help: @json(translate('send_the_order_link_to_your_whatsapp_number')),
                    value: userPhone
                },
                other: {
                    label: @json(translate('other_delivery_option')),
                    placeholder: @json(translate('type_your_preferred_delivery_option')),
                    help: @json(translate('examples')) + ': Telegram, Signal, iMessage, email, call',
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
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/fx-commerce.js') }}?v=20260929"></script>

    @if(getWebConfig('map_api_status') ==1 )
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{getWebConfig('map_api_key')}}&callback=mapsShopping&loading=async&libraries=places&v=3.56"
            defer>
        </script>
    @endif

@endpush
