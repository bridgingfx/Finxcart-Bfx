<?php
declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

$langFiles = [
    'resources/lang/en/new-messages.php',
];

$targetFiles = [];
foreach (['resources/themes'] as $baseDir) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if (!$file->isFile()) continue;
        $path = str_replace('\\', '/', $file->getPathname());
        if (!str_ends_with($path, '.blade.php')) continue;
        $targetFiles[] = $path;
    }
}
sort($targetFiles);

$existingEn = include $langFiles[0];
if (!is_array($existingEn)) {
    fwrite(STDERR, "English language file did not return an array.\n");
    exit(1);
}

$valueToKey = [];
$keyToValue = $existingEn;
foreach ($existingEn as $key => $value) {
    if (is_string($value) && !isset($valueToKey[$value])) {
        $valueToKey[$value] = $key;
    }
}

$newEntries   = [];
$updatedFiles = [];
$replacements = 0;

foreach ($targetFiles as $file) {
    $original = file_get_contents($file);
    $content  = $original;

    // Skip lines that contain Blade/PHP code
    $lines = explode("\n", $content);
    $result = [];

    foreach ($lines as $line) {
        // Never touch lines with these — they are code not text
        if (
            str_contains($line, '{{')           ||
            str_contains($line, '{!!')           ||
            str_contains($line, '@')             ||
            str_contains($line, '$')             ||
            str_contains($line, '->')            ||
            str_contains($line, 'route(')        ||
            str_contains($line, 'asset(')        ||
            str_contains($line, 'translate(')    ||
            str_contains($line, '__PROTECTED')   ||
            str_contains($line, '<?php')         ||
            str_contains($line, 'function(')
        ) {
            $result[] = $line;
            continue;
        }

        // PASS 1 - plain text between tags: >Some Text
        $line = preg_replace_callback(
            '/(?<=>)(\s*)([A-Za-z][^<>{}\n\r@$]{2,})(\s*)(?=<)/',
            function (array $m) use (&$valueToKey, &$keyToValue, &$newEntries, &$replacements) {
                $leading  = $m[1];
                $raw      = $m[2];
                $trailing = $m[3];
                $trimmed  = trim($raw);

                if (mb_strlen($trimmed) < 3) return $m[0];
                if (!preg_match('/[A-Za-z]{2,}/', $trimmed)) return $m[0];
                if (preg_match('/^[\d\s\.\,\-\:\!\?\(\)\/\%]+$/', $trimmed)) return $m[0];

                $key = resolveKey($trimmed, $valueToKey, $keyToValue, $newEntries);
                $replacements++;
                return $leading . "{{ translate('{$key}') }}" . $trailing;
            },
            $line
        );

        // PASS 2 - placeholder="Plain Text"
        $line = preg_replace_callback(
            '/\bplaceholder=(["\'])([A-Za-z][^"\'{}$@]{2,})\1/',
            function (array $m) use (&$valueToKey, &$keyToValue, &$newEntries, &$replacements) {
                $quote  = $m[1];
                $rawVal = trim($m[2]);
                if (mb_strlen($rawVal) < 3) return $m[0];
                $key = resolveKey($rawVal, $valueToKey, $keyToValue, $newEntries);
                $replacements++;
                return "placeholder={$quote}{{ translate('{$key}') }}{$quote}";
            },
            $line
        );

        $result[] = $line;
    }

    $content = implode("\n", $result);

    if ($content !== $original) {
        file_put_contents($file, $content);
        $updatedFiles[] = $file;
        echo "updated: " . $file . "\n";
    }
}

foreach ($langFiles as $langFile) {
    appendLangEntries($langFile, $newEntries);
}

echo "\n";
echo "Files updated : " . count($updatedFiles) . "\n";
echo "New keys      : " . count($newEntries) . "\n";
echo "Replacements  : " . $replacements . "\n";
echo "\nDone! Now run: php artisan view:clear\n";

function resolveKey(
    string $value,
    array &$valueToKey,
    array &$keyToValue,
    array &$newEntries
): string {
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = preg_replace('/\s+/', ' ', trim($value));

    if (isset($valueToKey[$value])) {
        return $valueToKey[$value];
    }

    $base = preg_replace('/[^a-z0-9]+/i', '_', strtolower($value));
    $base = trim($base, '_');
    if ($base === '') $base = 'text';
    if (preg_match('/^\d/', $base)) $base = 'text_' . $base;
    if (strlen($base) > 60) $base = rtrim(substr($base, 0, 60), '_');

    $key    = $base;
    $suffix = 2;
    while (isset($keyToValue[$key]) && $keyToValue[$key] !== $value) {
        $key = $base . '_' . $suffix++;
    }

    $valueToKey[$value] = $key;
    $keyToValue[$key]   = $value;
    $newEntries[$key]   = $value;

    return $key;
}

function appendLangEntries(string $file, array $entries): void
{
    if (!$entries) return;

    $content  = file_get_contents($file);
    $existing = include $file;

    if (!is_array($existing)) {
        throw new RuntimeException("Language file {$file} did not return an array");
    }

    $toAppend = [];
    foreach ($entries as $key => $value) {
        if (array_key_exists($key, $existing)) continue;
        $escapedKey   = addslashes($key);
        $escapedValue = addslashes($value);
        $toAppend[]   = "\t\"{$escapedKey}\" => \"{$escapedValue}\",";
    }

    if (!$toAppend) return;

    $insertion = implode(PHP_EOL, $toAppend) . PHP_EOL;
    $updated   = preg_replace('/\];\s*$/', $insertion . '];' . PHP_EOL, $content);
    file_put_contents($file, $updated);
}