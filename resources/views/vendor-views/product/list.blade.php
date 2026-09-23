@extends('layouts.vendor.app')

@section('title', translate($type=='new-request'?'pending_products':($type=='approved'?'approved_products':'product_list')))

@section('content')
    <div class="content container-fluid">

        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
                {{ translate($type=='new-request'?'pending_for_approval_products':($type=='approved'?'approved_products':'product_list')) }}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">
                    {{ $products->total() }}
                </span>
            </h2>
        </div>

        @include('vendor-views.product.partials._slot-counter')

        <div class="row mt-20">
            <div class="col-md-12">
                <div class="card">
                    <div class="px-3 py-4">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <form action="{{ url()->current() }}" method="GET" novalidate>
                                    {{-- ... (Your search form code, no changes here) ... --}}
                                </form>
                            </div>
                            <div class="col-lg-8 mt-3 mt-lg-0 d-flex flex-wrap gap-3 justify-content-lg-end">
                                {{-- ... (Your export/limited stocks buttons, no changes here) ... --}}

                                @if($type != 'new-request' )
                                <a href="{{ route('vendor.products.stock-limit-list') }}" class="btn btn-info">
                                    <i class="tio-add-circle"></i>
                                    <span class="text">{{ translate('limited_Stocks') }}</span>
                                </a>

                                    @if(isset($seller_not_approved) && $seller_not_approved)
                                        <button type="button" class="btn btn--primary" data-toggle="modal" data-target="#pendingApprovalModal">
                                            <i class="tio-add"></i>
                                            <span class="text">{{ translate('add_new_product') }}</span>
                                        </button>
                                    @else
                                        <a href="{{ route('vendor.products.add') }}" class="btn btn--primary">
                                            <i class="tio-add"></i>
                                            <span class="text">{{ translate('add_new_product') }}</span>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    @php
                        $showTierSections = isset($activeTiersList)
                            && $activeTiersList->count() > 0;
                        $colSpan = ($type != 'new-request') ? 8 : 7;
                    @endphp
                    @if(!$showTierSections)
                    <div class="table-responsive">
                        <table id="datatable"
                               class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                            <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th class="text-capitalize">{{ translate('product_name') }}</th>
                                <th class="text-capitalize">{{ translate('Assigned_Tier') }}</th>
                                <th class="text-center text-capitalize">{{ translate('product_type') }}</th>
                                <th class="text-center text-capitalize">{{ translate('unit_price') }}</th>
                                <th class="text-center text-capitalize">{{ translate('verify_status') }}</th>
                                @if($type != 'new-request' )
                                <th class="text-center text-capitalize">{{ translate('show_as_featured') }}</th>
                                <th class="text-center text-capitalize">{{ translate('active_status') }}</th>
                                @endif
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($products as $key => $product)
                                @include('vendor-views.product.partials._product-row', ['rowIdx' => $products->firstItem() + $key])
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-lg-end">
                            {{ $products->links() }}
                        </div>
                    </div>

                    @if(count($products)==0)
                        @include('layouts.vendor.partials._empty-state',['text'=>'no_product_found'],['image'=>'default'])
                    @endif
                    @endif{{-- end !$showTierSections inside card --}}
                </div>{{-- close .card --}}
            </div>{{-- close col-md-12 --}}
        </div>{{-- close row.mt-20 --}}

        @if($showTierSections)
        @php
            $activeTierIds = $activeTiersList->pluck('id')->all();
            $groupedProds  = $products->getCollection()->groupBy('vendor_tier_id');
            $globalIdx     = $products->firstItem();
        @endphp
        @foreach($activeTiersList as $vt)
            @php
                $vtProds  = $groupedProds->get($vt->id, collect());
                $vtLimit  = (int)($vt->tier->listings_per_fee ?? 0);
            @endphp
            <div class="card mb-3">
                <div class="d-flex align-items-center gap-2 py-3 px-3 border-bottom" style="background:#f8fafc;">
                    <strong class="text-dark">{{ $vt->tier->name ?? '—' }}</strong>
                    <span class="badge badge-soft-{{ $vt->status === 'trial' ? 'warning' : 'success' }}">
                        {{ translate(ucfirst($vt->status)) }}
                    </span>
                    <span class="ml-auto text-muted small">{{ $tierProductCounts[$vt->id] ?? 0 }}/{{ $vtLimit }} {{ translate('slots') }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                        <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th class="text-capitalize">{{ translate('product_name') }}</th>
                            <th class="text-capitalize">{{ translate('Assigned_Tier') }}</th>
                            <th class="text-center text-capitalize">{{ translate('product_type') }}</th>
                            <th class="text-center text-capitalize">{{ translate('unit_price') }}</th>
                            <th class="text-center text-capitalize">{{ translate('verify_status') }}</th>
                            @if($type != 'new-request')
                            <th class="text-center text-capitalize">{{ translate('show_as_featured') }}</th>
                            <th class="text-center text-capitalize">{{ translate('active_status') }}</th>
                            @endif
                            <th class="text-center">{{ translate('action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($vtProds as $product)
                            @include('vendor-views.product.partials._product-row', ['rowIdx' => $globalIdx++])
                        @endforeach
                        @for($s = ($tierProductCounts[$vt->id] ?? 0) + 1; $s <= $vtLimit; $s++)
                            <tr>
                                <td colspan="{{ $colSpan + 1 }}" class="py-2 px-3" style="background:#fafbfe;">
                                    <div class="d-flex align-items-center justify-content-between text-muted">
                                        <span>
                                            <strong>{{ translate('slot') }} {{ $s }}</strong>
                                            — {{ translate('no_product_listed_yet') }}
                                        </span>
                                        @if($type != 'new-request')
                                        <a href="{{ route('vendor.products.add', ['vendor_tier_id' => $vt->id]) }}" class="btn btn-outline-primary btn-sm">
                                            + {{ translate('add_product') }}
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
        @php $otherProds = $products->getCollection()->filter(fn($p) => !in_array($p->vendor_tier_id, $activeTierIds)); @endphp
        @if($otherProds->count() > 0)
            <div class="card mb-3">
                <div class="py-3 px-3 border-bottom text-muted" style="background:#f8fafc;">
                    <em>{{ translate('Other') }}</em>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                        <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th class="text-capitalize">{{ translate('product_name') }}</th>
                            <th class="text-capitalize">{{ translate('Assigned_Tier') }}</th>
                            <th class="text-center text-capitalize">{{ translate('product_type') }}</th>
                            <th class="text-center text-capitalize">{{ translate('unit_price') }}</th>
                            <th class="text-center text-capitalize">{{ translate('verify_status') }}</th>
                            @if($type != 'new-request')
                            <th class="text-center text-capitalize">{{ translate('show_as_featured') }}</th>
                            <th class="text-center text-capitalize">{{ translate('active_status') }}</th>
                            @endif
                            <th class="text-center">{{ translate('action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($otherProds as $product)
                            @include('vendor-views.product.partials._product-row', ['rowIdx' => $globalIdx++])
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        @else
        {{-- Original bottom slot cards (only for single-card view) --}}
        @php
            $totalSlotsVal    = $totalSlots ?? 0;
            $usedSlotsVal     = $usedSlots  ?? 0;
        @endphp
        @if($totalSlotsVal > 0)
            @for($slot = $usedSlotsVal + 1; $slot <= $totalSlotsVal; $slot++)
                <div class="card mb-2" style="border:2px dashed #cbd5e1;">
                    <div class="card-body d-flex align-items-center justify-content-between py-2 text-muted">
                        <span>
                            <strong>{{ translate('slot') }} {{ $slot }}</strong>
                            — {{ translate('no_product_listed_yet') }}
                        </span>
                        <a href="{{ route('vendor.products.add') }}" class="btn btn-outline-primary btn-sm">
                            + {{ translate('add_product') }}
                        </a>
                    </div>
                </div>
            @endfor

            @if($usedSlotsVal >= $totalSlotsVal)
                <div class="alert alert-warning mt-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <span>{{ translate('youve_used_all') }} {{ $totalSlotsVal }} {{ translate('listing_slots') }}.</span>
                    <a href="{{ route('vendor.tier.index') }}" class="btn btn-sm btn-warning">{{ translate('upgrade_plan') }}</a>
                </div>
            @endif
        @endif
        @endif
    </div>
    <span id="message-select-word" data-text="{{ translate('select') }}"></span>

{{-- Pending Approval Modal --}}
<div class="modal fade" id="pendingApprovalModal" tabindex="-1" role="dialog" aria-labelledby="pendingApprovalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div style="background:linear-gradient(135deg,#1a2f5e,#2563eb);height:6px;"></div>
            <div class="modal-body text-center px-5 py-5">
                <div class="mb-4" style="width:80px;height:80px;border-radius:50%;background:#fff8e1;display:flex;align-items:center;justify-content:center;margin:0 auto;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="#f59e0b"/>
                    </svg>
                </div>
                @if(isset($verification_submitted) && $verification_submitted)
                    <h4 class="font-weight-bold mb-3" style="color:#1a2f5e;">{{ translate('Credentials_Under_Review') }}</h4>
                    <p class="text-muted mb-4">Your account is under review. You can add products once approved.</p>
                    <button type="button" class="btn btn--primary px-5" data-dismiss="modal">{{ translate('ok_got_it') }}</button>
                @else
                    <h4 class="font-weight-bold mb-3" style="color:#1a2f5e;">{{ translate('Account_Pending_Approval') }}</h4>
                    <p class="text-muted mb-4">{{ translate('Please_submit_your_credentials_first_and_wait_for_admin_review_before_adding_products.') }}</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">{{ translate('ok_got_it') }}</button>
                        <a href="{{ route('vendor.verification.form') }}" class="btn btn--primary px-4">{{ translate('Submit_Credentials') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>@endsection

@push('script')
<script>
    $(document).ready(function() {
        @if(session('show_approval_pending_modal'))
        $('#pendingApprovalModal').modal('show');
        @endif

        // This AJAX handler is still useful as a fallback,
        // but our new UI logic will prevent this from being
        // called in most "at limit" cases.
        $(document).on('submit', '.admin-product-status-form', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            var url = form.attr('action');
            var checkbox = form.find('.switcher_input');
            var isActivating = formData.includes('status=1');

            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                dataType: 'json',
                success: function(data) {
                    if(data.success) {
                        toastr.success(data.message);
                        // Reload the page to see correct counts and button states
                        location.reload();
                    } else {
                        toastr.error(data.message || 'An unknown error occurred.');
                        if(isActivating) checkbox.prop('checked', false);
                        else checkbox.prop('checked', true);
                    }
                },
                error: function(xhr, status, error) {
                    // This is where we catch the 403 from the middleware
                    if (xhr.status === 403) {
                        var response = xhr.responseJSON;
                        toastr.error(response.message); // Show the error from middleware

                        // This is the key: reset the toggle
                        if (response.reset_toggle && isActivating) {
                            checkbox.prop('checked', false);
                        }
                    } else {
                        toastr.error('An error occurred while updating status.');
                        if(isActivating) checkbox.prop('checked', false);
                        else checkbox.prop('checked', true);
                    }
                }
            });
        });
    });
</script>
@if(session('clear_pending_product_stage'))
<script>document.addEventListener('DOMContentLoaded', () => sessionStorage.removeItem('pending_product_stage'));</script>
@endif
@endpush



