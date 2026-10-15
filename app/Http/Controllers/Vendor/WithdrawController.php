<?php

namespace App\Http\Controllers\Vendor;

use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Contracts\Repositories\WithdrawRequestRepositoryInterface;
use App\Contracts\Repositories\WithdrawalMethodRepositoryInterface;
use App\Enums\ViewPaths\Vendor\DeliveryManWithdraw;
use App\Enums\ViewPaths\Vendor\Withdraw;
use App\Exports\VendorWithdrawRequest;
use App\Http\Controllers\BaseController;
use App\Models\SellerWallet;
use App\Services\VendorWalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WithdrawController extends BaseController
{
    /**
     * @param WithdrawRequestRepositoryInterface $withdrawRequestRepo
     * @param VendorWalletRepositoryInterface $vendorWalletRepo
     * @param VendorWalletService $vendorWalletService
     */
    public function __construct(
        private readonly WithdrawRequestRepositoryInterface  $withdrawRequestRepo,
        private readonly VendorWalletRepositoryInterface     $vendorWalletRepo,
        private readonly VendorWalletService                 $vendorWalletService,
        private readonly VendorRepositoryInterface           $vendorRepo,
        private readonly WithdrawalMethodRepositoryInterface $withdrawalMethodRepo,
    )
    {

    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|null
     */
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable
    {
        return $this->getListView();
    }

    /**
     * @return View
     */
    public function getListView(): View
    {
        $vendorId = auth('seller')->id();
        $withdrawRequests = $this->withdrawRequestRepo->getListWhere(
            orderBy: ['id'=>'desc'],
            filters: ['vendorId' => $vendorId],
            relations: ['seller'],
            dataLimit: getWebConfig('pagination_limit')
        );
        $wallet = $this->vendorWalletRepo->getFirstWhere(params: ['seller_id' => $vendorId]);
        $withdrawalMethods = $this->withdrawalMethodRepo->getListWhere(filters: ['is_active' => 1], dataLimit: 'all');
        return view(Withdraw::INDEX[VIEW], compact('withdrawRequests', 'wallet', 'withdrawalMethods'));
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getListByStatus(Request $request):JsonResponse
    {
        $vendorId = auth('seller')->id();
        $withdrawRequests = $this->withdrawRequestRepo->getListWhere(
            filters: [
                'vendorId' => $vendorId,
                'status' => $request['status']
            ],
            relations: ['seller'],
            dataLimit: getWebConfig('pagination_limit')
        );
        return response()->json([
            'view' => view(Withdraw::INDEX[TABLE_VIEW], compact('withdrawRequests'))->render(),
            'count' => $withdrawRequests->total(),
        ], 200);
    }

    /**
     * @param string|int $id
     * @return RedirectResponse
     */
    public function closeWithdrawRequest(string|int $id):RedirectResponse
    {
        $withdrawRequest = $this->withdrawRequestRepo->getFirstWhere(params: [
            'id'        => $id,
            'seller_id' => auth('seller')->id(),
        ]);

        if (!$withdrawRequest) {
            ToastMagic::error(message: translate('access_denied'));
            return redirect()->back();
        }

        if ($withdrawRequest['approved'] == 0) {
            DB::transaction(function () use ($withdrawRequest) {
                $wallet = SellerWallet::where('seller_id', auth('seller')->id())
                    ->lockForUpdate()
                    ->first();

                $converted = currencyConverter($withdrawRequest['amount']);
                $this->vendorWalletRepo->update(
                    id: $wallet['id'],
                    data: $this->vendorWalletService->getVendorWalletData(
                        totalEarning: $wallet['total_earning'] + $converted,
                        pendingWithdraw: $wallet['pending_withdraw'] - $converted
                    )
                );
                $this->withdrawRequestRepo->delete(['id' => $withdrawRequest['id']]);
            });
            ToastMagic::success(message: translate('request_closed') . '!');
        } else {
            ToastMagic::error(message: translate('invalid_request'));
        }
        return redirect()->back();
    }

    public function exportList(Request $request):BinaryFileResponse
    {

        $vendorId = auth('seller')->id();
        $vendor = $this->vendorRepo->getFirstWhere(params:['id' => $vendorId]);
        $withdrawRequests = $this->withdrawRequestRepo->getListWhere(
            orderBy: ['id'=>'desc'],
            searchValue: $request['searchValue'],
            filters: [
                'vendorId'=> $vendorId,
                'status'=>$request['status']
            ],
            relations: ['seller'],
            dataLimit: 'all'
        );
        $pendingRequest = $withdrawRequests->where('approved',0)->count();
        $approvedRequest = $withdrawRequests->where('approved',1)->count();
        $deniedRequest = $withdrawRequests->where('approved',2)->count();
        $data = [
            'data-from' => 'vendor',
            'vendor' => $vendor,
            'withdraw_request'=>$withdrawRequests,
            'filter' => $request['status'],
            'searchValue'=> $request['searchValue'],
            'pending'=>$pendingRequest,
            'approved'=>$approvedRequest,
            'denied'=>$deniedRequest,
        ];
        return Excel::download(export: new VendorWithdrawRequest($data), fileName: Withdraw::EXPORT[FILE_NAME]);
    }
}
