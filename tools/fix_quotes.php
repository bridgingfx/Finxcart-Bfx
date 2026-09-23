<?php
declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

$files = [
    'resources/lang/en/new-messages.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "Not found: $file\n";
        continue;
    }

    $lines = file($file);
    $fixed = 0;
    $result = [];

    foreach ($lines as $line) {
        // Match translation lines: "key" => "value",
        if (preg_match('/^(\s*"(?:[^"\\\\]|\\\\.)*"\s*=>\s*)"(.+)"(,\s*)$/', $line, $m)) {
            $prefix  = $m[1];
            $value   = $m[2];
            $suffix  = $m[3];

            // Check if value has unescaped double quotes inside
            if (preg_match('/(?<!\\\\)"/', $value)) {
                // Replace inner double quotes with single quotes
                $newValue = preg_replace('/(?<!\\\\)"/', "'", $value);
                $line = $prefix . '"' . $newValue . '"' . $suffix;
                $fixed++;
                echo "Fixed: " . trim($line) . "\n";
            }
        }
        $result[] = $line;
    }

    file_put_contents($file, implode('', $result));
    echo "\nTotal fixed: $fixed lines in $file\n";
}

echo "\nChecking syntax...\n";
foreach ($files as $file) {
    $result = shell_exec('php -l ' . escapeshellarg($file));
    echo $file . ': ' . trim($result) . "\n";
}