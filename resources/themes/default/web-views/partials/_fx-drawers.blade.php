{{--
    _fx-drawers.blade.php — quick-preview drawer + compare tray/drawer markup.
    Include once per page that renders fx listing cards.
--}}
<div class="fx-drawer-overlay" data-fx-filter-overlay hidden></div>
<div class="fx-drawer-overlay" data-fx-qp-overlay hidden></div>
<aside class="fx-drawer" role="dialog" aria-modal="true" aria-labelledby="fx-qp-title"
       data-fx-qp-drawer hidden>
    <button type="button" class="fx-drawer__close" data-fx-qp-close aria-label="{{ translate('close') }}">×</button>
    <h2 id="fx-qp-title" class="fx-qp__title">{{ translate('quick_preview') }}</h2>
    <div data-fx-qp-body>
        <div class="fx-skeleton" style="height:220px"></div>
        <div class="fx-skeleton" style="height:20px;margin-top:12px"></div>
        <div class="fx-skeleton" style="height:20px;margin-top:8px;width:60%"></div>
    </div>
</aside>

<div class="fx-compare-tray" data-fx-compare-tray hidden role="region" aria-label="{{ translate('compare_tray') }}">
    <div class="fx-compare-tray__items" data-fx-tray-items></div>
    <p class="fx-meta fx-compare-tray__count" data-fx-tray-count></p>
    <div class="fx-compare-tray__actions">
        <button type="button" class="fx-btn fx-btn-sm fx-btn-amber" data-fx-tray-open>
            {{ translate('compare') }}
        </button>
        <button type="button" class="fx-btn fx-btn-sm fx-btn-ghost" data-fx-tray-clear>
            {{ translate('clear') }}
        </button>
    </div>
</div>

<div class="fx-drawer-overlay" data-fx-cmp-overlay hidden></div>
<aside class="fx-drawer fx-compare-drawer" role="dialog" aria-modal="true" aria-labelledby="fx-cmp-title"
       data-fx-cmp-drawer data-compare-url="{{ route('fx.compare-table') }}" hidden>
    <button type="button" class="fx-drawer__close" data-fx-cmp-close aria-label="{{ translate('close') }}">×</button>
    <h2 id="fx-cmp-title">{{ translate('compare_products') }}</h2>
    <div data-fx-cmp-body></div>
</aside>

<div class="fx-toast-zone" data-fx-toast-zone aria-live="polite"></div>
