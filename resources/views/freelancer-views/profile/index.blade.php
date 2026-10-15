@extends('layouts.freelancer.app')

@section('title', translate('profile'))

@push('css_or_js')
    <style>
        .fl-profile-hero {
            background: linear-gradient(120deg, #17395e 0%, #1f4a78 100%); border-radius: 12px;
            padding: 22px 26px; margin-bottom: 20px; display: flex; align-items: center; gap: 16px; color: #fff;
        }
        .fl-profile-hero img {
            width: 68px; height: 68px; border-radius: 50%; object-fit: cover;
            border: 3px solid rgba(255,255,255,.4); background: rgba(255,255,255,.15);
        }
        .fl-profile-hero h2 { font-weight: 800; font-size: 19px; margin: 0; color: #fff; }
        .fl-profile-hero p { margin: 2px 0 0; font-size: 13px; color: rgba(255,255,255,.75); }
        .fl-profile-card { border-radius: 12px; }
        .fl-profile-card .card-header { background: #fff; border-bottom: 1px solid #f1f3f6; }

        @media (max-width: 479.98px) {
            .fl-profile-hero { flex-direction: column; text-align: center; padding: 20px; }
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="fl-profile-hero">
            <img alt="" src="{{ getStorageImages(path: $vendor->image_full_url, type: 'backend-profile') }}">
            <div>
                <h2>{{ trim($vendor->f_name . ' ' . $vendor->l_name) }}</h2>
                <p>{{ $vendor->email }}</p>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card fl-profile-card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ translate('basic_information') }}</h5>
                    </div>
                    <div class="card-body">
                        <form id="freelancer-profile-form" action="{{ route('freelancer.profile.update', [$vendor->id]) }}" method="post" enctype="multipart/form-data" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">{{ translate('image') }}</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ translate('first_name') }} *</label>
                                <input type="text" class="form-control" name="f_name" value="{{ $vendor->f_name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ translate('last_name') }}</label>
                                <input type="text" class="form-control" name="l_name" value="{{ $vendor->l_name }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ translate('phone') }} *</label>
                                <input type="text" class="form-control" name="phone" value="{{ $vendor->phone }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ translate('email') }}</label>
                                <input type="email" class="form-control" value="{{ $vendor->email }}" disabled>
                            </div>
                            <button type="submit" class="btn btn--primary">{{ translate('save') }}</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card fl-profile-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">{{ translate('change_password') }}</h5>
                    </div>
                    <div class="card-body">
                        <form id="freelancer-password-form" action="{{ route('freelancer.profile.update-password', [$vendor->id]) }}" method="post" novalidate>
                            @csrf
                            @method('patch')
                            <div class="mb-3">
                                <label class="form-label">{{ translate('new_password') }} *</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ translate('confirm_password') }} *</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn--primary">{{ translate('update_password') }}</button>
                        </form>
                    </div>
                </div>

                <div class="card fl-profile-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ translate('bank_Information') }}</h5>
                        <a href="{{ route('freelancer.profile.update-bank-info', [$vendor->id]) }}" class="btn btn-sm btn-outline--primary">
                            {{ translate('manage') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-0">
                            {{ $vendor->holder_name
                                ? translate('bank_account_on_file') . ': ' . $vendor->bank_name . ' (' . $vendor->account_no . ')'
                                : translate('no_bank_information_added_yet') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            function submitAjax(form, successMessage) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    if (typeof FormValidators !== 'undefined' && !FormValidators.autoValidateForm('#' + form.id)) {
                        return;
                    }

                    fetch(form.getAttribute('action'), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: new FormData(form),
                    })
                        .then(res => res.json().then(data => ({ ok: res.ok, data })))
                        .then(({ ok, data }) => {
                            if (ok) {
                                if (typeof toastMagic !== 'undefined') toastMagic.success(data.message || successMessage);
                                if (form.id === 'freelancer-password-form') form.reset();
                            } else {
                                const message = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Something went wrong.');
                                if (typeof toastMagic !== 'undefined') toastMagic.error(message);
                            }
                        })
                        .catch(() => {
                            if (typeof toastMagic !== 'undefined') toastMagic.error('Something went wrong.');
                        });
                });
            }

            submitAjax(document.getElementById('freelancer-profile-form'), @json(translate('profile_updated_successfully')));
            submitAjax(document.getElementById('freelancer-password-form'), @json(translate('password_updated_successfully')));
        })();
    </script>
@endpush
