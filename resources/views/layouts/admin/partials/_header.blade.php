@php
    $pendingOrderQuery = \App\Models\Order::where('order_status', 'pending');
    $pendingOrderCount = Cache::remember('admin_header_pending_order_count', CACHE_FOR_1_MINUTE, fn() => (clone $pendingOrderQuery)->count());
    $last5Orders = (clone $pendingOrderQuery)->with('details.productAllStatus')->orderBy('id', 'desc')->take(5)->get();

    $unreadContactQuery = \App\Models\Contact::where('seen', 0);
    $unreadContactMessageCount = Cache::remember('admin_header_unread_contact_count', CACHE_FOR_1_MINUTE, fn() => (clone $unreadContactQuery)->count());
    $unreadContactMessages = (clone $unreadContactQuery)->latest()->take(5)->get();

    $adminChattingUnseenQuery = \App\Models\Chatting::where('admin_id', 0)
        ->where('seen_by_admin', 0)
        ->where('notification_receiver', 'admin');
    $adminChattingUnseenCount = Cache::remember('admin_header_chatting_unseen_count', CACHE_FOR_1_MINUTE, fn() => (clone $adminChattingUnseenQuery)->count());
    $adminChattingNotifications = (clone $adminChattingUnseenQuery)
        ->with(['customer', 'deliveryMan', 'seller'])
        ->latest()
        ->take(5)
        ->get()
        ->map(function ($chat) {
            if ($chat->user_id) {
                $chat->notification_label = translate('customer');
                $chat->notification_name = trim(($chat->customer->f_name ?? '') . ' ' . ($chat->customer->l_name ?? '')) ?: translate('customer');
                $chat->notification_link = route('admin.messages.index', ['type' => 'customer']);
            } elseif ($chat->delivery_man_id) {
                $chat->notification_label = translate('delivery_man');
                $chat->notification_name = trim(($chat->deliveryMan->f_name ?? '') . ' ' . ($chat->deliveryMan->l_name ?? '')) ?: translate('delivery_man');
                $chat->notification_link = route('admin.messages.index', ['type' => 'delivery-man']);
            } else {
                $chat->notification_label = translate('freelancer');
                $chat->notification_name = trim(($chat->seller->f_name ?? '') . ' ' . ($chat->seller->l_name ?? '')) ?: translate('freelancer');
                $chat->notification_link = route('admin.freelancer.messages.index', ['seller_id' => $chat->seller_id]);
            }
            return $chat;
        });

    $adminSystemNotificationQuery = \App\Models\AdminNotification::whereNull('read_at');
    $adminSystemNotificationCount = Cache::remember('admin_header_system_notification_count', CACHE_FOR_1_MINUTE, fn() => (clone $adminSystemNotificationQuery)->count());
    $adminSystemNotifications = (clone $adminSystemNotificationQuery)
        ->latest()
        ->take(5)
        ->get();

    $totalAdminNotificationCount = $unreadContactMessageCount + $adminChattingUnseenCount + $adminSystemNotificationCount;
@endphp

<style>
    .admin-dashboard-header .admin-header-actions {
        min-width: 0;
    }
    .admin-dashboard-header {
        border-bottom: 1px solid rgba(7, 59, 116, .12);
        box-shadow: 0 5px 18px rgba(7, 59, 116, .08) !important;
    }
    .admin-dashboard-header .navbar-nav {
        flex-wrap: nowrap;
    }
    .admin-dashboard-header .admin-menu-button,
    .admin-dashboard-header .admin-action-button,
    .admin-dashboard-header .gtranslate-dashboard-button {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        flex-shrink: 0;
        padding: 0;
        border: 1px solid rgba(7, 59, 116, .08) !important;
        border-radius: 50%;
        color: #073b74 !important;
        background: rgba(7, 59, 116, .06) !important;
        text-decoration: none;
        transition: background-color .2s ease, color .2s ease, transform .2s ease, box-shadow .2s ease;
    }
    .admin-dashboard-header .admin-menu-button:hover,
    .admin-dashboard-header .admin-action-button:hover,
    .admin-dashboard-header .gtranslate-dashboard-button:hover {
        color: #fff !important;
        background: #073b74 !important;
        box-shadow: 0 7px 16px rgba(7, 59, 116, .22);
        transform: translateY(-2px);
    }
    .admin-dashboard-header .admin-action-button .badge,
    .admin-dashboard-header .admin-action-button .btn-status {
        border: 2px solid #fff;
    }
    .admin-dashboard-header .admin-notification-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        border-radius: 999px;
        background: #e53935;
        color: #fff;
        font-size: 10px;
        line-height: 1;
        font-weight: 700;
        box-shadow: 0 0 0 2px rgba(229, 57, 53, .12);
    }
    .admin-dashboard-header .admin-message-dropdown {
        width: min(380px, calc(100vw - 20px));
        padding: 0;
        overflow: hidden;
    }
    .admin-dashboard-header .admin-message-dropdown-header {
        padding: 16px 18px;
        border-bottom: 1px solid rgba(7, 59, 116, .09);
    }
    .admin-dashboard-header .admin-message-item {
        display: block;
        padding: 12px 18px;
        border-bottom: 1px solid rgba(7, 59, 116, .07);
    }
    .admin-dashboard-header .admin-message-item:last-child {
        border-bottom: 0;
    }
    .admin-dashboard-header .admin-message-item:hover {
        background: rgba(7, 59, 116, .05);
    }
    .admin-dashboard-header .admin-message-preview {
        overflow: hidden;
        color: #75849a;
        font-size: 12px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .admin-dashboard-header .admin-message-empty {
        min-height: 110px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        color: #a5afbf;
        font-weight: 600;
    }
    .admin-dashboard-header .admin-profile-trigger {
        padding: 4px 6px 4px 12px;
        border: 1px solid rgba(7, 59, 116, .1);
        border-radius: 25px;
        color: #073b74;
        background: rgba(7, 59, 116, .04);
        transition: background-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }
    .admin-dashboard-header .admin-profile-trigger:hover {
        color: #073b74;
        background: rgba(7, 59, 116, .09);
        box-shadow: 0 7px 16px rgba(7, 59, 116, .12);
        transform: translateY(-1px);
    }
    .admin-dashboard-header .admin-profile-trigger img {
        border-color: rgba(7, 59, 116, .25) !important;
    }
    .admin-dashboard-header .dropdown-menu {
        border: 1px solid rgba(7, 59, 116, .1);
        border-radius: 12px;
        box-shadow: 0 14px 34px rgba(7, 59, 116, .14);
    }
    .admin-dashboard-header .dropdown-item {
        transition: color .15s ease, background-color .15s ease;
    }
    .admin-dashboard-header .dropdown-item:hover {
        color: #073b74;
        background: rgba(7, 59, 116, .06);
    }
    @media (max-width: 575.98px) {
        .admin-dashboard-header {
            padding-inline: 12px;
        }
        .admin-dashboard-header .navbar-nav {
            gap: 6px !important;
        }
        .admin-dashboard-header .admin-menu-button,
        .admin-dashboard-header .admin-action-button,
        .admin-dashboard-header .gtranslate-dashboard-button {
            width: 34px;
            height: 34px;
        }
        .admin-dashboard-header .admin-profile-trigger {
            padding: 2px;
            border: 0;
            background: transparent;
        }
        .admin-dashboard-header .dropdown-cart {
            position: fixed !important;
            top: 64px !important;
            right: 8px !important;
            left: 8px !important;
            width: auto !important;
            transform: none !important;
        }
        .admin-dashboard-header .admin-message-dropdown {
            position: fixed !important;
            top: 64px !important;
            right: 8px !important;
            left: 8px !important;
            width: auto !important;
            transform: none !important;
        }
    }
</style>

<header class="header fixed-top navbar-fixed shadow-sm bg-white admin-dashboard-header">
    <div class="d-flex align-items-center justify-content-between gap-3">
        <div class="">
            <button type="button" class="d-none d-lg-flex admin-menu-button">
                <i class="fi fi-rr-menu-burger" data-bs-toggle="tooltip" data-bs-title="Expand"></i>
            </button>
            <button type="button" class="d-lg-none admin-menu-button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasAside">
                <i class="fi fi-rr-menu-burger"></i>
            </button>
        </div>

        <div class="navbar-nav-wrap-content-right admin-header-actions">
            <ul class="navbar-nav align-items-center flex-row gap-3">
                <li class="nav-item">
                    <a class="admin-action-button" href="{{ route('home') }}" target="_blank" data-bs-toggle="tooltip"
                       data-bs-title="{{ translate('Website') }}">
                        <i class="fi fi-rr-globe fs-18"></i>
                    </a>
                </li>

                <li class="nav-item">
                    @includeIf('layouts.front-end.partials.gtranslater', [
                        'gtranslateBootstrapVersion' => 5,
                        'gtranslateDashboard' => true,
                    ])
                </li>

                @if(\App\Utils\Helpers::module_permission_check('order_management'))
                    <li class="dropdown nav-item">
                        <a class="admin-action-button" href="{{ route('admin.orders.list',['status'=>'pending']) }}"
                           data-bs-toggle="dropdown">
                            <div class="position-relative">
                                <i class="fi fi-sr-shopping-cart fs-18"></i>
                                @if($pendingOrderCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $pendingOrderCount > 99 ? '99+' : $pendingOrderCount }}
                                    <span class="visually-hidden">{{ translate('pending_Orders') }}</span>
                                </span>
                                @endif
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-cart dropdown-menu-end">
                            <div class="d-flex flex-column gap-2 px-3 pb-2">
                                <button type="button" class="d-flex d-sm-none btn-close border-0 btn-circle w-20 h-20 p-1 fs-10 bg-section2 shadow-none position-absolute top-0 inset-inline-end-0 m-2" data-bs-dismiss="modal" aria-label="Close"></button>
                                <div class="d-flex flex-wrap flex-column flex-sm-row column-gap-1 row-gap-3 justify-content-between py-2">
                                    <div class="d-flex gap-2 align-items-center">
                                        <h4 class="text-capitalize mb-0">{{ translate('total_orders') }}</h4>
                                        <span class="text-body-light">
                                            ({{ $pendingOrderCount }})
                                        </span>
                                    </div>
                                    <a href="{{ route('admin.orders.list',['status'=>'pending']) }}"
                                       class="text-primary d-flex gap-1 lh-1">{{ translate('view_all') }} <i class="fi fi-rr-arrow-small-right"></i></a>
                                </div>
                                <div class="overflow-auto max-h-65vh bg-body rounded">
                                    <div class="py-2 bg-body rounded">
                                        <table
                                        class="table bg-transparent table-borderless align-middle">
                                            <tbody>
                                                @foreach($last5Orders as $order)
                                                    <tr>
                                                        <td>
                                                            <div>
                                                                <div class="d-flex align-items-center flex-shrink-0 w-100px">
                                                                    @php($productImages = [])
                                                                    @foreach($order->details as $details)
                                                                        @if($details?->productAllStatus?->thumbnail_full_url && $details?->productAllStatus?->thumbnail_full_url['status'] === 200)
                                                                        @php($productImages[] = $details?->productAllStatus?->thumbnail_full_url)
                                                                        @endif
                                                                    @endforeach
                                                                    @if(count($productImages))
                                                                        @foreach($productImages as $imageKey => $productImage)
                                                                            <div class="w-100 ms-n-6px dropdown-cart-image {{ $imageKey == 2 ? 'position-relative' : '' }} {{ $imageKey > 2 ? 'd-none' : '' }}">
                                                                                <img
                                                                                    class="border bg-white rounded object-fit-cover w-100 shadow-left"
                                                                                    height="36" alt="product image"
                                                                                    src="{{ getStorageImages(path: $productImage, type: 'backend-product') }}">
                                                                                @if($imageKey == 2 && count($productImages) > 3)
                                                                                    <div class="extra-images rounded">
                                                                                        <span class="extra-image-count">
                                                                                            + {{ count($productImages) - 3 }}
                                                                                        </span>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        @endforeach
                                                                    @else
                                                                        <img class="border bg-white rounded object-fit-cover w-100"
                                                                            height="36" src="https://placehold.co/40x40" alt="placeholder">
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex flex-column gap-1 fs-12">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <div class="min-w-80 text-start">{{ translate('Order_id') }}</div>
                                                                    :
                                                                    <span class="text-dark">{{ $order->id }}</span>
                                                                </div>
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <div class="min-w-80 text-start">{{ translate('Order_Amount') }}</div>
                                                                    :
                                                                    <span class="text-dark">
                                                                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $order->order_amount), currencyCode: getCurrencyCode()) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex justify-content-end">
                                                                <a href="{{ route('admin.orders.details', ['id' => $order['id']]) }}"
                                                                class="btn btn-outline-primary btn-square">
                                                                    <i class="fi fi-sr-eye"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endif

                <li class="nav-item dropdown">
                    <a class="admin-action-button" href="javascript:" id="adminNotificationToggle"
                       data-bs-toggle="dropdown" data-bs-auto-close="outside"
                       aria-expanded="false" aria-label="{{ translate('notifications') }}">
                        <i class="fi fi-sr-bell fs-18"></i>
                        @if($totalAdminNotificationCount > 0)
                            <span class="admin-notification-badge" id="admin-notification-badge"
                                  data-count="{{ $totalAdminNotificationCount }}"
                                  aria-label="{{ $totalAdminNotificationCount }} {{ translate('unread_messages') }}">{{ $totalAdminNotificationCount > 99 ? '99+' : $totalAdminNotificationCount }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end admin-message-dropdown">
                        <div class="admin-message-dropdown-header d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <h4 class="mb-0">{{ translate('notifications') }}</h4>
                                <span class="fs-11 text-body-light" id="admin-notification-unread-text" @if($totalAdminNotificationCount == 0) style="display:none;" @endif>
                                    <span id="admin-notification-unread-count">{{ $totalAdminNotificationCount }}</span> {{ translate('unread') }}
                                </span>
                            </div>
                            <a href="javascript:" id="admin-notifications-clear-all" class="fs-12 text-primary fw-semibold text-uppercase flex-shrink-0" @if($totalAdminNotificationCount == 0) style="display:none;" @endif>
                                {{ translate('clear_all') }}
                            </a>
                        </div>

                        <div id="adminNotificationItems">
                            @include('layouts.admin.partials._notification-items')
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <div class="dropdown">
                        <a class="d-flex align-items-center gap-2 admin-profile-trigger" href="javascript:" data-bs-toggle="dropdown">
                            <div class="d-none d-md-block text-end">
                                <h5 class="mb-0 fs-13">{{ auth('admin')->user()->name }}</h5>
                                <span class="d-block fs-10 text-body-light">{{ ucwords(auth('admin')->user()->role->name) ?? '' }}</span>
                            </div>
                            <img class="rounded-circle border border-2 min-w-36 aspect-1" width="36"
                                 src="{{ getStorageImages(path: auth('admin')->user()->image_full_url, type: 'backend-profile') }}"
                                 alt="{{ translate('image_description') }}">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="dropdown-item">
                                <div class="media gap-2 align-items-center">
                                    <img class="rounded-circle border border-2 aspect-1" width="40"
                                         src="{{ getStorageImages(path: auth('admin')->user()->image_full_url, type: 'backend-profile') }}"
                                         alt="{{ translate('image_description') }}">

                                    <div class="media-body">
                                        <h4 class="fw-bold mb-1">{{ auth('admin')->user()->name }}</h4>
                                        <p class="fs-12 text-body-light fw-medium">
                                            {{ ucwords(auth('admin')->user()->role->name) ?? '' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item media gap-2 align-items-center"
                               href="{{ route('admin.profile.update', ['id' => auth('admin')->user()->id]) }}">
                                <i class="fi fi-rr-settings text-body-light"></i>
                                <span class="text-truncate media-body" title="{{ translate('settings') }}">
                                    {{ translate('settings') }}
                                </span>
                            </a>
                            <a class="dropdown-item media gap-2 align-items-center" href="javascript:"
                               data-bs-toggle="modal" data-bs-target="#sign-out-modal">
                                <i class="fi fi-sr-sign-out-alt text-body-light"></i>
                                <span class="text-truncate media-body" title="{{ translate('logout') }}">
                                    {{ translate('logout') }}
                                </span>
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

@push('script')
    <script>
        (function () {
            var itemsContainer = document.getElementById('adminNotificationItems');

            function getNotificationBadge() {
                return document.getElementById('admin-notification-badge');
            }

            function decrementAdminNotificationCount() {
                var notificationBadge = getNotificationBadge();
                var unreadTextEl = document.getElementById('admin-notification-unread-text');
                var unreadCountEl = document.getElementById('admin-notification-unread-count');

                if (!notificationBadge) {
                    return;
                }

                var count = Math.max(0, (parseInt(notificationBadge.dataset.count, 10) || 0) - 1);
                notificationBadge.dataset.count = count;

                if (count === 0) {
                    notificationBadge.remove();
                    if (unreadTextEl) {
                        unreadTextEl.style.display = 'none';
                    }
                    return;
                }

                notificationBadge.textContent = count > 99 ? '99+' : count;
                notificationBadge.setAttribute('aria-label', count + ' {{ translate('unread_messages') }}');
                if (unreadCountEl) {
                    unreadCountEl.textContent = count;
                }
            }

            function updateAdminNotificationBadge(count) {
                var toggle = document.getElementById('adminNotificationToggle');
                var notificationBadge = getNotificationBadge();
                var unreadTextEl = document.getElementById('admin-notification-unread-text');
                var unreadCountEl = document.getElementById('admin-notification-unread-count');

                if (count > 0) {
                    if (!notificationBadge && toggle) {
                        notificationBadge = document.createElement('span');
                        notificationBadge.className = 'admin-notification-badge';
                        notificationBadge.id = 'admin-notification-badge';
                        toggle.appendChild(notificationBadge);
                    }
                    if (notificationBadge) {
                        notificationBadge.dataset.count = count;
                        notificationBadge.textContent = count > 99 ? '99+' : count;
                        notificationBadge.setAttribute('aria-label', count + ' {{ translate('unread_messages') }}');
                    }
                    if (unreadTextEl) {
                        unreadTextEl.style.display = '';
                    }
                    if (unreadCountEl) {
                        unreadCountEl.textContent = count;
                    }
                } else {
                    if (notificationBadge) {
                        notificationBadge.remove();
                    }
                    if (unreadTextEl) {
                        unreadTextEl.style.display = 'none';
                    }
                    if (unreadCountEl) {
                        unreadCountEl.textContent = 0;
                    }
                }
            }

            if (itemsContainer) {
                itemsContainer.addEventListener('click', function (e) {
                    var item = e.target.closest('.admin-system-notification-item');
                    if (item && itemsContainer.contains(item)) {
                        if (item.dataset.read === '1') {
                            return;
                        }
                        item.dataset.read = '1';

                        var href = item.getAttribute('href');
                        var willNavigate = !!href && href !== 'javascript:';

                        if (willNavigate) {
                            e.preventDefault();
                        }

                        $.post({
                            url: '{{ route("admin.admin-notifications.mark-as-read") }}',
                            data: { _token: '{{ csrf_token() }}', id: item.dataset.notificationId },
                        }).always(function () {
                            if (willNavigate) {
                                window.location.href = href;
                            }
                        });

                        decrementAdminNotificationCount();
                        return;
                    }

                    var contactItem = e.target.closest('.admin-contact-notification-item');
                    if (contactItem && itemsContainer.contains(contactItem)) {
                        if (contactItem.dataset.read !== '1') {
                            contactItem.dataset.read = '1';
                            decrementAdminNotificationCount();
                        }
                        return;
                    }

                });
            }

            var clearAllLink = document.getElementById('admin-notifications-clear-all');
            if (clearAllLink) {
                clearAllLink.addEventListener('click', function (e) {
                    e.preventDefault();

                    $.post({
                        url: '{{ route("admin.admin-notifications.mark-all-read") }}',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function () {
                            updateAdminNotificationBadge(0);
                            clearAllLink.style.display = 'none';
                            pollAdminNotifications();
                        },
                    });
                });
            }

            function pollAdminNotifications() {
                $.get({
                    url: '{{ route("admin.admin-notifications.poll") }}',
                    cache: false,
                }).done(function (response) {
                        if (!response) {
                            return;
                        }
                        if (itemsContainer && typeof response.html === 'string') {
                            itemsContainer.innerHTML = response.html;
                        }
                        updateAdminNotificationBadge(response.count || 0);
                    });
            }

            window.finxcartSyncNotifications = pollAdminNotifications;
            pollAdminNotifications();
            setInterval(pollAdminNotifications, 3000);

            // Re-sync immediately on returning to this tab instead of waiting for the
            // next interval tick — a background tab throttles setInterval anyway, so
            // without this a badge can sit stale for well over 8s until you switch back.
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'visible') {
                    pollAdminNotifications();
                }
            });
        })();
    </script>
@endpush
