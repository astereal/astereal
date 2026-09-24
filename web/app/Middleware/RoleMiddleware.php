<?php

declare(strict_types=1);

namespace Astereal\Web\Middleware;

use Astereal\Web\Support\Auth;
use Astereal\Web\Support\Request;
use Astereal\Web\Support\Response;

class RoleMiddleware
{
    /** @var array<string> */
    protected array $roles;

    public function __construct(string|array $roles)
    {
        $this->roles = is_array($roles) ? $roles : explode(',', $roles);
    }

    public static function requires(string|array $roles): self
    {
        return new self($roles);
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

        if (!Auth::hasRole($this->roles)) {
            $required = implode(' or ', $this->roles);

            if (str_starts_with($request->path(), '/api/')) {
                Response::json([
                    'success' => false,
                    'error'   => "Forbidden. Requires role: {$required}",
                ], 403)->send();
                exit;
            }

            http_response_code(403);
            echo "<h1>403 Forbidden</h1><p>You do not have the required role (<code>" . htmlspecialchars($required) . "</code>) to access this resource.</p>";
            exit;
        }
    }
}
