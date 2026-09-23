@extends('layouts.admin.app')

@section('title', translate('Chat with') . ' ' . ($thread->user ? ($thread->user->f_name ?? $thread->user->name) : 'Guest'))

@push('css_or_js')
    {{-- Make sure to load the emoji picker script --}}
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    <style>
        /* NEW: Customer Info Box */
        .customer-info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .customer-info-box h5 {
            margin-bottom: 0.75rem;
        }
        .customer-info-box p {
            margin-bottom: 0.25rem;
        }
        
        #chat-container {
            display: flex;
            flex-direction: column;
            height: 70vh; /* Set height for container */
        }
        #chat-messages {
            flex: 1; /* Make messages area grow */
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .chat-message {
            padding: 0.65rem 1rem;
            border-radius: 18px;
            max-width: 85%;
            line-height: 1.5;
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .chat-message.customer {
            background: #e9ecef;
            color: #333;
            align-self: flex-start;
            border-bottom-left-radius: 6px;
        }
        .chat-message.admin {
            background: {{ $web_config['primary_color'] ?? '#007bff' }};
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 6px;
        }
        #chat-form-container {
            position: relative; /* For emoji picker */
            border-top: 1px solid #ddd;
            padding-top: 1rem;
        }
        #chat-form {
            display: flex;
            align-items: center;
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 0.5rem;
            background: #fff;
        }
        #chat-form input {
            flex: 1;
            border: none;
            outline: none;
            padding: 0.5rem;
            background: transparent;
        }
        #chat-form button {
            border: none;
            background: transparent;
            padding: 0.5rem;
            margin: 0 0.25rem;
            cursor: pointer;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }
        #chat-form button:hover {
            background-color: #f0f0f0;
        }
        #chat-form button#send-btn {
            background: {{ $web_config['primary_color'] ?? '#007bff' }};
            color: white;
        }
        #chat-form button#send-btn:hover {
            background: {{ $web_config['primary_color_dark'] ?? '#0056b3' }};
        }
        #emoji-picker-container {
            position: absolute;
            bottom: 100%; /* Position above the input form */
            right: 10px;
            display: none; /* Hidden by default */
            z-index: 100;
        }
    </style>
@endpush

@section('content')

{{-- NEW: Added notification sound --}}
<audio id="chat-notification-sound" preload="auto" style="display:none;">
    <source src="{{ dynamicAsset(path: 'public/sounds/new-message-sound.mp3') }}" type="audio/mpeg">
</audio>

<div class="content container-fluid">
    <div class="page-header d-flex justify-content-between flex-wrap">
        <h1 class="page-header-title mb-2 mb-sm-0">
            {{ translate('Chat with') }}
            @if($thread->user)
                {{ $thread->user->f_name ?? $thread->user->name }}
            @else
                {{ translate('Guest') }} ({{ Str::limit($thread->session_id, 10, '...') }})
            @endif
        </h1>
        <a href="{{ route('admin.chat.index') }}" class="btn btn-secondary">
            <i class="fi fi-rr-angle-small-left"></i> {{ translate('Back to List') }}
        </a>
    </div>
    <div class="card">
        <div class="card-body">
            
            {{-- NEW: Customer Info Box --}}
            @if($user)
                <div class="customer-info-box">
                    <h5>{{ $user->f_name ?? $user->name }}</h5>
                    <p><strong>{{ translate('Email') }}:</strong> {{ $user->email ?? 'N/A' }}</p>
                    <p><strong>{{ translate('Phone') }}:</strong> {{ $user->phone ?? 'N/A' }}</p>
                    {{-- Add any other fields you have, like 'created_at' --}}
                    <p><small>{{ translate('Customer since') }}: {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</small></p>
                </div>
            @else
                <div class="customer-info-box">
                    <h5>{{ translate('Guest User') }}</h5>
                    <p><strong>{{ translate('Session ID') }}:</strong> {{ $thread->session_id }}</p>
                </div>
            @endif
            {{-- End New Customer Info Box --}}

            <div id="chat-container">
                <div id="chat-messages">
                    @foreach($messages as $message)
                        <div class="chat-message {{ $message->sender_type }}">
                            {{ $message->body }}
                        </div>
                    @endforeach
                </div>

                <div id="chat-form-container">
                    <div id="emoji-picker-container">
                        <emoji-picker></emoji-picker>
                    </div>

                    <form id="chat-form" novalidate>
                        <button type="button" id="emoji-btn">
                            <i class="fi fi-rr-smile"></i>
                        </button>
                        <input type="text" id="chat-input" placeholder="{{ translate('Type your message...') }}" autocomplete="off" required>
                        <button type="submit" id="send-btn">
                           <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
{{-- This assumes 'app.js' which loads Echo is already included in your admin layout --}}
<script src="{{ asset('public/js/app.js') }}"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        if (!window.Echo) {
            console.error("Laravel Echo not found. Please ensure app.js is loaded in your admin layout.");
            return;
        }
        
        try {
            const csrfToken = '{{ csrf_token() }}';
            const threadId = {{ $thread->id }};

            const messagesEl = document.getElementById('chat-messages');
            const formEl = document.getElementById('chat-form');
            const inputEl = document.getElementById('chat-input');
            
            // Emoji UI
            const emojiBtn = document.getElementById('emoji-btn');
            const emojiPickerContainer = document.getElementById('emoji-picker-container');
            const emojiPicker = document.querySelector('emoji-picker');

            // NEW: Get the sound element
            const notificationSound = document.getElementById('chat-notification-sound');

            // Scroll to bottom on load
            messagesEl.scrollTop = messagesEl.scrollHeight;

            function appendMessage(message) {
                const el = document.createElement('div');
                el.className = `chat-message ${message.sender_type}`;
                // Simple text escaping
                el.innerText = message.body; 
                messagesEl.appendChild(el);
                messagesEl.scrollTop = messagesEl.scrollHeight;
            }
            
            // --- Emoji: Toggle Picker ---
            emojiBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isVisible = emojiPickerContainer.style.display === 'block';
                emojiPickerContainer.style.display = isVisible ? 'none' : 'block';
            });
            
            // --- Emoji: Insert Emoji ---
            emojiPicker.addEventListener('emoji-click', event => {
                inputEl.value += event.detail.unicode;
                inputEl.focus();
            });
            
            // --- Emoji: Hide picker if clicking outside ---
            document.addEventListener('click', (e) => {
                // Hide if click is outside the picker AND not on the toggle button
                if (!emojiPickerContainer.contains(e.target) && e.target !== emojiBtn && !emojiBtn.contains(e.target)) {
                    emojiPickerContainer.style.display = 'none';
                }
            });

            // --- Listen for new messages from Customer ---
            window.Echo.private(`chat-thread.${threadId}`)
                .listen('NewChatMessage', (e) => {
                    console.log('Admin received message:', e);
                    if (e.message.sender_type === 'customer') {
                        appendMessage(e.message);
                        
                        // --- NEW: Play sound ---
                        if(notificationSound) {
                            notificationSound.play().catch(error => console.warn("Could not play sound:", error));
                        }
                        // --- End new ---
                    }
                });

            // --- Send Message (as Admin) ---
            formEl.addEventListener('submit', async (e) => {
                e.preventDefault();
                const messageBody = inputEl.value.trim();
                if (!messageBody) return;

                // Hide emoji picker on send
                emojiPickerContainer.style.display = 'none';

                try {
                    const response = await fetch('/chat/send', { 
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            thread_id: threadId,
                            message: messageBody
                        })
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        console.error('Failed to send message:', errorData);
                        throw new Error(errorData.error || 'Failed to send message');
                    }

                    const message = await response.json();
                    appendMessage(message); // Optimistically append our own message
                    inputEl.value = ''; // Clear input

                } catch (error) {
                    console.error('Error sending message:', error);
                    alert('Error sending message. Please try again.\n' + error.message);
                }
            });

        } catch(e) {
            console.error("Error initializing admin chat:", e);
            alert("Could not initialize chat. See console for details.");
        }
    });
</script>
@endpush