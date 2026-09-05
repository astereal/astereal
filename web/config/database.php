<?php

/**
 * Astereal Web & Telephony Database Configuration
 */

return [
    // Default driver: 'mysql' or 'sqlite'
    'driver' => getenv('DB_DRIVER') ?: 'mysql',

    'mysql' => [
        'host'     => getenv('DB_HOST') ?: '127.0.0.1',
        'port'     => getenv('DB_PORT') ?: 3306,
        'database' => getenv('DB_DATABASE') ?: 'astereal',
        'username' => getenv('DB_USERNAME') ?: 'asterisk',
        'password' => getenv('DB_PASSWORD') ?: 'asterisk',
        'charset'  => 'utf8mb4',
    ],

    'sqlite' => [
        // Useful for rapid local testing without setting up MariaDB
        'database' => __DIR__ . '/../database/astereal.sqlite',
    ],
];
