<?php

declare(strict_types=1);

namespace Astereal\Web\Support;

use Astereal\Web\Models\User;

class Auth
{
    protected static ?string $lastError = null;

    public static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                ini_set('session.cookie_httponly', '1');
                ini_set('session.use_only_cookies', '1');
                ini_set('session.cookie_samesite', 'Lax');
                session_start();
            } else {
                @session_start();
            }
        }
    }

    public static function attempt(string $username, string $password): bool
    {
        self::initSession();
        self::$lastError = null;

        $user = User::findByUsername($username);
        if (!$user) {
            self::$lastError = 'Invalid username or password.';
            return false;
        }

        if (!User::verifyPassword($password, $user['password'])) {
            self::$lastError = 'Invalid username or password.';
            return false;
        }

        // Check if user is active
        if (isset($user['status']) && $user['status'] !== 'active') {
            self::$lastError = 'Your account has been deactivated. Please contact an administrator.';
            return false;
        }

        // Retrieve assigned roles
        $roles = User::getRoleSlugs((int)$user['id']);
        if (empty($roles)) {
            $roles = [$user['role'] ?? 'admin'];
        }

        // Restrict agent-only accounts from web portal login
        if (count($roles) === 1 && in_array('agent', $roles, true)) {
            self::$lastError = 'Agent accounts are restricted to softphone SIP registration and cannot log in to the web console.';
            return false;
        }

        self::login($user, $roles);
        return true;
    }

    public static function login(array $user, ?array $roles = null): void
    {
        self::initSession();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_regenerate_id(true);
        }

        $userId = (int)$user['id'];
        $roleSlugs = $roles ?? User::getRoleSlugs($userId);
        if (empty($roleSlugs)) {
            $roleSlugs = [$user['role'] ?? 'admin'];
        }

        $perms = User::getPermissionSlugs($userId);
        $teams = array_column(User::getTeams($userId), 'id');

        $_SESSION['user_id']              = $userId;
        $_SESSION['username']             = $user['username'];
        $_SESSION['user_name']            = $user['name'] ?? $user['username'];
        $_SESSION['user_role']            = $roleSlugs[0] ?? ($user['role'] ?? 'admin');
        $_SESSION['roles']                = $roleSlugs;
        $_SESSION['permissions']          = $perms;
        $_SESSION['teams']                = $teams;
        $_SESSION['must_change_password'] = !empty($user['must_change_password']);
        $_SESSION['auth_time']            = time();
    }

    public static function mustChangePassword(): bool
    {
        self::initSession();
        return !empty($_SESSION['must_change_password']);
    }

    public static function clearMustChangePassword(): void
    {
        self::initSession();
        $_SESSION['must_change_password'] = false;
    }

    public static function check(): bool
    {
        self::initSession();
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        self::initSession();
        if (!self::check()) {
            return null;
        }

        return User::findById((int)$_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        self::initSession();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function username(): string
    {
        self::initSession();
        return $_SESSION['username'] ?? 'Guest';
    }

    public static function roles(): array
    {
        self::initSession();
        return $_SESSION['roles'] ?? [$_SESSION['user_role'] ?? 'admin'];
    }

    public static function permissions(): array
    {
        self::initSession();
        return $_SESSION['permissions'] ?? [];
    }

    public static function teams(): array
    {
        self::initSession();
        return $_SESSION['teams'] ?? [];
    }

    public static function primaryRole(): string
    {
        self::initSession();
        return $_SESSION['user_role'] ?? 'admin';
    }

    public static function can(string $permission): bool
    {
        self::initSession();
        if (!self::check()) {
            return false;
        }

        // Superadmin bypass
        if (self::hasRole('superadmin')) {
            return true;
        }

        return in_array($permission, self::permissions(), true);
    }

    public static function hasRole(string|array $role): bool
    {
        self::initSession();
        if (!self::check()) {
            return false;
        }

        $userRoles = self::roles();
        $checkRoles = is_array($role) ? $role : [$role];

        foreach ($checkRoles as $r) {
            if (in_array($r, $userRoles, true)) {
                return true;
            }
        }
        return false;
    }

    public static function isSuperadmin(): bool
    {
        return self::hasRole('superadmin');
    }

    public static function isAdmin(): bool
    {
        return self::hasRole(['superadmin', 'admin']);
    }

    public static function isSupervisor(): bool
    {
        return self::hasRole('supervisor');
    }

    public static function isAgent(): bool
    {
        return self::hasRole('agent');
    }

    public static function teamScoped(): bool
    {
        return self::isSupervisor() && !self::isAdmin();
    }

    public static function lastError(): ?string
    {
        return self::$lastError;
    }

    public static function logout(): void
    {
        self::initSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies') && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }
}
