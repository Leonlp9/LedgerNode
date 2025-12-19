# 🚀 Schnellstart-Anleitung

## 📋 Voraussetzungen

### Server
- Ubuntu/Debian Server
- PHP 8.0+
- MySQL/MariaDB
- Apache mit mod_rewrite

### Raspberry Pi (Client)
- Raspberry Pi OS (Bullseye+)
- PHP 8.0+
- SQLite3
- Apache

## ⚡ Automatische Installation

### Server

```bash
# 1. Repository klonen
git clone https://github.com/your-repo/distributed-accounting.git
cd distributed-accounting

# 2. Setup-Script ausführen
chmod +x setup.sh
sudo ./setup.sh server

# 3. Folge den Anweisungen
# - MySQL-Credentials eingeben
# - API-Key wird automatisch generiert
# - Speichere den API-Key für Clients!
```

### Raspberry Pi (Client)

```bash
# 1. Repository klonen
git clone https://github.com/your-repo/distributed-accounting.git
cd distributed-accounting

# 2. Setup-Script ausführen
chmod +x setup.sh
sudo ./setup.sh client

# 3. Folge den Anweisungen
# - Server-URL eingeben
# - API-Key vom Server eingeben
```

## 🔐 Manuelle Installation

Falls das Setup-Script nicht funktioniert:

### Server

```bash
# Konfiguration
cp config.example.php config.php
nano config.php

# Setze:
# - IS_SERVER => true
# - API_KEY => generiere mit: php -r "echo bin2hex(random_bytes(32));"
# - DB-Credentials

# Datenbank
mysql -u root -p
CREATE DATABASE accounting_db;
CREATE USER 'accounting_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL ON accounting_db.* TO 'accounting_user'@'localhost';
EXIT;

mysql -u accounting_user -p accounting_db < database/server_schema.sql

# Berechtigungen
sudo chown -R www-data:www-data .
chmod 600 config.php
```

### Raspberry Pi

```bash
# Konfiguration
cp config.example.php config.php
nano config.php

# Setze:
# - IS_SERVER => false
# - API_URL => Server-URL
# - API_KEY => von Server kopieren
# - DB.driver => 'sqlite'

# Datenbank
mkdir -p database
sqlite3 database/local.db < database/client_schema.sql

# Berechtigungen
sudo chown -R www-data:www-data .
chmod 600 config.php
chmod 664 database/local.db
```

## ✅ Testing

### Server testen

```bash
# Health-Check
curl -H "X-API-Key: YOUR_API_KEY" \
  "http://your-server.com/api/endpoint.php?action=health"

# Erwartete Response:
# {"success":true,"data":{"status":"ok"}}
```

### Client testen

1. Browser öffnen: `http://raspberrypi.local`
2. Navigation zu "Gemeinsam"
3. Status prüfen: 🟢 = verbunden

## 🎯 Erste Schritte

### 1. Private Buchhaltung
- Klicke auf "Private"
- Erstelle Konten
- Füge Transaktionen hinzu
- Daten bleiben lokal

### 2. Gemeinsame Buchhaltung
- Klicke auf "Gemeinsam"
- Verbindung zum Server wird automatisch geprüft
- Erstelle gemeinsame Konten
- Füge Transaktionen hinzu
- Alle Pis sehen die gleichen Daten

## 🔧 Konfiguration anpassen

### API-Key ändern

```php
// config.php
'API_KEY' => 'neuer-64-zeichen-key',
```

**WICHTIG**: Muss auf ALLEN Instanzen gleich sein!

### Datenbank-Verbindung

```php
// Server (MySQL)
'DB' => [
    'driver' => 'mysql',
    'host' => 'localhost',
    'name' => 'accounting_db',
    'user' => 'accounting_user',
    'pass' => 'password',
]

// Client (SQLite)
'DB' => [
    'driver' => 'sqlite',
    'sqlite_path' => __DIR__ . '/database/local.db',
]
```

### Debug-Modus aktivieren

```php
// config.php
'APP' => [
    'debug' => true,  // Zeigt Fehler im Browser
]
```

**Nur in Development verwenden!**

## 📱 Weitere Pis hinzufügen

Für jeden weiteren Raspberry Pi:

```bash
# 1. Code klonen
git clone https://github.com/your-repo/distributed-accounting.git
cd distributed-accounting

# 2. Setup ausführen
sudo ./setup.sh client

# 3. Gleichen API-Key verwenden!

# FERTIG! ✅
```

## 🐛 Probleme lösen

### "API not available"
```bash
# Prüfe config.php
grep IS_SERVER config.php
# Server: Muss true sein
```

### "Ungültiger API-Key"
```bash
# Prüfe ob Keys identisch
# Server und alle Clients müssen gleichen Key haben!
```

### "Server nicht erreichbar"
```bash
# Teste Server-Verbindung
curl http://your-server.com/api/endpoint.php

# Prüfe Firewall
sudo ufw status
```

### Datenbank-Fehler
```bash
# MySQL: Teste Verbindung
mysql -u accounting_user -p

# SQLite: Prüfe Berechtigungen
ls -la database/local.db
```

## 📞 Support

- **Issues**: GitHub Issues
- **Logs**: `/var/log/apache2/accounting-error.log`
- **Debug**: `'APP.debug' => true` in config.php

## 🎉 Fertig!

Dein verteiltes Buchhaltungssystem läuft jetzt!

**Nächste Schritte:**
1. Konten erstellen
2. Transaktionen hinzufügen
3. Weitere Pis verbinden
4. Features erkunden

---

**Tipp**: Mache regelmäßig Backups deiner Datenbanken!

```bash
# Server-Backup
mysqldump -u accounting_user -p accounting_db > backup.sql

# Client-Backup
cp database/local.db backup/local_$(date +%Y%m%d).db
```
