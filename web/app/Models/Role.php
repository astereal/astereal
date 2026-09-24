<?php

declare(strict_types=1);

namespace Astereal\Web\Models;

use PDO;

class Role
{
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM roles ORDER BY id ASC");
        return $stmt->fetchAll() ?: [];
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM roles WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getPermissions(int $roleId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT p.* FROM permissions p
            INNER JOIN role_permissions rp ON rp.permission_id = p.id
            WHERE rp.role_id = :role_id
            ORDER BY p.category ASC, p.name ASC
        ");
        $stmt->execute([':role_id' => $roleId]);
        return $stmt->fetchAll() ?: [];
    }

    public static function syncPermissions(int $roleId, array $permissionIds): void
    {
        $pdo = Database::getConnection();
        $del = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
        $del->execute([':role_id' => $roleId]);

        if (!empty($permissionIds)) {
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            $insertSql = $driver === 'sqlite'
                ? "INSERT OR IGNORE INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)"
                : "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)";

            $stmt = $pdo->prepare($insertSql);
            foreach ($permissionIds as $permId) {
                $stmt->execute([':role_id' => $roleId, ':permission_id' => (int)$permId]);
            }
        }
    }

    public static function create(array $data): ?int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO roles (name, slug, description)
            VALUES (:name, :slug, :description)
        ");
        $stmt->execute([
            ':name'        => $data['name'],
            ':slug'        => $data['slug'],
            ':description' => $data['description'] ?? null,
        ]);

        return (int)$pdo->lastInsertId();
    }
}
