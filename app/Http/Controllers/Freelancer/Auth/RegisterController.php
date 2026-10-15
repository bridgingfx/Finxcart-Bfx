<?php

namespace App\Http\Controllers\Freelancer\Auth;

use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Events\VendorRegistrationEvent;
use App\Events\VendorRegistrationOtpEvent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\VendorAddRequest;
use App\Models\BusinessPage;
use App\Models\User;
use App\Services\VendorService;
use App\Traits\EmailTemplateTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth as AuthFacade;

class RegisterController extends BaseController
{
    use EmailTemplateTrait;

    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepo,
        private readonly VendorWalletRepositoryInterface $vendorWalletRepo,
        private readonly ShopRepositoryInterface $shopRepo,
        private readonly VendorService $vendorService,
    )
    {
    }

    public function index(Request|null $request = null, string $type = null): View|RedirectResponse
    {
        $businessMode = getWebConfig(name: 'business_mode');
        $vendorRegistration = getWebConfig(name: 'seller_registration');
        if ((isset($businessMode) && $businessMode == 'single') || (isset($vendorRegistration) && $vendorRegistration == 0)) {
            ToastMagic::warning(translate('access_denied') . '!!');
            return redirect('/');
        }
        $termsPage = BusinessPage::where('slug', 'vendor-terms')->first();
        $pendingOtpEmail = null;
        $pendingOtpExpiresAt = null;
        $registrationData = session('freelancer_registration_data');
        $pendingUser = $registrationData ? User::where('email', $registrationData['email'] ?? null)->first() : null;
        if ($pendingUser && $pendingUser->user_otp && $pendingUser->otp_expiry) {
            $pendingOtpEmail = $pendingUser->email;
            $pendingOtpExpiresAt = Carbon::parse($pendingUser->otp_expiry)->getTimestamp() * 1000;
        }

        return view('freelancer-views.auth.register', compact('termsPage', 'pendingOtpEmail', 'pendingOtpExpiresAt'));
    }

    public function add(VendorAddRequest $request): JsonResponse|RedirectResponse
    {
        $otp = (string) rand(100000, 999999);
        $otpExpiry = now()->addMinutes(5);
        $registrationData = $request->validated();
        unset($registrationData['password_confirmation']);
        $registrationData['seller_type'] = 'freelancer';

        $user = User::where('email', $request['email'])->first();
        if ($user) {
            $user->update([
                'user_otp' => $otp,
                'otp_expiry' => $otpExpiry,
            ]);
        } else {
            User::create([
                'name' => $request['vendor_name'],
                'phone' => $request['phone'],
                'email' => $request['email'],
                'password' => bcrypt($request['password']),
                'user_otp' => $otp,
                'otp_expiry' => $otpExpiry,
            ]);
        }

        session()->put('freelancer_otp_attempts', 0);
        session()->put('freelancer_registration_data', $registrationData);

        $mailSent = true;
        try {
            event(new VendorRegistrationOtpEvent(
                email: $request['email'],
                data: [
                    'userType' => 'vendor',
                    'templateName' => 'vendor-registration-otp',
                    'vendorName' => $request['email'],
                    'message' => $otp,
                ],
            ));
        } catch (\Throwable $throwable) {
            \Log::warning('OTP email failed: ' . $throwable->getMessage());
            $mailSent = false;
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => $mailSent
                    ? 'OTP sent to your email'
                    : 'OTP generated (email delivery may be delayed)',
                'otp_expires_at' => $otpExpiry->getTimestamp() * 1000,
                // Local dev only: mail is rarely configured locally, so surface the OTP
                // directly instead of leaving the tester stuck with no way to receive it.
                // Never sent in any other environment.
                'otp' => app()->environment('local') ? $otp : null,
            ]);
        }

        return redirect()->route('freelancer.auth.registration.index');
    }

    public function verifyRegistrationOtp(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $attempts = (int) session('freelancer_otp_attempts', 0);
        $registrationData = session('freelancer_registration_data');
        $user = $registrationData ? User::where('email', $registrationData['email'])->first() : null;

        if (!$user || !$user->user_otp || !$user->otp_expiry || !$registrationData) {
            return response()->json([
                'status' => false,
                'message' => 'OTP session not found. Please register again.',
            ]);
        }

        if (Carbon::parse($user->otp_expiry)->isPast()) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired. Please resend.',
                'expired' => true,
            ]);
        }

        if ((string) $request->otp === (string) $user->user_otp) {
            $registrationRequest = Request::create('/', 'POST', $registrationData);

            $clientIp = $request->ip();
            $seller = DB::transaction(function () use ($registrationRequest, $clientIp) {
                $seller = $this->vendorRepo->add(data: $this->vendorService->getAddData($registrationRequest, source: 'web'));
                if (($registrationRequest['agree_to_terms'] ?? null) === '1') {
                    $seller->update([
                        'terms_agreed_at' => now(),
                        'terms_agreed_ip' => $clientIp,
                    ]);
                }
                $shopName = Str::before($registrationRequest['email'], '@');
                $this->shopRepo->add([
                    'seller_id' => $seller['id'],
                    'name' => $shopName,
                    'slug' => Str::slug($shopName, '-') . '-' . Str::random(6),
                    'address' => '',
                    'contact' => $registrationRequest['phone'],
                    'email' => $registrationRequest['email'],
                    'image' => 'def.png',
                    'image_storage_type' => null,
                    'banner' => 'def.png',
                    'banner_storage_type' => null,
                    'bottom_banner' => 'def.png',
                    'bottom_banner_storage_type' => null,
                ]);
                $this->vendorWalletRepo->add($this->vendorService->getInitialWalletData(vendorId: $seller['id']));

                return $seller;
            });

            $data = [
                'name' => $registrationRequest['email'],
                'vendorName' => $registrationRequest['email'],
                'sellerType' => 'freelancer',
                'seller_type' => 'freelancer',
                'message' => 'You have registered on Finxcart as a freelancer. Please login to complete your profile and submit your documents for verification.',
                'status' => 'pending',
                'subject' => translate('Vendor_Registration_Successfully_Completed'),
                'title' => translate('Vendor_Registration_Successfully_Completed'),
                'userType' => 'vendor',
                'templateName' => 'registration',
            ];
            try {
                event(new VendorRegistrationEvent(email: $registrationRequest['email'], data: $data));
            } catch (\Throwable $e) {
                \Log::error('Registration email failed: ' . $e->getMessage());
            }

            $user->update([
                'user_otp' => null,
                'otp_expiry' => null,
            ]);
            session()->forget([
                'freelancer_otp_attempts',
                'freelancer_registration_data',
            ]);

            AuthFacade::guard('freelancer')->login($seller);

            return response()->json([
                'status' => true,
                'redirect' => route('freelancer.dashboard.index'),
                'message' => 'Email verified successfully. Welcome to your dashboard.',
            ]);
        }

        $attempts++;
        session()->put('freelancer_otp_attempts', $attempts);
        $attemptsLeft = max(0, 3 - $attempts);

        if ($attemptsLeft <= 0) {
            $user->update([
                'user_otp' => null,
                'otp_expiry' => null,
            ]);
            session()->forget([
                'freelancer_otp_attempts',
                'freelancer_registration_data',
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Too many failed attempts. Please register again.',
                'blocked' => true,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid OTP. ' . $attemptsLeft . ' attempts remaining.',
            'attempts_left' => $attemptsLeft,
        ]);
    }

    public function cancelRegistrationOtp(Request $request): JsonResponse
    {
        $registrationData = session('freelancer_registration_data');
        $user = $registrationData ? User::where('email', $registrationData['email'] ?? null)->first() : null;

        if ($user && $user->user_otp) {
            $user->update([
                'user_otp' => null,
                'otp_expiry' => null,
            ]);
        }

        session()->forget([
            'freelancer_otp_attempts',
            'freelancer_registration_data',
        ]);

        return response()->json(['status' => true]);
    }

    public function resendRegistrationOtp(Request $request): JsonResponse
    {
        $storedData = session('freelancer_registration_data');
        $user = $storedData ? User::where('email', $storedData['email'])->first() : null;

        if (!$storedData || !$user) {
            return response()->json([
                'status' => false,
                'message' => 'Registration session not found. Please register again.',
            ]);
        }

        $newOtp = (string) rand(100000, 999999);
        $otpExpiry = now()->addMinutes(5);
        $user->update([
            'user_otp' => $newOtp,
            'otp_expiry' => $otpExpiry,
        ]);
        session()->put('freelancer_otp_attempts', 0);

        $mailSent = true;
        try {
            event(new VendorRegistrationOtpEvent(
                email: $storedData['email'],
                data: [
                    'userType' => 'vendor',
                    'templateName' => 'vendor-registration-otp',
                    'vendorName' => $storedData['email'],
                    'message' => $newOtp,
                ],
            ));
        } catch (\Throwable $throwable) {
            \Log::warning('OTP email resend failed: ' . $throwable->getMessage());
            $mailSent = false;
        }

        return response()->json([
            'status' => true,
            'message' => $mailSent
                ? 'New OTP sent'
                : 'OTP regenerated (email delivery may be delayed)',
            'otp_expires_at' => $otpExpiry->getTimestamp() * 1000,
            'otp' => app()->environment('local') ? $newOtp : null,
        ]);
    }
}
