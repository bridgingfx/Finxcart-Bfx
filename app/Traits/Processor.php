<?php

namespace App\Traits;

use App\Models\Setting;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Storage;

trait  Processor
{
    public function response_formatter($constant, $content = null, $errors = []): array
    {
        $constant = (array)$constant;
        $constant['content'] = $content;
        $constant['errors'] = $errors;
        return $constant;
    }

    public function error_processor($validator): array
    {
        $errors = [];
        foreach ($validator->errors()->getMessages() as $index => $error) {
            $errors[] = ['error_code' => $index, 'message' => self::translate($error[0])];
        }
        return $errors;
    }

    public function translate($key)
    {
        try {
            App::setLocale('en');
            $lang_array = include(base_path('resources/lang/' . 'en' . '/lang.php'));
            $processed_key = ucfirst(str_replace('_', ' ', str_ireplace(['\'', '"', ',', ';', '<', '>', '?'], ' ', $key)));
            if (!array_key_exists($key, $lang_array)) {
                $lang_array[$key] = $processed_key;
                $str = "<?php return " . var_export($lang_array, true) . ";";
                file_put_contents(base_path('resources/lang/' . 'en' . '/lang.php'), $str);
                $result = $processed_key;
            } else {
                $result = __('lang.' . $key);
            }
            return $result;
        } catch (\Exception $exception) {
            return $key;
        }
    }

    public function payment_config($key, $settings_type): object|null
    {
        try {
            $config = DB::table('addon_settings')->where('key_name', $key)
                ->where('settings_type', $settings_type)->first();
        } catch (Exception $exception) {
            // Table missing (e.g. before installation): callers expect null
            // here, not an empty model (see the object|null signature).
            return null;
        }

        if (!isset($config)) {
            return null;
        }

        // All gateway controllers only initialize their credentials when the
        // mode is live or test; anything else leaves their typed
        // $config_values property uninitialized and crashes route
        // registration. Normalize to null so the "config usable" invariant
        // (non-null => mode is live/test) always holds.
        if (!in_array($config->mode ?? null, ['live', 'test'], true)) {
            return null;
        }

        return $config;
    }

    public function file_uploader(string $dir, string $format, $image = null, $old_image = null)
    {
        if ($image == null) return $old_image ?? 'def.png';

        if (isset($old_image)) Storage::disk('public')->delete($dir . $old_image);

        $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
        Storage::disk('public')->put($dir . $imageName, file_get_contents($image));

        return $imageName;
    }

    public function payment_response($payment_info, $payment_flag): Application|JsonResponse|Redirector|RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        // Every gateway controller's failure path ends up calling this with
        // whatever ->first() returned, which is null whenever the transaction/
        // session/reference ID a callback URL was hit with doesn't match a real
        // payment row (bookmarked or replayed callback URL, expired session,
        // gateway sent back a param this app doesn't recognize, etc.). Without
        // this guard, that null crashes on the very next line — instead of
        // reaching the payment-fail page callers already intend to show.
        if (!isset($payment_info)) {
            return redirect()->route('payment-fail');
        }

        $getNewUser = (int)0;
        $additionalData = json_decode($payment_info->additional_data, true);

        if (isset($additionalData['new_customer_id']) && isset($additionalData['is_guest_in_order'])) {
            $getNewUser = (int)(($additionalData['new_customer_id'] != 0 && $additionalData['is_guest_in_order'] != 1) ? 1 : 0);
        }

        $token_string = 'payment_method=' . $payment_info->payment_method . '&&transaction_reference=' . $payment_info->transaction_id;
        if (in_array($payment_info->payment_platform, ['web', 'app']) && $payment_info['external_redirect_link'] != null) {
            return redirect($payment_info['external_redirect_link'] . '?flag=' . $payment_flag . '&&token=' . base64_encode($token_string) . '&&new_user=' . $getNewUser);
        }
        return redirect()->route('payment-' . $payment_flag, ['token' => base64_encode($token_string), 'new_user' => $getNewUser]);
    }
}
