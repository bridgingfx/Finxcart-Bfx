{{-- This contains the HTML, Style, and JS for the customer chat widget --}}

<style>
    #chat-widget {
        position: fixed;
        bottom: 20px;
        right: 100px;
        width: 350px;
        max-width: 90%;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        border-radius: 8px;
        overflow: hidden;
        font-family: Arial, sans-serif;
        display: flex;
        flex-direction: column;
        background: #f9f9f9;
        display: none; /* Hidden by default */
        z-index: 1000;
    }
    #chat-header {
        background: #007bff;
        color: white;
        padding: 1rem;
        font-weight: bold;
        text-align: center;
    }
    #chat-messages {
        height: 300px;
        overflow-y: auto;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .chat-message {
        padding: 0.5rem 0.75rem;
        border-radius: 10px;
        max-width: 80%;
        line-height: 1.4;
    }
    .chat-message.customer {
        background: #007bff;
        color: white;
        align-self: flex-end;
    }
    .chat-message.admin {
        background: #e5e5e5;
        color: #333;
        align-self: flex-start;
    }
    #chat-form {
        display: flex;
        border-top: 1px solid #ddd;
    }
    #chat-form input {
        flex: 1;
        border: none;
        padding: 1rem;
        outline: none;
    }
    #chat-form button {
        border: none;
        background: #007bff;
        color: white;
        padding: 0 1.25rem;
        cursor: pointer;
    }
    #chat-toggle {
        position: fixed;
        bottom: 20px;
        right: 20px;
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
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        z-index: 1001;
    }
</style>

<div id="chat-toggle">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
</div>

<div id="chat-widget">
    <div id="chat-header">{{ translate('chat_with_support') }}</div>
    <div id="chat-messages">
        </div>
    <form id="chat-form" novalidate>
        <input type="text" id="chat-input" placeholder="{{ translate('Type your message...') }}" autocomplete="off" required>
        <button type="submit">{{ translate('Send') }}</button>
    </form>
</div>

<script type="module">
    // Get the CSRF token from the meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // --- DOM Elements ---
    const chatWidget = document.getElementById('chat-widget');
    const chatToggle = document.getElementById('chat-toggle');
    const messagesEl = document.getElementById('chat-messages');
    const formEl = document.getElementById('chat-form');
    const inputEl = document.getElementById('chat-input');

    // --- State ---
    let threadId = null;

    // --- Toggle Chat ---
    chatToggle.addEventListener('click', () => {
        chatWidget.style.display = 'flex';
        chatToggle.style.display = 'none';
        // Start the chat only when the user opens it
        if (!threadId) {
            startChatSession();
        }
    });

    // --- Append Message to UI ---
    function appendMessage(message) {
        const el = document.createElement('div');
        el.className = `chat-message ${message.sender_type}`;
        el.innerText = message.body;
        messagesEl.appendChild(el);
        // Scroll to bottom
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    // --- 1. Start Chat Session ---
    async function startChatSession() {
        try {
            const response = await fetch('/chat/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();

            threadId = data.thread_id;

            // Load old messages
            messagesEl.innerHTML = ''; // Clear old messages
            data.messages.forEach(msg => appendMessage(msg));

            // Now, listen for new messages
            listenOnChannel();

        } catch (error) {
            console.error('Error starting chat:', error);
        }
    }

    // --- 2. Listen on Private Channel ---
    function listenOnChannel() {
        if (!threadId) return;

        window.Echo.private(`chat-thread.${threadId}`)
            .listen('NewChatMessage', (e) => {
                // e.message contains the ChatMessage object
                // Only append if it's not our own message
                if (e.message.sender_type === 'admin') {
                    appendMessage(e.message);
                }
            });
    }

    // --- 3. Send Message ---
    formEl.addEventListener('submit', async (e) => {
        e.preventDefault();
        const messageBody = inputEl.value.trim();

        if (!messageBody || !threadId) return;

        try {
            // Send the message to the server
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

            const message = await response.json();

            // Optimistically append our own message
            appendMessage(message);

            inputEl.value = '';

        } catch (error) {
            console.error('Error sending message:', error);
        }
    });

</script>
