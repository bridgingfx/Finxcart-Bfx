@extends('errors::minimal')

@section('title', translate('something_went_wrong'))

@section('message')
    <div class="fx-empty">
        <p class="fx-error-code" aria-hidden="true">500</p>
        <p class="fx-empty__title">{{ translate('something_went_wrong') }}</p>
        <p class="fx-empty__body">
            {{ translate('We_are_sorry_server_is_not_responding') }}.
            {{ translate('Try_after_sometime') }}
        </p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a class="fx-btn" href="javascript:location.reload()">{{ translate('try_again') }}</a>
            <a class="fx-btn fx-btn-outline" href="{{ route('home') }}">{{ translate('back_to_home') }}</a>
        </div>
    </div>
@endsection
