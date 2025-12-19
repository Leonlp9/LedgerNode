<?php
/**
 * Konfigurations-Manager
 * 
 * Lädt und validiert die Konfiguration
 * Stellt sicher, dass alle erforderlichen Werte vorhanden sind
 */

namespace App\Core;

class Config
{
    private static ?array $config = null;
    private static ?self $instance = null;

    private function __construct()
    {
        $this->load();
    }

    /**
     * Singleton-Instanz abrufen
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Konfiguration laden und validieren
     */
    private function load(): void
    {
        $configPath = dirname(__DIR__, 2) . '/config.php';
        
        if (!file_exists($configPath)) {
            throw new \RuntimeException(
                'config.php nicht gefunden! Bitte config.example.php kopieren und anpassen.'
            );
        }

        self::$config = require $configPath;
        $this->validate();
    }

    /**
     * Konfiguration validieren
     */
    private function validate(): void
    {
        $required = ['IS_SERVER', 'API_KEY', 'DB'];
        
        foreach ($required as $key) {
            if (!isset(self::$config[$key])) {
                throw new \RuntimeException("Fehlende Konfiguration: {$key}");
            }
        }

        // API_KEY Validierung
        if (strlen(self::$config['API_KEY']) < 32) {
            throw new \RuntimeException('API_KEY muss mindestens 32 Zeichen lang sein!');
        }

        // Client-spezifische Validierung
        if (!self::$config['IS_SERVER']) {
            if (empty(self::$config['API_URL'])) {
                throw new \RuntimeException('API_URL muss für Clients konfiguriert sein!');
            }
            
            if (!filter_var(self::$config['API_URL'], FILTER_VALIDATE_URL)) {
                throw new \RuntimeException('API_URL ist keine gültige URL!');
            }
        }
    }

    /**
     * Konfigurationswert abrufen
     * 
     * @param string $key Dot-Notation möglich: 'DB.host'
     * @param mixed $default Standardwert falls nicht vorhanden
     */
    public static function get(string $key, $default = null)
    {
        if (self::$config === null) {
            self::getInstance();
        }

        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Prüft ob dies der zentrale Server ist
     */
    public static function isServer(): bool
    {
        return self::get('IS_SERVER', false) === true;
    }

    /**
     * Prüft ob dies ein Client ist
     */
    public static function isClient(): bool
    {
        return !self::isServer();
    }

    /**
     * API-Key abrufen
     */
    public static function getApiKey(): string
    {
        return self::get('API_KEY');
    }

    /**
     * Server API-URL abrufen (nur für Clients)
     */
    public static function getApiUrl(): ?string
    {
        return self::get('API_URL');
    }

    /**
     * Komplette Konfiguration abrufen
     */
    public static function all(): array
    {
        if (self::$config === null) {
            self::getInstance();
        }
        return self::$config;
    }
}
