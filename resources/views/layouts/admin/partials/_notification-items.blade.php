@if($adminSystemNotifications->isNotEmpty())
    <div class="admin-message-item bg-section2 fs-11 fw-bold text-uppercase text-body-light">
        {{ translate('activity') }}
    </div>
    @foreach($adminSystemNotifications as $systemNotification)
        <a href="{{ $systemNotification->link ?: 'javascript:' }}"
           class="admin-message-item admin-system-notification-item"
           data-notification-id="{{ $systemNotification->id }}">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-1">
                <span class="fw-semibold text-dark text-truncate">{{ $systemNotification->title }}</span>
                <span class="fs-10 text-body-light flex-shrink-0">
                    {{ $systemNotification->created_at?->diffForHumans() }}
                </span>
            </div>
            <div class="admin-message-preview">
                {{ $systemNotification->message }}
            </div>
        </a>
    @endforeach
@endif

@if($adminChattingNotifications->isNotEmpty())
    <div class="admin-message-item bg-section2 fs-11 fw-bold text-uppercase text-body-light">
        {{ translate('messages') }}
    </div>
    @foreach($adminChattingNotifications as $chatNotification)
        <a href="{{ $chatNotification->notification_link }}" class="admin-message-item">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-1">
                <span class="fw-semibold text-dark text-truncate">
                    {{ $chatNotification->notification_name }}
                    <span class="fs-10 text-body-light">({{ $chatNotification->notification_label }})</span>
                </span>
                <span class="fs-10 text-body-light flex-shrink-0">
                    {{ $chatNotification->created_at?->diffForHumans() }}
                </span>
            </div>
            <div class="admin-message-preview">
                {{ $chatNotification->message ?: translate('shared_files') }}
            </div>
        </a>
    @endforeach
@endif

@if(\App\Utils\Helpers::module_permission_check('support_section'))
    <div class="admin-message-item bg-section2 fs-11 fw-bold text-uppercase text-body-light d-flex align-items-center justify-content-between">
        <span>{{ translate('contact_messages') }}</span>
        <a href="{{ route('admin.contact.list') }}" class="fs-12 text-primary fw-semibold text-uppercase">
            {{ translate('view_all') }}
        </a>
    </div>
    @forelse($unreadContactMessages as $contactMessage)
        <a href="{{ route('admin.contact.view', $contactMessage->id) }}" class="admin-message-item admin-contact-notification-item">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-1">
                <span class="fw-semibold text-dark text-truncate">{{ $contactMessage->name }}</span>
                <span class="fs-10 text-body-light flex-shrink-0">
                    {{ $contactMessage->created_at?->diffForHumans() }}
                </span>
            </div>
            <div class="admin-message-preview">
                {{ $contactMessage->subject ?: $contactMessage->message }}
            </div>
        </a>
    @empty
        <div class="admin-message-empty">
            {{ translate('No_new_messages_yet') }}.
        </div>
    @endforelse
@endif

@if($adminSystemNotifications->isEmpty() && $adminChattingNotifications->isEmpty() && !\App\Utils\Helpers::module_permission_check('support_section'))
    <div class="admin-message-empty">
        {{ translate('No_new_messages_yet') }}.
    </div>
@endif
