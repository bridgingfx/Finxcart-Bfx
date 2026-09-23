<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SellerBankRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'bank_name' => 'required|string|max:255',
            'holder_name' => 'required|string|max:255',
            'account_no' => 'required|string|max:255',
            'branch' => 'nullable|string|max:100',
            'swift_code' => 'nullable|string|max:11',
            'ifsc_code' => 'nullable|string|max:20',
        ];
    }
}
