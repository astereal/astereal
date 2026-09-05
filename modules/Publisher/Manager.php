<?php

namespace Modules\Publisher;

class Manager
{
    protected string $basePath;
    protected string $configPath;
    protected string $appPath;

    public function __construct()
    {
        $this->basePath   = dirname(__DIR__, 2);
        $this->configPath = $this->basePath . '/settings/publisher.php';
        $this->appPath    = $this->basePath . '/app';
    }

    /**
     * Publish all configured paths and trigger module reloads
     */
    public function publish(): array
    {
        $this->assertUnix();
        
        if (!file_exists($this->configPath)) {
            return [
                'files' => [
                    'config' => [
                        'success' => false,
                        'error' => 'Publisher config not found.',
                    ],
                ],
                'reloads' => [],
            ];
        }

        $config = require $this->configPath;

        // Support both structured ['paths' => [...], 'reloads' => [...]] and legacy flat array
        $paths = $config['paths'] ?? (isset($config['reloads']) || isset($config['reload']) ? [] : $config);
        $reloadsConfig = $config['reloads'] ?? [];

        // Support flat 'reload' array if provided
        if (empty($reloadsConfig) && !empty($config['reload'])) {
            foreach ($config['reload'] as $idx => $cmd) {
                $reloadsConfig['cmd_' . $idx] = [
                    'enabled' => true,
                    'command' => $cmd,
                    'label'   => ucfirst($cmd),
                ];
            }
        }

        $fileResults = [];

        foreach ($paths as $key => $destination) {
            $source = "{$this->appPath}/{$key}";
            if (!is_dir($source) && is_dir("{$this->basePath}/{$key}")) {
                $source = "{$this->basePath}/{$key}";
            }

            if (!is_dir($source)) {
                $fileResults[$key] = [
                    'success' => false,
                    'error' => "Source directory not found: {$source}",
                ];
                continue;
            }

            // If destination is the base path itself and source is inside base path,
            // the files are already in place, so skip copying to avoid self-overwrite
            $realDest = realpath($destination);
            $realBase = realpath($this->basePath);
            if ($realDest && $realBase && $realDest === $realBase) {
                $fileResults[$key] = [
                    'success'     => true,
                    'destination' => $destination . ' (already in place)',
                ];
                continue;
            }

            try {
                $this->copyDirectory($source, $destination);

                $fileResults[$key] = [
                    'success' => true,
                    'destination' => $destination,
                ];
            } catch (\Throwable $e) {
                $fileResults[$key] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $autoStart = $config['auto_start_asterisk'] ?? true;

        // Execute post-publish Asterisk module reloads
        $reloadResults = $this->reloadModules($reloadsConfig, $autoStart);

        return [
            'files'   => $fileResults,
            'reloads' => $reloadResults,
        ];
    }

    /**
     * Check if Asterisk daemon is active and responsive
     */
    public function isAsteriskRunning(): bool
    {
        exec('asterisk -rx "core ping" 2>&1', $output, $returnVar);
        return $returnVar === 0;
    }

    /**
     * Attempt to start the Asterisk service
     */
    public function startAsterisk(): array
    {
        // 1. Try systemd
        exec('systemctl start asterisk 2>&1', $output, $returnVar);
        if ($returnVar === 0) {
            for ($i = 0; $i < 6; $i++) {
                usleep(500000); // 0.5s intervals
                if ($this->isAsteriskRunning()) {
                    return ['success' => true, 'method' => 'systemctl start asterisk'];
                }
            }
        }

        // 2. Try sysv service
        exec('service asterisk start 2>&1', $output, $returnVar);
        if ($returnVar === 0) {
            for ($i = 0; $i < 6; $i++) {
                usleep(500000);
                if ($this->isAsteriskRunning()) {
                    return ['success' => true, 'method' => 'service asterisk start'];
                }
            }
        }

        // 3. Try direct binary invocation
        exec('asterisk 2>&1', $output, $returnVar);
        for ($i = 0; $i < 6; $i++) {
            usleep(500000);
            if ($this->isAsteriskRunning()) {
                return ['success' => true, 'method' => 'asterisk'];
            }
        }

        return [
            'success' => false,
            'error'   => !empty($output) ? implode("\n", $output) : 'Could not start Asterisk service.',
        ];
    }

    /**
     * Reload configured Asterisk modules via Asterisk CLI
     */
    public function reloadModules(array $reloads = [], bool $autoStart = true): array
    {
        $results = [];

        // Filter enabled modules
        $enabled = array_filter($reloads, fn($item) => !empty($item['enabled']));
        if (empty($enabled)) {
            return $results;
        }

        // Verify Asterisk is running before attempting reloads
        if (!$this->isAsteriskRunning()) {
            if ($autoStart) {
                $startResult = $this->startAsterisk();
                if ($startResult['success']) {
                    $results['_service'] = [
                        'success' => true,
                        'label'   => 'Asterisk Service',
                        'message' => "Asterisk was stopped. Started successfully via {$startResult['method']}.",
                    ];
                } else {
                    $results['_service'] = [
                        'success' => false,
                        'label'   => 'Asterisk Service',
                        'error'   => 'Asterisk is stopped and could not be started automatically (' . ($startResult['error'] ?? '') . ').',
                    ];
                    return $results;
                }
            } else {
                $results['_service'] = [
                    'success' => false,
                    'label'   => 'Asterisk Service',
                    'error'   => 'Asterisk is not running. Module reloads skipped.',
                ];
                return $results;
            }
        }

        foreach ($enabled as $key => $item) {
            $label   = $item['label'] ?? ucfirst($key);
            $command = $item['command'] ?? "{$key} reload";

            exec('asterisk -rx ' . escapeshellarg($command) . ' 2>&1', $output, $returnVar);

            if ($returnVar === 0) {
                $results[$key] = [
                    'success' => true,
                    'label'   => $label,
                    'command' => $command,
                    'output'  => !empty($output) ? implode("\n", $output) : '',
                ];
            } else {
                $results[$key] = [
                    'success' => false,
                    'label'   => $label,
                    'command' => $command,
                    'error'   => !empty($output) ? implode("\n", $output) : "Command exited with status {$returnVar}",
                ];
            }
            $output = [];
        }

        return $results;
    }

    /**
     * Recursively copy a directory
     */
    protected function copyDirectory(string $source, string $destination): void
    {
        $destination = rtrim($destination, '/\\');

        if (!is_dir($destination)) {
            if (!mkdir($destination, 0755, true) && !is_dir($destination)) {
                throw new \RuntimeException("Failed to create destination directory: {$destination}");
            }
        }

        $items = scandir($source);
        if ($items === false) {
            throw new \RuntimeException("Failed to read source directory: {$source}");
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $src = rtrim($source, '/\\') . '/' . $item;
            $dst = $destination . '/' . $item;

            if (is_dir($src)) {
                $this->copyDirectory($src, $dst);
            } else {
                if (!copy($src, $dst)) {
                    throw new \RuntimeException("Failed to copy {$src} to {$dst}");
                }
                // Set executable permissions for AGI scripts and read/write for others
                if (str_ends_with($dst, '.php') || str_contains($destination, 'agi-bin')) {
                    @chmod($dst, 0755);
                } else {
                    @chmod($dst, 0644);
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
