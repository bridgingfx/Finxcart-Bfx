@extends('layouts.admin.app')

@section('title', translate('add_freelancer'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0">{{ translate('add_freelancer') }}</h2>
            <p class="text-muted mb-0">{{ translate('accounts_created_here_are_approved_and_active_immediately_no_document_verification_is_required') }}</p>
        </div>

        <form action="{{ route('admin.freelancer.accounts.store') }}" method="post" novalidate>
            @csrf
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('first_name') }} *</label>
                            <input class="form-control" name="f_name" maxlength="255" value="{{ old('f_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('last_name') }}</label>
                            <input class="form-control" name="l_name" maxlength="255" value="{{ old('l_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('email') }} *</label>
                            <input class="form-control" type="email" name="email" maxlength="80" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('phone') }} *</label>
                            <input class="form-control" name="phone" maxlength="20" value="{{ old('phone') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('shop_display_name') }}</label>
                            <input class="form-control" name="shop_name" maxlength="100" value="{{ old('shop_name') }}" placeholder="{{ translate('defaults_to_the_email_prefix_if_left_blank') }}">
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('password') }} *</label>
                            <input class="form-control" type="password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('confirm_password') }} *</label>
                            <input class="form-control" type="password" name="password_confirmation" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('admin.freelancer.accounts.index') }}" class="btn btn-secondary">{{ translate('cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ translate('save') }}</button>
            </div>
        </form>
    </div>
@endsection
