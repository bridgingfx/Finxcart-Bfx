@php
    $gtranslateBootstrapVersion = $gtranslateBootstrapVersion ?? 4;
    $gtranslateDashboard = $gtranslateDashboard ?? false;
@endphp

<!-- Custom GTranslate Wrapper -->
<div class="gtranslate_wrapper {{ $gtranslateDashboard ? 'gtranslate-dashboard' : '' }}">
    <style>
        .gt_switcher_wrapper { display: none !important; }

        /* ── Shared list styles ── */
        .gtranslate-menu-list { min-width: 180px; border-radius: 8px; padding: 5px 0; }
        .gtranslate-menu-list li { margin: 0 !important; }
        .gtranslate_wrapper li:before { content: none !important; }

        .gtranslate-menu-list li a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            font-weight: 500;
            font-size: 14px;
            color: #333;
            text-decoration: none;
            white-space: nowrap;
        }
        .gtranslate-menu-list li a img {
            width: 20px;
            height: 14px;
            border-radius: 2px;
            flex-shrink: 0;
        }
        .gtranslate-menu-list li a:hover,
        .gtranslate-menu-list li a.active-lang {
            background-color: #f18b32;
            color: #fff;
        }

        .gtranslate-dashboard .gtranslate-dashboard-button {
            width: 40px;
            height: 40px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 50%;
            background: transparent;
            box-shadow: none;
        }
        .gtranslate-dashboard .gtranslate-dashboard-button:hover,
        .gtranslate-dashboard .gtranslate-dashboard-button:focus {
            background: rgba(7, 59, 116, .08);
        }
        .gtranslate-dashboard .gtranslate-dashboard-button::after,
        .gtranslate-dashboard .lang-label {
            display: none;
        }
        .gtranslate-dashboard .gtranslate-menu-list {
            max-height: min(420px, calc(100vh - 90px));
            overflow-y: auto;
            border: 1px solid rgba(7, 59, 116, .12);
            box-shadow: 0 14px 34px rgba(7, 59, 116, .16) !important;
        }

        /* ── Mobile panel: show list flat, hide the dropdown button ── */
        .mobile-menu-panel .gtranslate_wrapper .dropdown > button { display: none !important; }
        .mobile-menu-panel .gtranslate_wrapper .gtranslate-menu-list {
            display: block !important;
            position: static !important;
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            padding: 0 !important;
            min-width: 0 !important;
            width: 100% !important;
            max-height: 200px;
            overflow-y: auto;
        }
        .mobile-menu-panel .gtranslate_wrapper .gtranslate-menu-list li a {
            font-size: 13px;
            padding: 8px 14px;
            color: #333;
            border-radius: 0;
        }
        .mobile-menu-panel .gtranslate_wrapper .gtranslate-menu-list li a img {
            width: 28px !important;
            height: 28px !important;
            border-radius: 50% !important;
            object-fit: cover;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.06);
        }
        .mobile-menu-panel .gtranslate_wrapper .gtranslate-menu-list li a:hover {
            background: #f1f3f5;
        }
        .mobile-menu-panel .gtranslate_wrapper .gtranslate-menu-list li a.active-lang {
            background: #eaf4ff;
            color: #003f6b;
            font-weight: 600;
        }
        .mobile-menu-panel .gtranslate_wrapper .gtranslate-menu-list li a.active-lang::after {
            content: "\2713";
            margin-left: auto;
            font-size: 12px;
            color: #28a745;
        }
        @media (max-width: 575.98px) {
            .gtranslate-dashboard .gtranslate-menu-list {
                position: fixed !important;
                top: 62px !important;
                right: 8px !important;
                left: auto !important;
                width: min(230px, calc(100vw - 16px)) !important;
                min-width: 0 !important;
                max-height: calc(100vh - 76px) !important;
                transform: none !important;
                border-radius: 12px;
                z-index: 1085;
            }
        }
    </style>

    <div class="dropdown flex-shrink-0 position-relative">
        <button class="btn btn-outline-secondary dropdown-toggle btn-head-menus notranslate {{ $gtranslateDashboard ? 'gtranslate-dashboard-button' : '' }}"
                type="button"
                @if($gtranslateBootstrapVersion >= 5)
                    data-bs-toggle="dropdown"
                    data-bs-display="static"
                @else
                    data-toggle="dropdown"
                    data-display="static"
                @endif
                aria-haspopup="true"
                aria-expanded="false"
                aria-label="{{ translate('language') }}"
                title="{{ translate('language') }}"
                translate="no">
            <img class="lang-flag" src="https://flagcdn.com/gb.svg" alt=""
                 style="width:20px;height:14px;border-radius:2px;flex-shrink:0;">
            <span class="btn-text text-white lang-label">English</span>
        </button>
        <ul class="dropdown-menu {{ $gtranslateBootstrapVersion >= 5 ? 'dropdown-menu-end' : 'dropdown-menu-right' }} gtranslate-menu-list shadow-sm notranslate" translate="no">
            <li><a href="#" data-lang="ar"    data-flag="https://flagcdn.com/kw.svg" data-label="العربية"><img src="https://flagcdn.com/kw.svg"> العربية</a></li>
            <li><a href="#" data-lang="en"    data-flag="https://flagcdn.com/gb.svg" data-label="English"><img src="https://flagcdn.com/gb.svg"> English</a></li>
            <li><a href="#" data-lang="hi"    data-flag="https://flagcdn.com/in.svg" data-label="हिन्दी"><img src="https://flagcdn.com/in.svg"> हिन्दी</a></li>
            <li><a href="#" data-lang="ms"    data-flag="https://flagcdn.com/my.svg" data-label="Malay"><img src="https://flagcdn.com/my.svg"> Malay</a></li>
            <li><a href="#" data-lang="ur"    data-flag="https://flagcdn.com/pk.svg" data-label="اُردُو"><img src="https://flagcdn.com/pk.svg"> اُردُو</a></li>
            <li><a href="#" data-lang="bn"    data-flag="https://flagcdn.com/bd.svg" data-label="বাংলা"><img src="https://flagcdn.com/bd.svg"> বাংলা</a></li>
            <li><a href="#" data-lang="fr"    data-flag="https://flagcdn.com/fr.svg" data-label="Français"><img src="https://flagcdn.com/fr.svg"> Français</a></li>
            <li><a href="#" data-lang="pt"    data-flag="https://flagcdn.com/pt.svg" data-label="Português"><img src="https://flagcdn.com/pt.svg"> Português</a></li>
            <li><a href="#" data-lang="it"    data-flag="https://flagcdn.com/it.svg" data-label="Italiano"><img src="https://flagcdn.com/it.svg"> Italiano</a></li>
            <li><a href="#" data-lang="nl"    data-flag="https://flagcdn.com/nl.svg" data-label="Nederlands"><img src="https://flagcdn.com/nl.svg"> Nederlands</a></li>
            <li><a href="#" data-lang="de"    data-flag="https://flagcdn.com/de.svg" data-label="Deutsch"><img src="https://flagcdn.com/de.svg"> Deutsch</a></li>
            <li><a href="#" data-lang="ru"    data-flag="https://flagcdn.com/ru.svg" data-label="Русский"><img src="https://flagcdn.com/ru.svg"> Русский</a></li>
            <li><a href="#" data-lang="es"    data-flag="https://flagcdn.com/es.svg" data-label="Español"><img src="https://flagcdn.com/es.svg"> Español</a></li>
            <li><a href="#" data-lang="zh-CN" data-flag="https://flagcdn.com/cn.svg" data-label="中文"><img src="https://flagcdn.com/cn.svg"> 中文</a></li>
            <li><a href="#" data-lang="th"    data-flag="https://flagcdn.com/th.svg" data-label="ไทย"><img src="https://flagcdn.com/th.svg"> ไทย</a></li>
            <li><a href="#" data-lang="tr"    data-flag="https://flagcdn.com/tr.svg" data-label="Türkçe"><img src="https://flagcdn.com/tr.svg"> Türkçe</a></li>
        </ul>
    </div>
</div>

<!-- GTranslate Widget -->
@once
<script>
    window.gtranslateSettings = {
        default_language: "en",
        native_language_names: true,
        languages: ["ar","en","hi","ms","ur","bn","fr","pt","it","nl","de","ru","es","zh-CN","th","tr"],
        wrapper_selector: ".gtranslate_wrapper",
        horizontal_position: "inline",
        flag_size: 16,
        switcher_vertical_position: "top"
    };
</script>
<script src="https://cdn.gtranslate.net/widgets/latest/dwf.js" defer></script>

<script>
(function () {
    var langMeta = {
        "ar":    { flag: "https://flagcdn.com/kw.svg", label: "العربية" },
        "en":    { flag: "https://flagcdn.com/gb.svg", label: "English" },
        "hi":    { flag: "https://flagcdn.com/in.svg", label: "हिन्दी" },
        "ms":    { flag: "https://flagcdn.com/my.svg", label: "Malay" },
        "ur":    { flag: "https://flagcdn.com/pk.svg", label: "اُردُو" },
        "bn":    { flag: "https://flagcdn.com/bd.svg", label: "বাংলা" },
        "fr":    { flag: "https://flagcdn.com/fr.svg", label: "Français" },
        "pt":    { flag: "https://flagcdn.com/pt.svg", label: "Português" },
        "it":    { flag: "https://flagcdn.com/it.svg", label: "Italiano" },
        "nl":    { flag: "https://flagcdn.com/nl.svg", label: "Nederlands" },
        "de":    { flag: "https://flagcdn.com/de.svg", label: "Deutsch" },
        "ru":    { flag: "https://flagcdn.com/ru.svg", label: "Русский" },
        "es":    { flag: "https://flagcdn.com/es.svg", label: "Español" },
        "zh-CN": { flag: "https://flagcdn.com/cn.svg", label: "中文" },
        "th":    { flag: "https://flagcdn.com/th.svg", label: "ไทย" },
        "tr":    { flag: "https://flagcdn.com/tr.svg", label: "Türkçe" }
    };

    function getCookieLang() {
        var match = document.cookie.match(/(?:^|; )googtrans=([^;]*)/);
        if (match) {
            var parts = decodeURIComponent(match[1]).split('/');
            return parts[2] || null;
        }
        return null;
    }

    function updateButtons(lang) {
        var meta = langMeta[lang];
        if (!meta) return;
        document.querySelectorAll('.lang-flag').forEach(function (img) { img.src = meta.flag; });
        document.querySelectorAll('.lang-label').forEach(function (el) { el.textContent = meta.label; });
        document.querySelectorAll('.gtranslate-menu-list a[data-lang]').forEach(function (a) {
            a.classList.toggle('active-lang', a.getAttribute('data-lang') === lang);
        });
    }

    function switchLang(lang) {
        if (typeof doGTranslate === 'function') {
            doGTranslate('en|' + lang);
        } else {
            var exp = new Date();
            exp.setFullYear(exp.getFullYear() + 1);
            document.cookie = 'googtrans=/en/' + lang + '; expires=' + exp.toUTCString() + '; path=/';
            document.cookie = 'googtrans=/en/' + lang + '; expires=' + exp.toUTCString() + '; path=/; domain=' + location.hostname;
            location.reload();
        }
        updateButtons(lang);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var activeLang = getCookieLang();
        if (activeLang && activeLang !== 'en') {
            updateButtons(activeLang);
        } else {
            // mark English active by default
            document.querySelectorAll('.gtranslate-menu-list a[data-lang="en"]').forEach(function (a) {
                a.classList.add('active-lang');
            });
        }

        document.querySelectorAll('.gtranslate-menu-list a[data-lang]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                switchLang(this.getAttribute('data-lang'));
            });
        });
    });
})();
</script>
@endonce
