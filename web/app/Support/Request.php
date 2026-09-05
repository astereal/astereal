<?php

namespace Astereal\Web\Support;

class Request
{
    protected array $query;
    protected array $post;
    protected array $headers;
    protected ?array $json = null;
    protected string $rawBody;

    public function __construct()
    {
        $this->query   = $_GET;
        $this->post    = $_POST;
        $this->rawBody = file_get_contents('php://input') ?: '';
        $this->headers = $this->loadHeaders();

        if ($this->isJson()) {
            $decoded = json_decode($this->rawBody, true);
            if (is_array($decoded)) {
                $this->json = $decoded;
            }
        }
    }

    public static function capture(): self
    {
        return new self();
    }

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        // Remove script sub-path if running inside a sub-directory
        $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptName !== '/' && str_starts_with($path, $scriptName)) {
            $path = substr($path, strlen($scriptName));
        }

        return '/' . trim($path, '/');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if ($this->json !== null && array_key_exists($key, $this->json)) {
            return $this->json[$key];
        }

        if (array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }

        if (array_key_exists($key, $this->query)) {
            return $this->query[$key];
        }

        return $default;
    }

    public function all(): array
    {
        if ($this->json !== null) {
            return array_merge($this->query, $this->json);
        }

        return array_merge($this->query, $this->post);
    }

    public function rawBody(): string
    {
        return $this->rawBody;
    }

    public function header(string $name, ?string $default = null): ?string
    {
        $key = strtoupper(str_replace('-', '_', $name));
        return $this->headers[$key] ?? $default;
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function isJson(): bool
    {
        $contentType = $this->header('CONTENT_TYPE', '');
        return str_contains(strtolower($contentType), 'application/json');
    }

    protected function loadHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerKey = substr($key, 5);
                $headers[$headerKey] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $headers[$key] = $value;
            }
        }
        return $headers;
    }
}
