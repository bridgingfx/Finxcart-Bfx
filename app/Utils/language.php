<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

if (!function_exists('translate')) {
    function translate($key = null): string|null
    {
        $local = getDefaultLanguage();
        if ($key) {
            $local = getDefaultLanguage();
            App::setLocale($local);
            $key = getOrPutTranslateMessageValueByKey(local: $local, key: $key);
        }
        App::setLocale(getLanguageCode(country_code: $local));
        return $key;
    }

    function getOrPutTranslateMessageValueByKey(string $local, string $key): array|string|null
    {
        static $translatedMessagesCache = [];
        static $newMessagesCache = [];

        try {
            if (!array_key_exists($local, $translatedMessagesCache)) {
                $included = include(base_path('resources/lang/' . $local . '/messages.php'));
                // A concurrent request can be mid-write on this file (see the atomic-rename
                // fix below); a bare include() of a partially written file returns 1 (PHP's
                // default success value), not an array. Guard against that instead of letting
                // the TypeError from array_key_exists() below bubble up as a 500.
                $translatedMessagesCache[$local] = is_array($included) ? $included : [];
            }
            if (!array_key_exists($local, $newMessagesCache)) {
                $included = include(base_path('resources/lang/' . $local . '/new-messages.php'));
                $newMessagesCache[$local] = is_array($included) ? $included : [];
            }
            $translatedMessagesArray = $translatedMessagesCache[$local];
            $newMessagesArray = $newMessagesCache[$local];
            $key = str_replace('"', '', $key);
            $processedKey = ucfirst(str_replace('_', ' ', removeSpecialCharacters($key)));

            if (!array_key_exists($key, $translatedMessagesArray) && !array_key_exists($key, $newMessagesArray)) {
                $newMessagesArray[$key] = $processedKey;
                $newMessagesCache[$local] = $newMessagesArray;

                $languageFileContents = "<?php\n\nreturn [\n";
                foreach ($newMessagesArray as $languageKey => $value) {
                    $languageFileContents .= "\t\"" . $languageKey . "\" => \"" . $value . "\",\n";
                }
                $languageFileContents .= "];\n";

                // Write to a temp file in the same directory and rename() into place.
                // rename() on both POSIX and NTFS is atomic, so a concurrent request's
                // include() of new-messages.php can never observe a half-written file —
                // it either sees the old complete file or the new complete file. On
                // Windows, rename() can transiently fail with a sharing violation if
                // another process still has a handle open on the target (e.g. a
                // just-completed include()); retry briefly, and always clean up the
                // temp file so failures don't leak it into the lang directory.
                $targetPath = base_path('resources/lang/' . $local . '/new-messages.php');
                $tmpPath = $targetPath . '.tmp-' . getmypid() . '-' . uniqid('', true);
                file_put_contents($tmpPath, $languageFileContents);
                $renamed = false;
                for ($attempt = 0; $attempt < 3 && !$renamed; $attempt++) {
                    if ($attempt > 0) {
                        usleep(10000);
                    }
                    $renamed = @rename($tmpPath, $targetPath);
                }
                if (!$renamed) {
                    // Last resort: overwrite the target directly, then discard the temp file.
                    file_put_contents($targetPath, $languageFileContents);
                }
                if (file_exists($tmpPath)) {
                    @unlink($tmpPath);
                }
                $message = $processedKey;
            } elseif (array_key_exists($key, $translatedMessagesArray)) {
                $message = __('messages.' . $key);
            } elseif (array_key_exists($key, $newMessagesArray)) {
                $message = __('new-messages.' . $key);
            } else {
                $message = __('messages.' . $key);
            }
        } catch (\Throwable $exception) {
            $message = ucfirst(str_replace('_', ' ', removeSpecialCharacters(str_replace("\'", "'", $key))));
        }
        return $local == 'en' ? ucfirst($message) : $message;
    }
}

if (!function_exists('getDirectoriesByGivenPath')) {
    function getDirectoriesByGivenPath(string $path): array
    {
        $directories = [];
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item == '..' || $item == '.')
                continue;
            if (is_dir($path . '/' . $item))
                $directories[] = $item;
        }
        return $directories;
    }
}


if (!function_exists('removeSpecialCharacters')) {
    function removeSpecialCharacters(string|null $text): string|null
    {
        return str_ireplace(['\'', '"', ';', '<', '>', '?', '“', '”'], ' ', preg_replace('/\s\s+/', ' ', $text));
    }
}

if (!function_exists('getDefaultLanguage')) {
    function getDefaultLanguage(): string
    {
        if (strpos(url()->current(), '/api')) {
            $lang = App::getLocale();
        } elseif (session()->has('local')) {
            $lang = session('local');
        } else {
            $data = getWebConfig('language');
            $code = 'en';
            $direction = 'ltr';
            foreach ($data ?? [] as $ln) {
                if (array_key_exists('default', $ln) && $ln['default']) {
                    $code = $ln['code'];
                    if (array_key_exists('direction', $ln)) {
                        $direction = $ln['direction'];
                    }
                }
            }
            session()->put('local', $code);
            Session::put('direction', $direction);
            $lang = $code;
        }
        return $lang;
    }
}

if (!function_exists('getLanguageName')) {
    function getLanguageName(string $key): string
    {
        $values = getWebConfig('language');
        foreach ($values as $value) {
            if ($value['code'] == $key) {
                $key = $value['name'];
            }
        }
        return $key;
    }
}

if (!function_exists('getLanguageCode')) {
    function getLanguageCode(string $country_code): string
    {
        $locales = array('af-ZA',
            'am-ET',
            'ar-AE',
            'ar-BH',
            'ar-DZ',
            'ar-EG',
            'ar-IQ',
            'ar-JO',
            'ar-KW',
            'ar-LB',
            'ar-LY',
            'ar-MA',
            'ar-OM',
            'ar-QA',
            'ar-SA',
            'ar-SY',
            'ar-TN',
            'ar-YE',
            'az-Cyrl-AZ',
            'az-Latn-AZ',
            'be-BY',
            'bg-BG',
            'bn-BD',
            'bs-Cyrl-BA',
            'bs-Latn-BA',
            'cs-CZ',
            'da-DK',
            'de-AT',
            'de-CH',
            'de-DE',
            'de-LI',
            'de-LU',
            'dv-MV',
            'el-GR',
            'en-AU',
            'en-BZ',
            'en-CA',
            'en-GB',
            'en-IE',
            'en-JM',
            'en-MY',
            'en-NZ',
            'en-SG',
            'en-TT',
            'en-US',
            'en-ZA',
            'en-ZW',
            'es-AR',
            'es-BO',
            'es-CL',
            'es-CO',
            'es-CR',
            'es-DO',
            'es-EC',
            'es-ES',
            'es-GT',
            'es-HN',
            'es-MX',
            'es-NI',
            'es-PA',
            'es-PE',
            'es-PR',
            'es-PY',
            'es-SV',
            'es-US',
            'es-UY',
            'es-VE',
            'et-EE',
            'fa-IR',
            'fi-FI',
            'fil-PH',
            'fo-FO',
            'fr-BE',
            'fr-CA',
            'fr-CH',
            'fr-FR',
            'fr-LU',
            'fr-MC',
            'he-IL',
            'hi-IN',
            'hr-BA',
            'hr-HR',
            'hu-HU',
            'hy-AM',
            'id-ID',
            'ig-NG',
            'is-IS',
            'it-CH',
            'it-IT',
            'ja-JP',
            'ka-GE',
            'kk-KZ',
            'kl-GL',
            'km-KH',
            'ko-KR',
            'ky-KG',
            'lb-LU',
            'lo-LA',
            'lt-LT',
            'lv-LV',
            'mi-NZ',
            'mk-MK',
            'mn-MN',
            'ms-BN',
            'ms-MY',
            'mt-MT',
            'nb-NO',
            'ne-NP',
            'nl-BE',
            'nl-NL',
            'pl-PL',
            'prs-AF',
            'ps-AF',
            'pt-BR',
            'pt-PT',
            'ro-RO',
            'ru-RU',
            'rw-RW',
            'sv-SE',
            'si-LK',
            'sk-SK',
            'sl-SI',
            'sq-AL',
            'sr-Cyrl-BA',
            'sr-Cyrl-CS',
            'sr-Cyrl-ME',
            'sr-Cyrl-RS',
            'sr-Latn-BA',
            'sr-Latn-CS',
            'sr-Latn-ME',
            'sr-Latn-RS',
            'sw-KE',
            'tg-Cyrl-TJ',
            'th-TH',
            'tk-TM',
            'tr-TR',
            'uk-UA',
            'ur-PK',
            'uz-Cyrl-UZ',
            'uz-Latn-UZ',
            'vi-VN',
            'wo-SN',
            'yo-NG',
            'zh-CN',
            'zh-HK',
            'zh-MO',
            'zh-SG',
            'zh-TW');

        foreach ($locales as $locale) {
            $locale_region = explode('-', $locale);
            if (strtoupper($country_code) == $locale_region[1]) {
                return $locale_region[0];
            }
        }

        return "en";
    }
}
if (!function_exists('autoTranslator')) {
    function autoTranslator($q, $sl, $tl): array|string
    {
        $res = file_get_contents("https://translate.googleapis.com/translate_a/single?client=gtx&ie=UTF-8&oe=UTF-8&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&dt=at&sl=" . $sl . "&tl=" . $tl . "&hl=hl&q=" . urlencode($q), $_SERVER['DOCUMENT_ROOT'] . "/transes.html");
        $res = json_decode($res);
        return str_replace('_', ' ', $res[0][0][0]);
    }
}
