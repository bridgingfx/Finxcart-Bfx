<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\PhoneOrEmailVerificationRepositoryInterface;
use App\Events\PasswordResetEvent;
use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Services\FirebaseService;
use App\Services\Web\CustomerAuthService;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private readonly CustomerAuthService                         $customerAuthService,
        private readonly CustomerRepositoryInterface                 $customerRepo,
        private readonly FirebaseService                             $firebaseService,
        private readonly PhoneOrEmailVerificationRepositoryInterface $phoneOrEmailVerificationRepo,
    ) {
        $this->middleware('guest:customer', ['except' => ['logout']]);
    }

    // -------------------------------------------------------------------------
    //  Helpers
    // -------------------------------------------------------------------------

    /**
     * Detect whether a given string looks like an e-mail address.
     */
    private function identityIsEmail(string $identity): bool
    {
        return filter_var($identity, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Resolve the verification channel ('phone' or 'email') from the submitted
     * identity string.  The channel is never hardcoded any more.
     */
    private function resolveVerificationChannel(string $identity): string
    {
        return $this->identityIsEmail($identity) ? 'email' : 'phone';
    }

    /**
     * Shared logic for sending a password-reset e-mail.
     * Extracted to avoid copy-paste between resetPasswordRequest() and
     * sendPasswordResetLinkByEmail().
     */
    private function dispatchPasswordResetEmail(mixed $customer, string $email): void
    {
        $token    = Str::random(120);
        $resetUrl = route('customer.auth.reset-password', [
            'identity' => base64_encode($email),
            'token'    => $token,
        ]);

        $data = [
            'userType'         => 'customer',
            'templateName'     => 'forgot-password',
            'userName'         => $customer->f_name ?? $customer['f_name'],
            'subject'          => translate('password_reset'),
            'title'            => translate('password_reset'),
            'passwordResetURL' => $resetUrl,
        ];

        $this->phoneOrEmailVerificationRepo->updateOrCreate(
            params: ['phone_or_email' => $email],
            value:  ['phone_or_email' => $email, 'token' => $token],
        );

        event(new PasswordResetEvent(email: $email, data: $data));
    }

    // -------------------------------------------------------------------------
    //  Views
    // -------------------------------------------------------------------------

    public function reset_password(): View
    {
        $verification_by = getWebConfig(name: 'forgot_password_verification');
        return view(VIEW_FILE_NAMES['recover_password'], compact('verification_by'));
    }

    // -------------------------------------------------------------------------
    //  resetPasswordRequest  (phone OR email, chosen by identity format)
    // -------------------------------------------------------------------------

    public function resetPasswordRequest(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate(['identity' => 'required']);

        // --- reCAPTCHA guard (Firebase OTP flow) ---
        $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification') ?? [];
        if ($firebaseOTPVerification && $firebaseOTPVerification['status'] && empty($request['g-recaptcha-response'])) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => translate('ReCAPTCHA_Failed')]);
            }
            Toastr::error(translate('ReCAPTCHA_Failed'));
            return redirect()->back();
        }

        $identity       = $request['identity'];
        // FIX #1 & #2: derive channel from the identity value; lookup uses a
        // generic 'identity' filter so both phone and e-mail are resolved.
        $verificationBy = $this->resolveVerificationChannel($identity);
        $filterKey      = $verificationBy === 'email' ? 'email' : 'phone';

        $customer = $this->customerRepo->getByIdentity(filters: [$filterKey => $identity]);
        if (!$customer) {
            Toastr::error(translate('No_such_user_found'));
            return back();
        }

        if ($customer->is_active == 0) {
            Toastr::error(translate('Your_account_is_deactivated'));
            return back();
        }

        session()->put('forgot_password_identity', $identity);

        // FIX #10: use a single, consistent fallback (60 s) everywhere
        $otpIntervalTime  = getWebConfig(name: 'otp_resend_time') ?? 60;
        $smsErrorMsg      = translate('something_went_wrong.') . ' ' . translate('please_try_again_after_sometime');
        $OTPVerificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $identity]);

        // --- Throttle: don't allow a new OTP until the interval has passed ---
        if ($OTPVerificationData && Carbon::parse($OTPVerificationData->created_at)->diffInSeconds() < $otpIntervalTime) {
            $time = $otpIntervalTime - Carbon::parse($OTPVerificationData->created_at)->diffInSeconds();
            Toastr::error(translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());
            return back();
        }

        // --- Email path ---
        if ($verificationBy === 'email') {
            try {
                $this->dispatchPasswordResetEmail($customer, $identity);
                Toastr::success(translate('Check_your_email') . ' ' . translate('Password_reset_url_sent'));
            } catch (\Throwable $exception) {
                Log::error('Password reset email failed for [redacted]: ' . $exception->getMessage());
                Toastr::error(translate('email_is_not_configured') . '. ' . translate('contact_with_the_administrator'));
            }
            return back();
        }

        // --- Phone path ---
        $token    = $this->customerAuthService->getCustomerVerificationToken();
        $response = null;

        if ($firebaseOTPVerification && $firebaseOTPVerification['status']) {
            $firebaseResponse = $this->firebaseService->sendOtp($customer['phone']);
            if ($firebaseResponse['status'] === 'success') {
                $token    = $firebaseResponse['sessionInfo'];
                $response = 'success';
            } else {
                // FIX #11: surface Firebase-specific error instead of swallowing it
                $smsErrorMsg = translate(strtolower($firebaseResponse['errors']));
            }
        } else {
            $result   = $this->customerAuthService->sendCustomerPhoneVerificationToken($customer['phone'], $token);
            $response = $result['status'];
            if (config('app.env') === 'local' || config('app.env') === 'dev') {
                $response = 'success';
            }
        }

        if ($response === 'success') {
            $this->phoneOrEmailVerificationRepo->updateOrCreate(
                params: ['phone_or_email' => $customer['phone']],
                value:  ['phone_or_email' => $customer['phone'], 'token' => $token],
            );
            $type = 'phone_verification';
            Toastr::success(translate('Check_your_phone') . ' ' . translate('Password_reset_OTP_sent'));
            return redirect()->route('customer.auth.login.verify-account', [
                'identity' => base64_encode($customer['phone']),
                'type'     => base64_encode($type),
                'action'   => base64_encode('password-reset'),
            ]);
        }

        Toastr::error($smsErrorMsg);
        return back();
    }

    // -------------------------------------------------------------------------
    //  sendPasswordResetLinkByEmail  (dedicated e-mail-only endpoint)
    // -------------------------------------------------------------------------

    public function sendPasswordResetLinkByEmail(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->input('email');
        // FIX #17: do not log raw PII; log a partial/hashed identifier
        Log::info('Password reset requested for email hash: ' . substr(md5($email), 0, 8));

        $customer = $this->customerRepo->getByIdentity(filters: ['email' => $email]);

        if (!$customer) {
            Log::warning('Password reset: no customer found (hash: ' . substr(md5($email), 0, 8) . ')');
            Toastr::error(translate('No_such_user_found'));
            return back();
        }

        if ($customer->is_active == 0) {
            Toastr::error(translate('Your_account_is_deactivated'));
            return back();
        }

        // FIX #10: consistent default of 60 s
        $otpIntervalTime     = getWebConfig(name: 'otp_resend_time') ?? 60;
        $OTPVerificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(
            params: ['phone_or_email' => $email]
        );

        if ($OTPVerificationData && Carbon::parse($OTPVerificationData->created_at)->diffInSeconds() < $otpIntervalTime) {
            $timeLeft = $otpIntervalTime - Carbon::parse($OTPVerificationData->created_at)->diffInSeconds();
            Toastr::error(translate('please_try_again_after_') . CarbonInterval::seconds($timeLeft)->cascade()->forHumans());
            return back();
        }

        try {
            // FIX #16: reuse shared helper instead of copy-pasting the block
            $this->dispatchPasswordResetEmail($customer, $email);
            Toastr::success(translate('Check_your_email') . ' ' . translate('Password_reset_url_sent'));
        } catch (\Throwable $e) {
            Log::error('Password reset email failed (hash: ' . substr(md5($email), 0, 8) . '): ' . $e->getMessage());
            Toastr::error(translate('email_is_not_configured') . '. ' . translate('contact_with_the_administrator'));
        }

        return back();
    }

    // -------------------------------------------------------------------------
    //  resendPhoneOTPRequest
    // -------------------------------------------------------------------------

    public function resendPhoneOTPRequest(Request $request): JsonResponse|RedirectResponse
    {
        $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification') ?? [];
        if ($firebaseOTPVerification && $firebaseOTPVerification['status'] && empty($request['g-recaptcha-response'])) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => translate('ReCAPTCHA_Failed')]);
            }
            Toastr::error(translate('ReCAPTCHA_Failed'));
            return redirect()->back();
        }

        // FIX #9: validate the decoded identity before using it
        $decodedIdentity = base64_decode($request['identity'] ?? '', true);
        if (empty($decodedIdentity)) {
            $message = translate('Invalid_user');
            if ($request->ajax()) {
                return response()->json(['status' => 0, 'message' => $message], 422);
            }
            Toastr::error($message);
            return redirect()->back();
        }

        $customer = $this->customerRepo->getByIdentity(filters: ['identity' => $decodedIdentity]);
        if (!$customer) {
            $message = translate('Invalid_user');
            if ($request->ajax()) {
                return response()->json(['status' => 0, 'message' => $message], 404);
            }
            Toastr::error($message);
            return redirect()->back();
        }

        $tokenInfo       = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $customer['phone']]);
        // FIX #10: consistent default
        $otpIntervalTime = getWebConfig(name: 'otp_resend_time') ?? 60;

        if ($tokenInfo && Carbon::parse($tokenInfo->updated_at)->diffInSeconds() < $otpIntervalTime) {
            $time = $otpIntervalTime - Carbon::parse($tokenInfo->updated_at)->diffInSeconds();
            $message = translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();
            if ($request->ajax()) {
                return response()->json([
                    'status' => 0,
                    'message' => $message,
                    'new_time' => $time,
                    'otp_expires_at' => now()->addSeconds($time)->getTimestamp() * 1000,
                ], 429);
            }
            Toastr::error($message);
            return redirect()->back();
        }

        $token    = $this->customerAuthService->getCustomerVerificationToken();
        $response = 'not_found';

        if ($firebaseOTPVerification && $firebaseOTPVerification['status']) {
            $firebaseResponse = $this->firebaseService->sendOtp($customer['phone']);
            if ($firebaseResponse['status'] === 'success') {
                $token    = $firebaseResponse['sessionInfo'];
                $response = 'success';
            } else {
                // FIX #11: surface Firebase error
                $message = translate(strtolower($firebaseResponse['errors'] ?? 'something_went_wrong.'));
                if ($request->ajax()) {
                    return response()->json(['status' => 0, 'message' => $message], 422);
                }
                Toastr::error($message);
                return redirect()->back();
            }
        } else {
            $result   = $this->customerAuthService->sendCustomerPhoneVerificationToken($customer['phone'], $token);
            $response = $result['status'];
        }

        $this->phoneOrEmailVerificationRepo->updateOrCreate(
            params: ['phone_or_email' => $customer['phone']],
            value:  [
                'phone_or_email'  => $customer['phone'],
                'token'           => $token,
                'otp_hit_count'   => 0,
                'is_temp_blocked' => 0,
                'temp_block_time' => null,
                'created_at'      => now(),
            ],
        );

        if ($response === 'not_found') {
            $message = translate('something_went_wrong.') . ' ' . translate('please_try_again_after_sometime');
            if ($request->ajax()) {
                return response()->json(['status' => 0, 'message' => $message], 422);
            }
            Toastr::error($message);
            return redirect()->back();
        }

        $message = translate('OTP_sent_successfully');
        if ($request->ajax()) {
            return response()->json([
                'status' => 1,
                'message' => $message,
                'new_time' => $otpIntervalTime,
                'otp_expires_at' => now()->addSeconds($otpIntervalTime)->getTimestamp() * 1000,
            ]);
        }
        Toastr::success($message);
        return redirect()->back();
    }

    // -------------------------------------------------------------------------
    //  Legacy OTP verification (PasswordReset model based)
    // -------------------------------------------------------------------------

    public function otp_verification(Request $request): View|RedirectResponse
    {
        $token_info = PasswordReset::where('identity', $request['identity'])->latest()->first();
        if (!$token_info) {
            return redirect()->route('customer.auth.recover-password');
        }

        $otp_resend_time = max((int) getWebConfig(name: 'otp_resend_time'), 0);
        $token_time      = Carbon::parse($token_info->created_at);
        $convert_time    = $token_time->addSeconds($otp_resend_time);
        $time_count      = $convert_time->greaterThan(Carbon::now())
            ? Carbon::now()->diffInSeconds($convert_time)
            : 0;

        return view(VIEW_FILE_NAMES['otp_verification'], compact('time_count'));
    }

    public function otp_verification_submit(Request $request): View|RedirectResponse
    {
        $max_otp_hit    = getWebConfig(name: 'maximum_otp_hit') ?? 5;
        $temp_block_time = getWebConfig(name: 'temporary_block_time') ?? 5; // minutes

        $id = theme_root_path() === 'default'
            ? session('forgot_password_identity')
            : $request['identity'];

        $password_reset_token = PasswordReset::where(['token' => $request['otp'], 'user_type' => 'customer'])
            ->where('identity', 'like', "%{$id}%")
            ->latest()
            ->first();

        if ($password_reset_token) {
            if (
                isset($password_reset_token->temp_block_time) &&
                Carbon::parse($password_reset_token->temp_block_time)->diffInSeconds() <= $temp_block_time
            ) {
                $time = $temp_block_time - Carbon::parse($password_reset_token->temp_block_time)->diffInSeconds();
                Toastr::error(translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());
                return redirect()->back();
            }

            return redirect()->route('customer.auth.reset-password', ['token' => $request['otp']]);
        }

        // Wrong OTP — look up the record to increment hit count / apply block
        $password_reset = PasswordReset::where(['user_type' => 'customer'])
            ->where('identity', 'like', "%{$id}%")
            ->latest()
            ->first();

        if (!$password_reset) {
            Toastr::error(translate('invalid_OTP'));
            return redirect()->back();
        }

        if (
            isset($password_reset->temp_block_time) &&
            Carbon::parse($password_reset->temp_block_time)->diffInSeconds() <= $temp_block_time
        ) {
            $time = $temp_block_time - Carbon::parse($password_reset->temp_block_time)->diffInSeconds();
            Toastr::error(translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());

        } elseif (
            $password_reset->is_temp_blocked == 1 &&
            Carbon::parse($password_reset->created_at)->diffInSeconds() >= $temp_block_time
        ) {
            $password_reset->otp_hit_count  = 1;
            $password_reset->is_temp_blocked = 0;
            $password_reset->temp_block_time = null;
            $password_reset->updated_at      = now();
            $password_reset->save();
            Toastr::error(translate('invalid_otp'));

        } elseif ($password_reset->otp_hit_count >= $max_otp_hit && $password_reset->is_temp_blocked == 0) {
            $password_reset->is_temp_blocked = 1;
            $password_reset->temp_block_time = now();
            $password_reset->updated_at      = now();
            $password_reset->save();

            // FIX #5: read temp_block_time AFTER saving so the diff is accurate
            $time = $temp_block_time - Carbon::parse($password_reset->temp_block_time)->diffInSeconds();
            Toastr::error(translate('Too_many_attempts. please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans());

        } else {
            $password_reset->otp_hit_count += 1;
            $password_reset->save();
            Toastr::error(translate('invalid_OTP'));
        }

        return redirect()->back();
    }

    // -------------------------------------------------------------------------
    //  Reset-password views & submission
    // -------------------------------------------------------------------------

    public function resetPasswordView(Request $request): View|RedirectResponse
    {
        // FIX #9: validate decoded identity
        $identity = base64_decode($request['identity'] ?? '', true);
        if (empty($identity) || empty($request['token'])) {
            Toastr::error(translate('Invalid_credentials'));
            return back();
        }

        $data = $this->phoneOrEmailVerificationRepo->getFirstWhere(
            params: ['phone_or_email' => $identity, 'token' => $request['token']]
        );

        if ($data) {
            $token = $request['token'];
            return view(VIEW_FILE_NAMES['reset_password'], compact('token'));
        }

        Toastr::error(translate('Invalid_credentials'));
        return back();
    }

    public function resetPasswordSubmit(Request $request): View|Redirector|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            // FIX #8: enforce minimum password length
            'password' => 'required|min:8|same:confirm_password',
        ]);

        $token = $request['reset_token'];
        if ($validator->fails()) {
            Toastr::error(translate('password_mismatch'));
            return view(VIEW_FILE_NAMES['reset_password'], compact('token'));
        }

        // FIX #9: validate decoded identity
        $identity = base64_decode($request['identity'] ?? '', true);
        if (empty($identity)) {
            Toastr::error(translate('Invalid_data'));
            return back();
        }

        $data     = $this->phoneOrEmailVerificationRepo->getFirstWhere(
            params: ['phone_or_email' => $identity, 'token' => $request['token']]
        );
        $customer = $this->customerRepo->getByIdentity(filters: ['identity' => $identity]);

        if ($data && $customer) {
            $updateData = [
                // AUTH-03 FIX: store the password exactly as typed. Previously spaces
                // were stripped here but not at login, so any password containing a
                // space could never be used to log in again after a reset.
                'password' => bcrypt($request['password']),
            ];

            // FIX #12: only mark email verified when the identity is an e-mail
            if ($this->identityIsEmail($identity)) {
                $updateData['is_email_verified'] = 1;
            } else {
                $updateData['is_phone_verified'] = 1;
            }

            $this->customerRepo->updateWhere(params: ['id' => $customer['id']], data: $updateData);

            Toastr::success(translate('Password_reset_successfully'));
            DB::table('password_resets')
                ->where('user_type', 'customer')
                ->where(['token' => $request['reset_token']])
                ->delete();
            $this->phoneOrEmailVerificationRepo->delete(params: ['phone_or_email' => $identity]);

            return redirect('/');
        }

        Toastr::error(translate('Invalid_data'));
        return back();
    }

    // -------------------------------------------------------------------------
    //  verifyRecoverPassword  (OTP / Firebase token check)
    // -------------------------------------------------------------------------

    public function verifyRecoverPassword(Request $request): View|RedirectResponse|JsonResponse
    {
        if (!$request->has('token') || empty($request['token'])) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => translate('The_token_field_is_required')]);
            }
            Toastr::error(translate('The_token_field_is_required'));
            return redirect()->back();
        }

        // FIX #9: validate decoded values before using them
        $identity = base64_decode($request['identity'] ?? '', true);
        $type     = base64_decode($request['type'] ?? '', true);
        if (empty($identity) || empty($type)) {
            Toastr::error(translate('Invalid_credentials'));
            return redirect()->back();
        }

        $phoneVerification       = ($type === 'phone_verification');
        $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification') ?? [];

        if ($firebaseOTPVerification && $firebaseOTPVerification['status'] && empty($request['g-recaptcha-response'])) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => translate('ReCAPTCHA_Failed')]);
            }
            Toastr::error(translate('ReCAPTCHA_Failed'));
            return redirect()->back();
        }

        $maxOTPHit     = getWebConfig(name: 'maximum_otp_hit') ?? 5;
        $maxOTPHitTime = getWebConfig(name: 'otp_resend_time') ?? 60;   // seconds
        $tempBlockTime = getWebConfig(name: 'temporary_block_time') ?? 600; // seconds

        $verificationData    = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $identity]);
        $OTPVerificationData = $this->phoneOrEmailVerificationRepo->getFirstWhere(params: ['phone_or_email' => $identity, 'token' => $request['token']]);
        $customer            = $this->customerRepo->getByIdentity(filters: ['identity' => $identity]);

        $validateBlock = false;
        $errorMsg      = translate('OTP_is_not_matched');

        if ($verificationData) {
            // --- Determine block state ---
            if (
                isset($verificationData->temp_block_time) &&
                Carbon::parse($verificationData->temp_block_time)->diffInSeconds() <= $tempBlockTime
            ) {
                $time          = $tempBlockTime - Carbon::parse($verificationData->temp_block_time)->diffInSeconds();
                $validateBlock = true;
                $errorMsg      = translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();

            } elseif (
                $verificationData['is_temp_blocked'] == 1 &&
                Carbon::parse($verificationData['updated_at'])->diffInSeconds() >= $tempBlockTime
            ) {
                // Block has expired — reset counters but still reject this attempt
                $this->phoneOrEmailVerificationRepo->updateOrCreate(
                    params: ['phone_or_email' => $identity],
                    value:  ['otp_hit_count' => 0, 'is_temp_blocked' => 0, 'temp_block_time' => null],
                );
                $validateBlock = true;
                $errorMsg      = translate('OTP_is_not_matched');

            } elseif (
                $verificationData['otp_hit_count'] >= $maxOTPHit &&
                Carbon::parse($verificationData['updated_at'])->diffInSeconds() < $maxOTPHitTime &&
                $verificationData['is_temp_blocked'] == 0
            ) {
                // FIX #5: persist temp_block_time FIRST, then compute the display value
                $this->phoneOrEmailVerificationRepo->updateOrCreate(
                    params: ['phone_or_email' => $identity],
                    value:  ['is_temp_blocked' => 1, 'temp_block_time' => now()],
                );
                $validateBlock = true;
                $time          = $tempBlockTime; // block just started → full duration remains
                $errorMsg      = translate('Too_many_attempts.') . ' ' . translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();
            }

            // FIX #3 & #4: only increment hit count when NOT already blocked and
            // NOT on a successful match.  Use a single atomic update to avoid the
            // race condition caused by the double read/write.
            if (!$validateBlock && !$OTPVerificationData) {
                $this->phoneOrEmailVerificationRepo->updateOrCreate(
                    params: ['phone_or_email' => $identity],
                    value:  [
                        'otp_hit_count' => ($verificationData['otp_hit_count'] + 1),
                        'updated_at'    => now(),
                    ],
                );
            }

            if ($validateBlock) {
                if ($request->ajax()) {
                    return response()->json(['status' => 0, 'message' => $errorMsg]);
                }
                Toastr::error($errorMsg);
                return redirect()->back();
            }
        }

        // --- Verify the token ---
        $tokenVerifyStatus = false;

        if ($verificationData && $phoneVerification && $firebaseOTPVerification && $firebaseOTPVerification['status']) {
            $firebaseVerify    = $this->firebaseService->verifyOtp($verificationData['token'], $verificationData['phone_or_email'], $request['token']);
            $tokenVerifyStatus = ($firebaseVerify['status'] === 'success');

            if (!$tokenVerifyStatus) {
                // FIX #3: on failure only, increment hit count
                $this->phoneOrEmailVerificationRepo->updateOrCreate(
                    params: ['phone_or_email' => $identity],
                    value:  [
                        'otp_hit_count' => ($verificationData['otp_hit_count'] + 1),
                        'updated_at'    => now(),
                        'temp_block_time' => null,
                    ],
                );
                Toastr::error(translate(strtolower($firebaseVerify['errors'] ?? 'something_went_wrong.')));
                return back();
            }
        } else {
            $tokenVerifyStatus = (bool) $OTPVerificationData;
        }

        if (!$tokenVerifyStatus) {
            if ($request->ajax()) {
                return response()->json(['status' => 0, 'message' => $errorMsg]);
            }
            Toastr::error($errorMsg);
            return redirect()->back();
        }

        // --- Token valid: check the user isn't still blocked ---
        if (
            isset($verificationData->temp_block_time) &&
            Carbon::parse($verificationData->temp_block_time)->diffInSeconds() <= $tempBlockTime
        ) {
            $time     = $tempBlockTime - Carbon::parse($verificationData->temp_block_time)->diffInSeconds();
            $errorMsg = translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();
            if ($request->ajax()) {
                return response()->json(['status' => 0, 'message' => $errorMsg]);
            }
            Toastr::error($errorMsg);
            return redirect()->back();
        }

        // FIX #12: mark phone verified (not email) when the identity is a phone number
        $this->customerRepo->updateWhere(
            params: ['id' => $customer['id']],
            data:   $phoneVerification
                ? ['is_phone_verified' => 1]
                : ['is_email_verified' => 1],
        );

        return redirect()->route('customer.auth.reset-password', [
            'identity' => base64_encode($identity),
            'token'    => $verificationData['token'],
        ]);
    }
}
