# ASTEREAL

**Astereal** is an Asterisk framework built with PHP, designed to make telephony development seamless and expressive. Inspired by the word ethereal, it aims to bring elegance and simplicity to the complex world of Asterisk automation.

`“The ethereal way to build Asterisk applications.”`

***This project is currently under development***

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
  quote  Display a random inspirational quote

Use 'php aster [command]:help' for details.
```

## Creator and Developer

`Jerome Soriano` - a very handsome asterisk developer.