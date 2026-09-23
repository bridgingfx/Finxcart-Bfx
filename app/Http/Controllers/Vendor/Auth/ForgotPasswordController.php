<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Contracts\Repositories\PasswordResetRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Enums\SessionKey;
use App\Enums\ViewPaths\Vendor\Auth;
use App\Enums\ViewPaths\Vendor\ForgotPassword;
use App\Events\PasswordResetEvent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\PasswordResetRequest;
use App\Http\Requests\Vendor\VendorPasswordRequest;
use App\Services\PasswordResetService;
use App\Traits\EmailTemplateTrait;
use App\Traits\SmsGateway;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Gateways\Traits\SmsGateway as AddonSmsGateway;

class ForgotPasswordController extends BaseController
{
    use SmsGateway, EmailTemplateTrait;

    /**
     * @param VendorRepositoryInterface $vendorRepo
     * @param PasswordResetRepositoryInterface $passwordResetRepo
     * @param PasswordResetService $passwordResetService
     */
    public function __construct(
        private readonly VendorRepositoryInterface        $vendorRepo,
        private readonly PasswordResetRepositoryInterface $passwordResetRepo,
        private readonly PasswordResetService             $passwordResetService,
    )
    {
        $this->middleware('guest:seller', ['except' => ['logout']]);
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     */
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getForgotPasswordView();
    }

    /**
     * @return View
     */
    public function getForgotPasswordView(): View
    {
        return view(ForgotPassword::INDEX[VIEW]);
    }

    /**
     * @param PasswordResetRequest $request
     * @return JsonResponse|RedirectResponse
     */
    public function getPasswordResetRequest(PasswordResetRequest $request): JsonResponse|RedirectResponse
    {
        session()->put(SessionKey::FORGOT_PASSWORD_IDENTIFY, $request['identity']);
        $verificationBy = getWebConfig('vendor_forgot_password_method') ?? 'phone';
        if ($verificationBy == 'email') {
            // Scoped to Vendor accounts (any seller_type except 'freelancer') — a
            // same-email Freelancer account must never receive/consume this vendor
            // password-reset request, but admin-created vendors (seller_type
            // 'company'/'individual') must still be matched here.
            $vendor = $this->vendorRepo->getFirstWhere(['identity' => $request['identity'], 'excludeSellerType' => 'freelancer']);
            if (isset($vendor)) {
                $token = Str::random(120);
                $this->passwordResetRepo->add($this->passwordResetService->getAddData(identity: $request['identity'], token: $token, userType: 'seller'));
                $resetUrl = route('vendor.auth.forgot-password.reset-password', ['token' => $token]);
                try {
                    $data = [
                        'userType' => 'vendor',
                        'templateName' => 'forgot-password',
                        'vendorName' => $vendor['f_name'],
                        'subject' => translate('password_reset'),
                        'title' => translate('password_reset'),
                        'passwordResetURL' => $resetUrl,
                    ];
                    event(new PasswordResetEvent(email: $vendor['email'], data: $data));
                } catch (\Exception $exception) {
                    if ($request->ajax()) {
                        if (app()->environment('local')) {
                            return response()->json([
                                'verificationBy' => 'mail',
                                'show_email_reset_modal' => true,
                            ]);
                        }
                        return response()->json(['error' => translate('email_send_fail') . '!!']);
                    }
                    if (app()->environment('local')) {
                        session()->flash('show_email_reset_modal', true);
                        return back();
                    }
                    ToastMagic::error(translate('email_send_fail'));
                    return back();
                }
                                session()->flash('show_email_reset_modal', true);
                if ($request->ajax()) {
                    return response()->json([
                        'verificationBy' => 'mail',
                        'success' => translate('mail_send_successfully'),
                        'show_email_reset_modal' => true,
                    ]);
                }
                return back();
            }
        } elseif ($verificationBy == 'phone') {
            // Scoped to Vendor accounts (any seller_type except 'freelancer') — a
            // same-email Freelancer account must never receive/consume this vendor
            // password-reset request, but admin-created vendors (seller_type
            // 'company'/'individual') must still be matched here.
            $vendor = $this->vendorRepo->getFirstWhere(['identity' => $request['identity'], 'excludeSellerType' => 'freelancer']);
            if (isset($vendor)) {
                $token = (env('APP_MODE') == 'live') ? rand(1000, 9999) : 1234;
                $this->passwordResetRepo->add($this->passwordResetService->getAddData(identity: $request['identity'], token: $token, userType: 'seller'));

                $paymentPublishedStatus = config('get_payment_publish_status') ?? 0;
                if ($paymentPublishedStatus == 1) {
                    $response = AddonSmsGateway::send($vendor['phone'], $token);
                } else {
                    $response = $this->send($vendor['phone'], $token);
                }

                if (env('APP_MODE') == 'dev') {
                    if ($request->ajax()) {
                        return response()->json([
                            'verificationBy' => 'phone',
                            'redirectRoute' => route('vendor.auth.forgot-password.otp-verification'),
                            'success' => translate('Check_your_phone') . ', ' . translate('password_reset_otp_sent'),
                        ]);
                    }
                    ToastMagic::success(translate('Check_your_phone') . ', ' . translate('password_reset_otp_sent'));
                    return redirect()->route('vendor.auth.forgot-password.otp-verification');
                }

                if ($response === "not_found") {
                    if ($request->ajax()) {
                        return response()->json([
                            'error' => translate('something_went_wrong.') . ' ' . translate('please_try_again_after_sometime'),
                        ]);
                    }
                    ToastMagic::error(translate('something_went_wrong'));
                    return back();
                }

                if ($request->ajax()) {
                    return response()->json([
                        'verificationBy' => 'phone',
                        'redirectRoute' => route('vendor.auth.forgot-password.otp-verification'),
                        'success' => translate('Check_your_phone') . ', ' . translate('password_reset_otp_sent'),
                    ]);
                }
                ToastMagic::success(translate('Check_your_phone') . ', ' . translate('password_reset_otp_sent'));
                return redirect()->route('vendor.auth.forgot-password.otp-verification');
            }
        }
        if ($request->ajax()) {
            return response()->json([
                'redirect' => true,
                'redirectRoute' => route('vendor.auth.registration.index') . '?reg_error=' . urlencode(translate('This_email_is_not_registered._Please_register_first')),
                'error' => translate('This_email_is_not_registered._Please_register_first'),
            ]);
        }
        return redirect()->route('vendor.auth.registration.index')
            ->with('registration_error', translate('This_email_is_not_registered._Please_register_first'));
    }

    /**
     * @return View
     */
    public function getOTPVerificationView(): View
    {
        return view(ForgotPassword::OTP_VERIFICATION[VIEW]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function submitOTPVerificationCode(Request $request): RedirectResponse
    {
        $id = session(SessionKey::FORGOT_PASSWORD_IDENTIFY);
        $passwordResetData = $this->passwordResetRepo->getFirstWhere(params: ['user_type' => 'seller', 'token' => $request['otp'], 'identity' => $id]);
        if (isset($passwordResetData)) {
            $token = $request['otp'];
            return redirect()->route('vendor.auth.forgot-password.reset-password', ['token' => $token]);
        }
        ToastMagic::error(translate('invalid_otp'));
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function getPasswordResetView(Request $request): View|RedirectResponse
    {
        $passwordResetData = $this->passwordResetRepo->getFirstWhere(params: ['user_type' => 'seller', 'token' => $request['token']]);
        if (isset($passwordResetData)) {
            $token = $request['token'];
            return view(ForgotPassword::RESET_PASSWORD[VIEW], compact('token'));
        }
        ToastMagic::error(translate('Invalid_URL'));
        return redirect()->route(Auth::VENDOR_LOGOUT[URI]);
    }

    /**
     * @param VendorPasswordRequest $request
     * @return JsonResponse|RedirectResponse
     */
    public function resetPassword(VendorPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $passwordResetData = $this->passwordResetRepo->getFirstWhere(params: ['user_type' => 'seller', 'token' => $request['reset_token']]);
        if ($passwordResetData) {
            $vendor = $this->vendorRepo->getFirstWhere(params: ['identity' => $passwordResetData['identity'], 'excludeSellerType' => 'freelancer']);
            $this->vendorRepo->update(id: $vendor['id'], data: ['password' => bcrypt($request['password'])]);
            $this->passwordResetRepo->delete(params: ['id' => $passwordResetData['id']]);
            if ($request->ajax()) {
                return response()->json([
                    'passwordUpdate' => 1,
                    'success' => translate('Password_reset_successfully'),
                    'redirectRoute' => route('vendor.auth.login'),
                ]);
            }
            ToastMagic::success(translate('Password_reset_successfully'));
            return redirect()->route('vendor.auth.login');
        }

        if ($request->ajax()) {
            return response()->json(['error' => translate('invalid_URL')]);
        }
        ToastMagic::error(translate('invalid_URL'));
        return back();
    }
}


