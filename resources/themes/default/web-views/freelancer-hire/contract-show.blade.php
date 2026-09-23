@extends('layouts.front-end.app')

@section('title', translate('contract') . ' #' . $contract->id)

@push('css_or_js')
    <style>
        .contract-page { background: #f6f8fb; padding: 38px 0 54px; }

        .contract-detail-header {
            background: linear-gradient(135deg, #17395e 0%, #1f4a78 100%); border-radius: 10px; padding: 24px 26px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 16px; flex-wrap: wrap; color: #fff;
        }
        .contract-detail-avatar {
            width: 56px; height: 56px; border-radius: 50%; object-fit: cover; background: rgba(255,255,255,.15);
            flex-shrink: 0; border: 2px solid rgba(255,255,255,.4);
        }
        .contract-detail-title { font-weight: 800; font-size: 19px; color: #fff; }
        .contract-detail-meta { font-size: 13px; color: rgba(255,255,255,.75); margin-top: 3px; }
        .contract-detail-header .status-pill { margin-left: auto; background: rgba(255,255,255,.18); color: #fff; }

        .contract-section { background: #fff; border: 1px solid #e7ebf0; border-radius: 10px; padding: 24px; margin-bottom: 22px; }
        .contract-section-title { font-weight: 800; font-size: 18px; color: #17395e; margin-bottom: 16px; }
        .status-pill { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; text-transform: capitalize; }
        .status-active, .status-pending { background: #e7f3ff; color: #1a73e8; }
        .status-submitted { background: #fff4e5; color: #b76e00; }
        .status-completed, .status-approved { background: #e6f7ee; color: #16794f; }
        .status-cancelled { background: #fde8e8; color: #c0392b; }

        .contract-fact-label { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .03em; }
        .contract-fact-value { font-size: 14px; font-weight: 700; color: #1f2937; margin-top: 2px; }
        .contract-fact-value.price { color: #16794f; font-size: 22px; }
        .contract-scope-box { background: #f9fafb; border-radius: 8px; padding: 14px; font-size: 14px; color: #374151; white-space: pre-line; }

        .deliverable-box { background: #f9fafb; border: 1px solid #eef1f5; border-radius: 8px; padding: 14px; margin-top: 14px; }
        .final-verdict-box { background: #f9fbfd; border: 1px solid #e5edf6; border-radius: 8px; padding: 16px; margin-top: 16px; }
        .final-verdict-form { display: grid; grid-template-columns: minmax(180px, 260px) 1fr auto; gap: 12px; align-items: end; }
        .final-verdict-reason { display: none; }
        .final-verdict-reason.is-visible { display: block; }
        /* Pin the submit button to the 3rd (auto-width) column explicitly — when the
           middle reason field is hidden (the common case), CSS Grid's auto-placement
           would otherwise slot the button into the wide 1fr column meant for that
           field, stretching it across the row. */
        .final-verdict-form button[type="submit"] { grid-column: 3; }
        .final-verdict-help { color: #6b7280; font-size: 12px; margin-top: 8px; }
        @media (max-width: 767.98px) {
            .final-verdict-form { grid-template-columns: 1fr; }
            .final-verdict-form button[type="submit"] { grid-column: 1; }
        }
        .deliverable-box-label { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .03em; margin-bottom: 6px; }
        .deliverable-empty { color: #9ca3af; font-size: 13px; font-style: italic; }
        .waiting-note { display: flex; align-items: center; gap: 8px; color: #b76e00; background: #fff8ec; border-radius: 8px; padding: 10px 14px; font-size: 13px; font-weight: 600; margin-top: 14px; }
        .message-thread { max-height: 380px; overflow-y: auto; padding: 4px; margin-bottom: 14px; }
        .message-bubble { max-width: 75%; padding: 10px 14px; border-radius: 10px; margin-bottom: 10px; }
        .message-bubble.mine { background: #1a73e8; color: #fff; margin-left: auto; }
        .message-bubble.theirs { background: #f1f3f5; color: #1f2937; }
        .message-meta { font-size: 11px; opacity: .7; margin-top: 4px; }
        .attachment-link { font-size: 12px; display: block; }
    </style>
@endpush

@section('content')
    <main class="contract-page">
        <div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
            <div class="row">
                @include('web-views.partials._profile-aside')
                <section class="col-lg-9 __customer-profile px-0">
            @php($freelancerName = $contract->freelancer->shop?->name ?? trim($contract->freelancer->f_name . ' ' . $contract->freelancer->l_name))
            <div class="contract-detail-header">
                <img class="contract-detail-avatar" alt="" src="{{ getStorageImages(path: $contract->freelancer->image_full_url, type: 'backend-profile') }}">
                <div>
                    <div class="contract-detail-title">{{ $contract->service?->title }}</div>
                    <div class="contract-detail-meta">{{ translate('with') }} {{ $freelancerName }} &middot; {{ $contract->created_at->format('d M, Y') }}</div>
                </div>
                <span class="status-pill">{{ str_replace('_', ' ', $contract->status) }}</span>
            </div>

            <div class="contract-section">
                <div class="contract-section-title">{{ translate('contract_summary') }}</div>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="contract-fact-label">{{ translate('total_amount') }}</div>
                        <div class="contract-fact-value price">{{ webCurrencyConverter($contract->total_amount) }}</div>
                    </div>
                    <div class="col-12">
                        <div class="contract-fact-label mb-2">{{ translate('scope') }}</div>
                        <div class="contract-scope-box">{{ $contract->scope }}</div>
                    </div>
                </div>
            </div>

            <div class="contract-section">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div class="contract-section-title mb-0">{{ translate('freelancer_details') }}</div>
                    <a href="{{ route('hire-freelancer.show', $contract->seller_id) }}" class="btn btn-outline-secondary btn-sm">
                        {{ translate('view_profile') }}
                    </a>
                </div>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="contract-fact-label">{{ translate('name') }}</div>
                        <div class="contract-fact-value">{{ $freelancerName }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="contract-fact-label">{{ translate('level') }}</div>
                        <div class="contract-fact-value">{{ $contract->freelancer->freelancerLevelLabel() }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="contract-fact-label">{{ translate('rating') }}</div>
                        <div class="contract-fact-value">
                            @if((int) $contract->freelancer->freelancer_rating_count > 0)
                                ★ {{ round((float) $contract->freelancer->freelancer_rating_avg, 1) }} ({{ $contract->freelancer->freelancer_rating_count }})
                            @else
                                {{ translate('no_reviews_yet') }}
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="contract-fact-label">{{ translate('email') }}</div>
                        <div class="contract-fact-value">
                            @if($contract->freelancer->email)
                                <a href="mailto:{{ $contract->freelancer->email }}">{{ $contract->freelancer->email }}</a>
                            @else
                                &mdash;
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="contract-fact-label">{{ translate('phone') }}</div>
                        <div class="contract-fact-value">{{ $contract->freelancer->phone ?: '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="contract-fact-label">{{ translate('jobs_completed') }}</div>
                        <div class="contract-fact-value">{{ (int) $contract->freelancer->freelancer_jobs_completed }}</div>
                    </div>
                </div>
            </div>

            @php($delivery = $contract->milestones->first())

            <div class="contract-section">
                <div class="contract-section-title">{{ translate('delivery') }}</div>

                <div style="max-width: 260px;" class="mb-3">
                    <label class="form-label fw-semibold">{{ translate('delivery_status') }}</label>
                    <select class="form-control" disabled>
                        <option selected>{{ translate(str_replace('_', ' ', $contract->delivery_status->value)) }}</option>
                    </select>
                </div>

                @if($delivery)
                    @php($deliveryStatus = $delivery->status)

                    @forelse($delivery->deliverables as $deliverable)
                        <div class="deliverable-box">
                            <div class="deliverable-box-label">{{ translate('submitted_by_freelancer') }}</div>
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
                        {{-- The freelancer can mark the overall contract "Delivered" directly,
                             skipping the milestone deliverable upload entirely (see
                             DeliveryStatus::allowedNextStates()) — don't tell the customer
                             nothing was submitted when the contract's own status already says
                             otherwise; the rest of this page (e.g. the verdict box below)
                             trusts delivery_status as authoritative too. --}}
                        @if($contract->delivery_status->value !== 'delivered')
                            <p class="deliverable-empty mb-0">{{ translate('the_freelancer_has_not_submitted_the_delivery_yet') }}</p>
                        @endif
                    @endforelse

                    @if($deliveryStatus === 'submitted')
                        <form action="{{ route('hire.contracts.milestones.approve', [$contract->id, $delivery->id]) }}" method="POST" class="mt-3" novalidate>
                            @csrf
                            <button type="submit" class="btn btn--primary btn-sm">{{ translate('approve_delivery') }}</button>
                        </form>
                    @elseif($deliveryStatus === 'completed')
                        <div class="waiting-note" style="color: #16794f; background: #eefaf3;">
                            <i class="tio-verified"></i> {{ translate('you_approved_this_delivery') }}
                        </div>
                    @endif
                @endif

                @if($contract->delivery_status->value === 'delivered' && $contract->verdict_status->value === 'pending')
                    <div class="final-verdict-box">
                        <div class="contract-section-title mb-2">{{ translate('final_verdict') }}</div>
                        <form action="{{ route('hire.contracts.verdict.submit', $contract->id) }}" method="POST" class="final-verdict-form" id="final-verdict-form-{{ $contract->id }}" novalidate>
                            @csrf
                            <div class="form-group mb-0">
                                <label class="form-label fw-semibold" for="final-verdict-select-{{ $contract->id }}">{{ translate('select_decision') }}</label>
                                <select name="verdict" id="final-verdict-select-{{ $contract->id }}" class="form-control final-verdict-select" required>
                                    <option value="">{{ translate('select') }}</option>
                                    <option value="approved" {{ old('verdict') === 'approved' ? 'selected' : '' }}>{{ translate('approved') }}</option>
                                    <option value="rejected" {{ old('verdict') === 'rejected' ? 'selected' : '' }}>{{ translate('reject') }}</option>
                                    <option value="revision" {{ old('verdict') === 'revision' ? 'selected' : '' }}>{{ translate('request_for_revision') }}</option>
                                </select>
                            </div>
                            <div class="form-group mb-0 final-verdict-reason {{ in_array(old('verdict'), ['rejected', 'revision'], true) ? 'is-visible' : '' }}">
                                <label class="form-label fw-semibold" for="final-verdict-reason-{{ $contract->id }}">{{ translate('feedback_for_freelancer') }} *</label>
                                <textarea name="rejection_reason" id="final-verdict-reason-{{ $contract->id }}" class="form-control" rows="3" minlength="5" maxlength="2500" {{ in_array(old('verdict'), ['rejected', 'revision'], true) ? 'required' : '' }}>{{ old('rejection_reason') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn--primary btn-sm">{{ translate('submit_verdict') }}</button>
                        </form>
                        <div class="final-verdict-help">{{ translate('after_approval_the_contract_will_be_completed_reject_or_revision_sends_feedback_to_the_freelancer') }}</div>
                    </div>
                @endif

                @if($contract->rejection_reason && $contract->verdict_status->value !== 'rejected')
                    <div class="alert alert-warning mt-3 mb-0">
                        <strong>{{ translate('your_last_revision_request') }}:</strong> {{ $contract->rejection_reason }}
                    </div>
                @endif
            </div>

            @if(in_array($contract->status, ['active', 'submitted'], true))
                <div class="contract-section">
                    <div class="contract-section-title">{{ translate('cancel_this_contract') }}</div>

                    @if($contract->cancellation_status === 'requested')
                        <div class="alert alert-warning mb-0">
                            {{ translate('your_cancellation_request_is_awaiting_admin_review') }}
                        </div>
                    @else
                        @if($contract->cancellation_status === 'denied')
                            <div class="alert alert-secondary">
                                {{ translate('your_previous_cancellation_request_was_denied') }}
                                @if($contract->cancellation_admin_note)
                                    &mdash; {{ $contract->cancellation_admin_note }}
                                @endif
                            </div>
                        @endif
                        <button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#request-cancellation-{{ $contract->id }}">
                            {{ translate('request_cancellation') }}
                        </button>

                        <div class="modal fade" id="request-cancellation-{{ $contract->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('hire.contracts.request-cancellation', $contract->id) }}" method="POST" novalidate>
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ translate('request_cancellation') }}</h5>
                                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="text-muted small">{{ translate('an_admin_will_review_this_request_before_the_contract_is_cancelled') }}</p>
                                            <label class="form-label">{{ translate('reason') }} *</label>
                                            <textarea name="reason" class="form-control" rows="4" minlength="5" maxlength="1000" required></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ translate('close') }}</button>
                                            <button type="submit" class="btn btn-danger">{{ translate('submit_request') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="contract-section">
                <div class="contract-section-title">{{ translate('messages') }}</div>

                <div class="message-thread" id="message-thread" data-last-id="{{ $contract->messages->max('id') ?? 0 }}">
                    @foreach($contract->messages as $message)
                        <div class="message-bubble {{ $message->sender_type === 'customer' ? 'mine' : 'theirs' }}">
                            @if($message->body)
                                <div>{{ $message->body }}</div>
                            @endif
                            @foreach($message->attachments as $attachment)
                                <a class="attachment-link" style="color: inherit;" href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('freelancer-contract-attachments.message', now()->addMinutes(30), ['attachment' => $attachment->id]) }}">
                                    &#128206; {{ $attachment->original_name }}
                                </a>
                            @endforeach
                            <div class="message-meta">{{ $message->created_at->format('d M, H:i') }}</div>
                        </div>
                    @endforeach
                </div>

                <form action="{{ route('hire.contracts.messages.store', $contract->id) }}" method="POST" enctype="multipart/form-data" id="message-form" novalidate>
                    @csrf
                    <div class="form-group">
                        <textarea name="body" class="form-control" rows="2" placeholder="{{ translate('Write_here') }}..."></textarea>
                    </div>
                    <div class="form-group">
                        <input type="file" name="attachments[]" multiple class="form-control-file">
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn--primary btn-sm">{{ translate('send') }}</button>
                    </div>
                </form>
            </div>

            @if($contract->status === 'completed')
                <div class="contract-section">
                    <div class="contract-section-title">{{ translate('review') }}</div>

                    @php($myReview = $contract->reviews->firstWhere('reviewer_type', 'customer'))

                    @if($myReview)
                        <div>{{ str_repeat('★', $myReview->rating) . str_repeat('☆', 5 - $myReview->rating) }}</div>
                        <div class="text-muted">{{ $myReview->body }}</div>
                    @else
                        <form action="{{ route('hire.contracts.review', $contract->id) }}" method="POST" novalidate>
                            @csrf
                            <div class="form-group">
                                <label>{{ translate('rating') }}</label>
                                <select name="rating" class="form-control" style="max-width: 160px;" required>
                                    <option value="5">5 - {{ translate('excellent') }}</option>
                                    <option value="4">4</option>
                                    <option value="3">3</option>
                                    <option value="2">2</option>
                                    <option value="1">1</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <textarea name="body" class="form-control" rows="3" placeholder="{{ translate('share_your_experience') }}"></textarea>
                            </div>
                            <button type="submit" class="btn btn--primary btn-sm">{{ translate('submit_review') }}</button>
                        </form>
                    @endif
                </div>
            @endif
                </section>
            </div>
        </div>
    </main>
@endsection

@push('script')
    <script>
        (function () {
            'use strict';
            var thread = document.getElementById('message-thread');
            var pollUrl = @json(route('hire.contracts.messages', $contract->id));

            function escapeHtml(value) {
                var div = document.createElement('div');
                div.textContent = value || '';
                return div.innerHTML;
            }

            function poll() {
                var lastId = thread.getAttribute('data-last-id') || 0;
                fetch(pollUrl + '?after_id=' + lastId)
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        (data.messages || []).forEach(function (message) {
                            var bubble = document.createElement('div');
                            bubble.className = 'message-bubble ' + (message.sender_type === 'customer' ? 'mine' : 'theirs');

                            var html = '';
                            if (message.body) {
                                html += '<div>' + escapeHtml(message.body) + '</div>';
                            }
                            (message.attachments || []).forEach(function (attachment) {
                                html += '<div class="attachment-link">&#128206; ' + escapeHtml(attachment.original_name) + '</div>';
                            });
                            html += '<div class="message-meta">' + escapeHtml(message.created_at) + '</div>';

                            bubble.innerHTML = html;
                            thread.appendChild(bubble);
                            thread.setAttribute('data-last-id', message.id);
                        });
                        thread.scrollTop = thread.scrollHeight;
                    })
                    .catch(function () {});
            }

            document.querySelectorAll('.final-verdict-select').forEach(function (select) {
                function toggleReason() {
                    var form = select.closest('form');
                    var reasonWrap = form ? form.querySelector('.final-verdict-reason') : null;
                    var reasonField = reasonWrap ? reasonWrap.querySelector('textarea') : null;
                    var needsReason = select.value === 'rejected' || select.value === 'revision';

                    if (reasonWrap) {
                        reasonWrap.classList.toggle('is-visible', needsReason);
                    }
                    if (reasonField) {
                        reasonField.required = needsReason;
                        if (!needsReason) {
                            reasonField.value = '';
                        }
                    }
                }

                select.addEventListener('change', toggleReason);
                toggleReason();
            });
            setInterval(poll, 6000);
        })();
    </script>
@endpush
