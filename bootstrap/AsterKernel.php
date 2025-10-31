<?php

namespace Bootstrap;

class AsterKernel
{
    protected array $commands = [];

    public function __construct()
    {
        $this->loadCommands(__DIR__ . '/Commands');
    }

    public function handle(array $argv): void
    {
        array_shift($argv); // remove script name

        $commandName = $argv[0] ?? null;

        if (!$commandName) {
            $this->listCommands();
            return;
        }

        if (!isset($this->commands[$commandName])) {
            $this->printError("Unknown command: $commandName");
            echo "\n";
            $this->listCommands();
            return;
        }

        $command = new $this->commands[$commandName]['class'];
        $command->handle(array_slice($argv, 1));
    }

    protected function loadCommands(string $dir): void
    {
        foreach (glob("$dir/*.php") as $file) {
            require_once $file;

            $className = 'Bootstrap\\Commands\\' . basename($file, '.php');
            if (!class_exists($className)) {
                continue;
            }

            $command = new $className();

            // Only register if it has a $name property
            if (property_exists($command, 'name')) {
                $this->commands[$command->name] = [
                    'class' => $className,
                    'description' => $command->description ?? '',
                ];
            }
        }
    }

    protected function listCommands(): void
    {
        $green = "\033[32m";
        $yellow = "\033[33m";
        $reset = "\033[0m";

        echo "{$green}Astereal CLI{$reset}\n";
        echo "Usage:\n  php aster [command]\n\n";
        echo "{$yellow}Available commands:{$reset}\n";

        foreach ($this->commands as $name => $info) {
            printf("  %-10s %s\n", "{$green}{$name}{$reset}", $info['description'] ?? '');
        }

        echo "\nUse 'php aster [command]' for details.\n\n";
    }

    protected function printError(string $message): void
    {
        $red = "\033[31m";
        $reset = "\033[0m";
        echo "{$red}{$message}{$reset}\n";
    }
}
