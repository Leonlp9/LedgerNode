<!-- Tab: Rechnungen (Placeholder for shared invoices) -->
<div class="tab-content" id="shared-tab-invoices" style="display: none;">
    <div class="module-header">
        <h2>Gemeinsame Rechnungen</h2>
        <p class="subtitle">Verwalte gemeinsame Rechnungen und Gutschriften</p>
    </div>

    <!-- Sub-Tabs für Rechnungen -->
    <div class="invoice-subtabs">
        <button class="subtab-btn active" data-subtab="all">
            📊 Alle
        </button>
        <button class="subtab-btn" data-subtab="received">
            📥 Erhalten
        </button>
        <button class="subtab-btn" data-subtab="issued">
            📤 Geschrieben
        </button>
    </div>

    <!-- Aktionen -->
    <div class="actions-bar">
        <button class="btn btn-success" onclick="InvoiceCreator.open('shared', 'invoice')">
            📝 Rechnung erstellen
        </button>
        <button class="btn btn-info" onclick="InvoiceCreator.open('shared', 'credit')">
            📝 Gutschrift erstellen
        </button>
    </div>

    <!-- Placeholder content -->
    <div class="card">
        <div class="card-header">
            <h3>Rechnungen</h3>
        </div>
        <div class="card-body">
            <div class="empty-state">
                <p>Gemeinsame Rechnungsverwaltung</p>
                <small>Funktion wird ähnlich wie Private Rechnungen implementiert, aber nutzt die Server-API.</small>
            </div>
        </div>
    </div>
</div>
