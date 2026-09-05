<?php

namespace Bootstrap\Commands;

use Modules\Publisher\Manager;
use RuntimeException;

class PublisherCommand
{
    public string $name = 'publish';
    public string $description = 'Publish application files to system paths';

    public function handle(array $args): void
    {
        echo "📦 Publishing application files...\n\n";

        try {
            $manager = new Manager();
            $results = $manager->publish();

            $files = $results['files'] ?? [];
            $reloads = $results['reloads'] ?? [];

            // Fallback for legacy flat array
            if (empty($files) && empty($reloads) && !empty($results)) {
                $files = $results;
            }

            $hasFailures = false;
            $green = "\033[32m";
            $red = "\033[31m";
            $reset = "\033[0m";

            // 1. Published Files
            foreach ($files as $key => $result) {
                if (is_array($result)) {
                    if (!empty($result['success'])) {
                        echo "  {$green}✅ Published [{$key}]{$reset} -> {$result['destination']}\n";
                    } else {
                        $hasFailures = true;
                        $err = $result['error'] ?? 'Unknown error';
                        echo "  {$red}❌ Failed [{$key}]{$reset}: {$err}\n";
                    }
                } else {
                    echo "  " . $result . "\n";
                }
            }

            // 2. Asterisk Module Reloads
            if (!empty($reloads)) {
                echo "\n🔄 Reloading Asterisk modules...\n\n";
                foreach ($reloads as $key => $reload) {
                    if ($key === '_service') {
                        if (!empty($reload['success'])) {
                            echo "  {$green}⚡ {$reload['message']}{$reset}\n";
                        } else {
                            $hasFailures = true;
                            echo "  {$red}❌ {$reload['error']}{$reset}\n";
                        }
                        continue;
                    }

                    $label = $reload['label'] ?? ucfirst($key);
                    $cmd = $reload['command'] ?? '';
                    if (!empty($reload['success'])) {
                        echo "  {$green}✅ Reloaded {$label}{$reset} ({$cmd})\n";
                    } else {
                        $hasFailures = true;
                        $err = $reload['error'] ?? 'Failed';
                        echo "  {$red}❌ Failed to reload {$label}{$reset} ({$cmd}): {$err}\n";
                    }
                }
            }

            if ($hasFailures) {
                echo "\n⚠️ Publish completed with errors.\n";
            } else {
                echo "\n✨ Publish process completed successfully.\n";
            }

        } catch (RuntimeException $e) {
            $this->error($e->getMessage());
        } catch (\Throwable $e) {
            $this->error("Unexpected error: {$e->getMessage()} (in {$e->getFile()}:{$e->getLine()})");
        }
    }

    protected function error(string $message): void
    {
        $red = "\033[31m";
        $reset = "\033[0m";
        echo "{$red}❌ {$message}{$reset}\n";
    }
}
