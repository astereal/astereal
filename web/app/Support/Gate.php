<?php

declare(strict_types=1);

namespace Astereal\Web\Support;

use Astereal\Web\Models\User;

class Gate
{
    /**
     * Check if given user (or current authenticated user) is allowed an action
     */
    public static function allows(mixed $user, string $permission): bool
    {
        if ($user === null) {
            return Auth::can($permission);
        }

        $userId = is_array($user) ? (int)($user['id'] ?? 0) : (int)$user;
        if ($userId <= 0) {
            return false;
        }

        return User::hasPermission($userId, $permission);
    }

    /**
     * Check if given user is denied an action
     */
    public static function denies(mixed $user, string $permission): bool
    {
        return !self::allows($user, $permission);
    }

    /**
     * Check if given user has specific role or one of given roles
     */
    public static function hasRole(mixed $user, string|array $role): bool
    {
        if ($user === null) {
            return Auth::hasRole($role);
        }

        $userId = is_array($user) ? (int)($user['id'] ?? 0) : (int)$user;
        if ($userId <= 0) {
            return false;
        }

        return User::hasRole($userId, $role);
    }

    /**
     * Determine if queries for this user must be team-scoped
     * Returns true for Supervisor (only see their team), false for Admin/Superadmin (global)
     */
    public static function teamScoped(mixed $user = null): bool
    {
        if ($user === null) {
            return Auth::teamScoped();
        }

        // If user is superadmin or admin, they are never scoped
        if (self::hasRole($user, ['superadmin', 'admin'])) {
            return false;
        }

        // If user has supervisor role, they are team-scoped
        return self::hasRole($user, 'supervisor');
    }

    /**
     * Check permission for current session user
     */
    public static function can(string $permission): bool
    {
        return Auth::can($permission);
    }

    /**
     * Check if current session user is superadmin
     */
    public static function isSuperadmin(): bool
    {
        return Auth::isSuperadmin();
    }

    /**
     * Check if current session user is admin
     */
    public static function isAdmin(): bool
    {
        return Auth::isAdmin();
    }

    /**
     * Check if current session user is supervisor
     */
    public static function isSupervisor(): bool
    {
        return Auth::isSupervisor();
    }

    /**
     * Check if current session user is agent
     */
    public static function isAgent(): bool
    {
        return Auth::isAgent();
    }
}
