<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Enums\SessionKey;
use App\Enums\ViewPaths\Vendor\Auth;
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
        $this->middleware('guest:seller', ['except' => ['logout']]);
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
        return view(Auth::VENDOR_LOGIN[VIEW], compact('recaptchaBuilder', 'recaptcha'));
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
                        // could block vendor login for Guzzle's default (much longer) timeout.
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

        // Vendor accounts are every seller_type except 'freelancer' (NULL for
        // self-registered, 'company'/'individual' for admin-created) — a
        // Freelancer account can share this email/phone, so both the lookup and
        // the auth attempt must exclude it to avoid matching the wrong row.
        $vendor = $this->vendorRepo->getFirstWhere(['identity' => $request['email'], 'excludeSellerType' => 'freelancer']);

        if (!$vendor) {
            ToastMagic::error(translate('account_not_found') . '!');
            return back();
        }

        $passwordCheck = Hash::check($request['password'], $vendor['password']);


        if ($passwordCheck && $vendor['account_status'] === 'inactive') {
            ToastMagic::error(translate('your_account_has_been_suspended_please_contact_support') . ' (support@finxcart.com)!');
            return back();
        }

        if ($this->vendorService->isLoginSuccessful($request->email, $request->password, $request->remember, 'seller', ['seller_type' => $vendor->seller_type])) {
            if ($this->vendorWalletRepo->getFirstWhere(params: ['id' => auth('seller')->id()]) === false) {
                $this->vendorWalletRepo->add($this->vendorService->getInitialWalletData(vendorId: auth('seller')->id()));
            }

            $seller = auth('seller')->user();
            $verification = $seller?->vendorVerification;

            if (!$seller?->seller_type || !$verification || $verification->status === 'rejected') {
                return redirect()->route('vendor.verification.form');
            }

            if ($seller?->status === 'approved' && (bool) $seller->first_login_after_approval) {
                return redirect()->route('vendor.tier.index');
            }

            return redirect()->route('vendor.dashboard.index');
        } else {
            ToastMagic::error(translate('credentials_doesnt_match') . '!');
            return back();
        }
    }

    public function logout(): RedirectResponse
    {
        $this->vendorService->logout();
        ToastMagic::success(translate('logged_out_successfully') . '.');
        return redirect()->route('vendor.auth.login');
    }
}
