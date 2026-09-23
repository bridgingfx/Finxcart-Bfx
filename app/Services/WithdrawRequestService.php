<?php

namespace App\Services;

use App\Models\SellerBank;

class WithdrawRequestService
{
    /**
     * @return array[seller_id: int|string, amount: float|int, transaction_note: null, withdrawal_method_id: mixed, withdrawal_method_fields: false|string, approved: int, created_at: \Illuminate\Support\Carbon, updated_at: \Illuminate\Support\Carbon]
     */
    public function getWithdrawRequestData(object $withdrawMethod, object $request , string $addedBy, int|string $vendorId):array
    {
        return [
            'seller_id' => $addedBy === 'vendor' ? $vendorId : '',
            'amount' => currencyConverter($request['amount']),
            'transaction_note' => null,
            'withdrawal_method_id' => $request['withdraw_method'],
            'withdrawal_method_fields' => json_encode($this->getWithdrawMethodFields(request:$request,withdrawMethod:$withdrawMethod, vendorId: $vendorId)),
            'approved' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * @param object $request
     * @param object $withdrawMethod
     * @param int|string $vendorId
     * @return array
     */
    public function getWithdrawMethodFields(object $request, object $withdrawMethod, int|string $vendorId = 0):array
    {
        if ($request->filled('withdraw_bank_id')) {
            $bank = SellerBank::where(['id' => $request['withdraw_bank_id'], 'seller_id' => $vendorId])->first();
            if ($bank) {
                return [
                    'method_name' => $withdrawMethod['method_name'],
                    'bank_name' => $bank->bank_name,
                    'holder_name' => $bank->holder_name,
                    'account_no' => $bank->account_no,
                    'branch' => $bank->branch,
                    'swift_code' => $bank->swift_code,
                    'ifsc_code' => $bank->ifsc_code,
                ];
            }
        }

        $inputFields = array_column($withdrawMethod['method_fields'], 'input_name');
        $method['method_name'] = $withdrawMethod['method_name'];
        $values = $request->all();
        foreach ($inputFields as $field) {
            if ($request->hasFile($field)) {
                $method[$field] = $request->file($field)->store('withdraw_files', 'public');
            } elseif (key_exists($field, $values)) {
                $method[$field] = $values[$field];
            }
        }
        return $method;
    }

}
