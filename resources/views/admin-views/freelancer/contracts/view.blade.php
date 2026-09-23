@extends('layouts.admin.app')

@section('title', translate('contract') . ' #' . $contract->id)

@section('content')
    @php
        $contractStatusBadge = match($contract->status) {
            'active' => 'badge-soft--primary',
            'submitted' => 'badge-soft-warning',
            'completed' => 'badge-soft-success',
            'cancelled' => 'badge-soft-danger',
            default => 'badge-soft-secondary',
        };
        $deliveryStatusValue = $contract->delivery_status?->value;
        $deliveryStatusBadge = match($deliveryStatusValue) {
            'in_progress' => 'badge-soft--primary',
            'submitted_for_review' => 'badge-soft-warning',
            'delivered' => 'badge-soft-success',
            default => 'badge-soft-secondary',
        };
    @endphp
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0">{{ translate('contract') }} #{{ $contract->id }}</h2>
            <div class="d-flex gap-2">
                <span class="badge {{ $contractStatusBadge }} text-capitalize fz-14">{{ str_replace('_', ' ', $contract->status) }}</span>
                @if($deliveryStatusValue)
                    <span class="badge {{ $deliveryStatusBadge }} text-capitalize fz-14">{{ translate('delivery') }}: {{ str_replace('_', ' ', $deliveryStatusValue) }}</span>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-muted small">{{ translate('client') }}</div>
                        <div class="fw-bold">{{ $contract->customer?->f_name }} {{ $contract->customer?->l_name }} ({{ $contract->customer?->email }})</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">{{ translate('freelancer') }}</div>
                        <div class="fw-bold">{{ $contract->freelancer?->shop?->name ?? trim($contract->freelancer?->f_name . ' ' . $contract->freelancer?->l_name) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">{{ translate('service') }}</div>
                        @if($contract->service)
                            <a href="{{ route('admin.freelancer.services.show', $contract->service->id) }}" class="fw-bold text-decoration-none text-hover-primary">
                                {{ $contract->service->title }}
                            </a>
                        @else
                            <div class="fw-bold">-</div>
                        @endif
                    </div>
                    <div class="col-md-12">
                        <div class="text-muted small">{{ translate('scope') }}</div>
                        <div>{{ $contract->scope }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">{{ translate('total_amount') }}</div>
                        <div class="fw-bold">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $contract->total_amount), currencyCode: getCurrencyCode()) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">{{ translate('delivery_status') }}</div>
                        <div class="fw-bold text-capitalize">{{ $deliveryStatusValue ? str_replace('_', ' ', $deliveryStatusValue) : '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">{{ translate('delivered_at') }}</div>
                        <div class="fw-bold">{{ $contract->delivered_at?->format('d M, Y h:i A') ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">{{ translate('created_at') }}</div>
                        <div class="fw-bold">{{ $contract->created_at->format('d M, Y h:i A') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">{{ translate('completed_at') }}</div>
                        <div class="fw-bold">{{ $contract->completed_at?->format('d M, Y h:i A') ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($contract->cancellation_status === 'requested')
            <div class="card mb-3 border-warning">
                <div class="card-header bg-soft-warning">
                    <h5 class="mb-0">{{ translate('cancellation_requested') }}</h5>
                </div>
                <div class="card-body">
                    <div class="text-muted small mb-1">{{ translate('customers_reason') }}</div>
                    <div class="mb-3">{{ $contract->cancellation_reason }}</div>
                    <div class="d-flex gap-2 flex-wrap">
                        <form action="{{ route('admin.freelancer.contracts.cancellation.approve', $contract->id) }}" method="POST" onsubmit="return confirm('{{ translate('this_will_cancel_the_contract_and_let_the_customer_re_hire_this_service') }}');" novalidate>
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">{{ translate('approve_cancel_contract') }}</button>
                        </form>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#denyCancellationModal">
                            {{ translate('deny') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="denyCancellationModal" tabindex="-1">
                <div class="modal-dialog">
                    <form action="{{ route('admin.freelancer.contracts.cancellation.deny', $contract->id) }}" method="POST" class="modal-content" novalidate>
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ translate('deny_cancellation_request') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">{{ translate('note_to_customer') }} ({{ translate('optional') }})</label>
                            <textarea name="admin_note" class="form-control" rows="3" maxlength="1000"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('close') }}</button>
                            <button type="submit" class="btn btn-danger">{{ translate('deny_request') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        @elseif($contract->cancellation_status === 'approved' || $contract->cancellation_status === 'denied')
            <div class="alert {{ $contract->cancellation_status === 'approved' ? 'alert-success' : 'alert-secondary' }} mb-3">
                {{ translate('cancellation_request') }}: <strong class="text-capitalize">{{ $contract->cancellation_status }}</strong>
                @if($contract->cancellation_admin_note)
                    &mdash; {{ $contract->cancellation_admin_note }}
                @endif
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">{{ translate('milestones') }}</h5></div>
            <div class="table-responsive">
                <table class="table table-hover table-borderless table-thead-bordered table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('title') }}</th>
                        <th>{{ translate('amount') }}</th>
                        <th>{{ translate('status') }}</th>
                        <th>{{ translate('submitted_at') }}</th>
                        <th>{{ translate('approved_at') }}</th>
                        <th>{{ translate('deliverables') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($contract->milestones as $milestone)
                        @php
                            $milestoneStatusBadge = match($milestone->status) {
                                'pending' => 'badge-soft-secondary',
                                'submitted' => 'badge-soft-warning',
                                'completed' => 'badge-soft-success',
                                default => 'badge-soft-secondary',
                            };
                        @endphp
                        <tr>
                            <td>{{ $milestone->title }}</td>
                            <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $milestone->amount), currencyCode: getCurrencyCode()) }}</td>
                            <td><span class="badge {{ $milestoneStatusBadge }} text-capitalize">{{ $milestone->status }}</span></td>
                            <td>{{ $milestone->submitted_at?->format('d M, Y h:i A') ?? '-' }}</td>
                            <td>{{ $milestone->approved_at?->format('d M, Y h:i A') ?? '-' }}</td>
                            <td>
                                @foreach($milestone->deliverables as $deliverable)
                                    <div class="mb-1">
                                        {{ $deliverable->note }}
                                        @foreach($deliverable->attachments as $attachment)
                                            <a href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('freelancer-contract-attachments.deliverable', now()->addMinutes(30), ['attachment' => $attachment->id]) }}" class="d-block small">
                                                &#128206; {{ $attachment->original_name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">{{ translate('messages') }}</h5></div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                @forelse($contract->messages as $message)
                    <div class="mb-2">
                        <span class="badge badge-soft-{{ $message->sender_type === 'customer' ? 'info' : 'success' }} text-capitalize">{{ $message->sender_type }}</span>
                        {{ $message->body }}
                        @foreach($message->attachments as $attachment)
                            <a href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('freelancer-contract-attachments.message', now()->addMinutes(30), ['attachment' => $attachment->id]) }}" class="d-block small ms-4">
                                &#128206; {{ $attachment->original_name }}
                            </a>
                        @endforeach
                        <div class="text-muted small">{{ $message->created_at->format('d M, Y h:i A') }}</div>
                    </div>
                @empty
                    <div class="text-muted">{{ translate('no_messages_yet') }}</div>
                @endforelse
            </div>
        </div>

        @if($contract->reviews->count())
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">{{ translate('reviews') }}</h5></div>
                <div class="card-body">
                    @foreach($contract->reviews as $review)
                        <div class="mb-2">
                            <span class="badge badge-soft-secondary text-capitalize">{{ $review->reviewer_type }}</span>
                            <span class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fi {{ $i > $review->rating ? 'fi-rr-star' : 'fi-sr-star' }}"></i>
                                @endfor
                            </span>
                            <div>{{ $review->body }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
