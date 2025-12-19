<?php
/**
 * Öffentlicher API-Endpunkt
 * 
 * Dies ist der einzige Einstiegspunkt für API-Requests
 * Wird über die URL /api/endpoint.php aufgerufen
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Api\Server;
use App\Core\Config;

// CORS-Headers (falls erforderlich)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: X-API-Key, Content-Type, Authorization');

// Preflight-Request behandeln
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Fehlerbehandlung
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

set_exception_handler(function($exception) {
    http_response_code(500);
    header('Content-Type: application/json');
    
    $response = [
        'success' => false,
        'error' => 'Internal Server Error'
    ];
    
    // Zeige Details nur im Debug-Modus
    if (Config::get('APP.debug', false)) {
        $response['debug'] = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
    }
    
    echo json_encode($response);
    exit;
});

try {
    // Prüfe ob dies ein Server ist
    if (!Config::isServer()) {
        http_response_code(403);
        die(json_encode([
            'success' => false,
            'error' => 'API nicht verfügbar. Diese Instanz ist kein Server.'
        ]));
    }

    // Instanziiere Server und verarbeite Request
    $api = new Server();
    $api->handleRequest();

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
