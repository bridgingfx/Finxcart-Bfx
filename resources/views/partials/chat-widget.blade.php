{{-- resources/views/components/chat-widget.blade.php --}}
{{-- Include this in your customer-facing layout --}}

<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
{{-- <script src="https://js.pusher.com/8.2/pusher.min.js"></script> --}}

<style>
    /* --- Improved Base Styles --- */
    #chat-widget {
        position: fixed;
        bottom: 30px; /* Make space for the toggle button */
        right: 20px;
        width: 370px;
        max-width: calc(100% - 40px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        border-radius: 12px;
        overflow: hidden;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        z-index: 1000;

        /* --- Design/Speed: Animation for open/close --- */
        opacity: 0;
        transform: translateY(20px);
        visibility: hidden;
        transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s;
    }

    #chat-widget.chat-widget-open {
        opacity: 1;
        transform: translateY(0);
        visibility: visible;
    }

    #chat-header {
        background: #007bff;
        color: white;
        padding: 1rem 1.25rem;
        font-weight: 600;
        font-size: 1.1rem;
        position: relative;
        border-bottom: 1px solid #0069d9;
    }

    #chat-messages {
        height: 350px;
        overflow-y: auto;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        background: #f9f9f9;
        scroll-behavior: smooth; /* --- Design: Smooth scroll on new message --- */
    }

    .chat-message {
        padding: 0.65rem 1rem;
        border-radius: 18px;
        max-width: 85%;
        line-height: 1.5;
        word-wrap: break-word;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    /* --- Design: "iMessage" style bubbles --- */
    .chat-message.customer {
        background: #007bff;
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 6px;
    }

    .chat-message.admin {
        background: #e9ecef;
        color: #333;
        align-self: flex-start;
        border-bottom-left-radius: 6px;
    }
    
    .chat-message.admin a {
        color: #007bff;
        text-decoration: underline;
        font-weight: 600;
    }

    /* --- Design: Modern Input Area --- */
    #chat-form {
        display: flex;
        align-items: center;
        border-top: 1px solid #ddd;
        padding: 0.5rem 0.5rem 0.5rem 1rem;
        background: #fff;
        position: relative; /* For emoji picker */
    }

    #chat-form input {
        flex: 1;
        border: none;
        padding: 0.75rem 0;
        outline: none;
        font-size: 1rem;
        background: transparent;
    }
    
    #chat-form button {
        border: none;
        background: transparent;
        color: #007bff;
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
        background: #007bff;
        color: white;
    }
    #chat-form button#send-btn:hover {
        background: #0056b3;
    }

    /* --- Emoji: Picker Styles --- */
    #emoji-picker-container {
        position: absolute;
        bottom: 100%; /* Position above the input form */
        right: 10px;
        display: none; /* Hidden by default */
    }
    
    /* --- Toggle Button --- */
    #chat-toggle {
        position: fixed;
        bottom: 130px;
        right: 20px; /* --- Design: Aligned with widget --- */
        background: #007bff;
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 1001;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    #chat-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 16px rgba(0,0,0,0.25);
    }
    
    #chat-toggle.chat-toggle-hide {
        transform: scale(0.8);
        opacity: 0;
        visibility: hidden;
    }

    #chat-close {
        position: absolute;
        top: 50%;
        right: 0.75rem;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: white;
        font-size: 1.75rem;
        cursor: pointer;
        line-height: 1;
        padding: 0.25rem 0.5rem;
        opacity: 0.8;
        transition: opacity 0.2s;
    }
    #chat-close:hover {
        opacity: 1;
    }
</style>

<div id="chat-toggle">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
    </svg>
</div>

<div id="chat-widget">
    <div id="chat-header">
        Chat with Support
        <button id="chat-close">&times;</button>
    </div>
    <div id="chat-messages"></div>
    
    <div id="emoji-picker-container">
        <emoji-picker></emoji-picker>
    </div>
    
    <form id="chat-form">
        {{-- <button type="button" id="emoji-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line>
            </svg>
        </button> --}}
        <input type="text" id="chat-input" placeholder="Type your message..." autocomplete="off" required>
        <button type="submit" id="send-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
        </button>
    </form>
</div>

<audio id="chat-notification-sound" preload="none" style="display:none;">
    <source src="{{ dynamicAsset(path:'public/sounds/new-message-sound.mp3') }}" type="audio/mpeg">
</audio>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        if (window.Echo) {
            initializeCustomerChat();
        } else {
            console.error('❌ Laravel Echo not found. Chat widget will not initialize.');
        }

        function initializeCustomerChat() {
            const csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
            if (!csrfTokenEl) {
                console.error('❌ CSRF token not found. Chat will fail.');
                return;
            }
            const csrfToken = csrfTokenEl.getAttribute('content');
            
            // Widget UI
            const chatWidget = document.getElementById('chat-widget');
            const chatToggle = document.getElementById('chat-toggle');
            const chatClose = document.getElementById('chat-close');
            const messagesEl = document.getElementById('chat-messages');
            const formEl = document.getElementById('chat-form');
            const inputEl = document.getElementById('chat-input');
            
            // Emoji UI
            const emojiBtn = document.getElementById('emoji-btn');
            const emojiPickerContainer = document.getElementById('emoji-picker-container');
            
            // Sound
            const notificationSound = document.getElementById('chat-notification-sound');

            // State
            let threadId = null;
            let authToken = null;
            let echoChannel = null;
            let hasSessionStarted = false; 

            // Laravel Blade: check login
            const isLoggedIn = {{ (Auth::guard('customer')->check() || Auth::guard('web')->check()) ? 'true' : 'false' }};
            const loginRoute = '{{ route('customer.auth.login') }}';

            // --- Speed: Pre-fetch chat session on hover ---
            chatToggle.addEventListener('pointerenter', () => {
                if (isLoggedIn && !hasSessionStarted) {
                    startChatSession();
                }
            }, { once: true });

            // Toggle Chat Open
            chatToggle.addEventListener('click', () => {
                chatWidget.classList.add('chat-widget-open');
                chatToggle.classList.add('chat-toggle-hide');

                if (!isLoggedIn) {
                    messagesEl.innerHTML = `
                        <div class="chat-message admin">
                            Please <a href="${loginRoute}">log in</a> to ask for support.
                        </div>`;
                    formEl.style.display = 'none';
                    return;
                }

                formEl.style.display = 'flex';
                if (!hasSessionStarted) {
                    startChatSession();
                }
                
                inputEl.focus();
                messagesEl.scrollTop = messagesEl.scrollHeight;
            });

            // Close Chat
            chatClose.addEventListener('click', () => {
                chatWidget.classList.remove('chat-widget-open');
                chatToggle.classList.remove('chat-toggle-hide');
                emojiPickerContainer.style.display = 'none';
            });


            // ===============================================
            // ### EMOJI FIX (from last time) ###
            // We wait until the 'emoji-picker' custom element is defined
            // ===============================================
            if (emojiBtn && emojiPickerContainer) {
                customElements.whenDefined('emoji-picker').then(() => {
                    const emojiPicker = document.querySelector('emoji-picker');
                    if (!emojiPicker) return;

                    emojiBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const isVisible = emojiPickerContainer.style.display === 'block';
                        emojiPickerContainer.style.display = isVisible ? 'none' : 'block';
                    });

                    emojiPicker.addEventListener('emoji-click', event => {
                        inputEl.value += event.detail.unicode;
                        inputEl.focus();
                    });
                }).catch(error => {
                    console.error('Failed to load emoji picker:', error);
                });
            }
            
            // --- Emoji: Hide picker if clicking outside ---
            document.addEventListener('click', (e) => {
                if (!emojiPickerContainer.contains(e.target) && e.target !== emojiBtn && !emojiBtn.contains(e.target)) {
                    emojiPickerContainer.style.display = 'none';
                }
            });


            // Append Message to UI
            function appendMessage(message, senderType) {
                const el = document.createElement('div');
                const body = message.body || message.message;
                const type = message.sender_type || senderType;
                
                el.className = `chat-message ${type}`;
                el.innerText = body;
                messagesEl.appendChild(el);
                messagesEl.scrollTop = messagesEl.scrollHeight;
            }

            // Start Chat Session
            async function startChatSession() {
                if (hasSessionStarted) return;
                hasSessionStarted = true;

                messagesEl.innerHTML = `<div class="chat-message admin">Connecting...</div>`;

                try {
                    const response = await fetch('/chat/start', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (!response.ok) throw new Error('Failed to start chat session');
                    
                    const data = await response.json();
                    threadId = data.thread_id;
                    authToken = data.auth_token;
                    hasSessionStarted = true;

                    messagesEl.innerHTML = '';
                    if (data.messages.length === 0) {
                        appendMessage({ body: 'Hi! How can we help you today?', sender_type: 'admin' });
                    } else {
                        data.messages.forEach(msg => appendMessage(msg));
                    }

                    listenOnChannel();

                } catch (error) {
                    console.error('❌ Error starting chat:', error);
                    messagesEl.innerHTML = `<div class="chat-message admin">Sorry, could not connect to chat.</div>`;
                    hasSessionStarted = false;
                }
            }

            // Listen on Private Channel
            function listenOnChannel() {
                if (!threadId || !window.Echo || echoChannel) return;

                if (authToken && window.axios) {
                    window.axios.defaults.headers.common['X-Auth-Token'] = authToken;
                }

                try {
                    echoChannel = window.Echo.private(`chat-thread.${threadId}`)
                        .listen('NewChatMessage', (e) => {
                            if (e.message.sender_type === 'admin') {
                                appendMessage(e.message, 'admin');

                                if (notificationSound) {
                                    notificationSound.play().catch(error => {
                                        console.warn("Notification sound blocked by browser:", error.message);
                                    });
                                }
                            }
                        })
                        .error((error) => {
                            console.error('❌ Customer Echo error:', error);
                            if (error.status === 403) {
                                appendMessage({ body: 'Chat session expired. Please refresh.', sender_type: 'admin' });
                                formEl.style.display = 'none';
                            }
                        });

                } catch (error) {
                    console.error('❌ Failed to initialize Echo listener:', error);
                }
            }

            // Send Message
            formEl.addEventListener('submit', async (e) => {
                e.preventDefault();
                const messageBody = inputEl.value.trim();

                if (!messageBody || !threadId) return;

                const tempMessage = { body: messageBody, sender_type: 'customer' };
                appendMessage(tempMessage);
                
                // --- THIS IS THE CORRECTED LINE ---
                inputEl.value = ''; 
                
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
                        throw new Error('Failed to send message');
                    }

                } catch (error) {
                    console.error('❌ Error sending message:', error);
                    messagesEl.lastChild.innerText += ' (failed to send)';
                    messagesEl.lastChild.style.opacity = '0.7';
                }
            });
        }
    });
</script>