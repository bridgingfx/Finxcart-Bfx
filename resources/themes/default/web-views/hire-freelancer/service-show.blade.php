@extends('layouts.front-end.app')

@section('title', $service->title)

@push('css_or_js')
    <style>
        .gig-page { background: #fff; padding: 24px 0 60px; }
        .gig-breadcrumb { font-size: 12px; color: #6b7280; margin-bottom: 14px; }
        .gig-breadcrumb a { color: #6b7280; text-decoration: none; }
        .gig-breadcrumb a:hover { color: #1a2f5e; }
        .gig-title { font-weight: 800; font-size: 26px; color: #1f2937; margin-bottom: 14px; line-height: 1.3; }
        .gig-seller-row { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
        .gig-seller-row img {
            width: 44px; height: 44px; border-radius: 50%; object-fit: cover;
            border: 2px solid #fff; box-shadow: 0 0 0 1px #e7ebf0;
        }
        .gig-seller-name { font-weight: 700; color: #1f2937; font-size: 14px; }
        .gig-seller-meta { font-size: 13px; color: #6b7280; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .gig-level-badge {
            display: inline-flex; align-items: center; font-size: 11px; font-weight: 700;
            background: #eef2ff; color: #3730a3; border-radius: 999px; padding: 2px 8px;
        }
        .gig-rating-stars { color: #f5a623; }

        .gig-gallery {
            border-radius: 16px; overflow: hidden; border: 1px solid #e7ebf0; margin-bottom: 10px; position: relative;
            box-shadow: 0 12px 28px rgba(23, 57, 94, 0.08);
        }
        .gig-gallery-main {
            width: 100%; height: 380px; object-fit: cover; background: #eef2f7; display: block;
            transition: transform .4s ease;
        }
        .gig-gallery:hover .gig-gallery-main { transform: scale(1.03); }
        .gig-gallery-nav {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,.9); border: none; border-radius: 50%; width: 34px; height: 34px;
            display: flex; align-items: center; justify-content: center; cursor: pointer;
        }
        .gig-gallery-nav.prev { left: 12px; }
        .gig-gallery-nav.next { right: 12px; }
        .gig-thumb-strip { display: flex; gap: 8px; margin-bottom: 24px; overflow-x: auto; }
        .gig-thumb-strip img { width: 72px; height: 56px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 2px solid transparent; flex-shrink: 0; }
        .gig-thumb-strip img.is-active { border-color: #1a2f5e; }

        .gig-section { margin-bottom: 26px; }
        .gig-section-title {
            font-weight: 800; font-size: 18px; color: #1f2937; margin-bottom: 12px;
            display: flex; align-items: center; gap: 9px;
        }
        .gig-section-title::before {
            content: ''; width: 5px; height: 18px; border-radius: 4px;
            background: linear-gradient(180deg, #f97316, #fb923c); display: inline-block;
        }
        .gig-description { font-size: 14px; color: #4b5563; white-space: pre-line; line-height: 1.6; }

        .gig-summary-box {
            border: 1px solid #e7ebf0; border-radius: 10px; padding: 18px; background: #fafbfc;
        }
        .gig-summary-box .gig-summary-title {
            display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 15px; color: #1f2937; margin-bottom: 10px;
        }
        .gig-summary-box .gig-summary-title i { color: #f97316; }

        .gig-loved-strip { display: flex; gap: 14px; overflow-x: auto; padding-bottom: 6px; margin-bottom: 22px; }
        .gig-loved-card {
            flex-shrink: 0; width: 260px; border: 1px solid #e8edf3; border-radius: 14px; padding: 16px; background: #fff;
            box-shadow: 0 4px 12px rgba(23, 57, 94, 0.04);
            transition: box-shadow .2s ease, transform .2s ease;
        }
        .gig-loved-card:hover { box-shadow: 0 12px 26px rgba(23, 57, 94, 0.1); transform: translateY(-3px); }
        .gig-loved-card .review-body { font-size: 13px; color: #4b5563; margin-top: 8px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

        .gig-review-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-bottom: 16px; }
        .gig-review-card {
            border: 1px solid #e8edf3; border-radius: 14px; padding: 18px; background: #fff;
            box-shadow: 0 4px 12px rgba(23, 57, 94, 0.04);
            transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
        }
        .gig-review-card:hover {
            box-shadow: 0 12px 26px rgba(23, 57, 94, 0.08); transform: translateY(-2px); border-color: #d8dee7;
        }
        .review-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, #17395e, #2d6cdf); color: #fff;
            display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0;
        }
        .gig-review-card .review-author { font-weight: 700; font-size: 13px; color: #1f2937; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .review-verified-badge {
            display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700;
            background: #e9f9f0; color: #219653; border-radius: 999px; padding: 2px 8px;
        }
        .review-verified-badge i { font-size: 10px; }
        .gig-review-card .review-meta { font-size: 12px; color: #9ca3af; }
        .gig-review-card .review-body { font-size: 13px; color: #4b5563; margin-top: 10px; line-height: 1.55; }
        .gig-rating-stars-row { display: inline-flex; gap: 1px; font-size: 11px; }
        .gig-rating-stars-row i.is-filled { color: #f5a623; }
        .gig-rating-stars-row i.is-empty { color: #e2e6ec; }

        @media (max-width: 767.98px) {
            .gig-review-list { grid-template-columns: 1fr; }
        }

        .gig-inline-message-card {
            border: 1px solid #e7ebf0; border-radius: 14px; padding: 24px;
            background: linear-gradient(180deg, #fafbfc 0%, #ffffff 100%);
        }
        .gig-inline-message-head { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .gig-inline-message-head img {
            width: 48px; height: 48px; border-radius: 50%; object-fit: cover;
            border: 2px solid #fff; box-shadow: 0 0 0 1px #e7ebf0;
        }
        .gig-inline-message-head h2 { font-weight: 800; font-size: 17px; color: #1f2937; margin: 0; }
        .gig-inline-message-head p { font-size: 13px; color: #6b7280; margin: 2px 0 0; }
        .gig-inline-message-card textarea {
            width: 100%; border: 1px solid #e2e6ec; border-radius: 10px; padding: 14px; font-size: 14px;
            resize: vertical; min-height: 100px; transition: border-color .18s ease, box-shadow .18s ease;
        }
        .gig-inline-message-card textarea:focus {
            outline: none; border-color: #1a2f5e; box-shadow: 0 0 0 3px rgba(26, 47, 94, 0.08);
        }
        .gig-inline-message-footer { display: flex; justify-content: flex-end; margin-top: 14px; }
        .gig-inline-message-footer .btn {
            display: inline-flex; align-items: center; gap: 8px; border-radius: 8px; padding: 9px 22px; font-weight: 700;
        }
        .gig-inline-message-login {
            display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
        }

        .gig-pricing-panel {
            border: 1px solid #e7ebf0; border-radius: 16px; overflow: hidden; position: sticky; top: 20px;
            box-shadow: 0 14px 32px rgba(23, 57, 94, 0.08);
        }
        .gig-pricing-tabs { display: flex; background: #fafbfc; }
        .gig-pricing-tab {
            flex: 1; text-align: center; padding: 13px 8px; font-size: 13px; font-weight: 700; color: #6b7280;
            cursor: pointer; border-bottom: 3px solid transparent; background: transparent; transition: color .18s ease, border-color .18s ease;
        }
        .gig-pricing-tab.is-active { color: #17395e; border-bottom-color: #f97316; background: #fff; }
        .gig-pricing-body { padding: 20px; }
        .gig-pricing-body .tier-title { font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: #f97316; }
        .gig-pricing-body .tier-price { font-weight: 800; font-size: 26px; color: #17395e; margin: 6px 0 10px; }
        .gig-pricing-body .tier-desc { font-size: 13px; color: #4b5563; margin-bottom: 14px; }
        .tier-feature-list { list-style: none; padding: 0; margin: 0 0 14px; display: flex; flex-direction: column; gap: 9px; }
        .tier-feature-list li { font-size: 13px; display: flex; align-items: center; gap: 8px; }
        .tier-feature-list li.is-included { color: #1f2937; }
        .tier-feature-list li.is-included i { color: #16a34a; }
        .tier-feature-list li.is-excluded { color: #9ca3af; }
        .tier-feature-list li.is-excluded i { color: #d1d5db; }
        .gig-pricing-meta { display: flex; gap: 16px; font-size: 12px; color: #6b7280; margin-bottom: 16px; }
        .gig-pricing-panel .btn--primary {
            border-radius: 8px; font-weight: 700; padding: 12px; box-shadow: 0 8px 18px rgba(23, 57, 94, 0.18);
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .gig-pricing-panel .btn--primary:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(23, 57, 94, 0.24); }
        .gig-simple-price { padding: 20px; }
        .gig-buying-header { padding: 16px 18px; background: linear-gradient(120deg, #17395e, #1f4a78); color: #fff; }
        .gig-buying-header h3 { color: #fff; font-size: 17px; font-weight: 800; margin: 0; }
        .gig-buying-header p { color: rgba(255,255,255,.78); font-size: 12px; margin: 4px 0 0; }
        .gig-pricing-panel .btn-outline-primary { border-radius: 8px; font-weight: 700; padding: 11px; }
        .gig-quote-card { margin: 0 16px 16px; border: 1px solid #fed7aa; border-radius: 12px; padding: 16px; background: #fff7ed; }
        .gig-quote-card-head { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; }
        .gig-quote-icon { width: 38px; height: 38px; border-radius: 10px; background: #f97316; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .gig-quote-title { font-weight: 800; font-size: 14px; color: #17395e; margin-bottom: 2px; }
        .gig-quote-text { font-size: 12px; color: #6b7280; line-height: 1.5; }
        .gig-package-empty { padding: 18px; font-size: 13px; color: #6b7280; }

        @media (max-width: 991.98px) {
            .gig-pricing-panel { position: static; margin-top: 20px; }
        }

        @media (max-width: 767.98px) {
            .gig-title { font-size: 21px; }
            .gig-gallery-main { height: 240px; }
            .gig-inline-message-card { padding: 18px; }
            .gig-inline-message-login { flex-direction: column; align-items: stretch; text-align: center; }
            .gig-inline-message-login .btn { width: 100%; }
        }

        @media (max-width: 479.98px) {
            .gig-inline-message-head { flex-direction: column; text-align: center; }
        }

        .gig-help-widget {
            border: 1px solid #e7ebf0; border-radius: 14px; overflow: hidden; margin-top: 14px; background: #fff;
            box-shadow: 0 12px 28px rgba(23, 57, 94, 0.06);
        }
        .gig-help-title { font-weight: 800; font-size: 13px; color: #1f2937; padding: 14px 16px 8px; }
        .gig-help-option {
            display: flex; align-items: center; gap: 10px; width: 100%; padding: 12px 16px;
            background: none; border: none; border-top: 1px solid #f1f3f6; text-align: left; cursor: pointer;
            transition: background .15s ease;
        }
        .gig-help-option:hover { background: #fafbfc; }
        .gig-help-option:hover .gig-help-icon { background: #f97316; color: #fff; }
        .gig-help-icon {
            width: 32px; height: 32px; border-radius: 50%; background: #eef2ff; color: #3730a3;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            transition: background .15s ease, color .15s ease;
        }
        .gig-help-label { font-size: 13px; font-weight: 600; color: #1f2937; flex: 1; }
        .gig-help-arrow { color: #9ca3af; }

        .fl-chat-fab {
            position: fixed; left: 20px; bottom: 20px; z-index: 1050;
            display: flex; align-items: center; gap: 10px;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 999px;
            padding: 8px 16px 8px 8px; box-shadow: 0 10px 30px rgba(15,23,42,.15);
            cursor: pointer; max-width: 260px;
        }
        .fl-chat-fab img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }
        .fl-chat-fab .fl-chat-fab-text { font-size: 13px; font-weight: 700; color: #1f2937; }
        .fl-chat-fab-badge {
            position: absolute; top: -4px; left: 28px; background: #e41e3f; color: #fff; font-size: 11px;
            font-weight: 800; min-width: 18px; height: 18px; border-radius: 999px; display: none;
            align-items: center; justify-content: center; padding: 0 4px; border: 2px solid #fff; line-height: 1;
        }
        .fl-chat-fab-badge.is-visible { display: flex; }
        .fl-chat-login-hint { font-size: 13px; color: #6b7280; text-align: center; padding: 10px 0; }
        .fl-chat-panel {
            position: fixed; left: 20px; bottom: 20px; z-index: 1060;
            width: 340px; max-width: calc(100vw - 40px); background: #fff;
            border-radius: 16px; box-shadow: 0 20px 60px rgba(15,23,42,.25);
            display: none; flex-direction: column; overflow: hidden;
        }
        .fl-chat-panel.is-open { display: flex; }
        .fl-chat-panel-header { display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-bottom: 1px solid #eef1f5; }
        .fl-chat-avatar-wrap { position: relative; flex: 0 0 auto; }
        .fl-chat-status-dot { position: absolute; right: 0; bottom: 0; width: 10px; height: 10px; border-radius: 50%; border: 2px solid #fff; background: #94a3b8; }
        .fl-chat-status-dot.is-online { background: #22c55e; }
        .fl-chat-title-wrap { min-width: 0; display: flex; flex-direction: column; gap: 2px; }
        .fl-chat-subtitle { font-size: 11px; color: #64748b; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .fl-chat-subtitle .online-text { color: #16a34a; font-weight: 700; }
        .fl-chat-panel-header img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
        .fl-chat-panel-header .fl-chat-title { font-weight: 700; font-size: 14px; color: #1f2937; }
        .fl-chat-close { margin-left: auto; background: none; border: none; font-size: 20px; line-height: 1; color: #9ca3af; cursor: pointer; }
        .fl-chat-panel-body { padding: 16px; }
        .fl-chat-panel textarea { width: 100%; border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px; font-size: 13px; resize: none; }
        .fl-chat-compose { display: flex; align-items: center; gap: 8px; }
        .fl-chat-attach-btn { width: 38px; height: 38px; border: 1px solid #dbe3ee; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: #17395e; background: #f8fafc; flex: 0 0 auto; }
        .fl-chat-attach-btn:hover { background: #eef6ff; border-color: #93c5fd; }
        .fl-chat-file-name { font-size: 11px; color: #64748b; margin-top: 6px; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .fl-chat-time { display: block; font-size: 10px; color: #94a3b8; margin-top: 4px; }
        .fl-chat-thread .send_msg { align-items: flex-end; }
        .fl-chat-thread .received_msg { align-items: flex-start; }
        .fl-chat-panel-footer { border-top: 1px solid #eef1f5; padding: 10px 14px; display: flex; justify-content: flex-end; }
        .fl-chat-thread {
            max-height: 240px; overflow-y: auto; margin-bottom: 12px; padding-right: 4px;
            display: flex; flex-direction: column;
        }
        .fl-chat-thread .incoming_msg, .fl-chat-thread .outgoing_msg { margin-bottom: 10px; }
        .fl-chat-thread img { width: 26px; height: 26px; border-radius: 50%; object-fit: cover; }
        .gig-page { background: linear-gradient(180deg, #f8fafc 0%, #eef3f8 100%); }
        .gig-gallery { border: 0; border-radius: 18px; box-shadow: 0 22px 55px rgba(15, 23, 42, .16); }
        .gig-title { font-size: 32px; letter-spacing: 0; color: #102a43; }
        .gig-seller-row { background: #fff; border: 1px solid #e7ebf0; border-radius: 14px; padding: 12px 14px; box-shadow: 0 10px 24px rgba(15,23,42,.05); }
        .gig-pricing-panel { border: 0; border-radius: 18px; box-shadow: 0 24px 58px rgba(15, 23, 42, .16); }
        .gig-buying-header { background: linear-gradient(120deg, #17395e, #4f46e5); padding: 20px; }
        .gig-pricing-tab.is-active { color: #4f46e5; border-bottom-color: #f97316; }
        .gig-quote-card { border: 0; border-radius: 14px; background: linear-gradient(180deg, #fff7ed, #fff); box-shadow: inset 0 0 0 1px #fed7aa; }
        .gig-help-widget { border: 0; border-radius: 16px; box-shadow: 0 16px 34px rgba(15, 23, 42, .08); }
        .gig-review-card { border: 0; box-shadow: 0 14px 30px rgba(15,23,42,.07); }
        #getQuoteModal .modal-dialog { max-width: 660px; }
        #getQuoteModal .modal-content { border: 0; border-radius: 18px; overflow: hidden; box-shadow: 0 28px 80px rgba(15, 23, 42, .28); }
        #getQuoteModal .modal-header { background: linear-gradient(120deg, #17395e, #4f46e5); color: #fff; border: 0; padding: 22px 24px; }
        #getQuoteModal .modal-title { color: #fff; font-weight: 800; }
        #getQuoteModal .close { color: #fff; opacity: .9; text-shadow: none; }
        #getQuoteModal .modal-body { padding: 24px; background: #f8fafc; }
        #getQuoteModal form .form-control { border-radius: 12px; border-color: #dbe3ee; min-height: 44px; }
        #getQuoteModal textarea.form-control { min-height: 118px; }
        .quote-delivery-option { border-radius: 999px; font-weight: 800; min-height: 40px; display: inline-flex; align-items: center; }
        .quote-delivery-option.is-selected { border-color: #f97316; background: #fff7ed; color: #17395e; box-shadow: 0 8px 18px rgba(249, 115, 22, .14); }
        @media (max-width: 767.98px) { .gig-title { font-size: 23px; } .gig-seller-row { align-items: flex-start; } }


        .quote-delivery-options { display: flex; gap: 8px; flex-wrap: wrap; }
        .quote-delivery-option {
            border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; padding: 8px 14px;
            font-size: 13px; cursor: pointer;
        }
        .quote-delivery-option.is-selected { border-color: #1a2f5e; background: #eef2ff; color: #1a2f5e; font-weight: 700; }
        .gig-page { background: #f6f8fb; }
        .gig-page .row { align-items: flex-start; }
        .gig-page .col-lg-8 > .gig-section,
        .gig-inline-message-card {
            border: 1px solid #e7ebf0;
            border-radius: 16px;
            padding: 22px;
            background: #fff;
            box-shadow: 0 10px 26px rgba(15, 23, 42, .05);
        }
        .gig-page .col-lg-8 > .gig-section:hover {
            box-shadow: 0 16px 34px rgba(15, 23, 42, .08);
        }
        .gig-breadcrumb {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #fff;
            border: 1px solid #e7ebf0;
        }
        .gig-title { max-width: 920px; }
        .gig-pricing-panel { background: #fff; }
        .rating-picker {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .rating-picker input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .rating-picker label {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            min-height: 38px;
            padding: 8px 13px;
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            background: #fff;
            color: #374151;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            transition: background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
        }
        .rating-picker label i { color: #f5a623; }
        .rating-picker input:checked + label,
        .rating-picker label:hover {
            border-color: #f97316;
            background: #fff7ed;
            color: #17395e;
            box-shadow: 0 8px 18px rgba(249, 115, 22, .13);
        }
        @media (max-width: 767.98px) {
            .gig-page { padding-top: 16px; }
            .gig-page .col-lg-8 > .gig-section,
            .gig-inline-message-card { padding: 16px; border-radius: 12px; }
            .gig-pricing-tabs { overflow-x: auto; }
            .gig-pricing-tab { min-width: 112px; }
        }

        /* Extra service detail redesign pass */
        .gig-page, .gig-page * { box-sizing: border-box; }
        .gig-page { overflow-x: hidden; background: linear-gradient(180deg, #f4f8ff 0%, #fff7ed 45%, #f8fafc 100%); }
        .gig-page .container { max-width: 1180px; width: 100%; }
        .gig-title { color: #0f172a; line-height: 1.15; }
        .gig-seller-row { overflow: hidden; background: rgba(255,255,255,.95); border-color: rgba(148,163,184,.22); box-shadow: 0 16px 36px rgba(15,23,42,.09); }
        .gig-seller-avatar-wrap { position: relative; width: 50px; height: 50px; flex: 0 0 auto; }
        .gig-seller-avatar-wrap img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 0 0 1px #dbe3ee; }
        .freelancer-online-dot { position: absolute; right: -1px; bottom: -1px; width: 14px; height: 14px; border-radius: 50%; background: #22c55e; border: 2px solid #fff; box-shadow: 0 0 0 4px rgba(34,197,94,.18); }
        .gig-online-pill { display: inline-flex; align-items: center; gap: 5px; padding: 4px 9px; border-radius: 999px; background: #f1f5f9; color: #64748b; font-weight: 900; font-size: 11px; }
        .gig-online-pill span { width: 7px; height: 7px; border-radius: 50%; background: #cbd5e1; }
        .gig-online-pill.is-online { background: #ecfdf5; color: #15803d; }
        .gig-online-pill.is-online span { background: #22c55e; }
        .gig-level-badge { background: linear-gradient(135deg, #eef2ff, #fae8ff); color: #4338ca; }
        .gig-gallery, .gig-section, .gig-pricing-panel, .gig-inline-message-card { border-radius: 20px; border: 1px solid rgba(148,163,184,.22); box-shadow: 0 18px 44px rgba(15,23,42,.09); }
        .gig-gallery { overflow: hidden; background: linear-gradient(135deg, #fff7ed, #fff7ed 50%, #fdf2f8); }
        .gig-pricing-tab:nth-child(3n+1) { background: #eff6ff; color: #1d4ed8; }
        .gig-pricing-tab:nth-child(3n+2) { background: #fff7ed; color: #c2410c; }
        .gig-pricing-tab:nth-child(3n+3) { background: #ecfdf5; color: #047857; }
        .gig-pricing-tab.is-active { color: #fff; background: linear-gradient(135deg, #f97316, #db2777); }
        .tier-feature-list li.is-included i { color: #16a34a; }
        .gig-quote-card { background: linear-gradient(135deg, #fff7ed, #eff6ff); border-radius: 18px; }
        .modal-content { border-radius: 22px; overflow: hidden; }
        .quote-delivery-option:nth-child(1) { background: #eff6ff; color: #1d4ed8; }
        .quote-delivery-option:nth-child(2) { background: #fff7ed; color: #c2410c; }
        .quote-delivery-option:nth-child(3) { background: #ecfdf5; color: #047857; }
        .quote-delivery-option:nth-child(4) { background: #fdf2f8; color: #be185d; }
        @media (max-width: 767.98px) {
            .gig-page .row { margin-left: 0; margin-right: 0; }
            .gig-page [class*="col-"] { padding-left: 0; padding-right: 0; }
            .gig-seller-row { align-items: flex-start; }
            .gig-seller-meta { gap: 7px; }
            .gig-pricing-tabs { overflow-x: visible; display: grid; grid-template-columns: 1fr; }
            .gig-pricing-tab { min-width: 0; width: 100%; text-align: center; }
        }

        /* Premium violet service-detail accent */
        .gig-page {
            background: linear-gradient(180deg, #faf7ff 0%, #fff7ed 45%, #f8fafc 100%) !important;
        }
        .gig-buying-header,
        #getQuoteModal .modal-header {
            background: linear-gradient(120deg, #111827, #4338ca) !important;
        }
        .gig-title,
        .tier-price,
        .gig-quote-title {
            color: #111827 !important;
        }
        .gig-pricing-tab.is-active {
            background: linear-gradient(135deg, #4338ca, #ec4899) !important;
            color: #fff !important;
            border-bottom-color: transparent !important;
        }
        .gig-gallery,
        .gig-section,
        .gig-pricing-panel,
        .gig-inline-message-card {
            box-shadow: 0 18px 44px rgba(109,40,217,.12) !important;
            border-color: rgba(79,70,229,.20) !important;
        }
        .gig-section-title::before {
            background: linear-gradient(180deg, #4338ca, #ec4899) !important;
        }
        .gig-pricing-panel .btn--primary,
        .gig-inline-message-footer .btn--primary {
            background: #4338ca !important;
            border-color: #4338ca !important;
        }

        /* Premium royal indigo service palette */
        .gig-page {
            background: linear-gradient(180deg, #f8f7ff 0%, #f8fafc 50%, #ffffff 100%) !important;
        }
        .gig-buying-header,
        #getQuoteModal .modal-header {
            background: linear-gradient(120deg, #111827, #4338ca) !important;
        }
        .gig-pricing-tab.is-active {
            background: linear-gradient(135deg, #4338ca, #06b6d4) !important;
            color: #fff !important;
        }
        .gig-section-title::before {
            background: linear-gradient(180deg, #4338ca, #06b6d4) !important;
        }
        .gig-pricing-panel .btn--primary,
        .gig-inline-message-footer .btn--primary {
            background: #4338ca !important;
            border-color: #4338ca !important;
        }
        .gig-gallery,
        .gig-section,
        .gig-pricing-panel,
        .gig-inline-message-card {
            border-color: rgba(79,70,229,.18) !important;
            box-shadow: 0 18px 44px rgba(49,46,129,.12) !important;
        }

        /* Fiverr-inspired professional service detail palette */
        .gig-page {
            background: #f7f7f7 !important;
        }
        .gig-title,
        .gig-section-title,
        .gig-seller-name,
        .tier-price,
        .gig-quote-title {
            color: #222325 !important;
        }
        .gig-breadcrumb,
        .gig-seller-row,
        .gig-gallery,
        .gig-section,
        .gig-pricing-panel,
        .gig-inline-message-card {
            background: #fff !important;
            border-color: #e4e5e7 !important;
            box-shadow: 0 14px 34px rgba(34,35,37,.08) !important;
        }
        .gig-breadcrumb a:hover,
        .gig-online-pill.is-online,
        .gig-rating-stars,
        .tier-feature-list li.is-included i {
            color: #f59e0b !important;
        }
        .gig-section-title::before {
            background: #f59e0b !important;
        }
        .gig-buying-header,
        #getQuoteModal .modal-header {
            background: linear-gradient(120deg, #222325, #f59e0b) !important;
        }
        .gig-pricing-tab {
            color: #74767e !important;
            background: #fafafa !important;
        }
        .gig-pricing-tab.is-active {
            background: #f59e0b !important;
            color: #fff !important;
            border-bottom-color: #f59e0b !important;
        }
        .gig-pricing-panel .btn--primary,
        .gig-inline-message-footer .btn--primary,
        .gig-pricing-panel .btn-outline-primary:hover {
            background: #f59e0b !important;
            border-color: #f59e0b !important;
            color: #fff !important;
        }
        .gig-quote-card {
            background: #effbf5 !important;
            box-shadow: inset 0 0 0 1px rgba(245,158,11,.18) !important;
        }
        .gig-quote-icon {
            background: #f59e0b !important;
        }
        .quote-delivery-option.is-selected,
        .quote-delivery-option:hover {
            border-color: #f59e0b !important;
            background: #effbf5 !important;
            color: #d97706 !important;
        }
        .review-verified-badge,
        .gig-online-pill.is-online {
            background: #effbf5 !important;
        }

        /* Upwork-inspired service detail accent refinement */
        .gig-buying-header,
        #getQuoteModal .modal-header {
            background: linear-gradient(120deg, #111827, #d97706) !important;
        }
        .gig-pricing-tab.is-active,
        .gig-pricing-panel .btn--primary,
        .gig-inline-message-footer .btn--primary,
        .gig-quote-icon {
            background: #f59e0b !important;
            border-color: #f59e0b !important;
        }
        .gig-breadcrumb a:hover,
        .gig-online-pill.is-online,
        .tier-feature-list li.is-included i {
            color: #d97706 !important;
        }
        .gig-section-title::before {
            background: #f59e0b !important;
        }
        .quote-delivery-option.is-selected,
        .quote-delivery-option:hover {
            border-color: #f59e0b !important;
            color: #d97706 !important;
        }

        /* Final professional navy + amber service palette */
        .gig-buying-header,
        #getQuoteModal .modal-header {
            background: linear-gradient(120deg, #111827, #1e293b) !important;
        }
        .gig-pricing-tab.is-active,
        .gig-pricing-panel .btn--primary,
        .gig-inline-message-footer .btn--primary,
        .gig-quote-icon {
            background: #f59e0b !important;
            border-color: #f59e0b !important;
            color: #fff !important;
        }
        .gig-breadcrumb a:hover,
        .gig-online-pill.is-online,
        .tier-feature-list li.is-included i,
        .quote-delivery-option.is-selected,
        .quote-delivery-option:hover {
            color: #d97706 !important;
        }
        .gig-section-title::before {
            background: linear-gradient(180deg, #111827, #f59e0b) !important;
        }
        .quote-delivery-option.is-selected,
        .quote-delivery-option:hover {
            border-color: #f59e0b !important;
            background: #fff7ed !important;
        }
        .gig-quote-card,
        .review-verified-badge,
        .gig-online-pill.is-online {
            background: #fff7ed !important;
        }
        .gig-gallery,
        .gig-section,
        .gig-pricing-panel,
        .gig-inline-message-card {
            border-color: rgba(245,158,11,.20) !important;
            box-shadow: 0 18px 44px rgba(17,24,39,.10) !important;
        }
    </style>
@endpush

@section('content')
    @php
        $sellerLastActive = $seller?->updated_at;
        $isFreelancerOnline = $seller && ($seller->status === 'approved') && (($seller->account_status ?? 'active') === 'active') && $sellerLastActive?->greaterThan(now()->subMinutes(5));
    @endphp
    <div class="gig-page">
        <div class="container">
            <div class="gig-breadcrumb">
                <a href="{{ route('hire-freelancer') }}">{{ translate('hire_freelancer') }}</a>
                @if($service->category)
                    / <a href="{{ route('hire-freelancer', ['category' => $service->category->slug]) }}">{{ $service->category->name }}</a>
                @endif
                @if($service->specialization)
                    / <a href="{{ route('hire-freelancer', ['specialization' => $service->specialization->slug]) }}">{{ $service->specialization->name }}</a>
                @endif
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <h1 class="gig-title">{{ $service->title }}</h1>

                    <div class="gig-seller-row">
                        <span class="gig-seller-avatar-wrap" title="{{ $displayName }}">
                            <img alt="" src="{{ getStorageImages(path: $imageFullUrl, type: 'backend-profile') }}">
                            @if($isFreelancerOnline)
                                <span class="freelancer-online-dot" title="Online now"></span>
                            @endif
                        </span>
                        <div>
                            <a href="{{ route('hire-freelancer.show', $seller->id) }}" class="gig-seller-name text-decoration-none" title="{{ $displayName }}">{{ $displayName }}</a>
                            <div class="gig-seller-meta">
                                <span class="gig-level-badge">{{ $freelancerLevel }}</span>
                                <span class="gig-online-pill {{ $isFreelancerOnline ? 'is-online' : '' }}"><span></span>{{ $isFreelancerOnline ? 'Online now' : translate('available') }}</span>
                                @if($activeOrdersCount > 0)
                                    <span>&middot; {{ $activeOrdersCount }} {{ translate('orders_in_queue') }}</span>
                                @endif
                                @if($ratingCount > 0)
                                    <span>&middot; <span class="gig-rating-stars">ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â¹ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã¢â‚¬Å“ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¦</span> {{ $ratingAvg }} ({{ $ratingCount }} {{ translate('reviews') }})</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($service->images->count())
                        <div class="gig-gallery">
                            <img class="gig-gallery-main" id="gig-gallery-main" alt="{{ $service->title }}"
                                 src="{{ getStorageImages(path: $service->images->first()->image_full_url, type: 'backend-profile') }}">
                            @if($service->images->count() > 1)
                                <button type="button" class="gig-gallery-nav prev" id="gig-gallery-prev"><i class="tio-chevron-left"></i></button>
                                <button type="button" class="gig-gallery-nav next" id="gig-gallery-next"><i class="tio-chevron-right"></i></button>
                            @endif
                        </div>
                        @if($service->images->count() > 1)
                            <div class="gig-thumb-strip" id="gig-thumb-strip">
                                @foreach($service->images as $index => $image)
                                    <img alt="" data-index="{{ $index }}" class="{{ $index === 0 ? 'is-active' : '' }}"
                                         src="{{ getStorageImages(path: $image->image_full_url, type: 'backend-profile') }}">
                                @endforeach
                            </div>
                        @endif
                    @endif

                    @if($service->description)
                        <div class="gig-section">
                            <h2 class="gig-section-title">{{ translate('about_this_gig') }}</h2>
                            <div class="gig-summary-box">
                                <div class="gig-summary-title"><i class="tio-file-text-outlined"></i> {{ translate('gig_summary') }}</div>
                                <div class="gig-description">{{ $service->description }}</div>
                            </div>
                        </div>
                    @endif

                    @php
                        $renderStarRow = function (int $rating) {
                            $html = '<span class="gig-rating-stars-row">';
                            for ($i = 1; $i <= 5; $i++) {
                                $html .= '<i class="tio-star ' . ($i <= $rating ? 'is-filled' : 'is-empty') . '"></i>';
                            }
                            return $html . '</span>';
                        };
                    @endphp

                    @php($lovedReviews = $reviews->sortByDesc('rating')->take(3))
                    @if($lovedReviews->count())
                        <div class="gig-section">
                            <h2 class="gig-section-title">{{ translate('what_people_loved_about_this_freelancer') }}</h2>
                            <div class="gig-loved-strip">
                                @foreach($lovedReviews as $review)
                                    <div class="gig-loved-card">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="review-avatar">{{ strtoupper(substr($review->reviewer_name, 0, 1)) }}</div>
                                            <div>
                                                <div class="review-author">{{ $review->reviewer_name }}</div>
                                                <div class="review-meta">{!! $renderStarRow($review->rating) !!}</div>
                                            </div>
                                        </div>
                                        @if($review->body)
                                            <div class="review-body">{{ $review->body }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="gig-section" id="reviews">
                        <h2 class="gig-section-title">{{ translate('reviews') }} ({{ $reviews->total() }})</h2>

                        @auth('customer')
                            @if($customerReviewedService)
                                <p class="text-muted small mb-3">{{ translate('you_have_already_reviewed_this_service') }}</p>
                            @else
                                <form action="{{ route('hire.reviews.store', ['type' => 'service', 'id' => $service->id]) }}" method="post" class="gig-review-form mb-4" novalidate>
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label d-block mb-1">{{ translate('your_rating') }}</label>
                                        <div class="rating-picker">
                                            @for($ratingOption = 5; $ratingOption >= 1; $ratingOption--)
                                                <input type="radio" name="rating" id="service-rating-{{ $ratingOption }}" value="{{ $ratingOption }}" required>
                                                <label for="service-rating-{{ $ratingOption }}"><i class="tio-star"></i>{{ $ratingOption }}</label>
                                            @endfor
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <textarea name="body" class="form-control" rows="3" maxlength="2000" placeholder="{{ translate('share_your_experience') }}..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn--primary btn-sm">{{ translate('submit_review') }}</button>
                                </form>
                            @endif
                        @endauth

                        <div class="gig-review-list">
                            @forelse($reviews as $review)
                                <div class="gig-review-card">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="review-avatar">{{ strtoupper(substr($review->reviewer_name, 0, 1)) }}</div>
                                        <div>
                                            <div class="review-author">
                                                {{ $review->reviewer_name }}
                                                @if($review->verified)
                                                    <span class="review-verified-badge"><i class="tio-verified"></i>{{ translate('verified_client') }}</span>
                                                @endif
                                            </div>
                                            <div class="review-meta d-flex align-items-center gap-2">
                                                {!! $renderStarRow($review->rating) !!}
                                                <span>&middot; {{ $review->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="review-body">{{ $review->body }}</div>
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ translate('no_reviews_yet') }}</p>
                            @endforelse
                        </div>

                        {{ $reviews->links() }}
                    </div>

                    <div class="gig-section gig-inline-message-card">
                        <div class="gig-inline-message-head">
                            <span class="gig-seller-avatar-wrap" title="{{ $displayName }}">
                            <img alt="" src="{{ getStorageImages(path: $imageFullUrl, type: 'backend-profile') }}">
                            @if($isFreelancerOnline)
                                <span class="freelancer-online-dot" title="Online now"></span>
                            @endif
                        </span>
                            <div>
                                <h2>{{ translate('send_a_message_to') }} {{ $displayName }}</h2>
                                <p>{{ translate('have_a_question_about_this_service_send_a_message_to') }} {{ $displayName }}</p>
                            </div>
                        </div>
                        @auth('customer')
                            <textarea id="inline-chat-message" rows="4" placeholder="{{ translate('Write_here') }}..."></textarea>
                            <div class="gig-inline-message-footer">
                                <button type="button" class="btn btn--primary" id="inline-chat-send-btn">
                                    {{ translate('send_message') }} <i class="tio-send"></i>
                                </button>
                            </div>
                        @else
                            <div class="gig-inline-message-login">
                                <p class="mb-0 text-muted">{{ translate('please_login_as_a_customer_to_message') }} {{ $displayName }}.</p>
                                <a href="{{ route('customer.auth.login') }}" class="btn btn--primary">{{ translate('login_to_continue') }}</a>
                            </div>
                        @endauth
                    </div>
                </div>

                <div class="col-lg-4">
                    @php($enabledPackages = $service->packages->where('is_enabled', true)->whereNotNull('price')->values())
                    <div class="gig-pricing-panel">
                        <div class="gig-buying-header">
                            <h3>{{ translate('choose_how_to_start') }}</h3>
                            <p>{{ translate('buy_a_package_or_request_a_custom_quote') }}</p>
                        </div>

                        @if($enabledPackages->count())
                            <div class="gig-pricing-tabs">
                                @foreach($enabledPackages as $index => $package)
                                    <div class="gig-pricing-tab {{ $index === 0 ? 'is-active' : '' }}" data-tab-index="{{ $index }}">
                                        {{ translate($package->tier) }}
                                    </div>
                                @endforeach
                            </div>
                            @foreach($enabledPackages as $index => $package)
                                <div class="gig-pricing-body" data-tab-panel="{{ $index }}" style="{{ $index === 0 ? '' : 'display:none' }}">
                                    <div class="tier-title">{{ $package->title ?: translate($package->tier) }}</div>
                                    <div class="tier-price">{{ webCurrencyConverter($package->price) }}</div>
                                    @if($package->description)
                                        <div class="tier-desc">{{ $package->description }}</div>
                                    @endif
                                    @if(!empty($package->features))
                                        <ul class="tier-feature-list">
                                            @foreach($package->features as $feature)
                                                <li class="{{ !empty($feature['included']) ? 'is-included' : 'is-excluded' }}">
                                                    <i class="tio-{{ !empty($feature['included']) ? 'checkmark-circle' : 'clear' }}"></i>
                                                    {{ $feature['label'] }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <div class="gig-pricing-meta">
                                        @if($package->delivery_time_days)
                                            <span><i class="tio-time"></i> {{ $package->delivery_time_days }}-{{ translate('day_delivery') }}</span>
                                        @endif
                                        @if($package->revisions !== null)
                                            <span><i class="tio-refresh"></i> {{ $package->revisions }} {{ translate('revisions') }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('hire.create', $service->id) }}?package={{ $package->tier }}" class="btn btn--primary w-100 d-flex align-items-center justify-content-center gap-2">
                                        {{ translate('buy_now') }} <i class="tio-arrow-forward"></i>
                                    </a>
                                </div>
                            @endforeach
                        @elseif($service->price !== null)
                            <div class="gig-simple-price">
                                <div class="tier-title">{{ translate('fixed_price') }}</div>
                                <div class="tier-price">{{ webCurrencyConverter($service->price) }}</div>
                                @if($service->delivery_time_days)
                                    <div class="gig-pricing-meta"><span><i class="tio-time"></i> {{ $service->delivery_time_days }}-{{ translate('day_delivery') }}</span></div>
                                @endif
                                <a href="{{ route('hire.create', $service->id) }}" class="btn btn--primary w-100 d-flex align-items-center justify-content-center gap-2">
                                    {{ translate('buy_now') }} <i class="tio-arrow-forward"></i>
                                </a>
                            </div>
                        @else
                            <div class="gig-package-empty">{{ translate('package_prices_are_not_configured_yet') }}</div>
                        @endif

                        <div class="gig-quote-card">
                            <div class="gig-quote-card-head">
                                <span class="gig-quote-icon"><i class="tio-message-outlined"></i></span>
                                <div>
                                    <div class="gig-quote-title">{{ translate('request_a_custom_quote') }}</div>
                                    <div class="gig-quote-text">{{ translate('share_your_requirements_and_get_a_custom_price_from_the_freelancer') }}</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2 gig-quote-trigger">
                                {{ translate('request_a_quote') }} <i class="tio-arrow-forward"></i>
                            </button>
                        </div>
                    </div>

                    <div class="gig-help-widget">
                        <div class="gig-help-title">{{ translate('how_can_i_help') }}</div>
                        <button type="button" class="gig-help-option" id="gig-contact-btn">
                            <span class="gig-help-icon"><i class="tio-message-outlined"></i></span>
                            <span class="gig-help-label">{{ translate('ask_a_question') }}</span>
                            <i class="tio-chevron-right gig-help-arrow"></i>
                        </button>
                        <button type="button" class="gig-help-option gig-quote-trigger">
                            <span class="gig-help-icon"><i class="tio-file-text-outlined"></i></span>
                            <span class="gig-help-label">{{ translate('request_a_quote') }}</span>
                            <i class="tio-chevron-right gig-help-arrow"></i>
                        </button>
                    </div>

                    @if($otherServices->count())
                        <div class="mt-4">
                            <h3 class="gig-section-title">{{ translate('more_from_this_freelancer') }}</h3>
                            @foreach($otherServices as $other)
                                <a href="{{ route('hire-freelancer.service-show', $other->id) }}" class="d-flex align-items-center gap-2 mb-2 text-decoration-none">
                                    @if($other->images->first())
                                        <img src="{{ getStorageImages(path: $other->images->first()->image_full_url, type: 'backend-profile') }}" alt="" style="width:52px;height:40px;object-fit:cover;border-radius:6px;">
                                    @endif
                                    <span class="small text-dark">{{ \Illuminate\Support\Str::limit($other->title, 60) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="getQuoteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('request_a_quote') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('hire.quotes.store', $service->id) }}" method="post" enctype="multipart/form-data" id="get-quote-form" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ translate('describe_the_service_you_are_looking_to_purchase') }}</label>
                            <textarea name="description" class="form-control" rows="4" maxlength="2500" required
                                      placeholder="{{ translate('i_am_looking_for') }}..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ translate('attach_files') }}</label>
                            <input type="file" name="attachments[]" class="form-control" multiple
                                   accept=".jpg,.jpeg,.png,.pdf,.webp,.doc,.docx,.zip">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ translate('when_would_you_like_your_service_delivered') }}</label>
                            <div class="quote-delivery-options">
                                <div class="quote-delivery-option" data-value="24_hours">{{ translate('24_hours') }}</div>
                                <div class="quote-delivery-option" data-value="3_days">{{ translate('3_days') }}</div>
                                <div class="quote-delivery-option" data-value="7_days">{{ translate('7_days') }}</div>
                                <div class="quote-delivery-option" data-value="custom">{{ translate('other') }}</div>
                            </div>
                            <input type="hidden" name="delivery_preference" id="quote-delivery-preference" required>
                            <input type="text" name="custom_delivery_text" id="quote-custom-delivery" class="form-control mt-2 d-none"
                                   placeholder="{{ translate('describe_your_preferred_timeline') }}" maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ translate('what_is_your_budget_for_this_service') }}</label>
                            <input type="number" step="0.01" min="0" name="budget" class="form-control">
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn--primary">{{ translate('submit_request') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="fl-chat-fab" id="fl-chat-fab">
        <img alt="" src="{{ getStorageImages(path: $imageFullUrl, type: 'backend-profile') }}">
        <span class="fl-chat-fab-text">{{ translate('message') }} {{ $displayName }}</span>
        @auth('customer')
            <span class="fl-chat-fab-badge" id="fl-chat-fab-badge"></span>
        @endauth
    </div>

    <div class="fl-chat-panel" id="fl-chat-panel" data-seller-id="{{ $seller->id }}">
        <div class="fl-chat-panel-header">
            <span class="fl-chat-avatar-wrap">
                <img alt="" src="{{ getStorageImages(path: $imageFullUrl, type: 'backend-profile') }}">
                <span class="fl-chat-status-dot {{ $isFreelancerOnline ? 'is-online' : '' }}"></span>
            </span>
            <span class="fl-chat-title-wrap">
                <span class="fl-chat-title">{{ translate('message') }} {{ $displayName }}</span>
                <span class="fl-chat-subtitle">
                    @if($isFreelancerOnline)
                        <span class="online-text">{{ translate('online') }}</span>
                    @endif
                    <span>{{ translate('last_login') }} {{ $sellerLastActive?->diffForHumans() ?? translate('not_available') }}</span>
                </span>
            </span>
            <button type="button" class="fl-chat-close" id="fl-chat-close">&times;</button>
        </div>
        <div class="fl-chat-panel-body">
            @auth('customer')
                <div class="fl-chat-thread" id="fl-chat-thread"></div>
                <form action="{{ route('messages') }}" method="post" id="fl-chat-form" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input value="{{ $seller->id }}" name="vendor_id" hidden>
                    <div class="fl-chat-compose">
                        <label class="fl-chat-attach-btn" for="fl-chat-file" title="{{ translate('file') }}">
                            <label class="py-0 cursor-pointer fl-file-picker" title="File">
                                <svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5.61597 17.2917C4.66813 17.2919 3.7415 17.011 2.95335 16.4845C2.16519 15.958 1.55092 15.2096 1.18827 14.3338C0.825613 13.4581 0.730874 12.4945 0.916037 11.5649C1.1012 10.6353 1.55794 9.78158 2.22847 9.11165L9.2993 2.03999C9.41655 1.92274 9.57557 1.85687 9.74139 1.85687C9.9072 1.85687 10.0662 1.92274 10.1835 2.03999C10.3007 2.15724 10.3666 2.31626 10.3666 2.48207C10.3666 2.64788 10.3007 2.80691 10.1835 2.92415L3.11181 9.99499C2.76945 10.3208 2.49576 10.7118 2.30686 11.145C2.11796 11.5782 2.01768 12.0449 2.01193 12.5175C2.00617 12.99 2.09506 13.459 2.27334 13.8967C2.45163 14.3344 2.71572 14.7319 3.05004 15.066C3.38436 15.4 3.78216 15.6638 4.21999 15.8417C4.65783 16.0196 5.12685 16.1081 5.59941 16.102C6.07198 16.0958 6.53854 15.9951 6.9716 15.8059C7.40465 15.6166 7.79545 15.3426 8.12097 15L17.2543 5.86665C17.6728 5.43446 17.9047 4.85506 17.8999 4.25344C17.895 3.65183 17.6539 3.07623 17.2285 2.65081C16.8031 2.22539 16.2275 1.98425 15.6258 1.97942C15.0242 1.97459 14.4448 2.20645 14.0126 2.62499L6.64764 9.99499C6.45226 10.1904 6.3425 10.4554 6.3425 10.7317C6.3425 11.008 6.45226 11.2729 6.64764 11.4683C6.84301 11.6637 7.108 11.7735 7.3843 11.7735C7.66061 11.7735 7.9256 11.6637 8.12097 11.4683L12.8335 6.75499C12.8911 6.69527 12.96 6.64762 13.0363 6.61483C13.1125 6.58204 13.1945 6.56476 13.2775 6.564C13.3605 6.56324 13.4428 6.57901 13.5196 6.6104C13.5964 6.64179 13.6663 6.68817 13.725 6.74682C13.7837 6.80548 13.8301 6.87524 13.8616 6.95203C13.893 7.02883 13.9089 7.11112 13.9082 7.19411C13.9075 7.27709 13.8903 7.35911 13.8576 7.43538C13.8249 7.51165 13.7773 7.58064 13.7176 7.63832L9.0043 12.3525C8.57454 12.7824 7.99162 13.0239 7.38377 13.024C6.77591 13.0241 6.19293 12.7827 5.76305 12.3529C5.33318 11.9231 5.09164 11.3402 5.09156 10.7324C5.09148 10.1245 5.33288 9.54153 5.76264 9.11165L13.1293 1.74999C13.7935 1.08573 14.6943 0.712511 15.6336 0.712433C16.5729 0.712355 17.4738 1.08542 18.1381 1.74957C18.8023 2.41372 19.1755 3.31454 19.1756 4.25386C19.1757 5.19318 18.8026 6.09406 18.1385 6.75832L9.00514 15.8883C8.56103 16.3347 8.03283 16.6885 7.45109 16.9294C6.86934 17.1703 6.24561 17.2934 5.61597 17.2917Z" fill="#46A046"></path>
                                </svg>
                            </label>
                        </label>
                        <input type="file" class="d-none" id="fl-chat-file" name="file[]" accept=".doc,.docx,.txt,.csv,.xls,.xlsx,.rar,.tar,.zip,.pdf">
                        <textarea name="message" id="fl-chat-message" rows="3"
                                  placeholder="{{ translate('Write_here') }}..."></textarea>
                    </div>
                    <div class="fl-chat-file-name d-none" id="fl-chat-file-name"></div>
                </form>
            @else
                <div class="fl-chat-login-hint">
                    {{ translate('please_login_as_a_customer_to_message') }} {{ $displayName }}.
                </div>
                <a href="{{ route('customer.auth.login') }}" class="btn btn--primary btn-sm w-100">{{ translate('login_to_continue') }}</a>
            @endauth
        </div>
        <div class="fl-chat-panel-footer">
            @auth('customer')
                <button type="submit" form="fl-chat-form" class="btn btn--primary btn-sm">{{ translate('send_message') }}</button>
            @endauth
        </div>
    </div>

    @push('script')
        <script>
            (function () {
                const fab = document.getElementById('fl-chat-fab');
                const panel = document.getElementById('fl-chat-panel');
                const closeBtn = document.getElementById('fl-chat-close');
                const chatForm = document.getElementById('fl-chat-form');
                const textarea = document.getElementById('fl-chat-message');
                const chatThread = document.getElementById('fl-chat-thread');
                const fabBadge = document.getElementById('fl-chat-fab-badge');
                const fileInput = document.getElementById('fl-chat-file');
                const fileName = document.getElementById('fl-chat-file-name');
                const maxChatFileSize = 2 * 1024 * 1024;
                // Read from the panel's own data attribute (always present) rather than
                // the form's hidden input, which only exists in the DOM once logged in.
                const chatSellerId = panel.dataset.sellerId;
                let chatPollTimer = null;
                let unreadPollTimer = null;

                // Loads/refreshes the actual conversation with this freelancer, so the
                // customer can see the freelancer's replies instead of this being a
                // one-way "send a message and hope" form.
                function loadChatThread() {
                    if (!chatThread) return;
                    $.get('{{ route('messages') }}', { vendor_id: chatSellerId }).done(function (response) {
                        if (response && typeof response.chattingMessages === 'string') {
                            chatThread.innerHTML = response.chattingMessages;
                            normalizeFloatingChatOrder();
                            chatThread.scrollTop = chatThread.scrollHeight;
                        }
                    });
                }

                function normalizeFloatingChatOrder() {
                    if (!chatThread) return;
                    Array.from(chatThread.children)
                        .sort(function (a, b) {
                            return parseInt(a.dataset.messageId || '0', 10) - parseInt(b.dataset.messageId || '0', 10);
                        })
                        .forEach(function (item) { chatThread.appendChild(item); });
                }

                function refreshUnreadBadge() {
                    if (!fabBadge || panel.classList.contains('is-open')) return;
                    $.get('{{ route('messages.unread-count') }}', { vendor_id: chatSellerId }).done(function (response) {
                        var count = response && response.count ? parseInt(response.count, 10) : 0;
                        if (count > 0) {
                            fabBadge.textContent = count > 9 ? '9+' : count;
                            fabBadge.classList.add('is-visible');
                        } else {
                            fabBadge.classList.remove('is-visible');
                        }
                    });
                }

                function clearUnreadBadge() {
                    if (fabBadge) fabBadge.classList.remove('is-visible');
                }

                function openChatPanel() {
                    panel.classList.add('is-open');
                    if (fab) fab.style.display = 'none';
                    clearUnreadBadge();
                    textarea?.focus();
                    loadChatThread();
                    if (chatThread && !chatPollTimer) {
                        chatPollTimer = setInterval(loadChatThread, 8000);
                    }
                }

                function closeChatPanel() {
                    panel.classList.remove('is-open');
                    if (fab) fab.style.display = 'flex';
                    if (chatPollTimer) {
                        clearInterval(chatPollTimer);
                        chatPollTimer = null;
                    }
                }

                fab?.addEventListener('click', openChatPanel);
                document.getElementById('gig-contact-btn')?.addEventListener('click', openChatPanel);
                closeBtn?.addEventListener('click', closeChatPanel);

                // The bottom-of-page "send a message" card is a lightweight compose
                // box that hands off to the existing floating chat panel/thread
                // instead of duplicating the send + polling logic.
                const inlineMessage = document.getElementById('inline-chat-message');
                const inlineSendBtn = document.getElementById('inline-chat-send-btn');
                inlineSendBtn?.addEventListener('click', function () {
                    const value = inlineMessage.value.trim();
                    if (!value) {
                        inlineMessage.focus();
                        return;
                    }
                    textarea.value = value;
                    openChatPanel();
                    $(chatForm).trigger('submit');
                    inlineMessage.value = '';
                });

                fileInput?.addEventListener('change', function () {
                    var file = this.files && this.files[0];
                    if (!file) {
                        fileName?.classList.add('d-none');
                        return;
                    }
                    if (file.size > maxChatFileSize) {
                        toastr.error('{{ translate('file_maximum_size_') }}2 MB');
                        this.value = '';
                        fileName?.classList.add('d-none');
                        return;
                    }
                    if (fileName) {
                        fileName.textContent = file.name;
                        fileName.classList.remove('d-none');
                    }
                });

                if (fabBadge) {
                    refreshUnreadBadge();
                    unreadPollTimer = setInterval(refreshUnreadBadge, 20000);
                }

                // This used to be a plain form post, so the browser navigated to
                // /message and rendered its raw JSON response instead of just
                // sending the message and staying on the page.
                if (chatForm) {
                    $(chatForm).on('submit', function (e) {
                        e.preventDefault();
                        var submitBtn = document.querySelector('button[form="fl-chat-form"]');
                        var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                        if (!textarea.value.trim() && !hasFile) {
                            textarea.focus();
                            return;
                        }
                        if (hasFile && fileInput.files[0].size > maxChatFileSize) {
                            toastr.error('{{ translate('file_maximum_size_') }}2 MB');
                            return;
                        }
                        submitBtn.disabled = true;
                        $.ajax({
                            url: chatForm.action,
                            method: 'POST',
                            data: new FormData(chatForm),
                            processData: false,
                            contentType: false,
                        }).done(function (response) {
                            textarea.value = '';
                            if (fileInput) fileInput.value = '';
                            if (fileName) { fileName.textContent = ''; fileName.classList.add('d-none'); }
                            if (chatThread && response && typeof response.chattingMessages === 'string') {
                                chatThread.innerHTML = response.chattingMessages;
                                normalizeFloatingChatOrder();
                                chatThread.scrollTop = chatThread.scrollHeight;
                            }
                        }).fail(function (xhr) {
                            var message = xhr.responseJSON?.errors?.[0]?.message
                                || '{{ translate('something_went_wrong') }}';
                            toastr.error(message);
                        }).always(function () {
                            submitBtn.disabled = false;
                        });
                    });
                }

                const quoteModal = document.getElementById('getQuoteModal');
                const quoteForm = document.getElementById('get-quote-form');
                const deliveryOptions = document.querySelectorAll('.quote-delivery-option');
                const deliveryInput = document.getElementById('quote-delivery-preference');
                const customDeliveryInput = document.getElementById('quote-custom-delivery');

                document.querySelectorAll('.gig-quote-trigger').forEach(function (button) {
                    button.addEventListener('click', function () {
                        $(quoteModal).modal('show');
                    });
                });

                deliveryOptions.forEach(function (option) {
                    option.addEventListener('click', function () {
                        deliveryOptions.forEach(function (o) { o.classList.remove('is-selected'); });
                        option.classList.add('is-selected');
                        deliveryInput.value = option.dataset.value;
                        customDeliveryInput.classList.toggle('d-none', option.dataset.value !== 'custom');
                    });
                });

                $(quoteForm).on('submit', function (e) {
                    e.preventDefault();

                    if (typeof FormValidators !== 'undefined' && !FormValidators.autoValidateForm(quoteForm)) {
                        return;
                    }

                    var submitBtn = quoteForm.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    $.ajax({
                        url: quoteForm.action,
                        method: 'POST',
                        data: new FormData(quoteForm),
                        processData: false,
                        contentType: false,
                    }).done(function () {
                        toastr.success('{{ translate('your_quote_request_has_been_sent') }}');
                        $(quoteModal).modal('hide');
                        quoteForm.reset();
                        deliveryOptions.forEach(function (o) { o.classList.remove('is-selected'); });
                        customDeliveryInput.classList.add('d-none');
                    }).fail(function (xhr) {
                        var message = xhr.responseJSON?.errors
                            ? Object.values(xhr.responseJSON.errors)[0][0]
                            : '{{ translate('something_went_wrong') }}';
                        toastr.error(message);
                    }).always(function () {
                        submitBtn.disabled = false;
                    });
                });

                const tabs = document.querySelectorAll('[data-tab-index]');
                tabs.forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        tabs.forEach(function (t) { t.classList.remove('is-active'); });
                        tab.classList.add('is-active');
                        document.querySelectorAll('[data-tab-panel]').forEach(function (panel) {
                            panel.style.display = panel.dataset.tabPanel === tab.dataset.tabIndex ? '' : 'none';
                        });
                    });
                });

                const images = @json($service->images->map(fn($i) => getStorageImages(path: $i->image_full_url, type: 'backend-profile')));
                let currentIndex = 0;
                const mainImg = document.getElementById('gig-gallery-main');
                const thumbs = document.querySelectorAll('#gig-thumb-strip img');

                function showImage(index) {
                    if (!images.length) return;
                    currentIndex = (index + images.length) % images.length;
                    mainImg.src = images[currentIndex];
                    thumbs.forEach(function (thumb) {
                        thumb.classList.toggle('is-active', Number(thumb.dataset.index) === currentIndex);
                    });
                }

                thumbs.forEach(function (thumb) {
                    thumb.addEventListener('click', function () { showImage(Number(thumb.dataset.index)); });
                });
                document.getElementById('gig-gallery-prev')?.addEventListener('click', function () { showImage(currentIndex - 1); });
                document.getElementById('gig-gallery-next')?.addEventListener('click', function () { showImage(currentIndex + 1); });
            })();
        </script>
    @endpush
@endsection




















