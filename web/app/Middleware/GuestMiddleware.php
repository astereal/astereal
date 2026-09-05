<?php

declare(strict_types=1);

namespace Astereal\Web\Middleware;

use Astereal\Web\Support\Auth;
use Astereal\Web\Support\Request;
use Astereal\Web\Support\Response;

class GuestMiddleware
{
    public function handle(Request $request): void
    {
        if (Auth::check()) {
            // Already authenticated: redirect to dashboard
            Response::redirect('/');
        }
    }
}
