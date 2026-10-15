<?php

namespace App\Http\Controllers\Freelancer;

use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\VendorBankInfoRequest;
use App\Http\Requests\Vendor\VendorPasswordRequest;
use App\Http\Requests\Vendor\VendorRequest;
use App\Repositories\VendorRepository;
use App\Services\VendorService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    public function __construct(
        private readonly VendorRepository                $vendorRepo,
        private readonly VendorService                   $vendorService,
        private readonly VendorWalletRepositoryInterface  $vendorWalletRepo,
    )
    {
    }

    public function index(): View
    {
        $vendor = $this->vendorRepo->getFirstWhere(['id' => auth('freelancer')->id()]);
        return view('freelancer-views.profile.index', compact('vendor'));
    }

    public function update(VendorRequest $request, string|int $id): JsonResponse
    {
        if (auth('freelancer')->id() != $id) {
            abort(403);
        }

        $vendor = $this->vendorRepo->getFirstWhere(['id' => $id]);
        $this->vendorRepo->update(id: $id, data: $this->vendorService->getVendorDataForUpdate(request: $request, vendor: $vendor));
        return response()->json(['message' => translate('profile_updated_successfully')]);
    }

    public function updatePassword(VendorPasswordRequest $request, string|int $id): JsonResponse
    {
        if (auth('freelancer')->id() != $id) {
            abort(403);
        }

        $this->vendorRepo->update(id: $id, data: $this->vendorService->getVendorPasswordData(request: $request));
        return response()->json(['message' => translate('password_updated_successfully')]);
    }

    public function getBankInfoUpdateView(string|int $id): View|RedirectResponse
    {
        $vendorId = auth('freelancer')->id();
        if ($vendorId != $id) {
            ToastMagic::warning(translate('you_can_not_change_others_info'));
            return redirect()->back();
        }
        $vendor = $this->vendorRepo->getFirstWhere(['id' => $vendorId]);
        $wallet = $this->vendorWalletRepo->getFirstWhere(params: ['seller_id' => $vendorId]);
        return view('freelancer-views.profile.bank-info-update-view', compact('vendor', 'wallet'));
    }

    public function updateBankInfo(VendorBankInfoRequest $request, string|int $id): RedirectResponse
    {
        $vendorId = auth('freelancer')->id();
        if ($vendorId != $id) {
            ToastMagic::warning(translate('you_can_not_change_others_info'));
            return redirect()->back();
        }

        $this->vendorRepo->update(id: $vendorId, data: $this->vendorService->getVendorBankInfoData(request: $request));
        ToastMagic::success(translate('successfully_updated') . '!!');
        return redirect()->route('freelancer.profile.index');
    }

    public function removeBankInfo(string|int $id): RedirectResponse
    {
        $vendorId = auth('freelancer')->id();
        if ($vendorId != $id) {
            ToastMagic::warning(translate('you_can_not_change_others_info'));
            return redirect()->back();
        }
        $this->vendorRepo->update(id: $vendorId, data: [
            'bank_name'           => null,
            'branch'              => null,
            'branch_code'         => null,
            'account_no'          => null,
            'holder_name'         => null,
            'swift_code'          => null,
            'account_type'        => null,
            'bank_country'        => null,
            'bank_address'        => null,
            'currency_preference' => null,
        ]);
        ToastMagic::success(translate('bank_info_removed_successfully'));
        return redirect()->route('freelancer.profile.update-bank-info', [$vendorId]);
    }
}
