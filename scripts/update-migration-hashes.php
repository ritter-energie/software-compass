<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$migrationDirectory = $root . '/database/migrations';
$manifestPath = $migrationDirectory . '/.migration-hashes.json';
$files = glob($migrationDirectory . '/*.php');

if ($files === false) {
    fwrite(STDERR, "Unable to read migration directory.\n");
    exit(1);
}

sort($files);

$hashes = [];
foreach ($files as $file) {
    $hashes[basename($file)] = hash_file('sha256', $file);
}

file_put_contents(
    $manifestPath,
    json_encode($hashes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL,
);

fwrite(STDOUT, sprintf("Updated %s with %d migration hashes.\n", $manifestPath, count($hashes)));

