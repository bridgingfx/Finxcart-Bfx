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
@include('layouts.freelancer.partials._header')
@include('layouts.freelancer.partials._side-bar')

<main id="content" role="main" class="main pointer-event" style="padding-bottom: 4rem">

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

    @include('layouts.freelancer.partials._modals')
    @include('layouts.vendor.partials._toggle-modal')
    @include('layouts.vendor.partials._translator-for-js')
    @include('layouts.freelancer.partials._sign-out-modal')
</main>

<audio id="myAudio">
    <source src="{{ dynamicAsset(path: 'public/assets/backend/sound/notification.mp3') }}" type="audio/mpeg">
</audio>

<span class="please_fill_out_this_field" data-text="{{ translate('please_fill_out_this_field') }}"></span>
<span id="get-root-path-for-toggle-modal-image" data-path="{{dynamicAsset(path: 'public/assets/back-end/img/modal')}}"></span>
<span class="system-default-country-code" data-value="{{ getWebConfig(name: 'country_code') ?? 'us' }}"></span>
<span id="message-select-word" data-text="{{ translate('select') }}"></span>
<span id="message-yes-word" data-text="{{ translate('yes') }}"></span>
<span id="message-no-word" data-text="{{ translate('no') }}"></span>
<span id="message-cancel-word" data-text="{{ translate('cancel') }}"></span>
<span id="message-are-you-sure" data-text="{{ translate('are_you_sure') }} ?"></span>
<span id="message-status-change-successfully" data-text="{{ translate('status_change_successfully') }}"></span>
<span id="message-are-you-sure-delete-this" data-text="{{ translate('are_you_sure_to_delete_this') }} ?"></span>
<span id="message-you-will-not-be-able-to-revert-this" data-text="{{ translate('you_will_not_be_able_to_revert_this') }}"></span>
<span id="exceeds10MBSizeLimit" data-text="{{ translate('File_exceeds_10MB_size_limit') }}"></span>

<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/vendor.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/theme.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/vendor/hs-navbar-vertical-aside/hs-navbar-vertical-aside-mini-cache.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/bootstrap.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/sweet_alert.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/custom.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/app-script.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/vendor/form-validation-core.js') }}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<span id="get-currency-symbol" data-currency-symbol="{{ getCurrencySymbol(currencyCode: getCurrencyCode(type: 'default')) }}"></span>

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
    $(document).on('click', '.notification-data-view', function (e) {
        e.preventDefault();

        let id = $(this).data('id');
        let notificationType = $(this).data('notification-type') || 'system';
        let redirectUrl = $(this).data('redirect-url') || $(this).attr('href') || '{{ route("freelancer.dashboard.index") }}';
        if (typeof redirectUrl !== 'string' || redirectUrl.indexOf('[object Object]') !== -1) {
            redirectUrl = '{{ route("freelancer.messages.admin") }}';
        }
        let markReadUrl = notificationType === 'custom'
            ? '{{ route("freelancer.notifications.mark-as-read") }}'
            : '{{ route("freelancer.notification.index") }}';

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

@push('script_2')
    <script>
        $(document).on('ready', function () {
            if (typeof toastr !== 'undefined') {
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": true,
                    "progressBar": false,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": true,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "0",
                    "extendedTimeOut": "0",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut",
                    "tapToDismiss": false,
                    "escapeHtml": false,
                    // Toastr's own "X" button calls stopPropagation() internally, so a
                    // delegated document click handler never sees it — this callback is
                    // the only reliable hook toastr gives us for the close button.
                    "onCloseClick": function (e) {
                        persistNotificationDismissal($(e.target).closest('.toast').attr('data-notification-id'));
                    }
                };

                @if(isset($kycNotificationSeen) && $kycNotificationSeen === 0)
                    @if(($kycVerificationStatus ?? null) === 'rejected')
                        var kycMsgHtml = '{{ translate("Your KYC verification was rejected. Please review the reason and resubmit your documents.") }}' +
                            '<div class="vendor-notification-actions"><button type="button" class="btn btn-dark btn-sm kyc-notification-dismiss">{{ translate("Dismiss") }}</button></div>';
                        var kycToast = toastr.error(kycMsgHtml, '{{ translate("KYC Rejected") }}');
                        if (kycToast) kycToast.addClass('vendor-notification-toast').attr('data-notification-id', 'kyc');
                    @else
                        var kycMsgHtml = '{{ translate("Congratulations! Your KYC verification has been approved.") }}' +
                            '<div class="vendor-notification-actions"><button type="button" class="btn btn-dark btn-sm kyc-notification-dismiss">{{ translate("Dismiss") }}</button></div>';
                        var kycToast = toastr.success(kycMsgHtml, '{{ translate("KYC Approved") }}');
                        if (kycToast) kycToast.addClass('vendor-notification-toast').attr('data-notification-id', 'kyc');
                    @endif
                @endif

                @php
                    $toastNotifications = isset($unreadNotifications)
                        ? $unreadNotifications->reject(fn($notification) => str_starts_with($notification->title, 'New Message'))
                        : collect();
                @endphp
                @if($toastNotifications->count() > 0)
                    @foreach($toastNotifications as $notification)
                        @php
                            $toastFallbackLink = $notification->title === 'New Message from Admin'
                                ? route('freelancer.messages.admin')
                                : route('freelancer.dashboard.index');
                            $toastNotificationLink = $notification->link ?: $toastFallbackLink;
                        @endphp
                        var msgHtml = @json($notification->message) +
                            '<div class="vendor-notification-actions">' +
                                '<button type="button" class="btn btn-light btn-sm notification-data-view" data-id="{{ $notification->id }}" data-notification-type="custom" data-redirect-url="' + @json($toastNotificationLink) + '">View</button>' +
                                '<button type="button" class="btn btn-dark btn-sm notification-dismiss" data-id="{{ $notification->id }}">Dismiss</button>' +
                            '</div>';
                        var toast = toastr.info(msgHtml, @json($notification->title));
                        if (toast) toast.addClass('vendor-notification-toast').attr('data-notification-id', '{{ $notification->id }}');
                    @endforeach
                @endif
            }
        });

        $(document).on('click', '.notification-dismiss', function () {
            markNotificationAsRead($(this).data('id'), this);
        });

        $(document).on('click', '.kyc-notification-dismiss', function () {
            var toastElement = $(this).closest('.toast');
            $.post({
                url: '{{ route("freelancer.notifications.mark-kyc-seen") }}',
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
                    url: '{{ route("freelancer.notifications.mark-kyc-seen") }}',
                    data: { _token: '{{ csrf_token() }}' },
                });
            } else {
                $.post({
                    url: '{{ route("freelancer.notifications.mark-as-read") }}',
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

        function markNotificationAsRead(notificationId, btnElement) {
            var toastElement = $(btnElement).closest('.toast');
            $.ajax({
                url: '{{ route("freelancer.notifications.mark-as-read") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', id: notificationId },
                success: function(response) {
                    if(response.success) {
                        toastElement.fadeOut(500, function(){ $(this).remove(); });
                        $('.notification_data_new_badge' + notificationId).fadeOut();
                        if (typeof response.notification_count !== 'undefined') {
                            updateNotificationCount(response.notification_count);
                        }
                    }
                },
                error: function() {
                    toastElement.show();
                }
            });
        }
    </script>
@endpush

<script>
    (function () {
        if (window.finxcartFreelancerNotificationFallbackStarted) return;
        window.finxcartFreelancerNotificationFallbackStarted = true;

        // The header partial (layouts.freelancer.partials._header) already runs the
        // primary poller and exposes it as window.finxcartSyncNotifications. Running
        // both unconditionally meant every freelancer page fired ~1.7 polls/sec
        // against the same heavy endpoint, all the time. Only start this fallback
        // loop if the primary poller genuinely isn't present.
        if (typeof window.finxcartSyncNotifications === 'function') return;

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

        function syncFreelancerNotificationBadge() {
            requestPortalNotifications('{{ route('freelancer.notifications.poll') }}', function (data) {
                if (!data) return;
                renderPortalBadge('freelancerNotificationToggle', data.count || 0);
                var itemsContainer = document.getElementById('freelancerNotificationItems');
                if (itemsContainer && typeof data.html === 'string') {
                    itemsContainer.innerHTML = data.html;
                }
            });
        }
        syncFreelancerNotificationBadge();
        setInterval(syncFreelancerNotificationBadge, 2000);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') syncFreelancerNotificationBadge();
        });
    })();
</script>
@stack('script')
@stack('script_2')

</body>
</html>
