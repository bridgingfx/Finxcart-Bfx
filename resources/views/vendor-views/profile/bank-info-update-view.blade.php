@extends('layouts.vendor.app')

@section('title', translate('bank_Info'))

@section('content')
<div class="content container-fluid text-start">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/my-bank-info.png') }}" alt="">
            {{ translate('edit_Bank_info') }}
        </h2>
    </div>

    @php($hasBankInfo = !empty($vendor->bank_name) || !empty($vendor->account_no))

    {{-- Current Saved Bank Info card (read-only display) --}}
    @if($hasBankInfo)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between py-3" style="background: linear-gradient(135deg,#1a8754 0%,#20c997 100%);">
                    <h5 class="mb-0 text-white d-flex align-items-center gap-2">
                        <i class="tio-checkmark-circle-outlined"></i>
                        {{ translate('current_bank_details') }}
                    </h5>
                    <span class="badge bg-white text-success fs-12 px-3 py-1">{{ translate('saved') }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-3">
                            <div class="p-3 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                <p class="mb-1 fs-12 fw-semibold" style="color:#166534;">{{ translate('Account_Holder') }}</p>
                                <p class="mb-0 fw-bold text-white">{{ $vendor->holder_name ?: '—' }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="p-3 rounded" style="background:#eff6ff;border:1px solid #bfdbfe;">
                                <p class="mb-1 fs-12 fw-semibold" style="color:#1e40af;">{{ translate('bank_Name') }}</p>
                                <p class="mb-0 fw-bold text-dark">{{ $vendor->bank_name ?: '—' }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="p-3 rounded" style="background:#fff7ed;border:1px solid #fed7aa;">
                                <p class="mb-1 fs-12 fw-semibold" style="color:#9a3412;">{{ translate('account_no') }}</p>
                                <p class="mb-0 fw-bold text-white" style="word-break:break-all;">{{ $vendor->account_no ?: '—' }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="p-3 rounded" style="background:#fdf4ff;border:1px solid #e9d5ff;">
                                <p class="mb-1 fs-12 fw-semibold" style="color:#6b21a8;">{{ translate('Account_Type') }}</p>
                                <p class="mb-0 fw-bold text-dark text-capitalize">{{ $vendor->account_type ?: '—' }}</p>
                            </div>
                        </div>
                        @if($vendor->swift_code)
                        <div class="col-sm-6 col-lg-3">
                            <div class="p-3 rounded" style="background:#f0f9ff;border:1px solid #bae6fd;">
                                <p class="mb-1 fs-12 fw-semibold" style="color:#0c4a6e;">{{ translate('SWIFT_BIC_Code') }}</p>
                                <p class="mb-0 fw-bold text-dark">{{ $vendor->swift_code }}</p>
                            </div>
                        </div>
                        @endif
                        @if($vendor->bank_country)
                        <div class="col-sm-6 col-lg-3">
                            <div class="p-3 rounded" style="background:#fefce8;border:1px solid #fef08a;">
                                <p class="mb-1 fs-12 fw-semibold" style="color:#713f12;">{{ translate('Bank_Country') }}</p>
                                <p class="mb-0 fw-bold text-dark">{{ $vendor->bank_country }}</p>
                            </div>
                        </div>
                        @endif
                        @if($vendor->branch)
                        <div class="col-sm-6 col-lg-3">
                            <div class="p-3 rounded bg-light border">
                                <p class="mb-1 fs-12 fw-semibold text-secondary">{{ translate('branch_Name') }}</p>
                                <p class="mb-0 fw-bold text-dark">{{ $vendor->branch }}</p>
                            </div>
                        </div>
                        @endif
                        @if($vendor->currency_preference)
                        <div class="col-sm-6 col-lg-3">
                            <div class="p-3 rounded bg-light border">
                                <p class="mb-1 fs-12 fw-semibold text-secondary">{{ translate('Currency_Preference') }}</p>
                                <p class="mb-0 fw-bold text-dark">{{ $vendor->currency_preference }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="mt-3 pt-3 border-top d-flex justify-content-end">
                        <form action="{{ route('vendor.profile.remove-bank-info', [$vendor->id]) }}" method="post"
                              onsubmit="return confirm('{{ translate('Are_you_sure_you_want_to_remove_all_bank_details?') }}')" novalidate>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="tio-delete-outlined me-1"></i>
                                {{ translate('remove_bank_details') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Edit / Add Bank Info Form --}}
    <div class="row mt-2">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0 text-capitalize">
                        {{ $hasBankInfo ? translate('Update_Bank_Information') : translate('Add_Bank_Information') }}
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('vendor.profile.update-bank-info', [$vendor->id]) }}" method="post"
                          enctype="multipart/form-data" novalidate>
                        @csrf

                        {{-- Row 1: Holder Name + Bank Name --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="title-color fw-semibold">
                                    {{ translate('Account_Holder_Full_Name') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="holder_name"
                                       value="{{ old('holder_name', $vendor->holder_name) }}"
                                       class="form-control @error('holder_name') is-invalid @enderror"
                                       placeholder="{{ translate('Full_name_as_registered_with_bank') }}"
                                       required>
                                <small class="form-text text-body">{{ translate('Must_exactly_match_name_registered_with_bank') }}</small>
                                @error('holder_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="title-color fw-semibold">
                                    {{ translate('bank_Name') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="bank_name"
                                       value="{{ old('bank_name', $vendor->bank_name) }}"
                                       class="form-control @error('bank_name') is-invalid @enderror"
                                       placeholder="{{ translate('Ex: HSBC, Barclays, Emirates NBD') }}"
                                       required>
                                @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Row 2: Account Number + Account Type --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="title-color fw-semibold">
                                    {{ translate('Account_Number') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="account_no"
                                       value="{{ old('account_no', $vendor->account_no) }}"
                                       class="form-control @error('account_no') is-invalid @enderror"
                                       placeholder="{{ translate('Ex: GB29NWBK60161331926819') }}"
                                       required>
                                <small class="form-text text-body">{{ translate('IBAN_format_for_SEPA_countries') }}</small>
                                @error('account_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="title-color fw-semibold">
                                    {{ translate('Account_Type') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="account_type"
                                        class="form-control @error('account_type') is-invalid @enderror"
                                        required>
                                    <option value="">--- {{ translate('Select_Account_Type') }} ---</option>
                                    <option value="current" {{ old('account_type', $vendor->account_type) == 'current' ? 'selected' : '' }}>
                                        {{ translate('Current_Account') }}
                                    </option>
                                    <option value="savings" {{ old('account_type', $vendor->account_type) == 'savings' ? 'selected' : '' }}>
                                        {{ translate('Savings_Account') }}
                                    </option>
                                </select>
                                @error('account_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Row 3: Branch Name + Branch Code --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="title-color fw-semibold">{{ translate('branch_Name') }}</label>
                                <input type="text" name="branch"
                                       value="{{ old('branch', $vendor->branch) }}"
                                       class="form-control @error('branch') is-invalid @enderror"
                                       placeholder="{{ translate('Ex: London City Branch') }}">
                                @error('branch') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="title-color fw-semibold">{{ translate('Branch_Code_Sort_Code') }}</label>
                                <input type="text" name="branch_code"
                                       value="{{ old('branch_code', $vendor->branch_code) }}"
                                       class="form-control @error('branch_code') is-invalid @enderror"
                                       placeholder="{{ translate('Ex: 20-00-00') }}">
                                <small class="form-text text-body">{{ translate('UK_sort_code_US_routing_number_or_local_branch_code') }}</small>
                                @error('branch_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Row 4: SWIFT/BIC + Bank Country --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="title-color fw-semibold">
                                    {{ translate('SWIFT_BIC_Code') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="swift_code"
                                       value="{{ old('swift_code', $vendor->swift_code) }}"
                                       class="form-control @error('swift_code') is-invalid @enderror"
                                       placeholder="{{ translate('Ex: HBUKGB4B or HBUKGB4BXXX') }}"
                                       required>
                                <small class="form-text text-body">{{ translate('Required_for_international_transfers_8_or_11_characters') }}</small>
                                @error('swift_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="title-color fw-semibold">
                                    {{ translate('Bank_Country') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="bank_country"
                                        class="form-control js-select2-custom @error('bank_country') is-invalid @enderror"
                                        required>
                                    <option value="">--- {{ translate('Select_Country') }} ---</option>
                                    @foreach(\App\Enums\GlobalConstant::COUNTRIES as $country)
                                        <option value="{{ $country['name'] }}"
                                            {{ old('bank_country', $vendor->bank_country) == $country['name'] ? 'selected' : '' }}>
                                            {{ $country['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Row 5: Currency Preference + Bank Address --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="title-color fw-semibold">{{ translate('Currency_Preference') }}</label>
                                <select name="currency_preference"
                                        class="form-control @error('currency_preference') is-invalid @enderror">
                                    <option value="">--- {{ translate('Select_Currency') }} ---</option>
                                    <option value="USD" {{ old('currency_preference', $vendor->currency_preference) == 'USD' ? 'selected' : '' }}>USD — US Dollar</option>
                                    <option value="GBP" {{ old('currency_preference', $vendor->currency_preference) == 'GBP' ? 'selected' : '' }}>GBP — British Pound</option>
                                    <option value="EUR" {{ old('currency_preference', $vendor->currency_preference) == 'EUR' ? 'selected' : '' }}>EUR — Euro</option>
                                    <option value="AED" {{ old('currency_preference', $vendor->currency_preference) == 'AED' ? 'selected' : '' }}>AED — UAE Dirham</option>
                                </select>
                                @error('currency_preference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="title-color fw-semibold">{{ translate('Bank_Address') }}</label>
                                <textarea name="bank_address" rows="3"
                                          class="form-control @error('bank_address') is-invalid @enderror"
                                          placeholder="{{ translate('Full_address_of_branch') }}">{{ old('bank_address', $vendor->bank_address) }}</textarea>
                                <small class="form-text text-body">{{ translate('Required_for_some_international_transfers') }}</small>
                                @error('bank_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-2">
                            <a class="btn btn-secondary" href="{{ route('vendor.profile.index') }}">{{ translate('cancel') }}</a>
                            <button type="submit" class="btn btn--primary" id="btn_update">
                                {{ $hasBankInfo ? translate('update') : translate('save_bank_info') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Payout Rules Panel --}}
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">{{ translate('Payout_Rules') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded border">
                                <p class="mb-1 fs-12 fw-semibold text-secondary">{{ translate('Platform_Commission_Rate') }}</p>
                                <h5 class="mb-0 text-dark">15% <span class="text-secondary fw-normal fs-12">{{ translate('deducted_automatically_per_sale') }}</span></h5>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded border">
                                <p class="mb-1 fs-12 fw-semibold text-secondary">{{ translate('Vendor_Payout_Rate') }}</p>
                                <h5 class="mb-0 text-success">85% <span class="text-secondary fw-normal fs-12">{{ translate('of_gross_sale_amount') }}</span></h5>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded border">
                                <p class="mb-1 fs-12 fw-semibold text-secondary">{{ translate('Minimum_Withdrawal') }}</p>
                                <h5 class="mb-0 text-dark">{{ setCurrencySymbol(amount: getWebConfig('minimum_withdrawal_amount') ?? 20) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded border">
                                <p class="mb-1 fs-12 fw-semibold text-secondary">{{ translate('Payout_Processing_Time') }}</p>
                                <h5 class="mb-0 text-dark">3–5 {{ translate('business_days') }}</h5>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded border">
                                <p class="mb-1 fs-12 fw-semibold text-secondary">{{ translate('Pending_Balance') }}</p>
                                <h5 class="mb-0 text-warning">
                                    {{ isset($wallet) ? setCurrencySymbol(amount: usdToDefaultCurrency($wallet->pending_withdraw ?? 0), currencyCode: getCurrencyCode()) : '—' }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded" style="background:#166534;">
                                <p class="mb-1 fs-12 fw-semibold text-white">{{ translate('Available_Balance') }}</p>
                                <h5 class="mb-0 text-white">
                                    {{ isset($wallet) ? setCurrencySymbol(amount: usdToDefaultCurrency($wallet->total_earning ?? 0), currencyCode: getCurrencyCode()) : '—' }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function () {
        $('.js-select2-custom').select2();
    });

    // SWIFT/BIC is either 8 or 11 characters (ISO 9362): 4 bank code letters,
    // 2 country letters, 2 location alnum, optional 3-char branch code.
$('input[name="swift_code"]').on('input', function () {
    let value = ($(this).val() || '').trim().toUpperCase();

    $(this).val(value);

    if (value === '') {
        clearFieldError(this);
        return;
    }

    // Must be EXACTLY 8 or EXACTLY 11 characters
    if (value.length !== 8 && value.length !== 11) {
        showFieldError(
            this,
            "{{ translate('SWIFT_BIC_code_must_be_8_or_11_characters') }}"
        );
        return;
    }

    // SWIFT/BIC format:
    // 4 letters + 2 letters + 2 alphanumeric
    // Optional 3 alphanumeric branch code
    let swiftPattern = /^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/;

    if (!swiftPattern.test(value)) {
        showFieldError(
            this,
            "{{ translate('Please_enter_a_valid_8_or_11_character_SWIFT_BIC_code') }}"
        );
    } else {
        clearFieldError(this);
    }
});
</script>
@endpush
