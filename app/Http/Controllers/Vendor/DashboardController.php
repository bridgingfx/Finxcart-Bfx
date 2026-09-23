<?php

namespace App\Http\Controllers\Vendor;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\SellerBankRepositoryInterface;
use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Contracts\Repositories\WithdrawalMethodRepositoryInterface;
use App\Contracts\Repositories\WithdrawRequestRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Enums\OrderStatus;
use App\Enums\ViewPaths\Vendor\Dashboard;
use App\Http\Controllers\BaseController;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Http\Requests\Vendor\WithdrawRequest;
use App\Models\SellerWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Repositories\BrandRepository;
use App\Repositories\OrderTransactionRepository;
use App\Services\DashboardService;
use App\Services\VendorWalletService;
use App\Services\WithdrawRequestService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends BaseController
{
    public function __construct(
        private readonly OrderTransactionRepository          $orderTransactionRepo,
        private readonly ProductRepositoryInterface          $productRepo,
        private readonly DeliveryManRepositoryInterface      $deliveryManRepo,
        private readonly OrderRepositoryInterface            $orderRepo,
        private readonly CustomerRepositoryInterface         $customerRepo,
        private readonly BrandRepository                     $brandRepo,
        private readonly VendorWalletRepositoryInterface     $vendorWalletRepo,
        private readonly VendorWalletService                 $vendorWalletService,
        private readonly WithdrawalMethodRepositoryInterface $withdrawalMethodRepo,
        private readonly WithdrawRequestRepositoryInterface  $withdrawRequestRepo,
        private readonly WithdrawRequestService              $withdrawRequestService,
        private readonly SellerBankRepositoryInterface       $sellerBankRepo,
        private readonly DashboardService                    $dashboardService,
        private readonly RestockProductRepositoryInterface   $restockProductRepo,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     */
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getView();
    }

    /**
     * @return View
     */
    public function getView(): View
    {
        $vendorId = auth('seller')->id();
        $topSell = $this->productRepo->getTopSellList(
            filters: [
                'added_by' => 'seller',
                'seller_id' => $vendorId,
                'request_status' => 1
            ],
            dataLimit: DASHBOARD_TOP_SELL_DATA_LIMIT
        );
        $topRatedProducts = $this->productRepo->getTopRatedList(
            filters: [
                'user_id' => $vendorId,
                'added_by' => 'seller',
                'request_status' => 1
            ],
            dataLimit: DASHBOARD_DATA_LIMIT
        );
        $topRatedDeliveryMan = $this->deliveryManRepo->getTopRatedList(
            orderBy: ['delivered_orders_count' => 'desc'],
            filters: [
                'seller_id' => $vendorId
            ],
            whereHasFilters: [
                'seller_is' => 'seller',
                'seller_id' => $vendorId
            ],
            relations: ['deliveredOrders'],
        )->take(DASHBOARD_DATA_LIMIT);

        $from = now()->startOfYear()->format('Y-m-d');
        $to = now()->endOfYear()->format('Y-m-d');
        $range = range(1, 12);
        $vendorEarning = $this->getVendorEarning(from: $from, to: $to, range: $range, type: 'month');
        $commissionEarn = $this->getAdminCommission(from: $from, to: $to, range: $range, type: 'month');
        $vendorWallet = $this->vendorWalletRepo->getFirstWhere(params: ['seller_id' => $vendorId]);
        $label = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        $dateType = 'yearEarn';
        $dashboardData = [
            'orderStatus' => $this->getOrderStatusArray(type: 'overall'),
            'customers' => \App\Models\User::whereNot('email', 'walking@customer.com')->count(),
            'products' => \App\Models\Product::where('user_id', $vendorId)->where('added_by', 'seller')->count(),
            'orders' => Order::where('seller_id', $vendorId)->where('seller_is', 'seller')->count(),
            'brands' => \App\Models\Brand::count(),
            'topSell' => $topSell,
            'topRatedProducts' => $topRatedProducts,
            'topRatedDeliveryMan' => $topRatedDeliveryMan,
            'totalEarning' => $vendorWallet->total_earning ?? 0,
            'withdrawn' => $vendorWallet->withdrawn ?? 0,
            'pendingWithdraw' => $vendorWallet->pending_withdraw ?? 0,
            'adminCommission' => $vendorWallet->commission_given ?? 0,
            'deliveryManChargeEarned' => $vendorWallet->delivery_charge_earned ?? 0,
            'collectedCash' => $vendorWallet->collected_cash ?? 0,
            'collectedTotalTax' => $vendorWallet->total_tax_collected ?? 0,
        ];
        $withdrawalMethods = $this->withdrawalMethodRepo->getListWhere(filters: ['is_active' => 1], dataLimit: 'all');
        $sellerBanks = $this->sellerBankRepo->getListWhere(filters: ['seller_id' => $vendorId], dataLimit: 'all');
        return view(Dashboard::INDEX[VIEW], compact('dashboardData', 'vendorEarning', 'commissionEarn', 'withdrawalMethods', 'sellerBanks', 'dateType', 'label'));
    }

    /**
     * @param string $type
     * @return JsonResponse
     */
    public function getOrderStatus(string $type): JsonResponse
    {
        $orderStatus = $this->getOrderStatusArray($type);
        return response()->json([
            'view' => view(Dashboard::ORDER_STATUS[VIEW], compact('orderStatus'))->render()
        ], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEarningStatistics(Request $request): JsonResponse
    {
        $dateType = $request['type'];
        $dateTypeArray = $this->dashboardService->getDateTypeData(dateType: $dateType);
        $from = $dateTypeArray['from'];
        $to = $dateTypeArray['to'];
        $type = $dateTypeArray['type'];
        $range = $dateTypeArray['range'];
        $vendorEarning = $this->getVendorEarning(from: $from, to: $to, range: $range, type: $type);
        $commissionEarn = $this->getAdminCommission(from: $from, to: $to, range: $range, type: $type);
        $vendorEarning = array_values($vendorEarning);
        $commissionEarn = array_values($commissionEarn);
        $label = $dateTypeArray['keyRange'] ?? [];
        return response()->json([
            'view' => view(Dashboard::EARNING_STATISTICS[VIEW], compact('vendorEarning', 'commissionEarn', 'label', 'dateType'))->render(),
        ]);
    }

    /**
     * @param WithdrawRequest $request
     * @return RedirectResponse
     */
    public function getWithdrawRequest(WithdrawRequest $request): RedirectResponse
    {
        $seller = auth('seller')->user();

        if ($this->kycRequiredForPayout() && !$this->sellerKycApproved($seller)) {
            ToastMagic::error(translate('Complete_your_KYC_verification_before_requesting_a_payout'));
            return redirect()->back();
        }

        $vendorId = auth('seller')->id();
        $withdrawMethod = $this->withdrawalMethodRepo->getFirstWhere(params: ['id' => $request['withdraw_method']]);
        $wallet = $this->vendorWalletRepo->getFirstWhere(params: ['seller_id' => auth('seller')->id()]);
        $minWithdraw = (float) (getWebConfig('minimum_withdrawal_amount') ?? 20);
        if ($request['amount'] < $minWithdraw) {
            ToastMagic::error(translate('minimum_withdrawal_amount_is') . ' ' . setCurrencySymbol(amount: $minWithdraw));
            return redirect()->back();
        }
        $amount = currencyConverter($request['amount']);
        $createdWithdrawRequest = null;
        // PAYOUT-01: perform the balance check and wallet deduction atomically under a
        // row lock so two concurrent requests cannot both pass the check and over-withdraw.
        $success = DB::transaction(function () use ($vendorId, $amount, $withdrawMethod, $request, &$createdWithdrawRequest) {
            $lockedWallet = SellerWallet::where('seller_id', $vendorId)->lockForUpdate()->first();
            if (!$lockedWallet || ($lockedWallet->total_earning ?? 0) < $amount || $request['amount'] <= 0) {
                return false;
            }
            $createdWithdrawRequest = $this->withdrawRequestRepo->add($this->withdrawRequestService->getWithdrawRequestData(
                withdrawMethod: $withdrawMethod,
                request: $request,
                addedBy: 'vendor',
                vendorId: $vendorId
            ));
            $this->vendorWalletRepo->update(
                id: $lockedWallet->id,
                data: $this->vendorWalletService->getVendorWalletData(
                    totalEarning: $lockedWallet->total_earning - $amount,
                    pendingWithdraw: $lockedWallet->pending_withdraw + $amount
                )
            );
            return true;
        });
        if ($success) {
            $this->notifyAdminOfWithdrawRequest($seller, $request['amount'], $createdWithdrawRequest?->id);
            ToastMagic::success(translate('withdraw_request_has_been_sent'));
        } else {
            ToastMagic::error(translate('invalid_request') . '!');
        }
        return redirect()->back();
    }

    private function notifyAdminOfWithdrawRequest($seller, $amount, $withdrawRequestId = null): void
    {
        try {
            $name = trim(($seller->f_name ?? '') . ' ' . ($seller->l_name ?? '')) ?: $seller->email;
            AdminNotification::create([
                'type' => 'withdraw_request',
                'title' => 'New Withdrawal Request',
                'message' => "{$name} requested a withdrawal of " . setCurrencySymbol(amount: $amount) . '.',
                'link' => route('admin.vendors.withdraw_list'),
                'reference_id' => $seller->id,
            ]);
        } catch (\Throwable $e) {
            \Log::error('[Vendor DashboardController] Admin notification failed: ' . $e->getMessage());
        }

        if (!$withdrawRequestId) {
            return;
        }

        // Deferred past the response since mail sends synchronously (no queue worker configured).
        dispatch(function () use ($withdrawRequestId) {
            try {
                $companyEmail = \App\Models\BusinessSetting::where('type', 'company_email')->first()?->value;
                if (!$companyEmail) {
                    return;
                }
                $freshWithdraw = \App\Models\WithdrawRequest::with(['seller', 'seller.shop'])->find($withdrawRequestId);
                if ($freshWithdraw) {
                    Mail::to($companyEmail)->send(new \App\Mail\NewWithdrawRequestMail($freshWithdraw));
                }
            } catch (\Throwable $e) {
                \Log::warning('[Vendor DashboardController] Admin withdraw-request email failed: ' . $e->getMessage());
            }
        })->afterResponse();
    }

    private function kycRequiredForPayout(): bool
    {
        return (bool) getWebConfig('kyc_required_for_payout')
            && (getWebConfig('kyc_method') ?? 'manual') !== 'disabled';
    }

    private function sellerKycApproved($seller): bool
    {
        return ($seller?->kyc_status ?? null) === 'approved';
    }

    /**
     * @param string $type
     * @return array
     */
    protected function getOrderStatusArray(string $type): array
    {
        $vendorId = auth('seller')->id();

        $query = Order::where('seller_is', 'seller')->where('seller_id', $vendorId);

        if ($type === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($type === 'thisMonth') {
            $query->whereMonth('created_at', Carbon::now());
        }

        $counts = $query->selectRaw('order_status, COUNT(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        $statusWiseOrders = [];
        foreach (OrderStatus::LIST as $key) {
            $statusWiseOrders[$key] = $counts->get($key, 0);
        }
        return $statusWiseOrders;
    }

    /**
     * @param string|Carbon $from
     * @param string|Carbon $to
     * @param array $range
     * @param string $type
     * @return array
     */
    protected function getVendorEarning(string|Carbon $from, string|Carbon $to, array $range, string $type): array
    {
        $vendorId = auth('seller')->id();
        $vendorEarnings = $this->orderTransactionRepo->getListWhereBetween(
            filters: [
                'seller_is' => 'seller',
                'seller_id' => $vendorId,
                'status' => 'disburse',
            ],
            selectColumn: 'seller_amount',
            whereBetween: 'created_at',
            whereBetweenFilters: [$from, $to],
            groupBy: $type,
        );
        return $this->dashboardService->getDateWiseAmount(range: $range, type: $type, amountArray: $vendorEarnings);
    }

    /**
     * @param string|Carbon $from
     * @param string|Carbon $to
     * @param array $range
     * @param string $type
     * @return array
     */
    protected function getAdminCommission(string|Carbon $from, string|Carbon $to, array $range, string $type): array
    {
        $vendorId = auth('seller')->id();
        $commissionGiven = $this->orderTransactionRepo->getListWhereBetween(
            filters: [
                'seller_is' => 'seller',
                'seller_id' => $vendorId,
                'status' => 'disburse',
            ],
            selectColumn: 'admin_commission',
            whereBetween: 'created_at',
            whereBetweenFilters: [$from, $to],
            groupBy: $type,
        );
        return $this->dashboardService->getDateWiseAmount(range: $range, type: $type, amountArray: $commissionGiven);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getMethodList(Request $request): JsonResponse
    {
        $method = $this->withdrawalMethodRepo->getFirstWhere(params: ['id' => $request['method_id'], 'is_active' => 1]);
        return response()->json(['content' => $method], 200);
    }

    public function getRealTimeActivities(): JsonResponse
    {
        try {
            // Polled every 5s by every open vendor tab — a direct SQL count instead
            // of fetching every unchecked order row just to count them.
            $newOrder = Order::where([
                'seller_is' => 'seller',
                'seller_id' => auth('seller')->id(),
                'checked' => 0,
            ])->count();

            // Polled every 5s by every open vendor tab — this used to fetch every
            // restock-product row (with product eager-loaded) via dataLimit:'all' just
            // to check whether there were 0, 1, or "more than 1" distinct products.
            // Get the distinct-product count cheaply first, and only do the fuller
            // (still bounded) fetch when the single-product branch actually needs the
            // product's name/thumbnail detail.
            $restockProductCount = (int) $this->restockProductRepo->getListWhere(
                filters: ['added_by' => 'seller', 'seller_id' => auth('seller')->id()],
                dataLimit: 'all'
            )->pluck('product_id')->unique()->count();

            $restockProduct = [];
            if ($restockProductCount == 1) {
                $products = $this->restockProductRepo->getListWhere(filters: ['added_by' => 'seller', 'seller_id' => auth('seller')->id()], relations: ['product'], dataLimit: 'all');
                $firstProduct = $products->first();

                $count = $products->sum('restock_product_customers_count');
                $restockProduct = [
                    'title' => $firstProduct?->product?->name ?? '',
                    'body' => $count < 100 ? translate('This_product_has') . ' ' . $count . ' ' . translate('restock_request') : translate('This_product_has') . ' 99+ ' . translate('restock_request'),
                    'image' => getStorageImages(path: $firstProduct?->product?->thumbnail_full_url ?? '', type: 'product'),
                    'route' => route('vendor.products.request-restock-list')
                ];
            } elseif ($restockProductCount > 1) {
                $restockProduct = [
                    'title' => translate('Restock_Request'),
                    'body' => ($restockProductCount < 100 ? $restockProductCount : '99 +') . ' ' . translate('more_products_have_restock_request'),
                    'image' => dynamicAsset(path: 'public/assets/back-end/img/icons/restock-request-icon.svg'),
                    'route' => route('vendor.products.request-restock-list')
                ];
            }

            return response()->json([
                'success' => 1,
                'new_order_count' => $newOrder,
                'restockProductCount' => $restockProductCount,
                'restockProduct' => $restockProduct
            ]);
        } catch (\Throwable $e) {
            \Log::error('getRealTimeActivities failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => 0], 500);
        }
    }
}
