@extends('layouts.front-end.app')

@section('title', translate('Terms of Use'))

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<style>
.tu-wrap {
    font-family: 'DM Sans', sans-serif;
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem 1.5rem 4rem;
}

/* Hero */
.tu-hero {
    text-align: center;
    padding: 3.5rem 1rem 3rem;
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 2.5rem;
}
.tu-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #FFF7ED;
    color: #c2410c;
    font-size: 12px;
    font-weight: 500;
    padding: 5px 16px;
    border-radius: 999px;
    margin-bottom: 1.25rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}
.tu-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: 44px;
    font-weight: 600;
    color: #0f172a;
    margin: 0 0 1rem;
    line-height: 1.2;
}
.tu-hero p {
    font-size: 15px;
    color: #64748b;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.75;
}
.tu-updated {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #94a3b8;
    margin-top: 1rem;
}

/* TOC */
.tu-toc {
    background: #f8fafc;
    border: 1px solid #e9ecef;
    border-radius: 14px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}
.tu-toc-title {
    font-size: 13px;
    font-weight: 500;
    color: #0f172a;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}
.tu-toc-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 6px 2rem;
}
.tu-toc-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #4338ca;
    text-decoration: none;
    padding: 4px 0;
}
.tu-toc-item:hover { color: #1e1b4b; text-decoration: underline; }
.tu-toc-num {
    font-size: 11px;
    color: #94a3b8;
    min-width: 18px;
}

/* Section cards */
.tu-section {
    margin-bottom: 1.25rem;
    border: 1px solid #e9ecef;
    border-radius: 14px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
}
.tu-section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 1.25rem 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e9ecef;
}
.tu-section-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 20px;
}
.tu-section-icon.orange  { background: #FFF7ED; color: #c2410c; }
.tu-section-icon.indigo  { background: #EEF2FF; color: #4338ca; }
.tu-section-icon.blue    { background: #EFF6FF; color: #1d4ed8; }
.tu-section-icon.emerald { background: #ECFDF5; color: #059669; }
.tu-section-icon.amber   { background: #FFFBEB; color: #b45309; }
.tu-section-icon.rose    { background: #FFF1F2; color: #e11d48; }
.tu-section-icon.purple  { background: #FAF5FF; color: #7c3aed; }
.tu-section-icon.slate   { background: #F1F5F9; color: #475569; }

.tu-section-title {
    font-family: 'Playfair Display', serif;
    font-size: 17px;
    font-weight: 500;
    color: #0f172a;
    margin: 0;
}
.tu-section-sub {
    font-size: 12px;
    color: #94a3b8;
    margin: 3px 0 0;
}
.tu-section-body {
    padding: 1.5rem;
}

.tu-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 11px;
}
.tu-list li {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    font-size: 14px;
    color: #475569;
    line-height: 1.75;
}
.tu-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #c2410c;
    margin-top: 9px;
    flex-shrink: 0;
}
.tu-dot.indigo  { background: #4338ca; }
.tu-dot.blue    { background: #1d4ed8; }
.tu-dot.emerald { background: #059669; }
.tu-dot.amber   { background: #b45309; }
.tu-dot.rose    { background: #e11d48; }
.tu-dot.purple  { background: #7c3aed; }
.tu-dot.slate   { background: #475569; }

.tu-prose {
    font-size: 14px;
    color: #475569;
    line-height: 1.8;
    margin: 0;
}
.tu-prose + .tu-prose { margin-top: 0.75rem; }

/* Highlight box */
.tu-highlight {
    background: #FFF7ED;
    border: 1px solid #fed7aa;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    font-size: 14px;
    color: #7c2d12;
    line-height: 1.7;
    margin-top: 1rem;
}
.tu-highlight strong { font-weight: 600; color: #431407; }

/* Footer */
.tu-footer {
    margin-top: 2rem;
    background: #0f172a;
    border-radius: 14px;
    padding: 2rem;
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
    color: #94a3b8;
    font-size: 14px;
    line-height: 1.7;
}
.tu-footer i {
    font-size: 28px;
    color: #f97316;
    flex-shrink: 0;
    margin-top: 2px;
}
.tu-footer strong {
    display: block;
    color: #f1f5f9;
    font-size: 15px;
    font-weight: 500;
    margin-bottom: 6px;
}

@media (max-width: 640px) {
    .tu-hero h1    { font-size: 30px; }
    .tu-toc-grid   { grid-template-columns: 1fr; }
    .tu-footer     { flex-direction: column; gap: 1rem; }
}
</style>

<div class="tu-wrap">

    {{-- Hero --}}
    <div class="tu-hero">
        <div class="tu-badge">
            <i class="ti ti-file-description"></i>
            {{ translate('Legal Agreement') }}
        </div>
        <h1>{{ translate('Terms of Use') }}</h1>
        <p>{{ translate('Please read these terms carefully before using the FinxCart platform. By accessing or using our marketplace, you agree to be bound by the following terms and conditions.') }}</p>
        <div class="tu-updated">
            <i class="ti ti-calendar" style="font-size:14px;"></i>
            {{ translate('Last updated') }}: {{ date('F d, Y') }}
        </div>
    </div>

    {{-- Table of Contents --}}
    <div class="tu-toc">
        <p class="tu-toc-title"><i class="ti ti-list" style="font-size:16px;"></i> {{ translate('Table of Contents') }}</p>
        <div class="tu-toc-grid">
            <a href="#acceptance"      class="tu-toc-item"><span class="tu-toc-num">01</span> {{ translate('Acceptance of Terms') }}</a>
            <a href="#platform"        class="tu-toc-item"><span class="tu-toc-num">02</span> {{ translate('Platform Overview') }}</a>
            <a href="#accounts"        class="tu-toc-item"><span class="tu-toc-num">03</span> {{ translate('User Accounts') }}</a>
            <a href="#buyers"          class="tu-toc-item"><span class="tu-toc-num">04</span> {{ translate('Buyer Obligations') }}</a>
            <a href="#vendors"         class="tu-toc-item"><span class="tu-toc-num">05</span> {{ translate('Vendor Obligations') }}</a>
            <a href="#payments"        class="tu-toc-item"><span class="tu-toc-num">06</span> {{ translate('Payments and Fees') }}</a>
            <a href="#disclaimer"      class="tu-toc-item"><span class="tu-toc-num">07</span> {{ translate('Financial Disclaimer') }}</a>
            <a href="#ip"              class="tu-toc-item"><span class="tu-toc-num">08</span> {{ translate('Intellectual Property') }}</a>
            <a href="#termination"     class="tu-toc-item"><span class="tu-toc-num">09</span> {{ translate('Termination') }}</a>
            <a href="#governing"       class="tu-toc-item"><span class="tu-toc-num">10</span> {{ translate('Governing Law') }}</a>
        </div>
    </div>

    {{-- Section 1: Acceptance --}}
    <div class="tu-section" id="acceptance">
        <div class="tu-section-header">
            <div class="tu-section-icon orange"><i class="ti ti-writing"></i></div>
            <div>
                <p class="tu-section-title">01. {{ translate('Acceptance of Terms') }}</p>
                <p class="tu-section-sub">{{ translate('Your agreement to these terms') }}</p>
            </div>
        </div>
        <div class="tu-section-body">
            <ul class="tu-list">
                <li><span class="tu-dot"></span>{{ translate('By accessing, browsing, or using the FinxCart platform, you confirm that you have read, understood, and agree to be bound by these Terms of Use.') }}</li>
                <li><span class="tu-dot"></span>{{ translate('If you do not agree with any part of these terms, you must immediately discontinue your use of the FinxCart platform.') }}</li>
                <li><span class="tu-dot"></span>{{ translate('FinxCart reserves the right to modify these terms at any time. Continued use of the platform after any changes constitutes your acceptance of the revised terms.') }}</li>
                <li><span class="tu-dot"></span>{{ translate('You must be at least 18 years of age and legally capable of entering into binding agreements to use this platform.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 2: Platform Overview --}}
    <div class="tu-section" id="platform">
        <div class="tu-section-header">
            <div class="tu-section-icon indigo"><i class="ti ti-chart-candle"></i></div>
            <div>
                <p class="tu-section-title">02. {{ translate('Platform Overview') }}</p>
                <p class="tu-section-sub">{{ translate('What FinxCart is and what it offers') }}</p>
            </div>
        </div>
        <div class="tu-section-body">
            <ul class="tu-list">
                <li><span class="tu-dot indigo"></span>{{ translate('FinxCart is an online marketplace based in Dubai, UAE, connecting buyers and vendors of digital financial products including Forex CRM software, trading tools, webinars, pre-built websites, and API integrations.') }}</li>
                <li><span class="tu-dot indigo"></span>{{ translate('FinxCart operates solely as an intermediary marketplace and does not itself provide financial advice, brokerage services, or investment products.') }}</li>
                <li><span class="tu-dot indigo"></span>{{ translate('All products and services listed on the platform are provided by independent third-party vendors. FinxCart does not guarantee the performance or outcomes of any listed product.') }}</li>
                <li><span class="tu-dot indigo"></span>{{ translate('FinxCart supports multiple languages and currencies to serve a global community of Forex professionals, brokers, traders, and fintech businesses.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 3: User Accounts --}}
    <div class="tu-section" id="accounts">
        <div class="tu-section-header">
            <div class="tu-section-icon blue"><i class="ti ti-user-circle"></i></div>
            <div>
                <p class="tu-section-title">03. {{ translate('User Accounts') }}</p>
                <p class="tu-section-sub">{{ translate('Registration, security, and account responsibilities') }}</p>
            </div>
        </div>
        <div class="tu-section-body">
            <ul class="tu-list">
                <li><span class="tu-dot blue"></span>{{ translate('You must register for an account to access certain features of the platform. You agree to provide accurate, current, and complete information during registration.') }}</li>
                <li><span class="tu-dot blue"></span>{{ translate('You are solely responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.') }}</li>
                <li><span class="tu-dot blue"></span>{{ translate('You must notify FinxCart immediately of any unauthorized use of your account or any other breach of security.') }}</li>
                <li><span class="tu-dot blue"></span>{{ translate('FinxCart reserves the right to suspend or terminate any account that violates these terms, engages in fraudulent activity, or poses a risk to other users.') }}</li>
                <li><span class="tu-dot blue"></span>{{ translate('One person or business entity may not maintain more than one active vendor account without prior written approval from FinxCart.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 4: Buyer Obligations --}}
    <div class="tu-section" id="buyers">
        <div class="tu-section-header">
            <div class="tu-section-icon emerald"><i class="ti ti-shopping-cart"></i></div>
            <div>
                <p class="tu-section-title">04. {{ translate('Buyer Obligations') }}</p>
                <p class="tu-section-sub">{{ translate('Responsibilities and expectations for buyers') }}</p>
            </div>
        </div>
        <div class="tu-section-body">
            <ul class="tu-list">
                <li><span class="tu-dot emerald"></span>{{ translate('Buyers agree to use purchased products and services only for their intended and lawful purposes.') }}</li>
                <li><span class="tu-dot emerald"></span>{{ translate('Buyers must not redistribute, resell, or share digital products purchased on FinxCart without explicit written permission from the vendor.') }}</li>
                <li><span class="tu-dot emerald"></span>{{ translate('Buyers are responsible for ensuring that any purchased software or integration is compatible with their existing systems before completing a purchase.') }}</li>
                <li><span class="tu-dot emerald"></span>{{ translate('Buyers agree not to engage in chargebacks or payment disputes without first attempting resolution through the FinxCart dispute process.') }}</li>
                <li><span class="tu-dot emerald"></span>{{ translate('Buyers must provide honest and fair reviews of products and vendors based on genuine experience.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 5: Vendor Obligations --}}
    <div class="tu-section" id="vendors">
        <div class="tu-section-header">
            <div class="tu-section-icon amber"><i class="ti ti-building-store"></i></div>
            <div>
                <p class="tu-section-title">05. {{ translate('Vendor Obligations') }}</p>
                <p class="tu-section-sub">{{ translate('Responsibilities and expectations for sellers') }}</p>
            </div>
        </div>
        <div class="tu-section-body">
            <ul class="tu-list">
                <li><span class="tu-dot amber"></span>{{ translate('Vendors must ensure all listed products and services are accurately described, fully functional, and comply with applicable laws and regulations.') }}</li>
                <li><span class="tu-dot amber"></span>{{ translate('Vendors must not list fraudulent, misleading, or unlicensed financial products on the FinxCart marketplace.') }}</li>
                <li><span class="tu-dot amber"></span>{{ translate('Vendors are responsible for providing customer support, updates, and warranty services as stated in their product listings.') }}</li>
                <li><span class="tu-dot amber"></span>{{ translate('Vendors agree to pay all applicable platform commissions and fees as outlined in the FinxCart Vendor Terms agreement.') }}</li>
                <li><span class="tu-dot amber"></span>{{ translate('Vendors must comply with KYC and AML requirements as requested by FinxCart to maintain an active seller account.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 6: Payments --}}
    <div class="tu-section" id="payments">
        <div class="tu-section-header">
            <div class="tu-section-icon purple"><i class="ti ti-credit-card"></i></div>
            <div>
                <p class="tu-section-title">06. {{ translate('Payments and Fees') }}</p>
                <p class="tu-section-sub">{{ translate('Pricing, transactions, and refund conditions') }}</p>
            </div>
        </div>
        <div class="tu-section-body">
            <ul class="tu-list">
                <li><span class="tu-dot purple"></span>{{ translate('All transactions on FinxCart are processed through secure third-party payment gateways including PayPal, Stripe, Skrill, Binance, and WebMoney.') }}</li>
                <li><span class="tu-dot purple"></span>{{ translate('Prices are displayed in USD by default. Currency conversion fees, if any, are the responsibility of the buyer.') }}</li>
                <li><span class="tu-dot purple"></span>{{ translate('Refunds are subject to the individual vendor refund policy and the FinxCart Warranty Policy. FinxCart does not guarantee refunds on all purchases.') }}</li>
                <li><span class="tu-dot purple"></span>{{ translate('FinxCart charges vendors a platform commission on each successful sale. Commission rates are detailed in the Vendor Terms agreement.') }}</li>
                <li><span class="tu-dot purple"></span>{{ translate('Any fraudulent payment activity will result in immediate account suspension and may be reported to the relevant UAE authorities.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 7: Financial Disclaimer --}}
    <div class="tu-section" id="disclaimer">
        <div class="tu-section-header">
            <div class="tu-section-icon rose"><i class="ti ti-alert-circle"></i></div>
            <div>
                <p class="tu-section-title">07. {{ translate('Financial Disclaimer') }}</p>
                <p class="tu-section-sub">{{ translate('Important notice regarding financial products and trading') }}</p>
            </div>
        </div>
        <div class="tu-section-body">
            <ul class="tu-list">
                <li><span class="tu-dot rose"></span>{{ translate('FinxCart is a digital marketplace and does not provide financial advice, investment recommendations, or regulated financial services.') }}</li>
                <li><span class="tu-dot rose"></span>{{ translate('Forex trading and financial markets involve significant risk. Past performance of any product, strategy, or tool listed on FinxCart does not guarantee future results.') }}</li>
                <li><span class="tu-dot rose"></span>{{ translate('Buyers use any trading tools, signals, strategies, or educational content purchased on FinxCart entirely at their own risk.') }}</li>
                <li><span class="tu-dot rose"></span>{{ translate('FinxCart is not liable for any financial losses, missed opportunities, or damages resulting from the use of products or services purchased through the platform.') }}</li>
            </ul>
            <div class="tu-highlight">
                <strong>{{ translate('Important:') }}</strong>
                {{ translate('Trading Forex and financial instruments carries a high level of risk and may not be suitable for all investors. You should only trade with money you can afford to lose. Seek independent financial advice if necessary.') }}
            </div>
        </div>
    </div>

    {{-- Section 8: Intellectual Property --}}
    <div class="tu-section" id="ip">
        <div class="tu-section-header">
            <div class="tu-section-icon indigo"><i class="ti ti-lock"></i></div>
            <div>
                <p class="tu-section-title">08. {{ translate('Intellectual Property') }}</p>
                <p class="tu-section-sub">{{ translate('Ownership rights and usage restrictions') }}</p>
            </div>
        </div>
        <div class="tu-section-body">
            <ul class="tu-list">
                <li><span class="tu-dot indigo"></span>{{ translate('All content on the FinxCart platform including logos, design, text, and software is the intellectual property of FinxCart or its respective vendors and is protected by UAE and international copyright law.') }}</li>
                <li><span class="tu-dot indigo"></span>{{ translate('Buyers are granted a limited, non-exclusive, non-transferable license to use purchased digital products for their intended purpose only.') }}</li>
                <li><span class="tu-dot indigo"></span>{{ translate('Unauthorized reproduction, distribution, resale, or modification of any product purchased on FinxCart is strictly prohibited and may result in legal action.') }}</li>
                <li><span class="tu-dot indigo"></span>{{ translate('Vendors retain ownership of all intellectual property in their listed products and grant FinxCart a license to display and market their products on the platform.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 9: Termination --}}
    <div class="tu-section" id="termination">
        <div class="tu-section-header">
            <div class="tu-section-icon slate"><i class="ti ti-user-off"></i></div>
            <div>
                <p class="tu-section-title">09. {{ translate('Termination') }}</p>
                <p class="tu-section-sub">{{ translate('Account suspension and termination conditions') }}</p>
            </div>
        </div>
        <div class="tu-section-body">
            <ul class="tu-list">
                <li><span class="tu-dot slate"></span>{{ translate('FinxCart reserves the right to suspend or permanently terminate any user or vendor account at its sole discretion for violation of these terms.') }}</li>
                <li><span class="tu-dot slate"></span>{{ translate('Users may close their account at any time by contacting FinxCart support. Pending transactions and obligations must be resolved before closure.') }}</li>
                <li><span class="tu-dot slate"></span>{{ translate('Upon termination, your right to access the platform ceases immediately. FinxCart is not liable for any loss resulting from account termination.') }}</li>
                <li><span class="tu-dot slate"></span>{{ translate('Sections of these terms that by their nature should survive termination including intellectual property, disclaimers, and governing law will remain in effect.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 10: Governing Law --}}
    <div class="tu-section" id="governing">
        <div class="tu-section-header">
            <div class="tu-section-icon emerald"><i class="ti ti-scale"></i></div>
            <div>
                <p class="tu-section-title">10. {{ translate('Governing Law') }}</p>
                <p class="tu-section-sub">{{ translate('Jurisdiction and legal framework') }}</p>
            </div>
        </div>
        <div class="tu-section-body">
            <ul class="tu-list">
                <li><span class="tu-dot emerald"></span>{{ translate('These Terms of Use are governed by and construed in accordance with the laws of the United Arab Emirates, specifically the Emirate of Dubai.') }}</li>
                <li><span class="tu-dot emerald"></span>{{ translate('Any disputes arising from the use of FinxCart that cannot be resolved through mediation shall be subject to the exclusive jurisdiction of the courts of Dubai, UAE.') }}</li>
                <li><span class="tu-dot emerald"></span>{{ translate('FinxCart complies with UAE Federal Law No. 15 of 2020 on Consumer Protection and applicable e-commerce regulations.') }}</li>
                <li><span class="tu-dot emerald"></span>{{ translate('If any provision of these terms is found to be unenforceable, the remaining provisions will continue in full force and effect.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Footer --}}
    <div class="tu-footer">
        <i class="ti ti-headset"></i>
        <div>
            <strong>{{ translate('Questions about our Terms of Use?') }}</strong>
            {{ translate('If you have any questions or concerns regarding these terms, please contact our legal and support team. We are available 24/7 via WhatsApp at +971 58 884 5033 or through the live chat on the platform. Office: Al Moosa Business Center, Oud Metha, Dubai, UAE.') }}
        </div>
    </div>

</div>

@endsection