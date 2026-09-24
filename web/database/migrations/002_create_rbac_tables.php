<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $autoInc = $driver === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';

        // 1. Roles table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS roles (
                id {$autoInc},
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(50) NOT NULL UNIQUE,
                description VARCHAR(255),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // 2. Permissions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS permissions (
                id {$autoInc},
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(50) NOT NULL UNIQUE,
                category VARCHAR(50) NOT NULL DEFAULT 'general',
                description VARCHAR(255),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // 3. Role Permissions Pivot
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS role_permissions (
                role_id INT NOT NULL,
                permission_id INT NOT NULL,
                PRIMARY KEY (role_id, permission_id)
            )
        ");

        // 4. Teams table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS teams (
                id {$autoInc},
                name VARCHAR(100) NOT NULL,
                description VARCHAR(255),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // 5. User Teams Pivot
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_teams (
                user_id INT NOT NULL,
                team_id INT NOT NULL,
                is_primary TINYINT(1) DEFAULT 0,
                PRIMARY KEY (user_id, team_id)
            )
        ");

        // 6. User Roles Pivot
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_roles (
                user_id INT NOT NULL,
                role_id INT NOT NULL,
                PRIMARY KEY (user_id, role_id)
            )
        ");

        // Seed Roles
        $rolesCount = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
        if ((int)$rolesCount === 0) {
            $stmt = $pdo->prepare("INSERT INTO roles (name, slug, description) VALUES (:name, :slug, :desc)");
            $defaultRoles = [
                ['Super Administrator', 'superadmin', 'Unrestricted access to all telephony operations, security, and system configuration'],
                ['Administrator', 'admin', 'Full administrative control over telephony, users, teams, and extensions'],
                ['Team Supervisor', 'supervisor', 'Team-scoped access to monitor calls, view CDR, and listen to recordings'],
                ['Telephony Agent', 'agent', 'Frontline softphone endpoint access; restricted from administrative web portal'],
            ];
            foreach ($defaultRoles as [$name, $slug, $desc]) {
                $stmt->execute([':name' => $name, ':slug' => $slug, ':desc' => $desc]);
            }
        }

        // Seed Permissions
        $permCount = $pdo->query("SELECT COUNT(*) FROM permissions")->fetchColumn();
        if ((int)$permCount === 0) {
            $stmt = $pdo->prepare("INSERT INTO permissions (name, slug, category, description) VALUES (:name, :slug, :cat, :desc)");
            $defaultPermissions = [
                // User Management
                ['View Users', 'view_users', 'users', 'Can view the list and profiles of users'],
                ['Create Users', 'create_users', 'users', 'Can create new users and telephony agents'],
                ['Edit Users', 'edit_users', 'users', 'Can edit user details and change passwords'],
                ['Delete Users', 'delete_users', 'users', 'Can deactivate or delete user accounts'],

                // Roles & Permissions
                ['Manage Roles', 'manage_roles', 'rbac', 'Can create roles and assign permissions'],
                ['Manage Permissions', 'manage_permissions', 'rbac', 'Can manage system permissions'],

                // Teams
                ['View Teams', 'view_teams', 'teams', 'Can view team structures and rosters'],
                ['Manage Teams', 'manage_teams', 'teams', 'Can create and modify teams'],

                // Telephony Operations & CDR
                ['View CDR Logs', 'view_cdr', 'telephony', 'Can search and view call detail records'],
                ['Listen Recordings', 'listen_recordings', 'telephony', 'Can listen to audio call recordings'],
                ['Download Recordings', 'download_recordings', 'telephony', 'Can download audio call recordings'],
                ['Delete Recordings', 'delete_recordings', 'telephony', 'Can purge call recordings'],
                ['Manage Extensions', 'manage_extensions', 'telephony', 'Can create and modify SIP extensions'],
                ['Manage Dialplan', 'manage_dialplan', 'telephony', 'Can configure dialplan routing rules'],
                ['Manage Trunks', 'manage_trunks', 'telephony', 'Can manage SIP carrier trunks and gateways'],

                // System & Admin
                ['View Dashboard', 'view_dashboard', 'system', 'Can access the central developer console dashboard'],
                ['Manage Settings', 'manage_settings', 'system', 'Can modify framework and telephony settings'],
                ['Publish Files', 'publish_files', 'system', 'Can execute telephony and web publishing'],
                ['View System Logs', 'view_system_logs', 'system', 'Can view Asterisk, AGI, and web audit logs'],
            ];

            foreach ($defaultPermissions as [$name, $slug, $cat, $desc]) {
                $stmt->execute([':name' => $name, ':slug' => $slug, ':cat' => $cat, ':desc' => $desc]);
            }
        }

        // Attach permissions to roles
        $rpCount = $pdo->query("SELECT COUNT(*) FROM role_permissions")->fetchColumn();
        if ((int)$rpCount === 0) {
            $roles = $pdo->query("SELECT id, slug FROM roles")->fetchAll(PDO::FETCH_KEY_PAIR);
            $perms = $pdo->query("SELECT slug, id FROM permissions")->fetchAll(PDO::FETCH_KEY_PAIR);

            $stmt = $pdo->prepare("INSERT OR IGNORE INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");
            if ($driver !== 'sqlite') {
                $stmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");
            }

            // Superadmin: all permissions
            if (isset($roles['superadmin'])) {
                foreach ($perms as $permId) {
                    $stmt->execute([':role_id' => $roles['superadmin'], ':permission_id' => $permId]);
                }
            }

            // Admin: most permissions except manage_roles/delete_recordings
            if (isset($roles['admin'])) {
                foreach ($perms as $permSlug => $permId) {
                    if (!in_array($permSlug, ['manage_roles', 'delete_recordings'], true)) {
                        $stmt->execute([':role_id' => $roles['admin'], ':permission_id' => $permId]);
                    }
                }
            }

            // Supervisor: monitoring, viewing, listening
            if (isset($roles['supervisor'])) {
                $supPerms = ['view_dashboard', 'view_users', 'view_teams', 'view_cdr', 'listen_recordings', 'download_recordings'];
                foreach ($supPerms as $slug) {
                    if (isset($perms[$slug])) {
                        $stmt->execute([':role_id' => $roles['supervisor'], ':permission_id' => $perms[$slug]]);
                    }
                }
            }

            // Agent: basic view
            if (isset($roles['agent'])) {
                $agentPerms = ['view_cdr'];
                foreach ($agentPerms as $slug) {
                    if (isset($perms[$slug])) {
                        $stmt->execute([':role_id' => $roles['agent'], ':permission_id' => $perms[$slug]]);
                    }
                }
            }
        }

        // Link existing admin user to superadmin role
        $adminUser = $pdo->query("SELECT id FROM users WHERE username = 'admin' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $superadminRole = $pdo->query("SELECT id FROM roles WHERE slug = 'superadmin' LIMIT 1")->fetch(PDO::FETCH_ASSOC);

        if ($adminUser && $superadminRole) {
            $userRoleExists = $pdo->query("SELECT COUNT(*) FROM user_roles WHERE user_id = {$adminUser['id']}")->fetchColumn();
            if ((int)$userRoleExists === 0) {
                $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)");
                $stmt->execute([
                    ':user_id' => $adminUser['id'],
                    ':role_id' => $superadminRole['id'],
                ]);
            }
        }
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS user_roles");
        $pdo->exec("DROP TABLE IF EXISTS user_teams");
        $pdo->exec("DROP TABLE IF EXISTS teams");
        $pdo->exec("DROP TABLE IF EXISTS role_permissions");
        $pdo->exec("DROP TABLE IF EXISTS permissions");
        $pdo->exec("DROP TABLE IF EXISTS roles");
    }
};
