<?php

declare(strict_types=1);

use Astereal\Web\Controllers\Web\AuthController;
use Astereal\Web\Controllers\Web\DashboardController;
use Astereal\Web\Controllers\Web\ProfileController;
use Astereal\Web\Middleware\GuestMiddleware;
use Astereal\Web\Middleware\WebAuthMiddleware;
use Astereal\Web\Support\Router;

/**
 * Astereal Web Interface Routes
 */

// Authentication routes (Guest only)
Router::get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
Router::post('/login', [AuthController::class, 'login'], [GuestMiddleware::class]);

// Logout route
Router::get('/logout', [AuthController::class, 'logout']);
Router::post('/logout', [AuthController::class, 'logout']);

// Mandatory Initial Password Setup (Requires Authentication)
Router::get('/password/change', [AuthController::class, 'showChangePassword'], [WebAuthMiddleware::class]);
Router::post('/password/change', [AuthController::class, 'updateInitialPassword'], [WebAuthMiddleware::class]);

// User Profile & Password Management (Requires Authentication)
Router::get('/profile', [ProfileController::class, 'show'], [WebAuthMiddleware::class]);
Router::post('/profile', [ProfileController::class, 'update'], [WebAuthMiddleware::class]);
Router::post('/profile/password', [ProfileController::class, 'changePassword'], [WebAuthMiddleware::class]);

// Protected Dashboard routes (Requires Authentication)
Router::get('/', [DashboardController::class, 'index'], [WebAuthMiddleware::class]);
Router::get('/dashboard', [DashboardController::class, 'index'], [WebAuthMiddleware::class]);
