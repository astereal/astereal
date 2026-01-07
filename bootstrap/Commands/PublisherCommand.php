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
        echo "📦 Started publishing files...\n\n";

        try {
            $manager = new Manager();
            $results = $manager->publish();

            // ✅ Display publish results
            if (!empty($results['publish'])) {
                foreach ($results['publish'] as $key => $status) {
                    if ($status['success']) {
                        echo "✅ {$key} published successfully.\n";
                    } else {
                        echo "❌ {$key} failed: {$status['error']}\n";
                    }
                }
            }

            // 🔁 Display reload results
            if (!empty($results['reload'])) {
                echo "\n🔁 Reload results:\n";
                foreach ($results['reload'] as $reload) {
                    $symbol = $reload['success'] ? '✅' : '❌';
                    echo " {$symbol} {$reload['command']}\n";
                }
            }

            echo "\n✨ Publish process completed.\n";

        } catch (RuntimeException $e) {
            $this->error($e->getMessage());
        } catch (\Throwable $e) {
            // last-resort safety net with more info
            $this->error("Unexpected error occurred: " . $e->getMessage());
        }
    }


    protected function error(string $message): void
    {
        $red = "\033[31m";
        $reset = "\033[0m";
        echo "{$red}❌ {$message}{$reset}\n";
    }
}
