---
name: astereal-framework
description: >-
  Framework standards and guidelines for developing Asterisk telephony applications
  using the Astereal PHP framework. Covers strict dialplan formatting rules (2-space indent,
  banner comments, loop nesting, mandatory hangup handlers, extensions_* modularity),
  AstDB CDR duration tracking, AGI architecture for database/API integrations, and CLI tooling.
---

# Astereal Framework Development Skill

## Overview
**Astereal** is an Asterisk framework built with PHP (8.1+), designed for professional Asterisk telephony developers. It enforces strict, clean dialplan formatting, modular dialplan architecture, AstDB-backed CDR metrics, and delegates all external database and API interactions to PHP AGI scripts.

---

## 1. Dialplan Formatting & Standards

### 1.1 Indentation & Spacing
- **Indentation Width**: Exactly **2 spaces** before `same =>` to ensure clean alignment and readability.
- **Do not use 1 space or 4 spaces** for standard dialplan steps.

```asterisk
[public]
exten => _X.,1,NoOp(*** Welcome to Astereal: Public Inbound ***)
  same => n,Verbose("Incoming call to ${EXTEN}")
  same => n,Dial(SIP/${EXTEN},20)
  same => n,Voicemail(${EXTEN}@default,u)
  same => n,Hangup()
```

### 1.2 Banner Comments & Context Entry
- Precede every context with a standardized block comment banner:
```asterisk
; *************************************
; Public Inbound Extensions
; Longer description of what this context accomplishes
; *************************************
[public]
exten => _X.,1,NoOp(*** Short summary of context entry ***)
```
- **Rule**:
  - The **longer, detailed description** goes into the `; *************************************` banner comment.
  - The **shorter version** must be the first line action using `NoOp(...)` or `Verbose(...)` on priority 1 (`1,NoOp(...)`).

### 1.3 Loop Formatting & Indentation
- When implementing a loop (e.g., `While`, `GotoIf`), nest the loop body with additional indentation (2 additional spaces or tab) for visual hierarchy:

```asterisk
  ;***************************************************
  ; LOOP Start: Retry dialing up to MAX_ATTEMPTS
  exten => s,n,While($[${ATTEMPT} < ${MAX_ATTEMPTS}])
    same => n(continue),Verbose("Attempt ${ATTEMPT} of ${MAX_ATTEMPTS}...")
    same => n,Set(ATTEMPT=$[${ATTEMPT} + 1])
  exten => s,n(end_loop),Verbose("Max attempts reached")
  exten => s,n,Hangup()
```

### 1.4 Mandatory Hangup Handler (`exten => h,1`) in Every Context
- **Every context must always include an `h` extension**:
```asterisk
exten => h,1,NoOp("Always have a hangup in the context.")
  same => n,AGI(aster_logger.php, "Call ended in context: ${CONTEXT}")
```
- **Purpose**: Guarantees sudden or unexpected hangups are never missed, and logs exactly which context the hangup occurred in.

### 1.5 Modular Subroutines & `GoSub()` File Separation
- Keep the main `extensions.conf` concise and maintainable.
- Sub-functions, reusable routines, and `GoSub()` destinations must be extracted into separate files.
- **Naming Rule**: All separated extension files must use the `extensions_` prefix:
  - `extensions_voicemail.conf`
  - `extensions_parallel_ring.conf`
  - `extensions_queues.conf`
- Included into `extensions.conf` via `#include "extensions_*.conf"`.

### 1.6 Variable Naming Convention
- All variables (channel variables, global variables, loop counters) must be **ALL CAPS with underscores**:
  - Good: `START_DATETIME`, `ATTEMPT`, `MAX_ATTEMPTS`, `TOTAL_DURATION`, `DIALED_DURATION`, `ANSWERED_DURATION`, `ANI`, `DNIS`
  - Avoid: `start_time`, `attemptCount`, `myVar`

---

## 2. CDR & Duration Calculation via Asterisk Database (AstDB)

1. **Call Start**:
   - At call arrival, capture and store the call start timestamp in the Asterisk Database (AstDB) or channel variable:
   ```asterisk
   same => n,Set(START_DATETIME=${EPOCH})
   same => n,Set(DB(call/${UNIQUEID}/start)=${START_DATETIME})
   ```

2. **Hangup Calculation in `h` extension**:
   - In `exten => h,1`, retrieve the `START_DATETIME` from AstDB/channel variable.
   - Calculate key metrics:
     - **TOTAL_DURATION**: `${EPOCH} - ${START_DATETIME}`
     - **DIALED_DURATION**: Time spent ringing the destination.
     - **ANSWERED_DURATION**: Total billable talk time.
   - Pass these metrics into `AGI(log.php)` or CDR handler to log into database/API.
   - Clean up the temporary AstDB entry: `Set(DB_DELETE(call/${UNIQUEID}/start)=1)` or via AGI.

---

## 3. AGI Architecture & Standards

- **Philosophy**: AGI is the sole gateway for external database queries, CRM/REST API calls, and business logic.
- **Script Location**: `app/agi/`
- **Built-in Framework AGI Prefix**:
  - All built-in framework AGIs must start with the **`aster_`** prefix (e.g., `aster_logger.php`).
- **Output Safety**:
  - Never write raw `echo` or `print` in AGI scripts; standard output is reserved for AGI protocol communication with Asterisk. Use `$agi->verbose()` or file logging.
- **Timeout Protection**:
  - All external API and DB calls must enforce strict timeouts (e.g., 2–3s) to avoid hanging Asterisk channels.

---

## 4. Astereal Logging Standards

### Directory & File Hierarchy:
Logs must be organized by component channel, year-month folder, and daily log file:
```
/var/log/astereal/{category}/{YYYYMM}/{YYYY-MM-DD}_astereal.log
```

### Component Categories & Ownership:
- **`core`**: Asterisk core telephony events & CDR logs (owned by `asterisk`).
  - Path: `/var/log/astereal/core/202609/2026-09-05_astereal.log`
- **`voice`**: IVR / voice application flows (owned by `asterisk`).
- **`api`**: REST/HTTP API requests and webhooks (owned by `apache` / `www-data`).
- **`background`**: Asynchronous workers, schedulers, and queue processors.

### Log Format:
Streamlined, single-line format focused on session tracking and custom messages:
```
[YYYY-MM-DD HH:MM:SS] CHANNEL={channel} ANI={ani} DNIS={dnis} MSG="{message}"
```
- **`CHANNEL`**: Unique Asterisk channel session (e.g., `PJSIP/100-00000001`). Used to sort/trace entire call flows.
- **`ANI`**: Calling party number (`CALLERID(num)`).
- **`DNIS`**: Called/dialed number (`EXTEN`).
- **`MSG`**: Custom event message passed directly via argument `AGI(aster_logger.php, "message")` or variable `${MSG}`.

### Directory Creation Rule:
Before creating the daily log file, loggers must check if the Year-Month folder (`{YYYYMM}`) exists; if not, create it recursively with `0775` permissions.



---

## 5. Astereal CLI (`php aster`)

- `php aster publish` - Publishes framework configs to Asterisk system paths (`/etc/asterisk/`, `/var/lib/asterisk/agi-bin/`). Automatically detects if Asterisk is stopped and starts it before reloading enabled modules (`dialplan`, `pjsip`).
- `php aster core:status` - Checks if Asterisk daemon is active and responsive.
- `php aster core:start` - Starts Asterisk daemon via `systemctl`, `service`, or binary fallback.
- `php aster core:stop` - Gracefully stops Asterisk.
- `php aster core:restart` - Restarts Asterisk daemon.
- `php aster dialplan:reload` - Standalone reload of Asterisk dialplan (`asterisk -rx "dialplan reload"`).
- `php aster pjsip:reload` - Standalone reload of PJSIP endpoints and configuration.
- Future commands: `make:agi`, `make:dialplan`, `test:agi`.


