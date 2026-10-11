@extends('errors::minimal')

@section('title', translate('Too_Many_Requests'))

@section('message')
    <div class="fx-empty">
        <p class="fx-error-code" aria-hidden="true">429</p>
        <p class="fx-empty__title">{{ translate('Too_Many_Requests') }}</p>
        <p class="fx-empty__body">{{ translate('you_have_made_too_many_requests_please_wait_a_moment_and_try_again') }}</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a class="fx-btn" href="javascript:location.reload()">{{ translate('try_again') }}</a>
            <a class="fx-btn fx-btn-outline" href="{{ route('home') }}">{{ translate('back_to_home') }}</a>
        </div>
    </div>
@endsection
