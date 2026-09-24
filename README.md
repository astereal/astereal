# ASTEREAL

**Astereal** is an Asterisk framework built with PHP (inspired by Laravel's elegance and developer ergonomics), designed to make telephony application development seamless, expressive, and modular.

`“The ethereal way to build Asterisk applications.”`

*This project is currently under active development.*

---

## System Requirements

Before creating an Astereal project, ensure your environment meets the following requirements:

| Component | Minimum Version | Notes |
| :--- | :--- | :--- |
| **Operating System** | Red Hat Enterprise Linux (RHEL) 9/10+, Rocky Linux 9/10+, or AlmaLinux 9/10+ | Linux x86_64 / aarch64 |
| **PHP** | 8.1 or higher (PHP 8.2+ recommended) | CLI & Web |
| **PHP Extensions** | `pdo_sqlite`, `curl`, `json`, `mbstring`, `openssl`, `posix` | `pdo_mysql` optional |
| **Asterisk** | Asterisk 21+ or 22 LTS | With `res_pjsip` & `res_agi` enabled |
| **Web Server** | Apache HTTPD 2.4+ (with `mod_rewrite`) or Nginx | For developer console & webhooks |
| **Database** | SQLite 3 (default) or MariaDB/MySQL | SQLite requires zero configuration |
| **Package Manager** | Composer 2.x | For installation & dependency management |
| **Privileges** | `sudo` / `root` access | Required for `php aster publish` to system paths |

---

## Installation

Create a new Astereal project using Composer:

```bash
composer create-project astereal/astereal your-project-name
cd your-project-name
```

During project creation, initial admin credentials for the developer console will be automatically generated and displayed in your terminal.

---

## Project Structure

```
ASTEREAL/
│
├── app/                      # Telephony application source
│   ├── agi/                  # PHP AGI scripts & API integrations
│   ├── config/               # Astereal framework configuration
│   ├── dialplan/             # Modular dialplan contexts (extensions_*.conf)
│   ├── httpd/                # Web server virtual host definitions
│   ├── sip/                  # PJSIP endpoint & trunk configurations
│   └── sounds/               # Custom telephony audio prompts
│
├── bootstrap/                # CLI Kernel & Command registrations
│   └── Commands/             # Built-in aster CLI commands
│
├── modules/                  # Framework core engine modules
│   ├── Dialplan/             # Dialplan parser & reloader
│   ├── PJSIP/                # PJSIP endpoint manager
│   └── Publisher/            # Application file deployment engine
│
├── web/                      # Developer Console & Web Tier (Native PHP)
│   ├── app/                  # Controllers, Models, Middleware, Support
│   ├── database/             # SQLite database & migrations/
│   ├── public/               # Web document root (index.php & assets)
│   ├── routes/               # Web & API route definitions
│   └── views/                # Cosmic-themed views & layouts
│
├── settings/                 # Publisher target mappings
│   └── publisher.php
│
├── aster                     # Astereal CLI binary entry point
└── composer.json
```

---

## Astereal CLI (`php aster`)

Astereal includes the `aster` CLI tool to manage telephony services, security, migrations, and deployment:

```bash
Astereal CLI
Usage:
  php aster [command][:action]

Available commands:
  auth      Manage web authentication & admin credentials (setup, reset, create, credentials)
  core      Manage Asterisk core service (status, start, stop, restart)
  dialplan  Manage dialplan routines and reloads
  migrate   Database migrations (run, status, rollback, fresh)
  pjsip     Manage PJSIP endpoints and module reloads
  publish   Publish application files to system paths (/etc/asterisk, /var/lib/asterisk/agi-bin, /var/www/html, etc.)
  quote     Display an inspirational quote

Use 'php aster [command]:help' for details.
```

---

## Deploying Your Application

Publish your telephony dialplans, AGI scripts, SIP configurations, and web definitions to system paths in one command:

```bash
php aster publish
```

This will automatically:
1. Sync `app/agi/` to `/var/lib/asterisk/agi-bin/`
2. Sync `app/dialplan/` and `app/sip/` to `/etc/asterisk/`
3. Sync `app/sounds/` to `/var/lib/asterisk/sounds/`
4. Sync `app/httpd/` to `/etc/httpd/conf.d/`
5. Sync `web/` to `/var/www/html/<app-name>`
6. Trigger seamless Asterisk dialplan and PJSIP reloads.

---

## Creator and Developer

**Jerome Soriano** - Laravel and Asterisk Telephony Developer