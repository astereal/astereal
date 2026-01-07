<?php

/**
 * Publisher Configuration File
 *
 * This file contains configuration settings specific to the Publisher module.
 * It defines various parameters that control how application files are published
 * to their respective Asterisk directories.
 *
 * @package Astereal
 * @category Configuration
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Publisher Paths
    |--------------------------------------------------------------------------
    |
    | These define where each set of Asterisk-related files will be copied to.
    | The keys correspond to folders inside your /app directory, and the values
    | are the destination directories on your Asterisk system.
    |
    */

    'agi'       => '/var/lib/asterisk/agi-bin/',
    'config'    => '/etc/astereal/',
    'dialplan'  => '/etc/asterisk/',
    'sounds'    => '/var/lib/asterisk/sounds/',

    /*
    |--------------------------------------------------------------------------
    | Asterisk Reload Commands
    |--------------------------------------------------------------------------
    |
    | These commands are executed AFTER publishing completes.
    | All commands listed here must be call-safe.
    |
    */
    'reload' => [
        'dialplan reload',
        'pjsip reload',
        'confbridge reload',
        'features reload',
    ],
];
