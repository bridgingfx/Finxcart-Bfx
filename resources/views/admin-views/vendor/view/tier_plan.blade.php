@extends('layouts.admin.app')

@section('title', $seller?->shop->name ?? translate("shop_Name"))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('vendor_details')}}
            </h2>
        </div>

        <div class="page-header border-0 mb-4">
            <div class="position-relative nav--tab-wrapper">
                <ul class="nav nav-pills nav--tab">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.vendors.view', $seller['id']) }}">{{translate('shop_overview')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.vendors.view',['id'=>$seller['id'], 'tab'=>'order']) }}">{{translate('order')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.vendors.view',['id'=>$seller['id'], 'tab'=>'product']) }}">{{translate('product')}}</a>
                    </li>

                    {{-- ACTIVE TAB --}}
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.vendors.view',['id'=>$seller['id'], 'tab'=>'tier_plan']) }}">{{translate('Vendor_Tier_Plans')}}</a>
                    </li>
                    {{-- -- --}}

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.vendors.view',['id'=>$seller['id'], 'tab'=>'clearance_sale']) }}">{{translate('clearance_sale_products')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.vendors.view',['id'=>$seller['id'], 'tab'=>'setting']) }}">{{translate('setting')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.vendors.view',['id'=>$seller['id'], 'tab'=>'transaction']) }}">{{translate('transaction')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.vendors.view',['id'=>$seller['id'], 'tab'=>'review']) }}">{{translate('review')}}</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">{{translate('vendor_tier_plans')}}</h5>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-soft-dark radius-50 fz-12">{{ $vendorTiers->total() }}</span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                                        <thead class="thead-light thead-50 text-capitalize">
                                            <tr>
                                                <th>{{translate('SL')}}</th>
                                                <th>{{translate('tier_name')}}</th>
                                                <th>{{translate('monthly_fee')}}</th>
                                                <th>{{translate('commission')}}</th>
                                                <th>{{translate('trial_commission')}}</th> {{-- NEW COLUMN --}}
                                                <th>{{translate('start_date')}}</th>
                                                <th>{{translate('end_date')}}</th>
                                                <th>{{translate('trial_end_date')}}</th> {{-- NEW COLUMN --}}
                                                <th>{{translate('status')}}</th>
                                                <th class="text-center">{{translate('auto_renew')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($vendorTiers as $key => $tier)
                                            <tr>
                                                <td>{{ $vendorTiers->firstItem() + $key }}</td>
                                                <td>
                                                    @if($tier->tier)
                                                        <span class="font-weight-bold text-primary">{{ $tier->tier->name }}</span>
                                                    @else
                                                        <span class="text-muted">{{ translate('tier_deleted') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $tier->monthly_fee_usd)) }}
                                                </td>
                                                <td>
                                                    {{ $tier->sales_commission_rate }}%
                                                </td>
                                               {{-- New Trial Commission Data --}}
                                                <td>
                                                    @if($tier->trial_commission_rate !== null)
                                                        <span class="text-info">{{ $tier->trial_commission_rate }}%</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    {{ date('d M, Y', strtotime($tier->start_date)) }}
                                                </td>
                                                <td>
                                                    {{ date('d M, Y', strtotime($tier->end_date)) }}
                                                </td>

                                                {{-- New Trial End Date Data --}}
                                                <td>
                                                    @if($tier->trial_end_date)
                                                        {{ date('d M, Y', strtotime($tier->trial_end_date)) }}
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($tier->status == 'active')
                                                        <span class="badge badge-soft-success">{{ translate('active') }}</span>
                                                    @elseif($tier->status == 'trial')
                                                        <span class="badge badge-soft-info">{{ translate('trial') }}</span>
                                                    @elseif($tier->status == 'expired')
                                                        <span class="badge badge-soft-warning">{{ translate('expired') }}</span>
                                                    @elseif($tier->status == 'cancelled')
                                                        <span class="badge badge-soft-danger">{{ translate('cancelled') }}</span>
                                                    @else
                                                        <span class="badge badge-soft-secondary">{{ translate($tier->status) }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($tier->is_auto_renew)
                                                        <i class="fi fi-rr-check-circle text-success" style="font-size: 1.2rem"></i>
                                                    @else
                                                        <i class="fi fi-rr-cross-circle text-muted" style="font-size: 1.2rem"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center p-4">
                                                    <div class="py-4">
                                                        <img class="mb-3 w-160" src="{{ dynamicAsset(path: 'public/assets/back-end/svg/illustrations/sorry.svg') }}" alt="Image Description">
                                                        <p class="mb-0">{{ translate('no_tier_plans_found') }}</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="table-responsive mt-4">
                                    <div class="px-4 d-flex justify-content-center justify-content-md-end">
                                        {{ $vendorTiers->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
