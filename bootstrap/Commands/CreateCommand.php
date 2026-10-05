<?php

declare(strict_types=1);

namespace Bootstrap\Commands;

class CreateCommand
{
    public string $name = 'create';
    public string $description = 'Scaffold telephony components, configs, migrations, models, controllers, and middleware';

    protected string $basePath;

    public function __construct()
    {
        $this->basePath = dirname(__DIR__, 2);
    }

    public function handle(array $args): void
    {
        $subcommand = strtolower($args[0] ?? 'help');
        $name = $args[1] ?? null;
        $options = array_slice($args, 2);

        switch ($subcommand) {
            case 'agi':
                $this->createAgi($name);
                break;

            case 'config':
                $this->createConfig($name);
                break;

            case 'migration':
                $this->createMigration($name);
                break;

            case 'model':
                $this->createModel($name);
                break;

            case 'controller':
                $this->createController($name, $options);
                break;

            case 'middleware':
                $this->createMiddleware($name);
                break;

            case 'help':
            default:
                $this->help();
                break;
        }
    }

    /**
     * Scaffold an AGI script in app/agi/<Name>.php
     */
    protected function createAgi(?string $name): void
    {
        if (empty($name)) {
            $this->printError("Missing AGI name. Usage: php aster create:agi <Name>");
            return;
        }

        $cleanName = basename(str_replace('\\', '/', $name));
        if (str_ends_with(strtolower($cleanName), '.php')) {
            $cleanName = substr($cleanName, 0, -4);
        }

        $fileName = $cleanName . '.php';
        $targetDir = $this->basePath . '/app/agi';
        $targetPath = $targetDir . '/' . $fileName;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        if (file_exists($targetPath)) {
            $this->printError("AGI script already exists: app/agi/{$fileName}");
            return;
        }

        $content = <<<PHP
#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Astereal Telephony - AGI Script: {$cleanName}
 *
 * Description:
 *   Handles custom telephony call flow logic, channel variables, and database/API actions.
 *
 * Dialplan Usage:
 *   exten => s,1,NoOp(*** Executing {$cleanName} AGI ***)
 *     same => n,AGI({$fileName})
 *     same => n,NoOp("AGI Result: \${AGI_{$cleanName}_STATUS}")
 *     same => n,Hangup()
 */

// 1. Locate and boot framework environment
\$autoloadCandidates = [
    dirname(__DIR__, 2) . '/bootstrap/autoload.php',
    dirname(__DIR__) . '/bootstrap/autoload.php',
    dirname(__DIR__, 2) . '/web/bootstrap/autoload.php',
];

foreach (\$autoloadCandidates as \$candidate) {
    if (file_exists(\$candidate)) {
        require_once \$candidate;
        break;
    }
}

require_once __DIR__ . '/CAGI.php';

// 2. Initialize AGI Client
\$agi = new CAGI();

// 3. Resolve Channel Session Identifiers
\$channelVar = \$agi->get_variable('CHANNEL');
\$channel    = !empty(\$channelVar['data']) ? \$channelVar['data'] : (\$agi->request['agi_channel'] ?? 'UNKNOWN');

\$aniVar = \$agi->get_variable('CALLERID(num)');
\$ani    = !empty(\$aniVar['data']) ? \$aniVar['data'] : (\$agi->request['agi_callerid'] ?? 'UNKNOWN');

\$dnisVar = \$agi->get_variable('EXTEN');
\$dnis    = !empty(\$dnisVar['data']) ? \$dnisVar['data'] : (\$agi->request['agi_extension'] ?? 'UNKNOWN');

\$uniqueIdVar = \$agi->get_variable('UNIQUEID');
\$uniqueId    = !empty(\$uniqueIdVar['data']) ? \$uniqueIdVar['data'] : (\$agi->request['agi_uniqueid'] ?? uniqid('call_'));

// 4. Record Call Start in AstDB if not already set
\$startVar = \$agi->get_variable('START_DATETIME');
if (empty(\$startVar['data'])) {
    \$startTime = (string)time();
    \$agi->set_variable('START_DATETIME', \$startTime);
    \$agi->database_put('call/' . \$uniqueId, 'start', \$startTime);
}

\$agi->verbose("[Astereal AGI: {$cleanName}] Started for Channel {\$channel} (ANI: {\$ani}, DNIS: {\$dnis})", 2);

try {
    // -------------------------------------------------------------
    // Place your custom telephony business logic here
    // e.g., CRM API lookups, IVR routing choices, database queries
    // -------------------------------------------------------------

    // Set channel variable response back to Asterisk dialplan
    \$agi->set_variable('AGI_{$cleanName}_STATUS', 'SUCCESS');
    \$agi->verbose("[Astereal AGI: {$cleanName}] Completed successfully", 2);

} catch (Throwable \$e) {
    \$agi->verbose("[Astereal AGI: {$cleanName}] ERROR: " . \$e->getMessage(), 1);
    \$agi->set_variable('AGI_{$cleanName}_STATUS', 'ERROR');
    \$agi->set_variable('AGI_ERROR_MSG', \$e->getMessage());
}
exit(0);

PHP;

        file_put_contents($targetPath, $content);
        @chmod($targetPath, 0755);

        $this->printSuccess("AGI Script created successfully: app/agi/{$fileName}");
        echo "  \033[36mDialplan snippet:\033[0m\n";
        echo "    same => n,AGI({$fileName})\n";
        echo "    same => n,NoOp(\"AGI Status: \${AGI_{$cleanName}_STATUS}\")\n\n";
    }

    /**
     * Scaffold a configuration file in app/config/<name>.php
     */
    protected function createConfig(?string $name): void
    {
        if (empty($name)) {
            $this->printError("Missing config name. Usage: php aster create:config <name>");
            return;
        }

        $cleanName = strtolower(trim($name));
        if (str_ends_with($cleanName, '.php')) {
            $cleanName = substr($cleanName, 0, -4);
        }
        $cleanName = preg_replace('/[^a-z0-9_]/', '_', $cleanName);

        $fileName = $cleanName . '.php';
        $targetDir = $this->basePath . '/app/config';
        $targetPath = $targetDir . '/' . $fileName;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        if (file_exists($targetPath)) {
            $this->printError("Config file already exists: app/config/{$fileName}");
            return;
        }

        $title = ucfirst(str_replace('_', ' ', $cleanName));
        $envPrefix = strtoupper($cleanName);

        $content = <<<PHP
<?php

declare(strict_types=1);

/**
 * Astereal Application Configuration - {$title}
 *
 * Access via standard PHP config loading or environment variables.
 */
return [
    'enabled' => filter_var(getenv('{$envPrefix}_ENABLED') ?: true, FILTER_VALIDATE_BOOLEAN),

    // Add your settings below:
    'default' => getenv('{$envPrefix}_DEFAULT') ?: 'standard',
    'options' => [
        'timeout' => (int)(getenv('{$envPrefix}_TIMEOUT') ?: 30),
        'retries' => (int)(getenv('{$envPrefix}_RETRIES') ?: 3),
    ],
];

PHP;

        file_put_contents($targetPath, $content);
        $this->printSuccess("Configuration file created: app/config/{$fileName}");
    }

    /**
     * Scaffold a database migration in web/database/migrations/<num>_<name>.php
     */
    protected function createMigration(?string $name): void
    {
        if (empty($name)) {
            $this->printError("Missing migration name. Usage: php aster create:migration <name>");
            return;
        }

        $cleanName = strtolower(trim($name));
        if (str_ends_with($cleanName, '.php')) {
            $cleanName = substr($cleanName, 0, -4);
        }
        $cleanName = preg_replace('/[^a-z0-9_]/', '_', $cleanName);

        $migrationsDir = $this->basePath . '/web/database/migrations';
        if (!is_dir($migrationsDir)) {
            mkdir($migrationsDir, 0775, true);
        }

        // Determine next sequence number (e.g. 003)
        $existing = glob($migrationsDir . '/*.php');
        $highest = 0;
        foreach ($existing as $file) {
            $base = basename($file);
            if (preg_match('/^(\d+)_/', $base, $matches)) {
                $num = (int)$matches[1];
                if ($num > $highest) {
                    $highest = $num;
                }
            }
        }

        $nextNum = sprintf('%03d', $highest + 1);
        $fileName = "{$nextNum}_{$cleanName}.php";
        $targetPath = $migrationsDir . '/' . $fileName;

        if (file_exists($targetPath)) {
            $this->printError("Migration already exists: web/database/migrations/{$fileName}");
            return;
        }

        // Infer table name if name matches create_XXX_table
        $tableName = 'my_table';
        if (preg_match('/^create_(.+)_table$/', $cleanName, $m)) {
            $tableName = $m[1];
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

return new class {
    public function up(PDO \$pdo): void
    {
        \$driver = \$pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        \$autoInc = \$driver === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';

        \$pdo->exec("
            CREATE TABLE IF NOT EXISTS {$tableName} (
                id {\$autoInc},
                name VARCHAR(150) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function down(PDO \$pdo): void
    {
        \$pdo->exec("DROP TABLE IF EXISTS {$tableName}");
    }
};

PHP;

        file_put_contents($targetPath, $content);
        $this->printSuccess("Migration created: web/database/migrations/{$fileName}");
        echo "  \033[36mRun:\033[0m php aster migrate\n\n";
    }

    /**
     * Scaffold a model in web/app/Models/<Name>.php
     */
    protected function createModel(?string $name): void
    {
        if (empty($name)) {
            $this->printError("Missing model name. Usage: php aster create:model <Name>");
            return;
        }

        $className = ucfirst($this->toPascalCase($name));
        $fileName = $className . '.php';
        $targetDir = $this->basePath . '/web/app/Models';
        $targetPath = $targetDir . '/' . $fileName;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        if (file_exists($targetPath)) {
            $this->printError("Model already exists: web/app/Models/{$fileName}");
            return;
        }

        $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace Astereal\Web\Models;

use PDO;

class {$className}
{
    protected static string \$table = '{$tableName}';

    /**
     * Retrieve all records
     */
    public static function all(): array
    {
        \$pdo = Database::getConnection();
        \$stmt = \$pdo->query("SELECT * FROM " . static::\$table . " ORDER BY id DESC");
        return \$stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Find record by ID
     */
    public static function findById(int \$id): ?array
    {
        \$pdo = Database::getConnection();
        \$stmt = \$pdo->prepare("SELECT * FROM " . static::\$table . " WHERE id = :id LIMIT 1");
        \$stmt->execute([':id' => \$id]);
        \$record = \$stmt->fetch(PDO::FETCH_ASSOC);

        return \$record ?: null;
    }

    /**
     * Create a new record
     */
    public static function create(array \$data): ?int
    {
        \$pdo = Database::getConnection();
        \$fields = array_keys(\$data);
        \$placeholders = array_map(fn(\$f) => ':' . \$f, \$fields);

        \$sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            static::\$table,
            implode(', ', \$fields),
            implode(', ', \$placeholders)
        );

        \$stmt = \$pdo->prepare(\$sql);
        \$params = [];
        foreach (\$data as \$key => \$value) {
            \$params[':' . \$key] = \$value;
        }

        return \$stmt->execute(\$params) ? (int)\$pdo->lastInsertId() : null;
    }

    /**
     * Delete record by ID
     */
    public static function delete(int \$id): bool
    {
        \$pdo = Database::getConnection();
        \$stmt = \$pdo->prepare("DELETE FROM " . static::\$table . " WHERE id = :id");
        return \$stmt->execute([':id' => \$id]);
    }
}

PHP;

        file_put_contents($targetPath, $content);
        $this->printSuccess("Model created: web/app/Models/{$fileName}");
    }

    /**
     * Scaffold a controller in web/app/Controllers/<Web|Api>/<Name>Controller.php
     */
    protected function createController(?string $name, array $options): void
    {
        if (empty($name)) {
            $this->printError("Missing controller name. Usage: php aster create:controller <Name> [--api|--web]");
            return;
        }

        $className = ucfirst($this->toPascalCase($name));
        if (!str_ends_with($className, 'Controller')) {
            $className .= 'Controller';
        }

        $isApi = in_array('--api', $options, true) || str_starts_with($name, 'Api/') || str_contains($className, 'Api');
        $subDir = $isApi ? 'Api' : 'Web';
        $namespace = "Astereal\\Web\\Controllers\\{$subDir}";

        $targetDir = $this->basePath . "/web/app/Controllers/{$subDir}";
        $targetPath = $targetDir . '/' . $className . '.php';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        if (file_exists($targetPath)) {
            $this->printError("Controller already exists: web/app/Controllers/{$subDir}/{$className}.php");
            return;
        }

        if ($isApi) {
            $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use Astereal\Web\Support\Request;
use Astereal\Web\Support\Response;

class {$className}
{
    /**
     * GET /api/v1/resource
     */
    public function index(Request \$request): void
    {
        Response::json([
            'status' => 'success',
            'data'   => [],
        ]);
    }

    /**
     * GET /api/v1/resource/{id}
     */
    public function show(Request \$request, array \$params): void
    {
        \$id = (int)(\$params['id'] ?? 0);

        Response::json([
            'status' => 'success',
            'data'   => ['id' => \$id],
        ]);
    }

    /**
     * POST /api/v1/resource
     */
    public function store(Request \$request): void
    {
        \$payload = \$request->json();

        Response::json([
            'status'  => 'success',
            'message' => 'Resource created',
            'data'    => \$payload,
        ], 201);
    }
}

PHP;
        } else {
            $viewSlug = strtolower(str_replace('Controller', '', $className));
            $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use Astereal\Web\Support\Request;
use Astereal\Web\Support\Response;

class {$className}
{
    public function index(Request \$request): void
    {
        Response::view('{$viewSlug}.index', [
            'title' => '{$className}',
        ]);
    }

    public function show(Request \$request, array \$params): void
    {
        \$id = (int)(\$params['id'] ?? 0);

        Response::view('{$viewSlug}.show', [
            'id' => \$id,
        ]);
    }
}

PHP;
        }

        file_put_contents($targetPath, $content);
        $this->printSuccess("Controller created: web/app/Controllers/{$subDir}/{$className}.php");
        echo "  \033[36mRegister route in:\033[0m web/routes/" . ($isApi ? 'api.php' : 'web.php') . "\n\n";
    }

    /**
     * Scaffold a middleware in web/app/Middleware/<Name>Middleware.php
     */
    protected function createMiddleware(?string $name): void
    {
        if (empty($name)) {
            $this->printError("Missing middleware name. Usage: php aster create:middleware <Name>");
            return;
        }

        $className = ucfirst($this->toPascalCase($name));
        if (!str_ends_with($className, 'Middleware')) {
            $className .= 'Middleware';
        }

        $fileName = $className . '.php';
        $targetDir = $this->basePath . '/web/app/Middleware';
        $targetPath = $targetDir . '/' . $fileName;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        if (file_exists($targetPath)) {
            $this->printError("Middleware already exists: web/app/Middleware/{$fileName}");
            return;
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace Astereal\Web\Middleware;

use Astereal\Web\Support\Request;
use Astereal\Web\Support\Response;

class {$className}
{
    /**
     * Handle an incoming HTTP or API request
     */
    public function handle(Request \$request): void
    {
        // Add your inspection, validation, or authentication guard here:
        // if (\$forbidden) {
        //     Response::error('Unauthorized access', 403);
        // }
    }
}

PHP;

        file_put_contents($targetPath, $content);
        $this->printSuccess("Middleware created: web/app/Middleware/{$fileName}");
    }

    protected function toPascalCase(string $string): string
    {
        $clean = preg_replace('/[^a-zA-Z0-9]/', ' ', $string);
        $words = ucwords($clean);
        return str_replace(' ', '', $words);
    }

    protected function printSuccess(string $message): void
    {
        echo "  \033[32m✅ {$message}\033[0m\n\n";
    }

    protected function printError(string $message): void
    {
        echo "  \033[31m❌ {$message}\033[0m\n\n";
    }

    protected function help(): void
    {
        $green  = "\033[32m";
        $cyan   = "\033[36m";
        $yellow = "\033[33m";
        $reset  = "\033[0m";

        echo "\n{$cyan}Astereal Scaffolding & Code Generator{$reset}\n";
        echo "Usage: php aster create:<type> <name> [options]\n\n";
        echo "{$yellow}Available Generators:{$reset}\n";
        printf("  %-32s %s\n", "{$green}create:agi <Name>{$reset}", "Scaffold a new AGI script in app/agi/<Name>.php");
        printf("  %-32s %s\n", "{$green}create:config <name>{$reset}", "Create a new configuration array in app/config/<name>.php");
        printf("  %-32s %s\n", "{$green}create:migration <name>{$reset}", "Create a numbered migration in web/database/migrations/");
        printf("  %-32s %s\n", "{$green}create:model <Name>{$reset}", "Create a PDO database model in web/app/Models/");
        printf("  %-32s %s\n", "{$green}create:controller <Name>{$reset}", "Create a Controller in web/app/Controllers/ [--api|--web]");
        printf("  %-32s %s\n", "{$green}create:middleware <Name>{$reset}", "Create an HTTP Middleware in web/app/Middleware/\n\n");
    }
}
