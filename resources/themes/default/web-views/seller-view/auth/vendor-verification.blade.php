@extends('layouts.front-end.app')

@section('title', translate('vendor_verification'))

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="mb-2">{{ translate('vendor_verification') }}</h2>
                        <p class="text-muted mb-4">{{ translate('please_complete_your_company_credentials_to_continue') }}</p>

                        @if($verification?->status === 'rejected' && $verification?->rejection_reason)
                            <div class="alert alert-danger">
                                <strong>{{ translate('rejection_reason') }}:</strong> {{ $verification->rejection_reason }}
                            </div>
                        @endif

                        <form action="{{ route('vendor.verification.store') }}" method="POST" enctype="multipart/form-data" class="row g-3" novalidate>
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('company_website') }} <span class="text-danger">*</span></label>
                                <input type="text" name="company_website" class="form-control" value="{{ old('company_website', $verification?->company_website) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('company_registration_no') }} <span class="text-danger">*</span></label>
                                <input type="text" name="company_no" class="form-control" value="{{ old('company_no', $verification?->company_no) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('company_registered_country') }} <span class="text-danger">*</span></label>
                                <input type="text" name="company_registered_country" class="form-control" value="{{ old('company_registered_country', $verification?->company_registered_country) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('company_license') }} <span class="text-danger">*</span></label>
                                <input type="file" name="company_license" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn--primary">
                                    {{ translate('submit_verification') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
