@php($notification_data=$systemNotifications ?? collect())
@foreach ($notification_data as $item)
    <a href="{{ route('vendor.dashboard.index') }}" class="dropdown-item position-relative notification-data-view" data-id="{{ $item->id }}" data-notification-type="system" data-redirect-url="{{ route('vendor.dashboard.index') }}">
    <span class="text-truncate pr-2 d-block"
          >{{translate($item->title)}}</span>
        <span class="fs-10">{{ $item->created_at->diffforHumans() }}</span>
        @if($item->notificationSeenBy == null)
            <span
                class="badge-soft-danger float-right small py-1 px-2 rounded notification_data_new_badge{{ $item->id }}">{{translate('new')}}</span>
        @endif
    </a>
    <div class="dropdown-divider"></div>
@endforeach
@if(isset($unreadNotifications))
    @foreach ($unreadNotifications as $item)
        @php($rawNotificationLink = is_string($item->link) ? trim($item->link) : '')
        {{-- $item->link is a free-form string column — guard against anything that
             isn't a real relative/absolute URL (e.g. bad data, or a stray JS object
             that got stringified to "[object Object]" before being saved) ending up
             as a clickable href/redirect target. --}}
        @php($notificationLink = ($rawNotificationLink !== '' && !str_contains($rawNotificationLink, 'object Object') && (str_starts_with($rawNotificationLink, '/') || filter_var($rawNotificationLink, FILTER_VALIDATE_URL))) ? $rawNotificationLink : route('vendor.dashboard.index'))
        <a href="{{ $notificationLink }}" class="dropdown-item position-relative notification-data-view" data-id="{{ $item->id }}" data-notification-type="custom" data-redirect-url="{{ $notificationLink }}">
            <span class="text-truncate pr-2 d-block"
                  >{{ $item->title }}</span>
            <span class="fs-10">{{ $item->created_at->diffForHumans() }}</span>
            <span
                class="badge-soft-danger float-right small py-1 px-2 rounded notification_data_new_badge{{ $item->id }}">{{translate('new')}}</span>
        </a>
        <div class="dropdown-divider"></div>
    @endforeach
@endif

@if($notification_data->isEmpty() && (!isset($unreadNotifications) || $unreadNotifications->isEmpty()))
    <div class="d-flex flex-column align-items-center justify-content-center py-4 px-3 text-center">
        <i class="tio-notifications-off-outlined fs-40 text-muted mb-2"></i>
        <p class="mb-0 text-muted fs-13">{{ translate('no_notification_found') }}</p>
    </div>
@endif
