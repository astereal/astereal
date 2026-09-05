#!/usr/bin/env php
<?php

/**
 * Astereal Framework - Core AGI Logger
 *
 * Streamlined telephony logger focused on session identification and custom messages.
 *
 * Log format:
 *   [YYYY-MM-DD HH:MM:SS] CHANNEL={channel} ANI={ani} DNIS={dnis} MSG="{message}"
 *
 * Directory hierarchy:
 *   /var/log/astereal/core/{YYYYMM}/{YYYY-MM-DD}_astereal.log
 *
 * Dialplan usage:
 *   ; Default hangup logging:
 *   exten => h,1,NoOp("Hangup in context: ${CONTEXT}")
 *     same => n,AGI(aster_logger.php)
 *
 *   ; Custom message via argument:
 *   same => n,AGI(aster_logger.php, "Call ended in context: ${CONTEXT}")
 *   same => n,AGI(aster_logger.php, "Caller authenticated successfully")
 *
 *   ; Or set MSG channel variable before calling:
 *   same => n,Set(MSG=Database lookup completed)
 *   same => n,AGI(aster_logger.php)
 */

require_once __DIR__ . '/CAGI.php';

// 1. Base directory and Year-Month folder setup
$baseLogDir = '/var/log/astereal';

// Graceful fallback for Windows local development environments
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && !is_dir('C:\\var\\log')) {
    $baseLogDir = dirname(__DIR__, 2) . '/logs/astereal';
}

$category   = 'core';
$yearMonth  = date('Ym');
$targetDir  = rtrim($baseLogDir, '/\\') . '/' . $category . '/' . $yearMonth;

if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        error_log("[Astereal Logger] Failed to create directory: {$targetDir}");
    }
}

// 2. Daily log file (/var/log/astereal/core/YYYYMM/YYYY-MM-DD_astereal.log)
$currentDate = date('Y-m-d');
$logFile     = $targetDir . '/' . $currentDate . '_astereal.log';

// 3. Initialize AGI Client
$agi = new CAGI();

$now       = time();
$timestamp = date('Y-m-d H:i:s', $now);

// 4. Resolve CHANNEL (session identifier)
$channelVar = $agi->get_variable('CHANNEL');
$channel    = (!empty($channelVar['data'])) ? $channelVar['data'] : ($agi->request['agi_channel'] ?? 'UNKNOWN');

// 5. Resolve ANI (calling number)
$aniVar = $agi->get_variable('ANI');
if (!empty($aniVar['data'])) {
    $ani = $aniVar['data'];
} else {
    $cidVar = $agi->get_variable('CALLERID(num)');
    $ani    = (!empty($cidVar['data'])) ? $cidVar['data'] : ($agi->request['agi_callerid'] ?? 'UNKNOWN');
}

// 6. Resolve DNIS (called number)
$dnisVar = $agi->get_variable('DNIS');
if (!empty($dnisVar['data'])) {
    $dnis = $dnisVar['data'];
} else {
    $extenVar = $agi->get_variable('EXTEN');
    $dnis     = (!empty($extenVar['data'])) ? $extenVar['data'] : ($agi->request['agi_extension'] ?? 'UNKNOWN');
}

// 7. Resolve Context
$contextVar = $agi->get_variable('CONTEXT');
$context    = (!empty($contextVar['data'])) ? $contextVar['data'] : ($agi->request['agi_context'] ?? 'UNKNOWN');

// 8. Resolve MSG (from $argv[1], channel variable ${MSG}, or default)
$message = null;
if (!empty($argv[1])) {
    $message = trim($argv[1]);
} else {
    $msgVar = $agi->get_variable('MSG');
    if (!empty($msgVar['data'])) {
        $message = trim($msgVar['data']);
    }
}

if ($message === null || $message === '') {
    $message = "Call ended in context: {$context}";
}

// Clean message for single-line safety
$cleanMessage = str_replace(["\r", "\n"], ' ', $message);

// 9. Format structured log entry
$logLine = "[{$timestamp}] CHANNEL={$channel} ANI={$ani} DNIS={$dnis} MSG=\"{$cleanMessage}\"" . PHP_EOL;

// 10. Write to log file with locking
@file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);

// 11. Notify Asterisk CLI console
$agi->verbose("Astereal Logger: {$logLine}", 2);

exit(0);
