@if($seller->status === 'pending')
<div class="mt-4">
    <div class="flex-start mb-2">
        <div class="mx-1"><h4><i class="fi fi-rr-shop"></i></h4></div>
        <div>{{ translate('vendor_request_for_open_a_shop') }}</div>
    </div>
    <div class="d-flex gap-2 justify-content-center">
        <button type="button" class="btn btn-primary btn-sm"
                data-bs-toggle="modal" data-bs-target="#vendorApproveModal">
            {{ translate('approve') }}
        </button>
        <button type="button" class="btn btn-danger btn-sm"
                data-bs-toggle="modal" data-bs-target="#vendorRejectModal">
            {{ translate('reject') }}
        </button>
    </div>
</div>

{{-- Approve Modal --}}
<div class="modal fade" id="vendorApproveModal" tabindex="-1" role="dialog" aria-labelledby="vendorApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.vendors.updateStatus') }}" method="POST" novalidate>
                @csrf
                <input type="hidden" name="id" value="{{ $seller->id }}">
                <input type="hidden" name="status" value="approved">
                <div class="modal-header">
                    <h5 class="modal-title" id="vendorApproveModalLabel">
                        <i class="fi fi-rr-check-circle text-success me-1"></i>
                        {{ translate('approve_vendor') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        {{ translate('you_are_approving') }}
                        <strong>{{ $seller->f_name }} {{ $seller->l_name }}</strong>.
                        {{ translate('the_vendor_will_be_notified_by_email') }}.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            {{ translate('admin_remark') }}
                            <span class="text-muted small fw-normal">({{ translate('optional') }})</span>
                        </label>
                        <textarea name="admin_remark" class="form-control" rows="3"
                                  placeholder="{{ translate('add_a_note_for_your_records_visible_to_vendor') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        {{ translate('cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fi fi-rr-check-circle me-1"></i> {{ translate('confirm_approve') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="vendorRejectModal" tabindex="-1" role="dialog" aria-labelledby="vendorRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.vendors.updateStatus') }}" method="POST" novalidate>
                @csrf
                <input type="hidden" name="id" value="{{ $seller->id }}">
                <input type="hidden" name="status" value="rejected">
                <div class="modal-header">
                    <h5 class="modal-title" id="vendorRejectModalLabel">
                        <i class="fi fi-rr-cross-circle text-danger me-1"></i>
                        {{ translate('reject_vendor') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        {{ translate('you_are_rejecting') }}
                        <strong>{{ $seller->f_name }} {{ $seller->l_name }}</strong>.
                        {{ translate('please_provide_a_reason_so_the_vendor_can_reapply') }}.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            {{ translate('reason_for_rejection') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="admin_remark" class="form-control" rows="3" required
                                  placeholder="{{ translate('e_g_incomplete_documents_or_invalid_business_details') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        {{ translate('cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fi fi-rr-cross-circle me-1"></i> {{ translate('confirm_reject') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
