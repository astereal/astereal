<?php

declare(strict_types=1);

namespace Astereal\Web\Controllers\Web;

use Astereal\Web\Models\User;
use Astereal\Web\Support\Auth;
use Astereal\Web\Support\Request;
use Astereal\Web\Support\Response;

class AuthController
{
    public function showLogin(Request $request): void
    {
        Response::view('auth.login', [
            'error'    => null,
            'username' => '',
        ]);
    }

    public function login(Request $request): void
    {
        $username = trim((string)$request->input('username', ''));
        $password = trim((string)$request->input('password', ''));

        if (empty($username) || empty($password)) {
            Response::view('auth.login', [
                'error'    => 'Please enter both username and password.',
                'username' => $username,
            ]);
            return;
        }

        if (Auth::attempt($username, $password)) {
            if (Auth::mustChangePassword()) {
                Response::redirect('/password/change');
                return;
            }
            Response::redirect('/');
            return;
        }

        error_log(sprintf(
            '[Astereal Auth] Failed login attempt for "%s" - %s (DB driver: %s, DB: %s)',
            $username,
            Auth::lastError() ?: 'Invalid credentials',
            \Astereal\Web\Models\Database::getDriver(),
            \Astereal\Web\Models\Database::getDatabaseName()
        ));

        Response::view('auth.login', [
            'error'    => Auth::lastError() ?: 'Invalid credentials. Access denied.',
            'username' => $username,
        ]);
    }

    public function showChangePassword(Request $request): void
    {
        if (!Auth::check()) {
            Response::redirect('/login');
            return;
        }

        Response::view('auth.change_password', [
            'error'   => null,
            'success' => null,
            'user'    => Auth::user(),
        ]);
    }

    public function updateInitialPassword(Request $request): void
    {
        if (!Auth::check()) {
            Response::redirect('/login');
            return;
        }

        $userId = Auth::id();
        $newPassword = (string)$request->input('new_password', '');
        $confirmPassword = (string)$request->input('confirm_password', '');

        if (strlen($newPassword) < 8) {
            Response::view('auth.change_password', [
                'error'   => 'Password must be at least 8 characters long.',
                'success' => null,
                'user'    => Auth::user(),
            ]);
            return;
        }

        if ($newPassword !== $confirmPassword) {
            Response::view('auth.change_password', [
                'error'   => 'New password and confirmation do not match.',
                'success' => null,
                'user'    => Auth::user(),
            ]);
            return;
        }

        if ($userId) {
            User::updatePassword($userId, $newPassword);
        }
        Auth::clearMustChangePassword();

        Response::redirect('/?welcome=1');
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        Response::redirect('/login');
    }
}
