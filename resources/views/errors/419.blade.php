@extends('errors::minimal')

@section('title', translate('Page_Expired'))

@section('message')
    <div class="fx-empty">
        <p class="fx-error-code" aria-hidden="true">419</p>
        <p class="fx-empty__title">{{ translate('Page_Expired') }}</p>
        <p class="fx-empty__body">{{ translate('your_session_has_expired_please_refresh_and_try_again') }}</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a class="fx-btn" href="javascript:location.reload()">{{ translate('refresh_page') }}</a>
            <a class="fx-btn fx-btn-outline" href="{{ route('home') }}">{{ translate('back_to_home') }}</a>
        </div>
    </div>
@endsection
