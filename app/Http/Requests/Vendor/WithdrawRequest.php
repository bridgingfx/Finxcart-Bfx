<?php

namespace App\Http\Requests\Vendor;

use App\Models\WithdrawalMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
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
            'withdraw_method'=>'required',
            'amount'=>'required',
            'withdraw_bank_id'=>['required_if:' . $this->bankMethodRuleTarget(), 'nullable', 'integer'],
        ];
    }

    /**
     * Builds the "required_if" target against the currently selected method,
     * so withdraw_bank_id is only mandatory when that method is a bank-transfer type.
     */
    private function bankMethodRuleTarget(): string
    {
        $isBankMethod = WithdrawalMethod::whereKey($this->input('withdraw_method'))
            ->where('method_name', 'LIKE', '%bank%')
            ->exists();

        return $isBankMethod ? 'withdraw_method,' . $this->input('withdraw_method') : 'withdraw_method,__none__';
    }
}
