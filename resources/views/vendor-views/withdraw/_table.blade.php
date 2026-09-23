<div class="table-responsive">
    <table id="datatable"
           style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
           class="table table-hover table-borderless table-thead-bordered table-align-middle card-table w-100">
        <thead class="thead-light thead-50 text-capitalize">
        <tr>
            <th>{{translate('SL')}}</th>
            <th>{{translate('amount')}}</th>
            <th>{{translate('request_time')}}</th>
            <th class="status-cell">{{translate('status')}}</th>
            <th class="status-cell">{{translate('delivery_status')}}</th>
            <th>{{translate('admin_note')}}</th>
            <th class="text-center">{{translate('action')}}</th>
        </tr>
        </thead>
        <tbody>
        @if($withdrawRequests->count() > 0)
            @foreach($withdrawRequests as $key=>$withdrawRequest)
                <tr>
                    <td>{{$withdrawRequests->firstitem()+$key}}</td>
                    <td>
                        <span class="fw-bold">
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $withdrawRequest['amount']), currencyCode: getCurrencyCode(type: 'default')) }}
                        </span>
                    </td>
                    <td>
                        <div>{{date("d M Y", strtotime($withdrawRequest->created_at))}}</div>
                        <small class="text-muted">{{date("h:i A", strtotime($withdrawRequest->created_at))}}</small>
                    </td>
                    <td>
                        @if($withdrawRequest->approved == 0)
                            <span class="badge badge-soft--primary">{{translate('pending')}}</span>
                        @elseif($withdrawRequest->approved == 1)
                            <div>
                                <span class="badge badge-soft-success">{{translate('approved')}}</span>
                                @if($withdrawRequest->approved_at)
                                    <div class="text-muted fs-11 mt-1">{{ date('d M Y, h:i A', strtotime($withdrawRequest->approved_at)) }}</div>
                                @endif
                            </div>
                        @else
                            <div>
                                <span class="badge badge-soft-danger">{{translate('denied')}}</span>
                                @if($withdrawRequest->denied_at)
                                    <div class="text-muted fs-11 mt-1">{{ date('d M Y, h:i A', strtotime($withdrawRequest->denied_at)) }}</div>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($withdrawRequest->approved == 1)
                            @php
                                $ds = $withdrawRequest->delivery_status ?? 'pending_transfer';
                                $dsColors = [
                                    'pending_transfer' => 'secondary',
                                    'in_transfer'      => 'warning',
                                    'transferred'      => 'success',
                                    'failed'           => 'danger',
                                ];
                                $dsColor = $dsColors[$ds] ?? 'secondary';
                            @endphp
                            <span class="badge badge-soft-{{ $dsColor }} text-capitalize">
                                {{ translate(str_replace('_', ' ', $ds)) }}
                            </span>
                            @if($withdrawRequest->delivery_note)
                                <div class="text-muted fs-11 mt-1" style="max-width:180px;white-space:normal;">
                                    {{ $withdrawRequest->delivery_note }}
                                </div>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($withdrawRequest->transaction_note)
                            <span class="text-dark" style="max-width:200px;white-space:normal;display:block;">
                                {{ $withdrawRequest->transaction_note }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($withdrawRequest->approved == 0)
                            <button data-action="{{ route('vendor.business-settings.withdraw.close', [$withdrawRequest['id']]) }}"
                                    class="btn btn-outline-danger btn-sm close-request"
                                    onclick="return confirm('{{ translate('Are_you_sure_you_want_to_cancel_this_withdrawal_request?') }}')">
                                <i class="tio-clear"></i> {{ translate('Cancel') }}
                            </button>
                        @elseif($withdrawRequest->approved == 1)
                            <span class="badge badge-soft-success px-3 py-2">{{ translate('Approved') }}</span>
                        @else
                            <span class="badge badge-soft-danger px-3 py-2">{{ translate('Denied') }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        @endif
        </tbody>
    </table>
</div>
@if(count($withdrawRequests)==0)
    @include('layouts.vendor.partials._empty-state',['text'=>'no_withdraw_request_found'],['image'=>'default'])
@endif
<div class="table-responsive mt-4">
    <div class="px-4 d-flex justify-content-lg-end">
        {{$withdrawRequests->links()}}
    </div>
</div>
