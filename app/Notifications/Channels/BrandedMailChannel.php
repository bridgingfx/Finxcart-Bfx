<?php

namespace App\Notifications\Channels;

use App\Models\SocialMedia;
use App\Services\BrevoMailService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

/**
 * Renders Notification::toMail() MailMessages through the app's branded
 * layout (logo/footer/social links) and delivers via the same Brevo-API-or-SMTP
 * path the admin-configurable email templates use.
 *
 * The stock Laravel "mail" channel never picks up the runtime Brevo config —
 * MailConfigServiceProvider only wires config('mail.mailers.smtp.*') and returns
 * early for the brevo/sendgrid drivers (see sendingMail() in EmailTemplateTrait,
 * which is why those emails still work). Any Notification using ->via(['mail', ...])
 * therefore silently fails to send whenever Brevo/SendGrid is the active driver,
 * and even when SMTP is active it renders Laravel's unbranded default template.
 */
class BrandedMailChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        $to = $notifiable->routeNotificationFor('mail', $notification);
        if (!$to) {
            return;
        }

        /** @var MailMessage $mailMessage */
        $mailMessage = $notification->toMail($notifiable);

        try {
            $html = View::make('email-templates.branded-notification', [
                'subject' => $mailMessage->subject ?: config('app.name'),
                'greeting' => $mailMessage->greeting ?: (translate('hello') . '!'),
                'introLines' => $mailMessage->introLines,
                'actionText' => $mailMessage->actionText,
                'actionUrl' => $mailMessage->actionUrl,
                'outroLines' => $mailMessage->outroLines,
                'salutation' => $mailMessage->salutation ?: (translate('regards') . ',<br>' . config('app.name')),
                'socialMedia' => SocialMedia::where('status', 1)->get(),
            ])->render();

            $mailConfig = getWebConfig(name: 'mail_config');
            if (($mailConfig['status'] ?? 0) == 0) {
                $mailConfig = getWebConfig(name: 'mail_config_sendgrid');
            }
            if (($mailConfig['status'] ?? 0) == 0) {
                $mailConfig = getWebConfig(name: 'mail_config_brevo');
            }

            $brevo = is_array($mailConfig) ? BrevoMailService::makeFromConfig($mailConfig) : null;

            if ($brevo) {
                $brevo->send($to, $mailMessage->subject, $html);
            } else {
                Mail::html($html, function ($mail) use ($to, $mailMessage) {
                    $mail->to($to)->subject($mailMessage->subject);
                });
            }
        } catch (\Throwable $e) {
            Log::error('[BrandedMailChannel] Send failed: ' . $e->getMessage(), [
                'to' => $to,
                'notification' => get_class($notification),
            ]);
        }
    }
}
