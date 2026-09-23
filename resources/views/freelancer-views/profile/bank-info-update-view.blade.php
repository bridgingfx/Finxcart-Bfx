@extends('layouts.freelancer.app')

@section('title', translate('bank_Information'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0">{{ translate('bank_Information') }}</h2>
            <a href="{{ route('freelancer.profile.index') }}" class="btn btn-secondary">{{ translate('back') }}</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('freelancer.profile.update-bank-info', [$vendor->id]) }}" method="post" novalidate>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('Account_holder_name') }} *</label>
                            <input type="text" class="form-control" name="holder_name" value="{{ old('holder_name', $vendor->holder_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('Bank_name') }} *</label>
                            <input type="text" class="form-control" name="bank_name" value="{{ old('bank_name', $vendor->bank_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('Account_number') }} *</label>
                            <input type="text" class="form-control" name="account_no" value="{{ old('account_no', $vendor->account_no) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('Account_type') }} *</label>
                            <select class="form-control" name="account_type" required>
                                <option value="current" {{ old('account_type', $vendor->account_type) === 'current' ? 'selected' : '' }}>{{ translate('current') }}</option>
                                <option value="savings" {{ old('account_type', $vendor->account_type) === 'savings' ? 'selected' : '' }}>{{ translate('savings') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('SWIFT_BIC_code') }} *</label>
                            <input type="text" class="form-control" name="swift_code" value="{{ old('swift_code', $vendor->swift_code) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('Bank_country') }} *</label>
                            <input type="text" class="form-control" name="bank_country" value="{{ old('bank_country', $vendor->bank_country) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('branch') }}</label>
                            <input type="text" class="form-control" name="branch" value="{{ old('branch', $vendor->branch) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('branch_code') }}</label>
                            <input type="text" class="form-control" name="branch_code" value="{{ old('branch_code', $vendor->branch_code) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('iban') }}</label>
                            <input type="text" class="form-control" name="iban" value="{{ old('iban', $vendor->iban) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('currency_preference') }}</label>
                            <input type="text" class="form-control" name="currency_preference" value="{{ old('currency_preference', $vendor->currency_preference) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ translate('bank_address') }}</label>
                            <textarea class="form-control" name="bank_address" rows="2">{{ old('bank_address', $vendor->bank_address) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        @if($vendor->holder_name)
                            <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#remove-bank-info-modal">
                                {{ translate('remove') }}
                            </button>
                        @else
                            <span></span>
                        @endif
                        <button type="submit" class="btn btn--primary">{{ translate('save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="remove-bank-info-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <h5 class="mb-3">{{ translate('are_you_sure') }}?</h5>
                    <form action="{{ route('freelancer.profile.remove-bank-info', [$vendor->id]) }}" method="post" novalidate>
                        @csrf
                        @method('delete')
                        <div class="d-flex justify-content-center gap-2">
                            <button type="submit" class="btn btn-danger">{{ translate('yes_delete_it') }}</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('cancel') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
