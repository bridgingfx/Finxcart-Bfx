<?php
namespace App\Traits;
use App\Mail\SendMail;
use App\Models\EmailTemplate;
use App\Models\SocialMedia;
use App\Services\BrevoMailService;
use App\Repositories\EmailTemplatesRepository;
use App\Services\EmailTemplateService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

trait EmailTemplateTrait
{
    use FileManagerTrait;

    protected function textVariableFormat(
        $value, $userName = null, $adminName = null, $vendorName = null, $shopName = null, $shopId = null,
        $deliveryManName = null, $orderId = null, $emailId = null, $rejectionReason = null, $sellerType = null, $message = null)
    {
        $data = $value;
        if ($data) {
            $data = $userName ? str_replace("{userName}", $userName, $data) : $data;
            $data = $vendorName ? str_replace("{vendorName}", $vendorName, $data) : $data;
            $data = $adminName ? str_replace("{adminName}", $adminName, $data) : $data;
            $data = $shopName ? str_replace("{shopName}", $shopName, $data) : $data;
            $data = $shopId ? str_replace("{shopId}", $shopId, $data) : $data;
            $data = $deliveryManName ? str_replace("{deliveryManName}", $deliveryManName, $data) : $data;
            $data = $orderId ? str_replace("{orderId}", $orderId, $data) : $data;
            $data = $emailId ? str_replace("{emailId}", $emailId, $data) : $data;
            $data = str_replace("{rejectionReason}", $rejectionReason ?? '', $data);
            $data = $sellerType ? str_replace("{sellerType}", $sellerType, $data) : $data;
            $data = $message ? str_replace("{message}", $message, $data) : $data;
        }
        return $data;
    }

    protected function sendingMail($sendMailTo, $userType, $templateName, $data = null): void
    {
        $template = EmailTemplate::with('translationCurrentLanguage')->where(['user_type' => $userType, 'template_name' => $templateName])->first();
        if (!$template) {
            \Log::error('MAIL TEMPLATE NOT FOUND', ['to' => $sendMailTo, 'user_type' => $userType, 'template' => $templateName]);
            throw new \RuntimeException("Mail template not found: {$templateName}");
        }

        if (count($template['translationCurrentLanguage'])) {
            foreach ($template?->translationCurrentLanguage ?? [] as $translate) {
                $template['title'] = $translate->key == 'title' ? $translate->value : $template['title'];
                $template['body'] = $translate->key == 'body' ? $translate->value : $template['body'];
                $template['footer_text'] = $translate->key == 'copyright_text' ? $translate->value : $template['footer_text'];
                $template['copyright_text'] = $translate->key == 'footer_text' ? $translate->value : $template['copyright_text'];
                $template['button_name'] = $translate->key == 'button_name' ? $translate->value : $template['button_name'];
            }
        }

        $socialMedia = SocialMedia::where(['status' => 1])->get();
        $template['body'] = $this->textVariableFormat(
            value: $template['body'],
            userName: $data['userName'] ?? null,
            adminName: $data['adminName'] ?? null,
            vendorName: $data['vendorName'] ?? null,
            shopName: $data['shopName'] ?? null,
            shopId: $data['shopId'] ?? null,
            deliveryManName: $data['deliveryManName'] ?? null,
            orderId: $data['orderId'] ?? null,
            emailId: $data['emailId'] ?? null,
            rejectionReason: $data['rejectionReason'] ?? null,
            sellerType: $data['sellerType'] ?? null,
            message: $data['message'] ?? null
        );
        $template['title'] = $this->textVariableFormat(
            value: $template['title'],
            userName: $data['userName'] ?? null,
            adminName: $data['adminName'] ?? null,
            vendorName: $data['vendorName'] ?? null,
            shopName: $data['shopName'] ?? null,
            deliveryManName: $data['deliveryManName'] ?? null,
            orderId: $data['orderId'] ?? null,
            rejectionReason: $data['rejectionReason'] ?? null,
            sellerType: $data['sellerType'] ?? null,
            message: $data['message'] ?? null
        );

        $data['send-mail'] = true;

        if ($template['status'] != 1) {
            \Log::error('MAIL TEMPLATE INACTIVE', ['to' => $sendMailTo, 'user_type' => $userType, 'template' => $templateName]);
            throw new \RuntimeException("Mail template inactive: {$templateName}");
        }

        $mailConfig = getWebConfig(name: 'mail_config');
        if (($mailConfig['status'] ?? 0) == 0) {
            $mailConfig = getWebConfig(name: 'mail_config_sendgrid');
        }
        if (($mailConfig['status'] ?? 0) == 0) {
            $mailConfig = getWebConfig(name: 'mail_config_brevo');
        }
        if (($mailConfig['status'] ?? 0) != 1) {
            \Log::error('MAIL CONFIG INACTIVE', ['to' => $sendMailTo, 'template' => $templateName]);
            throw new \RuntimeException('Mail configuration is inactive');
        }

        $fromAddress = $mailConfig['from'] ?? ($mailConfig['email_id'] ?? ($mailConfig['username'] ?? config('mail.from.address')));
        $fromName = $mailConfig['name'] ?? config('app.name', 'Finxcart');

        try {
            // Use Brevo HTTP API if driver is sendgrid
            $brevo = BrevoMailService::makeFromConfig($mailConfig);
            if ($brevo) {
                try {
                    $sendMail = new SendMail($data, $template, $socialMedia);
                    $htmlContent = View::make('email-templates.index', [
                        'data' => $data,
                        'template' => $template,
                        'socialMedia' => $socialMedia,
                    ])->render();
                } catch (\Throwable $e) {
                    // Fallback: render the mailable directly
                    $htmlContent = '<h2>' . ($template['title'] ?? 'Notification') . '</h2><p>' . ($template['body'] ?? '') . '</p>';
                }

                try {
                    $brevo->send($sendMailTo, $template['title'] ?? 'Notification from Finxcart', $htmlContent);
                    \Log::info('MAIL SEND SUCCESS', ['to' => $sendMailTo, 'template' => $templateName, 'via' => 'brevo_api']);
                } catch (\Throwable $e) {
                    \Log::error('MAIL SEND FAILED', ['error' => $e->getMessage(), 'to' => $sendMailTo, 'template' => $templateName, 'via' => 'brevo_api']);
                    throw $e;
                }
            } else {
                // SMTP path
                try {
                    Mail::mailer('smtp')->to($sendMailTo)->send(
                        (new SendMail($data, $template, $socialMedia))->from($fromAddress, $fromName)
                    );
                    \Log::info('MAIL SEND SUCCESS', ['to' => $sendMailTo, 'template' => $templateName, 'from' => $fromAddress, 'via' => 'smtp']);
                } catch (\Throwable $e) {
                    \Log::error('MAIL SEND FAILED', ['error' => $e->getMessage(), 'to' => $sendMailTo ?? 'unknown', 'template' => $templateName ?? 'unknown', 'from' => $fromAddress]);
                    throw $e;
                }
            }
        } finally {
            // Always clean up the temp invoice PDF, even if the send above failed —
            // previously a failed send left the file leaking on disk indefinitely.
            if (!empty($data['attachmentPath']) && file_exists($data['attachmentPath'])) {
                unlink($data['attachmentPath']);
            }
        }
    }

    public function getEmailTemplateDataForUpdate($userType): void
    {
        $emailTemplates = EmailTemplate::where(['user_type' => $userType])->get();
        $emailTemplateArray = (new EmailTemplateService)->getEmailTemplateData(userType: $userType);
        foreach ($emailTemplateArray as $value) {
            $checkKey = $emailTemplates->where('template_name', $value)->first();
            if ($checkKey === null) {
                $hideField = (new EmailTemplateService)->getHiddenField(userType: $userType, templateName: $value);
                $title = (new EmailTemplateService)->getTitleData(userType: $userType, templateName: $value);
                $body = (new EmailTemplateService)->getBodyData(userType: $userType, templateName: $value);
                $addData = (new EmailTemplateService)->getAddData(userType: $userType, templateName: $value, hideField: $hideField, title: $title, body: $body);
                EmailTemplate::create($addData);
            }
        }
        foreach ($emailTemplates as $value) {
            if (!in_array($value['template_name'], $emailTemplateArray)) {
                EmailTemplate::find($value['id'])->delete();
            }
        }
    }
}
