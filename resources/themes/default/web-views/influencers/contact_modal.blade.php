<div class="modal fade" id="contactModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('influencers.contact') }}" method="POST" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        Contact <span id="modalInfName">{{ translate('influencer') }}</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="influencer_id" id="modalInfId" value="">
                    
                    <div class="form-group mb-3">
                        <label>{{ translate('your_name') }}</label>
                        <input type="text" name="customer_name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>{{ translate('your_email') }}</label>
                        <input type="email" name="customer_email" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>{{ translate('phone_optional') }}</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label>{{ translate('message') }}</label>
                        <textarea name="message" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('send_message') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SCRIPT TO TRANSFER ID TO MODAL --}}
<script>
    // We use a timeout to ensure jQuery is loaded
    setTimeout(function() {
        if (typeof $ !== 'undefined') {
            $('#contactModal').on('show.bs.modal', function (event) {
                // Button that triggered the modal
                var button = $(event.relatedTarget); 
                
                // Extract info from data-* attributes
                var id = button.data('id'); 
                var name = button.data('name'); 

                // Update the modal's content via jQuery
                var modal = $(this);
                modal.find('#modalInfId').val(id);
                modal.find('#modalInfName').text(name);
                
            });
        }
    }, 500);
</script>