<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected Client $client;
    public function __construct()
    {
        $this->client = new Client();
    }

    private function getOtpApiKey(): string
    {
        $firebaseOtpVerification = getWebConfig('firebase_otp_verification') ?? [];
        if (!empty($firebaseOtpVerification['web_api_key'])) {
            return $firebaseOtpVerification['web_api_key'];
        }

        $fcmCredentials = getWebConfig('fcm_credentials') ?? [];
        return $fcmCredentials['apiKey'] ?? '';
    }

    public function sendOtp($phoneNumber): array
    {
        $apiKey = $this->getOtpApiKey();
        if (!$apiKey) {
            Log::warning('Firebase OTP send attempted without an API key.', ['phone' => $phoneNumber]);
            return [
                'result' => [],
                'sessionInfo' => '',
                'status' => 'error',
                'message' => 'Firebase OTP API key is missing',
                'errors' => 'MISSING_FIREBASE_API_KEY',
            ];
        }

        try {
            $response = Http::post('https://identitytoolkit.googleapis.com/v1/accounts:sendVerificationCode?key=' . $apiKey, [
                'phoneNumber' => $phoneNumber,
                'recaptchaToken' => request('g-recaptcha-response') ?? session('g-recaptcha-response'),
            ]);
        } catch (\Throwable $exception) {
            // Connection-level failures (unreachable host, DNS, timeout — common when
            // testing locally without outbound network access) must not 500 the request.
            Log::error('Firebase OTP request failed: ' . $exception->getMessage(), ['phone' => $phoneNumber]);
            return [
                'result' => [],
                'sessionInfo' => '',
                'status' => 'error',
                'message' => 'Firebase OTP request failed',
                'errors' => 'FIREBASE_REQUEST_FAILED',
            ];
        }

        $responseBody = $response->json();
        return [
            'result' => $responseBody,
            'sessionInfo' => trim($responseBody['sessionInfo'] ?? ''),
            'status' => $response->successful() ? 'success' : 'error',
            'message' => $responseBody['message'] ?? 'Something went wrong',
            'errors' => $responseBody['error']['message'] ?? null,
        ];
    }


    public function verifyOtp($sessionInfo, $phoneNumber, $otp): array
    {
        $apiKey = $this->getOtpApiKey();
        if (!$apiKey) {
            Log::warning('Firebase OTP verify attempted without an API key.', ['phone' => $phoneNumber]);
            return [
                'result' => [],
                'sessionInfo' => '',
                'status' => 'error',
                'message' => 'Firebase OTP API key is missing',
                'errors' => 'MISSING_FIREBASE_API_KEY',
            ];
        }

        $response = Http::post('https://identitytoolkit.googleapis.com/v1/accounts:signInWithPhoneNumber?key=' . $apiKey, [
            'sessionInfo' => $sessionInfo,
            'code' => $otp,
            'phoneNumber' => $phoneNumber,
        ]);
        $responseBody = $response->json();

        return [
            'result' => $responseBody,
            'sessionInfo' => trim($responseBody['sessionInfo'] ?? ''),
            'status' => $response->successful() ? 'success' : 'error',
            'message' => $responseBody['message'] ?? 'Something went wrong',
            'errors' => $responseBody['error']['message'] ?? 'No specific error message',
        ];
    }
}
