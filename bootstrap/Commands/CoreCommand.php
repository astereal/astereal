<?php

namespace Bootstrap\Commands;

class CoreCommand
{
    public string $name = 'core';
    public string $description = 'Manage Asterisk core service (status, start, stop, restart)';

    public function handle(array $args): void
    {
        $action = strtolower($args[0] ?? 'status');

        switch ($action) {
            case 'status':
                $this->status();
                break;

            case 'start':
                $this->start();
                break;

            case 'stop':
                $this->stop();
                break;

            case 'restart':
                $this->restart();
                break;

            case 'help':
            default:
                $this->help();
                break;
        }
    }

    public function isRunning(): bool
    {
        exec('asterisk -rx "core ping" 2>&1', $output, $returnVar);
        return $returnVar === 0;
    }

    protected function status(): void
    {
        $green = "\033[32m";
        $red = "\033[31m";
        $reset = "\033[0m";

        if ($this->isRunning()) {
            exec('asterisk -rx "core show version" 2>&1', $output);
            $version = trim($output[0] ?? 'Asterisk Active');
            echo "{$green}● Asterisk is running{$reset} ({$version})\n";
        } else {
            echo "{$red}○ Asterisk is stopped{$reset}\n";
            echo "  Run 'php aster core:start' or 'systemctl start asterisk' to start.\n";
        }
    }

    protected function start(): void
    {
        $green = "\033[32m";
        $red = "\033[31m";
        $reset = "\033[0m";

        if ($this->isRunning()) {
            echo "{$green}✔ Asterisk is already running.{$reset}\n";
            return;
        }

        echo "🔄 Starting Asterisk service...\n";

        // 1. Try systemd
        exec('systemctl start asterisk 2>&1', $output, $returnVar);
        if ($returnVar === 0) {
            for ($i = 0; $i < 6; $i++) {
                usleep(500000);
                if ($this->isRunning()) {
                    echo "{$green}✅ Asterisk started successfully via systemctl.{$reset}\n";
                    return;
                }
            }
        }

        // 2. Try sysv service
        exec('service asterisk start 2>&1', $output, $returnVar);
        if ($returnVar === 0) {
            for ($i = 0; $i < 6; $i++) {
                usleep(500000);
                if ($this->isRunning()) {
                    echo "{$green}✅ Asterisk started successfully via service.{$reset}\n";
                    return;
                }
            }
        }

        // 3. Try direct binary invocation
        exec('asterisk 2>&1', $output, $returnVar);
        for ($i = 0; $i < 6; $i++) {
            usleep(500000);
            if ($this->isRunning()) {
                echo "{$green}✅ Asterisk started directly.{$reset}\n";
                return;
            }
        }

        echo "{$red}❌ Failed to start Asterisk.{$reset}\n";
        if (!empty($output)) {
            echo implode("\n", $output) . "\n";
        }
    }

    protected function stop(): void
    {
        $green = "\033[32m";
        $red = "\033[31m";
        $reset = "\033[0m";

        if (!$this->isRunning()) {
            echo "{$green}✔ Asterisk is already stopped.{$reset}\n";
            return;
        }

        echo "🔄 Stopping Asterisk service...\n";

        exec('systemctl stop asterisk 2>&1', $output, $returnVar);
        if ($returnVar === 0) {
            echo "{$green}✅ Asterisk stopped successfully.{$reset}\n";
            return;
        }

        exec('asterisk -rx "core stop gracefully" 2>&1', $output, $returnVar);
        if ($returnVar === 0) {
            echo "{$green}✅ Asterisk stopped gracefully.{$reset}\n";
            return;
        }

        exec('service asterisk stop 2>&1', $output, $returnVar);
        echo "{$green}✅ Asterisk stop requested.{$reset}\n";
    }

    protected function restart(): void
    {
        $green = "\033[32m";
        $reset = "\033[0m";

        echo "🔄 Restarting Asterisk service...\n";

        exec('systemctl restart asterisk 2>&1', $output, $returnVar);
        if ($returnVar === 0) {
            sleep(1);
            if ($this->isRunning()) {
                echo "{$green}✅ Asterisk restarted successfully.{$reset}\n";
                return;
            }
        }

        exec('asterisk -rx "core restart gracefully" 2>&1', $output, $returnVar);
        echo "{$green}✅ Asterisk restart requested.{$reset}\n";
    }

    protected function help(): void
    {
        echo "Astereal Core Manager\n";
        echo "Usage:\n";
        echo "  php aster core:status    Check if Asterisk daemon is running\n";
        echo "  php aster core:start     Start the Asterisk service\n";
        echo "  php aster core:stop      Stop the Asterisk service\n";
        echo "  php aster core:restart   Restart the Asterisk service\n";
    }
}
