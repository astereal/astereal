<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $autoInc = $driver === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';

        // 1. Callers table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS callers (
                id {$autoInc},
                phone_number VARCHAR(50) NOT NULL UNIQUE,
                name VARCHAR(150) NOT NULL,
                is_vip TINYINT(1) DEFAULT 0,
                route_to VARCHAR(50) DEFAULT '100',
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Seed default callers if empty
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

        // 2. Users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id {$autoInc},
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                name VARCHAR(100) DEFAULT 'Admin',
                email VARCHAR(100),
                role VARCHAR(20) DEFAULT 'admin',
                status VARCHAR(20) DEFAULT 'active',
                must_change_password TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Seed default admin user if empty
        $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ((int)$userCount === 0) {
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, name, email, role, status, must_change_password)
                VALUES (:username, :password, :name, :email, :role, 'active', 0)
            ");
            $defaultPasswordHash = password_hash('astereal2026', PASSWORD_BCRYPT);
            $stmt->execute([
                ':username' => 'admin',
                ':password' => $defaultPasswordHash,
                ':name'     => 'Astereal Administrator',
                ':email'    => 'admin@astereal.local',
                ':role'     => 'superadmin',
            ]);
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS callers");
        $pdo->exec("DROP TABLE IF EXISTS users");
    }
};
