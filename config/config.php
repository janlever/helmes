<?php

$envFile = __DIR__ . '/../.env';

$altLocations = [
    __DIR__ . '/.env',
    __DIR__ . '/../../.env'
];

if (file_exists($envFile)) {
    loadEnv($envFile);
} else {
    foreach ($altLocations as $location) {
        if (file_exists($location)) {
            loadEnv($location);
            break;
        }
    }
}

function loadEnv($file) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!empty($name)) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

define('servername', getenv('servername') ?: 'localhost');
define('username', getenv('username') ?: 'root');
define('password', getenv('password') ?: '');
define('database', getenv('database') ?: 'user_form');
?>