<?php

namespace App\Traits;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Support\Facades\Session;

trait RecaptchaTrait
{
    protected function isGoogleRecaptchaValid(string $reCaptchaValue): bool
    {
        $secret_key = getWebConfig(name: 'recaptcha')['secret_key'];
        $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $reCaptchaValue;

        // Without an explicit timeout, a slow/unreachable Google endpoint blocks
        // the whole request (login/register/contact submit) for the default PHP
        // socket timeout — 60s+ — making the form look permanently "stuck loading."
        $context = stream_context_create(['http' => ['timeout' => 5]]);
        $result = @file_get_contents($url, false, $context);
        if ($result === false) {
            \Log::error('[Recaptcha] siteverify request failed or timed out');
            return false;
        }

        $response = json_decode($result);
        return $response->success ?? false;
    }

    public function generateDefaultReCaptcha(int $captureLength): CaptchaBuilder
    {
        $phrase = new PhraseBuilder;
        $code = $phrase->build($captureLength);
        $builder = new CaptchaBuilder($code, $phrase);
        $builder->setBackgroundColor(220, 210, 230);
        $builder->setMaxAngle(25);
        $builder->setMaxBehindLines(0);
        $builder->setMaxFrontLines(0);
        $builder->build($width = 100, $height = 40, $font = null);
        return $builder;
    }


    public function saveRecaptchaValueInSession(string $sessionKey, string $sessionValue):void{
        if (Session::has($sessionKey)) {
            Session::forget($sessionKey);
        }
        Session::put($sessionKey, $sessionValue);
    }
}
