<?php
/**
 * Haupteinstiegspunkt
 * 
 * Lädt die Anwendung und zeigt das UI
 */

// Prüfe, ob Composer-Autoloader vorhanden ist und vollständig generiert wurde.
$vendorAutoload = __DIR__ . '/vendor/autoload.php';
$composerAutoloadReal = __DIR__ . '/vendor/composer/autoload_real.php';
if (!file_exists($vendorAutoload) || !file_exists($composerAutoloadReal)) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html>';
    echo '<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>LedgerNode — Abhängigkeiten fehlen</title>';
    echo '<style>body{font-family:Arial,Helvetica,sans-serif;background:#f7f7f7;color:#222;padding:40px} .card{max-width:900px;margin:40px auto;background:#fff;padding:24px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.06)} h1{margin-top:0;color:#c0392b} p{line-height:1.5} .hint{background:#f1f1f1;padding:10px;border-radius:4px;font-size:0.95em} code{background:#eee;padding:2px 6px;border-radius:4px;font-family:monospace}</style>';
    echo '</head><body><div class="card">';
    echo '<h1>Abhängigkeiten nicht installiert</h1>';
    echo '<p>Der Composer-Autoloader oder dessen generierte Dateien fehlen. Die Anwendung kann nicht gestartet werden, bis die Abhängigkeiten installiert sind.</p>';
    echo '<h2>Was zu tun ist</h2>';
    echo '<ol>';
    echo '<li>Stellen Sie sicher, dass <code>composer</code> installiert ist: <code>composer --version</code>.</li>';
    echo '<li>Führen Sie im Projektstamm folgendes aus (PowerShell / CMD):</li>';
    echo '<li class="hint"><code>cd ' . htmlspecialchars(__DIR__, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . ' ; composer install</code></li>';
    echo '<li>Wenn Sie Composer nicht verwenden möchten, stellen Sie sicher, dass der Ordner <code>vendor/</code> vollständig vorhanden ist (inkl. <code>vendor/composer/autoload_real.php</code>).</li>';
    echo '<li>Prüfen Sie außerdem, ob <code>config.php</code> existiert (kopieren Sie ggf. <code>config.example.php</code> zu <code>config.php</code> und passen Sie die Datenbankeinstellungen an).</li>';
    echo '</ol>';
    echo '<p>Nach Installation der Abhängigkeiten oder Anlegen der Konfigurationsdatei laden Sie die Seite neu.</p>';
    echo '<p style="font-size:.9em;color:#666">Falls das Problem weiterhin besteht: Prüfen Sie die Datei <code>vendor/autoload.php</code> auf fehlende <code>vendor/composer/autoload_real.php</code> oder lesen Sie die README.</p>';
    echo '</div></body></html>';
    exit(1);
}

// Autoloader (composer oder eigener)
require_once $vendorAutoload;

use App\Core\Config;
use App\Core\Security;
use App\Database\SchemaManager;

// Versuche die Konfiguration früh zu laden und fange Fehler ab, damit der Anwender
// eine aussagekräftige, freundlich formatierte Fehlermeldung im Browser sieht.
try {
    // Diese Methode initialisiert und validiert die Konfiguration
    Config::getInstance();

    // Initialisiere Datenbank-Schema (erstellt Tabellen falls nötig)
    SchemaManager::init();
} catch (\Throwable $e) {
    // Technische Details in temporäres Log schreiben (nur für Admins lesbar auf dem Server)
    $logFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'ledger_error.log';
    error_log(sprintf("%s - Startup error: %s\n", date('c'), (string)$e), 3, $logFile);
    // Auch in das PHP-Errorlog schreiben
    error_log("LedgerNode startup error: " . (string)$e);

    // Benutzerfreundliche Fehlerseite anzeigen (DE)
    http_response_code(500);
    $safeMessage = htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $showDetails = (isset($_GET['debug']) && $_GET['debug'] === '1');
    echo '<!doctype html>';
    echo '<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>LedgerNode — Konfigurationsfehler</title>';
    echo '<style>body{font-family:Arial,Helvetica,sans-serif;background:#f7f7f7;color:#222;padding:40px} .card{max-width:800px;margin:40px auto;background:#fff;padding:24px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.06)} h1{margin-top:0;color:#c0392b} p{line-height:1.5} .hint{background:#f1f1f1;padding:10px;border-radius:4px;font-size:0.95em}</style>';
    echo '</head><body><div class="card">';
    echo '<h1>Einrichtungsfehler</h1>';
    echo '<p>Die Anwendung konnte nicht gestartet werden, weil die Server-Konfiguration unvollständig oder fehlerhaft ist.</p>';
    echo '<p class="hint"><strong>Fehler (Kurz):</strong> ' . $safeMessage . '</p>';
    echo '<h2>Was Sie jetzt tun können</h2>';
    echo '<ul>';
    echo '<li>Öffnen Sie die Datei <code>config.php</code> im Projektstamm (oder kopieren Sie <code>config.example.php</code> zu <code>config.php</code> und passen Sie sie an).</li>';
    echo '<li>Prüfen Sie insbesondere folgende Werte: <code>IS_SERVER</code>, <code>API_KEY</code> (mind. 32 Zeichen) und für Clients <code>API_URL</code>.</li>';
    echo '<li>Wenn Sie sich nicht sicher sind, lesen Sie die README oder QUICKSTART im Projektordner.</li>';
    echo '</ul>';
    echo '<p>Technische Details wurden in <code>' . htmlspecialchars($logFile, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . '</code> protokolliert.</p>';
    if ($showDetails) {
        echo '<h3>Technische Details</h3>';
        echo '<pre style="white-space:pre-wrap;background:#111;color:#eee;padding:12px;border-radius:4px">' . htmlspecialchars((string)$e, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
    } else {
        echo '<p style="font-size:.9em;color:#666">Für Debugging können Sie die technische Ausgabe sehen, indem Sie <code>?debug=1</code> an die URL anhängen (nur für lokale Tests).</p>';
    }
    echo '</div></body></html>';
    exit(1);
}

// Wenn dies eine Server-Instanz ist, soll sie KEINE Benutzeroberfläche ausliefern.
// Nur die API (z.B. /api/endpoint.php) soll verfügbar sein. Alle UI-Anfragen werden mit 404 beantwortet.
if (Config::isServer()) {
    // Return 404 for UI requests. If client expects JSON (AJAX/API) return JSON.
    http_response_code(404);
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $isApiLike = (strpos($accept, 'application/json') !== false) || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false);
    if ($isApiLike) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Not Found',
            'message' => 'Diese Instanz ist als Server konfiguriert und liefert keine Benutzeroberfläche. Bitte verwenden Sie die API unter /api/endpoint.php.'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>404 — Nicht gefunden</title></head><body style="font-family:Arial,Helvetica,sans-serif;padding:40px;color:#222;background:#f7f7f7"><div style="max-width:800px;margin:40px auto;background:#fff;padding:24px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.06)"><h1>404 — Nicht gefunden</h1><p>Diese Instanz läuft im Server-Modus und stellt keine Benutzeroberfläche bereit. Verwenden Sie die API: <code>/api/endpoint.php</code></p></div></body></html>';
    }
    exit;
}

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
