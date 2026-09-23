<?php

namespace App\Providers;

use App\Models\Chatting;
use App\Models\CustomerNotification;
use App\Models\FlashDealProduct;
use App\Models\LoginSetup;
use App\Models\Notification;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\StockClearanceProduct;
use App\Traits\CacheManagerTrait;
use App\Traits\FileManagerTrait;
use App\Traits\UpdateClass;
use App\Utils\Helpers;
use App\Enums\GlobalConstant;
use App\Models\Currency;
use App\Models\FreelancerCategory;
use App\Models\FreelancerQuoteRequest;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\SocialMedia;
use App\Models\FlashDeal;
use App\Models\FreelancerSpecialization;
use App\Models\Product;
use App\Traits\AddonHelper;
use App\Traits\ThemeHelper;
use App\Utils\ProductManager;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\VendorNotification;
use App\Helpers\AdHelper; // <-- Add this
use Illuminate\Support\Facades\Blade; // <-- Add this


ini_set('memory_limit', -1);
ini_set('upload_max_filesize', '180M');
ini_set('post_max_size', '200M');

class AppServiceProvider extends ServiceProvider
{

    use AddonHelper;
    use CacheManagerTrait;
    use FileManagerTrait;
    use ThemeHelper;
    use UpdateClass;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Amirami\Localizator\ServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public function boot(): void
    {
        if (!in_array(request()->ip(), ['127.0.0.1', '::1']) && env('FORCE_HTTPS')) {
            \URL::forceScheme('https');
        }
        Schema::defaultStringLength(191);
        // Share unread notifications with the vendor layout
                View::composer('layouts.vendor.app', function ($view) {
                    if (Auth::guard('seller')->check()) {
                        $seller = Auth::guard('seller')->user();
                        $sellerId = $seller->id;

                        // Preload once on the cached guard instance so every later
                        // auth('seller')->user()->shop / ->vendorVerification access
                        // (in the header/sidebar partials) reuses this, not a new query.
                        $seller->loadMissing(['shop', 'vendorVerification']);

                        // This composer runs on every vendor page render (including the
                        // page a form redirects back to), so a short cache turns repeat
                        // navigations within the same few seconds into zero extra queries
                        // instead of 6. Badge counts aren't live anyway (no JS polling
                        // reads these values), so a brief staleness window is unnoticeable.
                        $badgeData = Cache::remember("vendor_layout_badges_{$sellerId}", now()->addSeconds(15), function () use ($sellerId, $seller) {
                            // Non-KYC unread notifications (Listing Limit, Plan Expiry, etc.)
                            $unreadNotificationQuery = VendorNotification::where('seller_id', $sellerId)
                                ->whereNull('read_at')
                                ->whereNotIn('title', ['KYC Approved', 'KYC Rejected']);
                            $unreadNotificationCount = (clone $unreadNotificationQuery)->count();
                            $unreadNotifications = $unreadNotificationQuery
                                ->orderBy('created_at', 'desc')
                                ->take(20)
                                ->get();

                            // Computed once here instead of separately in both
                            // _header.blade.php and _side-bar.blade.php.
                            $orderStatusCounts = Order::where(['seller_is' => 'seller', 'seller_id' => $sellerId])
                                ->selectRaw('order_status, count(*) as total')
                                ->groupBy('order_status')
                                ->pluck('total', 'order_status');

                            $refundStatusCounts = RefundRequest::whereHas('order', function ($query) use ($sellerId) {
                                $query->where('seller_is', 'seller')->where('seller_id', $sellerId);
                            })->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

                            // One aggregate query instead of 3 separate Chatting::count() calls.
                            $chattingCounts = Chatting::where('seller_id', $sellerId)->selectRaw(
                                'SUM(CASE WHEN seen_by_seller = 0 THEN 1 ELSE 0 END) as unseen_total,
                                 SUM(CASE WHEN seen_by_seller = 0 AND user_id IS NOT NULL THEN 1 ELSE 0 END) as unseen_customer,
                                 SUM(CASE WHEN seen_by_seller = 1 THEN 1 ELSE 0 END) as seen_total'
                            )->first();

                            $systemNotificationCount = Notification::whereBetween('created_at', [$seller->created_at, \Carbon\Carbon::now()])
                                ->where('sent_to', 'seller')
                                ->whereDoesntHave('notificationSeenBy')
                                ->count();

                            // Capped: this previously had no limit and grew with account age.
                            $systemNotifications = Notification::whereBetween('created_at', [$seller->created_at, \Carbon\Carbon::now()])
                                ->where('sent_to', 'seller')
                                ->with('notificationSeenBy')
                                ->latest()
                                ->take(20)
                                ->get();

                            return [
                                'unreadNotifications' => $unreadNotifications,
                                'unreadNotificationCount' => $unreadNotificationCount,
                                'orderStatusCounts' => $orderStatusCounts,
                                'refundStatusCounts' => $refundStatusCounts,
                                'chattingCounts' => $chattingCounts,
                                'systemNotificationCount' => $systemNotificationCount,
                                'systemNotifications' => $systemNotifications,
                            ];
                        });

                        // KYC notification uses the sellers.kyc_notification_seen flag —
                        // read fresh from the auth guard's model, not cached, so a vendor
                        // who just acted on it doesn't see a stale value.
                        $kycNotificationSeen = (int) ($seller->kyc_notification_seen ?? 1);

                        $view->with(array_merge($badgeData, [
                            'kycNotificationSeen' => $kycNotificationSeen,
                            'kycVerificationStatus' => $seller->vendorVerification?->status,
                        ]));
                    } else {
                        $view->with([
                            'unreadNotifications' => collect(),
                            'unreadNotificationCount' => 0,
                            'kycNotificationSeen' => 1,
                            'kycVerificationStatus' => null,
                            'orderStatusCounts' => collect(),
                            'refundStatusCounts' => collect(),
                            'chattingCounts' => (object) ['unseen_total' => 0, 'unseen_customer' => 0, 'seen_total' => 0],
                            'systemNotificationCount' => 0,
                            'systemNotifications' => collect(),
                        ]);
                    }
                });

        // Share unread notifications with the freelancer layout
        View::composer('layouts.freelancer.app', function ($view) {
            if (Auth::guard('freelancer')->check()) {
                $seller = Auth::guard('freelancer')->user();
                $sellerId = $seller->id;

                $seller->loadMissing(['shop', 'vendorVerification']);

                $unreadNotificationQuery = VendorNotification::where('seller_id', $sellerId)
                            ->whereNull('read_at')
                            ->whereNotIn('title', ['KYC Approved', 'KYC Rejected']);
                        $unreadNotificationCount = (clone $unreadNotificationQuery)->count();
                        $unreadNotifications = $unreadNotificationQuery
                            ->orderBy('created_at', 'desc')
                            ->take(20)
                            ->get();

                $kycNotificationSeen = (int) ($seller->kyc_notification_seen ?? 1);

                $chattingCounts = Chatting::where('seller_id', $sellerId)->selectRaw(
                    'SUM(CASE WHEN seen_by_seller = 0 THEN 1 ELSE 0 END) as unseen_total,
                     SUM(CASE WHEN seen_by_seller = 0 AND user_id IS NOT NULL THEN 1 ELSE 0 END) as unseen_customer,
                     SUM(CASE WHEN seen_by_seller = 0 AND admin_id IS NOT NULL AND user_id IS NULL THEN 1 ELSE 0 END) as unseen_admin,
                     SUM(CASE WHEN seen_by_seller = 1 THEN 1 ELSE 0 END) as seen_total'
                )->first();

                $systemNotificationCount = Notification::whereBetween('created_at', [$seller->created_at, \Carbon\Carbon::now()])
                    ->where('sent_to', 'seller')
                    ->whereDoesntHave('notificationSeenBy')
                    ->count();

                $systemNotifications = Notification::whereBetween('created_at', [$seller->created_at, \Carbon\Carbon::now()])
                    ->where('sent_to', 'seller')
                    ->with('notificationSeenBy')
                    ->latest()
                    ->take(20)
                    ->get();

                // Computed once here instead of a raw query in _side-bar.blade.php
                // (was running on every freelancer page load).
                $pendingQuoteCount = FreelancerQuoteRequest::where('seller_id', $sellerId)
                    ->where('status', 'pending')
                    ->count();

                $view->with([
                    'unreadNotifications' => $unreadNotifications,
                    'unreadNotificationCount' => $unreadNotificationCount,
                    'kycNotificationSeen' => $kycNotificationSeen,
                    'kycVerificationStatus' => $seller->vendorVerification?->status,
                    'chattingCounts' => $chattingCounts,
                    'systemNotificationCount' => $systemNotificationCount,
                    'systemNotifications' => $systemNotifications,
                    'pendingQuoteCount' => $pendingQuoteCount,
                ]);
            } else {
                $view->with([
                    'unreadNotifications' => collect(),
                    'unreadNotificationCount' => 0,
                    'kycNotificationSeen' => 1,
                    'kycVerificationStatus' => null,
                    'chattingCounts' => (object) ['unseen_total' => 0, 'unseen_customer' => 0, 'unseen_admin' => 0, 'seen_total' => 0],
                    'systemNotificationCount' => 0,
                    'systemNotifications' => collect(),
                    'pendingQuoteCount' => 0,
                ]);
            }
        });

        View::composer('layouts.front-end.partials._header', function ($view) {
            $customerNotifications = collect();
            $customerUnreadNotificationCount = 0;

            if (Auth::guard('customer')->check()) {
                $customerId = Auth::guard('customer')->id();
                $customerNotifications = CustomerNotification::where('customer_id', $customerId)
                    ->orderBy('created_at', 'desc')
                    ->take(20)
                    ->get();
                $customerUnreadNotificationCount = $customerNotifications->whereNull('read_at')->count();
            }

            $view->with([
                'customerNotifications' => $customerNotifications,
                'customerUnreadNotificationCount' => $customerUnreadNotificationCount,
            ]);
            $hireFreelancerMenu = Cache::remember('frontend_hire_freelancer_menu', now()->addMinutes(30), function () {
                if (!Schema::hasTable('freelancer_categories') || !Schema::hasTable('freelancer_specializations')) {
                    return collect();
                }

                $specializations = FreelancerSpecialization::active()
                    ->whereHas('category', function ($query) {
                        $query->active()
                            ->whereNull('parent_id')
                            ->where('position', 0);
                    })
                    ->orderBy('priority', 'asc')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->groupBy('freelancer_category_id');

                return FreelancerCategory::active()
                    ->whereNull('parent_id')
                    ->where('position', 0)
                    ->orderBy('priority', 'asc')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($category) use ($specializations) {
                        $category->setRelation('activeSpecializations', $specializations->get($category->id, collect())->values());
                        return $category;
                    });
            });

            $view->with('hireFreelancerMenu', $hireFreelancerMenu);
        });






        if (!App::runningInConsole()) {
            Paginator::useBootstrap();

            Config::set('addon_admin_routes', $this->getAddonAdminRoutes());
            Config::set('get_payment_publish_status', $this->getPaymentPublishStatus());
            Config::set('get_theme_routes', $this->getThemeRoutesArray());

            $web_config = [
                'primary_color' => '',
                'secondary_color' => '',
                'primary_color_light' => '',
                'panel_sidebar_color' => '',
                'name' => '',
                'company_name' => '',
                'phone' => '',
                'web_logo' => ['path' => ''],
                'mob_logo' => ['path' => ''],
                'fav_icon' => ['path' => ''],
                'email' => '',
                'about' => '',
                'footer_logo' => ['path' => ''],
                'copyright_text' => '',
                'decimal_point_settings' => 0,
                'seller_registration' => 0,
                'wallet_status' => 0,
                'loyalty_point_status' => 0,
                'guest_checkout_status' => 0,
                'digital_product_setting' => 0,
                'language' => [],
                'publishing_houses' => null,
                'digital_product_authors' => null,
                'firebase_otp_verification' => [],
                'firebase_otp_verification_status' => 0,
                'meta_description' => '',
                'default_meta_content' => null,
                'social_media' => collect(),
                'business_pages' => collect(),
            ];
            $language = [];

            try {
                if (Schema::hasTable('business_settings')) {
                    $this->setStorageConnectionEnvironment();
                    $this->cacheInHouseShopInTemporaryStatus();

                    $web = $this->cacheBusinessSettingsTable();

                    $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification');
                    $firebaseCredentials = getWebConfig(name: 'fcm_credentials') ?? [];
                    $firebaseOtpApiKey = $firebaseOTPVerification['web_api_key'] ?? ($firebaseCredentials['apiKey'] ?? null);
                    $firebaseOTPVerificationStatus = (int)($firebaseOTPVerification && $firebaseOTPVerification['status'] && $firebaseOtpApiKey);

                    $systemColors = getWebConfig('colors');
                    $web_config = [
                        'primary_color' => $systemColors['primary'] ?? '',
                        'secondary_color' => $systemColors['secondary'] ?? '',
                        'primary_color_light' => $systemColors['primary_light'] ?? '',
                        'panel_sidebar_color' => $systemColors['panel-sidebar'] ?? '',
                        'name' => Helpers::get_settings($web, 'company_name'),
                        'company_name' => getWebConfig(name: 'company_name'),
                        'phone' => getWebConfig(name: 'company_phone'),
                        'web_logo' => getWebConfig(name: 'company_web_logo') ?? ['path' => ''],
                        'mob_logo' => getWebConfig(name:'company_mobile_logo') ?? ['path' => ''],
                        'fav_icon' => getWebConfig(name: 'company_fav_icon') ?? ['path' => ''],
                        'email' => getWebConfig(name: 'company_email'),
                        'about' => Helpers::get_settings($web, 'about_us'),
                        'footer_logo' => getWebConfig(name: 'company_footer_logo') ?? ['path' => ''],
                        'copyright_text' => getWebConfig(name: 'company_copyright_text'),
                        'decimal_point_settings' => !empty(getWebConfig(name: 'decimal_point_settings')) ? getWebConfig(name: 'decimal_point_settings') : 0,
                        'seller_registration' => getWebConfig(name: 'seller_registration') ?? 0,
                        'wallet_status' => getWebConfig(name: 'wallet_status'),
                        'loyalty_point_status' => getWebConfig(name: 'loyalty_point_status'),
                        'guest_checkout_status' => getWebConfig(name: 'guest_checkout'),
                        'digital_product_setting' => getWebConfig(name:'digital_product'),
                        'language' => getWebConfig(name: 'language'),
                        'publishing_houses' => Schema::hasTable('publishing_houses') ? ProductManager::getPublishingHouseList() : null,
                        'digital_product_authors' => Schema::hasTable('authors') ? ProductManager::getProductAuthorList() : null,
                        'firebase_otp_verification' => $firebaseOTPVerification,
                        'firebase_otp_verification_status' => $firebaseOTPVerificationStatus,
                        'meta_description' => substr(strip_tags(str_replace('&nbsp;', ' ', (getWebConfig(name: 'about_us') ?? ''))),0,160),
                    ];

                    if ((!Request::is('admin') && !Request::is('admin/*') && !Request::is('seller/*') && !Request::is('vendor/*')) || Request::is('vendor/auth/registration/*')) {
                        $userId = Auth::guard('customer')->user() ? Auth::guard('customer')->id() : 0;
                        $flashDeal = ProductManager::getPriorityWiseFlashDealsProductsQuery(userId: $userId);

                        $shops = Shop::whereHas('seller', function ($query) {
                            return $query->approved();
                        })->take(9)->get();

                        $recaptcha = getWebConfig(name: 'recaptcha');
                        $paymentGatewayPublishedStatus = config('get_payment_publish_status') ?? 0;

                        $paymentGatewaysQuery = Setting::whereIn('settings_type', ['payment_config'])->where('is_active', 1);
                        if ($paymentGatewayPublishedStatus == 1) {
                            $paymentsGatewaysList = $paymentGatewaysQuery->select('key_name', 'additional_data')->get();
                        } else {
                            $paymentsGatewaysList = $paymentGatewaysQuery->whereIn('key_name', GlobalConstant::DEFAULT_PAYMENT_GATEWAYS)->select('key_name', 'additional_data')->get();
                        }

                        $customerLoginOptions = LoginSetup::where(['key' => 'login_options'])->first()?->value ?? '';
                        $customerSocialLoginOptions = LoginSetup::where(['key' => 'social_media_for_login'])->first()?->value ?? '';
                        $customerSocialLoginOptions = json_decode($customerSocialLoginOptions, true) ?? [];
                        $socialLoginConfigStatus = $this->checkCustomerSocialMediaLoginAbility();

                        foreach ($customerSocialLoginOptions as $socialKey => $socialLoginService) {
                            $customerSocialLoginOptions[$socialKey] = isset($socialLoginConfigStatus[$socialKey]) && $socialLoginConfigStatus[$socialKey] ? 1 : 0;
                        }

                        $socialLoginTextShowStatus = false;
                        foreach ($customerSocialLoginOptions as $socialLoginService) {
                            if ($socialLoginService == 1) {
                                $socialLoginTextShowStatus = true;
                            }
                        }
                        $totalDiscountProducts = Product::active()
                            ->withCount('reviews')
                            ->where(function ($subQuery) {
                                return $subQuery->where(function ($query) {
                                    return $query->where('discount', '!=', 0);
                                })->orWhere(function ($query) {
                                    $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                                    return $query->whereIn('id', $stockClearanceProductIds);
                                });
                            })
                            ->count();

                        $web_config += [
                            'cookie_setting' => Helpers::get_settings($web, 'cookie_setting'),
                            'announcement' => getWebConfig(name: 'announcement'),
                            'currency_model' => getWebConfig(name: 'currency_model'),
                            'currencies' => Currency::where(['status' => 1])->get(),
                            'main_categories' => $this->cacheMainCategoriesList(),
                            'priority_wise_brands' => $this->cachePriorityWiseBrandList(),
                            'business_mode' => getWebConfig(name: 'business_mode'),
                            'social_media' => SocialMedia::where('active_status', 1)->get(),
                            'ios' => getWebConfig(name: 'download_app_apple_stroe'),
                            'android' => getWebConfig(name: 'download_app_google_stroe'),
                            'refund_policy' => getWebConfig(name: 'refund-policy'),
                            'return_policy' => getWebConfig(name: 'return-policy'),
                            'cancellation_policy' => getWebConfig(name: 'cancellation-policy'),
                            'shipping_policy' => getWebConfig(name: 'shipping-policy'),
                            'flash_deals' => $flashDeal['flashDeal'],
                            'flash_deals_products' => $flashDeal['flashDealProducts'] ?? [],
                            'shops' => $shops,
                            'brand_setting' => getWebConfig(name: 'product_brand'),
                            'discount_product' => $totalDiscountProducts,
                            'recaptcha' => $recaptcha,
                            'socials_login' => getWebConfig(name: 'social_login'),
                            'social_login_text' => $socialLoginTextShowStatus,
                            'popup_banner' => $this->cacheBannerTable(bannerType: 'Popup Banner'),
                            'header_banner' => $this->cacheBannerTable(bannerType: 'Header Banner'),
                            'payments_list' => $paymentsGatewaysList, // Fashion_theme
                            'ref_earning_status' => getWebConfig('ref_earning_status'),
                            'customer_login_options' => json_decode($customerLoginOptions, true),
                            'customer_social_login_options' => $customerSocialLoginOptions,
                            'customer_phone_verification' => getLoginConfig(key: 'phone_verification'),
                            'customer_email_verification' => getLoginConfig(key: 'email_verification'),
                            'default_meta_content' => $this->cacheRobotsMetaContent(page: 'default'),
                            'analytic_scripts' => $this->cacheActiveAnalyticScript(),
                            'clearance_sale_product_count' => $this->cacheClearanceSaleProductsCount(),
                            'business_pages' => $this->cacheBusinessPagesTable(),
                        ];

                        if (theme_root_path() == "theme_fashion") {
                            $featuresSection = [
                                'features_section_top' => getWebConfig(name: 'features_section_top') ?? [],
                                'features_section_middle' => getWebConfig(name: 'features_section_middle') ?? [],
                                'features_section_bottom' => getWebConfig(name: 'features_section_bottom') ?? [],
                            ];

                            $tags = $this->cacheTagTable();

                            $web_config += [
                                'tags' => $tags,
                                'features_section' => $featuresSection,
                                'total_discount_products' => $totalDiscountProducts,
                                'products_stock_limit' => getWebConfig(name: 'stock_limit'),
                            ];
                        }
                    }

                    // Language
                    $language = getWebConfig(name: 'language') ?? [];

                    // Currency
                    \App\Utils\Helpers::currency_load();

                    Schema::defaultStringLength(191);
                }
            } catch (\Exception $exception) {

            }

            View::share(['web_config' => $web_config, 'language' => $language]);
        }






        /**
         * Paginate a standard Laravel Collection.
         *
         * @param int $perPage
         * @param int $total
         * @param int $page
         * @param string $pageName
         * @return array
         */

        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });


        /**
         * Register the @ads() Blade directive.
         * This will parse content for [ad] shortcodes.
         */
       Blade::directive('ads', function ($expression) {
            // Check if an expression was passed.
            // If the user just wrote @ads, $expression will be empty.
            if (empty($expression)) {
                // Return an empty string to prevent the error.
                return "<?php echo ''; ?>";
            }

            // If an expression was passed, parse it.
            return "<?php echo app(\App\Helpers\AdHelper::class)->parse($expression); ?>";
        });

    }
}
