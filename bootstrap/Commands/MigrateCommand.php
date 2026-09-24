<?php

declare(strict_types=1);

namespace Bootstrap\Commands;

use Astereal\Web\Models\Database;
use Astereal\Web\Support\Migrator;
use Throwable;

class MigrateCommand
{
    public string $name = 'migrate';
    public string $description = 'Execute database migrations and view migration status';

    public function handle(array $args): void
    {
        $action = strtolower($args[0] ?? 'run');

        switch ($action) {
            case 'status':
                $this->status();
                break;

            case 'rollback':
                $this->rollback();
                break;

            case 'fresh':
                $this->fresh();
                break;

            case 'help':
                $this->help();
                break;

            case 'run':
            default:
                $this->runMigrations();
                break;
        }
    }

    /**
     * Run all pending database migrations
     */
    protected function runMigrations(): void
    {
        $green  = "\033[32m";
        $yellow = "\033[33m";
        $reset  = "\033[0m";
        $red    = "\033[31m";

        echo "🔄 Checking for pending database migrations...\n\n";

        try {
            $pdo = Database::getConnection(false);
            $migrator = new Migrator($pdo);
            $pending = $migrator->getPending();

            if (empty($pending)) {
                echo "  {$green}✨ Database is already up to date. Nothing to migrate.{$reset}\n\n";
                return;
            }

            $migrator->run(function (string $event, string $file) use ($green, $yellow, $reset) {
                if ($event === 'migrating') {
                    echo "  {$yellow}⏳ Migrating:{$reset} {$file}...\n";
                } elseif ($event === 'migrated') {
                    echo "  {$green}✅ Migrated: {$reset} {$file}\n";
                }
            });

            echo "\n{$green}🚀 All pending migrations completed successfully.{$reset}\n\n";

        } catch (Throwable $e) {
            echo "  {$red}❌ Migration failed:{$reset} " . $e->getMessage() . "\n\n";
        }
    }

    /**
     * Show table of all migrations (Ran vs Pending)
     */
    protected function status(): void
    {
        $green  = "\033[32m";
        $yellow = "\033[33m";
        $reset  = "\033[0m";
        $cyan   = "\033[36m";

        echo "📊 Database Migration Status:\n\n";

        try {
            $pdo = Database::getConnection(false);
            $migrator = new Migrator($pdo);
            $statusList = $migrator->status();

            if (empty($statusList)) {
                echo "  {$yellow}No migration files discovered in database/migrations/{$reset}\n\n";
                return;
            }

            printf("  %-40s | %-10s | %-6s | %-20s\n", "Migration File", "Status", "Batch", "Applied At");
            echo "  " . str_repeat('-', 85) . "\n";

            foreach ($statusList as $item) {
                $statusBadge = $item['ran'] ? "{$green}Ran{$reset}" : "{$yellow}Pending{$reset}";
                $batchStr = $item['batch'] !== null ? (string)$item['batch'] : '-';
                $appliedStr = $item['applied_at'] ?: '-';

                printf("  %-40s | %-19s | %-6s | %-20s\n", $item['migration'], $statusBadge, $batchStr, $appliedStr);
            }
            echo "\n";

        } catch (Throwable $e) {
            echo "  \033[31m❌ Could not retrieve migration status:\033[0m " . $e->getMessage() . "\n\n";
        }
    }

    /**
     * Rollback the latest batch of migrations
     */
    protected function rollback(): void
    {
        $green  = "\033[32m";
        $yellow = "\033[33m";
        $reset  = "\033[0m";
        $red    = "\033[31m";

        echo "⏪ Rolling back latest migration batch...\n\n";

        try {
            $pdo = Database::getConnection(false);
            $migrator = new Migrator($pdo);

            $rolledBack = $migrator->rollback(function (string $event, string $file) use ($yellow, $green, $reset) {
                if ($event === 'rolling_back') {
                    echo "  {$yellow}⏳ Rolling back:{$reset} {$file}...\n";
                } elseif ($event === 'rolled_back') {
                    echo "  {$green}✅ Rolled back: {$reset} {$file}\n";
                }
            });

            if (empty($rolledBack)) {
                echo "  {$yellow}Nothing to rollback.{$reset}\n\n";
            } else {
                echo "\n{$green}✨ Rollback complete.{$reset}\n\n";
            }

        } catch (Throwable $e) {
            echo "  {$red}❌ Rollback failed:{$reset} " . $e->getMessage() . "\n\n";
        }
    }

    /**
     * Drop all tables and re-run all migrations from scratch
     */
    protected function fresh(): void
    {
        $green  = "\033[32m";
        $yellow = "\033[33m";
        $reset  = "\033[0m";
        $red    = "\033[31m";

        echo "🧹 Dropping all database tables and running fresh migrations...\n\n";

        try {
            $pdo = Database::getConnection(false);
            $migrator = new Migrator($pdo);

            $migrator->fresh(function (string $event, string $name) use ($yellow, $green, $reset) {
                if ($event === 'dropping_table') {
                    echo "  {$yellow}🗑️  Dropping table:{$reset} {$name}\n";
                } elseif ($event === 'dropped_all') {
                    echo "  {$green}✨ Dropped all existing tables successfully.{$reset}\n\n";
                } elseif ($event === 'migrating') {
                    echo "  {$yellow}⏳ Migrating:{$reset} {$name}...\n";
                } elseif ($event === 'migrated') {
                    echo "  {$green}✅ Migrated: {$reset} {$name}\n";
                }
            });

            echo "\n{$green}🚀 Fresh migration completed successfully!{$reset}\n\n";

        } catch (Throwable $e) {
            echo "  {$red}❌ Fresh migration failed:{$reset} " . $e->getMessage() . "\n\n";
        }
    }

    protected function help(): void
    {
        $green = "\033[32m";
        $reset = "\033[0m";

        echo "Astereal Migration Command\n\n";
        echo "Usage:\n";
        echo "  php aster migrate          Run all pending migrations\n";
        echo "  php aster migrate:status   Display status of all migrations (Ran / Pending)\n";
        echo "  php aster migrate:rollback Roll back the latest migration batch\n";
        echo "  php aster migrate:fresh    Drop all tables and re-run all migrations from scratch\n\n";
    }
}
