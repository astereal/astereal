<?php

namespace Astereal\Web\Models;

use PDO;
use PDOException;

class Database
{
    protected static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = require dirname(__DIR__, 2) . '/config/database.php';
            $driver = $config['driver'] ?? 'mysql';

            try {
                if ($driver === 'sqlite') {
                    $dbFile = $config['sqlite']['database'];
                    $dir = dirname($dbFile);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0775, true);
                    }
                    self::$instance = new PDO("sqlite:{$dbFile}");
                } else {
                    $mysql = $config['mysql'];
                    $dsn = "mysql:host={$mysql['host']};port={$mysql['port']};dbname={$mysql['database']};charset={$mysql['charset']}";
                    self::$instance = new PDO($dsn, $mysql['username'], $mysql['password']);
                }

                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                // Initialize default tables if not existing
                self::ensureTables();

            } catch (PDOException $e) {
                // If MySQL is not running or credentials fail, fallback to SQLite for zero-downtime demo
                if ($driver !== 'sqlite' && !empty($config['sqlite']['database'])) {
                    $dbFile = $config['sqlite']['database'];
                    $dir = dirname($dbFile);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0775, true);
                    }
                    self::$instance = new PDO("sqlite:{$dbFile}");
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    self::ensureTables();
                } else {
                    throw $e;
                }
            }
        }

        return self::$instance;
    }

    protected static function ensureTables(): void
    {
        $pdo = self::$instance;

        // Callers table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS callers (
                id INTEGER PRIMARY KEY " . ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? 'AUTOINCREMENT' : 'AUTO_INCREMENT') . ",
                phone_number VARCHAR(50) NOT NULL UNIQUE,
                name VARCHAR(150) NOT NULL,
                is_vip TINYINT(1) DEFAULT 0,
                route_to VARCHAR(50) DEFAULT '100',
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Seed default VIP callers if empty
        $count = $pdo->query("SELECT COUNT(*) FROM callers")->fetchColumn();
        if ((int)$count === 0) {
            $stmt = $pdo->prepare("
                INSERT INTO callers (phone_number, name, is_vip, route_to, notes)
                VALUES (:phone, :name, :vip, :route, :notes)
            ");

            $stmt->execute([
                ':phone' => '100',
                ':name'  => 'Jerome Soriano',
                ':vip'   => 1,
                ':route' => '100',
                ':notes' => 'Lead Asterisk Developer',
            ]);

            $stmt->execute([
                ':phone' => '200',
                ':name'  => 'Tech Support Line',
                ':vip'   => 0,
                ':route' => '200',
                ':notes' => 'Internal Support Queue',
            ]);
        }

        // Users table for Admin Authentication
        $autoInc = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? 'AUTOINCREMENT' : 'AUTO_INCREMENT';
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY {$autoInc},
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                name VARCHAR(100) DEFAULT 'Admin',
                email VARCHAR(100),
                role VARCHAR(20) DEFAULT 'admin',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Seed default admin user if empty
        $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ((int)$userCount === 0) {
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, name, email, role)
                VALUES (:username, :password, :name, :email, :role)
            ");
            $defaultPasswordHash = password_hash('astereal2026', PASSWORD_BCRYPT);
            $stmt->execute([
                ':username' => 'admin',
                ':password' => $defaultPasswordHash,
                ':name'     => 'Astereal Administrator',
                ':email'    => 'admin@astereal.local',
                ':role'     => 'admin',
            ]);
        }
    }
}
