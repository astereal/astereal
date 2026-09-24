<?php

declare(strict_types=1);

namespace Astereal\Web\Support;

use PDO;
use RuntimeException;

class Migrator
{
    protected PDO $pdo;
    protected string $migrationsPath;

    public function __construct(PDO $pdo, ?string $migrationsPath = null)
    {
        $this->pdo = $pdo;
        $this->migrationsPath = $migrationsPath ?: dirname(__DIR__, 2) . '/database/migrations';

        if (!is_dir($this->migrationsPath)) {
            mkdir($this->migrationsPath, 0775, true);
        }
    }

    /**
     * Ensure the migrations tracking table exists
     */
    public function ensureMigrationTable(): void
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $autoInc = $driver === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id {$autoInc},
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT NOT NULL,
                applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    /**
     * Get all migrations that have already been executed
     */
    public function getRan(): array
    {
        $this->ensureMigrationTable();
        $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    /**
     * Get all migration files from directory in alphabetical order
     */
    public function getAllFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = scandir($this->migrationsPath);
        if ($files === false) {
            return [];
        }

        $migrations = array_filter($files, fn($f) => str_ends_with($f, '.php'));
        sort($migrations);
        return array_values($migrations);
    }

    /**
     * Get pending migration files
     */
    public function getPending(): array
    {
        $ran = $this->getRan();
        return array_values(array_diff($this->getAllFiles(), $ran));
    }

    /**
     * Get next batch number
     */
    public function getNextBatchNumber(): int
    {
        $this->ensureMigrationTable();
        $batch = $this->pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn();
        return ((int)$batch) + 1;
    }

    /**
     * Run all pending migrations
     */
    public function run(?callable $callback = null): array
    {
        $this->ensureMigrationTable();
        $pending = $this->getPending();

        if (empty($pending)) {
            return [];
        }

        $batch = $this->getNextBatchNumber();
        $executed = [];

        foreach ($pending as $file) {
            $filePath = $this->migrationsPath . '/' . $file;
            if ($callback) {
                $callback('migrating', $file);
            }

            $migration = require $filePath;
            if (!is_object($migration) || !method_exists($migration, 'up')) {
                throw new RuntimeException("Migration file {$file} must return an object with an up() method.");
            }

            $migration->up($this->pdo);

            $stmt = $this->pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)");
            $stmt->execute([
                ':migration' => $file,
                ':batch'     => $batch,
            ]);

            if ($callback) {
                $callback('migrated', $file);
            }

            $executed[] = $file;
        }

        return $executed;
    }

    /**
     * Rollback the latest batch of migrations
     */
    public function rollback(?callable $callback = null): array
    {
        $this->ensureMigrationTable();

        $lastBatch = $this->pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn();
        if (!$lastBatch) {
            return [];
        }

        $stmt = $this->pdo->prepare("SELECT migration FROM migrations WHERE batch = :batch ORDER BY id DESC");
        $stmt->execute([':batch' => $lastBatch]);
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $rolledBack = [];

        foreach ($migrations as $file) {
            $filePath = $this->migrationsPath . '/' . $file;
            if (!file_exists($filePath)) {
                continue;
            }

            if ($callback) {
                $callback('rolling_back', $file);
            }

            $migration = require $filePath;
            if (is_object($migration) && method_exists($migration, 'down')) {
                $migration->down($this->pdo);
            }

            $del = $this->pdo->prepare("DELETE FROM migrations WHERE migration = :migration");
            $del->execute([':migration' => $file]);

            if ($callback) {
                $callback('rolled_back', $file);
            }

            $rolledBack[] = $file;
        }

        return $rolledBack;
    }

    /**
     * Get detailed status of all migrations
     */
    public function status(): array
    {
        $this->ensureMigrationTable();

        $stmt = $this->pdo->query("SELECT migration, batch, applied_at FROM migrations");
        $ranRows = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ranRows[$row['migration']] = $row;
        }

        $allFiles = $this->getAllFiles();
        $status = [];

        foreach ($allFiles as $file) {
            $ran = isset($ranRows[$file]);
            $status[] = [
                'migration'  => $file,
                'ran'        => $ran,
                'batch'      => $ran ? (int)$ranRows[$file]['batch'] : null,
                'applied_at' => $ran ? $ranRows[$file]['applied_at'] : null,
            ];
        }

        return $status;
    }

    /**
     * Drop all tables from the database and re-run all migrations from scratch
     */
    public function fresh(?callable $callback = null): array
    {
        $this->dropAllTables($callback);
        return $this->run($callback);
    }

    /**
     * Drop all tables in the database across SQLite and MySQL
     */
    public function dropAllTables(?callable $callback = null): void
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($callback) {
            $callback('dropping_all', 'all tables');
        }

        if ($driver === 'sqlite') {
            $this->pdo->exec("PRAGMA foreign_keys = OFF");
            $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

            foreach ($tables as $table) {
                if ($callback) {
                    $callback('dropping_table', $table);
                }
                $this->pdo->exec("DROP TABLE IF EXISTS \"{$table}\"");
            }
            $this->pdo->exec("PRAGMA foreign_keys = ON");
        } else {
            // MySQL / MariaDB
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $stmt = $this->pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            $tables = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];

            foreach ($tables as $table) {
                if ($callback) {
                    $callback('dropping_table', $table);
                }
                $this->pdo->exec("DROP TABLE IF EXISTS `{$table}`");
            }
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        }

        if ($callback) {
            $callback('dropped_all', 'all tables');
        }
    }
}
