# Astereal Framework &bull; CLI Commands Reference Guide

Astereal features a unified, native command-line interface (`aster`) alongside an `artisan` alias to manage Asterisk telephony engines, SIP extensions, AGI scaffolding, database migrations, web publishing, and security.

You can invoke commands using either:
```bash
php aster <command>[:action] [arguments] [options]
# Or using the Laravel-familiar alias:
php artisan <command>[:action] [arguments] [options]
```

---

## Table of Contents
1. [Code Scaffolding & Generators (`create` / `make`)](#1-code-scaffolding--generators-create--make)
2. [Web & Telephony Publishing (`publish`)](#2-web--telephony-publishing-publish)
3. [Asterisk Core Service Management (`core`)](#3-asterisk-core-service-management-core)
4. [Authentication & User Management (`auth`)](#4-authentication--user-management-auth)
5. [Database Migrations (`migrate`)](#5-database-migrations-migrate)
6. [Dialplan Operations (`dialplan`)](#6-dialplan-operations-dialplan)
7. [PJSIP Operations (`pjsip`)](#7-pjsip-operations-pjsip)
8. [Developer Utilities (`quote`)](#8-developer-utilities-quote)

---

## 1. Code Scaffolding & Generators (`create` / `make`)

Rapidly scaffold framework components adhering strictly to Astereal standards (pure native PHP 8.1+, PSR-4 autoloading, AstDB CDR duration tracking, and zero bloated dependencies).

Both `php aster create:<type>` and `php aster make:<type>` are supported.

### 1.1 AGI Telephony Scripts (`create:agi`)
Generates an Asterisk Gateway Interface script equipped with framework autoloader, `CAGI` client, session variables (`CHANNEL`, `ANI`, `DNIS`, `UNIQUEID`), AstDB CDR duration start tracking, and try/catch error traps.

```bash
php aster create:agi <Name>
```

- **Target Path**: `app/agi/<Name>.php`
- **Permissions**: Automatically sets executable permissions (`chmod +x`).
- **Example**:
  ```bash
  php aster create:agi GetSchedule
  ```
- **Dialplan Snippet**:
  ```asterisk
  exten => s,1,NoOp(*** Executing GetSchedule AGI ***)
    same => n,AGI(GetSchedule.php)
    same => n,NoOp("AGI Result: ${AGI_GetSchedule_STATUS}")
    same => n,Hangup()
  ```

---

### 1.2 Configuration Files (`create:config`)
Generates a structured PHP configuration array supporting `.env` fallback resolution.

```bash
php aster create:config <name>
```

- **Target Path**: `app/config/<name>.php`
- **Example**:
  ```bash
  php aster create:config schedule
  ```

---

### 1.3 Database Migrations (`create:migration`)
Creates an auto-sequenced migration file supporting both SQLite and MySQL schema execution.

```bash
php aster create:migration <name>
```

- **Target Path**: `web/database/migrations/<sequential_number>_<name>.php`
- **Example**:
  ```bash
  php aster create:migration create_schedules_table
  ```
- **Generated Schema Structure**:
  Automatically provisions auto-incrementing primary keys (`INTEGER PRIMARY KEY AUTOINCREMENT` on SQLite or `INT AUTO_INCREMENT PRIMARY KEY` on MySQL) and timestamps.

---

### 1.4 Native PDO Models (`create:model`)
Generates an active data access model in the `Astereal\Web\Models` namespace with prepared statements.

```bash
php aster create:model <Name>
```

- **Target Path**: `web/app/Models/<Name>.php`
- **Example**:
  ```bash
  php aster create:model Schedule
  ```
- **Built-in Methods**:
  - `Schedule::all()`: Retrieve all records.
  - `Schedule::findById(int $id)`: Find a single record by primary key.
  - `Schedule::create(array $data)`: Insert record with parameter binding.
  - `Schedule::delete(int $id)`: Remove record by primary key.

---

### 1.5 Web & API Controllers (`create:controller`)
Scaffolds a controller for either the web frontend or REST API.

```bash
# Web Controller (HTML view responses)
php aster create:controller <Name> [--web]

# API Controller (JSON responses)
php aster create:controller <Name> --api
```

- **Target Paths**:
  - Web: `web/app/Controllers/Web/<Name>Controller.php`
  - API: `web/app/Controllers/Api/<Name>Controller.php`
- **Examples**:
  ```bash
  php aster create:controller QueueController
  php aster create:controller CallerApiController --api
  ```

---

### 1.6 HTTP Route Middleware (`create:middleware`)
Generates an HTTP request filter or authentication gate.

```bash
php aster create:middleware <Name>
```

- **Target Path**: `web/app/Middleware/<Name>Middleware.php`
- **Example**:
  ```bash
  php aster create:middleware CheckTenant
  ```

---

## 2. Web & Telephony Publishing (`publish`)

The `publish` command syncs your application from your working development directory to Asterisk and Apache system directories in one execution.

```bash
php aster publish
```

### Publishing Map (`settings/publisher.php`):
| Local Source Path | Target System Destination | Description |
| :--- | :--- | :--- |
| `app/agi/` | `/var/lib/asterisk/agi-bin/` | AGI scripts executed by Asterisk dialplan |
| `app/dialplan/` | `/etc/asterisk/` | Asterisk dialplan files (`extensions.conf`, `extensions_*.conf`) |
| `app/sip/` | `/etc/asterisk/` | PJSIP trunk and endpoint configs (`pjsip.conf`) |
| `app/sounds/` | `/var/lib/asterisk/sounds/` | Audio prompts, hold music, and IVR recordings |
| `app/httpd/` | `/etc/httpd/conf.d/` | Apache VirtualHost configuration (`astereal.conf`) |
| `web/` | `/var/www/html/<app-name>/` | Web console, REST API front controller, and assets |

### Automated Post-Publish Actions:
1. Detects if Asterisk is running. If stopped, automatically starts the daemon.
2. Triggers `asterisk -rx "dialplan reload"`.
3. Triggers `asterisk -rx "pjsip reload"`.
4. Adjusts file permissions (`chmod 0755` on AGI scripts, `0666` on SQLite database).

---

## 3. Asterisk Core Service Management (`core`)

Control and inspect the underlying Asterisk PBX daemon without manual systemd commands:

```bash
# Check if Asterisk daemon is online and responsive
php aster core:status

# Start Asterisk service
php aster core:start

# Stop Asterisk service gracefully
php aster core:stop

# Restart Asterisk service
php aster core:restart
```

---

## 4. Authentication & User Management (`auth`)

Manage web console authentication, administrator credentials, and password resets:

### 4.1 Reset Admin Password (`auth:reset`)
Generates a new secure random password, updates the local and published databases, and enforces the initial permanent password change upon next login.

```bash
# Generate random temporary password:
php aster auth:reset

# Or set custom temporary password:
php aster auth:reset MyCustomPass123!
```

### 4.2 Initial Setup (`auth:setup`)
Provisions the initial superadmin account and initializes `pjsip.conf` from example template if missing. Run automatically during `composer create-project`.

```bash
php aster auth:setup
```

### 4.3 List Users & Roles (`auth:credentials`)
Displays all provisioned database users, emails, and assigned RBAC roles.

```bash
php aster auth:credentials
```

### 4.4 Create New User (`auth:create`)
Creates an additional user account directly from the terminal.

```bash
php aster auth:create <username> [password]
```

---

## 5. Database Migrations (`migrate`)

Astereal includes an integrated, zero-dependency migration engine supporting both SQLite and MySQL:

```bash
# Run all pending migrations
php aster migrate

# View migration history and status
php aster migrate:status

# Roll back the last batch of applied migrations
php aster migrate:rollback

# Drop all tables and re-run all migrations from scratch
php aster migrate:fresh
```

---

## 6. Dialplan Operations (`dialplan`)

Reload Asterisk dialplans on demand:

```bash
php aster dialplan:reload
```
Executes `asterisk -rx "dialplan reload"` and reports the response status.

---

## 7. PJSIP Operations (`pjsip`)

Reload Asterisk PJSIP endpoints, transports, and authentication modules:

```bash
php aster pjsip:reload
```
Executes `asterisk -rx "pjsip reload"` and verifies active SIP configurations.

---

## 8. Developer Utilities (`quote`)

Display an inspirational quote from legendary computer scientists and software engineers:

```bash
php aster quote
```

---

## Quick Reference Summary

| Command | Action | Description |
| :--- | :--- | :--- |
| `php aster publish` | Publish | Deploy telephony, AGI, Apache, and web files to system paths |
| `php aster core:status` | Status | Check if Asterisk daemon is active |
| `php aster core:restart` | Restart | Restart Asterisk daemon |
| `php aster create:agi <Name>` | Scaffold | Generate telephony AGI script in `app/agi/` |
| `php aster create:config <name>` | Scaffold | Generate config file in `app/config/` |
| `php aster create:migration <name>` | Scaffold | Generate database migration |
| `php aster create:model <Name>` | Scaffold | Generate PDO database model |
| `php aster create:controller <Name>` | Scaffold | Generate Web or API controller |
| `php aster create:middleware <Name>` | Scaffold | Generate route middleware |
| `php aster migrate` | Migrate | Execute pending database migrations |
| `php aster migrate:fresh` | Reset DB | Drop all tables and re-run migrations from scratch |
| `php aster auth:reset` | Reset Auth | Generate temporary admin password |
| `php aster auth:credentials` | List Users | Display all registered web users |
| `php aster dialplan:reload` | Reload | Reload Asterisk dialplan rules |
| `php aster pjsip:reload` | Reload | Reload PJSIP endpoints |
