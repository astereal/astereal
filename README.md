# ASTEREAL

**Astereal** is an Asterisk framework built with PHP, designed to make telephony development seamless and expressive. Inspired by the word ethereal, it aims to bring elegance and simplicity to the complex world of Asterisk automation.

`“The ethereal way to build Asterisk applications.”`

***This project is currently under development***

## Installation

You must install composer and PHP minimum of 8.1 version. Below is the command to create project:
```
composer create-project astereal/astereal your-project-name
```

## Structure

```
ASTEREAL/
│
├─ app/
│   ├─ agi/
│   ├─ config/
│   ├─ dialplan/
│   └─ sounds/
│
├─ modules/
│   ├─ dialplan/
│   │   └─ Manager.php
|   ├─ pjsip/
│   │   └─ Manager.php
|   └─ publisher/
│       └─ Manager.php
│
├─ bootstrap/
│   ├─ Commands/
│   │   ├─ QuoteCommand.php
│   │   └─ PjsipCommand.php
│   ├─ AsterKernel.php
│   ├─ autoload.php
│   └─ bootstrap.php
|
├─ settings/
│   └─ publisher.php
│
├─ vendor/
│
├─ composer.json
├─ composer.lock
└─ aster
```

## Run Command

- To run command, execute `php aster`.

```bash
Astereal CLI
Usage:
  php aster [command][:action]

Available commands:
  dialplan Manage your dialplan (reload)
  pjsip  Manage the PJSIP module (reload, show, etc.)
  publish Publish application files to system paths
  quote  Display a random inspirational quote

Use 'php aster [command]:help' for details.
```

## Deploying your app

To deploy your application just run the command:
```bash
php aster publish
```

## Creator and Developer

`Jerome Soriano` - a very handsome asterisk developer.