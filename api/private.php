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

        // Private stats endpoint
        case 'stats':
            $db = Database::getInstance();
            
            $balance = $db->fetchOne("
                SELECT COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) as balance
                FROM private_transactions
            ")['balance'] ?? 0;

            $income = $db->fetchOne("
                SELECT COALESCE(SUM(amount), 0) as total
                FROM private_transactions
                WHERE type = 'income'
                AND strftime('%Y-%m', date) = strftime('%Y-%m', 'now')
            ")['total'] ?? 0;

            $expenses = $db->fetchOne("
                SELECT COALESCE(SUM(amount), 0) as total
                FROM private_transactions
                WHERE type = 'expense'
                AND strftime('%Y-%m', date) = strftime('%Y-%m', 'now')
            ")['total'] ?? 0;

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
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
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
