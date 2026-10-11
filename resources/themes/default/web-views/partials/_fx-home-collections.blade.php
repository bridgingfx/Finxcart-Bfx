{{-- FinXCart redesign: curated collections from real inventory (latest 12, no repeats).
     Unused by the homepage; expects $collectionPool from the caller — never queries here. --}}
@php
    $collectionPool = isset($collectionPool) ? collect($collectionPool) : collect();
    $collectionDefs = [
        [
            'title' => translate('Launch your business'),
            'purpose' => translate('Get set up: software, websites and payment essentials to start trading.'),
        ],
        [
            'title' => translate('Improve operations'),
            'purpose' => translate('Run smoother: integrations, automation and reporting for daily work.'),
        ],
        [
            'title' => translate('Build your brand'),
            'purpose' => translate('Stand out: design, content and identity that customers remember.'),
        ],
    ];
    $chunks = $collectionPool->chunk(4);
    $rendered = 0;
@endphp
@if($collectionPool->isNotEmpty())
    <section class="fx-home__section" aria-labelledby="fx-home-collections">
        <div class="fx-container">
            <div class="fx-home__section-head">
                <div>
                    <h2 class="fx-home__section-title" id="fx-home-collections">{{ translate('Curated collections') }}</h2>
                    <p class="fx-home__section-sub">{{ translate('Hand-picked starting points from the current catalog.') }}</p>
                </div>
                <a class="fx-home__view-all" href="{{ route('products') }}">{{ translate('View all') }}</a>
            </div>
            @foreach($chunks as $i => $items)
                @if($items->isNotEmpty() && isset($collectionDefs[$rendered]))
                    <div class="fx-home__row-wrap">
                        <h3 class="fx-home__row-title">{{ $collectionDefs[$rendered]['title'] }}</h3>
                        <p class="fx-home__row-purpose">{{ $collectionDefs[$rendered]['purpose'] }}</p>
                        <div class="fx-home__scroller" tabindex="0" aria-label="{{ $collectionDefs[$rendered]['title'] }}">
                            @foreach($items as $product)
                                @include('web-views.partials._fx-home-product-card', ['product' => $product])
                            @endforeach
                        </div>
                        <span class="fx-home__row-fade" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6" /></svg>
                        </span>
                    </div>
                    @php $rendered++; @endphp
                @endif
            @endforeach
        </div>
    </section>
@endif
