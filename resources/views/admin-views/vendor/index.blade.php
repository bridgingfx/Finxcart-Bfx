@php
    use Illuminate\Support\Str;
    $pendingVerificationCount = \App\Models\VendorVerification::whereIn('status', ['pending', 'resubmitted'])->count();
@endphp
@extends('layouts.admin.app')

@section('title', translate('vendor_List'))

@section('content')
    <span id="route-admin-vendor-delete" data-url="{{ route('admin.vendors.delete') }}"></span>
    <div class="content container-fluid">
        <div class="mb-4 d-flex flex-wrap gap-3 justify-content-between align-items-center">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('vendor_List')}}
                <span class="badge badge-info text-bg-info">{{ $vendors->total() }}</span>
            </h2>

            <button type="button"
                    class="btn btn-warning text-nowrap d-inline-flex align-items-center gap-2"
                    id="pending-review-filter-btn"
                    data-pending-count="{{ $pendingVerificationCount }}"
                    {{ $pendingVerificationCount === 0 ? 'disabled' : '' }}>
                <i class="fi fi-rr-hourglass-end"></i>
                <span>Pending Review ({{ $pendingVerificationCount }})</span>
            </button>
        </div>

        <div class="card">
            <div class="px-3 py-4">
                <div class="d-flex justify-content-between gap-10 flex-wrap align-items-center mb-4">
                    <div class="">
                        <form action="{{ url()->current() }}" method="GET" novalidate>
                            <div class="input-group">
                                <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                       placeholder="{{translate('search_by_shop_name_or_vendor_name_or_phone_or_email')}}" aria-label="Search orders" value="{{ request('searchValue') }}">
                                <div class="input-group-append search-submit">
                                    <button type="submit">
                                        <i class="fi fi-rr-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="d-flex justify-content-end gap-3">
                        <a type="button" class="btn btn-outline-primary text-nowrap" href="{{route('admin.vendors.export',['searchValue' => request('searchValue')])}}">
                            <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                            <span class="ps-2">{{ translate('export') }}</span>
                        </a>

                        <a href="{{route('admin.vendors.add')}}" type="button" class="btn btn-primary text-nowrap">
                            <i class="fi fi-rr-plus-small"></i>
                            {{translate('add_New_Vendor')}}
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-thead-bordered table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('shop_name')}}</th>
                                <th>{{translate('vendor_name')}}</th>
                                <th>{{translate('contact_info')}}</th>
                                <th class="text-center">{{translate('type')}}</th>
                                <th class="text-center">{{translate('account')}}</th>
                                <th class="text-center">{{translate('suspension')}}</th>
                                <th class="text-center">{{translate('company_verification')}}</th>
                                <th class="text-center">{{translate('kyc')}}</th>
                                <th class="text-center">{{translate('total_products')}}</th>
                                <th class="text-center">{{translate('total_orders')}}</th>
                                <th class="text-center">{{translate('created_at')}}</th>
                                <th class="text-center">{{translate('action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($vendors as $key=>$seller)
                            <tr class="{{ in_array($seller->vendorVerification?->status, ['pending', 'resubmitted']) ? 'table-warning' : '' }}"
                                data-verification-status="{{ $seller->vendorVerification?->status ?? 'none' }}">
                                <td>{{$vendors->firstItem()+$key}}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-10 w-max-content">
                                        <img width="50"
                                        class="avatar rounded-circle object-fit-cover" src="{{ getStorageImages(path: $seller?->shop?->image_full_url, type: 'backend-basic') }}"
                                            alt="">
                                        <div>
                                            <a class="text-dark text-hover-primary" href="{{ route('admin.vendors.view', ['id' => $seller->id]) }}">{{ $seller->shop?->name ? Str::limit($seller->shop->name, 20) : trim($seller->f_name.' '.$seller->l_name) }}</a>
                                            @if($seller->vendorVerification?->status === 'pending')
                                                <div class="mt-1">
                                                    <span class="badge badge-danger text-bg-danger">Verification pending</span>
                                                </div>
                                            @elseif($seller->vendorVerification?->status === 'resubmitted')
                                                <div class="mt-1">
                                                    <span class="badge bg-warning text-dark">Resubmitted</span>
                                                </div>
                                            @endif
                                            <span class="text-danger fs-12">
                                                @if($seller->shop && $seller->shop->temporary_close)
                                                <br>
                                                {{ translate('temporary_closed') }}
                                                @elseif($seller->shop && $seller->shop->vacation_status && $current_date >= date('Y-m-d', strtotime($seller->shop->vacation_start_date)) && $current_date <= date('Y-m-d', strtotime($seller->shop->vacation_end_date)))
                                                <br>
                                                {{ translate('on_vacation') }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a title="{{translate('view')}}"
                                        class="text-dark text-hover-primary"
                                        href="{{route('admin.vendors.view',$seller->id)}}">
                                        {{$seller->f_name}} {{$seller->l_name}}
                                    </a>
                                </td>
                                <td>
                                    <div class="mb-1">
                                        <strong><a class="text-dark text-hover-primary" href="mailto:{{$seller->email}}">{{$seller->email}}</a></strong>
                                    </div>
                                    <a class="text-dark text-hover-primary" href="tel:{{$seller->phone}}">{{$seller->phone}}</a>
                                </td>
                                <td class="text-center">
                                    @php($sellerType = strtolower($seller->seller_type ?: 'unknown'))
                                    <span class="badge text-capitalize {{ $sellerType === 'freelancer' ? 'bg-success' : ($sellerType === 'company' ? 'bg-primary' : 'bg-warning text-dark') }}">
                                        {{ $sellerType }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $seller->status === 'approved' ? 'badge-success text-bg-success' : 'badge-danger text-bg-danger' }}">
                                        {{ $seller->status === 'approved' ? translate('active') : translate('inactive') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $seller->account_status === 'active' ? 'badge-success text-bg-success' : 'badge-danger text-bg-danger' }}">
                                        {{ $seller->account_status === 'active' ? translate('unsuspend') : translate('suspended') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php($verificationStatus = $seller->vendorVerification?->status ?? 'not_submitted')
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
                                    @php($kycStatus = $seller->kyc_status ?? 'unsubmitted')
                                    <span class="badge {{ $kycStatus === 'approved' ? 'badge-success text-bg-success' : ($kycStatus === 'pending' ? 'bg-warning text-dark' : ($kycStatus === 'rejected' ? 'badge-danger text-bg-danger' : 'badge-secondary text-bg-secondary')) }}">
                                        {{ match($kycStatus) {
                                            'approved' => translate('approved'),
                                            'pending' => translate('pending'),
                                            'rejected' => translate('rejected'),
                                            default => translate('not_started'),
                                        } }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.vendors.view', ['id'=>$seller['id'], 'tab'=>'product']) }}"
                                        class="badge badge-info text-bg-info">
                                        {{$seller->product_count}}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.vendors.view',['id'=>$seller['id'], 'tab'=>'order']) }}"
                                        class="badge badge-info text-bg-info">
                                        {{ $seller->orders_filtered_count }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <div>{{ $seller->created_at?->format('d M Y') }}</div>
                                    <div class="text-muted fs-12">{{ $seller->created_at?->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <form action="{{ route('admin.vendors.account-status') }}" method="POST" class="d-inline vendor-status-toggle-form" novalidate>
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $seller->id }}">
                                            <input type="hidden" name="account_status" value="{{ $seller->account_status === 'active' ? 'inactive' : 'active' }}">
                                            <button type="submit"
                                                    class="btn icon-btn {{ $seller->account_status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    title="{{ $seller->account_status === 'active' ? translate('suspend') : translate('reactivate') }}">
                                                <i class="fi {{ $seller->account_status === 'active' ? 'fi-rr-ban' : 'fi-rr-check-circle' }}"></i>
                                            </button>
                                        </form>
                                        <a title="{{translate('view')}}"
                                            class="btn btn-outline-info icon-btn"
                                            href="{{route('admin.vendors.view',$seller->id)}}">
                                            <i class="fi fi-rr-eye"></i>
                                        </a>
                                        <a title="{{translate('delete')}}"
                                            class="btn btn-outline-danger icon-btn vendor-delete-button"
                                            id="{{ $seller->id }}">
                                            <i class="fi fi-rr-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive mt-4">
                    <div class="px-4 d-flex justify-content-center justify-content-md-end">
                        {!! $vendors->links() !!}
                    </div>
                </div>
                @if(count($vendors)==0)
                    @include('layouts.admin.partials._empty-state',['text'=>'no_vendor_found'],['image'=>'default'])
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButton = document.getElementById('pending-review-filter-btn');
            const rows = document.querySelectorAll('[data-verification-status]');

            if (!toggleButton || !rows.length) {
                return;
            }

            const pendingCount = toggleButton.dataset.pendingCount || '0';
            let pendingOnly = false;

            const render = () => {
                rows.forEach((row) => {
                    const isPending = row.dataset.verificationStatus === 'pending';
                    row.classList.toggle('d-none', pendingOnly && !isPending);
                });

                toggleButton.innerHTML = pendingOnly
                    ? '<i class="fi fi-rr-filter"></i><span>Show All Vendors</span>'
                    : '<i class="fi fi-rr-hourglass-end"></i><span>Pending Review (' + pendingCount + ')</span>';
            };

            toggleButton.addEventListener('click', function () {
                pendingOnly = !pendingOnly;
                render();
            });
        });

        let getYesWord = $("#message-yes-word").data("text");
        let getCancelWord = $("#message-cancel-word").data("text");
        let messageYouWillNotAbleRevertThis = $("#message-you-will-not-be-able-to-revert-this").data("text");

        $(".vendor-status-toggle-form").on("submit", function (e) {
            e.preventDefault();
            let form = this;
            let activating = $(form).find('input[name="account_status"]').val() === 'active';
            Swal.fire({
                title: activating ? "Reactivate this vendor's account?" : "Suspend this vendor's account?",
                text: activating
                    ? "The vendor will be able to log in and their products will show on the site again."
                    : "The vendor will not be able to log in or do anything, and their products will be hidden from the site until reactivated.",
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

        $(".vendor-delete-button").on("click", function () {
            let vendorId = $(this).attr("id");
            Swal.fire({
                title: "Delete this vendor?",
                text: "This will permanently delete the vendor and ALL related data — products, orders, wallet, withdraw requests and documents. " + messageYouWillNotAbleRevertThis,
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
                        url: $("#route-admin-vendor-delete").data("url"),
                        method: "POST",
                        data: { id: vendorId },
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
