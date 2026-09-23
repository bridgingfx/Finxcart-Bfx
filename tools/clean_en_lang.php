<?php

declare(strict_types=1);

$targets = $argv;
array_shift($targets);

if (!$targets) {
    $targets = ['en'];
}

foreach ($targets as $locale) {
    $file = __DIR__ . "/../resources/lang/{$locale}/new-messages.php";
    $lines = file($file);

    if ($lines === false) {
        fwrite(STDERR, "Failed to read {$file}\n");
        exit(1);
    }

    $output = [];
    $inCommentBlock = false;
    $removed = 0;

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '/*') {
            $inCommentBlock = true;
            $removed++;
            continue;
        }

        if ($inCommentBlock) {
            $removed++;
            if ($trimmed === '*/') {
                $inCommentBlock = false;
            }
            continue;
        }

        if (preg_match('/^\s*"((?:\\\\.|[^"\\\\])*)"\s*=>\s*"((?:\\\\.|[^"\\\\])*)",\s*$/', $line, $matches)) {
            $key = stripcslashes($matches[1]);
            $value = stripcslashes($matches[2]);

            if (shouldRemoveEntry($key, $value)) {
                $removed++;
                continue;
            }
        }

        $output[] = $line;
    }

    file_put_contents($file, implode('', $output));
    echo "{$locale}: removed {$removed} lines" . PHP_EOL;
}

function shouldRemoveEntry(string $key, string $value): bool
{
    if (str_starts_with($key, 'protected_block_')) {
        return true;
    }

    $codeMarkers = [
        '{{', '}}', '{!!', '@if', '@foreach', '@php', '@endphp',
        'route(', 'asset(', 'theme_asset(', 'dynamicAsset(', 'getWebConfig(',
        'session(', 'auth(', 'Auth::', 'request(', 'json_decode(', 'count(',
        'isset(', 'empty(', 'env(', 'base64_decode(', 'normalizeLanguageCode(',
        '\App\\', '$', 'class="', 'href="', 'src="', 'alt="', 'style="',
        'id="', 'name="', 'data-', '</', '<span', '<div', '<a ', '<img',
        'onerror=', 'target="_blank"', 'method="post"', 'hidden>', 'readonly>',
    ];

    foreach ($codeMarkers as $marker) {
        if (str_contains($value, $marker)) {
            return true;
        }
    }

    $junkKeyPatterns = [
        '/^(?:src|alt|href|title|link|slug|logo|profile_image|button_disabled)$/',
        '/^(?:id|id_\d+|data_.+|class_.+|style_.+|text_.+|count_.+|isset_.+|empty_.+|auth_.+|request_.+|session_.+|env_.+|base64_.+|json_.+|cart.+|product.+|order.+|reviewdata.+|minimum_.+|free_delivery_.+|shipping.+|payment_.+|invoice.+|blog.+|sociallogin.+|rememberid.+|web_config.+|flashdeal.+|decimalpointsettings.+|getlocale_.+|get_direction.+)$/',
    ];

    foreach ($junkKeyPatterns as $pattern) {
        if (preg_match($pattern, $key)) {
            return true;
        }
    }

    return false;
}
