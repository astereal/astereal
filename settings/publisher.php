<?php

/**
 * Publisher Configuration File
 *
 * This file contains configuration settings specific to the Publisher module.
 * It defines where application files are published to Asterisk directories,
 * and which Asterisk modules should be reloaded after publishing.
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
    'paths' => [
        'agi'       => '/var/lib/asterisk/agi-bin/',
        'config'    => '/etc/astereal/',
        'dialplan'  => '/etc/asterisk/',
        'sip'       => '/etc/asterisk/',
        'sounds'    => '/var/lib/asterisk/sounds/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Post-Publish Asterisk Module Reloads
    |--------------------------------------------------------------------------
    |
    | Configure which Asterisk modules to reload automatically after publishing.
    | - 'enabled': whether this module reloads on publish (true/false)
    | - 'command': the Asterisk CLI command executed via `asterisk -rx "<command>"`
    | - 'label': friendly display name
    |
    | If Asterisk is stopped, 'auto_start_asterisk' will automatically attempt
    | to start Asterisk so that reloads can proceed.
    |
    */
    'auto_start_asterisk' => true,

    'reloads' => [
        'dialplan' => [
            'enabled' => true,
            'command' => 'dialplan reload',
            'label'   => 'Dialplan',
        ],
        'pjsip' => [
            'enabled' => true,
            'command' => 'pjsip reload',
            'label'   => 'PJSIP',
        ],
        /**
         * Other modules to reload
         */
        'logger' => [
            'enabled' => false,
            'command' => 'logger reload',
            'label'   => 'Asterisk Logger',
        ],
    ],
];
