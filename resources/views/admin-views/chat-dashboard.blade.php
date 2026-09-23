@extends('layouts.admin.app')

@section('title', translate('Chat Dashboard'))

@push('css_or_js')
    <style>
        .chat-list .list-group-item {
            transition: background-color 0.2s ease;
        }
        .chat-list .list-group-item:hover {
            background-color: #f8f9fa;
        }
        .chat-list .list-group-item strong {
            color: {{ $web_config['primary_color'] ?? '#007bff' }};
        }
        .chat-list small {
            color: #6c757d;
        }
        /* Style for new message */
        .chat-list .list-group-item.new-message-highlight {
            background-color: #e6f7ff;
            animation: flash 1.5s 3;
        }
        @keyframes flash {
            0% { background-color: #e6f7ff; }
            50% { background-color: #f8f9fa; }
            100% { background-color: #e6f7ff; }
        }
    </style>
@endpush

@section('content')

{{-- NEW: Added notification sound --}}
<audio id="chat-notification-sound" preload="auto" style="display:none;">
    <source src="{{ dynamicAsset(path: 'public/sounds/new-message-sound.mp3') }}" type="audio/mpeg">
</audio>

<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">{{ translate('Customer Chat Dashboard') }}</h1>
            </div>
        </div>


{{-- @ads('[ad id="1"]') --}}
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link {{ $filter === 'today' ? 'active' : '' }}"
                               href="{{ route('admin.chat.index', ['filter' => 'today']) }}">
                                {{ translate('Today\'s Chats') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $filter === 'all' ? 'active' : '' }}"
                               href="{{ route('admin.chat.index', ['filter' => 'all']) }}">
                                {{ translate('All Chats') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body" style="padding: 0;">
                    <div class="list-group list-group-flush chat-list">
                        @forelse($threads as $thread)
                            <a href="{{ route('admin.chat.show', $thread) }}"
                               class="list-group-item list-group-item-action"
                               {{-- Added data-thread-id for our JavaScript --}}
                               data-thread-id="{{ $thread->id }}"
                               id="thread-link-{{ $thread->id }}">

                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>
                                        @if($thread->user)
                                            Chat with {{ $thread->user->f_name ?? $thread->user->name }}
                                            <span class="badge badge-soft-info ml-1">User #{{ $thread->user_id }}</span>
                                        @else
                                            Chat with Guest
                                            <span class="badge badge-soft-secondary ml-1">Session: {{ Str::limit($thread->session_id, 10, '...') }}</span>
                                        @endif
                                    </strong>
                                    <small id="thread-time-{{ $thread->id }}">{{ $thread->updated_at->diffForHumans() }}</small>
                                </div>
                                <small id="thread-count-{{ $thread->id }}">
                                    {{ $thread->messages_count }} {{ Str::plural('message', $thread->messages_count) }}.
                                </small>
                            </a>
                        @empty
                            <div class="list-group-item">
                                <div class="text-center p-4">
                                    <img src="{{ dynamicAsset(path: 'public/assets/back-end/svg/illustrations/communication.svg') }}" alt="No chats" style="width: 100px;">
                                    <p class="mb-0 mt-3">{{ translate('No active chats for this period.') }}</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ asset('public/js/app.js') }}"></script>
{{-- This assumes 'app.js' which loads Echo is already included in your admin layout --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {

        if (!window.Echo) {
            console.error("Laravel Echo not found. Real-time dashboard updates will not work.");
            return;
        }

        // NEW: Get the sound element
        const notificationSound = document.getElementById('chat-notification-sound');

        // Get all thread IDs from the dashboard
        const threadLinks = document.querySelectorAll('[data-thread-id]');

        threadLinks.forEach(link => {
            const threadId = link.getAttribute('data-thread-id');

            // Listen on the private channel for each thread
            window.Echo.private(`chat-thread.${threadId}`)
                .listen('NewChatMessage', (e) => {
                    console.log(`New message in thread ${threadId}`, e);

                    // Only react if the message is from the customer
                    if (e.message.sender_type === 'customer') {

                        // --- NEW: Play sound ---
                        if(notificationSound) {
                            notificationSound.play().catch(error => console.warn("Could not play sound:", error));
                        }
                        // --- End new ---

                        const threadLink = document.getElementById(`thread-link-${threadId}`);
                        const timeEl = document.getElementById(`thread-time-${threadId}`);
                        const countEl = document.getElementById(`thread-count-${threadId}`);

                        if (threadLink) {
                            // Highlight the chat
                            threadLink.classList.add('new-message-highlight');

                            // Optional: Alert the admin
                            // We check if the browser tab is in focus
                            if (!document.hasFocus()) {
                                // Simple toastr notification (if you use toastr)
                                if (window.toastr) {
                                    window.toastr.info(`New message from ${threadLink.querySelector('strong').innerText.trim()}`);
                                } else {
                                    console.log('New message received (tab not focused)');
                                }
                            }

                            // Update timestamp (simple text)
                            if (timeEl) timeEl.innerText = 'Just now';

                            // Update message count (this is tricky without the new count)
                            // A simple solution is to just add a badge
                            if (countEl) countEl.innerText = 'New message received!';

                            // Remove highlight after a few seconds
                            setTimeout(() => {
                                threadLink.classList.remove('new-message-highlight');
                            }, 4500);
                        }
                    }
                });
        });

        console.log(`Listening for new messages on ${threadLinks.length} threads.`);
    });
</script>
@endpush
