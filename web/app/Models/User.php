<?php

declare(strict_types=1);

namespace Astereal\Web\Models;

use PDO;

class User
{
    public static function findByUsername(string $username): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => trim($username)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function updatePassword(int $userId, string $newPassword): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET password = :hash WHERE id = :id");
        return $stmt->execute([
            ':id'   => $userId,
            ':hash' => password_hash($newPassword, PASSWORD_BCRYPT),
        ]);
    }
}
