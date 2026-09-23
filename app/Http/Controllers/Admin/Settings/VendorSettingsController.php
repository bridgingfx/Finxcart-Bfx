<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\VendorSettingsRequest;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VendorSettingsController extends BaseController
{

    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getView();
    }

    public function getView(): View
    {
        $sales_commission = $this->businessSettingRepo->getFirstWhere(params: ['type'=>'sales_commission']);
        if (!isset($sales_commission)) {
            $this->businessSettingRepo->add(data: ['type' => 'sales_commission', 'value' => 0]);
        }

        $seller_registration = $this->businessSettingRepo->getFirstWhere(params: ['type'=>'seller_registration']);
        if (!isset($seller_registration)) {
            $this->businessSettingRepo->add(data: ['type' => 'seller_registration', 'value' => 1]);
        }
        return view('admin-views.business-settings.seller-settings');
    }

    public function update(Request $request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'seller_pos', value: $request->get('seller_pos', 0));
        $this->businessSettingRepo->updateOrInsert(type: 'seller_registration', value: $request->get('seller_registration', 0));
        $this->businessSettingRepo->updateOrInsert(type: 'minimum_order_amount_by_seller', value: $request->get('minimum_order_amount_by_seller', 0));
        $this->businessSettingRepo->updateOrInsert(type: 'vendor_review_reply_status', value: $request->get('vendor_review_reply_status', 0));
        $this->businessSettingRepo->updateOrInsert(type: 'vendor_forgot_password_method', value: $request->get('vendor_forgot_password_method', 'phone'));
        ToastMagic::success(translate('Updated_successfully'));
        return redirect()->back();
    }

    public function updateKyc(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kyc_method' => ['required', 'in:manual,sumsub,disabled'],
            'sumsub_app_token' => ['nullable', 'string', 'max:255'],
            'sumsub_secret_key' => ['nullable', 'string', 'max:255'],
            'sumsub_flow_name' => ['nullable', 'string', 'max:100'],
            'kyc_required_for_payout' => ['required', 'in:0,1'],
        ]);

        foreach ([
            'kyc_method',
            'sumsub_app_token',
            'sumsub_secret_key',
            'sumsub_flow_name',
            'kyc_required_for_payout',
        ] as $key) {
            $this->businessSettingRepo->updateOrInsert(
                type: $key,
                value: $validated[$key] ?? ''
            );
        }

        clearWebConfigCacheKeys();
        ToastMagic::success(translate('Updated_successfully'));
        return redirect()->back();
    }

}
