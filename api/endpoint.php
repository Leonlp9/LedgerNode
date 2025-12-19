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
use App\Core\Database;

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

// Bestimme den Request-Pfad. Erlaube auch Übergabe über ?path=/api/.. (für direkte Aufrufe ohne Rewrite)
$reqPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (isset($_REQUEST['path']) && is_string($_REQUEST['path']) && $_REQUEST['path'] !== '') {
    // Normalisiere sicherheitshalber
    $reqPath = $_REQUEST['path'];
}
// Entferne optionales App-Base (z.B. /LedgerNode) falls vorhanden
if (strpos($reqPath, '/api/') === false) {
    $pathParts = explode('/api/', $reqPath);
    if (count($pathParts) > 1) {
        $reqPath = '/api/' . end($pathParts);
    }
}

// Behandle einfache private-API Pfade als schnelle Stubs (nur wenn diese Instanz KEIN Server ist)
if (preg_match('#^/api/private(?:/.*)?$#', $reqPath) && !Config::isServer()) {
    header('Content-Type: application/json');

    // Hilfsfunktion: JSON-Body parsen falls vorhanden
    $rawInput = file_get_contents('php://input');
    $jsonInput = json_decode($rawInput, true);
    if (!is_array($jsonInput)) {
        $jsonInput = [];
    }

    // Instanz der DB
    try {
        $db = Database::getInstance();
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Datenbankverbindung fehlgeschlagen']);
        exit;
    }

    // ------------------------------------------------------------------------------------
    // NEU: Client-side Update-Check (lokale Git-Operationen)
    // GET/POST /api/private/check_updates
    if (($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') && preg_match('#^/api/private/check_updates$#', $reqPath)) {
        try {
            $output = [];
            $return = 0;
            // Wechsle in Projektroot
            $projectRoot = dirname(__DIR__);
            if (is_dir($projectRoot)) chdir($projectRoot);

            // git fetch
            exec('git fetch 2>&1', $output, $return);
            if ($return !== 0) {
                echo json_encode(['success' => false, 'error' => 'git fetch failed', 'details' => $output]);
                exit;
            }

            // bestimme remote branch
            $branch = 'origin/master';
            $out = [];
            $ret = 0;
            exec('git rev-parse --abbrev-ref origin/HEAD 2>&1', $out, $ret);
            if ($ret === 0 && !empty($out[0]) && strpos($out[0], '/') !== false) {
                $parts = explode('/', trim($out[0]));
                $branch = 'origin/' . end($parts);
            }

            // prüfe commits
            $commitOutput = [];
            $ret = 0;
            exec("git log HEAD..{$branch} --oneline 2>&1", $commitOutput, $ret);
            if ($ret !== 0) {
                echo json_encode(['success' => false, 'error' => 'git log failed', 'details' => $commitOutput]);
                exit;
            }

            if (empty($commitOutput)) {
                echo json_encode(['success' => true, 'data' => ['updates' => false, 'commits' => [], 'branch' => $branch]]);
                exit;
            }

            echo json_encode(['success' => true, 'data' => ['updates' => true, 'commits' => $commitOutput, 'branch' => $branch]]);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    // ------------------------------------------------------------------------------------
    // POST /api/private/install_updates -> führt git pull lokal aus (nur nach Bestätigung durch Client UI)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && preg_match('#^/api/private/install_updates$#', $reqPath)) {
        try {
            $exclude = ['config.php', 'config.ini', 'uploads'];
            $projectRoot = dirname(__DIR__);
            if (is_dir($projectRoot)) chdir($projectRoot);

            // mark exclude
            foreach ($exclude as $item) {
                exec('git update-index --assume-unchanged ' . escapeshellarg($item) . ' 2>&1');
            }

            $out = [];
            $ret = 0;
            exec('git checkout -- . 2>&1', $out, $ret);
            if ($ret !== 0) {
                // undo
                foreach ($exclude as $item) {
                    exec('git update-index --no-assume-unchanged ' . escapeshellarg($item) . ' 2>&1');
                }
                echo json_encode(['success' => false, 'error' => 'git checkout failed', 'details' => $out]);
                exit;
            }

            // determine remote branch
            $branch = 'origin/master';
            $out2 = [];$r2=0;
            exec('git rev-parse --abbrev-ref origin/HEAD 2>&1', $out2, $r2);
            if ($r2 === 0 && !empty($out2[0]) && strpos($out2[0], '/') !== false) {
                $parts = explode('/', trim($out2[0]));
                $branch = 'origin/' . end($parts);
            }

            // pull
            $pullOut = [];$r3=0;
            exec('git pull ' . escapeshellarg($branch) . ' 2>&1', $pullOut, $r3);

            // undo exclude
            foreach ($exclude as $item) {
                exec('git update-index --no-assume-unchanged ' . escapeshellarg($item) . ' 2>&1');
            }

            if ($r3 !== 0) {
                echo json_encode(['success' => false, 'error' => 'git pull failed', 'details' => $pullOut]);
                exit;
            }

            echo json_encode(['success' => true, 'data' => ['message' => 'updated', 'output' => $pullOut]]);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    // /api/private/stats
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('#^/api/private/stats$#', $reqPath)) {
        try {
            $incomeRow = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM private_transactions WHERE type = 'income'");
            $expensesRow = $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM private_transactions WHERE type = 'expense'");
            $initialRow = $db->fetchOne("SELECT COALESCE(SUM(initial_balance), 0) as total FROM private_accounts");

            $income = (float) (isset($incomeRow['total']) ? $incomeRow['total'] : 0);
            $expenses = (float) (isset($expensesRow['total']) ? $expensesRow['total'] : 0);
            $initial = (float) (isset($initialRow['total']) ? $initialRow['total'] : 0);

            $balance = $initial + $income - $expenses;

            echo json_encode(['success' => true, 'data' => ['balance' => $balance, 'income' => $income, 'expenses' => $expenses]]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Fehler beim Berechnen der Statistiken']);
        }
        exit;
    }

    // /api/private/transactions (GET)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('#^/api/private/transactions$#', $reqPath)) {
        $limit = (int) (isset($_GET['limit']) ? $_GET['limit'] : 100);
        $offset = (int) (isset($_GET['offset']) ? $_GET['offset'] : 0);

        try {
            $sql = "SELECT t.*, a.name as account_name FROM private_transactions t LEFT JOIN private_accounts a ON t.account_id = a.id ORDER BY t.date DESC, t.created_at DESC LIMIT :limit OFFSET :offset";
            $rows = $db->fetchAll($sql, [':limit' => $limit, ':offset' => $offset]);
            echo json_encode(['success' => true, 'data' => $rows]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => []]);
        }
        exit;
    }

    // /api/private/accounts (GET)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('#^/api/private/accounts$#', $reqPath)) {
        try {
            // Verwende Unterabfrage, um den Kontostand pro Konto zu berechnen und GROUP BY-Probleme zu vermeiden
            $sql = "SELECT a.id, a.name, a.type, a.description, a.initial_balance, a.created_at, a.updated_at, 
                        (SELECT COALESCE(SUM(CASE WHEN t.type = 'income' THEN t.amount WHEN t.type = 'expense' THEN -t.amount ELSE 0 END), 0) FROM private_transactions t WHERE t.account_id = a.id) as balance 
                    FROM private_accounts a 
                    ORDER BY a.name ASC";
            $rows = $db->fetchAll($sql);
            echo json_encode(['success' => true, 'data' => $rows]);
        } catch (\Exception $e) {
            echo json_encode(['success' => true, 'data' => []]);
        }
        exit;
    }

    // /api/private/transactions (POST) -> erstelle echte Transaktion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && preg_match('#^/api/private/transactions$#', $reqPath)) {
        $input = array_merge($_POST, $jsonInput);

        $required = ['account_id', 'amount', 'description', 'date'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                echo json_encode(['success' => false, 'error' => "Pflichtfeld fehlt: {$field}"]);
                exit;
            }
        }

        $accountId = (int) $input['account_id'];
        $amount = (float) $input['amount'];
        $description = (string) $input['description'];
        $date = (string) $input['date'];
        $type = isset($input['type']) ? $input['type'] : 'expense';

        if (!in_array($type, array('income', 'expense'))) {
            $type = 'expense';
        }

        try {
            $data = [
                'account_id' => $accountId,
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
                'date' => $date,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $id = $db->insertArray('private_transactions', $data);

            echo json_encode(['success' => true, 'data' => ['id' => $id, 'message' => 'Transaktion erstellt']]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Fehler beim Erstellen der Transaktion']);
        }
        exit;
    }

    // /api/private/transactions/{id} (DELETE)
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && preg_match('#^/api/private/transactions/(\d+)$#', $reqPath, $m)) {
        $id = (int) $m[1];
        try {
            $affected = $db->execute('DELETE FROM private_transactions WHERE id = :id', array(':id' => $id));
            if ($affected === 0) {
                echo json_encode(['success' => false, 'error' => 'Transaktion nicht gefunden']);
            } else {
                echo json_encode(['success' => true, 'data' => ['message' => 'Transaktion gelöscht']]);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Fehler beim Löschen der Transaktion']);
        }
        exit;
    }

    // /api/private/stats/balance_series -> kumulierter Kontostand (letzte 12 Monate)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('#^/api/private/stats/balance_series$#', $reqPath)) {
        try {
            // Summe der initial balances
            $initialRow = $db->fetchOne("SELECT COALESCE(SUM(initial_balance), 0) as total FROM private_accounts");
            $initial = (float) ($initialRow['total'] ?? 0);

            // Net changes grouped by year/month
            $rows = $db->fetchAll("SELECT YEAR(`date`) as y, MONTH(`date`) as m, SUM(CASE WHEN `type` = 'income' THEN `amount` WHEN `type` = 'expense' THEN -`amount` ELSE 0 END) as net FROM private_transactions GROUP BY y, m ORDER BY y, m");

            $netMap = [];
            foreach ($rows as $r) {
                $key = sprintf('%04d-%02d', $r['y'], $r['m']);
                $netMap[$key] = (float) $r['net'];
            }

            // Erzeuge Labels für die letzten 12 Monate (älteste -> neueste)
            $labels = [];
            $data = [];
            $dt = new DateTime();
            $dt->modify('first day of this month');
            for ($i = 11; $i >= 0; $i--) {
                $m = clone $dt;
                $m->modify("-{$i} months");
                $labels[] = $m->format('M Y');
            }

            // Kumulierten Kontostand berechnen
            $cumulative = $initial;
            foreach ($labels as $label) {
                // Label zurück in Year-Month konvertieren
                $d = DateTime::createFromFormat('M Y', $label);
                if ($d === false) {
                    // Fallback: nimm heutiges Datum (sollte eigentlich nicht passieren)
                    $d = new DateTime();
                }
                $key = $d->format('Y-m');
                $monthlyNet = isset($netMap[$key]) ? $netMap[$key] : 0.0;
                $cumulative += $monthlyNet;
                $data[] = round($cumulative, 2);
            }

            echo json_encode(['success' => true, 'data' => ['labels' => $labels, 'data' => $data]]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Fehler beim Erzeugen der Balance-Serie']);
        }
        exit;
    }

    // /api/private/stats/expenses_by_category -> Summen pro Kategorie
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('#^/api/private/stats/expenses_by_category$#', $reqPath)) {
        try {
            $sql = "SELECT COALESCE(category, 'Uncategorized') as category, COALESCE(SUM(amount), 0) as total FROM private_transactions WHERE `type` = 'expense' GROUP BY category ORDER BY total DESC";
            $rows = $db->fetchAll($sql);

            $result = array_map(function($r) {
                return ['category' => $r['category'], 'amount' => (float) $r['total']];
            }, $rows ?: []);

            echo json_encode(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Fehler beim Berechnen der Ausgaben nach Kategorie']);
        }
        exit;
    }

    // Wenn kein Match -> 404
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Not Found (private-api)']);
    exit;
}

try {
    // Prüfe ob dies ein Server ist
    if (!Config::isServer()) {
        http_response_code(403);
        die(json_encode([
            'success' => false,
            'error' => 'API nicht verfügbar. Diese Instanz ist kein Server.'
        ]));
    }

    // --- SERVER: Starte asynchron einen Hintergrund-Update-Worker bei jedem Request ---
    $script = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'auto_update.php';
    if (file_exists($script)) {
        // Platform-abhängiger Hintergrundstart
        if (stripos(PHP_OS, 'WIN') === 0) {
            // Windows: start /B "" php "script"
            @exec('start /B "" php ' . escapeshellarg($script));
        } else {
            // Unix-like: php script > /dev/null 2>&1 &
            @exec('php ' . escapeshellarg($script) . ' > /dev/null 2>&1 &');
        }
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
