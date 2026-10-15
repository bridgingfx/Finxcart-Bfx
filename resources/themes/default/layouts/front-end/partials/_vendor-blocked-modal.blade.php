<div class="modal fade" id="vendorBlockedModal" tabindex="-1" role="dialog" aria-labelledby="vendorBlockedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div style="background:linear-gradient(135deg,#1a2f5e,#2563eb);height:6px;"></div>
            <div class="modal-body text-center px-5 py-5">
                <div class="mb-4" style="width:80px;height:80px;border-radius:50%;background:#fff8e1;display:flex;align-items:center;justify-content:center;margin:0 auto;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="#f59e0b"/>
                    </svg>
                </div>
                <h4 class="font-weight-bold mb-3" style="color:#1a2f5e;">{{ translate('Vendors_Cannot_Purchase') }}</h4>
                <p class="text-muted mb-4" id="vendorBlockedMessage">
                    {{ translate('Vendors_cannot_purchase_products._Please_register_or_login_as_a_customer_to_buy.') }}
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="{{ route('customer.auth.login') }}" class="btn btn-outline-secondary px-4">{{ translate('Customer_Login') }}</a>
                    <a href="{{ route('customer.auth.sign-up') }}" class="btn btn--primary px-4">{{ translate('Register_as_Customer') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
    $(document).ajaxSuccess(function (event, xhr) {
        var response = xhr.responseJSON;
        if (response && response.vendor_blocked) {
            $('#vendorBlockedMessage').text(response.message);
            $('#vendorBlockedModal').modal('show');
        }
    });
</script>
@endpush
