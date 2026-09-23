<?php

namespace App\Http\Controllers;

use App\Traits\RecaptchaTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SharedController extends Controller
{
    use RecaptchaTrait;

    public function changeLanguage(Request $request): JsonResponse
    {
        $direction = 'ltr';
        $language = getWebConfig('language');
        foreach ($language as $data) {
            if ($data['code'] == $request['language_code']) {
                $direction = $data['direction'] ?? 'ltr';
            }
        }

        $languageCode = $request['language_code'];
        App::setLocale($languageCode);
        session()->put('local', $languageCode);
        session()->put('locale', $languageCode);
        Session::put('direction', $direction);

        return response()->json(['message' => translate('language_change_successfully') . '.']);
    }

    public function changeLanguagePath(Request $request, string $languageCode): RedirectResponse
    {
        $direction = 'ltr';
        $language = getWebConfig('language');
        $availableLanguageCodes = [];

        foreach ($language as $data) {
            $availableLanguageCodes[] = $data['code'];
            if ($data['code'] == $languageCode) {
                $direction = $data['direction'] ?? 'ltr';
            }
        }

        if (!in_array($languageCode, $availableLanguageCodes, true)) {
            return redirect()->back();
        }

        App::setLocale($languageCode);
        session()->put('local', $languageCode);
        session()->put('locale', $languageCode);
        Session::put('direction', $direction);

        return redirect()->back();
    }

    public function getSessionRecaptchaCode(Request $request): JsonResponse
    {
        if (env('APP_MODE') == 'dev' && session()->has($request['sessionKey'])) {
            $code = session($request['sessionKey']);
        }
        return response()->json(['code' => $code ?? '']);
    }

    public function storeRecaptchaResponse(Request $request): JsonResponse
    {
        $response = $request->get('g_recaptcha_response', null);
        if ($response) {
            session()->put('g-recaptcha-response', $response);
        }
        return response()->json(['recaptcha' => $response]);
    }

    public function storeRecaptchaSession(Request $request): void
    {
        $recaptchaBuilder = $this->generateDefaultReCaptcha(4);
        if (session()->has($request['sessionKey'])) {
            Session::forget($request['sessionKey']);
        }
        Session::put($request['sessionKey'], $recaptchaBuilder->getPhrase());
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        $recaptchaBuilder->output();
    }


}
