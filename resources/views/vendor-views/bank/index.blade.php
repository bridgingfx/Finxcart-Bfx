@extends('layouts.vendor.app')

@section('title', translate('My_Banks'))

@section('content')
<div class="content container-fluid text-start">
    <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <i class="tio-bank-outlined"></i>
            {{ translate('My_Banks') }}
        </h2>
        <button type="button" class="btn btn--primary" data-toggle="modal" data-target="#add-bank-modal">
            <i class="tio-add"></i> {{ translate('Add_Bank') }}
        </button>
    </div>

    <div class="alert alert-info">
        {{ translate('Exactly_one_bank_must_be_active_at_all_times._This_is_the_account_used_for_your_withdrawal_requests.') }}
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ translate('bank_Name') }}</th>
                                    <th>{{ translate('holder_name') }}</th>
                                    <th>{{ translate('account_no') }}</th>
                                    <th>{{ translate('branch') }}</th>
                                    <th>{{ translate('status') }}</th>
                                    <th class="text-end">{{ translate('action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($banks as $bank)
                                    <tr>
                                        <td>{{ $bank->bank_name }}</td>
                                        <td>{{ $bank->holder_name }}</td>
                                        <td>{{ $bank->account_no }}</td>
                                        <td>{{ $bank->branch ?: '—' }}</td>
                                        <td>
                                            @if($bank->is_active)
                                                <span class="badge bg-success">{{ translate('active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ translate('inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                @if(!$bank->is_active)
                                                    <form action="{{ route('vendor.business-settings.bank.set-active', $bank->id) }}" method="post" novalidate>
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                                            {{ translate('Set_Active') }}
                                                        </button>
                                                    </form>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-toggle="modal" data-target="#edit-bank-modal-{{ $bank->id }}">
                                                    <i class="tio-edit"></i>
                                                </button>
                                                <form action="{{ route('vendor.business-settings.bank.destroy', $bank->id) }}" method="post"
                                                      onsubmit="return confirm('{{ translate('Are_you_sure_you_want_to_remove_this_bank?') }}')" novalidate>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            {{ $bank->is_active ? 'disabled title="'.translate('Set_another_bank_as_active_before_deleting_this_one').'"' : '' }}>
                                                        <i class="tio-delete-outlined"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Edit modal --}}
                                    <div class="modal fade" id="edit-bank-modal-{{ $bank->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <form action="{{ route('vendor.business-settings.bank.update', $bank->id) }}" method="post" novalidate>
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ translate('Edit_Bank') }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @include('vendor-views.bank._form-fields', ['bank' => $bank])
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('close') }}</button>
                                                        <button type="submit" class="btn btn--primary">{{ translate('update') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">{{ translate('No_banks_added_yet') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add modal --}}
<div class="modal fade" id="add-bank-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('vendor.business-settings.bank.store') }}" method="post" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Add_Bank') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('vendor-views.bank._form-fields')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('close') }}</button>
                    <button type="submit" class="btn btn--primary">{{ translate('save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
