#!/bin/bash

# ==========================================
# Setup-Script für verteiltes Buchhaltungssystem
# ==========================================
# 
# Verwendung:
#   ./setup.sh server   # Server-Instanz einrichten
#   ./setup.sh client   # Client-Instanz einrichten
# ==========================================

set -e  # Bei Fehler abbrechen

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funktionen
print_header() {
    echo -e "${BLUE}=========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}=========================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

# Prüfe Argument
if [ $# -eq 0 ]; then
    print_error "Kein Typ angegeben!"
    echo "Verwendung: ./setup.sh [server|client]"
    exit 1
fi

INSTALL_TYPE=$1

if [ "$INSTALL_TYPE" != "server" ] && [ "$INSTALL_TYPE" != "client" ]; then
    print_error "Ungültiger Typ: $INSTALL_TYPE"
    echo "Verwendung: ./setup.sh [server|client]"
    exit 1
fi

print_header "Setup: $INSTALL_TYPE"

# ==========================================
# 1. Voraussetzungen prüfen
# ==========================================
print_info "Prüfe Voraussetzungen..."

# PHP prüfen
if ! command -v php &> /dev/null; then
    print_error "PHP ist nicht installiert!"
    exit 1
fi
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
print_success "PHP $PHP_VERSION gefunden"

# Prüfe PHP-Version >= 8.0
if ! php -r 'exit(version_compare(PHP_VERSION, "8.0.0", ">=") ? 0 : 1);'; then
    print_error "PHP 8.0 oder höher erforderlich!"
    exit 1
fi

# Apache prüfen
if ! command -v apache2 &> /dev/null && ! command -v httpd &> /dev/null; then
    print_warning "Apache nicht gefunden. Bitte manuell installieren."
fi

# ==========================================
# 2. Konfiguration erstellen
# ==========================================
print_info "Erstelle Konfiguration..."

if [ -f "config.php" ]; then
    print_warning "config.php existiert bereits!"
    read -p "Überschreiben? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_info "Überspringe Konfiguration."
    else
        rm config.php
    fi
fi

if [ ! -f "config.php" ]; then
    cp config.example.php config.php
    print_success "config.php erstellt"
    
    # API-Key generieren
    API_KEY=$(php -r 'echo bin2hex(random_bytes(32));')
    
    if [ "$INSTALL_TYPE" == "server" ]; then
        # Server-Konfiguration
        print_info "Server-Instanz wird konfiguriert..."
        
        read -p "MySQL Host [localhost]: " DB_HOST
        DB_HOST=${DB_HOST:-localhost}
        
        read -p "MySQL Datenbank [accounting_db]: " DB_NAME
        DB_NAME=${DB_NAME:-accounting_db}
        
        read -p "MySQL User [accounting_user]: " DB_USER
        DB_USER=${DB_USER:-accounting_user}
        
        read -sp "MySQL Passwort: " DB_PASS
        echo
        
        # Ersetze in config.php
        sed -i "s/'IS_SERVER' => false/'IS_SERVER' => true/" config.php
        sed -i "s/'your-64-character-hex-key-here'/'$API_KEY'/" config.php
        sed -i "s/'driver' => 'mysql'/'driver' => 'mysql'/" config.php
        sed -i "s/'host'   => ''/'host'   => '$DB_HOST'/" config.php
        sed -i "s/'name'   => ''/'name'   => '$DB_NAME'/" config.php
        sed -i "s/'user'   => ''/'user'   => '$DB_USER'/" config.php
        sed -i "s/'pass'   => ''/'pass'   => '$DB_PASS'/" config.php
        
        print_success "Server-Konfiguration erstellt"
        print_warning "WICHTIG: Speichere diesen API-Key für Clients:"
        echo -e "${GREEN}$API_KEY${NC}"
        
    else
        # Client-Konfiguration
        print_info "Client-Instanz wird konfiguriert..."
        
        read -p "Server API-URL: " API_URL
        read -p "API-Key (vom Server): " PROVIDED_KEY
        
        # Ersetze in config.php
        sed -i "s/'IS_SERVER' => false/'IS_SERVER' => false/" config.php
        sed -i "s|'API_URL'   => ''|'API_URL'   => '$API_URL'|" config.php
        sed -i "s/'your-64-character-hex-key-here'/'$PROVIDED_KEY'/" config.php
        sed -i "s/'driver' => 'mysql'/'driver' => 'sqlite'/" config.php
        
        print_success "Client-Konfiguration erstellt"
    fi
    
    # Berechtigungen setzen
    chmod 600 config.php
    print_success "Berechtigungen gesetzt (600)"
fi

# ==========================================
# 3. Datenbank einrichten
# ==========================================
print_info "Richte Datenbank ein..."

if [ "$INSTALL_TYPE" == "server" ]; then
    # MySQL-Datenbank
    print_info "MySQL-Schema wird importiert..."
    
    if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/server_schema.sql 2>/dev/null; then
        print_success "MySQL-Schema importiert"
    else
        print_error "MySQL-Import fehlgeschlagen!"
        print_info "Bitte manuell importieren: mysql -u $DB_USER -p $DB_NAME < database/server_schema.sql"
    fi
else
    # SQLite-Datenbank
    mkdir -p database
    
    if [ -f "database/local.db" ]; then
        print_warning "database/local.db existiert bereits"
        read -p "Neu erstellen? (y/N) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            rm database/local.db
        fi
    fi
    
    if [ ! -f "database/local.db" ]; then
        if command -v sqlite3 &> /dev/null; then
            sqlite3 database/local.db < database/client_schema.sql
            print_success "SQLite-Datenbank erstellt"
            
            # Berechtigungen
            chmod 664 database/local.db
            chmod 775 database
        else
            print_error "sqlite3 nicht gefunden!"
            print_info "Installiere mit: sudo apt-get install sqlite3"
        fi
    fi
fi

# ==========================================
# 4. Verzeichnisberechtigungen
# ==========================================
print_info "Setze Berechtigungen..."

# Basis-Berechtigungen
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Spezielle Berechtigungen
chmod 600 config.php
chmod 755 setup.sh

# Web-Server-Owner (falls sudo verfügbar)
if [ "$EUID" -eq 0 ]; then
    chown -R www-data:www-data .
    print_success "Owner auf www-data gesetzt"
else
    print_warning "Nicht als root ausgeführt. Owner-Änderung übersprungen."
    print_info "Führe aus: sudo chown -R www-data:www-data ."
fi

print_success "Berechtigungen gesetzt"

# ==========================================
# 5. Apache-Konfiguration
# ==========================================
print_info "Apache-Konfiguration..."

if [ "$INSTALL_TYPE" == "server" ]; then
    read -p "ServerName (z.B. accounting.example.com): " SERVER_NAME
else
    SERVER_NAME="raspberrypi.local"
fi

VHOST_FILE="/etc/apache2/sites-available/accounting.conf"

if [ "$EUID" -eq 0 ] || [ -w "/etc/apache2/sites-available/" ]; then
    cat > "$VHOST_FILE" <<EOF
<VirtualHost *:80>
    ServerName $SERVER_NAME
    DocumentRoot $(pwd)
    
    <Directory $(pwd)>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/accounting-error.log
    CustomLog \${APACHE_LOG_DIR}/accounting-access.log combined
</VirtualHost>
EOF
    
    print_success "VirtualHost erstellt: $VHOST_FILE"
    
    # Site aktivieren
    if command -v a2ensite &> /dev/null; then
        a2ensite accounting
        a2enmod rewrite
        systemctl restart apache2
        print_success "Site aktiviert und Apache neugestartet"
    fi
else
    print_warning "Keine Berechtigung für Apache-Konfiguration"
    print_info "Bitte manuell einrichten oder mit sudo ausführen"
fi

# ==========================================
# Fertig!
# ==========================================
echo
print_header "Setup abgeschlossen!"
echo

if [ "$INSTALL_TYPE" == "server" ]; then
    echo -e "${GREEN}Server-Instanz erfolgreich eingerichtet!${NC}"
    echo
    echo "Nächste Schritte:"
    echo "1. Server aufrufen: http://$SERVER_NAME"
    echo "2. API testen: http://$SERVER_NAME/api/endpoint.php?action=health"
    echo "3. API-Key an Clients weitergeben"
    echo
    echo -e "${YELLOW}API-Key:${NC} $API_KEY"
else
    echo -e "${GREEN}Client-Instanz erfolgreich eingerichtet!${NC}"
    echo
    echo "Nächste Schritte:"
    echo "1. Client aufrufen: http://$SERVER_NAME"
    echo "2. Server-Verbindung testen im 'Gemeinsam'-Bereich"
fi

echo
print_info "Weitere Informationen: README.md"
echo
