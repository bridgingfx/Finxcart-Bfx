@extends('layouts.admin.app')

@section('title', translate('vendor_verification'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize">{{ translate('vendor_verification') }}</h2>
        </div>

        <div class="card">
            <div class="card-body">
                @php($activeType = $type ?? request('type', 'all'))
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach(['all' => translate('all'), 'company' => translate('company'), 'individual' => translate('individual')] as $filterType => $filterLabel)
                        <a href="{{ route('admin.vendors.vendor-verifications.index', ['type' => $filterType]) }}"
                           class="btn btn-sm {{ $activeType === $filterType ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ $filterLabel }}
                        </a>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>{{ translate('vendor_name') }}</th>
                            <th>{{ translate('type') }}</th>
                            <th>{{ translate('website_or_contact') }}</th>
                            <th>{{ translate('country') }}</th>
                            <th>{{ translate('document') }}</th>
                            <th>{{ translate('status') }}</th>
                            <th class="text-end">{{ translate('action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($verifications as $key => $verification)
                            @php($isCompany = $verification->seller_type === 'company')
                            @php($document = $isCompany ? $verification->company_license : $verification->personal_id_document)
                            @php($documentLink = $document ? storageLink('vendor-verifications', $document, getWebConfig(name: 'storage_connection_type') ?? 'public') : null)
                            <tr>
                                <td>{{ $verifications->firstItem() + $key }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $verification->seller?->f_name }} {{ $verification->seller?->l_name }}</div>
                                    <small class="text-muted">{{ $verification->seller?->shop?->name }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-soft-info text-capitalize">{{ $verification->seller_type }}</span>
                                </td>
                                <td>
                                    @if($isCompany && $verification->company_website)
                                        <a href="{{ $verification->company_website }}" target="_blank">{{ $verification->company_website }}</a>
                                        <div class="small text-muted">{{ $verification->company_no }}</div>
                                    @else
                                        <span>{{ $verification->seller?->phone ?? translate('not_available') }}</span>
                                        @if($verification->sell_description)
                                            <div class="small text-muted text-truncate" style="max-width: 240px;">{{ $verification->sell_description }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $verification->company_registered_country }}</td>
                                <td>
                                    @if($documentLink)
                                        <a href="{{ $documentLink['path'] ?? '#' }}" target="_blank">
                                            {{ $document }}
                                        </a>
                                    @else
                                        <span class="text-muted">{{ translate('not_available') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($verification->status === 'resubmitted')
                                        <span class="badge bg-warning text-dark">{{ translate('Resubmitted') }}</span>
                                    @else
                                        <span class="badge badge-warning text-dark text-capitalize">{{ $verification->status }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveVerificationModal{{ $verification->id }}">
                                        {{ translate('approve') }}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectVerificationModal{{ $verification->id }}">
                                        {{ translate('reject') }}
                                    </button>
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

                @foreach($verifications as $verification)
                    <div class="modal fade" id="approveVerificationModal{{ $verification->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="{{ route('admin.vendors.vendor-verifications.approve', $verification->id) }}" method="POST" novalidate>
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ translate('want_to_approve_this_vendor') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label">{{ translate('approval_note') }}</label>
                                        <textarea name="approval_note" class="form-control" rows="4" maxlength="1000" placeholder="{{ translate('optional_note_for_vendor') }}"></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('cancel') }}</button>
                                        <button type="submit" class="btn btn-success">{{ translate('approve') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="rejectVerificationModal{{ $verification->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="{{ route('admin.vendors.vendor-verifications.reject', $verification->id) }}" method="POST" novalidate>
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ translate('want_to_reject_this_vendor') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label">{{ translate('rejection_reason') }}</label>
                                        <textarea name="rejection_reason" class="form-control" rows="4" required></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('cancel') }}</button>
                                        <button type="submit" class="btn btn-danger">{{ translate('reject') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-end">
                    {!! $verifications->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
