# 🏗️ System-Architektur

## 📐 Übersicht

```
┌─────────────────────────────────────────────────────────────┐
│                      ZENTRALER SERVER                        │
│                                                              │
│  ┌──────────────┐     ┌──────────────┐     ┌─────────────┐ │
│  │   Apache     │────▶│  index.php   │────▶│   Views     │ │
│  │  Web Server  │     │  (Frontend)  │     │  (Layout)   │ │
│  └──────────────┘     └──────────────┘     └─────────────┘ │
│                                                              │
│  ┌──────────────┐     ┌──────────────┐     ┌─────────────┐ │
│  │  API-Client  │────▶│ Server-API   │────▶│   MySQL     │ │
│  │  (Requests)  │     │ (endpoint.php)│     │  Database   │ │
│  └──────────────┘     └──────────────┘     └─────────────┘ │
│         ▲                                          │         │
└─────────│──────────────────────────────────────────│─────────┘
          │                                          │
          │ HTTPS                           Shared Data
          │                                          │
          │                                          ▼
┌─────────┴──────────────────────────────────────────────────┐
│                  INTERNET / NETZWERK                        │
└─────────┬──────────────────────────────────────────────────┘
          │
          │
    ┌─────┴─────┬─────────────┬─────────────┐
    │           │             │             │
    ▼           ▼             ▼             ▼
┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐
│  Pi #1  │ │  Pi #2  │ │  Pi #3  │ │  Pi #N  │
│         │ │         │ │         │ │         │
│ Apache  │ │ Apache  │ │ Apache  │ │ Apache  │
│  PHP    │ │  PHP    │ │  PHP    │ │  PHP    │
│ SQLite  │ │ SQLite  │ │ SQLite  │ │ SQLite  │
│         │ │         │ │         │ │         │
│ Private │ │ Private │ │ Private │ │ Private │
│  Data   │ │  Data   │ │  Data   │ │  Data   │
└─────────┘ └─────────┘ └─────────┘ └─────────┘
```

## 🔑 Kern-Komponenten

### 1. Config-System (`config.php`)

**Zweck**: Zentrale Konfiguration für alle Instanzen

```php
[
    'IS_SERVER' => true|false,  // Instanz-Typ
    'API_URL'   => 'https://...',  // Server-URL (nur Clients)
    'API_KEY'   => '...',  // Gemeinsamer Schlüssel (ALLE)
    'DB' => [...]  // Datenbank-Config
]
```

**Regeln**:
- ✅ Gleiche Codebasis für Server + Clients
- ✅ Nur config.php unterscheidet die Instanz
- ✅ API_KEY muss überall identisch sein

### 2. Core-System

#### Config-Manager (`src/Core/Config.php`)
```
Aufgaben:
├─ Konfiguration laden & validieren
├─ Singleton-Pattern
├─ Dot-Notation für Zugriff
└─ Typ-Checks (isServer/isClient)
```

#### Database-Manager (`src/Core/Database.php`)
```
Aufgaben:
├─ PDO-Wrapper
├─ MySQL + SQLite Support
├─ Prepared Statements
├─ Helper-Methoden
└─ Transaction-Support
```

#### Security-Manager (`src/Core/Security.php`)
```
Aufgaben:
├─ API-Key-Validierung
├─ Rate-Limiting
├─ CSRF-Protection
├─ Input-Sanitization
└─ Server-Mode-Enforcement
```

### 3. API-System

#### Server-API (`src/Api/Server.php`)

**Läuft nur wenn**: `IS_SERVER === true`

```
Flow:
Request
  │
  ├─ Security::enforceServerApi()
  ├─ Security::authenticateApiRequest()
  │
  ├─ Action-Routing
  │  ├─ getSharedTransactions()
  │  ├─ addSharedTransaction()
  │  ├─ getSharedAccounts()
  │  ├─ createSharedAccount()
  │  └─ getSharedStats()
  │
  └─ JSON Response
```

**Verfügbare Actions**:
- `health` - Server-Status
- `getSharedTransactions` - Transaktionen abrufen
- `addSharedTransaction` - Transaktion hinzufügen
- `deleteTransaction` - Transaktion löschen
- `getSharedAccounts` - Konten abrufen
- `createSharedAccount` - Konto erstellen
- `getSharedStats` - Statistiken abrufen

#### API-Client (`src/Api/Client.php`)

**Läuft nur wenn**: `IS_SERVER === false`

```
Flow:
Client-Request
  │
  ├─ cURL-Request zum Server
  ├─ X-API-Key Header
  ├─ Timeout-Handling
  ├─ Retry-Logic (optional)
  │
  └─ Response-Parsing
```

**Features**:
- ✅ Automatische API-Key-Injection
- ✅ Error-Handling
- ✅ Timeout-Protection
- ✅ Convenience-Methoden

### 4. Frontend-Architektur

#### SPA-Navigation

```
User Click
  │
  ├─ App.switchModule('shared')
  │
  ├─ Fade-Out aktuelles Modul (CSS)
  ├─ Module wechseln (DOM)
  ├─ Fade-In neues Modul (CSS)
  │
  └─ Modul-Init (AJAX-Daten laden)
```

**Keine Page Reloads!** ✨

#### Modul-System

```
views/
├── layout.php              # Container + Navigation
└── modules/
    ├── private.php         # Private Buchhaltung
    └── shared.php          # Gemeinsame Buchhaltung
```

**Module enthalten**:
- ✅ HTML-Struktur
- ✅ JavaScript-Logik (inline)
- ✅ AJAX-Requests
- ✅ DOM-Manipulation
- ✅ Event-Handler

#### API-Kommunikation (Frontend)

```javascript
// Private Daten (lokal)
API.get('/api/private/transactions')
   .then(data => render(data))

// Shared Daten (Server)
API.getShared('getSharedTransactions')
   .then(data => render(data))
```

## 🔒 Sicherheits-Konzept

### 1. API-Key-Authentifizierung

```
Request-Flow:
Client
  │
  ├─ X-API-Key: xxxxxxx (Header)
  │
  ▼
Server
  │
  ├─ Security::extractApiKey()
  ├─ Security::validateApiKey()
  │  └─ hash_equals() [Timing-Safe!]
  │
  ├─ ✅ Valid → Process
  └─ ❌ Invalid → 401 Unauthorized
```

### 2. Server-Mode-Protection

```php
// In Server-API
Security::enforceServerApi();

// Wirft Exception wenn:
Config::isServer() === false
```

**Verhindert**: Client kann nicht Server-API ausführen

### 3. Rate-Limiting

```
IP-basiert:
├─ Max 60 Requests/Minute (default)
├─ Gespeichert in tmp-Files
└─ 429 Response bei Überschreitung
```

**Produktiv**: Redis/Memcached verwenden!

### 4. Input-Validierung

```php
// Alle Inputs werden:
├─ Type-checked (int, float, string)
├─ Sanitized (htmlspecialchars)
├─ Validated (required, format)
└─ Escaped (PDO Prepared Statements)
```

## 💾 Daten-Architektur

### Server-Datenbank (MySQL)

```sql
shared_accounts
  ├─ id (PK)
  ├─ name
  ├─ type (enum)
  ├─ description
  └─ timestamps

shared_transactions
  ├─ id (PK)
  ├─ account_id (FK)
  ├─ type (income/expense)
  ├─ amount (decimal)
  ├─ description
  ├─ date
  └─ timestamps
```

**Zugriff**: Nur via Server-API

### Client-Datenbank (SQLite)

```sql
private_accounts
  ├─ id (PK)
  ├─ name
  ├─ type (checking/savings/cash)
  ├─ initial_balance
  └─ timestamps

private_transactions
  ├─ id (PK)
  ├─ account_id (FK)
  ├─ type (income/expense)
  ├─ amount
  ├─ description
  ├─ category
  ├─ date
  └─ timestamps

categories
  ├─ id (PK)
  ├─ name
  ├─ type
  ├─ icon
  └─ color
```

**Zugriff**: Lokal, kein Server-Zugriff

## 🔄 Datenfluss

### Gemeinsame Transaktion hinzufügen

```
Pi-Browser
  │
  │ FormData
  ▼
SharedModule.submitTransaction()
  │
  │ AJAX POST
  ▼
API.postShared('addSharedTransaction', data)
  │
  │ HTTP + X-API-Key
  ▼
Server: /api/endpoint.php
  │
  ├─ Security::authenticateApiRequest()
  ├─ Server::handleRequest()
  ├─ Server::actionAddSharedTransaction()
  │
  │ PDO INSERT
  ▼
MySQL: shared_transactions
  │
  │ Response
  ▼
Pi-Browser
  │
  └─ UI Update
```

### Private Transaktion hinzufügen

```
Pi-Browser
  │
  │ FormData
  ▼
PrivateModule.submitTransaction()
  │
  │ AJAX POST
  ▼
API.post('/api/private/transactions', data)
  │
  │ Lokaler Request
  ▼
Pi: Local Controller
  │
  │ PDO INSERT
  ▼
SQLite: private_transactions
  │
  │ Response
  ▼
Pi-Browser
  │
  └─ UI Update
```

**Unterschied**: Private Daten verlassen den Pi nie!

## 🚀 Skalierbarkeit

### Horizontale Skalierung

```
Server:
├─ Load-Balancer (Nginx)
├─ Multiple PHP-FPM-Pools
├─ MySQL Master-Slave
└─ Redis für Sessions/Cache
```

### Pi-Skalierung

```
Unbegrenzt viele Pis:
├─ Keine Code-Änderung
├─ Nur config.php anpassen
├─ Gleicher API-Key
└─ Eigene lokale DB
```

**Limitierung**: Server-Performance

## 📊 Performance-Optimierung

### Caching-Strategie

```
Browser:
├─ Static Assets (CSS/JS)
└─ Cache-Control Headers

PHP:
├─ OPcache
└─ APCu für Config

Datenbank:
├─ Query-Cache
├─ Prepared Statements
└─ Indizes auf Foreign Keys
```

### AJAX-Optimierung

```javascript
// Batch-Requests
Promise.all([
    API.getShared('getSharedAccounts'),
    API.getShared('getSharedStats')
]).then(([accounts, stats]) => {
    // Render both
});
```

## 🔍 Monitoring

### Logs

```
Apache:
├─ /var/log/apache2/accounting-access.log
└─ /var/log/apache2/accounting-error.log

PHP:
└─ Configured via php.ini

Datenbank:
├─ api_logs (MySQL)
└─ Custom logging
```

### Health-Checks

```bash
# Server
curl -H "X-API-Key: xxx" \
  "https://server.com/api/endpoint.php?action=health"

# Erwartete Response:
{"success":true,"data":{"status":"ok"}}
```

## 🎯 Design-Prinzipien

1. **DRY** (Don't Repeat Yourself)
   - Gleiche Codebasis für alle Instanzen
   - Config-basierte Unterscheidung

2. **Security First**
   - API-Key-Authentifizierung
   - Rate-Limiting
   - Input-Validation
   - Prepared Statements

3. **Separation of Concerns**
   - Core / Api / Controllers / Views
   - Klare Verantwortlichkeiten

4. **Progressive Enhancement**
   - Funktioniert ohne JavaScript (Basic)
   - Enhanced mit JavaScript (SPA)

5. **Mobile-First**
   - Responsive Design
   - Touch-optimiert

## 🔮 Erweiterbarkeit

### Neue API-Action hinzufügen

```php
// src/Api/Server.php
private function actionMyNewAction(): array {
    // Implementierung
    return ['data' => $result];
}

// Automatisch verfügbar via:
API.postShared('myNewAction', {...})
```

### Neues Modul hinzufügen

```php
// views/modules/new_module.php
<div class="module-content">
    <!-- HTML -->
</div>
<script>
const NewModule = {
    init() { /* ... */ }
};
</script>

// views/layout.php
<div id="module-new" class="module">
    <?php include 'modules/new_module.php'; ?>
</div>
```

### Neue Datenbank-Tabelle

```sql
-- Server: Zu server_schema.sql hinzufügen
-- Client: Zu client_schema.sql hinzufügen

-- Migration ausführen:
mysql -u user -p db < schema.sql
# oder
sqlite3 local.db < schema.sql
```

## 🎓 Best Practices

### Code-Organisation
- ✅ PSR-4 Autoloading
- ✅ Namespaces verwenden
- ✅ Single Responsibility
- ✅ Type Hints

### Sicherheit
- ✅ Niemals config.php committen
- ✅ Prepared Statements
- ✅ Input-Validation
- ✅ Output-Escaping
- ✅ HTTPS in Produktion

### Performance
- ✅ Indizes auf Foreign Keys
- ✅ LIMIT bei großen Resultsets
- ✅ Batch-Requests
- ✅ Asset-Minification (Produktion)

### Wartbarkeit
- ✅ Code kommentieren
- ✅ Fehler loggen
- ✅ Tests schreiben (TODO)
- ✅ Dokumentation aktuell halten

---

**Next Steps**: Siehe ROADMAP.md für geplante Features
