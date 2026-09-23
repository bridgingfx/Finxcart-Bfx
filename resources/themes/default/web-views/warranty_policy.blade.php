@extends('layouts.front-end.app')

@section('title', translate('Warranty Policy'))

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<style>
.wp-wrap {
    font-family: 'DM Sans', sans-serif;
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem 1.5rem 4rem;
}

.wp-hero {
    text-align: center;
    padding: 3.5rem 1rem 3rem;
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 2.5rem;
}
.wp-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #EEF2FF;
    color: #3730a3;
    font-size: 12px;
    font-weight: 500;
    padding: 5px 16px;
    border-radius: 999px;
    margin-bottom: 1.25rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}
.wp-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: 44px;
    font-weight: 600;
    color: #0f172a;
    margin: 0 0 1rem;
    line-height: 1.2;
}
.wp-hero p {
    font-size: 15px;
    color: #64748b;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.75;
}

.wp-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    background: #e9ecef;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 2.5rem;
}
.wp-stat {
    background: #fff;
    padding: 1.5rem 1rem;
    text-align: center;
}
.wp-stat-value {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 600;
    color: #1e3a5f;
    display: block;
}
.wp-stat-label {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 4px;
    display: block;
}

.wp-section {
    margin-bottom: 1.25rem;
    border: 1px solid #e9ecef;
    border-radius: 14px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
}
.wp-section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 1.25rem 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e9ecef;
}
.wp-section-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 20px;
}
.wp-section-icon.indigo  { background: #EEF2FF; color: #4338ca; }
.wp-section-icon.blue    { background: #EFF6FF; color: #1d4ed8; }
.wp-section-icon.emerald { background: #ECFDF5; color: #059669; }
.wp-section-icon.amber   { background: #FFFBEB; color: #b45309; }
.wp-section-icon.rose    { background: #FFF1F2; color: #e11d48; }

.wp-section-title {
    font-family: 'Playfair Display', serif;
    font-size: 17px;
    font-weight: 500;
    color: #0f172a;
    margin: 0;
}
.wp-section-sub {
    font-size: 12px;
    color: #94a3b8;
    margin: 3px 0 0;
}
.wp-section-body {
    padding: 1.5rem;
}

.wp-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 11px;
}
.wp-list li {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    font-size: 14px;
    color: #475569;
    line-height: 1.75;
}
.wp-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #4338ca;
    margin-top: 9px;
    flex-shrink: 0;
}
.wp-dot.blue    { background: #1d4ed8; }
.wp-dot.emerald { background: #059669; }
.wp-dot.amber   { background: #b45309; }
.wp-dot.rose    { background: #e11d48; }

.wp-footer {
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
.wp-footer i {
    font-size: 28px;
    color: #6366f1;
    flex-shrink: 0;
    margin-top: 2px;
}
.wp-footer strong {
    display: block;
    color: #f1f5f9;
    font-size: 15px;
    font-weight: 500;
    margin-bottom: 6px;
}

@media (max-width: 640px) {
    .wp-hero h1 { font-size: 30px; }
    .wp-stats   { grid-template-columns: 1fr; }
    .wp-footer  { flex-direction: column; gap: 1rem; }
}
</style>

<div class="wp-wrap">

    {{-- Hero --}}
    <div class="wp-hero">
        <div class="wp-badge">
            <i class="ti ti-shield-check"></i>
            {{ translate('Buyer and Vendor Protection') }}
        </div>
        <h1>{{ translate('Warranty Policy') }}</h1>
        <p>{{ translate('FinxCart is committed to delivering high-quality digital financial products and services. This policy outlines your rights and protections as a buyer or vendor on our marketplace.') }}</p>
    </div>

    {{-- Stats --}}
    <div class="wp-stats">
        <div class="wp-stat">
            <span class="wp-stat-value">100%</span>
            <span class="wp-stat-label">{{ translate('Digital Product Coverage') }}</span>
        </div>
        <div class="wp-stat">
            <span class="wp-stat-value">24/7</span>
            <span class="wp-stat-label">{{ translate('Support Availability') }}</span>
        </div>
        <div class="wp-stat">
            <span class="wp-stat-value">{{ translate('UAE') }}</span>
            <span class="wp-stat-label">{{ translate('Regulated and Compliant') }}</span>
        </div>
    </div>

    {{-- Section 1: Product Coverage --}}
    <div class="wp-section">
        <div class="wp-section-header">
            <div class="wp-section-icon indigo"><i class="ti ti-certificate"></i></div>
            <div>
                <p class="wp-section-title">{{ translate('Digital Product Warranty Coverage') }}</p>
                <p class="wp-section-sub">{{ translate('What is covered under our warranty') }}</p>
            </div>
        </div>
        <div class="wp-section-body">
            <ul class="wp-list">
                <li>
                    <span class="wp-dot"></span>
                    {{ translate('Our warranty covers defects in digital products including CRM software, Forex tools, webinars, pre-built websites, and API integrations sold on the FinxCart marketplace.') }}
                </li>
                <li>
                    <span class="wp-dot"></span>
                    {{ translate('Products or services that do not match their listed description, features, or specifications are eligible for a warranty claim.') }}
                </li>
                <li>
                    <span class="wp-dot"></span>
                    {{ translate('Warranty duration for each product or service is set by the respective vendor and is clearly displayed on the product listing page.') }}
                </li>
                <li>
                    <span class="wp-dot"></span>
                    {{ translate('FinxCart acts as an intermediary marketplace. Warranty services for third-party vendor products are the responsibility of the vendor. FinxCart will assist buyers in resolving disputes when vendors are unresponsive.') }}
                </li>
                <li>
                    <span class="wp-dot"></span>
                    {{ translate('Your original purchase receipt and order confirmation are required to validate any warranty claim. Please retain these documents after purchase.') }}
                </li>
            </ul>
        </div>
    </div>

    {{-- Section 2: Vendor Responsibilities --}}
    <div class="wp-section">
        <div class="wp-section-header">
            <div class="wp-section-icon blue"><i class="ti ti-building-store"></i></div>
            <div>
                <p class="wp-section-title">{{ translate('Vendor Responsibilities') }}</p>
                <p class="wp-section-sub">{{ translate('Obligations of sellers on the FinxCart platform') }}</p>
            </div>
        </div>
        <div class="wp-section-body">
            <ul class="wp-list">
                <li>
                    <span class="wp-dot blue"></span>
                    {{ translate('Vendors must ensure all listed products and services function as described and meet the quality standards stated on their product pages.') }}
                </li>
                <li>
                    <span class="wp-dot blue"></span>
                    {{ translate('Vendors are responsible for providing timely support, updates, and fixes for their digital products within the warranty period.') }}
                </li>
                <li>
                    <span class="wp-dot blue"></span>
                    {{ translate('Vendors must clearly state the warranty period, support scope, and any limitations in their product listings before publishing.') }}
                </li>
                <li>
                    <span class="wp-dot blue"></span>
                    {{ translate('Failure to honor warranty obligations may result in vendor account suspension or removal from the FinxCart marketplace.') }}
                </li>
            </ul>
        </div>
    </div>

    {{-- Section 3: Buyer Rights --}}
    <div class="wp-section">
        <div class="wp-section-header">
            <div class="wp-section-icon emerald"><i class="ti ti-user-check"></i></div>
            <div>
                <p class="wp-section-title">{{ translate('Buyer Rights and Claims') }}</p>
                <p class="wp-section-sub">{{ translate('How to raise and resolve a warranty claim') }}</p>
            </div>
        </div>
        <div class="wp-section-body">
            <ul class="wp-list">
                <li>
                    <span class="wp-dot emerald"></span>
                    {{ translate('Buyers may raise a warranty claim by contacting the vendor directly through the FinxCart messaging system within the stated warranty period.') }}
                </li>
                <li>
                    <span class="wp-dot emerald"></span>
                    {{ translate('If a vendor does not respond within 5 business days, buyers may escalate the claim to FinxCart support for mediation.') }}
                </li>
                <li>
                    <span class="wp-dot emerald"></span>
                    {{ translate('FinxCart may issue a full or partial refund at its discretion if the product is found to be materially defective or misrepresented.') }}
                </li>
                <li>
                    <span class="wp-dot emerald"></span>
                    {{ translate('Warranty claims must be submitted with evidence such as screenshots, error logs, or a detailed description of the issue encountered.') }}
                </li>
                <li>
                    <span class="wp-dot emerald"></span>
                    {{ translate('Resolutions provided under warranty do not automatically extend the original warranty period unless stated by the vendor.') }}
                </li>
            </ul>
        </div>
    </div>

    {{-- Section 4: Exclusions --}}
    <div class="wp-section">
        <div class="wp-section-header">
            <div class="wp-section-icon amber"><i class="ti ti-alert-triangle"></i></div>
            <div>
                <p class="wp-section-title">{{ translate('Warranty Exclusions') }}</p>
                <p class="wp-section-sub">{{ translate('What is not covered under this policy') }}</p>
            </div>
        </div>
        <div class="wp-section-body">
            <ul class="wp-list">
                <li>
                    <span class="wp-dot amber"></span>
                    {{ translate('Issues caused by buyer misuse, unauthorized modifications, or incompatible third-party integrations are not covered.') }}
                </li>
                <li>
                    <span class="wp-dot amber"></span>
                    {{ translate('Trading losses, missed market opportunities, or financial outcomes resulting from the use of any product on FinxCart are not covered under this warranty.') }}
                </li>
                <li>
                    <span class="wp-dot amber"></span>
                    {{ translate('Webinars, educational content, and courses that have been fully accessed or downloaded are not eligible for warranty claims.') }}
                </li>
                <li>
                    <span class="wp-dot amber"></span>
                    {{ translate('Claims submitted after the stated warranty period has expired will not be entertained.') }}
                </li>
                <li>
                    <span class="wp-dot amber"></span>
                    {{ translate('Products affected by changes in third-party platform APIs such as MT4, MT5, or broker integrations due to external provider decisions are excluded.') }}
                </li>
            </ul>
        </div>
    </div>

    {{-- Section 5: Compliance --}}
    <div class="wp-section">
        <div class="wp-section-header">
            <div class="wp-section-icon rose"><i class="ti ti-scale"></i></div>
            <div>
                <p class="wp-section-title">{{ translate('Regulatory Compliance and Governing Law') }}</p>
                <p class="wp-section-sub">{{ translate('Legal framework governing this policy') }}</p>
            </div>
        </div>
        <div class="wp-section-body">
            <ul class="wp-list">
                <li>
                    <span class="wp-dot rose"></span>
                    {{ translate('This warranty policy is governed by the laws of the United Arab Emirates and applicable regulations in the Dubai jurisdiction.') }}
                </li>
                <li>
                    <span class="wp-dot rose"></span>
                    {{ translate('FinxCart complies with UAE consumer protection laws and financial marketplace regulations in providing this warranty framework.') }}
                </li>
                <li>
                    <span class="wp-dot rose"></span>
                    {{ translate('Any disputes not resolved through FinxCart mediation may be referred to the relevant UAE consumer protection authority or courts of Dubai.') }}
                </li>
                <li>
                    <span class="wp-dot rose"></span>
                    {{ translate('FinxCart reserves the right to update this policy at any time. Continued use of the platform constitutes acceptance of any revised terms.') }}
                </li>
            </ul>
        </div>
    </div>

    {{-- Footer CTA --}}
    <div class="wp-footer">
        <i class="ti ti-headset"></i>
        <div>
            <strong>{{ translate('Need help with a warranty claim?') }}</strong>
            {{ translate('Our support team is available 24/7 to assist buyers and vendors with any warranty-related inquiries. Contact us via WhatsApp at +971 58 884 5033 or through the live chat on our platform. Office: Al Moosa Business Center, Oud Metha, Dubai, UAE.') }}
        </div>
    </div>

</div>

@endsection