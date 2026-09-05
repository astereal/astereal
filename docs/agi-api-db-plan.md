# Astereal Web & AGI-API-DB Integration Plan

## 1. Overview & Architecture Philosophy

The goal of **Astereal Web** is to provide an ultra-lightweight, zero-bloat web portal and REST API for Asterisk telephony automation. Instead of relying on massive full-stack frameworks (like Laravel or Symfony) that introduce hundreds of megabytes of dependencies, Astereal Web is built with **modern native PHP 8.1+**, **TailwindCSS**, **jQuery**, and **Alpine.js**.

```
[ Asterisk Dialplan ]
        │
        ▼ (executes AGI)
[ app/agi/aster_api.php ]
        │
        │  1. Signs payload with HMAC-SHA256 + Timestamp
        │  2. Strict telephony timeout (1.5 - 2.0s)
        ▼ (HTTP POST /api/v1/...)
[ web/public/index.php ] (Astereal Web Front Controller)
        │
        ├──▶ [ Middleware: HmacAuth & RateLimit ]
        │      • Validates timestamp freshness (< 30s)
        │      • Validates HMAC signature
        │      • Validates IP whitelist (localhost or Asterisk server)
        │
        ├──▶ [ Controllers: Api / Web ]
        │      • Caller Lookup, Routing, CDR, SIP Management
        │
        └──▶ [ Database Layer: Native PDO Models ]
               • Strict prepared statements (zero SQL injection)
               • MySQL / MariaDB / PostgreSQL / SQLite
```

---

## 2. Technology Stack

| Layer | Technology | Purpose |
| :--- | :--- | :--- |
| **Backend Core** | Native PHP 8.1+ | Ultra-fast execution (~2–5ms boot time), zero heavy vendor overhead. |
| **Database** | Native PDO (Prepared Statements) | Direct, secure, injection-proof database communication. |
| **Styling** | TailwindCSS | Modern, responsive, utility-first CSS design (dark/light themes). |
| **DOM & AJAX** | jQuery | Seamless AJAX communication, telephony event handling, DOM helpers. |
| **UI Reactivity** | Alpine.js | Declarative, lightweight frontend reactivity (modals, dropdowns, tabs, live state). |

---

## 3. Directory Structure

```text
ASTEREAL/
├── app/                                <- Asterisk Telephony Core
│   ├── agi/
│   │   ├── CAGI.php                    <- Core AGI client
│   │   ├── aster_logger.php            <- Telephony session logger
│   │   └── aster_api.php               <- Telephony HTTP/API client
│   ├── dialplan/
│   │   └── extensions.conf             <- Asterisk dialplans
│   └── sip/
│       └── pjsip.example.conf          <- SIP/PJSIP definitions
│
├── web/                                <- Astereal Native Web & API
│   ├── public/                         <- Web Server DocumentRoot (Apache/Nginx/PHP-CLI)
│   │   ├── index.php                   <- Application front controller & routing entry
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   └── tailwind.min.css    <- Tailwind styling
│   │   │   └── js/
│   │   │       ├── jquery.min.js       <- jQuery library
│   │   │       └── alpine.min.js       <- Alpine.js library
│   ├── app/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── CallerController.php  <- AGI caller lookup & routing
│   │   │   │   └── CdrController.php     <- Call detail recording API
│   │   │   └── Web/
│   │   │       ├── DashboardController.php <- Admin overview
│   │   │       └── SipController.php       <- SIP endpoint manager
│   │   ├── Middleware/
│   │   │   ├── HmacAuthMiddleware.php  <- Validates AGI HMAC signatures
│   │   │   └── IpWhitelistMiddleware.php <- Restricts API to Asterisk IPs
│   │   ├── Models/
│   │   │   ├── Database.php            <- PDO singleton connection manager
│   │   │   ├── Caller.php              <- Customer / Caller data model
│   │   │   └── Cdr.php                 <- CDR records model
│   │   └── Support/
│   │       ├── Router.php              <- Lightweight micro-router
│   │       ├── Request.php             <- HTTP request wrapper
│   │       └── Response.php            <- JSON / HTML response helper
│   ├── config/
│   │   ├── database.php                <- Database credentials
│   │   └── security.php                <- API secrets, IP whitelist, timeouts
│   ├── routes/
│   │   ├── api.php                     <- Protected API routes (for Asterisk AGI)
│   │   └── web.php                     <- Web UI routes (Dashboard, Endpoints, CDRs)
│   └── views/                          <- Server-rendered HTML templates
│       ├── layouts/
│       │   └── app.php                 <- Master layout (Tailwind + Alpine + jQuery)
│       ├── dashboard/
│       │   └── index.php               <- Live calls, Asterisk status, quick stats
│       └── callers/
│           └── index.php               <- Caller directory & VIP management
│
├── settings/                           <- Astereal CLI configuration
│   └── publisher.php
├── bootstrap/                          <- Astereal CLI Kernel (`php aster`)
└── aster                               <- Astereal CLI executable
```

---

## 4. Security Layer (AGI $\rightarrow$ API $\rightarrow$ Database)

Telephony APIs control call routing and access customer information. They must be secured against unauthorized access, eavesdropping, and replay attacks.

### 4.1 HMAC-SHA256 Cryptographic Signing
All requests from Asterisk AGI to the API are cryptographically signed using a shared secret (`API_SECRET`):

1. **Headers sent by AGI (`aster_api.php`)**:
   - `X-Astereal-Timestamp`: Current UNIX timestamp (e.g. `1725528258`).
   - `X-Astereal-Key`: Public identifier for the Asterisk node (e.g. `asterisk-node-01`).
   - `X-Astereal-Signature`: HMAC-SHA256 hash computed over:
     ```php
     $signature = hash_hmac('sha256', $timestamp . $httpMethod . $endpoint . $payloadJson, $apiSecret);
     ```

2. **Verification in API Middleware (`HmacAuthMiddleware.php`)**:
   - **Timestamp Freshness**: The API validates `abs(time() - $timestamp) <= 30` seconds. Any delayed or replayed requests are immediately rejected (`401 Unauthorized`).
   - **Signature Integrity**: The API recomputes the HMAC hash using its local `API_SECRET`. If any query parameter, ANI, or payload byte was tampered with, verification fails.

### 4.2 Network Isolation & IP Whitelisting
- **Localhost Binding**: When Asterisk and the Web API reside on the same server, the web server is configured to bind strictly to `127.0.0.1` or unix domain sockets, isolating the API from external internet scanners.
- **IP Allowlist**: If Asterisk and the Web API reside on separate machines, `IpWhitelistMiddleware.php` validates `$_SERVER['REMOTE_ADDR']` against an approved list of Asterisk server IPs.

### 4.3 Database Injection Prevention
All SQL queries inside `web/app/Models/` must use **PDO prepared statements** with strict parameter binding:
```php
$stmt = $pdo->prepare("SELECT id, name, vip, route_destination FROM callers WHERE phone_number = :ani LIMIT 1");
$stmt->execute([':ani' => $ani]);
return $stmt->fetch(PDO::FETCH_ASSOC);
```

---

## 5. End-to-End Workflow: Inbound Call Lookup

### Step 1: Inbound Call enters Dialplan
```asterisk
[internal]
exten => _X.,1,NoOp(*** Inbound Call Routed through Astereal ***)
  same => n,Set(ANI=${CALLERID(num)})
  same => n,Set(DNIS=${EXTEN})
  same => n,AGI(aster_api.php, "caller/lookup")
  same => n,Verbose("Lookup Result: Caller=${CALLER_NAME}, VIP=${IS_VIP}, Target=${ROUTE_TO}")
  same => n,ExecIf($["${IS_VIP}" = "1"]?Goto(vip-queue,s,1))
  same => n,Dial(PJSIP/${ROUTE_TO},25)
  same => n,Hangup()
```

### Step 2: AGI Client executes (`app/agi/aster_api.php`)
1. Reads channel variables `${ANI}`, `${DNIS}`, `${CHANNEL}`.
2. Formats JSON payload: `{"ani": "100", "dnis": "128", "channel": "PJSIP/100-00000002"}`.
3. Generates HMAC signature and timestamp.
4. Executes cURL request to `http://127.0.0.1/api/v1/caller/lookup` with a **1.5-second timeout**.
5. Upon response:
   ```json
   {
     "status": "success",
     "data": {
       "CALLER_NAME": "Jerome Soriano",
       "IS_VIP": "1",
       "ROUTE_TO": "200"
     }
   }
   ```
6. Sets Asterisk channel variables:
   - `CALLER_NAME` = "Jerome Soriano"
   - `IS_VIP` = "1"
   - `ROUTE_TO` = "200"

### Step 3: API Controller handles request (`web/app/Controllers/Api/CallerController.php`)
1. Validates HMAC signature via middleware.
2. Queries the database using `Caller::findByPhone($ani)`.
3. Returns JSON response in < 5 milliseconds.

---

## 6. Frontend Stack (TailwindCSS + Alpine.js + jQuery)

### 6.1 Roles & Synergies
- **TailwindCSS**: Provides modern dashboard UI components, cards, tables, status badges, and typography.
- **Alpine.js**: Manages local UI interactions:
  - Opening/closing edit modals.
  - Dropdown menus.
  - Switching tabs (e.g. Call Logs vs. Active Endpoints vs. Settings).
  - Toggling dark mode.
- **jQuery**: Handles asynchronous communication and DOM utility:
  - Making AJAX calls to update records without page reloads.
  - Periodic polling for active Asterisk channels (`/api/v1/channels/active`).
  - Toast notification display.

### 6.2 Example Dashboard Component (Blade/PHP View)
```html
<div x-data="{ openModal: false, selectedCaller: null }" class="p-6 bg-slate-900 text-white min-h-screen">
  <!-- Header -->
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Astereal Telephony Portal</h1>
    <button @click="openModal = true" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-lg text-sm font-medium shadow">
      + Add VIP Caller
    </button>
  </div>

  <!-- Real-time Status Card (Polled via jQuery) -->
  <div id="active-calls-container" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Populated dynamically -->
  </div>

  <!-- Alpine Modal -->
  <div x-show="openModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4" x-cloak>
    <div @click.away="openModal = false" class="bg-slate-800 rounded-xl max-w-md w-full p-6 border border-slate-700">
      <h3 class="text-lg font-semibold mb-4">Add VIP Caller</h3>
      <input type="text" id="new-ani" placeholder="Phone Number" class="w-full mb-3 px-3 py-2 bg-slate-900 rounded border border-slate-700 text-white">
      <input type="text" id="new-name" placeholder="Caller Name" class="w-full mb-4 px-3 py-2 bg-slate-900 rounded border border-slate-700 text-white">
      <div class="flex justify-end gap-2">
        <button @click="openModal = false" class="px-4 py-2 bg-slate-700 rounded text-sm">Cancel</button>
        <button id="save-caller-btn" class="px-4 py-2 bg-indigo-600 rounded text-sm font-medium">Save Caller</button>
      </div>
    </div>
  </div>
</div>

<script>
  // jQuery handles the AJAX submission and polling
  $(document).ready(function() {
    $('#save-caller-btn').on('click', function() {
      const payload = {
        phone: $('#new-ani').val(),
        name: $('#new-name').val()
      };
      $.post('/api/v1/callers', payload, function(res) {
        alert('Caller added successfully');
        location.reload();
      });
    });
  });
</script>
```

---

## 7. Step-by-Step Implementation Roadmap

1. **Step 1: Core Web & Router Foundation**
   - Create `web/public/index.php`, lightweight micro-router (`Router.php`), Request, and Response classes.
   - Configure clean URL rewriting via `.htaccess` (Apache) / `nginx.conf`.

2. **Step 2: Database Connection & Base Model**
   - Create `web/config/database.php` and `web/app/Models/Database.php` using native PDO.
   - Provide migration/schema for `callers` and `cdr` tables.

3. **Step 3: Security Middleware & HMAC Signing**
   - Build `web/app/Middleware/HmacAuthMiddleware.php`.
   - Build `web/config/security.php` with secret key and IP allowlist.

4. **Step 4: AGI HTTP Client (`app/agi/aster_api.php`)**
   - Create `aster_api.php` with HMAC signing, strict 1.5s timeout, and Asterisk variable injection.
   - Test end-to-end with dialplan.

5. **Step 5: Frontend UI (TailwindCSS + Alpine.js + jQuery)**
   - Setup layout and dashboard view with dark-mode Asterisk stats.
   - Implement live call monitoring and VIP directory management.
