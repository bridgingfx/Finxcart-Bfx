@extends('errors::minimal')

@section('title', translate('page_Not_found'))

@section('message')
    <div class="fx-empty">
        <p class="fx-error-code" aria-hidden="true">405</p>
        <p class="fx-empty__title">{{ translate('this_action_is_not_available_here') }}</p>
        <p class="fx-empty__body">
            {{ translate('we_are_sorry') }}, {{ translate('that_link_cannot_be_opened_directly') }}.
            {{ translate('please_go_back_to_the_homepage') }}
        </p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a class="fx-btn" href="{{ route('home') }}">{{ translate('back_to_home') }}</a>
            <a class="fx-btn fx-btn-outline" href="javascript:history.back()">{{ translate('go_back') }}</a>
        </div>
    </div>
@endsection
