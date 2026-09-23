@extends('layouts.vendor.app')

@section('title', translate('Payout_KYC'))

@push('css_or_js')
    <style>
        .kyc-hero {
            overflow: hidden;
            border: 0;
            border-radius: 20px;
            color: #fff;
            background: linear-gradient(135deg, #073b74 0%, #1268b3 70%, #1f86d3 100%);
            box-shadow: 0 18px 42px rgba(7, 59, 116, .16);
        }
        .kyc-hero-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: #073b74;
            background: rgba(255, 255, 255, .94);
        }
        .kyc-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 13px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .28);
            color: #fff;
            font-weight: 700;
        }
        .kyc-workspace {
            border: 1px solid #e4edf6;
            border-radius: 18px;
            box-shadow: 0 12px 34px rgba(7, 59, 116, .07);
        }
        .kyc-step {
            height: 100%;
            padding: 16px;
            border: 1px solid #e5edf6;
            border-radius: 14px;
            background: #f8fbff;
            transition: transform .2s ease, border-color .2s ease;
        }
        .kyc-step:hover {
            transform: translateY(-2px);
            border-color: rgba(7, 59, 116, .3);
        }
        .kyc-step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #073b74;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        @php($payoutKycStatus = $seller?->kyc_status ?? 'not_started')
        @php($companyVerified = ($seller?->status ?? null) === 'approved' || ($verification?->status ?? null) === 'approved')
        @php($payoutDocument = $verification?->payout_kyc_document)
        @php($payoutDocumentLink = $payoutDocument ? storageLink('vendor-verifications', $payoutDocument, getWebConfig(name: 'storage_connection_type') ?? 'public') : null)

        <div class="card kyc-hero mb-4">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="kyc-hero-icon"><i class="tio-security-on"></i></span>
                        <div>
                            <div class="text-uppercase font-weight-bold opacity-75 mb-1">{{ translate('Secure_Withdrawals') }}</div>
                            <h2 class="text-white mb-1">{{ translate('Payout_KYC') }}</h2>
                            <p class="mb-0 text-white-50">{{ translate('Verify_your_identity_to_keep_wallet_withdrawals_secure.') }}</p>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="kyc-status-pill"><i class="tio-verified"></i>{{ ucfirst(str_replace('_', ' ', $payoutKycStatus)) }}</span>
                        <a href="{{ route('vendor.profile.update', auth('seller')->id()) }}?tab=payout-kyc" class="btn btn-light">
                            <i class="tio-user-outlined mr-1"></i>{{ translate('Profile_Settings') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="kyc-step"><span class="kyc-step-number">1</span><h5 class="mt-3 mb-1">{{ translate('Company_Verification') }}</h5><div class="text-muted">{{ $companyVerified ? translate('Completed') : translate('Required_first') }}</div></div>
            </div>
            <div class="col-md-4">
                <div class="kyc-step"><span class="kyc-step-number">2</span><h5 class="mt-3 mb-1">{{ translate('Submit_Document') }}</h5><div class="text-muted">{{ $payoutDocument ? translate('Document_uploaded') : translate('Waiting_for_document') }}</div></div>
            </div>
            <div class="col-md-4">
                <div class="kyc-step"><span class="kyc-step-number">3</span><h5 class="mt-3 mb-1">{{ translate('Admin_Review') }}</h5><div class="text-muted">{{ ucfirst(str_replace('_', ' ', $payoutKycStatus)) }}</div></div>
            </div>
        </div>

        <div class="card kyc-workspace">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h4 class="mb-1">{{ translate('Withdrawal_Verification') }}</h4>
                        <p class="text-muted mb-0">
                            {{ translate('This_KYC_is_for_vendor_wallet_withdrawal_and_payout_access_only.') }}
                        </p>
                    </div>
                    <span class="badge badge-{{ $payoutKycStatus === 'approved' ? 'success' : ($payoutKycStatus === 'rejected' ? 'danger' : 'warning') }}">
                        {{ ucfirst(str_replace('_', ' ', $payoutKycStatus)) }}
                    </span>
                </div>

                @if(!$companyVerified)
                    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-3 mb-0">
                        <span>{{ translate('Complete_company_profile_verification_before_starting_payout_KYC.') }}</span>
                        <a href="{{ route('vendor.verification.form') }}" class="btn btn-sm btn-warning">
                            {{ translate('Company_Verification') }}
                        </a>
                    </div>
                @elseif($kycMethod === 'disabled')
                    <div class="alert alert-info mb-0">
                        {{ translate('Payout_KYC_is_not_required_for_your_account.') }}
                    </div>
                @elseif($payoutKycStatus === 'approved')
                    <div class="alert alert-success d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <span><i class="tio-checkmark-circle mr-1"></i>{{ translate('Your_payout_KYC_is_approved.') }}</span>
                        <button type="button" class="btn btn-sm btn-outline-success" data-toggle="collapse" data-target="#approved-kyc-update">
                            <i class="tio-edit mr-1"></i>{{ translate('Update_Document') }}
                        </button>
                    </div>
                    @if($payoutDocumentLink)
                        <div class="alert alert-light border mb-3">
                            <div class="font-weight-semibold mb-1">{{ translate('Approved_document') }}</div>
                            <a href="{{ $payoutDocumentLink['path'] ?? '#' }}" target="_blank"><i class="tio-open-in-new mr-1"></i>{{ $payoutDocument }}</a>
                        </div>
                    @endif
                    <div class="collapse" id="approved-kyc-update">
                        <div class="alert alert-warning">{{ translate('Submitting_a_replacement_document_will_send_your_payout_KYC_back_for_admin_review.') }}</div>
                        <form action="{{ route('vendor.payout-kyc.request-review') }}" method="POST" enctype="multipart/form-data" novalidate>
                            @csrf
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label class="form-label">{{ translate('Replacement_KYC_Document') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="payout_kyc_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">{{ translate('Note') }}</label>
                                    <textarea name="payout_kyc_note" class="form-control" rows="3" maxlength="1000" placeholder="{{ translate('Explain_what_you_are_updating') }}">{{ old('payout_kyc_note', $verification?->payout_kyc_note) }}</textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn--primary mt-3"><i class="tio-send mr-1"></i>{{ translate('Submit_Update_For_Review') }}</button>
                        </form>
                    </div>
                @elseif($kycMethod === 'sumsub')
                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <strong>{{ translate('Automated_payout_KYC_is_enabled.') }}</strong>
                        <div class="small mt-1">{{ translate('Click_start_to_complete_the_identity_check_with_Sumsub.') }}</div>
                    </div>
                    <button type="button" id="start-sumsub-kyc" class="btn btn--primary mb-4">
                        {{ translate('Start_Sumsub_Verification') }}
                    </button>
                    <div id="sumsub-websdk-container" class="border rounded bg-light d-none" style="min-height:600px;"></div>
                    <div id="sumsub-websdk-error" class="alert alert-danger border-0 shadow-sm mt-3 d-none"></div>
                @else
                    <div class="alert alert-warning mb-4">
                        {{ translate('Manual_payout_KYC_is_enabled._Admin_will_review_this_separately_from_company_verification.') }}
                    </div>

                    @if($payoutDocumentLink)
                        <div class="alert alert-light border mb-4">
                            <div class="fw-semibold mb-1">{{ translate('Current_uploaded_document') }}</div>
                            <a href="{{ $payoutDocumentLink['path'] ?? '#' }}" target="_blank">{{ $payoutDocument }}</a>
                            @if($verification?->payout_kyc_submitted_at)
                                <div class="small text-muted mt-1">{{ translate('Submitted_at') }}: {{ $verification->payout_kyc_submitted_at->format('M d, Y h:i A') }}</div>
                            @endif
                        </div>
                    @endif

                    @if(!$payoutDocumentLink || $payoutKycStatus !== 'pending')
                        <form action="{{ route('vendor.payout-kyc.request-review') }}" method="POST" enctype="multipart/form-data" novalidate>
                            @csrf
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label class="form-label">{{ translate('Payout_KYC_Document') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="payout_kyc_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                                    <div class="small text-muted mt-1">{{ translate('Upload_bank_statement_or_government_ID_for_payout_verification.') }}</div>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">{{ translate('Note') }}</label>
                                    <textarea name="payout_kyc_note" class="form-control" rows="3" maxlength="1000" placeholder="{{ translate('Optional_note_for_admin') }}">{{ old('payout_kyc_note', $verification?->payout_kyc_note) }}</textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn--primary mt-4">
                                {{ $payoutDocumentLink ? translate('Resubmit_Payout_KYC_For_Review') : translate('Submit_Payout_KYC_For_Review') }}
                            </button>
                        </form>
                    @else
                        <div class="alert alert-info mb-0">
                            {{ translate('Your_payout_KYC_request_is_waiting_for_admin_review.') }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script')
    @if($companyVerified && $kycMethod === 'sumsub' && $payoutKycStatus !== 'approved')
        <script src="https://static.sumsub.com/idensic/static/sns-websdk-builder.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const sumsubErrorBox = document.getElementById('sumsub-websdk-error');
                const showSumsubError = function (message) {
                    if (!sumsubErrorBox) {
                        return;
                    }

                    sumsubErrorBox.classList.remove('d-none');
                    sumsubErrorBox.textContent = message;
                };
                const getNewAccessToken = function () {
                    return fetch('{{ route('vendor.kyc.token') }}')
                        .then(response => response.json().then(data => ({ ok: response.ok, data })))
                        .then(({ ok, data }) => {
                            if (!ok || !data.token) {
                                throw new Error(data.message || 'Unable to start Sumsub verification.');
                            }

                            return data.token;
                        });
                };

                document.getElementById('start-sumsub-kyc')?.addEventListener('click', function () {
                    const container = document.getElementById('sumsub-websdk-container');
                    this.classList.add('d-none');
                    container?.classList.remove('d-none');

                    getNewAccessToken()
                        .then(function (accessToken) {
                            if (typeof snsWebSdk === 'undefined') {
                                throw new Error('Sumsub WebSDK could not be loaded.');
                            }

                            snsWebSdk
                                .init(accessToken, getNewAccessToken)
                                .withConf({
                                    lang: 'en',
                                    email: @json($seller?->email),
                                    phone: @json($seller?->phone),
                                })
                                .withOptions({ addViewportTag: false, adaptIframeHeight: true })
                                .on('idCheck.onApplicantStatusChanged', function (payload) {
                                    if (payload.reviewStatus === 'completed') {
                                        window.location.reload();
                                    }
                                })
                                .on('idCheck.onError', function () {
                                    showSumsubError('There was a problem loading the verification widget. Please refresh and try again.');
                                })
                                .build()
                                .launch('#sumsub-websdk-container');
                        })
                        .catch(function (error) {
                            showSumsubError(error.message || 'Unable to start Sumsub verification.');
                        });
                    });
            });
        </script>
    @endif

    <script>
        $(document).on('change', 'input[name="payout_kyc_document"]', function () {
            FormValidators.file(this, ['pdf', 'jpg', 'jpeg', 'png', 'webp'], 10,
                "{{ translate('Only_PDF_JPG_PNG_WEBP_files_up_to_10MB_are_allowed') }}");
        });

        // File inputs are architecturally skipped by the generic auto-validator
        // (presence of a file can't be inferred from the DOM the way text/select
        // values can), so a required file input needs an explicit submit-time
        // presence check — otherwise a vendor can submit with nothing chosen and
        // get no feedback until the server round-trips a 422.
        $(document).on('submit', 'form:has(input[name="payout_kyc_document"][required])', function (e) {
            let $file = $(this).find('input[name="payout_kyc_document"]');
            let input = $file[0];
            if (!input.files || !input.files.length) {
                showFieldError($file, "{{ translate('Please_select_a_file_to_upload') }}");
                e.preventDefault();
            } else {
                clearFieldError($file);
            }
        });
    </script>
@endpush
