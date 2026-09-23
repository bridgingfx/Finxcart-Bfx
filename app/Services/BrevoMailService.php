<?php
namespace App\Services;

use Illuminate\Support\Facades\Log;

class BrevoMailService
{
    private string $apiKey;
    private string $fromEmail;
    private string $fromName;

    public function __construct(string $apiKey, string $fromEmail, string $fromName)
    {
        $this->apiKey = $apiKey;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function send(string $toEmail, string $subject, string $htmlContent): void
    {
        $payload = json_encode([
            'sender' => ['email' => $this->fromEmail, 'name' => $this->fromName],
            'to' => [['email' => $toEmail]],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ]);

        [$response, $httpCode, $curlError] = $this->performRequest($payload);

        if (
            $curlError &&
            $this->shouldRetryWithoutSslVerification($curlError)
        ) {
            Log::warning('BREVO MAIL SSL VERIFY FAILED, retrying without peer verification in local/dev', [
                'to' => $toEmail,
                'error' => $curlError,
            ]);

            [$response, $httpCode, $curlError] = $this->performRequest($payload, false);
        }

        if ($curlError) {
            throw new \RuntimeException('Brevo API curl error: ' . $curlError);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException('Brevo API error (' . $httpCode . '): ' . $response);
        }

        Log::info('BREVO MAIL SENT', ['to' => $toEmail, 'subject' => $subject, 'http_code' => $httpCode]);
    }

    private function performRequest(string $payload, bool $verifySsl = true): array
    {
        $ch = \curl_init('https://api.brevo.com/v3/smtp/email');
        \curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'api-key: ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => $verifySsl,
            CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
        ]);

        $response = \curl_exec($ch);
        $httpCode = \curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = \curl_error($ch);
        \curl_close($ch);

        return [$response, $httpCode, $curlError];
    }

    private function shouldRetryWithoutSslVerification(string $curlError): bool
    {
        return app()->environment(['local', 'development', 'dev'])
            && str_contains(strtolower($curlError), 'ssl certificate problem');
    }

    public static function makeFromConfig(array $mailConfig): ?self
    {
        $driver = strtolower($mailConfig['driver'] ?? '');
        if (!in_array($driver, ['sendgrid', 'brevo'], true)) {
            return null;
        }
        $apiKey = $mailConfig['password'] ?? null;
        $fromEmail = $mailConfig['email_id'] ?? ($mailConfig['username'] ?? null);
        $fromName = $mailConfig['name'] ?? config('app.name', 'Finxcart');

        if (!$apiKey || !$fromEmail) {
            return null;
        }
        return new self($apiKey, $fromEmail, $fromName);
    }
}
