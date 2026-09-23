@extends('layouts.admin.app')

@section('title', translate('Payout_KYC'))

@push('css_or_js')
    <style>
        .payout-kyc-status-tab.active {
            color: #fff !important;
        }

        .payout-kyc-status-tab.active:hover,
        .payout-kyc-status-tab.active:focus {
            color: #fff !important;
            filter: brightness(.95);
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize">{{ translate('Payout_KYC') }}</h2>
            <div class="text-muted fs-12 mt-1">
                {{ translate('Approve_or_reject_vendor_withdrawal_KYC_separately_from_company_verification.') }}
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @php
                    $activeStatus = $status ?? 'all';
                    $tabs = [
                        'all'         => ['label' => translate('all'),         'color' => 'primary'],
                        'pending'     => ['label' => translate('pending'),     'color' => 'warning'],
                        'approved'    => ['label' => translate('approved'),    'color' => 'success'],
                        'rejected'    => ['label' => translate('rejected'),    'color' => 'danger'],
                        'not_started' => ['label' => translate('not_started'), 'color' => 'secondary'],
                    ];
                @endphp

                {{-- Status tabs with counts --}}
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($tabs as $tabKey => $tab)
                        <a href="{{ route('admin.vendors.payout-kyc.index', array_merge(request()->only(['search','seller_type']), ['status' => $tabKey])) }}"
                           class="btn btn-sm payout-kyc-status-tab {{ $activeStatus === $tabKey ? 'active btn-' . $tab['color'] : 'btn-outline-' . $tab['color'] }}">
                            {{ $tab['label'] }}
                            <span class="badge {{ $activeStatus === $tabKey ? 'bg-white text-' . $tab['color'] : 'bg-' . $tab['color'] . ' text-white' }} ms-1">
                                {{ $counts[$tabKey] ?? 0 }}
                            </span>
                        </a>
                    @endforeach
                </div>

                {{-- Search + Filter row --}}
                <form method="GET" action="{{ route('admin.vendors.payout-kyc.index') }}" class="mb-3" novalidate>
                    <input type="hidden" name="status" value="{{ $activeStatus }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small text-muted mb-1">{{ translate('search') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fi fi-rr-search"></i></span>
                                <input type="text" name="search" class="form-control"
                                       placeholder="{{ translate('search_by_name_or_email') }}"
                                       value="{{ $search ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">{{ translate('seller_type') }}</label>
                            <select name="seller_type" class="form-control">
                                <option value="">{{ translate('all_types') }}</option>
                                <option value="company"    {{ ($sellerType ?? '') === 'company'    ? 'selected' : '' }}>{{ translate('company') }}</option>
                                <option value="individual" {{ ($sellerType ?? '') === 'individual' ? 'selected' : '' }}>{{ translate('individual') }}</option>
                                <option value="freelancer" {{ ($sellerType ?? '') === 'freelancer' ? 'selected' : '' }}>{{ translate('freelancer') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fi fi-rr-filter-list me-1"></i> {{ translate('filter') }}
                            </button>
                        </div>
                        @if(($search ?? '') || ($sellerType ?? ''))
                            <div class="col-md-2">
                                <a href="{{ route('admin.vendors.payout-kyc.index', ['status' => $activeStatus]) }}"
                                   class="btn btn-outline-secondary w-100">
                                    <i class="fi fi-rr-cross me-1"></i> {{ translate('reset') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>{{ translate('vendor') }}</th>
                            <th>{{ translate('company_verification') }}</th>
                            <th>{{ translate('payout_KYC') }}</th>
                            <th>{{ translate('document') }}</th>
                            <th>{{ translate('seller_type') }}</th>
                            <th class="text-end">{{ translate('action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($vendors as $key => $vendor)
                            @php($payoutStatus = $vendor->kyc_status ?? 'not_started')
                            @php($payoutDocument = $vendor->vendorVerification?->payout_kyc_document)
                            @php($payoutDocumentLink = $payoutDocument ? storageLink('vendor-verifications', $payoutDocument, getWebConfig(name: 'storage_connection_type') ?? 'public') : null)
                            @php($manualPayoutKyc = (getWebConfig('kyc_method') ?? 'manual') === 'manual')
                            <tr>
                                <td>{{ $vendors->firstItem() + $key }}</td>
                                <td>
                                    <div class="fw-semibold">{{ trim($vendor->f_name . ' ' . $vendor->l_name) }}</div>
                                    <small class="text-muted">{{ $vendor->email }}</small>
                                    <div class="small text-muted">{{ $vendor->shop?->name }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-success">{{ translate('approved') }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $payoutStatus === 'approved' ? 'success' : ($payoutStatus === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst(str_replace('_', ' ', $payoutStatus)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($payoutDocumentLink)
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary doc-preview-btn"
                                                data-url="{{ $payoutDocumentLink['path'] ?? '#' }}"
                                                data-name="{{ $payoutDocument }}"
                                                data-type="{{ in_array(strtolower(pathinfo($payoutDocument, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp','bmp']) ? 'image' : 'pdf' }}">
                                            <i class="fi fi-rr-eye"></i> {{ translate('view_document') }}
                                        </button>
                                        @if($vendor->vendorVerification?->payout_kyc_submitted_at)
                                            <div class="small text-muted mt-1">{{ $vendor->vendorVerification->payout_kyc_submitted_at->format('M d, Y h:i A') }}</div>
                                        @endif
                                        @if($vendor->vendorVerification?->payout_kyc_note)
                                            <div class="small text-muted text-truncate" style="max-width: 220px;">{{ $vendor->vendorVerification->payout_kyc_note }}</div>
                                        @endif
                                    @elseif($vendor->vendorVerification?->sumsub_applicant_id)
                                        <span class="badge badge-soft-info">Sumsub</span>
                                        <div class="small text-muted">{{ $vendor->vendorVerification->sumsub_review_status }}</div>
                                    @else
                                        <span class="text-muted">{{ translate('not_available') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-soft-info text-capitalize">{{ $vendor->seller_type }}</span>
                                </td>
                                <td class="text-end">
                                    @if($payoutStatus !== 'approved')
                                        @if(!$manualPayoutKyc || $payoutDocumentLink)
                                            <form action="{{ route('admin.vendors.payout-kyc.approve', $vendor->id) }}" method="POST" class="d-inline kyc-action-form" novalidate>
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-success kyc-confirm-btn"
                                                    data-action="{{ translate('approve') }}"
                                                    data-color="#28a745">{{ translate('approve') }}</button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-secondary" disabled>
                                                {{ translate('document_required') }}
                                            </button>
                                        @endif
                                    @endif
                                    @if($payoutStatus !== 'rejected')
                                        <form action="{{ route('admin.vendors.payout-kyc.reject', $vendor->id) }}" method="POST" class="d-inline kyc-action-form" novalidate>
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-danger kyc-confirm-btn"
                                                data-action="{{ translate('reject') }}"
                                                data-color="#dc3545">{{ translate('reject') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">{{ translate('no_data_to_show') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    {!! $vendors->links() !!}
                </div>
            </div>
        </div>
    </div>

    {{-- Document Preview Modal --}}
    <div class="modal fade" id="docPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius:12px;overflow:hidden;">
                <div class="modal-header border-0 pb-0 px-4 pt-3">
                    <h6 class="modal-title text-truncate" id="docPreviewModalTitle" style="max-width:80%;"></h6>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"
                            style="background:none;border:none;font-size:1.5rem;line-height:1;opacity:.7;cursor:pointer;">
                        &times;
                    </button>
                </div>
                <div class="modal-body p-3 text-center" id="docPreviewBody" style="min-height:520px;background:#f1f5f9;">
                    <div class="d-flex align-items-center justify-content-center h-100" id="docPreviewSpinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
$(document).on('click', '.kyc-confirm-btn', function () {
    const btn = $(this);
    const action = btn.data('action');
    const color = btn.data('color');
    const form = btn.closest('.kyc-action-form');
    Swal.fire({
        title: '{{ translate('Are_you_sure?') }}',
        text: '{{ translate('This_will') }} ' + action + ' {{ translate('the_payout_KYC.') }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: color,
        confirmButtonText: '{{ translate('Yes') }}, ' + action,
        cancelButtonText: '{{ translate('Cancel') }}',
    }).then(function (result) {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});

document.querySelectorAll('.doc-preview-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var url    = this.getAttribute('data-url');
        var name   = this.getAttribute('data-name');
        var type   = this.getAttribute('data-type');
        var body   = document.getElementById('docPreviewBody');
        var title  = document.getElementById('docPreviewModalTitle');

        title.textContent = name;
        body.innerHTML = '<div class="d-flex align-items-center justify-content-center" style="min-height:480px;"><div class="spinner-border text-primary" role="status"></div></div>';

        var modal = new bootstrap.Modal(document.getElementById('docPreviewModal'));
        modal.show();

        if (type === 'image') {
            var img = new Image();
            img.onload = function() {
                body.innerHTML = '<img src="' + url + '" class="img-fluid rounded" style="max-height:75vh;object-fit:contain;" />';
            };
            img.onerror = function() {
                body.innerHTML = '<div class="text-danger py-5">Could not load image. <a href="' + url + '" target="_blank">Open in new tab</a></div>';
            };
            img.src = url;
        } else {
            body.innerHTML = '<iframe src="' + url + '" style="width:100%;height:75vh;border:none;border-radius:8px;" onload="this.style.background=\'#fff\'"></iframe>';
        }
    });
});
</script>
@endpush
