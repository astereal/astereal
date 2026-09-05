<?php

declare(strict_types=1);

namespace Astereal\Web\Controllers\Web;

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
        $password = (string)$request->input('password', '');

        if (empty($username) || empty($password)) {
            Response::view('auth.login', [
                'error'    => 'Please enter both username and password.',
                'username' => $username,
            ]);
            return;
        }

        if (Auth::attempt($username, $password)) {
            Response::redirect('/');
            return;
        }

        Response::view('auth.login', [
            'error'    => 'Invalid credentials. Access denied.',
            'username' => $username,
        ]);
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        Response::redirect('/login');
    }
}
