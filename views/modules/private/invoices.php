<!-- Tab: Rechnungen -->
<div class="tab-content" id="private-tab-invoices" style="display: none;">
    <div class="module-header">
        <h2>Rechnungen</h2>
        <p class="subtitle">Verwalte deine Rechnungen und Gutschriften</p>
    </div>

    <!-- Sub-Tabs für Rechnungen -->
    <div class="invoice-subtabs">
        <button class="subtab-btn active" data-subtab="all" onclick="PrivateModule.switchInvoiceSubtab('all')">
            📊 Alle
        </button>
        <button class="subtab-btn" data-subtab="received" onclick="PrivateModule.switchInvoiceSubtab('received')">
            📥 Erhalten
        </button>
        <button class="subtab-btn" data-subtab="issued" onclick="PrivateModule.switchInvoiceSubtab('issued')">
            📤 Geschrieben
        </button>
    </div>

    <!-- Aktionen -->
    <div class="actions-bar">
        <button class="btn btn-primary" onclick="PrivateModule.showAddInvoice()">
            ➕ Neue Rechnung
        </button>
        <button class="btn btn-success" onclick="InvoiceCreator.open('private', 'invoice')">
            📝 Rechnung erstellen
        </button>
        <button class="btn btn-info" onclick="InvoiceCreator.open('private', 'credit')">
            📝 Gutschrift erstellen
        </button>
    </div>

    <!-- Rechnungsliste -->
    <div class="card">
        <div class="card-header">
            <h3 id="private-invoice-list-title">Alle Rechnungen</h3>
            <button class="btn btn-small" onclick="PrivateModule.loadInvoices()">
                🔄 Aktualisieren
            </button>
        </div>
        <div class="card-body">
            <div id="private-invoices-list" class="invoices-list">
                <!-- Wird dynamisch gefüllt -->
                <div class="empty-state">
                    <p>Noch keine Rechnungen vorhanden</p>
                    <button class="btn btn-primary" onclick="PrivateModule.showAddInvoice()">
                        Erste Rechnung anlegen
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination-container" id="private-invoice-pagination" style="display: none;">
        <div class="pagination-info">
            <span id="private-invoice-pagination-info">Zeige 0-0 von 0</span>
        </div>
        <div class="pagination-controls">
            <button class="btn btn-small" id="private-invoice-prev-btn" onclick="PrivateModule.prevInvoicePage()" disabled>
                ‹ Zurück
            </button>
            <span class="pagination-pages" id="private-invoice-pages"></span>
            <button class="btn btn-small" id="private-invoice-next-btn" onclick="PrivateModule.nextInvoicePage()" disabled>
                Weiter ›
            </button>
        </div>
        <div class="pagination-per-page">
            <label for="private-invoice-per-page">Pro Seite:</label>
            <select id="private-invoice-per-page" onchange="PrivateModule.changeInvoicePerPage(this.value)">
                <option value="15" selected>15</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>
</div>
