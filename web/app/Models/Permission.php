<?php

declare(strict_types=1);

namespace Astereal\Web\Models;

use PDO;

class Permission
{
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM permissions ORDER BY category ASC, name ASC");
        return $stmt->fetchAll() ?: [];
    }

    public static function allGrouped(): array
    {
        $all = self::all();
        $grouped = [];
        foreach ($all as $perm) {
            $cat = $perm['category'] ?: 'general';
            $grouped[$cat][] = $perm;
        }
        return $grouped;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM permissions WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM permissions WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
