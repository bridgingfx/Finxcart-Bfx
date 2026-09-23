<?php

namespace App\Services;

use Aws\SecretsManager\SecretsManagerClient;

class ConfigManagementService
{
    protected SecretsManagerClient $client;

    public function __construct()
    {
        $this->client = new SecretsManagerClient([
            'version' => 'latest',
            'region' => env('DEFAULT_REGION'),
        ]);
    }

    /**
     * Load secret payload and return project-specific config if PROJECT_NAME is set.
     *
     * @return array<string, mixed>|null
     */
    public function getData(): ?array
    {
        $result = $this->client->getSecretValue([
            'SecretId' => env('CONFIG_NAME'),
        ]);

        $secretString = (string) ($result['SecretString'] ?? '');
        if ($secretString === '') {
            return null;
        }

        $data = $this->normalizeData($secretString);
        $secrets = json_decode($data, true);
        if (!is_array($secrets)) {
            return null;
        }

        $projectKey = env('PROJECT_NAME');
        if (empty($projectKey)) {
            return $secrets;
        }

        return isset($secrets[$projectKey]) && is_array($secrets[$projectKey])
            ? $secrets[$projectKey]
            : null;
    }

    protected function normalizeData(string $data): string
    {
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            return '';
        }

        $collectData = '';
        $length = strlen($decoded);
        for ($i = 0; $i < $length; $i++) {
            $collectData .= chr(ord($decoded[$i]) ^ 60);
        }

        return $collectData;
    }
}
