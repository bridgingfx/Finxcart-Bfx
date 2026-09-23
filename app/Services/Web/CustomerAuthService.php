<?php

namespace App\Services\Web;

use App\Events\EmailVerificationEvent;
use App\Utils\Helpers;
use App\Utils\SMSModule;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class CustomerAuthService
{
    public function getCustomerVerificationToken(): string
    {
        return (env('APP_MODE') == 'live') ? rand(100000, 999999) : 123456;
    }

    public function getCustomerLoginDataReset(): array
    {
        return [
            'login_hit_count' => 0,
            'is_temp_blocked' => 0,
            'temp_block_time' => null,
            'updated_at' => now()
        ];
    }


    public function sendCustomerPhoneVerificationToken($phone, $token): array
    {
        // SMS gateways (Twilio/Nexmo/etc.) make a live network call here — if the
        // configured gateway is unreachable or misconfigured (e.g. no gateway set up
        // locally), this must not bubble up into a 500; the OTP is already saved to
        // the DB by the caller regardless of whether the SMS actually went out.
        try {
            $response = SMSModule::sendCentralizedSMS($phone, $token);
            return [
                'response' => $response,
                'status' => 'success',
                'message' => translate('please_check_your_SMS_for_OTP'),
            ];
        } catch (\Throwable $exception) {
            \Log::error('CUSTOMER_PHONE_OTP_SEND_FAILED', ['phone' => $phone, 'exception' => $exception->getMessage()]);
            return [
                'response' => null,
                'status' => 'error',
                'message' => translate('sms_gateway_is_not_configured') . '. ' . translate('contact_with_the_administrator'),
            ];
        }
    }

    public function sendCustomerEmailVerificationToken($user, $token): array
    {
        $emailServicesSmtp = getWebConfig(name: 'mail_config');
        \Log::info('CUSTOMER_EMAIL_OTP_DEBUG', ['mail_config_status' => $emailServicesSmtp['status'] ?? null]);
        if ($emailServicesSmtp['status'] == 0) {
            $emailServicesSmtp = getWebConfig(name: 'mail_config_sendgrid');
            \Log::info('CUSTOMER_EMAIL_OTP_DEBUG', ['fell_to_sendgrid_status' => $emailServicesSmtp['status'] ?? null]);
        }
        if ($emailServicesSmtp['status'] == 0) {
            $emailServicesSmtp = getWebConfig(name: 'mail_config_brevo');
            \Log::info('CUSTOMER_EMAIL_OTP_DEBUG', ['fell_to_brevo_status' => $emailServicesSmtp['status'] ?? null]);
        }
        if ($emailServicesSmtp['status'] == 1 && $user['email']) {
            try {
                $data = [
                    'userName' => $user['f_name'],
                    'subject' => translate('registration_Verification_Code'),
                    'title' => translate('registration_Verification_Code'),
                    'verificationCode' => $token,
                    'userType' => 'customer',
                    'templateName' => 'registration-verification',
                ];

                \Log::info('CUSTOMER_EMAIL_OTP_DEBUG', ['firing_event_to' => $user['email']]);
                event(new EmailVerificationEvent(email: $user['email'], data: $data));
                return [
                    'status' => 'success',
                    'message' => translate('check_your_email'),
                ];
            } catch (\Throwable $exception) {
                \Log::error('CUSTOMER_EMAIL_OTP_DEBUG', ['exception' => $exception->getMessage()]);
                return [
                    'status' => 'error',
                    'message' => translate('email_is_not_configured') . '. ' . translate('contact_with_the_administrator'),
                ];
            }
        } else {
            \Log::warning('CUSTOMER_EMAIL_OTP_DEBUG', ['skipped_send' => true, 'status' => $emailServicesSmtp['status'] ?? null, 'email' => $user['email'] ?? null]);
            return [
                'status' => 'error',
                'message' => translate('email_failed'),
            ];
        }
    }

    public function getCustomerLoginPreviousRoute($previousUrl): string
    {
        $redirectUrl = "";
        $previousUrl = url()->previous();
        if (
            strpos($previousUrl, 'checkout-complete') !== false ||
            strpos($previousUrl, 'offline-payment-checkout-complete') !== false ||
            strpos($previousUrl, 'track-order') !== false
        ) {
            $redirectUrl = route('home');
        }
        return $redirectUrl;
    }

    public function getCustomerRegisterData(object|array $request, object|array|null $referUser)
    {
        return [
            'name' => $request['f_name'] . ' ' . $request['l_name'],
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'is_active' => 1,
            'password' => bcrypt($request['password']),
            'referral_code' => Helpers::generate_referer_code(),
            'referred_by' => $referUser ? $referUser['id'] : null,
        ];
    }
}
