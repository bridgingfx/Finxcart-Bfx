@php
    $seller = auth('seller')->user();
    $verification = $seller?->vendorVerification;
@endphp

@if(isset($seller_not_approved) && $seller_not_approved)
    @if($verification && $verification->status === 'pending')
        <div class="alert alert-info d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
            <div>
                <div class="fw-semibold">Your documents are under review.</div>
                <div class="text-muted fs-12">Your dashboard is read-only until admin approval is completed.</div>
            </div>
            <span class="badge bg-info">Read Only</span>
        </div>
    @else
        <div class="alert alert-warning d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
            <div>
                <div class="fw-semibold">Your account is pending verification.</div>
                <div class="text-muted fs-12">Please complete your profile and submit documents to unlock vendor actions.</div>
            </div>
            <a href="{{ route('vendor.verification.form') }}" class="btn btn-dark text-nowrap">Complete Verification</a>
        </div>
    @endif
@elseif($seller && $seller->status === 'pending' && !$verification && empty($seller->seller_type))
    <div class="alert alert-warning d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
            <div class="fw-semibold">Complete your profile to start selling.</div>
            <div class="text-muted fs-12">Finish your profile details, choose a seller type, and upload your documents.</div>
        </div>
        <a href="{{ route('vendor.verification.form') }}" class="btn btn-dark text-nowrap">Complete Profile</a>
    </div>
@elseif($seller && $seller->status === 'pending' && !$verification && !empty($seller->seller_type))
    <div class="alert alert-warning d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
            <div class="fw-semibold">Submit your documents to get verified.</div>
            <div class="text-muted fs-12">Your profile type is set. Please upload your verification documents.</div>
        </div>
        <a href="{{ route('vendor.verification.form') }}" class="btn btn-dark text-nowrap">Submit Documents</a>
    </div>
@elseif($seller && $seller->status === 'pending' && $verification && $verification->status === 'pending')
    <div class="alert alert-success d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
            <div class="fw-semibold">Application submitted successfully. Please wait for admin approval.</div>
            <div class="text-white fs-12">You will be notified by email once a decision is made.</div>
        </div>
        <span class="badge bg-success">Under Review</span>
    </div>
@endif
