@extends('errors::minimal')

@section('title', translate('page_Not_found'))

@section('message')
    <div class="fx-empty">
        <p class="fx-error-code" aria-hidden="true">404</p>
        <p class="fx-empty__title">{{ translate('page_Not_found') }}</p>
        <p class="fx-empty__body">
            {{ translate('we_are_sorry') }}, {{ translate('the_page_you_are_looking_for_cannot_be_found') }}.
            {{ translate('it_may_have_been_moved_or_deleted') }}
        </p>
        <form class="fx-search" role="search" action="{{ route('products') }}" method="GET" style="max-width:420px;margin:0 auto 20px;">
            <input class="fx-search__input" type="search" name="name" placeholder="{{ translate('search_for_products') }}" aria-label="{{ translate('search_for_products') }}">
            <input type="hidden" name="global_search_input" value="1">
            <button class="fx-search__btn" type="submit" aria-label="{{ translate('search') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
        </form>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a class="fx-btn" href="{{ route('home') }}">{{ translate('back_to_home') }}</a>
            <a class="fx-btn fx-btn-outline" href="javascript:history.back()">{{ translate('go_back') }}</a>
        </div>
    </div>
@endsection
