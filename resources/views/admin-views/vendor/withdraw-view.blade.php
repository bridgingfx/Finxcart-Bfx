@extends('layouts.admin.app')

@section('title', translate('Withdraw_Request_Details'))

@section('content')
<div class="content container-fluid">
    <div class="mb-3 d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/withdraw-icon.png')}}" alt="">
            {{ translate('Withdraw_Request_Details') }}
        </h2>
        <a href="{{ route('admin.vendors.withdraw_list') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fi fi-rr-arrow-left me-1"></i> {{ translate('back_to_list') }}
        </a>
    </div>

    @php
        $ds = $withdrawRequest->delivery_status ?? 'pending_transfer';
        $dsColors = ['pending_transfer'=>'secondary','in_transfer'=>'warning','transferred'=>'success','failed'=>'danger'];
        $dsLabels = ['pending_transfer'=>'Pending Transfer','in_transfer'=>'In Transfer','transferred'=>'Transferred','failed'=>'Failed'];
        $dsColor = $dsColors[$ds] ?? 'secondary';
        $dsLabel = $dsLabels[$ds] ?? ucwords(str_replace('_',' ',$ds));
    @endphp

    <div class="row g-3">

        {{-- ===== LEFT COLUMN ===== --}}
        <div class="col-lg-8">

            {{-- Summary card --}}
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">{{ translate('request_summary') }}</h5>
                    @if($withdrawRequest->approved == 0)
                        <span class="badge badge-soft--primary px-3 py-2">{{ translate('pending') }}</span>
                    @elseif($withdrawRequest->approved == 1)
                        <span class="badge badge-soft-success px-3 py-2">{{ translate('approved') }}</span>
                    @else
                        <span class="badge badge-soft-danger px-3 py-2">{{ translate('denied') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-4 text-center">
                            <div class="p-3 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                <p class="mb-1 fs-12 text-secondary">{{ translate('amount') }}</p>
                                <h3 class="mb-0 text-success fw-bold">
                                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $withdrawRequest->amount), currencyCode: getCurrencyCode(type: 'default')) }}
                                </h3>
                            </div>
                        </div>
                        <div class="col-sm-4 text-center">
                            <div class="p-3 rounded bg-light border">
                                <p class="mb-1 fs-12 text-secondary">{{ translate('request_date') }}</p>
                                <p class="mb-0 fw-semibold">{{ date('d M Y', strtotime($withdrawRequest->created_at)) }}</p>
                                <small class="text-muted">{{ date('h:i A', strtotime($withdrawRequest->created_at)) }}</small>
                            </div>
                        </div>
                        <div class="col-sm-4 text-center">
                            <div class="p-3 rounded bg-light border">
                                <p class="mb-1 fs-12 text-secondary">{{ translate('delivery_status') }}</p>
                                @if($withdrawRequest->approved == 1)
                                    <span class="badge badge-soft-{{ $dsColor }} px-3 py-1 text-capitalize">{{ $dsLabel }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($withdrawRequest->transaction_note)
                    <div class="mt-3 p-3 rounded" style="background:#fffbeb;border:1px solid #fde68a;">
                        <p class="mb-1 fs-12 fw-semibold" style="color:#92400e;">{{ translate('admin_note') }}</p>
                        <p class="mb-0 text-dark">{{ $withdrawRequest->transaction_note }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ===== REQUEST DETAILS ===== --}}
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="fi fi-rr-document fs-5 text-primary"></i>
                    <h5 class="mb-0">{{ translate('Request_Details') }}</h5>
                </div>
                <div class="card-body">
                    @if($withdrawalMethod)
                        @php
                            if (is_array($withdrawalMethod)) {
                                $wm = $withdrawalMethod;
                            } elseif (is_object($withdrawalMethod)) {
                                $wm = (array) $withdrawalMethod;
                            } else {
                                $wm = json_decode($withdrawalMethod, true) ?: [];
                            }
                        @endphp
                        <h6 class="text-muted mb-3">{{ $wm['method_name'] ?? translate('Withdrawal_Method') }}</h6>
                        <div class="row g-2">
                            @foreach($wm as $key => $val)
                                @if($key !== 'method_name')
                                @php
                                    if (is_array($val)) {
                                        $fieldValue = implode(', ', array_map(function ($item) {
                                            return is_scalar($item) ? $item : json_encode($item);
                                        }, $val));
                                    } else {
                                        $fieldValue = $val;
                                    }
                                @endphp
                                <div class="col-sm-6">
                                    <div class="p-3 rounded bg-light border">
                                        <p class="mb-1 fs-11 fw-semibold text-secondary text-uppercase">{{ str_replace('_',' ',$key) }}</p>
                                        @php
                                            $isImage = false; $filePath = null;
                                            if (is_string($fieldValue) && $fieldValue) {
                                                $ext = strtolower(pathinfo($fieldValue, PATHINFO_EXTENSION));
                                                $isImageExt = in_array($ext, ['jpg','jpeg','png','gif','webp','svg']);
                                                if (str_starts_with($fieldValue, 'withdraw_files/')) {
                                                    $filePath = $fieldValue;
                                                    $isImage = $isImageExt;
                                                } elseif ($isImageExt || str_starts_with($fieldValue, 'public/') || str_starts_with($fieldValue, 'storage/')) {
                                                    $filePath = $isImageExt ? 'withdraw_files/' . $fieldValue : $fieldValue;
                                                    $isImage = $isImageExt;
                                                }
                                            }
                                        @endphp
                                        @if($filePath)
                                            @if($isImage)
                                                <a href="{{ Storage::url($filePath) }}" target="_blank">
                                                    <img src="{{ Storage::url($filePath) }}" alt="uploaded file" style="max-width:180px;max-height:180px;border-radius:4px;">
                                                </a>
                                            @else
                                                <a href="{{ Storage::url($filePath) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                                    <i class="fi fi-rr-download me-1"></i>{{ translate('download_file') }}
                                                </a>
                                            @endif
                                        @else
                                            <p class="mb-0 fw-semibold text-dark" style="word-break:break-all;">{{ $fieldValue }}</p>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info mb-0">{{ translate('No_method_details_were_submitted_with_this_request') }}</div>
                    @endif
                </div>
            </div>

            {{-- ===== VENDOR BANK ACCOUNT DETAILS ===== --}}
            @if($withdrawRequest->seller)
                @php
                    $bankFields = [
                        'holder_name'  => translate('account_holder_name'),
                        'bank_name'    => translate('bank_name'),
                        'account_no'   => translate('account_number'),
                        'account_type' => translate('account_type'),
                        'branch'       => translate('branch'),
                        'branch_code'  => translate('branch_code'),
                        'swift_code'   => translate('swift_code'),
                        'iban'         => translate('iban'),
                        'bank_country' => translate('bank_country'),
                        'bank_address' => translate('bank_address'),
                    ];
                    $bankValues = array_filter($bankFields, fn($label, $field) => filled($withdrawRequest->seller->{$field}), ARRAY_FILTER_USE_BOTH);
                @endphp
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="fi fi-rr-document fs-5 text-primary"></i>
                        <h5 class="mb-0">{{ translate('vendor_bank_account_details') }}</h5>
                    </div>
                    <div class="card-body">
                        @if(count($bankValues))
                            <div class="row g-2">
                                @foreach($bankValues as $field => $label)
                                    <div class="col-sm-6">
                                        <div class="p-3 rounded bg-light border">
                                            <p class="mb-1 fs-11 fw-semibold text-secondary text-uppercase">{{ $label }}</p>
                                            <p class="mb-0 fw-semibold text-dark" style="word-break:break-all;">{{ $withdrawRequest->seller->{$field} }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info mb-0">{{ translate('vendor_has_not_submitted_bank_account_details_yet') }}</div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ===== DELIVERY STATUS UPDATE (approved only) ===== --}}
            @if($withdrawRequest->approved == 1)
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="fi fi-rr-truck-side fs-5 text-warning"></i>
                    <h5 class="mb-0">{{ translate('money_delivery_status') }}</h5>
                </div>
                <div class="card-body">
                    {{-- Delivery status stepper --}}
                    <div class="d-flex align-items-center gap-0 mb-4 overflow-auto">
                        @foreach(['pending_transfer'=>'Pending Transfer','in_transfer'=>'In Transfer','transferred'=>'Transferred','failed'=>'Failed'] as $step => $label)
                            @php
                                $stepColors = ['pending_transfer'=>'secondary','in_transfer'=>'warning','transferred'=>'success','failed'=>'danger'];
                                $isActive = $ds === $step;
                            @endphp
                            <div class="text-center px-2" style="min-width:100px;">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1 {{ $isActive ? 'bg-'.$stepColors[$step].' text-white' : 'bg-light border' }}"
                                     style="width:36px;height:36px;font-size:13px;">
                                    @php($stepIcons = ['transferred' => '&#10003;', 'failed' => '&#10005;', 'in_transfer' => '&#8635;'])
                                    {!! $stepIcons[$step] ?? '&#9675;' !!}
                                </div>
                                {{--
                                    @if($step === 'transferred') ✓
                                    @elseif($step === 'failed') ✕
                                    @elseif($step === 'in_transfer') ⟳
                                    @else ○ @endif
                                </div>
                                --}}
                                <div class="fs-11 {{ $isActive ? 'fw-bold text-'.$stepColors[$step] : 'text-muted' }}">{{ $label }}</div>
                            </div>
                            @if($step !== 'failed')
                            <div class="flex-grow-1 border-top" style="height:1px;min-width:20px;margin-bottom:18px;"></div>
                            @endif
                        @endforeach
                    </div>

                    <form action="{{ route('admin.vendors.withdraw_delivery_status', $withdrawRequest->id) }}" method="POST" novalidate>
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-sm-5">
                                <label class="form-label fw-semibold">{{ translate('update_delivery_status') }}</label>
                                <select name="delivery_status" class="form-control" required>
                                    <option value="pending_transfer" {{ $ds === 'pending_transfer' ? 'selected' : '' }}>Pending Transfer</option>
                                    <option value="in_transfer"      {{ $ds === 'in_transfer'      ? 'selected' : '' }}>In Transfer</option>
                                    <option value="transferred"      {{ $ds === 'transferred'      ? 'selected' : '' }}>Transferred</option>
                                    <option value="failed"           {{ $ds === 'failed'           ? 'selected' : '' }}>Failed</option>
                                </select>
                            </div>
                            <div class="col-sm-5">
                                <label class="form-label fw-semibold">{{ translate('delivery_note') }} <small class="text-muted fw-normal">(optional)</small></label>
                                <input type="text" name="delivery_note" class="form-control"
                                       value="{{ $withdrawRequest->delivery_note }}"
                                       placeholder="{{ translate('e.g. Reference: TXN12345') }}">
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-primary w-100">{{ translate('update') }}</button>
                            </div>
                        </div>
                        @if($withdrawRequest->delivery_status_at)
                        <small class="text-muted mt-2 d-block">
                            {{ translate('last_updated') }}: {{ date('d M Y, h:i A', strtotime($withdrawRequest->delivery_status_at)) }}
                        </small>
                        @endif
                    </form>
                </div>
            </div>
            @endif

        </div>{{-- /col-lg-8 --}}

        {{-- ===== RIGHT COLUMN ===== --}}
        <div class="col-lg-4">

            {{-- Timeline --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ translate('request_timeline') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0" style="position:relative;padding-left:24px;">
                        <li style="position:relative;margin-bottom:20px;">
                            <span style="position:absolute;left:-24px;top:3px;width:12px;height:12px;border-radius:50%;background:#16a34a;display:inline-block;"></span>
                            <div class="fw-semibold text-dark fs-14">{{ translate('request_submitted') }}</div>
                            <small class="text-muted">{{ date('d M Y, h:i A', strtotime($withdrawRequest->created_at)) }}</small>
                        </li>

                        @if($withdrawRequest->approved == 1 && $withdrawRequest->approved_at)
                        <li style="position:relative;margin-bottom:20px;">
                            <span style="position:absolute;left:-24px;top:3px;width:12px;height:12px;border-radius:50%;background:#16a34a;display:inline-block;"></span>
                            <div class="fw-semibold text-success fs-14">{{ translate('approved') }}</div>
                            <small class="text-muted">{{ date('d M Y, h:i A', strtotime($withdrawRequest->approved_at)) }}</small>
                        </li>
                        @elseif($withdrawRequest->approved == 2 && $withdrawRequest->denied_at)
                        <li style="position:relative;margin-bottom:20px;">
                            <span style="position:absolute;left:-24px;top:3px;width:12px;height:12px;border-radius:50%;background:#dc2626;display:inline-block;"></span>
                            <div class="fw-semibold text-danger fs-14">{{ translate('denied') }}</div>
                            <small class="text-muted">{{ date('d M Y, h:i A', strtotime($withdrawRequest->denied_at)) }}</small>
                        </li>
                        @elseif($withdrawRequest->approved == 0)
                        <li style="position:relative;margin-bottom:20px;opacity:.4;">
                            <span style="position:absolute;left:-24px;top:3px;width:12px;height:12px;border-radius:50%;background:#d1d5db;display:inline-block;"></span>
                            <div class="fw-semibold text-muted fs-14">{{ translate('awaiting_admin_decision') }}</div>
                        </li>
                        @endif

                        @if($withdrawRequest->approved == 1 && $withdrawRequest->delivery_status_at)
                        <li style="position:relative;margin-bottom:20px;">
                            <span style="position:absolute;left:-24px;top:3px;width:12px;height:12px;border-radius:50%;background:#{{ $ds === 'transferred' ? '16a34a' : ($ds === 'failed' ? 'dc2626' : 'f59e0b') }};display:inline-block;"></span>
                            <div class="fw-semibold text-dark fs-14">{{ $dsLabel }}</div>
                            <small class="text-muted">{{ date('d M Y, h:i A', strtotime($withdrawRequest->delivery_status_at)) }}</small>
                            @if($withdrawRequest->delivery_note)
                                <div class="text-muted fs-12 mt-1">{{ $withdrawRequest->delivery_note }}</div>
                            @endif
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Vendor Info --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ translate('vendor_info') }}</h5>
                </div>
                <div class="card-body">
                    @if(!$withdrawRequest->seller)
                        <div class="alert alert-warning mb-0 py-2">
                            {{ translate('vendor_account_no_longer_exists') }}
                            <br><small class="text-muted">Seller ID: {{ $withdrawRequest->seller_id }}</small>
                        </div>
                    @else
                    <div class="d-flex flex-column gap-2">
                        <div>
                            <p class="mb-0 fs-12 text-secondary">{{ translate('name') }}</p>
                            <p class="mb-0 fw-semibold text-dark">{{ $withdrawRequest->seller->f_name }} {{ $withdrawRequest->seller->l_name }}</p>
                        </div>
                        <div>
                            <p class="mb-0 fs-12 text-secondary">{{ translate('email') }}</p>
                            <p class="mb-0 fw-semibold text-dark">{{ $withdrawRequest->seller->email }}</p>
                        </div>
                        <div>
                            <p class="mb-0 fs-12 text-secondary">{{ translate('phone') }}</p>
                            <p class="mb-0 fw-semibold text-dark">{{ $withdrawRequest->seller->phone }}</p>
                        </div>
                        @if($withdrawRequest->seller->shop)
                        <hr class="my-1">
                        <div>
                            <p class="mb-0 fs-12 text-secondary">{{ translate('shop') }}</p>
                            <p class="mb-0 fw-semibold text-dark">{{ $withdrawRequest->seller->shop->name }}</p>
                        </div>
                        <div>
                            <p class="mb-0 fs-12 text-secondary">{{ translate('contact') }}</p>
                            <p class="mb-0 fw-semibold text-dark">{{ $withdrawRequest->seller->shop->contact }}</p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Action button or status --}}
            @if($withdrawRequest->approved == 0)
            <div class="card">
                <div class="card-body">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#processModal">
                        <i class="fi fi-rr-check me-1"></i>
                        {{ translate('process_this_request') }}
                    </button>
                </div>
            </div>
            @endif

        </div>{{-- /col-lg-4 --}}
    </div>{{-- /row --}}

    {{-- ===== PROCESS MODAL ===== --}}
    @if($withdrawRequest->approved == 0)
    <div class="modal fade" id="processModal" tabindex="-1" aria-labelledby="processModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="processModalLabel">{{ translate('process_withdrawal_request') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.vendors.withdraw_status', $withdrawRequest->id) }}" method="POST" novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="p-3 rounded mb-3" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                            <p class="mb-0 text-secondary fs-12">{{ translate('amount') }}</p>
                            <h4 class="mb-0 text-success fw-bold">
                                {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $withdrawRequest->amount), currencyCode: getCurrencyCode(type: 'default')) }}
                            </h4>
                        </div>

                        <div class="form-group mb-3">
                            <label class="fw-semibold">{{ translate('decision') }} <span class="text-danger">*</span></label>
                            <select name="approved" class="form-control" id="decisionSelect" required>
                                <option value="1">✓ {{ translate('approve') }}</option>
                                <option value="2">✕ {{ translate('rejected') }}</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="fw-semibold">
                                {{ translate('reason_note') }}
                                <small class="text-muted fw-normal" id="noteHint">({{ translate('optional_for_approval') }})</small>
                            </label>
                            <textarea class="form-control" name="note" rows="3"
                                      placeholder="{{ translate('e.g. Transfer scheduled for Monday') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('cancel') }}</button>
                        <button type="submit" class="btn btn-primary" id="submitDecision">{{ translate('submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('script')
<script>
    "use strict";
    document.getElementById('decisionSelect')?.addEventListener('change', function () {
        var hint = document.getElementById('noteHint');
        if (this.value === '2') {
            hint.textContent = '({{ translate('recommended_explain_reason') }})';
            hint.style.color = '#dc2626';
        } else {
            hint.textContent = '({{ translate('optional_for_approval') }})';
            hint.style.color = '';
        }
    });
</script>
@endpush
