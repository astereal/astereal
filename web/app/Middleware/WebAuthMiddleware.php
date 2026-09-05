<?php

declare(strict_types=1);

namespace Astereal\Web\Middleware;

use Astereal\Web\Support\Auth;
use Astereal\Web\Support\Request;
use Astereal\Web\Support\Response;

class WebAuthMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            // Unauthenticated: redirect to login page
            Response::redirect('/login');
        }
    }
}
