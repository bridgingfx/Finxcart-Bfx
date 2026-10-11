{{-- FinXCart redesign v5: "Browse categories" bento tiles. Expects $tileCategories (collection). --}}
@php
    // Generic name-based icon matching (no fixture slugs). Falls back to 'grid'.
    $tileIconKeywords = [
        'chart' => ['trading', 'forex', 'chart', 'signal', 'indicator'],
        'code' => ['web', 'development', 'developer', 'software', 'plugin', 'api', 'integration'],
        'briefcase' => ['broker', 'crm', 'agency'],
        'palette' => ['marketing', 'brand', 'design', 'seo', 'media'],
        'card' => ['payment', 'gateway', 'finance', 'billing'],
        'users' => ['consult', 'service', 'freelance', 'expert', 'support'],
    ];
    $fxTileIcon = function ($name) use ($tileIconKeywords) {
        $n = mb_strtolower($name ?? '');
        foreach ($tileIconKeywords as $icon => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($n, $kw)) return $icon;
            }
        }
        return 'grid';
    };
    $bentoTones = ['navy', 'amber', 'indigo', 'teal'];
@endphp
@if($tileCategories->isNotEmpty())
    <section class="fx-cats fx-reveal" aria-labelledby="fx-cats-title">
        <div class="fx-container">
            <div class="fx-sec-head">
                <h2 class="fx-sec-title" id="fx-cats-title">{{ translate('Browse categories') }}</h2>
                <a class="fx-sec-link" href="{{ route('products') }}">{{ translate('View all') }}</a>
            </div>
            <div class="fx-bento">
                @foreach($tileCategories as $i => $category)
                    <a class="fx-bento__tile fx-bento__tile--{{ $bentoTones[$i % 4] }}{{ $i === 0 ? ' fx-bento__tile--wide' : '' }}" href="{{ route('products', ['category_id' => $category->id, 'data_from' => 'category', 'page' => 1]) }}">
                        <span class="fx-bento__icon">@include('web-views.partials._fx-home-tile-icon', ['icon' => $fxTileIcon($category->name)])</span>
                        <span class="fx-bento__meta">
                            <span class="fx-bento__name">{{ $category->name }}</span>
                            <span class="fx-bento__go">{{ translate('Explore') }}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
