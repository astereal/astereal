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

            foreach ($results as $message) {
                echo $message . "\n";
            }

            echo "\n✨ Publish process completed.\n";

        } catch (RuntimeException $e) {
            $this->error($e->getMessage());
        } catch (\Throwable $e) {
            // last-resort safety net
            $this->error('Unexpected error occurred.');
        }
    }

    protected function error(string $message): void
    {
        $red = "\033[31m";
        $reset = "\033[0m";
        echo "{$red}❌ {$message}{$reset}\n";
    }
}
