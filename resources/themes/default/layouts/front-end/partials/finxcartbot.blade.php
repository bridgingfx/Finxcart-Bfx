<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ translate('finxcart_assistant') }}</title>
    <link rel="icon" href="https://finxcart.com/storage/app/public/company/2025-05-21-682ddd7d10827.webp" type="image/webp">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --orange: #F47B20;
            --orange-dark: #D96A10;
            --orange-light: #FFF3E8;
            --white: #FFFFFF;
            --gray-50: #F9FAFB;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #4B5563;
            --gray-700: #374151;
            --gray-800: #1F2937;
            --gray-900: #111827;
            --shadow: 0 10px 40px rgba(0,0,0,0.12);
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #FFF7ED 0%, #FFFFFF 50%, #FFF1E0 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* API Key Setup Screen */
        .setup-screen {
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 48px;
            max-width: 520px;
            width: 100%;
            text-align: center;
        }

        .setup-screen.hidden { display: none; }

        .setup-logo {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            object-fit: contain;
            margin-bottom: 20px;
        }

        .setup-screen h1 {
            font-size: 24px;
            color: var(--gray-800);
            margin-bottom: 8px;
        }

        .setup-screen p {
            color: var(--gray-500);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .setup-screen .steps {
            text-align: left;
            background: var(--orange-light);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .setup-screen .steps h3 {
            font-size: 13px;
            color: var(--orange-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .setup-screen .steps ol {
            padding-left: 20px;
            color: var(--gray-600);
            font-size: 13px;
            line-height: 1.8;
        }

        .setup-screen .steps a {
            color: var(--orange);
            text-decoration: none;
            font-weight: 600;
        }

        .setup-screen .steps a:hover { text-decoration: underline; }

        .api-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 12px;
        }

        .api-input-group input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .api-input-group input:focus {
            border-color: var(--orange);
        }

        .btn-primary {
            background: var(--orange);
            color: var(--white);
            border: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: var(--orange-dark);
            transform: translateY(-1px);
        }

        .setup-error {
            color: #DC2626;
            font-size: 13px;
            margin-top: 8px;
            display: none;
        }

        /* Chat Container */
        .chat-container {
            width: 100%;
            max-width: 480px;
            height: 92vh;
            max-height: 740px;
            background: var(--white);
            border-radius: 24px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-container.hidden { display: none; }

        /* Header */
        .chat-header {
            background: var(--orange);
            color: var(--white);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            flex-shrink: 0;
        }

        .chat-header-avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--white);
            padding: 4px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .chat-header-info { flex: 1; }

        .chat-header-info h2 {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.2px;
        }

        .chat-header-info span {
            font-size: 12px;
            opacity: 0.85;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .chat-header-info .status-dot {
            width: 7px;
            height: 7px;
            background: #4ADE80;
            border-radius: 50%;
            display: inline-block;
        }

        .header-actions {
            display: flex;
            gap: 8px;
        }

        .header-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
            color: var(--white);
        }

        .header-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .header-btn svg {
            width: 18px;
            height: 18px;
        }

        /* Chat Messages Area */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            background: var(--gray-50);
        }

        .chat-messages::-webkit-scrollbar { width: 4px; }
        .chat-messages::-webkit-scrollbar-track { background: transparent; }
        .chat-messages::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 4px; }

        .message {
            display: flex;
            gap: 10px;
            max-width: 88%;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.bot { align-self: flex-start; }
        .message.user { align-self: flex-end; flex-direction: row-reverse; }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            flex-shrink: 0;
            object-fit: contain;
        }

        .message.bot .message-avatar {
            background: var(--orange-light);
            padding: 3px;
        }

        .message.user .message-avatar {
            background: var(--orange);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .message-bubble {
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.6;
            word-wrap: break-word;
        }

        .message.bot .message-bubble {
            background: var(--white);
            color: var(--gray-700);
            border: 1px solid var(--gray-200);
            border-top-left-radius: 4px;
        }

        .message.user .message-bubble {
            background: var(--orange);
            color: var(--white);
            border-top-right-radius: 4px;
        }

        .message-bubble p { margin-bottom: 8px; }
        .message-bubble p:last-child { margin-bottom: 0; }
        .message-bubble strong { font-weight: 600; }
        .message-bubble ul, .message-bubble ol {
            padding-left: 18px;
            margin: 6px 0;
        }
        .message-bubble li { margin-bottom: 4px; }

        .message-time {
            font-size: 11px;
            color: var(--gray-400);
            margin-top: 4px;
            padding: 0 4px;
        }

        .message.user .message-time { text-align: right; }

        /* Typing indicator */
        .typing-indicator {
            display: flex;
            gap: 10px;
            align-self: flex-start;
            max-width: 88%;
        }

        .typing-indicator.hidden { display: none; }

        .typing-dots {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 16px;
            border-top-left-radius: 4px;
            padding: 14px 20px;
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .typing-dots span {
            width: 7px;
            height: 7px;
            background: var(--gray-400);
            border-radius: 50%;
            animation: typing 1.4s ease-in-out infinite;
        }

        .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 100% { transform: translateY(0); opacity: 0.4; }
            50% { transform: translateY(-4px); opacity: 1; }
        }

        /* Quick Actions */
        .quick-actions {
            padding: 12px 20px 4px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            background: var(--gray-50);
        }

        .quick-btn {
            background: var(--white);
            border: 1px solid var(--gray-200);
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 12px;
            color: var(--gray-600);
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .quick-btn:hover {
            border-color: var(--orange);
            color: var(--orange);
            background: var(--orange-light);
        }

        /* WhatsApp Banner */
        .whatsapp-banner {
            background: #E8F5E9;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: background 0.2s;
            flex-shrink: 0;
        }

        .whatsapp-banner:hover { background: #C8E6C9; }

        .whatsapp-banner svg {
            width: 22px;
            height: 22px;
            fill: #25D366;
            flex-shrink: 0;
        }

        .whatsapp-banner-text {
            flex: 1;
            font-size: 13px;
            color: #1B5E20;
        }

        .whatsapp-banner-text strong {
            display: block;
            font-size: 12px;
            font-weight: 600;
        }

        .whatsapp-banner-text span {
            font-size: 11px;
            opacity: 0.8;
        }

        .whatsapp-banner-arrow {
            color: #25D366;
            font-size: 18px;
        }

        /* Input Area */
        .chat-input-area {
            padding: 14px 16px;
            background: var(--white);
            border-top: 1px solid var(--gray-100);
            display: flex;
            align-items: flex-end;
            gap: 10px;
            flex-shrink: 0;
        }

        .input-wrapper {
            flex: 1;
            display: flex;
            align-items: flex-end;
            background: var(--gray-50);
            border: 2px solid var(--gray-200);
            border-radius: 14px;
            padding: 4px;
            transition: border-color 0.2s;
        }

        .input-wrapper:focus-within {
            border-color: var(--orange);
        }

        .chat-input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 10px 12px;
            font-size: 14px;
            font-family: inherit;
            resize: none;
            outline: none;
            max-height: 120px;
            line-height: 1.4;
            color: var(--gray-800);
        }

        .chat-input::placeholder { color: var(--gray-400); }

        .voice-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: none;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            color: var(--gray-400);
            flex-shrink: 0;
        }

        .voice-btn:hover { color: var(--orange); background: var(--orange-light); }

        .voice-btn.recording {
            color: #DC2626;
            background: #FEE2E2;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.3); }
            50% { box-shadow: 0 0 0 8px rgba(220, 38, 38, 0); }
        }

        .voice-btn svg { width: 20px; height: 20px; }

        .send-btn {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            border: none;
            background: var(--orange);
            color: var(--white);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .send-btn:hover {
            background: var(--orange-dark);
            transform: translateY(-1px);
        }

        .send-btn:disabled {
            background: var(--gray-300);
            cursor: not-allowed;
            transform: none;
        }

        .send-btn svg { width: 20px; height: 20px; }

        /* Voice output toggle */
        .voice-output-toggle {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            font-size: 11px;
            color: var(--gray-400);
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
            user-select: none;
        }

        .voice-output-toggle:hover { color: var(--orange); }
        .voice-output-toggle.active { color: var(--orange); }

        .voice-output-toggle svg { width: 14px; height: 14px; }

        /* Powered by */
        .powered-by {
            text-align: center;
            padding: 8px;
            font-size: 11px;
            color: var(--gray-400);
            background: var(--white);
            flex-shrink: 0;
        }

        .powered-by a {
            color: var(--orange);
            text-decoration: none;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 520px) {
            body { padding: 0; }
            .chat-container {
                max-width: 100%;
                height: 100vh;
                max-height: 100vh;
                border-radius: 0;
            }
            .setup-screen {
                border-radius: 0;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<!-- API Key Setup -->
<div class="setup-screen" id="setupScreen">
    <img src="https://finxcart.com/storage/app/public/company/2025-05-21-682ddd7d10827.webp" alt="FinXCart" class="setup-logo">
    <h1>{{ translate('finxcart_assistant') }}</h1>
    <p>{{ translate('your_ai_powered_forex_trading_companion_to_get_started_you_n') }}</p>
    <div class="steps">
        <h3>{{ translate('quick_setup_free') }}</h3>
        <ol>
            <li>{{ translate('visit') }}<a href="https://aistudio.google.com/app/apikey" target="_blank">{{ translate('google_ai_studio') }}</a></li>
            <li>{{ translate('sign_in_with_your_google_account') }}</li>
            <li>{{ translate('click_create_api_key_and_copy_it') }}</li>
            <li>{{ translate('paste_it_below_and_start_chatting') }}</li>
        </ol>
    </div>
    <div class="api-input-group">
        <input type="password" id="apiKeyInput" placeholder="{{ translate('paste_your_gemini_api_key_here') }}">
        <button class="btn-primary" onclick="initializeChat()">{{ translate('start') }}</button>
    </div>
    <p class="setup-error" id="setupError"></p>
</div>

<!-- Chat Interface -->
<div class="chat-container hidden" id="chatContainer">
    <!-- Header -->
    <div class="chat-header">
        <img src="https://finxcart.com/storage/app/public/company/2025-05-21-682ddd7d10827.webp" alt="FinXCart" class="chat-header-avatar">
        <div class="chat-header-info">
            <h2>{{ translate('finxcart_assistant') }}</h2>
            <span><span class="status-dot"></span> {{ translate('online_forex_trading_expert') }}</span>
        </div>
        <div class="header-actions">
            <button class="header-btn voice-output-toggle" id="voiceToggle" onclick="toggleVoiceOutput()" title="Toggle voice responses">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                    <path d="M15.54 8.46a5 5 0 0 1 0 7.07" id="voiceWave1"></path>
                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14" id="voiceWave2"></path>
                </svg>
            </button>
            <button class="header-btn" onclick="clearChat()" title="Clear chat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Messages -->
    <div class="chat-messages" id="chatMessages">
        <!-- Welcome message injected by JS -->
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions" id="quickActions">
        <button class="quick-btn" onclick="sendQuickMessage('What is Forex trading?')">{{ translate('what_is_forex') }}</button>
        <button class="quick-btn" onclick="sendQuickMessage('Tell me about FinXCart CRM solutions')">{{ translate('crm_solutions') }}</button>
        <button class="quick-btn" onclick="sendQuickMessage('What trading platforms do you support?')">{{ translate('trading_platforms') }}</button>
        <button class="quick-btn" onclick="sendQuickMessage('How can FinXCart help my brokerage?')">{{ translate('for_brokers') }}</button>
    </div>

    <!-- WhatsApp Banner -->
    <div class="whatsapp-banner" onclick="openWhatsApp()">
        <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        <div class="whatsapp-banner-text">
            <strong>{{ translate('chat_with_us_on_whatsapp') }}</strong>
            <span>+971 58 884 5033 &mdash; Get instant support</span>
        </div>
        <span class="whatsapp-banner-arrow">&rsaquo;</span>
    </div>

    <!-- Input Area -->
    <div class="chat-input-area">
        <div class="input-wrapper">
            <textarea class="chat-input" id="chatInput" placeholder="{{ translate('ask_about_forex_trading_tools') }}" rows="1" onkeydown="handleKeyDown(event)" oninput="autoResize(this)"></textarea>
            <button class="voice-btn" id="voiceBtn" onclick="toggleVoiceInput()" title="Voice input">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                    <line x1="12" y1="19" x2="12" y2="23"></line>
                    <line x1="8" y1="23" x2="16" y2="23"></line>
                </svg>
            </button>
        </div>
        <button class="send-btn" id="sendBtn" onclick="sendMessage()" title="Send message">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
        </button>
    </div>

    <div class="powered-by">
        Powered by <a href="https://finxcart.com" target="_blank">{{ translate('finxcart_com') }}</a>
    </div>
</div>

<script>
// ========== Configuration ==========
const FINXCART_LOGO = 'https://finxcart.com/storage/app/public/company/2025-05-21-682ddd7d10827.webp';
const WHATSAPP_NUMBER = '971588845033';
const WHATSAPP_MESSAGE = 'Hi, I would like to know more about FinXCart services.';

const SYSTEM_PROMPT = `You are FinXCart Assistant, the official AI assistant for FinXCart.com — a leading FinTech company based in Dubai, UAE specializing in Forex trading solutions.

ABOUT FINXCART:
- Full Name: FinXCart
- Website: https://finxcart.com
- Location: Office 111, Al Moosa Business Center, Oud Metha, Dubai, UAE
- Phone: +971 58 594 5733 (24/7)
- WhatsApp: +971 58 884 5033
- Tagline: "Your One-Stop FinTech Destination" / "Everything You Need, All in One Place"

CORE SERVICES:
1. CRM Solutions: Advanced Forex CRM software with client management, dashboards, analytics, lead tracking, and automated reporting.
2. Trading Platform Integration: MT4, MT5, cTrader, X Trader, XOH, Verted, WinTrado.
3. Payment Gateway Integration: PayPal, Stripe, Binance, Skrill, WebMoney, and more.
4. Mobile Trading SDKs & Apps: Custom mobile trading solutions and notification services.
5. Marketing Automation: Email campaigns via Mailchimp, SendGrid, Constant Contact.
6. Compliance Services: KYC, AML, regulatory reporting, automated tax calculators.
7. Data & Cloud: AWS S3, Google Cloud, Dropbox, Box, OneDrive integrations.
8. Educational Resources: Trading courses, webinars, technical analysis tools, market sentiment analysis.
9. White Label Solutions: Custom themes, templates, and branding for forex brokerages.
10. API Integrations: Forex data feeds, economic calendar APIs, financial news feeds.

TARGET CLIENTS: Forex brokers, retail traders, money managers, financial advisors, wealth management firms, educational forex platforms, fintech startups, and affiliate networks.

YOUR PERSONALITY:
- Professional yet friendly and approachable
- Expert in Forex trading, financial markets, trading tools, and FinXCart products
- You explain complex trading concepts clearly for both beginners and experts
- You always represent the FinXCart brand positively
- When appropriate, suggest relevant FinXCart services that could help the user
- For specific account issues or purchases, direct users to contact via WhatsApp (+971 58 884 5033) or visit finxcart.com

FOREX KNOWLEDGE:
You are deeply knowledgeable about:
- Currency pairs (major, minor, exotic), pip values, lot sizes, leverage, margin
- Technical analysis: candlestick patterns, indicators (RSI, MACD, Bollinger Bands, Moving Averages, Fibonacci retracements)
- Fundamental analysis: economic indicators, central bank policies, NFP, CPI, GDP, interest rate decisions
- Trading strategies: scalping, day trading, swing trading, position trading, carry trade
- Risk management: stop losses, take profits, position sizing, risk-reward ratios
- Trading psychology: discipline, emotional management, trading plans
- MetaTrader 4/5 features and usage
- Market sessions: London, New York, Tokyo, Sydney
- Cryptocurrency and CFD trading basics

RESPONSE GUIDELINES:
- Keep responses concise but informative (2-4 paragraphs max unless detailed explanation requested)
- Use bullet points for lists
- When discussing risk, always include a risk disclaimer
- For technical questions, provide clear step-by-step guidance
- If asked about something outside your expertise, politely acknowledge and redirect to relevant FinXCart services or WhatsApp support
- Never provide specific financial advice or guarantee profits
- Always add appropriate risk warnings when discussing trading strategies`;

// ========== State ==========
let API_KEY = '';
let ACTIVE_MODEL = '';
const MODELS = ['gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-1.5-flash-8b'];
let conversationHistory = [];
let isVoiceOutputEnabled = false;
let isRecording = false;
let recognition = null;
let speechSynth = window.speechSynthesis;

// ========== Initialization ==========
async function initializeChat() {
    const key = document.getElementById('apiKeyInput').value.trim();
    const btn = document.querySelector('.btn-primary');
    if (!key) {
        showSetupError('Please enter your Gemini API key.');
        return;
    }
    if (key.length < 20) {
        showSetupError('That doesn\'t look like a valid API key. Please check and try again.');
        return;
    }

    // Accept the key (format validated above) - don't waste quota on validation calls
    btn.textContent = 'Starting...';
    btn.disabled = true;

    API_KEY = key;
    ACTIVE_MODEL = MODELS[0];
    localStorage.setItem('finxcart_api_key', key);
    localStorage.setItem('finxcart_model', ACTIVE_MODEL);

    document.getElementById('setupScreen').classList.add('hidden');
    document.getElementById('chatContainer').classList.remove('hidden');

    addBotMessage(getWelcomeMessage());
    document.getElementById('chatInput').focus();
}

function showSetupError(msg) {
    const el = document.getElementById('setupError');
    el.textContent = msg;
    el.style.display = 'block';
}

// Check for saved key on load
window.addEventListener('DOMContentLoaded', () => {
    const savedKey = localStorage.getItem('finxcart_api_key');
    const savedModel = localStorage.getItem('finxcart_model');
    if (savedKey) {
        API_KEY = savedKey;
        ACTIVE_MODEL = savedModel || MODELS[0];
        document.getElementById('setupScreen').classList.add('hidden');
        document.getElementById('chatContainer').classList.remove('hidden');
        addBotMessage(getWelcomeMessage());
    }
    initVoiceRecognition();
});

function getWelcomeMessage() {
    return `Welcome to **FinXCart Assistant**! I'm your AI-powered Forex & Trading expert.

I can help you with:
- **Forex Trading** concepts, strategies & analysis
- **FinXCart CRM** solutions & platform features
- **Trading Platforms** (MT4, MT5, cTrader & more)
- **Market Insights** & technical analysis
- **Risk Management** & trading psychology

How can I assist you today?`;
}

// ========== Messaging ==========
async function sendMessage() {
    const input = document.getElementById('chatInput');
    const text = input.value.trim();
    if (!text) return;

    input.value = '';
    autoResize(input);
    hideQuickActions();
    addUserMessage(text);
    showTyping();

    try {
        const reply = await callGeminiAPI(text);
        hideTyping();
        addBotMessage(reply);
        if (isVoiceOutputEnabled) speakText(reply);
    } catch (err) {
        hideTyping();
        console.error('API Error:', err);
        if (err.message && (err.message.includes('API_KEY') || err.message.includes('API key') || err.message.includes('UNAUTHENTICATED') || err.message.includes('PERMISSION_DENIED'))) {
            addBotMessage('Your API key appears to be invalid or expired. Please refresh the page and enter a new Gemini API key from [Google AI Studio](https://aistudio.google.com/app/apikey).');
            localStorage.removeItem('finxcart_api_key');
        } else if (err.message && err.message.includes('safety')) {
            addBotMessage(err.message);
        } else if (err.message && (err.message.includes('quota') || err.message.includes('429') || err.message.includes('RESOURCE_EXHAUSTED'))) {
            addBotMessage('The free API quota has been temporarily exceeded. Please wait about **1 minute** and try again. This is a rate limit on the free Gemini tier and resets quickly.');
        } else {
            addBotMessage('Sorry, something went wrong: **' + escapeHtml(err.message) + '**\n\nPlease try again or reach out to us on **WhatsApp** at +971 58 884 5033 for immediate assistance.');
        }
    }
}

function sendQuickMessage(text) {
    document.getElementById('chatInput').value = text;
    sendMessage();
}

function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

// ========== API Call ==========
async function callGeminiAPI(userMessage) {
    conversationHistory.push({ role: 'user', parts: [{ text: userMessage }] });

    const requestBody = {
        system_instruction: {
            parts: [{ text: SYSTEM_PROMPT }]
        },
        contents: [...conversationHistory],
        generationConfig: {
            temperature: 0.7,
            topP: 0.9,
            topK: 40,
            maxOutputTokens: 1024,
        },
        safetySettings: [
            { category: 'HARM_CATEGORY_HARASSMENT', threshold: 'BLOCK_MEDIUM_AND_ABOVE' },
            { category: 'HARM_CATEGORY_HATE_SPEECH', threshold: 'BLOCK_MEDIUM_AND_ABOVE' },
            { category: 'HARM_CATEGORY_SEXUALLY_EXPLICIT', threshold: 'BLOCK_MEDIUM_AND_ABOVE' },
            { category: 'HARM_CATEGORY_DANGEROUS_CONTENT', threshold: 'BLOCK_MEDIUM_AND_ABOVE' },
        ]
    };

    // Try active model first, then fall back to others
    const modelsToTry = [ACTIVE_MODEL, ...MODELS.filter(m => m !== ACTIVE_MODEL)];
    let lastError = '';

    for (const model of modelsToTry) {
        try {
            const response = await fetch(
                `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${API_KEY}`,
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(requestBody)
                }
            );

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                lastError = errorData?.error?.message || `HTTP ${response.status}`;
                const errStatus = errorData?.error?.status;
                if (errStatus === 'PERMISSION_DENIED' || errStatus === 'UNAUTHENTICATED') {
                    break; // No point trying other models with bad key
                }
                continue; // Try next model
            }

            const data = await response.json();
            const reply = data.candidates?.[0]?.content?.parts?.[0]?.text;

            if (!reply) {
                const blockReason = data.candidates?.[0]?.finishReason;
                if (blockReason === 'SAFETY') {
                    conversationHistory.pop();
                    throw new Error('Response blocked by safety filters. Please rephrase your question.');
                }
                continue; // Try next model
            }

            // Success - update active model if it changed
            if (model !== ACTIVE_MODEL) {
                ACTIVE_MODEL = model;
                localStorage.setItem('finxcart_model', model);
            }

            conversationHistory.push({ role: 'model', parts: [{ text: reply }] });

            if (conversationHistory.length > 20) {
                conversationHistory = conversationHistory.slice(-16);
            }

            return reply;
        } catch (fetchErr) {
            if (fetchErr.message.includes('safety')) throw fetchErr;
            lastError = fetchErr.message;
            continue;
        }
    }

    // All models failed
    conversationHistory.pop();
    throw new Error(lastError || 'All models failed. Please try again.');
}

// ========== UI Helpers ==========
function addBotMessage(text) {
    const container = document.getElementById('chatMessages');
    const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    const html = `
        <div class="message bot">
            <img src="${FINXCART_LOGO}" alt="FinXCart" class="message-avatar">
            <div>
                <div class="message-bubble">${formatMarkdown(text)}</div>
                <div class="message-time">${time}</div>
            </div>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
    scrollToBottom();
}

function addUserMessage(text) {
    const container = document.getElementById('chatMessages');
    const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    const html = `
        <div class="message user">
            <div class="message-avatar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="white" stroke="none">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div>
                <div class="message-bubble">${escapeHtml(text)}</div>
                <div class="message-time">${time}</div>
            </div>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
    scrollToBottom();
}

function showTyping() {
    const container = document.getElementById('chatMessages');
    const html = `
        <div class="typing-indicator" id="typingIndicator">
            <img src="${FINXCART_LOGO}" alt="FinXCart" class="message-avatar" style="background: var(--orange-light); padding: 3px;">
            <div class="typing-dots"><span></span><span></span><span></span></div>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
    scrollToBottom();
}

function hideTyping() {
    const el = document.getElementById('typingIndicator');
    if (el) el.remove();
}

function hideQuickActions() {
    const el = document.getElementById('quickActions');
    if (el) el.style.display = 'none';
}

function scrollToBottom() {
    const container = document.getElementById('chatMessages');
    container.scrollTop = container.scrollHeight;
}

function clearChat() {
    conversationHistory = [];
    document.getElementById('chatMessages').innerHTML = '';
    const qa = document.getElementById('quickActions');
    if (qa) qa.style.display = 'flex';
    addBotMessage(getWelcomeMessage());
}

function formatMarkdown(text) {
    // Basic markdown formatting
    let html = escapeHtml(text);

    // Bold: **text**
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

    // Italic: *text*
    html = html.replace(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');

    // Bullet lists
    html = html.replace(/^[-*]\s+(.+)$/gm, '<li>$1</li>');
    html = html.replace(/(<li>.*<\/li>\n?)+/g, '<ul>$&</ul>');

    // Numbered lists
    html = html.replace(/^\d+\.\s+(.+)$/gm, '<li>$1</li>');

    // Line breaks
    html = html.replace(/\n\n/g, '</p><p>');
    html = html.replace(/\n/g, '<br>');
    html = '<p>' + html + '</p>';

    // Clean up empty paragraphs
    html = html.replace(/<p>\s*<\/p>/g, '');

    return html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ========== Voice Input ==========
function initVoiceRecognition() {
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        const voiceBtn = document.getElementById('voiceBtn');
        if (voiceBtn) voiceBtn.style.display = 'none';
        return;
    }

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = true;
    recognition.lang = 'en-US';

    recognition.onresult = (event) => {
        let transcript = '';
        for (let i = event.resultIndex; i < event.results.length; i++) {
            transcript += event.results[i][0].transcript;
        }
        document.getElementById('chatInput').value = transcript;
        autoResize(document.getElementById('chatInput'));
    };

    recognition.onend = () => {
        isRecording = false;
        document.getElementById('voiceBtn').classList.remove('recording');
        // Auto-send if there's text
        const input = document.getElementById('chatInput');
        if (input.value.trim()) {
            sendMessage();
        }
    };

    recognition.onerror = (event) => {
        isRecording = false;
        document.getElementById('voiceBtn').classList.remove('recording');
        if (event.error !== 'no-speech') {
            console.error('Speech recognition error:', event.error);
        }
    };
}

function toggleVoiceInput() {
    if (!recognition) return;

    if (isRecording) {
        recognition.stop();
        isRecording = false;
        document.getElementById('voiceBtn').classList.remove('recording');
    } else {
        recognition.start();
        isRecording = true;
        document.getElementById('voiceBtn').classList.add('recording');
        document.getElementById('chatInput').value = '';
        document.getElementById('chatInput').placeholder = 'Listening...';
        setTimeout(() => {
            document.getElementById('chatInput').placeholder = 'Ask about Forex, trading tools...';
        }, 3000);
    }
}

// ========== Voice Output ==========
function toggleVoiceOutput() {
    isVoiceOutputEnabled = !isVoiceOutputEnabled;
    const toggle = document.getElementById('voiceToggle');
    const wave1 = document.getElementById('voiceWave1');
    const wave2 = document.getElementById('voiceWave2');

    if (isVoiceOutputEnabled) {
        toggle.classList.add('active');
        wave1.style.opacity = '1';
        wave2.style.opacity = '1';
    } else {
        toggle.classList.remove('active');
        wave1.style.opacity = '0.3';
        wave2.style.opacity = '0.3';
        speechSynth.cancel();
    }
}

function speakText(text) {
    if (!speechSynth) return;
    speechSynth.cancel();

    // Strip markdown for speech
    const cleanText = text
        .replace(/\*\*(.+?)\*\*/g, '$1')
        .replace(/\*(.+?)\*/g, '$1')
        .replace(/[-*]\s+/g, ', ')
        .replace(/\d+\.\s+/g, ', ')
        .replace(/\n+/g, '. ');

    const utterance = new SpeechSynthesisUtterance(cleanText);
    utterance.rate = 1;
    utterance.pitch = 1;
    utterance.volume = 1;

    // Try to use a natural-sounding voice
    const voices = speechSynth.getVoices();
    const preferred = voices.find(v => v.name.includes('Google') && v.lang.startsWith('en'))
        || voices.find(v => v.lang.startsWith('en-US'))
        || voices.find(v => v.lang.startsWith('en'));
    if (preferred) utterance.voice = preferred;

    speechSynth.speak(utterance);
}

// Load voices
if (speechSynth) {
    speechSynth.onvoiceschanged = () => speechSynth.getVoices();
}

// ========== WhatsApp ==========
function openWhatsApp() {
    const encoded = encodeURIComponent(WHATSAPP_MESSAGE);
    window.open(`https://wa.me/${WHATSAPP_NUMBER}?text=${encoded}`, '_blank');
}
</script>
</body>
</html>