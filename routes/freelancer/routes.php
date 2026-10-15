<?php

use App\Enums\ViewPaths\Freelancer\Chatting;
use App\Http\Controllers\Freelancer\Auth\ForgotPasswordController;
use App\Http\Controllers\Freelancer\Auth\LoginController;
use App\Http\Controllers\Freelancer\Auth\RegisterController;
use App\Http\Controllers\Freelancer\ChattingController;
use App\Http\Controllers\Freelancer\DashboardController;
use App\Http\Controllers\Freelancer\FreelancerQuoteController;
use App\Http\Controllers\Freelancer\FreelancerContractController;
use App\Http\Controllers\Freelancer\FreelancerPortfolioController;
use App\Http\Controllers\Freelancer\FreelancerServiceController;
use App\Http\Controllers\Freelancer\LedgerController;
use App\Http\Controllers\Freelancer\NotificationController;
use App\Http\Controllers\Freelancer\ProfileController;
use App\Http\Controllers\Freelancer\ReviewController;
use App\Http\Controllers\Freelancer\VerificationController;
use App\Http\Controllers\Freelancer\WalletController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['maintenance_mode']], function () {

    Route::group(['prefix' => 'freelancer', 'as' => 'freelancer.'], function () {
        /* authentication */
        Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
            Route::controller(LoginController::class)->group(function () {
                Route::get('login', 'getLoginView')->name('login.index');
                Route::get('recaptcha/{tmp}', 'generateReCaptcha')->name('recaptcha');
                Route::post('login', 'login')->name('login')->middleware('throttle:10,1');
                Route::get('logout', 'logout')->name('logout');
            });
            Route::group(['prefix' => 'forgot-password', 'as' => 'forgot-password.'], function () {
                Route::controller(ForgotPasswordController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/', 'getPasswordResetRequest')->middleware('throttle:6,1');
                    Route::get('otp-verification', 'getOTPVerificationView')->name('otp-verification');
                    Route::post('otp-verification', 'submitOTPVerificationCode')->middleware('throttle:10,1');
                    Route::get('reset-password', 'getPasswordResetView')->name('reset-password');
                    Route::post('reset-password', 'resetPassword')->middleware('throttle:10,1');
                });
            });
            Route::group(['prefix' => 'registration', 'as' => 'registration.'], function () {
                Route::controller(RegisterController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/', 'add')->middleware('throttle:6,1');
                });
            });
            Route::controller(RegisterController::class)->group(function () {
                Route::post('verify-registration-otp', 'verifyRegistrationOtp')->name('verify-registration-otp')->middleware('throttle:10,1');
                Route::post('resend-registration-otp', 'resendRegistrationOtp')->name('resend-registration-otp')->middleware('throttle:3,1');
                Route::post('cancel-registration-otp', 'cancelRegistrationOtp')->name('cancel-registration-otp')->middleware('throttle:10,1');
            });
        });
        /* end authentication */

        Route::group(['middleware' => ['freelancer']], function () {

            Route::group(['prefix' => 'verification', 'as' => 'verification.'], function () {
                Route::controller(VerificationController::class)->group(function () {
                    Route::get('/', 'index')->name('form');
                    Route::post('/', 'store')->name('store');
                });
            });

            Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
            Route::post('/notifications/mark-kyc-seen', [NotificationController::class, 'markKycSeen'])->name('notifications.mark-kyc-seen');
            Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');

            Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
                Route::get('/', [DashboardController::class, 'index'])->name('index');
            });

            Route::group(['prefix' => 'services', 'as' => 'services.'], function () {
                Route::controller(FreelancerServiceController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('create', 'create')->name('create');
                    Route::post('store', 'store')->name('store');
                    Route::get('view/{id}', 'show')->name('view');
                    Route::get('edit/{id}', 'edit')->name('edit');
                    Route::post('update/{id}', 'update')->name('update');
                    Route::delete('delete/{id}', 'destroy')->name('destroy');
                    Route::post('status-update/{id}', 'statusUpdate')->name('status-update');
                    Route::post('categories', 'storeCategory')->name('categories.store');
                    Route::delete('categories/{id}', 'destroyCategory')->name('categories.destroy');
                    Route::post('specializations', 'storeSpecialization')->name('specializations.store');
                    Route::delete('specializations/{id}', 'destroySpecialization')->name('specializations.destroy');
                });
            });

            Route::group(['prefix' => 'portfolio', 'as' => 'portfolio.'], function () {
                Route::controller(FreelancerPortfolioController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('create', 'create')->name('create');
                    Route::post('store', 'store')->name('store');
                    Route::get('view/{id}', 'show')->name('view');
                    Route::get('edit/{id}', 'edit')->name('edit');
                    Route::post('update/{id}', 'update')->name('update');
                    Route::post('activate/{id}', 'activate')->name('activate');
                    Route::delete('delete/{id}', 'destroy')->name('destroy');
                });
            });

            Route::group(['prefix' => 'quotes', 'as' => 'quotes.'], function () {
                Route::controller(FreelancerQuoteController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('{id}', 'show')->name('view');
                    Route::post('{id}/reply', 'reply')->name('reply');
                });
            });

            Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');

            Route::group(['prefix' => 'contracts', 'as' => 'contracts.'], function () {
                Route::controller(FreelancerContractController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('{contract}', 'show')->name('show');
                    Route::post('milestones/{milestone}/submit', 'submitMilestone')->name('milestones.submit');
                    Route::post('{contract}/delivery-status', 'updateDeliveryStatus')->name('delivery-status.update');
                    Route::get('{contract}/messages', 'messages')->name('messages');
                    Route::post('{contract}/messages', 'sendMessage')->name('messages.store');
                    Route::post('{contract}/review', 'storeReview')->name('review');
                });
            });

            Route::get('wallet', [WalletController::class, 'index'])->name('wallet.index');
            Route::get('ledger', [LedgerController::class, 'index'])->name('ledger.index');

            Route::group(['prefix' => 'messages', 'as' => 'messages.'], function () {
                Route::controller(ChattingController::class)->group(function () {
                    Route::get(Chatting::INDEX[URI] . '/{type?}', 'index')->name('index');
                    Route::get(Chatting::MESSAGE[URI], 'getMessageByUser')->name('message');
                    Route::post(Chatting::MESSAGE[URI], 'addFreelancerMessage');
                    Route::get(Chatting::NEW_NOTIFICATION[URI], 'getNewNotification')->name('new-notification');
                    Route::post('typing', 'markTyping')->name('typing');
                    Route::get('typing-status', 'typingStatus')->name('typing-status');
                    Route::get('admin', 'getAdminMessagesView')->name('admin');
                    Route::get('admin/poll', 'pollAdminMessages')->name('admin.poll');
                    Route::post('admin', 'sendAdminMessage')->name('admin.send');
                    Route::post('admin/typing', 'markTypingToAdmin')->name('admin.typing');
                    Route::get('admin/typing-status', 'adminTypingStatus')->name('admin.typing-status');
                });
            });

            Route::get('messages/{malformedRoute}', function (string $malformedRoute) {
                if (str_contains($malformedRoute, '[object Object]')) {
                    return redirect()->route('freelancer.messages.admin');
                }

                abort(404);
            })->where('malformedRoute', '.*');

            Route::group(['prefix' => 'notification', 'as' => 'notification.'], function () {
                Route::post('/', [NotificationController::class, 'getNotificationModalView'])->name('index');
            });

            Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
                Route::controller(ProfileController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('update/{id}', 'update')->name('update');
                    Route::patch('update/{id}', 'updatePassword')->name('update-password');
                    Route::get('update-bank-info/{id}', 'getBankInfoUpdateView')->name('update-bank-info');
                    Route::post('update-bank-info/{id}', 'updateBankInfo');
                    Route::delete('update-bank-info/{id}', 'removeBankInfo')->name('remove-bank-info');
                });
            });

            // Last-resort safety net: some client-side code somewhere still manages
            // to send a stray "[object Object]" as a URL segment (e.g. a redirect
            // built from an unstringified JS object) directly under /freelancer/,
            // one level above the messages/{malformedRoute} case already handled
            // above. Registered last in the group so it never shadows a real route.
            Route::get('{malformedRoute}', function (string $malformedRoute) {
                if (str_contains($malformedRoute, '[object Object]')) {
                    return redirect()->route('freelancer.dashboard.index');
                }

                abort(404);
            })->where('malformedRoute', '.*');
        });
    });

});
