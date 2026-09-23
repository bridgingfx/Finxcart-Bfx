<?php

declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

$langFiles = [
    'resources/lang/en/new-messages.php',
    'resources/lang/es/new-messages.php',
    'resources/lang/ar/new-messages.php',
    'resources/lang/sa/new-messages.php',
    'resources/lang/bd/new-messages.php',
    'resources/lang/tr/new-messages.php',
];

$targetFiles = [];
foreach (['resources/themes', 'resources/views'] as $baseDir) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $path = str_replace('\\', '/', $file->getPathname());
        if (!str_ends_with($path, '.blade.php')) {
            continue;
        }
        if (str_starts_with($path, 'resources/views/vendor/')) {
            continue;
        }
        $targetFiles[] = $path;
    }
}

sort($targetFiles);

$existingEn = include $langFiles[0];
if (!is_array($existingEn)) {
    fwrite(STDERR, "English language file did not return an array.\n");
    exit(1);
}

$keyToValue = $existingEn;
$valueToKey = [];
foreach ($existingEn as $key => $value) {
    if (is_string($value) && !isset($valueToKey[$value])) {
        $valueToKey[$value] = $key;
    }
}

$newEntries = [];
$updatedFiles = [];
$replacements = 0;

foreach ($targetFiles as $file) {
    $original = file_get_contents($file);
    if ($original === false) {
        fwrite(STDERR, "Failed to read {$file}\n");
        exit(1);
    }

    $content = $original;
    $protected = [];
    $protectIndex = 0;

    $protect = static function (string $text) use (&$protected, &$protectIndex): string {
        $token = "__PROTECTED_BLOCK_{$protectIndex}__";
        $protected[$token] = $text;
        $protectIndex++;
        return $token;
    };

    $content = preg_replace_callback('/\{\{--.*?--\}\}/s', fn($m) => $protect($m[0]), $content);
    $content = preg_replace_callback('/<!--.*?-->/s', fn($m) => $protect($m[0]), $content);
    $content = preg_replace_callback('/<script\b[^>]*>.*?<\/script>/is', fn($m) => $protect($m[0]), $content);
    $content = preg_replace_callback('/<style\b[^>]*>.*?<\/style>/is', fn($m) => $protect($m[0]), $content);
    $content = preg_replace_callback('/@php\b.*?@endphp/s', fn($m) => $protect($m[0]), $content);
    $content = preg_replace_callback('/<\?(?:php|=).*?\?>/s', fn($m) => $protect($m[0]), $content);

    $segments = preg_split('/(<[^>]+>)/s', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ($segments === false) {
        fwrite(STDERR, "Failed to split {$file}\n");
        exit(1);
    }

    foreach ($segments as $index => $segment) {
        if ($segment === '' || isset($protected[$segment])) {
            continue;
        }

        if (str_starts_with($segment, '<')) {
            $segments[$index] = processTagSegment($segment, $file, $valueToKey, $keyToValue, $newEntries, $replacements);
            continue;
        }

        $segments[$index] = processTextSegment($segment, $file, $valueToKey, $keyToValue, $newEntries, $replacements);
    }

    $content = implode('', $segments);
    if ($protected) {
        $content = strtr($content, $protected);
    }

    if ($content !== $original) {
        file_put_contents($file, $content);
        $updatedFiles[] = $file;
    }
}

foreach ($langFiles as $langFile) {
    appendLangEntries($langFile, $newEntries);
}

echo json_encode([
    'updated_files' => count($updatedFiles),
    'new_keys' => count($newEntries),
    'replacements' => $replacements,
], JSON_PRETTY_PRINT) . PHP_EOL;

function processTagSegment(
    string $segment,
    string $file,
    array &$valueToKey,
    array &$keyToValue,
    array &$newEntries,
    int &$replacements
): string {
    $pattern = '/\b(placeholder|title|alt|value)\s*=\s*("([^"]*)"|\'([^\']*)\')/i';

    return preg_replace_callback($pattern, function (array $matches) use ($segment, $file, &$valueToKey, &$keyToValue, &$newEntries, &$replacements) {
        $attribute = strtolower($matches[1]);
        $full = $matches[0];
        $doubleQuoted = $matches[3] ?? '';
        $singleQuoted = $matches[4] ?? '';
        $rawValue = $doubleQuoted !== '' ? $doubleQuoted : $singleQuoted;
        $quote = $doubleQuoted !== '' ? '"' : "'";

        if (str_contains($rawValue, '{{') || str_contains($rawValue, '{!!') || str_contains($rawValue, '@lang(') || str_contains($rawValue, 'translate(') || str_contains($rawValue, '__(')) {
            return $full;
        }

        if ($attribute === 'value') {
            if (!preg_match('/^<input\b/i', $segment) && !preg_match('/^<button\b/i', $segment)) {
                return $full;
            }

            if (preg_match('/\btype\s*=\s*("([^"]*)"|\'([^\']*)\')/i', $segment, $typeMatch)) {
                $type = strtolower($typeMatch[2] !== '' ? $typeMatch[2] : $typeMatch[3]);
                if (!in_array($type, ['button', 'submit', 'reset'], true)) {
                    return $full;
                }
            } elseif (!preg_match('/^<button\b/i', $segment)) {
                return $full;
            }
        }

        $trimmed = normalizeText($rawValue);
        if (!shouldTranslate($trimmed)) {
            return $full;
        }

        $key = resolveKey($trimmed, $file, $valueToKey, $keyToValue, $newEntries);
        $replacements++;

        return sprintf('%s=%s{{ translate(\'%s\') }}%s', $matches[1], $quote, $key, $quote);
    }, $segment) ?? $segment;
}

function processTextSegment(
    string $segment,
    string $file,
    array &$valueToKey,
    array &$keyToValue,
    array &$newEntries,
    int &$replacements
): string {
    if (trim($segment) === '') {
        return $segment;
    }

    $parts = preg_split('/(\{\{.*?\}\}|\{!!.*?!!\}|@[A-Za-z_][A-Za-z0-9_]*(?:\([^()\r\n]*\))?)/s', $segment, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ($parts === false) {
        return $segment;
    }

    foreach ($parts as $i => $part) {
        if ($part === '' || preg_match('/^(\{\{.*\}\}|\{!!.*!!\}|@[A-Za-z_][A-Za-z0-9_]*)/s', $part)) {
            continue;
        }

        $lines = preg_split("/(\r\n|\n|\r)/", $part, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($lines === false) {
            continue;
        }

        foreach ($lines as $j => $line) {
            if ($line === "\r\n" || $line === "\n" || $line === "\r") {
                continue;
            }

            if (!preg_match('/^(\s*)(.*?)(\s*)$/s', $line, $matches)) {
                continue;
            }

            $leading = $matches[1];
            $core = $matches[2];
            $trailing = $matches[3];
            $trimmed = normalizeText($core);

            if (!shouldTranslate($trimmed)) {
                continue;
            }

            $key = resolveKey($trimmed, $file, $valueToKey, $keyToValue, $newEntries);
            $lines[$j] = $leading . "{{ translate('{$key}') }}" . $trailing;
            $replacements++;
        }

        $parts[$i] = implode('', $lines);
    }

    return implode('', $parts);
}

function normalizeText(string $text): string
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);
    return $text;
}

function shouldTranslate(string $text): bool
{
    if ($text === '') {
        return false;
    }
    if (str_contains($text, 'translate(') || str_contains($text, '@lang(') || preg_match('/__\s*\(/', $text)) {
        return false;
    }
    if (preg_match('/^(?:&nbsp;|&middot;|&bull;|[-:,.!?()\/\\\\[\]#%+*|])+$/', $text)) {
        return false;
    }
    if (!preg_match('/[A-Za-z]/', $text)) {
        return false;
    }
    if (preg_match('/^[\$@]/', $text)) {
        return false;
    }
    if (preg_match('/^(?:if|else|elseif|endif|foreach|endforeach|forelse|empty|endforelse|endempty|for|endfor|while|endwhile|switch|case|break|default|endswitch|section|endsection|yield|extends|include|push|endpush|stack|csrf|method)\b/i', $text)) {
        return false;
    }
    return true;
}

function resolveKey(
    string $value,
    string $file,
    array &$valueToKey,
    array &$keyToValue,
    array &$newEntries
): string {
    if (isset($valueToKey[$value])) {
        return $valueToKey[$value];
    }

    $base = preg_replace('/[^a-z0-9]+/i', '_', strtolower($value)) ?? '';
    $base = trim($base, '_');
    if ($base === '') {
        $base = 'text';
    }
    if (preg_match('/^\d/', $base)) {
        $base = 'text_' . $base;
    }

    $key = $base;
    $suffix = 2;
    while (isset($keyToValue[$key]) && $keyToValue[$key] !== $value) {
        $key = $base . '_' . $suffix;
        $suffix++;
    }

    $valueToKey[$value] = $key;
    $keyToValue[$key] = $value;
    $newEntries[$key] = $value;

    return $key;
}

function appendLangEntries(string $file, array $entries): void
{
    if (!$entries) {
        return;
    }

    $content = file_get_contents($file);
    if ($content === false) {
        throw new RuntimeException("Failed to read {$file}");
    }

    $existing = include $file;
    if (!is_array($existing)) {
        throw new RuntimeException("Language file {$file} did not return an array");
    }

    $toAppend = [];
    foreach ($entries as $key => $value) {
        if (array_key_exists($key, $existing)) {
            continue;
        }
        $escapedKey = addslashes($key);
        $escapedValue = addslashes($value);
        $toAppend[] = "\t\"{$escapedKey}\" => \"{$escapedValue}\",";
    }

    if (!$toAppend) {
        return;
    }

    $insertion = implode(PHP_EOL, $toAppend) . PHP_EOL;
    $updated = preg_replace('/\];\s*$/', $insertion . '];' . PHP_EOL, $content);
    if ($updated === null) {
        throw new RuntimeException("Failed to update {$file}");
    }

    file_put_contents($file, $updated);
}
