<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? "ltr" }}">

<head>
    <meta charset="utf-8">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="robots" content="nofollow, noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>@yield('title')</title>
    <link rel="shortcut icon"
          href="{{ getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type: 'backend-logo') }}">

    @include("layouts.admin.partials._style-partials")

    {!! ToastMagic::styles() !!}
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/premium-toasts.css') }}">

    @stack('css_or_js')
</head>

<body data-bs-theme="light">
<script type="text/javascript">
    localStorage.getItem('aside-mini') === 'true' ? document.body.classList.add('aside-mini') : document.body.classList.remove('aside-mini');
</script>

<div class="row">
    <div class="col-12 position-fixed loader-container mt-10rem">
        <div id="loading" class="d--none">
            <div id="loader"></div>
        </div>
    </div>
</div>

@include('layouts.admin.partials._header')
@include('layouts.admin.partials._side-bar')

<main id="content" role="main" class="main-content">
    @yield('content')
    @include('layouts.admin.partials._toggle-modal')
    @include('layouts.admin.components.image-modal')
    @include('layouts.admin.partials._sign-out-modal')
    @include('layouts.admin.partials._modals')
    @include('layouts.admin.partials._alert-message')
</main>

<audio id="myAudio">
    <source src="{{ dynamicAsset(path: 'public/assets/backend/sound/notification.mp3') }}" type="audio/mpeg">
</audio>

@include('layouts.admin.partials._translator-for-js')
@include("layouts.admin.partials._translated-message-container")
@include("layouts.admin.partials._routes-list-container")
@include("layouts.admin.partials._script-partials")

<script>
    (function () {
        if (window.finxcartAdminNotificationFallbackStarted) return;
        window.finxcartAdminNotificationFallbackStarted = true;

        function updateAdminBadge(count) {
            var toggle = document.getElementById('adminNotificationToggle');
            var badge = document.getElementById('admin-notification-badge');
            var unreadText = document.getElementById('admin-notification-unread-text');
            var unreadCount = document.getElementById('admin-notification-unread-count');
            count = parseInt(count, 10) || 0;

            if (count > 0) {
                if (!badge && toggle) {
                    badge = document.createElement('span');
                    badge.className = 'admin-notification-badge';
                    badge.id = 'admin-notification-badge';
                    toggle.appendChild(badge);
                }
                if (badge) {
                    badge.dataset.count = count;
                    badge.textContent = count > 99 ? '99+' : count;
                }
                if (unreadText) unreadText.style.display = '';
                if (unreadCount) unreadCount.textContent = count;
            } else {
                if (badge) badge.remove();
                if (unreadText) unreadText.style.display = 'none';
                if (unreadCount) unreadCount.textContent = 0;
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

        function syncAdminNotificationBadge() {
            requestPortalNotifications('{{ route('admin.admin-notifications.poll') }}', function (data) {
                if (!data) return;
                updateAdminBadge(data.count || 0);
                var itemsContainer = document.getElementById('adminNotificationItems');
                if (itemsContainer && typeof data.html === 'string') {
                    itemsContainer.innerHTML = data.html;
                }
            });
        }
        syncAdminNotificationBadge();
        setInterval(syncAdminNotificationBadge, 2000);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') syncAdminNotificationBadge();
        });
    })();
</script>
@stack('script')
</body>

</html>
