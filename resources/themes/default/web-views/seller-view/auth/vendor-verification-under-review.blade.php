@extends('layouts.front-end.app')

@section('title', translate('vendor_verification'))

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5 text-center">
                        <span class="badge badge-warning text-dark mb-3 px-3 py-2">{{ translate('under_review') }}</span>
                        <h2 class="mb-3">{{ translate('vendor_verification_documents_submitted_under_review') }}</h2>
                        <p class="text-muted mb-0">{{ translate('you_will_receive_an_email_as_soon_as_a_decision_is_made') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
