@php($allDeliveryStages = \App\Enums\Freelancer\DeliveryStatus::cases())
@php($currentStageIndex = array_search($contract->delivery_status, $allDeliveryStages, true))
@php($nextStates = $contract->delivery_status->allowedNextStates())
@php($isEditable = !empty($nextStates) && !in_array($contract->status, ['completed', 'cancelled'], true))

<div class="delivery-stage-tracker mb-4">
    @foreach($allDeliveryStages as $stageIndex => $stage)
        <div class="delivery-stage {{ $stageIndex < $currentStageIndex ? 'is-done' : ($stageIndex === $currentStageIndex ? 'is-current' : 'is-upcoming') }}">
            <span class="delivery-stage-dot">{{ $stageIndex < $currentStageIndex ? '✓' : $stageIndex + 1 }}</span>
            <span class="delivery-stage-label">{{ translate(str_replace('_', ' ', $stage->value)) }}</span>
        </div>
    @endforeach
</div>

@if($isEditable)
    <form action="{{ route('freelancer.contracts.delivery-status.update', $contract->id) }}" method="POST"
          class="delivery-update-bar mb-3" id="deliveryStatusForm" novalidate>
        @csrf
        <div class="delivery-update-field">
            <label class="delivery-update-label" for="delivery_status_select">{{ translate('move_delivery_to') }}</label>
            <select name="delivery_status" id="delivery_status_select" class="form-control delivery-update-select" required>
                @foreach($nextStates as $state)
                    <option value="{{ $state->value }}">{{ translate(str_replace('_', ' ', $state->value)) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn--primary delivery-update-btn" id="deliveryStatusSubmitBtn">
            <span class="btn-label">{{ translate('update') }}</span>
        </button>
    </form>
@else
    <div class="delivery-status-note mb-3">
        @if($contract->status === 'cancelled')
            {{ translate('this_contract_was_cancelled') }}
        @elseif($contract->status === 'completed')
            <i class="tio-verified"></i> {{ translate('this_delivery_was_approved_and_the_contract_is_complete') }}
        @elseif($contract->delivery_status === \App\Enums\Freelancer\DeliveryStatus::Delivered)
            {{ translate('delivered_waiting_for_the_clients_review') }}
        @endif
    </div>
@endif

@if($contract->rejection_reason)
    <div class="alert alert-warning mb-3">
        <strong>{{ translate('customer_requested_revisions') }}:</strong> {{ $contract->rejection_reason }}
    </div>
@endif
