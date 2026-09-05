<?php

/**
 * Astereal Telephony API Security Configuration
 */

return [
    // Shared secret for HMAC-SHA256 signature verification between AGI and API
    'api_secret' => getenv('ASTEREAL_API_SECRET') ?: 'astereal_secret_key_change_in_production_2026',

    // Maximum allowed timestamp skew in seconds to prevent replay attacks
    'timestamp_window' => 30,

    // IP Whitelist: IP addresses authorized to hit telephony API routes
    'allowed_ips' => [
        '127.0.0.1',
        '::1',
        // Add private IP of remote Asterisk server if separate from Web:
        // '192.168.1.10',
    ],

    // Enforce IP whitelist (set to false to only rely on HMAC signature)
    'enforce_ip_whitelist' => false,
];
