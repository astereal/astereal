#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Astereal Framework - Secured AGI REST API Client
 *
 * Communicates with the Astereal native REST API backend using HMAC-SHA256
 * authentication, anti-replay timestamps, and sub-second fail-safes.
 *
 * Dialplan usage:
 *   ; Inbound caller lookup
 *   exten => _X.,1,NoOp("Inbound Call from ${CALLERID(num)}")
 *     same => n,AGI(aster_api.php, "caller/lookup")
 *     same => n,NoOp("AGI Status: ${AGI_API_STATUS}, Name: ${CALLER_NAME}, VIP: ${IS_VIP}, Route: ${ROUTE_TO}")
 *     same => n,GotoIf($["${IS_VIP}" = "1"]?vip-handler,s,1)
 *     same => n,GotoIf($["${ROUTE_TO}" != "default" & "${ROUTE_TO}" != ""]?internal,${ROUTE_TO},1)
 *     same => n,Dial(PJSIP/100,30,tT)
 *     same => n,Hangup()
 */

require_once __DIR__ . '/CAGI.php';

// 1. Initialize AGI Client
$agi = new CAGI();

// 2. Resolve Secret Key and API Endpoint
$secret = 'astereal_secret_key_change_in_production_2026';
$apiUrl = 'http://127.0.0.1/api/v1';

$secConfigPaths = [
    dirname(__DIR__, 2) . '/web/config/security.php',
    '/var/www/html/astereal/web/config/security.php',
];

foreach ($secConfigPaths as $secPath) {
    if (file_exists($secPath)) {
        $secConfig = require $secPath;
        if (!empty($secConfig['api_secret'])) {
            $secret = $secConfig['api_secret'];
        } elseif (!empty($secConfig['secret_key'])) {
            $secret = $secConfig['secret_key'];
        }
        if (!empty($secConfig['api_url'])) {
            $apiUrl = rtrim($secConfig['api_url'], '/');
        }
        break;
    }
}

// 3. Resolve Target Endpoint/Action
$action = !empty($argv[1]) ? trim($argv[1]) : 'caller/lookup';
$targetUrl = rtrim($apiUrl, '/') . '/' . ltrim($action, '/');

// 4. Resolve Asterisk Channel Variables
$channelVar = $agi->get_variable('CHANNEL');
$channel    = (!empty($channelVar['data'])) ? $channelVar['data'] : ($agi->request['agi_channel'] ?? 'UNKNOWN');

$aniVar = $agi->get_variable('ANI');
if (!empty($aniVar['data'])) {
    $ani = $aniVar['data'];
} else {
    $cidVar = $agi->get_variable('CALLERID(num)');
    $ani    = (!empty($cidVar['data'])) ? $cidVar['data'] : ($agi->request['agi_callerid'] ?? 'UNKNOWN');
}

$dnisVar = $agi->get_variable('DNIS');
if (!empty($dnisVar['data'])) {
    $dnis = $dnisVar['data'];
} else {
    $extenVar = $agi->get_variable('EXTEN');
    $dnis     = (!empty($extenVar['data'])) ? $extenVar['data'] : ($agi->request['agi_extension'] ?? 'UNKNOWN');
}

$uniqueid = $agi->request['agi_uniqueid'] ?? '';

// 5. Assemble Payload
$payloadArray = [
    'ani'       => $ani,
    'dnis'      => $dnis,
    'channel'   => $channel,
    'uniqueid'  => $uniqueid,
    'timestamp' => time(),
];

$payloadJson = json_encode($payloadArray, JSON_UNESCAPED_SLASHES);
if ($payloadJson === false) {
    $payloadJson = '{}';
}

// 6. Generate HMAC-SHA256 Signature (matches HmacAuthMiddleware format: {timestamp}:{method}:{path}:{rawBody})
$timestamp = time();
$parsedPath = parse_url($targetUrl, PHP_URL_PATH) ?: '/api/v1/' . ltrim($action, '/');
$payloadToSign = "{$timestamp}:POST:{$parsedPath}:{$payloadJson}";
$signature = hash_hmac('sha256', $payloadToSign, $secret);

// 7. Dispatch HTTP POST request via cURL (Fast 2.0s fail-safe timeout)
$ch = curl_init($targetUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payloadJson,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 1,
    CURLOPT_TIMEOUT        => 2,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'User-Agent: Astereal-AGI/1.0',
        'X-Astereal-Timestamp: ' . $timestamp,
        'X-Astereal-Signature: ' . $signature,
    ],
]);

$responseBody = curl_exec($ch);
$httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError    = curl_error($ch);
curl_close($ch);

// 8. Process Response and Populate Asterisk Channel Variables
if ($httpCode === 200 && is_string($responseBody)) {
    $response = json_decode($responseBody, true);

    if (is_array($response) && ($response['status'] ?? '') === 'success' && isset($response['data'])) {
        $callerData = $response['data'];

        $agi->set_variable('AGI_API_STATUS', 'SUCCESS');
        $agi->set_variable('CALLER_NAME', (string)($callerData['name'] ?? ''));
        $agi->set_variable('CALLER_COMPANY', (string)($callerData['company'] ?? ''));
        $agi->set_variable('IS_VIP', (string)($callerData['is_vip'] ?? '0'));
        $agi->set_variable('ROUTE_TO', (string)($callerData['route_to'] ?? 'default'));

        $agi->verbose(sprintf(
            '[Astereal API] Success: ANI=%s Name="%s" VIP=%s Route=%s',
            $ani,
            $callerData['name'] ?? 'Unknown',
            $callerData['is_vip'] ?? '0',
            $callerData['route_to'] ?? 'default'
        ), 2);

        exit(0);
    }
}

// Fail-safe default variables if API is unreachable or returns error
$agi->verbose(sprintf(
    '[Astereal API] Warning: Request failed (HTTP %d, %s). Applying fail-safe defaults.',
    $httpCode,
    $curlError ?: 'Invalid response'
), 1);

$agi->set_variable('AGI_API_STATUS', 'FAILED');
$agi->set_variable('CALLER_NAME', '');
$agi->set_variable('CALLER_COMPANY', '');
$agi->set_variable('IS_VIP', '0');
$agi->set_variable('ROUTE_TO', 'default');

exit(0);
