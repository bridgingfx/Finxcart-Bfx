<?php

use App\Enums\ViewPaths\Web\Chatting;
use App\Enums\ViewPaths\Web\ProductCompare;
use App\Enums\ViewPaths\Web\ShopFollower;
use App\Http\Controllers\Customer\Auth\CustomerAuthController;
use App\Http\Controllers\Customer\Auth\ForgotPasswordController;
use App\Http\Controllers\Customer\Auth\LoginController;
use App\Http\Controllers\Customer\Auth\RegisterController;
use App\Http\Controllers\Customer\Auth\SocialAuthController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\SystemController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\ChattingController;
use App\Http\Controllers\Web\CouponController;
use App\Http\Controllers\Web\DigitalProductDownloadController;
use App\Http\Controllers\Web\HireFreelancerController;
use App\Http\Controllers\Web\FreelancerHireController;
use App\Http\Controllers\Web\FreelancerQuoteController;
use App\Http\Controllers\Web\FreelancerContractController;
use App\Http\Controllers\Web\FreelancerReviewController;
use App\Http\Controllers\Web\FreelancerContractAttachmentController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ProductCompareController;
use App\Http\Controllers\Web\ProductDetailsController;
use App\Http\Controllers\Web\ProductListController;
use App\Http\Controllers\Web\Shop\ShopFollowerController;
use App\Http\Controllers\Web\ShopViewController;
use App\Http\Controllers\Web\UserProfileController;
use App\Http\Controllers\Web\UserWalletController;
use App\Http\Controllers\Web\WebController;
use Illuminate\Support\Facades\Route;
use App\Enums\ViewPaths\Web\Review;
use App\Enums\ViewPaths\Web\UserLoyalty;
use App\Http\Controllers\Web\CurrencyController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\UserLoyaltyController;
use App\Http\Controllers\Payment_Methods\SslCommerzPaymentController;
use App\Http\Controllers\Payment_Methods\StripePaymentController;
use App\Http\Controllers\Payment_Methods\PaymobController;
use App\Http\Controllers\Payment_Methods\FlutterwaveV3Controller;
use App\Http\Controllers\Payment_Methods\PaytmController;
use App\Http\Controllers\Payment_Methods\PaypalPaymentController;
use App\Http\Controllers\Payment_Methods\PaytabsController;
use App\Http\Controllers\Payment_Methods\LiqPayController;
use App\Http\Controllers\Payment_Methods\RazorPayController;
use App\Http\Controllers\Payment_Methods\SenangPayController;
use App\Http\Controllers\Payment_Methods\MercadoPagoController;
use App\Http\Controllers\Payment_Methods\BkashPaymentController;
use App\Http\Controllers\Payment_Methods\NowpaymentsController;
use App\Http\Controllers\Payment_Methods\PaystackController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdTrackingController;
use App\Http\Controllers\InfluencerController;
use App\Http\Controllers\Admin\InfluencerAdminController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\Vendor\SumsubController;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use Illuminate\Support\Facades\Log;

Route::get('/about-us', fn () => redirect()->route('business-page.view', ['slug' => 'about-us'], 302));
Route::get('/contact', fn () => redirect()->route('contacts', [], 302));
Route::get('/contact-us', fn () => redirect()->route('contacts', [], 302));
Route::get('/faq', fn () => redirect()->route('helpTopic', [], 302));
Route::get('/tracking', fn () => redirect()->route('track-order.index', [], 302));
Route::get('/terms-and-conditions', fn () => redirect()->route('business-page.view', ['slug' => 'terms-and-conditions'], 302));
Route::get('/refund-policy', fn () => redirect()->route('business-page.view', ['slug' => 'refund-policy'], 302));
Route::get('/flash-deals', fn () => redirect()->route('products', [], 302));
Route::get('/checkout', fn () => redirect()->route('checkout-details', [], 302));
Route::get('/hire-freelancer', [HireFreelancerController::class, 'index'])->name('hire-freelancer');
Route::get('/hire-freelancer/profile/{seller}', [HireFreelancerController::class, 'show'])->name('hire-freelancer.show');
Route::get('/hire-freelancer/service/{service}', [HireFreelancerController::class, 'showService'])->name('hire-freelancer.service-show');

Route::get('/test-log', function () {
    info('Testing Slack logging from Laravel!');
    return 'Slack log triggered!';
});

Route::get('/test-mail-debug', function () {
    try {
        $config = \App\Models\BusinessSetting::where('type', 'mail_config')->first();
        if (!$config) {
            return response()->json([
                'status' => 'FAILED',
                'error' => 'mail_config row not found',
                'config_used' => config('mail.mailers.smtp'),
            ], 404);
        }

        $mailConfig = json_decode($config->value, true);
        if (!is_array($mailConfig)) {
            return response()->json([
                'status' => 'FAILED',
                'error' => 'mail_config JSON is invalid',
                'config_used' => config('mail.mailers.smtp'),
            ], 500);
        }

        $fromAddress = $mailConfig['from'] ?? ($mailConfig['email_id'] ?? ($mailConfig['username'] ?? null));
        $fromName = $mailConfig['name'] ?? config('app.name', 'Finxcart');
        $testEmail = request('email', $mailConfig['username'] ?? null);
        $timestamp = now()->toString();
        $encryption = strtolower($mailConfig['encryption'] ?? 'tls');

        config(['mail.default' => 'smtp']);
        config(['mail.mailers.smtp.transport' => 'smtp']);
        config(['mail.mailers.smtp.host' => $mailConfig['host'] ?? null]);
        config(['mail.mailers.smtp.port' => $mailConfig['port'] ?? null]);
        config(['mail.mailers.smtp.encryption' => $encryption]);
        config(['mail.mailers.smtp.username' => $mailConfig['username'] ?? null]);
        config(['mail.mailers.smtp.password' => $mailConfig['password'] ?? null]);
        config(['mail.mailers.smtp.timeout' => null]);
        config(['mail.from.address' => $fromAddress]);
        config(['mail.from.name' => $fromName]);
        config(['mail.driver' => 'smtp']);
        config(['mail.host' => $mailConfig['host'] ?? null]);
        config(['mail.port' => $mailConfig['port'] ?? null]);
        config(['mail.encryption' => $encryption]);
        config(['mail.username' => $mailConfig['username'] ?? null]);
        config(['mail.password' => $mailConfig['password'] ?? null]);
        $socketScheme = $encryption === 'ssl' ? 'ssl://' : '';
        $socketTarget = $socketScheme . ($mailConfig['host'] ?? '');
        $socketPort = (int)($mailConfig['port'] ?? 0);
        $socket = @fsockopen($socketTarget, $socketPort, $errno, $errstr, 10);
        if ($socket) {
            $banner = fgets($socket, 512);
            fclose($socket);
            $socketTest = 'CONNECTED: ' . trim((string)$banner);
        } else {
            $socketTest = 'FAILED: ' . $errno . ' - ' . $errstr;
        }

        \Log::info('MAIL SOCKET TEST COMPLETED', [
            'target' => $socketTarget,
            'port' => $socketPort,
            'result' => $socketTest,
        ]);

        $directSend = [
            'mailer' => null,
            'result' => null,
            'failures' => [],
        ];

        if (class_exists(\Swift_Mailer::class) && class_exists(\Swift_SmtpTransport::class) && class_exists(\Swift_Message::class)) {
            $transport = new \Swift_SmtpTransport($mailConfig['host'], $socketPort, $encryption);
            $transport->setUsername($mailConfig['username']);
            $transport->setPassword($mailConfig['password']);

            $mailer = new \Swift_Mailer($transport);
            $message = (new \Swift_Message('Direct Swift Test ' . $timestamp))
                ->setFrom([$fromAddress => $fromName])
                ->setTo([$testEmail])
                ->setBody('Direct Swift Mailer test at ' . $timestamp);

            $failures = [];
            $result = $mailer->send($message, $failures);

            $directSend = [
                'mailer' => 'swift',
                'result' => $result,
                'failures' => $failures,
            ];
        } elseif (class_exists(\Symfony\Component\Mailer\Transport::class)
            && class_exists(\Symfony\Component\Mailer\Mailer::class)
            && class_exists(\Symfony\Component\Mime\Email::class)) {
            $dsn = sprintf(
                'smtp://%s:%s@%s:%d?encryption=%s',
                rawurlencode((string)$mailConfig['username']),
                rawurlencode((string)$mailConfig['password']),
                $mailConfig['host'],
                $socketPort,
                $encryption
            );

            $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);
            $mailer = new \Symfony\Component\Mailer\Mailer($transport);

            $email = (new \Symfony\Component\Mime\Email())
                ->from(new \Symfony\Component\Mime\Address($fromAddress, $fromName))
                ->to($testEmail)
                ->subject('Direct Symfony Test ' . $timestamp)
                ->text('Direct Symfony Mailer test at ' . $timestamp);

            $mailer->send($email);

            $directSend = [
                'mailer' => 'symfony',
                'result' => 'sent',
                'failures' => [],
            ];
        } else {
            $directSend = [
                'mailer' => 'unavailable',
                'result' => 'No direct mailer class available',
                'failures' => [],
            ];
        }

        \Log::info('DIRECT MAIL TEST COMPLETED', [
            'to' => $testEmail,
            'mailer' => $directSend['mailer'],
            'result' => $directSend['result'],
            'failures' => $directSend['failures'],
        ]);

        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'Direct mail debug completed for ' . $testEmail,
            'timestamp' => $timestamp,
            'socket_test' => $socketTest,
            'direct_send' => $directSend,
            'config_used' => [
                'host' => $mailConfig['host'] ?? null,
                'port' => $mailConfig['port'] ?? null,
                'encryption' => $encryption,
                'username' => $mailConfig['username'] ?? null,
                'from' => $fromAddress,
                'from_name' => $fromName,
            ],
        ]);
    } catch (\Exception $e) {
        \Log::error('TEST MAIL FAILED', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'config' => config('mail.mailers.smtp'),
        ]);

        return response()->json([
            'status' => 'FAILED',
            'error' => $e->getMessage(),
            'config_used' => config('mail.mailers.smtp'),
        ], 500);
    }
})->middleware('auth:admin');

 Route::prefix('chat')->group(function () {
        Route::post('/start', [ChatController::class, 'startChat'])->name('chat.start');
        Route::post('/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    });
Route::get('/test-broadcast-auth', function() {
    return response()->json([
        'broadcasting_routes_loaded' => Route::has('broadcasting.auth'),
        'session_id' => session()->getId(),
        'csrf_token' => csrf_token(),
    ]);
});

Route::post('/vendor/kyc/sumsub-webhook', [SumsubController::class, 'webhook'])
    ->name('vendor.kyc.sumsub-webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/warranty_policy', function() {
    return view('default.web-views.warranty_policy');
})->name('warranty.policy');

Route::get('/terms-of-use', function () {
    return view('default.web-views.terms_of_use');
})->name('terms.of.use');

Route::get('/terms-of-sale', function () {
    return view('default.web-views.terms_of_sale');
})->name('terms.of.sale');

Route::get('/privacy-policy', function () {
    return view('default.web-views.privacy_policy');
})->name('privacy.policy');

Route::get('/consumer-rights', function () {
    return view('default.web-views.consumer_rights');
})->name('consumer.rights');

Route::get('/my-account-help', function () {
    if (Auth::guard('customer')->check()) {
        return redirect()->route('user-account');
    }

    return view('default.web-views.my_account_help');
})->name('my.account.help');

Route::get('/customer-service', function () {
    return view('default.web-views.customer_service');
})->name('customer.service');

Route::get('/product-support', function () {
    return view('default.web-views.product_support');
})->name('product.support');

Route::get('/ads/click/{ad}', [AdTrackingController::class, 'trackClick'])
    ->name('ads.click');

Route::get('/ads/impression/{ad}', [AdTrackingController::class, 'trackImpression'])
    ->name('ads.impression');
Route::controller(WebController::class)->group(function () {
    Route::get('maintenance-mode', 'maintenance_mode')->name('maintenance-mode');
});

Route::group(['namespace' => 'Web', 'middleware' => ['maintenance_mode', 'guestCheck']], function () {
    Route::group(['prefix' => 'product-compare', 'as' => 'product-compare.'], function () {
        Route::controller(ProductCompareController::class)->group(function () {
            Route::get(ProductCompare::INDEX[URI], 'index')->name('index')->middleware('customer');
            Route::post(ProductCompare::INDEX[URI], 'add');
            Route::get(ProductCompare::DELETE[URI], 'delete')->name('delete');
            Route::get(ProductCompare::DELETE_ALL[URI], 'deleteAllCompareProduct')->name('delete-all');
        });
    });
    Route::post(ShopFollower::SHOP_FOLLOW[URI], [ShopFollowerController::class, 'followOrUnfollowShop'])->name('shop-follow');
});

Route::group(['namespace' => 'Web', 'middleware' => ['maintenance_mode', 'guestCheck']], function () {

Route::get('/influencers', [InfluencerController::class, 'index'])->name('influencers.index');
Route::get('/influencers/{slug}', [InfluencerController::class, 'show'])->name('influencers.show');
Route::post('/influencers/contact', [InfluencerController::class, 'storeInquiry'])->name('influencers.contact');
Route::post('/influencers/{id}/rate', [InfluencerController::class, 'storeRating'])->name('influencers.rate');

Route::get('/brokers', [BrokerController::class, 'index'])->name('brokers.index');
Route::get('/brokers/{slug}', [BrokerController::class, 'show'])->name('brokers.show');
Route::post('/brokers/{id}/review', [BrokerController::class, 'storeReview'])->name('brokers.review');
   
Route::controller(HomeController::class)->group(function () {
        Route::get('/', 'index')->name('home');
        Route::get('home/latest-products/load-more', 'loadMoreLatestProducts')->name('home.load-more-latest-products');
        Route::get('home/top-rated/load-more', 'loadMoreTopRated')->name('home.load-more-top-rated');
        Route::get('home/best-selling/load-more', 'loadMoreBestSelling')->name('home.load-more-best-selling');
    });
        Route::prefix('chat')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->group(function () {
            Route::post('/start', [ChatController::class, 'startChat']);
            Route::post('/send', [ChatController::class, 'sendMessage']);
        });
    Route::controller(WebController::class)->group(function () {
        Route::get('quick-view', 'getQuickView')->name('quick-view');
        Route::get('searched-products', 'getSearchedProducts')->name('searched-products');
    });

    Route::group(['middleware' => ['customer']], function () {
        Route::controller(ReviewController::class)->group(function () {
            Route::post(Review::ADD[URI], 'add')->name('review.store');
            Route::post(Review::ADD_DELIVERYMAN_REVIEW[URI], 'addDeliveryManReview')->name('submit-deliveryman-review');
            Route::post(Review::DELETE_REVIEW_IMAGE[URI], 'deleteReviewImage')->name('delete-review-image');
        });

        Route::controller(\App\Http\Controllers\Web\CustomerNotificationController::class)->prefix('notifications')->name('customer-notifications.')->group(function () {
            Route::get('{id}/read', 'read')->name('read');
            Route::post('mark-all-read', 'markAllRead')->name('mark-all-read');
            Route::get('poll', 'poll')->name('poll');
        });
    });

    Route::controller(WebController::class)->group(function () {
        Route::get('checkout-details', 'checkout_details')->name('checkout-details');
        Route::get('checkout-payment', 'checkout_payment')->name('checkout-payment');
        Route::get('checkout-complete', 'getCashOnDeliveryCheckoutComplete')->name('checkout-complete');
        Route::post('offline-payment-checkout-complete', 'getOfflinePaymentCheckoutComplete')->name('offline-payment-checkout-complete');
        Route::get('order-placed', 'order_placed')->name('order-placed');
        Route::get('order-placed-success', 'getOrderPlaceView')->name('order-placed-success');
        Route::get('shop-cart', 'shop_cart')->name('shop-cart');
        Route::post('order_note', 'order_note')->name('order_note');
        Route::get('digital-product-download/{id}', 'getDigitalProductDownload')->name('digital-product-download');
        Route::post('digital-product-download-otp-verify', 'getDigitalProductDownloadOtpVerify')->name('digital-product-download-otp-verify')->middleware('throttle:10,1');
        Route::post('digital-product-download-otp-reset', 'getDigitalProductDownloadOtpReset')->name('digital-product-download-otp-reset')->middleware('throttle:3,1');
        Route::get('pay-offline-method-list', 'pay_offline_method_list')->name('pay-offline-method-list')->middleware('guestCheck');

        //wallet payment
        Route::post('checkout-complete-wallet', 'checkout_complete_wallet')->name('checkout-complete-wallet');

        Route::post('subscription', 'subscription')->name('subscription');
        Route::get('search-shop', 'search_shop')->name('search-shop');

        Route::get('categories', 'getAllCategoriesView')->name('categories');
        Route::get('category-ajax/{id}', 'categories_by_category')->name('category-ajax');

        Route::get('brands', 'getAllBrandsView')->name('brands');
        Route::get('vendors', 'getAllVendorsView')->name('vendors');
    });

    Route::controller(PageController::class)->group(function () {
        Route::get('business-page/{slug}', 'getPageView')->name('business-page.view');
        Route::get('contacts', 'getContactView')->name('contacts');
        Route::get('helpTopic', 'getHelpTopicView')->name('helpTopic');
    });

    Route::controller(ProductDetailsController::class)->group(function () {
        Route::get('/product/{slug}', 'index')->name('product');
    });

    Route::controller(ProductListController::class)->group(function () {
        Route::get('products', 'products')->name('products');
        Route::get('flash-deals/{id}', 'getFlashDealsView')->name('flash-deals');
        Route::post('flash-deals/{id}', 'getFlashDealsProducts');
    });

    Route::controller(ShopViewController::class)->group(function () {
        Route::post('ajax-filter-products', 'filterProductsAjaxResponse')->name('ajax-filter-products');
    });

    Route::controller(WebController::class)->group(function () {
        Route::get('discounted-products', 'discounted_products')->name('discounted-products');
        Route::post('/products-view-style', 'product_view_style')->name('product_view_style');

        Route::post('review-list-product', 'review_list_product')->name('review-list-product');
        Route::post('review-list-shop', 'getShopReviewList')->name('review-list-shop'); // theme fashion
        //Chat with seller from product details
        Route::get('chat-for-product', 'chat_for_product')->name('chat-for-product');

        Route::get('wishlists', 'viewWishlist')->name('wishlists')->middleware('customer');
        Route::post('store-wishlist', 'storeWishlist')->name('store-wishlist');
        Route::post('delete-wishlist', 'deleteWishlist')->name('delete-wishlist');
        Route::get('delete-wishlist-all', 'deleteAllWishListItems')->name('delete-wishlist-all')->middleware('customer');

        // end theme_aster compare list
        Route::get('searched-products-for-compare', 'getSearchedProductsForCompareList')->name('searched-products-compare'); // theme fashion compare list
    });

    Route::controller(CurrencyController::class)->group(function () {
        Route::post('/currency', 'changeCurrency')->name('currency.change');
    });

    // Support Ticket
    Route::controller(UserProfileController::class)->group(function () {
        Route::group(['prefix' => 'support-ticket', 'as' => 'support-ticket.'], function () {
            Route::get('{id}', 'single_ticket')->name('index');
            Route::post('{id}', 'comment_submit')->name('comment');
            Route::get('delete/{id}', 'support_ticket_delete')->name('delete');
            Route::get('close/{id}', 'support_ticket_close')->name('close');
        });
    });

    Route::controller(UserProfileController::class)->group(function () {
        Route::group(['prefix' => 'track-order', 'as' => 'track-order.'], function () {
            Route::get('', 'track_order')->name('index');
            Route::get('result-view', 'track_order_result')->name('result-view');
            Route::get('last', 'track_last_order')->name('last');
            Route::any('result', 'track_order_result')->name('result');
            Route::get('order-wise-result-view', 'track_order_wise_result')->name('order-wise-result-view');
        });
    });

    Route::controller(UserProfileController::class)->group(function () {
        Route::get('user-profile', 'user_profile')->name('user-profile')->middleware('customer'); //theme_aster
        Route::get('user-account', 'user_account')->name('user-account')->middleware('customer');
        Route::post('user-account-update', 'getUserProfileUpdate')->name('user-update')->middleware('customer');
        Route::get('account-address-add', 'account_address_add')->name('account-address-add');
        Route::get('account-address', 'account_address')->name('account-address');
        Route::post('account-address-store', 'address_store')->name('address-store');
        Route::get('account-address-delete', 'address_delete')->name('address-delete');
        ROute::get('account-address-edit/{id}', 'address_edit')->name('address-edit');
        Route::post('account-address-update', 'address_update')->name('address-update');
        Route::get('account-payment', 'account_payment')->name('account-payment');
        Route::get('account-oder', 'account_order')->name('account-oder')->middleware('customer');
        Route::get('account-order-details', 'account_order_details')->name('account-order-details')->middleware('customer');
        Route::get('account-order-details-vendor-info', 'account_order_details_seller_info')->name('account-order-details-vendor-info')->middleware('customer');
        Route::get('account-order-details-delivery-man-info', 'account_order_details_delivery_man_info')->name('account-order-details-delivery-man-info')->middleware('customer');
        Route::get('account-order-details-reviews', 'getAccountOrderDetailsReviewsView')->name('account-order-details-reviews')->middleware('customer');
        Route::get('generate-invoice/{id}', 'generate_invoice')->name('generate-invoice');
        Route::get('account-wishlist', 'account_wishlist')->name('account-wishlist'); //add to card not work
        Route::get('refund-request/{id}', 'refund_request')->name('refund-request');
        Route::get('refund-details/{id}', 'refund_details')->name('refund-details');
        Route::post('refund-store', 'store_refund')->name('refund-store');
        Route::get('account-tickets', 'account_tickets')->name('account-tickets');
        Route::get('order-cancel/{id}', 'order_cancel')->name('order-cancel');
        Route::post('ticket-submit', 'submitSupportTicket')->name('ticket-submit');
        Route::get('account-delete/{id}', 'account_delete')->name('account-delete');
        Route::get('refer-earn', 'refer_earn')->name('refer-earn')->middleware('customer');
        Route::get('user-coupons', 'user_coupons')->name('user-coupons')->middleware('customer');
        Route::get('user-restock-requests', 'restockRequestsView')->name('user-restock-requests')->middleware('customer');
        Route::get('user-restock-request-delete', 'deleteRestockRequest')->name('user-restock-request-delete')->middleware('customer');
    });

    Route::controller(ChattingController::class)->group(function () {
        Route::get('chat/{type}', 'index')->name('chat')->middleware('customer');
        Route::get('message', 'getMessageByUser')->name('messages')->middleware('customer');
        Route::post('message', 'addMessage')->middleware('customer');
        Route::get('message/unread-count', 'getUnreadCount')->name('messages.unread-count');
        Route::post('message/typing', 'markTyping')->name('messages.typing')->middleware('customer');
        Route::get('message/typing-status', 'typingStatus')->name('messages.typing-status')->middleware('customer');
    });

    Route::controller(UserWalletController::class)->group(function () {
        Route::get('wallet-account', 'myWalletAccount')->name('wallet-account'); //theme fashion
        Route::get('wallet', 'index')->name('wallet')->middleware('customer');
    });

    Route::group(['prefix' => 'hire', 'as' => 'hire.', 'middleware' => ['customer']], function () {
        // These literal-path routes must be registered before the FreelancerHireController's
        // catch-all `{service}` routes below — otherwise Laravel matches "quotes"/"contracts"
        // against `{service}` first, fails to resolve it as a FreelancerService, and 404s.
        Route::controller(FreelancerQuoteController::class)->group(function () {
            Route::get('quotes', 'index')->name('quotes.index');
            Route::get('quotes/{quote}', 'show')->name('quotes.show')->where('quote', '[0-9]+');
            Route::post('quotes/{service}', 'store')->name('quotes.store');
            Route::post('quotes/{quote}/decline', 'decline')->name('quotes.decline');
        });

        Route::controller(FreelancerContractController::class)->group(function () {
            Route::get('contracts', 'index')->name('contracts.index');
            Route::get('contracts/{contract}', 'show')->name('contracts.show');
            Route::post('contracts/{contract}/milestones/{milestone}/approve', 'approveMilestone')->name('contracts.milestones.approve');
            Route::post('contracts/{contract}/verdict', 'submitVerdict')->name('contracts.verdict.submit');
            Route::post('contracts/{contract}/verdict/approve', 'approveDelivery')->name('contracts.verdict.approve');
            Route::post('contracts/{contract}/verdict/reject', 'rejectDelivery')->name('contracts.verdict.reject');
            Route::post('contracts/{contract}/request-cancellation', 'requestCancellation')->name('contracts.request-cancellation');
            // A GET on this path (browser back/forward after the POST, or a refreshed/
            // resubmitted form) has nowhere else to go otherwise — Laravel has no custom
            // 405 view in this app, so it would show a raw, unstyled error page. Redirect
            // back to the contract instead of letting that happen.
            Route::get('contracts/{contract}/milestones/{milestone}/approve', 'approveMilestoneFallback');
            Route::get('contracts/{contract}/messages', 'messages')->name('contracts.messages');
            Route::post('contracts/{contract}/messages', 'sendMessage')->name('contracts.messages.store');
            Route::post('contracts/{contract}/review', 'storeReview')->name('contracts.review');
        });

        Route::post('reviews/{type}/{id}', [FreelancerReviewController::class, 'store'])
            ->name('reviews.store')
            ->where(['type' => 'service|portfolio', 'id' => '[0-9]+']);

        // Also a literal-path route that must be registered before the catch-all
        // `{service}` routes below.
        Route::get('payment-result', [FreelancerHireController::class, 'paymentResult'])->name('payment-result');

        Route::controller(FreelancerHireController::class)->group(function () {
            Route::get('{service}', 'create')->name('create');
            Route::post('{service}', 'checkout')->name('store');
        });
    });

    Route::controller(FreelancerContractAttachmentController::class)->group(function () {
        Route::get('freelancer-contract-attachments/deliverable/{attachment}', 'deliverable')
            ->name('freelancer-contract-attachments.deliverable')
            ->middleware('signed');
        Route::get('freelancer-contract-attachments/message/{attachment}', 'message')
            ->name('freelancer-contract-attachments.message')
            ->middleware('signed');
    });

    Route::controller(UserLoyaltyController::class)->group(function () {
        Route::get('loyalty', 'index')->name('loyalty')->middleware('customer');
        Route::post('loyalty-exchange-currency', 'getLoyaltyExchangeCurrency')->name('loyalty-exchange-currency');
        Route::get('ajax-loyalty-currency-amount', 'getLoyaltyCurrencyAmount')->name('ajax-loyalty-currency-amount');
    });

    Route::controller(DigitalProductDownloadController::class)->group(function () {
        Route::group(['prefix' => 'digital-product-download-pos', 'as' => 'digital-product-download-pos.'], function () {
            Route::get('/', 'index')->name('index');
        });
    });

    Route::controller(ShopViewController::class)->group(function () {
        Route::get('shopView/{id}', 'seller_shop')->name('shopView');
        Route::get('ajax-shop-vacation-check', 'ajax_shop_vacation_check')->name('ajax-shop-vacation-check');
    });

    Route::controller(WebController::class)->group(function () {
        Route::post('shopView/{id}', 'seller_shop_product');
        Route::get('top-rated', 'top_rated')->name('topRated');
        Route::get('best-sell', 'best_sell')->name('bestSell');
        Route::get('new-product', 'new_product')->name('newProduct');
    });


    Route::group(['prefix' => 'contact', 'as' => 'contact.'], function () {
        Route::controller(WebController::class)->group(function () {
            Route::post('store', 'contact_store')->name('store');
            Route::get('/code/captcha/{tmp}', 'captcha')->name('default-captcha');
        });
    });
});

// Check done
Route::group(['prefix' => 'cart', 'as' => 'cart.', 'namespace' => 'Web'], function () {
    Route::controller(CartController::class)->group(function () {
        Route::post('variant_price', 'getVariantPrice')->name('variant_price');
        Route::post('add', 'addToCart')->name('add');
        Route::post('update-variation', 'update_variation')->name('update-variation'); //theme fashion
        Route::post('remove', 'removeFromCart')->name('remove');
        Route::get('remove-all', 'remove_all_cart')->name('remove-all'); //theme fashion
        Route::post('nav-cart-items', 'updateNavCart')->name('nav-cart');
        Route::post('floating-nav-cart-items', 'update_floating_nav')->name('floating-nav-cart-items'); // theme fashion floating nav
        Route::post('updateQuantity', 'updateQuantity')->name('updateQuantity');
        Route::post('updateQuantity-guest', 'updateQuantity_guest')->name('updateQuantity.guest');
        Route::post('order-again', 'orderAgain')->name('order-again')->middleware('customer');
        Route::post('select-cart-items', 'updateCheckedCartItems')->name('select-cart-items');
        Route::post('product-restock-request', 'addProductRestockRequest')->name('product-restock-request');
    });
});


Route::group(['prefix' => 'coupon', 'as' => 'coupon.', 'namespace' => 'Web'], function () {
    Route::controller(CouponController::class)->group(function () {
        Route::post('apply', 'apply')->name('apply');
        Route::get('remove', 'removeCoupon')->name('remove');
    });
});

/*Auth::routes();*/
Route::get('authentication-failed', function () {
    $errors = [];
    array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
    return response()->json([
        'errors' => $errors
    ], 401);
})->name('authentication-failed');

Route::group(['namespace' => 'Customer', 'prefix' => 'customer', 'as' => 'customer.'], function () {

    Route::group(['namespace' => 'Auth', 'prefix' => 'auth', 'as' => 'auth.'], function () {

        Route::controller(CustomerAuthController::class)->group(function () {
            Route::get('login', 'loginView')->name('login');
            Route::post('login', 'loginSubmit')->middleware('throttle:10,1');
            Route::get('login/verify-account', 'loginVerifyPhone')->name('login.verify-account');
            Route::post('login/verify-account/submit', 'verifyAccount')->name('login.verify-account.submit')->middleware('throttle:10,1');
            Route::get('login/update-info', 'updateInfo')->name('login.update-info');
            Route::post('login/update-info', 'updateInfoSubmit')->middleware('throttle:10,1');
            Route::post('login/resend-otp-code', 'resendOTPCode')->name('resend-otp-code')->middleware('throttle:3,1');
        });

        Route::controller(LoginController::class)->group(function () {
            Route::get('/code/captcha/{tmp}', 'captcha')->name('default-captcha');
            Route::get('logout', 'logout')->name('logout');
            Route::get('get-login-modal-data', 'getLoginModalView')->name('get-login-modal-data');
        });

        Route::controller(RegisterController::class)->group(function () {
            Route::get('sign-up', 'getRegisterView')->name('sign-up');
            Route::post('sign-up', 'submitRegisterData')->middleware('throttle:6,1');
            Route::get('check-verification', 'verificationCheckView')->name('check-verification');
            Route::post('verify', 'verifyRegistration')->name('verify')->middleware('throttle:10,1');
            Route::post('ajax-verify', 'ajax_verify')->name('ajax_verify')->middleware('throttle:10,1');
            Route::post('resend-otp', 'resendOTPToCustomer')->name('resend_otp')->middleware('throttle:3,1');
        });

        Route::controller(SocialAuthController::class)->group(function () {
            Route::get('login/{service}', 'redirectToProvider')->name('service-login');
            Route::get('login/{service}/callback', 'handleProviderCallback')->name('service-callback');
            Route::get('login/social/confirmation', 'socialLoginConfirmation')->name('social-login-confirmation');
            Route::post('login/social/confirmation/update', 'updateSocialLoginConfirmation')->name('social-login-confirmation.update')->middleware('throttle:10,1');
            Route::post('login/social/verify-account', 'verifyAccount')->name('login.social.verify-account')->middleware('throttle:10,1');
        });

        Route::controller(ForgotPasswordController::class)->group(function () {
            Route::get('recover-password', 'reset_password')->name('recover-password');
            Route::post('forgot-password', 'resetPasswordRequest')->name('forgot-password')->middleware('throttle:6,1');
            Route::post('verify-recover-password', 'verifyRecoverPassword')->name('verify-recover-password')->middleware('throttle:10,1');
            Route::get('otp-verification', 'otp_verification')->name('otp-verification');
            Route::post('otp-verification', 'otp_verification_submit')->middleware('throttle:10,1');
            Route::get('reset-password', 'resetPasswordView')->name('reset-password');
            Route::post('reset-password', 'resetPasswordSubmit')->middleware('throttle:10,1');
            Route::post('resend-otp-reset-password', 'resendPhoneOTPRequest')->name('resend-otp-reset-password')->middleware('throttle:3,1');
            Route::post('forgot-password-email', 'sendPasswordResetLinkByEmail')->name('forgot-password-email')->middleware('throttle:6,1');

        });
    });

    Route::group([], function () {

        Route::controller(SystemController::class)->group(function () {
            Route::get('set-payment-method/{name}', 'setPaymentMethod')->name('set-payment-method');
            Route::get('set-shipping-method', 'setShippingMethod')->name('set-shipping-method');
            Route::post('choose-shipping-address', 'getChooseShippingAddress')->name('choose-shipping-address');
            Route::post('choose-shipping-address-other', 'getChooseShippingAddressOther')->name('choose-shipping-address-other');
        });

        Route::group(['prefix' => 'reward-points', 'as' => 'reward-points.', 'middleware' => ['auth:customer']], function () {
            Route::get('convert', 'RewardPointController@convert')->name('convert');
        });
    });
});

Route::group(['namespace' => 'Customer', 'prefix' => 'customer', 'as' => 'customer.'], function () {
    Route::controller(PaymentController::class)->group(function () {
        Route::post('/web-payment-request', 'payment')->name('web-payment-request');
        Route::post('/customer-add-fund-request', 'customer_add_to_fund_request')->name('add-fund-request');
    });
});

Route::controller(PaymentController::class)->group(function () {
    Route::get('web-payment', 'web_payment_success')->name('web-payment-success');
    Route::get('payment-success', 'success')->name('payment-success');
    Route::get('payment-fail', 'fail')->name('payment-fail');
    Route::get('payment-cancel', 'cancel')->name('payment-cancel');
});

$isGatewayPublished = 0;
try {
    $full_data = include('Modules/Gateways/Addon/info.php');
    $isGatewayPublished = $full_data['is_published'] == 1 ? 1 : 0;
} catch (\Exception $exception) {
}

if (!$isGatewayPublished) {
    Route::group(['prefix' => 'payment'], function () {

        //SSLCOMMERZ
        Route::group(['prefix' => 'sslcommerz', 'as' => 'sslcommerz.'], function () {
            Route::get('pay', [SslCommerzPaymentController::class, 'index'])->name('pay');
            Route::post('success', [SslCommerzPaymentController::class, 'success'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('failed', [SslCommerzPaymentController::class, 'failed'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('canceled', [SslCommerzPaymentController::class, 'canceled'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //STRIPE
        Route::group(['prefix' => 'stripe', 'as' => 'stripe.'], function () {
            Route::get('pay', [StripePaymentController::class, 'index'])->name('pay');
            Route::get('token', [StripePaymentController::class, 'payment_process_3d'])->name('token');
            Route::get('success', [StripePaymentController::class, 'success'])->name('success');
        });

        //RAZOR-PAY
        Route::group(['prefix' => 'razor-pay', 'as' => 'razor-pay.'], function () {
            Route::get('pay', [RazorPayController::class, 'index']);
            Route::post('payment', [RazorPayController::class, 'payment'])->name('payment')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('callback', [RazorPayController::class, 'callback'])->name('callback')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::any('cancel', [RazorPayController::class, 'cancel'])->name('cancel')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

            Route::any('create-order', [RazorPayController::class, 'createOrder'])->name('create-order')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::any('verify-payment', [RazorPayController::class, 'verifyPayment'])->name('verify-payment')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //PAYPAL
        Route::group(['prefix' => 'paypal', 'as' => 'paypal.'], function () {
            Route::get('pay', [PaypalPaymentController::class, 'payment']);
            Route::any('success', [PaypalPaymentController::class, 'success'])->name('success')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::any('cancel', [PaypalPaymentController::class, 'cancel'])->name('cancel')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //SENANG-PAY
        Route::group(['prefix' => 'senang-pay', 'as' => 'senang-pay.'], function () {
            Route::get('pay', [SenangPayController::class, 'index']);
            Route::any('callback', [SenangPayController::class, 'return_senang_pay']);
        });

        //PAYTM
        Route::group(['prefix' => 'paytm', 'as' => 'paytm.'], function () {
            Route::get('pay', [PaytmController::class, 'payment']);
            Route::any('response', [PaytmController::class, 'callback'])->name('response')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //FLUTTERWAVE
        Route::group(['prefix' => 'flutterwave-v3', 'as' => 'flutterwave-v3.'], function () {
            Route::get('pay', [FlutterwaveV3Controller::class, 'initialize'])->name('pay');
            Route::get('callback', [FlutterwaveV3Controller::class, 'callback'])->name('callback');
        });

        //PAYSTACK
        Route::group(['prefix' => 'paystack', 'as' => 'paystack.'], function () {
            Route::get('pay', [PaystackController::class, 'index'])->name('pay');
            Route::post('payment', [PaystackController::class, 'redirectToGateway'])->name('payment');
            Route::get('callback', [PaystackController::class, 'handleGatewayCallback'])->name('callback');
            Route::get('cancel', [PaystackController::class, 'cancel'])->name('cancel');
        });

        //BKASH
        Route::group(['prefix' => 'bkash', 'as' => 'bkash.'], function () {
            // Payment Routes for bKash
            Route::get('make-payment', [BkashPaymentController::class, 'make_tokenize_payment'])->name('make-payment');
            Route::any('callback', [BkashPaymentController::class, 'callback'])->name('callback');
        });

        //Liqpay
        Route::group(['prefix' => 'liqpay', 'as' => 'liqpay.'], function () {
            Route::get('payment', [LiqPayController::class, 'payment'])->name('payment');
            Route::any('callback', [LiqPayController::class, 'callback'])->name('callback');
        });

        //MERCADOPAGO
        Route::group(['prefix' => 'mercadopago', 'as' => 'mercadopago.'], function () {
            Route::get('pay', [MercadoPagoController::class, 'index'])->name('index');
            Route::post('make-payment', [MercadoPagoController::class, 'make_payment'])->name('make_payment');
        });

        //PAYMOB
        Route::group(['prefix' => 'paymob', 'as' => 'paymob.'], function () {
            Route::any('pay', [PaymobController::class, 'credit'])->name('pay');
            Route::any('callback', [PaymobController::class, 'callback'])->name('callback');
        });

        //PAYTABS
        Route::group(['prefix' => 'paytabs', 'as' => 'paytabs.'], function () {
            Route::any('pay', [PaytabsController::class, 'payment'])->name('pay');
            Route::any('callback', [PaytabsController::class, 'callback'])->name('callback');
            Route::any('response', [PaytabsController::class, 'response'])->name('response');
        });
        //NOWPAYMENTS
         Route::group(['prefix' => 'now-payments', 'as' => 'now-payments.'], function () {
            Route::any('pay', [NowpaymentsController::class, 'index'])->name('pay');
            Route::post('webhook', [NowpaymentsController::class, 'webhook'])
                ->name('webhook')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::get('status', [NowpaymentsController::class, 'checkStatus'])->name('status');
            Route::get('payment', [NowpaymentsController::class, 'handlePaymentStatus'])->name('handle-payment-status');
            Route::any('cancel', [NowpaymentsController::class, 'cancel'])->name('cancel')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });
    });
}

if (app()->environment('local')) {
    Route::get('/__qa_login_freelancer/{id}', function ($id) {
        auth('freelancer')->loginUsingId($id);
        return redirect('/freelancer/messages/admin');
    });
}
