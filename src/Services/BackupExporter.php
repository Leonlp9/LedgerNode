<?php
/**
 * Backup and Export Service
 * 
 * Generates ZIP files with invoice documents and Excel spreadsheets
 * for data backup and archival purposes
 */

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;
use App\Core\Database;

class BackupExporter
{
    private Database $db;
    private string $tempDir;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->tempDir = sys_get_temp_dir() . '/ledgernode_backups/';
        
        // Create temp directory if it doesn't exist
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }
    
    /**
     * Generate backup ZIP for private invoices
     * 
     * @param string $period 'month', 'year', or 'all'
     * @param array $params Additional parameters (month, year)
     * @return string Path to generated ZIP file
     */
    public function generatePrivateBackup(string $period, array $params = []): string
    {
        $invoices = $this->getPrivateInvoices($period, $params);
        
        if (empty($invoices)) {
            throw new \RuntimeException('Keine Rechnungen für den gewählten Zeitraum gefunden');
        }
        
        $zipFilename = $this->generateFilename('private', $period, $params);
        $zipPath = $this->tempDir . $zipFilename;
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('ZIP-Datei konnte nicht erstellt werden');
        }
        
        // Add invoice files
        foreach ($invoices as $invoice) {
            if (!empty($invoice['file_path']) && file_exists($invoice['file_path'])) {
                $filename = basename($invoice['file_path']);
                $zip->addFile($invoice['file_path'], 'invoices/' . $filename);
            }
        }
        
        // Generate and add Excel file
        $excelPath = $this->generateExcel($invoices, 'private', $period, $params);
        $zip->addFile($excelPath, 'invoice_details.xlsx');
        
        $zip->close();
        
        // Clean up temporary Excel file
        if (file_exists($excelPath)) {
            unlink($excelPath);
        }
        
        return $zipPath;
    }
    
    /**
     * Generate backup ZIP for shared invoices
     */
    public function generateSharedBackup(string $period, array $params = []): string
    {
        $invoices = $this->getSharedInvoices($period, $params);
        
        if (empty($invoices)) {
            throw new \RuntimeException('Keine Rechnungen für den gewählten Zeitraum gefunden');
        }
        
        $zipFilename = $this->generateFilename('shared', $period, $params);
        $zipPath = $this->tempDir . $zipFilename;
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('ZIP-Datei konnte nicht erstellt werden');
        }
        
        // Add invoice files
        foreach ($invoices as $invoice) {
            if (!empty($invoice['file_path']) && file_exists($invoice['file_path'])) {
                $filename = basename($invoice['file_path']);
                $zip->addFile($invoice['file_path'], 'invoices/' . $filename);
            }
        }
        
        // Generate and add Excel file
        $excelPath = $this->generateExcel($invoices, 'shared', $period, $params);
        $zip->addFile($excelPath, 'invoice_details.xlsx');
        
        $zip->close();
        
        // Clean up temporary Excel file
        if (file_exists($excelPath)) {
            unlink($excelPath);
        }
        
        return $zipPath;
    }
    
    /**
     * Get private invoices based on period
     */
    private function getPrivateInvoices(string $period, array $params): array
    {
        $where = [];
        $bindings = [];
        
        if ($period === 'month') {
            $year = $params['year'] ?? date('Y');
            $month = $params['month'] ?? date('m');
            
            // SQLite date format
            $where[] = "strftime('%Y', invoice_date) = :year";
            $where[] = "strftime('%m', invoice_date) = :month";
            $bindings[':year'] = $year;
            $bindings[':month'] = str_pad($month, 2, '0', STR_PAD_LEFT);
            
        } elseif ($period === 'year') {
            $year = $params['year'] ?? date('Y');
            $where[] = "strftime('%Y', invoice_date) = :year";
            $bindings[':year'] = $year;
        }
        // 'all' has no where clause
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "
            SELECT *
            FROM private_invoices
            {$whereClause}
            ORDER BY invoice_date DESC
        ";
        
        return $this->db->fetchAll($sql, $bindings);
    }
    
    /**
     * Get shared invoices based on period
     */
    private function getSharedInvoices(string $period, array $params): array
    {
        $where = [];
        $bindings = [];
        
        if ($period === 'month') {
            $year = $params['year'] ?? date('Y');
            $month = $params['month'] ?? date('m');
            
            $where[] = "YEAR(invoice_date) = :year";
            $where[] = "MONTH(invoice_date) = :month";
            $bindings[':year'] = $year;
            $bindings[':month'] = $month;
            
        } elseif ($period === 'year') {
            $year = $params['year'] ?? date('Y');
            $where[] = "YEAR(invoice_date) = :year";
            $bindings[':year'] = $year;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "
            SELECT *
            FROM shared_invoices
            {$whereClause}
            ORDER BY invoice_date DESC
        ";
        
        return $this->db->fetchAll($sql, $bindings);
    }
    
    /**
     * Generate Excel file with invoice details
     */
    private function generateExcel(array $invoices, string $type, string $period, array $params): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $periodLabel = $this->getPeriodLabel($period, $params);
        $sheet->setTitle('Rechnungen');
        
        // Headers
        $sheet->setCellValue('A1', 'Rechnungsnummer');
        $sheet->setCellValue('B1', 'Typ');
        $sheet->setCellValue('C1', 'Datum');
        $sheet->setCellValue('D1', 'Fällig');
        $sheet->setCellValue('E1', 'Betrag');
        $sheet->setCellValue('F1', 'Von');
        $sheet->setCellValue('G1', 'An');
        $sheet->setCellValue('H1', 'Beschreibung');
        $sheet->setCellValue('I1', 'Status');
        $sheet->setCellValue('J1', 'Verknüpft');
        
        // Style headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3B82F6']
            ],
            'font' => ['color' => ['rgb' => 'FFFFFF']]
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        
        // Data rows
        $row = 2;
        foreach ($invoices as $invoice) {
            $sheet->setCellValue('A' . $row, $invoice['invoice_number'] ?? '');
            $sheet->setCellValue('B' . $row, $invoice['type'] === 'received' ? 'Erhalten' : 'Geschrieben');
            $sheet->setCellValue('C' . $row, $invoice['invoice_date']);
            $sheet->setCellValue('D' . $row, $invoice['due_date'] ?? '');
            $sheet->setCellValue('E' . $row, $invoice['amount']);
            $sheet->setCellValue('F' . $row, $invoice['sender']);
            $sheet->setCellValue('G' . $row, $invoice['recipient']);
            $sheet->setCellValue('H' . $row, $invoice['description']);
            $sheet->setCellValue('I' . $row, $this->getStatusLabel($invoice['status']));
            $sheet->setCellValue('J' . $row, $invoice['is_linked'] ? 'Ja' : 'Nein');
            
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Save to temporary file
        $filename = 'invoice_details_' . time() . '.xlsx';
        $filepath = $this->tempDir . $filename;
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filepath;
    }
    
    /**
     * Generate filename for ZIP
     */
    private function generateFilename(string $type, string $period, array $params): string
    {
        $parts = [$type, 'backup'];
        
        if ($period === 'month') {
            $year = $params['year'] ?? date('Y');
            $month = str_pad($params['month'] ?? date('m'), 2, '0', STR_PAD_LEFT);
            $parts[] = $year . '_' . $month;
        } elseif ($period === 'year') {
            $year = $params['year'] ?? date('Y');
            $parts[] = $year;
        } else {
            $parts[] = 'all';
        }
        
        $parts[] = date('Ymd_His');
        
        return implode('_', $parts) . '.zip';
    }
    
    /**
     * Get period label for display
     */
    private function getPeriodLabel(string $period, array $params): string
    {
        if ($period === 'month') {
            $year = $params['year'] ?? date('Y');
            $month = $params['month'] ?? date('m');
            $monthName = ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'][$month - 1];
            return $monthName . ' ' . $year;
        } elseif ($period === 'year') {
            return 'Jahr ' . ($params['year'] ?? date('Y'));
        } else {
            return 'Alle';
        }
    }
    
    /**
     * Get status label
     */
    private function getStatusLabel(string $status): string
    {
        $labels = [
            'open' => 'Offen',
            'paid' => 'Bezahlt',
            'overdue' => 'Überfällig',
            'cancelled' => 'Storniert'
        ];
        return $labels[$status] ?? $status;
    }
    
    /**
     * Clean up old backup files (older than 1 hour)
     */
    public function cleanup(): void
    {
        $files = glob($this->tempDir . '*.zip');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file) > 3600)) {
                unlink($file);
            }
        }
    }
}
