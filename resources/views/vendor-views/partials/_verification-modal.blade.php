@php
    $seller = auth('seller')->user();
    $verification = $seller?->vendorVerification;

    // Don't show on verification pages — vendor is already there to resubmit
    $currentRoute = request()->route()?->getName() ?? '';
    $hideOnRoutes = str_starts_with($currentRoute, 'vendor.verification.')
        || str_starts_with($currentRoute, 'vendor.payout-kyc')
        || str_starts_with($currentRoute, 'vendor.kyc');
@endphp

@if($seller && $verification && $verification->status === 'rejected' && !$hideOnRoutes)
<style>
    .modal-open .modal-backdrop {
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        background-color: rgba(15,23,42,.5);
    }
    #vendorVerifModal .verif-close-btn {
        position: absolute;
        top: 12px; right: 14px; z-index: 10;
        width: 32px; height: 32px;
        border-radius: 50%;
        background: rgba(255,255,255,.18);
        border: 1.5px solid rgba(255,255,255,.35);
        color: #fff;
        font-size: 18px;
        line-height: 1;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        transition: background .15s;
    }
    #vendorVerifModal .verif-close-btn:hover {
        background: rgba(255,255,255,.32);
    }
</style>

<div class="modal fade" id="vendorVerifModal" tabindex="-1" aria-hidden="true"
     data-dismiss="modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius:16px;">
            <div class="modal-body p-0 position-relative">

                {{-- × close button (top-right, over the red panel) --}}
                <button type="button" class="verif-close-btn" data-dismiss="modal" aria-label="Close">
                    <i class="tio-clear"></i>
                </button>

                <div class="row g-0">

                    {{-- Left: Red panel — content vertically centred --}}
                    <div class="col-lg-5 text-white p-4 p-md-5 d-flex align-items-center"
                         style="min-height:340px;background:linear-gradient(145deg,#b91c1c 0%,#ef4444 100%);">
                        <div>
                            <span class="badge mb-3 px-3 py-2"
                                  style="background:rgba(255,255,255,.2);color:#fff;font-size:11px;font-weight:600;letter-spacing:.4px;border-radius:50px;">
                                {{ translate('Verification_Rejected') }}
                            </span>
                            <h3 class="mb-3 text-white fw-bold lh-sm">
                                {{ translate('Your_Application_Was_Rejected') }}
                            </h3>
                            <p class="mb-0 small" style="color:rgba(255,255,255,.72);">
                                {{ translate('Please_review_the_rejection_reason_and_resubmit_your_updated_credentials.') }}
                            </p>
                        </div>
                    </div>

                    {{-- Right: Action panel --}}
                    <div class="col-lg-7 p-4 p-md-5 d-flex flex-column justify-content-center">

                        <h5 class="fw-semibold mb-1">{{ translate('What_to_do_next') }}</h5>
                        <p class="text-muted small mb-3">
                            {{ translate('Review_the_reason_below_then_update_and_resubmit_your_documents.') }}
                        </p>

                        @if($verification->rejection_reason)
                            <div class="rounded p-3 mb-4"
                                 style="background:#fef2f2;border-left:3px solid #ef4444;">
                                <div class="fw-semibold small mb-1" style="color:#b91c1c;">
                                    {{ translate('Rejection_Reason') }}
                                </div>
                                <div class="small text-dark">{{ $verification->rejection_reason }}</div>
                                @if($verification->reviewed_at)
                                    <div class="small mt-1" style="color:#9ca3af;">
                                        {{ translate('Reviewed') }} {{ $verification->reviewed_at->format('d M Y, h:i A') }}
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="d-grid gap-2">
                            {{-- Resubmit — dismisses modal first, then navigates --}}
                            <a href="{{ route('vendor.verification.form') }}"
                               class="btn btn--primary"
                               onclick="$('#vendorVerifModal').modal('hide'); return true;">
                                <i class="tio-edit mr-1"></i>
                                {{ translate('Resubmit_Verification') }}
                            </a>

                            {{-- Email support --}}
                            <a href="mailto:{{ getWebConfig('email') ?? 'support@finxcart.com' }}"
                               class="btn btn-outline-secondary">
                                <i class="tio-email-outlined mr-1"></i>
                                {{ translate('Email_Support') }}
                            </a>
                        </div>

                        <p class="text-muted small mt-3 mb-0">
                            {{ translate('You_can_close_this_and_browse_your_dashboard_while_preparing_your_documents.') }}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@push('script_2')
<script>
    $(document).ready(function () {
        $('#vendorVerifModal').modal({
            backdrop: true,
            keyboard: true,
            show: true
        });
    });
</script>
@endpush
@endif
