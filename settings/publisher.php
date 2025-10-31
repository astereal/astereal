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
];
