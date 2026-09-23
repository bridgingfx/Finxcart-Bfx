@extends('layouts.front-end.app')

@section('title', 'Consumer Rights')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<style>
.cr-wrap {
    font-family: 'DM Sans', sans-serif;
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem 1.5rem 4rem;
}
.cr-hero {
    text-align: center;
    padding: 3.5rem 1rem 3rem;
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 2.5rem;
}
.cr-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #F0FDFA;
    color: #0f766e;
    font-size: 12px;
    font-weight: 500;
    padding: 5px 16px;
    border-radius: 999px;
    margin-bottom: 1.25rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}
.cr-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: 44px;
    font-weight: 600;
    color: #0f172a;
    margin: 0 0 1rem;
    line-height: 1.2;
}
.cr-hero p {
    font-size: 15px;
    color: #64748b;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.75;
}
.cr-updated {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #94a3b8;
    margin-top: 1rem;
}
.cr-toc {
    background: #f8fafc;
    border: 1px solid #e9ecef;
    border-radius: 14px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}
.cr-toc-title {
    font-size: 13px;
    font-weight: 500;
    color: #0f172a;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}
.cr-toc-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 6px 2rem;
}
.cr-toc-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #0f766e;
    text-decoration: none;
    padding: 4px 0;
}
.cr-toc-item:hover { color: #134e4a; text-decoration: underline; }
.cr-toc-num { font-size: 11px; color: #94a3b8; min-width: 18px; }
.cr-section {
    margin-bottom: 1.25rem;
    border: 1px solid #e9ecef;
    border-radius: 14px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
}
.cr-section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 1.25rem 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e9ecef;
}
.cr-section-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 20px;
}
.cr-section-icon.teal   { background: #F0FDFA; color: #0f766e; }
.cr-section-icon.blue   { background: #EFF6FF; color: #1d4ed8; }
.cr-section-icon.indigo { background: #EEF2FF; color: #4338ca; }
.cr-section-icon.green  { background: #F0FDF4; color: #15803d; }
.cr-section-icon.amber  { background: #FFFBEB; color: #b45309; }
.cr-section-icon.purple { background: #FAF5FF; color: #7c3aed; }
.cr-section-icon.orange { background: #FFF7ED; color: #c2410c; }
.cr-section-icon.slate  { background: #F1F5F9; color: #475569; }
.cr-section-title {
    font-family: 'Playfair Display', serif;
    font-size: 17px;
    font-weight: 500;
    color: #0f172a;
    margin: 0;
}
.cr-section-sub { font-size: 12px; color: #94a3b8; margin: 3px 0 0; }
.cr-section-body { padding: 1.5rem; }
.cr-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 11px;
}
.cr-list li {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    font-size: 14px;
    color: #475569;
    line-height: 1.75;
}
.cr-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #0f766e;
    margin-top: 9px;
    flex-shrink: 0;
}
.cr-dot.blue   { background: #1d4ed8; }
.cr-dot.indigo { background: #4338ca; }
.cr-dot.green  { background: #15803d; }
.cr-dot.amber  { background: #b45309; }
.cr-dot.purple { background: #7c3aed; }
.cr-dot.orange { background: #c2410c; }
.cr-dot.slate  { background: #475569; }
.cr-highlight {
    background: #F0FDFA;
    border: 1px solid #99f6e4;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    font-size: 14px;
    color: #134e4a;
    line-height: 1.7;
    margin-top: 1rem;
}
.cr-highlight strong { font-weight: 600; color: #0f172a; }
.cr-highlight.warning { background: #FFFBEB; border-color: #fde68a; color: #78350f; }
.cr-highlight.warning strong { color: #451a03; }
.cr-rights-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 1rem;
}
.cr-right-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem;
    display: flex;
    gap: 10px;
    align-items: flex-start;
}
.cr-right-icon { font-size: 20px; color: #0f766e; flex-shrink: 0; margin-top: 2px; }
.cr-right-title { font-size: 13px; font-weight: 500; color: #0f172a; margin: 0 0 3px; }
.cr-right-desc { font-size: 12px; color: #64748b; line-height: 1.6; margin: 0; }
.cr-footer {
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
.cr-footer i { font-size: 28px; color: #2dd4bf; flex-shrink: 0; margin-top: 2px; }
.cr-footer strong { display: block; color: #f1f5f9; font-size: 15px; font-weight: 500; margin-bottom: 6px; }
@media (max-width: 640px) {
    .cr-hero h1     { font-size: 30px; }
    .cr-toc-grid    { grid-template-columns: 1fr; }
    .cr-rights-grid { grid-template-columns: 1fr; }
    .cr-footer      { flex-direction: column; gap: 1rem; }
}
</style>

<div class="cr-wrap">

    {{-- Hero --}}
    <div class="cr-hero">
        <div class="cr-badge">
            <i class="ti ti-shield-check"></i>
            UAE Federal Law No. 15 of 2020
        </div>
        <h1>{{ translate('consumer_rights') }}</h1>
        <p>{{ translate('finxcart_is_committed_to_protecting_the_rights_of_every_buye') }}</p>
        <div class="cr-updated">
            <i class="ti ti-calendar" style="font-size:14px;"></i>
            Last updated: {{ date('F d, Y') }}
        </div>
    </div>

    {{-- Table of Contents --}}
    <div class="cr-toc">
        <p class="cr-toc-title"><i class="ti ti-list" style="font-size:16px;"></i> {{ translate('table_of_contents') }}</p>
        <div class="cr-toc-grid">
            <a href="#your-rights"     class="cr-toc-item"><span class="cr-toc-num">01</span> {{ translate('your_core_consumer_rights') }}</a>
            <a href="#right-to-info"   class="cr-toc-item"><span class="cr-toc-num">02</span> {{ translate('right_to_information') }}</a>
            <a href="#right-to-safety" class="cr-toc-item"><span class="cr-toc-num">03</span> {{ translate('right_to_safety') }}</a>
            <a href="#right-to-refund" class="cr-toc-item"><span class="cr-toc-num">04</span> {{ translate('right_to_refund_remedy') }}</a>
            <a href="#right-to-fair"   class="cr-toc-item"><span class="cr-toc-num">05</span> {{ translate('right_to_fair_treatment') }}</a>
            <a href="#right-to-data"   class="cr-toc-item"><span class="cr-toc-num">06</span> {{ translate('right_to_data_privacy') }}</a>
            <a href="#complaints"      class="cr-toc-item"><span class="cr-toc-num">07</span> {{ translate('how_to_file_a_complaint') }}</a>
            <a href="#uae-law"         class="cr-toc-item"><span class="cr-toc-num">08</span> {{ translate('uae_legal_framework') }}</a>
        </div>
    </div>

    {{-- Section 1: Core Rights --}}
    <div class="cr-section" id="your-rights">
        <div class="cr-section-header">
            <div class="cr-section-icon teal"><i class="ti ti-shield-check"></i></div>
            <div>
                <p class="cr-section-title">01. Your Core Consumer Rights</p>
                <p class="cr-section-sub">{{ translate('fundamental_protections_guaranteed_to_every_finxcart_buyer') }}</p>
            </div>
        </div>
        <div class="cr-section-body">
            <div class="cr-rights-grid">
                <div class="cr-right-card">
                    <i class="ti ti-info-circle cr-right-icon"></i>
                    <div>
                        <p class="cr-right-title">{{ translate('right_to_information') }}</p>
                        <p class="cr-right-desc">{{ translate('receive_clear_accurate_and_complete_information_about_any_pr') }}</p>
                    </div>
                </div>
                <div class="cr-right-card">
                    <i class="ti ti-shield cr-right-icon"></i>
                    <div>
                        <p class="cr-right-title">{{ translate('right_to_safety') }}</p>
                        <p class="cr-right-desc">{{ translate('be_protected_from_products_or_services_that_could_cause_harm') }}</p>
                    </div>
                </div>
                <div class="cr-right-card">
                    <i class="ti ti-receipt-refund cr-right-icon"></i>
                    <div>
                        <p class="cr-right-title">{{ translate('right_to_remedy') }}</p>
                        <p class="cr-right-desc">{{ translate('receive_a_refund_replacement_or_resolution_when_a_product_fa') }}</p>
                    </div>
                </div>
                <div class="cr-right-card">
                    <i class="ti ti-scale cr-right-icon"></i>
                    <div>
                        <p class="cr-right-title">{{ translate('right_to_fair_treatment') }}</p>
                        <p class="cr-right-desc">{{ translate('be_treated_fairly_without_discrimination_or_deceptive_commer') }}</p>
                    </div>
                </div>
                <div class="cr-right-card">
                    <i class="ti ti-lock cr-right-icon"></i>
                    <div>
                        <p class="cr-right-title">{{ translate('right_to_privacy') }}</p>
                        <p class="cr-right-desc">{{ translate('have_your_personal_data_collected_stored_and_used_in_accorda') }}</p>
                    </div>
                </div>
                <div class="cr-right-card">
                    <i class="ti ti-message-circle cr-right-icon"></i>
                    <div>
                        <p class="cr-right-title">{{ translate('right_to_complain') }}</p>
                        <p class="cr-right-desc">{{ translate('submit_a_complaint_and_receive_a_timely_fair_response_from_f') }}</p>
                    </div>
                </div>
            </div>
            <div class="cr-highlight">
                <strong>{{ translate('legal_basis') }}</strong> These rights are guaranteed under UAE Federal Law No. 15 of 2020 on Consumer Protection and its Executive Regulations. FinxCart is fully committed to upholding these rights for every buyer on our platform.
            </div>
        </div>
    </div>

    {{-- Section 2: Right to Information --}}
    <div class="cr-section" id="right-to-info">
        <div class="cr-section-header">
            <div class="cr-section-icon blue"><i class="ti ti-info-circle"></i></div>
            <div>
                <p class="cr-section-title">02. Right to Information</p>
                <p class="cr-section-sub">{{ translate('your_right_to_clear_and_honest_product_details') }}</p>
            </div>
        </div>
        <div class="cr-section-body">
            <ul class="cr-list">
                <li><span class="cr-dot blue"></span>{{ translate('every_product_listed_on_finxcart_must_include_an_accurate_ti') }}</li>
                <li><span class="cr-dot blue"></span>{{ translate('vendors_are_prohibited_from_using_misleading_exaggerated_or') }}</li>
                <li><span class="cr-dot blue"></span>{{ translate('you_have_the_right_to_know_the_identity_of_the_vendor_sellin') }}</li>
                <li><span class="cr-dot blue"></span>{{ translate('all_fees_commissions_and_charges_must_be_disclosed_upfront_f') }}</li>
                <li><span class="cr-dot blue"></span>{{ translate('if_a_product_s_description_functionality_or_pricing_changes') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 3: Right to Safety --}}
    <div class="cr-section" id="right-to-safety">
        <div class="cr-section-header">
            <div class="cr-section-icon green"><i class="ti ti-shield"></i></div>
            <div>
                <p class="cr-section-title">03. Right to Safety</p>
                <p class="cr-section-sub">{{ translate('protection_from_harmful_or_fraudulent_products') }}</p>
            </div>
        </div>
        <div class="cr-section-body">
            <ul class="cr-list">
                <li><span class="cr-dot green"></span>{{ translate('finxcart_actively_reviews_and_moderates_vendor_listings_to_p') }}</li>
                <li><span class="cr-dot green"></span>{{ translate('any_product_that_poses_a_risk_of_financial_harm_through_dece') }}</li>
                <li><span class="cr-dot green"></span>{{ translate('vendors_must_comply_with_all_applicable_uae_regulations_befo') }}</li>
                <li><span class="cr-dot green"></span>{{ translate('finxcart_does_not_allow_vendors_to_collect_sensitive_buyer_f') }}</li>
                <li><span class="cr-dot green"></span>{{ translate('if_you_encounter_a_product_or_vendor_you_believe_is_fraudule') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 4: Right to Refund --}}
    <div class="cr-section" id="right-to-refund">
        <div class="cr-section-header">
            <div class="cr-section-icon indigo"><i class="ti ti-receipt-refund"></i></div>
            <div>
                <p class="cr-section-title">04. Right to Refund & Remedy</p>
                <p class="cr-section-sub">{{ translate('when_and_how_you_can_claim_a_refund_or_resolution') }}</p>
            </div>
        </div>
        <div class="cr-section-body">
            <ul class="cr-list">
                <li><span class="cr-dot indigo"></span>{{ translate('you_are_entitled_to_a_refund_or_replacement_if_a_product_is') }}</li>
                <li><span class="cr-dot indigo"></span>{{ translate('refund_requests_must_be_submitted_within_7_days_of_purchase') }}</li>
                <li><span class="cr-dot indigo"></span>{{ translate('if_a_vendor_refuses_a_legitimate_refund_finxcart_has_the_aut') }}</li>
                <li><span class="cr-dot indigo"></span>{{ translate('approved_refunds_are_returned_to_the_original_payment_method') }}</li>
                <li><span class="cr-dot indigo"></span>{{ translate('if_your_dispute_is_not_resolved_through_finxcart_s_resolutio') }}</li>
            </ul>
            <div class="cr-highlight">
                <strong>{{ translate('how_to_request_a_refund') }}</strong> Log in to your FinxCart account, go to your Order History, select the relevant order, and click "Request Refund". Alternatively, contact our support team via WhatsApp at +971 58 884 5033 or through live chat.
            </div>
        </div>
    </div>

    {{-- Section 5: Right to Fair Treatment --}}
    <div class="cr-section" id="right-to-fair">
        <div class="cr-section-header">
            <div class="cr-section-icon amber"><i class="ti ti-scale"></i></div>
            <div>
                <p class="cr-section-title">05. Right to Fair Treatment</p>
                <p class="cr-section-sub">{{ translate('protection_from_discrimination_and_unfair_practices') }}</p>
            </div>
        </div>
        <div class="cr-section-body">
            <ul class="cr-list">
                <li><span class="cr-dot amber"></span>{{ translate('every_buyer_on_finxcart_has_the_right_to_be_treated_fairly_a') }}</li>
                <li><span class="cr-dot amber"></span>{{ translate('vendors_must_not_apply_discriminatory_pricing_preferential_t') }}</li>
                <li><span class="cr-dot amber"></span>{{ translate('finxcart_prohibits_all_forms_of_aggressive_deceptive_or_mani') }}</li>
                <li><span class="cr-dot amber"></span>{{ translate('promotional_offers_and_discounts_advertised_on_the_platform') }}</li>
                <li><span class="cr-dot amber"></span>{{ translate('you_have_the_right_to_leave_an_honest_review_of_any_product') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 6: Right to Data Privacy --}}
    <div class="cr-section" id="right-to-data">
        <div class="cr-section-header">
            <div class="cr-section-icon purple"><i class="ti ti-lock"></i></div>
            <div>
                <p class="cr-section-title">06. Right to Data Privacy</p>
                <p class="cr-section-sub">{{ translate('how_your_personal_data_is_protected_on_finxcart') }}</p>
            </div>
        </div>
        <div class="cr-section-body">
            <ul class="cr-list">
                <li><span class="cr-dot purple"></span>{{ translate('your_personal_data_is_collected_stored_and_processed_in_acco') }}</li>
                <li><span class="cr-dot purple"></span>{{ translate('you_have_the_right_to_access_correct_or_request_deletion_of') }}</li>
                <li><span class="cr-dot purple"></span>{{ translate('finxcart_will_never_sell_your_personal_data_to_third_parties') }}</li>
                <li><span class="cr-dot purple"></span>{{ translate('you_have_the_right_to_withdraw_your_consent_to_marketing_com') }}</li>
                <li><span class="cr-dot purple"></span>{{ translate('in_the_event_of_a_data_breach_affecting_your_personal_inform') }}</li>
            </ul>
        </div>
    </div>

    {{-- Section 7: How to File a Complaint --}}
    <div class="cr-section" id="complaints">
        <div class="cr-section-header">
            <div class="cr-section-icon orange"><i class="ti ti-message-report"></i></div>
            <div>
                <p class="cr-section-title">07. How to File a Complaint</p>
                <p class="cr-section-sub">{{ translate('steps_to_raise_a_concern_or_dispute_on_finxcart') }}</p>
            </div>
        </div>
        <div class="cr-section-body">
            <ul class="cr-list">
                <li><span class="cr-dot orange"></span><div><strong style="color:#0f172a;font-size:13px;">{{ translate('step_1_contact_the_vendor') }}</strong> {{ translate('use_the_finxcart_messaging_system_to_contact_the_vendor_dire') }}</div></li>
                <li><span class="cr-dot orange"></span><div><strong style="color:#0f172a;font-size:13px;">{{ translate('step_2_open_a_dispute') }}</strong> {{ translate('if_unresolved_go_to_your_order_history_select_the_order_and') }}</div></li>
                <li><span class="cr-dot orange"></span><div><strong style="color:#0f172a;font-size:13px;">{{ translate('step_3_finxcart_review') }}</strong> {{ translate('our_team_will_review_the_evidence_submitted_by_both_parties') }}</div></li>
                <li><span class="cr-dot orange"></span><div><strong style="color:#0f172a;font-size:13px;">{{ translate('step_4_escalate_externally') }}</strong> {{ translate('if_you_are_unsatisfied_with_finxcart_s_decision_you_may_esca') }}</div></li>
            </ul>
            <div class="cr-highlight">
                <strong>Contact FinxCart Support:</strong> WhatsApp: +971 58 884 5033 | Live Chat: available on the platform 24/7 | Email: support@finxcart.com | Office: Al Moosa Business Center, Oud Metha, Dubai, UAE.
            </div>
        </div>
    </div>

    {{-- Section 8: UAE Legal Framework --}}
    <div class="cr-section" id="uae-law">
        <div class="cr-section-header">
            <div class="cr-section-icon slate"><i class="ti ti-building-bank"></i></div>
            <div>
                <p class="cr-section-title">08. UAE Legal Framework</p>
                <p class="cr-section-sub">{{ translate('laws_and_regulations_that_protect_you_as_a_consumer') }}</p>
            </div>
        </div>
        <div class="cr-section-body">
            <ul class="cr-list">
                <li><span class="cr-dot slate"></span><div><strong style="color:#0f172a;font-size:13px;">{{ translate('federal_law_no_15_of_2020_on_consumer_protection') }}</strong> {{ translate('the_primary_legislation_governing_consumer_rights_in_the_uae') }}</div></li>
                <li><span class="cr-dot slate"></span><div><strong style="color:#0f172a;font-size:13px;">{{ translate('federal_decree_law_no_45_of_2021_on_personal_data_protection') }}</strong> {{ translate('governs_how_personal_data_must_be_collected_stored_processed') }}</div></li>
                <li><span class="cr-dot slate"></span><div><strong style="color:#0f172a;font-size:13px;">{{ translate('cabinet_resolution_no_66_of_2023_e_commerce_regulations') }}</strong> {{ translate('regulates_electronic_commerce_activities_in_the_uae_includin') }}</div></li>
                <li><span class="cr-dot slate"></span><div><strong style="color:#0f172a;font-size:13px;">{{ translate('uae_central_bank_dfsa_regulations') }}</strong> {{ translate('vendors_listing_any_financial_products_or_services_on_finxca') }}</div></li>
            </ul>
            <div class="cr-highlight warning">
                <strong>{{ translate('external_escalation') }}</strong> If your complaint is not resolved through FinxCart, you have the right to contact the UAE Ministry of Economy's Consumer Protection Department online at consumerprotection.gov.ae or by calling 600 522 225.
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="cr-footer">
        <i class="ti ti-headset"></i>
        <div>
            <strong>{{ translate('we_are_here_to_protect_your_rights') }}</strong>
            If you have any questions about your consumer rights or need assistance with a complaint, our support team is available 24/7 via WhatsApp at +971 58 884 5033, through live chat on the platform, or by email at support@finxcart.com. Office: Al Moosa Business Center, Oud Metha, Dubai, UAE.
        </div>
    </div>

</div>

@endsection