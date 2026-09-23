@extends('layouts.front-end.app')

@section('title', 'Privacy Policy')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;1,500&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --navy:   #0f172a;
    --slate:  #334155;
    --muted:  #64748b;
    --light:  #94a3b8;
    --border: #e2e8f0;
    --bg:     #f8fafc;
    --white:  #ffffff;
    --blue:   #1d4ed8;
    --blue-lt:#EFF6FF;
    --blue-bd:#bfdbfe;
    --accent: #3b82f6;
}

.pp-page {
    font-family: 'DM Sans', sans-serif;
    background: var(--white);
    color: var(--slate);
    max-width: 860px;
    margin: 0 auto;
    padding: 0 1.5rem 5rem;
}

/* Hero */
.pp-hero {
    padding: 4rem 1rem 3rem;
    text-align: center;
    border-bottom: 1px solid var(--border);
    margin-bottom: 3rem;
}
.pp-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--blue-lt);
    color: var(--blue);
    font-size: 11px;
    font-weight: 500;
    letter-spacing: .08em;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: 999px;
    margin-bottom: 1.25rem;
}
.pp-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(32px, 6vw, 52px);
    font-weight: 600;
    color: var(--navy);
    margin: 0 0 1rem;
    line-height: 1.15;
}
.pp-hero p {
    font-size: 15px;
    color: var(--muted);
    max-width: 580px;
    margin: 0 auto 1.25rem;
    line-height: 1.8;
    font-weight: 300;
}
.pp-meta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--light);
}

/* TOC */
.pp-toc {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2.5rem;
}
.pp-toc-label {
    font-size: 11px;
    font-weight: 500;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--light);
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 7px;
}
.pp-toc-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 4px 2rem;
}
.pp-toc-link {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: var(--blue);
    text-decoration: none;
    padding: 5px 0;
    transition: color .15s;
}
.pp-toc-link:hover { color: var(--navy); }
.pp-toc-num {
    font-size: 10px;
    color: var(--light);
    min-width: 20px;
}

/* Accordion */
.pp-section {
    border: 1px solid var(--border);
    border-radius: 12px;
    margin-bottom: 10px;
    overflow: hidden;
    background: var(--white);
    transition: box-shadow .2s;
}
.pp-section:hover { box-shadow: 0 4px 20px rgba(0,0,0,.06); }
.pp-section.is-open {
    box-shadow: 0 4px 24px rgba(29,78,216,.08);
    border-color: var(--blue-bd);
}

.pp-trigger {
    width: 100%;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 1.1rem 1.4rem;
    text-align: left;
    background: var(--bg);
    transition: background .15s;
}
.pp-trigger:hover { background: #f1f5f9; }
.pp-section.is-open .pp-trigger { background: var(--blue-lt); }

.pp-icon {
    width: 38px; height: 38px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.pp-icon.blue   { background: #EFF6FF; color: #1d4ed8; }
.pp-icon.indigo { background: #EEF2FF; color: #4338ca; }
.pp-icon.purple { background: #FAF5FF; color: #7c3aed; }
.pp-icon.teal   { background: #F0FDFA; color: #0f766e; }
.pp-icon.green  { background: #F0FDF4; color: #15803d; }
.pp-icon.amber  { background: #FFFBEB; color: #b45309; }
.pp-icon.rose   { background: #FFF1F2; color: #e11d48; }
.pp-icon.slate  { background: #F1F5F9; color: #475569; }
.pp-icon.orange { background: #FFF7ED; color: #c2410c; }
.pp-icon.cyan   { background: #ECFEFF; color: #0e7490; }

.pp-trigger-text { flex: 1; }
.pp-trigger-title {
    font-family: 'Playfair Display', serif;
    font-size: 16px;
    font-weight: 500;
    color: var(--navy);
    margin: 0;
    display: block;
}
.pp-trigger-sub {
    font-size: 11.5px;
    color: var(--muted);
    margin: 2px 0 0;
    display: block;
}
.pp-chevron {
    font-size: 18px;
    color: var(--light);
    flex-shrink: 0;
    transition: transform .25s ease, color .15s;
}
.pp-section.is-open .pp-chevron {
    transform: rotate(180deg);
    color: var(--blue);
}

.pp-panel {
    max-height: 0;
    overflow: hidden;
    transition: max-height .35s ease;
}
.pp-section.is-open .pp-panel { max-height: 2000px; }

.pp-body {
    padding: 1.4rem 1.5rem 1.5rem;
    border-top: 1px solid var(--border);
}

/* List */
.pp-list {
    list-style: none;
    padding: 0; margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.pp-list li {
    display: flex;
    gap: 11px;
    align-items: flex-start;
    font-size: 13.5px;
    color: var(--slate);
    line-height: 1.75;
}
.pp-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--blue);
    margin-top: 9px;
    flex-shrink: 0;
}
.pp-dot.indigo { background: #4338ca; }
.pp-dot.purple { background: #7c3aed; }
.pp-dot.teal   { background: #0f766e; }
.pp-dot.green  { background: #15803d; }
.pp-dot.amber  { background: #b45309; }
.pp-dot.rose   { background: #e11d48; }
.pp-dot.slate  { background: #475569; }
.pp-dot.orange { background: #c2410c; }
.pp-dot.cyan   { background: #0e7490; }

/* Note box */
.pp-note {
    background: var(--blue-lt);
    border: 1px solid var(--blue-bd);
    border-radius: 9px;
    padding: .9rem 1.1rem;
    font-size: 13px;
    color: #1e3a8a;
    line-height: 1.7;
    margin-top: 1rem;
}
.pp-note strong { font-weight: 600; }

/* Rights grid */
.pp-rights {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
.pp-right-card {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 9px;
    padding: .9rem;
    display: flex;
    gap: 10px;
    align-items: flex-start;
}
.pp-right-icon { font-size: 17px; color: var(--blue); flex-shrink: 0; margin-top: 1px; }
.pp-right-title { font-size: 12.5px; font-weight: 500; color: var(--navy); margin: 0 0 2px; }
.pp-right-desc  { font-size: 11.5px; color: var(--muted); line-height: 1.6; margin: 0; }

/* Footer */
.pp-footer {
    margin-top: 2.5rem;
    background: var(--navy);
    border-radius: 12px;
    padding: 2rem;
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
}
.pp-footer i { font-size: 26px; color: var(--accent); flex-shrink: 0; margin-top: 3px; }
.pp-footer-text { font-size: 13.5px; color: #94a3b8; line-height: 1.75; }
.pp-footer-text strong { display: block; color: #f1f5f9; font-size: 14.5px; font-weight: 500; margin-bottom: 5px; }

@media (max-width: 600px) {
    .pp-toc-grid { grid-template-columns: 1fr; }
    .pp-rights   { grid-template-columns: 1fr; }
    .pp-footer   { flex-direction: column; gap: .9rem; }
}
</style>

<div class="pp-page">

    {{-- Hero --}}
    <div class="pp-hero">
        <div class="pp-badge"><i class="ti ti-shield-lock"></i> {{ translate('data_protection') }}</div>
        <h1>{{ translate('privacy_policy_2') }}</h1>
        <p>{{ translate('at_finxcart_your_privacy_matters_this_policy_explains_how_we') }}</p>
        <div class="pp-meta">
            <i class="ti ti-calendar" style="font-size:13px;"></i>
            Last updated: {{ date('F d, Y') }}
        </div>
    </div>

    {{-- TOC --}}
    <div class="pp-toc">
        <p class="pp-toc-label"><i class="ti ti-list"></i> {{ translate('table_of_contents') }}</p>
        <div class="pp-toc-grid">
            <a href="#s1"  class="pp-toc-link"><span class="pp-toc-num">01</span> {{ translate('information_we_collect') }}</a>
            <a href="#s2"  class="pp-toc-link"><span class="pp-toc-num">02</span> {{ translate('how_we_use_your_data') }}</a>
            <a href="#s3"  class="pp-toc-link"><span class="pp-toc-num">03</span> {{ translate('sharing_of_information') }}</a>
            <a href="#s4"  class="pp-toc-link"><span class="pp-toc-num">04</span> {{ translate('cookies_tracking') }}</a>
            <a href="#s5"  class="pp-toc-link"><span class="pp-toc-num">05</span> {{ translate('data_storage_security') }}</a>
            <a href="#s6"  class="pp-toc-link"><span class="pp-toc-num">06</span> {{ translate('data_retention') }}</a>
            <a href="#s7"  class="pp-toc-link"><span class="pp-toc-num">07</span> {{ translate('your_rights') }}</a>
            <a href="#s8"  class="pp-toc-link"><span class="pp-toc-num">08</span> {{ translate('third_party_links') }}</a>
            <a href="#s9"  class="pp-toc-link"><span class="pp-toc-num">09</span> {{ translate('children_s_privacy') }}</a>
            <a href="#s10" class="pp-toc-link"><span class="pp-toc-num">10</span> {{ translate('changes_to_this_policy') }}</a>
        </div>
    </div>

    {{-- 01 --}}
    <div class="pp-section" id="s1">
        <button class="pp-trigger" onclick="ppToggle(this)">
            <span class="pp-icon blue"><i class="ti ti-database"></i></span>
            <span class="pp-trigger-text">
                <span class="pp-trigger-title">01. Information We Collect</span>
                <span class="pp-trigger-sub">{{ translate('what_data_we_gather_from_you') }}</span>
            </span>
            <i class="ti ti-chevron-down pp-chevron"></i>
        </button>
        <div class="pp-panel"><div class="pp-body">
            <ul class="pp-list">
                <li><span class="pp-dot"></span>{{ translate('personal_identification_information_including_your_full_name') }}</li>
                <li><span class="pp-dot"></span>{{ translate('business_information_for_vendors_including_company_name_trad') }}</li>
                <li><span class="pp-dot"></span>{{ translate('transaction_data_including_purchase_history_order_details_pa') }}</li>
                <li><span class="pp-dot"></span>{{ translate('technical_data_including_your_ip_address_browser_type_device') }}</li>
                <li><span class="pp-dot"></span>{{ translate('communications_data_including_messages_sent_through_our_plat') }}</li>
                <li><span class="pp-dot"></span>{{ translate('kyc_and_aml_verification_documents_including_government_issu') }}</li>
            </ul>
        </div></div>
    </div>

    {{-- 02 --}}
    <div class="pp-section" id="s2">
        <button class="pp-trigger" onclick="ppToggle(this)">
            <span class="pp-icon indigo"><i class="ti ti-settings"></i></span>
            <span class="pp-trigger-text">
                <span class="pp-trigger-title">02. How We Use Your Data</span>
                <span class="pp-trigger-sub">{{ translate('the_purposes_for_which_we_process_your_information') }}</span>
            </span>
            <i class="ti ti-chevron-down pp-chevron"></i>
        </button>
        <div class="pp-panel"><div class="pp-body">
            <ul class="pp-list">
                <li><span class="pp-dot indigo"></span>{{ translate('to_create_and_manage_your_account_verify_your_identity_and_p') }}</li>
                <li><span class="pp-dot indigo"></span>{{ translate('to_process_transactions_facilitate_payments_issue_invoices_a') }}</li>
                <li><span class="pp-dot indigo"></span>{{ translate('to_communicate_with_you_regarding_your_orders_account_activi') }}</li>
                <li><span class="pp-dot indigo"></span>{{ translate('to_send_you_relevant_marketing_communications_product_recomm') }}</li>
                <li><span class="pp-dot indigo"></span>{{ translate('to_detect_prevent_and_investigate_fraudulent_activity_securi') }}</li>
                <li><span class="pp-dot indigo"></span>{{ translate('to_comply_with_applicable_uae_laws_regulatory_requirements_a') }}</li>
                <li><span class="pp-dot indigo"></span>{{ translate('to_improve_and_personalise_the_finxcart_platform_through_ana') }}</li>
            </ul>
        </div></div>
    </div>

    {{-- 03 --}}
    <div class="pp-section" id="s3">
        <button class="pp-trigger" onclick="ppToggle(this)">
            <span class="pp-icon purple"><i class="ti ti-share"></i></span>
            <span class="pp-trigger-text">
                <span class="pp-trigger-title">03. Sharing of Information</span>
                <span class="pp-trigger-sub">{{ translate('who_we_share_your_data_with_and_why') }}</span>
            </span>
            <i class="ti ti-chevron-down pp-chevron"></i>
        </button>
        <div class="pp-panel"><div class="pp-body">
            <ul class="pp-list">
                <li><span class="pp-dot purple"></span>{{ translate('finxcart_does_not_sell_rent_or_trade_your_personal_informati') }}</li>
                <li><span class="pp-dot purple"></span>{{ translate('we_share_necessary_transaction_data_with_vendors_solely_to_f') }}</li>
                <li><span class="pp-dot purple"></span>{{ translate('we_share_data_with_trusted_third_party_service_providers_inc') }}</li>
                <li><span class="pp-dot purple"></span>{{ translate('we_may_disclose_your_information_to_regulatory_authorities_l') }}</li>
                <li><span class="pp-dot purple"></span>{{ translate('in_the_event_of_a_merger_acquisition_or_sale_of_finxcart_ass') }}</li>
            </ul>
            <div class="pp-note">
                <strong>{{ translate('our_commitment') }}</strong> We will never share your personal data with advertisers or data brokers. Any third party we work with is contractually required to protect your information in accordance with applicable UAE data protection law.
            </div>
        </div></div>
    </div>

    {{-- 04 --}}
    <div class="pp-section" id="s4">
        <button class="pp-trigger" onclick="ppToggle(this)">
            <span class="pp-icon teal"><i class="ti ti-cookie"></i></span>
            <span class="pp-trigger-text">
                <span class="pp-trigger-title">04. Cookies & Tracking</span>
                <span class="pp-trigger-sub">{{ translate('how_we_use_cookies_and_similar_technologies') }}</span>
            </span>
            <i class="ti ti-chevron-down pp-chevron"></i>
        </button>
        <div class="pp-panel"><div class="pp-body">
            <ul class="pp-list">
                <li><span class="pp-dot teal"></span>{{ translate('finxcart_uses_cookies_and_similar_tracking_technologies_to_e') }}</li>
                <li><span class="pp-dot teal"></span>{{ translate('we_use_analytics_cookies_such_as_google_analytics_to_underst') }}</li>
                <li><span class="pp-dot teal"></span>{{ translate('we_use_functional_cookies_to_store_your_language_preference') }}</li>
                <li><span class="pp-dot teal"></span>{{ translate('we_may_use_marketing_cookies_to_display_relevant_advertiseme') }}</li>
                <li><span class="pp-dot teal"></span>{{ translate('you_can_manage_or_disable_cookies_through_your_browser_setti') }}</li>
            </ul>
        </div></div>
    </div>

    {{-- 05 --}}
    <div class="pp-section" id="s5">
        <button class="pp-trigger" onclick="ppToggle(this)">
            <span class="pp-icon green"><i class="ti ti-lock"></i></span>
            <span class="pp-trigger-text">
                <span class="pp-trigger-title">05. Data Storage & Security</span>
                <span class="pp-trigger-sub">{{ translate('how_we_protect_and_store_your_information') }}</span>
            </span>
            <i class="ti ti-chevron-down pp-chevron"></i>
        </button>
        <div class="pp-panel"><div class="pp-body">
            <ul class="pp-list">
                <li><span class="pp-dot green"></span>{{ translate('all_personal_data_collected_by_finxcart_is_stored_on_secure') }}</li>
                <li><span class="pp-dot green"></span>{{ translate('we_implement_industry_standard_security_measures_including_s') }}</li>
                <li><span class="pp-dot green"></span>{{ translate('access_to_personal_data_is_strictly_limited_to_authorised_fi') }}</li>
                <li><span class="pp-dot green"></span>{{ translate('payment_information_is_processed_exclusively_through_pci_dss') }}</li>
                <li><span class="pp-dot green"></span>{{ translate('in_the_event_of_a_data_breach_that_affects_your_personal_inf') }}</li>
            </ul>
        </div></div>
    </div>

    {{-- 06 --}}
    <div class="pp-section" id="s6">
        <button class="pp-trigger" onclick="ppToggle(this)">
            <span class="pp-icon amber"><i class="ti ti-calendar-time"></i></span>
            <span class="pp-trigger-text">
                <span class="pp-trigger-title">06. Data Retention</span>
                <span class="pp-trigger-sub">{{ translate('how_long_we_keep_your_personal_information') }}</span>
            </span>
            <i class="ti ti-chevron-down pp-chevron"></i>
        </button>
        <div class="pp-panel"><div class="pp-body">
            <ul class="pp-list">
                <li><span class="pp-dot amber"></span>{{ translate('we_retain_your_personal_data_for_as_long_as_your_account_is') }}</li>
                <li><span class="pp-dot amber"></span>{{ translate('transaction_records_and_financial_data_are_retained_for_a_mi') }}</li>
                <li><span class="pp-dot amber"></span>{{ translate('kyc_and_aml_verification_documents_submitted_by_vendors_are') }}</li>
                <li><span class="pp-dot amber"></span>{{ translate('if_you_close_your_account_we_will_delete_or_anonymise_your_p') }}</li>
                <li><span class="pp-dot amber"></span>{{ translate('marketing_consent_records_are_retained_for_the_duration_of_y') }}</li>
            </ul>
        </div></div>
    </div>

    {{-- 07 --}}
    <div class="pp-section" id="s7">
        <button class="pp-trigger" onclick="ppToggle(this)">
            <span class="pp-icon cyan"><i class="ti ti-user-check"></i></span>
            <span class="pp-trigger-text">
                <span class="pp-trigger-title">07. Your Rights</span>
                <span class="pp-trigger-sub">{{ translate('your_data_protection_rights_under_uae_law') }}</span>
            </span>
            <i class="ti ti-chevron-down pp-chevron"></i>
        </button>
        <div class="pp-panel"><div class="pp-body">
            <div class="pp-rights">
                <div class="pp-right-card">
                    <i class="ti ti-eye pp-right-icon"></i>
                    <div>
                        <p class="pp-right-title">{{ translate('right_to_access') }}</p>
                        <p class="pp-right-desc">{{ translate('request_a_copy_of_the_personal_data_we_hold_about_you_at_any') }}</p>
                    </div>
                </div>
                <div class="pp-right-card">
                    <i class="ti ti-edit pp-right-icon"></i>
                    <div>
                        <p class="pp-right-title">{{ translate('right_to_rectification') }}</p>
                        <p class="pp-right-desc">{{ translate('request_correction_of_any_inaccurate_or_incomplete_personal') }}</p>
                    </div>
                </div>
                <div class="pp-right-card">
                    <i class="ti ti-trash pp-right-icon"></i>
                    <div>
                        <p class="pp-right-title">{{ translate('right_to_erasure') }}</p>
                        <p class="pp-right-desc">{{ translate('request_deletion_of_your_personal_data_subject_to_legal_rete') }}</p>
                    </div>
                </div>
                <div class="pp-right-card">
                    <i class="ti ti-hand-stop pp-right-icon"></i>
                    <div>
                        <p class="pp-right-title">{{ translate('right_to_object') }}</p>
                        <p class="pp-right-desc">{{ translate('object_to_the_processing_of_your_data_for_marketing_or_profi') }}</p>
                    </div>
                </div>
                <div class="pp-right-card">
                    <i class="ti ti-download pp-right-icon"></i>
                    <div>
                        <p class="pp-right-title">{{ translate('right_to_portability') }}</p>
                        <p class="pp-right-desc">{{ translate('request_your_data_in_a_structured_machine_readable_format_fo') }}</p>
                    </div>
                </div>
                <div class="pp-right-card">
                    <i class="ti ti-mail pp-right-icon"></i>
                    <div>
                        <p class="pp-right-title">{{ translate('withdraw_consent') }}</p>
                        <p class="pp-right-desc">{{ translate('withdraw_marketing_consent_at_any_time_via_account_settings') }}</p>
                    </div>
                </div>
            </div>
            <div class="pp-note" style="margin-top:1rem;">
                <strong>How to Exercise Your Rights:</strong> To exercise any of the above rights, please contact us at privacy@finxcart.com or via WhatsApp at +971 58 884 5033. We will respond within 30 days in accordance with UAE Federal Decree-Law No. 45 of 2021.
            </div>
        </div></div>
    </div>

    {{-- 08 --}}
    <div class="pp-section" id="s8">
        <button class="pp-trigger" onclick="ppToggle(this)">
            <span class="pp-icon orange"><i class="ti ti-external-link"></i></span>
            <span class="pp-trigger-text">
                <span class="pp-trigger-title">08. Third-Party Links</span>
                <span class="pp-trigger-sub">{{ translate('external_websites_and_services_linked_from_finxcart') }}</span>
            </span>
            <i class="ti ti-chevron-down pp-chevron"></i>
        </button>
        <div class="pp-panel"><div class="pp-body">
            <ul class="pp-list">
                <li><span class="pp-dot orange"></span>{{ translate('the_finxcart_platform_may_contain_links_to_third_party_websi') }}</li>
                <li><span class="pp-dot orange"></span>{{ translate('finxcart_has_no_control_over_the_content_privacy_practices_o') }}</li>
                <li><span class="pp-dot orange"></span>{{ translate('we_encourage_you_to_review_the_privacy_policy_of_any_third_p') }}</li>
                <li><span class="pp-dot orange"></span>{{ translate('vendor_product_pages_may_include_links_to_external_demonstra') }}</li>
            </ul>
        </div></div>
    </div>

    {{-- 09 --}}
    <div class="pp-section" id="s9">
        <button class="pp-trigger" onclick="ppToggle(this)">
            <span class="pp-icon rose"><i class="ti ti-mood-kid"></i></span>
            <span class="pp-trigger-text">
                <span class="pp-trigger-title">09. Children's Privacy</span>
                <span class="pp-trigger-sub">{{ translate('our_policy_regarding_minors') }}</span>
            </span>
            <i class="ti ti-chevron-down pp-chevron"></i>
        </button>
        <div class="pp-panel"><div class="pp-body">
            <ul class="pp-list">
                <li><span class="pp-dot rose"></span>{{ translate('finxcart_is_intended_exclusively_for_users_who_are_18_years') }}</li>
                <li><span class="pp-dot rose"></span>{{ translate('we_do_not_knowingly_collect_store_or_process_personal_data_f') }}</li>
                <li><span class="pp-dot rose"></span>{{ translate('if_we_become_aware_that_we_have_inadvertently_collected_pers') }}</li>
                <li><span class="pp-dot rose"></span>If you believe a minor has provided personal data to FinxCart, please contact us immediately at privacy@finxcart.com so we can take appropriate action.</li>
            </ul>
        </div></div>
    </div>

    {{-- 10 --}}
    <div class="pp-section" id="s10">
        <button class="pp-trigger" onclick="ppToggle(this)">
            <span class="pp-icon slate"><i class="ti ti-refresh"></i></span>
            <span class="pp-trigger-text">
                <span class="pp-trigger-title">10. Changes to This Policy</span>
                <span class="pp-trigger-sub">{{ translate('how_we_notify_you_of_updates') }}</span>
            </span>
            <i class="ti ti-chevron-down pp-chevron"></i>
        </button>
        <div class="pp-panel"><div class="pp-body">
            <ul class="pp-list">
                <li><span class="pp-dot slate"></span>{{ translate('finxcart_reserves_the_right_to_update_or_modify_this_privacy') }}</li>
                <span class="pp-dot slate"></span>{{ translate('when_we_make_material_changes_to_this_policy_we_will_notify') }}</li>
                <li><span class="pp-dot slate"></span>{{ translate('the_last_updated_date_at_the_top_of_this_page_reflects_when') }}</li>
                <li><span class="pp-dot slate"></span>{{ translate('your_continued_use_of_the_finxcart_platform_after_any_change') }}</li>
            </ul>
        </div></div>
    </div>

    {{-- Footer --}}
    <div class="pp-footer">
        <i class="ti ti-shield-lock"></i>
        <div class="pp-footer-text">
            <strong>{{ translate('questions_about_your_privacy') }}</strong>
            If you have any questions, concerns, or requests regarding this Privacy Policy or your personal data, please contact our Data Protection team at privacy@finxcart.com, via WhatsApp at +971 58 884 5033, or through the live chat on the platform. Office: Al Moosa Business Center, Oud Metha, Dubai, UAE.
        </div>
    </div>

</div>

<script>
function ppToggle(btn) {
    const section = btn.closest('.pp-section');
    const isOpen  = section.classList.contains('is-open');
    document.querySelectorAll('.pp-section.is-open').forEach(s => s.classList.remove('is-open'));
    if (!isOpen) section.classList.add('is-open');
}

document.addEventListener('DOMContentLoaded', () => {
    const first = document.querySelector('.pp-section');
    if (first) first.classList.add('is-open');
});

document.querySelectorAll('.pp-toc-link').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const target = document.querySelector(link.getAttribute('href'));
        if (!target) return;
        document.querySelectorAll('.pp-section.is-open').forEach(s => s.classList.remove('is-open'));
        target.classList.add('is-open');
        setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 50);
    });
});
</script>

@endsection