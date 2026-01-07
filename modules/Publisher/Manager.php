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
            echo "❌ Publisher config not found.\n";
            return [
                'error' => [
                    'success' => false,
                    'error' => 'Publisher config not found.',
                ],
            ];
        }

        $config = require $this->configPath;
        $paths  = $config['paths'] ?? [];
        $results = [];

        echo "\n📦 Publishing application files...\n";

        foreach ($paths as $key => $destination) {
            $source = "{$this->appPath}/{$key}";
            echo "➡️  Publishing {$key} → {$destination} ... ";

            if (!is_dir($source)) {
                echo "❌ Source directory not found.\n";
                $results[$key] = [
                    'success' => false,
                    'error' => "Source directory not found: {$source}",
                ];
                continue;
            }

            try {
                $this->copyDirectory($source, $destination);
                echo "✅ Done.\n";

                $results[$key] = [
                    'success' => true,
                    'destination' => $destination,
                ];
            } catch (\Throwable $e) {
                echo "❌ Failed: {$e->getMessage()}\n";
                $results[$key] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // 🔁 Reload Asterisk (if configured)
        if (!empty($config['reload'])) {
            $results['reload'] = $this->reloadAsterisk($config['reload']);
        }

        echo "\n✔ Publish process completed.\n";

        return $results;
    }

    /**
     * Reload asterisk modules such as dialplan.
     */
    protected function reloadAsterisk(array $commands): array
    {
        $results = [];

        echo "\n🔁 Reloading Asterisk subsystems...\n";

        foreach ($commands as $command) {
            echo "➡️  Running: {$command} ... ";

            $cmd = 'asterisk -rx ' . escapeshellarg($command);
            exec($cmd, $output, $exitCode);

            if ($exitCode === 0) {
                echo "✅ OK\n";
            } else {
                echo "❌ FAILED\n";
            }

            $results[] = [
                'command' => $command,
                'success' => $exitCode === 0,
                'output'  => $output,
            ];
        }

        echo "✔ Reload process completed.\n\n";

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
