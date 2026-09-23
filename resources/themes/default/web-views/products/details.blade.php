@extends('layouts.front-end.app')

@section('title', $product['name'])

@push('css_or_js')
    @include(VIEW_FILE_NAMES['product_seo_meta_content_partials'], ['metaContentData' => $product?->seoInfo, 'productDetails' => $product])
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/product-details.css') }}"/>
    <style>
        .product-premium-page {
            padding-bottom: 35px;
            background:
                radial-gradient(circle at 8% 4%, rgba(7, 59, 116, .08), transparent 24%),
                linear-gradient(180deg, #f8fbff 0%, #f3f7fb 100%);
        }
        .product-premium-page .product-main-shell {
            padding: 22px;
            border: 1px solid #e3ebf4;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 18px 48px rgba(20, 49, 78, .09);
        }
        .product-premium-page .cz-product-gallery {
            position: relative;
            padding: 14px;
            border: 1px solid #e8eff6;
            border-radius: 18px;
            background: linear-gradient(145deg, #fff, #f7faff);
        }
        .product-premium-page .cz-preview {
            position: relative;
            border-radius: 14px;
            background: #fff;
        }
        .product-premium-page .cz {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #e7eef6;
        }
        .product-premium-page .product-thumb-slider::before {
            content: "{{ translate('select_image') }}";
            display: block;
            margin-bottom: 8px;
            color: #52677d;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .55px;
            text-transform: uppercase;
        }
        .product-premium-page .product-preview-thumb {
            overflow: hidden;
            border-radius: 12px;
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }
        .product-premium-page .product-preview-thumb:hover,
        .product-premium-page .product-preview-thumb.active {
            transform: translateY(-2px);
            border-color: #0b579b;
            box-shadow: 0 8px 18px rgba(7, 59, 116, .13);
        }
        .product-premium-page .details {
            padding: 6px 8px;
        }
        .product-premium-page .wishList-pos-btn i {
            font-size: 16px;
        }
        .product-share-panel {
            position: absolute;
            top: 26px;
            right: 22px;
            z-index: 6;
            padding: 7px;
            border: 1px solid rgba(220, 230, 240, .9);
            border-radius: 14px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 12px 28px rgba(20, 49, 78, .15);
            backdrop-filter: blur(10px);
            opacity: 1;
            transform: translateX(0);
            transition: opacity .22s ease, transform .22s ease;
        }
        .product-share-panel::before {
            content: "{{ translate('share') }}";
            position: absolute;
            top: 9px;
            right: calc(100% + 8px);
            padding: 5px 9px;
            border-radius: 8px;
            color: #fff;
            background: #073b74;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .35px;
            text-transform: uppercase;
            opacity: 0;
            transform: translateX(5px);
            transition: opacity .18s ease, transform .18s ease;
        }
        .product-share-panel:hover::before {
            opacity: 1;
            transform: translateX(0);
        }
        .product-share-title {
            display: none;
        }
        .product-share-actions {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }
        .product-share-button {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dce6f0;
            border-radius: 11px;
            color: #073b74;
            background: #f6faff;
            transition: transform .18s ease, color .18s ease, background .18s ease, box-shadow .18s ease;
        }
        .product-share-button:hover {
            transform: translateY(-2px);
            color: #fff;
            background: #073b74;
            box-shadow: 0 8px 18px rgba(7, 59, 116, .2);
        }
        .product-share-button.share-whatsapp:hover { background: #168b4b; }
        .product-share-button.share-facebook:hover { background: #1877f2; }
        .product-share-button.share-linkedin:hover { background: #0a66c2; }
        .product-share-button.share-x:hover { background: #111827; }
        .product-share-copy-label {
            position: absolute;
            right: 0;
            bottom: -28px;
            display: none;
            width: max-content;
            padding: 4px 8px;
            border-radius: 8px;
            color: #fff;
            background: #087443;
            font-size: 10px;
            font-weight: 700;
        }
        .product-information-card {
            margin-top: 30px !important;
            padding: 20px !important;
            border: 1px solid #e3ebf4;
            border-radius: 20px !important;
            box-shadow: 0 14px 38px rgba(20, 49, 78, .08);
        }
        .product-information-tabs {
            gap: 10px;
            border: 0;
        }
        .product-information-tabs .nav-item {
            margin: 0;
        }
        .product-information-tabs .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin: 0;
            padding: 10px 15px !important;
            border: 1px solid #e1eaf3 !important;
            border-radius: 12px !important;
            color: #52677d !important;
            background: #f7faff;
            font-size: 13px !important;
            font-weight: 700 !important;
            transition: transform .18s ease, color .18s ease, background .18s ease, box-shadow .18s ease;
        }
        .product-information-tabs .nav-link:hover {
            transform: translateY(-2px);
            color: #073b74 !important;
            border-color: rgba(7, 59, 116, .25) !important;
        }
        .product-information-tabs .nav-link.active {
            color: #fff !important;
            border-color: #073b74 !important;
            background: linear-gradient(135deg, #073b74, #1684cf) !important;
            box-shadow: 0 8px 20px rgba(7, 59, 116, .2);
        }
        .product-information-card .tab-content {
            margin-top: 18px;
            padding: 0;
            border: 0;
            background: transparent;
        }
        .product-tab-panel {
            min-height: 170px;
            padding: 22px;
            border: 1px solid #e3ebf4;
            border-radius: 18px;
            background: linear-gradient(145deg, #fff 0%, #f5f9fd 100%);
        }
        .product-tab-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e7eef6;
        }
        .product-tab-heading-icon {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            color: #fff;
            background: linear-gradient(135deg, #073b74, #1684cf);
            box-shadow: 0 8px 18px rgba(7, 59, 116, .18);
            font-size: 19px;
        }
        .product-tab-heading h3 {
            margin: 0 0 3px;
            color: #172b43;
            font-size: 17px;
            font-weight: 800;
        }
        .product-tab-heading p {
            margin: 0;
            color: #728397;
            font-size: 12px;
        }
        .product-tab-content-card {
            padding: 4px 2px 0;
            border: 0;
            background: transparent;
            color: #53677d !important;
            font-size: 14px !important;
            line-height: 1.8;
        }
        .product-overview-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(260px, .75fr);
            gap: 20px;
            align-items: stretch;
        }
        .product-overview-copy {
            padding: 20px;
            border: 1px solid #e4edf5;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 22px rgba(20, 49, 78, .05);
        }
        .product-overview-kicker {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
            padding: 6px 10px;
            border-radius: 999px;
            color: #087443;
            background: #e9f8f1;
            font-size: 11px;
            font-weight: 800;
        }
        .product-overview-benefits {
            display: grid;
            gap: 10px;
        }
        .product-overview-benefit {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 70px;
            padding: 13px;
            border: 1px solid #e3ebf4;
            border-radius: 14px;
            background: rgba(255, 255, 255, .88);
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
        }
        .product-overview-benefit:hover {
            transform: translateX(3px);
            border-color: rgba(7, 59, 116, .28);
            box-shadow: 0 8px 18px rgba(20, 49, 78, .08);
        }
        .product-overview-benefit i {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: #fff;
            background: linear-gradient(135deg, #073b74, #1684cf);
            font-size: 17px;
        }
        .product-overview-benefit strong,
        .product-overview-benefit small {
            display: block;
        }
        .product-overview-benefit strong {
            margin-bottom: 2px;
            color: #172b43;
            font-size: 13px;
        }
        .product-overview-benefit small {
            color: #7d8da0;
            font-size: 11px;
        }
        .product-spec-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
        .product-spec-item {
            padding: 15px;
            border: 1px solid #e5edf5;
            border-radius: 13px;
            background: #fff;
        }
        .product-spec-item i {
            display: block;
            margin-bottom: 9px;
            color: #1684cf;
            font-size: 20px;
        }
        .product-spec-item small {
            display: block;
            margin-bottom: 3px;
            color: #8190a2;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .4px;
            text-transform: uppercase;
        }
        .product-spec-item strong {
            color: #172b43;
            font-size: 13px;
        }
        .similar-products-section {
            margin-top: 32px;
        }
        .similar-products-card {
            overflow: hidden;
            border: 1px solid #e9eef4 !important;
            border-radius: 22px !important;
            box-shadow: 0 18px 44px rgba(20, 49, 78, .07);
        }
        .similar-products-card .card-body {
            padding: 26px 26px 28px;
            background: linear-gradient(180deg, #fbfdff, #ffffff 140px);
        }
        .similar-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 22px;
            margin-top: 22px;
        }
        .similar-product-item {
            display: block;
            overflow: hidden;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(20, 49, 78, .07);
            transition: transform .28s cubic-bezier(.22,1,.36,1), box-shadow .28s ease, border-color .28s ease;
            border: 1px solid #edf2f8;
        }
        .similar-product-item:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 34px rgba(20, 49, 78, .14);
            border-color: #cfe3f7;
        }
        .similar-product-image {
            position: relative;
            overflow: hidden;
            aspect-ratio: 4 / 3;
            background: linear-gradient(145deg, #f4f8fc, #e9f1f9);
        }
        .similar-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .4s cubic-bezier(.22,1,.36,1);
        }
        .similar-product-item:hover .similar-product-image img {
            transform: scale(1.07);
        }
        .similar-product-body {
            padding: 15px 16px 16px;
        }
        .similar-product-body h3 {
            min-height: 38px;
            margin: 0 0 10px;
            color: #172b43;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.4;
        }
        .similar-product-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .similar-product-price {
            color: #f58b2a;
            font-size: 15px;
            font-weight: 800;
            background: #fff4e8;
            padding: 4px 10px;
            border-radius: 30px;
        }
        .similar-product-arrow {
            width: 28px;
            height: 28px;
            flex-shrink: 0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #1684cf;
            background: #eef6fd;
            opacity: 0;
            transform: translateX(-4px);
            transition: opacity .22s ease, transform .22s ease;
        }
        .similar-product-item:hover .similar-product-arrow {
            opacity: 1;
            transform: translateX(0);
        }
        .similar-products-heading {
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 18px !important;
        }
        .similar-products-heading-icon {
            width: 36px;
            height: 36px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, #073b74, #1684cf);
        }
        .similar-products-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 24px;
            padding: 0 8px;
            border-radius: 30px;
            background: #eef6fd;
            color: #1684cf;
            font-size: 12px;
            font-weight: 800;
        }
        @media (max-width: 767px) {
            .product-premium-page .product-main-shell {
                padding: 14px;
                border-radius: 16px;
            }
            .product-premium-page .details {
                padding: 16px 0 0;
            }
            .product-share-panel {
                top: 20px;
                right: 18px;
                opacity: 1;
                transform: none;
                pointer-events: auto;
            }
            .product-information-tabs {
                flex-wrap: nowrap;
                justify-content: flex-start !important;
                overflow-x: auto;
                padding-bottom: 5px;
            }
            .product-information-tabs .nav-link {
                white-space: nowrap;
            }
            .product-spec-grid {
                grid-template-columns: 1fr;
            }
            .product-overview-layout {
                grid-template-columns: 1fr;
            }
            .similar-products-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
            }
            .similar-product-image {
                aspect-ratio: 1 / 1;
            }
            .similar-product-price {
                font-size: 13px;
            }
        }
        .event-featured-badge {
            display: inline-block;
            background: #dcfce7;
            color: #14532d;
            border-radius: 20px;
            font-weight: 600;
        }
        .event-facts-card, .event-downloads-card {
            border: 1px solid #e3ebf4;
            border-radius: 14px;
            background: #fff;
            padding: 18px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 22px rgba(20, 83, 45, .05);
            transition: box-shadow .2s ease, transform .2s ease;
        }
        .event-facts-card::before, .event-downloads-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #14532d, #22c55e);
        }
        .event-facts-card:hover, .event-downloads-card:hover {
            box-shadow: 0 14px 30px rgba(20, 83, 45, .1);
            transform: translateY(-2px);
        }
        .event-facts-card h3, .event-downloads-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 14px;
        }
        .event-facts-list div {
            font-size: 13px;
            margin-bottom: 8px;
            color: #333;
        }
        .event-facts-list strong {
            color: #666;
            font-weight: 600;
        }
        .event-downloads-card .btn {
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            border-radius: 10px !important;
            font-weight: 700 !important;
            font-size: 14px !important;
            height: 44px;
            line-height: 1;
            margin-bottom: 10px;
            box-shadow: 0 2px 6px rgba(20, 49, 78, .06) !important;
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .event-downloads-card .btn:last-child {
            margin-bottom: 0;
        }
        .event-downloads-card .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(20, 49, 78, .12) !important;
        }
        .event-downloads-card .btn i {
            font-size: 16px;
        }
        .event-stat-card {
            border: 1px solid #d7f2e2 !important;
            background: #f6fdf9;
            border-radius: 10px !important;
            padding: 14px 8px !important;
        }
        .event-stat-card .event-stat-value {
            color: #14532d;
            font-size: 22px;
            line-height: 1.2;
        }
        .event-cta-group {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) !important;
            align-items: stretch !important;
            gap: 10px !important;
        }
        .event-cta-group .btn {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100% !important;
            margin: 0 !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            font-size: 15px !important;
            height: 48px;
            padding: 0 20px !important;
            line-height: 1;
            box-shadow: 0 2px 6px rgba(20, 49, 78, .06) !important;
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .event-cta-group .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(20, 49, 78, .12) !important;
        }
        .event-cta-group .btn i,
        .event-downloads-card .btn i,
        .hero-cta-group .btn i {
            font-size: 16px;
        }
        .btn-event-primary {
            background-color: #14532d !important;
            border-color: #14532d !important;
            color: #fff !important;
        }
        .btn-event-primary:hover,
        .btn-event-primary:focus {
            background-color: #0e3d21 !important;
            border-color: #0e3d21 !important;
            color: #fff !important;
        }
        .btn-event-outline {
            background-color: #f6fdf9 !important;
            border: 1px solid #15803d !important;
            color: #15803d !important;
        }
        .btn-event-outline:hover,
        .btn-event-outline:focus {
            background-color: #15803d !important;
            border-color: #15803d !important;
            color: #fff !important;
        }
        .btn-event-sponsor {
            background-color: #7c3aed !important;
            border-color: #7c3aed !important;
            color: #fff !important;
        }
        .btn-event-sponsor:hover,
        .btn-event-sponsor:focus {
            background-color: #6b21e0 !important;
            border-color: #6b21e0 !important;
            color: #fff !important;
        }
        /* Broker pages get their own navy/blue finance palette instead of the
           event green, so CTAs read as "trusted broker" rather than "event". */
        .btn-broker-primary {
            background: linear-gradient(135deg, #073b74, #1684cf) !important;
            border-color: #073b74 !important;
            color: #fff !important;
        }
        .btn-broker-primary:hover,
        .btn-broker-primary:focus {
            background: linear-gradient(135deg, #052a54, #0f6bab) !important;
            border-color: #052a54 !important;
            color: #fff !important;
        }
        .btn-broker-outline {
            background-color: #f4f9fd !important;
            border: 1px solid #1684cf !important;
            color: #0b4d8c !important;
        }
        .btn-broker-outline:hover,
        .btn-broker-outline:focus {
            background-color: #1684cf !important;
            border-color: #1684cf !important;
            color: #fff !important;
        }
        .broker-facts-card, .broker-downloads-card {
            border: 1px solid #dbe8f5;
            border-radius: 14px;
            background: #fff;
            padding: 18px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 22px rgba(7, 59, 116, .06);
            transition: box-shadow .2s ease, transform .2s ease;
        }
        .broker-facts-card::before, .broker-downloads-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #073b74, #1684cf);
        }
        .broker-facts-card:hover, .broker-downloads-card:hover {
            box-shadow: 0 14px 30px rgba(7, 59, 116, .12);
            transform: translateY(-2px);
        }
        .broker-facts-card h3, .broker-downloads-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 14px;
            color: #0b2b52;
        }
        .broker-stat-value { color: #073b74 !important; }
        .broker-verified-badge {
            background: #e5f1fc !important;
            color: #0b4d8c !important;
        }
        .broker-risk-card {
            border: 1px solid #fde7c7;
            background: #fffaf0;
            border-radius: 14px;
            padding: 16px 18px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }
        .broker-risk-card i {
            color: #b45309;
            font-size: 20px;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .broker-risk-card h3 {
            font-size: 13px;
            font-weight: 800;
            color: #92400e;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .broker-risk-card p {
            font-size: 12px;
            color: #78716c;
            line-height: 1.6;
        }
        .event-tabs {
            border-bottom: 1px solid #e9ecef !important;
            gap: 4px !important;
        }
        .event-tabs .nav-item {
            margin: 0 !important;
        }
        .event-tabs .tab_link,
        ul.event-tabs li.nav-item .tab_link {
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #6b7280 !important;
            padding: 10px 14px !important;
            border: none !important;
            border-bottom: 2px solid transparent !important;
            border-radius: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            margin-bottom: -1px;
            transform: none !important;
        }
        .event-tabs .tab_link.active,
        ul.event-tabs li.nav-item .tab_link.active {
            color: #14532d !important;
            font-weight: 700 !important;
            border-bottom: 2px solid #14532d !important;
            background: transparent !important;
        }
        .event-about-panel {
            padding: 4px 0;
        }
        .event-about-heading {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .event-about-text {
            font-size: 14px;
            line-height: 1.7;
            color: #4b5563;
        }
        .hero-logo-box {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 18px;
            background: linear-gradient(135deg, #0e3d21, #14532d);
            box-shadow: 0 16px 36px rgba(20, 49, 78, .14), 0 0 0 1px rgba(20, 49, 78, .04);
            transition: box-shadow .25s ease, transform .25s ease;
        }
        .hero-logo-box.hero-logo-event {
            aspect-ratio: 16 / 10;
        }
        .hero-logo-box.hero-logo-broker {
            background: linear-gradient(135deg, #073b74, #1684cf);
            box-shadow: 0 16px 36px rgba(7, 59, 116, .16), 0 0 0 1px rgba(7, 59, 116, .05);
        }
        .hero-logo-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 22px 44px rgba(20, 49, 78, .18), 0 0 0 1px rgba(20, 49, 78, .05);
        }
        .hero-logo-box.hero-logo-broker:hover {
            box-shadow: 0 22px 44px rgba(7, 59, 116, .22), 0 0 0 1px rgba(7, 59, 116, .06);
        }
        .hero-logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #fff;
            padding: 10px;
        }
        .hero-logo-placeholder {
            padding: 14px;
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .hero-thumb-strip {
            margin-top: 10px;
        }
        .hero-thumb-strip-label {
            display: block;
            margin-bottom: 8px;
            color: #52677d;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .55px;
            text-transform: uppercase;
        }
        .hero-thumb-strip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .hero-thumb {
            width: 52px;
            height: 52px;
            padding: 0;
            overflow: hidden;
            border: 2px solid #e3ebf4;
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
        }
        .hero-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .hero-thumb:hover {
            transform: translateY(-2px);
            border-color: #14532d;
        }
        .hero-thumb.active {
            border-color: #14532d;
            box-shadow: 0 6px 14px rgba(20, 83, 45, .2);
        }
        .hero-verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
            padding: 5px 12px;
            border-radius: 20px;
            color: #0b4d8c !important;
            background: #e5f1fc;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .4px;
            text-transform: uppercase;
        }
        .hero-tagline {
            margin-bottom: 12px;
            color: #4b5563;
            font-size: 14px;
            line-height: 1.6;
        }
        .hero-inline-rating {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .hero-inline-rating .star-rating {
            font-size: 15px;
        }
        .hero-inline-rating-score {
            color: #172b43;
            font-size: 14px;
            font-weight: 800;
        }
        .hero-inline-rating-count {
            color: #728397;
            font-size: 13px;
        }
        .overview-checklist {
            display: grid;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .overview-checklist li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: #374151;
            font-size: 14px;
        }
        .overview-checklist li i {
            margin-top: 2px;
            color: #15803d;
            font-size: 16px;
        }
        .broker-overview-checklist li i {
            color: #1684cf;
        }
        .key-facts-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #eef2f6;
            font-size: 13px;
        }
        .key-facts-row:last-child {
            border-bottom: 0;
        }
        .key-facts-row span:first-child {
            color: #6b7280;
            font-weight: 600;
        }
        .key-facts-row span:last-child {
            color: #172b43;
            font-weight: 700;
            text-align: right;
        }
        .review-write-card {
            padding: 22px;
            border: 1px solid #e3ebf4;
            border-radius: 16px;
            background: linear-gradient(145deg, #fff 0%, #f5f9fd 100%);
            box-shadow: 0 8px 22px rgba(20, 49, 78, .05);
            margin-bottom: 24px;
        }
        .review-write-card h3 {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
            color: #172b43;
            font-size: 16px;
            font-weight: 800;
        }
        .review-write-card h3 i {
            color: #14532d;
        }
        .review-write-subtitle {
            margin-bottom: 16px;
            color: #728397;
            font-size: 12px;
        }
        .review-write-card .star-wrap {
            margin: 0 0 16px;
        }
        .review-write-card .star-label {
            width: 2.4rem;
            height: 2.4rem;
        }
        .review-write-card .star-shape {
            background-color: #14532d;
        }
        .review-write-card .review-comment-box {
            border-radius: 10px !important;
            border: 1px solid #e1eaf3 !important;
            resize: vertical;
        }
        .review-write-card .review-comment-box:focus {
            border-color: #14532d !important;
            box-shadow: 0 0 0 .15rem rgba(20, 83, 45, .12) !important;
        }
        .review-write-card .btn {
            margin-top: 14px;
        }
        .review-login-cta {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .review-login-cta .btn {
            width: auto !important;
        }
        .review-empty-state {
            padding: 34px 15px;
            text-align: center;
            border: 1px dashed #dce6f0;
            border-radius: 16px;
            background: #fbfdff;
        }
        .review-list-card {
            padding: 4px 0;
        }
    </style>
@endpush
@php
    $embedUrl = str_replace(
        ['watch?v=', 'youtu.be/', 'shorts/'],
        ['embed/', 'embed/', 'embed/'],
        $product->video_url ?? ''
    );
    $shareUrl = url()->current();
    $shareTitle = $product->name;
    $encodedShareUrl = rawurlencode($shareUrl);
    $encodedShareText = rawurlencode($shareTitle . ' - ' . $shareUrl);
    $hasProductSidebar = false;
@endphp
@section('content')
    <div class="__inline-23 product-premium-page">
        {{-- {{ dd($product) }} --}}
        <div class="container mt-4 rtl text-align-direction">
            <div class="row {{Session::get('direction') === "rtl" ? '__dir-rtl' : ''}}">
                <div class="{{ in_array($product->product_type, ['event', 'broker']) ? 'col-lg-8' : ($hasProductSidebar ? 'col-lg-9' : 'col-12') }}">
                    <div class="row product-main-shell">
                        <div class="col-lg-5 col-md-4">
                            @if(in_array($product->product_type, ['broker', 'event']))
                                <div class="hero-logo-box {{ $product->product_type == 'event' ? 'hero-logo-event' : 'hero-logo-broker' }}">
                                    <button type="button" data-product-id="{{$product['id']}}"
                                            class="btn __text-18px border wishList-pos-btn product-action-add-wishlist">
                                        <i class="{{($wishlistStatus == 1 ? 'fas' : 'far')}} fa-heart wishlist_icon_{{$product['id']}} web-text-primary"
                                           aria-hidden="true"></i>
                                        <div class="wishlist-tooltip" x-placement="top">
                                            <div class="arrow"></div><div class="inner">
                                                <span class="add">{{translate('added_to_wishlist')}}</span>
                                                <span class="remove">{{translate('removed_from_wishlist')}}</span>
                                            </div>
                                        </div>
                                    </button>
                                    @if(isset($product->images_full_url[0]))
                                        <img id="heroLogoImage" src="{{ getStorageImages($product->images_full_url[0], type: 'product') }}" alt="{{ $product->name }}">
                                    @else
                                        <div class="hero-logo-placeholder">{{ $product->name }}</div>
                                    @endif
                                </div>
                                @if(count($product->images_full_url ?? []) > 0)
                                    <div class="hero-thumb-strip">
                                        <span class="hero-thumb-strip-label">{{ translate('select_image') }}</span>
                                        <div class="hero-thumb-strip-row">
                                            @foreach($product->images_full_url as $key => $photo)
                                                <button type="button"
                                                        class="hero-thumb {{ $key == 0 ? 'active' : '' }}"
                                                        onclick="document.getElementById('heroLogoImage').src='{{ getStorageImages($photo, type: 'product') }}'; document.querySelectorAll('.hero-thumb').forEach(function(el){ el.classList.remove('active'); }); this.classList.add('active');">
                                                    <img src="{{ getStorageImages($photo, type: 'product') }}" alt="{{ $product->name }}">
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <div class="product-share-panel">
                                    <div class="product-share-title mb-2"><i class="tio-share-vs me-1"></i>{{ translate('share_product') }}</div>
                                    <div class="product-share-actions">
                                        <a class="product-share-button share-facebook" href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedShareUrl }}" target="_blank" rel="noopener noreferrer" title="Facebook">
                                            <i class="tio-facebook"></i>
                                        </a>
                                        <a class="product-share-button share-x" href="https://twitter.com/intent/tweet?url={{ $encodedShareUrl }}&text={{ rawurlencode($shareTitle) }}" target="_blank" rel="noopener noreferrer" title="X">
                                            <i class="tio-twitter"></i>
                                        </a>
                                        <a class="product-share-button share-whatsapp" href="https://wa.me/?text={{ $encodedShareText }}" target="_blank" rel="noopener noreferrer" title="WhatsApp">
                                            <i class="tio-whatsapp"></i>
                                        </a>
                                        <a class="product-share-button share-linkedin" href="https://www.linkedin.com/sharing/share-offsite/?url={{ $encodedShareUrl }}" target="_blank" rel="noopener noreferrer" title="LinkedIn">
                                            <i class="tio-linkedin"></i>
                                        </a>
                                        <button type="button" class="product-share-button product-native-share" data-url="{{ $shareUrl }}" data-title="{{ $shareTitle }}" title="{{ translate('share') }}">
                                            <i class="tio-share-vs"></i>
                                        </button>
                                        <button type="button" class="product-share-button product-copy-link" data-url="{{ $shareUrl }}" title="{{ translate('copy_link') }}">
                                            <i class="tio-link"></i>
                                        </button>
                                    </div>
                                    <span class="product-share-copy-label mt-2">{{ translate('link_copied') }}</span>
                                </div>
                            @else
                                <div class="cz-product-gallery">
                                    <div class="cz-preview" style="height:280px;">
                                        <div id="sync1" class="owl-carousel owl-theme product-thumbnail-slider">
                                            @if($product->images!=null && json_decode($product->images)>0)
                                                @if(json_decode($product->colors) && count($product->color_images_full_url)>0)
                                                    @foreach ($product->color_images_full_url as $key => $photo)
                                                        @if($photo['color'] != null)
                                                            <div
                                                                class="product-preview-item d-flex align-items-center justify-content-center {{$key==0?'active':''}}"
                                                                id="image{{$photo['color']}}">
                                                                <img class="cz-image-zoom img-responsive w-100"
                                                                    src="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}"
                                                                    data-zoom="{{ getStorageImages(path: $photo['image_name'], type: 'product')  }}"
                                                                    alt="{{ translate('product') }}" width="" style="max-width: 100%;max-height: 200px;object-fit: contain;">
                                                                <div class="cz-image-zoom-pane"></div>
                                                            </div>
                                                        @else
                                                            <div
                                                                class="product-preview-item d-flex align-items-center justify-content-center {{$key==0?'active':''}}"
                                                                id="image{{$key}}">
                                                                <img class="cz-image-zoom img-responsive w-100"
                                                                    src="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}"
                                                                    data-zoom="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}"
                                                                    alt="{{ translate('product') }}" width="" style="max-width: 100%;max-height: 200px;object-fit: contain;" >
                                                                <div class="cz-image-zoom-pane"></div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    @foreach ($product->images_full_url as $key => $photo)
                                                        <div
                                                            class="product-preview-item d-flex align-items-center justify-content-center {{$key==0?'active':''}}"
                                                            id="image{{$key}}">
                                                            <img class="cz-image-zoom img-responsive w-100"
                                                                src="{{ getStorageImages($photo, type: 'product') }}"
                                                                data-zoom="{{ getStorageImages(path: $photo, type: 'product') }}"
                                                                alt="{{ translate('product') }}" width="" style="max-width: 100%;max-height: 200px;object-fit: contain;" >
                                                            <div class="cz-image-zoom-pane"></div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            @endif
                                        </div>

                                    </div>

                                    <div>
                                        <button type="button" data-product-id="{{$product['id']}}"
                                                class="btn __text-18px border wishList-pos-btn product-action-add-wishlist">
                                            <i class="{{($wishlistStatus == 1 ? 'fas' : 'far')}} fa-heart wishlist_icon_{{$product['id']}} web-text-primary"
                                            aria-hidden="true"></i>
                                            <div class="wishlist-tooltip" x-placement="top">
                                                <div class="arrow"></div><div class="inner">
                                                    <span class="add">{{translate('added_to_wishlist')}}</span>
                                                    <span class="remove">{{translate('removed_from_wishlist')}}</span>
                                                </div>
                                            </div>
                                        </button>

                                        <div class="product-share-panel">
                                            <div class="product-share-title mb-2"><i class="tio-share-vs me-1"></i>{{ translate('share_product') }}</div>
                                            <div class="product-share-actions">
                                                <a class="product-share-button share-facebook" href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedShareUrl }}" target="_blank" rel="noopener noreferrer" title="Facebook">
                                                    <i class="tio-facebook"></i>
                                                </a>
                                                <a class="product-share-button share-x" href="https://twitter.com/intent/tweet?url={{ $encodedShareUrl }}&text={{ rawurlencode($shareTitle) }}" target="_blank" rel="noopener noreferrer" title="X">
                                                    <i class="tio-twitter"></i>
                                                </a>
                                                <a class="product-share-button share-whatsapp" href="https://wa.me/?text={{ $encodedShareText }}" target="_blank" rel="noopener noreferrer" title="WhatsApp">
                                                    <i class="tio-whatsapp"></i>
                                                </a>
                                                <a class="product-share-button share-linkedin" href="https://www.linkedin.com/sharing/share-offsite/?url={{ $encodedShareUrl }}" target="_blank" rel="noopener noreferrer" title="LinkedIn">
                                                    <i class="tio-linkedin"></i>
                                                </a>
                                                <button type="button" class="product-share-button product-native-share" data-url="{{ $shareUrl }}" data-title="{{ $shareTitle }}" title="{{ translate('share') }}">
                                                    <i class="tio-share-vs"></i>
                                                </button>
                                                <button type="button" class="product-share-button product-copy-link" data-url="{{ $shareUrl }}" title="{{ translate('copy_link') }}">
                                                    <i class="tio-link"></i>
                                                </button>
                                            </div>
                                            <span class="product-share-copy-label mt-2">{{ translate('link_copied') }}</span>
                                        </div>
                                    </div>

                                    <div class="cz">
                                        <div class="table-responsive __max-h-515px" data-simplebar>
                                            <div class="d-flex">
                                                <div id="sync2" class="owl-carousel owl-theme product-thumb-slider">
                                                    @if($product->images!=null && json_decode($product->images)>0)
                                                        @if(json_decode($product->colors) && count($product->color_images_full_url)>0)
                                                            @foreach ($product->color_images_full_url as $key => $photo)
                                                                @if($photo['color'] != null)
                                                                    <div class="">
                                                                        <a class="product-preview-thumb color-variants-preview-box-{{ $photo['color'] }} {{$key==0?'active':''}} d-flex align-items-center justify-content-center"
                                                                        id="preview-img{{$photo['color']}}"
                                                                        href="#image{{$photo['color']}}">
                                                                            <img alt="{{ translate('product') }}"
                                                                                src="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}" style="max-width: 100%;max-height: 200px;object-fit: contain;">
                                                                        </a>
                                                                    </div>
                                                                @else
                                                                    <div class="">
                                                                        <a class="product-preview-thumb {{$key==0?'active':''}} d-flex align-items-center justify-content-center"
                                                                        id="preview-img{{$key}}" href="#image{{$key}}">
                                                                            <img alt="{{ translate('product') }}"
                                                                                src="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}" style="max-width: 100%;max-height: 200px;object-fit: contain;"  >
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            @foreach ($product->images_full_url as $key => $photo)
                                                                <div class="">
                                                                    <a class="product-preview-thumb {{$key==0?'active':''}} d-flex align-items-center justify-content-center"
                                                                    id="preview-img{{$key}}" href="#image{{$key}}">
                                                                        <img alt="{{ translate('product') }}"
                                                                            src="{{ getStorageImages(path: $photo, type: 'product') }}">
                                                                    </a>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-lg-7 col-md-8 mt-md-0 mt-sm-3 web-direction">
                            <div class="details __h-100 product-cart-option-container">
                                @if($product->product_type == 'broker')
                                    <div class="hero-verified-badge"><i class="tio-verified-outlined"></i>{{ translate('verification_status_from_crm') }}</div>
                                @endif
                                <h2 class="mb-2 __inline-24">{{ $product->name }}</h2>
                                @if($product->product_type == 'broker')
                                    <p class="hero-tagline">{{ translate('broker_page_tagline') }}</p>
                                @endif
                                @if(in_array($product->product_type, ['broker', 'event']))
                                    <div class="hero-inline-rating mb-3">
                                        <div class="star-rating">
                                            @for($inc=1;$inc<=5;$inc++)
                                                @if ($inc <= (int)$overallRating[0])
                                                    <i class="tio-star text-warning"></i>
                                                @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0] > ((int)$overallRating[0]))
                                                    <i class="tio-star-half text-warning"></i>
                                                @else
                                                    <i class="tio-star-outlined text-warning"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <span class="hero-inline-rating-score">{{ $overallRating[0] }}</span>
                                        <span class="hero-inline-rating-count">({{ $overallRating[1] }} {{ translate('reviews') }})</span>
                                    </div>
                                @endif
                                @unless(in_array($product->product_type, ['broker', 'event']))
                                <div class="event-facts-card product-rating-card mb-3">
                                    <h3>{{ translate('finxcart_rating') }}</h3>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fs-24 font-bold event-stat-value">{{ $overallRating[0] }}</span>
                                        <div class="star-rating">
                                            @for($inc=1;$inc<=5;$inc++)
                                                @if ($inc <= (int)$overallRating[0])
                                                    <i class="tio-star text-warning"></i>
                                                @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0] > ((int)$overallRating[0]))
                                                    <i class="tio-star-half text-warning"></i>
                                                @else
                                                    <i class="tio-star-outlined text-warning"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                    <div class="fs-12 text-muted mt-1">
                                        {{ $overallRating[1] }} {{ translate('reviews') }}
                                        <span class="mx-1">&middot;</span>
                                        {{ $countOrder }} {{ translate('orders') }}
                                        <span class="mx-1">&middot;</span>
                                        <span class="countWishlist-{{ $product->id }}">{{ $countWishlist }}</span> {{ translate('wish_listed') }}
                                    </div>
                                </div>
                                <div><img class="img-fluid m-auto max-height-bfx" src="{{ asset('public/assets/img/bfx_logo_finx.png') }}" alt="Bridgingfx Logo">
                                   <br> <span class="font-size-12 text-gray-5">{{ translate('Added_By_BridgingFX') }}</span>
                                </div>
                                @endunless

                                @if($product['product_type'] == 'digital')
                                    <div class="digital-product-authors mb-2">
                                        @if(count($productPublishingHouseInfo['data']) > 0)
                                            <div class="d-flex align-items-center g-2 me-2">
                                                <span class="text-capitalize digital-product-author-title">{{ translate('Publishing_House') }} :</span>
                                                <div class="item-list">
                                                    @foreach($productPublishingHouseInfo['data'] as $publishingHouseName)
                                                        <a href="{{ route('products', ['publishing_house_id' => $publishingHouseName['id'], 'product_type' => 'digital', 'page'=>1]) }}"
                                                           class="text-base">
                                                            {{ $publishingHouseName['name'] }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if(count($productAuthorsInfo['data']) > 0)
                                            <div class="d-flex align-items-center g-2 me-2">
                                                <span class="text-capitalize digital-product-author-title">{{ translate('Author') }} :</span>
                                                <div class="item-list">
                                                    @foreach($productAuthorsInfo['data'] as $productAuthor)
                                                        <a href="{{ route('products',['author_id' => $productAuthor['id'], 'product_type' => 'digital', 'page' => 1]) }}"
                                                           class="text-base">
                                                            {{ $productAuthor['name'] }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                {{-- Delivery type info --}}
                                @if($product['product_type'] == 'digital')
                                    <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge badge-soft-primary px-3 py-2 fs-12">
                                            <i class="tio-cloud-outlined me-1"></i>{{ translate('digital_product') }}
                                        </span>
                                        @if($product['digital_product_type'] == 'ready_product')
                                            <span class="text-muted fs-12">
                                                <i class="tio-bolt me-1 text-success"></i>{{ translate('instant_download_after_payment') }}
                                            </span>
                                        @else
                                            <span class="text-muted fs-12">
                                                <i class="tio-time me-1 text-warning"></i>{{ translate('file_sent_after_order_processed') }}
                                            </span>
                                        @endif
                                    </div>
                                @elseif($product['product_type'] == 'event')
                                    <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
                                        @if($product->featured_status == 1)
                                            <span class="event-featured-badge px-3 py-1 fs-12">{{ translate('featured_event') }}</span>
                                        @endif
                                        <span class="badge badge-soft-primary px-3 py-2 fs-12">
                                            <i class="tio-calendar me-1"></i>{{ translate('event') }}
                                        </span>
                                    </div>
                                @elseif($product['product_type'] == 'broker')
                                    <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge badge-soft-primary px-3 py-2 fs-12">
                                            <i class="tio-verified me-1"></i>{{ translate('broker') }}
                                        </span>
                                    </div>
                                @else
                                    <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge badge-soft-success px-3 py-2 fs-12">
                                            <i class="tio-shopping-cart-outlined me-1"></i>{{ translate('physical_product') }}
                                        </span>
                                        <span class="text-muted fs-12">
                                            <i class="tio-delivery me-1"></i>{{ translate('shipped_to_your_address') }}
                                        </span>
                                    </div>
                                @endif

                                @php
                                    $minimumOrderQty = max((int)($product->minimum_order_qty ?? 1), 1);
                                    $stockUnderMinimum = $product['product_type'] == 'physical' && $product->current_stock < $minimumOrderQty && $product->current_stock > 0;
                                @endphp
                                <form class="mb-2 addToCartDynamicForm add-to-cart-details-form" data-product-type="{{ $product['product_type'] }}" novalidate>

                                    @if(!$product->not_sellable)
                                        <div class="mb-3">
                                            <h3 class="font-weight-normal text-accent d-flex align-items-end gap-2 pt-1">
                                                <span class="discounted-unit-price fs-24 font-bold">
                                                    {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                                                </span>
                                                @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                                    <del class="product-total-unit-price align-middle text-muted fs-18 font-semibold">
                                                        {{ webCurrencyConverter(amount: $product->unit_price) }}
                                                    </del>
                                                @endif
                                            </h3>
                                        </div>
                                    @endif

                                    @if($product['product_type'] == 'physical' && $product->current_stock > 0 && $product->current_stock < 10)
                                        <div class="mb-2">
                                            @if($product->current_stock < $minimumOrderQty)
                                                <span class="badge badge-soft-danger border border-danger fs-12 px-2 py-1">
                                                    <i class="tio-warning-outlined me-1"></i>{{ translate('out_of_stock') }}
                                                </span>
                                            @else
                                                <span class="badge badge-soft-danger border border-danger fs-12 px-2 py-1">
                                                    <i class="tio-warning-outlined me-1"></i>{{ translate('in_stock') }}: {{ $product->current_stock }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    @if($minimumOrderQty > 1 && $product['product_type'] != 'digital')
                                        <div class="mb-2">
                                            <span class="badge badge-soft-warning border border-warning fs-12 px-2 py-1">
                                                <i class="tio-info-outlined me-1"></i>{{ translate('minimum_buy_amount') }}: {{ $minimumOrderQty }}
                                            </span>
                                        </div>
                                    @endif

                                    @if($stockUnderMinimum)
                                        <div class="mb-2">
                                            <span class="badge badge-soft-danger border border-danger fs-12 px-2 py-1">
                                                <i class="tio-warning-outlined me-1"></i>{{ translate('stock_is_below_minimum_order_quantity') }}
                                            </span>
                                        </div>
                                    @endif

                                    @csrf
                                    <input type="hidden" name="id" value="{{ $product->id }}">
                                    <div
                                        class="position-relative {{Session::get('direction') === "rtl" ? 'ml-n4' : 'mr-n4'}} mb-2">
                                        @if (count(json_decode($product->colors)) > 0)
                                            <div class="flex-start align-items-center mb-2 gap-2">
                                                <div class="product-description-label m-0 text-dark font-bold">
                                                    {{translate('color')}}:
                                                </div>
                                                <div>
                                                    <ul class="list-inline checkbox-color mb-0 flex-start ms-2 ps-0">
                                                        @foreach (json_decode($product->colors) as $key => $color)
                                                            <li>
                                                                <input type="radio"
                                                                       id="{{ str_replace(' ', '', ($product->id. '-color-'. str_replace('#','',$color))) }}"
                                                                       name="color" value="{{ $color }}"
                                                                       @if($key == 0) checked @endif>
                                                                <label style="background: {{ $color }};"
                                                                       class="focus-preview-image-by-color shadow-border"
                                                                       for="{{ str_replace(' ', '', ($product->id. '-color-'. str_replace('#','',$color))) }}"
                                                                       data-toggle="tooltip"
                                                                       data-key="{{ str_replace('#','',$color) }}"
                                                                      data-colorid="preview-box-{{ str_replace('#','',$color) }}" data-title="{{ getColorNameByCode(code: $color) }}">
                                                                    <span class="outline"></span></label>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        @endif
                                        @php
                                            $qty = 0;
                                            if(!empty($product->variation)){
                                            foreach (json_decode($product->variation) as $key => $variation) {
                                                    $qty += $variation->qty;
                                                }
                                            }
                                        @endphp
                                    </div>

                                    @php($extensionIndex=0)
                                    @if($product['product_type'] == 'digital' && $product['digital_product_file_types'] && count($product['digital_product_file_types']) > 0 && $product['digital_product_extensions'])
                                        @foreach($product['digital_product_extensions'] as $extensionKey => $extensionGroup)
                                        <div class="row flex-start mx-0 align-items-center mb-1">
                                            <div class="product-description-label text-dark font-bold {{Session::get('direction') === "rtl" ? 'pl-2' : 'pr-2'}} text-capitalize mb-2">
                                                {{ translate($extensionKey) }} :
                                            </div>
                                            <div>
                                                @if(count($extensionGroup) > 0)
                                                <div class="list-inline checkbox-alphanumeric checkbox-alphanumeric--style-1 mb-0 mx-1 flex-start ps-0">
                                                    @foreach($extensionGroup as $index => $extension)
                                                    <div class="user-select-none">
                                                        <div class="for-mobile-capacity">
                                                            <input type="radio" hidden
                                                                   id="extension_{{ str_replace(' ', '-', $extension) }}"
                                                                   name="variant_key"
                                                                   value="{{ $extensionKey.'-'.preg_replace('/\s+/', '-', $extension) }}"
                                                                {{ $extensionIndex == 0 ? 'checked' : ''}}>
                                                            <label for="extension_{{ str_replace(' ', '-', $extension) }}"
                                                                   class="__text-12px">
                                                                {{ $extension }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                    @php($extensionIndex++)
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    @endif



                                    @foreach (json_decode($product->choice_options) as $key => $choice)
                                        <div class="row flex-start mx-0 align-items-center">
                                            <div
                                                class="product-description-label text-dark font-bold {{Session::get('direction') === "rtl" ? 'pl-2' : 'pr-2'}} text-capitalize mb-2">{{ $choice->title }}
                                                :
                                            </div>
                                            <div>
                                                <div class="list-inline checkbox-alphanumeric checkbox-alphanumeric--style-1 mb-0 mx-1 flex-start row ps-0">
                                                    @foreach ($choice->options as $index => $option)
                                                        <div class="user-select-none">
                                                            <div class="for-mobile-capacity">
                                                                <input type="radio"
                                                                       id="{{ str_replace(' ', '', ($choice->name. '-'. $option)) }}"
                                                                       name="{{ $choice->name }}" value="{{ $option }}"
                                                                       @if($index == 0) checked @endif >
                                                                <label class="__text-12px"
                                                                       for="{{ str_replace(' ', '', ($choice->name. '-'. $option)) }}">{{ $option }}</label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="mt-3">
                                        <div class="product-quantity d-flex flex-column __gap-15">
                                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                                @if($product['product_type'] == 'physical')
                                                    <div class="product-qty-controls d-none align-items-center gap-3 flex-wrap">
                                                        <div class="d-flex justify-content-center align-items-center quantity-box border rounded border-base web-text-primary">
                                                            <span class="input-group-btn">
                                                                <button class="btn btn-number __p-10 web-text-primary" type="button"
                                                                        data-type="minus" data-field="quantity"
                                                                        disabled="disabled">
                                                                    -
                                                                </button>
                                                            </span>
                                                            <input type="text" name="quantity"
                                                                   class="form-control input-number text-center product-details-cart-qty __inline-29 border-0"
                                                                   placeholder="{{ $minimumOrderQty }}"
                                                                   value="{{ $minimumOrderQty }}"
                                                                   data-producttype="{{ $product->product_type }}"
                                                                   min="{{ $minimumOrderQty }}"
                                                                   max="{{ $product['product_type'] == 'physical' ? $product->current_stock : 100 }}">
                                                            <span class="input-group-btn">
                                                                <button class="btn btn-number __p-10 web-text-primary" type="button"
                                                                        data-producttype="{{ $product->product_type }}"
                                                                        data-type="plus" data-field="quantity">
                                                                    +
                                                                </button>
                                                            </span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <input type="hidden" name="quantity"
                                                           class="form-control input-number text-center product-details-cart-qty __inline-29 border-0"
                                                           value="1"
                                                           data-producttype="{{ $product->product_type }}"
                                                           min="1"
                                                           max="1">
                                                @endif
                                                <input type="hidden" class="product-generated-variation-code" name="product_variation_code" data-product-id="{{ $product['id'] }}">
                                                <input type="hidden" value="" class="product-exist-in-cart-list form-control w-50" name="key">
                                            </div>

                                            @if(!$product->not_sellable)
                                                <div class="product-details-chosen-price-section">
                                                    <div
                                                        class="d-none d-sm-flex justify-content-start align-items-center me-2">
                                                        <div
                                                            class="product-description-label text-dark font-bold text-capitalize">
                                                            <strong>{{translate('total_price')}}</strong> :
                                                        </div>
                                                         &nbsp; <strong class="text-base product-details-chosen-price-amount"></strong>
                                                        <small class="ms-2 font-regular product-details-tax-amount-container">
                                                            (<small>{{translate('tax')}} : </small>
                                                            <small class="product-details-tax-amount"></small>)
                                                        </small>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="__btn-grp mt-2 mb-3 product-add-and-buy-section-parent">
                                        @if($product->not_sellable && $product->product_type == 'event')
                                            <div class="product-add-and-buy-section event-cta-group">
                                                @if($product->book_tickets_url)
                                                    <a href="{{ $product->book_tickets_url }}" target="_blank" rel="noopener" class="btn btn-event-primary element-center">
                                                        <i class="tio-ticket"></i><span class="string-limit">{{ translate('book_ticket') }}</span>
                                                    </a>
                                                @endif
                                                <a href="{{ $product->external_url }}" target="_blank" rel="noopener"
                                                   class="btn btn-event-outline element-center">
                                                    <i class="tio-globe"></i><span class="string-limit">{{ translate('visit_website') }}</span>
                                                </a>
                                                @if($product->view_floorplan_url)
                                                    <a href="{{ $product->view_floorplan_url }}" target="_blank" rel="noopener" class="btn btn-event-outline element-center">
                                                        <i class="tio-map-outlined"></i><span class="string-limit">{{ translate('view_floor_plan') }}</span>
                                                    </a>
                                                @endif
                                                @if($product->brochure)
                                                    <a href="{{ $product->brochure_full_url['path'] }}" target="_blank" rel="noopener" class="btn btn-event-outline element-center">
                                                        <i class="tio-download-to"></i><span class="string-limit">{{ translate('download_brochure') }}</span>
                                                    </a>
                                                @endif
                                                @if($product->sponsor_exhibit_url)
                                                    <a href="{{ $product->sponsor_exhibit_url }}" target="_blank" rel="noopener" class="btn btn-event-sponsor element-center">
                                                        <i class="tio-award-outlined"></i><span class="string-limit">{{ translate('sponsor_exhibit') }}</span>
                                                    </a>
                                                @endif
                                                <a href="{{ route('products') }}" class="btn btn-outline-secondary element-center product-continue-shopping-btn">
                                                    <i class="tio-arrow-forward"></i><span class="string-limit">{{ translate('continue_shopping') }}</span>
                                                </a>
                                            </div>
                                        @elseif($product->not_sellable && $product->product_type == 'broker')
                                            <div class="product-add-and-buy-section gap-2 d-flex flex-wrap">
                                                <a href="{{ route('products') }}" class="btn btn-outline-primary element-center product-continue-shopping-btn">
                                                    <i class="tio-arrow-forward me-1"></i><span class="string-limit">{{ translate('continue_shopping') }}</span>
                                                </a>
                                            </div>
                                        @elseif($product->not_sellable)
                                            <div class="product-add-and-buy-section gap-2 d-flex flex-wrap">
                                                <a href="{{ $product->external_url }}" target="_blank" rel="noopener"
                                                   class="btn btn--primary element-center">
                                                    <span class="string-limit">{{ translate('visit_website') }}</span>
                                                </a>
                                                <a href="{{ route('products') }}" class="btn btn-outline-primary element-center product-continue-shopping-btn">
                                                    <span class="string-limit">{{ translate('continue_shopping') }}</span>
                                                </a>
                                            </div>
                                        @else
                                            <div class="product-add-and-buy-section gap-2 {!! $firstVariationQuantity <= 0 ? '' : 'd-flex' !!}" {!! $firstVariationQuantity <= 0 ? 'style="display: none;"' : '' !!}>
                                                @if(($product->added_by == 'seller' && ($sellerTemporaryClose || (isset($product->seller->shop) && $product->seller->shop->vacation_status && $currentDate >= $sellerVacationStartDate && $currentDate <= $sellerVacationEndDate))) ||
                                                    ($product->added_by == 'admin' && ($inHouseTemporaryClose || ($inHouseVacationStatus && $currentDate >= $inHouseVacationStartDate && $currentDate <= $inHouseVacationEndDate))))
                                                        <button class="btn btn-secondary" type="button" disabled>
                                                            {{ translate('buy_now') }}
                                                        </button>
                                                        <button class="btn btn--primary string-limit" type="button" disabled>
                                                            {{ translate('add_to_cart') }}
                                                        </button>
                                                @elseif($stockUnderMinimum)
                                                    <button class="btn btn-secondary" type="button" disabled title="{{ translate('stock_is_below_minimum_order_quantity') }}">
                                                        <span class="string-limit">{{ translate('buy_now') }}</span>
                                                    </button>
                                                    <button class="btn btn--primary element-center" type="button" disabled title="{{ translate('stock_is_below_minimum_order_quantity') }}">
                                                        <span class="string-limit">{{ translate('add_to_cart') }}</span>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-secondary element-center btn-gap-{{Session::get('direction') === "rtl" ? 'left' : 'right'}} product-buy-now-button"
                                                            data-form=".add-to-cart-details-form"
                                                            data-auth="{{( getWebConfig(name: 'guest_checkout') == 1 || Auth::guard('customer')->check() ? 'true':'false')}}"
                                                            data-route="{{ route('shop-cart') }}"
                                                    >
                                                        <span class="string-limit">{{ translate('buy_now') }}</span>
                                                    </button>
                                                    <button class="btn btn--primary element-center product-add-to-cart-button"
                                                            type="button"
                                                            data-form=".add-to-cart-details-form"
                                                            data-product-type="{{ $product['product_type'] }}"
                                                            data-update="{{ translate('go_to_cart') }}"
                                                            data-add="{{ translate('add_to_cart') }}"
                                                            data-cart-url="{{ route('shop-cart') }}"
                                                    >
                                                        <span class="string-limit">{{ translate('add_to_cart') }}</span>
                                                    </button>
                                                @endif
                                                <a href="{{ route('products') }}" class="btn btn-outline-primary element-center product-continue-shopping-btn">
                                                    <span class="string-limit">{{ translate('continue_shopping') }}</span>
                                                </a>
                                            </div>
                                            @if(($product['product_type'] == 'physical'))
                                                <div class="product-restock-request-section collapse" {!! $firstVariationQuantity <= 0 ? 'style="display: block;"' : '' !!}>
                                                    <button type="button"
                                                            class="btn request-restock-btn btn-outline-primary fw-semibold product-restock-request-button"
                                                            data-auth="{{ auth('customer')->check() }}"
                                                            data-form=".addToCartDynamicForm"
                                                            data-default="{{ translate('Request_Restock') }}"
                                                            data-requested="{{ translate('Request_Sent') }}"
                                                    >
                                                        {{ translate('Request_Restock')}}
                                                    </button>
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rtl text-align-direction">
                        <div class="product-information-card mb-3 mr-0 mr-md-2 bg-white __review-overview">
                        @if($product->product_type == 'event')
                            <ul class="nav nav-tabs nav--tabs product-information-tabs event-tabs d-flex justify-content-start" role="tablist">
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link active" href="#overview" data-toggle="tab" role="tab">{{ translate('overview') }}</h2></li>
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link" href="#agenda" data-toggle="tab" role="tab">{{ translate('agenda') }}</h2></li>
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link" href="#speakers" data-toggle="tab" role="tab">{{ translate('speakers') }}</h2></li>
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link" href="#exhibitors" data-toggle="tab" role="tab">{{ translate('exhibitors') }}</h2></li>
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link" href="#sponsors" data-toggle="tab" role="tab">{{ translate('sponsors') }}</h2></li>
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link" href="#venue" data-toggle="tab" role="tab">{{ translate('venue') }}</h2></li>
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link" href="#reviews" data-toggle="tab" role="tab">{{ translate('reviews') }}</h2></li>
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link" href="#faqs" data-toggle="tab" role="tab">{{ translate('faqs') }}</h2></li>
                            </ul>
                            <div class="tab-content px-lg-3">
                                <div class="tab-pane fade show active text-justify" id="overview" role="tabpanel">
                                    @include('web-views.products.partials._event-overview-tab-content')
                                </div>
                                <div class="tab-pane fade text-justify" id="agenda" role="tabpanel">
                                    @include('web-views.products.partials._tab-coming-soon')
                                </div>
                                <div class="tab-pane fade text-justify" id="speakers" role="tabpanel">
                                    @if($product->event_speakers_count)
                                        <div class="product-tab-panel"><h3 class="mb-2">{{ translate('speakers') }}</h3><p class="fs-24 font-bold mb-0">{{ $product->event_speakers_count }}</p></div>
                                    @else
                                        @include('web-views.products.partials._tab-coming-soon')
                                    @endif
                                </div>
                                <div class="tab-pane fade text-justify" id="exhibitors" role="tabpanel">
                                    @if($product->event_industry_brands)
                                        <div class="product-tab-panel"><h3 class="mb-2">{{ translate('exhibitors') }}</h3><p class="fs-24 font-bold mb-0">{{ $product->event_industry_brands }}</p></div>
                                    @else
                                        @include('web-views.products.partials._tab-coming-soon')
                                    @endif
                                </div>
                                <div class="tab-pane fade text-justify" id="sponsors" role="tabpanel">
                                    @if($product->sponsor_exhibit_url)
                                        <div class="product-tab-panel">
                                            <h3 class="mb-2">{{ translate('sponsors') }}</h3>
                                            <p>{{ translate('interested_in_sponsoring_or_exhibiting_at_this_event') }}</p>
                                            <a href="{{ $product->sponsor_exhibit_url }}" target="_blank" rel="noopener" class="btn btn-event-sponsor element-center">
                                                <span class="string-limit">{{ translate('sponsor_exhibit') }}</span>
                                            </a>
                                        </div>
                                    @else
                                        @include('web-views.products.partials._tab-coming-soon')
                                    @endif
                                </div>
                                <div class="tab-pane fade text-justify" id="venue" role="tabpanel">
                                    @include('web-views.products.partials._tab-coming-soon')
                                </div>
                                <div class="tab-pane fade" id="reviews" role="tabpanel">
                                    @include('web-views.products.partials._reviews-tab-content')
                                </div>
                                <div class="tab-pane fade text-justify" id="faqs" role="tabpanel">
                                    @include('web-views.products.partials._tab-coming-soon')
                                </div>
                            </div>
                        @elseif($product->product_type == 'broker')
                            <ul class="nav nav-tabs nav--tabs product-information-tabs event-tabs d-flex justify-content-start" role="tablist">
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link active" href="#overview" data-toggle="tab" role="tab">{{ translate('overview') }}</h2></li>
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link" href="#account_types" data-toggle="tab" role="tab">{{ translate('account_types') }}</h2></li>
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link" href="#platforms" data-toggle="tab" role="tab">{{ translate('platforms') }}</h2></li>
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link" href="#instruments" data-toggle="tab" role="tab">{{ translate('instruments') }}</h2></li>
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link" href="#fees" data-toggle="tab" role="tab">{{ translate('fees') }}</h2></li>
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link" href="#regulation" data-toggle="tab" role="tab">{{ translate('regulation') }}</h2></li>
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link" href="#reviews" data-toggle="tab" role="tab">{{ translate('reviews') }}</h2></li>
                                <li class="nav-item"><h2 class="nav-link __inline-27 tab_link" href="#faqs" data-toggle="tab" role="tab">{{ translate('faqs') }}</h2></li>
                            </ul>
                            <div class="tab-content px-lg-3">
                                <div class="tab-pane fade show active text-justify" id="overview" role="tabpanel">
                                    @include('web-views.products.partials._broker-overview-tab-content')
                                </div>
                                <div class="tab-pane fade text-justify" id="account_types" role="tabpanel">
                                    @include('web-views.products.partials._tab-coming-soon')
                                </div>
                                <div class="tab-pane fade text-justify" id="platforms" role="tabpanel">
                                    @include('web-views.products.partials._tab-coming-soon')
                                </div>
                                <div class="tab-pane fade text-justify" id="instruments" role="tabpanel">
                                    @include('web-views.products.partials._tab-coming-soon')
                                </div>
                                <div class="tab-pane fade text-justify" id="fees" role="tabpanel">
                                    @include('web-views.products.partials._tab-coming-soon')
                                </div>
                                <div class="tab-pane fade text-justify" id="regulation" role="tabpanel">
                                    @include('web-views.products.partials._tab-coming-soon')
                                </div>
                                <div class="tab-pane fade" id="reviews" role="tabpanel">
                                    @include('web-views.products.partials._reviews-tab-content')
                                </div>
                                <div class="tab-pane fade text-justify" id="faqs" role="tabpanel">
                                    @include('web-views.products.partials._tab-coming-soon')
                                </div>
                            </div>
                        @else
                            <ul class="nav nav-tabs nav--tabs product-information-tabs d-flex justify-content-center"
                                role="tablist">
                                <li class="nav-item">
                                    <h2 class="nav-link __inline-27 tab_link active" href="#overview"
                                        data-toggle="tab" role="tab">
                                        <i class="tio-dashboard-vs-outlined"></i>{{translate('overview')}}
                                    </h2>
                                </li>
                                <li class="nav-item">
                                    <h2 class="nav-link __inline-27 tab_link " href="#description"
                                        data-toggle="tab" role="tab">
                                        <i class="tio-document-text-outlined"></i>{{translate('description')}}
                                    </h2>
                                </li>
                                <li class="nav-item">
                                    <h2 class="nav-link __inline-27 tab_link " href="#specification"
                                        data-toggle="tab" role="tab">
                                        <i class="tio-format-bullets"></i>{{translate('specification')}}
                                    </h2>
                                </li>
                                <li class="nav-item">
                                    <h2 class="nav-link __inline-27 tab_link" href="#reviews" data-toggle="tab"
                                        role="tab">
                                        <i class="tio-star-outlined"></i>{{translate('reviews')}}
                                    </h2>
                                </li>
                                {{-- ADDED: Preview Tab Link --}}
                                @if($product->preview_url)
                                <li class="nav-item">
                                    <h2 class="nav-link __inline-27 tab_link" href="#preview" data-toggle="tab" role="tab">
                                        <i class="tio-visible-outlined"></i>{{translate('Preview')}}
                                    </h2>
                                </li>
                                @endif
                                 @if($product->video_url)
                                <li class="nav-item">
                                    <h2 class="nav-link __inline-27 tab_link" href="#preview_video" data-toggle="tab" role="tab">
                                        <i class="tio-play-circle-outlined"></i>{{translate('Video')}}
                                    </h2>
                                </li>
                                @endif
                            </ul>
                            <div class="tab-content px-lg-3">
                                <div class="tab-pane fade show active text-justify" id="overview"
                                     role="tabpanel">
                                    @include('web-views.products.partials._overview-tab-content')
                                </div>
                                <div class="tab-pane fade text-justify" id="description"
                                     role="tabpanel">
                                    <div class="product-tab-panel">
                                        <div class="product-tab-heading">
                                            <span class="product-tab-heading-icon"><i class="tio-document-text-outlined"></i></span>
                                            <div>
                                                <h3>{{ translate('product_description') }}</h3>
                                                <p>{{ translate('learn_more_about_product_features_and_benefits') }}</p>
                                            </div>
                                        </div>
                                    <div class="row specification">

                                        @if ($product['details'])
                                            <div class="product-tab-content-card text-body col-lg-12 col-md-12 overflow-scroll fs-13 text-justify details-text-justify rich-editor-html-content">
                                                {!! clean_html($product['details']) !!}
                                            </div>
                                        @endif


                                    </div>
                                     @if (!$product['details'] )
                                        <div>
                                            <div class="text-center text-capitalize py-5">
                                                <img class="mw-90"
                                                     src="{{theme_asset(path: 'public/assets/front-end/img/icons/nodata.svg')}}"
                                                     alt="">
                                                <p class="text-capitalize mt-2">
                                                    <small>{{translate('product_description_not_found')}}
                                                        !</small>
                                                </p>
                                            </div>
                                        </div>
                                     @endif
                                    </div>
                                </div>
                                <div class="tab-pane fade  text-justify" id="specification"
                                     role="tabpanel">
                                    <div class="product-tab-panel">
                                        <div class="product-tab-heading">
                                            <span class="product-tab-heading-icon"><i class="tio-format-bullets"></i></span>
                                            <div>
                                                <h3>{{ translate('product_specification') }}</h3>
                                                <p>{{ translate('quick_product_information') }}</p>
                                            </div>
                                        </div>
                                        <div class="product-spec-grid">
                                            <div class="product-spec-item">
                                                <i class="tio-category-outlined"></i>
                                                <small>{{ translate('category') }}</small>
                                                <strong>{{ $product['subCategory']->name ?? $product['category']->name ?? translate('not_available') }}</strong>
                                            </div>
                                            <div class="product-spec-item">
                                                <i class="tio-diamond-outlined"></i>
                                                <small>{{ translate('product_type') }}</small>
                                                <strong>{{ translate($product['product_type']) }}</strong>
                                            </div>
                                            <div class="product-spec-item">
                                                <i class="tio-label-outlined"></i>
                                                <small>{{ translate('product_code') }}</small>
                                                <strong>{{ $product['code'] ?: translate('not_available') }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="reviews" role="tabpanel">
                                    @if(count($product->reviews)==0 && $productReviews->total() == 0)
                                        <div>
                                            <div class="text-center text-capitalize">
                                                <img class="mw-100"
                                                     src="{{theme_asset(path: 'public/assets/front-end/img/icons/empty-review.svg')}}"
                                                     alt="">
                                                <p class="text-capitalize">
                                                    <small>{{translate('No_review_given_yet')}}!</small>
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="row pt-2 pb-3">
                                            <div class="col-lg-4 col-md-5 ">
                                                <div
                                                    class=" row d-flex justify-content-center align-items-center">
                                                    <div
                                                        class="col-12 d-flex justify-content-center align-items-center">
                                                        <h2 class="overall_review mb-2 __inline-28">
                                                            {{$overallRating[0]}}
                                                        </h2>
                                                    </div>
                                                    <div
                                                        class="d-flex justify-content-center align-items-center star-rating ">
                                                        @for($inc=1;$inc<=5;$inc++)
                                                            @if ($inc <= (int)$overallRating[0])
                                                                <i class="tio-star text-warning"></i>
                                                            @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0] > ((int)$overallRating[0]))
                                                                <i class="tio-star-half text-warning"></i>
                                                            @else
                                                                <i class="tio-star-outlined text-warning"></i>
                                                            @endif
                                                        @endfor
                                                    </div>
                                                    <div
                                                        class="col-12 d-flex justify-content-center align-items-center mt-2">
                                                        <span class="text-center">
                                                            {{$productReviews->total()}} {{translate('ratings')}}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-8 col-md-7 pt-sm-3 pt-md-0">
                                                <div
                                                    class="d-flex align-items-center mb-2 font-size-sm">
                                                    <div
                                                        class="__rev-txt"><span
                                                            class="d-inline-block align-middle text-body">{{translate('excellent')}}</span>
                                                    </div>
                                                    <div class="w-0 flex-grow">
                                                        <div class="progress text-body __h-5px">
                                                            <div class="progress-bar web--bg-primary"
                                                                 role="progressbar"
                                                                 style="width: <?php echo $widthRating = ($rating[0] != 0) ? ($rating[0] / $overallRating[1]) * 100 : (0); ?>%;"
                                                                 aria-valuenow="60" aria-valuemin="0"
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-1 text-body">
                                                        <span
                                                            class=" {{Session::get('direction') === "rtl" ? 'me-3 float-left' : 'ml-3 float-right'}} ">
                                                            {{$rating[0]}}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div
                                                    class="d-flex align-items-center mb-2 text-body font-size-sm">
                                                    <div
                                                        class="__rev-txt"><span
                                                            class="d-inline-block align-middle ">{{translate('good')}}</span>
                                                    </div>
                                                    <div class="w-0 flex-grow">
                                                        <div class="progress __h-5px">
                                                            <div class="progress-bar web--bg-primary" role="progressbar"
                                                                 style="width: <?php echo $widthRating = ($rating[1] != 0) ? ($rating[1] / $overallRating[1]) * 100 : (0); ?>%; background-color: #a7e453;"
                                                                 aria-valuenow="27" aria-valuemin="0"
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-1">
                                                        <span
                                                            class="{{Session::get('direction') === "rtl" ? 'me-3 float-left' : 'ml-3 float-right'}}">
                                                                {{$rating[1]}}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div
                                                    class="d-flex align-items-center mb-2 text-body font-size-sm">
                                                    <div
                                                        class="__rev-txt"><span
                                                            class="d-inline-block align-middle ">{{translate('average')}}</span>
                                                    </div>
                                                    <div class="w-0 flex-grow">
                                                        <div class="progress __h-5px">
                                                            <div class="progress-bar web--bg-primary" role="progressbar"
                                                                 style="width: <?php echo $widthRating = ($rating[2] != 0) ? ($rating[2] / $overallRating[1]) * 100 : (0); ?>%; background-color: #ffda75;"
                                                                 aria-valuenow="17" aria-valuemin="0"
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-1">
                                                        <span
                                                            class="{{Session::get('direction') === "rtl" ? 'me-3 float-left' : 'ml-3 float-right'}}">
                                                                {{$rating[2]}}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div
                                                    class="d-flex align-items-center mb-2 text-body font-size-sm">
                                                    <div
                                                        class="__rev-txt "><span
                                                            class="d-inline-block align-middle">{{translate('below_Average')}}</span>
                                                    </div>
                                                    <div class="w-0 flex-grow">
                                                        <div class="progress __h-5px">
                                                            <div class="progress-bar web--bg-primary" role="progressbar"
                                                                 style="width: <?php echo $widthRating = ($rating[3] != 0) ? ($rating[3] / $overallRating[1]) * 100 : (0); ?>%; background-color: #fea569;"
                                                                 aria-valuenow="9" aria-valuemin="0"
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-1">
                                                        <span
                                                            class="{{Session::get('direction') === "rtl" ? 'me-3 float-left' : 'ml-3 float-right'}}">
                                                                {{$rating[3]}}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div
                                                    class="d-flex align-items-center mb-2 text-body font-size-sm">
                                                    <div
                                                        class="__rev-txt"><span
                                                            class="d-inline-block align-middle ">{{translate('poor')}}</span>
                                                    </div>
                                                    <div class="w-0 flex-grow">
                                                        <div class="progress __h-5px">
                                                            <div class="progress-bar web--bg-primary" role="progressbar"
                                                                 style="width: <?php echo $widthRating = ($rating[4] != 0) ? ($rating[4] / $overallRating[1]) * 100 : (0); ?>%;"
                                                                 aria-valuenow="4" aria-valuemin="0"
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-1">
                                                        <span
                                                            class="{{Session::get('direction') === "rtl" ? 'me-3 float-left' : 'ml-3 float-right'}}">
                                                                {{$rating[4]}}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row pb-4 mb-3">
                                            <div class="__inline-30">
                                                <span
                                                    class="text-capitalize">{{ translate('Product_review') }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row pb-4">
                                        <div class="col-12" id="product-review-list">
                                            @include('web-views.partials._product-reviews')
                                        </div>

                                        @if(count($product->reviews) > 2)
                                            <div class="col-12">
                                                <div
                                                    class="card-footer d-flex justify-content-center align-items-center">
                                                    <button class="btn text-white view_more_button web--bg-primary">
                                                        {{ translate('view_more') }}
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- ADDED: Preview Tab Content --}}
                                @if($product->preview_url)
                                    <div class="tab-pane fade" id="preview" role="tabpanel">
                                        <div class="row pt-2 pb-5">
                                            <div class="col-12 text-center">
                                                <div class="p-5">
                                                    <button class="btn btn--primary btn-lg" onclick="openPreviewModal()">
                                                        <i class="tio-desktop"></i> {{ translate('Live_Preview') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif


                                @if(!empty($product->video_url))
                                <div class="tab-pane fade" id="preview_video" role="tabpanel">
                                    <div class="row pt-2 pb-5">
                                        <div class="col-12 text-center">
                                            <div class="col-12 mb-4">
                                                <iframe width="420" height="315"
                                                        src="{{ $embedUrl }}"
                                                        frameborder="0"
                                                        allowfullscreen></iframe>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                            </div>
                        @endif
                        </div>
                    </div>
                </div>
                @if($product->product_type == 'event')
                    @php($eventOrganizerName = ($product->added_by == 'seller' && isset($product->seller->shop)) ? $product->seller->shop->name : ($web_config['company_name'] ?? null))
                    @php($eventTypeLabel = $product['subCategory']->name ?? $product['category']->name ?? null)
                    @php($eventOrganizerLink = ($product->added_by == 'seller' && isset($product->seller->shop)) ? route('shopView', ['id' => $product->seller->shop->id]) : route('shopView', [0]))
                    <div class="col-lg-4">
                        <div class="event-facts-card mb-3">
                            <h3>{{ translate('finxcart_rating') }}</h3>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-24 font-bold event-stat-value">{{ $overallRating[0] }}</span>
                                <div class="star-rating">
                                    @for($inc=1;$inc<=5;$inc++)
                                        @if ($inc <= (int)$overallRating[0])
                                            <i class="tio-star text-warning"></i>
                                        @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0] > ((int)$overallRating[0]))
                                            <i class="tio-star-half text-warning"></i>
                                        @else
                                            <i class="tio-star-outlined text-warning"></i>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                            <div class="fs-12 text-muted mt-1">{{ $overallRating[1] }} {{ translate('reviews') }}</div>
                        </div>
                        @if($eventTypeLabel || $eventOrganizerName)
                            <div class="event-facts-card mb-3">
                                <h3>{{ translate('event_facts') }}</h3>
                                <div class="event-facts-list">
                                    @if($eventTypeLabel)
                                        <div><strong>{{ translate('event_type') }}:</strong> {{ $eventTypeLabel }}</div>
                                    @endif
                                    @if($eventOrganizerName)
                                        <div><strong>{{ translate('organizer') }}:</strong> {{ $eventOrganizerName }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if($product->brochure || $product->view_floorplan_url || $product->sponsor_exhibit_url || $eventOrganizerName)
                            <div class="event-downloads-card">
                                <h3>{{ translate('downloads_&_enquiries') }}</h3>
                                @if($product->brochure)
                                    <a href="{{ $product->brochure_full_url['path'] }}" target="_blank" rel="noopener" class="btn btn-event-outline element-center">
                                        <i class="tio-download-to"></i><span class="string-limit">{{ translate('download_event_brochure') }}</span>
                                    </a>
                                @endif
                                @if($product->view_floorplan_url)
                                    <a href="{{ $product->view_floorplan_url }}" target="_blank" rel="noopener" class="btn btn-event-outline element-center">
                                        <i class="tio-map-outlined"></i><span class="string-limit">{{ translate('download_floor_plan') }}</span>
                                    </a>
                                @endif
                                @if($product->sponsor_exhibit_url)
                                    <a href="{{ $product->sponsor_exhibit_url }}" target="_blank" rel="noopener" class="btn btn-event-sponsor element-center">
                                        <i class="tio-award-outlined"></i><span class="string-limit">{{ translate('become_a_sponsor') }}</span>
                                    </a>
                                @endif
                                @if($eventOrganizerName)
                                    <a href="{{ $eventOrganizerLink }}" class="btn btn-event-outline element-center">
                                        <i class="tio-new-message"></i><span class="string-limit">{{ translate('contact_organizer') }}</span>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
                @if($product->product_type == 'broker')
                    <div class="col-lg-4">
                        <div class="broker-facts-card mb-3">
                            <h3>{{ translate('finxcart_rating') }}</h3>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-24 font-bold broker-stat-value">{{ $overallRating[0] }}</span>
                                <div class="star-rating">
                                    @for($inc=1;$inc<=5;$inc++)
                                        @if ($inc <= (int)$overallRating[0])
                                            <i class="tio-star text-warning"></i>
                                        @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0] > ((int)$overallRating[0]))
                                            <i class="tio-star-half text-warning"></i>
                                        @else
                                            <i class="tio-star-outlined text-warning"></i>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                            <div class="fs-12 text-muted mt-1">{{ $overallRating[1] }} {{ translate('reviews') }}</div>
                        </div>
                        @if($product->external_url || $product->open_account_url || $product->contact_broker_url || $product->brochure)

                            <div class="broker-downloads-card"
                                style="
                                    width:100%;
                                    max-width:380px;
                                    background:#ffffff;
                                    border:1px solid #e6eaf2;
                                    border-radius:20px;
                                    overflow:hidden;
                                    box-shadow:0 15px 40px rgba(15,23,42,0.10);
                                    box-sizing:border-box;
                                ">

                                <!-- =========================
                                    CARD HEADER
                                ========================== -->
                                <div style="
                                    position:relative;
                                    padding:24px;
                                    background:linear-gradient(135deg,#111827 0%,#1e293b 55%,#4338ca 100%);
                                    overflow:hidden;
                                ">

                                    <!-- Decorative shapes -->
                                    <div style="
                                        position:absolute;
                                        width:150px;
                                        height:150px;
                                        right:-70px;
                                        top:-75px;
                                        border-radius:50%;
                                        background:rgba(99,102,241,.18);
                                    "></div>

                                    <div style="
                                        position:absolute;
                                        width:90px;
                                        height:90px;
                                        right:45px;
                                        bottom:-65px;
                                        border-radius:50%;
                                        background:rgba(168,85,247,.14);
                                    "></div>


                                    <div style="
                                        position:relative;
                                        z-index:2;
                                        display:flex;
                                        align-items:center;
                                        gap:14px;
                                    ">

                                        <!-- Header Icon -->
                                        <div style="
                                            width:48px;
                                            height:48px;
                                            min-width:48px;
                                            display:flex;
                                            align-items:center;
                                            justify-content:center;
                                            border-radius:14px;
                                            background:rgba(255,255,255,.12);
                                            border:1px solid rgba(255,255,255,.18);
                                            box-shadow:0 8px 20px rgba(0,0,0,.15);
                                        ">

                                            <i class="fas fa-rocket"
                                            style="
                                                font-size:20px;
                                                color:#ffffff !important;
                                            ">
                                            </i>

                                        </div>


                                        <div>

                                            <h3 style="
                                                margin:0;
                                                padding:0;
                                                color:#ffffff !important;
                                                font-size:19px !important;
                                                font-weight:700 !important;
                                                line-height:1.35 !important;
                                            ">
                                                {{ translate('take_the_next_step') }}
                                            </h3>

                                            <p style="
                                                margin:5px 0 0;
                                                padding:0;
                                                color:#cbd5e1 !important;
                                                font-size:12px !important;
                                                line-height:1.5 !important;
                                            ">
                                                Explore this broker and get started
                                            </p>

                                        </div>

                                    </div>

                                </div>


                                <!-- =========================
                                    CARD BODY
                                ========================== -->
                                <div style="
                                    padding:20px;
                                    box-sizing:border-box;
                                ">


                                    <!-- PRIMARY WEBSITE BUTTON -->
                                    @if($product->external_url)

                                        <a href="{{ $product->external_url }}"
                                        target="_blank"
                                        rel="noopener"
                                        style="
                                                display:flex !important;
                                                align-items:center !important;
                                                justify-content:space-between !important;
                                                width:100%;
                                                min-height:54px;
                                                margin-bottom:14px;
                                                padding:0 17px;
                                                border-radius:12px;
                                                background:linear-gradient(135deg,#4f46e5,#7c3aed) !important;
                                                border:0 !important;
                                                color:#ffffff !important;
                                                text-decoration:none !important;
                                                box-shadow:0 8px 18px rgba(79,70,229,.25);
                                                box-sizing:border-box;
                                        ">

                                            <span style="
                                                display:flex;
                                                align-items:center;
                                                gap:11px;
                                                color:#ffffff !important;
                                                font-size:13px !important;
                                                font-weight:700 !important;
                                            ">

                                                <span style="
                                                    width:32px;
                                                    height:32px;
                                                    display:flex;
                                                    align-items:center;
                                                    justify-content:center;
                                                    border-radius:9px;
                                                    background:rgba(255,255,255,.14);
                                                ">

                                                    <i class="fas fa-globe"
                                                    style="
                                                        color:#ffffff !important;
                                                        font-size:15px;
                                                    ">
                                                    </i>

                                                </span>

                                                <span style="color:#ffffff !important;">
                                                    {{ translate('visit_website') }}
                                                </span>

                                            </span>


                                            <i class="fas fa-arrow-up-right-from-square"
                                            style="
                                                color:#ffffff !important;
                                                font-size:13px;
                                            ">
                                            </i>

                                        </a>

                                    @endif


                                    <!-- =========================
                                        SECONDARY ACTIONS
                                    ========================== -->

                                    <div style="
                                        display:grid;
                                        grid-template-columns:repeat(2,minmax(0,1fr));
                                        gap:10px;
                                    ">


                                        <!-- OPEN ACCOUNT -->
                                        @if($product->open_account_url)

                                            <a href="{{ $product->open_account_url }}"
                                            target="_blank"
                                            rel="noopener"
                                            style="
                                                    display:flex;
                                                    align-items:center;
                                                    gap:10px;
                                                    min-width:0;
                                                    min-height:58px;
                                                    padding:10px 12px;
                                                    background:#f8faff !important;
                                                    border:1px solid #e5e9f2 !important;
                                                    border-radius:12px;
                                                    color:#1e293b !important;
                                                    text-decoration:none !important;
                                                    box-sizing:border-box;
                                            ">

                                                <span style="
                                                    width:34px;
                                                    height:34px;
                                                    min-width:34px;
                                                    display:flex;
                                                    align-items:center;
                                                    justify-content:center;
                                                    border-radius:9px;
                                                    background:#eef2ff;
                                                ">

                                                    <i class="fas fa-user-plus"
                                                    style="
                                                        color:#4f46e5 !important;
                                                        font-size:14px;
                                                    ">
                                                    </i>

                                                </span>

                                                <span style="
                                                    color:#334155 !important;
                                                    font-size:12px !important;
                                                    font-weight:600 !important;
                                                    line-height:1.35 !important;
                                                ">
                                                    {{ translate('open_account') }}
                                                </span>

                                            </a>

                                        @endif


                                        <!-- CONTACT BROKER -->
                                        @if($product->contact_broker_url)

                                            <a href="{{ $product->contact_broker_url }}"
                                            target="_blank"
                                            rel="noopener"
                                            style="
                                                    display:flex;
                                                    align-items:center;
                                                    gap:10px;
                                                    min-width:0;
                                                    min-height:58px;
                                                    padding:10px 12px;
                                                    background:#f8faff !important;
                                                    border:1px solid #e5e9f2 !important;
                                                    border-radius:12px;
                                                    color:#1e293b !important;
                                                    text-decoration:none !important;
                                                    box-sizing:border-box;
                                            ">

                                                <span style="
                                                    width:34px;
                                                    height:34px;
                                                    min-width:34px;
                                                    display:flex;
                                                    align-items:center;
                                                    justify-content:center;
                                                    border-radius:9px;
                                                    background:#ecfeff;
                                                ">

                                                    <i class="fas fa-comments"
                                                    style="
                                                        color:#0891b2 !important;
                                                        font-size:14px;
                                                    ">
                                                    </i>

                                                </span>

                                                <span style="
                                                    color:#334155 !important;
                                                    font-size:12px !important;
                                                    font-weight:600 !important;
                                                    line-height:1.35 !important;
                                                ">
                                                    {{ translate('contact_broker') }}
                                                </span>

                                            </a>

                                        @endif


                                        <!-- DOWNLOAD BROCHURE -->
                                        @if($product->brochure)

                                            <a href="{{ $product->brochure_full_url['path'] }}"
                                            target="_blank"
                                            rel="noopener"
                                            style="
                                                    display:flex;
                                                    align-items:center;
                                                    gap:10px;
                                                    min-width:0;
                                                    min-height:58px;
                                                    padding:10px 12px;
                                                    background:#f8faff !important;
                                                    border:1px solid #e5e9f2 !important;
                                                    border-radius:12px;
                                                    color:#1e293b !important;
                                                    text-decoration:none !important;
                                                    box-sizing:border-box;
                                            ">

                                                <span style="
                                                    width:34px;
                                                    height:34px;
                                                    min-width:34px;
                                                    display:flex;
                                                    align-items:center;
                                                    justify-content:center;
                                                    border-radius:9px;
                                                    background:#ecfdf5;
                                                ">

                                                    <i class="fas fa-file-arrow-down"
                                                    style="
                                                        color:#059669 !important;
                                                        font-size:14px;
                                                    ">
                                                    </i>

                                                </span>

                                                <span style="
                                                    color:#334155 !important;
                                                    font-size:12px !important;
                                                    font-weight:600 !important;
                                                    line-height:1.35 !important;
                                                ">
                                                    {{ translate('download_brochure') }}
                                                </span>

                                            </a>

                                        @endif


                                        <!-- SECURITY / SUPPORT -->
                                        <div style="
                                            display:flex;
                                            align-items:center;
                                            gap:10px;
                                            min-width:0;
                                            min-height:58px;
                                            padding:10px 12px;
                                            background:#f8faff;
                                            border:1px solid #e5e9f2;
                                            border-radius:12px;
                                            box-sizing:border-box;
                                        ">

                                            <span style="
                                                width:34px;
                                                height:34px;
                                                min-width:34px;
                                                display:flex;
                                                align-items:center;
                                                justify-content:center;
                                                border-radius:9px;
                                                background:#fff7ed;
                                            ">

                                                <i class="fas fa-shield-halved"
                                                style="
                                                    color:#ea580c !important;
                                                    font-size:14px;
                                                ">
                                                </i>

                                            </span>

                                            <span style="
                                                color:#334155 !important;
                                                font-size:12px !important;
                                                font-weight:600 !important;
                                                line-height:1.35 !important;
                                            ">
                                                Trusted Broker
                                            </span>

                                        </div>

                                    </div>


                                    <!-- DIVIDER -->
                                    <div style="
                                        display:flex;
                                        align-items:center;
                                        gap:10px;
                                        margin:20px 0 14px;
                                    ">

                                        <div style="
                                            flex:1;
                                            height:1px;
                                            background:#e5e7eb;
                                        ">
                                        </div>

                                        <span style="
                                            color:#94a3b8 !important;
                                            font-size:10px !important;
                                            font-weight:700;
                                            text-transform:uppercase;
                                            letter-spacing:.6px;
                                        ">
                                            Compare
                                        </span>

                                        <div style="
                                            flex:1;
                                            height:1px;
                                            background:#e5e7eb;
                                        ">
                                        </div>

                                    </div>


                                    <!-- COMPARE BROKERS -->
                                    <a href="{{ route('products', ['product_type' => 'broker', 'page' => 1]) }}"
                                    style="
                                            display:flex;
                                            align-items:center;
                                            justify-content:center;
                                            gap:9px;
                                            width:100%;
                                            height:46px;
                                            border:1px solid #cbd5e1 !important;
                                            border-radius:11px;
                                            background:#ffffff !important;
                                            color:#334155 !important;
                                            text-decoration:none !important;
                                            font-size:13px !important;
                                            font-weight:700 !important;
                                            box-sizing:border-box;
                                    ">

                                        <i class="fas fa-code-compare"
                                        style="
                                            color:#6366f1 !important;
                                            font-size:15px;
                                        ">
                                        </i>

                                        <span style="color:#334155 !important;">
                                            {{ translate('compare') }} Brokers
                                        </span>

                                    </a>


                                    <!-- TRUST MESSAGE -->
                                    <div style="
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                        gap:6px;
                                        margin-top:14px;
                                    ">

                                        <i class="fas fa-lock"
                                        style="
                                            color:#94a3b8 !important;
                                            font-size:10px;
                                        ">
                                        </i>

                                        <span style="
                                            color:#94a3b8 !important;
                                            font-size:10px !important;
                                            line-height:1.4 !important;
                                        ">
                                            Secure external links
                                        </span>

                                    </div>

                                </div>


                                <!-- Bottom Gradient -->
                                <div style="
                                    height:3px;
                                    width:100%;
                                    background:linear-gradient(90deg,#4f46e5,#7c3aed,#06b6d4);
                                ">
                                </div>

                            </div>

                        @endif
                        <div class="broker-risk-card mt-3">
                            <i class="tio-warning-outlined"></i>
                            <div>
                                <h3>{{ translate('risk_disclosure') }}</h3>
                                <p class="mb-0">{{ translate('trading_financial_instruments_involves_risk_disclaimer') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                @if($hasProductSidebar)
                <div class="col-lg-3">
                    @php($companyReliability = getWebConfig('company_reliability'))
                    @if($companyReliability != null)
                        {{-- <div class="product-details-shipping-details">
                            @foreach ($companyReliability as $key=>$value)
                                @if ($value['status'] == 1 && !empty($value['title']))
                                    <div class="shipping-details-bottom-border">
                                        <div class="px-3 py-3">
                                            <img class="{{Session::get('direction') === "rtl" ? 'float-right ml-2' : 'mr-2'}} __img-20"
                                                 src="{{ getStorageImages(path: imagePathProcessing(imageData: $value['image'],path: 'company-reliability'), type: 'source', source: 'public/assets/front-end/img'.'/'.$value['item'].'.png') }}"
                                                alt="">
                                            <span>{{translate($value['title'])}}</span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div> --}}
                    @endif

                    @if(getWebConfig(name: 'business_mode')=='multi')
                    <div class="d-none">

                        @if($product->added_by=='seller')
                            @if(isset($product->seller->shop))
                                {{-- <div class="row position-relative">
                                    <div class="col-12 position-relative">
                                        <a href="{{route('shopView',['id'=> $product?->seller?->shop->id])}}" class="d-block">
                                            <div class="d-flex __seller-author align-items-center">
                                                <div>
                                                    <img class="__img-60 img-circle" alt=""
                                                         src="{{ getStorageImages(path: $product?->seller?->shop->image_full_url, type: 'shop') }}">
                                                </div>
                                                <div
                                                    class="ms-2 w-0 flex-grow">
                                                    <h2 class="fs-15 mb-1">
                                                        {{$product->seller->shop->name}}
                                                    </h2>
                                                    <h3 class="text-capitalize fs-12">{{translate('vendor_info')}}</h3>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">

                                                @if($sellerTemporaryClose || ($product->seller->shop->vacation_status && $currentDate >= $sellerVacationStartDate && $currentDate <= $sellerVacationEndDate))
                                                    <span class="chat-seller-info product-details-seller-info"
                                                          data-toggle="tooltip"
                                                          title="{{ translate('this_shop_is_temporary_closed_or_on_vacation').' '.translate('You_cannot_add_product_to_cart_from_this_shop_for_now') }}">
                                                        <img src="{{theme_asset(path: 'public/assets/front-end/img/info.png')}}" alt="i">
                                                    </span>
                                                @endif
                                            </div>
                                        </a>
                                    </div>
                                </div> --}}
                            @endif
                        @else
                            <div class="row position-relative d-flex justify-content-between">
                                <div class="col-9">
                                    <a href="{{route('shopView',[0])}}" class="row d-flex ">
                                        <div>
                                            <img class="__inline-32" alt=""
                                                 src="{{ getStorageImages(path:$web_config['fav_icon'], type: 'logo') }}">
                                        </div>
                                        <div class="{{Session::get('direction') === "rtl" ? 'right' : 'mt-3 ml-2'}} get-view-by-onclick"
                                             data-link="{{ route('shopView',[0]) }}">
                                            <h2 class="font-bold __text-16px mb-0">
                                                {{$web_config['company_name']}}
                                            </h2><br>
                                        </div>

                                        @if($product->added_by == 'admin' && ($inHouseTemporaryClose || ($inHouseVacationStatus && $currentDate >= $inHouseVacationStartDate && $currentDate <= $inHouseVacationEndDate)))
                                            <div class="{{Session::get('direction') === "rtl" ? 'right' : 'ml-3'}}">
                                                <span class="chat-seller-info" data-toggle="tooltip"
                                                      title="{{translate('this_shop_is_temporary_closed_or_on_vacation._You_cannot_add_product_to_cart_from_this_shop_for_now')}}">
                                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/info.png')}}"
                                                         alt="i">
                                                </span>
                                            </div>
                                        @endif
                                    </a>
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="row d-flex justify-content-between">
                                        <div class="col-6 ">
                                            <div
                                                class="d-flex justify-content-center align-items-center rounded __h-79px hr-right-before">
                                                <div class="text-center">
                                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/rating.svg')}}"
                                                         class="mb-2" alt="">
                                                    <div class="__text-12px text-base">
                                                        <strong>{{$totalReviews}}</strong> {{translate('reviews')}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div
                                                class="d-flex justify-content-center align-items-center rounded __h-79px">
                                                <div class="text-center">
                                                    <img
                                                        src="{{theme_asset(path: 'public/assets/front-end/img/products.svg')}}"
                                                        class="mb-2" alt="">
                                                    <div class="__text-12px text-base">
                                                        <strong>{{$productsForReview->total()}}</strong> {{translate('products')}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 position-static mt-3">
                                    <div class="chat_with_seller-buttons">
                                            @if (auth('customer')->id())
                                                <button class="btn w-100 d-block text-center web--bg-primary text-white"
                                                        data-toggle="modal"
                                                        data-target="#chatting_modal" {{ ($inHouseTemporaryClose || ($inHouseVacationStatus && $currentDate >= $inHouseVacationStartDate && $currentDate <= $inHouseVacationEndDate)) ? 'disabled' : '' }}>
                                                <img class="mb-1" alt=""
                                                     src="{{ theme_asset(path: 'public/assets/front-end/img/chat-16-filled-icon.png')}}">
                                                <span class="d-none d-sm-inline-block text-capitalize">
                                                        {{translate('chat_with_vendor')}}
                                                </span>
                                            </button>
                                            @else
                                                <a href="{{ route('shopView',[0]) }}" class="btn w-100 d-block text-center web--bg-primary text-white">
                                                <img class="mb-1" alt=""
                                                     src="{{ theme_asset(path: 'public/assets/front-end/img/chat-16-filled-icon.png')}}">
                                                <span class="d-none d-sm-inline-block text-capitalize">
                                                        {{translate('chat_with_vendor')}}
                                                </span>
                                            </a>
                                            @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @endif

                    @if($hasProductSidebar)
                    <div class="pt-4 pb-3" style="border: 1px solid #eee; box-shadow: 0px 23px 24px -4px rgba(205, 205, 205, 0.1), 0px 4px 2px rgba(205, 205, 205, 0.1); ">
                        <h2 class=" __text-16px font-bold text-capitalize">
                            @if(getWebConfig(name: 'business_mode')=='multi')
                                {{ translate('more_from_the_store')}}
                            @else
                                {{ translate('you_may_also_like')}}
                            @endif
                        </h2>
                    </div>
                    <div>
                        @foreach($moreProductFromSeller as $item)
                            @include('web-views.partials._seller-products-product-details',['product'=>$item,'decimal_point_settings'=>$decimalPointSettings])
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

        @if (count($relatedProducts)>0)
            <div class="container rtl text-align-direction similar-products-section">
                <div class="card __card similar-products-card mb-4">
                    <div class="card-body">
                        <div class="row flex-center-between align-items-center mb-2">
                            <div class="ms-1 d-flex align-items-center gap-2">
                                <h2 class="similar-products-heading text-capitalize font-bold mb-0">
                                    <span class="similar-products-heading-icon"><i class="tio-shopping-basket-outlined"></i></span>
                                    {{ translate('similar_products')}}
                                </h2>
                                <span class="similar-products-count">{{ count($relatedProducts) }}</span>
                            </div>
                            <div class="view_all d-flex justify-content-center align-items-center">
                                <div>
                                    @php($category=json_decode($product['category_ids']))
                                    @if($category)
                                        <a class="text-capitalize view-all-text web-text-primary me-1"
                                           href="{{route('products',['category_id'=> $category[0]->id,'data_from'=>'category','page'=>1])}}">{{ translate('view_all')}}
                                            <i class="czi-arrow-{{Session::get('direction') === "rtl" ? 'left mr-1 ml-n1 mt-1 ' : 'right ml-1 mr-n1'}}"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="similar-products-grid">
                            @foreach($relatedProducts as $key => $relatedProduct)
                                @php($relatedImage = $relatedProduct->thumbnail_full_url)
                                @if(empty($relatedImage['path'] ?? null) && count($relatedProduct->images_full_url ?? []) > 0)
                                    @php($relatedImage = $relatedProduct->images_full_url[0])
                                @endif
                                @if(empty($relatedImage['path'] ?? null) && count($product->images_full_url ?? []) > 0)
                                    @php($relatedImage = $product->images_full_url[0])
                                @endif
                                <a class="similar-product-item" href="{{ route('product', $relatedProduct->slug) }}" data-aos="fade-up">
                                    <div class="similar-product-image">
                                        <img src="{{ getStorageImages(path: $relatedImage, type: 'product') }}"
                                             alt="{{ $relatedProduct->name }}">
                                    </div>
                                    <div class="similar-product-body">
                                        <h3>{{ Str::limit($relatedProduct->name, 58) }}</h3>
                                        <div class="similar-product-footer">
                                            <div class="similar-product-price">
                                                {{ $relatedProduct->not_sellable ? translate('visit_website') : getProductPriceByType(product: $relatedProduct, type: 'discounted_unit_price', result: 'string') }}
                                            </div>
                                            <span class="similar-product-arrow">
                                                <i class="czi-arrow-{{Session::get('direction') === "rtl" ? 'left' : 'right'}}"></i>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="modal fade rtl text-align-direction" id="show-modal-view" tabindex="-1" role="dialog" aria-labelledby="show-modal-image"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body flex justify-content-center">
                        <button class="btn btn-default __inline-33 dir-end-minus-7px"
                                data-dismiss="modal">
                            <i class="fa fa-close"></i>
                        </button>
                        <img class="element-center" id="attachment-view" src="" alt="">
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- @include("web-views.products._product-details-sticky", ['productDetails' => $product]) --}}

    @if(!empty($product->preview_file_full_url['path'] ?? null))
        @include('web-views.partials._product-preview-modal', ['previewFileInfo' => $previewFileInfo])
    @endif

    @include('layouts.front-end.partials.modal._chatting',['seller'=>$product->seller, 'user_type'=>$product->added_by])

    <span id="route-review-list-product" data-url="{{ route('review-list-product') }}"></span>
    <span id="products-details-page-data" data-id="{{ $product['id'] }}"></span>

    {{-- ADDED: Modal for Preview URL Iframe --}}
    @if($product->preview_url)
    <div class="modal fade" id="previewUrlModal" tabindex="-1" role="dialog" aria-labelledby="previewUrlModalLabel" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-xl" role="document" style="max-width: 95vw;">
            <div class="modal-content" style="height: 90vh;">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewUrlModalLabel">{{ translate('Finxcart_Live_Preview') }} - {{ $product->name }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <iframe src="{{ $product->preview_url }}" style="width: 100%; height: 100%; border: none;" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/product-details.js') }}"></script>
    <script>
        function openPreviewModal() {
            $('#previewUrlModal').modal('show');
        }

        $(document).ready(function () {
            cartQuantityInitialize();

            document.querySelector('.product-native-share')?.addEventListener('click', async function () {
                const shareData = { title: this.dataset.title, text: this.dataset.title, url: this.dataset.url };
                if (navigator.share) {
                    try {
                        await navigator.share(shareData);
                        return;
                    } catch (error) {
                        if (error.name === 'AbortError') return;
                    }
                }
                document.querySelector('.product-copy-link')?.click();
            });

            document.querySelector('.product-copy-link')?.addEventListener('click', async function () {
                const label = document.querySelector('.product-share-copy-label');
                try {
                    await navigator.clipboard.writeText(this.dataset.url);
                } catch (error) {
                    const temporaryInput = document.createElement('input');
                    temporaryInput.value = this.dataset.url;
                    document.body.appendChild(temporaryInput);
                    temporaryInput.select();
                    document.execCommand('copy');
                    temporaryInput.remove();
                }
                if (label) {
                    label.style.display = 'block';
                    setTimeout(() => label.style.display = 'none', 2200);
                }
                if (window.toastr) toastr.success('{{ translate('link_copied') }}');
            });
        });
    </script>
@endpush
