<?php
/**
 * Server-API
 * 
 * Behandelt alle eingehenden API-Requests
 * Läuft NUR wenn IS_SERVER === true
 */

namespace App\Api;

use App\Core\Config;
use App\Core\Database;
use App\Core\Security;

class Server
{
    private Database $db;

    public function __construct()
    {
        // Stelle sicher, dass dies nur auf dem Server läuft
        Security::enforceServerApi();
        
        $this->db = Database::getInstance();
    }

    /**
     * Hauptmethode: Request verarbeiten
     */
    public function handleRequest(): void
    {
        // Authentifizierung
        Security::authenticateApiRequest();

        // Hole Action aus Request
        $action = $_POST['action'] ?? $_GET['action'] ?? null;

        if (empty($action)) {
            $this->sendError('Keine Action angegeben', 400);
        }

        // Route zu entsprechender Methode
        $method = 'action' . ucfirst($action);

        if (!method_exists($this, $method)) {
            $this->sendError("Unbekannte Action: {$action}", 404);
        }

        try {
            $result = $this->$method();
            $this->sendSuccess($result);
        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Action: Alle gemeinsamen Transaktionen abrufen
     */
    private function actionGetSharedTransactions(): array
    {
        $limit = (int) ($_GET['limit'] ?? 100);
        $offset = (int) ($_GET['offset'] ?? 0);

        $sql = "
            SELECT 
                t.*,
                a.name as account_name
            FROM shared_transactions t
            LEFT JOIN shared_accounts a ON t.account_id = a.id
            ORDER BY t.date DESC, t.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        return $this->db->fetchAll($sql, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);
    }

    /**
     * Action: Gemeinsame Transaktion hinzufügen
     */
    private function actionAddSharedTransaction(): array
    {
        $required = ['account_id', 'amount', 'description', 'date'];
        $data = [];

        foreach ($required as $field) {
            if (!isset($_POST[$field])) {
                throw new \InvalidArgumentException("Pflichtfeld fehlt: {$field}");
            }
            $data[$field] = $_POST[$field];
        }

        // Validierung
        if (!is_numeric($data['amount'])) {
            throw new \InvalidArgumentException('Amount muss eine Zahl sein');
        }

        $data['type'] = $_POST['type'] ?? 'expense';
        $data['created_at'] = date('Y-m-d H:i:s');

        $id = $this->db->insertArray('shared_transactions', $data);

        return [
            'id' => $id,
            'message' => 'Transaktion erfolgreich hinzugefügt'
        ];
    }

    /**
     * Action: Gemeinsame Konten abrufen
     */
    private function actionGetSharedAccounts(): array
    {
        $sql = "
            SELECT 
                a.*,
                COUNT(t.id) as transaction_count,
                COALESCE(SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE -t.amount END), 0) as balance
            FROM shared_accounts a
            LEFT JOIN shared_transactions t ON a.id = t.account_id
            GROUP BY a.id
            ORDER BY a.name ASC
        ";

        return $this->db->fetchAll($sql);
    }

    /**
     * Action: Gemeinsames Konto erstellen
     */
    private function actionCreateSharedAccount(): array
    {
        $name = $_POST['name'] ?? null;
        $type = $_POST['type'] ?? 'general';

        if (empty($name)) {
            throw new \InvalidArgumentException('Kontoname erforderlich');
        }

        $data = [
            'name' => $name,
            'type' => $type,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->db->insertArray('shared_accounts', $data);

        return [
            'id' => $id,
            'message' => 'Konto erfolgreich erstellt'
        ];
    }

    /**
     * Action: Dashboard-Statistiken
     */
    private function actionGetSharedStats(): array
    {
        // Gesamteinnahmen
        $income = $this->db->fetchOne("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM shared_transactions
            WHERE type = 'income'
        ")['total'];

        // Gesamtausgaben
        $expenses = $this->db->fetchOne("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM shared_transactions
            WHERE type = 'expense'
        ")['total'];

        // Transaktionen pro Monat (letzten 12 Monate)
        $monthlyStats = $this->db->fetchAll("
            SELECT 
                DATE_FORMAT(date, '%Y-%m') as month,
                COUNT(*) as transaction_count,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses
            FROM shared_transactions
            WHERE date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY month
            ORDER BY month DESC
        ");

        return [
            'total_income' => $income,
            'total_expenses' => $expenses,
            'balance' => $income - $expenses,
            'monthly_stats' => $monthlyStats
        ];
    }

    /**
     * Action: Transaktion löschen
     */
    private function actionDeleteTransaction(): array
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            throw new \InvalidArgumentException('Ungültige Transaction-ID');
        }

        $affected = $this->db->execute(
            'DELETE FROM shared_transactions WHERE id = :id',
            [':id' => $id]
        );

        if ($affected === 0) {
            throw new \RuntimeException('Transaktion nicht gefunden');
        }

        return ['message' => 'Transaktion gelöscht'];
    }

    /**
     * Action: Health-Check (zum Testen der Verbindung)
     */
    private function actionHealth(): array
    {
        return [
            'status' => 'ok',
            'server' => true,
            'timestamp' => time(),
            'version' => Config::get('APP.version', '1.0.0')
        ];
    }

    /**
     * Erfolgreiche Response senden
     */
    private function sendSuccess($data): void
    {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }

    /**
     * Fehler-Response senden
     */
    private function sendError(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }
}
