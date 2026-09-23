@extends('layouts.front-end.app')

@section('title', translate('Terms of Sale'))

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<style>
.ts-wrap {
    font-family: 'DM Sans', sans-serif;
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem 1.5rem 4rem;
}

/* Hero */
.ts-hero {
    text-align: center;
    padding: 3.5rem 1rem 3rem;
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 2.5rem;
}
.ts-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #F0FDF4;
    color: #15803d;
    font-size: 12px;
    font-weight: 500;
    padding: 5px 16px;
    border-radius: 999px;
    margin-bottom: 1.25rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}
.ts-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: 44px;
    font-weight: 600;
    color: #0f172a;
    margin: 0 0 1rem;
    line-height: 1.2;
}
.ts-hero p {
    font-size: 15px;
    color: #64748b;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.75;
}
.ts-updated {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #94a3b8;
    margin-top: 1rem;
}

/* TOC */
.ts-toc {
    background: #f8fafc;
    border: 1px solid #e9ecef;
    border-radius: 14px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}
.ts-toc-title {
    font-size: 13px;
    font-weight: 500;
    color: #0f172a;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ts-toc-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 6px 2rem;
}
.ts-toc-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #15803d;
    text-decoration: none;
    padding: 4px 0;
}
.ts-toc-item:hover { color: #14532d; text-decoration: underline; }
.ts-toc-num {
    font-size: 11px;
    color: #94a3b8;
    min-width: 18px;
}

/* Section cards */
.ts-section {
    margin-bottom: 1.25rem;
    border: 1px solid #e9ecef;
    border-radius: 14px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
}
.ts-section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 1.25rem 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e9ecef;
}
.ts-section-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 20px;
}
.ts-section-icon.green   { background: #F0FDF4; color: #15803d; }
.ts-section-icon.indigo  { background: #EEF2FF; color: #4338ca; }
.ts-section-icon.blue    { background: #EFF6FF; color: #1d4ed8; }
.ts-section-icon.amber   { background: #FFFBEB; color: #b45309; }
.ts-section-icon.rose    { background: #FFF1F2; color: #e11d48; }
.ts-section-icon.purple  { background: #FAF5FF; color: #7c3aed; }
.ts-section-icon.slate   { background: #F1F5F9; color: #475569; }
.ts-section-icon.orange  { background: #FFF7ED; color: #c2410c; }
.ts-section-icon.teal    { background: #F0FDFA; color: #0f766e; }

.ts-section-title {
    font-family: 'Playfair Display', serif;
    font-size: 17px;
    font-weight: 500;
    color: #0f172a;
    margin: 0;
}
.ts-section-sub {
    font-size: 12px;
    color: #94a3b8;
    margin: 3px 0 0;
}
.ts-section-body {
    padding: 1.5rem;
}

.ts-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 11px;
}
.ts-list li {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    font-size: 14px;
    color: #475569;
    line-height: 1.75;
}
.ts-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #15803d;
    margin-top: 9px;
    flex-shrink: 0;
}
.ts-dot.indigo  { background: #4338ca; }
.ts-dot.blue    { background: #1d4ed8; }
.ts-dot.amber   { background: #b45309; }
.ts-dot.rose    { background: #e11d48; }
.ts-dot.purple  { background: #7c3aed; }
.ts-dot.slate   { background: #475569; }
.ts-dot.orange  { background: #c2410c; }
.ts-dot.teal    { background: #0f766e; }

/* Highlight box */
.ts-highlight {
    background: #F0FDF4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    font-size: 14px;
    color: #14532d;
    line-height: 1.7;
    margin-top: 1rem;
}
.ts-highlight strong { font-weight: 600; color: #052e16; }

.ts-highlight.warning {
    background: #FFFBEB;
    border-color: #fde68a;
    color: #78350f;
}
.ts-highlight.warning strong { color: #451a03; }

/* Table */
.ts-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
    margin-top: 1rem;
}
.ts-table th {
    background: #f1f5f9;
    color: #0f172a;
    font-weight: 500;
    text-align: left;
    padding: 10px 14px;
    border: 1px solid #e2e8f0;
}
.ts-table td {
    padding: 10px 14px;
    border: 1px solid #e2e8f0;
    color: #475569;
    vertical-align: top;
}
.ts-table tr:nth-child(even) td { background: #f8fafc; }

/* Footer */
.ts-footer {
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
.ts-footer i {
    font-size: 28px;
    color: #22c55e;
    flex-shrink: 0;
    margin-top: 2px;
}
.ts-footer strong {
    display: block;
    color: #f1f5f9;
    font-size: 15px;
    font-weight: 500;
    margin-bottom: 6px;
}

@media (max-width: 640px) {
    .ts-hero h1  { font-size: 30px; }
    .ts-toc-grid { grid-template-columns: 1fr; }
    .ts-footer   { flex-direction: column; gap: 1rem; }
    .ts-table    { font-size: 12px; }
}
</style>

<div class="ts-wrap">

    {{-- Hero --}}
    <div class="ts-hero">
        <div class="ts-badge">
            <i class="ti ti-receipt"></i>
            {{ translate('Vendor Agreement') }}
        </div>
        <h1>{{ translate('Terms of Sale') }}</h1>
        <p>{{ translate('These Terms of Sale govern all transactions conducted on the FinxCart marketplace between buyers, vendors, and FinxCart. By completing a purchase or listing a product, you agree to the following conditions.') }}</p>
        <div class="ts-updated">
            <i class="ti ti-calendar" style="font-size:14px;"></i>
            {{ translate('Last updated') }}: {{ date('F d, Y') }}
        </div>
    </div>

    {{-- Table of Contents --}}
    <div class="ts-toc">
        <p class="ts-toc-title"><i class="ti ti-list" style="font-size:16px;"></i> {{ translate('Table of Contents') }}</p>
        <div class="ts-toc-grid">
            <a href="#scope"         class="ts-toc-item"><span class="ts-toc-num">01</span> {{ translate('Scope of Agreement') }}</a>
            <a href="#orders"        class="ts-toc-item"><span class="ts-toc-num">02</span> {{ translate('Orders & Purchases') }}</a>
            <a href="#pricing"       class="ts-toc-item"><span class="ts-toc-num">03</span> {{ translate('Pricing & Currency') }}</a>
            <a href="#delivery"      class="ts-toc-item"><span class="ts-toc-num">04</span> {{ translate('Delivery of Products') }}</a>
            <a href="#refunds"       class="ts-toc-item"><span class="ts-toc-num">05</span> {{ translate('Refunds & Warranty') }}</a>
            <a href="#commissions"   class="ts-toc-item"><span class="ts-toc-num">06</span> {{ translate('Commissions & Payouts') }}</a>
            <a href="#prohibited"    class="ts-toc-item"><span class="ts-toc-num">07</span> {{ translate('Prohibited Products') }}</a>
            <a href="#disputes"      class="ts-toc-item"><span class="ts-toc-num">08</span> {{ translate('Dispute Resolution') }}</a>
            <a href="#liability"     class="ts-toc-item"><span class="ts-toc-num">09</span> {{ translate('Limitation of Liability') }}</a>
            <a href="#governing"     class="ts-toc-item"><span class="ts-toc-num">10</span> {{ translate('Governing Law') }}</a>
        </div>
    </div>

    {{-- Section 1: Scope --}}
    <div class="ts-section" id="scope">
        <div class="ts-section-header">
            <div class="ts-section-icon green"><i class="ti ti-file-invoice"></i></div>
            <div>
                <p class="ts-section-title">01. {{ translate('Scope of Agreement') }}</p>
                <p class="ts-section-sub">{{ translate('Who these terms apply to') }}</p>
            </div>
        </div>
        <div class="ts-section-body">
            <ul class="ts-list">
                <li><span class="ts-dot"></span>{{ translate('These Terms of Sale apply to all buyers who purchase products or services on the FinxCart marketplace and all vendors who list and sell products on the platform.') }}</li>
                <li><span class="ts-dot"></span>{{ translate('By placing an order or completing a sale on FinxCart, both buyers and vendors agree to be legally bound by these terms.') }}</li>
                <li><span class="ts-dot"></span>{{ translate('These Terms of Sale are supplementary to the FinxCart Terms of Use and should be read in conjunction with them.') }}</li>
                <li><span class="ts-dot"></span>{{ translate('FinxCart acts solely as an intermediary marketplace facilitating transactions between independent buyers and vendors. FinxCart is not a party to any individual sale contract.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 2: Orders --}}
    <div class="ts-section" id="orders">
        <div class="ts-section-header">
            <div class="ts-section-icon indigo"><i class="ti ti-shopping-bag"></i></div>
            <div>
                <p class="ts-section-title">02. {{ translate('Orders & Purchases') }}</p>
                <p class="ts-section-sub">{{ translate('How orders are placed and confirmed') }}</p>
            </div>
        </div>
        <div class="ts-section-body">
            <ul class="ts-list">
                <li><span class="ts-dot indigo"></span>{{ translate('An order is confirmed once the buyer completes payment and receives a confirmation email from FinxCart. This constitutes a binding purchase agreement between the buyer and vendor.') }}</li>
                <li><span class="ts-dot indigo"></span>{{ translate('Buyers are responsible for reviewing all product details, compatibility requirements, and vendor policies before completing a purchase.') }}</li>
                <li><span class="ts-dot indigo"></span>{{ translate('FinxCart reserves the right to cancel any order that is suspected to be fraudulent, duplicated, or in violation of these terms, with a full refund issued to the buyer.') }}</li>
                <li><span class="ts-dot indigo"></span>{{ translate('Vendors must fulfill confirmed orders within the delivery timeframe stated in their product listing. Failure to do so may result in order cancellation and account penalties.') }}</li>
                <li><span class="ts-dot indigo"></span>{{ translate('Bulk or enterprise orders may require a separate written agreement between the buyer and vendor, facilitated through the FinxCart platform.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 3: Pricing --}}
    <div class="ts-section" id="pricing">
        <div class="ts-section-header">
            <div class="ts-section-icon blue"><i class="ti ti-currency-dollar"></i></div>
            <div>
                <p class="ts-section-title">03. {{ translate('Pricing & Currency') }}</p>
                <p class="ts-section-sub">{{ translate('How prices are set and displayed') }}</p>
            </div>
        </div>
        <div class="ts-section-body">
            <ul class="ts-list">
                <li><span class="ts-dot blue"></span>{{ translate('All prices on FinxCart are listed in USD (United States Dollar) by default unless otherwise specified by the vendor.') }}</li>
                <li><span class="ts-dot blue"></span>{{ translate('FinxCart may display prices in other currencies as a convenience. The actual charge will be processed in USD and currency conversion is handled by the payment gateway.') }}</li>
                <li><span class="ts-dot blue"></span>{{ translate('Vendors are solely responsible for setting accurate and fair prices for their products and for updating them as necessary.') }}</li>
                <li><span class="ts-dot blue"></span>{{ translate('FinxCart may apply platform-wide promotional discounts or coupon codes with prior notice to vendors. Vendor participation in promotions is voluntary.') }}</li>
                <li><span class="ts-dot blue"></span>{{ translate('Any applicable taxes, VAT, or government levies are the sole responsibility of the buyer and/or vendor as required by their local jurisdiction.') }}</li>
            </ul>
            <div class="ts-highlight">
                <strong>{{ translate('Note:') }}</strong>
                {{ translate('FinxCart does not add hidden fees at checkout. The price displayed on the product page is the final price charged to the buyer, excluding any currency conversion or banking fees applied by the buyer\'s payment provider.') }}
            </div>
        </div>
    </div>

    {{-- Section 4: Delivery --}}
    <div class="ts-section" id="delivery">
        <div class="ts-section-header">
            <div class="ts-section-icon teal"><i class="ti ti-package"></i></div>
            <div>
                <p class="ts-section-title">04. {{ translate('Delivery of Products') }}</p>
                <p class="ts-section-sub">{{ translate('How digital products are delivered to buyers') }}</p>
            </div>
        </div>
        <div class="ts-section-body">
            <ul class="ts-list">
                <li><span class="ts-dot teal"></span>{{ translate('All products sold on FinxCart are digital in nature. Delivery is fulfilled electronically through download links, license keys, account credentials, or direct vendor communication.') }}</li>
                <li><span class="ts-dot teal"></span>{{ translate('Delivery timelines vary by product type. Instant-delivery products are made available immediately upon payment confirmation. Custom or service-based products follow the timeline stated in the listing.') }}</li>
                <li><span class="ts-dot teal"></span>{{ translate('Vendors must clearly state the expected delivery method and timeline in their product listing before publishing.') }}</li>
                <li><span class="ts-dot teal"></span>{{ translate('If a buyer does not receive their product within the stated delivery window, they must first contact the vendor through the FinxCart messaging system before raising a dispute.') }}</li>
                <li><span class="ts-dot teal"></span>{{ translate('FinxCart is not responsible for delivery failures caused by incorrect buyer contact information, spam filters blocking emails, or vendor-side technical issues.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 5: Refunds --}}
    <div class="ts-section" id="refunds">
        <div class="ts-section-header">
            <div class="ts-section-icon rose"><i class="ti ti-receipt-refund"></i></div>
            <div>
                <p class="ts-section-title">05. {{ translate('Refunds & Warranty') }}</p>
                <p class="ts-section-sub">{{ translate('Conditions for refunds and product guarantees') }}</p>
            </div>
        </div>
        <div class="ts-section-body">
            <ul class="ts-list">
                <li><span class="ts-dot rose"></span>{{ translate('Refunds are governed by the individual vendor\'s refund policy as stated in their product listing. Buyers must review the vendor\'s policy before purchasing.') }}</li>
                <li><span class="ts-dot rose"></span>{{ translate('FinxCart offers a platform-level Warranty Policy that covers cases where a product is significantly not as described, non-functional, or undelivered within the stated timeframe.') }}</li>
                <li><span class="ts-dot rose"></span>{{ translate('Refund requests must be submitted within 7 days of purchase through the FinxCart Resolution Center. Requests submitted after this window may not be eligible.') }}</li>
                <li><span class="ts-dot rose"></span>{{ translate('Refunds will not be issued for change of mind, incompatibility due to buyer\'s system not meeting stated requirements, or partial use of a digital product.') }}</li>
                <li><span class="ts-dot rose"></span>{{ translate('In cases where a refund is approved, funds will be returned to the original payment method within 5–10 business days depending on the payment gateway.') }}</li>
            </ul>
            <div class="ts-highlight warning">
                <strong>{{ translate('Important:') }}</strong>
                {{ translate('Initiating a chargeback or payment dispute with your bank without first going through the FinxCart Resolution Center may result in immediate account suspension and disqualification from future purchases.') }}
            </div>
        </div>
    </div>

    {{-- Section 6: Commissions --}}
    <div class="ts-section" id="commissions">
        <div class="ts-section-header">
            <div class="ts-section-icon purple"><i class="ti ti-chart-pie"></i></div>
            <div>
                <p class="ts-section-title">06. {{ translate('Commissions & Payouts') }}</p>
                <p class="ts-section-sub">{{ translate('How vendors are paid and platform fees') }}</p>
            </div>
        </div>
        <div class="ts-section-body">
            <ul class="ts-list">
                <li><span class="ts-dot purple"></span>{{ translate('FinxCart charges vendors a commission on each completed sale. The applicable commission rate is communicated to the vendor upon account approval and may be updated with 30 days\' notice.') }}</li>
                <li><span class="ts-dot purple"></span>{{ translate('Vendor payouts are processed after the buyer\'s satisfaction window has closed and no active disputes exist on the order.') }}</li>
                <li><span class="ts-dot purple"></span>{{ translate('Payouts are made via the vendor\'s registered payment method — PayPal, Skrill, Binance Pay, WebMoney, or bank transfer — subject to minimum payout thresholds.') }}</li>
                <li><span class="ts-dot purple"></span>{{ translate('FinxCart reserves the right to withhold vendor payouts if the account is under review for policy violations, fraud, or active buyer disputes.') }}</li>
                <li><span class="ts-dot purple"></span>{{ translate('Any payment gateway fees associated with receiving a payout are the responsibility of the vendor.') }}</li>
            </ul>

            <table class="ts-table">
                <thead>
                    <tr>
                        <th>{{ translate('Payout Method') }}</th>
                        <th>{{ translate('Processing Time') }}</th>
                        <th>{{ translate('Minimum Payout') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ translate('paypal') }}</td>
                        <td>{{ translate('1–3 Business Days') }}</td>
                        <td>$50</td>
                    </tr>
                    <tr>
                        <td>{{ translate('skrill') }}</td>
                        <td>{{ translate('1–3 Business Days') }}</td>
                        <td>$50</td>
                    </tr>
                    <tr>
                        <td>{{ translate('binance_pay') }}</td>
                        <td>{{ translate('Instant – 24 Hours') }}</td>
                        <td>$20</td>
                    </tr>
                    <tr>
                        <td>{{ translate('webmoney') }}</td>
                        <td>{{ translate('1–2 Business Days') }}</td>
                        <td>$30</td>
                    </tr>
                    <tr>
                        <td>{{ translate('Bank Transfer') }}</td>
                        <td>{{ translate('3–7 Business Days') }}</td>
                        <td>$200</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section 7: Prohibited --}}
    <div class="ts-section" id="prohibited">
        <div class="ts-section-header">
            <div class="ts-section-icon amber"><i class="ti ti-ban"></i></div>
            <div>
                <p class="ts-section-title">07. {{ translate('Prohibited Products') }}</p>
                <p class="ts-section-sub">{{ translate('What cannot be listed or sold on FinxCart') }}</p>
            </div>
        </div>
        <div class="ts-section-body">
            <ul class="ts-list">
                <li><span class="ts-dot amber"></span>{{ translate('Unlicensed or pirated software, cracked tools, or any product that infringes on third-party intellectual property rights.') }}</li>
                <li><span class="ts-dot amber"></span>{{ translate('Regulated financial services including investment advisory, fund management, signal subscriptions that constitute financial advice under UAE law, without proper licensing.') }}</li>
                <li><span class="ts-dot amber"></span>{{ translate('Products that make false or exaggerated claims about trading performance, guaranteed returns, or risk-free investment outcomes.') }}</li>
                <li><span class="ts-dot amber"></span>{{ translate('Any product designed to manipulate, deceive, or defraud buyers, including fake reviews, counterfeit software licenses, or misleading descriptions.') }}</li>
                <li><span class="ts-dot amber"></span>{{ translate('Products that violate UAE Federal laws, DIFC regulations, or any applicable international financial regulations.') }}</li>
            </ul>
            <div class="ts-highlight warning">
                <strong>{{ translate('Enforcement:') }}</strong>
                {{ translate('Vendors found listing prohibited products will face immediate removal of listings, account suspension, forfeiture of pending payouts, and may be reported to the relevant UAE regulatory authorities.') }}
            </div>
        </div>
    </div>

    {{-- Section 8: Disputes --}}
    <div class="ts-section" id="disputes">
        <div class="ts-section-header">
            <div class="ts-section-icon orange"><i class="ti ti-gavel"></i></div>
            <div>
                <p class="ts-section-title">08. {{ translate('Dispute Resolution') }}</p>
                <p class="ts-section-sub">{{ translate('How conflicts between buyers and vendors are handled') }}</p>
            </div>
        </div>
        <div class="ts-section-body">
            <ul class="ts-list">
                <li><span class="ts-dot orange"></span>{{ translate('In the event of a transaction dispute, the buyer must first contact the vendor directly through the FinxCart messaging system and allow 48 hours for a response.') }}</li>
                <li><span class="ts-dot orange"></span>{{ translate('If the dispute is not resolved between the buyer and vendor, either party may escalate it to the FinxCart Resolution Center within 7 days of the original complaint.') }}</li>
                <li><span class="ts-dot orange"></span>{{ translate('FinxCart will review the dispute, request evidence from both parties, and issue a binding decision within 5–10 business days.') }}</li>
                <li><span class="ts-dot orange"></span>{{ translate('FinxCart\'s decision in a dispute is final and binding on both the buyer and vendor. Both parties agree to this process by using the platform.') }}</li>
                <li><span class="ts-dot orange"></span>{{ translate('Abuse of the dispute resolution system, including filing false claims, will result in account penalties or permanent suspension.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 9: Liability --}}
    <div class="ts-section" id="liability">
        <div class="ts-section-header">
            <div class="ts-section-icon slate"><i class="ti ti-shield-off"></i></div>
            <div>
                <p class="ts-section-title">09. {{ translate('Limitation of Liability') }}</p>
                <p class="ts-section-sub">{{ translate('The extent of FinxCart\'s legal responsibility') }}</p>
            </div>
        </div>
        <div class="ts-section-body">
            <ul class="ts-list">
                <li><span class="ts-dot slate"></span>{{ translate('FinxCart\'s total liability to any buyer or vendor arising from any transaction shall not exceed the total transaction value of the order in question.') }}</li>
                <li><span class="ts-dot slate"></span>{{ translate('FinxCart is not liable for indirect, incidental, special, or consequential damages including loss of profits, data loss, or trading losses resulting from the use of any product purchased on the platform.') }}</li>
                <li><span class="ts-dot slate"></span>{{ translate('FinxCart does not guarantee the accuracy, reliability, or fitness for purpose of any vendor-listed product or service.') }}</li>
                <li><span class="ts-dot slate"></span>{{ translate('FinxCart is not responsible for any third-party services, integrations, or platforms that a purchased product may interact with.') }}</li>
                <li><span class="ts-dot slate"></span>{{ translate('Nothing in these terms excludes liability for fraud, willful misconduct, or any liability that cannot be excluded by law.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 10: Governing Law --}}
    <div class="ts-section" id="governing">
        <div class="ts-section-header">
            <div class="ts-section-icon green"><i class="ti ti-scale"></i></div>
            <div>
                <p class="ts-section-title">10. {{ translate('Governing Law') }}</p>
                <p class="ts-section-sub">{{ translate('Jurisdiction and legal framework') }}</p>
            </div>
        </div>
        <div class="ts-section-body">
            <ul class="ts-list">
                <li><span class="ts-dot"></span>{{ translate('These Terms of Sale are governed by and construed in accordance with the laws of the United Arab Emirates, specifically the Emirate of Dubai.') }}</li>
                <li><span class="ts-dot"></span>{{ translate('Any disputes arising from these Terms of Sale that cannot be resolved through the FinxCart Resolution Center shall be subject to the exclusive jurisdiction of the courts of Dubai, UAE.') }}</li>
                <li><span class="ts-dot"></span>{{ translate('FinxCart complies with UAE Federal Law No. 15 of 2020 on Consumer Protection, Federal Decree-Law No. 45 of 2021 on the Protection of Personal Data, and applicable e-commerce regulations.') }}</li>
                <li><span class="ts-dot"></span>{{ translate('If any provision of these Terms of Sale is found to be invalid or unenforceable, the remaining provisions shall continue in full force and effect.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Footer --}}
    <div class="ts-footer">
        <i class="ti ti-headset"></i>
        <div>
            <strong>{{ translate('Questions about our Terms of Sale?') }}</strong>
            {{ translate('If you have any questions or concerns regarding these terms, please contact our legal and support team. We are available 24/7 via WhatsApp at +971 58 884 5033 or through the live chat on the platform. Office: Al Moosa Business Center, Oud Metha, Dubai, UAE.') }}
        </div>
    </div>

</div>

@endsection