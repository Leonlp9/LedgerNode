# Rechnungsverwaltung (Invoice Management)

Diese Funktion ermöglicht das Verwalten von Rechnungen und Gutschriften sowohl im privaten als auch im gemeinsamen Bereich.

## Features

### 1. Universelle Datei-Upload-Klasse
- **Speicherort**: `src/Core/FileUpload.php`
- Konfigurierbare Dateigrößenbeschränkungen (Standard: 10 MB)
- Dateityp-Validierung (PDF, JPG, PNG)
- MIME-Type-Prüfung zur Sicherheit
- Automatische Ordnerstruktur (Jahr/Monat)

### 2. Rechnungsverwaltung

#### Private Rechnungen
- **API**: `/api/private.php`
- **Handler**: `src/Api/PrivateInvoices.php`
- **DB**: SQLite

#### Gemeinsame Rechnungen  
- **API**: `/api/endpoint.php`
- **Handler**: `src/Api/Server.php`
- **DB**: MySQL/MariaDB

### 3. Funktionen

- Rechnungen hochladen (PDF, Bilder)
- Mit Überweisungen verknüpfen
- Pagination (15, 50, 100 pro Seite)
- Filtern nach Typ (Erhalten/Geschrieben)
- Dashboard-Widget für unverknüpfte Rechnungen

## Installation

```bash
# Migration ausführen
sqlite3 database/local.db < database/migrations/001_add_invoices.sql

# Uploads-Verzeichnis Rechte setzen
chmod 755 uploads
```

## Verwendung

Die Rechnungsverwaltung ist über den Tab "Rechnungen" in beiden Modulen (Privat & Gemeinsam) erreichbar.
