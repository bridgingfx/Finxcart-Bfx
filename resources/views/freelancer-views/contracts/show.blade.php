@extends('layouts.freelancer.app')

@section('title', translate('contract') . ' #' . $contract->id)

@push('css_or_js')
    <style>
        .contract-detail-header {
            background: linear-gradient(135deg, #17395e 0%, #1f4a78 100%); border-radius: 10px; padding: 22px 24px; margin-bottom: 18px;
            display: flex; align-items: center; gap: 16px; flex-wrap: wrap; color: #fff;
        }
        .contract-detail-avatar {
            width: 52px; height: 52px; border-radius: 50%; object-fit: cover; background: rgba(255,255,255,.15);
            flex-shrink: 0; border: 2px solid rgba(255,255,255,.4);
        }
        .contract-detail-title { font-weight: 800; font-size: 17px; color: #fff; }
        .contract-detail-meta { font-size: 13px; color: rgba(255,255,255,.75); margin-top: 2px; }
        .contract-status-pill {
            display: inline-block; padding: 5px 14px; border-radius: 999px; font-size: 12px; font-weight: 700;
            margin-left: auto; text-transform: capitalize; background: rgba(255,255,255,.18); color: #fff;
        }

        .contract-section { background: #fff; border: 1px solid #e8edf3; border-radius: 10px; padding: 22px 24px; margin-bottom: 18px; }
        .contract-section-title { font-weight: 800; font-size: 15px; color: #17395e; margin-bottom: 14px; }
        .contract-fact-label { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .03em; }
        .contract-fact-value { font-size: 14px; font-weight: 700; color: #1f2937; margin-top: 2px; }
        .contract-fact-value.price { color: #16794f; font-size: 20px; }
        .contract-scope-box { background: #f9fafb; border-radius: 8px; padding: 14px; font-size: 14px; color: #374151; white-space: pre-line; }

        .deliverable-box { background: #f9fafb; border: 1px solid #eef1f5; border-radius: 8px; padding: 14px; margin-top: 10px; }
        .deliverable-empty { color: #9ca3af; font-size: 13px; font-style: italic; }

        .delivery-stage-tracker { display: flex; align-items: flex-start; }
        .delivery-stage { flex: 1; text-align: center; position: relative; }
        .delivery-stage:not(:last-child)::after {
            content: ''; position: absolute; top: 15px; left: 50%; width: 100%; height: 2px; background: #e5e9f0; z-index: 0;
        }
        .delivery-stage.is-done:not(:last-child)::after { background: #16a34a; }
        .delivery-stage-dot {
            display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 50%;
            background: #f1f3f6; color: #9ca3af; font-weight: 700; font-size: 13px; margin: 0 auto 8px; position: relative; z-index: 1;
            border: 2px solid #f1f3f6;
        }
        .delivery-stage.is-done .delivery-stage-dot { background: #16a34a; border-color: #16a34a; color: #fff; }
        .delivery-stage.is-current .delivery-stage-dot { background: #fff; border-color: #1a2f5e; color: #1a2f5e; }
        .delivery-stage-label { font-size: 11.5px; font-weight: 700; color: #9ca3af; text-transform: capitalize; }
        .delivery-stage.is-done .delivery-stage-label { color: #16a34a; }
        .delivery-stage.is-current .delivery-stage-label { color: #1a2f5e; }
        .delivery-status-note {
            background: #f6f8fb; border: 1px solid #e8edf3; border-radius: 8px; padding: 12px 14px;
            font-size: 13px; color: #4b5563; font-weight: 600;
        }
        .delivery-update-bar {
            background: #f6f8fb; border: 1px solid #e8edf3; border-radius: 10px;
            padding: 16px 18px; display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap;
        }
        .delivery-update-field { display: flex; flex-direction: column; gap: 6px; }
        .delivery-update-label {
            font-size: 11.5px; font-weight: 700; color: #6b7280; text-transform: uppercase;
            letter-spacing: .03em; margin: 0;
        }
        .delivery-update-select {
            min-width: 240px; height: 42px; border-radius: 8px; border: 1px solid #d7dee8;
            font-weight: 600; color: #1f2937; padding: 0 12px;
        }
        .delivery-update-select:focus {
            border-color: #1a2f5e; box-shadow: 0 0 0 3px rgba(26, 47, 94, .12); outline: none;
        }
        .delivery-update-btn {
            height: 42px; padding: 0 24px; border-radius: 8px; font-weight: 700;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; white-space: nowrap;
        }
        .delivery-update-btn:disabled { opacity: .7; cursor: not-allowed; }
        .delivery-update-btn .spinner-border { width: 14px; height: 14px; border-width: 2px; }
        .message-thread { max-height: 380px; overflow-y: auto; padding: 4px; margin-bottom: 14px; }
        .message-bubble { max-width: 75%; padding: 10px 14px; border-radius: 10px; margin-bottom: 10px; }
        .message-bubble.mine { background: #1a73e8; color: #fff; margin-left: auto; }
        .message-bubble.theirs { background: #f1f3f5; color: #1f2937; }
        .message-meta { font-size: 11px; opacity: .7; margin-top: 4px; }
        .attachment-link { font-size: 12px; display: block; }
        .contract-review-stars { font-size: 15px; display: inline-flex; gap: 2px; margin-bottom: 6px; }
        .contract-review-stars i.is-filled { color: #f5a623; }
        .contract-review-stars i.is-empty { color: #e2e6ec; }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0">{{ translate('contract') }} #{{ $contract->id }}</h2>
            <a href="{{ route('freelancer.contracts.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="tio-chevron-left"></i> {{ translate('back') }}
            </a>
        </div>

        @php($customerName = trim(($contract->customer?->f_name ?? '') . ' ' . ($contract->customer?->l_name ?? '')) ?: translate('customer'))
        <div class="contract-detail-header">
            <img class="contract-detail-avatar" alt="" src="{{ getStorageImages(path: $contract->customer?->image_full_url, type: 'backend-profile') }}">
            <div>
                @if($contract->service)
                    <a href="{{ route('freelancer.services.view', $contract->service->id) }}" class="contract-detail-title text-decoration-none">
                        {{ $contract->service->title }}
                    </a>
                @else
                    <div class="contract-detail-title">-</div>
                @endif
                <div class="contract-detail-meta">{{ translate('client') }}: {{ $customerName }} &middot; {{ $contract->created_at->format('d M, Y') }}</div>
            </div>
            <span class="contract-status-pill">{{ str_replace('_', ' ', $contract->status) }}</span>
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
            <div class="contract-section-title">{{ translate('client_details') }}</div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="contract-fact-label">{{ translate('name') }}</div>
                    <div class="contract-fact-value">{{ $customerName }}</div>
                </div>
                <div class="col-md-4">
                    <div class="contract-fact-label">{{ translate('email') }}</div>
                    <div class="contract-fact-value">
                        @if($contract->customer?->email)
                            <a href="mailto:{{ $contract->customer->email }}">{{ $contract->customer->email }}</a>
                        @else
                            &mdash;
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="contract-fact-label">{{ translate('phone') }}</div>
                    <div class="contract-fact-value">{{ $contract->customer?->phone ?: '—' }}</div>
                </div>
                @if($contract->customer?->city || $contract->customer?->country)
                    <div class="col-md-4">
                        <div class="contract-fact-label">{{ translate('location') }}</div>
                        <div class="contract-fact-value">{{ trim(($contract->customer->city ?? '') . ', ' . ($contract->customer->country ?? ''), ', ') }}</div>
                    </div>
                @endif
                <div class="col-md-4">
                    <div class="contract-fact-label">{{ translate('customer_since') }}</div>
                    <div class="contract-fact-value">{{ $contract->customer?->created_at?->format('d M, Y') ?? '—' }}</div>
                </div>
            </div>
        </div>

        @php($quoteAttachments = $contract->quoteRequests->flatMap(fn($quote) => $quote->attachment_full_url))
        @if($quoteAttachments->isNotEmpty())
            <div class="contract-section">
                <div class="contract-section-title">{{ translate('reference_materials_from_customer') }}</div>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($quoteAttachments as $file)
                        @php($isImage = in_array(strtolower(pathinfo($file['key'] ?? '', PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                        <a href="{{ $file['path'] ?? '#' }}" target="_blank" class="text-center">
                            @if($isImage)
                                <img src="{{ $file['path'] }}" alt="{{ $file['key'] ?? '' }}"
                                     class="rounded border object-fit-cover" style="width: 96px; height: 96px;">
                            @else
                                <div class="d-flex align-items-center justify-content-center rounded border bg-light"
                                     style="width: 96px; height: 96px;">
                                    <i class="tio-attachment fs-2"></i>
                                </div>
                            @endif
                            <div class="fs-11 text-truncate mt-1" style="max-width: 96px;">{{ $file['key'] ?? translate('attachment') }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @php($delivery = $contract->milestones->first())

        <div class="contract-section">
            <div class="contract-section-title">{{ translate('delivery') }}</div>

            <div id="deliveryStatusPanel">
                @include('freelancer-views.contracts._delivery-status-panel')
            </div>

            <div id="deliverySubmissionPanel">
                @include('freelancer-views.contracts._delivery-submission-panel')
            </div>
        </div>

        <div class="contract-section">
            <div class="contract-section-title">{{ translate('messages') }}</div>

            <div class="message-thread" id="message-thread" data-last-id="{{ $contract->messages->max('id') ?? 0 }}">
                @foreach($contract->messages as $message)
                    <div class="message-bubble {{ $message->sender_type === 'seller' ? 'mine' : 'theirs' }}">
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

            <form action="{{ route('freelancer.contracts.messages.store', $contract->id) }}" method="POST"
                  enctype="multipart/form-data" id="sendMessageForm" novalidate>
                @csrf
                <div class="form-group">
                    <textarea name="body" class="form-control" rows="2" placeholder="{{ translate('Write_here') }}..."></textarea>
                </div>
                <div class="form-group">
                    <input type="file" name="attachments[]" multiple class="form-control-file">
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn--primary btn-sm" id="sendMessageBtn">{{ translate('send') }}</button>
                </div>
            </form>
        </div>

        @if($contract->status === 'completed')
            <div class="contract-section">
                <div class="contract-section-title">{{ translate('review') }}</div>

                @php($myReview = $contract->reviews->firstWhere('reviewer_type', 'seller'))

                @if($myReview)
                    <div class="contract-review-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="tio-star {{ $i <= $myReview->rating ? 'is-filled' : 'is-empty' }}"></i>
                        @endfor
                    </div>
                    <div class="text-muted">{{ $myReview->body }}</div>
                @else
                    <form action="{{ route('freelancer.contracts.review', $contract->id) }}" method="POST" novalidate>
                        @csrf
                        <div class="form-group">
                            <label>{{ translate('rating') }}</label>
                            <select name="rating" class="form-control" style="max-width: 160px;" required>
                                <option value="5">5</option>
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
    </div>
@endsection

@push('script')
    <script>
        (function () {
            'use strict';
            var thread = document.getElementById('message-thread');
            var pollUrl = @json(route('freelancer.contracts.messages', $contract->id));

            function escapeHtml(value) {
                var div = document.createElement('div');
                div.textContent = value || '';
                return div.innerHTML;
            }

            function appendMessageBubble(message) {
                var bubble = document.createElement('div');
                bubble.className = 'message-bubble ' + (message.sender_type === 'seller' ? 'mine' : 'theirs');

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
                thread.scrollTop = thread.scrollHeight;
            }

            function poll() {
                var lastId = thread.getAttribute('data-last-id') || 0;
                fetch(pollUrl + '?after_id=' + lastId)
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        (data.messages || []).forEach(appendMessageBubble);
                    })
                    .catch(function () {});
            }

            setInterval(poll, 6000);

            // All forms on this page submit via AJAX so the page never fully reloads.
            // Event delegation is used for panels whose form gets replaced after each submit.
            function ajaxSubmitAndSwap(panel, formId, btnId, busyLabel, onSuccess) {
                panel.addEventListener('submit', function (e) {
                    var form = e.target.closest('#' + formId);
                    if (!form || !panel.contains(form)) {
                        return;
                    }
                    e.preventDefault();

                    if (typeof FormValidators !== 'undefined' && !FormValidators.autoValidateForm('#' + formId)) {
                        return;
                    }

                    var btn = document.getElementById(btnId);
                    if (!btn || btn.disabled) {
                        return;
                    }
                    var originalBtnHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border" role="status" aria-hidden="true"></span>'
                        + '<span class="btn-label">' + busyLabel + '...</span>';

                    fetch(form.getAttribute('action'), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                    })
                        .then(function (response) {
                            return response.json().then(function (data) {
                                return { ok: response.ok, data: data };
                            });
                        })
                        .then(function (result) {
                            if (result.ok && result.data.success) {
                                onSuccess(result.data);
                                if (window.toastMagic) {
                                    toastMagic.success(result.data.message);
                                }
                            } else {
                                if (window.toastMagic) {
                                    toastMagic.error(result.data.message || 'Something went wrong.');
                                }
                                btn.disabled = false;
                                btn.innerHTML = originalBtnHtml;
                            }
                        })
                        .catch(function () {
                            if (window.toastMagic) {
                                toastMagic.error('Something went wrong. Please try again.');
                            }
                            btn.disabled = false;
                            btn.innerHTML = originalBtnHtml;
                        });
                });
            }

            var deliveryStatusPanel = document.getElementById('deliveryStatusPanel');
            if (deliveryStatusPanel) {
                ajaxSubmitAndSwap(deliveryStatusPanel, 'deliveryStatusForm', 'deliveryStatusSubmitBtn', '{{ translate('updating') }}', function (data) {
                    deliveryStatusPanel.innerHTML = data.html;
                });
            }

            var deliverySubmissionPanel = document.getElementById('deliverySubmissionPanel');
            if (deliverySubmissionPanel) {
                ajaxSubmitAndSwap(deliverySubmissionPanel, 'deliverySubmitForm', 'deliverySubmitBtn', '{{ translate('submitting') }}', function (data) {
                    deliverySubmissionPanel.innerHTML = data.html;
                });
            }

            var sendMessageForm = document.getElementById('sendMessageForm');
            if (sendMessageForm) {
                sendMessageForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    var btn = document.getElementById('sendMessageBtn');
                    if (!btn || btn.disabled) {
                        return;
                    }
                    var originalBtnHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '{{ translate('sending') }}...';

                    fetch(sendMessageForm.getAttribute('action'), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(sendMessageForm),
                    })
                        .then(function (response) {
                            return response.json().then(function (data) {
                                return { ok: response.ok, data: data };
                            });
                        })
                        .then(function (result) {
                            if (result.ok && result.data.success) {
                                appendMessageBubble(result.data.data);
                                sendMessageForm.reset();
                            } else if (window.toastMagic) {
                                toastMagic.error((result.data && result.data.message) || 'Something went wrong.');
                            }
                        })
                        .catch(function () {
                            if (window.toastMagic) {
                                toastMagic.error('Something went wrong. Please try again.');
                            }
                        })
                        .finally(function () {
                            btn.disabled = false;
                            btn.innerHTML = originalBtnHtml;
                        });
                });
            }
        })();
    </script>
@endpush
