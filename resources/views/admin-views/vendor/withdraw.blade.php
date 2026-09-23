@extends('layouts.admin.app')

@section('title', translate('withdraw_request'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/withdraw-icon.png')}}" alt="">
                {{translate('withdraw')}}
            </h2>
        </div>

        @php
            $pendingCount = \App\Models\WithdrawRequest::whereNull('delivery_man_id')->where('approved', 0)->count();
        @endphp
        @if($pendingCount > 0)
        <div class="alert d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3"
             style="background:#fff8e1;border-left:4px solid #f59e0b;border-radius:8px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fi fi-sr-bell-ring fs-5" style="color:#f59e0b;"></i>
                <div>
                    <strong>{{ $pendingCount }} {{ translate('new_payout_request') }}{{ $pendingCount > 1 ? 's' : '' }} {{ translate('pending_your_action') }}</strong><br>
                    <small class="text-muted">{{ translate('Review_and_approve_or_deny_each_vendor_payout_request') }}</small>
                </div>
            </div>
            <a href="{{ route('admin.vendors.withdraw_list') }}?approved=pending"
               class="btn btn-sm btn-warning text-white fw-bold">
                {{ translate('View_Pending') }}
            </a>
        </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row gy-1 align-items-center justify-content-between mb-4">
                            <div class="col-auto">
                                <h3 class="text-capitalize">
                                {{ translate('withdraw_request_table')}}
                                    <span class="badge badge-info text-bg-info">{{ $withdrawRequests->total() }}</span>
                                </h3>
                            </div>
                            <div class="col-auto">
                                <div class="d-flex gap-3">
                                    <div class="select-wrapper">
                                        <select name="withdraw_status_filter" data-action="{{url()->current()}}" class="form-select min-w-120 withdraw-status-filter">
                                            <option value="all" {{request('approved') == 'all' ? 'selected' : ''}}>{{translate('all')}}</option>
                                            <option value="approved" {{request('approved') == 'approved' ? 'selected' : ''}}>{{translate('approved')}}</option>
                                            <option value="denied" {{request('approved') == 'denied' ? 'selected' : ''}}>{{translate('denied')}}</option>
                                            <option value="pending" {{request('approved') == 'pending' ? 'selected' : ''}}>{{translate('pending')}}</option>
                                        </select>
                                    </div>

                                    <a type="button" class="btn btn-outline-primary text-nowrap" href="{{ route('admin.vendors.withdraw-list-export-excel') }}?approved={{request('approved')}}">
                                        <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                                        <span class="ps-2">{{ translate('export') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover table-borderless table-nowrap align-middle">
                                <thead class="thead-light thead-50 text-capitalize">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('amount')}}</th>
                                    <th>{{ translate('vendor') }}</th>
                                    <th>{{ translate('method') }}</th>
                                    <th>{{translate('request_time')}}</th>
                                    <th class="text-center">{{translate('approval_status')}}</th>
                                    <th class="text-center">{{translate('transfer_status')}}</th>
                                    <th class="text-center">{{translate('action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($withdrawRequests as $key => $withdrawRequest)
                                    @php
                                        $ds = $withdrawRequest->delivery_status ?? 'pending_transfer';
                                        $dsColors = ['pending_transfer'=>'secondary','in_transfer'=>'warning','transferred'=>'success','failed'=>'danger'];
                                    @endphp
                                    <tr>
                                        <td>{{$withdrawRequests->firstItem() + $key }}</td>
                                        <td>
                                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $withdrawRequest['amount']), currencyCode: getCurrencyCode(type: 'default')) }}
                                        </td>
                                        <td>
                                            @if (isset($withdrawRequest->seller))
                                                <a href="{{route('admin.vendors.view', $withdrawRequest->seller_id)}}" class="text-dark text-hover-primary">{{ $withdrawRequest->seller->f_name . ' ' . $withdrawRequest->seller->l_name }}</a>
                                            @else
                                                <span class="text-muted">{{translate('not_found')}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-info">{{ $withdrawRequest->withdrawMethod?->method_name ?? translate('unknown') }}</span>
                                        </td>
                                        <td>
                                            <div>{{date("d M Y", strtotime($withdrawRequest->created_at))}}</div>
                                            <small class="text-muted">{{date("h:i A", strtotime($withdrawRequest->created_at))}}</small>
                                        </td>
                                        <td class="text-center">
                                            @if($withdrawRequest->approved == 0)
                                                <span class="badge badge-soft--primary">{{translate('pending')}}</span>
                                            @elseif($withdrawRequest->approved == 1)
                                                <span class="badge badge-soft-success">{{translate('approved')}}</span>
                                            @else
                                                <span class="badge badge-soft-danger">{{translate('denied')}}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($withdrawRequest->approved == 1)
                                                <form method="POST" action="{{ route('admin.vendors.withdraw_delivery_status', $withdrawRequest->id) }}" novalidate>
                                                    @csrf
                                                    <select name="delivery_status" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:140px;">
                                                        <option value="pending_transfer" {{ $ds == 'pending_transfer' ? 'selected' : '' }}>{{ translate('Pending Transfer') }}</option>
                                                        <option value="in_transfer"      {{ $ds == 'in_transfer'      ? 'selected' : '' }}>{{ translate('In Transfer') }}</option>
                                                        <option value="transferred"      {{ $ds == 'transferred'      ? 'selected' : '' }}>{{ translate('Transferred') }}</option>
                                                        <option value="failed"           {{ $ds == 'failed'           ? 'selected' : '' }}>{{ translate('Failed') }}</option>
                                                    </select>
                                                </form>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                @if (isset($withdrawRequest->seller))
                                                    <a href="{{route('admin.vendors.withdraw_view', ['withdrawId'=>$withdrawRequest['id'], 'vendorId'=>$withdrawRequest->seller['id']])}}"
                                                        class="btn btn-outline-info icon-btn" title="{{translate('view')}}">
                                                        <i class="fi fi-rr-eye"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">{{translate('action_disabled')}}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="table-responsive mt-4">
                            <div class="px-4 d-flex justify-content-center justify-content-end">
                                {{ $withdrawRequests->links() }}
                            </div>
                        </div>
                        @if(count($withdrawRequests) == 0)
                            @include('layouts.admin.partials._empty-state',['text'=>'no_withdraw_request_found'],['image'=>'default'])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
