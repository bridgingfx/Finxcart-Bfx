{{-- ========== PHP IMPORTS & DATA ========== --}}
@php
    use App\Models\Brand;
    use App\Models\Category;
    use App\Utils\Helpers;

    $announcement = getWebConfig(name: 'announcement');
    $categorie = \App\Utils\CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting();
    // Same call as $categorie above — this used to hit the method (and its cache
    // lookup) twice per page load, on every page site-wide.
    $megacategorie = $categorie;
    $brands = \App\Utils\BrandManager::getActiveBrandWithCountingAndPriorityWiseSorting();
    $cart = \App\Utils\CartManager::getCartListQuery();
    $productCatRootCategory = \App\Utils\CategoryManager::getCategoryWithProductsRootCategory();
    $productCatWithProducts = \App\Utils\CategoryManager::getCategoryWithProducts();
    $currency_model = getWebConfig(name: 'currency_model');
    $currencies = class_exists('\App\Models\Currency')
        ? Cache::remember(CACHE_FOR_ACTIVE_CURRENCY_LIST, CACHE_FOR_3_HOURS, fn() => \App\Models\Currency::where('status', 1)->get())
        : collect();
@endphp

{{-- ========== STYLES ========== --}}
<style>
    /* ── Topbar Button (Desktop) ── */
    .topbar-btn {
        border: 1px solid #003f6b;
        border-radius: 999px;
        padding: 6px 14px;
        color: #003f6b;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background-color: white;
        transition: 0.2s ease;
    }

    .topbar-btn:hover {
        background-color: #003f6b;
        color: white;
    }

    /* ── Navbar row full-width space-between ── */
    .header-inner-row {
        flex-wrap: nowrap !important;
        width: 100%;
        align-items: center;
    }

    .header-inner-row .col-logo {
        flex-shrink: 0;
        flex-grow: 0;
    }

    .header-inner-row .col-search {
        flex: 1 1 auto;
        min-width: 0;
    }

    .header-inner-row .col-icons {
        flex-shrink: 0;
        flex-grow: 0;
        margin-left: auto;
    }

    /* ── btn-head-menus ── */
    .btn-head-menus {
        display: inline-flex !important;
        align-items: center !important;
        flex-wrap: nowrap !important;
        overflow: hidden;
        white-space: nowrap;
        border-radius: 10px;
        max-width: clamp(70px, 15vw, 140px);
        font-size: clamp(9px, 1.5vw, 14px) !important;
        padding: clamp(3px, 0.8vw, 7px) clamp(5px, 1.2vw, 12px) !important;
        gap: clamp(3px, 0.5vw, 6px);
        line-height: 1.5;
    }

    .btn-head-menus.dropdown-toggle::after {
        flex-shrink: 0;
        margin-left: clamp(2px, 0.4vw, 4px);
    }

    .btn-head-menus img {
        flex-shrink: 0;
        width: clamp(16px, 2vw, 24px) !important;
        height: clamp(16px, 2vw, 24px) !important;
        border-radius: 50%;
        object-fit: cover;
    }

    .btn-head-menus i {
        flex-shrink: 0;
        font-size: clamp(10px, 1.5vw, 14px) !important;
    }

    .btn-head-menus .btn-text {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ── btn-head-menus Breakpoints ── */
    @media (max-width: 1199px) {
        .btn-head-menus {
            max-width: 130px;
            font-size: 13px !important;
            padding: 5px 10px !important;
        }
    }

    @media (max-width: 991px) {
        .btn-head-menus {
            max-width: 120px;
            font-size: 12px !important;
            padding: 4px 8px !important;
        }
    }

    @media (max-width: 767px) {
        .btn-head-menus {
            max-width: 110px;
            font-size: 11px !important;
            padding: 3px 7px !important;
        }
    }

    @media (max-width: 480px) {
        .btn-head-menus {
            max-width: 90px;
            font-size: 10px !important;
            padding: 3px 6px !important;
        }

        .btn-head-menus img {
            width: 16px !important;
            height: 16px !important;
        }

        .btn-head-menus i {
            font-size: 10px !important;
        }
    }

    @media (max-width: 360px) {
        .btn-head-menus {
            max-width: 70px;
            font-size: 9px !important;
            padding: 2px 5px !important;
        }

        .btn-head-menus .btn-text {
            display: none;
        }
    }

    /* ── Hover States ── */
    .btn-outline-secondary:hover {
        background-color: #003f6b;
        color: white;
        border-color: #003f6b;
    }

    /* ── Dropdown Items ── */
    .dropdown-item:hover {
        background-color: #f1f1f1;
    }

    .dropdown-item.active {
        background-color: #e8f5e9;
        font-weight: 600;
    }

    /* ── Customer Notification Bell — same footprint as the Wishlist/Cart
       icons beside it (plain icon + corner badge) so all three sit level
       instead of the bell looking like a separate account button ── */
    .customer-notification-toggle {
        width: 22px;
        height: 22px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    /* Bootstrap's default dropdown-toggle caret (::after) was still showing
       up next to the bell — the theme has no ".no-caret" rule defined, so
       that utility class was a no-op. Kill the caret explicitly instead. */
    .customer-notification-toggle.dropdown-toggle::after {
        display: none !important;
    }

    .customer-notification-toggle .fi-rr-bell {
        font-size: 20px;
        line-height: 1;
        transition: color .2s ease, transform .2s ease;
    }

    .customer-notification-toggle:hover .fi-rr-bell {
        color: #fff;
        transform: rotate(-8deg);
    }

    /* ── Customer Notification Dropdown ── */
    .customer-notification-menu {
        border: 1px solid #edf0f3;
        border-radius: 10px;
        overflow: hidden;
    }

    .customer-notification-menu-header {
        background: #f8fafc;
        border-bottom: 1px solid #edf0f3;
    }

    .customer-notification-menu .dropdown-item {
        color: #212529 !important;
    }

    .customer-notification-menu .dropdown-item:hover,
    .customer-notification-menu .dropdown-item:focus {
        background-color: #f4f8ff;
    }

    .customer-notification-menu .dropdown-item.bg-light {
        background-color: #eef5ff !important;
        position: relative;
    }

    .customer-notification-menu .dropdown-item.bg-light::before {
        content: "";
        position: absolute;
        left: 4px;
        top: 16px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #003f6b;
    }

    .customer-notification-menu .dropdown-item .small {
        color: #1f2937 !important;
        font-weight: 600;
    }

    .customer-notification-menu .dropdown-item .text-muted {
        color: #6b7280 !important;
    }

    .customer-notification-mark-all-form button {
        color: #003f6b;
        font-weight: 600;
    }

    .customer-notification-mark-all-form button:hover {
        text-decoration: none;
        color: #002a49;
    }

    /* ── Currency Dropdown ── */
    .currency-dropdown-item {
        display: flex !important;
        align-items: center;
        gap: 10px;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        transition: background-color 0.2s ease;
        width: 100%;
    }

    .currency-dropdown-item .currency-icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background-color: rgba(0, 63, 107, 0.08);
        color: #003f6b;
        flex-shrink: 0;
    }

    .currency-dropdown-item .currency-symbol svg {
        width: 1em;
        height: 1em;
        display: inline-block;
        vertical-align: middle;
        margin-top: -9px;
    }

    .currency-dropdown-item .currency-name {
        font-size: 0.85rem;
        color: #6c757d;
    }

    /* ── Mobile burger dropdown panel ── */
    .mobile-menu-panel {
        min-width: 250px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #e0e0e0;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .mobile-menu-panel .panel-section-header {
        padding: 6px 14px 4px;
        background: #f4f6f8;
        border-bottom: 1px solid #e8e8e8;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #888;
    }

    .mobile-menu-panel .panel-divider {
        height: 1px;
        background: #ebebeb;
        margin: 2px 0;
    }

    .mobile-menu-panel .panel-user-header {
        display: flex;
        align-items: center;
        padding: 12px 14px;
        background: #f8f9fa;
        border-bottom: 1px solid #eee;
    }

    .mobile-menu-panel .panel-user-header img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        margin-right: 10px;
        border: 2px solid #dee2e6;
    }

    .mobile-menu-panel .panel-user-name {
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
        color: #222;
    }

    .mobile-menu-panel .panel-user-sub {
        font-size: 11px;
        color: #888;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 160px;
    }

    .mobile-menu-panel .panel-item {
        display: flex;
        align-items: center;
        padding: 9px 14px;
        font-size: 13px;
        color: #333;
        text-decoration: none;
        transition: background 0.15s;
        cursor: pointer;
    }

    .mobile-menu-panel .panel-item:hover {
        background: #f1f3f5;
        color: #003f6b;
    }

    .mobile-menu-panel .panel-item i {
        width: 20px;
        flex-shrink: 0;
        color: #003f6b;
    }

    .mobile-menu-panel .panel-item.danger {
        color: #dc3545;
    }

    .mobile-menu-panel .panel-item.danger i {
        color: #dc3545;
    }

    .mobile-menu-panel .panel-item .item-badge {
        margin-left: auto;
        font-size: 11px;
        background: #e9ecef;
        padding: 1px 7px;
        border-radius: 20px;
        color: #555;
    }

    .mobile-menu-panel .currency-scroll {
        max-height: 150px;
        overflow-y: auto;
    }

    .mobile-menu-panel .currency-item {
        display: flex;
        align-items: center;
        padding: 8px 14px;
        font-size: 13px;
        color: #333;
        cursor: pointer;
        transition: background 0.15s;
        text-decoration: none;
    }

    .mobile-menu-panel .currency-item:hover {
        background: #f1f3f5;
    }

    .mobile-menu-panel .currency-item.active {
        background: #eaf4ff;
        color: #003f6b;
        font-weight: 600;
    }

    .mobile-menu-panel .currency-item .currency-sym {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(0, 63, 107, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        color: #003f6b;
        flex-shrink: 0;
        margin-right: 10px;
    }

    .mobile-menu-panel .currency-item .check-icon {
        margin-left: auto;
        color: #28a745;
        font-size: 12px;
    }

    .mobile-menu-panel .lang-wrap {
        padding: 8px 14px 10px;
        min-width: 240px;
        width: 100%;
    }

    /* ── Custom Dropdown Wrapper ── */
    .dropdown-wrapper {
        position: relative;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background-color: #fff;
        min-width: 100%;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        z-index: 1000;
        margin-top: 5px;
        max-height: 200px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .dropdown-wrapper:hover .dropdown-content {
        display: block;
    }

    .dropdown-content li {
        padding: 6px 14px;
        cursor: pointer;
        white-space: nowrap;
        color: #333;
    }

    .dropdown-content li:hover {
        background-color: #f2f2f2;
    }

    .dropdown-content.dropdown {
        display: none;
    }

    .dropdown-wrapper.active .dropdown-content.dropdown {
        display: block;
    }

    .dropdown-menu-list li a {
        color: #333;
        text-decoration: none;
    }

    /* ── Bootstrap Dropdown Positioning ── */
    .topbar-right,
    .scrolling-topbar {
        position: relative;
    }

    .topbar-right .btn-group,
    .scrolling-topbar .btn-group {
        position: relative !important;
        display: inline-block !important;
        vertical-align: middle;
    }

    .topbar-right .btn-group .dropdown-menu,
    .scrolling-topbar .btn-group .dropdown-menu {
        position: absolute !important;
        top: 100% !important;
        right: 0 !important;
        bottom: auto !important;
        margin-top: 5px !important;
        border: 1px solid rgba(0, 0, 0, 0.08);
        animation: slideDown 0.3s ease-out;
        z-index: 9999 !important;
        transform: translateY(0) !important;
        display: none;
    }

    .topbar-right .btn-group .dropdown-menu.show,
    .scrolling-topbar .btn-group .dropdown-menu.show {
        display: block !important;
    }

    .topbar-right .btn-group .dropdown-item,
    .scrolling-topbar .btn-group .dropdown-item {
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .topbar-right .btn-group .dropdown-item:hover,
    .scrolling-topbar .btn-group .dropdown-item:hover {
        background-color: rgba(0, 123, 255, 0.08) !important;
    }

    .topbar-right .btn-group .dropdown-item.active,
    .scrolling-topbar .btn-group .dropdown-item.active {
        background-color: rgba(0, 123, 255, 0.12) !important;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .scrolling-topbar .dropdown {
            position: relative;
        }

        .scrolling-topbar .dropdown-menu {
            position: absolute;
            left: auto;
            right: 0;
            transform: none;
            width: max-content;
            min-width: 100%;
            max-width: calc(100vw - 24px);
            z-index: 1000;
        }

        .topbar-right .btn-group .dropdown-menu,
        .scrolling-topbar .btn-group .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            bottom: auto !important;
            transform: translateY(0) !important;
            margin-top: 5px !important;
        }
    }

    /* ── Google Translate ── */
    .skiptranslate,
    .goog-te-banner-frame,
    body>.skiptranslate,
    .goog-te-banner-frame.skiptranslate {
        display: none !important;
        visibility: hidden !important;
    }

    body {
        top: 0 !important;
    }

    .btn-head-menus .btn-text.text-white {
        color: #ffffff !important;
    }

    /* ── Desktop nav scroll wrapper ── */
    .bg-menu,
    .bg-menu .container-fluid,
    .bg-menu .min-height-45 {
        overflow: visible;
    }

    .nav-scroll-wrapper {
        position: relative;
        display: flex;
        align-items: stretch;
        overflow: visible;
        width: 100%;
    }

    .nav-scroll-inner {
        display: flex;
        overflow-x: auto;
        overflow-y: visible;
        scroll-behavior: smooth;
        scrollbar-width: none;
        -ms-overflow-style: none;
        flex: 1;
        min-width: 0;
    }
    .nav-scroll-inner::-webkit-scrollbar { display: none; }

    .nav-scroll-btn {
        flex-shrink: 0;
        display: none;
        align-items: center;
        justify-content: center;
        width: 28px;
        background: transparent;
        border: none;
        color: #fff;
        font-size: 20px;
        cursor: pointer;
        z-index: 10;
    }
    .nav-scroll-btn:hover { color: #f18b32; }
    .nav-scroll-btn.visible { display: flex; }

    /* ── Desktop nav menu ── */
    .desktop-category-menu {
        display: flex;
        justify-content: center;
        align-items: stretch;
        flex-wrap: nowrap;
        min-width: max-content;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .desktop-category-item {
        position: relative;
        display: flex;
        align-items: stretch;
        flex: 0 0 auto;
    }

    .desktop-category-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.28rem;
        min-height: 52px;
        padding: 0.95rem clamp(0.7rem, 0.45rem + 0.45vw, 1.15rem);
        border-right: 1px solid rgba(255, 255, 255, 0.18);
        color: #fff;
        font-size: clamp(0.8rem, 0.68rem + 0.28vw, 1rem);
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
        text-decoration: none;
        transition: background-color 0.2s ease;
    }

    .desktop-category-item:first-child .desktop-category-link {
        border-left: 1px solid rgba(255, 255, 255, 0.18);
    }

    .desktop-category-link:hover,
    .desktop-category-item:hover>.desktop-category-link {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
        text-decoration: none;
    }

    .desktop-category-link .caret-icon {
        font-size: 11px;
        transition: transform 0.2s;
    }

    .desktop-category-item:hover>.desktop-category-link .caret-icon {
        transform: rotate(180deg);
    }

    /* ── Mega Panel ── */
    .mega-dropdown-panel {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        width: 860px;
        background: #fff;
        border-top: 3px solid #f97316;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.13);
        z-index: 1100;
        overflow: hidden;
    }

    .desktop-category-item:hover>.mega-dropdown-panel,
    .desktop-category-item:hover>.simple-dropdown-panel {
        display: block;
        position: fixed;
    }

    .mega-panel-body {
        padding: 20px 22px 6px;
        max-height: 420px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(249, 115, 22, 0.4) transparent;
    }

    .mega-panel-body::-webkit-scrollbar {
        width: 4px;
    }

    .mega-panel-body::-webkit-scrollbar-track {
        background: transparent;
    }

    .mega-panel-body::-webkit-scrollbar-thumb {
        background: rgba(249, 115, 22, 0.4);
        border-radius: 99px;
    }

    .mega-row-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #bbb;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .mega-row-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #f0f0f0;
    }

    .mega-cols-row {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 18px 22px;
        margin-bottom: 20px;
    }

    .mega-sub-col {}

    .mega-sub-title {
        display: block;
        font-size: 14px;
        font-weight: 700;
        color: #1a3a5c;
        padding-bottom: 6px;
        border-bottom: 2px solid #f97316;
        margin-bottom: 8px;
        text-decoration: none;
        line-height: 1.3;
        transition: color 0.15s;
    }

    .mega-sub-title:hover {
        color: #f97316;
    }

    .mega-subsub-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .mega-subsub-list li {
        padding: 2px 0;
    }

    .mega-subsub-list li a {
        font-size: 13px;
        color: #666;
        text-decoration: none;
        display: block;
        line-height: 1.5;
        transition: color 0.15s;
    }

    .mega-subsub-list li a:hover {
        color: #f97316;
    }

    .mega-subsub-list li.more-link a {
        color: #f97316;
        font-weight: 700;
        font-size: 12px;
    }

    .mega-panel-footer {
        padding: 10px 22px 12px;
        border-top: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fafafa;
    }

    .mega-view-all {
        font-size: 12px;
        color: #f97316;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .mega-view-all:hover {
        text-decoration: underline;
        color: #f97316;
    }

    .mega-count-pill {
        font-size: 11px;
        background: #eee;
        color: #888;
        padding: 2px 10px;
        border-radius: 20px;
    }

    /* Simple dropdown for cats with no sub-sub (fallback) */
    .simple-dropdown-panel {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        min-width: 220px;
        background: #fff;
        border-top: 3px solid #f97316;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.13);
        z-index: 1100;
        padding: 6px 0;
    }

    .simple-dropdown-panel a {
        display: block;
        padding: 8px 16px;
        font-size: 13px;
        color: #1a3a5c;
        text-decoration: none;
        transition: background 0.15s, color 0.15s;
    }

    .simple-dropdown-panel a:hover {
        background: #f3f7fb;
        color: #f97316;
    }

    .hire-freelancer-panel {
        width: min(1040px, calc(100vw - 24px));
    }

    .desktop-category-item.hire-menu-open>.hire-freelancer-panel,
    .desktop-category-item:focus-within>.hire-freelancer-panel {
        display: block;
        position: fixed;
    }

    .hire-mega-layout {
        display: grid;
        grid-template-columns: minmax(190px, 250px) 1fr;
        min-height: 330px;
    }

    .hire-mega-categories {
        border-right: 1px solid #edf0f4;
        background: #fbfcfe;
        padding: 14px 0;
    }

    .hire-mega-category {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 11px 18px;
        color: #4b5563;
        font-weight: 700;
        text-decoration: none;
        line-height: 1.25;
    }

    .hire-mega-category:hover,
    .hire-mega-category:focus,
    .hire-mega-category.active {
        color: #17395e;
        background: #eef3f8;
        text-decoration: none;
        outline: none;
    }

    .hire-mega-services {
        display: none;
        padding: 24px 28px;
    }

    .hire-mega-services.active,
    .hire-mega-category:focus+.hire-mega-services {
        display: block;
    }

    .hire-mega-group-title {
        color: #17395e;
        font-weight: 800;
        font-size: 15px;
        text-transform: uppercase;
        margin-bottom: 18px;
    }

    .hire-mega-service-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px 42px;
    }

    .hire-mega-service {
        display: block;
        color: #1f2937;
        text-decoration: none;
    }

    .hire-mega-service:hover,
    .hire-mega-service:focus {
        color: #f97316;
        text-decoration: none;
        outline: none;
    }

    .hire-mega-service-title {
        display: block;
        font-size: 15px;
        font-weight: 800;
        line-height: 1.35;
    }

    .hire-mega-service-desc {
        display: block;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.45;
        margin-top: 5px;
    }

    .hire-mobile-desc {
        display: block;
        color: #7b8794;
        font-size: 11px;
        line-height: 1.4;
        margin-top: 2px;
        white-space: normal;
    }

    @media (max-width: 767.98px) {
        .hire-mega-layout {
            grid-template-columns: 1fr;
        }

        .hire-mega-service-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 1399.98px) {
        .desktop-category-link {
            font-size: clamp(0.76rem, 0.66rem + 0.2vw, 0.92rem);
            padding-left: 0.8rem;
            padding-right: 0.8rem;
        }
    }

    .mega-panel-footer {
        padding: 10px 22px 12px;
        border-top: 1px solid #f0f0f0;
        background: #fafafa;
    }
</style>

{{-- ========== HEADER ========== --}}
<header id="header" class="u-header u-header-left-aligned-nav">

    {{-- ========== ANNOUNCEMENT BAR ========== --}}
    @if (isset($announcement) && $announcement['status'] == 1)
        <div class="text-center position-relative px-4 py-1" id="announcement"
            style="background-color: {{ $announcement['color'] }}; color: {{ $announcement['text_color'] }}; margin-bottom: 6px;">
            <span>{{ str_ireplace(['FinXCart', 'FINXCART'], $web_config['company_name'] ?? 'Finxcart', $announcement['announcement']) }}</span>
            <span class="__close-announcement web-announcement-slideUp">X</span>
        </div>
    @endif

    <div class="u-header__section">

        {{-- ===== LOGO + SEARCH + ICONS BAR ===== --}}
        <div class="py-3 py-xl-2 bg-primary">
            <div class="container">
                <div class="row header-inner-row">

                    {{-- ── Logo + Offcanvas ── --}}
                    <div class="col-logo">
                        <nav
                            class="navbar navbar-expand u-header__navbar py-0 justify-content-xl-between max-width-270 min-width-270">
                            <a class="order-1 order-xl-0 navbar-brand u-header__navbar-brand u-header__navbar-brand-center ps-3"
                                href="{{ route('home') }}">
                                <img class="__inline-11"
                                    src="{{ getStorageImages(path: $web_config['web_logo'], type: 'logo') }}"
                                    alt="{{ $web_config['company_name'] }}">
                            </a>
                            <button id="sidebarHeaderInvokerMenu" type="button"
                                class="navbar-toggler d-block d-xl-none btn u-hamburger mr-3 mr-xl-0" data-unfold-event="click"
                                data-unfold-hide-on-scroll="false" data-unfold-target="#sidebarHeader1"
                                data-unfold-type="css-animation" data-unfold-animation-in="fadeInLeft"
                                data-unfold-animation-out="fadeOutLeft" data-unfold-duration="500">
                                <span id="hamburgerTriggerMenu" class="u-hamburger__box">
                                    <span class="u-hamburger__inner"></span>
                                </span>
                            </button>
                        </nav>

                        {{-- ── Sidebar ── --}}
                    <aside id="sidebarHeader1" class="u-sidebar u-sidebar--left"
                           aria-labelledby="sidebarHeaderInvokerMenu"
                           aria-hidden="true">
                            <div class="u-sidebar__scroller">
                                <div class="u-sidebar__container">
                                    <div class="u-header-sidebar__footer-offset pb-0">
                                        <div class="position-absolute top-0 right-0 z-index-2 pt-4 pr-7">
                                            <button type="button" class="close ml-auto" data-unfold-event="click"
                                                data-unfold-hide-on-scroll="false" data-unfold-target="#sidebarHeader1"
                                                data-unfold-type="css-animation" data-unfold-animation-in="fadeInLeft"
                                                data-unfold-animation-out="fadeOutLeft" data-unfold-duration="500">
                                                <span aria-hidden="true">
                                                    <i class="ec ec-close-remove text-gray-90 font-size-20"></i>
                                                </span>
                                            </button>
                                        </div>
                                        <div class="js-scrollbar u-sidebar__body">
                                            <div id="headerSidebarContent"
                                                class="u-sidebar__content u-header-sidebar__content">
                                                <a class="d-flex ml-0 navbar-brand u-header__navbar-brand u-header__navbar-brand-vertical"
                                                    href="{{ route('home') }}">
                                                    <img class="{{ Session::get('direction') === 'rtl' ? 'right-align' : '' }}"
                                                        src="{{ getStorageImages(path: $web_config['footer_logo'], type: 'logo') }}"
                                                        alt="{{ $web_config['company_name'] }}" />
                                                </a>
                                                <hr />
                                                <ul id="headerSidebarList" class="u-header-collapse__nav">
                                                    <?php $hireFreelancerMobileInserted = false; ?>
                                                    @foreach ($categorie as $cat)
                                                        <li class="u-has-submenu u-header-collapse__submenu">
                                                            <a class="u-header-collapse__nav-link u-header-collapse__nav-pointer"
                                                                href="javascript:;" role="button"
                                                                data-toggle="collapse"
                                                                aria-controls="headerSidebarCollapse-{{ $cat->id }}"
                                                                data-target="#headerSidebarCollapse-{{ $cat->id }}">
                                                                {{ $cat->name }}
                                                            </a>
                                                            @if ($productCatRootCategory && $cat->id == $productCatRootCategory->id && !empty($productCatWithProducts))
                                                                <div id="headerSidebarCollapse-{{ $cat->id }}"
                                                                    class="collapse"
                                                                    data-parent="#headerSidebarContent">
                                                                    <ul class="u-header-collapse__nav-list">
                                                                        @foreach ($productCatWithProducts as $group)
                                                                            <li class="u-has-submenu">
                                                                                <a class="u-header-collapse__submenu-nav-link"
                                                                                    href="javascript:;">
                                                                                    {{ $group['subcategory']->name }}
                                                                                </a>
                                                                                @if (count($group['products']) > 0)
                                                                                    <ul class="pl-3">
                                                                                        @foreach ($group['products'] as $product)
                                                                                            <li>
                                                                                                <a class="u-header-collapse__submenu-nav-link"
                                                                                                    href="{{ route('product', $product->slug) }}">
                                                                                                    {{ $product->name }}
                                                                                                </a>
                                                                                            </li>
                                                                                        @endforeach
                                                                                    </ul>
                                                                                @endif
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            @elseif($cat->childes->count())
                                                                <div id="headerSidebarCollapse-{{ $cat->id }}"
                                                                    class="collapse"
                                                                    data-parent="#headerSidebarContent">
                                                                    <ul class="u-header-collapse__nav-list">
                                                                        @foreach ($cat->childes as $subcat)
                                                                            <li>
                                                                                <a class="u-header-collapse__submenu-nav-link"
                                                                                    href="{{ $subcat->slug === 'latest-listings' ? route('products', ['data_from' => 'latest', 'page' => 1]) : route('products', ['sub_category_id' => $subcat->id, 'data_from' => 'category', 'page' => 1]) }}">
                                                                                    {{ $subcat->name }}
                                                                                </a>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            @endif
                                                        </li>
                                                        @if (!$hireFreelancerMobileInserted && ($cat->slug ?? null) === 'events' && isset($hireFreelancerMenu))
                                                            <?php $hireFreelancerMobileInserted = true; ?>
                                                            <li class="u-has-submenu u-header-collapse__submenu">
                                                                <a class="u-header-collapse__nav-link u-header-collapse__nav-pointer"
                                                                   href="javascript:;" role="button"
                                                                   data-toggle="collapse"
                                                                   aria-controls="hireFreelancerMobileCollapse"
                                                                   data-target="#hireFreelancerMobileCollapse"
                                                                   aria-expanded="false">
                                                                    {{ translate('hire_freelancer') }}
                                                                </a>
                                                                <div id="hireFreelancerMobileCollapse" class="collapse" data-parent="#headerSidebarContent">
                                                                    <ul class="u-header-collapse__nav-list">
                                                                        @foreach($hireFreelancerMenu as $freelancerCategory)
                                                                            <li class="u-has-submenu">
                                                                                <a class="u-header-collapse__submenu-nav-link"
                                                                                   href="javascript:;"
                                                                                   data-toggle="collapse"
                                                                                   aria-controls="hireFreelancerCategoryCollapse-{{ $freelancerCategory->id }}"
                                                                                   data-target="#hireFreelancerCategoryCollapse-{{ $freelancerCategory->id }}"
                                                                                   aria-expanded="false">
                                                                                    {{ $freelancerCategory->name }}
                                                                                </a>
                                                                                <div id="hireFreelancerCategoryCollapse-{{ $freelancerCategory->id }}" class="collapse">
                                                                                    <ul class="pl-3">
                                                                                        @forelse($freelancerCategory->activeSpecializations as $specialization)
                                                                                            <li>
                                                                                                <a class="u-header-collapse__submenu-nav-link"
                                                                                                   href="{{ route('hire-freelancer', ['specialization' => $specialization->slug]) }}">
                                                                                                    {{ $specialization->name }}
                                                                                                    @if(filled($specialization->description))
                                                                                                        <span class="hire-mobile-desc">{{ \Illuminate\Support\Str::limit(strip_tags($specialization->description), 76) }}</span>
                                                                                                    @endif
                                                                                                </a>
                                                                                            </li>
                                                                                        @empty
                                                                                            <li>
                                                                                                <a class="u-header-collapse__submenu-nav-link"
                                                                                                   href="{{ route('hire-freelancer', ['category' => $freelancerCategory->slug]) }}">
                                                                                                    {{ translate('all_freelancer_services') }}
                                                                                                </a>
                                                                                            </li>
                                                                                        @endforelse
                                                                                    </ul>
                                                                                </div>
                                                                            </li>
                                                                        @endforeach
                                                                        <li>
                                                                            <a class="u-header-collapse__submenu-nav-link"
                                                                               href="{{ route('hire-freelancer') }}">
                                                                                {{ translate('all_freelancer_services') }}
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                    @if (!$hireFreelancerMobileInserted && isset($hireFreelancerMenu))
                                                        <li class="u-has-submenu u-header-collapse__submenu">
                                                            <a class="u-header-collapse__nav-link u-header-collapse__nav-pointer"
                                                               href="javascript:;" role="button"
                                                               data-toggle="collapse"
                                                               aria-controls="hireFreelancerMobileCollapseFallback"
                                                               data-target="#hireFreelancerMobileCollapseFallback"
                                                               aria-expanded="false">
                                                                {{ translate('hire_freelancer') }}
                                                            </a>
                                                            <div id="hireFreelancerMobileCollapseFallback" class="collapse" data-parent="#headerSidebarContent">
                                                                <ul class="u-header-collapse__nav-list">
                                                                    @foreach($hireFreelancerMenu as $freelancerCategory)
                                                                        <li>
                                                                            <a class="u-header-collapse__submenu-nav-link"
                                                                               href="{{ route('hire-freelancer', ['category' => $freelancerCategory->slug]) }}">
                                                                                {{ $freelancerCategory->name }}
                                                                            </a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </aside>
                        {{-- ── End Sidebar ── --}}
                    </div>
                    {{-- ── End Logo + Offcanvas ── --}}

                    {{-- ── Search Bar (Desktop Only) ── --}}
                    <div class="col-search d-none d-xl-flex align-items-center px-3">
                        <form action="{{ route('products') }}" class="search_form js-focus-state w-100" novalidate>
                            <label class="sr-only" for="searchproduct-item">{{ translate('search') }}</label>
                            <div class="input-group">
                                <input type="text"
                                    class="form-control py-2 pl-5 font-size-15 border-right-0 height-40 border-width-2 rounded-left-pill border-primary"
                                    name="name" id="searchproduct-item"
                                    placeholder="{{ translate('search_for_products') }}"
                                    aria-label="Search for Products" aria-describedby="searchProduct1" required
                                    value="{{ request('name') }}" />
                                <input type="hidden" name="global_search_input" value="1">
                                <div class="input-group-append">
                                    <div
                                        class="dropdown bootstrap-select js-select dropdown-select custom-search-categories-select bg-white">
                                        <select
                                            name="category_ids[]"
                                            class="js-select selectpicker dropdown-select custom-search-categories-select"
                                            data-style="btn height-40 text-gray-60 font-weight-normal border-top border-bottom border-left-0 rounded-0 border-primary border-width-2 pl-0 pr-5 py-2">
                                            <option value="all" {{ empty(request('category_ids')) ? 'selected' : '' }}>{{ translate('all_categories') }}</option>
                                            @foreach ($categorie as $cat)
                                                <option value="{{ $cat->id }}" {{ in_array((string) $cat->id, (array) request('category_ids', [])) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button class="btn btn-dark height-40 py-2 px-3 rounded-right-pill" type="submit"
                                        id="searchProduct1">
                                        <span class="ec ec-search font-size-24"></span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    {{-- ── End Search Bar ── --}}

                    {{-- ── Header Icons ── --}}
                    <div class="col-icons text-right pl-0 pl-xl-3 position-static">
                        <div class="d-inline-flex">
                            <ul class="d-flex list-unstyled mb-0 align-items-center">

                                {{-- Notifications --}}
                                @if(auth('customer')->check())
                                    <li class="col px-2 px-sm-3 dropdown position-relative">
                                        <a href="javascript:void(0)" class="customer-notification-toggle text-gray-90 position-relative d-flex dropdown-toggle no-caret"
                                            id="customerNotificationDropdown" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false" data-placement="top"
                                            title="{{ translate('notifications') }}">
                                            <i class="fi fi-rr-bell"></i>
                                            @if($customerUnreadNotificationCount > 0)
                                                <span
                                                    class="notification-badge width-22 height-22 bg-primary position-absolute d-flex align-items-center justify-content-center rounded-circle left-12 top-8 font-weight-bold font-size-12">
                                                    {{ $customerUnreadNotificationCount }}
                                                </span>
                                            @endif
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right customer-notification-menu p-0 shadow-sm"
                                            aria-labelledby="customerNotificationDropdown"
                                            style="min-width:320px;max-height:400px;overflow-y:auto;">
                                            <div class="customer-notification-menu-header d-flex align-items-center justify-content-between px-3 py-2">
                                                <span class="font-weight-bold font-size-14 text-dark">{{ translate('notifications') }}</span>
                                            </div>
                                            <div id="customerNotificationItems">
                                                @include('web-views.partials._customer-notification-items')
                                            </div>
                                        </div>
                                    </li>
                                @endif

                                {{-- Wishlist --}}
                                <li class="col px-2 px-sm-3">
                                    <a href="{{ route('wishlists') }}" class="text-gray-90" data-toggle="tooltip"
                                        data-placement="top" title="Favorites">
                                        <i class="font-size-22 ec ec-favorites"></i>
                                        <span id="wish_count"
                                            class="width-22 height-22 bg-primary position-absolute d-flex align-items-center justify-content-center rounded-circle left-12 top-8 font-weight-bold font-size-12">
                                            {{ session()->has('wish_list') ? count(session('wish_list')) : 0 }}
                                        </span>
                                    </a>
                                </li>

                                {{-- Cart --}}
                                <li class="col pr-xl-0 px-2 px-sm-3">
                                    <a href="{{ route('shop-cart') }}"
                                        class="text-gray-90 position-relative d-flex" data-toggle="tooltip"
                                        data-placement="top" title="Cart">
                                        <i class="font-size-22 ec ec-shopping-bag"></i>
                                        <span id="user_cart_count"
                                            class="width-22 height-22 bg-primary position-absolute d-flex align-items-center justify-content-center rounded-circle left-12 top-8 font-weight-bold font-size-12">
                                            {{ $cart->count() }}
                                        </span>
                                    </a>
                                </li>

                                {{-- ══════════════════════════════════════════
                                 CURRENCY + TRANSLATE + USER
                            ══════════════════════════════════════════ --}}
                                <li class="px-1">

                                    {{-- ── MOBILE BURGER (xs only, below 576px) ── --}}
                                    <div class="d-flex d-sm-none">
                                        <div class="btn-group">
                                            <button type="button"
                                                class="btn btn-outline-secondary dropdown-toggle btn-head-menus"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="ec ec-menu" style="flex-shrink:0;"></i>
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-right p-0 mobile-menu-panel">

                                                {{-- USER HEADER --}}
                                                <?php if(auth('customer')->check()): ?>
                                                    <?php
                                                        $profileImg = auth('customer')->user()->image
                                                            && file_exists(storage_path('app/public/profile/' . auth('customer')->user()->image))
                                                                ? asset('storage/app/public/profile/' . auth('customer')->user()->image)
                                                                : asset('public/assets/back-end/img/customer-info.png');
                                                    ?>
                                                    <div class="panel-user-header">
                                                        <img src="{{ $profileImg }}" alt="Profile">
                                                        <div style="overflow:hidden;">
                                                            <div class="panel-user-name">
                                                                {{ ucwords(auth('customer')->user()->f_name) }}
                                                            </div>
                                                            @if (!empty(auth('customer')->user()->phone))
                                                                <div class="panel-user-sub">
                                                                    {{ auth('customer')->user()->phone }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <a class="panel-item" href="{{ route('wallet') }}">
                                                        <i class="fi fi-sr-wallet mr-2"></i>
                                                        {{ translate('my_wallet') }}
                                                        <span class="item-badge">
                                                            {{ webCurrencyConverter(auth('customer')->user()->wallet_balance ?? 0) }}
                                                        </span>
                                                    </a>
                                                    <a class="panel-item" href="{{ route('account-oder') }}">
                                                        <i class="fi fi-sr-box mr-2"></i>
                                                        {{ translate('my_Order') }}
                                                    </a>
                                                    <a class="panel-item" href="{{ route('user-account') }}">
                                                        <i class="ec ec-user mr-2"></i>
                                                        {{ translate('my_Profile') }}
                                                    </a>
                                                    <a class="panel-item danger"
                                                        href="{{ route('customer.auth.logout') }}">
                                                        <i class="fi fi-sr-sign-out-alt mr-2"></i>
                                                        {{ translate('logout') }}
                                                    </a>
                                                <?php endif; ?>
                                                <?php if(auth('seller')->check()): ?>
                                                    <div class="panel-user-header">
                                                        <img src="{{ asset('public/assets/back-end/img/customer-info.png') }}" alt="Profile">
                                                        <div style="overflow:hidden;">
                                                            <div class="panel-user-name">
                                                                {{ optional(auth('seller')->user()->shop)->name ?? ucwords(auth('seller')->user()->f_name) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <a class="panel-item" href="{{ route('vendor.dashboard.index') }}">
                                                        <i class="ec ec-user mr-2"></i>
                                                        {{ translate('vendor_dashboard') }}
                                                    </a>
                                                    <a class="panel-item danger" href="{{ route('vendor.auth.logout') }}">
                                                        <i class="fi fi-sr-sign-out-alt mr-2"></i>
                                                        {{ translate('logout') }}
                                                    </a>
                                                <?php endif; ?>
                                                <?php if(auth('freelancer')->check()): ?>
                                                    <div class="panel-user-header">
                                                        <img src="{{ asset('public/assets/back-end/img/customer-info.png') }}" alt="Profile">
                                                        <div style="overflow:hidden;">
                                                            <div class="panel-user-name">
                                                                {{ ucwords(auth('freelancer')->user()->f_name) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <a class="panel-item" href="{{ route('freelancer.dashboard.index') }}">
                                                        <i class="ec ec-user mr-2"></i>
                                                        {{ translate('freelancer_dashboard') }}
                                                    </a>
                                                    <a class="panel-item danger" href="{{ route('freelancer.auth.logout') }}">
                                                        <i class="fi fi-sr-sign-out-alt mr-2"></i>
                                                        {{ translate('logout') }}
                                                    </a>
                                                <?php endif; ?>
                                                <?php if(!auth('customer')->check() && !auth('seller')->check() && !auth('freelancer')->check()): ?>
                                                    <div class="panel-section-header">{{ translate('account') }}</div>
                                                    <a class="panel-item" href="{{ route('customer.auth.login') }}">
                                                        <i class="ec ec-user mr-2"></i>
                                                        {{ __('Customer Login') }}
                                                    </a>
                                                    <a class="panel-item"
                                                        href="{{ route('vendor.auth.registration.index') }}">
                                                        <i class="ec ec-user mr-2"></i>
                                                        {{ __('Vendor Login') }}
                                                    </a>
                                                    <a class="panel-item"
                                                        href="{{ route('freelancer.auth.login.index') }}">
                                                        <i class="ec ec-user mr-2"></i>
                                                        {{ __('Freelancer Login') }}
                                                    </a>
                                                <?php endif; ?>

                                                {{-- CURRENCY --}}
                                                @if ($currency_model == 'multi_currency' && $currencies->count())
                                                    <div class="panel-divider"></div>
                                                    <div class="panel-section-header">{{ translate('currency') }}
                                                    </div>
                                                    <div class="currency-scroll">
                                                        @foreach ($currencies as $currency)
                                                            <a class="currency-item get-currency-change-function {{ session('currency_code') == $currency->code ? 'active' : '' }}"
                                                                href="javascript:void(0)"
                                                                data-code="{{ $currency->code }}">
                                                                <span class="currency-sym">
                                                                    @if (!empty($currency->symbol_svg))
                                                                        {!! $currency->symbol_svg !!}
                                                                    @else
                                                                        {{ $currency->symbol }}
                                                                    @endif
                                                                </span>
                                                                {{ $currency->name }}
                                                                @if (session('currency_code') == $currency->code)
                                                                    <i class="fi fi-sr-check check-icon"></i>
                                                                @endif
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                {{-- LANGUAGE --}}
                                                <div class="panel-divider"></div>
                                                <div class="panel-section-header">{{ translate('Language') }}</div>
                                                <div class="lang-wrap">
                                                    @include('layouts.front-end.partials.gtranslater')
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    {{-- ── END MOBILE BURGER ── --}}

                                    {{-- ── DESKTOP / TABLET INLINE (sm and above) ── --}}
                                    <div class="d-none d-sm-flex flex-nowrap align-items-center ml-2"
                                        style="gap: 6px;">

                                        {{-- Currency --}}
                                        @if ($currency_model == 'multi_currency' && $currencies->count())
                                            <div class="btn-group">
                                                <button type="button"
                                                    class="btn btn-outline-secondary dropdown-toggle btn-head-menus"
                                                    data-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                    <span class="btn-text text-white">
                                                        {{ session('currency_code') }}
                                                        ({{ session('currency_symbol') }})
                                                    </span>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right shadow-sm"
                                                    style="min-width: 200px; border-radius: 8px; padding: 0.5rem;">
                                                    @foreach ($currencies as $currency)
                                                        <a class="dropdown-item get-currency-change-function currency-dropdown-item {{ session('currency_code') == $currency->code ? 'active bg-light font-weight-bold' : '' }}"
                                                            href="javascript:void(0)"
                                                            data-code="{{ $currency->code }}">
                                                            <span class="currency-icon-wrapper">
                                                                <span class="currency-symbol">
                                                                    @if (!empty($currency->symbol_svg))
                                                                        {!! $currency->symbol_svg !!}
                                                                    @else
                                                                        {{ $currency->symbol }}
                                                                    @endif
                                                                </span>
                                                            </span>
                                                            <div class="d-flex flex-column ml-2">
                                                                <span
                                                                    class="currency-name">{{ $currency->name }}</span>
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Google Translate --}}
                                        @include('layouts.front-end.partials.gtranslater')

                                        {{-- Auth: Logged In --}}
                                        <?php if(auth('customer')->check()): ?>
                                            <?php
                                                $profileImgDesktop = auth('customer')->user()->image
                                                    && file_exists(storage_path('app/public/profile/' . auth('customer')->user()->image))
                                                        ? asset('storage/app/public/profile/' . auth('customer')->user()->image)
                                                        : asset('public/assets/back-end/img/customer-info.png');
                                            ?>
                                            <div class="dropdown flex-shrink-0 position-relative">
                                                <button class="btn btn-outline-secondary dropdown-toggle btn-head-menus"
                                                    data-toggle="dropdown" type="button">
                                                    <img src="{{ $profileImgDesktop }}" alt="Profile">
                                                    <span class="btn-text text-white">
                                                        {{ ucwords(auth('customer')->user()->f_name) }}
                                                    </span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li class="dropdown-item">
                                                        <a href="{{ route('wallet') }}">
                                                            <i class="fi fi-sr-wallet mr-2"></i>
                                                            {{ translate('my_wallet') }}<br>
                                                            <small style="margin-left:22px;">
                                                                ({{ webCurrencyConverter(auth('customer')->user()->wallet_balance ?? 0) }})
                                                            </small>
                                                        </a>
                                                    </li>
                                                    <li class="dropdown-item">
                                                        <a href="{{ route('account-oder') }}">
                                                            <i class="fi fi-sr-box mr-2"></i>
                                                            {{ translate('my_Order') }}
                                                        </a>
                                                    </li>
                                                    <li class="dropdown-item">
                                                        <a href="{{ route('user-account') }}">
                                                            <i class="ec ec-user mr-2"></i>
                                                            {{ translate('my_Profile') }}
                                                        </a>
                                                    </li>
                                                    <li class="dropdown-item">
                                                        <a href="{{ route('customer.auth.logout') }}">
                                                            <i class="fi fi-sr-sign-out-alt mr-2"></i>
                                                            {{ translate('logout') }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                        <?php endif; ?>
                                        <?php if(auth('seller')->check()): ?>
                                            <div class="dropdown flex-shrink-0 position-relative">
                                                <button class="btn btn-outline-secondary dropdown-toggle btn-head-menus"
                                                    data-toggle="dropdown" type="button">
                                                    <i class="ec ec-user" style="flex-shrink:0;"></i>
                                                    <span class="btn-text text-white">
                                                        {{ optional(auth('seller')->user()->shop)->name ?? ucwords(auth('seller')->user()->f_name) }}
                                                    </span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li class="dropdown-item">
                                                        <a href="{{ route('vendor.dashboard.index') }}">
                                                            <i class="ec ec-user mr-2"></i>
                                                            {{ translate('vendor_dashboard') }}
                                                        </a>
                                                    </li>
                                                    <li class="dropdown-item">
                                                        <a href="{{ route('vendor.auth.logout') }}">
                                                            <i class="fi fi-sr-sign-out-alt mr-2"></i>
                                                            {{ translate('logout') }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                            {{-- Auth: Guest --}}
                                        <?php endif; ?>
                                        <?php if(auth('freelancer')->check()): ?>
                                            <div class="dropdown flex-shrink-0 position-relative">
                                                <button class="btn btn-outline-secondary dropdown-toggle btn-head-menus"
                                                    data-toggle="dropdown" type="button">
                                                    <i class="ec ec-user" style="flex-shrink:0;"></i>
                                                    <span class="btn-text text-white">
                                                        {{ ucwords(auth('freelancer')->user()->f_name) }}
                                                    </span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li class="dropdown-item">
                                                        <a href="{{ route('freelancer.dashboard.index') }}">
                                                            <i class="ec ec-user mr-2"></i>
                                                            {{ translate('freelancer_dashboard') }}
                                                        </a>
                                                    </li>
                                                    <li class="dropdown-item">
                                                        <a href="{{ route('freelancer.auth.logout') }}">
                                                            <i class="fi fi-sr-sign-out-alt mr-2"></i>
                                                            {{ translate('logout') }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                            {{-- Auth: Guest --}}
                                        <?php endif; ?>
                                        <?php if(!auth('customer')->check() && !auth('seller')->check() && !auth('freelancer')->check()): ?>
                                            <div class="dropdown flex-shrink-0 position-relative">
                                                <button class="btn btn-outline-secondary dropdown-toggle btn-head-menus"
                                                    type="button" data-toggle="dropdown">
                                                    <i class="ec ec-user" style="flex-shrink:0;"></i>
                                                    <span class="btn-text text-white">{{ __('LogIn') }}</span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li class="dropdown-item">
                                                        <i class="ec ec-user mr-1"></i>
                                                        <a
                                                            href="{{ route('customer.auth.login') }}">{{ __('Customer') }}</a>
                                                    </li>
                                                    <li class="dropdown-item">
                                                        <i class="ec ec-user mr-1"></i>
                                                        <a
                                                            href="{{ route('vendor.auth.login.index') }}">{{ __('Vendor') }}</a>
                                                    </li>
                                                    <li class="dropdown-item">
                                                        <i class="ec ec-user mr-1"></i>
                                                        <a
                                                            href="{{ route('freelancer.auth.login.index') }}">{{ __('Freelancer') }}</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                    {{-- ── END DESKTOP / TABLET INLINE ── --}}

                                </li>
                                {{-- ══ END CURRENCY + TRANSLATE + USER ══ --}}

                            </ul>
                        </div>
                    </div>
                    {{-- ── End Header Icons ── --}}

                </div>{{-- ── end .header-inner-row ── --}}
            </div>
        </div>
        {{-- ===== END LOGO + SEARCH + ICONS BAR ===== --}}

        {{-- ===== DESKTOP NAV MENU ===== --}}
        <div class="d-none d-xl-block bg-menu border-color-6">
            <div class="container-fluid">
                <div class="min-height-45">
                    <div class="nav-scroll-wrapper">
                        <button class="nav-scroll-btn" id="navScrollLeft" aria-label="Scroll left">&#8249;</button>
                        <div class="nav-scroll-inner" id="navScrollInner">
                    <nav
                        class="navbar navbar-expand-md u-header__navbar u-header__navbar--no-space desktop-category-scroll" style="width:100%;">
                        <div id="navBar"
                            class="collapse navbar-collapse u-header__navbar-collapse justify-content-center">
                            <ul class="navbar-nav u-header__navbar-nav desktop-category-menu">

                                <?php $hireFreelancerDesktopInserted = false; ?>
                                @foreach ($megacategorie as $megacat)
                                    @php
                                        $hasSubs = $megacat->childes->count() > 0;
                                        $hasMega =
                                            $hasSubs && $megacat->childes->contains(fn($s) => $s->childes->count() > 0);
                                        $subChunks = $hasSubs ? $megacat->childes->chunk(5) : collect();
                                    @endphp

                                    <li class="nav-item u-header__nav-item desktop-category-item">

                                        <a id="megaMenu-{{ $megacat->id }}" class="nav-link desktop-category-link"
                                            href="{{ route('products', ['category_id' => $megacat->id, 'data_from' => 'category', 'page' => 1]) }}">
                                            {{ $megacat->name }}
                                            @if ($hasSubs)
                                                <span class="caret-icon">&#9662;</span>
                                            @endif
                                        </a>

                                        @if ($hasMega)
                                            <div class="mega-dropdown-panel">
                                                <div class="mega-panel-body">
                                                    @foreach ($subChunks as $chunkIndex => $chunk)
                                                        @if ($chunkIndex > 0)
                                                            <div
                                                                style="border-top: 1px solid #f0f0f0; margin: 4px 0 18px;">
                                                            </div>
                                                        @endif
                                                        <div class="mega-cols-row">
                                                            @foreach ($chunk as $subcat)
                                                                <div class="mega-sub-col">
                                                                    <a class="mega-sub-title"
                                                                        href="{{ $subcat->slug === 'latest-listings' ? route('products', ['data_from' => 'latest', 'page' => 1]) : route('products', ['sub_category_id' => $subcat->id, 'data_from' => 'category', 'page' => 1]) }}">
                                                                        {{ $subcat->name }}
                                                                    </a>
                                                                    @if ($subcat->childes->count())
                                                                        <ul class="mega-subsub-list">
                                                                            @foreach ($subcat->childes->take(6) as $childcat)
                                                                                <li>
                                                                                    <a 
                                                                                        href="{{ route('products', ['sub_sub_category_id' => $childcat->id, 'data_from' => 'category', 'page' => 1]) }}">
                                                                                        {{ $childcat->name }}
                                                                                    </a>
                                                                                </li>
                                                                            @endforeach
                                                                            @if ($subcat->childes->count() > 6)
                                                                                <li class="more-link">
                                                                                    <a
                                                                                        href="{{ $subcat->slug === 'latest-listings' ? route('products', ['data_from' => 'latest', 'page' => 1]) : route('products', ['sub_category_id' => $subcat->id, 'data_from' => 'category', 'page' => 1]) }}">
                                                                                        +{{ $subcat->childes->count() - 6 }}
                                                                                        more
                                                                                    </a>
                                                                                </li>
                                                                            @endif
                                                                        </ul>
                                                                    @else
                                                                        <ul class="mega-subsub-list">
                                                                            <li><span
                                                                                    style="color:#ccc; font-size:11px;">—</span>
                                                                            </li>
                                                                        </ul>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                </div>

                                                {{-- "View all" footer — no count pill --}}
                                                <div class="mega-panel-footer">
                                                    <a class="mega-view-all"
                                                        href="{{ route('products', ['category_id' => $megacat->id, 'data_from' => 'category', 'page' => 1]) }}">
                                                        View all in {{ $megacat->name }} &rarr;
                                                    </a>
                                                </div>
                                            </div>
                                        @elseif($hasSubs)
                                            <div class="simple-dropdown-panel">
                                                @foreach ($megacat->childes as $subcat)
                                                    <a
                                                        href="{{ $subcat->slug === 'latest-listings' ? route('products', ['data_from' => 'latest', 'page' => 1]) : route('products', ['sub_category_id' => $subcat->id, 'data_from' => 'category', 'page' => 1]) }}">
                                                        {{ $subcat->name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif

                                    </li>
                                    @if (!$hireFreelancerDesktopInserted && ($megacat->slug ?? null) === 'events' && isset($hireFreelancerMenu))
                                        <?php $hireFreelancerDesktopInserted = true; ?>
                                        <li class="nav-item u-header__nav-item desktop-category-item {{ $hireFreelancerMenu->count() ? 'hire-freelancer-nav-item' : '' }}">
                                            <a id="hireFreelancerMegaMenu"
                                               class="nav-link desktop-category-link {{ $hireFreelancerMenu->count() ? 'hire-freelancer-trigger' : '' }}"
                                               href="{{ route('hire-freelancer') }}"
                                               aria-haspopup="true"
                                               aria-expanded="false">
                                                {{ translate('hire_freelancer') }}
                                                @if($hireFreelancerMenu->count())
                                                    <span class="caret-icon">&#9662;</span>
                                                @endif
                                            </a>

                                            <div class="mega-dropdown-panel hire-freelancer-panel {{ $hireFreelancerMenu->count() ? '' : 'd-none' }}" role="menu" aria-labelledby="hireFreelancerMegaMenu">
                                                <div class="hire-mega-layout">
                                                    <div class="hire-mega-categories" role="presentation">
                                                        @foreach($hireFreelancerMenu as $freelancerCategory)
                                                            <a class="hire-mega-category {{ $loop->first ? 'active' : '' }}"
                                                               href="{{ route('hire-freelancer', ['category' => $freelancerCategory->slug]) }}"
                                                               data-hire-category="{{ $freelancerCategory->id }}"
                                                               role="menuitem">
                                                                <span>{{ $freelancerCategory->name }}</span>
                                                                <span aria-hidden="true">&#8250;</span>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                    <div class="hire-mega-content">
                                                        @foreach($hireFreelancerMenu as $freelancerCategory)
                                                            <div class="hire-mega-services {{ $loop->first ? 'active' : '' }}"
                                                                 data-hire-category-panel="{{ $freelancerCategory->id }}">
                                                                <div class="hire-mega-group-title">{{ $freelancerCategory->name }}</div>
                                                                <div class="hire-mega-service-grid">
                                                                    @forelse($freelancerCategory->activeSpecializations as $specialization)
                                                                        <a class="hire-mega-service"
                                                                           href="{{ route('hire-freelancer', ['specialization' => $specialization->slug]) }}"
                                                                           role="menuitem">
                                                                            <span class="hire-mega-service-title">{{ $specialization->name }}</span>
                                                                            @if(filled($specialization->description))
                                                                                <span class="hire-mega-service-desc">{{ \Illuminate\Support\Str::limit(strip_tags($specialization->description), 88) }}</span>
                                                                            @endif
                                                                        </a>
                                                                    @empty
                                                                        <a class="hire-mega-service"
                                                                           href="{{ route('hire-freelancer', ['category' => $freelancerCategory->slug]) }}"
                                                                           role="menuitem">
                                                                            <span class="hire-mega-service-title">{{ translate('all_freelancer_services') }}</span>
                                                                        </a>
                                                                    @endforelse
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="mega-panel-footer">
                                                    <a class="mega-view-all" href="{{ route('hire-freelancer') }}">
                                                        {{ translate('all_freelancer_services') }} &rarr;
                                                    </a>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                @endforeach
                                @if (!$hireFreelancerDesktopInserted && isset($hireFreelancerMenu))
                                    <li class="nav-item u-header__nav-item desktop-category-item {{ $hireFreelancerMenu->count() ? 'hire-freelancer-nav-item' : '' }}">
                                        <a id="hireFreelancerMegaMenuFallback"
                                           class="nav-link desktop-category-link {{ $hireFreelancerMenu->count() ? 'hire-freelancer-trigger' : '' }}"
                                           href="{{ route('hire-freelancer') }}"
                                           aria-haspopup="true"
                                           aria-expanded="false">
                                            {{ translate('hire_freelancer') }}
                                            @if($hireFreelancerMenu->count())
                                                <span class="caret-icon">&#9662;</span>
                                            @endif
                                        </a>

                                        <div class="mega-dropdown-panel hire-freelancer-panel {{ $hireFreelancerMenu->count() ? '' : 'd-none' }}" role="menu" aria-labelledby="hireFreelancerMegaMenuFallback">
                                            <div class="hire-mega-layout">
                                                <div class="hire-mega-categories" role="presentation">
                                                    @foreach($hireFreelancerMenu as $freelancerCategory)
                                                        <a class="hire-mega-category {{ $loop->first ? 'active' : '' }}"
                                                           href="{{ route('hire-freelancer', ['category' => $freelancerCategory->slug]) }}"
                                                           data-hire-category="{{ $freelancerCategory->id }}"
                                                           role="menuitem">
                                                            <span>{{ $freelancerCategory->name }}</span>
                                                            <span aria-hidden="true">&#8250;</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                                <div class="hire-mega-content">
                                                    @foreach($hireFreelancerMenu as $freelancerCategory)
                                                        <div class="hire-mega-services {{ $loop->first ? 'active' : '' }}"
                                                             data-hire-category-panel="{{ $freelancerCategory->id }}">
                                                            <div class="hire-mega-group-title">{{ $freelancerCategory->name }}</div>
                                                            <div class="hire-mega-service-grid">
                                                                @forelse($freelancerCategory->activeSpecializations as $specialization)
                                                                    <a class="hire-mega-service"
                                                                       href="{{ route('hire-freelancer', ['specialization' => $specialization->slug]) }}"
                                                                       role="menuitem">
                                                                        <span class="hire-mega-service-title">{{ $specialization->name }}</span>
                                                                        @if(filled($specialization->description))
                                                                            <span class="hire-mega-service-desc">{{ \Illuminate\Support\Str::limit(strip_tags($specialization->description), 88) }}</span>
                                                                        @endif
                                                                    </a>
                                                                @empty
                                                                    <a class="hire-mega-service"
                                                                       href="{{ route('hire-freelancer', ['category' => $freelancerCategory->slug]) }}"
                                                                       role="menuitem">
                                                                        <span class="hire-mega-service-title">{{ translate('all_freelancer_services') }}</span>
                                                                    </a>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="mega-panel-footer">
                                                <a class="mega-view-all" href="{{ route('hire-freelancer') }}">
                                                    {{ translate('all_freelancer_services') }} &rarr;
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                @endif

                            </ul>
                        </div>
                    </nav>
                        </div>{{-- nav-scroll-inner --}}
                        <button class="nav-scroll-btn" id="navScrollRight" aria-label="Scroll right">&#8250;</button>
                    </div>{{-- nav-scroll-wrapper --}}
                </div>
            </div>
        </div>
        {{-- ===== END DESKTOP NAV MENU ===== --}}

    </div>
</header>
{{-- ========== END HEADER ========== --}}

{{-- ========== SCRIPTS ========== --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var sidebar = document.getElementById('sidebarHeader1');
        var sidebarInvoker = document.getElementById('sidebarHeaderInvokerMenu');
        if (sidebar && sidebarInvoker) {
            var setSidebarAriaHidden = function(hidden) {
                sidebar.setAttribute('aria-hidden', hidden ? 'true' : 'false');
            };
            setSidebarAriaHidden(true);
            sidebarInvoker.addEventListener('click', function() {
                setTimeout(function() {
                    var isOpen = sidebar.classList.contains('u-unfold--shown');
                    setSidebarAriaHidden(!isOpen);
                }, 50);
            });
            document.querySelectorAll('[data-unfold-target="#sidebarHeader1"]').forEach(function(el) {
                el.addEventListener('click', function() {
                    if (el === sidebarInvoker) {
                        return;
                    }
                    setTimeout(function() {
                        var isOpen = sidebar.classList.contains('u-unfold--shown');
                        setSidebarAriaHidden(!isOpen);
                    }, 50);
                });
            });
        }

        document.querySelectorAll('.js-dropdown-toggle').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var wrapper = btn.closest('.dropdown-wrapper');
                document.querySelectorAll('.dropdown-wrapper.active').forEach(function(el) {
                    if (el !== wrapper) el.classList.remove('active');
                });
                wrapper.classList.toggle('active');
            });
        });
        document.body.addEventListener('click', function() {
            document.querySelectorAll('.dropdown-wrapper.active').forEach(function(el) {
                el.classList.remove('active');
            });
        });

        // ── Announcement close ──
        var closeBtn = document.querySelector('.__close-announcement');
        if (closeBtn) {
            closeBtn.style.cssText = 'position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;font-weight:700;padding:2px 8px;';
            closeBtn.addEventListener('click', function() {
                var bar = document.getElementById('announcement');
                if (bar) bar.style.display = 'none';
            });
        }

        // ── Category bar mega menus (fixed position below hovered item) ──
        function positionCategoryDropdown(item) {
            var panel = item.querySelector('.mega-dropdown-panel, .simple-dropdown-panel');
            var isOpen = item.matches(':hover') || item.matches(':focus-within') || item.classList.contains('hire-menu-open');
            if (!panel || !isOpen) {
                return;
            }
            var rect = item.getBoundingClientRect();
            var panelWidth = panel.offsetWidth || (panel.classList.contains('mega-dropdown-panel') ? 860 : 220);
            var left = Math.max(8, Math.min(rect.left, window.innerWidth - panelWidth - 8));
            panel.style.top = rect.bottom + 'px';
            panel.style.left = left + 'px';
        }

        document.querySelectorAll('.desktop-category-item').forEach(function(item) {
            if (!item.querySelector('.mega-dropdown-panel, .simple-dropdown-panel')) {
                return;
            }
            item.addEventListener('mouseenter', function() {
                requestAnimationFrame(function() {
                    positionCategoryDropdown(item);
                });
            });
            item.addEventListener('mouseleave', function() {
                var panel = item.querySelector('.mega-dropdown-panel, .simple-dropdown-panel');
                if (panel) {
                    panel.style.top = '';
                    panel.style.left = '';
                }
            });
        });

        document.querySelectorAll('.hire-freelancer-nav-item').forEach(function(item) {
            var trigger = item.querySelector('.hire-freelancer-trigger');
            if (!trigger) {
                return;
            }

            function closeMenu() {
                item.classList.remove('hire-menu-open');
                trigger.setAttribute('aria-expanded', 'false');
            }

            function openMenu() {
                item.classList.add('hire-menu-open');
                trigger.setAttribute('aria-expanded', 'true');
                requestAnimationFrame(function() {
                    positionCategoryDropdown(item);
                });
            }

            trigger.addEventListener('click', function(event) {
                if (!item.classList.contains('hire-menu-open')) {
                    event.preventDefault();
                    openMenu();
                }
            });

            item.addEventListener('focusin', openMenu);
            item.addEventListener('mouseenter', openMenu);
            item.addEventListener('mouseleave', function() {
                if (!item.matches(':focus-within')) {
                    closeMenu();
                }
            });
            item.addEventListener('focusout', function() {
                setTimeout(function() {
                    if (!item.matches(':focus-within')) {
                        closeMenu();
                    }
                }, 0);
            });
            item.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeMenu();
                    trigger.focus();
                }
            });

            item.querySelectorAll('.hire-mega-category').forEach(function(categoryLink) {
                function activateCategory() {
                    var categoryId = categoryLink.getAttribute('data-hire-category');
                    item.querySelectorAll('.hire-mega-category').forEach(function(link) {
                        link.classList.toggle('active', link === categoryLink);
                    });
                    item.querySelectorAll('.hire-mega-services').forEach(function(panel) {
                        panel.classList.toggle('active', panel.getAttribute('data-hire-category-panel') === categoryId);
                    });
                }

                categoryLink.addEventListener('mouseenter', activateCategory);
                categoryLink.addEventListener('focus', activateCategory);
            });
        });

        document.addEventListener('click', function(event) {
            document.querySelectorAll('.hire-freelancer-nav-item.hire-menu-open').forEach(function(item) {
                if (item.contains(event.target)) {
                    return;
                }
                var trigger = item.querySelector('.hire-freelancer-trigger');
                item.classList.remove('hire-menu-open');
                if (trigger) {
                    trigger.setAttribute('aria-expanded', 'false');
                }
            });
        });

        // ── Nav scroll arrows ──
        var scrollInner = document.getElementById('navScrollInner');
        var btnLeft     = document.getElementById('navScrollLeft');
        var btnRight    = document.getElementById('navScrollRight');
        if (scrollInner && btnLeft && btnRight) {
            function updateNavArrows() {
                var atStart = scrollInner.scrollLeft <= 2;
                var atEnd   = scrollInner.scrollLeft + scrollInner.clientWidth >= scrollInner.scrollWidth - 2;
                var needsScroll = scrollInner.scrollWidth > scrollInner.clientWidth + 4;
                btnLeft.classList.toggle('visible',  needsScroll && !atStart);
                btnRight.classList.toggle('visible', needsScroll && !atEnd);
            }
            function repositionOpenCategoryDropdowns() {
                document.querySelectorAll('.desktop-category-item:hover').forEach(positionCategoryDropdown);
            }
            btnLeft.addEventListener('click',  function() { scrollInner.scrollBy({ left: -200, behavior: 'smooth' }); });
            btnRight.addEventListener('click', function() { scrollInner.scrollBy({ left:  200, behavior: 'smooth' }); });
            scrollInner.addEventListener('scroll', function() {
                updateNavArrows();
                repositionOpenCategoryDropdowns();
            });
            window.addEventListener('resize', function() {
                updateNavArrows();
                repositionOpenCategoryDropdowns();
            });
            window.addEventListener('scroll', repositionOpenCategoryDropdowns, true);
            updateNavArrows();
        }

        // ── Auto-submit the header search form when a category is picked ──
        $(document).on('changed.bs.select', '.custom-search-categories-select', function () {
            this.closest('form').submit();
        });
    });
</script>

@auth('customer')
    <script>
        // Poll for new notifications so the bell badge and dropdown list update
        // live in the background — no full page reload needed to see a new
        // quote reply, chat message, or delivery status update come in.
        (function () {
            var badgeParent = document.getElementById('customerNotificationDropdown');
            var itemsContainer = document.getElementById('customerNotificationItems');
            if (!badgeParent || !itemsContainer) return;

            function renderBadge(count) {
                var existing = badgeParent.querySelector('.notification-badge');
                if (count > 0) {
                    if (existing) {
                        existing.textContent = count;
                    } else {
                        var span = document.createElement('span');
                        span.className = 'notification-badge width-22 height-22 bg-primary position-absolute d-flex align-items-center justify-content-center rounded-circle left-12 top-8 font-weight-bold font-size-12';
                        span.textContent = count;
                        badgeParent.appendChild(span);
                    }
                } else if (existing) {
                    existing.remove();
                }
            }

            function pollCustomerNotifications() {
                fetch('{{ route('customer-notifications.poll') }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                }).then(function (response) {
                    return response.ok ? response.json() : null;
                }).then(function (data) {
                    if (!data) return;
                    renderBadge(data.count);
                    itemsContainer.innerHTML = data.html;
                }).catch(function () {
                    // Silent — a missed poll just tries again next interval.
                });
            }

            setInterval(pollCustomerNotifications, 8000);

            // Re-sync immediately on returning to this tab instead of waiting for the
            // next interval tick — a background tab throttles setInterval anyway, so
            // without this a badge can sit stale for well over 8s until you switch back.
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'visible') {
                    pollCustomerNotifications();
                }
            });

            // "Mark all as read" is submitted via AJAX instead of a normal
            // form POST, because a normal POST navigates the whole browser
            // tab to the notifications URL — if the session/CSRF token had
            // expired in the background that dumped the user on Laravel's
            // raw "419 | Page Expired" page. Delegate the listener because
            // itemsContainer's innerHTML (and the form inside it) is
            // replaced wholesale on every poll.
            itemsContainer.addEventListener('submit', function (e) {
                var form = e.target.closest('.customer-notification-mark-all-form');
                if (!form) return;
                e.preventDefault();

                var tokenInput = form.querySelector('input[name="_token"]');
                fetch(form.getAttribute('action'), {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': tokenInput ? tokenInput.value : '',
                    },
                    credentials: 'same-origin',
                }).then(function (response) {
                    if (!response.ok) {
                        // Session/token genuinely expired — refresh the page
                        // to establish a fresh one instead of showing 419.
                        window.location.reload();
                        return;
                    }
                    pollCustomerNotifications();
                }).catch(function () {
                    // Network hiccup — leave the list as-is, next poll will sync it.
                });
            });
        })();
    </script>
@endauth
