<?php

declare(strict_types=1);

use Astereal\Web\Controllers\Api\CallerController;
use Astereal\Web\Middleware\HmacAuthMiddleware;
use Astereal\Web\Support\Router;

/**
 * Astereal Secured REST API Routes
 * All endpoints are authenticated via HMAC-SHA256 signatures.
 */

// Caller lookup (used by AGI aster_api.php)
Router::post('/api/v1/caller/lookup', [CallerController::class, 'lookup'], [HmacAuthMiddleware::class]);

// Caller create/update (used by AGI or Admin)
Router::post('/api/v1/caller/save', [CallerController::class, 'store'], [HmacAuthMiddleware::class]);
