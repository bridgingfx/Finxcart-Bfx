@extends('layouts.admin.app')

@section('title', translate('freelancer_accounts'))

@section('content')
    <span id="route-admin-freelancer-delete" data-url="{{ route('admin.freelancer.accounts.delete') }}"></span>
    <div class="content container-fluid">
        <div class="mb-4 d-flex flex-wrap gap-3 justify-content-between align-items-center">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/brand-setup.png') }}" alt="">
                {{ translate('freelancer_accounts') }}
                <span class="badge badge-soft-dark radius-50 fz-14">{{ $freelancers->total() }}</span>
            </h2>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.freelancer.accounts.create') }}" class="btn btn-primary text-nowrap d-inline-flex align-items-center gap-2">
                    <i class="fi fi-rr-plus"></i>
                    <span>{{ translate('add_freelancer') }}</span>
                </a>
                <button type="button"
                        class="btn btn-warning text-nowrap d-inline-flex align-items-center gap-2"
                        id="fl-pending-review-filter-btn"
                        data-pending-count="{{ $pendingVerificationCount }}"
                        {{ $pendingVerificationCount === 0 ? 'disabled' : '' }}>
                    <i class="fi fi-rr-hourglass-end"></i>
                    <span>{{ translate('pending_review') }} ({{ $pendingVerificationCount }})</span>
                </button>
            </div>
        </div>

        <div class="card">
            <div class="px-3 py-4">
                <div class="d-flex justify-content-between gap-10 flex-wrap align-items-center mb-4">
                    <form action="{{ route('admin.freelancer.accounts.index') }}" method="get" novalidate>
                        <div class="input-group">
                            <input type="search" name="searchValue" class="form-control"
                                   placeholder="{{ translate('search_by_name_email_or_phone') }}" value="{{ request('searchValue') }}">
                            <div class="input-group-append search-submit">
                                <button type="submit"><i class="fi fi-rr-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-thead-bordered table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('freelancer') }}</th>
                            <th>{{ translate('contact_info') }}</th>
                            <th class="text-center">{{ translate('account') }}</th>
                            <th class="text-center">{{ translate('suspension') }}</th>
                            <th class="text-center">{{ translate('verification') }}</th>
                            <th class="text-center">{{ translate('services') }}</th>
                            <th class="text-center">{{ translate('contracts') }}</th>
                            <th class="text-center">{{ translate('created_at') }}</th>
                            <th class="text-center">{{ translate('action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($freelancers as $key => $freelancer)
                            @php($verificationStatus = $freelancer->vendorVerification?->status ?? 'unsubmitted')
                            <tr class="{{ in_array($verificationStatus, ['pending', 'resubmitted']) ? 'table-warning' : '' }}"
                                data-verification-status="{{ $verificationStatus }}">
                                <td>{{ $freelancers->firstItem() + $key }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-10 w-max-content">
                                        <img width="42" class="avatar rounded-circle object-fit-cover" loading="lazy"
                                             src="{{ getStorageImages(path: $freelancer->image_full_url, type: 'backend-profile') }}" alt="">
                                        <div>
                                            <a class="text-dark text-hover-primary fw-semibold"
                                               href="{{ route('admin.freelancer.accounts.view', $freelancer->id) }}">
                                                {{ $freelancer->f_name }} {{ $freelancer->l_name }}
                                            </a>
                                            @if($verificationStatus === 'pending')
                                                <div class="mt-1"><span class="badge badge-danger text-bg-danger">{{ translate('verification_pending') }}</span></div>
                                            @elseif($verificationStatus === 'resubmitted')
                                                <div class="mt-1"><span class="badge bg-warning text-dark">{{ translate('Resubmitted') }}</span></div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="mb-1"><strong><a class="text-dark text-hover-primary" href="mailto:{{ $freelancer->email }}">{{ $freelancer->email }}</a></strong></div>
                                    <a class="text-dark text-hover-primary" href="tel:{{ $freelancer->phone }}">{{ $freelancer->phone }}</a>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $freelancer->status === 'approved' ? 'badge-success text-bg-success' : 'badge-danger text-bg-danger' }}">
                                        {{ $freelancer->status === 'approved' ? translate('active') : translate('inactive') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $freelancer->account_status !== 'inactive' ? 'badge-success text-bg-success' : 'badge-danger text-bg-danger' }}">
                                        {{ $freelancer->account_status !== 'inactive' ? translate('active') : translate('suspended') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ in_array($verificationStatus, ['pending', 'resubmitted']) ? 'bg-warning text-dark' : ($verificationStatus === 'approved' ? 'badge-success text-bg-success' : ($verificationStatus === 'rejected' ? 'badge-danger text-bg-danger' : 'badge-secondary text-bg-secondary')) }}">
                                        {{ match($verificationStatus) {
                                            'approved' => translate('verified'),
                                            'pending' => translate('pending'),
                                            'resubmitted' => translate('resubmitted'),
                                            'rejected' => translate('rejected'),
                                            default => translate('not_submitted'),
                                        } }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.freelancer.accounts.view', ['id' => $freelancer->id, 'tab' => 'services']) }}" class="badge badge-info text-bg-info">
                                        {{ $freelancer->services_count }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.freelancer.accounts.view', ['id' => $freelancer->id, 'tab' => 'contracts']) }}" class="badge badge-info text-bg-info">
                                        {{ $freelancer->contracts_count }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <div>{{ $freelancer->created_at?->format('d M Y') }}</div>
                                    <div class="text-muted fs-12">{{ $freelancer->created_at?->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <form action="{{ route('admin.freelancer.accounts.account-status') }}" method="POST" class="d-inline freelancer-status-toggle-form" novalidate>
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $freelancer->id }}">
                                            <input type="hidden" name="account_status" value="{{ $freelancer->account_status === 'inactive' ? 'active' : 'inactive' }}">
                                            <button type="submit"
                                                    class="btn icon-btn {{ $freelancer->account_status !== 'inactive' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    title="{{ $freelancer->account_status !== 'inactive' ? translate('suspend') : translate('reactivate') }}">
                                                <i class="fi {{ $freelancer->account_status !== 'inactive' ? 'fi-rr-ban' : 'fi-rr-check-circle' }}"></i>
                                            </button>
                                        </form>
                                        <a title="{{ translate('view') }}"
                                           class="btn btn-outline-info icon-btn"
                                           href="{{ route('admin.freelancer.accounts.view', $freelancer->id) }}">
                                            <i class="fi fi-rr-eye"></i>
                                        </a>
                                        <a title="{{ translate('edit') }}"
                                           class="btn btn-outline-primary icon-btn"
                                           href="{{ route('admin.freelancer.accounts.edit', $freelancer->id) }}">
                                            <i class="fi fi-rr-edit"></i>
                                        </a>
                                        <a title="{{ translate('delete') }}"
                                           class="btn btn-outline-danger icon-btn freelancer-delete-button"
                                           href="javascript:"
                                           id="{{ $freelancer->id }}">
                                            <i class="fi fi-rr-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                @if($freelancers->count() == 0)
                    @include('layouts.admin.partials._empty-state', ['text' => 'no_freelancer_found', 'image' => 'default'])
                @endif

                <div class="table-responsive mt-4">
                    <div class="px-4 d-flex justify-content-center justify-content-md-end">
                        {{ $freelancers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButton = document.getElementById('fl-pending-review-filter-btn');
            const rows = document.querySelectorAll('[data-verification-status]');

            if (!toggleButton || !rows.length) {
                return;
            }

            const pendingCount = toggleButton.dataset.pendingCount || '0';
            let pendingOnly = false;

            const render = () => {
                rows.forEach((row) => {
                    const status = row.dataset.verificationStatus;
                    const isPending = status === 'pending' || status === 'resubmitted';
                    row.classList.toggle('d-none', pendingOnly && !isPending);
                });

                toggleButton.innerHTML = pendingOnly
                    ? '<i class="fi fi-rr-filter"></i><span>{{ translate('show_all_freelancers') }}</span>'
                    : '<i class="fi fi-rr-hourglass-end"></i><span>{{ translate('pending_review') }} (' + pendingCount + ')</span>';
            };

            toggleButton.addEventListener('click', function () {
                pendingOnly = !pendingOnly;
                render();
            });
        });

        let getYesWord = $("#message-yes-word").data("text");
        let getCancelWord = $("#message-cancel-word").data("text");
        let messageYouWillNotAbleRevertThis = $("#message-you-will-not-be-able-to-revert-this").data("text");

        $(".freelancer-status-toggle-form").on("submit", function (e) {
            e.preventDefault();
            let form = this;
            let activating = $(form).find('input[name="account_status"]').val() === 'active';
            Swal.fire({
                title: activating ? "Reactivate this freelancer's account?" : "Suspend this freelancer's account?",
                text: activating
                    ? "The freelancer will be able to log in again."
                    : "The freelancer will not be able to log in until reactivated.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: activating ? "#198754" : "#dc3545",
                cancelButtonColor: "#d33",
                confirmButtonText: getYesWord,
                cancelButtonText: getCancelWord,
                reverseButtons: true,
            }).then((result) => {
                if (result.value) {
                    form.submit();
                }
            });
        });

        $(".freelancer-delete-button").on("click", function () {
            let freelancerId = $(this).attr("id");
            Swal.fire({
                title: "Delete this freelancer?",
                text: "This will permanently delete the freelancer account and ALL related data — services, portfolio items and documents. " + messageYouWillNotAbleRevertThis,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#d33",
                confirmButtonText: getYesWord,
                cancelButtonText: getCancelWord,
                reverseButtons: true,
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
                        },
                    });
                    $.ajax({
                        url: $("#route-admin-freelancer-delete").data("url"),
                        method: "POST",
                        data: { id: freelancerId },
                        success: function (response) {
                            Swal.fire({
                                title: response.message,
                                icon: "success",
                                confirmButtonColor: "#0f9d58",
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function (xhr) {
                            let message = xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : "Something went wrong.";
                            toastMagic.error(message);
                        },
                    });
                }
            });
        });
    </script>
@endpush
