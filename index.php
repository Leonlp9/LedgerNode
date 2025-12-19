<?php
/**
 * Haupteinstiegspunkt
 * 
 * Lädt die Anwendung und zeigt das UI
 */

// Autoloader (composer oder eigener)
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Config;
use App\Core\Security;

// Error Reporting (nur in Development)
if (Config::get('APP.debug', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone setzen
date_default_timezone_set(Config::get('APP.timezone', 'Europe/Berlin'));

// Session starten
session_name(Config::get('SESSION.name', 'ACC_SESSION'));
session_set_cookie_params([
    'lifetime' => Config::get('SESSION.lifetime', 7200),
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// CSRF-Token generieren (falls nicht vorhanden)
Security::generateCsrfToken();

// Seitentitel
$pageTitle = Config::get('APP.name', 'Accounting');

// Layout laden
require_once __DIR__ . '/views/layout.php';
