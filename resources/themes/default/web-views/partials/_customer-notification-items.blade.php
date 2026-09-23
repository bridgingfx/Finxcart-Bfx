@forelse($customerNotifications as $notification)
    <a href="{{ route('customer-notifications.read', $notification->id) }}"
        class="dropdown-item text-wrap {{ $notification->read_at ? '' : 'bg-light font-weight-bold' }}"
        style="padding:10px 16px 10px 22px;border-bottom:1px solid #f0f0f0;white-space:normal;">
        <div class="small">{{ $notification->title }}</div>
        <div class="text-muted font-weight-normal" style="font-size:12px;">
            {{ \Illuminate\Support\Str::limit($notification->message, 70) }}
        </div>
        <div class="text-muted font-weight-normal" style="font-size:11px;">
            {{ $notification->created_at->diffForHumans() }}
        </div>
    </a>
@empty
    <div class="text-center text-muted p-4" style="font-size:13px;">
        <i class="fi fi-rr-bell d-block font-size-22 mb-2" style="color:#c9d2db;"></i>
        {{ translate('no_notification_found') }}
    </div>
@endforelse
@if($customerNotifications->isNotEmpty())
    <form method="POST"
        action="{{ route('customer-notifications.mark-all-read') }}"
        class="customer-notification-mark-all-form text-center p-2 border-top" novalidate>
        @csrf
        <button type="submit" class="btn btn-sm btn-link">
            {{ translate('mark_all_as_read') }}
        </button>
    </form>
@endif
