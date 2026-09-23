@extends('layouts.front-end.app')

@section('title', translate('hire_freelancer'))

@push('css_or_js')
    <style>
        .hire-freelancer-page {
            background: #f6f8fb;
            padding: 0 0 54px;
        }

        .hire-freelancer-hero {
            background: linear-gradient(120deg, #17395e 0%, #1f4a78 55%, #f97316 160%);
            padding: 38px 0 78px;
            margin-bottom: -54px;
        }

        .hire-freelancer-heading {
            color: #fff;
            font-weight: 800;
            font-size: 34px;
            margin-bottom: 8px;
        }

        .hire-freelancer-subtitle {
            color: rgba(255,255,255,.82);
            max-width: 680px;
            margin-bottom: 0;
        }

        /* Fiverr-style horizontal category tab bar */
        .hire-category-tabs-wrapper {
            background: #fff;
            border: 1px solid #e7ebf0;
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 16px 36px rgba(15, 30, 55, 0.14);
        }

        .hire-category-tabs {
            display: flex;
            gap: 4px;
            overflow-x: auto;
            padding: 0 10px;
            scrollbar-width: thin;
        }

        .hire-category-tabs::-webkit-scrollbar { height: 4px; }
        .hire-category-tabs::-webkit-scrollbar-thumb { background: #d8dee7; border-radius: 999px; }

        .hire-category-tab {
            flex-shrink: 0;
            padding: 14px 14px;
            font-size: 14px;
            font-weight: 700;
            color: #4b5563;
            text-decoration: none;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
        }

        .hire-category-tab:hover {
            color: #17395e;
            text-decoration: none;
        }

        .hire-category-tab.active {
            color: #17395e;
            border-bottom-color: #f97316;
        }
        .hire-category-tabs-wrapper {
            border: 0;
            border-radius: 18px;
            background: rgba(255,255,255,.96);
            box-shadow: 0 18px 46px rgba(15, 23, 42, .16);
        }
        .hire-category-tabs { gap: 8px; padding: 10px; }
        .hire-category-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 46px;
            padding: 10px 15px;
            border: 1px solid transparent;
            border-radius: 999px;
            border-bottom: 1px solid transparent;
            transition: background .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        .hire-category-tab i {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f6f8fb;
            color: #f97316;
            font-size: 12px;
        }
        .hire-category-tab:hover,
        .hire-category-tab.active {
            background: #17395e;
            color: #fff;
            border-color: #17395e;
            box-shadow: 0 10px 22px rgba(23, 57, 94, .22);
            transform: translateY(-1px);
        }
        .hire-category-tab:hover i,
        .hire-category-tab.active i { background: rgba(255,255,255,.16); color: #fff; }
        .gig-card { border-radius: 12px; box-shadow: 0 12px 28px rgba(15, 23, 42, .08); }
        .gig-card-cover { aspect-ratio: 16 / 11; }
        .gig-card-body { padding: 18px; }
        .gig-card-title { font-size: 15px; font-weight: 700; min-height: 42px; }


        .hire-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 18px;
        }

        .hire-breadcrumb a { color: #6b7280; text-decoration: none; }
        .hire-breadcrumb a:hover { color: #17395e; text-decoration: underline; }
        .hire-breadcrumb span.current { color: #17395e; font-weight: 700; }

        .hire-category-section {
            background: #fff;
            border: 1px solid #e7ebf0;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 20px;
            transition: box-shadow .2s ease;
        }

        .hire-category-section:hover {
            box-shadow: 0 10px 30px rgba(23, 57, 94, 0.06);
        }

        .hire-category-title {
            color: #17395e;
            font-size: 21px;
            font-weight: 800;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .hire-category-title-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            flex-shrink: 0;
        }

        /* Fiverr-style icon-circle sub-category scroller */
        .hire-subcat-scroller {
            display: flex;
            gap: 24px;
            overflow-x: auto;
            padding: 6px 2px 10px;
        }

        .hire-subcat-item {
            flex-shrink: 0;
            width: 104px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            text-decoration: none;
            gap: 10px;
        }

        .hire-subcat-icon {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: transform .22s ease, box-shadow .22s ease;
            box-shadow: 0 4px 14px rgba(23, 57, 94, 0.1), 0 0 0 4px #fff, 0 0 0 5px #eef1f5;
        }

        .hire-subcat-item:hover .hire-subcat-icon,
        .hire-subcat-item.active .hire-subcat-icon {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 14px 28px rgba(23, 57, 94, 0.18), 0 0 0 4px #fff, 0 0 0 5px #f97316;
        }

        .hire-subcat-icon img { width: 100%; height: 100%; object-fit: cover; }
        .hire-subcat-icon i { font-size: 32px; }

        .hire-subcat-label {
            font-size: 12.5px;
            font-weight: 600;
            color: #374151;
            line-height: 1.3;
        }

        .hire-subcat-item:hover .hire-subcat-label,
        .hire-subcat-item.active .hire-subcat-label {
            color: #17395e;
        }
        .hire-category-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .hire-category-card {
            position: relative;
            display: flex;
            min-height: 210px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            background: #fff;
            color: #1f2937;
            text-decoration: none;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
            transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        }

        .hire-category-card:hover {
            color: #1f2937;
            text-decoration: none;
            transform: translateY(-6px);
            border-color: rgba(249, 115, 22, .38);
            box-shadow: 0 22px 42px rgba(15, 23, 42, 0.14);
        }

        .hire-category-card-bg { position: absolute; inset: 0; opacity: .12; transition: opacity .22s ease, transform .35s ease; }
        .hire-category-card:hover .hire-category-card-bg { opacity: .2; transform: scale(1.08); }
        .hire-category-card-content { position: relative; z-index: 1; width: 100%; padding: 20px; display: flex; flex-direction: column; }
        .hire-category-card-icon { width: 48px; height: 48px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 16px; box-shadow: inset 0 0 0 1px rgba(255,255,255,.65); }
        .hire-category-card-title { font-size: 18px; font-weight: 800; color: #17395e; line-height: 1.25; margin-bottom: 8px; }
        .hire-category-card-count { font-size: 12px; font-weight: 700; color: #6b7280; margin-bottom: 14px; }
        .hire-category-specializations { display: flex; flex-wrap: wrap; gap: 7px; margin-top: auto; opacity: .68; max-height: 58px; overflow: hidden; transition: opacity .2s ease, max-height .24s ease; }
        .hire-category-card:hover .hire-category-specializations,
        .hire-category-card:focus .hire-category-specializations { opacity: 1; max-height: 120px; }
        .hire-category-specialization-chip { display: inline-flex; align-items: center; max-width: 100%; padding: 5px 10px; border-radius: 999px; background: #f6f8fb; color: #374151; font-size: 11px; font-weight: 700; line-height: 1.2; }
        .hire-category-card-action { display: inline-flex; align-items: center; gap: 7px; margin-top: 16px; color: #f97316; font-size: 13px; font-weight: 800; }


        .hire-empty-state {
            background: #fff;
            border: 1px solid #e7ebf0;
            border-radius: 8px;
            padding: 34px;
            color: #6b7280;
            text-align: center;
        }

        .results-toolbar {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;
        }

        .results-count { color: #6b7280; font-size: 13px; }
        .results-sort { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #6b7280; }
        .results-sort select { border: 1px solid #d8dee7; border-radius: 8px; padding: 7px 12px; font-size: 13px; font-weight: 600; color: #17395e; }

        .hire-filter-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 18px; }
        .hire-filter-dropdown { position: relative; }
        .hire-filter-btn {
            display: inline-flex; align-items: center; gap: 6px; border: 1px solid #d8dee7; border-radius: 8px;
            background: #fff; padding: 8px 14px; font-size: 13px; font-weight: 700; color: #21486f; cursor: pointer;
        }
        .hire-filter-btn:hover, .hire-filter-btn.is-active { border-color: #17395e; }
        .hire-filter-btn i { font-size: 14px; color: #6b7280; }
        .hire-filter-panel {
            display: none; position: absolute; top: calc(100% + 6px); left: 0; z-index: 20;
            min-width: 220px; background: #fff; border: 1px solid #e7ebf0; border-radius: 10px;
            box-shadow: 0 12px 28px rgba(23, 57, 94, 0.12); padding: 14px;
        }
        .hire-filter-panel.is-open { display: block; }
        .hire-delivery-option {
            display: block; padding: 7px 8px; border-radius: 6px; font-size: 13px; color: #374151; text-decoration: none;
        }
        .hire-delivery-option:hover { background: #f6f8fb; text-decoration: none; }
        .hire-delivery-option.active { background: #eef2ff; color: #17395e; font-weight: 700; }
        .hire-pro-toggle { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: #6b7280; margin-left: auto; }
        .hire-pro-toggle input { accent-color: #f97316; }

        .gig-card-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 22px;
            margin-bottom: 24px;
        }

        .gig-card {
            display: flex;
            flex-direction: column;
            background: #fff;
            border: 1px solid #e7ebf0;
            border-radius: 18px;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            position: relative;
            box-shadow: 0 6px 18px rgba(23, 57, 94, 0.05);
            transition: box-shadow .22s ease, transform .22s ease, border-color .22s ease;
        }

        .gig-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #17395e, #f97316);
            z-index: 1;
            opacity: 0;
            transition: opacity .22s ease;
        }

        .gig-card:hover::before { opacity: 1; }

        .gig-card:hover {
            box-shadow: 0 18px 36px rgba(23, 57, 94, 0.14);
            transform: translateY(-5px);
            border-color: #d8dee7;
            color: inherit;
            text-decoration: none;
        }

        .gig-card-cover-wrap { position: relative; overflow: hidden; }

        .gig-card-cover {
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            background: #eef2f7;
            transition: transform .35s ease;
        }

        .gig-card:hover .gig-card-cover { transform: scale(1.06); }

        .gig-card-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .02em;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 999px;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        }

        .gig-card-badge.badge-new { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .gig-card-badge.badge-popular { background: linear-gradient(135deg, #f97316, #ea580c); }
        .gig-card-badge i { font-size: 12px; }

        .gig-card-body { padding: 16px; display: flex; flex-direction: column; flex: 1; }

        .gig-card-seller { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
        .gig-card-seller img {
            width: 30px; height: 30px; border-radius: 50%; object-fit: cover;
            border: 2px solid #fff; box-shadow: 0 0 0 1px #e7ebf0;
            transition: box-shadow .22s ease;
        }
        .gig-card:hover .gig-card-seller img { box-shadow: 0 0 0 1px #f97316; }
        .gig-card-seller-name { font-weight: 700; font-size: 13px; color: #1f2937; }
        .gig-card-level {
            font-size: 10px; font-weight: 700; background: #eef2ff; color: #3730a3; border-radius: 999px; padding: 2px 8px;
        }

        .gig-card-title {
            font-size: 14px; font-weight: 600; color: #1f2937; line-height: 1.45; margin-bottom: 12px;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }

        .gig-card-rating { display: flex; align-items: center; gap: 4px; font-size: 13px; margin-bottom: 0; margin-top: auto; }
        .gig-card-rating .stars { color: #f5a623; display: inline-flex; gap: 1px; font-size: 11px; }
        .gig-card-rating strong { color: #1f2937; }
        .gig-card-rating .count { color: #9ca3af; font-size: 12px; }

        .gig-card-footer {
            display: flex; justify-content: space-between; align-items: center;
            border-top: 1px solid #f1f3f6; margin-top: 12px; padding-top: 12px;
        }
        .gig-card-price-label { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .03em; }
        .gig-card-price { font-weight: 800; color: #17395e; font-size: 17px; }
        .gig-card-cta {
            display: inline-flex; align-items: center; justify-content: center;
            width: 34px; height: 34px; border-radius: 50%;
            background: #f6f8fb; color: #17395e; font-size: 14px;
            transition: background .18s ease, color .18s ease, transform .18s ease;
        }
        .gig-card:hover .gig-card-cta { background: #f97316; color: #fff; transform: translateX(2px); }

        @media (max-width: 1199.98px) {
            .hire-category-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .gig-card-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        @media (max-width: 991.98px) {
            .hire-category-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .gig-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 767.98px) {
            .hire-freelancer-heading {
                font-size: 26px;
            }

            .gig-card-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .hire-category-grid { grid-template-columns: 1fr; gap: 14px; }
            .hire-category-card { min-height: 178px; }
            .hire-category-specializations { opacity: 1; max-height: none; }
            .hire-category-section { padding: 18px; }
            .hire-filter-bar { gap: 8px; }
            .hire-pro-toggle { margin-left: 0; width: 100%; }
            .results-toolbar { flex-direction: column; align-items: flex-start; }
        }

        @media (max-width: 479.98px) {
            .hire-freelancer-heading { font-size: 22px; }
            .hire-subcat-item { width: 84px; }
            .hire-subcat-icon { width: 60px; height: 60px; }
        }
        /* Final freelancer marketplace redesign overrides */
        .hire-freelancer-page {
            background:
                linear-gradient(180deg, rgba(248, 250, 252, .96), rgba(239, 246, 255, .96)),
                radial-gradient(circle at 12% 22%, rgba(249, 115, 22, .16), transparent 28%),
                radial-gradient(circle at 86% 16%, rgba(20, 184, 166, .15), transparent 30%);
        }
        .hire-freelancer-hero {
            background:
                linear-gradient(118deg, rgba(13, 42, 72, .96) 0%, rgba(23, 57, 94, .92) 48%, rgba(15, 118, 110, .78) 100%),
                linear-gradient(45deg, #f97316, #4f46e5);
            padding: 52px 0 92px;
        }
        .hire-freelancer-heading { font-size: 38px; letter-spacing: 0; }
        .hire-category-tabs-wrapper {
            margin-top: -40px;
            margin-bottom: 24px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 24px 58px rgba(15, 23, 42, .18);
        }
        .hire-category-tabs { padding: 12px; gap: 10px; }
        .hire-category-tab {
            min-height: 48px;
            padding: 10px 16px;
            border-radius: 999px;
            color: #17395e;
            background: #f8fafc;
            border: 1px solid #e7edf5;
            border-bottom: 1px solid #e7edf5;
            font-weight: 900;
        }
        .hire-category-tab i {
            width: 30px;
            height: 30px;
            background: #fff;
            color: #f97316;
            box-shadow: 0 4px 12px rgba(15, 23, 42, .08);
        }
        .hire-category-tab:hover,
        .hire-category-tab.active {
            background: linear-gradient(120deg, #17395e, #4f46e5);
            color: #fff;
            border-color: transparent;
            transform: translateY(-2px);
        }
        .hire-breadcrumb {
            display: inline-flex;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.9);
            border: 1px solid #e7edf5;
        }
        .hire-category-section {
            border: 0;
            border-radius: 20px;
            padding: 28px;
            background: rgba(255,255,255,.94);
            box-shadow: 0 18px 46px rgba(15, 23, 42, .10);
        }
        .hire-category-title { font-size: 24px; margin-bottom: 22px; }
        .hire-category-title-icon {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            box-shadow: 0 12px 24px rgba(249, 115, 22, .16);
        }
        .hire-subcat-scroller {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 14px;
            overflow: visible;
            padding: 0;
        }
        .hire-subcat-item {
            width: auto;
            min-height: 146px;
            padding: 16px 12px;
            border-radius: 18px;
            background: linear-gradient(180deg, #fff, #f8fafc);
            border: 1px solid #e7edf5;
            box-shadow: 0 12px 26px rgba(15, 23, 42, .07);
            transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease, background .22s ease;
        }
        .hire-subcat-item:hover,
        .hire-subcat-item.active {
            transform: translateY(-6px);
            border-color: rgba(249, 115, 22, .45);
            background: linear-gradient(180deg, #fff7ed, #fff);
            box-shadow: 0 22px 42px rgba(15, 23, 42, .14);
        }
        .hire-subcat-icon {
            width: 66px;
            height: 66px;
            border-radius: 18px;
            box-shadow: none;
            background: linear-gradient(135deg, #fff7ed, #fff7ed) !important;
        }
        .hire-subcat-item:hover .hire-subcat-icon,
        .hire-subcat-item.active .hire-subcat-icon {
            transform: none;
            box-shadow: 0 14px 28px rgba(249, 115, 22, .16);
        }
        .hire-subcat-icon i { font-size: 28px; }
        .hire-subcat-label { font-size: 13px; font-weight: 900; color: #17395e; }
        .hire-filter-bar {
            padding: 14px;
            border-radius: 16px;
            background: linear-gradient(120deg, #f8fafc, #fff7ed);
            border: 1px solid #edf1f7;
        }
        .hire-filter-btn,
        .results-sort select {
            min-height: 42px;
            border-radius: 12px;
            border-color: #dbe3ee;
            background: #fff;
            color: #17395e;
            font-weight: 900;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .04);
        }
        .results-toolbar { margin-top: 18px; }
        .results-count {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            padding: 6px 12px;
            border-radius: 999px;
            background: #fff7ed;
            color: #075985;
            font-weight: 900;
        }
        .gig-card-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }
        .gig-card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 18px 42px rgba(15, 23, 42, .12);
            min-height: 100%;
        }
        .gig-card::before {
            height: 6px;
            opacity: 1;
            background: linear-gradient(90deg, #f97316, #4f46e5, #4f46e5);
        }
        .gig-card-cover {
            aspect-ratio: 16 / 10;
            background:
                linear-gradient(135deg, rgba(23, 57, 94, .08), rgba(249, 115, 22, .10)),
                #eef2f7;
        }
        .gig-card-cover.d-flex {
            background:
                radial-gradient(circle at 30% 20%, rgba(249, 115, 22, .18), transparent 34%),
                radial-gradient(circle at 72% 72%, rgba(20, 184, 166, .18), transparent 36%),
                linear-gradient(135deg, #edf2f7, #f8fafc);
        }
        .gig-card-cover.d-flex i {
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            color: #94a3b8 !important;
        }
        .gig-card-body { padding: 18px; }
        .gig-card-seller img { width: 34px; height: 34px; }
        .gig-card-title { font-size: 15.5px; font-weight: 900; color: #102a43; min-height: 44px; }
        .gig-card-rating .count { font-weight: 800; color: #64748b; }
        .gig-card-footer { border-top-color: #edf1f7; }
        .gig-card-price { color: #4f46e5; font-size: 19px; }
        .gig-card-cta { background: #17395e; color: #fff; }
        @media (max-width: 991.98px) {
            .gig-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 767.98px) {
            .hire-freelancer-hero { padding: 36px 0 74px; }
            .hire-category-tabs-wrapper { margin-top: -32px; }
            .hire-category-section { padding: 18px; }
            .hire-subcat-scroller { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .gig-card-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 420px) {
            .hire-subcat-scroller { grid-template-columns: 1fr; }
        }
    
        /* Extra freelancer redesign pass: colorful badges, no right-scroll, polished responsive sizing */
        .hire-freelancer-page, .hire-freelancer-page * { box-sizing: border-box; }
        .hire-freelancer-page { overflow-x: hidden; background: linear-gradient(180deg, #f4f8ff 0%, #fff7ed 46%, #f8fafc 100%); }
        .hire-freelancer-page .container { max-width: 1180px; width: 100%; }
        .hire-category-tabs-wrapper, .hire-category-tabs, .hire-subcat-scroller { max-width: 100%; overflow: visible; }
        .hire-category-tabs { flex-wrap: wrap; gap: 10px; padding: 12px; border: 1px solid rgba(148, 163, 184, .2); }
        .hire-category-tab { border-radius: 999px; min-height: 42px; background: #fff; box-shadow: 0 8px 20px rgba(15, 23, 42, .08); }
        .hire-category-tab:nth-child(5n+1) { --tab-color: #f97316; }
        .hire-category-tab:nth-child(5n+2) { --tab-color: #4f46e5; }
        .hire-category-tab:nth-child(5n+3) { --tab-color: #16a34a; }
        .hire-category-tab:nth-child(5n+4) { --tab-color: #db2777; }
        .hire-category-tab:nth-child(5n+5) { --tab-color: #4338ca; }
        .hire-category-tab:hover, .hire-category-tab.active { color: var(--tab-color); background: color-mix(in srgb, var(--tab-color) 10%, white); box-shadow: 0 14px 28px rgba(15, 23, 42, .12); transform: translateY(-2px); }
        .hire-category-tab.active::after { background: var(--tab-color); }
        .hire-category-section { width: 100%; overflow: hidden; background: rgba(255,255,255,.9); border: 1px solid rgba(148, 163, 184, .22); box-shadow: 0 18px 45px rgba(15,23,42,.08); }
        .hire-subcat-scroller { display: grid; grid-template-columns: repeat(auto-fill, minmax(138px, 1fr)); gap: 16px; padding: 4px 0; }
        .hire-subcat-item { width: 100%; min-width: 0; min-height: 142px; border: 1px solid rgba(148, 163, 184, .22); border-radius: 18px; background: linear-gradient(180deg, #fff, #f8fafc); padding: 16px 10px; box-shadow: 0 10px 26px rgba(15,23,42,.07); }
        .hire-subcat-item:hover { transform: translateY(-5px); border-color: #f97316; box-shadow: 0 18px 36px rgba(249, 115, 22, .18); }
        .hire-subcat-item:hover .hire-subcat-label { color: #0f172a; }
        .hire-subcat-icon { width: 68px; height: 68px; box-shadow: inset 0 0 0 5px rgba(255,255,255,.72), 0 10px 24px rgba(15,23,42,.12); }
        .gig-card-grid { grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 22px; width: 100%; max-width: 100%; }
        .gig-card { overflow: hidden; border-radius: 18px; border: 1px solid rgba(148, 163, 184, .25); box-shadow: 0 16px 34px rgba(15,23,42,.09); }
        .gig-card:hover { transform: translateY(-6px); border-color: rgba(249, 115, 22, .55); box-shadow: 0 24px 50px rgba(15,23,42,.16); }
        .gig-card-cover-wrap { background: linear-gradient(135deg, #fff7ed, #fff7ed 48%, #fce7f3); }
        .gig-card-badge { border: 0; color: #fff; box-shadow: 0 10px 22px rgba(15,23,42,.18); }
        .badge-new { background: linear-gradient(135deg, #16a34a, #22c55e); }
        .badge-popular { background: linear-gradient(135deg, #f97316, #db2777); }
        .gig-card-seller { min-width: 0; align-items: center; }
        .gig-card-avatar-wrap { position: relative; width: 36px; height: 36px; flex: 0 0 auto; }
        .gig-card-avatar-wrap img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
        .freelancer-online-dot { position: absolute; right: -1px; bottom: -1px; width: 12px; height: 12px; border-radius: 50%; background: #22c55e; border: 2px solid #fff; box-shadow: 0 0 0 3px rgba(34,197,94,.18); }
        .gig-card-seller-copy { display: flex; flex-direction: column; min-width: 0; flex: 1; }
        .gig-card-seller-name { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .gig-card-seller-status { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; color: #64748b; font-weight: 800; }
        .gig-card-seller-status span { width: 6px; height: 6px; border-radius: 50%; background: #cbd5e1; }
        .gig-card-seller-status.is-online { color: #15803d; }
        .gig-card-seller-status.is-online span { background: #22c55e; }
        .gig-card-level { background: linear-gradient(135deg, #eef2ff, #fae8ff); color: #4338ca; }
        .gig-card-tags { display: flex; flex-wrap: wrap; gap: 7px; margin: 0 0 10px; }
        .gig-service-chip { display: inline-flex; align-items: center; gap: 5px; max-width: 100%; padding: 5px 9px; border-radius: 999px; font-size: 10px; font-weight: 900; line-height: 1.1; }
        .chip-category { background: #eff6ff; color: #1d4ed8; }
        .chip-specialization { background: #fff7ed; color: #c2410c; }
        .chip-delivery { background: #ecfdf5; color: #047857; }
        .gig-card-title { min-height: 42px; color: #0f172a; }
        @media (max-width: 767.98px) {
            .hire-freelancer-hero { padding: 34px 0 92px; }
            .hire-category-tabs { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); border-radius: 18px; }
            .hire-category-tab { width: 100%; justify-content: center; text-align: center; padding: 10px 8px; font-size: 12px; }
            .hire-subcat-scroller, .gig-card-grid { grid-template-columns: 1fr; }
            .hire-category-section, .hire-results-panel { padding: 18px; border-radius: 18px; }
            .results-toolbar, .hire-filter-row { align-items: stretch; flex-direction: column; }
            .results-sort select, .filter-pill { width: 100%; }
        }
        @media (max-width: 420px) {
            .hire-category-tabs { grid-template-columns: 1fr; }
            .gig-card-footer { align-items: flex-start; flex-direction: column; }
        }
    
        /* Single-row category ribbon override */
        .hire-category-tabs-wrapper {
            max-width: 1320px;
            margin-left: auto;
            margin-right: auto;
            border-radius: 18px;
            background: rgba(255,255,255,.96);
            border: 1px solid rgba(226, 232, 240, .95);
            box-shadow: 0 18px 44px rgba(15,23,42,.14);
            overflow: hidden;
        }
        .hire-category-tabs-wrapper::after {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 42px;
            pointer-events: none;
            background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,.96));
        }
        .hire-category-tabs {
            position: relative;
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: center;
            gap: 7px;
            padding: 12px 14px;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            scrollbar-width: thin;
            border: 0;
            scroll-snap-type: x proximity;
        }
        .hire-category-tabs::-webkit-scrollbar { height: 5px; }
        .hire-category-tabs::-webkit-scrollbar-track { background: transparent; }
        .hire-category-tabs::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        .hire-category-tab {
            flex: 0 0 auto;
            min-height: 44px;
            padding: 9px 13px;
            gap: 7px;
            font-size: 13px;
            line-height: 1;
            white-space: nowrap;
            scroll-snap-align: start;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #fff, #f8fafc);
            box-shadow: 0 8px 18px rgba(15, 23, 42, .07);
        }
        .hire-category-tab i {
            width: 26px;
            height: 26px;
            font-size: 11px;
            flex: 0 0 auto;
        }
        .hire-category-tab:hover,
        .hire-category-tab.active {
            transform: translateY(-1px);
            border-color: color-mix(in srgb, var(--tab-color) 34%, #e2e8f0);
            background: color-mix(in srgb, var(--tab-color) 12%, white);
            color: var(--tab-color);
            box-shadow: 0 12px 24px rgba(15, 23, 42, .1);
        }
        .hire-category-tab:hover i,
        .hire-category-tab.active i {
            color: #fff;
            background: var(--tab-color);
        }
        @media (min-width: 1400px) {
            .hire-category-tab { padding-left: 15px; padding-right: 15px; font-size: 13.5px; }
        }
        @media (max-width: 767.98px) {
            .hire-category-tabs-wrapper { border-radius: 16px; }
            .hire-category-tabs {
                display: flex !important;
                grid-template-columns: none !important;
                flex-wrap: nowrap !important;
                padding: 10px;
            }
            .hire-category-tab {
                width: auto;
                min-height: 42px;
                justify-content: flex-start;
                text-align: left;
                font-size: 12px;
                padding: 9px 11px;
            }
        }
        @media (max-width: 420px) {
            .hire-category-tabs { grid-template-columns: none !important; }
        }
    
        /* Fiverr-style click-scroll tabs: no visible scrollbar */
        .hire-category-tabs-shell {
            position: relative;
            display: flex;
            align-items: center;
            max-width: 1320px;
            margin: 0 auto 20px;
        }
        .hire-category-tabs-shell .hire-category-tabs-wrapper {
            flex: 1 1 auto;
            min-width: 0;
            margin: 0;
            overflow: hidden;
            border-radius: 0;
            border: 0;
            box-shadow: none;
            background: transparent;
        }
        .hire-category-tabs-shell .hire-category-tabs-wrapper::after { display: none; }
        .hire-category-tabs-shell .hire-category-tabs {
            display: flex !important;
            flex-wrap: nowrap !important;
            gap: 24px;
            padding: 0 8px;
            min-height: 58px;
            align-items: center;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            scrollbar-width: none;
            scroll-behavior: smooth;
            scroll-snap-type: x proximity;
            border: 0;
        }
        .hire-category-tabs-shell .hire-category-tabs::-webkit-scrollbar { display: none; width: 0; height: 0; }
        .hire-category-tabs-shell .hire-category-tab {
            flex: 0 0 auto;
            min-height: auto;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            color: #334155;
            font-size: 15px;
            font-weight: 700;
            gap: 7px;
            white-space: nowrap;
            transition: color .18s ease, transform .18s ease;
        }
        .hire-category-tabs-shell .hire-category-tab i {
            width: auto;
            height: auto;
            border-radius: 0;
            background: transparent;
            color: #f97316;
            font-size: 13px;
        }
        .hire-category-tabs-shell .hire-category-tab:hover,
        .hire-category-tabs-shell .hire-category-tab.active {
            color: #f97316;
            background: transparent;
            box-shadow: none;
            border-color: transparent;
            transform: translateY(-1px);
        }
        .hire-category-tabs-shell .hire-category-tab:hover i,
        .hire-category-tabs-shell .hire-category-tab.active i {
            color: #f97316;
            background: transparent;
        }
        .hire-category-scroll-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .12);
            transition: transform .18s ease, color .18s ease, opacity .18s ease;
            z-index: 2;
        }
        .hire-category-scroll-btn:hover { color: #f97316; transform: translateY(-1px); }
        .hire-category-scroll-btn.is-disabled { opacity: .35; pointer-events: none; box-shadow: none; }
        .hire-category-scroll-btn.prev { margin-right: 10px; }
        .hire-category-scroll-btn.next { margin-left: 10px; }
        @media (max-width: 767.98px) {
            .hire-category-tabs-shell { margin-bottom: 16px; }
            .hire-category-tabs-shell .hire-category-tabs { gap: 18px; min-height: 52px; }
            .hire-category-tabs-shell .hire-category-tab { font-size: 13px; }
            .hire-category-scroll-btn { width: 32px; height: 32px; }
            .hire-category-scroll-btn.prev { margin-right: 7px; }
            .hire-category-scroll-btn.next { margin-left: 7px; }
        }
    
        /* Final category ribbon color fix with #4f46e5c7 accent */
        .hire-freelancer-page {
            background: linear-gradient(180deg, rgba(79,70,229,.06) 0%, #ffffff 38%, rgba(79,70,229,.04) 100%);
        }
        .hire-freelancer-hero {
            background: linear-gradient(120deg, #17395e 0%, #4f46e5c7 58%, #4f46e5c7 100%) !important;
            margin-bottom: -34px;
            padding-bottom: 78px;
        }
        .hire-category-tabs-shell {
            max-width: 1320px;
            margin: 0 auto 24px;
            padding: 12px;
            border-radius: 20px;
            background: #fff;
            border: 1px solid rgba(79,70,229,.18);
            box-shadow: 0 18px 44px rgba(15, 118, 110, .16), 0 8px 22px rgba(15, 23, 42, .08);
        }
        .hire-category-tabs-shell .hire-category-tabs-wrapper {
            background: #fff;
        }
        .hire-category-tabs-shell .hire-category-tabs {
            min-height: 48px;
            gap: 10px;
            padding: 0 4px;
        }
        .hire-category-tabs-shell .hire-category-tab {
            min-height: 42px;
            padding: 8px 13px;
            border-radius: 999px;
            border: 1px solid rgba(79,70,229,.14);
            background: #fff;
            color: #0f172a !important;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
            font-weight: 800;
        }
        .hire-category-tabs-shell .hire-category-tab:nth-child(6n+1) { --tab-color: #4f46e5; --tab-bg: rgba(79,70,229,.12); }
        .hire-category-tabs-shell .hire-category-tab:nth-child(6n+2) { --tab-color: #f97316; --tab-bg: rgba(249,115,22,.12); }
        .hire-category-tabs-shell .hire-category-tab:nth-child(6n+3) { --tab-color: #4f46e5; --tab-bg: rgba(79,70,229,.12); }
        .hire-category-tabs-shell .hire-category-tab:nth-child(6n+4) { --tab-color: #db2777; --tab-bg: rgba(219,39,119,.12); }
        .hire-category-tabs-shell .hire-category-tab:nth-child(6n+5) { --tab-color: #4338ca; --tab-bg: rgba(124,58,237,.12); }
        .hire-category-tabs-shell .hire-category-tab:nth-child(6n+6) { --tab-color: #16a34a; --tab-bg: rgba(22,163,74,.12); }
        .hire-category-tabs-shell .hire-category-tab i {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--tab-bg);
            color: var(--tab-color) !important;
            font-size: 12px;
        }
        .hire-category-tabs-shell .hire-category-tab:hover,
        .hire-category-tabs-shell .hire-category-tab.active {
            background: var(--tab-color) !important;
            border-color: var(--tab-color) !important;
            color: #fff !important;
            box-shadow: 0 14px 28px rgba(15, 118, 110, .18);
            transform: translateY(-2px);
        }
        .hire-category-tabs-shell .hire-category-tab:hover i,
        .hire-category-tabs-shell .hire-category-tab.active i {
            background: rgba(255,255,255,.2) !important;
            color: #fff !important;
        }
        .hire-category-scroll-btn {
            border-color: rgba(79,70,229,.2);
            color: #4f46e5;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 118, 110, .16);
        }
        .hire-category-scroll-btn:hover {
            color: #fff;
            background: #4f46e5;
            border-color: #4f46e5;
        }
        .hire-category-scroll-btn.is-disabled {
            opacity: .42;
            color: #94a3b8;
            background: #f8fafc;
        }
        .hire-breadcrumb {
            margin-top: 2px;
        }
        .hire-breadcrumb span.current,
        .hire-category-title,
        .gig-card-price {
            color: #4f46e5 !important;
        }
        .hire-category-title-icon,
        .hire-subcat-item:hover,
        .hire-subcat-item.active {
            border-color: rgba(79,70,229,.28);
        }
        .hire-category-section,
        .hire-results-panel {
            border-color: rgba(79,70,229,.16) !important;
            box-shadow: 0 18px 42px rgba(79,70,229,.10) !important;
        }
        .hire-subcat-item:hover {
            border-color: #4f46e5 !important;
            box-shadow: 0 18px 36px rgba(79,70,229,.18) !important;
        }
        @media (max-width: 767.98px) {
            .hire-freelancer-hero { margin-bottom: -26px; padding-bottom: 66px; }
            .hire-category-tabs-shell { padding: 9px; border-radius: 16px; }
            .hire-category-tabs-shell .hire-category-tabs { gap: 8px; min-height: 46px; }
            .hire-category-tabs-shell .hire-category-tab { padding: 7px 10px; font-size: 12px; }
        }
    
        /* Compact service marketplace cards */
        .hire-results-panel {
            padding: 22px !important;
        }
        .gig-card-grid {
            grid-template-columns: repeat(auto-fill, minmax(210px, 240px)) !important;
            justify-content: start;
            gap: 16px !important;
        }
        .gig-card {
            border-radius: 16px !important;
            min-height: 0 !important;
            max-width: 240px;
            background: #fff;
            box-shadow: 0 12px 28px rgba(15, 118, 110, .10) !important;
        }
        .gig-card:hover {
            transform: translateY(-4px) !important;
            border-color: rgba(79,70,229,.36) !important;
            box-shadow: 0 18px 36px rgba(15, 118, 110, .16) !important;
        }
        .gig-card::before {
            height: 4px !important;
            background: linear-gradient(90deg, #4f46e5, #4f46e5) !important;
            opacity: 1 !important;
        }
        .gig-card-cover-wrap {
            height: 112px !important;
            min-height: 112px !important;
            max-height: 112px !important;
            background: radial-gradient(circle at 18% 16%, rgba(249,115,22,.16), transparent 32%), linear-gradient(135deg, rgba(79,70,229,.10), rgba(79,70,229,.08)) !important;
        }
        .gig-card-cover {
            height: 112px !important;
            min-height: 112px !important;
            max-height: 112px !important;
            aspect-ratio: auto !important;
            object-fit: cover;
        }
        .gig-card-cover.d-flex i {
            font-size: 26px !important;
            color: rgba(79,70,229,.45) !important;
        }
        .gig-card-badge {
            top: 8px !important;
            left: 8px !important;
            padding: 4px 8px !important;
            font-size: 9px !important;
            min-height: 20px;
        }
        .gig-card-body {
            padding: 12px 14px 14px !important;
            gap: 7px;
        }
        .gig-card-seller {
            gap: 7px !important;
            margin-bottom: 0 !important;
        }
        .gig-card-avatar-wrap,
        .gig-card-avatar-wrap img {
            width: 30px !important;
            height: 30px !important;
        }
        .gig-card-seller-name {
            font-size: 11px !important;
            line-height: 1.15;
        }
        .gig-card-seller-status {
            font-size: 9px !important;
            line-height: 1.1;
        }
        .freelancer-online-dot {
            width: 10px !important;
            height: 10px !important;
            box-shadow: 0 0 0 2px rgba(34,197,94,.16) !important;
        }
        .gig-card-level {
            padding: 3px 6px !important;
            font-size: 9px !important;
            white-space: nowrap;
        }
        .gig-card-tags {
            gap: 5px !important;
            margin: 0 !important;
            max-height: 42px;
            overflow: hidden;
        }
        .gig-service-chip {
            padding: 4px 7px !important;
            font-size: 9px !important;
            max-width: 100%;
        }
        .gig-card-title {
            min-height: 34px !important;
            max-height: 38px;
            overflow: hidden;
            font-size: 13px !important;
            line-height: 1.28 !important;
            margin: 0 !important;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .gig-card-rating {
            margin: 0 !important;
            min-height: 18px;
            font-size: 11px !important;
        }
        .gig-card-rating .count {
            font-size: 10px !important;
        }
        .gig-card-footer {
            margin-top: 2px !important;
            padding-top: 10px !important;
            border-top: 1px solid rgba(79,70,229,.12) !important;
        }
        .gig-card-price-label {
            font-size: 9px !important;
            line-height: 1;
        }
        .gig-card-price {
            font-size: 15px !important;
            line-height: 1.15;
        }
        .gig-card-cta {
            width: 30px !important;
            height: 30px !important;
            font-size: 12px !important;
            background: #4f46e5 !important;
        }
        @media (max-width: 575.98px) {
            .gig-card-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 12px !important;
            }
            .gig-card { max-width: none; }
            .gig-card-cover-wrap,
            .gig-card-cover { height: 98px !important; min-height: 98px !important; max-height: 98px !important; }
            .gig-card-body { padding: 10px !important; }
            .gig-card-tags { max-height: 24px; }
            .gig-service-chip:nth-child(n+3) { display: none; }
        }
        @media (max-width: 380px) {
            .gig-card-grid { grid-template-columns: 1fr !important; }
        }
    
        /* Redesigned specialization tiles */
        .hire-category-section {
            position: relative;
            padding: 26px 28px 30px !important;
            border-radius: 22px !important;
            background: linear-gradient(180deg, #ffffff 0%, rgba(79,70,229,.035) 100%) !important;
            overflow: hidden;
        }
        .hire-category-section::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 5px;
            background: linear-gradient(90deg, #4f46e5, #14b8a6, #4f46e5);
        }
        .hire-category-title {
            margin-bottom: 22px !important;
            color: #4f46e5 !important;
            font-size: 24px !important;
            letter-spacing: 0;
        }
        .hire-category-title-icon {
            width: 48px !important;
            height: 48px !important;
            border-radius: 16px !important;
            background: rgba(79,70,229,.10) !important;
            color: #4f46e5 !important;
            box-shadow: 0 14px 30px rgba(79,70,229,.16);
        }
        .hire-category-title-icon i {
            color: #4f46e5 !important;
        }
        .hire-subcat-scroller {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)) !important;
            gap: 14px !important;
            padding: 0 !important;
            overflow: visible !important;
        }
        .hire-subcat-item {
            position: relative;
            min-height: 112px !important;
            width: 100% !important;
            padding: 16px 14px !important;
            border-radius: 18px !important;
            background: #fff !important;
            border: 1px solid rgba(79,70,229,.14) !important;
            box-shadow: 0 10px 22px rgba(15, 23, 42, .06) !important;
            flex-direction: row !important;
            justify-content: flex-start;
            text-align: left !important;
            gap: 13px !important;
            overflow: hidden;
        }
        .hire-subcat-item::after {
            content: '\f061';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%) translateX(8px);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(79,70,229,.10);
            color: #4f46e5;
            opacity: 0;
            transition: opacity .18s ease, transform .18s ease;
        }
        .hire-subcat-item:hover,
        .hire-subcat-item.active {
            transform: translateY(-4px) !important;
            background: linear-gradient(135deg, #ffffff, rgba(79,70,229,.08)) !important;
            border-color: rgba(79,70,229,.42) !important;
            box-shadow: 0 18px 34px rgba(79,70,229,.16) !important;
        }
        .hire-subcat-item:hover::after,
        .hire-subcat-item.active::after {
            opacity: 1;
            transform: translateY(-50%) translateX(0);
        }
        .hire-subcat-icon {
            width: 56px !important;
            height: 56px !important;
            flex: 0 0 56px;
            border-radius: 16px !important;
            background: linear-gradient(135deg, rgba(79,70,229,.12), rgba(79,70,229,.08)) !important;
            box-shadow: inset 0 0 0 5px rgba(255,255,255,.76), 0 8px 18px rgba(79,70,229,.12) !important;
        }
        .hire-subcat-icon i {
            color: #4f46e5 !important;
            font-size: 21px !important;
        }
        .hire-subcat-icon img {
            border-radius: 14px;
        }
        .hire-subcat-label {
            min-width: 0;
            padding-right: 28px;
            color: #0f172a !important;
            font-size: 13px !important;
            font-weight: 900 !important;
            line-height: 1.25 !important;
        }
        .hire-subcat-item:hover .hire-subcat-label,
        .hire-subcat-item.active .hire-subcat-label {
            color: #4f46e5 !important;
        }
        @media (max-width: 767.98px) {
            .hire-category-section { padding: 20px 16px !important; }
            .hire-category-title { font-size: 20px !important; }
            .hire-subcat-scroller { grid-template-columns: 1fr !important; gap: 10px !important; }
            .hire-subcat-item { min-height: 86px !important; padding: 13px !important; }
            .hire-subcat-icon { width: 48px !important; height: 48px !important; flex-basis: 48px; }
        }
    
        /* Final specialization alignment fix */
        .hire-category-section {
            max-width: 1160px;
            margin-left: auto;
            margin-right: auto;
            padding: 28px !important;
        }
        .hire-subcat-scroller {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)) !important;
            gap: 16px !important;
            align-items: stretch;
        }
        .hire-subcat-item {
            min-height: 132px !important;
            padding: 18px 12px 16px !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            gap: 12px !important;
            border-radius: 18px !important;
            background: linear-gradient(180deg, #fff, rgba(79,70,229,.04)) !important;
        }
        .hire-subcat-item::after {
            display: none !important;
        }
        .hire-subcat-icon {
            width: 58px !important;
            height: 58px !important;
            flex: 0 0 58px !important;
            margin: 0 !important;
            border-radius: 16px !important;
            background: linear-gradient(135deg, rgba(79,70,229,.14), rgba(20,184,166,.08)) !important;
        }
        .hire-subcat-icon img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 14px !important;
        }
        .hire-subcat-icon i {
            font-size: 22px !important;
            color: #4f46e5 !important;
        }
        .hire-subcat-label {
            display: block !important;
            width: 100% !important;
            max-width: 128px;
            padding: 0 !important;
            margin: 0 auto !important;
            color: #0f172a !important;
            font-size: 13px !important;
            font-weight: 900 !important;
            line-height: 1.22 !important;
            text-align: center !important;
            white-space: normal !important;
            word-break: normal !important;
            overflow-wrap: normal !important;
            hyphens: none !important;
        }
        .hire-subcat-item:hover .hire-subcat-label,
        .hire-subcat-item.active .hire-subcat-label {
            color: #4f46e5 !important;
        }
        @media (max-width: 767.98px) {
            .hire-category-section { padding: 20px 14px !important; }
            .hire-subcat-scroller { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 12px !important; }
            .hire-subcat-item { min-height: 120px !important; }
            .hire-subcat-label { max-width: 120px; font-size: 12px !important; }
        }
        @media (max-width: 420px) {
            .hire-subcat-scroller { grid-template-columns: 1fr !important; }
            .hire-subcat-item { min-height: 96px !important; flex-direction: row !important; justify-content: flex-start !important; text-align: left !important; padding: 14px !important; }
            .hire-subcat-label { max-width: none; text-align: left !important; margin: 0 !important; }
        }
    
        /* Distinct specialization colors and icons */
        .hire-subcat-item {
            border-color: color-mix(in srgb, var(--specialization-color, #4f46e5) 18%, #e2e8f0) !important;
            background: linear-gradient(180deg, #fff 0%, color-mix(in srgb, var(--specialization-bg, #f0fdfa) 42%, #fff) 100%) !important;
        }
        .hire-subcat-item:hover,
        .hire-subcat-item.active {
            border-color: color-mix(in srgb, var(--specialization-color, #4f46e5) 55%, #e2e8f0) !important;
            box-shadow: 0 18px 34px color-mix(in srgb, var(--specialization-color, #4f46e5) 18%, transparent) !important;
        }
        .hire-subcat-icon {
            background: var(--specialization-bg, #f0fdfa) !important;
            box-shadow: inset 0 0 0 5px rgba(255,255,255,.72), 0 10px 20px color-mix(in srgb, var(--specialization-color, #4f46e5) 18%, transparent) !important;
        }
        .hire-subcat-icon i {
            color: var(--specialization-color, #4f46e5) !important;
        }
        .hire-subcat-item:hover .hire-subcat-icon,
        .hire-subcat-item.active .hire-subcat-icon {
            background: var(--specialization-color, #4f46e5) !important;
        }
        .hire-subcat-item:hover .hire-subcat-icon i,
        .hire-subcat-item.active .hire-subcat-icon i {
            color: #fff !important;
        }
        .hire-subcat-item:hover .hire-subcat-label,
        .hire-subcat-item.active .hire-subcat-label {
            color: var(--specialization-color, #4f46e5) !important;
        }
    
        /* Premium violet accent polish */
        .hire-freelancer-hero {
            background: linear-gradient(120deg, #111827 0%, #312e81 48%, #4f46e5c7 100%) !important;
        }
        .hire-category-tabs-shell {
            border-color: rgba(79,70,229,.22) !important;
            box-shadow: 0 20px 48px rgba(109,40,217,.18), 0 8px 22px rgba(15,23,42,.08) !important;
        }
        .hire-category-scroll-btn:hover,
        .gig-card-cta {
            background: #4338ca !important;
            border-color: #4338ca !important;
        }
        .hire-category-section::before {
            background: linear-gradient(90deg, #4338ca, #ec4899, #f97316) !important;
        }
        .hire-category-title,
        .hire-breadcrumb span.current,
        .gig-card-price {
            color: #4338ca !important;
        }
        .hire-category-title-icon {
            background: rgba(79,70,229,.12) !important;
            box-shadow: 0 14px 30px rgba(109,40,217,.16) !important;
        }
        .hire-category-title-icon i {
            color: #4338ca !important;
        }
        .gig-card::before {
            background: linear-gradient(90deg, #4338ca, #ec4899) !important;
        }
        .gig-card-cover-wrap {
            background: radial-gradient(circle at 18% 16%, rgba(236,72,153,.16), transparent 32%), linear-gradient(135deg, rgba(79,70,229,.12), rgba(249,115,22,.08)) !important;
        }
    
        /* Premium royal indigo final palette */
        .hire-freelancer-hero {
            background: linear-gradient(120deg, #111827 0%, #312e81 48%, #4f46e5c7 100%) !important;
        }
        .hire-category-tabs-shell {
            border-color: rgba(79,70,229,.24) !important;
            box-shadow: 0 22px 54px rgba(49,46,129,.20), 0 8px 22px rgba(15,23,42,.08) !important;
        }
        .hire-category-section::before {
            background: linear-gradient(90deg, #4338ca, #06b6d4, #f59e0b) !important;
        }
        .hire-category-title,
        .hire-breadcrumb span.current,
        .gig-card-price {
            color: #4338ca !important;
        }
        .hire-category-scroll-btn:hover,
        .gig-card-cta {
            background: #4338ca !important;
            border-color: #4338ca !important;
        }
        .gig-card::before {
            background: linear-gradient(90deg, #4338ca, #06b6d4) !important;
        }
        .hire-category-title-icon {
            background: rgba(79,70,229,.12) !important;
            box-shadow: 0 14px 30px rgba(49,46,129,.16) !important;
        }
        .hire-category-title-icon i {
            color: #4338ca !important;
        }
        .gig-card-cover-wrap {
            background: radial-gradient(circle at 18% 16%, rgba(245,158,11,.16), transparent 32%), linear-gradient(135deg, rgba(79,70,229,.12), rgba(6,182,212,.08)) !important;
        }
    
        /* Fiverr-inspired professional marketplace palette */
        .hire-freelancer-page {
            background: #f7f7f7 !important;
        }
        .hire-freelancer-hero {
            background: linear-gradient(120deg, #0f172a 0%, #1f7a4d 58%, #f59e0b 120%) !important;
        }
        .hire-freelancer-heading,
        .hire-freelancer-subtitle {
            color: #fff !important;
        }
        .hire-category-tabs-shell {
            background: #fff !important;
            border: 1px solid #e4e5e7 !important;
            box-shadow: 0 18px 42px rgba(34,35,37,.12) !important;
        }
        .hire-category-tabs-shell .hire-category-tab {
            color: #404145 !important;
            border-color: #e4e5e7 !important;
            background: #fff !important;
            box-shadow: none !important;
        }
        .hire-category-tabs-shell .hire-category-tab i {
            color: #f59e0b !important;
            background: #effbf5 !important;
        }
        .hire-category-tabs-shell .hire-category-tab:hover,
        .hire-category-tabs-shell .hire-category-tab.active {
            color: #fff !important;
            background: #f59e0b !important;
            border-color: #f59e0b !important;
            box-shadow: 0 12px 26px rgba(245,158,11,.24) !important;
        }
        .hire-category-tabs-shell .hire-category-tab:hover i,
        .hire-category-tabs-shell .hire-category-tab.active i {
            color: #fff !important;
            background: rgba(255,255,255,.18) !important;
        }
        .hire-category-scroll-btn {
            color: #404145 !important;
            border-color: #dadbdd !important;
            background: #fff !important;
            box-shadow: 0 8px 18px rgba(34,35,37,.12) !important;
        }
        .hire-category-scroll-btn:hover {
            color: #fff !important;
            background: #f59e0b !important;
            border-color: #f59e0b !important;
        }
        .hire-breadcrumb {
            background: #fff !important;
            border-color: #e4e5e7 !important;
            color: #74767e !important;
        }
        .hire-breadcrumb span.current,
        .hire-breadcrumb a:hover,
        .hire-category-title,
        .gig-card-price {
            color: #f59e0b !important;
        }
        .hire-category-section,
        .hire-results-panel {
            background: #fff !important;
            border-color: #e4e5e7 !important;
            box-shadow: 0 14px 34px rgba(34,35,37,.08) !important;
        }
        .hire-category-section::before,
        .gig-card::before {
            background: #f59e0b !important;
        }
        .hire-category-title-icon,
        .hire-subcat-icon {
            background: #effbf5 !important;
            color: #f59e0b !important;
            box-shadow: none !important;
        }
        .hire-category-title-icon i,
        .hire-subcat-icon i {
            color: #f59e0b !important;
        }
        .hire-subcat-item {
            background: #fff !important;
            border-color: #e4e5e7 !important;
            box-shadow: 0 8px 22px rgba(34,35,37,.06) !important;
        }
        .hire-subcat-item:hover,
        .hire-subcat-item.active {
            background: #f6fff9 !important;
            border-color: #f59e0b !important;
            box-shadow: 0 14px 32px rgba(245,158,11,.16) !important;
        }
        .hire-subcat-item:hover .hire-subcat-icon,
        .hire-subcat-item.active .hire-subcat-icon {
            background: #f59e0b !important;
        }
        .hire-subcat-item:hover .hire-subcat-icon i,
        .hire-subcat-item.active .hire-subcat-icon i {
            color: #fff !important;
        }
        .hire-subcat-label,
        .gig-card-title,
        .gig-card-seller-name {
            color: #222325 !important;
        }
        .hire-subcat-item:hover .hire-subcat-label,
        .hire-subcat-item.active .hire-subcat-label {
            color: #f59e0b !important;
        }
        .filter-pill,
        .results-sort select,
        .hire-filter-btn {
            border-color: #dadbdd !important;
            color: #404145 !important;
            background: #fff !important;
        }
        .hire-filter-btn:hover,
        .hire-filter-btn.is-active {
            border-color: #f59e0b !important;
            color: #f59e0b !important;
        }
        .gig-card {
            background: #fff !important;
            border-color: #e4e5e7 !important;
            box-shadow: 0 10px 26px rgba(34,35,37,.08) !important;
        }
        .gig-card:hover {
            border-color: #f59e0b !important;
            box-shadow: 0 18px 38px rgba(34,35,37,.12) !important;
        }
        .gig-card-cover-wrap {
            background: linear-gradient(135deg, #f5f5f5, #effbf5) !important;
        }
        .gig-card-cta {
            background: #f59e0b !important;
            color: #fff !important;
        }
        .gig-card-level,
        .gig-service-chip {
            box-shadow: none !important;
        }
        .chip-category { background: #effbf5 !important; color: #d97706 !important; }
        .chip-specialization { background: #f5f5f5 !important; color: #404145 !important; }
        .chip-delivery { background: #eefbf7 !important; color: #f59e0b !important; }
    
        /* Upwork-inspired green accent refinement */
        .hire-freelancer-hero {
            background: linear-gradient(120deg, #111827 0%, #d97706 62%, #f59e0b 120%) !important;
        }
        .hire-category-tabs-shell .hire-category-tab:hover,
        .hire-category-tabs-shell .hire-category-tab.active,
        .hire-category-scroll-btn:hover,
        .gig-card-cta {
            background: #f59e0b !important;
            border-color: #f59e0b !important;
        }
        .hire-category-section::before,
        .gig-card::before,
        .gig-section-title::before {
            background: #f59e0b !important;
        }
        .hire-breadcrumb span.current,
        .hire-category-title,
        .gig-card-price,
        .hire-subcat-item:hover .hire-subcat-label,
        .hire-subcat-item.active .hire-subcat-label {
            color: #d97706 !important;
        }
        .hire-subcat-item:hover,
        .hire-subcat-item.active,
        .gig-card:hover {
            border-color: #f59e0b !important;
        }
    
        /* Final professional navy + amber palette */
        .hire-freelancer-hero {
            background: linear-gradient(120deg, #111827 0%, #1e293b 58%, #f59e0b 145%) !important;
        }
        .hire-category-tabs-shell {
            border-color: rgba(245,158,11,.24) !important;
            box-shadow: 0 20px 46px rgba(17,24,39,.16), 0 8px 22px rgba(245,158,11,.10) !important;
        }
        .hire-category-tabs-shell .hire-category-tab i,
        .hire-category-title-icon,
        .hire-subcat-icon {
            background: #fff7ed !important;
            color: #d97706 !important;
        }
        .hire-category-tabs-shell .hire-category-tab i,
        .hire-category-title-icon i,
        .hire-subcat-icon i {
            color: #d97706 !important;
        }
        .hire-category-tabs-shell .hire-category-tab:hover,
        .hire-category-tabs-shell .hire-category-tab.active,
        .hire-category-scroll-btn:hover,
        .gig-card-cta {
            background: #f59e0b !important;
            border-color: #f59e0b !important;
            color: #fff !important;
        }
        .hire-category-section::before,
        .gig-card::before {
            background: linear-gradient(90deg, #111827, #f59e0b) !important;
        }
        .hire-breadcrumb span.current,
        .hire-category-title,
        .gig-card-price,
        .hire-subcat-item:hover .hire-subcat-label,
        .hire-subcat-item.active .hire-subcat-label {
            color: #d97706 !important;
        }
        .hire-subcat-item:hover,
        .hire-subcat-item.active,
        .gig-card:hover {
            border-color: #f59e0b !important;
        }
        .hire-subcat-item:hover .hire-subcat-icon,
        .hire-subcat-item.active .hire-subcat-icon {
            background: #f59e0b !important;
        }
        .chip-category { background: #fff7ed !important; color: #b45309 !important; }
        .chip-delivery { background: #fffbeb !important; color: #d97706 !important; }
        .gig-card-cover-wrap {
            background: linear-gradient(135deg, #f8fafc, #fff7ed) !important;
        }
    </style>
@endpush

@section('content')
    <div class="hire-freelancer-page">
        <div class="hire-freelancer-hero">
            <div class="container">
                <h1 class="hire-freelancer-heading">{{ translate('hire_freelancer') }}</h1>
                <p class="hire-freelancer-subtitle">{{ translate('browse_freelancer_services_intro') }}</p>
            </div>
        </div>
        <div class="container">
            @if($categories->count())
                @php
                    $resolveTabIcon = function (string $name) {
                        $icons = [
                            'graphic' => 'fas fa-paint-brush', 'design' => 'fas fa-swatchbook',
                            'program' => 'fas fa-code', 'tech' => 'fas fa-laptop-code', 'develop' => 'fas fa-terminal',
                            'market' => 'fas fa-bullhorn', 'video' => 'fas fa-video', 'animat' => 'fas fa-film',
                            'writ' => 'fas fa-pen-nib', 'translat' => 'fas fa-language',
                            'music' => 'fas fa-music', 'audio' => 'fas fa-headphones', 'business' => 'fas fa-briefcase',
                        ];
                        $needle = strtolower($name);
                        foreach ($icons as $keyword => $icon) {
                            if (str_contains($needle, $keyword)) return $icon;
                        }
                        return 'fas fa-layer-group';
                    };
                @endphp
                <div class="hire-category-tabs-shell" data-category-tabs-shell>
                    <button type="button" class="hire-category-scroll-btn prev" data-category-scroll="prev" aria-label="Previous categories">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="hire-category-tabs-wrapper">
                        <nav class="hire-category-tabs" aria-label="{{ translate('freelancer_categories') }}" data-category-tabs>
                            <a class="hire-category-tab {{ !$selectedCategory ? 'active' : '' }}" href="{{ route('hire-freelancer') }}">
                                <i class="fas fa-th-large"></i>{{ translate('all_freelancer_services') }}
                            </a>
                            @foreach($categories as $category)
                                <a class="hire-category-tab {{ $selectedCategory?->id === $category->id ? 'active' : '' }}"
                                   href="{{ route('hire-freelancer', ['category' => $category->slug]) }}">
                                    <i class="{{ $resolveTabIcon($category->name) }}"></i>{{ $category->name }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                    <button type="button" class="hire-category-scroll-btn next" data-category-scroll="next" aria-label="Next categories">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                @if($selectedCategory)
                    <nav class="hire-breadcrumb" aria-label="breadcrumb">
                        <a href="{{ route('hire-freelancer') }}">{{ translate('home') }}</a>
                        <span>/</span>
                        @if($selectedSpecialization)
                            <a href="{{ route('hire-freelancer', ['category' => $selectedCategory->slug]) }}">{{ $selectedCategory->name }}</a>
                            <span>/</span>
                            <span class="current">{{ $selectedSpecialization->name }}</span>
                        @else
                            <span class="current">{{ $selectedCategory->name }}</span>
                        @endif
                    </nav>
                @endif

                @php
                    $hireCategoryStyles = [
                        'graphic'  => ['icon' => 'fas fa-paint-brush', 'bg' => '#fff1e8', 'color' => '#f97316'],
                        'design'   => ['icon' => 'fas fa-swatchbook', 'bg' => '#fff1e8', 'color' => '#f97316'],
                        'program'  => ['icon' => 'fas fa-code', 'bg' => '#eaf1ff', 'color' => '#4f46e5'],
                        'tech'     => ['icon' => 'fas fa-laptop-code', 'bg' => '#eaf1ff', 'color' => '#4f46e5'],
                        'develop'  => ['icon' => 'fas fa-terminal', 'bg' => '#eaf1ff', 'color' => '#4f46e5'],
                        'market'   => ['icon' => 'fas fa-bullhorn', 'bg' => '#ffeaf1', 'color' => '#e11d48'],
                        'video'    => ['icon' => 'fas fa-video', 'bg' => '#f3e8ff', 'color' => '#4338ca'],
                        'animat'   => ['icon' => 'fas fa-film', 'bg' => '#f3e8ff', 'color' => '#4338ca'],
                        'writ'     => ['icon' => 'fas fa-pen-nib', 'bg' => '#e9f9f0', 'color' => '#16834f'],
                        'translat' => ['icon' => 'fas fa-language', 'bg' => '#e9f9f0', 'color' => '#16834f'],
                        'music'    => ['icon' => 'fas fa-music', 'bg' => '#fff7db', 'color' => '#b7791f'],
                        'audio'    => ['icon' => 'fas fa-headphones', 'bg' => '#fff7db', 'color' => '#b7791f'],
                        'business' => ['icon' => 'fas fa-briefcase', 'bg' => '#eef2ff', 'color' => '#4f46e5'],
                    ];

                    $resolveHireCategoryStyle = function (string $name) use ($hireCategoryStyles) {
                        $needle = strtolower($name);
                        foreach ($hireCategoryStyles as $keyword => $style) {
                            if (str_contains($needle, $keyword)) {
                                return $style;
                            }
                        }
                        return ['icon' => 'fas fa-layer-group', 'bg' => '#eef2f7', 'color' => '#475569'];
                    };

                    $hireSpecializationStyles = [
                        'logo'        => ['icon' => 'fas fa-pen-fancy', 'bg' => '#fff7ed', 'color' => '#f97316'],
                        'brand'       => ['icon' => 'fas fa-copyright', 'bg' => '#eff6ff', 'color' => '#4f46e5'],
                        'business'    => ['icon' => 'fas fa-id-card', 'bg' => '#ecfdf5', 'color' => '#059669'],
                        'stationery'  => ['icon' => 'fas fa-address-card', 'bg' => '#ecfdf5', 'color' => '#059669'],
                        'illustration'=> ['icon' => 'fas fa-palette', 'bg' => '#fdf2f8', 'color' => '#db2777'],
                        'web'         => ['icon' => 'fas fa-desktop', 'bg' => '#eef2ff', 'color' => '#4f46e5'],
                        'app'         => ['icon' => 'fas fa-mobile-alt', 'bg' => '#eef2ff', 'color' => '#4f46e5'],
                        'packaging'   => ['icon' => 'fas fa-box-open', 'bg' => '#fefce8', 'color' => '#ca8a04'],
                        'label'       => ['icon' => 'fas fa-tags', 'bg' => '#fefce8', 'color' => '#ca8a04'],
                        'social'      => ['icon' => 'fas fa-share-alt', 'bg' => '#eff6ff', 'color' => '#4f46e5'],
                        'video'       => ['icon' => 'fas fa-video', 'bg' => '#f5f3ff', 'color' => '#4338ca'],
                        'writing'     => ['icon' => 'fas fa-feather-alt', 'bg' => '#f0fdfa', 'color' => '#4f46e5'],
                        'translation' => ['icon' => 'fas fa-language', 'bg' => '#f0fdfa', 'color' => '#4f46e5'],
                    ];

                    $resolveHireSpecializationStyle = function (string $name) use ($hireSpecializationStyles, $resolveHireCategoryStyle) {
                        $needle = strtolower($name);
                        foreach ($hireSpecializationStyles as $keyword => $style) {
                            if (str_contains($needle, $keyword)) {
                                return $style;
                            }
                        }
                        return $resolveHireCategoryStyle($name);
                    };
                @endphp

                @if(!$selectedCategory && !$selectedSpecialization)
                    <div class="hire-category-grid">
                        @foreach($displayCategories as $category)
                            @php
                                $categoryStyle = $resolveHireCategoryStyle($category->name);
                            @endphp
                            <a class="hire-category-card" href="{{ route('hire-freelancer', ['category' => $category->slug]) }}" id="freelancer-category-{{ $category->id }}">
                                <span class="hire-category-card-bg" style="background: radial-gradient(circle at top right, {{ $categoryStyle['color'] }}, transparent 58%);"></span>
                                <span class="hire-category-card-content">
                                    <span class="hire-category-card-icon" style="background: {{ $categoryStyle['bg'] }};">
                                        <i class="{{ $categoryStyle['icon'] }}" style="color: {{ $categoryStyle['color'] }};"></i>
                                    </span>
                                    <span class="hire-category-card-title">{{ $category->name }}</span>
                                    <span class="hire-category-card-count">{{ $category->activeSpecializations->count() }} {{ translate('specializations') }}</span>
                                    @if($category->activeSpecializations->count())
                                        <span class="hire-category-specializations">
                                            @foreach($category->activeSpecializations->take(6) as $specialization)
                                                <span class="hire-category-specialization-chip">{{ $specialization->name }}</span>
                                            @endforeach
                                        </span>
                                    @endif
                                    <span class="hire-category-card-action">{{ translate('explore_services') }} <i class="fas fa-arrow-right"></i></span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    @foreach($displayCategories as $category)
                        @php
                                $categoryStyle = $resolveHireCategoryStyle($category->name);
                            @endphp
                        <section class="hire-category-section" id="freelancer-category-{{ $category->id }}">
                            <h2 class="hire-category-title">
                                <span class="hire-category-title-icon" style="background: {{ $categoryStyle['bg'] }};">
                                    <i class="{{ $categoryStyle['icon'] }}" style="color: {{ $categoryStyle['color'] }};"></i>
                                </span>
                                {{ $category->name }}
                            </h2>

                            @if($category->activeSpecializations->count())
                                <div class="hire-subcat-scroller">
                                    @foreach($category->activeSpecializations as $specialization)
                                        @php
                                            $specializationStyle = $resolveHireSpecializationStyle($specialization->name);
                                        @endphp
                                        <a class="hire-subcat-item {{ $selectedSpecialization?->id === $specialization->id ? 'active' : '' }}"
                                           href="{{ route('hire-freelancer', ['specialization' => $specialization->slug]) }}"
                                           id="freelancer-specialization-{{ $specialization->id }}"
                                           style="--specialization-color: {{ $specializationStyle['color'] }}; --specialization-bg: {{ $specializationStyle['bg'] }};">
                                            <span class="hire-subcat-icon" style="background: {{ $specializationStyle['bg'] }};">
                                                @if(!empty($specialization->image) && $specialization->image !== 'def.png')
                                                    <img alt="{{ $specialization->name }}" src="{{ getStorageImages(path: $specialization->image_full_url, type: 'backend-profile') }}">
                                                @else
                                                    <i class="{{ $specializationStyle['icon'] }}" style="color: {{ $specializationStyle['color'] }};"></i>
                                                @endif
                                            </span>
                                            <span class="hire-subcat-label">{{ $specialization->name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">{{ translate('no_freelancer_specialization_found') }}</p>
                            @endif
                        </section>
                    @endforeach
                @endif

                @if($selectedCategory || $selectedSpecialization)
                    @php
                        $filterBaseParams = array_filter([
                            'category' => request('category'),
                            'specialization' => request('specialization'),
                            'sort' => $sort !== 'recent' ? $sort : null,
                            'min_price' => $minPrice,
                            'max_price' => $maxPrice,
                            'max_delivery_days' => $maxDeliveryDays,
                        ], fn ($value) => $value !== null && $value !== '' && $value !== []);

                        // Every filter panel below re-submits this same param set (minus its own
                        // key, which it supplies fresh) as hidden inputs, so applying one filter
                        // never silently drops the others that are already active.
                        $renderHiddenFilterInputs = function (array $except = []) use ($filterBaseParams) {
                            $html = '';
                            foreach ($filterBaseParams as $key => $value) {
                                if (in_array($key, $except, true)) {
                                    continue;
                                }
                                foreach ((array) $value as $item) {
                                    $name = is_array($value) ? $key . '[]' : $key;
                                    $html .= '<input type="hidden" name="' . e($name) . '" value="' . e($item) . '">';
                                }
                            }
                            return $html;
                        };

                        $deliveryOptions = [3 => 'up_to_3_days', 7 => 'up_to_7_days', 30 => 'up_to_30_days'];
                    @endphp
                    <section class="hire-category-section">
                        <div class="hire-filter-bar">
                            <div class="hire-filter-dropdown">
                                <button type="button" class="hire-filter-btn {{ ($minPrice !== null || $maxPrice !== null) ? 'is-active' : '' }}" data-filter-toggle="budget-filter">
                                    {{ translate('budget') }} <i class="tio-chevron-down"></i>
                                </button>
                                <div class="hire-filter-panel" id="budget-filter">
                                    <form method="get" novalidate>
                                        {!! $renderHiddenFilterInputs(['min_price', 'max_price']) !!}
                                        <div class="d-flex gap-2 mb-2">
                                            <input type="number" min="0" step="1" name="min_price" value="{{ $minPrice }}" class="form-control form-control-sm" placeholder="{{ translate('min') }}">
                                            <input type="number" min="0" step="1" name="max_price" value="{{ $maxPrice }}" class="form-control form-control-sm" placeholder="{{ translate('max') }}">
                                        </div>
                                        <div class="d-flex gap-2 mt-2">
                                            <a href="{{ route('hire-freelancer', collect($filterBaseParams)->except(['min_price', 'max_price'])->all()) }}" class="btn btn-outline-secondary btn-sm w-50">{{ translate('clear_all') }}</a>
                                            <button type="submit" class="btn btn--primary btn-sm w-50">{{ translate('apply') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="hire-filter-dropdown">
                                <button type="button" class="hire-filter-btn {{ $maxDeliveryDays ? 'is-active' : '' }}" data-filter-toggle="delivery-filter">
                                    {{ translate('delivery_time') }} <i class="tio-chevron-down"></i>
                                </button>
                                <div class="hire-filter-panel" id="delivery-filter">
                                    @foreach($deliveryOptions as $days => $label)
                                        <a href="{{ route('hire-freelancer', array_merge($filterBaseParams, ['max_delivery_days' => $days])) }}"
                                           class="hire-delivery-option {{ $maxDeliveryDays == $days ? 'active' : '' }}">
                                            {{ translate($label) }}
                                        </a>
                                    @endforeach
                                    <a href="{{ route('hire-freelancer', collect($filterBaseParams)->except('max_delivery_days')->all()) }}"
                                       class="hire-delivery-option {{ !$maxDeliveryDays ? 'active' : '' }}">
                                        {{ translate('anytime') }}
                                    </a>
                                </div>
                            </div>

                        </div>

                        <div class="results-toolbar">
                            <div class="results-count">{{ $services->total() }} {{ translate('results') }}</div>
                            <form method="get" class="results-sort" novalidate>
                                {!! $renderHiddenFilterInputs(['sort']) !!}
                                {{ translate('sort_by') }}:
                                <select name="sort" onchange="this.form.submit()">
                                    <option value="recent" {{ $sort === 'recent' ? 'selected' : '' }}>{{ translate('most_recent') }}</option>
                                    <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>{{ translate('highest_rated') }}</option>
                                    <option value="price_low" {{ $sort === 'price_low' ? 'selected' : '' }}>{{ translate('price_low_to_high') }}</option>
                                    <option value="price_high" {{ $sort === 'price_high' ? 'selected' : '' }}>{{ translate('price_high_to_low') }}</option>
                                </select>
                            </form>
                        </div>

                        @if($services->count())
                            <div class="gig-card-grid">
                                @foreach($services as $service)
                                    @php
                                        $seller = $service->seller;
                                        $sellerName = $seller?->shop?->name ?: trim(($seller?->f_name ?? '') . ' ' . ($seller?->l_name ?? ''));
                                        $cover = $service->images->first();
                                        $startingPrice = $service->enabledPackages->pluck('price')->filter()->min() ?? $service->price;
                                        $ratingAvg = round((float) ($seller?->freelancer_rating_avg ?? 0), 1);
                                        $ratingCount = (int) ($seller?->freelancer_rating_count ?? 0);
                                        $isNew = $service->created_at && $service->created_at->greaterThan(now()->subDays(14));
                                        $isPopular = $ratingCount >= 10 && $ratingAvg >= 4.5;
                                        $isSellerOnline = $seller && ($seller->status === 'approved') && (($seller->account_status ?? 'active') === 'active');
                                    @endphp
                                    <a class="gig-card" href="{{ route('hire-freelancer.service-show', $service->id) }}">
                                        <div class="gig-card-cover-wrap">
                                            @if($cover)
                                                <img class="gig-card-cover" alt="{{ $service->title }}" src="{{ getStorageImages(path: $cover->image_full_url, type: 'backend-profile') }}">
                                            @else
                                                <div class="gig-card-cover d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-image" style="font-size: 38px; color: #94a3b8;"></i>
                                                </div>
                                            @endif
                                            @if($isPopular)
                                                <span class="gig-card-badge badge-popular"><i class="tio-flash"></i>{{ translate('popular') }}</span>
                                            @elseif($isNew)
                                                <span class="gig-card-badge badge-new"><i class="tio-new-release"></i>{{ translate('new') }}</span>
                                            @endif
                                        </div>
                                        <div class="gig-card-body">
                                            <div class="gig-card-seller">
                                                <span class="gig-card-avatar-wrap" title="{{ $sellerName }}">
                                                    <img alt="" src="{{ getStorageImages(path: $seller?->image_full_url, type: 'backend-profile') }}">
                                                    @if($isSellerOnline)
                                                        <span class="freelancer-online-dot" title="Online now"></span>
                                                    @endif
                                                </span>
                                                <span class="gig-card-seller-copy">
                                                    <span class="gig-card-seller-name" title="{{ $sellerName }}">{{ $sellerName }}</span>
                                                    <span class="gig-card-seller-status {{ $isSellerOnline ? 'is-online' : '' }}">
                                                        <span></span>{{ $isSellerOnline ? 'Online now' : translate('available') }}
                                                    </span>
                                                </span>
                                                @if($seller)
                                                    <span class="gig-card-level">{{ $seller->freelancerLevelLabel() }}</span>
                                                @endif
                                            </div>
                                            <div class="gig-card-tags">
                                                @if($service->category?->defaultname)
                                                    <span class="gig-service-chip chip-category"><i class="fas fa-layer-group"></i>{{ $service->category->defaultname }}</span>
                                                @endif
                                                @if($service->specialization?->defaultname)
                                                    <span class="gig-service-chip chip-specialization"><i class="fas fa-bolt"></i>{{ $service->specialization->defaultname }}</span>
                                                @endif
                                                @if($service->delivery_time_days)
                                                    <span class="gig-service-chip chip-delivery"><i class="fas fa-clock"></i>{{ $service->delivery_time_days }} {{ translate('days') }}</span>
                                                @endif
                                            </div>
                                            <div class="gig-card-title">{{ $service->title }}</div>
                                            <div class="gig-card-rating">
                                                @if($ratingCount > 0)
                                                    <span class="stars"><i class="tio-star"></i></span> <strong>{{ $ratingAvg }}</strong>
                                                    <span class="count">({{ $ratingCount }})</span>
                                                @else
                                                    <span class="count">{{ translate('no_reviews_yet') }}</span>
                                                @endif
                                            </div>
                                            <div class="gig-card-footer">
                                                <div>
                                                    <div class="gig-card-price-label">{{ translate('from') }}</div>
                                                    <div class="gig-card-price">{{ $startingPrice !== null ? webCurrencyConverter($startingPrice) : translate('not_available') }}</div>
                                                </div>
                                                <span class="gig-card-cta"><i class="fas fa-arrow-right"></i></span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            {{ $services->links() }}
                        @else
                            <p class="hire-freelancer-subtitle mb-0">{{ translate('no_services_found_for_this_selection') }}</p>
                        @endif
                    </section>
                @endif
            @else
                <div class="hire-empty-state">{{ translate('no_freelancer_category_found') }}</div>
            @endif
        </div>
    </div>

    @push('script')
        <script>
            (function () {
                document.querySelectorAll('[data-filter-toggle]').forEach(function (button) {
                    var panel = document.getElementById(button.dataset.filterToggle);
                    if (!panel) return;
                    button.addEventListener('click', function (event) {
                        event.stopPropagation();
                        var wasOpen = panel.classList.contains('is-open');
                        document.querySelectorAll('.hire-filter-panel.is-open').forEach(function (open) {
                            open.classList.remove('is-open');
                        });
                        if (!wasOpen) panel.classList.add('is-open');
                    });
                    panel.addEventListener('click', function (event) { event.stopPropagation(); });
                });
                document.addEventListener('click', function () {
                    document.querySelectorAll('.hire-filter-panel.is-open').forEach(function (open) {
                        open.classList.remove('is-open');
                    });
                });

                document.querySelectorAll('[data-category-tabs-shell]').forEach(function (shell) {
                    var tabs = shell.querySelector('[data-category-tabs]');
                    var prev = shell.querySelector('[data-category-scroll="prev"]');
                    var next = shell.querySelector('[data-category-scroll="next"]');
                    if (!tabs || !prev || !next) return;

                    var updateButtons = function () {
                        var maxScroll = tabs.scrollWidth - tabs.clientWidth - 2;
                        prev.classList.toggle('is-disabled', tabs.scrollLeft <= 2);
                        next.classList.toggle('is-disabled', tabs.scrollLeft >= maxScroll);
                        shell.classList.toggle('is-scrollable', maxScroll > 2);
                    };

                    var moveTabs = function (direction) {
                        var distance = Math.max(220, Math.floor(tabs.clientWidth * 0.72));
                        tabs.scrollBy({ left: direction * distance, behavior: 'smooth' });
                    };

                    prev.addEventListener('click', function () { moveTabs(-1); });
                    next.addEventListener('click', function () { moveTabs(1); });
                    tabs.addEventListener('scroll', updateButtons, { passive: true });
                    window.addEventListener('resize', updateButtons);
                    updateButtons();

                    var active = tabs.querySelector('.hire-category-tab.active');
                    if (active) {
                        active.scrollIntoView({ inline: 'center', block: 'nearest' });
                        setTimeout(updateButtons, 250);
                    }
                });
            })();
        </script>
    @endpush
@endsection

































