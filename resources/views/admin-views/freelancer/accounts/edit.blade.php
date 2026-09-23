@extends('layouts.admin.app')

@section('title', translate('edit_freelancer'))

@push('css_or_js')
    <style>
        .freelancer-edit-image-drop {
            border: 2px dashed #d7dde5; border-radius: 12px; padding: 24px; text-align: center;
            cursor: pointer; transition: border-color .15s ease; background: #fafbfc;
        }
        .freelancer-edit-image-drop:hover { border-color: #1684cf; }
        .freelancer-edit-image-drop img { max-width: 100%; max-height: 160px; border-radius: 50%; object-fit: cover; }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0">{{ translate('edit_freelancer') }}</h2>
        </div>

        <form action="{{ route('admin.freelancer.accounts.update', $freelancer->id) }}" method="post" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{ translate('image') }}</label>
                            <label class="freelancer-edit-image-drop d-block" id="freelancerImageDrop">
                                <img id="freelancerImagePreview" src="{{ getStorageImages(path: $freelancer->image_full_url, type: 'backend-profile') }}" alt="">
                                <input type="file" name="image" id="freelancerImageInput" class="d-none" accept=".webp,.jpg,.jpeg,.png">
                            </label>
                        </div>
                        <div class="col-md-9">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ translate('first_name') }} *</label>
                                    <input class="form-control" name="f_name" maxlength="255" value="{{ old('f_name', $freelancer->f_name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ translate('last_name') }}</label>
                                    <input class="form-control" name="l_name" maxlength="255" value="{{ old('l_name', $freelancer->l_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ translate('email') }} *</label>
                                    <input class="form-control" type="email" name="email" maxlength="80" value="{{ old('email', $freelancer->email) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ translate('phone') }} *</label>
                                    <input class="form-control" name="phone" maxlength="20" value="{{ old('phone', $freelancer->phone) }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">{{ translate('shop_display_name') }}</label>
                                    <input class="form-control" name="shop_name" maxlength="100" value="{{ old('shop_name', $freelancer->shop?->name) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ translate('reset_password') }}</h5>
                    <p class="text-muted small mb-0">{{ translate('leave_blank_to_keep_the_current_password') }}</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('password') }}</label>
                            <input class="form-control" type="password" name="password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('confirm_password') }}</label>
                            <input class="form-control" type="password" name="password_confirmation">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('admin.freelancer.accounts.view', $freelancer->id) }}" class="btn btn-secondary">{{ translate('cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ translate('save') }}</button>
            </div>
        </form>
    </div>
@endsection

@push('script')
    <script>
        (function () {
            const input = document.getElementById('freelancerImageInput');
            const preview = document.getElementById('freelancerImagePreview');
            input.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function (event) { preview.src = event.target.result; };
                reader.readAsDataURL(file);
            });
        })();
    </script>
@endpush
