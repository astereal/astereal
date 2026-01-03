<?php

namespace Modules\Publisher;

class Manager
{
    protected string $configPath;
    protected string $appPath;

    public function __construct()
    {
        $this->configPath = __DIR__ . '/../../settings/publisher.php';
        $this->appPath    = __DIR__ . '/../../app';
    }

    /**
     * Publish all configured paths
     */
    public function publish(): array
    {
        $this->assertUnix();
        
        if (!file_exists($this->configPath)) {
            return [
                'error' => [
                    'success' => false,
                    'error' => 'Publisher config not found.',
                ],
            ];
        }

        $paths = require $this->configPath;
        $results = [];

        foreach ($paths as $key => $destination) {
            $source = "{$this->appPath}/{$key}";

            if (!is_dir($source)) {
                $results[$key] = [
                    'success' => false,
                    'error' => "Source directory not found: {$source}",
                ];
                continue;
            }

            try {
                $this->copyDirectory($source, $destination);

                $results[$key] = [
                    'success' => true,
                    'destination' => $destination,
                ];
            } catch (\Throwable $e) {
                $results[$key] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Recursively copy a directory
     */
    protected function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $items = scandir($source);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $src = "{$source}/{$item}";
            $dst = "{$destination}/{$item}";

            if (is_dir($src)) {
                $this->copyDirectory($src, $dst);
            } else {
                if (!copy($src, $dst)) {
                    throw new \RuntimeException("Failed to copy {$src} to {$dst}");
                }
            }
        }
    }

    protected function assertUnix(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            throw new \RuntimeException(
                'Publisher can only run on Unix/Linux systems.'
            );
        }
    }

}
