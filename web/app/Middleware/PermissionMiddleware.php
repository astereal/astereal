<?php

declare(strict_types=1);

namespace Astereal\Web\Middleware;

use Astereal\Web\Support\Auth;
use Astereal\Web\Support\Request;
use Astereal\Web\Support\Response;

class PermissionMiddleware
{
    protected string $permission;

    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }

    public static function requires(string $permission): self
    {
        return new self($permission);
    }

    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            if (str_starts_with($request->path(), '/api/')) {
                Response::json(['success' => false, 'error' => 'Unauthenticated.'], 401)->send();
                exit;
            }
            Response::redirect('/login')->send();
            exit;
        }

        if (!Auth::can($this->permission)) {
            if (str_starts_with($request->path(), '/api/')) {
                Response::json([
                    'success' => false,
                    'error'   => "Forbidden. Requires permission: {$this->permission}",
                ], 403)->send();
                exit;
            }

            http_response_code(403);
            echo "<h1>403 Forbidden</h1><p>You do not have the required permission (<code>" . htmlspecialchars($this->permission) . "</code>) to access this resource.</p>";
            exit;
        }
    }
}
