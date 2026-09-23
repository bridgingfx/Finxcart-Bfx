<?php

namespace App\Http\Controllers\Freelancer\Auth;

use App\Contracts\Repositories\PasswordResetRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Enums\SessionKey;
use App\Events\PasswordResetEvent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\PasswordResetRequest;
use App\Http\Requests\Vendor\VendorPasswordRequest;
use App\Services\PasswordResetService;
use App\Traits\EmailTemplateTrait;
use App\Traits\SmsGateway;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Gateways\Traits\SmsGateway as AddonSmsGateway;

class ForgotPasswordController extends BaseController
{
    use SmsGateway, EmailTemplateTrait;

    public function __construct(
        private readonly VendorRepositoryInterface        $vendorRepo,
        private readonly PasswordResetRepositoryInterface $passwordResetRepo,
        private readonly PasswordResetService             $passwordResetService,
    )
    {
        $this->middleware('guest:freelancer');
    }

    public function index(Request|null $request = null, string $type = null): View
    {
        return $this->getForgotPasswordView();
    }

    public function getForgotPasswordView(): View
    {
        return view('freelancer-views.auth.forgot-password.index');
    }

    public function getPasswordResetRequest(PasswordResetRequest $request): JsonResponse|RedirectResponse
    {
        session()->put(SessionKey::FORGOT_PASSWORD_IDENTIFY, $request['identity']);
        $verificationBy = getWebConfig('vendor_forgot_password_method') ?? 'phone';
        if ($verificationBy == 'email') {
            // Scoped to Freelancer accounts — a same-email Vendor account must
            // never receive/consume this freelancer password-reset request.
            $seller = $this->vendorRepo->getFirstWhere(['identity' => $request['identity'], 'seller_type' => 'freelancer']);
            if (isset($seller)) {
                $token = Str::random(120);
                $this->passwordResetRepo->add($this->passwordResetService->getAddData(identity: $request['identity'], token: $token, userType: 'seller'));
                $resetUrl = route('freelancer.auth.forgot-password.reset-password', ['token' => $token]);
                try {
                    $data = [
                        'userType' => 'vendor',
                        'templateName' => 'forgot-password',
                        'vendorName' => $seller['f_name'],
                        'subject' => translate('password_reset'),
                        'title' => translate('password_reset'),
                        'passwordResetURL' => $resetUrl,
                    ];
                    event(new PasswordResetEvent(email: $seller['email'], data: $data));
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
            // Scoped to Freelancer accounts — a same-email Vendor account must
            // never receive/consume this freelancer password-reset request.
            $seller = $this->vendorRepo->getFirstWhere(['identity' => $request['identity'], 'seller_type' => 'freelancer']);
            if (isset($seller)) {
                $token = (env('APP_MODE') == 'live') ? rand(1000, 9999) : 1234;
                $this->passwordResetRepo->add($this->passwordResetService->getAddData(identity: $request['identity'], token: $token, userType: 'seller'));

                $paymentPublishedStatus = config('get_payment_publish_status') ?? 0;
                if ($paymentPublishedStatus == 1) {
                    $response = AddonSmsGateway::send($seller['phone'], $token);
                } else {
                    $response = $this->send($seller['phone'], $token);
                }

                if (env('APP_MODE') == 'dev') {
                    if ($request->ajax()) {
                        return response()->json([
                            'verificationBy' => 'phone',
                            'redirectRoute' => route('freelancer.auth.forgot-password.otp-verification'),
                            'success' => translate('Check_your_phone') . ', ' . translate('password_reset_otp_sent'),
                        ]);
                    }
                    ToastMagic::success(translate('Check_your_phone') . ', ' . translate('password_reset_otp_sent'));
                    return redirect()->route('freelancer.auth.forgot-password.otp-verification');
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
                        'redirectRoute' => route('freelancer.auth.forgot-password.otp-verification'),
                        'success' => translate('Check_your_phone') . ', ' . translate('password_reset_otp_sent'),
                    ]);
                }
                ToastMagic::success(translate('Check_your_phone') . ', ' . translate('password_reset_otp_sent'));
                return redirect()->route('freelancer.auth.forgot-password.otp-verification');
            }
        }
        if ($request->ajax()) {
            return response()->json([
                'redirect' => true,
                'redirectRoute' => route('freelancer.auth.registration.index') . '?reg_error=' . urlencode(translate('This_email_is_not_registered._Please_register_first')),
                'error' => translate('This_email_is_not_registered._Please_register_first'),
            ]);
        }
        return redirect()->route('freelancer.auth.registration.index')
            ->with('registration_error', translate('This_email_is_not_registered._Please_register_first'));
    }

    public function getOTPVerificationView(): View
    {
        return view('freelancer-views.auth.forgot-password.verify-otp-view');
    }

    public function submitOTPVerificationCode(Request $request): RedirectResponse
    {
        $id = session(SessionKey::FORGOT_PASSWORD_IDENTIFY);
        $passwordResetData = $this->passwordResetRepo->getFirstWhere(params: ['user_type' => 'seller', 'token' => $request['otp'], 'identity' => $id]);
        if (isset($passwordResetData)) {
            $token = $request['otp'];
            return redirect()->route('freelancer.auth.forgot-password.reset-password', ['token' => $token]);
        }
        ToastMagic::error(translate('invalid_otp'));
        return redirect()->back();
    }

    public function getPasswordResetView(Request $request): View|RedirectResponse
    {
        $passwordResetData = $this->passwordResetRepo->getFirstWhere(params: ['user_type' => 'seller', 'token' => $request['token']]);
        if (isset($passwordResetData)) {
            $token = $request['token'];
            return view('freelancer-views.auth.forgot-password.reset-password-view', compact('token'));
        }
        ToastMagic::error(translate('Invalid_URL'));
        return redirect()->route('freelancer.auth.login');
    }

    public function resetPassword(VendorPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $passwordResetData = $this->passwordResetRepo->getFirstWhere(params: ['user_type' => 'seller', 'token' => $request['reset_token']]);
        if ($passwordResetData) {
            $seller = $this->vendorRepo->getFirstWhere(params: ['identity' => $passwordResetData['identity'], 'seller_type' => 'freelancer']);
            $this->vendorRepo->update(id: $seller['id'], data: ['password' => bcrypt($request['password'])]);
            $this->passwordResetRepo->delete(params: ['id' => $passwordResetData['id']]);
            if ($request->ajax()) {
                return response()->json([
                    'passwordUpdate' => 1,
                    'success' => translate('Password_reset_successfully'),
                    'redirectRoute' => route('freelancer.auth.login'),
                ]);
            }
            ToastMagic::success(translate('Password_reset_successfully'));
            return redirect()->route('freelancer.auth.login');
        }

        if ($request->ajax()) {
            return response()->json(['error' => translate('invalid_URL')]);
        }
        ToastMagic::error(translate('invalid_URL'));
        return back();
    }
}
