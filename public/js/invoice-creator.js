/**
 * Invoice Creator Component
 * 
 * Provides a user-friendly interface to create professional invoices and credit notes
 * with automatic PDF generation and server storage
 */

const InvoiceCreator = {
    currentModule: 'private', // or 'shared'
    
    /**
     * Open the invoice creator dialog
     */
    open(module = 'private', type = 'invoice') {
        this.currentModule = module;
        const modal = document.getElementById('invoice-creator-modal');
        if (!modal) {
            this.createModal();
        }
        
        // Reset form
        document.getElementById('ic-form').reset();
        document.getElementById('ic-type').value = type === 'credit' ? 'credit' : 'invoice';
        document.getElementById('ic-invoice-type').value = 'issued'; // Default to issued
        
        // Set today's date
        document.getElementById('ic-date').valueAsDate = new Date();
        
        // Calculate due date (30 days from now)
        const dueDate = new Date();
        dueDate.setDate(dueDate.getDate() + 30);
        document.getElementById('ic-due-date').valueAsDate = dueDate;
        
        // Generate invoice number
        this.generateInvoiceNumber();
        
        // Clear line items
        this.lineItems = [];
        this.updateLineItemsTable();
        
        // Show modal
        document.getElementById('invoice-creator-modal').style.display = 'flex';
    },
    
    /**
     * Close the invoice creator dialog
     */
    close() {
        document.getElementById('invoice-creator-modal').style.display = 'none';
    },
    
    /**
     * Create the invoice creator modal DOM
     */
    createModal() {
        const modalHTML = `
        <div id="invoice-creator-modal" class="modal" style="display: none;">
            <div class="modal-content modal-large" style="max-width: 900px;">
                <div class="modal-header">
                    <h3>📝 Rechnung/Gutschrift erstellen</h3>
                    <button class="modal-close" onclick="InvoiceCreator.close()">&times;</button>
                </div>
                <form id="ic-form" onsubmit="InvoiceCreator.submit(event)">
                    <input type="hidden" id="ic-type" name="document_type" value="invoice">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ic-invoice-type">Art</label>
                            <select id="ic-invoice-type" name="invoice_type" required>
                                <option value="issued">Geschrieben (Ausgehend)</option>
                                <option value="received">Erhalten (Eingehend)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="ic-document-type-select">Dokumenttyp</label>
                            <select id="ic-document-type-select" onchange="InvoiceCreator.updateDocumentType(this.value)">
                                <option value="invoice">Rechnung</option>
                                <option value="credit">Gutschrift</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ic-number">Rechnungsnummer</label>
                            <input type="text" id="ic-number" name="invoice_number" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="ic-date">Rechnungsdatum</label>
                            <input type="date" id="ic-date" name="invoice_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="ic-due-date">Fälligkeitsdatum</label>
                            <input type="date" id="ic-due-date" name="due_date">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ic-sender">Von (Absender)</label>
                            <textarea id="ic-sender" name="sender" rows="3" required placeholder="Name&#10;Straße Hausnr.&#10;PLZ Ort"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="ic-recipient">An (Empfänger)</label>
                            <textarea id="ic-recipient" name="recipient" rows="3" required placeholder="Name&#10;Straße Hausnr.&#10;PLZ Ort"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Positionen</label>
                        <div id="ic-line-items-container">
                            <table class="line-items-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40%;">Beschreibung</th>
                                        <th style="width: 15%;">Menge</th>
                                        <th style="width: 15%;">Einzelpreis</th>
                                        <th style="width: 15%;">MwSt. %</th>
                                        <th style="width: 15%;">Gesamt</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="ic-line-items">
                                    <!-- Filled dynamically -->
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-small btn-secondary" onclick="InvoiceCreator.addLineItem()">
                                ➕ Position hinzufügen
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-row" style="justify-content: flex-end;">
                        <div class="invoice-totals" style="min-width: 300px;">
                            <div class="total-row">
                                <span>Nettobetrag:</span>
                                <span id="ic-subtotal">0,00 €</span>
                            </div>
                            <div class="total-row">
                                <span>MwSt.:</span>
                                <span id="ic-tax">0,00 €</span>
                            </div>
                            <div class="total-row total-final">
                                <strong>Gesamtbetrag:</strong>
                                <strong id="ic-total">0,00 €</strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="ic-notes">Notizen / Zahlungsbedingungen</label>
                        <textarea id="ic-notes" name="notes" rows="3" placeholder="z.B. Zahlbar innerhalb von 30 Tagen"></textarea>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="InvoiceCreator.close()">
                            Abbrechen
                        </button>
                        <button type="submit" class="btn btn-primary">
                            💾 Erstellen & als PDF herunterladen
                        </button>
                    </div>
                </form>
            </div>
        </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    },
    
    lineItems: [],
    
    /**
     * Add a line item to the invoice
     */
    addLineItem() {
        const item = {
            id: Date.now(),
            description: '',
            quantity: 1,
            price: 0,
            tax: 19 // Default German VAT
        };
        this.lineItems.push(item);
        this.updateLineItemsTable();
    },
    
    /**
     * Remove a line item
     */
    removeLineItem(id) {
        this.lineItems = this.lineItems.filter(item => item.id !== id);
        this.updateLineItemsTable();
    },
    
    /**
     * Update a line item value
     */
    updateLineItem(id, field, value) {
        const item = this.lineItems.find(i => i.id === id);
        if (item) {
            item[field] = value;
            this.updateLineItemsTable();
        }
    },
    
    /**
     * Update the line items table display
     */
    updateLineItemsTable() {
        const tbody = document.getElementById('ic-line-items');
        if (!tbody) return;
        
        if (this.lineItems.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
                        Noch keine Positionen vorhanden. Klicken Sie "Position hinzufügen" um zu beginnen.
                    </td>
                </tr>
            `;
            this.updateTotals();
            return;
        }
        
        const html = this.lineItems.map(item => {
            const total = item.quantity * item.price;
            return `
                <tr>
                    <td>
                        <input type="text" 
                               value="${this.escapeHtml(item.description)}" 
                               onchange="InvoiceCreator.updateLineItem(${item.id}, 'description', this.value)"
                               placeholder="Beschreibung"
                               style="width: 100%;">
                    </td>
                    <td>
                        <input type="number" 
                               value="${item.quantity}" 
                               onchange="InvoiceCreator.updateLineItem(${item.id}, 'quantity', parseFloat(this.value))"
                               min="0.01"
                               step="0.01"
                               style="width: 100%;">
                    </td>
                    <td>
                        <input type="number" 
                               value="${item.price}" 
                               onchange="InvoiceCreator.updateLineItem(${item.id}, 'price', parseFloat(this.value))"
                               min="0"
                               step="0.01"
                               style="width: 100%;">
                    </td>
                    <td>
                        <input type="number" 
                               value="${item.tax}" 
                               onchange="InvoiceCreator.updateLineItem(${item.id}, 'tax', parseFloat(this.value))"
                               min="0"
                               max="100"
                               step="0.01"
                               style="width: 100%;">
                    </td>
                    <td style="text-align: right; padding-right: 10px;">
                        ${this.formatCurrency(total)}
                    </td>
                    <td>
                        <button type="button" 
                                class="btn-icon" 
                                onclick="InvoiceCreator.removeLineItem(${item.id})"
                                title="Entfernen">
                            🗑️
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
        
        tbody.innerHTML = html;
        this.updateTotals();
    },
    
    /**
     * Calculate and update totals
     */
    updateTotals() {
        let subtotal = 0;
        let taxTotal = 0;
        
        this.lineItems.forEach(item => {
            const lineTotal = item.quantity * item.price;
            subtotal += lineTotal;
            taxTotal += lineTotal * (item.tax / 100);
        });
        
        const total = subtotal + taxTotal;
        
        document.getElementById('ic-subtotal').textContent = this.formatCurrency(subtotal);
        document.getElementById('ic-tax').textContent = this.formatCurrency(taxTotal);
        document.getElementById('ic-total').textContent = this.formatCurrency(total);
    },
    
    /**
     * Generate a unique invoice number
     */
    generateInvoiceNumber() {
        const year = new Date().getFullYear();
        const month = String(new Date().getMonth() + 1).padStart(2, '0');
        const random = String(Math.floor(Math.random() * 10000)).padStart(4, '0');
        const number = `RE-${year}${month}-${random}`;
        document.getElementById('ic-number').value = number;
    },
    
    /**
     * Update document type (invoice/credit)
     */
    updateDocumentType(type) {
        document.getElementById('ic-type').value = type;
        if (type === 'credit') {
            // Change number prefix for credits
            const currentNumber = document.getElementById('ic-number').value;
            if (currentNumber.startsWith('RE-')) {
                document.getElementById('ic-number').value = currentNumber.replace('RE-', 'GS-');
            }
        } else {
            const currentNumber = document.getElementById('ic-number').value;
            if (currentNumber.startsWith('GS-')) {
                document.getElementById('ic-number').value = currentNumber.replace('GS-', 'RE-');
            }
        }
    },
    
    /**
     * Submit the invoice creator form
     */
    async submit(event) {
        event.preventDefault();
        
        if (this.lineItems.length === 0) {
            if (typeof App !== 'undefined') {
                App.showToast('Bitte fügen Sie mindestens eine Position hinzu', 'error');
            } else {
                alert('Bitte fügen Sie mindestens eine Position hinzu');
            }
            return;
        }
        
        // Show loading state
        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳ Erstelle PDF...';
        
        try {
            // Collect form data
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData);
            
            // Add line items
            data.line_items = JSON.stringify(this.lineItems);
            
            // Calculate totals
            let subtotal = 0;
            let taxTotal = 0;
            this.lineItems.forEach(item => {
                const lineTotal = item.quantity * item.price;
                subtotal += lineTotal;
                taxTotal += lineTotal * (item.tax / 100);
            });
            data.amount = (subtotal + taxTotal).toFixed(2);
            data.subtotal = subtotal.toFixed(2);
            data.tax_total = taxTotal.toFixed(2);
            
            // Create description from line items
            const descriptions = this.lineItems.map(item => item.description).filter(d => d);
            data.description = descriptions.join(', ') || 'Rechnung';
            
            // Set type based on invoice_type
            data.type = data.invoice_type; // 'issued' or 'received'
            
            // Set status
            data.status = 'open';
            
            // Create invoice via API
            const endpoint = this.currentModule === 'private' 
                ? '/api/private.php?action=createInvoiceWithPDF' 
                : '/api/endpoint.php?action=createInvoiceWithPDF';
            
            const result = this.currentModule === 'private'
                ? await API.postForm(endpoint, formData)
                : await API.postShared('createInvoiceWithPDF', Object.fromEntries(formData));
            
            if (result && result.pdf_url) {
                // Download PDF
                const link = document.createElement('a');
                link.href = result.pdf_url;
                link.download = `${data.invoice_number || 'Rechnung'}.pdf`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                if (typeof App !== 'undefined') {
                    App.showToast('✅ Rechnung erstellt und PDF heruntergeladen', 'success');
                }
                
                this.close();
                
                // Reload invoices list
                if (this.currentModule === 'private' && typeof PrivateModule !== 'undefined') {
                    await PrivateModule.loadInvoices();
                    await PrivateModule.loadStats();
                } else if (this.currentModule === 'shared' && typeof SharedModule !== 'undefined') {
                    await SharedModule.loadInvoices();
                    await SharedModule.loadStats();
                }
            } else {
                throw new Error('PDF konnte nicht erstellt werden');
            }
            
        } catch (error) {
            console.error('Error creating invoice:', error);
            if (typeof App !== 'undefined') {
                App.showToast('Fehler beim Erstellen: ' + (error.message || 'Unbekannter Fehler'), 'error');
            } else {
                alert('Fehler beim Erstellen: ' + (error.message || 'Unbekannter Fehler'));
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    },
    
    /**
     * Format currency
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('de-DE', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount || 0);
    },
    
    /**
     * Escape HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }
};

// Auto-create modal when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        InvoiceCreator.createModal();
    });
} else {
    InvoiceCreator.createModal();
}
