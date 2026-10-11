@extends('errors::minimal')

@section('title', translate('Unauthorized'))

@section('message')
    <div class="fx-empty">
        <p class="fx-error-code" aria-hidden="true">401</p>
        <p class="fx-empty__title">{{ translate('Unauthorized') }}</p>
        <p class="fx-empty__body">{{ translate('please_sign_in_to_continue') }}</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a class="fx-btn" href="{{ route('customer.auth.login') }}">{{ translate('sign_in') }}</a>
            <a class="fx-btn fx-btn-outline" href="{{ route('home') }}">{{ translate('back_to_home') }}</a>
        </div>
    </div>
@endsection
