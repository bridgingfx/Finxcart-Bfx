<?php

namespace App\Http\Controllers\Vendor;

use App\Contracts\Repositories\SellerBankRepositoryInterface;
use App\Enums\ViewPaths\Vendor\Bank;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\SellerBankRequest;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SellerBankController extends BaseController
{
    public function __construct(
        private readonly SellerBankRepositoryInterface $sellerBankRepo,
    )
    {
    }

    // Signature must stay compatible with App\Contracts\ControllerInterface::index()
    // (implemented via BaseController) — a narrower signature here is a PHP fatal
    // "declaration must be compatible" error at class-load time, which crashes the
    // whole app on every request since routes/vendor/routes.php loads this class.
    public function index(?Request $request = null, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse|JsonResponse
    {
        $vendorId = auth('seller')->id();
        $banks = $this->sellerBankRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['seller_id' => $vendorId],
            dataLimit: 'all'
        );
        return view(Bank::INDEX[VIEW], compact('banks'));
    }

    public function store(SellerBankRequest $request): RedirectResponse
    {
        $vendorId = auth('seller')->id();
        $existingCount = $this->sellerBankRepo->getListWhere(filters: ['seller_id' => $vendorId], dataLimit: 'all')->count();

        $this->sellerBankRepo->add([
            'seller_id' => $vendorId,
            'bank_name' => $request['bank_name'],
            'holder_name' => $request['holder_name'],
            'account_no' => $request['account_no'],
            'branch' => $request['branch'],
            'swift_code' => $request['swift_code'],
            'ifsc_code' => $request['ifsc_code'],
            'is_active' => $existingCount === 0,
        ]);

        ToastMagic::success(translate('bank_added_successfully'));
        return redirect()->back();
    }

    public function update(SellerBankRequest $request, string $id): RedirectResponse
    {
        $bank = $this->sellerBankRepo->getFirstWhere(params: ['id' => $id, 'seller_id' => auth('seller')->id()]);
        if (!$bank) {
            ToastMagic::error(translate('access_denied'));
            return redirect()->back();
        }

        $this->sellerBankRepo->update(id: $id, data: [
            'bank_name' => $request['bank_name'],
            'holder_name' => $request['holder_name'],
            'account_no' => $request['account_no'],
            'branch' => $request['branch'],
            'swift_code' => $request['swift_code'],
            'ifsc_code' => $request['ifsc_code'],
        ]);

        ToastMagic::success(translate('bank_updated_successfully'));
        return redirect()->back();
    }

    public function setActive(string $id): RedirectResponse
    {
        $vendorId = auth('seller')->id();
        $bank = $this->sellerBankRepo->getFirstWhere(params: ['id' => $id, 'seller_id' => $vendorId]);
        if (!$bank) {
            ToastMagic::error(translate('access_denied'));
            return redirect()->back();
        }

        $this->sellerBankRepo->setActive(sellerId: $vendorId, bankId: (int) $id);
        ToastMagic::success(translate('active_bank_updated_successfully'));
        return redirect()->back();
    }

    public function destroy(string $id): RedirectResponse
    {
        $vendorId = auth('seller')->id();
        $bank = $this->sellerBankRepo->getFirstWhere(params: ['id' => $id, 'seller_id' => $vendorId]);
        if (!$bank) {
            ToastMagic::error(translate('access_denied'));
            return redirect()->back();
        }

        if ($bank['is_active']) {
            ToastMagic::error(translate('Set_another_bank_as_active_before_deleting_this_one'));
            return redirect()->back();
        }

        $this->sellerBankRepo->delete(['id' => $id, 'seller_id' => $vendorId]);
        ToastMagic::success(translate('bank_removed_successfully'));
        return redirect()->back();
    }

    public function preview(string $id): JsonResponse
    {
        $bank = $this->sellerBankRepo->getFirstWhere(params: ['id' => $id, 'seller_id' => auth('seller')->id()]);
        if (!$bank) {
            return response()->json(['success' => 0], 404);
        }

        return response()->json(['success' => 1, 'content' => $bank], 200);
    }
}
