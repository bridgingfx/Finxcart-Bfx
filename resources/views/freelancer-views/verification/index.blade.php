@extends('layouts.freelancer.app')

@section('title', translate('verification'))

@php
    $hasSellerImage = !empty($seller?->image) && $seller->image !== 'def.png';
    $hasShopLogo = !empty($shop?->image) && $shop->image !== 'def.png';
    $hasShopBanner = !empty($shop?->banner) && $shop->banner !== 'def.png';
    $sellerImageUrl = $hasSellerImage ? getStorageImages(path: $seller?->image_full_url, type: 'backend-basic') : null;
    $shopLogoUrl = $hasShopLogo ? getStorageImages(path: $shop?->image_full_url, type: 'backend-basic') : null;
    $shopBannerUrl = $hasShopBanner ? getStorageImages(path: $shop?->banner_full_url, type: 'backend-basic') : null;
    $personalIdUrl = !empty($verification?->personal_id_document) ? dynamicStorage(path: 'storage/app/public/vendor-verifications/' . $verification->personal_id_document) : null;
@endphp

@push('css_or_js')
    <style>
        .fv-upload-box {
            border: 1px dashed #cbd5e1;
            border-radius: 20px;
            background: #fff;
            padding: 18px;
            height: 100%;
            transition: all .2s ease;
        }

        .fv-upload-box:hover {
            transform: translateY(-2px);
            border-color: #94a3b8;
            box-shadow: 0 10px 26px rgba(15, 23, 42, .05);
        }

        .fv-upload-preview {
            width: 100%;
            min-height: 180px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #f8fafc, #eef2ff);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }

        .fv-upload-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .fv-upload-placeholder {
            color: #64748b;
            text-align: center;
            padding: 24px;
        }

        .fv-upload-placeholder .fi {
            font-size: 20px;
            display: block;
            margin-bottom: 8px;
        }

        .fv-file-chip {
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(148, 163, 184, 0.35);
            max-width: calc(100% - 28px);
            text-align: center;
            word-break: break-word;
        }

        .fv-upload-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0">{{ translate('verification') }}</h2>
            <p class="text-muted mb-0">{{ translate('complete_your_profile_to_start_offering_services') }}</p>
        </div>

        @if($verification?->status === 'approved')
            <div class="alert alert-success d-flex align-items-center gap-2">
                <i class="fi fi-rr-check-circle"></i> {{ translate('your_verification_has_been_approved') }}
            </div>
        @elseif($verification?->status === 'rejected' && $verification->rejection_reason)
            <div class="alert alert-danger">
                <strong>{{ translate('rejection_reason') }}:</strong> {{ $verification->rejection_reason }}
            </div>
        @endif

        <form action="{{ route('freelancer.verification.store') }}" method="post" enctype="multipart/form-data" id="freelancer-verification-form" novalidate>
            @csrf
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('name') }} *</label>
                            <input type="text" class="form-control" name="vendor_name"
                                   value="{{ old('vendor_name', $seller?->f_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('profile_photo') }} {{ $hasSellerImage ? '' : '*' }}</label>
                            <input type="file" class="d-none" id="vendor-image-input" name="vendor_image" accept="image/*" {{ $hasSellerImage ? '' : 'required' }}>
                            <div class="fv-upload-box">
                                <div class="fv-upload-preview" data-preview="vendor-image">
                                    @if($sellerImageUrl)
                                        <img src="{{ $sellerImageUrl }}" alt="{{ translate('profile_photo') }}">
                                    @else
                                        <div class="fv-upload-placeholder">
                                            <i class="fi fi-rr-user fs-2 d-block mb-2"></i>
                                            <div>{{ translate('no_file_selected') }}</div>
                                            <small>JPG, PNG accepted | Max 5MB</small>
                                        </div>
                                    @endif
                                </div>
                                <div class="fv-upload-meta">
                                    <div>
                                        <div class="fw-semibold">{{ translate('profile_photo') }}</div>
                                        <div class="text-muted fs-12">JPG, PNG accepted | Max 5MB</div>
                                    </div>
                                    <label class="btn btn-outline-primary mb-0" for="vendor-image-input">{{ translate('choose_file') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ translate('personal_email') }} *</label>
                            <input type="email" class="form-control" name="personal_email"
                                   value="{{ old('personal_email', $verification?->personal_email ?? $seller?->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('personal_contact') }} *</label>
                            <input type="text" class="form-control" name="personal_contact"
                                   value="{{ old('personal_contact', $verification?->personal_contact ?? $seller?->phone) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ translate('personal_id_document') }} {{ $verification?->personal_id_document ? '' : '*' }}</label>
                            <input type="file" class="d-none" id="personal-id-input" name="personal_id_document" accept=".jpg,.jpeg,.png,.pdf,.webp" {{ $verification?->personal_id_document ? '' : 'required' }}>
                            <div class="fv-upload-box">
                                <div class="fv-upload-preview" data-preview="personal-id">
                                    @if($personalIdUrl)
                                        @if(str_ends_with(strtolower($verification->personal_id_document), '.pdf'))
                                            <div class="fv-file-chip">
                                                <i class="fi fi-rr-file-pdf d-block fs-3 mb-1"></i>
                                                <div class="fw-semibold">{{ $verification->personal_id_document }}</div>
                                            </div>
                                        @else
                                            <img src="{{ $personalIdUrl }}" alt="{{ translate('personal_id_document') }}">
                                        @endif
                                    @else
                                        <div class="fv-upload-placeholder">
                                            <i class="fi fi-rr-file fs-2 d-block mb-2"></i>
                                            <div>{{ translate('no_file_selected') }}</div>
                                            <small>PDF or image files accepted | Max 5MB</small>
                                        </div>
                                    @endif
                                </div>
                                <div class="fv-upload-meta">
                                    <div>
                                        <div class="fw-semibold">{{ translate('personal_id_document') }}</div>
                                        <div class="text-muted fs-12">PDF or image files accepted | Max 5MB</div>
                                    </div>
                                    <label class="btn btn-outline-primary mb-0" for="personal-id-input">{{ translate('choose_file') }}</label>
                                </div>
                            </div>
                            @if($verification?->personal_id_document)
                                <div class="form-text">{{ translate('a_document_has_already_been_uploaded_upload_a_new_file_to_replace_it') }}</div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ translate('logo') }} {{ $hasShopLogo ? '' : '*' }}</label>
                            <input type="file" class="d-none" id="logo-input" name="logo" accept="image/*" {{ $hasShopLogo ? '' : 'required' }}>
                            <div class="fv-upload-box">
                                <div class="fv-upload-preview" data-preview="logo">
                                    @if($shopLogoUrl)
                                        <img src="{{ $shopLogoUrl }}" alt="{{ translate('logo') }}">
                                    @else
                                        <div class="fv-upload-placeholder">
                                            <i class="fi fi-rr-image fs-2 d-block mb-2"></i>
                                            <div>{{ translate('no_file_selected') }}</div>
                                            <small>JPG, PNG accepted | Max 5MB</small>
                                        </div>
                                    @endif
                                </div>
                                <div class="fv-upload-meta">
                                    <div>
                                        <div class="fw-semibold">{{ translate('logo') }}</div>
                                        <div class="text-muted fs-12">JPG, PNG accepted | Max 5MB</div>
                                    </div>
                                    <label class="btn btn-outline-primary mb-0" for="logo-input">{{ translate('choose_file') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('banner') }} {{ $hasShopBanner ? '' : '*' }}</label>
                            <input type="file" class="d-none" id="banner-input" name="banner" accept="image/*" {{ $hasShopBanner ? '' : 'required' }}>
                            <div class="fv-upload-box">
                                <div class="fv-upload-preview" data-preview="banner">
                                    @if($shopBannerUrl)
                                        <img src="{{ $shopBannerUrl }}" alt="{{ translate('banner') }}">
                                    @else
                                        <div class="fv-upload-placeholder">
                                            <i class="fi fi-rr-panorama fs-2 d-block mb-2"></i>
                                            <div>{{ translate('no_file_selected') }}</div>
                                            <small>JPG, PNG accepted | Max 5MB</small>
                                        </div>
                                    @endif
                                </div>
                                <div class="fv-upload-meta">
                                    <div>
                                        <div class="fw-semibold">{{ translate('banner') }}</div>
                                        <div class="text-muted fs-12">JPG, PNG accepted | Max 5MB</div>
                                    </div>
                                    <label class="btn btn-outline-primary mb-0" for="banner-input">{{ translate('choose_file') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
            </div>
        </form>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileInputs = [
                ['vendor-image-input', 'vendor-image'],
                ['personal-id-input', 'personal-id'],
                ['logo-input', 'logo'],
                ['banner-input', 'banner'],
            ];

            // personal-id can be a PDF and is a compliance document — never re-encode it.
            // The other 3 are routinely 1-2MB+ marketing graphics with no server-side
            // resize, so uploading them at full resolution is what makes submitting this
            // form feel like it hangs. Downscaling them in the browser before they're ever
            // uploaded is what actually shortens that wait (mirrors the vendor verification
            // page's fix for the same issue).
            const pdfCapableInputs = new Set(['personal-id-input']);
            const MAX_IMAGE_DIMENSION = 1600;

            const resizeImageFile = (file, maxDim) => new Promise((resolve) => {
                if (!file.type.startsWith('image/') || file.type === 'image/gif') {
                    resolve(file);
                    return;
                }

                const objectUrl = URL.createObjectURL(file);
                const img = new Image();
                img.onload = function () {
                    const { width, height } = img;
                    if (width <= maxDim && height <= maxDim) {
                        URL.revokeObjectURL(objectUrl);
                        resolve(file);
                        return;
                    }

                    const scale = Math.min(maxDim / width, maxDim / height);
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.round(width * scale);
                    canvas.height = Math.round(height * scale);
                    canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                    URL.revokeObjectURL(objectUrl);

                    const outputType = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
                    canvas.toBlob((blob) => {
                        if (!blob) {
                            resolve(file);
                            return;
                        }
                        resolve(new File([blob], file.name, { type: outputType, lastModified: Date.now() }));
                    }, outputType, 0.85);
                };
                img.onerror = function () {
                    URL.revokeObjectURL(objectUrl);
                    resolve(file);
                };
                img.src = objectUrl;
            });

            const renderPreview = (preview, file) => {
                const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
                if (isPdf) {
                    preview.innerHTML = '<div class="fv-file-chip"><i class="fi fi-rr-file-pdf d-block fs-3 mb-1"></i><div class="fw-semibold">' + file.name + '</div></div>';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    preview.innerHTML = '<img src="' + event.target.result + '" alt="' + file.name + '">';
                };
                reader.readAsDataURL(file);
            };

            fileInputs.forEach(([inputId, previewKey]) => {
                const input = document.getElementById(inputId);
                const preview = document.querySelector('[data-preview="' + previewKey + '"]');
                if (!input || !preview) {
                    return;
                }

                input.addEventListener('change', function () {
                    const file = this.files && this.files[0];
                    if (!file) {
                        return;
                    }

                    if (pdfCapableInputs.has(inputId)) {
                        renderPreview(preview, file);
                        return;
                    }

                    resizeImageFile(file, MAX_IMAGE_DIMENSION).then((resizedFile) => {
                        try {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(resizedFile);
                            this.files = dataTransfer.files;
                        } catch (e) {
                            // DataTransfer reassignment unsupported — submit the original file untouched.
                        }
                        renderPreview(preview, resizedFile);
                    });
                });
            });

            const form = document.getElementById('freelancer-verification-form');
            if (form) {
                form.addEventListener('submit', function () {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>' + submitBtn.textContent.trim();
                    }
                });
            }
        });
    </script>
@endpush
