{{--
    _fx-search.blade.php — fx catalog search with grouped autocomplete.
    Include anywhere (header, catalog page): @include('web-views.partials._fx-search', ['id' => 'fx-search-main'])
    Requires fx-search.js (pushed by the including view).
--}}
@php($fxSearchId = $id ?? 'fx-search-' . uniqid())
<div class="fx-search-wrap" data-fx-search-wrap>
    <form class="fx-search" role="search" action="{{ route('products') }}" method="GET"
          data-fx-search-form data-suggest-url="{{ route('fx.search.suggest') }}" aria-label="{{ translate('search') }}">
        <label class="sr-only" for="{{ $fxSearchId }}">{{ translate('search_for_products') }}</label>
        <input class="fx-search__input" type="search" id="{{ $fxSearchId }}" name="name"
               value="{{ request('name') }}" autocomplete="off"
               placeholder="{{ translate('search_for_products') }}"
               aria-expanded="false" aria-controls="{{ $fxSearchId }}-listbox" role="combobox"
               aria-autocomplete="list" data-fx-search-input>
        <button class="fx-search__btn" type="submit" data-fx-search-submit>
            {{ translate('search') }}
        </button>
        <div class="fx-search__suggest" id="{{ $fxSearchId }}-listbox" role="listbox"
             aria-label="{{ translate('suggestions') }}" hidden data-fx-suggest></div>
    </form>
</div>
