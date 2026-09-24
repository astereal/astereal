<?php

declare(strict_types=1);

namespace Astereal\Web\Models;

use PDO;

class Team
{
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM teams ORDER BY name ASC");
        return $stmt->fetchAll() ?: [];
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM teams WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): ?int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO teams (name, description) VALUES (:name, :description)");
        $stmt->execute([
            ':name'        => $data['name'],
            ':description' => $data['description'] ?? null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function getMembers(int $teamId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.name, u.email, u.role, ut.is_primary
            FROM users u
            INNER JOIN user_teams ut ON ut.user_id = u.id
            WHERE ut.team_id = :team_id
            ORDER BY u.name ASC
        ");
        $stmt->execute([':team_id' => $teamId]);
        return $stmt->fetchAll() ?: [];
    }

    public static function getUserTeams(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT t.*, ut.is_primary
            FROM teams t
            INNER JOIN user_teams ut ON ut.team_id = t.id
            WHERE ut.user_id = :user_id
            ORDER BY ut.is_primary DESC, t.name ASC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll() ?: [];
    }

    public static function addUser(int $userId, int $teamId, bool $isPrimary = false): void
    {
        $pdo = Database::getConnection();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $sql = $driver === 'sqlite'
            ? "INSERT OR REPLACE INTO user_teams (user_id, team_id, is_primary) VALUES (:user_id, :team_id, :is_primary)"
            : "INSERT INTO user_teams (user_id, team_id, is_primary) VALUES (:user_id, :team_id, :is_primary) ON DUPLICATE KEY UPDATE is_primary = VALUES(is_primary)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id'    => $userId,
            ':team_id'    => $teamId,
            ':is_primary' => $isPrimary ? 1 : 0,
        ]);
    }

    public static function removeUser(int $userId, int $teamId): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM user_teams WHERE user_id = :user_id AND team_id = :team_id");
        $stmt->execute([':user_id' => $userId, ':team_id' => $teamId]);
    }
}
