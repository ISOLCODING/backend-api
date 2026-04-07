<?php

// Ensure all storage directories exist in /tmp
$storageDirectories = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/bootstrap/cache',
    '/tmp/storage/logs',
];

foreach ($storageDirectories as $directory) {
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}

// Forward all requests to the public/index.php file.
require __DIR__ . '/../public/index.php';
