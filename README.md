# 🏗️ Verteiltes PHP-Buchhaltungssystem

Ein produktionsreifes, erweiterbares PHP-System für verteilte Buchhaltung auf Raspberry Pi Clients mit zentralem Server.

## 🎯 Features

- ✅ **Verteilte Architektur**: Zentraler Server + beliebig viele Raspberry Pi Clients
- ✅ **Duale Datenhaltung**: Private Daten lokal, gemeinsame Daten zentral
- ✅ **Single Page Application**: Weiche Übergänge ohne Page Reloads
- ✅ **Sicheres API-System**: API-Key-Authentifizierung + Rate-Limiting
- ✅ **Responsive Design**: Mobile-First, modernes UI
- ✅ **Zero Framework**: Reines PHP, JS, CSS
- ✅ **SQLite für Pis**: Leichtgewichtig, wartungsfrei
- ✅ **MySQL für Server**: Skalierbar, robust

## 📁 Projektstruktur

```
distributed-accounting/
├── config.php                 # Instanz-Konfiguration (nicht im Git!)
├── config.example.php         # Konfigurations-Vorlage
├── index.php                  # Haupteinstiegspunkt
│
├── src/
│   ├── Core/
│   │   ├── Config.php         # Konfigurations-Manager
│   │   ├── Database.php       # PDO-Wrapper
│   │   └── Security.php       # API-Key-Validierung
│   │
│   ├── Api/
│   │   ├── Server.php         # Server-API-Logik
│   │   └── Client.php         # API-Client für Pis
│   │
│   └── Controllers/
│       ├── PrivateController.php
│       └── SharedController.php
│
├── public/
│   ├── css/
│   │   ├── main.css
│   │   └── transitions.css
│   └── js/
│       ├── app.js            # SPA-Navigation
│       └── api.js            # AJAX-Helper
│
├── views/
│   ├── layout.php            # Haupt-Layout
│   └── modules/
│       ├── private.php
│       └── shared.php
│
├── api/
│   └── endpoint.php          # Öffentlicher API-Endpunkt
│
└── database/
    ├── server_schema.sql
    └── client_schema.sql
```

## 🚀 Installation

### 1. Server-Instanz

#### Voraussetzungen
- PHP 8.0+
- MySQL/MariaDB
- Apache/Nginx mit mod_rewrite

#### Schritte

```bash
# 1. Repository klonen
git clone https://github.com/your-repo/distributed-accounting.git
cd distributed-accounting

# 2. Konfiguration erstellen
cp config.example.php config.php

# 3. config.php anpassen
nano config.php
```

**Server config.php:**
```php
return [
    'IS_SERVER' => true,  // WICHTIG!
    'API_KEY' => 'your-64-character-hex-key-here',  // Generiere mit: bin2hex(random_bytes(32))
    'DB' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'name' => 'accounting_db',
        'user' => 'accounting_user',
        'pass' => 'secure-password',
    ],
];
```

```bash
# 4. Datenbank erstellen
mysql -u root -p

CREATE DATABASE accounting_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'accounting_user'@'localhost' IDENTIFIED BY 'secure-password';
GRANT ALL PRIVILEGES ON accounting_db.* TO 'accounting_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# 5. Schema importieren
mysql -u accounting_user -p accounting_db < database/server_schema.sql

# 6. Apache VirtualHost einrichten
sudo nano /etc/apache2/sites-available/accounting.conf
```

**Apache VirtualHost:**
```apache
<VirtualHost *:80>
    ServerName accounting.example.com
    DocumentRoot /var/www/distributed-accounting
    
    <Directory /var/www/distributed-accounting>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/accounting-error.log
    CustomLog ${APACHE_LOG_DIR}/accounting-access.log combined
</VirtualHost>
```

```bash
# 7. Site aktivieren
sudo a2ensite accounting
sudo a2enmod rewrite
sudo systemctl restart apache2

# 8. Berechtigungen setzen
sudo chown -R www-data:www-data /var/www/distributed-accounting
sudo chmod 600 config.php  # Konfiguration schützen!
```

### 2. Raspberry Pi Client-Instanz

#### Voraussetzungen
- Raspberry Pi OS (Bullseye oder neuer)
- PHP 8.0+
- SQLite3
- Apache/Nginx

#### Schritte

```bash
# 1. Repository klonen
git clone https://github.com/your-repo/distributed-accounting.git
cd distributed-accounting

# 2. Konfiguration erstellen
cp config.example.php config.php

# 3. config.php anpassen
nano config.php
```

**Client config.php:**
```php
return [
    'IS_SERVER' => false,  // WICHTIG!
    'API_URL' => 'https://accounting.example.com/api/endpoint.php',
    'API_KEY' => 'your-64-character-hex-key-here',  // MUSS identisch mit Server sein!
    'DB' => [
        'driver' => 'sqlite',
        'sqlite_path' => __DIR__ . '/database/local.db',
    ],
];
```

```bash
# 4. SQLite-Datenbank erstellen
mkdir -p database
sqlite3 database/local.db < database/client_schema.sql

# 5. Berechtigungen
chmod 664 database/local.db
chmod 775 database
sudo chown -R www-data:www-data .
sudo chmod 600 config.php

# 6. Apache auf Pi einrichten
sudo nano /etc/apache2/sites-available/accounting.conf
```

**Apache für Pi (lokal):**
```apache
<VirtualHost *:80>
    ServerName raspberrypi.local
    DocumentRoot /var/www/distributed-accounting
    
    <Directory /var/www/distributed-accounting>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

```bash
# 7. Site aktivieren
sudo a2ensite accounting
sudo systemctl restart apache2
```

### 3. Weitere Raspberry Pis hinzufügen

Für jeden weiteren Pi:

1. Gleiche Schritte wie bei Client-Instanz
2. **Wichtig**: `API_KEY` muss **identisch** sein!
3. Jeder Pi hat seine eigene lokale Datenbank
4. Keine Codeänderungen notwendig ✅

## 🔐 Sicherheit

### API-Key Generierung

```bash
# Sichere API-Keys generieren
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

### Wichtige Sicherheitsmaßnahmen

1. **config.php niemals committen!**
   ```bash
   echo "config.php" >> .gitignore
   ```

2. **Berechtigungen prüfen:**
   ```bash
   chmod 600 config.php
   chmod 755 public/
   chmod 644 public/*.php
   ```

3. **HTTPS verwenden:**
   - Let's Encrypt für Server
   - Selbstsigniertes Zertifikat für Pis (optional)

4. **Firewall-Regeln:**
   ```bash
   # Auf Server: Nur Port 80/443 öffnen
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   
   # Auf Pis: Nur lokal erreichbar
   # Kein Port-Forwarding!
   ```

## 🧪 Testen

### Server-API testen

```bash
# Health-Check
curl -X GET \
  -H "X-API-Key: your-api-key-here" \
  "https://accounting.example.com/api/endpoint.php?action=health"

# Erwartete Response:
# {"success":true,"data":{"status":"ok","server":true,"timestamp":1234567890}}
```

### Client-Verbindung testen

1. Browser öffnen: `http://raspberrypi.local`
2. Zu "Gemeinsam" navigieren
3. Status-Anzeige prüft automatisch Server-Verbindung
4. Bei Erfolg: 🟢 "Verbunden mit Server"
5. Bei Fehler: 🔴 "Server nicht erreichbar"

## 📊 Verwendung

### Private Buchhaltung

- Läuft komplett lokal auf jedem Pi
- Daten bleiben auf dem Gerät
- Keine Server-Kommunikation

### Gemeinsame Buchhaltung

- Alle Daten zentral auf Server
- Alle Pis greifen auf gleiche Daten zu
- Automatische Synchronisation

### Navigation

- **Private** ↔ **Gemeinsam**: Navigation ohne Page Reload
- Weiche Transitions
- Daten werden bei Modul-Wechsel geladen

## 🔧 Entwicklung

### Neue Endpoints hinzufügen

**Server-API** (`src/Api/Server.php`):
```php
private function actionMyNewAction(): array
{
    // Deine Logik
    return ['result' => 'data'];
}
```

**Client nutzt automatisch** (`src/Api/Client.php`):
```php
$client = new Client();
$result = $client->request('myNewAction', ['param' => 'value']);
```

### Neues Modul hinzufügen

1. View erstellen: `views/modules/my_module.php`
2. Navigation erweitern in `views/layout.php`
3. CSS für Module ist bereits vorhanden
4. JavaScript-Logik im Modul implementieren

## 🐛 Troubleshooting

### "API not available"
- ✅ Prüfe `IS_SERVER` in config.php
- ✅ Auf Server muss `IS_SERVER = true` sein

### "Ungültiger API-Key"
- ✅ API-Key auf allen Instanzen identisch?
- ✅ Keine Leerzeichen im Key?
- ✅ Key mindestens 32 Zeichen?

### "Server nicht erreichbar"
- ✅ API_URL korrekt?
- ✅ Server läuft?
- ✅ Firewall blockiert nicht?
- ✅ DNS/Hostnamen auflösbar?

### Datenbank-Fehler
- ✅ Credentials korrekt?
- ✅ Datenbank existiert?
- ✅ Schema importiert?
- ✅ Berechtigungen korrekt?

### Frontend lädt nicht
- ✅ Browser-Konsole prüfen (F12)
- ✅ Apache/Nginx läuft?
- ✅ mod_rewrite aktiviert?
- ✅ Dateiberechtigungen korrekt?

## 📝 Roadmap

- [ ] User-Management (Multi-User)
- [ ] Export zu Excel/PDF
- [ ] Charts & Visualisierungen
- [ ] Mobile App
- [ ] Backup/Restore-System
- [ ] Benachrichtigungen
- [ ] Budget-Alerts
- [ ] Recurring Transactions

## 🤝 Contributing

Pull Requests willkommen! Bitte:

1. Fork das Repository
2. Feature-Branch erstellen
3. Commits mit klaren Messages
4. Tests hinzufügen
5. Pull Request öffnen

## 📄 Lizenz

MIT License - siehe LICENSE-Datei

## 👥 Support

Bei Problemen:
1. Issues auf GitHub öffnen
2. Logs prüfen: `/var/log/apache2/accounting-error.log`
3. Debug-Modus aktivieren in config.php

---

**Hinweis**: Dies ist ein Produktionssystem. Verwende es nur in sicheren Netzwerken und setze alle Sicherheitsmaßnahmen um!
test