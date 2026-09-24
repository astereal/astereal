<?php

declare(strict_types=1);

namespace Astereal\Web\Controllers\Web;

use Astereal\Web\Models\User;
use Astereal\Web\Support\Auth;
use Astereal\Web\Support\Request;
use Astereal\Web\Support\Response;

class ProfileController
{
    public function show(Request $request): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::redirect('/login');
            return;
        }

        $userId = (int)$user['id'];
        $roles = User::getRoles($userId);
        $permissions = User::getPermissions($userId);
        $teams = User::getTeams($userId);

        $success = $_SESSION['flash_success'] ?? null;
        $error   = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        Response::view('profile.index', [
            'user'        => $user,
            'roles'       => $roles,
            'permissions' => $permissions,
            'teams'       => $teams,
            'success'     => $success,
            'error'       => $error,
            'title'       => 'Admin Profile &bull; ' . (getenv('APP_NAME') ?: 'Astereal'),
        ]);
    }

    public function update(Request $request): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::redirect('/login');
            return;
        }

        $userId = (int)$user['id'];
        $name   = trim((string)$request->input('name', ''));
        $email  = trim((string)$request->input('email', ''));

        if (empty($name)) {
            $_SESSION['flash_error'] = 'Display Name cannot be empty.';
            Response::redirect('/profile');
            return;
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Please enter a valid email address.';
            Response::redirect('/profile');
            return;
        }

        User::updateProfile($userId, $name, $email);
        $_SESSION['user_name'] = $name;
        $_SESSION['flash_success'] = 'Profile information updated successfully.';

        Response::redirect('/profile');
    }

    public function changePassword(Request $request): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::redirect('/login');
            return;
        }

        $userId          = (int)$user['id'];
        $currentPassword = (string)$request->input('current_password', '');
        $newPassword     = (string)$request->input('new_password', '');
        $confirmPassword = (string)$request->input('confirm_password', '');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['flash_error'] = 'All password fields are required.';
            Response::redirect('/profile');
            return;
        }

        if (!User::verifyPassword($currentPassword, $user['password'])) {
            $_SESSION['flash_error'] = 'The current password you entered is incorrect.';
            Response::redirect('/profile');
            return;
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['flash_error'] = 'New password must be at least 8 characters long.';
            Response::redirect('/profile');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = 'New password and confirmation do not match.';
            Response::redirect('/profile');
            return;
        }

        User::updatePassword($userId, $newPassword);
        Auth::clearMustChangePassword();
        $_SESSION['flash_success'] = 'Your password has been changed successfully.';

        Response::redirect('/profile');
    }
}
