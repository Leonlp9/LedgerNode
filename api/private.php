<?php
/**
 * Local API Endpoint for Private Data
 * 
 * Handles API requests for private/local data including invoices
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\Config;
use App\Api\PrivateInvoices;

// Set JSON header
header('Content-Type: application/json');

// Only for clients (not servers)
if (Config::isServer()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Not available on server']);
    exit;
}

try {
    $action = $_REQUEST['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    // Route to appropriate handler
    switch ($action) {
        // Invoice endpoints
        case 'getInvoices':
            $handler = new PrivateInvoices();
            $result = $handler->getInvoices($_GET);
            sendSuccess($result);
            break;

        case 'getInvoice':
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                throw new InvalidArgumentException('Ungültige ID');
            }
            $handler = new PrivateInvoices();
            $result = $handler->getInvoice($id);
            if (!$result) {
                throw new RuntimeException('Rechnung nicht gefunden');
            }
            sendSuccess($result);
            break;

        case 'createInvoice':
            if ($method !== 'POST') {
                throw new RuntimeException('Nur POST erlaubt');
            }
            $handler = new PrivateInvoices();
            $file = $_FILES['file'] ?? null;
            $result = $handler->createInvoice($_POST, $file);
            sendSuccess($result);
            break;

        case 'updateInvoice':
            if ($method !== 'POST') {
                throw new RuntimeException('Nur POST erlaubt');
            }
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new InvalidArgumentException('Ungültige ID');
            }
            $handler = new PrivateInvoices();
            $file = $_FILES['file'] ?? null;
            $result = $handler->updateInvoice($id, $_POST, $file);
            sendSuccess($result);
            break;

        case 'deleteInvoice':
            if ($method !== 'POST') {
                throw new RuntimeException('Nur POST erlaubt');
            }
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new InvalidArgumentException('Ungültige ID');
            }
            $handler = new PrivateInvoices();
            $result = $handler->deleteInvoice($id);
            sendSuccess($result);
            break;

        case 'linkInvoiceToTransaction':
            if ($method !== 'POST') {
                throw new RuntimeException('Nur POST erlaubt');
            }
            $invoiceId = (int)($_POST['invoice_id'] ?? 0);
            $transactionId = (int)($_POST['transaction_id'] ?? 0);
            if ($invoiceId <= 0 || $transactionId <= 0) {
                throw new InvalidArgumentException('Ungültige IDs');
            }
            $handler = new PrivateInvoices();
            $result = $handler->linkToTransaction($invoiceId, $transactionId);
            sendSuccess($result);
            break;

        case 'unlinkInvoiceFromTransaction':
            if ($method !== 'POST') {
                throw new RuntimeException('Nur POST erlaubt');
            }
            $invoiceId = (int)($_POST['invoice_id'] ?? 0);
            if ($invoiceId <= 0) {
                throw new InvalidArgumentException('Ungültige ID');
            }
            $handler = new PrivateInvoices();
            $result = $handler->unlinkFromTransaction($invoiceId);
            sendSuccess($result);
            break;

        case 'getAvailableTransactions':
            $invoiceId = (int)($_GET['invoice_id'] ?? 0);
            if ($invoiceId <= 0) {
                throw new InvalidArgumentException('Ungültige ID');
            }
            $handler = new PrivateInvoices();
            $result = $handler->getAvailableTransactions($invoiceId);
            sendSuccess($result);
            break;

        case 'getInvoiceStats':
            $handler = new PrivateInvoices();
            $result = $handler->getStats();
            sendSuccess($result);
            break;

        case 'createInvoiceWithPDF':
            if ($method !== 'POST') {
                throw new RuntimeException('Nur POST erlaubt');
            }
            
            // Server-side validation for better error messages
            $validationErrors = [];
            $input = $_POST;

            // Required fields basic check
            $required = ['invoice_number', 'invoice_date', 'sender', 'recipient', 'line_items'];
            foreach ($required as $f) {
                if (!isset($input[$f]) || $input[$f] === '' || $input[$f] === null) {
                    $validationErrors[] = [
                        'field' => $f,
                        'message' => 'Dieses Feld wird benötigt.'
                    ];
                }
            }

            // If line_items exists try to decode and validate structure
            if (isset($input['line_items']) && $input['line_items'] !== '') {
                $raw = $input['line_items'];
                $decoded = null;
                if (is_string($raw)) {
                    $decoded = json_decode($raw, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $validationErrors[] = [
                            'field' => 'line_items',
                            'message' => 'Ungültiges JSON: ' . json_last_error_msg()
                        ];
                    }
                } elseif (is_array($raw)) {
                    $decoded = $raw;
                } else {
                    $validationErrors[] = [
                        'field' => 'line_items',
                        'message' => 'Erwartet JSON-Array oder Array.'
                    ];
                }

                if (is_array($decoded)) {
                    // Validate each item
                    foreach ($decoded as $idx => $item) {
                        $prefix = "line_items[{$idx}]";
                        if (!is_array($item)) {
                            $validationErrors[] = ['field' => $prefix, 'message' => 'Position muss ein Objekt/Array sein.'];
                            continue;
                        }
                        // description
                        if (empty($item['description'])) {
                            $validationErrors[] = ['field' => $prefix . '.description', 'message' => 'Beschreibung fehlt.'];
                        }
                        // quantity
                        if (!isset($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                            $validationErrors[] = ['field' => $prefix . '.quantity', 'message' => 'Menge muss eine Zahl > 0 sein.'];
                        }
                        // price
                        if (!isset($item['price']) || !is_numeric($item['price']) || $item['price'] < 0) {
                            $validationErrors[] = ['field' => $prefix . '.price', 'message' => 'Preis muss eine Zahl >= 0 sein.'];
                        }
                        // tax
                        // Accept missing tax (default to 0). If present, it must be numeric and >= 0
                        if (!isset($item['tax'])) {
                            // ok - will default to 0 later when generating
                        } elseif (!is_numeric($item['tax']) || $item['tax'] < 0) {
                            $validationErrors[] = ['field' => $prefix . '.tax', 'message' => 'MwSt. muss eine Zahl >= 0 sein.'];
                        }
                    }
                }
            }

            // If validation errors occurred, return structured 422 response
            if (!empty($validationErrors)) {
                // Debug: log received POST keys to server error log to help track missing fields
                try {
                    error_log('createInvoiceWithPDF validation failed. Received POST keys: ' . json_encode(array_keys($_POST)));
                    if (isset($_POST['line_items'])) {
                        error_log('createInvoiceWithPDF received line_items length: ' . strlen($_POST['line_items']));
                    }
                } catch (\Throwable $logEx) {
                    // ignore logging errors
                }
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'error' => 'Validierung fehlgeschlagen',
                    'errors' => $validationErrors
                ]);
                exit;
            }

            // Use the PDF generator
            require_once __DIR__ . '/../src/Services/InvoicePDFGenerator.php';
            $pdfGenerator = new \App\Services\InvoicePDFGenerator();
            
            // Generate PDF
            $pdfPath = $pdfGenerator->generate($_POST);
            
            // Save invoice to database
            $handler = new PrivateInvoices();
            $invoiceData = $_POST;
            
            // Ensure the generated PDF is web-accessible. We'll copy it into public/uploads/invoices
            $basePath = dirname(__DIR__);
            // Use the project's uploads directory so FileUpload::getFileUrl can map it to /uploads/...
            $uploadBase = $basePath . DIRECTORY_SEPARATOR . 'uploads';
            $year = date('Y');
            $month = date('m');
            $targetDir = $uploadBase . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $month;
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }

            $destPath = $targetDir . DIRECTORY_SEPARATOR . basename($pdfPath);
            $relativePath = null;
            $fakeFile = null;

            if (file_exists($pdfPath)) {
                // Copy into uploads/YYYY/MM/ so FileUpload->getFileUrl will work
                $copied = @copy($pdfPath, $destPath);
                $moved = false;
                if (!$copied) {
                    // fallback to rename (move)
                    $moved = @rename($pdfPath, $destPath);
                }

                // If copy succeeded, remove the original temp file to avoid duplicates
                if ($copied) {
                    try { @unlink($pdfPath); } catch (\Throwable $_) { /* ignore */ }
                }

                // Store absolute filesystem path in DB so FileUpload can work with it
                $invoiceData['file_path'] = realpath($destPath) ?: $destPath;
                $invoiceData['file_name'] = basename($destPath);
                // Also compute a relative URL for immediate response (client can use file_url from GET later)
                $relativePath = str_replace('\\', '/', str_replace($basePath, '', $invoiceData['file_path']));
                if (substr($relativePath, 0, 1) !== '/') $relativePath = '/' . $relativePath;
            }

            $result = $handler->createInvoice($invoiceData, $fakeFile);

             // Return success with PDF URL
            // Build full URL if possible
            $fullUrl = null;
            $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
            if ($docRoot && !empty($invoiceData['file_path'])) {
                $real = realpath($invoiceData['file_path']);
                if ($real && strpos($real, $docRoot) === 0) {
                    $urlPath = str_replace('\\', '/', substr($real, strlen($docRoot)));
                    if (substr($urlPath, 0, 1) !== '/') $urlPath = '/' . $urlPath;
                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
                    $fullUrl = $scheme . '://' . $host . $urlPath;
                }
            }

            sendSuccess([
                'id' => $result['id'],
                'message' => $result['message'],
                'pdf_url' => $relativePath,
                'pdf_full_url' => $fullUrl,
                'pdf_path' => $pdfPath
            ]);
             break;

        case 'generateBackup':
            if ($method !== 'POST') {
                throw new RuntimeException('Nur POST erlaubt');
            }
            
            require_once __DIR__ . '/../src/Services/BackupExporter.php';
            $exporter = new \App\Services\BackupExporter();
            
            $period = $_POST['period'] ?? 'all';
            $params = [];
            
            if ($period === 'month') {
                $params['year'] = $_POST['year'] ?? date('Y');
                $params['month'] = $_POST['month'] ?? date('m');
            } elseif ($period === 'year') {
                $params['year'] = $_POST['year'] ?? date('Y');
            }
            
            $zipPath = $exporter->generatePrivateBackup($period, $params);

            // Ensure the file is web-accessible: copy/move into public/backups if necessary
            $basePath = dirname(__DIR__);
            $publicBackups = $basePath . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'backups';
            if (!is_dir($publicBackups)) {
                @mkdir($publicBackups, 0755, true);
            }

            if (!file_exists($zipPath)) {
                throw new RuntimeException('Erstelltes Archiv konnte nicht gefunden werden: ' . $zipPath);
            }

            $filename = basename($zipPath);
            $destPath = $publicBackups . DIRECTORY_SEPARATOR . $filename;

            // Wenn die Datei nicht bereits im public/backups liegt, kopieren
            if (realpath($zipPath) !== realpath($destPath)) {
                if (!@copy($zipPath, $destPath)) {
                    // Falls copy fehlschlägt, versuche mit rename (move)
                    if (!@rename($zipPath, $destPath)) {
                        // Wenn beides fehlschlägt, gib eine aussagekräftige Fehlermeldung
                        throw new RuntimeException('Archiv konnte nicht in das öffentliche Verzeichnis kopiert werden. Pfad: ' . $zipPath);
                    }
                } else {
                    // Optional: lösche die Originaldatei im temp, falls wir kopiert haben
                    if (is_writable(dirname($zipPath))) {
                        @unlink($zipPath);
                    }
                }
            }

            // Erzeuge eine HTTP-URL, indem der Pfad relativ zum DOCUMENT_ROOT gesetzt wird
            $fullUrl = null;
            $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
            $destReal = realpath($destPath);
            if ($docRoot && $destReal && strpos($destReal, $docRoot) === 0) {
                $urlPath = str_replace('\\', '/', substr($destReal, strlen($docRoot)));
                if (substr($urlPath, 0, 1) !== '/') $urlPath = '/' . $urlPath;
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
                $fullUrl = $scheme . '://' . $host . $urlPath;
            }

            // Fallback: relative URL innerhalb der App (slash-normalisiert)
            $relativeUrl = str_replace($basePath, '', $destPath);
            $relativeUrl = str_replace('\\', '/', $relativeUrl);
            if (substr($relativeUrl, 0, 1) !== '/') {
                $relativeUrl = '/' . $relativeUrl;
            }

            sendSuccess([
                'download_url' => $fullUrl ?? $relativeUrl,
                'filename' => $filename,
                'message' => 'Backup erfolgreich erstellt'
            ]);
            break;

        // Private stats endpoint
        case 'stats':
            $db = Database::getInstance();

            // Determine driver to use DB-specific SQL functions
            $driver = \App\Core\Config::get('DB.driver', 'mysql');

            if ($driver === 'sqlite') {
                $balance = $db->fetchOne("\n                SELECT COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) as balance\n                FROM private_transactions\n            ")['balance'] ?? 0;

                $income = $db->fetchOne("\n                SELECT COALESCE(SUM(amount), 0) as total\n                FROM private_transactions\n                WHERE type = 'income'\n                AND strftime('%Y-%m', date) = strftime('%Y-%m', 'now')\n            ")['total'] ?? 0;

                $expenses = $db->fetchOne("\n                SELECT COALESCE(SUM(amount), 0) as total\n                FROM private_transactions\n                WHERE type = 'expense'\n                AND strftime('%Y-%m', date) = strftime('%Y-%m', 'now')\n            ")['total'] ?? 0;
            } else {
                // Default to MySQL-compatible SQL
                $balance = $db->fetchOne("\n                SELECT COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) as balance\n                FROM private_transactions\n            ")['balance'] ?? 0;

                $income = $db->fetchOne("\n                SELECT COALESCE(SUM(amount), 0) as total\n                FROM private_transactions\n                WHERE type = 'income'\n                AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')\n            ")['total'] ?? 0;

                $expenses = $db->fetchOne("\n                SELECT COALESCE(SUM(amount), 0) as total\n                FROM private_transactions\n                WHERE type = 'expense'\n                AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')\n            ")['total'] ?? 0;
            }

            sendSuccess([
                'balance' => $balance,
                'income' => $income,
                'expenses' => $expenses
            ]);
            break;

        // Private transactions
        case 'transactions':
            $db = Database::getInstance();
            $sql = "
                SELECT 
                    t.*,
                    a.name as account_name
                FROM private_transactions t
                LEFT JOIN private_accounts a ON t.account_id = a.id
                ORDER BY t.date DESC, t.created_at DESC
                LIMIT 100
            ";
            $transactions = $db->fetchAll($sql);
            sendSuccess($transactions);
            break;

        // Private accounts
        case 'accounts':
            $db = Database::getInstance();
            $sql = "
                SELECT 
                    a.*,
                    COUNT(t.id) as transaction_count,
                    COALESCE(SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE -t.amount END), 0) as balance
                FROM private_accounts a
                LEFT JOIN private_transactions t ON a.id = t.account_id
                GROUP BY a.id
                ORDER BY a.name ASC
            ";
            $accounts = $db->fetchAll($sql);
            sendSuccess($accounts);
            break;

        default:
            throw new RuntimeException('Unbekannte Action: ' . $action);
    }

} catch (Throwable $e) {
    // Log the full error for debugging
    error_log('Private API Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

    // Return sanitized error message to user
    http_response_code(500);
    $userMessage = 'Ein Fehler ist aufgetreten';
    
    // Only show specific errors in development mode
    if (defined('DEBUG') && DEBUG) {
        $userMessage = $e->getMessage();
    }
    
    echo json_encode([
        'success' => false,
        'error' => $userMessage
    ]);
}

function sendSuccess($data) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    exit;
}
