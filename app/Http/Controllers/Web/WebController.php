<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\RobotsMetaContentRepositoryInterface;
use App\Services\ProductService;
use App\Traits\CacheManagerTrait;
use App\Traits\InHouseTrait;
use App\Models\User;
use App\Traits\MaintenanceModeTrait;
use App\Utils\BrandManager;
use App\Utils\CategoryManager;
use App\Utils\Helpers;
use App\Events\DigitalProductOtpVerificationEvent;
use App\Http\Controllers\Controller;
use App\Models\OfflinePaymentMethod;
use App\Models\ShippingAddress;
use App\Models\ShippingMethod;
use App\Models\ShippingType;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\OrderDetail;
use App\Models\Review;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Cart;
use App\Models\CartShipping;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Currency;
use App\Models\DeliveryZipCode;
use App\Models\DigitalProductOtpVerification;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCompare;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\VendorTier;
use App\Models\Wishlist;
use App\Traits\CommonTrait;
use App\Traits\SmsGateway;
use App\Utils\CartManager;
use App\Utils\Convert;
use App\Utils\CustomerManager;
use App\Utils\OrderManager;
use App\Utils\ProductManager;
use App\Utils\SMSModule;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use function App\Utils\payment_gateways;

class WebController extends Controller
{
    use CommonTrait;
    use InHouseTrait;
    use SmsGateway;
    use MaintenanceModeTrait;
    use CacheManagerTrait;

    public function __construct(
        private OrderDetail                                   $order_details,
        private Product                                       $product,
        private Wishlist                                      $wishlist,
        private Order                                         $order,
        private Category                                      $category,
        private Brand                                         $brand,
        private Seller                                        $seller,
        private ProductCompare                                $compare,
        private readonly RobotsMetaContentRepositoryInterface $robotsMetaContentRepo,
        private readonly ProductService                       $productService,
    )
    {

    }

    public function maintenance_mode(): View|RedirectResponse
    {
        if ($this->checkMaintenanceMode()) {
            return view(VIEW_FILE_NAMES['maintenance_mode'], [
                'maintenanceMessages' => getWebConfig(name: 'maintenance_message_setup') ?? [],
                'maintenanceTypeAndDuration' => getWebConfig(name: 'maintenance_duration_setup') ?? [],
            ]);
        }
        return redirect()->route('home');
    }

    public function search_shop(Request $request): View|RedirectResponse
    {
        return $this->getAllVendorsView($request);
    }

    public function getAllCategoriesView(Request $request): View|RedirectResponse
    {
        if (theme_root_path() == 'theme_fashion') {
            Toastr::warning(translate('Page_not_found'));
            return back();
        }

        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'categories']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $categories = Category::with(['product' => function ($query) {
            return $query->active()->withCount(['orderDetails']);
        }])->when($request['search'], function ($query) use ($request) {
            return $query->where('name', 'like', "%{$request['search']}%");
        })->withCount(['product' => function ($query) {
            $query->active();
        }])->with(['childes' => function ($query) {
            $query->with(['childes' => function ($query) {
                $query->withCount(['subSubCategoryProduct'])->where('position', 2);
            }])->withCount(['subCategoryProduct'])->where('position', 1);
        }, 'childes.childes'])->where('position', 0)->get();

        return view('web-views.products.categories', [
            'categories' => CategoryManager::getPriorityWiseCategorySortQuery(query: $categories),
            'robotsMetaContentData' => $robotsMetaContentData
        ]);
    }

    public function categories_by_category($id)
    {
        $category = Category::with(['childes.childes'])->where('id', $id)->first();
        return response()->json([
            'view' => view('web-views.partials._category-list-ajax', compact('category'))->render(),
        ]);
    }

    public function getAllBrandsView(Request $request): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'brands']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $brandStatus = getWebConfig(name: 'product_brand');
        session()->put('product_brand', $brandStatus);
        if ($brandStatus == 1) {
            $brandList = Brand::active()->with(['brandProducts' => function ($query) {
                return $query->withCount(['orderDetails']);
            }])
                ->withCount('brandProducts')
                ->when($request->has('search'), function ($query) use ($request) {
                    $query->where('name', 'LIKE', '%' . $request['search'] . '%');
                });

            return view(VIEW_FILE_NAMES['all_brands'], [
                'brands' => self::getPriorityWiseBrandProductsQuery(request: $request, query: $brandList),
                'robotsMetaContentData' => $robotsMetaContentData
            ]);
        } else {
            return redirect()->route('home');
        }
    }

    function getPriorityWiseBrandProductsQuery($request, $query)
    {
        if (theme_root_path() == 'theme_aster') {
            $paginateLimit = 12;
        } elseif (theme_root_path() == 'theme_fashion') {
            $paginateLimit = 10;
        } else {
            $paginateLimit = 18;
        }
        $brandProductSortBy = getWebConfig(name: 'brand_list_priority');
        $orderBy = $request->get('order_by', 'desc');

        if (empty($request['order_by']) && $brandProductSortBy && ($brandProductSortBy['custom_sorting_status'] == 1)) {
            if ($brandProductSortBy['sort_by'] == 'most_order') {
                $query = $query->get()->map(function ($brand) {
                    $brand['order_count'] = $brand->brandProducts->sum('order_details_count');
                    return $brand;
                })->sortByDesc('order_count');
            } elseif ($brandProductSortBy['sort_by'] == 'latest_created') {
                $query = $query->orderBy('id', 'desc');
            } elseif ($brandProductSortBy['sort_by'] == 'first_created') {
                $query = $query->orderBy('id', 'asc');
            } elseif ($brandProductSortBy['sort_by'] == 'a_to_z') {
                $query = $query->orderBy('name', 'asc');
            } elseif ($brandProductSortBy['sort_by'] == 'z_to_a') {
                $query = $query->orderBy('name', 'desc');
            }

            return $query->paginate($paginateLimit)->appends(['order_by' => $orderBy, 'search' => $request['search']]);
        } else {
            return $query->orderBy('name', $orderBy)->latest()->paginate($paginateLimit)->appends(['order_by' => $orderBy, 'search' => $request['search']]);
        }

    }

    public function getAllVendorsView(Request $request): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'vendors']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $businessMode = getWebConfig(name: 'business_mode');
        if (isset($businessMode) && $businessMode == 'single') {
            Toastr::warning(translate('access_denied') . ' !!');
            return back();
        }

        $vendorsList = Shop::active()
            ->withCount(['products' => function ($query) {
                $query->active();
            }])
            ->when(isset($request['shop_name']), function ($query) use ($request) {
                $key = explode(' ', $request['shop_name']);
                return $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })
            ->with('seller', function ($query) {
                $query->withCount(['orders', 'productReviews', 'positiveProductReviews'])
                      ->withAvg('productReviews', 'rating')
                      ->withSum('productReviews', 'rating');
            })
            ->get()
            ->each(function ($shop) {
                $shop->orders_count = $shop->seller->orders_count ?? 0;
                $shop->review_count = $shop->seller->product_reviews_count ?? 0;
                $shop->average_rating = $shop->seller->product_reviews_avg_rating ?? 0;
                $shop->total_rating = $shop->seller->product_reviews_sum_rating ?? 0;
                $positiveReviewsCount = $shop->seller->positive_product_reviews_count ?? 0;
                $shop->positive_review = ($shop->review_count !== 0) ? ($positiveReviewsCount * 100) / $shop->review_count : 0;

                $currentDate = date('Y-m-d');
                $shop->is_vacation_mode_now = $shop['vacation_status'] && ($currentDate >= $shop['vacation_start_date']) && ($currentDate <= $shop['vacation_end_date']) ? 1 : 0;
                return $shop;
            });

        $inhouseProductIds = Product::active()->where(['added_by' => 'admin'])->pluck('id');
        $inhouseProductCount = $inhouseProductIds->count();

        $inhouseReviewData = Review::active()->whereIn('product_id', $inhouseProductIds);
        $inhouseReviewDataCount = $inhouseReviewData->count();
        $inhouseRattingStatusPositive = (clone $inhouseReviewData)->where('rating', '>=', 4)->count();

        $current_date = date('Y-m-d');
        $inhouseShop = $this->getInHouseShopObject();
        $inhouseShop->id = 0;
        $inhouseShop->products_count = $inhouseProductCount;
        $inhouseShop->total_rating = $inhouseReviewDataCount;
        $inhouseShop->review_count = $inhouseReviewDataCount;
        $inhouseShop->average_rating = $inhouseReviewData->avg('rating');
        $inhouseShop->positive_review = $inhouseReviewDataCount != 0 ? ($inhouseRattingStatusPositive * 100) / $inhouseReviewDataCount : 0;
        $inhouseShop->orders_count = Order::where(['seller_is' => 'admin'])->count();
        $inhouseShop->is_vacation_mode_now = $inhouseShop['vacation_status'] && ($current_date >= $inhouseShop['vacation_start_date']) && ($current_date <= $inhouseShop['vacation_end_date']) ? 1 : 0;

        if (!(isset($request['shop_name']) && !str_contains(strtolower(getWebConfig(name: 'company_name')), strtolower($request['shop_name'])))) {
            $vendorsList = $vendorsList->prepend($inhouseShop);
        }

        if ($request->has('filter') && $request['filter'] == 'top-vendors') {
            $vendorsList = ProductManager::getPriorityWiseTopVendorQuery($vendorsList);
        } else {
            $vendorsList = ProductManager::getPriorityWiseVendorQuery($vendorsList);
        }

        // Work out which of the listed shops are "Featured Vendor" tier — used both to
        // badge them in the view and (for the default sort) to pin them to the top.
        $featuredSellerIds = new \Illuminate\Support\Collection();
        $sellerIds = $vendorsList->pluck('seller_id')->filter()->unique()->all();
        if (!empty($sellerIds)) {
            $today = Carbon::today()->toDateString();
            $featuredSellerIds = VendorTier::with('tier')
                ->whereIn('seller_id', $sellerIds)
                ->whereIn('status', ['active', 'trial'])
                ->where(function ($query) use ($today) {
                    $query->where(fn ($q) => $q->where('status', 'active')->whereDate('end_date', '>=', $today))
                        ->orWhere(fn ($q) => $q->where('status', 'trial')->whereDate('trial_end_date', '>=', $today));
                })
                ->get()
                ->filter(fn ($vendorTier) => (bool) ($vendorTier->tier?->is_featured_vendor ?? false))
                ->pluck('seller_id')
                ->unique();
        }

        // Pin featured vendors to the top of the default listing (skipped when the
        // customer picks an explicit sort order).
        if (!$request->has('order_by') && $featuredSellerIds->isNotEmpty()) {
            $vendorsList = $vendorsList->sortByDesc(fn ($shop) => $featuredSellerIds->contains($shop->seller_id) ? 1 : 0);
        }

        if ($request->has('order_by')) {
            if ($request['order_by'] == 'asc') {
                $vendorsList = $vendorsList->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
            } else if ($request['order_by'] == 'desc') {
                $vendorsList = $vendorsList->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE);
            } else if ($request['order_by'] == 'highest-products') {
                $vendorsList = $vendorsList->sortByDesc('products_count');
            } else if ($request['order_by'] == 'lowest-products') {
                $vendorsList = $vendorsList->sortBy('products_count');
            } else if ($request['order_by'] == 'rating-high-to-low') {
                $vendorsList = $vendorsList->sortByDesc('average_rating');
            } else if ($request['order_by'] == 'rating-low-to-high') {
                $vendorsList = $vendorsList->sortBy('average_rating');
            };
        }

        return view(VIEW_FILE_NAMES['all_stores_page'], [
            'vendorsList' => $vendorsList->paginate(12)->appends($request->all()),
            'order_by' => $request['order_by'],
            'robotsMetaContentData' => $robotsMetaContentData,
            'featuredSellerIds' => $featuredSellerIds,
        ]);
    }

    public function getSearchedProducts(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => 'Product name is required!',
        ]);

        $result = ProductManager::getSearchProductsForWeb($request['name'], $request['category_id'] ?? 'all');
        $products = $result['products'];
        if ($products == null) {
            $result = ProductManager::getTranslatedProductSearchForWeb($request['name'], $request['category_id'] ?? 'all');
            $products = $result['products'];
        }

        $sellers = Shop::where(function ($query) use ($request) {
            $query->orWhere('name', 'like', "%{$request['name']}%");
        })->whereHas('seller', function ($query) {
            return $query->where(['status' => 'approved']);
        })->with('products', function ($query) {
            return $query->active()->where('added_by', 'seller');
        })->get();

        $product_ids = [];
        foreach ($sellers as $seller) {
            if (isset($seller->product) && $seller->product->count() > 0) {
                $ids = $seller->product->pluck('id');
                array_push($product_ids, ...$ids);
            }
        }

        $companyName = getWebConfig(name: 'company_name');
        if (strpos($request['name'], $companyName) !== false) {
            $ids = Product::active()->Where('added_by', 'admin')->pluck('id');
            array_push($product_ids, ...$ids);
        }

        $seller_products = Product::active()->withCount('reviews')->whereIn('id', $product_ids)
            ->orderByRaw("LOCATE(?, name), name", [$request['name']])->get(); // SEC-01 FIX: bound parameter

        return response()->json([
            'result' => view(VIEW_FILE_NAMES['product_search_result'], compact('products', 'seller_products'))->render(),
            'seller_products' => $seller_products->count(),
        ]);
    }
    public function getSearchedProductsForCompareList(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => 'Product name is required!',
        ]);
        $compare_id = $request['compare_id'];
        $result = ProductManager::getSearchProductsForWeb($request['name']);
        $products = $result['products'];
        if ($products == null) {
            $result = ProductManager::getTranslatedProductSearchForWeb($request['name']);
            $products = $result['products'];
        }
        return response()->json([
            'result' => view(VIEW_FILE_NAMES['product_search_result_for_compare_list'], compact('products', 'compare_id'))->render(),
        ]);
    }

    public function checkout_details(Request $request)
    {
        if (!auth('customer')->check() && (!getWebConfig(name: 'guest_checkout') || !session()->has('guest_id') || !session('guest_id'))) {
            return redirect()->guest(route('customer.auth.login'));
        }

        if (auth('customer')->check() && Cart::where(['customer_id' => auth('customer')->id()])->count() < 1) {
            Toastr::error(translate('invalid_access'));
            return redirect('/');
        }
        ProductManager::updateProductPriceInCartList(request: $request);

        $response = self::checkValidationForCheckoutPages($request);
        if ($response['status'] == 0) {
            foreach ($response['message'] as $message) {
                Toastr::error($message);
            }
            return isset($response['redirect']) ? redirect($response['redirect']) : redirect('/');
        }

        $countryRestrictStatus = getWebConfig(name: 'delivery_country_restriction');
        $zipRestrictStatus = getWebConfig(name: 'delivery_zip_code_area_restriction');
        $countries = $countryRestrictStatus ? $this->get_delivery_country_array() : COUNTRIES;
        $zipCodes = $zipRestrictStatus ? DeliveryZipCode::all() : 0;
        $billingInputByCustomer = getWebConfig(name: 'billing_input_by_customer');
        $defaultLocation = getWebConfig(name: 'default_location');

        $user = Helpers::getCustomerInformation($request);
        $shippingAddresses = ShippingAddress::where([
            'customer_id' => $user == 'offline' ? session('guest_id') : auth('customer')->id(),
            'is_guest' => $user == 'offline' ? 1 : '0',
        ])->get();

        $countriesName = [];
        $countriesCode = [];
        foreach ($countries as $country) {
            $countriesName[] = $country['name'];
            $countriesCode[] = $country['code'];
        }

        $hasDigitalProducts = Cart::where([
            'customer_id' => $user == 'offline' ? session('guest_id') : auth('customer')->id(),
            'is_guest' => $user == 'offline' ? 1 : 0,
            'is_checked' => 1,
        ])->where('product_type', 'digital')->exists();

        return view(VIEW_FILE_NAMES['order_shipping'], [
            'physical_product_view' => $response['physical_product_view'],
            'has_digital_products' => $hasDigitalProducts,
            'zip_codes' => $zipCodes,
            'country_restrict_status' => $countryRestrictStatus,
            'zip_restrict_status' => $zipRestrictStatus,
            'countries' => $countries,
            'countriesName' => $countriesName,
            'countriesCode' => $countriesCode,
            'billing_input_by_customer' => $billingInputByCustomer,
            'default_location' => $defaultLocation,
            'shipping_addresses' => $shippingAddresses,
            'billing_addresses' => $shippingAddresses
        ]);
    }

    public function checkout_payment(Request $request): View|RedirectResponse
    {
        if (!session('address_id') && !session('billing_address_id') && !session('digital_delivery_type')) {
            Toastr::error(translate('Please_update_address_information'));
        }

        $response = self::checkValidationForCheckoutPages($request);
        if ($response['status'] == 0) {
            foreach ($response['message'] as $message) {
                Toastr::error($message);
            }
            return $response['redirect'] ? redirect($response['redirect']) : redirect('/');
        }

        $cartItemGroupIDs = CartManager::get_cart_group_ids(type: 'checked');
        $cartGroupList = Cart::with(['product'])->whereHas('product', function ($query) {
            return $query->active();
        })->whereIn('cart_group_id', $cartItemGroupIDs)->where(['is_checked' => 1])->get()->groupBy('cart_group_id');

        $isPhysicalProductExistArray = [];
        foreach ($cartGroupList as $groupId => $cartGroup) {
            $isPhysicalProductExist = false;
            foreach ($cartGroup as $cart) {
                if ($cart->product_type == 'physical') {
                    $isPhysicalProductExist = true;
                }
            }
            $isPhysicalProductExistArray[$groupId] = $isPhysicalProductExist;
        }

        $cashOnDeliveryBtnShow = !in_array(false, $isPhysicalProductExistArray);

        $order = Order::find(session('order_id'));
        $couponDiscount = session()->has('coupon_discount') ? session('coupon_discount') : 0;
        $orderWiseShippingDiscount = CartManager::order_wise_shipping_discount();
        $getShippingCostSavedForFreeDelivery = CartManager::getShippingCostSavedForFreeDelivery(type: 'checked');
        $amount = CartManager::cart_grand_total(type: 'checked') - $couponDiscount - $orderWiseShippingDiscount - $getShippingCostSavedForFreeDelivery;
        $checkoutCurrencies = Currency::where('symbol', '₹')->orWhere('code', 'USD')->orWhere('code', 'MYR')->get();
        $inr = $checkoutCurrencies->firstWhere('symbol', '₹');
        $usd = $checkoutCurrencies->firstWhere('code', 'USD');
        $myr = $checkoutCurrencies->firstWhere('code', 'MYR');

        $offlinePaymentMethods = OfflinePaymentMethod::where('status', 1)->get();
        $paymentGatewayPublishedStatus = config('get_payment_publish_status') ?? 0;
        $offlinePaymentStatus = getWebConfig(name: 'offline_payment');

        $availablePaymentMethod = [];

        $cashOnDeliveryStatus = getWebConfig(name: 'cash_on_delivery');
        if ($cashOnDeliveryStatus && $cashOnDeliveryStatus['status'] && $cashOnDeliveryBtnShow) {
            $availablePaymentMethod = ['cash_on_delivery'];
        }

        if (getWebConfig(name: 'digital_payment') && count(payment_gateways()) > 0) {
            $availablePaymentMethod = ['payment_gateways'];
        }

        if ($offlinePaymentStatus && $offlinePaymentStatus['status'] == 1 && count($offlinePaymentMethods) > 0) {
            $availablePaymentMethod = ['offline_payment'];
        }
        if (auth('customer')->check() && getWebConfig(name: 'wallet_status')) {
            $availablePaymentMethod = ['wallet_status'];
        }

        $isDigitalOnly = !$response['physical_product_view'] && session()->has('digital_delivery_type');
        if ((session()->has('address_id') && session()->has('billing_address_id')) || $isDigitalOnly) {
            return view(VIEW_FILE_NAMES['payment_details'], [
                'cashOnDeliveryBtnShow' => $cashOnDeliveryBtnShow,
                'order' => $order,
                'cash_on_delivery' => $cashOnDeliveryStatus,
                'digital_payment' => getWebConfig(name: 'digital_payment'),
                'wallet_status' => getWebConfig(name: 'wallet_status'),
                'offline_payment' => $offlinePaymentStatus,
                'coupon_discount' => $couponDiscount,
                'amount' => $amount,
                'inr' => $inr,
                'usd' => $usd,
                'myr' => $myr,
                'paymentGatewayPublishedStatus' => $paymentGatewayPublishedStatus,
                'payment_gateways_list' => payment_gateways(),
                'offline_payment_methods' => $offlinePaymentMethods,
                'activeMinimumMethods' => count($availablePaymentMethod) > 0,
            ]);
        }

        Toastr::error(translate('incomplete_info'));
        return back();
    }

    public function getCashOnDeliveryCheckoutComplete(Request $request): View|RedirectResponse|JsonResponse
    {
        if ($request['payment_method'] != 'cash_on_delivery') {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 0,
                    'message' => translate('Something_went_wrong'),
                ]);
            }
            return back()->with('error', 'Something_went_wrong');
        }
        $uniqueID = OrderManager::generateUniqueOrderID();
        $orderIds = [];
        $cartGroupIds = CartManager::get_cart_group_ids(request: $request, type: 'checked');
        $carts = Cart::whereHas('product', function ($query) {
            return $query->active();
        })->with('product')->whereIn('cart_group_id', $cartGroupIds)->where(['is_checked' => 1])->get();

        $productStockCheck = CartManager::product_stock_check($carts);
        if (!$productStockCheck) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 0,
                    'message' => translate('the_following_items_in_your_cart_are_currently_out_of_stock'),
                ]);
            }
            Toastr::error(translate('the_following_items_in_your_cart_are_currently_out_of_stock'));
            return redirect()->route('shop-cart');
        }

        $verifyStatus = OrderManager::verifyCartListMinimumOrderAmount($request);
        if ($verifyStatus['status'] == 0) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 0,
                    'message' => translate('check_minimum_order_amount_requirement'),
                    'redirect' => route('shop-cart'),
                ]);
            }

            Toastr::info(translate('check_minimum_order_amount_requirement'));
            return redirect()->route('shop-cart');
        }

        $physicalProductExist = false;
        foreach ($carts as $cart) {
            if ($cart->product_type == 'physical') {
                $physicalProductExist = true;
            }
        }

        if ($physicalProductExist) {

            if (session('newCustomerRegister')) {
                $newCustomerRegister = session('newCustomerRegister');
                if (User::where(['email' => $newCustomerRegister['email']])->orWhere(['phone' => $newCustomerRegister['phone']])->first()) {
                    if ($request->ajax()) {
                        return response()->json([
                            'status' => 0,
                            'message' => translate('Already_registered'),
                        ]);
                    }
                    Toastr::error(translate('Already_registered'));
                    return back();
                }

                $addCustomer = User::create([
                    'name' => $newCustomerRegister['name'],
                    'f_name' => $newCustomerRegister['name'],
                    'l_name' => $newCustomerRegister['l_name'],
                    'email' => $newCustomerRegister['email'],
                    'phone' => $newCustomerRegister['phone'],
                    'is_active' => 1,
                    'password' => bcrypt($newCustomerRegister['password']),
                    'referral_code' => $newCustomerRegister['referral_code'],
                ]);
                session()->put('newRegisterCustomerInfo', $addCustomer);

                $customerID = session()->has('guest_id') ? session('guest_id') : 0;
                ShippingAddress::where(['customer_id' => $customerID, 'is_guest' => 1, 'id' => session('address_id')])
                    ->update(['customer_id' => $addCustomer['id'], 'is_guest' => 0]);
                ShippingAddress::where(['customer_id' => $customerID, 'is_guest' => 1, 'id' => session('billing_address_id')])
                    ->update(['customer_id' => $addCustomer['id'], 'is_guest' => 0]);
            }

            DB::transaction(function () use ($request, $uniqueID, $cartGroupIds, &$orderIds) {
                $parentOrderId = null;
                if (count($cartGroupIds) > 1) {
                    $parentOrderId = OrderManager::create_parent_order([
                        'payment_method' => 'cash_on_delivery',
                        'order_status'   => 'pending',
                        'payment_status' => 'unpaid',
                        'transaction_ref'=> '',
                        'order_group_id' => $uniqueID,
                    ], $request);
                    $orderIds[] = $parentOrderId;
                }
                $childNumber = 0;
                foreach ($cartGroupIds as $groupId) {
                    $data = [
                        'payment_method' => 'cash_on_delivery',
                        'order_status' => 'pending',
                        'payment_status' => 'unpaid',
                        'transaction_ref' => '',
                        'order_group_id' => $uniqueID,
                        'cart_group_id' => $groupId,
                        'bring_change_amount' => $request['bring_change_amount'] ?? 0,
                        'bring_change_amount_currency' => session('currency_code'),
                        'parent_order_id' => $parentOrderId,
                        'child_number' => $parentOrderId ? ++$childNumber : null,
                    ];
                    $orderId = OrderManager::generate_order($data);
                    $orderIds[] = $orderId;
                }
                if ($parentOrderId) {
                    Order::where('id', $parentOrderId)->update([
                        'order_amount' => Order::where('parent_order_id', $parentOrderId)->sum('order_amount'),
                        'updated_at'   => now(),
                    ]);
                    OrderManager::send_parent_order_customer_email($parentOrderId);
                }
            });

            CartManager::cart_clean();

            if ($request->ajax()) {
                return response()->json([
                    'status' => 1,
                    'message' => translate('Order_Placed_Successfully'),
                    'redirect' => route('order-placed-success', ['orderIds' => json_encode($orderIds)]),
                ]);
            }

            $isNewCustomerInSession = session('newCustomerRegister');
            session()->forget('newCustomerRegister');
            session()->forget('newRegisterCustomerInfo');

            return view(VIEW_FILE_NAMES['order_complete'], [
                'order_ids' => $orderIds,
                'isNewCustomerInSession' => $isNewCustomerInSession,
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 0,
                'message' => translate('Something_went_wrong'),
            ]);
        }
        return back()->with('error', translate('Something_went_wrong'));
    }

    public function getOrderPlaceView(Request $request): View
    {
        $isNewCustomerInSession = session('newCustomerRegister');
        session()->forget('newCustomerRegister');
        session()->forget('newRegisterCustomerInfo');

        $orderIds = json_decode($request['orderIds'] ?? '',true);

        return view(VIEW_FILE_NAMES['order_complete'], [
            'order_ids' => $orderIds,
            'isNewCustomerInSession' => $isNewCustomerInSession,
        ]);
    }

    public function getOfflinePaymentCheckoutComplete(Request $request): View|RedirectResponse
    {
        if ($request['payment_method'] != 'offline_payment') {
            return back()->with('error', 'Something went wrong!');
        }
        $genUniqueId = OrderManager::generateUniqueOrderID();
        $orderIds = [];
        $cartGroupIds = CartManager::get_cart_group_ids(request: $request, type: 'checked');
        $carts = Cart::whereHas('product', function ($query) {
            return $query->active();
        })->with('product')->whereIn('cart_group_id', $cartGroupIds)->where(['is_checked' => 1])->get();

        $productStockCheck = CartManager::product_stock_check($carts);
        if (!$productStockCheck) {
            Toastr::error(translate('the_following_items_in_your_cart_are_currently_out_of_stock'));
            return redirect()->route('shop-cart');
        }

        $verifyStatus = OrderManager::verifyCartListMinimumOrderAmount($request);
        if ($verifyStatus['status'] == 0) {
            Toastr::info(translate('check_minimum_order_amount_requirement'));
            return redirect()->route('shop-cart');
        }

        $offlinePaymentInfo = [];
        $method = OfflinePaymentMethod::where(['id' => $request['method_id'], 'status' => 1])->first();

        if (isset($method)) {
            $fields = array_column($method->method_informations, 'customer_input');
            $values = $request->all();

            $offlinePaymentInfo['method_id'] = $request['method_id'];
            $offlinePaymentInfo['method_name'] = $method->method_name;
            foreach ($fields as $field) {
                if (key_exists($field, $values)) {
                    $offlinePaymentInfo[$field] = $values[$field];
                }
            }
        }

        if (session('newCustomerRegister')) {
            $newCustomerRegister = session('newCustomerRegister');
            if (User::where(['email' => $newCustomerRegister['email']])->orWhere(['phone' => $newCustomerRegister['phone']])->first()) {
                Toastr::error(translate('Already_registered'));
                return back();
            }

            $addCustomer = User::create([
                'name' => $newCustomerRegister['name'],
                'f_name' => $newCustomerRegister['name'],
                'l_name' => $newCustomerRegister['l_name'],
                'email' => $newCustomerRegister['email'],
                'phone' => $newCustomerRegister['phone'],
                'is_active' => 1,
                'password' => bcrypt($newCustomerRegister['password']),
                'referral_code' => $newCustomerRegister['referral_code'],
            ]);
            session()->put('newRegisterCustomerInfo', $addCustomer);

            $customerID = session()->has('guest_id') ? session('guest_id') : 0;
            ShippingAddress::where(['customer_id' => $customerID, 'is_guest' => 1, 'id' => session('address_id')])
                ->update(['customer_id' => $addCustomer['id'], 'is_guest' => 0]);
            ShippingAddress::where(['customer_id' => $customerID, 'is_guest' => 1, 'id' => session('billing_address_id')])
                ->update(['customer_id' => $addCustomer['id'], 'is_guest' => 0]);
        }

        DB::transaction(function () use ($request, $genUniqueId, $cartGroupIds, $offlinePaymentInfo, &$orderIds) {
            $parentOrderId = null;
            if (count($cartGroupIds) > 1) {
                $parentOrderId = OrderManager::create_parent_order([
                    'payment_method' => 'offline_payment',
                    'order_status'   => 'pending',
                    'payment_status' => 'unpaid',
                    'payment_note'   => $request['payment_note'] ?? null,
                    'order_group_id' => $genUniqueId,
                ], $request);
                $orderIds[] = $parentOrderId;
            }
            $childNumber = 0;
            foreach ($cartGroupIds as $cartGroupSingleId) {
                $data = [
                    'payment_method' => 'offline_payment',
                    'order_status' => 'pending',
                    'payment_status' => 'unpaid',
                    'payment_note' => $request['payment_note'],
                    'order_group_id' => $genUniqueId,
                    'cart_group_id' => $cartGroupSingleId,
                    'offline_payment_info' => $offlinePaymentInfo,
                    'parent_order_id' => $parentOrderId,
                    'child_number' => $parentOrderId ? ++$childNumber : null,
                ];
                $getOrderId = OrderManager::generate_order($data);
                $orderIds[] = $getOrderId;
            }
            if ($parentOrderId) {
                Order::where('id', $parentOrderId)->update([
                    'order_amount' => Order::where('parent_order_id', $parentOrderId)->sum('order_amount'),
                    'updated_at'   => now(),
                ]);
                OrderManager::send_parent_order_customer_email($parentOrderId);
            }
        });

        CartManager::cart_clean();

        $isNewCustomerInSession = session('newCustomerRegister');
        session()->forget('newCustomerRegister');
        session()->forget('newRegisterCustomerInfo');

        return view(VIEW_FILE_NAMES['order_complete'], [
            'order_ids' => $orderIds,
            'isNewCustomerInSession' => $isNewCustomerInSession,
        ]);
    }

    public function checkout_complete_wallet(Request $request = null)
    {
        // CustomerManager::create_wallet_transaction() silently no-ops (returns
        // false, debits nothing) whenever the wallet_status business setting is
        // off — but its return value was never checked here, so an order could
        // still be created and marked payment_status=paid/order_status=confirmed
        // with the customer's wallet never actually debited. Guard here so a
        // disabled wallet payment method can't be used to complete a free order.
        if (!getWebConfig(name: 'wallet_status')) {
            if ($request && $request->ajax()) {
                return response()->json([
                    'status' => 0,
                    'message' => translate('Something_went_wrong'),
                ]);
            }
            Toastr::error(translate('Something_went_wrong'));
            return back();
        }

        $discount = session()->has('coupon_discount') ? session('coupon_discount') : 0;
        $orderWiseShippingDiscount = CartManager::order_wise_shipping_discount();
        $shippingCostSaved = CartManager::getShippingCostSavedForFreeDelivery(type: 'checked');
        $paymentAmount = CartManager::cart_grand_total(type: 'checked') - $discount - $orderWiseShippingDiscount - $shippingCostSaved;

        $user = Helpers::getCustomerInformation($request);
        if ($paymentAmount > $user->wallet_balance) {
            Toastr::warning(translate('Inefficient_balance_in_your_wallet_to_pay_for_this_order') . '!!');
            return back();
        } else {
            $unique_id = OrderManager::generateUniqueOrderID();
            $cart_group_ids = CartManager::get_cart_group_ids(type: 'checked');
            $carts = Cart::whereHas('product', function ($query) {
                return $query->active();
            })->with('product')
                ->whereIn('cart_group_id', $cart_group_ids)
                ->where(['is_checked' => 1])->get();

            $productStockCheck = CartManager::product_stock_check($carts);
            if (!$productStockCheck) {
                Toastr::error(translate('the_following_items_in_your_cart_are_currently_out_of_stock'));
                return redirect()->route('shop-cart');
            }

            $verifyStatus = OrderManager::verifyCartListMinimumOrderAmount($request);
            if ($verifyStatus['status'] == 0) {
                Toastr::info(translate('check_minimum_order_amount_requirement'));
                return redirect()->route('shop-cart');
            }

            $order_ids = [];
            DB::transaction(function () use ($request, $unique_id, $cart_group_ids, &$order_ids) {
                $parentOrderId = null;
                if (count($cart_group_ids) > 1) {
                    $parentOrderId = OrderManager::create_parent_order([
                        'payment_method' => 'pay_by_wallet',
                        'order_status'   => 'confirmed',
                        'payment_status' => 'paid',
                        'transaction_ref'=> '',
                        'order_group_id' => $unique_id,
                    ], $request);
                    $order_ids[] = $parentOrderId;
                }
                $childNumber = 0;
                foreach ($cart_group_ids as $group_id) {
                    $data = [
                        'payment_method' => 'pay_by_wallet',
                        'order_status' => 'confirmed',
                        'payment_status' => 'paid',
                        'transaction_ref' => '',
                        'order_group_id' => $unique_id,
                        'cart_group_id' => $group_id,
                        'parent_order_id' => $parentOrderId,
                        'child_number' => $parentOrderId ? ++$childNumber : null,
                    ];
                    $order_id = OrderManager::generate_order($data);
                    $order_ids[] = $order_id;
                }
                if ($parentOrderId) {
                    Order::where('id', $parentOrderId)->update([
                        'order_amount' => Order::where('parent_order_id', $parentOrderId)->sum('order_amount'),
                        'updated_at'   => now(),
                    ]);
                    OrderManager::send_parent_order_customer_email($parentOrderId);
                }
            });

            foreach ($order_ids as $order_id) {
                OrderManager::generateReferBonusForFirstOrder(orderId: $order_id);
            }

            $walletTransactionResult = CustomerManager::create_wallet_transaction($user->id, Convert::default($paymentAmount), 'order_place', 'order payment');
            if (!$walletTransactionResult) {
                // The order(s) above are already committed as paid/confirmed at this
                // point (generate_order() ran in its own transaction), so this can't
                // be silently rolled back here — but it must not go unnoticed: without
                // this log, a failed debit (e.g. wallet_status toggled off between the
                // page load and submit, or a DB error) left orders marked paid with no
                // corresponding wallet_transactions row and no trace anywhere.
                \Log::error('[checkout_complete_wallet] Wallet debit failed after order(s) already marked paid', [
                    'order_ids' => $order_ids,
                    'customer_id' => $user->id,
                    'amount' => $paymentAmount,
                ]);
            }
            CartManager::cart_clean();
        }

        if (session()->has('payment_mode') && session('payment_mode') == 'app') {
            return redirect()->route('payment-success');
        }

        $isNewCustomerInSession = session('newCustomerRegister');
        session()->forget('newCustomerRegister');
        session()->forget('coupon_discount');
        return view(VIEW_FILE_NAMES['order_complete'], compact('order_ids', 'isNewCustomerInSession'));
    }

    public function order_placed(): View
    {
        $isNewCustomerInSession = session('newCustomerRegister');
        session()->forget('newCustomerRegister');
        return view(VIEW_FILE_NAMES['order_complete'], compact('isNewCustomerInSession'));
    }

    public function shop_cart(Request $request): View|RedirectResponse
    {
        if (!auth('customer')->check() && !(getWebConfig(name: 'guest_checkout') && session()->has('guest_id') && session('guest_id'))) {
            Toastr::warning(translate('please_login_your_account'));
            return redirect()->guest(route('customer.auth.login'));
        }

        ProductManager::updateProductPriceInCartList(request: $request);
        $topRatedShops = [];
        $newSellers = [];
        $currentDate = date('Y-m-d H:i:s');
        if (theme_root_path() === "theme_fashion") {

            $sellerList = $this->seller->approved()
                ->with(['shop'])
                ->withCount(['product' => function ($query) { $query->active(); }])
                ->withAvg('productReviews', 'rating')
                ->withCount('productReviews')
                ->with(['product' => function ($query) { $query->active(); }])
                ->get();
            $sellerList?->map(function ($seller) {
                $count = $seller->product_reviews_count ?? 0;
                $seller['average_rating'] = $seller->product_reviews_avg_rating ?? 0;
                $seller['rating_count'] = $count;

                $productCount = $seller->product->count();
                $randomProduct = Arr::random($seller->product->toArray(), $productCount < 3 ? $productCount : 3);
                $seller['product'] = $randomProduct;
                return $seller;
            });
            $newSellers = $sellerList->sortByDesc('id')->take(12);
            $topRatedShops = $sellerList->where('rating_count', '!=', 0)->sortByDesc('average_rating')->take(12);
        }
        return view(VIEW_FILE_NAMES['cart_list'], compact('topRatedShops', 'newSellers', 'currentDate', 'request'));
    }

    public function seller_shop_product(Request $request, $id): View|JsonResponse
    {
        $products = Product::active()->withCount('reviews')->with('shop')->where(['added_by' => 'seller'])
            ->where('user_id', $id)
            ->whereHas('categories', fn ($query) => $query->where('categories.id', $request->category_id))
            ->paginate(12);
        $shop = Shop::where('seller_id', $id)->first();
        if ($request['sort_by'] == null) {
            $request['sort_by'] = 'latest';
        }

        if ($request->ajax()) {
            return response()->json([
                'view' => view(VIEW_FILE_NAMES['products__ajax_partials'], compact('products'))->render(),
            ], 200);
        }

        return view(VIEW_FILE_NAMES['shop_view_page'], compact('products', 'shop'))->with('seller_id', $id);
    }

    public function getQuickView(Request $request): JsonResponse
    {
        $product = ProductManager::get_product($request['product_id']);
        if (!$product) {
            return response()->json(['success' => 0, 'message' => translate('product_not_found')], 404);
        }
        $order_details = OrderDetail::where('product_id', $product->id)->get();
        $wishlists = Wishlist::where('product_id', $product->id)->get();
        $wishlist_status = Wishlist::where(['product_id' => $product->id, 'customer_id' => auth('customer')->id()])->count();
        $countOrder = count($order_details);
        $countWishlist = count($wishlists);
        $relatedCategoryIds = $product->categories()->pluck('categories.id');
        $relatedProducts = Product::with(['reviews'])->withCount('reviews')->whereHas('categories', fn ($query) => $query->whereIn('categories.id', $relatedCategoryIds))->where('id', '!=', $product->id)->limit(12)->get();
        $currentDate = date('Y-m-d');
        $seller_vacation_start_date = ($product->added_by == 'seller' && isset($product->seller->shop->vacation_start_date)) ? date('Y-m-d', strtotime($product->seller->shop->vacation_start_date)) : null;
        $seller_vacation_end_date = ($product->added_by == 'seller' && isset($product->seller->shop->vacation_end_date)) ? date('Y-m-d', strtotime($product->seller->shop->vacation_end_date)) : null;
        $seller_temporary_close = ($product->added_by == 'seller' && isset($product->seller->shop->temporary_close)) ? $product->seller->shop->temporary_close : false;
        $productAuthorsInfo = $this->productService->getProductAuthorsInfo(product: $product);
        $productPublishingHouseInfo = $this->productService->getProductPublishingHouseInfo(product: $product);

        $temporary_close = getWebConfig(name: 'temporary_close');
        $inhouse_vacation = getWebConfig(name: 'vacation_add');
        $inhouse_vacation_start_date = $product->added_by == 'admin' ? $inhouse_vacation['vacation_start_date'] : null;
        $inhouse_vacation_end_date = $product->added_by == 'admin' ? $inhouse_vacation['vacation_end_date'] : null;
        $inHouseVacationStatus = $product->added_by == 'admin' ? $inhouse_vacation['status'] : false;
        $inhouse_temporary_close = $product->added_by == 'admin' ? $temporary_close['status'] : false;

        // Newly Added From Blade
        $overallRating = getOverallRating($product->reviews);
        $rating = getRating($product->reviews);
        $reviews_of_product = Review::where('product_id', $product->id)->latest()->paginate(2);
        $decimal_point_settings = getWebConfig(name: 'decimal_point_settings');
        $more_product_from_seller = Product::active()->withCount('reviews')->where('added_by', $product->added_by)->where('id', '!=', $product->id)->where('user_id', $product->user_id)->latest()->take(5)->get();

        $firstVariationQuantity = $product['current_stock'];
        if (count(json_decode($product['variation'], true) ?? []) > 0) {
            $firstVariationQuantity = json_decode($product['variation'], true)[0]['qty'];
        }
        $firstVariationQuantity = $product['product_type'] == 'physical' ? $firstVariationQuantity : 999;

        $customerId = auth('customer')->id();
        if ($customerId) {
            $inCart = Cart::where('product_id', $product->id)->where('customer_id', $customerId)->where('is_guest', 0)->exists();
        } else {
            $guestId = session('guest_id');
            $inCart = $guestId
                ? Cart::where('product_id', $product->id)->where('customer_id', $guestId)->where('is_guest', 1)->exists()
                : false;
        }

        return response()->json([
            'success' => 1,
            'product' => $product,
            'view' => view(VIEW_FILE_NAMES['product_quick_view_partials'], compact('product', 'countWishlist', 'countOrder',
                'relatedProducts', 'currentDate', 'seller_vacation_start_date', 'seller_vacation_end_date', 'seller_temporary_close',
                'productAuthorsInfo', 'productPublishingHouseInfo',
                'inhouse_vacation_start_date', 'inhouse_vacation_end_date', 'inHouseVacationStatus', 'inhouse_temporary_close', 'wishlist_status', 'overallRating', 'rating', 'firstVariationQuantity', 'inCart'))->render(),
        ]);
    }

    public function discounted_products(Request $request): View|JsonResponse
    {
        $request['sort_by'] = $request['sort_by'] ?? 'latest';
        $request['data_from'] = $request['data_from'] ?? 'discounted_products';
        $request['offer_type'] = $request['offer_type'] ?? 'discounted';
        $request['product_type'] = $request['product_type'] ?? null;
        $request['id'] = $request['id'] ?? null;
        $request['name'] = $request['name'] ?? null;
        $request['page'] = $request['page'] ?? 1;
        $request['min_price'] = $request['min_price'] ?? null;
        $request['max_price'] = $request['max_price'] ?? null;
        $request['brand_id'] = $request['brand_id'] ?? null;
        $request['category_id'] = $request['category_id'] ?? null;
        $request['sub_category_id'] = $request['sub_category_id'] ?? null;
        $request['sub_sub_category_id'] = $request['sub_sub_category_id'] ?? null;
        $request['shop_id'] = $request['shop_id'] ?? null;
        $request['author_id'] = $request['author_id'] ?? null;
        $request['publishing_house_id'] = $request['publishing_house_id'] ?? null;

        $productData = Product::active()->with(['reviews'])->withCount('reviews');
        $query = Product::with(['reviews'])->active()->where('discount', '!=', 0);

        if ($request['data_from'] == 'category') {
            $query = $productData->whereHas('categories', fn ($query) => $query->where('categories.id', $request['id']));
        }

        if ($request['data_from'] == 'brand') {
            $query = $productData->where('brand_id', $request['id']);
        }

        if ($request['data_from'] == 'latest') {
            $query = $productData->orderBy('id', 'DESC');
        }

        if ($request['data_from'] == 'top-rated') {
            $reviews = Review::select('product_id', DB::raw('AVG(rating) as count'))
                ->groupBy('product_id')
                ->orderBy("count", 'desc')->get();
            $product_ids = [];
            foreach ($reviews as $review) {
                array_push($product_ids, $review['product_id']);
            }
            $query = $productData->whereIn('id', $product_ids);
        }

        if ($request['data_from'] == 'best-selling') {
            $details = OrderDetail::with('product')
                ->select('product_id', DB::raw('COUNT(product_id) as count'))
                ->groupBy('product_id')
                ->orderBy("count", 'desc')
                ->get();
            $product_ids = [];
            foreach ($details as $detail) {
                array_push($product_ids, $detail['product_id']);
            }
            $query = $productData->whereIn('id', $product_ids);
        }

        if ($request['data_from'] == 'most-favorite') {
            $details = Wishlist::with('product')
                ->select('product_id', DB::raw('COUNT(product_id) as count'))
                ->groupBy('product_id')
                ->orderBy("count", 'desc')
                ->get();
            $product_ids = [];
            foreach ($details as $detail) {
                array_push($product_ids, $detail['product_id']);
            }
            $query = $productData->whereIn('id', $product_ids);
        }

        if ($request['data_from'] == 'featured') {
            $query = Product::with(['reviews'])->active()->where('featured', 1);
        }

        if ($request['data_from'] == 'search') {
            $key = explode(' ', $request['name']);
            $query = $productData->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            });
        }

        if ($request['data_from'] == 'discounted_products') {
            $query = Product::with(['reviews'])->active()->where('discount', '!=', 0);
        }

        if ($request['sort_by'] == 'latest') {
            $fetched = $query->latest();
        } elseif ($request['sort_by'] == 'low-high') {
            $fetched = $query->orderBy('unit_price', 'ASC');
        } elseif ($request['sort_by'] == 'high-low') {
            $fetched = $query->orderBy('unit_price', 'DESC');
        } elseif ($request['sort_by'] == 'a-z') {
            $fetched = $query->orderBy('name', 'ASC');
        } elseif ($request['sort_by'] == 'z-a') {
            $fetched = $query->orderBy('name', 'DESC');
        } else {
            $fetched = $query;
        }

        if ($request['min_price'] != null || $request['max_price'] != null) {
            $fetched = $fetched->whereBetween('unit_price', [Helpers::convert_currency_to_usd($request['min_price']), Helpers::convert_currency_to_usd($request['max_price'])]);
        }

        $data = [
            'id' => $request['id'],
            'name' => $request['name'],
            'data_from' => $request['data_from'],
            'sort_by' => $request['sort_by'],
            'page_no' => $request['page'],
            'min_price' => $request['min_price'],
            'max_price' => $request['max_price'],
            'offer_type' => $request['offer_type'],
            'product_type' => $request['product_type'],
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
            'sub_category_id' => $request['sub_category_id'],
            'sub_sub_category_id' => $request['sub_sub_category_id'],
            'shop_id' => $request['shop_id'],
            'author_id' => $request['author_id'],
            'publishing_house_id' => $request['publishing_house_id'],
        ];

        $products = $fetched->paginate(5)->appends($data);
        $product_ids = $products->pluck('id')->toArray();
        $singlePageProductCount = 5;
        $pageTitleContent = translate($data['offer_type'] ?? $data['data_from']).' '.translate('products');
        $categories = CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting();
        $activeBrands = BrandManager::getActiveBrandWithCountingAndPriorityWiseSorting();
        $ratings = [
            'rating_1' => 0,
            'rating_2' => 0,
            'rating_3' => 0,
            'rating_4' => 0,
            'rating_5' => 0,
        ];
        $selectedRatings = $request['rating'] ?? [];
        $tags_category = [];
        $tags_brands = [];
        $publishingHouse = [];
        $productAuthors = [];
        $sort_by = $request['sort_by'];

        if ($request->ajax()) {
            $isInitialLoad = (int) ($request['page'] ?? 1) <= 1;
            return response()->json([
                'total_product' => $products->total(),
                'html_products' => view(VIEW_FILE_NAMES['products__ajax_partials'], compact('products', 'product_ids', 'singlePageProductCount', 'isInitialLoad'))->render(),
                'view' => view(VIEW_FILE_NAMES['products__ajax_partials'], compact('products', 'product_ids', 'singlePageProductCount', 'isInitialLoad'))->render(),
            ], 200);
        }
        if ($request['data_from'] == 'category') {
            $data['brand_name'] = Category::find((int)$request['id'])?->name ?? translate('category_not_found');
        }
        if ($request['data_from'] == 'brand') {
            $data['brand_name'] = Brand::active()->find((int)$request['id'])?->name ?? translate('brand_not_found');
        }

        return view(VIEW_FILE_NAMES['products_view_page'], compact(
            'products',
            'data',
            'pageTitleContent',
            'categories',
            'activeBrands',
            'ratings',
            'selectedRatings',
            'product_ids',
            'singlePageProductCount',
            'tags_category',
            'tags_brands',
            'publishingHouse',
            'productAuthors',
            'sort_by'
        ), $data);

    }

    public function top_rated(Request $request): View|JsonResponse
    {
        $request->merge(['data_from' => 'top-rated']);
        return $this->discounted_products($request);
    }

    public function best_sell(Request $request): View|JsonResponse
    {
        $request->merge(['data_from' => 'best-selling']);
        return $this->discounted_products($request);
    }

    public function new_product(Request $request): View|JsonResponse
    {
        $request->merge(['data_from' => 'latest']);
        return $this->discounted_products($request);
    }

    public function viewWishlist(Request $request): View
    {
        $brand_setting = BusinessSetting::where('type', 'product_brand')->first()->value;

        $wishlists = Wishlist::with([
            'productFullInfo' => function ($query) {
                $query->with(['digitalVariation', 'clearanceSale' => function ($query) {
                    $query->active();
                }]);
            },
            'productFullInfo.compareList' => function ($query) {
                return $query->where('user_id', auth('customer')->id() ?? 0);
            }
        ])
            ->whereHas('wishlistProduct', function ($q) use ($request) {
                $q->when($request['search'], function ($query) use ($request) {
                    $query->where('name', 'like', "%{$request['search']}%")
                        ->orWhereHas('category', function ($qq) use ($request) {
                            $qq->where('name', 'like', "%{$request['search']}%");
                        });
                });
            })
            ->where('customer_id', auth('customer')->id())->paginate(15);

        return view(VIEW_FILE_NAMES['account_wishlist'], compact('wishlists', 'brand_setting'));
    }

    public function storeWishlist(Request $request)
    {
        if ($request->ajax()) {
            if (auth('customer')->check()) {
                $wishlist = Wishlist::where('customer_id', auth('customer')->id())->where('product_id', $request->product_id)->first();
                if ($wishlist) {
                    $wishlist->delete();

                    $countWishlist = Wishlist::whereHas('wishlistProduct', function ($q) {
                        return $q;
                    })->where('customer_id', auth('customer')->id())->count();
                    $product_count = Wishlist::where(['product_id' => $request->product_id])->count();

                    session()->forget('wish_list');
                    session()->put('wish_list', Wishlist::whereHas('product', function ($query) {
                        return $query->active();
                    })->where('customer_id', auth('customer')->user()->id)->pluck('product_id')->toArray());

                    return response()->json([
                        'error' => translate("product_removed_from_the_wishlist"),
                        'value' => 2,
                        'count' => $countWishlist,
                        'product_count' => $product_count
                    ]);

                } else {
                    $wishlist = new Wishlist;
                    $wishlist->customer_id = auth('customer')->id();
                    $wishlist->product_id = $request->product_id;
                    $wishlist->save();

                    $countWishlist = Wishlist::whereHas('wishlistProduct', function ($q) {
                        return $q;
                    })->where('customer_id', auth('customer')->id())->count();

                    $product_count = Wishlist::where(['product_id' => $request->product_id])->count();
                    session()->forget('wish_list');
                    session()->put('wish_list', Wishlist::whereHas('product', function ($query) {
                        return $query->active();
                    })->where('customer_id', auth('customer')->user()->id)->pluck('product_id')->toArray());

                    return response()->json([
                        'success' => translate("Product has been added to wishlist"),
                        'value' => 1, 'count' => $countWishlist,
                        'id' => $request->product_id,
                        'product_count' => $product_count
                    ]);
                }

            } else {
                return response()->json(['error' => translate('please_login_your_account'), 'value' => 0]);
            }
        }
    }

    public function deleteWishlist(Request $request): JsonResponse
    {
        $this->wishlist->where(['product_id' => $request['id'], 'customer_id' => auth('customer')->id()])->delete();
        $data = translate('product_has_been_remove_from_wishlist') . '!';
        $wishlists = $this->wishlist->where('customer_id', auth('customer')->id())->paginate(15);
        $brand_setting = BusinessSetting::where('type', 'product_brand')->first()->value;
        session()->forget('wish_list');
        session()->put('wish_list', $this->wishlist->whereHas('product', function ($query) {
            return $query->active();
        })->where('customer_id', auth('customer')->id())->pluck('product_id')->toArray());
        return response()->json([
            'success' => $data,
            'count' => count($wishlists),
            'id' => $request->id,
            'wishlist' => view(VIEW_FILE_NAMES['account_wishlist_partials'], compact('wishlists', 'brand_setting'))->render(),
        ]);
    }

    public function deleteAllWishListItems(): RedirectResponse
    {
        $this->wishlist->where('customer_id', auth('customer')->id())->delete();
        session()->forget('wish_list');
        session()->put('wish_list', $this->wishlist->where('customer_id', auth('customer')->id())->pluck('product_id')->toArray());
        return redirect()->back();
    }

    //order Details

    public function chat_for_product(Request $request)
    {
        return $request->all();
    }

    public function supportChat()
    {
        return view('web-views.users-profile.profile.supportTicketChat');
    }

    public function error()
    {
        return view('web-views.404-error-page');
    }

    public function contact_store(Request $request)
    {
        $recaptcha = getWebConfig(name: 'recaptcha');
        if (isset($recaptcha) && $recaptcha['status'] == 1) {

            try {
                $request->validate([
                    'g-recaptcha-response' => [
                        function ($attribute, $value, $fail) {
                            $secret_key = getWebConfig(name: 'recaptcha')['secret_key'];
                            $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $value;
                            // Bounded timeout — an unreachable Google endpoint previously
                            // blocked the contact form submit for the default 60s+ socket timeout.
                            $context = stream_context_create(['http' => ['timeout' => 5]]);
                            $response = @file_get_contents($url, false, $context);
                            $response = $response !== false ? json_decode($response) : null;
                            if (!($response->success ?? false)) {
                                $fail(translate('ReCAPTCHA Failed'));
                            }
                        },
                    ],
                ]);

            } catch (\Exception $exception) {
                return back()->withErrors(translate('Captcha Failed'))->withInput($request->input());
            }
        } else {
            if (strtolower($request->default_captcha_value) != strtolower(Session('default_captcha_code'))) {
                Session::forget('default_captcha_code');
                Toastr::error(translate('captcha_failed'));
                return back()->withInput($request->input());
            }
        }

        $request->validate([
            'mobile_number' => 'required|max:20',
            'subject' => 'required',
            'message' => 'required',
            'email' => 'email',
        ], [
            'mobile_number.required' => translate('phone_number_is_required'),
            'mobile_number.max' => translate('The_phone_number_may_not_be_greater_than_20_characters'),
            'subject.required' => translate('Subject_is_Empty'),
            'message.required' => translate('Message_is_Empty'),
        ]);

        $numericPhoneValue = preg_replace('/[^0-9]/', '', $request['mobile_number']);
        $numericLength = strlen($numericPhoneValue);
        if ($numericLength < 4 || $numericLength > 20) {
            $request->validate([
                'mobile_number' => 'min:5|max:20',
            ], [
                'mobile_number.min' => translate('The_phone_number_must_be_at_least_4_characters'),
                'mobile_number.max' => translate('The_phone_number_may_not_be_greater_than_20_characters'),
            ]);
        }

        $contact = new Contact;
        $contact->name = $request['name'];
        $contact->email = $request['email'];
        $contact->mobile_number = $request['mobile_number'];
        $contact->subject = $request['subject'];
        $contact->message = $request['message'];
        $contact->save();
        Toastr::success(translate('Your_Message_Send_Successfully'));
        return back();
    }

    public function captcha($tmp)
    {

        $phrase = new PhraseBuilder;
        $code = $phrase->build(4);
        $builder = new CaptchaBuilder($code, $phrase);
        $builder->setBackgroundColor(220, 210, 230);
        $builder->setMaxAngle(25);
        $builder->setMaxBehindLines(0);
        $builder->setMaxFrontLines(0);
        $builder->build($width = 100, $height = 40, $font = null);
        $phrase = $builder->getPhrase();

        if (Session::has('default_captcha_code')) {
            Session::forget('default_captcha_code');
        }
        Session::put('default_captcha_code', $phrase);
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        $builder->output();
    }

    public function order_note(Request $request)
    {
        if ($request->has('order_note')) {
            session::put('order_note', $request['order_note']);
        }
        $response = self::checkValidationForCheckoutPages($request);
        return response()->json($response);
    }

    public function checkValidationForCheckoutPages(Request $request): array
    {
        $response['status'] = 1;
        $response['physical_product_view'] = false;
        $message = [];

        $verifyStatus = OrderManager::verifyCartListMinimumOrderAmount($request);
        if ($verifyStatus['status'] == 0) {
            $response['status'] = 0;
            $response['errorType'] = 'minimum-order-amount';
            $response['redirect'] = route('shop-cart');
            foreach ($verifyStatus['messages'] as $verifyStatusMessages) {
                $message[] = $verifyStatusMessages;
            }
        }

        $cartItemGroupIDsAll = CartManager::get_cart_group_ids();
        $cartItemGroupIDs = CartManager::get_cart_group_ids(type: 'checked');

        if (count($cartItemGroupIDsAll) <= 0) {
            $response['status'] = 0;
            $response['errorType'] = 'empty-cart';
            $response['redirect'] = url('/');
            $message[] = translate('no_items_in_basket');
        } elseif (count($cartItemGroupIDs) <= 0) {
            $response['status'] = 0;
            $response['errorType'] = 'empty-shipping';
            $response['redirect'] = route('shop-cart');
            $message[] = translate('Please_add_or_checked_items_before_proceeding_to_checkout');
        }

        $unavailableVendorsStatus = 0;

        $isPhysicalProductExist = false;
        $productStockStatus = true;

        // One query for every cart group instead of one query per group.
        $allCartItems = Cart::whereHas('product', function ($query) {
            return $query->active();
        })->with(['product' => function ($query) {
            return $query->active();
        }])->whereIn('cart_group_id', $cartItemGroupIDs)->where('is_checked', 1)->get()
            ->groupBy('cart_group_id');

        foreach ($cartItemGroupIDs as $groupId) {
            $isPhysicalProductExist = false;
            // ->values() re-indexes from 0 within each group — groupBy() otherwise
            // keeps each item's original position in the combined result set, which
            // would break the "only check the group's first item" logic below.
            $cartList = $allCartItems->get($groupId, collect())->values();
            foreach ($cartList as $cart) {
                if ($cart->product_type == 'physical') {
                    $isPhysicalProductExist = true;
                    $response['physical_product_view'] = true;
                }
            }

            $productStockCheck = CartManager::product_stock_check($cartList);
            if (!$productStockCheck) {
                $productStockStatus = false;
            }

            foreach ($cartList as $cartKey => $cart) {
                if ($cartKey == 0) {
                    if ($cart->seller_is == 'admin') {
                        $inhouseTemporaryClose = getWebConfig(name: 'temporary_close') ? getWebConfig(name: 'temporary_close')['status'] : 0;
                        $inhouseVacation = getWebConfig(name: 'vacation_add');
                        $vacationStartDate = $inhouseVacation['vacation_start_date'] ? date('Y-m-d', strtotime($inhouseVacation['vacation_start_date'])) : null;
                        $vacationEndDate = $inhouseVacation['vacation_end_date'] ? date('Y-m-d', strtotime($inhouseVacation['vacation_end_date'])) : null;
                        $vacationStatus = $inhouseVacation['status'] ?? 0;
                        if ($inhouseTemporaryClose || ($vacationStatus && (date('Y-m-d') >= $vacationStartDate) && (date('Y-m-d') <= $vacationEndDate))) {
                            $unavailableVendorsStatus = 1;
                        }
                    } else {
                        $sellerInfo = Seller::with('shop')->where('id', $cart->seller_id)->first();
                        if (!$sellerInfo || $sellerInfo->status != 'approved') {
                            $unavailableVendorsStatus = 1;
                        }
                        if (!isset($sellerInfo->shop) || ($sellerInfo->shop->temporary_close)) {
                            $unavailableVendorsStatus = 1;
                        }

                        if ($sellerInfo && $sellerInfo->shop->vacation_status) {
                            $vacationStartDate = $sellerInfo->shop->vacation_start_date ? date('Y-m-d', strtotime($sellerInfo->shop->vacation_start_date)) : null;
                            $vacationEndDate = $sellerInfo->shop->vacation_end_date ? date('Y-m-d', strtotime($sellerInfo->shop->vacation_end_date)) : null;
                            if ((date('Y-m-d') >= $vacationStartDate) && (date('Y-m-d') <= $vacationEndDate)) {
                                $unavailableVendorsStatus = 1;
                            }
                        }
                    }
                }
            }
        }

        if ($unavailableVendorsStatus) {
            $message[] = translate('please_remove_all_products_from_unavailable_vendors');
            $response['status'] = 0;
            $response['redirect'] = route('shop-cart');
        }

        if (!$productStockStatus) {
            $message[] = translate('Please_remove_this_unavailable_product_for_continue');
            $response['status'] = 0;
            $response['redirect'] = route('shop-cart');
        }

        $response['message'] = $message;
        return $response ?? [];
    }


public function getDigitalProductDownload($id, Request $request): JsonResponse
    {
        $orderDetailsData = OrderDetail::with('order.customer')->find($id);

        if ($orderDetailsData) {
            // Check 1: Payment Status
            if ($orderDetailsData->order->payment_status !== "paid") {
                return response()->json([
                    'status' => 0,
                    'message' => translate('Payment_must_be_confirmed_first') . ' !!',
                ]);
            }

            // --- Authentication and File Retrieval Logic ---
            $fileName = '';
            $filePath = '';
            $fileExist = false;

            if ($orderDetailsData->order->is_guest) {
                $customerEmail = $orderDetailsData->order->shipping_address_data ? $orderDetailsData->order->shipping_address_data->email : ($orderDetailsData->order->billing_address_data ? $orderDetailsData->order->billing_address_data->email : '');
                $customerPhone = $orderDetailsData->order->shipping_address_data ? $orderDetailsData->order->shipping_address_data->phone : ($orderDetailsData->order->billing_address_data ? $orderDetailsData->order->billing_address_data->phone : '');
                $customerData = ['email' => $customerEmail, 'phone' => $customerPhone];
                
                // Assuming getDigitalProductDownloadProcess handles file retrieval for guests.
                // If it successfully initiates a download, the status update should ideally happen inside that method
                // or if it returns success data that leads to a download. For simplicity, we assume here that for guests
                // the existing flow is correct and doesn't require further change outside that method.
                return self::getDigitalProductDownloadProcess(orderDetailsData: $orderDetailsData, customer: $customerData);
            } else {
                if (auth('customer')->check() && auth('customer')->user()->id == $orderDetailsData->order->customer->id) {
                    $productDetails = json_decode($orderDetailsData['product_details'], true);
                    
                    if ($productDetails['digital_product_type'] == 'ready_product' && $productDetails['digital_file_ready']) {
                        $checkFilePath = storageLink('product/digital-product', $productDetails['digital_file_ready'], ($productDetails['storage_path'] ?? 'public'));
                        $filePath = $checkFilePath['path'];
                        $fileExist = $checkFilePath['status'] == 200;
                        $fileName = $productDetails['digital_file_ready'];
                    } else {
                        $checkFilePath = $orderDetailsData->digital_file_after_sell_full_url;
                        $filePath = $checkFilePath['path'];
                        $fileName = $orderDetailsData['digital_file_after_sell'];
                        $fileExist = $checkFilePath['status'] == 200;
                    }

                    if (!is_null($fileName) && $fileExist) {
                        // **<-- START: ADDED LOGIC FOR STATUS UPDATE ON SUCCESSFUL DIGITAL DOWNLOAD -->**
                        
                        $order = $orderDetailsData->order;

                        if ($order->order_status !== 'delivered') {
                            // Update the main order status to 'delivered'
                            $order->update([
                                'order_status' => 'delivered',
                                'payment_status' => 'paid',
                                'is_pause' => 0 // Assuming 'delivered' means the order is not paused
                            ]);

                            // Update the specific order detail's delivery and payment status
                            $orderDetailsData->update([
                                'delivery_status' => 'delivered',
                                'payment_status' => 'paid'
                            ]);
                            
                            // **Note:** Additional logic like triggering events, updating stock, and managing
                            // loyalty points/wallets (as seen in the OrderController::updateStatus)
                            // should ideally be called here or encapsulated in a service method for consistency.
                            // For this specific scenario, we'll only include the direct DB update as requested,
                            // assuming surrounding logic handles the rest.
                        }
                        
                        if (is_null($orderDetailsData->digital_file_first_accessed_at)) {
                            $orderDetailsData->update([
                                'digital_file_first_accessed_at' => now(),
                                'digital_access_ip' => request()->ip(),
                            ]);
                        }

                        return response()->json([
                            'status' => 1,
                            'file_path' => $filePath,
                            'file_name' => $fileName,
                        ]);
                    } else {
                        return response()->json([
                            'status' => 0,
                            'message' => translate('file_not_found'),
                        ]);
                    }
                } else {
                    $customerData = ['email' => $orderDetailsData->order->customer->email ?? '', 'phone' => $orderDetailsData->order->customer->phone ?? ''];
                    return self::getDigitalProductDownloadProcess(orderDetailsData: $orderDetailsData, customer: $customerData);
                }
            }
        } else {
            return response()->json([
                'status' => 0,
                'message' => translate('order_Not_Found') . ' !',
            ]);
        }
    }

    public function getDigitalProductDownloadOtpVerify(Request $request): JsonResponse
    {
        $verification = DigitalProductOtpVerification::where(['token' => $request->otp, 'order_details_id' => $request->order_details_id])->first();
        $orderDetailsData = OrderDetail::with('order.customer')->find($request->order_details_id);

        if ($verification) {
            $fileName = '';
            $fileExist = false;
            if ($orderDetailsData) {
                $productDetails = json_decode($orderDetailsData['product_details'], true);
                if ($productDetails['digital_product_type'] == 'ready_product' && $productDetails['digital_file_ready']) {
                    $checkFilePath = storageLink('product/digital-product', $productDetails['digital_file_ready'], ($productDetails['storage_path'] ?? 'public'));
                    $filePath = $checkFilePath['path'];
                    $fileExist = $checkFilePath['status'] == 200;
                    $fileName = $productDetails['digital_file_ready'];
                } else {
                    $checkFilePath = $orderDetailsData->digital_file_after_sell_full_url;
                    $filePath = $checkFilePath['path'];
                    $fileName = $orderDetailsData['digital_file_after_sell'];
                    $fileExist = $checkFilePath['status'] == 200;
                }
            }

            DigitalProductOtpVerification::where(['token' => $request->otp, 'order_details_id' => $request->order_details_id])->delete();

            if (!is_null($fileName) && $fileExist) {
                return response()->json([
                    'status' => 1,
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'message' => translate('successfully_verified'),
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => translate('file_not_found'),
                ]);
            }
        } else {
            return response()->json([
                'status' => 0,
                'message' => translate('the_OTP_is_incorrect') . ' !',
            ]);
        }
    }

    public function getDigitalProductDownloadOtpReset(Request $request): JsonResponse
    {
        $tokenInfo = DigitalProductOtpVerification::where(['order_details_id' => $request->order_details_id])->first();
        $otpIntervalTime = getWebConfig(name: 'otp_resend_time') ?? 1; //minute
        if (isset($tokenInfo) && Carbon::parse($tokenInfo->created_at)->diffInSeconds() < $otpIntervalTime) {
            $timeCount = $otpIntervalTime - Carbon::parse($tokenInfo->created_at)->diffInSeconds();

            return response()->json([
                'status' => 0,
                'time_count' => CarbonInterval::seconds($timeCount)->cascade()->forHumans(),
                'message' => translate('Please_try_again_after') . ' ' . CarbonInterval::seconds($timeCount)->cascade()->forHumans()
            ]);
        } else {
            $guestEmail = '';
            $guestPhone = '';
            $token = rand(1000, 9999);

            $orderDetailsData = OrderDetail::with('order.customer')->find($request->order_details_id);

            try {
                if ($orderDetailsData->order->is_guest) {
                    if ($orderDetailsData->order->shipping_address_data) {
                        $guestName = $orderDetailsData->order->shipping_address_data ? $orderDetailsData->order->shipping_address_data->contact_person_name : null;
                        $guestEmail = $orderDetailsData->order->shipping_address_data ? $orderDetailsData->order->shipping_address_data->email : null;
                        $guestPhone = $orderDetailsData->order->shipping_address_data ? $orderDetailsData->order->shipping_address_data->phone : null;
                    } else {
                        $guestName = $orderDetailsData->order->billing_address_data ? $orderDetailsData->order->billing_address_data->contact_person_name : null;
                        $guestEmail = $orderDetailsData->order->billing_address_data ? $orderDetailsData->order->billing_address_data->email : null;
                        $guestPhone = $orderDetailsData->order->billing_address_data ? $orderDetailsData->order->billing_address_data->phone : null;
                    }
                } else {
                    $guestName = $orderDetailsData->order->customer->f_name;
                    $guestEmail = $orderDetailsData->order->customer->email;
                    $guestPhone = $orderDetailsData->order->customer->phone;
                }
            } catch (\Throwable $th) {

            }

            $verifyData = [
                'order_details_id' => $orderDetailsData->id,
                'token' => $token,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DigitalProductOtpVerification::updateOrInsert(['identity' => $guestEmail, 'order_details_id' => $orderDetailsData->id], $verifyData);
            DigitalProductOtpVerification::updateOrInsert(['identity' => $guestPhone, 'order_details_id' => $orderDetailsData->id], $verifyData);

            $emailServicesSmtp = getWebConfig(name: 'mail_config');
            if ($emailServicesSmtp['status'] == 0) {
                $emailServicesSmtp = getWebConfig(name: 'mail_config_sendgrid');
            }
            if ($emailServicesSmtp['status'] == 0) {
                $emailServicesSmtp = getWebConfig(name: 'mail_config_brevo');
            }

            if ($emailServicesSmtp['status'] == 1) {
                try {
                    $data = [
                        'userName' => $guestName,
                        'userType' => 'customer',
                        'templateName' => 'digital-product-otp',
                        'subject' => translate('verification_Code'),
                        'title' => translate('verification_Code') . '!',
                        'verificationCode' => $token,
                    ];
                    event(new DigitalProductOtpVerificationEvent(email: $guestEmail, data: $data));
                    $mailStatus = 1;
                } catch (\Exception $exception) {
                    $mailStatus = 0;
                }
            } else {
                $mailStatus = 0;
            }

            $response = SMSModule::sendCentralizedSMS($guestPhone, $token);
            $smsStatus = $response == "not_found" ? 0 : 1;

            return response()->json([
                'mail_status' => $mailStatus,
                'sms_status' => $smsStatus,
                'status' => ($mailStatus || $smsStatus) ? 1 : 0,
                'new_time' => $otpIntervalTime,
                'message' => ($mailStatus || $smsStatus) ? translate('OTP_sent_successfully') : translate('OTP_sent_fail'),
            ]);

        }
    }

    public function getDigitalProductDownloadProcess($orderDetailsData, $customer): JsonResponse
    {
        $status = 2;
        $emailServicesSmtp = getWebConfig(name: 'mail_config');
        if ($emailServicesSmtp['status'] == 0) {
            $emailServicesSmtp = getWebConfig(name: 'mail_config_sendgrid');
        }
        if ($emailServicesSmtp['status'] == 0) {
            $emailServicesSmtp = getWebConfig(name: 'mail_config_brevo');
        }

        $paymentPublishedStatus = config('get_payment_publish_status') ?? 0;

        if ($paymentPublishedStatus == 1) {
            $smsConfigStatus = Setting::where(['settings_type' => 'sms_config', 'is_active' => 1])->count() > 0 ? 1 : 0;
        } else {
            $smsConfigStatus = Setting::where(['settings_type' => 'sms_config', 'is_active' => 1])->whereIn('key_name', Helpers::getDefaultSMSGateways())->count() > 0 ? 1 : 0;
        }

        if ($emailServicesSmtp['status'] || $smsConfigStatus) {
            $token = rand(1000, 9999);
            if ($customer['email'] == '' && $customer['phone'] == '') {
                return response()->json([
                    'status' => $status,
                    'file_path' => '',
                    'view' => view(VIEW_FILE_NAMES['digital_product_order_otp_verify_failed'])->render(),
                ]);
            }

            $verificationData = DigitalProductOtpVerification::where('identity', $customer['email'])->orWhere('identity', $customer['phone'])->where('order_details_id', $orderDetailsData->id)->latest()->first();
            $otpIntervalTime = getWebConfig(name: 'otp_resend_time') ?? 1; //second

            if (isset($verificationData) && Carbon::parse($verificationData->created_at)->diffInSeconds() < $otpIntervalTime) {
                $timeCount = $otpIntervalTime - Carbon::parse($verificationData->created_at)->diffInSeconds();
                return response()->json([
                    'status' => $status,
                    'file_path' => '',
                    'view' => view(VIEW_FILE_NAMES['digital_product_order_otp_verify'], ['orderDetailID' => $orderDetailsData->id, 'time_count' => $timeCount])->render(),
                ]);
            } else {
                $verifyData = [
                    'order_details_id' => $orderDetailsData->id,
                    'token' => $token,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                DigitalProductOtpVerification::updateOrInsert(['identity' => $customer['email'], 'order_details_id' => $orderDetailsData->id], $verifyData);
                DigitalProductOtpVerification::updateOrInsert(['identity' => $customer['phone'], 'order_details_id' => $orderDetailsData->id], $verifyData);

                $resetData = DigitalProductOtpVerification::where('identity', $customer['email'])->orWhere('identity', $customer['phone'])->where('order_details_id', $orderDetailsData->id)->latest()->first();
                $otpResendTime = getWebConfig(name: 'otp_resend_time') > 0 ? getWebConfig(name: 'otp_resend_time') : 0;
                $tokenTime = Carbon::parse($resetData->created_at);
                $convertTime = $tokenTime->addSeconds($otpResendTime);
                $timeCount = $convertTime > Carbon::now() ? Carbon::now()->diffInSeconds($convertTime) : 0;
                $mailStatus = 0;

                if ($emailServicesSmtp['status'] == 1) {
                    try {
                        $data = [
                            'userName' => $customer['f_name'],
                            'userType' => 'customer',
                            'templateName' => 'digital-product-otp',
                            'subject' => translate('verification_Code'),
                            'title' => translate('verification_Code') . '!',
                            'verificationCode' => $token,
                        ];
                        event(new DigitalProductOtpVerificationEvent(email: $customer['email'], data: $data));
                        $mailStatus = 1;
                    } catch (\Exception $exception) {
                    }
                }

                $response = SMSModule::sendCentralizedSMS($customer['phone'], $token);

                $smsStatus = ($response == "not_found" || $smsConfigStatus == 0) ? 0 : 1;
                if ($mailStatus || $smsStatus) {
                    return response()->json([
                        'status' => $status,
                        'file_path' => '',
                        'view' => view(VIEW_FILE_NAMES['digital_product_order_otp_verify'], ['orderDetailID' => $orderDetailsData->id, 'time_count' => $timeCount])->render(),
                    ]);
                } else {
                    return response()->json([
                        'status' => $status,
                        'file_path' => '',
                        'view' => view(VIEW_FILE_NAMES['digital_product_order_otp_verify_failed'])->render(),
                    ]);
                }
            }
        } else {
            return response()->json([
                'status' => $status,
                'file_path' => '',
                'view' => view(VIEW_FILE_NAMES['digital_product_order_otp_verify_failed'])->render(),
            ]);
        }
    }


    public function subscription(Request $request)
    {
        $request->validate([
            'subscription_email' => 'required|email'
        ]);
        $subscriptionEmail = Subscription::where('email', $request['subscription_email'])->first();

        if (isset($subscriptionEmail)) {
            Toastr::info(translate('You_already_subscribed_this_site'));
        } else {
            $newSubscription = new Subscription;
            $newSubscription->email = $request['subscription_email'];
            $newSubscription->save();
            Toastr::success(translate('Your_subscription_successfully_done'));
        }
        if (str_contains(url()->previous(), 'checkout-complete') || str_contains(url()->previous(), 'web-payment')) {
            return redirect()->route('home');
        }
        return back();
    }

    public function review_list_product(Request $request)
    {
        $productReviews = Review::where('product_id', $request->product_id)->latest()->paginate(2, ['*'], 'page', $request->offset + 1);
        $checkReviews = Review::where('product_id', $request->product_id)->latest()->paginate(2, ['*'], 'page', ($request->offset + 1));
        return response()->json([
            'productReview' => view(VIEW_FILE_NAMES['product_reviews_partials'], compact('productReviews'))->render(),
            'not_empty' => $productReviews->count(),
            'checkReviews' => $checkReviews->count(),
        ]);
    }

    public function getShopReviewList(Request $request): JsonResponse
    {
        $sellerId = 0;
        if ($request['shop_id'] != 0) {
            $sellerId = Shop::where('id', $request['shop_id'])->first()->seller_id;
        }
        $getProductIds = Product::active()
            ->when($request['shop_id'] == 0, function ($query) {
                return $query->where(['added_by' => 'admin']);
            })
            ->when($request['shop_id'] != 0, function ($query) use ($sellerId) {
                return $query->where(['added_by' => 'seller', 'user_id' => $sellerId]);
            })
            ->pluck('id')->toArray();

        $productReviews = Review::active()->whereIn('product_id', $getProductIds)->latest()->paginate(4, ['*'], 'page', $request['offset'] + 1);
        $checkReviews = Review::active()->whereIn('product_id', $getProductIds)->latest()->paginate(4, ['*'], 'page', ($request['offset'] + 1));

        return response()->json([
            'productReview' => view(VIEW_FILE_NAMES['product_reviews_partials'], compact('productReviews'))->render(),
            'not_empty' => $productReviews->count(),
            'checkReviews' => $checkReviews->count(),
        ]);
    }

    public function product_view_style(Request $request)
    {
        Session::put('product_view_style', $request->value);
        return response()->json([
            'message' => translate('View_style_updated') . "!",
        ]);
    }


    public function pay_offline_method_list(Request $request)
    {
        $method = OfflinePaymentMethod::where(['id' => $request->method_id, 'status' => 1])->first();
        return response()->json([
            'methodHtml' => view(VIEW_FILE_NAMES['pay_offline_method_list_partials'], compact('method'))->render(),
        ]);
    }

}
