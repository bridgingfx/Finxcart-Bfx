<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NowPaymentsService
{
    private const INVOICE_ENDPOINT = 'https://api.nowpayments.io/v1/invoice';

    public function __construct(private readonly APIService $apiService)
    {
    }

    public function getApiKey(): ?string
    {
        return $this->apiService->getKey('nowpayments', 'api_key');
    }

    public function getIpnSecret(): ?string
    {
        return $this->apiService->getKey('nowpayments', 'ipn_secret');
    }

    public function getInvoiceEndpoint(): string
    {
        return self::INVOICE_ENDPOINT;
    }

    public function verifyIpnSignature(Request $request): bool
    {
        $ipnSecret = $this->getIpnSecret();
        if (empty($ipnSecret)) {
            Log::warning('NowPayments IPN: ipn_secret not configured');
            return false;
        }

        $receivedSignature = $request->header('x-nowpayments-sig');
        $rawBody = $request->getContent();
        if ($receivedSignature === null || $receivedSignature === '' || $rawBody === '') {
            return false;
        }

        $payload = json_decode($rawBody, true);
        if (!is_array($payload)) {
            return false;
        }

        $this->recursiveKsort($payload);
        $sortedJson = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $calculatedSignature = hash_hmac('sha512', $sortedJson, trim($ipnSecret));

        return hash_equals($calculatedSignature, $receivedSignature);
    }

    /**
     * @param array<string, mixed> $array
     */
    protected function recursiveKsort(array &$array): void
    {
        ksort($array);
        foreach (array_keys($array) as $k) {
            if (is_array($array[$k])) {
                $this->recursiveKsort($array[$k]);
            }
        }
    }
}
