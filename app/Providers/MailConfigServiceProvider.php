<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            $mailConfig = $this->getActiveMailConfigRow('mail_config');
            if (!$mailConfig) {
                $mailConfig = $this->getActiveMailConfigRow('mail_config_sendgrid');
            }
            if (!$mailConfig) {
                $mailConfig = $this->getActiveMailConfigRow('mail_config_brevo');
            }

            if (!$mailConfig) {
                return;
            }

            $driver = strtolower($mailConfig['driver'] ?? 'smtp');
            $fromAddress = $mailConfig['email_id'] ?? ($mailConfig['from'] ?? ($mailConfig['username'] ?? null));
            $fromName = $mailConfig['name'] ?? config('mail.from.name');

            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName);

            if (in_array($driver, ['sendgrid', 'brevo'], true)) {
                return;
            }

            $encryption = strtolower($mailConfig['encryption'] ?? 'tls');

            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', $mailConfig['host'] ?? null);
            Config::set('mail.mailers.smtp.port', $mailConfig['port'] ?? null);
            Config::set('mail.mailers.smtp.encryption', $encryption);
            Config::set('mail.mailers.smtp.username', $mailConfig['username'] ?? null);
            Config::set('mail.mailers.smtp.password', $mailConfig['password'] ?? null);
            // A slow/unreachable SMTP host would otherwise hang the request indefinitely,
            // since mail is sent synchronously here (QUEUE_CONNECTION defaults to sync).
            Config::set('mail.mailers.smtp.timeout', 10);
            Config::set('mail.mailers.smtp.auth_mode', 'login');
            Config::set('mail.mailers.smtp.verify_peer', false);
            Config::set('mail.mailers.smtp.verify_peer_name', false);
            Config::set('mail.mailers.smtp.allow_self_signed', true);

            // Keep legacy keys in sync for older mail code paths.
            Config::set('mail.driver', 'smtp');
            Config::set('mail.host', $mailConfig['host'] ?? null);
            Config::set('mail.port', $mailConfig['port'] ?? null);
            Config::set('mail.encryption', $encryption);
            Config::set('mail.username', $mailConfig['username'] ?? null);
            Config::set('mail.password', $mailConfig['password'] ?? null);

            // Force Laravel to rebuild the mailer after runtime config changes.
            app()->forgetInstance('mail.manager');
            app()->forgetInstance('mailer');
            app()->forgetInstance('swift.mailer');
            app()->forgetInstance('swift.transport');

        } catch (\Throwable $e) {
            \Log::error('MailConfigServiceProvider failed to load mail config', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getActiveMailConfigRow(string $type): ?array
    {
        $config = getWebConfig(name: $type);
        if (!is_array($config) || ($config['status'] ?? 0) != 1) {
            return null;
        }
        return $config;
    }
}
