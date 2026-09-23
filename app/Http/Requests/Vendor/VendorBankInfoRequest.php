<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class VendorBankInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'holder_name'         => 'required|string|max:100',
            'bank_name'           => 'required|string|max:100',
            'account_no'          => 'required|string|max:34',
            'account_type'        => 'required|in:current,savings',
            'swift_code'          => 'required|string|min:8|max:11',
            'bank_country'        => 'required|string|max:60',
            'branch'              => 'nullable|string|max:100',
            'branch_code'         => 'nullable|string|max:20',
            'iban'                => 'nullable|string|max:34',
            'bank_address'        => 'nullable|string|max:500',
            'currency_preference' => 'nullable|string|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'holder_name.required'  => translate('Account_holder_name_is_required'),
            'bank_name.required'    => translate('Bank_name_is_required'),
            'account_no.required'   => translate('Account_number_is_required'),
            'account_type.required' => translate('Account_type_is_required'),
            'account_type.in'       => translate('Account_type_must_be_current_or_savings'),
            'swift_code.required'   => translate('SWIFT_BIC_code_is_required'),
            'swift_code.min'        => translate('SWIFT_code_must_be_8_or_11_characters'),
            'swift_code.max'        => translate('SWIFT_code_must_be_8_or_11_characters'),
            'bank_country.required' => translate('Bank_country_is_required'),
        ];
    }
}
