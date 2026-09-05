<?php

namespace Astereal\Web\Middleware;

use Astereal\Web\Support\Request;
use Astereal\Web\Support\Response;

class HmacAuthMiddleware
{
    public function handle(Request $request): void
    {
        $config = require dirname(__DIR__, 2) . '/config/security.php';

        // 1. Check IP Whitelist if enforced
        if (!empty($config['enforce_ip_whitelist'])) {
            $clientIp = $request->ip();
            $allowedIps = $config['allowed_ips'] ?? [];
            if (!in_array($clientIp, $allowedIps, true)) {
                Response::error("Forbidden: Client IP [{$clientIp}] not authorized", 403);
            }
        }

        // 2. Validate Timestamp Header (Freshness / Replay Attack Protection)
        $timestamp = $request->header('X_ASTEREAL_TIMESTAMP');
        if (empty($timestamp) || !is_numeric($timestamp)) {
            Response::error('Unauthorized: Missing or invalid X-Astereal-Timestamp header', 401);
        }

        $now = time();
        $window = (int)($config['timestamp_window'] ?? 30);
        if (abs($now - (int)$timestamp) > $window) {
            Response::error('Unauthorized: Request timestamp expired (replay attack protection)', 401);
        }

        // 3. Validate HMAC-SHA256 Signature Header
        $providedSignature = $request->header('X_ASTEREAL_SIGNATURE');
        if (empty($providedSignature)) {
            Response::error('Unauthorized: Missing X-Astereal-Signature header', 401);
        }

        $apiSecret = $config['api_secret'] ?? '';
        if (empty($apiSecret)) {
            Response::error('Server error: API secret not configured', 500);
        }

        // Signature payload: Timestamp + HTTP Method + Request Path + Raw Request Body
        $method  = $request->method();
        $path    = $request->path();
        $rawBody = $request->rawBody();

        $payloadToSign     = "{$timestamp}:{$method}:{$path}:{$rawBody}";
        $expectedSignature = hash_hmac('sha256', $payloadToSign, $apiSecret);

        // Constant-time string comparison against timing attacks
        if (!hash_equals($expectedSignature, $providedSignature)) {
            Response::error('Unauthorized: Invalid cryptographic signature', 401);
        }
    }
}
