{{-- FinXCart hero v7: Clean centered premium layout, real search, real stats. --}}
@php
    $chips = isset($heroChips) ? $heroChips : collect();
    $stats = isset($fxStats) && is_array($fxStats) ? $fxStats : null;
    $catCount = isset($populatedCategoryCount) ? (int) $populatedCategoryCount : 0;
    $tickerCats = $chips->count() ? $chips : collect();

@endphp
<section class="fx-hero7" aria-label="{{ translate('Featured') }}">
    <div class="fx-hero7__bg" aria-hidden="true">
        <span class="fx-hero7__grid"></span>
    </div>

    <div class="fx-hero7__inner">
        <h1 class="fx-hero7__title">{{ translate('The app store for') }} <span class="fx-hero7__grad">{{ translate('fintech.') }}</span></h1>
        <p class="fx-hero7__sub">{{ translate('Trading tools, broker solutions and expert services — listed by real vendors, ready to deploy.') }}</p>

        <form class="fx-hero7__search" role="search" action="{{ route('products') }}" method="GET">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            <input type="search" name="name" placeholder="{{ translate('Search trading tools, broker CRM, APIs…') }}" aria-label="{{ translate('Search products') }}" autocomplete="off">
            <button type="submit">{{ translate('Search') }}</button>
        </form>

        @if($chips->count())
            <div class="fx-hero7__chips" aria-label="{{ translate('Popular categories') }}">
                @foreach($chips as $chipCat)
                    <a class="fx-hero7__chip" href="{{ route('products', ['category_id' => $chipCat->id, 'data_from' => 'category', 'page' => 1]) }}">{{ $chipCat->name }}</a>
                @endforeach
            </div>
        @endif

        @if($stats && ($stats['products'] > 0 || $stats['vendors'] > 0))
            <dl class="fx-hero7__stats">
                <div class="fx-hero7__stat"><dd>{{ number_format((int) $stats['products']) }}</dd><dt>{{ translate('Products listed') }}</dt></div>
                <div class="fx-hero7__stat"><dd>{{ number_format((int) $stats['vendors']) }}</dd><dt>{{ translate('Vendors') }}</dt></div>
                @if($catCount > 0)
                    <div class="fx-hero7__stat"><dd>{{ number_format($catCount) }}</dd><dt>{{ translate('Categories') }}</dt></div>
                @endif
            </dl>
        @endif

        <a class="fx-hero7__vendor" href="{{ route('contacts') }}">{{ translate('Sell on FinXCart') }}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>

    @if($tickerCats->count())
        <div class="fx-hero7__ticker">
            <div class="fx-hero7__ticker-track">
                @foreach($tickerCats as $tickCat)
                    <a class="fx-hero7__tick" href="{{ route('products', ['category_id' => $tickCat->id, 'data_from' => 'category', 'page' => 1]) }}"><span>{{ $tickCat->name }}</span><i>✦</i></a>
                @endforeach
                @foreach($tickerCats as $tickCat)
                    <span class="fx-hero7__tick" aria-hidden="true"><span>{{ $tickCat->name }}</span><i>✦</i></span>
                @endforeach
            </div>
        </div>
    @endif
</section>
