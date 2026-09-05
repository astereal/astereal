<?php

namespace Astereal\Web\Support;

class Router
{
    protected static array $routes = [];

    public static function get(string $uri, mixed $action, array $middleware = []): void
    {
        self::addRoute('GET', $uri, $action, $middleware);
    }

    public static function post(string $uri, mixed $action, array $middleware = []): void
    {
        self::addRoute('POST', $uri, $action, $middleware);
    }

    public static function addRoute(string $method, string $uri, mixed $action, array $middleware = []): void
    {
        $normalizedUri = '/' . trim($uri, '/');
        self::$routes[] = [
            'method'     => strtoupper($method),
            'uri'        => $normalizedUri,
            'action'     => $action,
            'middleware' => $middleware,
        ];
    }

    public static function dispatch(Request $request): void
    {
        $requestMethod = $request->method();
        $requestPath   = $request->path();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            $pattern = self::compilePattern($route['uri']);
            if (preg_match($pattern, $requestPath, $matches)) {
                // Execute route middleware pipeline
                foreach ($route['middleware'] as $mwClass) {
                    $mw = new $mwClass();
                    $mw->handle($request);
                }

                // Extract named URL parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Execute action
                $action = $route['action'];

                if (is_callable($action)) {
                    $action($request, $params);
                    return;
                }

                if (is_array($action) && count($action) === 2) {
                    [$controllerClass, $method] = $action;
                    $controller = new $controllerClass();
                    $controller->$method($request, $params);
                    return;
                }
            }
        }

        // No route matched
        if ($request->isJson() || str_starts_with($requestPath, '/api/')) {
            Response::error('Endpoint not found', 404);
        } else {
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>The requested URL {$requestPath} was not found on Astereal.</p>";
            exit(0);
        }
    }

    protected static function compilePattern(string $uri): string
    {
        // Convert {param} placeholders to regex named groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }
}
