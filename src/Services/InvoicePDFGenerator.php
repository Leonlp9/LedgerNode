<?php
/**
 * PDF Generator Service
 * 
 * Generates professional invoices and credit notes as PDF documents
 * using TCPDF library
 */

namespace App\Services;

use TCPDF;

class InvoicePDFGenerator
{
    /**
     * Generate invoice PDF
     * 
     * @param array $data Invoice data
     * @return string PDF file path
     */
    public function generate(array $data): string
    {
        // Validate required fields
        $required = ['invoice_number', 'invoice_date', 'sender', 'recipient', 'line_items', 'amount'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Required field missing: {$field}");
            }
        }
        
        // Create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('LedgerNode');
        $pdf->SetAuthor($data['sender']);
        $pdf->SetTitle($data['invoice_number']);
        $pdf->SetSubject($data['document_type'] === 'credit' ? 'Gutschrift' : 'Rechnung');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(true, 20);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Generate HTML content
        $html = $this->generateHTML($data);
        
        // Output HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Generate filename
        $filename = $this->sanitizeFilename($data['invoice_number']) . '.pdf';
        $uploadDir = __DIR__ . '/../../uploads/invoices/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filepath = $uploadDir . $filename;
        
        // Save PDF to file
        $pdf->Output($filepath, 'F');
        
        return $filepath;
    }
    
    /**
     * Generate HTML content for the PDF
     */
    private function generateHTML(array $data): string
    {
        $isCredit = ($data['document_type'] ?? 'invoice') === 'credit';
        $documentTitle = $isCredit ? 'Gutschrift' : 'Rechnung';
        
        // Parse line items
        $lineItems = json_decode($data['line_items'], true);
        if (!is_array($lineItems)) {
            $lineItems = [];
        }
        
        // Calculate totals
        $subtotal = 0;
        $taxTotal = 0;
        foreach ($lineItems as $item) {
            $lineTotal = $item['quantity'] * $item['price'];
            $subtotal += $lineTotal;
            $taxTotal += $lineTotal * ($item['tax'] / 100);
        }
        $total = $subtotal + $taxTotal;
        
        // Format sender and recipient (preserve line breaks)
        $sender = nl2br(htmlspecialchars($data['sender']));
        $recipient = nl2br(htmlspecialchars($data['recipient']));
        
        // Build line items HTML
        $lineItemsHTML = '';
        foreach ($lineItems as $item) {
            $lineTotal = $item['quantity'] * $item['price'];
            $lineTax = $lineTotal * ($item['tax'] / 100);
            $lineGross = $lineTotal + $lineTax;
            
            $lineItemsHTML .= '<tr>
                <td>' . htmlspecialchars($item['description']) . '</td>
                <td style="text-align: right;">' . number_format($item['quantity'], 2, ',', '.') . '</td>
                <td style="text-align: right;">' . number_format($item['price'], 2, ',', '.') . ' €</td>
                <td style="text-align: right;">' . number_format($item['tax'], 2, ',', '.') . '%</td>
                <td style="text-align: right;">' . number_format($lineGross, 2, ',', '.') . ' €</td>
            </tr>';
        }
        
        // Format dates
        $invoiceDate = date('d.m.Y', strtotime($data['invoice_date']));
        $dueDate = !empty($data['due_date']) ? date('d.m.Y', strtotime($data['due_date'])) : '-';
        
        // Notes
        $notes = !empty($data['notes']) ? nl2br(htmlspecialchars($data['notes'])) : '';
        
        $html = '
        <style>
            h1 { font-size: 24px; color: #333; margin-bottom: 10px; }
            h2 { font-size: 14px; color: #666; font-weight: normal; margin-bottom: 20px; }
            .header-box { background-color: #f5f5f5; padding: 10px; margin-bottom: 20px; }
            .address-box { margin-bottom: 30px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th { background-color: #3b82f6; color: white; padding: 8px; text-align: left; font-weight: bold; }
            td { border-bottom: 1px solid #ddd; padding: 8px; }
            .totals-table { width: 50%; margin-left: auto; margin-top: 20px; }
            .totals-table td { border: none; padding: 5px; }
            .totals-table tr.final { font-weight: bold; border-top: 2px solid #333; }
            .notes { background-color: #f9f9f9; padding: 10px; margin-top: 30px; font-size: 9px; color: #666; }
        </style>
        
        <h1>' . $documentTitle . '</h1>
        <h2>' . htmlspecialchars($data['invoice_number']) . '</h2>
        
        <div class="header-box">
            <table style="border: none;">
                <tr>
                    <td style="border: none; width: 50%;"><strong>Von:</strong><br>' . $sender . '</td>
                    <td style="border: none; width: 50%;"><strong>An:</strong><br>' . $recipient . '</td>
                </tr>
            </table>
        </div>
        
        <table>
            <tr>
                <td style="border: none; width: 50%;"><strong>Rechnungsnummer:</strong></td>
                <td style="border: none; width: 50%;">' . htmlspecialchars($data['invoice_number']) . '</td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Rechnungsdatum:</strong></td>
                <td style="border: none;">' . $invoiceDate . '</td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Fälligkeitsdatum:</strong></td>
                <td style="border: none;">' . $dueDate . '</td>
            </tr>
        </table>
        
        <h2 style="margin-top: 30px;">Positionen</h2>
        <table>
            <thead>
                <tr>
                    <th>Beschreibung</th>
                    <th style="text-align: right;">Menge</th>
                    <th style="text-align: right;">Einzelpreis</th>
                    <th style="text-align: right;">MwSt.</th>
                    <th style="text-align: right;">Gesamt</th>
                </tr>
            </thead>
            <tbody>
                ' . $lineItemsHTML . '
            </tbody>
        </table>
        
        <table class="totals-table">
            <tr>
                <td>Nettobetrag:</td>
                <td style="text-align: right;">' . number_format($subtotal, 2, ',', '.') . ' €</td>
            </tr>
            <tr>
                <td>MwSt.:</td>
                <td style="text-align: right;">' . number_format($taxTotal, 2, ',', '.') . ' €</td>
            </tr>
            <tr class="final">
                <td>Gesamtbetrag:</td>
                <td style="text-align: right;">' . number_format($total, 2, ',', '.') . ' €</td>
            </tr>
        </table>
        
        ' . ($notes ? '<div class="notes"><strong>Hinweise:</strong><br>' . $notes . '</div>' : '') . '
        
        <div class="notes" style="margin-top: 50px;">
            <p>Erstellt mit LedgerNode am ' . date('d.m.Y H:i') . '</p>
        </div>
        ';
        
        return $html;
    }
    
    /**
     * Sanitize filename
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove special characters
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
        return $filename;
    }
}
