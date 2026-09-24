<?php

declare(strict_types=1);

/**
 * Astereal Native Web & REST API Front Controller
 * Zero external framework dependencies.
 */

// 0. Locate and boot Composer autoloader & .env environment
$autoloadCandidates = [
    dirname(__DIR__, 2) . '/bootstrap/autoload.php', // In-place repo (web/public -> root)
    dirname(__DIR__) . '/bootstrap/autoload.php',    // Published root (/var/www/html/app/public -> app)
    dirname(__DIR__, 2) . '/vendor/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php',
];

foreach ($autoloadCandidates as $autoloadCandidate) {
    if (file_exists($autoloadCandidate)) {
        require_once $autoloadCandidate;
        break;
    }
}

// Ensure .env is loaded if bootstrap/autoload.php wasn't present in standalone published web directory
if (!getenv('APP_KEY') && !getenv('DB_DRIVER')) {
    $envCandidates = [
        dirname(__DIR__) . '/.env',
        dirname(__DIR__, 2) . '/.env',
        '/etc/astereal/.env',
    ];
    foreach ($envCandidates as $envCandidate) {
        if (file_exists($envCandidate)) {
            $lines = file($envCandidate, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (str_contains($line, '=')) {
                    [$key, $val] = explode('=', $line, 2);
                    $key = trim($key);
                    $val = trim($val, " \t\n\r\0\x0B\"'");
                    if (!getenv($key)) {
                        putenv("{$key}={$val}");
                        $_ENV[$key] = $val;
                        $_SERVER[$key] = $val;
                    }
                }
            }
            break;
        }
    }
}

// 1. Register PSR-4 autoloader for Astereal\Web namespace
spl_autoload_register(function (string $class) {
    $prefix = 'Astereal\\Web\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use Astereal\Web\Support\Request;
use Astereal\Web\Support\Router;

// 2. Load web and API routes
require_once dirname(__DIR__) . '/routes/web.php';
require_once dirname(__DIR__) . '/routes/api.php';

// 3. Capture incoming request and dispatch
$request = Request::capture();
Router::dispatch($request);
