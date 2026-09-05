<?php

declare(strict_types=1);

namespace Astereal\Web\Support;

use Astereal\Web\Models\User;

class Auth
{
    public static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_samesite', 'Lax');
            session_start();
        }
    }

    public static function attempt(string $username, string $password): bool
    {
        self::initSession();

        $user = User::findByUsername($username);
        if (!$user) {
            return false;
        }

        if (User::verifyPassword($password, $user['password'])) {
            self::login($user);
            return true;
        }

        return false;
    }

    public static function login(array $user): void
    {
        self::initSession();
        session_regenerate_id(true);

        $_SESSION['user_id']   = (int)$user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['user_name'] = $user['name'] ?? $user['username'];
        $_SESSION['user_role'] = $user['role'] ?? 'admin';
        $_SESSION['auth_time'] = time();
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

    public static function logout(): void
    {
        self::initSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
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
        session_destroy();
    }
}
