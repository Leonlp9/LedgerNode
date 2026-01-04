# Automatisches Datenbank-Schema Management

## Übersicht

Das LedgerNode-System verwendet jetzt ein **automatisches Schema-Management**, das bei jedem Request alle benötigten Datenbanktabellen erstellt und aktualisiert.

## Wie es funktioniert

### 1. Schema Manager (`database/schema.php`)

Die zentrale Klasse `App\Database\SchemaManager` verwaltet alle Datenbanktabellen:

```php
use App\Database\SchemaManager;

// Initialisiert automatisch alle Tabellen
SchemaManager::init();
```

### 2. Automatische Integration

Der SchemaManager wird automatisch in folgenden Dateien initialisiert:

- **`index.php`** - Haupteinstiegspunkt der Anwendung
- **`api/endpoint.php`** - Server-API
- **`api/private.php`** - Client-API für private Daten

### 3. Intelligente Erkennung

Der SchemaManager erkennt automatisch:

- **Server-Modus** (`IS_SERVER = true`): Erstellt Server-Tabellen (shared_*)
- **Client-Modus** (`IS_SERVER = false`): Erstellt Client-Tabellen (private_*)
- **Datenbank-Typ**: MySQL vs SQLite - passende Syntax wird automatisch verwendet

## Server-Tabellen

Bei `IS_SERVER = true` werden folgende Tabellen erstellt:

1. **shared_accounts** - Gemeinsame Konten
2. **shared_transactions** - Gemeinsame Transaktionen
3. **shared_categories** - Kategorien
4. **shared_youtube_income** - YouTube Einnahmen-Tracking
5. **shared_youtube_expenses** - YouTube Ausgaben
6. **sync_log** - Synchronisations-Protokoll

## Client-Tabellen

Bei `IS_SERVER = false` werden folgende Tabellen erstellt:

1. **private_accounts** - Private Konten
2. **private_transactions** - Private Transaktionen
3. **categories** - Kategorien
4. **budgets** - Budget-Planung
5. **private_invoices** - Rechnungen

## Performance

- **Caching**: SchemaManager wird nur einmal pro Request ausgeführt
- **CREATE IF NOT EXISTS**: Keine Fehler bei bereits existierenden Tabellen
- **Minimaler Overhead**: Nur bei ersten Zugriff werden Tabellen geprüft

## Migration von SQL-Dateien

### Vorher (SQL-Dateien)
```
database/
  ├── client_schema.sql
  ├── server_schema.sql
  └── migrations/
      ├── 001_add_invoices.sql
      └── 002_add_youtube_tracking.sql
```

### Nachher (Alles in PHP)
```
database/
  └── schema.php  ← Enthält alles
```

## Vorteile

✅ **Automatisch**: Keine manuellen Migrationen mehr nötig  
✅ **Plattform-unabhängig**: Funktioniert auf allen Systemen  
✅ **Fehler-sicher**: CREATE IF NOT EXISTS verhindert Duplikate  
✅ **Wartbar**: Alles in einer Datei, klare Struktur  
✅ **Flexibel**: Unterstützt MySQL und SQLite automatisch  

## Neue Tabelle hinzufügen

Um eine neue Tabelle hinzuzufügen, bearbeiten Sie `database/schema.php`:

```php
// In createServerTables() für Server-Tabellen
$this->db->execute("
    CREATE TABLE IF NOT EXISTS shared_new_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// In createClientTables() für Client-Tabellen
if ($isSqlite) {
    $this->db->execute("
        CREATE TABLE IF NOT EXISTS private_new_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )
    ");
} else {
    $this->db->execute("
        CREATE TABLE IF NOT EXISTS private_new_table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}
```

## Fehlerbehebung

### Problem: Tabellen werden nicht erstellt

**Lösung**: Prüfen Sie die Datenbankverbindung in `config.php`:

```php
'DB' => [
    'driver' => 'mysql',  // oder 'sqlite'
    'host'   => 'localhost',
    'name'   => 'buchhaltung',
    'user'   => 'root',
    'pass'   => '',
]
```

### Problem: "Class not found"

**Lösung**: Autoloader aktualisieren:

```bash
composer dump-autoload
```

### Manueller Test

Testen Sie das Schema-Management manuell:

```bash
php test_schema.php
```

## Zusammenfassung

Das neue automatische Schema-Management macht die Verwaltung der Datenbank **einfacher, sicherer und wartbarer**. SQL-Migrations-Dateien gehören der Vergangenheit an!

