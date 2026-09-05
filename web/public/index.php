<?php

declare(strict_types=1);

/**
 * Astereal Native Web & REST API Front Controller
 * Zero external framework dependencies.
 */

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
