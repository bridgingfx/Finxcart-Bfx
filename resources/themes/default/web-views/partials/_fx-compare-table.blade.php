{{--
    _fx-compare-table.blade.php — rendered by WebController::fxCompareTable.
    Expects: $products (Collection of active products, same fx type enforced client-side)
    Missing facts render as "Not provided". Differing values get .is-diff + a
    "(differs)" marker (never color alone).
--}}
@php
    $notProvided = translate('not_provided');
    $rows = [];
    foreach ($products as $p) {
        $fx = \App\Utils\FxListing::type($p);
        [$price, $old] = \App\Utils\FxListing::priceStrings($p);
        $rating = getOverallRating($p->reviews ?? []);
        $rows[] = [
            'product' => $p,
            'fx' => $fx,
            'image' => getStorageImages(path: $p->thumbnail_full_url, type: 'product'),
            'provider' => \App\Utils\FxListing::providerName($p),
            'price' => $price ?? ($fx['buyable'] ? translate('request_quote') : $notProvided),
            'delivery' => $fx['delivery'] ?? $notProvided,
            'attr1' => $fx['attrs'][0][1] ?? $notProvided,
            'attr2' => $fx['attrs'][1][1] ?? $notProvided,
            'reviews' => $rating[1] > 0 ? $rating[0] . ' / 5 (' . $rating[1] . ')' : translate('no_reviews_yet'),
            'stock' => $fx['key'] === 'physical' ? \App\Utils\FxListing::type($p)['attrs'][1][1] : $notProvided,
        ];
    }
    $attrRows = [
        ['label' => translate('provider'), 'key' => 'provider'],
        ['label' => translate('listing_type'), 'key' => 'tag', 'isTag' => true],
        ['label' => translate('price'), 'key' => 'price'],
        // Note: no separate delivery_model row — FxListing::type()['attrs']
        // already carries the delivery promise as attrs[0] (digital/physical)
        // or attrs[1] (subscription/recurring), so a standalone row duplicates it.
        ['label' => $rows[0]['fx']['attrs'][0][0] ?? translate('details'), 'key' => 'attr1'],
        ['label' => $rows[0]['fx']['attrs'][1][0] ?? translate('details'), 'key' => 'attr2'],
        ['label' => translate('reviews'), 'key' => 'reviews'],
        ['label' => translate('availability'), 'key' => 'stock'],
    ];
    $cellValue = fn($row, $key) => $key === 'tag' ? $row['fx']['tag'] : $row[$key];
    $diffKeys = [];
    foreach ($attrRows as $ar) {
        $vals = array_unique(array_map(fn($r) => (string) $cellValue($r, $ar['key']), $rows));
        if (count($vals) > 1) { $diffKeys[$ar['key']] = true; }
    }
@endphp
@if(count($rows) === 0)
    <div class="fx-empty">
        <p class="fx-empty__title">{{ translate('nothing_to_compare') }}</p>
        <p class="fx-empty__body">{{ translate('add_items_to_compare_them_side_by_side') }}</p>
    </div>
@else
    <div class="fx-compare__scroll" tabindex="0" aria-label="{{ translate('comparison_table') }}">
        <table class="fx-compare__table">
            <thead>
            <tr>
                <th scope="col" class="fx-compare__stub"><span class="sr-only">{{ translate('attribute') }}</span></th>
                @foreach($rows as $row)
                    <th scope="col" class="fx-compare__col">
                        <img src="{{ $row['image'] }}" alt="" width="96" height="96" loading="lazy">
                        <a href="{{ route('product', $row['product']->slug) }}">{{ $row['product']->name }}</a>
                    </th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($attrRows as $ar)
                <tr class="{{ isset($diffKeys[$ar['key']]) ? 'is-diff-row' : '' }}">
                    <th scope="row" class="fx-compare__stub">{{ $ar['label'] }}</th>
                    @foreach($rows as $row)
                        <td class="fx-compare__cell{{ isset($diffKeys[$ar['key']]) ? ' is-diff' : '' }}">
                            @if(($ar['isTag'] ?? false))
                                <span class="fx-type-tag">{{ $cellValue($row, $ar['key']) }}</span>
                            @else
                                {{ $cellValue($row, $ar['key']) }}
                            @endif
                            @if(isset($diffKeys[$ar['key']]))
                                <span class="fx-compare__diffmark" title="{{ translate('differs_between_items') }}">≠<span class="sr-only">({{ translate('differs') }})</span></span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            <tr>
                <th scope="row" class="fx-compare__stub">{{ translate('actions') }}</th>
                @foreach($rows as $row)
                    <td class="fx-compare__cell">
                        <a class="fx-btn fx-btn-sm fx-btn-outline" href="{{ route('product', $row['product']->slug) }}">{{ translate('view_details') }}</a>
                        <button type="button" class="fx-btn fx-btn-sm fx-btn-ghost" data-fx-cmp-remove="{{ $row['product']->id }}">
                            {{ translate('remove') }}
                        </button>
                    </td>
                @endforeach
            </tr>
            </tbody>
        </table>
    </div>
    <p class="fx-meta fx-compare__note">{{ translate('compare_same_type_only_note') }}</p>
@endif
