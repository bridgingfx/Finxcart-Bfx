@extends('layouts.front-end.app')

@section('title', $displayName)

@push('css_or_js')
    <style>
        .freelancer-profile-page { background: #f6f8fb; padding: 0 0 54px; }
        .freelancer-profile-banner {
            height: 130px; margin-bottom: -70px;
            background: linear-gradient(120deg, #17395e 0%, #1e4a7a 45%, #f97316 150%);
        }
        .freelancer-profile-header {
            background: #fff; border: 1px solid #e7ebf0; border-radius: 16px;
            box-shadow: 0 12px 32px rgba(23, 57, 94, 0.08);
            padding: 26px; margin-bottom: 22px; display: flex; justify-content: space-between; gap: 20px; flex-wrap: wrap;
            position: relative;
        }
        .freelancer-profile-header .avatar {
            width: 96px; height: 96px; border-radius: 50%; object-fit: cover; background: #eef2f7; flex-shrink: 0;
            border: 4px solid #fff; box-shadow: 0 4px 16px rgba(23, 57, 94, 0.15);
        }
        .freelancer-profile-name { font-weight: 800; font-size: 26px; color: #17395e; margin-bottom: 2px; }
        .freelancer-handle { font-size: 14px; font-weight: 500; color: #9ca3af; margin-left: 4px; }
        .freelancer-rating-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 4px; }
        .freelancer-rating-stars { color: #f5a623; font-size: 14px; letter-spacing: 1px; }
        .freelancer-rating-count { color: #6b7280; font-size: 13px; }
        .freelancer-level-badge {
            display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 700;
            background: #eef2ff; color: #3730a3; border-radius: 999px; padding: 3px 10px;
        }
        .freelancer-stat-row { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
        .freelancer-stat-chip {
            display: flex; align-items: center; gap: 8px; background: #f6f8fb; border: 1px solid #eef1f5;
            border-radius: 10px; padding: 8px 14px;
        }
        .freelancer-stat-chip i { font-size: 16px; color: #f97316; }
        .freelancer-stat-chip .stat-value { font-weight: 800; font-size: 14px; color: #1f2937; line-height: 1.2; }
        .freelancer-stat-chip .stat-label { font-size: 11px; color: #9ca3af; line-height: 1.2; }
        .freelancer-skills { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 14px; }
        .freelancer-skill-tag {
            font-size: 12px; font-weight: 600; color: #374151; background: #f3f4f6;
            border-radius: 999px; padding: 4px 12px; transition: background .15s ease, color .15s ease;
        }
        .freelancer-skill-tag:hover { background: #eef2ff; color: #3730a3; }
        .freelancer-layout { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 22px; align-items: start; }
        .freelancer-sidebar-card {
            background: #fff; border: 1px solid #e7ebf0; border-radius: 14px; padding: 22px;
            position: sticky; top: 20px; text-align: center;
            box-shadow: 0 12px 28px rgba(23, 57, 94, 0.06);
        }
        .freelancer-sidebar-card .avatar {
            width: 68px; height: 68px; border-radius: 50%; object-fit: cover; margin: 0 auto 10px;
            border: 3px solid #fff; box-shadow: 0 0 0 1px #e7ebf0;
        }
        .freelancer-sidebar-card .name { font-weight: 700; font-size: 15px; color: #1f2937; }
        .freelancer-sidebar-card .queue-note { font-size: 12px; color: #6b7280; margin: 6px 0 16px; }
        .freelancer-sidebar-card .btn--primary { border-radius: 8px; font-weight: 700; padding: 10px; }
        .freelancer-section {
            background: #fff; border: 1px solid #e7ebf0; border-radius: 14px; padding: 26px; margin-bottom: 22px;
            transition: box-shadow .2s ease;
        }
        .freelancer-section:hover { box-shadow: 0 12px 28px rgba(23, 57, 94, 0.05); }
        .freelancer-section-title {
            font-weight: 800; font-size: 20px; color: #17395e; margin-bottom: 18px;
            display: flex; align-items: center; gap: 10px;
        }
        .freelancer-section-title::before {
            content: ''; width: 6px; height: 20px; border-radius: 4px;
            background: linear-gradient(180deg, #f97316, #fb923c); display: inline-block;
        }
        .service-category-heading {
            font-weight: 800; color: #17395e; font-size: 15px; margin: 22px 0 12px;
            display: flex; align-items: center; gap: 8px;
        }
        .service-category-heading::before {
            content: ''; width: 5px; height: 15px; border-radius: 4px;
            background: linear-gradient(180deg, #f97316, #fb923c); display: inline-block;
        }
        .service-category-heading:first-child { margin-top: 0; }
        .service-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .service-card {
            border: 1px solid #e8edf3; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column;
            background: #fff; box-shadow: 0 4px 14px rgba(23, 57, 94, 0.05); position: relative;
            transition: box-shadow .22s ease, transform .22s ease, border-color .22s ease;
        }
        .service-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #17395e, #f97316); z-index: 1; opacity: 0; transition: opacity .22s ease;
        }
        .service-card:hover::before { opacity: 1; }
        .service-card:hover {
            box-shadow: 0 18px 34px rgba(23, 57, 94, 0.13); transform: translateY(-5px); border-color: #d8dee7;
        }
        .service-card .service-cover {
            width: 100%; height: 160px; object-fit: cover; background: #eef2f7; display: block; transition: transform .4s ease;
        }
        .service-card .service-cover-wrap { overflow: hidden; }
        .service-card:hover .service-cover { transform: scale(1.06); }
        .service-card .service-card-body { padding: 18px; display: flex; flex-direction: column; gap: 7px; flex-grow: 1; }
        .service-title {
            font-weight: 700; color: #1f2937; font-size: 15.5px; line-height: 1.4;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .service-specialization {
            display: inline-flex; align-self: flex-start; font-size: 11px; font-weight: 700; color: #3730a3;
            background: #eef2ff; border-radius: 999px; padding: 3px 10px;
        }
        .service-description { font-size: 13px; color: #4b5563; margin-top: 2px; line-height: 1.55; }
        .service-simple-row {
            display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;
            margin-top: auto; padding-top: 12px; border-top: 1px solid #f1f3f6;
        }
        .service-price-label { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .03em; }
        .service-price { font-weight: 800; color: #16794f; white-space: nowrap; font-size: 18px; }
        .service-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
        .service-actions .btn { border-radius: 999px; font-weight: 700; }
        .service-simple-row .btn--primary { border-radius: 999px; font-weight: 700; box-shadow: 0 8px 16px rgba(23, 57, 94, 0.14); }
        .service-details-toggle { font-size: 13px; font-weight: 700; color: #1a2f5e; cursor: pointer; text-decoration: underline; text-underline-offset: 2px; background: none; border: none; padding: 0; }
        .tier-grid { display: none; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 14px; }
        .tier-grid.is-open { display: grid; }
        .tier-card {
            border: 1px solid #e8edf3; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; gap: 8px;
            background: #fafbfc; transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
        }
        .tier-card:hover { box-shadow: 0 10px 24px rgba(23, 57, 94, 0.08); transform: translateY(-2px); border-color: #d8dee7; background: #fff; }
        .tier-card .tier-name { font-weight: 800; text-transform: uppercase; font-size: 12px; letter-spacing: .04em; color: #17395e; }
        .tier-card .tier-price { font-weight: 800; font-size: 20px; color: #16794f; }
        .tier-card .tier-meta { font-size: 12px; color: #6b7280; }
        .tier-card .tier-desc { font-size: 13px; color: #4b5563; flex-grow: 1; white-space: pre-line; }
        .tier-card .btn--primary { border-radius: 999px; font-weight: 700; }
        .tier-feature-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 6px; }
        .tier-feature-list li { font-size: 12px; display: flex; align-items: center; gap: 6px; }
        .tier-feature-list li.is-included { color: #1f2937; }
        .tier-feature-list li.is-excluded { color: #9ca3af; }
        .tier-feature-list li.is-excluded i { color: #d1d5db; }
        .portfolio-list { display: flex; flex-direction: column; gap: 20px; }
        .portfolio-card {
            border: 1px solid #e8edf3; border-radius: 16px; overflow: hidden; display: flex; flex-wrap: wrap;
            background: #fff; box-shadow: 0 4px 14px rgba(23, 57, 94, 0.05);
            transition: box-shadow .22s ease, transform .22s ease, border-color .22s ease;
        }
        .portfolio-card:hover {
            box-shadow: 0 20px 40px rgba(23, 57, 94, 0.14); transform: translateY(-5px); border-color: #d8dee7;
        }
        .portfolio-card .portfolio-cover-wrap { position: relative; overflow: hidden; flex-shrink: 0; width: 320px; max-width: 100%; }
        .portfolio-card img.cover {
            width: 100%; height: 240px; object-fit: cover; background: #eef2f7; display: block;
            transition: transform .4s ease;
        }
        .portfolio-card:hover img.cover { transform: scale(1.06); }
        .portfolio-card-date-badge {
            position: absolute; top: 12px; left: 12px; display: inline-flex; align-items: center; gap: 5px;
            font-size: 11px; font-weight: 800; letter-spacing: .02em; text-transform: uppercase;
            padding: 4px 10px; border-radius: 999px; color: #fff; background: rgba(23, 57, 94, 0.85);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12); backdrop-filter: blur(2px);
        }
        .portfolio-card .portfolio-card-body { padding: 24px; flex: 1 1 260px; min-width: 240px; display: flex; flex-direction: column; }
        .portfolio-card .portfolio-card-title { font-weight: 800; font-size: 19px; color: #1f2937; margin-bottom: 8px; line-height: 1.35; }
        .portfolio-card .portfolio-card-desc { font-size: 13px; color: #4b5563; line-height: 1.65; margin-bottom: 12px; }
        .portfolio-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
        .portfolio-tag {
            font-size: 11px; font-weight: 700; color: #17395e; background: #eef2ff; border-radius: 999px; padding: 4px 12px;
            transition: background .15s ease, color .15s ease;
        }
        .portfolio-tag:hover { background: #17395e; color: #fff; }
        .portfolio-project-link {
            display: inline-flex; align-self: flex-start; align-items: center; gap: 6px; font-size: 12px; font-weight: 700;
            color: #f97316; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 999px; padding: 5px 14px;
            text-decoration: none; margin-bottom: 12px; transition: background .15s ease, color .15s ease;
        }
        .portfolio-project-link:hover { background: #f97316; color: #fff; text-decoration: none; }
        .portfolio-card .gallery-strip { display: flex; gap: 8px; padding: 16px 22px; border-top: 1px solid #f1f3f6; width: 100%; flex-basis: 100%; }
        .portfolio-card .gallery-strip img {
            width: 52px; height: 52px; object-fit: cover; border-radius: 8px; transition: transform .18s ease, box-shadow .18s ease;
        }
        .portfolio-card .gallery-strip img:hover { transform: translateY(-3px); box-shadow: 0 8px 16px rgba(23, 57, 94, 0.16); }
        .portfolio-card-actions { margin-top: auto; padding-top: 10px; display: flex; gap: 8px; flex-wrap: wrap; }
        .portfolio-card-actions .btn { border-radius: 999px; font-weight: 700; }
        .portfolio-card-rating {
            display: inline-flex; align-self: flex-start; align-items: center; gap: 5px; font-size: 12px; font-weight: 700;
            color: #b76e00; background: #fff4e5; border-radius: 999px; padding: 4px 12px; margin-bottom: 10px;
        }
        .portfolio-card-rating .review-stars { color: #f5a623; }
        .reviews-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px; }
        .reviews-count { font-size: 13px; color: #6b7280; }
        .reviews-sort select { border: 1px solid #e5e7eb; border-radius: 8px; padding: 6px 10px; font-size: 13px; }
        .review-card {
            border: 1px solid #e8edf3; border-radius: 12px; padding: 18px; margin-bottom: 14px;
            transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
        }
        .review-card:hover {
            box-shadow: 0 12px 26px rgba(23, 57, 94, 0.08); transform: translateY(-2px); border-color: #d8dee7;
        }
        .review-card:last-child { margin-bottom: 0; }
        .review-card .review-head { display: flex; align-items: center; gap: 10px; }
        .review-card .review-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, #17395e, #2d6cdf); color: #fff;
            display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0;
        }
        .review-card .review-author { font-weight: 700; color: #1f2937; font-size: 14px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .review-card .review-meta { font-size: 12px; color: #9ca3af; }
        .review-card .review-stars { display: flex; align-items: center; gap: 8px; font-size: 13px; margin-top: 4px; }
        .review-card .review-stars-row { display: inline-flex; gap: 1px; font-size: 11px; }
        .review-card .review-stars-row i.is-filled { color: #f5a623; }
        .review-card .review-stars-row i.is-empty { color: #e2e6ec; }
        .review-card .review-body { font-size: 13px; color: #4b5563; margin-top: 10px; line-height: 1.55; }
        .review-verified-badge {
            display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700;
            background: #e9f9f0; color: #219653; border-radius: 999px; padding: 2px 8px;
        }
        .review-verified-badge i { font-size: 10px; }
        .review-card .review-facts { display: flex; gap: 16px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #f1f3f6; flex-wrap: wrap; }
        .review-card .review-fact-label { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .03em; }
        .review-card .review-fact-value { font-size: 13px; font-weight: 700; color: #1f2937; }
        .review-card .review-service-tag { display: flex; align-items: center; gap: 8px; margin-left: auto; }
        .review-card .review-service-tag img { width: 32px; height: 32px; border-radius: 6px; object-fit: cover; background: #eef2f7; }
        .review-card .review-service-tag span { font-size: 12px; color: #374151; }
        @media (max-width: 991.98px) {
            .freelancer-layout { grid-template-columns: 1fr; }
            .freelancer-sidebar-card { position: static; }
        }
        @media (max-width: 767.98px) {
            .service-grid { grid-template-columns: 1fr; }
            .tier-grid { grid-template-columns: 1fr; }
            .freelancer-profile-header { flex-direction: column; }
            .freelancer-profile-header .btn--primary { width: 100%; }
            .freelancer-profile-name { font-size: 21px; }
            .review-card .review-facts { gap: 12px; }
            .review-card .review-service-tag { margin-left: 0; flex-basis: 100%; }
        }

        @media (max-width: 479.98px) {
            .freelancer-profile-banner { height: 90px; margin-bottom: -50px; }
            .freelancer-profile-header .avatar { width: 72px; height: 72px; }
            .freelancer-stat-chip { flex: 1 1 calc(50% - 10px); }
            .portfolio-card img.cover { height: 180px; }
        }

        /* Floating chat widget */
        .fl-chat-fab {
            position: fixed; left: 20px; bottom: 20px; z-index: 1050;
            display: flex; align-items: center; gap: 10px;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 999px;
            padding: 8px 16px 8px 8px; box-shadow: 0 10px 30px rgba(15,23,42,.15);
            cursor: pointer; max-width: 260px;
        }
        .fl-chat-fab img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }
        .fl-chat-fab .fl-chat-fab-text { font-size: 13px; font-weight: 700; color: #1f2937; }
        .fl-chat-fab { position: fixed; }
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
        .fl-chat-panel-header {
            display: flex; align-items: center; gap: 10px; padding: 14px 16px;
            border-bottom: 1px solid #eef1f5;
        }
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
        .fl-chat-panel-body .fl-chat-hint { font-size: 13px; color: #6b7280; margin-bottom: 12px; }
        .fl-chat-suggestions { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }
        .fl-chat-suggestion {
            text-align: left; border: 1px solid #e5e7eb; border-radius: 999px; background: #fff;
            padding: 8px 14px; font-size: 12px; color: #374151; cursor: pointer;
        }
        .fl-chat-suggestion:hover { border-color: #1a2f5e; color: #1a2f5e; }
        .fl-chat-panel-footer { border-top: 1px solid #eef1f5; padding: 10px 14px; display: flex; justify-content: flex-end; }
        .fl-chat-panel textarea { width: 100%; border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px; font-size: 13px; resize: none; }
        .fl-chat-compose { display: flex; align-items: center; gap: 8px; }
        .fl-chat-attach-btn { width: 38px; height: 38px; border: 1px solid #dbe3ee; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: #17395e; background: #f8fafc; flex: 0 0 auto; }
        .fl-chat-attach-btn:hover { background: #eef6ff; border-color: #93c5fd; }
        .fl-chat-file-name { font-size: 11px; color: #64748b; margin-top: 6px; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .fl-chat-time { display: block; font-size: 10px; color: #94a3b8; margin-top: 4px; }
        .fl-chat-thread {
            max-height: 240px; overflow-y: auto; margin-bottom: 12px; padding-right: 4px;
            display: flex; flex-direction: column;
        }
        .fl-chat-thread .incoming_msg, .fl-chat-thread .outgoing_msg { margin-bottom: 10px; }
        .fl-chat-thread img { width: 26px; height: 26px; border-radius: 50%; object-fit: cover; }

        /* Get a quote modal */
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
        .review-sort-tabs {
            display: inline-flex;
            gap: 6px;
            padding: 4px;
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            background: #f8fafc;
        }
        .review-sort-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 7px 14px;
            border-radius: 999px;
            color: #64748b;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }
        .review-sort-tab:hover,
        .review-sort-tab.is-active {
            background: #17395e;
            color: #fff;
            text-decoration: none;
        }
        @media (min-width: 768px) {
            .portfolio-list {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .portfolio-card { display: flex; flex-direction: column; flex-wrap: nowrap; }
            .portfolio-card .portfolio-cover-wrap { width: 100%; }
            .portfolio-card img.cover { height: 220px; }
            .portfolio-card .gallery-strip { margin-top: auto; }
        }
        .freelancer-section { border-radius: 18px; box-shadow: 0 14px 34px rgba(15, 23, 42, .06); }
        .service-grid { gap: 22px; }
        .service-card { border-radius: 14px; border: 0; box-shadow: 0 16px 34px rgba(15, 23, 42, .08); }
        .service-card::before { height: 5px; opacity: 1; background: linear-gradient(90deg, #17395e, #f97316, #14b8a6); }
        .service-card .service-cover { height: 190px; }
        .service-card .service-card-body { padding: 20px; gap: 10px; }
        .service-specialization { color: #0f766e; background: #e6fffa; }
        .service-description { color: #64748b; }
        .service-actions .btn { min-height: 36px; padding-inline: 14px; }
        .portfolio-card { border: 0; border-radius: 18px; box-shadow: 0 16px 34px rgba(15, 23, 42, .08); }
        .portfolio-card .portfolio-cover-wrap::after {
            content: '';
            position: absolute;
            inset: auto 0 0;
            height: 46%;
            background: linear-gradient(180deg, transparent, rgba(15, 23, 42, .42));
            pointer-events: none;
        }
        #getQuoteModal .modal-dialog { max-width: 660px; }
        #getQuoteModal .modal-content { border: 0; border-radius: 18px; overflow: hidden; box-shadow: 0 28px 80px rgba(15, 23, 42, .28); }
        #getQuoteModal .modal-header { background: linear-gradient(120deg, #17395e, #0f766e); color: #fff; border: 0; padding: 22px 24px; }
        #getQuoteModal .modal-title { color: #fff; font-weight: 800; }
        #getQuoteModal .close { color: #fff; opacity: .9; text-shadow: none; }
        #getQuoteModal .modal-body { padding: 24px; background: #f8fafc; }
        #getQuoteModal form .form-control { border-radius: 12px; border-color: #dbe3ee; min-height: 44px; }
        #getQuoteModal textarea.form-control { min-height: 118px; }
        .quote-delivery-option { border-radius: 999px; font-weight: 800; min-height: 40px; display: inline-flex; align-items: center; }
        .quote-delivery-option.is-selected { border-color: #f97316; background: #fff7ed; color: #17395e; box-shadow: 0 8px 18px rgba(249, 115, 22, .14); }


        .quote-delivery-options { display: flex; gap: 8px; flex-wrap: wrap; }
        .quote-delivery-option {
            border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; padding: 8px 14px;
            font-size: 13px; cursor: pointer;
        }
        .quote-delivery-option.is-selected { border-color: #1a2f5e; background: #eef2ff; color: #1a2f5e; font-weight: 700; }

        /* Extra freelancer profile redesign pass */
        .freelancer-profile-page, .freelancer-profile-page * { box-sizing: border-box; }
        .freelancer-profile-page { overflow-x: hidden; background: linear-gradient(180deg, #f4f8ff 0%, #fff7ed 42%, #f8fafc 100%); }
        .freelancer-profile-page .container { max-width: 1180px; width: 100%; }
        .freelancer-profile-header { border: 1px solid rgba(148,163,184,.22); box-shadow: 0 22px 54px rgba(15,23,42,.12); background: rgba(255,255,255,.94); }
        .freelancer-profile-avatar-wrap { position: relative; flex: 0 0 auto; }
        .freelancer-profile-avatar-wrap .avatar { box-shadow: 0 0 0 5px #fff, 0 14px 30px rgba(15,23,42,.16); }
        .freelancer-online-dot { position: absolute; right: 3px; bottom: 3px; width: 14px; height: 14px; border-radius: 50%; background: #22c55e; border: 2px solid #fff; box-shadow: 0 0 0 4px rgba(34,197,94,.18); }
        .freelancer-online-dot.large { width: 18px; height: 18px; right: 4px; bottom: 4px; }
        .freelancer-online-pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 11px; border-radius: 999px; background: #f1f5f9; color: #64748b; font-size: 11px; font-weight: 900; }
        .freelancer-online-pill span { width: 7px; height: 7px; border-radius: 50%; background: #cbd5e1; }
        .freelancer-online-pill.is-online { background: #ecfdf5; color: #15803d; }
        .freelancer-online-pill.is-online span { background: #22c55e; }
        .freelancer-skills { gap: 8px; }
        .freelancer-skill-tag:nth-child(4n+1) { background: #eff6ff; color: #1d4ed8; }
        .freelancer-skill-tag:nth-child(4n+2) { background: #fff7ed; color: #c2410c; }
        .freelancer-skill-tag:nth-child(4n+3) { background: #ecfdf5; color: #047857; }
        .freelancer-skill-tag:nth-child(4n+4) { background: #fdf2f8; color: #be185d; }
        .service-grid, .portfolio-list { width: 100%; max-width: 100%; }
        .service-card { overflow: hidden; border-radius: 18px; border: 1px solid rgba(148,163,184,.22); box-shadow: 0 16px 34px rgba(15,23,42,.09); }
        .service-card:hover { transform: translateY(-5px); border-color: rgba(249,115,22,.55); box-shadow: 0 24px 48px rgba(15,23,42,.14); }
        .service-specialization { display: inline-flex; width: fit-content; max-width: 100%; padding: 5px 10px; border-radius: 999px; background: #fff7ed; color: #c2410c; font-weight: 900; }
        .tier-card:nth-child(3n+1) { background: linear-gradient(180deg, #eff6ff, #fff); }
        .tier-card:nth-child(3n+2) { background: linear-gradient(180deg, #fff7ed, #fff); }
        .tier-card:nth-child(3n+3) { background: linear-gradient(180deg, #ecfdf5, #fff); }
        .portfolio-card, .gig-review-card { border-radius: 18px; box-shadow: 0 14px 32px rgba(15,23,42,.08); }
        @media (max-width: 767.98px) {
            .freelancer-profile-header { padding: 18px; }
            .freelancer-profile-header > .d-flex { flex-direction: column; }
            .freelancer-stat-row, .service-actions { align-items: stretch; flex-direction: column; }
            .service-actions .btn { width: 100%; justify-content: center; }
        }
    </style>
@endpush

@section('content')
    @php
        $sellerLastActive = $seller?->updated_at;
        $isFreelancerOnline = $seller && ($seller->status === 'approved') && (($seller->account_status ?? 'active') === 'active') && $sellerLastActive?->greaterThan(now()->subMinutes(5));
    @endphp
    <div class="freelancer-profile-page">
        <div class="freelancer-profile-banner"></div>
        <div class="container">
            <div class="freelancer-profile-header">
                <div class="d-flex align-items-start gap-3">
                    <span class="freelancer-profile-avatar-wrap" title="{{ $displayName }}">
                        <img class="avatar" alt="" src="{{ getStorageImages(path: $imageFullUrl, type: 'backend-profile') }}">
                        @if($isFreelancerOnline)
                            <span class="freelancer-online-dot large" title="Online now"></span>
                        @endif
                    </span>
                    <div>
                        <div class="freelancer-profile-name">
                            {{ $displayName }}
                            @if($seller->shop?->slug)
                                <span class="freelancer-handle">{{ '@' . $seller->shop->slug }}</span>
                            @endif
                        </div>
                        <div class="freelancer-rating-row">
                            @if($ratingCount > 0)
                                <span class="freelancer-rating-stars">{{ str_repeat('ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹ÃƒÆ’Ã¢â‚¬Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¦', round($ratingAvg)) }}{{ str_repeat('ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹ÃƒÆ’Ã¢â‚¬Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â ', 5 - round($ratingAvg)) }}</span>
                                <span class="fw-bold">{{ $ratingAvg }}</span>
                                <span class="freelancer-rating-count">({{ $ratingCount }} {{ translate('reviews') }})</span>
                            @else
                                <span class="freelancer-rating-count">{{ translate('no_reviews_yet') }}</span>
                            @endif
                            <span class="freelancer-level-badge">{{ $freelancerLevel }}</span>
                            <span class="freelancer-online-pill {{ $isFreelancerOnline ? 'is-online' : '' }}"><span></span>{{ $isFreelancerOnline ? 'Online now' : translate('available') }}</span>
                        </div>
                        @if($skills->count())
                            <div class="freelancer-skills">
                                @foreach($skills as $skill)
                                    <span class="freelancer-skill-tag">{{ $skill }}</span>
                                @endforeach
                            </div>
                        @endif
                        <div class="freelancer-stat-row">
                            <div class="freelancer-stat-chip">
                                <i class="tio-checkmark-circle-outlined"></i>
                                <div>
                                    <div class="stat-value">{{ $jobsCompleted }}</div>
                                    <div class="stat-label">{{ translate('jobs_completed') }}</div>
                                </div>
                            </div>
                            @if($activeOrdersCount > 0)
                                <div class="freelancer-stat-chip">
                                    <i class="tio-time"></i>
                                    <div>
                                        <div class="stat-value">{{ $activeOrdersCount }}</div>
                                        <div class="stat-label">{{ translate('orders_in_queue') }}</div>
                                    </div>
                                </div>
                            @endif
                            <div class="freelancer-stat-chip">
                                <i class="tio-calendar"></i>
                                <div>
                                    <div class="stat-value">{{ $seller->created_at?->format('M Y') }}</div>
                                    <div class="stat-label">{{ translate('member_since') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn--primary align-self-start" id="fl-chat-fab-btn">
                    {{ translate('contact_me') }}
                </button>
            </div>

            <div class="freelancer-layout">
                <div>
                    <div class="freelancer-section">
                        <h2 class="freelancer-section-title">{{ translate('services') }}</h2>
                        @forelse($services as $categoryName => $categoryServices)
                            <h3 class="service-category-heading">{{ $categoryName }}</h3>
                            <div class="service-grid mb-3">
                                @foreach($categoryServices as $service)
                                    @php
                                        $cover = $service->images->first();
                                    @endphp
                                    <div class="service-card">
                                        @if($cover)
                                            <div class="service-cover-wrap">
                                                <img class="service-cover" alt="{{ $service->title }}" src="{{ getStorageImages(path: $cover->image_full_url, type: 'backend-profile') }}">
                                            </div>
                                        @endif
                                        <div class="service-card-body">
                                            <div class="service-title">{{ $service->title }}</div>
                                            <div class="service-specialization">{{ $service->specialization?->defaultname }}</div>
                                            @if($service->description)
                                                <div class="service-description">{{ \Illuminate\Support\Str::limit($service->description, 110) }}</div>
                                            @endif

                                            @if($service->enabledPackages->count())
                                                <div class="service-simple-row">
                                                    <div>
                                                        <div class="service-price-label">{{ translate('from') }}</div>
                                                        <div class="service-price">{{ webCurrencyConverter($service->enabledPackages->min('price') ?? 0) }}</div>
                                                    </div>
                                                    <button type="button" class="service-details-toggle" data-toggle-tiers>{{ translate('more_details') }}</button>
                                                </div>
                                                <div class="tier-grid" data-tier-grid>
                                                    @foreach($service->enabledPackages as $package)
                                                        <div class="tier-card">
                                                            <div class="tier-name">{{ $package->title ?: translate($package->tier) }}</div>
                                                            @if($package->price !== null)
                                                                <div class="tier-price">{{ webCurrencyConverter($package->price) }}</div>
                                                            @endif
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
                                                            <div class="tier-meta">
                                                                @if($package->delivery_time_days)
                                                                    {{ $package->delivery_time_days }} {{ translate('days_delivery') }}
                                                                @endif
                                                                @if($package->revisions !== null)
                                                                    &middot; {{ $package->revisions }} {{ translate('revisions') }}
                                                                @endif
                                                            </div>
                                                            <a href="{{ route('hire.create', $service->id) }}?package={{ $package->tier }}" class="btn btn--primary btn-sm mt-2">
                                                                {{ translate('hire') }}
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="service-simple-row">
                                                    <div>
                                                        @if($service->price !== null)
                                                            <div class="service-price">{{ webCurrencyConverter($service->price) }}</div>
                                                        @endif
                                                        @if($service->delivery_time_days)
                                                            <div class="service-specialization">{{ $service->delivery_time_days }} {{ translate('days') }}</div>
                                                        @endif
                                                    </div>
                                                    <a href="{{ route('hire.create', $service->id) }}" class="btn btn--primary btn-sm">
                                                        {{ translate('hire') }}
                                                    </a>
                                                </div>
                                            @endif

                                            <div class="service-actions">
                                                <button type="button" class="btn btn-outline-primary btn-sm get-quote-btn"
                                                        data-service-id="{{ $service->id }}">
                                                    {{ translate('get_a_quote') }}
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm fl-chat-trigger">
                                                    {{ translate('message') }}
                                                </button>
                                                <a href="{{ route('hire-freelancer.service-show', $service->id) }}#reviews"
                                                   class="btn btn-outline-secondary btn-sm">
                                                    {{ translate('reviews') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ translate('no_services_added_yet') }}</p>
                        @endforelse
                    </div>

                    @if($portfolioItems->count())
                        <div class="freelancer-section">
                            <h2 class="freelancer-section-title">{{ translate('portfolio') }}</h2>
                            <div class="portfolio-list">
                                @foreach($portfolioItems as $item)
                                    @php
                                        $itemReviews = $portfolioReviews->get($item->id, collect());
                                    @endphp
                                    <div class="portfolio-card">
                                        <div class="portfolio-cover-wrap">
                                            <img class="cover" alt="" src="{{ getStorageImages(path: $item->image_full_url, type: 'backend-profile') }}">
                                            @if($item->completed_at)
                                                <span class="portfolio-card-date-badge"><i class="tio-calendar"></i>{{ $item->completed_at->format('F Y') }}</span>
                                            @endif
                                        </div>
                                        <div class="portfolio-card-body">
                                            <div class="portfolio-card-title">{{ $item->title }}</div>
                                            @if($item->description)
                                                <div class="portfolio-card-desc">{{ \Illuminate\Support\Str::limit($item->description, 160) }}</div>
                                            @endif
                                            @if(!empty($item->tags))
                                                <div class="portfolio-tags">
                                                    @foreach($item->tags as $tag)
                                                        <span class="portfolio-tag">{{ $tag }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if($item->project_url)
                                                <a href="{{ $item->project_url }}" target="_blank" rel="noopener" class="portfolio-project-link">
                                                    <i class="tio-open-in-new"></i>{{ translate('view_link') }}
                                                </a>
                                            @endif
                                            @if($itemReviews->count())
                                                <div class="portfolio-card-rating">
                                                    <span class="review-stars">ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹ÃƒÆ’Ã¢â‚¬Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¦</span>
                                                    {{ round($itemReviews->avg('rating'), 1) }}
                                                    ({{ $itemReviews->count() }} {{ translate('reviews') }})
                                                </div>
                                            @endif
                                            <div class="portfolio-card-actions">
                                                <button type="button" class="btn btn-outline-secondary btn-sm fl-chat-trigger">
                                                    {{ translate('message') }}
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-toggle="modal"
                                                        data-bs-target="#portfolio-review-modal-{{ $item->id }}" data-target="#portfolio-review-modal-{{ $item->id }}">
                                                    {{ translate('reviews') }} ({{ $itemReviews->count() }})
                                                </button>
                                            </div>
                                        </div>
                                        @if($item->galleryItems->count())
                                            <div class="gallery-strip">
                                                @foreach($item->galleryItems->take(4) as $galleryItem)
                                                    <img alt="" src="{{ getStorageImages(path: $galleryItem->image_full_url, type: 'backend-profile') }}">
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="modal fade" id="portfolio-review-modal-{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ translate('reviews') }} &mdash; {{ $item->title }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    @auth('customer')
                                                        @if($customerReviewedPortfolioIds->contains($item->id))
                                                            <p class="text-muted small">{{ translate('you_have_already_reviewed_this') }}</p>
                                                        @else
                                                            <form action="{{ route('hire.reviews.store', ['type' => 'portfolio', 'id' => $item->id]) }}" method="post" class="mb-4" novalidate>
                                                                @csrf
                                                                <div class="mb-2">
                                                                    <label class="form-label d-block mb-1">{{ translate('your_rating') }}</label>
                                                                    <div class="rating-picker">
                                                                        @for($ratingOption = 5; $ratingOption >= 1; $ratingOption--)
                                                                            <input type="radio" name="rating" id="portfolio-rating-{{ $item->id }}-{{ $ratingOption }}" value="{{ $ratingOption }}" required>
                                                                            <label for="portfolio-rating-{{ $item->id }}-{{ $ratingOption }}"><i class="tio-star"></i>{{ $ratingOption }}</label>
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

                                                    @forelse($itemReviews as $review)
                                                        @php
                                                            $reviewerName = trim(($review->customer?->f_name ?? '') . ' ' . ($review->customer?->l_name ?? '')) ?: translate('customer');
                                                        @endphp
                                                        <div class="review-card">
                                                            <div class="review-head">
                                                                <div class="review-avatar">{{ strtoupper(substr($reviewerName, 0, 1)) }}</div>
                                                                <div>
                                                                    <div class="review-author">{{ $reviewerName }}</div>
                                                                </div>
                                                            </div>
                                                            <div class="review-stars">
                                                                {{ str_repeat('ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹ÃƒÆ’Ã¢â‚¬Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¦', $review->rating) }}{{ str_repeat('ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹ÃƒÆ’Ã¢â‚¬Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â ', 5 - $review->rating) }}
                                                                <span class="review-meta">&middot; {{ $review->created_at->diffForHumans() }}</span>
                                                            </div>
                                                            @if($review->body)
                                                                <div class="review-body">{{ $review->body }}</div>
                                                            @endif
                                                        </div>
                                                    @empty
                                                        <p class="text-muted mb-0">{{ translate('no_reviews_yet') }}</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="freelancer-section">
                        <div class="reviews-toolbar">
                            <div>
                                <h2 class="freelancer-section-title mb-1">{{ translate('reviews') }}</h2>
                                @if($reviews->total() > 0)
                                    <div class="reviews-count">
                                        {{ $reviews->firstItem() }}-{{ $reviews->lastItem() }} {{ translate('out_of') }} {{ $reviews->total() }} {{ translate('reviews') }}
                                    </div>
                                @endif
                            </div>
                            <div class="review-sort-tabs" aria-label="{{ translate('sort_by') }}">
                                <a class="review-sort-tab {{ $reviewSort === 'recent' ? 'is-active' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'recent']) }}">{{ translate('most_recent') }}</a>
                                <a class="review-sort-tab {{ $reviewSort === 'highest' ? 'is-active' : '' }}" href="{{ request()->fullUrlWithQuery(['sort' => 'highest']) }}">{{ translate('highest_rated') }}</a>
                            </div>
                        </div>

                        @php
                            $renderStarRow = function (int $rating) {
                                $html = '<span class="review-stars-row">';
                                for ($i = 1; $i <= 5; $i++) {
                                    $html .= '<i class="tio-star ' . ($i <= $rating ? 'is-filled' : 'is-empty') . '"></i>';
                                }
                                return $html . '</span>';
                            };
                        @endphp
                        @forelse($reviews as $review)
                            @php
                                $reviewerName = trim(($review->contract?->customer?->f_name ?? '') . ' ' . ($review->contract?->customer?->l_name ?? '')) ?: translate('customer');
                                $reviewCover = $review->contract?->service?->images->first();
                            @endphp
                            <div class="review-card">
                                <div class="review-head">
                                    <div class="review-avatar">{{ strtoupper(substr($reviewerName, 0, 1)) }}</div>
                                    <div>
                                        <div class="review-author">
                                            {{ $reviewerName }}
                                            <span class="review-verified-badge"><i class="tio-verified"></i>{{ translate('verified_client') }}</span>
                                        </div>
                                        @if($review->contract?->customer?->country)
                                            <div class="review-meta">{{ $review->contract->customer->country }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="review-stars">{!! $renderStarRow($review->rating) !!}
                                    <span class="review-meta">&middot; {{ $review->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="review-body">{{ $review->body }}</div>
                                <div class="review-facts">
                                    @if($review->contract?->total_amount)
                                        <div>
                                            <div class="review-fact-label">{{ translate('price') }}</div>
                                            <div class="review-fact-value">{{ webCurrencyConverter($review->contract->total_amount) }}</div>
                                        </div>
                                    @endif
                                    @if($review->contract?->completed_at && $review->contract?->created_at)
                                        <div>
                                            <div class="review-fact-label">{{ translate('duration') }}</div>
                                            <div class="review-fact-value">{{ $review->contract->created_at->diffInDays($review->contract->completed_at) }} {{ translate('days') }}</div>
                                        </div>
                                    @endif
                                    @if($review->contract?->service)
                                        <div class="review-service-tag">
                                            @if($reviewCover)
                                                <img alt="" src="{{ getStorageImages(path: $reviewCover->image_full_url, type: 'backend-profile') }}">
                                            @endif
                                            <span>{{ $review->contract->service->title }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ translate('no_reviews_yet') }}</p>
                        @endforelse

                        {{ $reviews->links() }}
                    </div>
                </div>

                <div class="freelancer-sidebar-card">
                    <span class="freelancer-profile-avatar-wrap" title="{{ $displayName }}">
                        <img class="avatar" alt="" src="{{ getStorageImages(path: $imageFullUrl, type: 'backend-profile') }}">
                        @if($isFreelancerOnline)
                            <span class="freelancer-online-dot large" title="Online now"></span>
                        @endif
                    </span>
                    <div class="name">{{ $displayName }}</div>
                    @if($activeOrdersCount > 0)
                        <div class="queue-note">{{ $activeOrdersCount }} {{ translate('orders_in_queue') }}</div>
                    @else
                        <div class="queue-note">&nbsp;</div>
                    @endif
                    <button type="button" class="btn btn--primary w-100" id="fl-chat-fab-btn-sidebar">
                        {{ translate('contact_me') }}
                    </button>
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
        </div>        <div class="fl-chat-panel-body">
            @auth('customer')
                <div class="fl-chat-thread" id="fl-chat-thread"></div>
                <div class="fl-chat-hint">{{ translate('ask') }} {{ $displayName }} {{ translate('a_question_or_share_your_project_details') }}</div>
                <div class="fl-chat-suggestions">
                    <button type="button" class="fl-chat-suggestion" data-suggestion="{{ translate('hey') }} {{ $displayName }}, {{ translate('can_you_help_me_with') }}...">
                        ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â°ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¸ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¹Ãƒâ€¦Ã¢â‚¬Å“ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¹ {{ translate('hey') }} {{ $displayName }}, {{ translate('can_you_help_me_with') }}...
                    </button>
                    <button type="button" class="fl-chat-suggestion" data-suggestion="{{ translate('can_you_provide_your_rate_for') }}...">
                        ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â°ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¸ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¾Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â² {{ translate('can_you_provide_your_rate_for') }}...
                    </button>
                    <button type="button" class="fl-chat-suggestion" data-suggestion="{{ translate('do_you_think_you_can_deliver_an_order_by') }}...">
                        ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â°ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¸ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¯ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¸ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â {{ translate('do_you_think_you_can_deliver_an_order_by') }}...
                    </button>
                </div>
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
                    <form action="" method="post" enctype="multipart/form-data" id="get-quote-form" novalidate>
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

    @push('script')
        <script>
            (function () {
                const quoteModal = document.getElementById('getQuoteModal');
                const quoteForm = document.getElementById('get-quote-form');
                const deliveryOptions = document.querySelectorAll('.quote-delivery-option');
                const deliveryInput = document.getElementById('quote-delivery-preference');
                const customDeliveryInput = document.getElementById('quote-custom-delivery');

                document.querySelectorAll('.get-quote-btn').forEach(function (button) {
                    button.addEventListener('click', function () {
                        quoteForm.action = '{{ url('hire/quotes') }}/' + button.dataset.serviceId;
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

                // Submitted as a normal form before, which fully reloaded the page
                // after a redirect response ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â send it via AJAX instead.
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

                document.querySelectorAll('[data-toggle-tiers]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const grid = button.closest('.service-card-body').querySelector('[data-tier-grid]');
                        grid?.classList.toggle('is-open');
                        button.textContent = grid?.classList.contains('is-open') ? '{{ translate('hide_details') }}' : '{{ translate('more_details') }}';
                    });
                });
            })();
        </script>
    @endpush

    @push('script')
        <script>
            (function () {
                const fab = document.getElementById('fl-chat-fab');
                const panel = document.getElementById('fl-chat-panel');
                const fabBtn = document.getElementById('fl-chat-fab-btn');
                const fabBtnSidebar = document.getElementById('fl-chat-fab-btn-sidebar');
                const closeBtn = document.getElementById('fl-chat-close');
                const textarea = document.getElementById('fl-chat-message');
                const chatForm = document.getElementById('fl-chat-form');
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

                // Background badge, shown even while the panel is closed ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â mirrors the
                // classic "red dot with a number" unread indicator. Uses a dedicated
                // endpoint that does NOT mark messages as seen (unlike loadChatThread's
                // /message call), so the badge accurately reflects unread state until
                // the customer actually opens the panel.
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

                function openPanel() {
                    panel.classList.add('is-open');
                    fab.style.display = 'none';
                    textarea?.focus();
                    clearUnreadBadge();
                    loadChatThread();
                    if (chatThread && !chatPollTimer) {
                        chatPollTimer = setInterval(loadChatThread, 8000);
                    }
                }

                function closePanel() {
                    panel.classList.remove('is-open');
                    fab.style.display = 'flex';
                    if (chatPollTimer) {
                        clearInterval(chatPollTimer);
                        chatPollTimer = null;
                    }
                }

                fab?.addEventListener('click', openPanel);
                fabBtn?.addEventListener('click', openPanel);
                fabBtnSidebar?.addEventListener('click', openPanel);
                closeBtn?.addEventListener('click', closePanel);

                // Every "Message" button on the service/portfolio cards opens the
                // same shared chat panel instead of each needing its own widget.
                document.querySelectorAll('.fl-chat-trigger').forEach(function (button) {
                    button.addEventListener('click', openPanel);
                });

                document.querySelectorAll('.fl-chat-suggestion').forEach(function (button) {
                    button.addEventListener('click', function () {
                        textarea.value = button.dataset.suggestion.replace(/\.\.\.$/, ' ');
                        textarea.focus();
                    });
                });

                fileInput?.addEventListener('change', function () {
                    var file = this.files && this.files[0];
                    if (!file) { fileName?.classList.add('d-none'); return; }
                    if (file.size > maxChatFileSize) {
                        toastr.error('{{ translate('file_maximum_size_') }}2 MB');
                        this.value = '';
                        fileName?.classList.add('d-none');
                        return;
                    }
                    if (fileName) { fileName.textContent = file.name; fileName.classList.remove('d-none'); }
                });
                if (fabBadge) {
                    refreshUnreadBadge();
                    unreadPollTimer = setInterval(refreshUnreadBadge, 20000);
                }

                // This used to be a plain form post, so the browser navigated to
                // /message and rendered its raw JSON response instead of just
                // sending the message and staying on the profile page.
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
            })();
        </script>
    @endpush
@endsection





