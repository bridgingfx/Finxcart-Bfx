@foreach($chattingMessages as $message)
    @php
        $senderClass = $message->sent_by_admin ? 'fl-chat-bubble-admin' : ($message->sent_by_seller ? 'fl-chat-bubble-seller' : 'fl-chat-bubble-customer');
        $senderLabel = $message->sent_by_admin ? translate('support_admin') : ($message->sent_by_seller ? translate('freelancer') : translate('customer'));
    @endphp
    <div class="fl-chat-bubble-row">
        <div class="fl-chat-bubble {{ $senderClass }}">
            <span class="fl-chat-sender">{{ $senderLabel }}</span>
            @if($message->message)
                <span>{{ $message->message }}</span>
            @endif
            @if(!empty($message->attachment_full_url))
                <div class="d-flex flex-column gap-1 mt-1">
                    @foreach($message->attachment_full_url as $attachment)
                        <a href="{{ $attachment['path'] ?? '#' }}" target="_blank" class="{{ $message->sent_by_admin ? 'text-white' : 'text--primary' }}" style="text-decoration: underline;">
                            {{ $attachment['key'] ?? translate('attachment') }}
                        </a>
                    @endforeach
                </div>
            @endif
            <small class="d-block mt-1 {{ $message->sent_by_admin ? 'text-white-50' : 'text-muted' }} fl-visible-message-time">
                {{ $message->created_at->format('M d, h:i A') }}
            </small>
        </div>
    </div>
@endforeach
