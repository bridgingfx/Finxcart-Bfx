<?php
declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

$foundKeys = [];

// Scan all blade files in themes
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('resources/themes', FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile()) continue;
    $path = str_replace('\\', '/', $file->getPathname());
    if (!str_ends_with($path, '.blade.php')) continue;

    $content = file_get_contents($path);

    // Find all translate('key') or translate("key")
    preg_match_all("/translate\(['\"](.+?)['\"]\)/", $content, $matches);

    foreach ($matches[1] as $key) {
        $key = trim($key);
        if (!isset($foundKeys[$key])) {
            $foundKeys[$key] = $key; // value same as key for now
        }
    }
}

// Load existing en keys so we don't overwrite existing translations
$existingEn = include 'resources/lang/en/new-messages.php';
if (!is_array($existingEn)) {
    $existingEn = [];
}

// Merge - existing translations take priority
$merged = $existingEn;
$newCount = 0;
foreach ($foundKeys as $key => $value) {
    if (!array_key_exists($key, $merged)) {
        $merged[$key] = $value;
        $newCount++;
    }
}

// Write back to lang/en/new-messages.php
$lines = ['<?php', '', 'return ['];
foreach ($merged as $key => $value) {
    $escapedKey   = addslashes((string)$key);
    $escapedValue = addslashes((string)$value);
    $lines[] = "\t\"{$escapedKey}\" => \"{$escapedValue}\",";
}
$lines[] = '];';
$lines[] = '';

file_put_contents('resources/lang/en/new-messages.php', implode(PHP_EOL, $lines));

echo "Total keys found : " . count($foundKeys) . PHP_EOL;
echo "New keys added   : " . $newCount . PHP_EOL;
echo "Total in file    : " . count($merged) . PHP_EOL;
echo PHP_EOL;
echo "Done! Check resources/lang/en/new-messages.php" . PHP_EOL;