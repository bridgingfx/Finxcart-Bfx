<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;

class VendorService
{
    use FileManagerTrait;
    /**
     * @param string $email
     * @param string $password
     * @param string|bool|null $rememberToken
     * @return bool
     */
    public function isLoginSuccessful(string $email, string $password, string|null|bool $rememberToken, string $guard = 'seller', array $extraConditions = []): bool
    {
        $credentials = ['email' => $email, 'password' => $password] + $extraConditions;

        if (auth($guard)->attempt($credentials, $rememberToken)) {
            return true;
        }
        return false;
    }

    /**
     * @param int $vendorId
     * @return array
     */
    public function getInitialWalletData(int $vendorId): array
    {
        return [
            'seller_id' => $vendorId,
            'withdrawn' => 0,
            'commission_given' => 0,
            'total_earning' => 0,
            'pending_withdraw' => 0,
            'delivery_charge_earned' => 0,
            'collected_cash' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function logout(string $guard = 'seller'): void
    {
        auth()->guard($guard)->logout();
        session()->invalidate();
    }

    /**
     * @param object $request
     * @return array
     */
    public function getFreeDeliveryOverAmountData(object $request):array
    {
        return [
            'free_delivery_status' => $request['free_delivery_status'] == 'on' ? 1 : 0,
            'free_delivery_over_amount' => currencyConverter($request['free_delivery_over_amount'], 'usd'),
        ];
    }

    /**
     * @return array[minimum_order_amount: float|int]
     */
    public function getMinimumOrderAmount(object $request) :array
    {
        return [
            'minimum_order_amount' => currencyConverter($request['minimum_order_amount'], 'usd')
        ];
    }

    /**
     * @param object $request
     * @param object $vendor
     * @return array
     */
    public function getVendorDataForUpdate(object $request, object $vendor):array
    {
        $image = $request['image'] ? $this->update(dir: 'seller/', oldImage: $vendor['image'], format: 'webp', image: $request->file('image')) : $vendor['image'];
        return [
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'phone' => $request['phone'],
            'image' => $image,
        ];
    }

    /**
     * @return array[password: string]
     */
    public function getVendorPasswordData(object $request):array
    {
        return [
            'password' => bcrypt($request['password']),
        ];
    }

    /**
     * @param object $request
     * @return array
     */
    public function getVendorBankInfoData(object $request):array
    {
        return [
            'holder_name'         => trim((string) $request['holder_name']),
            'bank_name'           => $request['bank_name'],
            'account_no'          => trim((string) $request['account_no']),
            'account_type'        => $request['account_type'],
            'swift_code'          => $request['swift_code'],
            'bank_country'        => $request['bank_country'],
            'branch'              => $request['branch'],
            'branch_code'         => $request['branch_code'],
            'iban'                => $request['iban'],
            'bank_address'        => $request['bank_address'],
            'currency_preference' => $request['currency_preference'],
        ];
    }
    public function getAddData(object $request, string $source = 'admin'):array
    {
        $emailPrefix = Str::before($request['email'], '@');
        return [
            'f_name' => $source === 'web'
                ? ($request['vendor_name'] ?? $emailPrefix)
                : ($request['vendor_name'] ?? $request['f_name'] ?? $emailPrefix),
            'l_name' => $request['l_name'] ?? null,
            'phone' => $request['phone'],
            'email' => $request['email'],
            'image' => $request->hasFile('image')
                ? $this->upload(dir: 'seller/', format: 'webp', image: $request->file('image'))
                : 'def.png',
            'password' => bcrypt($request['password']),
            'status' => 'pending',
            'seller_type' => $request['seller_type'] ?? null,
            'first_login_after_approval' => false,
        ];
    }
}
