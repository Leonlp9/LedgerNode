<?php
/**
 * Local API Handler for Private Invoices
 * 
 * Handles all invoice-related operations for private/local data
 */

namespace App\Api;

use App\Core\Database;
use App\Core\FileUpload;

class PrivateInvoices
{
    private Database $db;
    private FileUpload $fileUpload;

    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Configure file upload for invoices
        $this->fileUpload = new FileUpload([
            'max_size' => 10485760, // 10 MB for invoices
            'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
            'allowed_mimes' => [
                'application/pdf',
                'image/jpeg',
                'image/png'
            ],
            'filename_strategy' => 'hash'
        ]);
    }

    /**
     * Get all invoices with pagination and filtering
     */
    public function getInvoices(array $params = []): array
    {
        $type = $params['type'] ?? null; // 'received', 'issued', or null for all
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = min(100, max(1, (int)($params['per_page'] ?? 15)));
        $offset = ($page - 1) * $perPage;

        // Build WHERE clause
        $where = [];
        $bindings = [];

        if ($type && in_array($type, ['received', 'issued'])) {
            // Qualify column with invoice alias to avoid ambiguity with transaction.type
            $where[] = 'i.type = :type';
            $bindings[':type'] = $type;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count (use alias i for consistency)
        $countSql = "SELECT COUNT(*) as total FROM private_invoices i {$whereClause}";
        $totalResult = $this->db->fetchOne($countSql, $bindings);
        $total = $totalResult['total'] ?? 0;

        // Get invoices
        $sql = "
            SELECT 
                i.*,
                t.description as transaction_description,
                t.amount as transaction_amount,
                t.date as transaction_date
            FROM private_invoices i
            LEFT JOIN private_transactions t ON i.transaction_id = t.id
            {$whereClause}
            ORDER BY i.invoice_date DESC, i.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $bindings[':limit'] = $perPage;
        $bindings[':offset'] = $offset;

        $invoices = $this->db->fetchAll($sql, $bindings);

        // Map filesystem paths to web URLs for client consumption
        $mapped = array_map(function($inv) {
            if (!empty($inv['file_path'])) {
                $url = $this->fileUpload->getFileUrl($inv['file_path']);
                $inv['file_url'] = $url;
            } else {
                $inv['file_url'] = null;
            }
            // For backward compatibility keep file_path (filesystem) but clients should use file_url
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
     * Get single invoice
     */
    public function getInvoice(int $id): ?array
    {
        $sql = "
            SELECT 
                i.*,
                t.description as transaction_description,
                t.amount as transaction_amount,
                t.date as transaction_date
            FROM private_invoices i
            LEFT JOIN private_transactions t ON i.transaction_id = t.id
            WHERE i.id = :id
        ";

        $inv = $this->db->fetchOne($sql, [':id' => $id]);
        if ($inv && !empty($inv['file_path'])) {
            $inv['file_url'] = $this->fileUpload->getFileUrl($inv['file_path']);
        } else {
            $inv['file_url'] = null;
        }

        return $inv;
    }

    /**
     * Create new invoice
     */
    public function createInvoice(array $data, ?array $file = null): array
    {
        // Validate required fields
        $required = ['type', 'invoice_date', 'amount', 'sender', 'recipient', 'description'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Pflichtfeld fehlt: {$field}");
            }
        }

        // Validate type
        if (!in_array($data['type'], ['received', 'issued'])) {
            throw new \InvalidArgumentException('Ungültiger Typ (received oder issued)');
        }

        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new \InvalidArgumentException('Ungültiger Betrag');
        }

        // Handle file upload if provided
        $filePath = null;
        $fileName = null;

        if ($file && !empty($file['tmp_name'])) {
            $uploadedPath = $this->fileUpload->upload($file);
            if ($uploadedPath === false) {
                $errors = implode(', ', $this->fileUpload->getErrors());
                throw new \RuntimeException("Datei-Upload fehlgeschlagen: {$errors}");
            }
            $filePath = $uploadedPath;
            $fileName = $file['name'];
        }

        // Prepare data for insertion
        // Normalize due_date: convert empty string to null to avoid SQL errors for DATE columns
        $dueRaw = isset($data['due_date']) ? trim((string)$data['due_date']) : '';
        $dueDate = $dueRaw !== '' ? $dueRaw : null;

        $insertData = [
            'type' => $data['type'],
            'invoice_number' => $data['invoice_number'] ?? null,
            'invoice_date' => $data['invoice_date'],
            'due_date' => $dueDate,
            'amount' => $data['amount'],
            'sender' => $data['sender'],
            'recipient' => $data['recipient'],
            'description' => $data['description'],
            'file_path' => $filePath,
            'file_name' => $fileName,
            'status' => $data['status'] ?? 'open',
            'notes' => $data['notes'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->db->insertArray('private_invoices', $insertData);

        return [
            'id' => $id,
            'message' => 'Rechnung erfolgreich erstellt'
        ];
    }

    /**
     * Update invoice
     */
    public function updateInvoice(int $id, array $data, ?array $file = null): array
    {
        // Check if invoice exists
        $existing = $this->getInvoice($id);
        if (!$existing) {
            throw new \RuntimeException('Rechnung nicht gefunden');
        }

        // Handle file upload if provided
        if ($file && !empty($file['tmp_name'])) {
            // Delete old file if exists
            if ($existing['file_path'] && file_exists($existing['file_path'])) {
                $this->fileUpload->delete($existing['file_path']);
            }

            $uploadedPath = $this->fileUpload->upload($file);
            if ($uploadedPath === false) {
                $errors = implode(', ', $this->fileUpload->getErrors());
                throw new \RuntimeException("Datei-Upload fehlgeschlagen: {$errors}");
            }
            $data['file_path'] = $uploadedPath;
            $data['file_name'] = $file['name'];
        }

        // Build update query
        $updateFields = [];
        $bindings = [':id' => $id];

        $allowedFields = ['invoice_number', 'invoice_date', 'due_date', 'amount', 
                          'sender', 'recipient', 'description', 'file_path', 
                          'file_name', 'status', 'notes'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateFields[] = "{$field} = :{$field}";
                // Convert empty due_date string to null for DATE columns
                if ($field === 'due_date') {
                    $valRaw = isset($data['due_date']) ? trim((string)$data['due_date']) : '';
                    $bindings[":{$field}"] = $valRaw !== '' ? $valRaw : null;
                } else {
                    $bindings[":{$field}"] = $data[$field];
                }
            }
        }

        if (empty($updateFields)) {
            throw new \InvalidArgumentException('Keine Felder zum Aktualisieren');
        }

        $updateFields[] = "updated_at = :updated_at";
        $bindings[':updated_at'] = date('Y-m-d H:i:s');

        $sql = "UPDATE private_invoices SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $this->db->execute($sql, $bindings);

        return ['message' => 'Rechnung aktualisiert'];
    }

    /**
     * Delete invoice
     */
    public function deleteInvoice(int $id): array
    {
        $invoice = $this->getInvoice($id);
        if (!$invoice) {
            throw new \RuntimeException('Rechnung nicht gefunden');
        }

        // Delete file if exists
        if ($invoice['file_path'] && file_exists($invoice['file_path'])) {
            $this->fileUpload->delete($invoice['file_path']);
        }

        $this->db->execute('DELETE FROM private_invoices WHERE id = :id', [':id' => $id]);

        return ['message' => 'Rechnung gelöscht'];
    }

    /**
     * Link invoice to transaction
     */
    public function linkToTransaction(int $invoiceId, int $transactionId): array
    {
        // Verify invoice exists
        $invoice = $this->getInvoice($invoiceId);
        if (!$invoice) {
            throw new \RuntimeException('Rechnung nicht gefunden');
        }

        // Verify transaction exists
        $transaction = $this->db->fetchOne(
            'SELECT * FROM private_transactions WHERE id = :id',
            [':id' => $transactionId]
        );
        if (!$transaction) {
            throw new \RuntimeException('Transaktion nicht gefunden');
        }

        // Check if transaction is already linked
        $existing = $this->db->fetchOne(
            'SELECT * FROM private_invoices WHERE transaction_id = :tid AND id != :id',
            [':tid' => $transactionId, ':id' => $invoiceId]
        );
        if ($existing) {
            throw new \RuntimeException('Transaktion ist bereits mit einer anderen Rechnung verknüpft');
        }

        // Link
        $this->db->execute(
            'UPDATE private_invoices SET transaction_id = :tid, is_linked = 1 WHERE id = :id',
            [':tid' => $transactionId, ':id' => $invoiceId]
        );

        return ['message' => 'Rechnung mit Transaktion verknüpft'];
    }

    /**
     * Unlink invoice from transaction
     */
    public function unlinkFromTransaction(int $invoiceId): array
    {
        $this->db->execute(
            'UPDATE private_invoices SET transaction_id = NULL, is_linked = 0 WHERE id = :id',
            [':id' => $invoiceId]
        );

        return ['message' => 'Verknüpfung entfernt'];
    }

    /**
     * Get available transactions for linking (same amount, not already linked)
     */
    public function getAvailableTransactions(int $invoiceId): array
    {
        $invoice = $this->getInvoice($invoiceId);
        if (!$invoice) {
            throw new \RuntimeException('Rechnung nicht gefunden');
        }

        // Determine transaction type based on invoice type
        // received invoice = you received a bill to pay = expense
        // issued invoice = you issued a bill to receive payment = income
        $transactionType = $invoice['type'] === 'received' ? 'expense' : 'income';

        // Get all transactions of the correct type that are not linked to other invoices
        $sql = "
            SELECT t.*, a.name as account_name,
                   CASE WHEN ABS(t.amount - :amount) < 0.01 THEN 1 ELSE 0 END as is_matching_amount
            FROM private_transactions t
            LEFT JOIN private_accounts a ON t.account_id = a.id
            LEFT JOIN private_invoices inv ON inv.transaction_id = t.id AND inv.id != :invoice_id
            WHERE t.type = :type
              AND inv.id IS NULL
            ORDER BY is_matching_amount DESC, t.date DESC
            LIMIT 100
        ";

        return $this->db->fetchAll($sql, [
            ':type' => $transactionType,
            ':amount' => $invoice['amount'],
            ':invoice_id' => $invoiceId
        ]);
    }

    /**
     * Get count of unlinked invoices
     */
    public function getUnlinkedCount(): int
    {
        $result = $this->db->fetchOne('SELECT COUNT(*) as count FROM private_invoices WHERE is_linked = 0');
        return $result['count'] ?? 0;
    }

    /**
     * Get invoice statistics
     */
    public function getStats(): array
    {
        $stats = $this->db->fetchOne("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN type = 'received' THEN 1 ELSE 0 END) as received,
                SUM(CASE WHEN type = 'issued' THEN 1 ELSE 0 END) as issued,
                SUM(CASE WHEN is_linked = 0 THEN 1 ELSE 0 END) as unlinked,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count,
                SUM(amount) as total_amount
            FROM private_invoices
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
}
