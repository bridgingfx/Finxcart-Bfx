@extends('layouts.front-end.app')

@section('title', translate('hire_freelancer'))

@push('css_or_js')
<style>
    /* Freelancer discovery — fx light-first skin. JS hooks preserved:
       [data-category-tabs-shell], [data-category-tabs], [data-category-scroll],
       .hire-category-tab.active, [data-filter-toggle], .hire-filter-panel.is-open */
    .fx-hire-tabs-shell { display: flex; align-items: center; gap: var(--fx-s2); margin-bottom: var(--fx-s4); }
    .fx-hire-tabs-wrapper { flex: 1; overflow: hidden; }
    .fx-hire-tabs { display: flex; gap: var(--fx-s2); overflow-x: auto; padding: var(--fx-s1) 0; scrollbar-width: none; }
    .fx-hire-tabs::-webkit-scrollbar { display: none; }
    .fx-hire-tabs .fx-chip { white-space: nowrap; text-decoration: none; }
    .fx-hire-tabs .fx-chip i { font-style: normal; }
    .fx-hire-tab-scroll { flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%; border: 1px solid var(--fx-border); background: var(--fx-surface); color: var(--fx-navy); cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .fx-hire-tab-scroll.is-disabled { opacity: .35; cursor: default; }
    .fx-hire-tab-scroll:focus-visible { outline: 2px solid var(--fx-navy); outline-offset: 2px; }
    .fx-hire-tabs-shell:not(.is-scrollable) .fx-hire-tab-scroll { display: none; }

    .fx-hire-subcats { display: flex; gap: var(--fx-s2); overflow-x: auto; padding: var(--fx-s1) 0 var(--fx-s2); scrollbar-width: none; }
    .fx-hire-subcats::-webkit-scrollbar { display: none; }

    .fx-hire-filterbar { display: flex; flex-wrap: wrap; gap: var(--fx-s2); align-items: center; margin-bottom: var(--fx-s4); }
    .fx-hire-filterwrap { position: relative; }
    .hire-filter-panel {
        display: none; position: absolute; top: calc(100% + 8px); inset-inline-start: 0; z-index: 40;
        background: var(--fx-surface); border: 1px solid var(--fx-border); border-radius: var(--fx-radius-md);
        box-shadow: 0 12px 32px rgba(23, 43, 66, .12); padding: var(--fx-s4); min-width: 260px;
    }
    .hire-filter-panel.is-open { display: block; }
    .hire-filter-panel .fx-form input { min-height: 40px; font-size: 15px; }
    .hire-delivery-option {
        display: block; padding: var(--fx-s2) var(--fx-s3); border-radius: var(--fx-radius-sm);
        color: var(--fx-text); text-decoration: none; font-size: 14px; font-weight: 600;
    }
    .hire-delivery-option:hover { background: var(--fx-canvas); }
    .hire-delivery-option.active { background: rgba(32, 63, 96, .08); color: var(--fx-navy); }

    .fx-hire-toolbar { display: flex; justify-content: space-between; align-items: center; gap: var(--fx-s3); flex-wrap: wrap; margin-bottom: var(--fx-s4); }
    .fx-hire-sort { display: flex; align-items: center; gap: var(--fx-s2); font-size: 14px; color: var(--fx-muted); }
    .fx-hire-sort select { min-height: 40px; border: 1px solid var(--fx-border); border-radius: var(--fx-radius-md); background: var(--fx-surface); color: var(--fx-text); font-size: 14px; padding: 0 var(--fx-s2); }

    .fx-gig-card { padding: 0; overflow: hidden; display: flex; flex-direction: column; color: inherit; text-decoration: none; }
    .fx-gig-card__cover { position: relative; aspect-ratio: 16 / 9; background: var(--fx-canvas); }
    .fx-gig-card__cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .fx-gig-card__cover .fx-gig-card__noimg { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: var(--fx-muted); }
    .fx-gig-card__badge { position: absolute; top: var(--fx-s3); inset-inline-start: var(--fx-s3); }
    .fx-gig-card__body { padding: var(--fx-s4); display: flex; flex-direction: column; gap: var(--fx-s2); flex: 1; }
    .fx-gig-card__seller { display: flex; align-items: center; gap: var(--fx-s2); }
    .fx-gig-card__avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; background: var(--fx-canvas); border: 1px solid var(--fx-border); flex-shrink: 0; }
    .fx-gig-card__seller-name { font-size: 14px; font-weight: 600; display: block; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .fx-gig-card__level { margin-inline-start: auto; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--fx-navy); background: rgba(32, 63, 96, .08); border-radius: var(--fx-radius-pill); padding: var(--fx-s1) var(--fx-s2); white-space: nowrap; }
    .fx-gig-card__tags { display: flex; flex-wrap: wrap; gap: var(--fx-s1); }
    .fx-gig-card__tags .fx-type-tag { text-transform: none; letter-spacing: 0; }
    .fx-gig-card__title { font-size: 16px; font-weight: 700; margin: 0; letter-spacing: -0.01em; line-height: 1.35; }
    .fx-gig-card__rating { font-size: 14px; margin: 0; }
    .fx-gig-card__footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--fx-border); padding-top: var(--fx-s3); margin-top: auto; }
    .fx-gig-card__price-label { font-size: 12px; color: var(--fx-muted); text-transform: uppercase; letter-spacing: .04em; font-weight: 600; }
    @media (max-width: 767px) {
        .fx-hire-toolbar { align-items: stretch; flex-direction: column; }
    }
</style>
@endpush

@section('content')
<div class="fx-container" style="padding-top: var(--fx-s6); padding-bottom: var(--fx-s12);">
    <nav aria-label="{{ translate('breadcrumb') }}">
        <ol class="fx-breadcrumb">
            <li><a href="{{ route('home') }}">{{ translate('home') }}</a></li>
            <li aria-current="page">{{ translate('hire_freelancer') }}</li>
        </ol>
    </nav>

    <h1 class="fx-hero" style="margin:0 0 var(--fx-s2);">{{ translate('hire_freelancer') }}</h1>
    <p class="fx-meta" style="margin:0 0 var(--fx-s6);">{{ translate('browse_freelancer_services_intro') }}</p>

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
        <div class="fx-hire-tabs-shell" data-category-tabs-shell>
            <button type="button" class="fx-hire-tab-scroll" data-category-scroll="prev" aria-label="{{ translate('previous_categories') }}">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            <div class="fx-hire-tabs-wrapper">
                <nav class="fx-hire-tabs" aria-label="{{ translate('freelancer_categories') }}" data-category-tabs>
                    <a class="fx-chip hire-category-tab {{ !$selectedCategory ? 'fx-chip--active' : '' }}" href="{{ route('hire-freelancer') }}">
                        <i class="fas fa-th-large" aria-hidden="true"></i>{{ translate('all_freelancer_services') }}
                    </a>
                    @foreach($categories as $category)
                        <a class="fx-chip hire-category-tab {{ $selectedCategory?->id === $category->id ? 'fx-chip--active' : '' }}"
                           href="{{ route('hire-freelancer', ['category' => $category->slug]) }}">
                            <i class="{{ $resolveTabIcon($category->name) }}" aria-hidden="true"></i>{{ $category->name }}
                        </a>
                    @endforeach
                </nav>
            </div>
            <button type="button" class="fx-hire-tab-scroll" data-category-scroll="next" aria-label="{{ translate('next_categories') }}">
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
        </div>

        @if($selectedCategory)
            <nav class="fx-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('hire-freelancer') }}">{{ translate('home') }}</a>
                @if($selectedSpecialization)
                    <a href="{{ route('hire-freelancer', ['category' => $selectedCategory->slug]) }}">{{ $selectedCategory->name }}</a>
                    <span aria-current="page">{{ $selectedSpecialization->name }}</span>
                @else
                    <span aria-current="page">{{ $selectedCategory->name }}</span>
                @endif
            </nav>
        @endif

        @if(!$selectedCategory && !$selectedSpecialization)
            <div class="fx-grid fx-grid-3" style="margin-top: var(--fx-s4);">
                @foreach($displayCategories as $category)
                    <a class="fx-card" href="{{ route('hire-freelancer', ['category' => $category->slug]) }}" id="freelancer-category-{{ $category->id }}"
                       style="display:flex; gap: var(--fx-s4); align-items:flex-start; text-decoration:none; color:inherit;">
                        <span style="width:52px; height:52px; border-radius: var(--fx-radius-md); background: var(--fx-canvas); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i class="{{ $resolveTabIcon($category->name) }}" style="font-size:22px; color: var(--fx-navy);" aria-hidden="true"></i>
                        </span>
                        <span style="min-width:0;">
                            <span style="display:block; font-size:17px; font-weight:700; letter-spacing:-0.01em;">{{ $category->name }}</span>
                            <span class="fx-meta" style="display:block; margin: var(--fx-s1) 0 var(--fx-s2);">{{ $category->activeSpecializations->count() }} {{ translate('specializations') }}</span>
                            @if($category->activeSpecializations->count())
                                <span style="display:flex; flex-wrap:wrap; gap: var(--fx-s1);">
                                    @foreach($category->activeSpecializations->take(6) as $specialization)
                                        <span class="fx-type-tag" style="text-transform:none; letter-spacing:0;">{{ $specialization->name }}</span>
                                    @endforeach
                                </span>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            @foreach($displayCategories as $category)
                <section id="freelancer-category-{{ $category->id }}" style="margin-top: var(--fx-s4);">
                    <h2 style="font-size:20px; font-weight:700; margin:0 0 var(--fx-s3); letter-spacing:-0.01em;">{{ $category->name }}</h2>
                    @if($category->activeSpecializations->count())
                        <div class="fx-hire-subcats">
                            @foreach($category->activeSpecializations as $specialization)
                                <a class="fx-chip {{ $selectedSpecialization?->id === $specialization->id ? 'fx-chip--active' : '' }}"
                                   href="{{ route('hire-freelancer', ['specialization' => $specialization->slug]) }}"
                                   id="freelancer-specialization-{{ $specialization->id }}">
                                    {{ $specialization->name }}
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="fx-meta">{{ translate('no_freelancer_specialization_found') }}</p>
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
            <section style="margin-top: var(--fx-s6);">
                <div class="fx-hire-filterbar">
                    <div class="fx-hire-filterwrap">
                        <button type="button" class="fx-chip {{ ($minPrice !== null || $maxPrice !== null) ? 'fx-chip--active' : '' }}" data-filter-toggle="budget-filter" aria-expanded="false">
                            {{ translate('budget') }}{{ ($minPrice !== null || $maxPrice !== null) ? ' •' : '' }}
                        </button>
                        <div class="hire-filter-panel" id="budget-filter" role="dialog" aria-label="{{ translate('budget') }}">
                            <form method="get" class="fx-form" novalidate>
                                {!! $renderHiddenFilterInputs(['min_price', 'max_price']) !!}
                                <div style="display:flex; gap: var(--fx-s2); margin-bottom: var(--fx-s3);">
                                    <div class="fx-field" style="margin:0; flex:1;">
                                        <label for="fx-min-price">{{ translate('min') }}</label>
                                        <input type="number" min="0" step="1" id="fx-min-price" name="min_price" value="{{ $minPrice }}">
                                    </div>
                                    <div class="fx-field" style="margin:0; flex:1;">
                                        <label for="fx-max-price">{{ translate('max') }}</label>
                                        <input type="number" min="0" step="1" id="fx-max-price" name="max_price" value="{{ $maxPrice }}">
                                    </div>
                                </div>
                                <div style="display:flex; gap: var(--fx-s2);">
                                    <a href="{{ route('hire-freelancer', collect($filterBaseParams)->except(['min_price', 'max_price'])->all()) }}" class="fx-btn fx-btn-outline fx-btn-sm" style="flex:1;">{{ translate('clear_all') }}</a>
                                    <button type="submit" class="fx-btn fx-btn-sm" style="flex:1;">{{ translate('apply') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="fx-hire-filterwrap">
                        <button type="button" class="fx-chip {{ $maxDeliveryDays ? 'fx-chip--active' : '' }}" data-filter-toggle="delivery-filter" aria-expanded="false">
                            {{ translate('delivery_time') }}{{ $maxDeliveryDays ? ' •' : '' }}
                        </button>
                        <div class="hire-filter-panel" id="delivery-filter" role="dialog" aria-label="{{ translate('delivery_time') }}">
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

                <div class="fx-hire-toolbar">
                    <p class="fx-meta" style="margin:0;"><strong style="color:var(--fx-text);">{{ $services->total() }}</strong> {{ translate('results') }}</p>
                    <form method="get" class="fx-hire-sort" novalidate>
                        {!! $renderHiddenFilterInputs(['sort']) !!}
                        <label for="fx-hire-sort">{{ translate('sort_by') }}:</label>
                        <select name="sort" id="fx-hire-sort" onchange="this.form.submit()">
                            <option value="recent" {{ $sort === 'recent' ? 'selected' : '' }}>{{ translate('most_recent') }}</option>
                            <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>{{ translate('highest_rated') }}</option>
                            <option value="price_low" {{ $sort === 'price_low' ? 'selected' : '' }}>{{ translate('price_low_to_high') }}</option>
                            <option value="price_high" {{ $sort === 'price_high' ? 'selected' : '' }}>{{ translate('price_high_to_low') }}</option>
                        </select>
                    </form>
                </div>

                @if($services->count())
                    <div class="fx-grid fx-grid-3">
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
                            @endphp
                            <a class="fx-card fx-gig-card" href="{{ route('hire-freelancer.service-show', $service->id) }}"
                               aria-label="{{ $service->title }} — {{ $sellerName }}">
                                <span class="fx-gig-card__cover">
                                    @if($cover)
                                        <img alt="{{ $service->title }}" loading="lazy" src="{{ getStorageImages(path: $cover->image_full_url, type: 'backend-profile') }}">
                                    @else
                                        <span class="fx-gig-card__noimg" aria-hidden="true">
                                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                        </span>
                                    @endif
                                    @if($isPopular)
                                        <span class="fx-badge fx-badge--neutral fx-gig-card__badge">{{ translate('popular') }}</span>
                                    @elseif($isNew)
                                        <span class="fx-badge fx-badge--neutral fx-gig-card__badge">{{ translate('new') }}</span>
                                    @endif
                                </span>
                                <span class="fx-gig-card__body">
                                    <span class="fx-gig-card__seller">
                                        <img class="fx-gig-card__avatar" alt="" loading="lazy"
                                             src="{{ getStorageImages(path: $seller?->image_full_url, type: 'backend-profile') }}">
                                        <span class="fx-gig-card__seller-name">{{ $sellerName }}</span>
                                        @if($seller)
                                            <span class="fx-gig-card__level">{{ $seller->freelancerLevelLabel() }}</span>
                                        @endif
                                    </span>
                                    <span class="fx-gig-card__tags">
                                        @if($service->category?->name)
                                            <span class="fx-type-tag">{{ $service->category->name }}</span>
                                        @endif
                                        @if($service->specialization?->name)
                                            <span class="fx-type-tag">{{ $service->specialization->name }}</span>
                                        @endif
                                        @if($service->delivery_time_days)
                                            <span class="fx-type-tag">{{ $service->delivery_time_days }} {{ translate('days') }}</span>
                                        @endif
                                    </span>
                                    <span class="fx-gig-card__title">{{ $service->title }}</span>
                                    <span class="fx-gig-card__rating">
                                        @if($ratingCount > 0)
                                            <span aria-hidden="true" style="color:var(--fx-amber);">★</span>
                                            <strong>{{ $ratingAvg }}</strong>
                                            <span class="fx-meta">({{ $ratingCount }})</span>
                                        @else
                                            <span class="fx-meta">{{ translate('no_reviews_yet') }}</span>
                                        @endif
                                    </span>
                                    <span class="fx-gig-card__footer">
                                        <span>
                                            <span class="fx-gig-card__price-label">{{ translate('from') }}</span><br>
                                            <span class="fx-price">{{ $startingPrice !== null ? webCurrencyConverter($startingPrice) : translate('not_available') }}</span>
                                        </span>
                                        <span class="fx-btn fx-btn-sm fx-btn-outline" aria-hidden="true">{{ translate('view_service') }} →</span>
                                    </span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                    <div style="display:flex; justify-content:center;">
                        @include('web-views.partials._fx-pagination', ['paginator' => $services])
                    </div>
                @else
                    <div class="fx-empty">
                        <h2 class="fx-empty__title">{{ translate('no_services_found') }}</h2>
                        <p class="fx-empty__body">{{ translate('no_services_found_for_this_selection') }}</p>
                    </div>
                @endif
            </section>
        @endif
    @else
        <div class="fx-empty">
            <h2 class="fx-empty__title">{{ translate('no_freelancer_category_found') }}</h2>
            <p class="fx-empty__body">{{ translate('freelancer_categories_coming_soon') }}</p>
        </div>
    @endif
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
                document.querySelectorAll('[data-filter-toggle]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
                if (!wasOpen) {
                    panel.classList.add('is-open');
                    button.setAttribute('aria-expanded', 'true');
                }
            });
            panel.addEventListener('click', function (event) { event.stopPropagation(); });
        });
        document.addEventListener('click', function () {
            document.querySelectorAll('.hire-filter-panel.is-open').forEach(function (open) {
                open.classList.remove('is-open');
            });
            document.querySelectorAll('[data-filter-toggle]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
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

            var active = tabs.querySelector('.hire-category-tab.fx-chip--active');
            if (active) {
                active.scrollIntoView({ inline: 'center', block: 'nearest' });
                setTimeout(updateButtons, 250);
            }
        });
    })();
</script>
@endpush
@endsection
