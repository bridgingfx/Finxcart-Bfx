@php
    use App\Utils\Helpers;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ Session::get('direction') }}"
      style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="nofollow, noindex ">
    <title>@yield('title')</title>
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type:'backend-logo')}}">

    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/backend/webfonts/uicons-regular-rounded.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/backend/webfonts/uicons-solid-rounded.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/vendor.min.css') }}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/google-fonts.css')}}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/vendor/icon-set/style.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/theme.minc619.css?v=1.0') }}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/daterangepicker.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/style.css')}}">
    @if (Session::get('direction') === 'rtl')
        <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/menurtl.css')}}">
    @endif
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/css/lightbox.css') }}">
    
    {{-- Added Toastr CSS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    {!! ToastMagic::styles() !!}
    @stack('css_or_js')

    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/custom.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/premium-toasts.css') }}">
    <style>
        select {
            background-image: url('{{dynamicAsset(path: 'public/assets/back-end/img/arrow-down.png')}}');
            background-size: 7px;
            background-position: 96% center;
        }

        #toast-container.toast-top-right {
            top: 76px;
            right: 18px;
        }

        #toast-container > .toast.vendor-notification-toast {
            width: min(360px, calc(100vw - 32px));
            padding: 16px 18px;
            border-radius: 8px;
            background-image: none !important;
            box-shadow: 0 12px 32px rgba(20, 34, 61, 0.18);
        }

        .vendor-notification-toast .toast-title {
            margin-bottom: 6px;
            font-size: 15px;
            line-height: 1.3;
        }

        .vendor-notification-toast .toast-message {
            font-size: 14px;
            line-height: 1.45;
        }

        .vendor-notification-toast .toast-close-button {
            top: -4px;
            right: -4px;
        }

        .vendor-notification-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .vendor-notification-actions .btn {
            min-width: 78px;
        }

        @media (max-width: 575.98px) {
            #toast-container.toast-top-right {
                top: 68px;
                right: 10px;
                left: 10px;
            }

            #toast-container > .toast.vendor-notification-toast {
                width: 100%;
            }
        }
    </style>
</head>
<body class="footer-offset has-navbar-vertical-aside navbar-vertical-aside-show-xl">
<div class="row">
    <div class="col-12 position-fixed z-9999 mt-10rem">
        <div id="loading" class="d--none">
            <div id="loader"></div>
        </div>
    </div>
</div>
@include('layouts.vendor.partials._header')
@include('layouts.vendor.partials._side-bar')

<main id="content" role="main" class="main pointer-event" style="padding-bottom: 4rem">

    @include('vendor-views.partials._verification-banner')
    @yield('content')

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
            {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @include('layouts.vendor.partials._footer')

    @include('layouts.vendor.partials._modals')

    @include('layouts.vendor.partials._toggle-modal')
    @include('layouts.vendor.partials._translator-for-js')
    @include('layouts.vendor.partials._sign-out-modal')
    @include('layouts.vendor.partials._alert-message')
    @include('vendor-views.partials._verification-modal')
</main>

<audio id="myAudio">
    <source src="{{ dynamicAsset(path: 'public/assets/backend/sound/notification.mp3') }}" type="audio/mpeg">
</audio>


<span class="please_fill_out_this_field" data-text="{{ translate('please_fill_out_this_field') }}"></span>
<span id="onerror-chatting" data-onerror-chatting="{{dynamicAsset(path: 'public/assets/back-end/img/image-place-holder.png')}}"></span>
<span id="onerror-user" data-onerror-user="{{dynamicAsset(path: 'public/assets/back-end/img/160x160/img1.jpg')}}"></span>
<span id="get-root-path-for-toggle-modal-image" data-path="{{dynamicAsset(path: 'public/assets/back-end/img/modal')}}"></span>
<span id="get-customer-list-route" data-action="{{route('vendor.customer.list')}}"></span>
<span id="get-search-product-route" data-action="{{route('vendor.products.search-product')}}"></span>
<span id="get-orders-list-route" data-action="{{route('vendor.orders.list', ['status' => 'all'])}}"></span>
<span class="system-default-country-code" data-value="{{ getWebConfig(name: 'country_code') ?? 'us' }}"></span>
<span id="message-select-word" data-text="{{ translate('select') }}"></span>
<span id="message-yes-word" data-text="{{ translate('yes') }}"></span>
<span id="message-no-word" data-text="{{ translate('no') }}"></span>
<span id="message-cancel-word" data-text="{{ translate('cancel') }}"></span>
<span id="message-are-you-sure" data-text="{{ translate('are_you_sure') }} ?"></span>
<span id="message-invalid-date-range" data-text="{{ translate('invalid_date_range') }}"></span>
<span id="message-status-change-successfully" data-text="{{ translate('status_change_successfully') }}"></span>
<span id="message-are-you-sure-delete-this" data-text="{{ translate('are_you_sure_to_delete_this') }} ?"></span>
<span id="message-you-will-not-be-able-to-revert-this"
      data-text="{{ translate('you_will_not_be_able_to_revert_this') }}"></span>
<span id="exceeds10MBSizeLimit" data-text="{{ translate('File_exceeds_10MB_size_limit') }}"></span>
<span id="getChattingNewNotificationCheckRoute" data-route="{{ route('vendor.messages.new-notification') }}"></span>
<span id="get-search-vendor-product-for-clearance-route" data-action="{{route('vendor.clearance-sale.search-product-for-clearance')}}"></span>
<span id="get-multiple-clearance-product-details-route" data-action="{{route('vendor.clearance-sale.multiple-clearance-product-details')}}"></span>

<span id="get-stock-limit-status" data-action="{{route('vendor.products.stock-limit-status')}}"></span>
<span id="get-product-stock-limit-title" data-title="{{translate('warning')}}"></span>
<span id="get-product-stock-limit-image" data-warning-image="{{ dynamicAsset(path: 'public/assets/back-end/img/warning-2.png') }}"></span>
<span id="get-product-stock-limit-message"
      data-message-for-multiple="{{ translate('there_is_not_enough_quantity_on_stock').' . '.translate('please_check_products_in_limited_stock').'.' }}"
      data-message-for-three-plus-product="{{translate('_more_products_have_low_stock') }}"
      data-message-for-one-product="{{translate('this_product_is_low_on_stock')}}">
    </span>
<span id="get-product-stock-view"
      data-stock-limit-page="{{route('vendor.products.stock-limit-list')}}"
>
    </span>
<span id="route-for-real-time-activities" data-route="{{ route('vendor.dashboard.real-time-activities') }}"></span>
<span id="get-confirm-and-cancel-button-text-for-delete-all-products" data-sure ="{{translate('are_you_sure').'?'}}"
      data-text="{{translate('want_to_clear_all_stock_clearance_products?').'!'}}"
      data-confirm="{{translate('yes_delete_it')}}" data-cancel="{{translate('cancel')}}"></span>

<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/vendor.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/theme.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/vendor/hs-navbar-vertical-aside/hs-navbar-vertical-aside-mini-cache.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/bootstrap.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/sweet_alert.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/js/lightbox.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/custom.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/app-script.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/vendor/form-validation-core.js') }}"></script>

{{-- Added Toastr JS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<span id="get-currency-symbol"
      data-currency-symbol="{{ getCurrencySymbol(currencyCode: getCurrencyCode(type: 'default')) }}"></span>

{!! ToastMagic::scripts() !!}

@if ($errors->any())
    <script>
        'use strict';
        @foreach($errors->all() as $error)
        toastMagic.error('{{ $error }}');
        @endforeach
    </script>
@endif

<script>
    'use strict'
    setInterval(function () {
        getInitialDataForPanel();
    }, 5000);
</script>

<script>
    // UPDATED: Use $(document).on() for Event Delegation to handle dynamically created elements (like Toasts)
    $(document).on('click', '.notification-data-view', function (e) {
        e.preventDefault();

        let id = $(this).data('id');
        let notificationType = $(this).data('notification-type') || 'system';
        let redirectUrl = $(this).data('redirect-url') || $(this).attr('href') || '{{ route("vendor.dashboard.index") }}';
        let markReadUrl = notificationType === 'custom'
            ? '{{ route("vendor.notifications.mark-as-read") }}'
            : '{{ route("vendor.notification.index") }}';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.post({
            url: markReadUrl,
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                notification_type: notificationType,
            },
        }).always(function () {
            window.location.href = redirectUrl;
        });
    });
    if (/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) document.write(
        '<script src="{{ dynamicAsset(path: 'public/assets/back-end') }}/vendor/babel-polyfill/polyfill.min.js"><\/script>');
</script>

@if(env('APP_MODE') == 'demo')
    <script>
        'use strict'
        function checkDemoResetTime() {
            let currentMinute = new Date().getMinutes();
            if (currentMinute > 55 && currentMinute <= 60) {
                $('#demo-reset-warning').addClass('active');
            } else {
                $('#demo-reset-warning').removeClass('active');
            }
        }
        checkDemoResetTime();
        setInterval(checkDemoResetTime, 60000);
    </script>
@endif

@push('script_2')
    <script>
        $(document).on('ready', function () {
            // Configure toastr options for sticky notifications
            if (typeof toastr !== 'undefined') {
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": true,
                    "progressBar": false,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": true,
                    "onclick": null,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "0", // 0 means it stays until action
                    "extendedTimeOut": "0",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut",
                    "tapToDismiss": false,
                    "escapeHtml": false, // Allows HTML buttons inside toast
                    // Toastr's own "X" button calls stopPropagation() internally, so a
                    // delegated document click handler never sees it — this callback is
                    // the only reliable hook toastr gives us for the close button.
                    "onCloseClick": function (e) {
                        persistNotificationDismissal($(e.target).closest('.toast').attr('data-notification-id'));
                    }
                };

                // KYC Approved/Rejected notification — controlled by sellers.kyc_notification_seen flag
                @if(isset($kycNotificationSeen) && $kycNotificationSeen === 0)
                    @if(($kycVerificationStatus ?? null) === 'rejected')
                        var kycMsgHtml = '{{ translate("Your KYC verification was rejected. Please review the reason and resubmit your documents.") }}' +
                            '<div class="vendor-notification-actions">' +
                                '<button type="button" class="btn btn-dark btn-sm kyc-notification-dismiss">{{ translate("Dismiss") }}</button>' +
                            '</div>';
                        var kycToast = toastr.error(kycMsgHtml, '{{ translate("KYC Rejected") }}');
                        if (kycToast) kycToast.addClass('vendor-notification-toast').attr('data-notification-id', 'kyc');
                    @else
                        var kycMsgHtml = '{{ translate("Congratulations! Your KYC verification has been approved. You can now start listing products.") }}' +
                            '<div class="vendor-notification-actions">' +
                                '<button type="button" class="btn btn-dark btn-sm kyc-notification-dismiss">{{ translate("Dismiss") }}</button>' +
                            '</div>';
                        var kycToast = toastr.success(kycMsgHtml, '{{ translate("KYC Approved") }}');
                        if (kycToast) kycToast.addClass('vendor-notification-toast').attr('data-notification-id', 'kyc');
                    @endif
                @endif

                // Loop through other unread notifications from AppServiceProvider
                @php
                    $toastNotifications = isset($unreadNotifications)
                        ? $unreadNotifications->reject(fn($notification) => str_starts_with($notification->title, 'New Message'))
                        : collect();
                @endphp
                @if($toastNotifications->count() > 0)
                    @foreach($toastNotifications as $notification)
                        var msgHtml = @json($notification->message) +
                            '<div class="vendor-notification-actions">' +
                                '<button type="button" class="btn btn-light btn-sm notification-data-view" data-id="{{ $notification->id }}" data-notification-type="custom" data-redirect-url="' + @json($notification->link ?: route('vendor.dashboard.index')) + '">View</button>' +
                                '<button type="button" class="btn btn-dark btn-sm notification-dismiss" data-id="{{ $notification->id }}">Dismiss</button>' +
                            '</div>';
                        var toast = toastr.info(msgHtml, @json($notification->title));
                        if (toast) toast.addClass('vendor-notification-toast').attr('data-notification-id', '{{ $notification->id }}');
                    @endforeach
                @endif
            } else {
                console.error('Toastr library is missing. Please check network/assets.');
            }
        });

        $(document).on('click', '.notification-dismiss', function () {
            markNotificationAsRead($(this).data('id'), this);
        });

        $(document).on('click', '.kyc-notification-dismiss', function () {
            var btn = $(this);
            var toastElement = btn.closest('.toast');
            $.post({
                url: '{{ route("vendor.notifications.mark-kyc-seen") }}',
                data: { _token: '{{ csrf_token() }}' },
                success: function () {
                    toastElement.fadeOut(500, function () { $(this).remove(); });
                }
            });
        });

        // Shared by toastr's "X" close button (via onCloseClick above) so dismissing
        // that way persists just like the custom Dismiss button does.
        function persistNotificationDismissal(notificationId) {
            if (!notificationId) {
                return;
            }
            if (notificationId === 'kyc') {
                $.post({
                    url: '{{ route("vendor.notifications.mark-kyc-seen") }}',
                    data: { _token: '{{ csrf_token() }}' },
                });
            } else {
                $.post({
                    url: '{{ route("vendor.notifications.mark-as-read") }}',
                    data: { _token: '{{ csrf_token() }}', id: notificationId },
                    success: function (response) {
                        $('.notification_data_new_badge' + notificationId).fadeOut();
                        if (response && typeof response.notification_count !== 'undefined') {
                            updateNotificationCount(response.notification_count);
                        }
                    },
                });
            }
        }

        function updateNotificationCount(count) {
            var countElem = $('.notification_data_new_count');
            var notificationCount = parseInt(count);

            if (notificationCount > 0) {
                countElem.text(notificationCount).fadeIn();
                return;
            }

            countElem.fadeOut();
        }

        // Function to dismiss notification (Mark as Read) without opening modal
        function markNotificationAsRead(notificationId, btnElement) {
            // Find the specific toast element for immediate hiding
            var toastElement = $(btnElement).closest('.toast');
            
            $.ajax({
                url: '{{ route("vendor.notifications.mark-as-read") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: notificationId
                },
                success: function(response) {
                    if(response.success) {
                        // Remove the toast smoothly
                        toastElement.fadeOut(500, function(){ $(this).remove(); });
                        $('.notification_data_new_badge' + notificationId).fadeOut();
                        
                        if (typeof response.notification_count !== 'undefined') {
                            updateNotificationCount(response.notification_count);
                        }
                    }
                },
                error: function() {
                    // If error, show toast again and display error message
                    toastElement.show(); 
                    if (typeof toastr !== 'undefined') {
                        toastr.options.timeOut = 5000;
                        toastr.error('{{ translate("Could_not_dismiss._Try_again.") }}');
                    }
                }
            });
        }
    </script>
@endpush

<script>
    (function () {
        if (window.finxcartVendorNotificationFallbackStarted) return;
        window.finxcartVendorNotificationFallbackStarted = true;

        function renderPortalBadge(toggleId, count) {
            var toggle = document.getElementById(toggleId);
            if (!toggle) return;
            var badge = toggle.querySelector('.notification_data_new_count');
            count = parseInt(count, 10) || 0;

            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'btn-status btn-sm-status btn-status-danger notification_data_new_count';
                    toggle.appendChild(badge);
                }
                badge.textContent = count > 99 ? '99+' : count;
            } else if (badge) {
                badge.remove();
            }
        }

        function requestPortalNotifications(url, callback) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url + (url.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now(), true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4 || xhr.status < 200 || xhr.status >= 300) return;
                try {
                    callback(JSON.parse(xhr.responseText));
                } catch (e) {}
            };
            xhr.send();
        }

        function syncVendorNotificationBadge() {
            requestPortalNotifications('{{ route('vendor.notifications.poll') }}', function (data) {
                if (!data) return;
                renderPortalBadge('vendorNotificationToggle', data.count || 0);
                var itemsContainer = document.getElementById('vendorNotificationItems');
                if (itemsContainer && typeof data.html === 'string') {
                    itemsContainer.innerHTML = data.html;
                }
            });
        }
        syncVendorNotificationBadge();
        setInterval(syncVendorNotificationBadge, 2000);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') syncVendorNotificationBadge();
        });
    })();
</script>
@stack('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/common-script.js') }}"></script>
@stack('script_2')

</body>
</html>
