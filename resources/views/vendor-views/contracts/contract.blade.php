@extends('layouts.vendor.app')

@section('title', translate('Select_Your_Business_Tier'))

@section('content')
<div class="content container-fluid">
    {{-- CONTRACT SIGNING MODAL --}}
    <div class="modal fade" id="contractSigningModal" tabindex="-1" aria-labelledby="contractSigningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contractSigningModalLabel">Finxcart <span id="modal-tier-name"></span> Tier Agreement</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="contract-content-placeholder">
                        <div class="text-center p-5"><i class="spinner-border"></i> {{ translate('Loading contract...') }}</div>
                    </div>

                    <hr class="mt-4">

                    <form id="eContractForm" action="{{ route('vendor.contract.submit') }}" method="POST" enctype="multipart/form-data" class="js-skip-auto-validate" novalidate>
                        @csrf
                        <input type="hidden" name="tier_name" id="form-tier-name">
                        <input type="hidden" name="tier_id" id="form-tier-id">

                        <h4 class="mb-3">{{ translate('Electronic_Signature_and_Agreement') }}</h4>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="seller_full_name" class="form-label">{{ translate('Seller_Full_Name') }}:</label>
                                <input type="text" id="seller_full_name" name="seller_full_name" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label for="seller_entity" class="form-label">{{ translate('Seller_Entity_(if_applicable)') }}:</label>
                                <input type="text" id="seller_entity" name="seller_entity" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label for="agreement_date" class="form-label">{{ translate('Date') }}:</label>
                                <input type="date" id="agreement_date" name="agreement_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-12 mt-3">
                                <h5 class="mb-3">{{ translate('Seller_Signature') }}</h5>
                                <p class="small text-muted">{{ translate('You_must_provide_a_signature_by_either_uploading_an_image_or_drawing_it_in_the_box.') }}</p>

                                <ul class="nav nav-tabs" id="signatureTab" role="tablist">
                                    {{-- ⭐ MODIFICATION: Un-commented and set as default 'active' --}}
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="upload-tab" data-toggle="tab" data-target="#upload-pane" type="button" role="tab" aria-controls="upload-pane" aria-selected="true">{{ translate('Upload_Image') }}</button>
                                    </li>
                                    {{-- ⭐ MODIFICATION: Removed 'active' class --}}
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="draw-tab" data-toggle="tab" data-target="#draw-pane" type="button" role="tab" aria-controls="draw-pane" aria-selected="false">{{ translate('Draw_Signature') }}</button>
                                    </li>
                                </ul>

                                <div class="tab-content border border-top-0 p-3" id="signatureTabContent">
                                    {{-- ⭐ MODIFICATION: Added 'show active' classes --}}
                                    <div class="tab-pane fade show active" id="upload-pane" role="tabpanel" aria-labelledby="upload-tab" tabindex="0">
                                        <p class="small text-muted">{{ translate('Upload_a_clear_image_of_your_signature_(PNG_or_JPG).') }}</p>
                                        <input type="file" id="signature_file_upload" name="signature_file_upload" class="form-control" accept="image/png, image/jpeg, image/jpg">
                                        {{-- This hidden input is primarily for 'draw', but we leave it here. JS will clear it. --}}
                                        <input type="hidden" name="signature_base64_data" id="signature_base64_data" value="">
                                    </div>

                                    {{-- ⭐ MODIFICATION: Removed 'show active' classes --}}
                                    <div class="tab-pane fade" id="draw-pane" role="tabpanel" aria-labelledby="draw-tab" tabindex="0">
                                        <p class="small text-muted">{{ translate('Draw_your_signature_in_the_box_below.') }}</p>
                                        <canvas id="signatureCanvas" class="border border-dark bg-white" style="width: 100%; height: 200px; touch-action: none;"></canvas>
                                        <div class="d-flex justify-content-end mt-2">
                                            <button type="button" class="btn btn-outline-danger btn-sm" id="clearSignatureBtn">{{ translate('Clear') }}</button>
                                        </div>
                                    </div>
                                </div>
                                {{-- ⭐ MODIFICATION: Changed default value to 'upload' --}}
                                <input type="hidden" name="signature_method" id="signature_method" value="upload">
                            </div>
                        </div>

                        <div class="mt-4">
                            <input type="checkbox" id="i_agree" name="i_agree" value="1" required>
                            <label for="i_agree" class="ms-2">
                                {{ translate('I_Agree_by_checking_this_box_I_acknowledge_I_have_read_understood_and_agree_to_be_bound_by_this_Agreement') }}
                            </label>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" form="eContractForm" class="btn btn-primary" id="submitContractButton">{{ translate('Click_to_Proceed_and_Confirm_Agreement') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('css_or_js')
<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.1.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
<style>
.modal { z-index: 1060 !important; }
.modal-backdrop { z-index: 1050 !important; }
#signatureCanvas { cursor: crosshair; border-radius: 4px; }
.tier-card { transition: transform 0.2s ease-in-out; }
.tier-card:hover { transform: translateY(-5px); }
</style>
@endpush

@push('script')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<script>
$(document).ready(function() {
    let signaturePad = null;
    let currentTierId = null;

    function initSignaturePad() {
        const canvas = document.getElementById('signatureCanvas');
        if (!canvas) {
            return;
        }
        if (signaturePad) {
            signaturePad.off();
            signaturePad = null;
        }
        const container = canvas.parentElement;
        const rect = container.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = 200;

        try {
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                minWidth: 1,
                maxWidth: 3,
                penColor: 'rgb(0, 0, 0)',
                throttle: 16
            });
            $('#clearSignatureBtn').off('click').on('click', function() {
                if (signaturePad) {
                    signaturePad.clear();
                }
            });
        } catch (error) {
            // signature pad init failed silently
        }
    }

    function openContractModalForTier(tierName, tierId) {
        $('#modal-tier-name').text(tierName);
        $('#form-tier-name').val(tierName);
        $('#form-tier-id').val(tierId);
        currentTierId = tierId;

        const $contractPlaceholder = $('#contract-content-placeholder');
        $contractPlaceholder.html('<div class="text-center p-5"><i class="spinner-border"></i> {{ translate('Loading contract...') }}</div>');

        $('#eContractForm')[0].reset();

        // ⭐ MODIFICATION: Default to 'upload'
        $('#signature_method').val('upload');
        $('#signature_base64_data').val('');
        $('#submitContractButton').prop('disabled', false).html('{{ translate('Click_to_Proceed_and_Confirm_Agreement') }}');

        // ⭐ MODIFICATION: Ensure 'upload' tab is active on modal open
        $('#upload-tab').addClass('active').attr('aria-selected', 'true');
        $('#draw-tab').removeClass('active').attr('aria-selected', 'false');
        $('#upload-pane').addClass('show active');
        $('#draw-pane').removeClass('show active');

        $('#contractSigningModal').modal('show');

        const contractUrl = '{{ url('vendor/contract/content') }}/' + tierName;
        $.ajax({
            url: contractUrl,
            method: 'GET',
            success: function(response) {
                $contractPlaceholder.html(response.html);
                $contractPlaceholder.scrollTop(0);
            },
            error: function(xhr, status, error) {
                $contractPlaceholder.html('<div class="alert alert-danger">{{ translate('Error_loading_contract_Please_try_again.') }}</div>');
            }
        });
    }

    $('.show-contract-modal').on('click', function() {
        const tierName = $(this).data('tier-name');
        const tierId = $(this).data('tier-id');
        openContractModalForTier(tierName, tierId);
    });

    const passedTierName = "{{ $tier_name ?? null }}";
    const passedTierId = "{{ $tierID ?? null }}";
    if (passedTierName && passedTierId) {
        setTimeout(function() {
            openContractModalForTier(passedTierName, passedTierId);
        }, 300);
    }

    // ⭐ MODIFICATION: Updated tab change handler
    $('#signatureTab').on('shown.bs.tab', function (e) {
        const target = $(e.target);
        const targetId = target.attr('id');
        const methodInput = $('#signature_method');
        const fileInput = $('#signature_file_upload');

        if (targetId === 'upload-tab') {
            methodInput.val('upload');
            $('#signature_base64_data').val(''); // Clear draw data
            if (signaturePad) {
                signaturePad.clear(); // Clear canvas
            }
        } else if (targetId === 'draw-tab') {
            methodInput.val('draw');
            fileInput.val(null); // Clear file input

            // Initialize signature pad when draw tab is shown
            setTimeout(() => {
                initSignaturePad();
            }, 150);
        }
    });

    // Handle modal shown event
    $('#contractSigningModal').on('shown.bs.modal', function () {
        // ⭐ MODIFICATION: Only init pad if draw tab is active
        if ($('#draw-tab').hasClass('active')) {
             setTimeout(() => {
                 initSignaturePad();
             }, 200);
        }
    });

    $('#contractSigningModal').on('hidden.bs.modal', function () {
        if (signaturePad) {
            signaturePad.off();
            signaturePad = null;
        }
        currentTierId = null;
        $('#eContractForm')[0].reset();
        // ⭐ MODIFICATION: Reset default to 'upload'
        $('#signature_method').val('upload');
    });

    // ⭐ FINAL MODIFICATION: Form submission logic (Your existing logic is robust and already handles both cases)
    $('#eContractForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const method = $('#signature_method').val();
        let isSignatureValid = true;

        // --- Required text/date fields (form carries js-skip-auto-validate, so
        // the generic engine never runs on submit — check them explicitly here) ---
        let isValid = true;
        if (!$.trim($('#seller_full_name').val())) {
            showFieldError('#seller_full_name', '{{ translate('This_field_is_required') }}');
            isValid = false;
        } else {
            clearFieldError('#seller_full_name');
        }
        if (!$.trim($('#agreement_date').val())) {
            showFieldError('#agreement_date', '{{ translate('This_field_is_required') }}');
            isValid = false;
        } else {
            clearFieldError('#agreement_date');
        }
        if (!isValid) {
            return false;
        }

        // --- Signature Validation ---
        if (method === 'draw') {
            if (!signaturePad || signaturePad.isEmpty()) {
                isSignatureValid = false;
                alert('{{ translate('Please_draw_your_signature.') }}');
            } else {
                const base64Data = signaturePad.toDataURL("image/png");
                $('#signature_base64_data').val(base64Data);
            }
        } else if (method === 'upload') {
            const fileInput = $('#signature_file_upload')[0];
            if (!fileInput.files || !fileInput.files[0]) {
                isSignatureValid = false;
                alert('{{ translate('Please_upload_a_signature_image.') }}');
            }
            // Clear base64 data just in case
            $('#signature_base64_data').val('');
        }

        if (!isSignatureValid) {
            return false;
        }

        // --- Agreement Checkbox Validation ---
        if (!$('#i_agree').is(':checked')) {
            alert('{{ translate('Please_agree_to_the_terms_and_conditions.') }}');
            return false;
        }

        // --- AJAX Submission ---
        const $button = $('#submitContractButton');
        $button.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> {{ translate('Processing...') }}');

        const formData = new FormData(this);

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Using Blade directive
            },
            success: function(response) {
                if (response.pdf_storage_path && response.download_file_name) {
                    const downloadUrl = '{{ route('vendor.contract.download') }}' +
                        '?path=' + encodeURIComponent(response.pdf_storage_path) +
                        '&name=' + encodeURIComponent(response.download_file_name);

                    window.open(downloadUrl, '_blank');
                }

                setTimeout(() => {
                    window.location.href = '{{ route('vendor.tier.my-tier-plan') }}';
                }, 500);
            },
            error: function(xhr, status, error) {

                // Handle validation errors
                if (xhr.status === 422) {
                    let errorMsg = '{{ translate('Submission_failed_Please_check_your_input.') }}\n';
                    try {
                        let errors = xhr.responseJSON.errors;
                        for (let key in errors) {
                            errorMsg += '- ' + errors[key][0] + '\n';
                        }
                    } catch (e) {
                         errorMsg = xhr.responseJSON.message || '{{ translate('An_unknown_error_occurred.') }}';
                    }
                    alert(errorMsg);
                } else {
                     alert('{{ translate('Submission_failed_Please_try_again.') }} ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown Error'));
                }

                $button.prop('disabled', false).html('{{ translate('Click_to_Proceed_and_Confirm_Agreement') }}');
            }
        });

        return false;
    });

    $(window).on('resize', function() {
        if ($('#contractSigningModal').hasClass('show') && signaturePad && $('#draw-tab').hasClass('active')) {
            setTimeout(() => {
                initSignaturePad();
            }, 100);
        }
    });

});
</script>
@endpush
