<?php

declare(strict_types=1);

namespace Astereal\Web\Models;

use Astereal\Web\Support\Migrator;
use PDO;
use PDOException;
use Throwable;

class Database
{
    protected static ?PDO $instance = null;
    protected static bool $migrationsRan = false;

    public static function getConnection(bool $autoMigrate = true): PDO
    {
        if (self::$instance === null) {
            $config = require dirname(__DIR__, 2) . '/config/database.php';
            $driver = $config['driver'] ?? 'sqlite';

            try {
                if ($driver === 'sqlite') {
                    $dbFile = $config['sqlite']['database'];
                    $dir = dirname($dbFile);
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0777, true);
                    }
                    self::$instance = new PDO("sqlite:{$dbFile}");
                    if (file_exists($dbFile)) {
                        @chmod($dbFile, 0666);
                        @chmod($dir, 0777);
                    }
                } else {
                    $mysql = $config['mysql'];
                    $dsn = "mysql:host={$mysql['host']};port={$mysql['port']};dbname={$mysql['database']};charset={$mysql['charset']}";
                    self::$instance = new PDO($dsn, $mysql['username'], $mysql['password']);
                }

                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                // If MySQL is not running or credentials fail, fallback to SQLite for zero-downtime demo
                if ($driver !== 'sqlite' && !empty($config['sqlite']['database'])) {
                    $dbFile = $config['sqlite']['database'];
                    $dir = dirname($dbFile);
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0777, true);
                    }
                    self::$instance = new PDO("sqlite:{$dbFile}");
                    if (file_exists($dbFile)) {
                        @chmod($dbFile, 0666);
                        @chmod($dir, 0777);
                    }
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                } else {
                    throw $e;
                }
            }
        }

        // Run pending migrations once on first connection
        if ($autoMigrate && !self::$migrationsRan && self::$instance !== null) {
            self::$migrationsRan = true;
            try {
                $migrator = new Migrator(self::$instance);
                $migrator->run();
            } catch (Throwable $e) {
                error_log("Migrator warning: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    public static function getDriver(): string
    {
        return self::$instance ? (string)self::$instance->getAttribute(PDO::ATTR_DRIVER_NAME) : 'none';
    }

    public static function getDatabaseName(): string
    {
        if (!self::$instance) {
            return 'none';
        }
        $driver = self::$instance->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            return 'sqlite';
        }
        try {
            return (string)self::$instance->query('SELECT DATABASE()')->fetchColumn();
        } catch (Throwable $e) {
            return 'unknown';
        }
    }
}
