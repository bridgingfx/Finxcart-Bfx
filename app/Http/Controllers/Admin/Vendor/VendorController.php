<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\DeliveryZipCodeRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\OrderTransactionRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Contracts\Repositories\ShippingAddressRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Contracts\Repositories\StockClearanceProductRepositoryInterface;
use App\Contracts\Repositories\StockClearanceSetupRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Contracts\Repositories\WithdrawRequestRepositoryInterface;
use App\Enums\ExportFileNames\Admin\Vendor as VendorExport;
use App\Enums\ViewPaths\Admin\Vendor;
use App\Enums\WebConfigKey;
use App\Events\VendorRegistrationEvent;
use App\Events\WithdrawStatusUpdateEvent;
use App\Exports\VendorListExport;
use App\Exports\VendorWithdrawRequest;
use App\Exports\VendorOrderListExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\VendorAddRequest;
use App\Services\ProductService;
use App\Services\ShopService;
use App\Services\VendorService;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\FlashDealProduct;
use App\Models\DealOfTheDay;
use App\Models\Translation;
use App\Models\Order;
use App\Models\Review;
use App\Models\SellerWallet;
use App\Models\Coupon;
use App\Models\Chatting;
use App\Models\CategoryShippingCost;
use App\Models\CommissionLedger;
use App\Models\Storage;
use App\Models\Seller;
use App\Traits\CommonTrait;
use App\Traits\EmailTemplateTrait;
use App\Traits\FileManagerTrait;
use App\Traits\PaginatorTrait;
use App\Traits\PushNotificationTrait;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Models\VendorTier;
use App\Models\VendorVerification;
use App\Models\VendorNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\WithdrawRequestStatusMail;

class VendorController extends BaseController
{
    use PaginatorTrait;
    use CommonTrait;
    use PushNotificationTrait;
    use EmailTemplateTrait;
    use FileManagerTrait;

    public function __construct(
        private readonly VendorRepositoryInterface           $vendorRepo,
        private readonly OrderRepositoryInterface            $orderRepo,
        private readonly ProductRepositoryInterface          $productRepo,
        private readonly ReviewRepositoryInterface           $reviewRepo,
        private readonly DeliveryManRepositoryInterface      $deliveryManRepo,
        private readonly OrderTransactionRepositoryInterface $orderTransactionRepo,
        private readonly ShippingAddressRepositoryInterface  $shippingAddressRepo,
        private readonly DeliveryZipCodeRepositoryInterface  $deliveryZipCodeRepo,
        private readonly WithdrawRequestRepositoryInterface  $withdrawRequestRepo,
        private readonly VendorWalletRepositoryInterface     $vendorWalletRepo,
        private readonly ShopRepositoryInterface             $shopRepo,
        private readonly VendorService                       $vendorService,
        private readonly ShopService                         $shopService,
        private readonly ProductService                      $productService,
        private readonly StockClearanceProductRepositoryInterface $stockClearanceProductRepo,
        private readonly StockClearanceSetupRepositoryInterface   $stockClearanceSetupRepo,
    )
    {
    }

    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }

    public function getListView(Request $request): View
    {
        $current_date = date('Y-m-d');
        $vendors = $this->vendorRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['exclude_seller_type' => 'freelancer'],
            relations: [
                'shop:id,seller_id,name,image,image_storage_type,temporary_close,vacation_status,vacation_start_date,vacation_end_date',
                'vendorVerification:id,seller_id,status',
                'product',
                'orders_filtered',
            ],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );
        return view(Vendor::LIST[VIEW], compact('vendors', 'current_date'));
    }

    public function getAddView(Request $request): View
    {
        return view(Vendor::ADD[VIEW]);
    }

    public function add(VendorAddRequest $request): JsonResponse
    {
        $companyLicense = null;
        $personalIdDocument = null;

        if ($request['seller_type'] === 'company' && $request->hasFile('company_license')) {
            $companyLicense = $this->fileUpload(
                dir: 'vendor-verifications/',
                format: $request->file('company_license')->getClientOriginalExtension(),
                file: $request->file('company_license')
            );
        }

        if ($request['seller_type'] === 'individual' && $request->hasFile('personal_id_document')) {
            $personalIdDocument = $this->fileUpload(
                dir: 'vendor-verifications/',
                format: $request->file('personal_id_document')->getClientOriginalExtension(),
                file: $request->file('personal_id_document')
            );
        }

        $vendor = DB::transaction(function () use ($request, $companyLicense, $personalIdDocument) {
            $vendor = $this->vendorRepo->add(data: $this->vendorService->getAddData($request, source: 'admin'));
            $this->shopRepo->add($this->shopService->getAddShopDataForRegistration(request: $request, vendorId: $vendor['id']));
            $this->vendorWalletRepo->add($this->vendorService->getInitialWalletData(vendorId: $vendor['id']));

            VendorVerification::create([
                'seller_id' => $vendor['id'],
                'seller_type' => $request['seller_type'],
                'company_website' => $request['seller_type'] === 'company' ? $request['company_website'] : null,
                'company_no' => $request['seller_type'] === 'company' ? $request['company_no'] : null,
                'company_license' => $companyLicense,
                'company_registered_country' => $request['seller_type'] === 'company' ? $request['company_registered_country'] : $request['country'],
                'personal_id_document' => $personalIdDocument,
                'personal_name' => $request['seller_type'] === 'individual' ? trim($request['f_name'] . ' ' . $request['l_name']) : null,
                'sell_description' => $request['seller_type'] === 'individual' ? $request['sell_description'] : null,
                'status' => 'pending',
                'rejection_reason' => null,
            ]);

            return $vendor;
        });

        $data = [
            'name' => $request['f_name'] . ' ' . $request['l_name'],
            'vendorName' => $request['f_name'] . ' ' . $request['l_name'],
            'sellerType' => ucfirst($request['seller_type']),
            'seller_type' => $request['seller_type'],
            'message' => 'You have been registered as a vendor on Finxcart. Your credentials have been submitted for review. Please login to check your status.',
            'status' => 'pending',
            'subject' => translate('Verification_Submitted'),
            'title' => translate('Verification_Submitted'),
            'userType' => 'vendor',
            'templateName' => 'vendor-verification-submitted',
        ];
        try {
            event(new VendorRegistrationEvent(email: $request['email'], data: $data));
        } catch (\Throwable $e) {
            \Log::error('Admin vendor registration email failed: ' . $e->getMessage());
        }
        return response()->json(['message' => translate('vendor_added_successfully')]);
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $vendor = $this->vendorRepo->getFirstWhere(params: ['id' => $request['id']]);
        $updateData = ['status' => $request['status']];
        if (!empty($request['admin_remark'])) {
            $updateData['admin_remark']    = $request['admin_remark'];
            $updateData['admin_remark_at'] = now();
        }
        $this->vendorRepo->update(id: $request['id'], data: $updateData);
        if ($request['status'] == "approved") {
            ToastMagic::success(translate('Vendor_has_been_approved_successfully'));
        } else if ($request['status'] == "rejected") {
            ToastMagic::info(translate('Vendor_has_been_rejected_successfully'));
        }
        $data = null;

        if ($vendor['status'] == 'pending') {
            if ($request['status'] == "approved") {
                $data = [
                    'vendorName'   => $vendor['f_name'],
                    'status'       => 'approved',
                    'subject'      => translate('Vendor_Registration_Approved'),
                    'title'        => translate('Vendor_Registration_Approved'),
                    'userType'     => 'vendor',
                    'templateName' => 'registration-approved',
                ];
            } elseif ($request['status'] == "rejected") {
                $data = [
                    'vendorName'   => $vendor['f_name'],
                    'status'       => 'denied',
                    'subject'      => translate('Vendor_Registration_Denied'),
                    'title'        => translate('Vendor_Registration_Denied'),
                    'userType'     => 'vendor',
                    'templateName' => 'registration-denied',
                ];
            }
        } elseif ($request['status'] == "approved") {
            $data = [
                'vendorName'   => $vendor['f_name'],
                'status'       => 'approved',
                'subject'      => translate('Account_Activate'),
                'title'        => translate('Account_Activate'),
                'userType'     => 'vendor',
                'templateName' => 'account-activation',
            ];
            // rejected from non-pending (e.g. toggled back) — no email, remark shown on dashboard
        }

        if ($data !== null) {
            try {
                event(new VendorRegistrationEvent(email: $vendor['email'], data: $data));
            } catch (\Throwable $e) {
                \Log::warning('Vendor status email failed: ' . $e->getMessage());
            }
        }

        return back();
    }

    public function updateAccountStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required|integer|regex:/^\d+$/|exists:sellers,id',
            'account_status' => 'required|in:active,inactive',
        ]);

        $vendor = $this->vendorRepo->getFirstWhere(params: ['id' => $request['id']]);
        $updateData = ['account_status' => $request['account_status']];

        if ($request['account_status'] === 'inactive') {
            $updateData['auth_token'] = Str::random(80);
        }

        $this->vendorRepo->update(id: $request['id'], data: $updateData);
        cacheRemoveByType(type: 'products');

        if ($request['account_status'] === 'inactive') {
            ToastMagic::error(translate('Vendor_account_has_been_suspended_successfully'));
        } else {
            ToastMagic::success(translate('Vendor_account_has_been_reactivated_successfully'));
        }

        $data = [
            'vendorName'   => $vendor['f_name'],
            'status'       => $request['account_status'],
            'subject'      => $request['account_status'] === 'inactive' ? translate('Account_Suspended') : translate('Account_Reactivated'),
            'title'        => $request['account_status'] === 'inactive' ? translate('Account_Suspended') : translate('Account_Reactivated'),
            'userType'     => 'vendor',
            'templateName' => $request['account_status'] === 'inactive' ? 'account-suspended' : 'account-activation',
        ];

        try {
            event(new VendorRegistrationEvent(email: $vendor['email'], data: $data));
        } catch (\Throwable $e) {
            \Log::warning('Vendor account status email failed: ' . $e->getMessage());
        }

        return back();
    }

    public function destroy(Request $request): JsonResponse
    {
        $seller = $this->vendorRepo->getFirstWhere(params: ['id' => $request['id']]);

        if (!$seller) {
            return response()->json(['message' => translate('vendor_not_found_It_may_be_deleted')], 404);
        }

        DB::transaction(function () use ($seller) {
            $sellerId = $seller->id;

            $productIds = Product::where('user_id', $sellerId)->where('added_by', 'seller')->pluck('id');

            Cart::whereIn('product_id', $productIds)->delete();
            Wishlist::whereIn('product_id', $productIds)->delete();
            FlashDealProduct::whereIn('product_id', $productIds)->delete();
            DealOfTheDay::whereIn('product_id', $productIds)->delete();
            Review::whereIn('product_id', $productIds)->delete();
            Translation::where('translationable_type', Product::class)->whereIn('translationable_id', $productIds)->delete();
            DB::table('banners')->where('resource_type', 'product')->whereIn('resource_id', $productIds)
                ->update(['published' => 0, 'resource_id' => null]);

            foreach (Product::where('user_id', $sellerId)->where('added_by', 'seller')->get() as $product) {
                $this->productService->deleteImages(product: $product);
            }
            Product::where('user_id', $sellerId)->where('added_by', 'seller')->delete();

            // Orders (and order_details/order_transactions) are deliberately NOT deleted
            // when a vendor is removed — only customer deletion removes order history.
            // The vendor's orders are left in place for accounting/reporting continuity,
            // with seller_id pointing to the now-deleted seller's old id.

            DB::table('seller_wallet_histories')->where('seller_id', $sellerId)->delete();
            SellerWallet::where('seller_id', $sellerId)->delete();

            DB::table('withdraw_requests')->where('seller_id', $sellerId)->delete();
            DB::table('notification_seens')->where('seller_id', $sellerId)->delete();

            Coupon::where('seller_id', $sellerId)->delete();
            VendorTier::where('seller_id', $sellerId)->delete();
            Chatting::where('seller_id', $sellerId)->delete();
            CategoryShippingCost::where('seller_id', $sellerId)->delete();
            CommissionLedger::where('seller_id', $sellerId)->delete();

            $verification = VendorVerification::where('seller_id', $sellerId)->first();
            if ($verification) {
                foreach (['company_license', 'personal_id_document', 'payout_kyc_document'] as $docField) {
                    if (!empty($verification->$docField)) {
                        $this->delete('vendor-verifications/' . $verification->$docField);
                    }
                }
                $verification->delete();
            }

            $shop = $seller->shop;
            if ($shop) {
                if (!empty($shop->image) && $shop->image !== 'def.png') {
                    $this->delete('shop/' . $shop->image);
                }
                if (!empty($shop->banner) && $shop->banner !== 'def.png') {
                    $this->delete('shop/banner/' . $shop->banner);
                }
                $shop->delete();
            }

            Storage::where('data_type', Seller::class)->where('data_id', $sellerId)->delete();

            if (!empty($seller->image) && $seller->image !== 'def.png') {
                $this->delete('seller/' . $seller->image);
            }

            $seller->delete();
        });

        cacheRemoveByType(type: 'products');

        return response()->json(['message' => translate('vendor_deleted_successfully')]);
    }

    public function exportList(Request $request): BinaryFileResponse
    {
        $vendors = $this->vendorRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['exclude_seller_type' => 'freelancer'],
            relations: ['orders', 'product'],
            dataLimit: 'all'
        );

        $active = $vendors->where('status', 'approved')->count();
        $inactive = $vendors->where('status', '!=', 'approved')->count();
        $data = [
            'vendors' => $vendors,
            'search' => $request['searchValue'],
            'active' => $active,
            'inactive' => $inactive,
        ];
        return Excel::download(new VendorListExport($data), VendorExport::EXPORT_XLSX);
    }

    public function exportOrderList(Request $request, $vendorId): BinaryFileResponse
    {
        $shop = $this->shopRepo->getFirstWhere(params: ['seller_id' => $vendorId]);
        $orders = $this->orderRepo->getListWhere(orderBy: ['id' => 'desc'], searchValue: $request['searchValue'], filters: ['seller_id' => $vendorId, 'seller_is' => 'seller'], dataLimit: 'all');
        $statusArray = [
            'pending' => 0,
            'confirmed' => 0,
            'processing' => 0,
            'out_for_delivery' => 0,
            'delivered' => 0,
            'returned' => 0,
            'failed' => 0,
            'canceled' => 0,
        ];
        $orders?->map(function ($order) use (&$statusArray) { // Pass by reference using &
            if (isset($statusArray[$order->order_status])) {
                $statusArray[$order->order_status]++;
            }
        });
        $data = [
            'shop' => $shop,
            'statusArray' => $statusArray,
            'orders' => $orders,
        ];
        return Excel::download(new VendorOrderListExport($data), VendorExport::ORDER_LIST_EXPORT);
    }

    public function updateSalesCommission(Request $request, $id): RedirectResponse
    {
        if ($request['status'] == 1 && $request['commission'] == null) {
            ToastMagic::error(translate('you_did_not_set_commission_percentage_field'));
            return back();
        }
        $this->vendorRepo->update(id: $id, data: ['sales_commission_percentage' => $request['commission_status'] == 1 ? $request['commission'] : null]);
        ToastMagic::success(translate('Commission_percentage_for_this_seller_has_been_updated'));
        return back();
    }

    public function getOrderDetailsView($order_id, $seller_id): View
    {
        $country_restrict_status = getWebConfig(name: 'delivery_country_restriction');
        $zip_restrict_status = getWebConfig(name: 'delivery_zip_code_area_restriction');

        $countries = $country_restrict_status ? $this->get_delivery_country_array() : COUNTRIES;
        $zip_codes = $zip_restrict_status ? $this->deliveryZipCodeRepo->getListWhere(dataLimit: 'all') : 0;

        $order = $this->orderRepo->getFirstWhere(
            params: ['id' => $order_id],
            relations: ['shipping', 'customer', 'details.product'],
        );

        $physical_product = false;
        foreach ($order->details as $product) {
            if (isset($product->product) && $product->product->product_type == 'physical') {
                $physical_product = true;
            }
        }

        $shipping_method = getWebConfig(name: 'shipping_method');

        $delivery_men = $this->deliveryManRepo->getListWhereIn(
            filters: ['is_active' => 1, 'order_seller' => $order['seller_is'], 'seller_id' => $order['seller_id'], 'shipping_method' => $shipping_method],
            dataLimit: 'all',
        );

        $shipping_address = $this->shippingAddressRepo->getFirstWhere(params: ['id' => $order['shipping_address']]);
        $total_delivered = $this->orderRepo->getListWhere(
            filters: ['seller_id' => $order['seller_id'], 'seller_is' => 'seller', 'order_status' => 'delivered', 'order_type' => 'default_type'],
            dataLimit: 'all',
        )->count();

        $linked_orders = $this->orderRepo->getListWhereNotIn(
            filters: ['order_group_id' => $order['order_group_id']],
            whereNotIn: ['order_group_id' => ['def-order-group'], 'id' => [$order['id']]],
            dataLimit: 'all',
        );
        if ($order['order_type'] == 'default_type') {
            $orderCount = $this->orderRepo->getListWhereCount(filters: ['customer_id' => $order['customer_id']]);
        } else {
            $orderCount = $this->orderRepo->getListWhereCount(filters: ['customer_id' => $order['customer_id'], 'order_type' => 'POS']);
        }
        return view(Vendor::ORDER_DETAILS[VIEW], compact('order', 'seller_id', 'delivery_men', 'linked_orders', 'physical_product',
            'shipping_address', 'total_delivered', 'countries', 'zip_codes', 'zip_restrict_status', 'country_restrict_status', 'orderCount'));
    }

    // --- THIS IS THE UPDATED getView METHOD ---
    public function getView(Request $request, $id, $tab = null): View|RedirectResponse
    {
        $seller = $this->vendorRepo->getFirstWhere(
            params: ['id' => $id, 'withCount' => ['product', 'orders' => function ($query) use ($id) {
                $query->where(['seller_id' => $id, 'seller_is' => ($id == 0 ? 'admin' : 'seller')]);
            }]],
            relations: ['orders', 'product.reviews', 'vendorVerification']
        );

        if (!$seller) {
            return redirect()->route('admin.vendors.vendor-list');
        }
        $seller?->product?->map(function ($product) {
            $product['rating'] = $product?->reviews->pluck('rating')->sum();
            $product['rating_count'] = $product->reviews->count();
            $product['single_rating_5'] = 0;
            $product['single_rating_4'] = 0;
            $product['single_rating_3'] = 0;
            $product['single_rating_2'] = 0;
            $product['single_rating_1'] = 0;
            foreach ($product->reviews as $review) {
                $rating = $review->rating;
                if ($rating > 0) {
                    match ($rating) {
                        5 => $product->single_rating_5++,
                        4 => $product->single_rating_4++,
                        3 => $product->single_rating_3++,
                        2 => $product->single_rating_2++,
                        1 => $product->single_rating_1++,
                    };
                }
            }
        });
        $seller['single_rating_5'] = $seller?->product->pluck('single_rating_5')->sum();
        $seller['single_rating_4'] = $seller?->product->pluck('single_rating_4')->sum();
        $seller['single_rating_3'] = $seller?->product->pluck('single_rating_3')->sum();
        $seller['single_rating_2'] = $seller?->product->pluck('single_rating_2')->sum();
        $seller['single_rating_1'] = $seller?->product->pluck('single_rating_1')->sum();
        $seller['total_rating'] = $seller?->product->pluck('rating')->sum();
        $seller['rating_count'] = $seller->product->pluck('rating_count')->sum();
        $seller['average_rating'] = $seller['total_rating'] / ($seller['rating_count'] == 0 ? 1 : $seller['rating_count']);

        if (!isset($seller)) {
            ToastMagic::error(translate('vendor_not_found_It_may_be_deleted'));
            return back();
        }

        // --- START OF NEW TAB LOGIC ---
        if ($tab == 'tier_plan') {
            return $this->getTierPlanTabView(request: $request, seller: $seller);
        }
        // --- END OF NEW TAB LOGIC ---

        if ($tab == 'order') {
            return $this->getOrderListTabView(request: $request, seller: $seller);
        } else if ($tab == 'product') {
            return $this->getProductListTabView(request: $request, seller: $seller);
        } else if ($tab == 'setting') {
            return $this->getSettingListTabView(request: $request, seller: $seller, id: $id);
        } else if ($tab == 'transaction') {
            return $this->getTransactionListTabView(request: $request, seller: $seller);
        } else if ($tab == 'review') {
            return $this->getReviewListTabView(request: $request, seller: $seller);
        } else if ($tab == 'clearance_sale') {
            return $this->getClearanceSaleTabView(request: $request, seller: $seller);
        }

        return view(Vendor::VIEW[VIEW], [
            'seller' => $seller,
            'current_date' => date('Y-m-d'),
        ]);
    }

    public function getOrderListTabView(Request $request, $seller): View
    {
        $orders = $this->orderRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['seller_id' => $seller['id'], 'seller_is' => 'seller', 'order_type' => 'default_type'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
        );
        $pendingOrder = $this->orderRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['seller_id' => $seller['id'], 'seller_is' => 'seller', 'order_type' => 'default_type', 'order_status' => 'pending'],
            dataLimit: 'all',
        )->count();
        $deliveredOrder = $this->orderRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['seller_id' => $seller['id'], 'seller_is' => 'seller', 'order_type' => 'default_type', 'order_status' => 'delivered'],
            dataLimit: 'all',
        )->count();

        return view(Vendor::VIEW_ORDER[VIEW], compact('seller', 'orders', 'pendingOrder', 'deliveredOrder'));
    }

    public function getProductListTabView(Request $request, $seller): View
    {
        $products = $this->productRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['seller_id' => $seller['id'], 'added_by' => 'seller'],
            relations: ['translations'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );
        return view(Vendor::VIEW_PRODUCT[VIEW], compact('seller', 'products'));
    }

    public function getSettingListTabView(Request $request, $seller, $id): View
    {
        return view(Vendor::VIEW_SETTING[VIEW], compact('seller'));
    }

    public function updateSetting(Request $request, $id): RedirectResponse
    {
        if ($request->has('commission')) {
            request()->validate([
                'commission' => 'required|numeric|min:1|regex:/^\d+(\.\d+)?$/',
            ]);
            if ($request['commission_status'] == 1 && $request['commission'] == null) {
                ToastMagic::error(translate('you_did_not_set_commission_percentage_field.'));
            } else {
                $this->vendorRepo->update(id: $id, data: ['sales_commission_percentage' => $request['commission_status'] == 1 ? $request['commission'] : null]);
                ToastMagic::success(translate('commission_percentage_for_this_vendor_has_been_updated.'));
            }
        }
        if ($request->has('gst')) {
            if ($request['gst_status'] == 1 && $request['gst'] == null) {
                ToastMagic::error(translate('you_did_not_set_GST_number_field.'));
            } else {
                $this->vendorRepo->update(id: $id, data: ['gst' => $request['gst_status'] == 1 ? $request['gst'] : null]);
                ToastMagic::success(translate('GST_number_for_this_vendor_has_been_updated.'));
            }
        }
        if ($request->has('seller_pos_update')) {
            $this->vendorRepo->update(id: $id, data: ['pos_status' => $request->get('seller_pos', 0)]);
            ToastMagic::success(translate('vendor_pos_permission_updated'));
        }
        return redirect()->back();
    }

    public function getTransactionListTabView(Request $request, $seller): View
    {
        $filters = [
            'seller_is' => 'seller',
            'seller_id' => $seller['id'],
            'status' => $request['status'] ?? 'all'

        ];
        $transactions = $this->orderTransactionRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: $filters,
            relations: ['order.customer'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
        );
        return view(Vendor::VIEW_TRANSACTION[VIEW], compact('seller', 'transactions'));
    }

    public function getReviewListTabView(Request $request, $seller): View
    {
        if ($request->has('searchValue')) {
            $product_id = $this->productRepo->getListWhere(
                searchValue: $request['searchValue'],
                filters: ['added_by' => 'seller', 'seller_id' => $seller['id']],
                dataLimit: 'all')->pluck('id')->toArray();
            $filtersBy = [
                'product_id' => !empty($product_id) ? $product_id : [0],
            ];

            $reviews = $this->reviewRepo->getListWhereIn(
                orderBy: ['id' => 'desc'],
                filters: ['added_by' => 'seller', 'product_user_id' => $seller['id']],
                whereInFilters: $filtersBy,
                relations: ['product'],
                nullFields: ['delivery_man_id'],
                dataLimit: getWebConfig(name: 'pagination_limit'));
        } else {
            $reviews = $this->reviewRepo->getListWhereIn(
                orderBy: ['id' => 'desc'],
                filters: ['added_by' => 'seller', 'product_user_id' => $seller['id']],
                relations: ['product'],
                dataLimit: getWebConfig(name: 'pagination_limit'));
        }
        return view(Vendor::VIEW_REVIEW[VIEW], [
            'seller' => $seller,
            'reviews' => $reviews,
        ]);
    }

    public function getClearanceSaleTabView(Request $request, $seller): View
    {
        $searchValue = $request['searchValue'] ?? null;
        $clearanceConfig = $this->stockClearanceSetupRepo->getFirstWhere(params: ['setup_by' => 'vendor', 'user_id' => $seller->id]);
        $stockClearanceProduct = $this->stockClearanceProductRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $searchValue,
            filters: ['added_by' => 'vendor', 'user_id' => $seller->id],
            relations: ['product']
        );
        return view(Vendor::VIEW_CLEARANCE_SALE[VIEW], [
            'seller' => $seller,
            'stockClearanceProduct' => $stockClearanceProduct,
            'clearanceConfig' => $clearanceConfig,
        ]);
    }

    public function getWithdrawView($withdrawId, $vendorId): View|RedirectResponse
    {
        $withdrawRequest = $this->withdrawRequestRepo->getFirstWhere(params: ['id' => $withdrawId], relations: ['seller', 'seller.shop']);
        if ($withdrawRequest) {
            $withdrawalMethod = is_array($withdrawRequest['withdrawal_method_fields']) ? $withdrawRequest['withdrawal_method_fields'] : json_decode($withdrawRequest['withdrawal_method_fields']);
            $direction = session('direction');
            return view(Vendor::WITHDRAW_VIEW[VIEW], compact('withdrawRequest', 'withdrawalMethod', 'direction'));
        }
        ToastMagic::error(translate('withdraw_request_not_found'));
        return back();
    }

    public function getWithdrawListView(Request $request): View
    {
        $withdrawRequests = $this->withdrawRequestRepo->getListWhereNull(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['approved' => $request['approved']],
            nullFilters: ['delivery_man_id'],
            relations: ['seller', 'withdrawMethod'],
            dataLimit: getWebConfig(name: 'pagination_limit')
        );
        return view(Vendor::WITHDRAW_LIST[VIEW], compact('withdrawRequests'));
    }

    public function exportWithdrawList(Request $request): BinaryFileResponse
    {
        $withdrawRequests = $this->withdrawRequestRepo->getListWhereNull(
            orderBy: ['id' => 'desc'],
            filters: ['approved' => $request['approved']],
            nullFilters: ['delivery_man_id'],
            relations: ['seller.shop', 'withdrawMethod'],
            dataLimit: 'all'
        );

        $withdrawRequests->map(function ($query) {
            $query->shop_name = isset($query->seller) ? $query->seller->shop->name : '';
            $query->shop_phone = isset($query->seller) ? $query->seller->shop->contact : '';
            $query->shop_address = isset($query->seller) ? $query->seller->shop->address : '';
            $query->shop_email = isset($query->seller) ? $query->seller->email : '';
            $query->withdrawal_amount = setCurrencySymbol(amount: usdToDefaultCurrency(amount: $query->amount), currencyCode: getCurrencyCode(type: 'default'));
            $query->status = $query->approved == 0 ? 'Pending' : ($query->approved == 1 ? 'Approved' : 'Denied');
            $query->note = $query->transaction_note;
            $query->withdraw_method_name = isset($query->withdrawMethod) ? $query->withdrawMethod->method_name : '';
            if (!empty($query->withdrawal_method_fields)) {
                $withdrawal_method_fields = is_array($query->withdrawal_method_fields) ? $query->withdrawal_method_fields : json_decode($query->withdrawal_method_fields);
                foreach ($withdrawal_method_fields as $key => $field) {
                    $query[$key] = $field;
                }
            }
        });

        $pending = $withdrawRequests->where('approved', 0)->count();
        $approved = $withdrawRequests->where('approved', 1)->count();
        $denied = $withdrawRequests->where('approved', 2)->count();

        return Excel::download(new VendorWithdrawRequest([
            'data-from' => 'admin',
            'withdraw_request' => $withdrawRequests,
            'filter' => session('withdraw_status_filter'),
            'pending' => $pending,
            'approved' => $approved,
            'denied' => $denied,
        ]), 'Vendor-Withdraw-Request.xlsx'
        );
    }


    public function withdrawStatus(Request $request, $id): RedirectResponse
    {
        $withdraw = $this->withdrawRequestRepo->getFirstWhere(params: ['id' => $id], relations: ['seller', 'seller.shop']);

        $withdrawData = [
            'approved'         => $request['approved'],
            'transaction_note' => $request['note'],
        ];

        if (isset($withdraw->seller->cm_firebase_token) && $withdraw->seller->cm_firebase_token) {
            event(new WithdrawStatusUpdateEvent(key: 'withdraw_request_status_message', type: 'seller', lang: $withdraw->deliveryMan?->app_language ?? getDefaultLanguage(), status: $request['approved'], fcmToken: $withdraw->seller?->cm_firebase_token));
        }

        if ($request['approved'] == 1) {
            $withdrawData['approved_at']      = now();
            $withdrawData['delivery_status']  = 'pending_transfer';

            $this->vendorWalletRepo->getFirstWhere(params: ['seller_id' => $withdraw['seller_id']])->increment('withdrawn', $withdraw['amount']);
            $this->vendorWalletRepo->getFirstWhere(params: ['seller_id' => $withdraw['seller_id']])->decrement('pending_withdraw', $withdraw['amount']);
            $this->withdrawRequestRepo->update(id: $id, data: $withdrawData);

            $this->notifyVendorOfWithdrawStatus($withdraw, 1, $withdraw['amount']);

            // Send approval email with PDF receipt — deferred past the response since mail
            // sends synchronously (no queue worker configured) and PDF generation is slow.
            dispatch(function () use ($id) {
                try {
                    $freshWithdraw = $this->withdrawRequestRepo->getFirstWhere(params: ['id' => $id], relations: ['seller', 'seller.shop']);
                    if ($freshWithdraw->seller?->email) {
                        Mail::to($freshWithdraw->seller->email)->send(new WithdrawRequestStatusMail($freshWithdraw));
                    }
                } catch (\Exception $e) {
                    // Email failure should not block the approval
                }
            })->afterResponse();

            ToastMagic::success(translate('Vendor_Payment_has_been_approved_successfully'));
            return redirect()->route('admin.vendors.withdraw_list');
        }

        $withdrawData['denied_at'] = now();
        $this->vendorWalletRepo->getFirstWhere(params: ['seller_id' => $withdraw['seller_id']])->increment('total_earning', $withdraw['amount']);
        $this->vendorWalletRepo->getFirstWhere(params: ['seller_id' => $withdraw['seller_id']])->decrement('pending_withdraw', $withdraw['amount']);
        $this->withdrawRequestRepo->update(id: $id, data: $withdrawData);

        $this->notifyVendorOfWithdrawStatus($withdraw, 2, $withdraw['amount']);

        // Send denial email — deferred past the response since mail sends synchronously
        // (no queue worker configured) and PDF generation is slow.
        dispatch(function () use ($id) {
            try {
                $freshWithdraw = $this->withdrawRequestRepo->getFirstWhere(params: ['id' => $id], relations: ['seller', 'seller.shop']);
                if ($freshWithdraw->seller?->email) {
                    Mail::to($freshWithdraw->seller->email)->send(new WithdrawRequestStatusMail($freshWithdraw));
                }
            } catch (\Exception $e) {
                // Email failure should not block the denial
            }
        })->afterResponse();

        ToastMagic::info(translate('Vendor_Payment_request_has_been_Denied_successfully'));
        return redirect()->route('admin.vendors.withdraw_list');
    }

    private function notifyVendorOfWithdrawStatus($withdraw, int $approved, $amount): void
    {
        try {
            VendorNotification::create([
                'seller_id' => $withdraw['seller_id'],
                'title' => $approved == 1 ? 'Withdrawal Request Approved' : 'Withdrawal Request Denied',
                'message' => $approved == 1
                    ? 'Your withdrawal request of ' . setCurrencySymbol(amount: $amount) . ' has been approved.'
                    : 'Your withdrawal request of ' . setCurrencySymbol(amount: $amount) . ' has been denied.',
                'link' => route('vendor.business-settings.withdraw.index'),
                'reference_id' => $withdraw['id'],
                'read_at' => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('[Admin VendorController] Vendor notification failed: ' . $e->getMessage());
        }
    }

    public function updateWithdrawDeliveryStatus(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'delivery_status' => 'required|in:pending_transfer,in_transfer,transferred,failed',
        ]);

        $this->withdrawRequestRepo->update(id: $id, data: [
            'delivery_status'    => $request['delivery_status'],
            'delivery_status_at' => now(),
            'delivery_note'      => $request['delivery_note'] ?? null,
        ]);

        ToastMagic::success(translate('delivery_status_updated_successfully'));
        return redirect()->back();
    }

    // --- THIS IS THE NEW METHOD ---
    public function getTierPlanTabView(Request $request, $seller): View
    {
        $search = $request['searchValue'];
        $paginationLimit = (int) (getWebConfig(name: WebConfigKey::PAGINATION_LIMIT) ?? 25);

        $vendorTiers = VendorTier::with('tier')
            ->where('seller_id', $seller->id)
            ->when($search, function ($q) use ($search) {
                $q->whereHas('tier', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($paginationLimit);

        return view(Vendor::VIEW_TIER_PLAN['VIEW'], compact('seller', 'vendorTiers'));
    }
}
