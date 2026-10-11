@extends('errors::minimal')

@section('title', translate('maintenance_mode'))

@section('message')
    <div class="fx-empty">
        <p class="fx-error-code" aria-hidden="true">503</p>
        <p class="fx-empty__title">{{ translate('maintenance_mode') }}</p>
        <p class="fx-empty__body">
            {{ 'System database is being refreshed, please keep patience. System will be up in 2 minutes....' }}
        </p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a class="fx-btn" href="javascript:location.reload()">{{ translate('try_again') }}</a>
        </div>
    </div>
@endsection
