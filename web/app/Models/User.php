<?php

declare(strict_types=1);

namespace Astereal\Web\Models;

use PDO;

class User
{
    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, username, name, email, role, status, created_at FROM users ORDER BY id ASC");
        return $stmt->fetchAll() ?: [];
    }

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
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("UPDATE users SET password = :hash, must_change_password = 0, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $success = $stmt->execute([':id' => $userId, ':hash' => $hash]);
        } catch (\Throwable $e) {
            $stmt = $pdo->prepare("UPDATE users SET password = :hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $success = $stmt->execute([':id' => $userId, ':hash' => $hash]);
        }

        return $success;
    }

    public static function updateProfile(int $userId, string $name, ?string $email = null): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute([
            ':id'    => $userId,
            ':name'  => trim($name),
            ':email' => !empty($email) ? trim($email) : null,
        ]);
    }

    public static function mustChangePassword(int $userId): bool
    {
        $user = self::findById($userId);
        return !empty($user['must_change_password']);
    }

    public static function create(array $data): ?int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, name, email, role, status)
            VALUES (:username, :password, :name, :email, :role, :status)
        ");
        $stmt->execute([
            ':username' => $data['username'],
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':name'     => $data['name'] ?? 'User',
            ':email'    => $data['email'] ?? null,
            ':role'     => $data['role'] ?? 'agent',
            ':status'   => $data['status'] ?? 'active',
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Get all roles assigned to user
     */
    public static function getRoles(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT r.* FROM roles r
            INNER JOIN user_roles ur ON ur.role_id = r.id
            WHERE ur.user_id = :user_id
            ORDER BY r.name ASC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get slug array of all roles assigned to user
     */
    public static function getRoleSlugs(int $userId): array
    {
        $roles = self::getRoles($userId);
        return array_column($roles, 'slug');
    }

    /**
     * Get all permissions assigned to user through their roles
     */
    public static function getPermissions(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.* FROM permissions p
            INNER JOIN role_permissions rp ON rp.permission_id = p.id
            INNER JOIN user_roles ur ON ur.role_id = rp.role_id
            WHERE ur.user_id = :user_id
            ORDER BY p.category ASC, p.name ASC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get slug array of all permissions assigned to user
     */
    public static function getPermissionSlugs(int $userId): array
    {
        $perms = self::getPermissions($userId);
        return array_column($perms, 'slug');
    }

    /**
     * Get all teams assigned to user
     */
    public static function getTeams(int $userId): array
    {
        return Team::getUserTeams($userId);
    }

    /**
     * Check if user has specific role or one of given roles
     */
    public static function hasRole(int $userId, string|array $roles): bool
    {
        $userRoles = self::getRoleSlugs($userId);
        $checkRoles = is_array($roles) ? $roles : [$roles];

        foreach ($checkRoles as $r) {
            if (in_array($r, $userRoles, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has specific permission
     */
    public static function hasPermission(int $userId, string $permission): bool
    {
        $userRoles = self::getRoleSlugs($userId);
        // Superadmin bypass
        if (in_array('superadmin', $userRoles, true)) {
            return true;
        }

        $userPerms = self::getPermissionSlugs($userId);
        return in_array($permission, $userPerms, true);
    }

    /**
     * Assign a single role to user
     */
    public static function assignRole(int $userId, int|string $roleIdOrSlug): void
    {
        $pdo = Database::getConnection();
        $roleId = is_int($roleIdOrSlug)
            ? $roleIdOrSlug
            : (int)($pdo->query("SELECT id FROM roles WHERE slug = " . $pdo->quote($roleIdOrSlug) . " LIMIT 1")->fetchColumn() ?: 0);

        if ($roleId > 0) {
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            $sql = $driver === 'sqlite'
                ? "INSERT OR IGNORE INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)"
                : "INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user_id' => $userId, ':role_id' => $roleId]);
        }
    }

    /**
     * Sync user roles
     */
    public static function syncRoles(int $userId, array $roleIds): void
    {
        $pdo = Database::getConnection();
        $del = $pdo->prepare("DELETE FROM user_roles WHERE user_id = :user_id");
        $del->execute([':user_id' => $userId]);

        if (!empty($roleIds)) {
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            $sql = $driver === 'sqlite'
                ? "INSERT OR IGNORE INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)"
                : "INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";

            $stmt = $pdo->prepare($sql);
            foreach ($roleIds as $rid) {
                $stmt->execute([':user_id' => $userId, ':role_id' => (int)$rid]);
            }
        }
    }
}
