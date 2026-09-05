<?php

namespace Astereal\Web\Support;

class Response
{
    public static function json(mixed $data, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        foreach ($headers as $key => $value) {
            header("{$key}: {$value}");
        }

        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit(0);
    }

    public static function view(string $viewPath, array $data = []): void
    {
        $baseViewDir = dirname(__DIR__, 2) . '/views';
        $file = $baseViewDir . '/' . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($file)) {
            http_response_code(500);
            echo "View [{$viewPath}] not found at {$file}";
            exit(1);
        }

        // Extract variables into view scope
        extract($data);

        ob_start();
        include $file;
        $content = ob_get_clean();

        echo $content;
        exit(0);
    }

    public static function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: {$url}");
        exit(0);
    }

    public static function error(string $message, int $status = 400): void
    {
        self::json([
            'status'  => 'error',
            'message' => $message,
        ], $status);
    }
}
