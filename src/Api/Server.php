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
        // Hole Action aus Request (vor Authentifizierung, damit public-Checks wie 'health' funktionieren)
        $action = $_POST['action'] ?? $_GET['action'] ?? null;

        if (empty($action)) {
            $this->sendError('Keine Action angegeben', 400);
        }

        // Route zu entsprechender Methode
        $method = 'action' . ucfirst($action);

        if (!method_exists($this, $method)) {
            $this->sendError("Unbekannte Action: {$action}", 404);
        }

        // Wenn es sich um den Health-Check handelt, führe ihn OHNE Authentifizierung aus
        if (strtolower($action) === 'health') {
            try {
                $result = $this->$method();
                $this->sendSuccess($result);
            } catch (\Exception $e) {
                $this->sendError($e->getMessage(), 500);
            }
            return;
        }

        // Authentifizierung (für alle anderen Actions)
        Security::authenticateApiRequest();

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
        // Gesamteinnahmen aus Transaktionen
        $income = $this->db->fetchOne("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM shared_transactions
            WHERE type = 'income'
        ")['total'];

        // YouTube Einnahmen hinzufügen
        $youtubeIncome = $this->db->fetchOne("
            SELECT COALESCE(SUM(total_revenue), 0) as total
            FROM shared_youtube_income
        ")['total'] ?? 0;
        
        $totalIncome = $income + $youtubeIncome;

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
            'total_income' => $totalIncome,
            'total_expenses' => $expenses,
            'balance' => $totalIncome - $expenses,
            'monthly_stats' => $monthlyStats,
            'youtube_income' => $youtubeIncome
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
     * Führt einen Git-Befehl im Projektroot aus und gibt Erfolg/Fehler zurück
     */
    private function runGitCommand($command, &$output = null, &$returnVar = null)
    {
        // setze auf Projektwurzel
        $projectRoot = dirname(dirname(__DIR__)); // src/Api -> src -> project root
        if (is_dir($projectRoot)) {
            chdir($projectRoot);
        }

        // Führe Befehl aus
        $command = $command . ' 2>&1';
        exec($command, $output, $returnVar);
        $output = is_array($output) ? array_values(array_filter($output, function($line) {
            return !preg_match('/^hint:/i', (string)$line);
        })) : [];

        return $returnVar === 0;
    }

    /**
     * Action: prüft, ob im entfernten Repo neue Commits vorhanden sind
     * Rückgabe: ['updates' => bool, 'commits' => array, 'branch' => string, 'repo' => string]
     */
    private function actionCheckUpdates()
    {
        // versuche remote Branch zu bestimmen
        $output = [];
        $return = 0;

        // Fetch ausführen
        if (!$this->runGitCommand('git fetch', $output, $return)) {
            throw new \RuntimeException('Fehler beim Ausführen von git fetch: ' . implode("\n", $output));
        }

        // Ermittle remote HEAD (z.B. origin/main oder origin/master)
        $branch = 'origin/master';
        if ($this->runGitCommand('git rev-parse --abbrev-ref origin/HEAD', $output, $return)) {
            if (!empty($output) && strpos($output[0], '/') !== false) {
                $parts = explode('/', $output[0]);
                $branch = 'origin/' . end($parts);
            }
        }

        // Prüfe auf neue Commits
        $commitOutput = [];
        if (!$this->runGitCommand("git log HEAD..{$branch} --oneline", $commitOutput, $return)) {
            // Wenn Befehl fehlschlägt, gib Fehler zurück
            throw new \RuntimeException('Fehler beim Prüfen auf Updates: ' . implode("\n", $commitOutput));
        }

        if (empty($commitOutput)) {
            return ['updates' => false, 'commits' => [], 'branch' => $branch];
        }

        return ['updates' => true, 'commits' => $commitOutput, 'branch' => $branch];
    }

    /**
     * Action: installiert Updates (git pull) — markiert vorher bestimmte Dateien als unverändert
     */
    private function actionInstallUpdates()
    {
        // Liste der auszuschließenden Dateien/Ordner (dangerous -> nur wichtige Dateien)
        $exclude = [
            'config.php',
            'config.ini',
            'uploads'
        ];

        $output = [];
        $return = 0;

        // Fetch
        if (!$this->runGitCommand('git fetch', $output, $return)) {
            throw new \RuntimeException('Fehler beim Ausführen von git fetch: ' . implode("\n", $output));
        }

        // Ermittle remote Branch
        $branch = 'origin/master';
        if ($this->runGitCommand('git rev-parse --abbrev-ref origin/HEAD', $output, $return)) {
            if (!empty($output) && strpos($output[0], '/') !== false) {
                $parts = explode('/', $output[0]);
                $branch = 'origin/' . end($parts);
            }
        }

        // markiere exclude als assume-unchanged
        foreach ($exclude as $item) {
            $this->runGitCommand('git update-index --assume-unchanged ' . escapeshellarg($item), $o, $r);
        }

        // Sicheres Zurücksetzen der Arbeitskopie
        if (!$this->runGitCommand('git checkout -- .', $output, $return)) {
            // versuche undo assume-unchanged zurückzusetzen
            foreach ($exclude as $item) {
                $this->runGitCommand('git update-index --no-assume-unchanged ' . escapeshellarg($item), $o, $r);
            }
            throw new \RuntimeException('Fehler beim Zurücksetzen der Dateien: ' . implode("\n", $output));
        }

        // Pull vom Remote-Branch
        $pullCmd = "git pull {$branch}";
        $pullOutput = [];
        if (!$this->runGitCommand($pullCmd, $pullOutput, $return)) {
            // setze exclude zurück
            foreach ($exclude as $item) {
                $this->runGitCommand('git update-index --no-assume-unchanged ' . escapeshellarg($item), $o, $r);
            }
            throw new \RuntimeException('Fehler beim Aktualisieren des Repositories: ' . implode("\n", $pullOutput));
        }

        // setze exclude zurück
        foreach ($exclude as $item) {
            $this->runGitCommand('git update-index --no-assume-unchanged ' . escapeshellarg($item), $o, $r);
        }

        return ['message' => 'Repository wurde erfolgreich aktualisiert', 'output' => $pullOutput];
    }

    /**
     * Action: Get shared invoices with pagination
     */
    private function actionGetSharedInvoices(): array
    {
        $type = $_GET['type'] ?? null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 15)));
        $offset = ($page - 1) * $perPage;

        $where = [];
        $bindings = [];

        if ($type && in_array($type, ['received', 'issued'])) {
            $where[] = 'i.type = :type';
            $bindings[':type'] = $type;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM shared_invoices i {$whereClause}";
        $totalResult = $this->db->fetchOne($countSql, $bindings);
        $total = $totalResult['total'] ?? 0;

        // Get invoices
        $sql = "
            SELECT 
                i.*,
                t.description as transaction_description,
                t.amount as transaction_amount,
                t.date as transaction_date
            FROM shared_invoices i
            LEFT JOIN shared_transactions t ON i.transaction_id = t.id
            {$whereClause}
            ORDER BY i.invoice_date DESC, i.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $bindings[':limit'] = $perPage;
        $bindings[':offset'] = $offset;

        $invoices = $this->db->fetchAll($sql, $bindings);

        // After fetching, map file_path -> file_url using FileUpload
        $fileUpload = new \App\Core\FileUpload();
        $mapped = array_map(function($inv) use ($fileUpload) {
            if (!empty($inv['file_path'])) {
                $inv['file_url'] = $fileUpload->getFileUrl($inv['file_path']);
            } else {
                $inv['file_url'] = null;
            }
            return $inv;
        }, $invoices ?: []);

        return [
            'invoices' => $mapped,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Action: Create shared invoice
     */
    private function actionCreateSharedInvoice(): array
    {
        $required = ['type', 'invoice_date', 'amount', 'sender', 'recipient', 'description'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new \InvalidArgumentException("Pflichtfeld fehlt: {$field}");
            }
        }

        if (!in_array($_POST['type'], ['received', 'issued'])) {
            throw new \InvalidArgumentException('Ungültiger Typ');
        }

        if (!is_numeric($_POST['amount']) || $_POST['amount'] <= 0) {
            throw new \InvalidArgumentException('Ungültiger Betrag');
        }

        // Handle file upload if provided
        $filePath = null;
        $fileName = null;

        if (!empty($_FILES['file']['tmp_name'])) {
            $fileUpload = new \App\Core\FileUpload([
                'max_size' => 10485760,
                'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
                'allowed_mimes' => ['application/pdf', 'image/jpeg', 'image/png'],
                'filename_strategy' => 'hash'
            ]);

            $uploadedPath = $fileUpload->upload($_FILES['file']);
            if ($uploadedPath === false) {
                $errors = implode(', ', $fileUpload->getErrors());
                throw new \RuntimeException("Datei-Upload fehlgeschlagen: {$errors}");
            }
            $filePath = $uploadedPath;
            $fileName = $_FILES['file']['name'];
        }

        $data = [
            'type' => $_POST['type'],
            'invoice_number' => $_POST['invoice_number'] ?? null,
            'invoice_date' => $_POST['invoice_date'],
            'due_date' => $_POST['due_date'] ?? null,
            'amount' => $_POST['amount'],
            'sender' => $_POST['sender'],
            'recipient' => $_POST['recipient'],
            'description' => $_POST['description'],
            'file_path' => $filePath,
            'file_name' => $fileName,
            'status' => $_POST['status'] ?? 'open',
            'notes' => $_POST['notes'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->db->insertArray('shared_invoices', $data);

        return [
            'id' => $id,
            'message' => 'Rechnung erfolgreich erstellt'
        ];
    }

    /**
     * Action: Link invoice to transaction
     */
    private function actionLinkInvoiceToTransaction(): array
    {
        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $transactionId = (int)($_POST['transaction_id'] ?? 0);

        if ($invoiceId <= 0 || $transactionId <= 0) {
            throw new \InvalidArgumentException('Ungültige IDs');
        }

        // Check if transaction is already linked
        $existing = $this->db->fetchOne(
            'SELECT * FROM shared_invoices WHERE transaction_id = :tid AND id != :id',
            [':tid' => $transactionId, ':id' => $invoiceId]
        );
        if ($existing) {
            throw new \RuntimeException('Transaktion ist bereits mit einer anderen Rechnung verknüpft');
        }

        $this->db->execute(
            'UPDATE shared_invoices SET transaction_id = :tid, is_linked = TRUE WHERE id = :id',
            [':tid' => $transactionId, ':id' => $invoiceId]
        );

        return ['message' => 'Rechnung mit Transaktion verknüpft'];
    }

    /**
     * Action: Get available transactions for linking
     */
    private function actionGetAvailableTransactionsForInvoice(): array
    {
        $invoiceId = (int)($_GET['invoice_id'] ?? 0);

        if ($invoiceId <= 0) {
            throw new \InvalidArgumentException('Ungültige Rechnungs-ID');
        }

        $invoice = $this->db->fetchOne('SELECT * FROM shared_invoices WHERE id = :id', [':id' => $invoiceId]);
        if (!$invoice) {
            throw new \RuntimeException('Rechnung nicht gefunden');
        }

        $transactionType = $invoice['type'] === 'received' ? 'income' : 'expense';

        $sql = "
            SELECT t.*, a.name as account_name
            FROM shared_transactions t
            LEFT JOIN shared_accounts a ON t.account_id = a.id
            WHERE t.type = :type
              AND t.amount = :amount
              AND t.id NOT IN (SELECT transaction_id FROM shared_invoices WHERE transaction_id IS NOT NULL AND id != :invoice_id)
            ORDER BY t.date DESC
            LIMIT 50
        ";

        return $this->db->fetchAll($sql, [
            ':type' => $transactionType,
            ':amount' => $invoice['amount'],
            ':invoice_id' => $invoiceId
        ]);
    }

    /**
     * Action: Get invoice statistics
     */
    private function actionGetSharedInvoiceStats(): array
    {
        $stats = $this->db->fetchOne("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN type = 'received' THEN 1 ELSE 0 END) as received,
                SUM(CASE WHEN type = 'issued' THEN 1 ELSE 0 END) as issued,
                SUM(CASE WHEN is_linked = FALSE THEN 1 ELSE 0 END) as unlinked,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count,
                SUM(amount) as total_amount
            FROM shared_invoices
        ");

        return $stats ?: [
            'total' => 0,
            'received' => 0,
            'issued' => 0,
            'unlinked' => 0,
            'open_count' => 0,
            'total_amount' => 0
        ];
    }

    /**
     * Action: Delete shared invoice
     */
    private function actionDeleteSharedInvoice(): array
    {
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            throw new \InvalidArgumentException('Ungültige ID');
        }

        $invoice = $this->db->fetchOne('SELECT * FROM shared_invoices WHERE id = :id', [':id' => $id]);
        if (!$invoice) {
            throw new \RuntimeException('Rechnung nicht gefunden');
        }

        // Delete file if exists
        if ($invoice['file_path'] && file_exists($invoice['file_path'])) {
            $fileUpload = new \App\Core\FileUpload();
            $fileUpload->delete($invoice['file_path']);
        }

        $this->db->execute('DELETE FROM shared_invoices WHERE id = :id', [':id' => $id]);

        return ['message' => 'Rechnung gelöscht'];
    }

    /**
     * Action: Get YouTube Income
     */
    private function actionGetYouTubeIncome(): array
    {
        $sql = "
            SELECT *
            FROM shared_youtube_income
            ORDER BY year DESC, month DESC
        ";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Action: Add YouTube Income
     */
    private function actionAddYouTubeIncome(): array
    {
        $required = ['year', 'month', 'total_revenue'];
        foreach ($required as $field) {
            if (!isset($_POST[$field])) {
                throw new \InvalidArgumentException("Pflichtfeld fehlt: {$field}");
            }
        }

        $data = [
            'year' => (int)$_POST['year'],
            'month' => (int)$_POST['month'],
            'total_revenue' => (float)$_POST['total_revenue'],
            'donations' => (float)($_POST['donations'] ?? 0),
            'members' => (float)($_POST['members'] ?? 0),
            'notes' => $_POST['notes'] ?? null
        ];

        // Check if entry for this month already exists
        $existing = $this->db->fetchOne(
            'SELECT id FROM shared_youtube_income WHERE year = :year AND month = :month',
            [':year' => $data['year'], ':month' => $data['month']]
        );

        if ($existing) {
            throw new \RuntimeException('Einnahmen für diesen Monat bereits vorhanden. Bitte bearbeiten Sie den bestehenden Eintrag.');
        }

        $id = $this->db->insertArray('shared_youtube_income', $data);

        return [
            'id' => $id,
            'message' => 'YouTube Einnahmen hinzugefügt'
        ];
    }

    /**
     * Action: Update YouTube Income
     */
    private function actionUpdateYouTubeIncome(): array
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            throw new \InvalidArgumentException('Ungültige ID');
        }

        $updates = [];
        $bindings = [':id' => $id];

        $allowedFields = ['year', 'month', 'total_revenue', 'donations', 'members', 'notes'];
        foreach ($allowedFields as $field) {
            if (isset($_POST[$field])) {
                $updates[] = "{$field} = :{$field}";
                $bindings[":{$field}"] = $_POST[$field];
            }
        }

        if (empty($updates)) {
            throw new \InvalidArgumentException('Keine Felder zum Aktualisieren');
        }

        $sql = "UPDATE shared_youtube_income SET " . implode(', ', $updates) . " WHERE id = :id";
        $this->db->execute($sql, $bindings);

        return ['message' => 'YouTube Einnahmen aktualisiert'];
    }

    /**
     * Action: Delete YouTube Income
     */
    private function actionDeleteYouTubeIncome(): array
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            throw new \InvalidArgumentException('Ungültige ID');
        }

        $this->db->execute('DELETE FROM shared_youtube_income WHERE id = :id', [':id' => $id]);

        return ['message' => 'YouTube Einnahmen gelöscht'];
    }

    /**
     * Action: Get YouTube Expenses
     */
    private function actionGetYouTubeExpenses(): array
    {
        $sql = "
            SELECT *
            FROM shared_youtube_expenses
            ORDER BY date DESC, created_at DESC
        ";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Action: Add YouTube Expense
     */
    private function actionAddYouTubeExpense(): array
    {
        $required = ['amount', 'recipient', 'description', 'date'];
        foreach ($required as $field) {
            if (!isset($_POST[$field])) {
                throw new \InvalidArgumentException("Pflichtfeld fehlt: {$field}");
            }
        }

        $data = [
            'amount' => (float)$_POST['amount'],
            'recipient' => $_POST['recipient'],
            'description' => $_POST['description'],
            'date' => $_POST['date']
        ];

        $id = $this->db->insertArray('shared_youtube_expenses', $data);

        return [
            'id' => $id,
            'message' => 'YouTube Ausgabe hinzugefügt'
        ];
    }

    /**
     * Action: Delete YouTube Expense
     */
    private function actionDeleteYouTubeExpense(): array
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            throw new \InvalidArgumentException('Ungültige ID');
        }

        $this->db->execute('DELETE FROM shared_youtube_expenses WHERE id = :id', [':id' => $id]);

        return ['message' => 'YouTube Ausgabe gelöscht'];
    }

    /**
     * Erfolgreiche Response senden
     */
    private function sendSuccess($data)
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
    private function sendError($message, $code = 400)
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
