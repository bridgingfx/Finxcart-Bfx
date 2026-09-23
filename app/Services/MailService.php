<?php

namespace App\Services;

use Exception;
use App\Mail\TestEmailSender;
use App\Services\BrevoMailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService
{
    private function getActiveMailConfig(): ?array
    {
        $mailConfig = getWebConfig(name: 'mail_config');
        if (($mailConfig['status'] ?? 0) == 0) {
            $mailConfig = getWebConfig(name: 'mail_config_sendgrid');
        }
        if (($mailConfig['status'] ?? 0) == 0) {
            $mailConfig = getWebConfig(name: 'mail_config_brevo');
        }
        if (($mailConfig['status'] ?? 0) != 1) {
            return null;
        }
        $mailConfig['driver'] = strtolower($mailConfig['driver'] ?? 'smtp');
        $mailConfig['encryption'] = strtolower($mailConfig['encryption'] ?? 'tls');
        return $mailConfig;
    }

    public function getData(object $request): array
    {
        return [
            "status" => $request->get('status', 0),
            "name" => $request['name'],
            "host" => $request['host'],
            "driver" => $request['driver'],
            "port" => $request['port'],
            "username" => $request['username'],
            "email_id" => $request['email'],
            "encryption" => $request['encryption'],
            "password" => $request['password']
        ];
    }

    public function getMailData(object|array $mailData): array
    {
        return [
            "status" => 0,
            "name" => $mailData['name'],
            "host" => $mailData['host'],
            "driver" => $mailData['driver'],
            "port" => $mailData['port'],
            "username" => $mailData['username'],
            "email_id" => $mailData['email_id'],
            "encryption" => $mailData['encryption'],
            "password" => $mailData['password']
        ];
    }

    public function sendMail(object $request): array
    {
        $status = 0;
        $message = 'success';
        try {
            $mailConfig = $this->getActiveMailConfig();
            if (!$mailConfig) {
                return ['status' => 0, 'message' => 'Mail configuration is inactive'];
            }

            $fromAddress = $mailConfig['from'] ?? ($mailConfig['email_id'] ?? $mailConfig['username']);
            $fromName = $mailConfig['name'] ?? config('app.name', 'Finxcart');

            Log::info('ADMIN TEST MAIL ATTEMPT', [
                'to' => $request->email,
                'from' => $fromAddress,
                'from_name' => $fromName,
                'driver' => $mailConfig['driver'],
            ]);

            // Use Brevo HTTP API if driver is sendgrid
            $brevo = BrevoMailService::makeFromConfig($mailConfig);
            if ($brevo) {
                $htmlContent = '<h2>Test Email</h2><p>This is a test email from Finxcart sent via Brevo API.</p>';
                $brevo->send($request->email, 'Test Email - Finxcart', $htmlContent);
            } else {
                // Fall back to SMTP
                config([
                    'mail.driver' => $mailConfig['driver'],
                    'mail.host' => $mailConfig['host'],
                    'mail.port' => $mailConfig['port'],
                    'mail.username' => $mailConfig['username'],
                    'mail.password' => $mailConfig['password'],
                    'mail.encryption' => $mailConfig['encryption'],
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.transport' => 'smtp',
                    'mail.mailers.smtp.host' => $mailConfig['host'],
                    'mail.mailers.smtp.port' => $mailConfig['port'],
                    'mail.mailers.smtp.username' => $mailConfig['username'],
                    'mail.mailers.smtp.password' => $mailConfig['password'],
                    'mail.mailers.smtp.encryption' => $mailConfig['encryption'],
                    'mail.mailers.smtp.timeout' => null,
                    'mail.mailers.smtp.auth_mode' => 'login',
                    'mail.mailers.smtp.verify_peer' => false,
                    'mail.mailers.smtp.verify_peer_name' => false,
                    'mail.mailers.smtp.allow_self_signed' => true,
                    'mail.from.address' => $fromAddress,
                    'mail.from.name' => $fromName,
                ]);
                app()->forgetInstance('mail.manager');
                app()->forgetInstance('mailer');
                app()->forgetInstance('swift.mailer');
                app()->forgetInstance('swift.transport');
                Mail::mailer('smtp')->to($request->email)->send(
                    (new TestEmailSender())->from($fromAddress, $fromName)
                );
            }

            Log::info('ADMIN TEST MAIL SUCCESS', [
                'to' => $request->email,
                'from' => $fromAddress,
                'from_name' => $fromName,
            ]);
            $status = 1;
        } catch (Exception $exception) {
            $message = $exception->getMessage();
            $status = 2;
            Log::error('ADMIN TEST MAIL FAILED', [
                'error' => $exception->getMessage(),
                'to' => $request->email ?? null,
            ]);
        }
        return ['status' => $status, 'message' => $message];
    }
}