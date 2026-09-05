<?php

namespace Bootstrap\Commands;

use PDO;

class AuthCommand
{
    public string $name = 'auth';
    public string $description = 'Manage web authentication and admin credentials (setup, reset, create)';

    protected string $basePath;

    public function __construct()
    {
        $this->basePath = dirname(__DIR__, 2);
        $this->registerWebAutoloader();
    }

    public function handle(array $args): void
    {
        $action = strtolower($args[0] ?? 'setup');

        switch ($action) {
            case 'setup':
                $this->setup();
                break;

            case 'reset':
                $newPassword = $args[1] ?? null;
                $this->resetPassword($newPassword);
                break;

            case 'credentials':
            case 'show':
                $this->showCredentials();
                break;

            case 'create':
                $username = $args[1] ?? null;
                $password = $args[2] ?? null;
                $this->createUser($username, $password);
                break;

            case 'help':
            default:
                $this->help();
                break;
        }
    }

    /**
     * Setup admin credentials during project initialization (post-create-project)
     */
    protected function setup(): void
    {
        $db = $this->getPdo();
        if (!$db) {
            return;
        }

        // Check if admin user already exists
        $stmt = $db->prepare("SELECT id, username FROM users WHERE username = 'admin' LIMIT 1");
        $stmt->execute();
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $this->printInfo("Admin account is already provisioned.");
            echo "  Run 'php aster auth:reset' if you need to generate a new password.\n\n";
            return;
        }

        // Generate cryptographically secure random password
        $password = $this->generateSecurePassword();
        $hash     = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $db->prepare("
            INSERT INTO users (username, password, name, email, role)
            VALUES ('admin', :password, 'Astereal Administrator', 'admin@astereal.local', 'admin')
        ");
        $stmt->execute([':password' => $hash]);

        $this->displayCredentialsBanner('admin', $password, true);
    }

    /**
     * Reset admin password to a new random or specified password
     */
    protected function resetPassword(?string $customPassword = null): void
    {
        $db = $this->getPdo();
        if (!$db) {
            return;
        }

        $password = $customPassword ?: $this->generateSecurePassword();
        $hash     = password_hash($password, PASSWORD_BCRYPT);

        // Check if admin exists; if not, create
        $stmt = $db->prepare("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
        $stmt->execute();
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $update = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
            $update->execute([':password' => $hash, ':id' => $existing['id']]);
        } else {
            $insert = $db->prepare("
                INSERT INTO users (username, password, name, email, role)
                VALUES ('admin', :password, 'Astereal Administrator', 'admin@astereal.local', 'admin')
            ");
            $insert->execute([':password' => $hash]);
        }

        $this->displayCredentialsBanner('admin', $password, false);
    }

    /**
     * Show current admin status
     */
    protected function showCredentials(): void
    {
        $db = $this->getPdo();
        if (!$db) {
            return;
        }

        $stmt = $db->query("SELECT id, username, name, email, role, created_at FROM users ORDER BY id ASC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cyan  = "\033[36m";
        $green = "\033[32m";
        $bold  = "\033[1m";
        $reset = "\033[0m";

        echo "\n{$cyan}┌─────────────────────────────────────────────────────────────┐{$reset}\n";
        echo "│ {$bold}ASTEREAL WEB USERS & AUTHENTICATION{$reset}                       │\n";
        echo "{$cyan}└─────────────────────────────────────────────────────────────┘{$reset}\n\n";

        if (empty($users)) {
            echo "  No users found in database.\n";
            echo "  Run 'php aster auth:setup' to create default admin.\n\n";
            return;
        }

        foreach ($users as $u) {
            echo "  {$green}●{$reset} {$bold}{$u['username']}{$reset} ({$u['role']}) - {$u['email']}\n";
        }

        echo "\n  Password hashes are stored securely with BCRYPT.\n";
        echo "  To reset a password, run: {$cyan}php aster auth:reset [new_password]{$reset}\n\n";
    }

    /**
     * Create a new user account
     */
    protected function createUser(?string $username, ?string $customPassword = null): void
    {
        if (empty($username)) {
            echo "\033[31mError: Username is required.\033[0m Usage: php aster auth:create <username> [password]\n";
            return;
        }

        $db = $this->getPdo();
        if (!$db) {
            return;
        }

        $password = $customPassword ?: $this->generateSecurePassword();
        $hash     = password_hash($password, PASSWORD_BCRYPT);

        // Check if user exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetch()) {
            echo "\033[31mError: User [{$username}] already exists.\033[0m\n";
            return;
        }

        $insert = $db->prepare("
            INSERT INTO users (username, password, name, email, role)
            VALUES (:username, :password, :name, :email, 'user')
        ");
        $insert->execute([
            ':username' => $username,
            ':password' => $hash,
            ':name'     => ucfirst($username),
            ':email'    => "{$username}@astereal.local",
        ]);

        $this->displayCredentialsBanner($username, $password, false);
    }

    /**
     * Displays a formatted cosmic credentials banner in the CLI terminal
     */
    protected function displayCredentialsBanner(string $username, string $password, bool $isInitialSetup): void
    {
        $cyan   = "\033[38;2;0;217;245m";
        $mint   = "\033[38;2;0;245;160m";
        $white  = "\033[1;37m";
        $yellow = "\033[33m";
        $reset  = "\033[0m";

        $serverIp = $this->resolveServerIp();

        echo "\n";
        echo "{$cyan}═══════════════════════════════════════════════════════════════{$reset}\n";
        echo "  {$mint}★ ASTEREAL WEB ADMINISTRATION CREDENTIALS{$reset}\n";
        echo "{$cyan}═══════════════════════════════════════════════════════════════{$reset}\n\n";

        echo "  Web Login URL : {$white}http://{$serverIp}/login{$reset}\n";
        echo "  Username      : {$mint}{$username}{$reset}\n";
        echo "  Password      : {$white}{$password}{$reset}\n\n";

        echo "{$cyan}───────────────────────────────────────────────────────────────{$reset}\n";
        echo "  {$yellow}⚠️  SECURITY NOTICE:{$reset}\n";
        echo "  Please copy and securely store this password.\n";
        echo "  It is encrypted in the database and only displayed once.\n";
        echo "  You can regenerate it anytime with: {$cyan}php aster auth:reset{$reset}\n";
        echo "{$cyan}═══════════════════════════════════════════════════════════════{$reset}\n\n";
    }

    /**
     * Generate a memorable yet secure random password (e.g. Aste-8f2a-b7e1)
     */
    protected function generateSecurePassword(): string
    {
        $bytes = bin2hex(random_bytes(4)); // 8 chars
        return 'Aste-' . substr($bytes, 0, 4) . '-' . substr($bytes, 4, 4);
    }

    /**
     * Resolve the server's primary IP address for the display URL
     */
    protected function resolveServerIp(): string
    {
        $ip = '127.0.0.1';

        if (PHP_OS_FAMILY !== 'Windows') {
            $hostnameIp = exec("hostname -I 2>/dev/null | awk '{print $1}'");
            if (!empty($hostnameIp) && filter_var($hostnameIp, FILTER_VALIDATE_IP)) {
                return $hostnameIp;
            }
        }

        return $ip;
    }

    /**
     * Get or initialize PDO connection to database
     */
    protected function getPdo(): ?PDO
    {
        try {
            // Use Astereal\Web\Models\Database if available
            if (class_exists('Astereal\\Web\\Models\\Database')) {
                return \Astereal\Web\Models\Database::getConnection();
            }

            // Fallback direct SQLite initialization
            $configFile = $this->basePath . '/web/config/database.php';
            if (!file_exists($configFile)) {
                echo "\033[31mError: web/config/database.php not found.\033[0m\n";
                return null;
            }

            $config = require $configFile;
            $dbFile = $config['sqlite']['database'] ?? ($this->basePath . '/web/database/astereal.sqlite');
            $dir = dirname($dbFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $pdo = new PDO("sqlite:{$dbFile}");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Ensure users table exists
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    name VARCHAR(100) DEFAULT 'Admin',
                    email VARCHAR(100),
                    role VARCHAR(20) DEFAULT 'admin',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");

            return $pdo;

        } catch (\Throwable $e) {
            echo "\033[31mDatabase error: " . $e->getMessage() . "\033[0m\n";
            return null;
        }
    }

    protected function registerWebAutoloader(): void
    {
        spl_autoload_register(function (string $class) {
            $prefix = 'Astereal\\Web\\';
            $baseDir = $this->basePath . '/web/app/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }

    protected function printInfo(string $message): void
    {
        echo "\033[32m●\033[0m {$message}\n";
    }

    protected function help(): void
    {
        echo "Astereal Authentication CLI\n";
        echo "Usage:\n";
        echo "  php aster auth:setup             Provision initial admin account with random password\n";
        echo "  php aster auth:reset [password]  Reset admin password to a new random (or custom) password\n";
        echo "  php aster auth:credentials       List all provisioned users and roles\n";
        echo "  php aster auth:create <user>     Create a new user account\n\n";
    }
}
