<?php

namespace App\Http\Controllers\Freelancer\Auth;

use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Enums\SessionKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\LoginRequest;
use App\Repositories\VendorWalletRepository;
use App\Services\VendorService;
use App\Traits\RecaptchaTrait;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    use RecaptchaTrait;

    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepo,
        private readonly VendorService             $vendorService,
        private readonly VendorWalletRepository    $vendorWalletRepo,
    )
    {
        $this->middleware('guest:freelancer', ['except' => ['logout']]);
    }

    public function generateReCaptcha(): void
    {
        $recaptchaBuilder = $this->generateDefaultReCaptcha(4);
        if (Session::has(SessionKey::VENDOR_RECAPTCHA_KEY)) {
            Session::forget(SessionKey::VENDOR_RECAPTCHA_KEY);
        }
        Session::put(SessionKey::VENDOR_RECAPTCHA_KEY, $recaptchaBuilder->getPhrase());
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        $recaptchaBuilder->output();
    }

    public function getLoginView(): View
    {
        $recaptchaBuilder = $this->generateDefaultReCaptcha(4);
        $recaptcha = getWebConfig(name: 'recaptcha');
        Session::put(SessionKey::VENDOR_RECAPTCHA_KEY, $recaptchaBuilder->getPhrase());
        return view('freelancer-views.auth.login', compact('recaptchaBuilder', 'recaptcha'));
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $recaptcha = getWebConfig(name: 'recaptcha');
        if (isset($recaptcha) && $recaptcha['status'] == 1) {
            $request->validate([
                'g-recaptcha-response' => [
                    function ($attribute, $value, $fail) {
                        $secret_key = getWebConfig(name: 'recaptcha')['secret_key'];
                        $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $value;
                        // Bounded timeout — an unreachable Google endpoint previously
                        // could block freelancer login for Guzzle's default (much longer) timeout.
                        try {
                            $response = Http::timeout(5)->get($url)->json();
                        } catch (\Throwable $e) {
                            $response = null;
                        }
                        if (!isset($response['success']) || !$response['success']) {
                            $fail(translate('recaptcha_failed'));
                        }
                    },
                ],
            ]);
        } else {
            if ($recaptcha['status'] != 1 && strtolower($request->vendorRecaptchaKey) != strtolower(Session(SessionKey::VENDOR_RECAPTCHA_KEY))) {
                ToastMagic::error(translate('ReCAPTCHA_Failed'));
                return back();
            }
        }

        // Scoped to seller_type='freelancer' — a Vendor account may legitimately
        // share this email, and must never be matched by the freelancer login.
        $seller = $this->vendorRepo->getFirstWhere(['identity' => $request['email'], 'seller_type' => 'freelancer']);
        if (!$seller) {
            ToastMagic::error(translate('account_not_found') . '!');
            return back();
        }

        $passwordCheck = Hash::check($request['password'], $seller['password']);

        if ($passwordCheck && $seller['account_status'] === 'inactive') {
            ToastMagic::error(translate('your_account_has_been_suspended_please_contact_support') . ' (support@finxcart.com)!');
            return back();
        }

        if ($this->vendorService->isLoginSuccessful($request->email, $request->password, $request->remember, 'freelancer', ['seller_type' => 'freelancer'])) {
            if ($this->vendorWalletRepo->getFirstWhere(params: ['id' => auth('freelancer')->id()]) === false) {
                $this->vendorWalletRepo->add($this->vendorService->getInitialWalletData(vendorId: auth('freelancer')->id()));
            }

            $seller = auth('freelancer')->user();
            $verification = $seller?->vendorVerification;

            if (!$verification || $verification->status === 'rejected') {
                return redirect()->route('freelancer.verification.form');
            }

            return redirect()->route('freelancer.dashboard.index');
        } else {
            ToastMagic::error(translate('credentials_doesnt_match') . '!');
            return back();
        }
    }

    public function logout(): RedirectResponse
    {
        $this->vendorService->logout('freelancer');
        ToastMagic::success(translate('logged_out_successfully') . '.');
        return redirect()->route('freelancer.auth.login');
    }
}
