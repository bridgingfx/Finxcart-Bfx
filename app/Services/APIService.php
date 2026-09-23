<?php

namespace App\Services;

class APIService
{
    public function getConfig(String $payment_method_name): ?array
    {
        $configService = app(ConfigManagementService::class);
        $data = $configService->getData();
        if (!is_array($data) || empty($data['payments'][$payment_method_name]) || !is_array($data['payments'][$payment_method_name])) {
            return null;
        }
        return $data['payments'][$payment_method_name];
    }

    public function getKey(String $payment_method_name, String $key_name): ?string
    {
        $config = $this->getConfig($payment_method_name);
        $apiKey = $config[$key_name] ?? null;
        if (!is_string($apiKey) || trim($apiKey) === '') {
            return null;
        }
        return trim($apiKey);
    }
}
