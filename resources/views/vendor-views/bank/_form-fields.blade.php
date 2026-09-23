@php($bank = $bank ?? null)
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="title-color fw-semibold">
            {{ translate('bank_Name') }} <span class="text-danger">*</span>
        </label>
        <input type="text" name="bank_name" value="{{ old('bank_name', $bank->bank_name ?? '') }}"
               class="form-control @error('bank_name') is-invalid @enderror" required>
        @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="title-color fw-semibold">
            {{ translate('holder_name') }} <span class="text-danger">*</span>
        </label>
        <input type="text" name="holder_name" value="{{ old('holder_name', $bank->holder_name ?? '') }}"
               class="form-control @error('holder_name') is-invalid @enderror" required>
        @error('holder_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="title-color fw-semibold">
            {{ translate('account_no') }} <span class="text-danger">*</span>
        </label>
        <input type="text" name="account_no" value="{{ old('account_no', $bank->account_no ?? '') }}"
               class="form-control @error('account_no') is-invalid @enderror" required>
        @error('account_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="title-color fw-semibold">{{ translate('branch') }}</label>
        <input type="text" name="branch" value="{{ old('branch', $bank->branch ?? '') }}"
               class="form-control @error('branch') is-invalid @enderror">
        @error('branch') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="title-color fw-semibold">{{ translate('swift_code') }}</label>
        <input type="text" name="swift_code" value="{{ old('swift_code', $bank->swift_code ?? '') }}"
               class="form-control @error('swift_code') is-invalid @enderror">
        @error('swift_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="title-color fw-semibold">{{ translate('ifsc_code') }}</label>
        <input type="text" name="ifsc_code" value="{{ old('ifsc_code', $bank->ifsc_code ?? '') }}"
               class="form-control @error('ifsc_code') is-invalid @enderror">
        @error('ifsc_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
