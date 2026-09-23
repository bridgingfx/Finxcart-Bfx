<?php

declare(strict_types=1);

$targets = $argv;
array_shift($targets);

if (!$targets) {
    $targets = ['en', 'es', 'fr', 'ar', 'sa', 'bd'];
}

foreach ($targets as $locale) {
    $file = __DIR__ . "/../resources/lang/{$locale}/new-messages.php";
    $lines = file($file);

    if ($lines === false) {
        fwrite(STDERR, "Failed to read {$file}\n");
        exit(1);
    }

    $output = [];
    $removed = 0;

    foreach ($lines as $line) {
        if (preg_match('/^\s*"((?:\\\\.|[^"\\\\])*)"\s*=>\s*"((?:\\\\.|[^"\\\\])*)",\s*$/', $line, $matches)) {
            $key = stripcslashes($matches[1]);
            $value = stripcslashes($matches[2]);

            if (shouldPrune($key, $value)) {
                $removed++;
                continue;
            }
        }

        $output[] = $line;
    }

    file_put_contents($file, implode('', $output));
    echo "{$locale}: pruned {$removed} entries" . PHP_EOL;
}

function shouldPrune(string $key, string $value): bool
{
    $exactKeys = [
        'src', 'alt', 'href', 'title', 'link', 'slug', 'logo', 'search',
        'cart', 'profile', 'finxcart', 'visa', 'mastercard', 'discover',
        'skrill', 'paypal', 'razor', 's', 'x', 'x_2',
        'autocomplete_off_required', 'button_disabled', 'opacity_0_6',
        'cursor_not_allowed', 'getlocale_dir', 'get_direction',
    ];
    if (in_array($key, $exactKeys, true)) {
        return true;
    }

    $keyPatterns = [
        '/^protected_block_/i',
        '/^data_/i',
        '/^class_/i',
        '/^style_/i',
        '/^id(_\d+)?$/i',
        '/^id_.+/i',
        '/^text_.+/i',
        '/^count_.+/i',
        '/^isset_.+/i',
        '/^empty_.+/i',
        '/^auth_.+/i',
        '/^request_.+/i',
        '/^session_.+/i',
        '/^env_.+/i',
        '/^json_.+/i',
        '/^base64_.+/i',
        '/^cart.+/i',
        '/^cartlist.+/i',
        '/^product.+/i',
        '/^order.+/i',
        '/^reviewdata.+/i',
        '/^minimum_.+/i',
        '/^free_delivery_.+/i',
        '/^shipping.+/i',
        '/^payment.+/i',
        '/^invoice.+/i',
        '/^blog.+/i',
        '/^sociallogin.+/i',
        '/^rememberid.+/i',
        '/^web_config.+/i',
        '/^flashdeal.+/i',
        '/^decimalpointsettings.+/i',
        '/^getlocale_.+/i',
        '/^get_direction.+/i',
        '/^oninput_.+/i',
        '/^name_.+/i',
        '/^key_.+/i',
        '/^live_values_.+/i',
        '/^test_values_.+/i',
        '/^additional_data_.+/i',
        '/^image_full_url_.+/i',
        '/^thumbnail_full_url_.+/i',
        '/^productallstatus_.+/i',
        '/^minimum_order_qty_.+/i',
        '/^status_.+/i',
        '/^check_.+/i',
        '/^slug_.+/i',
        '/^logo_.+/i',
        '/^profile_image_.+/i',
        '/^xmlns_.+/i',
    ];
    foreach ($keyPatterns as $pattern) {
        if (preg_match($pattern, $key)) {
            return true;
        }
    }

    $valueMarkers = [
        '{{', '}}', '{!!', '@if', '@foreach', '@php', '@endphp',
        'route(', 'asset(', 'theme_asset(', 'dynamicAsset(', 'getWebConfig(',
        'session(', 'auth(', 'Auth::', 'request(', 'json_decode(', 'count(',
        'isset(', 'empty(', 'env(', 'base64_decode(', '\App\\', '$',
        'class="', 'href="', 'src="', 'alt="', 'style="', 'id="', 'name="',
        'data-', '</', '<span', '<div', '<a ', '<img', 'onerror=',
        'target="_blank"', 'method="post"', 'hidden>', 'readonly>',
        'autocomplete="off" required',
    ];
    foreach ($valueMarkers as $marker) {
        if (str_contains($value, $marker)) {
            return true;
        }
    }

    return false;
}
