<?php
/**
 * Security-Manager
 * 
 * Kümmert sich um:
 * - API-Key-Validierung
 * - Rate-Limiting
 * - Request-Authentifizierung
 */

namespace App\Core;

class Security
{
    /**
     * Validiert den API-Key aus dem Request
     * 
     * @param string|null $providedKey Key aus Header oder POST
     * @return bool
     */
    public static function validateApiKey(?string $providedKey): bool
    {
        if (empty($providedKey)) {
            return false;
        }

        $configKey = Config::getApiKey();
        
        // Timing-safe Vergleich gegen Timing-Attacken
        return hash_equals($configKey, $providedKey);
    }

    /**
     * Extrahiert API-Key aus verschiedenen Quellen
     * 
     * Priorisierung:
     * 1. Authorization Header (Bearer Token)
     * 2. X-API-Key Header
     * 3. POST-Parameter 'api_key'
     * 4. JSON-Body 'api_key' (wenn Content-Type: application/json)
     * 5. Query-Parameter 'api_key'
     */
    public static function extractApiKey(): ?string
    {
        // 1. Authorization Header
        $authHeader = self::getHeader('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            return trim($matches[1]);
        }

        // 2. X-API-Key Header
        $apiKeyHeader = self::getHeader('X-API-Key');
        if ($apiKeyHeader) {
            return $apiKeyHeader;
        }

        // 3. POST-Parameter (Form-POST)
        if (isset($_POST['api_key']) && !empty($_POST['api_key'])) {
            return $_POST['api_key'];
        }

        // 4. JSON-Body (Fetch/JS sendet häufig JSON -> PHP füllt $_POST nicht)
        $rawInput = file_get_contents('php://input');
        if (!empty($rawInput)) {
            $jsonInput = json_decode($rawInput, true);
            if (is_array($jsonInput) && isset($jsonInput['api_key']) && !empty($jsonInput['api_key'])) {
                return $jsonInput['api_key'];
            }
        }

        // 5. Query-Parameter
        if (isset($_GET['api_key']) && !empty($_GET['api_key'])) {
            return $_GET['api_key'];
        }

        return null;
    }

    /**
     * Holt einen HTTP-Header (case-insensitive)
     *
     * Diese Funktion versucht mehrere Varianten, da Webserver (Apache, Nginx, IIS, FastCGI)
     * Header unterschiedlich in $_SERVER oder über getallheaders()/apache_request_headers()
     * bereitstellen. Wir prüfen mehrere Kandidaten wie HTTP_<NAME>, REDIRECT_HTTP_<NAME>
     * und nutzen getallheaders()/apache_request_headers() als Fallback.
     */
    private static function getHeader(string $name): ?string
    {
        // Normalisiere Namen
        $normalized = str_replace('-', '_', $name);
        $upper = strtoupper($normalized);

        $candidates = [
            "HTTP_{$upper}",
            $upper,
            "REDIRECT_HTTP_{$upper}",
            // manche SAPI liefern AUTHORIZATION ohne HTTP_ prefix
            'AUTHORIZATION'
        ];

        // Prüfe $_SERVER-Varianten
        foreach ($candidates as $cand) {
            if (isset($_SERVER[$cand]) && $_SERVER[$cand] !== '') {
                return $_SERVER[$cand];
            }
        }

        // Fallback: getallheaders() / apache_request_headers() (case-insensitive)
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (is_array($headers)) {
                foreach ($headers as $key => $value) {
                    if (strcasecmp($key, $name) === 0 || strcasecmp(str_replace('_', '-', $key), $name) === 0) {
                        return $value;
                    }
                }
            }
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (is_array($headers)) {
                foreach ($headers as $key => $value) {
                    if (strcasecmp($key, $name) === 0) {
                        return $value;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Prüft ob die Server-API aufgerufen werden darf
     * 
     * @throws \Exception wenn API nicht verfügbar
     */
    public static function enforceServerApi(): void
    {
        if (!Config::isServer()) {
            http_response_code(403);
            die(json_encode([
                'error' => 'API not available',
                'message' => 'Diese Instanz ist kein Server. IS_SERVER muss true sein.'
            ]));
        }
    }

    /**
     * Vollständige API-Authentifizierung
     * 
     * Prüft:
     * 1. IS_SERVER === true
     * 2. API-Key ist gültig
     * 
     * @throws \Exception bei fehlgeschlagener Authentifizierung
     */
    public static function authenticateApiRequest(): void
    {
        // Server-Check
        self::enforceServerApi();

        // API-Key-Check
        $providedKey = self::extractApiKey();
        
        if (!self::validateApiKey($providedKey)) {
            http_response_code(401);
            die(json_encode([
                'error' => 'Unauthorized',
                'message' => 'Ungültiger oder fehlender API-Key'
            ]));
        }

        // Optional: Rate-Limiting
        self::checkRateLimit();
    }

    /**
     * Einfaches Rate-Limiting (IP-basiert)
     * 
     * Speichert Requests in Session/File
     * Produktiv: Redis oder Memcached verwenden!
     */
    private static function checkRateLimit(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $limit = Config::get('SECURITY.rate_limit_per_minute', 60);
        $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($ip) . '.tmp';

        $now = time();
        $requests = [];

        // Lade existierende Requests
        if (file_exists($cacheFile)) {
            $data = file_get_contents($cacheFile);
            $requests = json_decode($data, true) ?? [];
        }

        // Filter alte Requests (älter als 1 Minute)
        $requests = array_filter($requests, fn($timestamp) => $timestamp > ($now - 60));

        // Prüfe Limit
        if (count($requests) >= $limit) {
            http_response_code(429);
            die(json_encode([
                'error' => 'Too Many Requests',
                'message' => "Rate-Limit überschritten. Max. {$limit} Requests/Minute."
            ]));
        }

        // Füge aktuellen Request hinzu
        $requests[] = $now;
        file_put_contents($cacheFile, json_encode($requests));
    }

    /**
     * CSRF-Token generieren (für Formular-Submissions)
     */
    public static function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * CSRF-Token validieren
     */
    public static function validateCsrfToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Input-Sanitierung (XSS-Schutz)
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
