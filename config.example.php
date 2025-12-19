<?php
/**
 * Konfigurationsvorlage für verteiltes Buchhaltungssystem
 * 
 * WICHTIG: 
 * - Diese Datei in config.php kopieren
 * - Werte anpassen
 * - config.php NICHT ins Git committen!
 */

return [
    /**
     * Instanz-Typ
     * true  = Zentraler Server (API aktiv, zentrale DB)
     * false = Raspberry Pi Client (nutzt Server-API)
     */
    'IS_SERVER' => false,

    /**
     * API-URL des zentralen Servers
     * Nur für Clients relevant (IS_SERVER = false)
     * Beispiel: 'https://buchhaltung.example.com/api/endpoint.php'
     */
    'API_URL' => '',

    /**
     * Gemeinsamer API-Schlüssel
     * MUSS auf ALLEN Instanzen identisch sein!
     * Mindestens 32 Zeichen, kryptographisch sicher
     * 
     * Generierung: bin2hex(random_bytes(32))
     */
    'API_KEY' => 'your-64-character-hex-key-here-replace-this-value-immediately',

    /**
     * Datenbank-Konfiguration
     * 
     * Server: Zentrale Datenbank
     * Client: Lokale SQLite oder MySQL
     */
    'DB' => [
        'driver' => 'mysql',  // mysql oder sqlite
        'host'   => 'localhost',
        'port'   => 3306,
        'name'   => 'accounting',
        'user'   => 'root',
        'pass'   => '',
        'charset' => 'utf8mb4',
        
        // Für SQLite (auf Pis empfohlen):
        'sqlite_path' => __DIR__ . '/database/local.db'
    ],

    /**
     * Anwendungs-Einstellungen
     */
    'APP' => [
        'name' => 'LedgerNode',
        'version' => '1.0.0',
        'timezone' => 'Europe/Berlin',
        'debug' => false,  // Nur in Entwicklung true
    ],

    /**
     * Session-Einstellungen
     */
    'SESSION' => [
        'lifetime' => 7200,  // 2 Stunden
        'name' => 'ACC_SESSION',
    ],

    /**
     * Sicherheits-Einstellungen
     */
    'SECURITY' => [
        'rate_limit_per_minute' => 60,
        'max_failed_requests' => 10,
    ]
];
