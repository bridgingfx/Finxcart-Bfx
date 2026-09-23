@if($delivery)
    @php($milestoneStatus = $delivery->status)

    @forelse($delivery->deliverables as $deliverable)
        <div class="deliverable-box">
            @if($deliverable->note)
                <div>{{ $deliverable->note }}</div>
            @endif
            @foreach($deliverable->attachments as $attachment)
                <a class="attachment-link" href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('freelancer-contract-attachments.deliverable', now()->addMinutes(30), ['attachment' => $attachment->id]) }}">
                    &#128206; {{ $attachment->original_name }}
                </a>
            @endforeach
        </div>
    @empty
        <p class="deliverable-empty mb-0">{{ translate('you_have_not_submitted_a_delivery_yet') }}</p>
    @endforelse

    @if($milestoneStatus === 'pending')
        {{-- <form action="{{ route('freelancer.contracts.milestones.submit', $delivery->id) }}" method="POST"
              enctype="multipart/form-data" class="mt-3" id="deliverySubmitForm" novalidate>
            @csrf
            <div class="form-group">
                <textarea name="note" class="form-control" rows="2" placeholder="{{ translate('describe_what_you_delivered') }}"></textarea>
            </div>
            <div class="form-group">
                <input type="file" name="attachments[]" multiple class="form-control-file">
            </div>
            <button type="submit" class="btn btn--primary btn" id="deliverySubmitBtn">{{ translate('submit_delivery') }}</button>
        </form> --}}

        <form action="{{ route('freelancer.contracts.milestones.submit', $delivery->id) }}" method="POST"
            enctype="multipart/form-data" class="mt-3" id="deliverySubmitForm" novalidate>
            @csrf

            <div class="form-group">
                <textarea name="note" class="form-control" rows="2"
                    placeholder="{{ translate('describe_what_you_delivered') }}"></textarea>
            </div>

            <div class="form-group">
                <input type="file" name="attachments[]" multiple class="form-control-file">
            </div>

            <div class="text-center mt-3">
                <button type="submit" class="btn btn--primary" id="deliverySubmitBtn">
                    {{ translate('submit_delivery') }}
                </button>
            </div>
        </form>
    @elseif($milestoneStatus === 'submitted')
        <div class="text-muted small mt-2">{{ translate('waiting_for_client_approval') }}</div>
    @elseif($milestoneStatus === 'completed')
        <div class="text-success small mt-2"><i class="tio-verified"></i> {{ translate('client_approved_this_delivery') }}</div>
    @endif
@endif
