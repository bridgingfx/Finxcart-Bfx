@extends('errors::minimal')

@section('title', translate('Forbidden'))

@section('message')
    <div class="fx-empty">
        <p class="fx-error-code" aria-hidden="true">403</p>
        <p class="fx-empty__title">{{ translate('Forbidden') }}</p>
        <p class="fx-empty__body">{{ translate('you_do_not_have_permission_to_view_this_page') }}</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a class="fx-btn" href="{{ route('home') }}">{{ translate('back_to_home') }}</a>
            <a class="fx-btn fx-btn-outline" href="javascript:history.back()">{{ translate('go_back') }}</a>
        </div>
    </div>
@endsection
