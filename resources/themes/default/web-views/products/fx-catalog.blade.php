{{--
    fx-catalog.blade.php — fx catalog / search results page.
    GET-based: query, filters, sort and page all live in URL params (shareable, Back-safe).
    Facets shown ONLY where real backend data exists (type, price, category, brand).
--}}
@extends('layouts.front-end.app')

@section('title', $pageTitleContent ?? translate('products'))

@push('css_or_js')
    <meta property="og:url" content="{{ url()->current() }}">
    <style>
        /* Scoped compensation (fx pages only): the shared header's mobile row
           (burger + 160px logo + actions) overflows viewports under ~430px.
           Cap the logo until the header owner fixes the root cause. */
        @media (max-width: 767px) {
            .fxh-logo img { max-width: 108px !important; height: auto !important; }
            body > .row { margin-left: 0; margin-right: 0; }
        }
        @media (max-width: 360px) {
            /* 360px and below: icon-only header actions so the row fits.
               Sign-in lives in the drawer + bottom nav Account tab. */
            .fxh-logo img { max-width: 84px !important; }
            .fxh-action > span:last-child { display: none !important; }
            .fxh-acct > a.fx-btn { display: none !important; }
            .fxh-acct-btn .fxh-acct-name { max-width: 56px !important; }
        }
        .fx-catalog__layout { display: grid; grid-template-columns: 260px 1fr; gap: 24px; align-items: start; }
        .fx-catalog__rail { position: sticky; top: 16px; }
        .fx-catalog__toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
        .fx-catalog__toolbar .fx-sort-desktop { display: flex; align-items: center; gap: 8px; margin: 0; white-space: nowrap; flex-shrink: 0; }
        .fx-catalog__grid { grid-template-columns: repeat(3, 1fr); }
        .fx-catalog__grid .fx-listing-card--wide { grid-column: 1 / -1; }
        .fx-catalog__rail .fx-accordion__body { max-height: 240px; overflow: auto; }
        .fx-filter-check { display: flex; align-items: center; gap: 8px; padding: 6px 0; font-size: 14px; cursor: pointer; min-height: 36px; }
        .fx-filter-check input { width: 18px; height: 18px; accent-color: #203F60; }
        .fx-filter-sheet-head { display: none; }
        .fx-mobile-filterbar { display: none; }
        /* listing card internals */
        .fx-listing-card { display: flex; flex-direction: column; padding: 0; overflow: hidden; }
        .fx-listing-card__media { position: relative; aspect-ratio: 1 / 1; background: var(--fx-canvas); }
        .fx-listing-card__media img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .fx-listing-card__typetag { position: absolute; top: 8px; inset-inline-start: 8px; }
        .fx-listing-card__discount { position: absolute; top: 8px; inset-inline-end: 8px; background: #B3261E; color: #fff; }
        .fx-listing-card__stock { position: absolute; bottom: 8px; inset-inline-start: 8px; background: rgba(23,43,66,.85); color: #fff; }
        .fx-listing-card__body { display: flex; flex-direction: column; gap: 6px; padding: 12px 12px 14px; flex: 1; }
        .fx-listing-card__title { font-size: 15px; font-weight: 600; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .fx-listing-card__title a { color: inherit; text-decoration: none; }
        .fx-listing-card__provider { margin: 0; font-size: 13px; }
        .fx-listing-card__rating { margin: 0; font-size: 13px; }
        .fx-listing-card__stars { color: #B7791F; letter-spacing: 1px; }
        .fx-listing-card__price { margin-top: 2px; }
        .fx-listing-card__attrs { display: grid; gap: 4px; margin: 4px 0 0; padding: 0; }
        .fx-listing-card__attr { display: flex; gap: 6px; font-size: 13px; }
        .fx-listing-card__attr dt { color: var(--fx-muted); font-weight: 600; }
        .fx-listing-card__attr dd { margin: 0; }
        .fx-listing-card__delivery { margin: 0; font-size: 13px; }
        .fx-listing-card__actions { display: flex; gap: 8px; margin-top: auto; padding-top: 8px; align-items: center; }
        .fx-listing-card__actions .fx-btn:first-child { flex: 1; }
        .fx-listing-card--wide { flex-direction: row; }
        .fx-listing-card--wide .fx-listing-card__media { width: 220px; flex: none; aspect-ratio: 1 / 1; }
        .fx-save-btn.is-saved { color: #B3261E; }
        /* search suggestions */
        .fx-search-wrap { position: relative; }
        .fx-search__suggest { position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: var(--fx-surface); border: 1px solid var(--fx-border); border-radius: 12px; box-shadow: 0 12px 32px rgba(23,43,66,.16); z-index: 80; max-height: 60vh; max-height: 60dvh; overflow: auto; padding: 8px; }
        .fx-search__group { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--fx-muted); padding: 8px 8px 4px; }
        .fx-search__item { display: flex; align-items: center; gap: 10px; width: 100%; text-align: start; border: 0; background: none; padding: 10px 8px; border-radius: 8px; font-size: 15px; cursor: pointer; min-height: 44px; color: var(--fx-text); text-decoration: none; }
        .fx-search__item[aria-selected="true"], .fx-search__item:hover { background: var(--fx-canvas); }
        .fx-search__item img { width: 36px; height: 36px; object-fit: cover; border-radius: 8px; flex: none; }
        .fx-search__retry { padding: 12px; text-align: center; }
        /* compare tray + drawers */
        .fx-compare-tray { position: fixed; bottom: 12px; left: 50%; transform: translateX(-50%); z-index: 95; display: flex; align-items: center; gap: 12px; background: var(--fx-surface); border: 1px solid var(--fx-border); border-radius: 16px; padding: 8px 12px; box-shadow: 0 12px 32px rgba(23,43,66,.2); max-width: calc(100vw - 24px); padding-bottom: calc(8px + env(safe-area-inset-bottom)); }
        .fx-compare-tray[hidden] { display: none; }
        .fx-compare-tray__items { display: flex; gap: 6px; }
        .fx-compare-tray__thumb { position: relative; width: 44px; height: 44px; border-radius: 8px; overflow: hidden; border: 1px solid var(--fx-border); }
        .fx-compare-tray__thumb img { width: 100%; height: 100%; object-fit: cover; }
        .fx-compare-tray__thumb button { position: absolute; top: 0; inset-inline-end: 0; width: 22px; height: 22px; border: 0; background: rgba(23,43,66,.8); color: #fff; font-size: 12px; cursor: pointer; }
        .fx-compare-drawer { width: min(960px, 96vw); }
        .fx-compare__scroll { overflow: auto; max-width: 100%; border: 1px solid var(--fx-border); border-radius: 12px; }
        .fx-compare__table { border-collapse: collapse; width: 100%; min-width: 560px; }
        .fx-compare__table th, .fx-compare__table td { padding: 10px 12px; border-bottom: 1px solid var(--fx-border); text-align: start; vertical-align: top; font-size: 14px; }
        .fx-compare__stub { position: sticky; left: 0; background: var(--fx-surface); font-weight: 700; white-space: nowrap; min-width: 120px; }
        html[dir="rtl"] .fx-compare__stub { left: auto; right: 0; }
        .fx-compare__col img { border-radius: 8px; display: block; margin-bottom: 6px; }
        .fx-compare__col a { font-weight: 600; color: var(--fx-navy); text-decoration: none; }
        .fx-compare__cell.is-diff { background: rgba(243,163,41,.10); }
        .fx-compare__diffmark { font-weight: 700; color: #8a5a00; margin-inline-start: 4px; }
        /* quick preview drawer content */
        .fx-qp__media { position: relative; aspect-ratio: 16/9; background: var(--fx-canvas); border-radius: 12px; overflow: hidden; margin-bottom: 12px; }
        .fx-qp__media img { width: 100%; height: 100%; object-fit: cover; }
        .fx-qp__media .fx-type-tag { position: absolute; top: 8px; inset-inline-start: 8px; }
        .fx-qp__name { font-size: 18px; font-weight: 700; margin: 0 0 4px; }
        .fx-qp__rating { font-size: 14px; }
        .fx-qp__price { margin: 8px 0; }
        .fx-qp__facts { display: grid; gap: 6px; margin: 12px 0; padding: 0; }
        .fx-qp__fact { display: flex; gap: 8px; font-size: 14px; }
        .fx-qp__fact dt { color: var(--fx-muted); font-weight: 600; min-width: 110px; }
        .fx-qp__fact dd { margin: 0; }
        .fx-qp__actions { display: grid; gap: 8px; margin-top: 12px; }
        .fx-qp__error { text-align: center; padding: 24px 0; }
        /* skeleton grid */
        .fx-catalog__skeleton-grid { display: none; }
        .fx-compare-tray__items .fx-chip { white-space: nowrap; max-width: 200px; overflow: hidden; text-overflow: ellipsis; }
        @media (max-width: 1023px) {
            .fx-catalog__layout { grid-template-columns: 1fr; }
            .fx-catalog__grid { grid-template-columns: repeat(2, 1fr); }
            .fx-catalog__rail { position: fixed; inset: 0 auto 0 0; width: min(360px, 92vw); max-width: 92vw; height: 100dvh; background: var(--fx-surface); z-index: 100; transform: translateX(-105%); transition: transform 200ms ease; overflow: hidden; display: flex; flex-direction: column; padding: 0; border-inline-end: 1px solid var(--fx-border); }
            html[dir="rtl"] .fx-catalog__rail { inset: 0 0 0 auto; transform: translateX(105%); }
            .fx-catalog__rail.is-open { transform: translateX(0); visibility: visible; }
            .fx-catalog__rail { visibility: hidden; transition: transform 200ms ease, visibility 0s linear 200ms; }
            .fx-catalog__rail.is-open { transition: transform 200ms ease, visibility 0s; }
            .fx-filter-sheet-head { display: flex; align-items: center; justify-content: space-between; padding: 16px; border-bottom: 1px solid var(--fx-border); }
            .fx-filter-sheet-body { flex: 1; overflow: auto; padding: 16px; }
            .fx-filter-sheet-foot { padding: 12px 16px calc(12px + env(safe-area-inset-bottom)); border-top: 1px solid var(--fx-border); display: flex; gap: 8px; }
            .fx-filter-sheet-foot .fx-btn { flex: 1; }
            .fx-mobile-filterbar { display: flex; position: sticky; top: 0; z-index: 40; background: var(--fx-canvas); padding: 8px 0; gap: 8px; }
            .fx-mobile-filterbar .fx-btn { flex: 1; }
            .fx-catalog__toolbar .fx-sort-desktop { display: none; }
        }
        @media (max-width: 767px) {
            .fx-listing-card--wide { flex-direction: column; }
            .fx-listing-card--wide .fx-listing-card__media { width: 100%; aspect-ratio: 16/9; }
        }
        @media (max-width: 420px) {
            .fx-catalog__grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .fx-listing-card__title { font-size: 14px; }
            .fx-listing-card__attrs { display: none; }
            /* Two-column cards are too narrow for [primary + save + compare] in
               one row: stack the primary button full-width, icon buttons share row 2. */
            .fx-listing-card__actions { flex-wrap: wrap; }
            .fx-listing-card__actions .fx-btn:first-child { flex: 1 1 100%; padding-left: 8px; padding-right: 8px; }
            .fx-listing-card__actions .fx-save-btn,
            .fx-listing-card__actions .fx-compare-btn { flex: 1; }
        }
        @media (prefers-reduced-motion: reduce) {
            .fx-catalog__rail { transition: none; }
        }
    </style>
@endpush

@php
    $isCategoryLanding = ($data['data_from'] ?? null) === 'category' && !empty($data['category_id']);
    $isBrandLanding = ($data['data_from'] ?? null) === 'brand' && !empty($data['brand_id']);
    $landingCategory = $isCategoryLanding ? \App\Models\Category::with('childes')->find((int)$data['category_id']) : null;
    $activeType = request('product_type', 'all');
    $activeSort = request('sort_by', 'latest');
    $activeCats = (array) request('category_ids', []);
    $selectedBrandIds = (array) request('brand_ids', []);
    $typeOptions = ['all' => translate('all'), 'physical' => translate('physical_product'), 'digital' => translate('download'),
        'fixed_service' => translate('fixed_scope_service'), 'custom_service' => translate('custom_service'),
        'event' => translate('event'), 'broker' => translate('provider_directory'),
        'subscription' => translate('subscription'), 'recurring' => translate('recurring')];
    $sortOptions = ['latest' => translate('latest'), 'low-high' => translate('price_low_to_high'),
        'high-low' => translate('price_high_to_low'), 'rating-high-low' => translate('rating_high_to_low'),
        'rating-low-high' => translate('rating_low_to_high'), 'a-z' => translate('a_to_z'), 'z-a' => translate('z_to_a')];
    $hasActiveFilters = request('name') || $activeType !== 'all' || request('min_price') !== null && request('min_price') !== ''
        || request('max_price') !== null && request('max_price') !== '' || count($activeCats) || count($selectedBrandIds);
    $baseParams = array_filter(['data_from' => $data['data_from'] ?? null, 'category_id' => $data['category_id'] ?? null,
        'brand_id' => $data['brand_id'] ?? null, 'offer_type' => $data['offer_type'] ?? null]);
@endphp

@section('content')
<div class="fx fx-catalog">
    <div class="fx-container">
        <nav aria-label="{{ translate('breadcrumb') }}">
            <ol class="fx-breadcrumb">
                <li><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
                <li><a href="{{ route('products') }}">{{ translate('products') }}</a></li>
                @if($landingCategory)<li aria-current="page">{{ $landingCategory->name }}</li>@endif
            </ol>
        </nav>

        <header class="fx-catalog__head">
            <h1 class="fx-hero" style="font-size:32px">{{ $pageTitleContent ?? translate('products') }}</h1>
            <p class="fx-meta">
                {{ translate('total') }}: {{ $products->total() }}
            </p>
            @include('web-views.partials._fx-search', ['id' => 'fx-search-catalog'])
        </header>

        @if($isCategoryLanding && $landingCategory)
            <section class="fx-card" style="margin:16px 0" aria-label="{{ translate('category') }}">
                <h2 style="font-size:18px;margin:0 0 8px">{{ translate('shop_by_subcategory') }}</h2>
                @if($landingCategory->childes->count())
                    <div style="display:flex;flex-wrap:wrap;gap:8px">
                        @foreach($landingCategory->childes as $child)
                            <a class="fx-chip" href="{{ route('products', ['data_from' => 'category', 'category_id' => $child->id]) }}">{{ $child->name }}</a>
                        @endforeach
                    </div>
                @else
                    <p class="fx-meta" style="margin:0">{{ translate('no_subcategories') }}</p>
                @endif
                <p class="fx-meta" style="margin:12px 0 0">{{ translate('category_compare_guidance') }}</p>
            </section>
        @endif

        @if($hasActiveFilters)
            <div class="fx-chip-row" style="display:flex;flex-wrap:wrap;gap:8px;margin:12px 0" data-fx-chips aria-label="{{ translate('applied_filters') }}">
                <span class="fx-meta">{{ translate('applied_filters') }}:</span>
                @if(request('name'))
                    <span class="fx-chip">{{ translate('search') }}: “{{ request('name') }}”
                        <button type="button" class="fx-chip__x" data-fx-chip-remove="name" aria-label="{{ translate('remove_filter') }}">×</button></span>
                @endif
                @if($activeType !== 'all')
                    <span class="fx-chip">{{ $typeOptions[$activeType] ?? $activeType }}
                        <button type="button" class="fx-chip__x" data-fx-chip-remove="product_type" aria-label="{{ translate('remove_filter') }}">×</button></span>
                @endif
                @if((request('min_price') !== null && request('min_price') !== '') || (request('max_price') !== null && request('max_price') !== ''))
                    <span class="fx-chip">{{ request('min_price', '0') }} – {{ request('max_price', '∞') }}
                        <button type="button" class="fx-chip__x" data-fx-chip-remove="price" aria-label="{{ translate('remove_filter') }}">×</button></span>
                @endif
                @foreach($activeCats as $cid)
                    @php($catName = \App\Models\Category::find($cid)?->name ?? $cid)
                    <span class="fx-chip">{{ \Illuminate\Support\Str::limit($catName, 24) }}
                        <button type="button" class="fx-chip__x" data-fx-chip-remove="category_ids" data-fx-chip-value="{{ $cid }}" aria-label="{{ translate('remove_filter') }}">×</button></span>
                @endforeach
                @foreach($selectedBrandIds as $bid)
                    @php($brandName = \App\Models\Brand::find($bid)?->name ?? $bid)
                    <span class="fx-chip">{{ \Illuminate\Support\Str::limit($brandName, 24) }}
                        <button type="button" class="fx-chip__x" data-fx-chip-remove="brand_ids" data-fx-chip-value="{{ $bid }}" aria-label="{{ translate('remove_filter') }}">×</button></span>
                @endforeach
                <a class="fx-chip" href="{{ route('products', $baseParams) }}">{{ translate('clear_all') }}</a>
            </div>
        @endif

        <div class="fx-mobile-filterbar">
            <button type="button" class="fx-btn fx-btn-outline" data-fx-open-filters aria-haspopup="dialog">
                {{ translate('filters_and_sort') }}
                @if($hasActiveFilters)<span class="fx-badge fx-badge--neutral" aria-label="{{ translate('filters_active') }}">•</span>@endif
            </button>
        </div>

        <div class="fx-catalog__layout">
            <aside class="fx-catalog__rail" data-fx-filter-rail aria-label="{{ translate('filters') }}">
                <div class="fx-filter-sheet-head">
                    <h2 style="font-size:18px;margin:0">{{ translate('filters_and_sort') }}</h2>
                    <button type="button" class="fx-drawer__close" data-fx-close-filters aria-label="{{ translate('close') }}">×</button>
                </div>
                <div class="fx-filter-sheet-body">
                    <form method="GET" action="{{ route('products') }}" data-fx-filter-form novalidate>
                        @foreach($baseParams as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                        @if(request('name'))<input type="hidden" name="name" value="{{ request('name') }}">@endif

                        <div class="fx-accordion" style="margin-bottom:16px">
                            <details open>
                                <summary>{{ translate('sort_by') }}</summary>
                                <div class="fx-accordion__body">
                                    @foreach($sortOptions as $val => $label)
                                        <label class="fx-filter-check">
                                            <input type="radio" name="sort_by" value="{{ $val }}" {{ $activeSort === $val ? 'checked' : '' }}>
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        </div>

                        <div class="fx-accordion" style="margin-bottom:16px">
                            <details open>
                                <summary>{{ translate('transaction_type') }}</summary>
                                <div class="fx-accordion__body">
                                    @foreach($typeOptions as $val => $label)
                                        <label class="fx-filter-check">
                                            <input type="radio" name="product_type" value="{{ $val }}" {{ $activeType === $val ? 'checked' : '' }}>
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        </div>

                        <div class="fx-accordion" style="margin-bottom:16px">
                            <details open>
                                <summary>{{ translate('price_range') }}</summary>
                                <div class="fx-accordion__body">
                                    <div class="fx-form" style="display:flex;gap:8px">
                                        <div style="flex:1">
                                            <label for="fx-min-price">{{ translate('min') }}</label>
                                            <input type="number" id="fx-min-price" name="min_price" min="0" inputmode="decimal"
                                                   value="{{ request('min_price') }}" placeholder="0">
                                        </div>
                                        <div style="flex:1">
                                            <label for="fx-max-price">{{ translate('max') }}</label>
                                            <input type="number" id="fx-max-price" name="max_price" min="0" inputmode="decimal"
                                                   value="{{ request('max_price') }}" placeholder="∞">
                                        </div>
                                    </div>
                                </div>
                            </details>
                        </div>

                        @if($categories && count($categories))
                            <div class="fx-accordion" style="margin-bottom:16px">
                                <details>
                                    <summary>{{ translate('categories') }}</summary>
                                    <div class="fx-accordion__body">
                                        @foreach($categories as $category)
                                            <label class="fx-filter-check">
                                                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                                                    {{ in_array($category->id, $activeCats) ? 'checked' : '' }}>
                                                {{ $category->name }}
                                                <span class="fx-meta">({{ $category->product->count() ?? 0 }})</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </details>
                            </div>
                        @endif

                        @if($activeBrands && count($activeBrands))
                            <div class="fx-accordion" style="margin-bottom:16px">
                                <details>
                                    <summary>{{ translate('brands') }}</summary>
                                    <div class="fx-accordion__body">
                                        @foreach($activeBrands as $brand)
                                            <label class="fx-filter-check">
                                                <input type="checkbox" name="brand_ids[]" value="{{ $brand->id }}"
                                                    {{ in_array($brand->id, $selectedBrandIds) ? 'checked' : '' }}>
                                                {{ $brand->name }}
                                                <span class="fx-meta">({{ $brand->brand_products_count ?? 0 }})</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </details>
                            </div>
                        @endif

                        <div class="fx-filter-sheet-foot">
                            <button type="submit" class="fx-btn fx-btn-amber" data-fx-apply-filters>
                                {{ translate('apply') }}
                                <span data-fx-apply-count>({{ $products->total() }})</span>
                            </button>
                            <a class="fx-btn fx-btn-ghost" href="{{ route('products', $baseParams) }}">{{ translate('clear') }}</a>
                        </div>
                    </form>
                </div>
            </aside>

            <section aria-label="{{ translate('results') }}">
                <div class="fx-catalog__toolbar">
                    <p class="fx-meta" style="margin:0" role="status">
                        {{ $products->total() }} {{ translate('results_found') }}
                    </p>
                    <label class="fx-sort-desktop fx-meta">
                        {{ translate('sort_by') }}
                        <select class="fx-form" data-fx-sort-select aria-label="{{ translate('sort_by') }}"
                                style="min-height:44px;border:1px solid var(--fx-border);border-radius:12px;padding:8px 12px;font-size:16px">
                            @foreach($sortOptions as $val => $label)
                                <option value="{{ $val }}" {{ $activeSort === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                @if($products->total() > 0)
                    <div class="fx-grid fx-catalog__grid" data-fx-grid>
                        @foreach($products as $product)
                            @include('web-views.partials._fx-listing-card', ['product' => $product])
                        @endforeach
                    </div>
                    @if($products->hasPages())
                        <nav aria-label="{{ translate('pagination') }}">
                            <ul class="fx-pagination">
                                @foreach($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                                    @if($page == $products->currentPage())
                                        <li><span aria-current="page">{{ $page }}</span></li>
                                    @elseif(abs($page - $products->currentPage()) <= 2 || $page == 1 || $page == $products->lastPage())
                                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                                    @elseif(abs($page - $products->currentPage()) == 3)
                                        <li><span aria-hidden="true">…</span></li>
                                    @endif
                                @endforeach
                            </ul>
                        </nav>
                    @endif
                @else
                    <div class="fx-empty" role="status">
                        <div class="fx-empty__slot" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                        </div>
                        <p class="fx-empty__title">
                            @if(request('name'))
                                {{ translate('no_results_found_for') }} “{{ request('name') }}”
                            @else
                                {{ translate('no_products_found') }}
                            @endif
                        </p>
                        <p class="fx-empty__body">{{ translate('try_broader_search_or_adjust_filters') }}</p>
                        <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
                            <a class="fx-btn fx-btn-outline" href="{{ route('products') }}">{{ translate('reset_filters') }}</a>
                            @if($categories && count($categories))
                                <a class="fx-btn fx-btn-ghost" href="{{ route('categories') }}">{{ translate('browse_categories') }}</a>
                            @endif
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>

@include('web-views.partials._fx-drawers')
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/fx-catalog.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/fx-search.js') }}"></script>
@endpush
