<?php
declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

$files = glob('resources/lang/*/new-messages.php');

foreach ($files as $file) {
    $lines = file($file);
    if ($lines === false) {
        echo "Skipping (cannot read): $file\n";
        continue;
    }

    $result = [];
    $fixed  = 0;

    foreach ($lines as $line) {
        // Match any quoted key => value line (single or double quoted)
        if (preg_match('/^\s*(["\'])(.+?)\1\s*=>\s*(["\'])(.*?)\3,\s*$/', $line, $m)) {
            $key   = $m[2];
            $value = $m[4];

            // Clean up any broken escaping first
            $key   = stripslashes($key);
            $value = stripslashes($value);

            // Now properly escape for single quotes
            $key   = str_replace(['\\', "'"], ['\\\\', "\\'"], $key);
            $value = str_replace(['\\', "'"], ['\\\\', "\\'"], $value);

            $result[] = "\t'{$key}' => '{$value}'," . PHP_EOL;
            $fixed++;
        } else {
            // Keep non-translation lines as is (<?php, return [, ];)
            $result[] = $line;
        }
    }

    file_put_contents($file, implode('', $result));
    echo "Converted: $file ($fixed lines)\n";
}

echo "\nChecking syntax...\n";
foreach (glob('resources/lang/*/new-messages.php') as $file) {
    $result = shell_exec('php -l ' . escapeshellarg($file));
    echo $file . ': ' . trim($result) . "\n";
}