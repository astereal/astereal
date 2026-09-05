<?php

namespace Astereal\Web\Models;

use PDO;

class Caller
{
    public static function findByPhone(string $phone): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM callers WHERE phone_number = :phone LIMIT 1");
        $stmt->execute([':phone' => trim($phone)]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public static function all(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM callers ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $data): bool
    {
        return self::createOrUpdate($data);
    }

    public static function createOrUpdate(array $data): bool
    {
        $pdo = Database::getConnection();
        $phone = trim($data['phone_number'] ?? $data['ani'] ?? '');
        $existing = self::findByPhone($phone);

        if ($existing) {
            $stmt = $pdo->prepare("
                UPDATE callers 
                SET name = :name, is_vip = :vip, route_to = :route, notes = :notes
                WHERE phone_number = :phone
            ");
            return $stmt->execute([
                ':phone' => $phone,
                ':name'  => trim($data['name'] ?? ''),
                ':vip'   => !empty($data['is_vip']) ? 1 : 0,
                ':route' => trim($data['route_to'] ?? '100'),
                ':notes' => trim($data['notes'] ?? ''),
            ]);
        }

        $stmt = $pdo->prepare("
            INSERT INTO callers (phone_number, name, is_vip, route_to, notes)
            VALUES (:phone, :name, :vip, :route, :notes)
        ");

        return $stmt->execute([
            ':phone' => $phone,
            ':name'  => trim($data['name'] ?? ''),
            ':vip'   => !empty($data['is_vip']) ? 1 : 0,
            ':route' => trim($data['route_to'] ?? '100'),
            ':notes' => trim($data['notes'] ?? ''),
        ]);
    }
}
