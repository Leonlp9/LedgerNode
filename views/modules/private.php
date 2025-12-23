<div class="module-content">
    <!-- Tab: Dashboard -->
    <div class="tab-content" id="private-tab-dashboard">
        <div class="module-header">
            <h2>Dashboard</h2>
            <p class="subtitle">Deine persönlichen Finanzen</p>
        </div>

        <!-- Dashboard Cards -->
    <div class="dashboard-grid">
        <div class="card">
            <div class="card-header">
                <h3>Kontostand</h3>
                <span class="card-icon">💰</span>
            </div>
            <div class="card-body">
                <div class="stat-value" id="private-balance">0,00 €</div>
                <div class="stat-label">Aktueller Saldo</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Einnahmen</h3>
                <span class="card-icon">📈</span>
            </div>
            <div class="card-body">
                <div class="stat-value stat-positive" id="private-income">0,00 €</div>
                <div class="stat-label">Diesen Monat</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Ausgaben</h3>
                <span class="card-icon">📉</span>
            </div>
            <div class="card-body">
                <div class="stat-value stat-negative" id="private-expenses">0,00 €</div>
                <div class="stat-label">Diesen Monat</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Unverknüpfte Rechnungen</h3>
                <span class="card-icon">📄</span>
            </div>
            <div class="card-body">
                <div class="stat-value stat-warning" id="private-unlinked-invoices">0</div>
                <div class="stat-label">Noch nicht zugeordnet</div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <div class="card chart-card">
            <div class="card-header">
                <h3>Kontostandsverlauf (letzte 12 Monate)</h3>
            </div>
            <div class="card-body">
                <canvas id="chart-balance" width="600" height="250"></canvas>
            </div>
        </div>

        <div class="card chart-card">
            <div class="card-header">
                <h3>Ausgaben nach Kategorie</h3>
            </div>
            <div class="card-body">
                <canvas id="chart-expenses-categories" width="400" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Aktionen -->
    <div class="actions-bar">
        <button class="btn btn-primary" onclick="PrivateModule.showAddTransactionWithTab()">
            ➕ Neue Transaktion
        </button>
    </div>

    <!-- Transaktionsliste (Preview) -->
    <div class="card">
        <div class="card-header">
            <h3>Letzte Transaktionen</h3>
            <button class="btn btn-small" onclick="App.switchTab('private', 'transactions')">
                Alle anzeigen →
            </button>
        </div>
        <div class="card-body">
            <div id="private-transactions-preview" class="transactions-list">
                <!-- Wird dynamisch gefüllt -->
            </div>
        </div>
    </div>
</div>

<!-- Tab: Transaktionen -->
<div class="tab-content" id="private-tab-transactions" style="display: none;">
    <div class="module-header">
        <h2>Transaktionen</h2>
        <p class="subtitle">Alle privaten Transaktionen</p>
    </div>

    <!-- Aktionen -->
    <div class="actions-bar">
        <button class="btn btn-primary" onclick="PrivateModule.showAddTransaction()">
            ➕ Neue Transaktion
        </button>
    </div>

    <!-- Transaktionsliste -->
    <div class="card">
        <div class="card-header">
            <h3>Alle Transaktionen</h3>
            <button class="btn btn-small" onclick="PrivateModule.loadTransactions()">
                🔄 Aktualisieren
            </button>
        </div>
        <div class="card-body">
            <div id="private-transactions-list" class="transactions-list">
                <!-- Wird dynamisch gefüllt -->
                <div class="empty-state">
                    <p>Noch keine Transaktionen vorhanden</p>
                    <button class="btn btn-primary" onclick="PrivateModule.showAddTransaction()">
                        Erste Transaktion anlegen
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination für Transaktionen -->
    <div class="pagination-container" id="private-transaction-pagination" style="display: none;">
        <div class="pagination-info">
            <span id="private-transaction-pagination-info">Zeige 0-0 von 0</span>
        </div>
        <div class="pagination-controls">
            <button class="btn btn-small" id="private-transaction-prev-btn" onclick="PrivateModule.prevTransactionPage()" disabled>
                ‹ Zurück
            </button>
            <span class="pagination-pages" id="private-transaction-pages"></span>
            <button class="btn btn-small" id="private-transaction-next-btn" onclick="PrivateModule.nextTransactionPage()" disabled>
                Weiter ›
            </button>
        </div>
        <div class="pagination-per-page">
            <label for="private-transaction-per-page">Pro Seite:</label>
            <select id="private-transaction-per-page" onchange="PrivateModule.changeTransactionPerPage(this.value)">
                <option value="15" selected>15</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>
</div>

<!-- Tab: Konten verwalten -->
<div class="tab-content" id="private-tab-accounts" style="display: none;">
    <div class="module-header">
        <h2>Konten verwalten</h2>
        <p class="subtitle">Verwalte deine privaten Konten</p>
    </div>

    <!-- Aktionen -->
    <div class="actions-bar">
        <button class="btn btn-primary" onclick="PrivateModule.showAddAccount()">
            ➕ Neues Konto
        </button>
    </div>

    <!-- Kontenliste -->
    <div class="card">
        <div class="card-header">
            <h3>Deine Konten</h3>
            <button class="btn btn-small" onclick="PrivateModule.loadAccountsManagement()">
                🔄 Aktualisieren
            </button>
        </div>
        <div class="card-body">
            <div id="private-accounts-management" class="accounts-management-list">
                <!-- Wird dynamisch gefüllt -->
                <div class="empty-state">
                    <p>Noch keine Konten vorhanden</p>
                    <button class="btn btn-primary" onclick="PrivateModule.showAddAccount()">
                        Erstes Konto erstellen
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination für Konten -->
    <div class="pagination-container" id="private-account-pagination" style="display: none;">
        <div class="pagination-info">
            <span id="private-account-pagination-info">Zeige 0-0 von 0</span>
        </div>
        <div class="pagination-controls">
            <button class="btn btn-small" id="private-account-prev-btn" onclick="PrivateModule.prevAccountPage()" disabled>
                ‹ Zurück
            </button>
            <span class="pagination-pages" id="private-account-pages"></span>
            <button class="btn btn-small" id="private-account-next-btn" onclick="PrivateModule.nextAccountPage()" disabled>
                Weiter ›
            </button>
        </div>
        <div class="pagination-per-page">
            <label for="private-account-per-page">Pro Seite:</label>
            <select id="private-account-per-page" onchange="PrivateModule.changeAccountPerPage(this.value)">
                <option value="15" selected>15</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>
</div>

<!-- Modal: Neues Konto -->
<div id="private-account-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="private-account-modal-title">Neues Konto</h3>
            <button class="modal-close" onclick="PrivateModule.closeAccountModal()">&times;</button>
        </div>
        <form id="private-account-form" onsubmit="PrivateModule.submitAccount(event)">
            <input type="hidden" id="private-acc-id" name="id">
            <div class="form-group">
                <label for="private-acc-name">Kontoname</label>
                <input type="text" 
                       id="private-acc-name" 
                       name="name" 
                       placeholder="z.B. Hauptkonto"
                       required>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="PrivateModule.closeAccountModal()">
                    Abbrechen
                </button>
                <button type="submit" class="btn btn-primary">
                    Speichern
                </button>
            </div>
        </form>
    </div>
</div>

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

<!-- Modal: Neue Rechnung -->
<div id="private-invoice-modal" class="modal" style="display: none;">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 id="private-invoice-modal-title">Neue Rechnung</h3>
            <button class="modal-close" onclick="PrivateModule.closeInvoiceModal()">&times;</button>
        </div>
        <form id="private-invoice-form" onsubmit="PrivateModule.submitInvoice(event)" enctype="multipart/form-data">
            <input type="hidden" id="private-inv-id" name="id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="private-inv-type">Typ</label>
                    <select id="private-inv-type" name="type" required>
                        <option value="received">Erhalten (Rechnung/Gutschrift die ich bekommen habe)</option>
                        <option value="issued">Geschrieben (Rechnung/Gutschrift die ich geschrieben habe)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="private-inv-status">Status</label>
                    <select id="private-inv-status" name="status">
                        <option value="open">Offen</option>
                        <option value="paid">Bezahlt</option>
                        <option value="overdue">Überfällig</option>
                        <option value="cancelled">Storniert</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="private-inv-number">Rechnungsnummer</label>
                    <input type="text"
                           id="private-inv-number"
                           name="invoice_number"
                           placeholder="z.B. RE-2024-001">
                </div>

                <div class="form-group">
                    <label for="private-inv-amount">Betrag (€)</label>
                    <input type="number"
                           id="private-inv-amount"
                           name="amount"
                           step="0.01"
                           min="0.01"
                           placeholder="0.00"
                           required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="private-inv-date">Rechnungsdatum</label>
                    <input type="date"
                           id="private-inv-date"
                           name="invoice_date"
                           required>
                </div>

                <div class="form-group">
                    <label for="private-inv-due-date">Fälligkeitsdatum</label>
                    <input type="date"
                           id="private-inv-due-date"
                           name="due_date">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="private-inv-sender">Von (Absender)</label>
                    <input type="text"
                           id="private-inv-sender"
                           name="sender"
                           placeholder="Name des Absenders"
                           required>
                </div>

                <div class="form-group">
                    <label for="private-inv-recipient">An (Empfänger)</label>
                    <input type="text"
                           id="private-inv-recipient"
                           name="recipient"
                           placeholder="Name des Empfängers"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label for="private-inv-description">Beschreibung</label>
                <textarea id="private-inv-description" 
                          name="description" 
                          rows="3" 
                          placeholder="Beschreibung der Rechnung"
                          required></textarea>
            </div>

            <div class="form-group">
                <label for="private-inv-notes">Notizen (optional)</label>
                <textarea id="private-inv-notes" 
                          name="notes" 
                          rows="2" 
                          placeholder="Zusätzliche Notizen"></textarea>
            </div>

            <div class="form-group">
                <label for="private-inv-file">PDF/Bild hochladen</label>
                <input type="file" 
                       id="private-inv-file" 
                       name="file" 
                       accept=".pdf,.jpg,.jpeg,.png">
                <small class="form-hint">Erlaubt: PDF, JPG, PNG (max. 10 MB)</small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="PrivateModule.closeInvoiceModal()">
                    Abbrechen
                </button>
                <button type="submit" class="btn btn-primary">
                    Speichern
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Rechnung mit Überweisung verknüpfen -->
<div id="private-invoice-link-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Mit Überweisung verknüpfen</h3>
            <button class="modal-close" onclick="PrivateModule.closeLinkModal()">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="private-link-invoice-id">
            <input type="hidden" id="private-link-invoice-amount">
            <p>Wähle eine oder mehrere Überweisungen zum Verknüpfen:</p>
            <div id="private-available-transactions" class="transactions-list">
                <div class="loading">Lädt...</div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="PrivateModule.closeLinkModal()">
                Abbrechen
            </button>
        </div>
    </div>
</div>

<!-- Fullscreen Invoice Viewer Modal -->
<div id="private-invoice-viewer-modal" class="modal" style="display:none; align-items:stretch;">
    <div class="modal-content" style="width:100%; height:100vh; max-width:100%; border-radius:0; display:flex; flex-direction:column;">
        <div class="modal-header" style="display:flex; align-items:center; justify-content:space-between;">
            <div>
                <h3 id="viewer-inv-number">Rechnung</h3>
                <div id="viewer-inv-meta" style="font-size:0.9rem; color:#666666; margin-top:4px;"></div>
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
                <button id="viewer-edit-btn" class="btn btn-small btn-secondary" onclick="PrivateModule.toggleInvoiceEdit()">✏️ Bearbeiten</button>
                <!-- Styled close button: small, round, consistent with .btn styles -->
                <button class="btn btn-small btn-icon" onclick="PrivateModule.closeInvoiceViewer()" aria-label="Schließen" title="Schließen" style="border-radius:999px; padding:6px 8px; line-height:1; display:inline-flex; align-items:center; justify-content:center;">×</button>
            </div>
        </div>
        <div class="modal-body viewer-modal-body" style="flex:1; display:flex; gap:16px; padding:12px;">
            <div id="viewer-file-wrapper" class="viewer-file" style="flex:1; display:flex; flex-direction:column;">
                <!-- Viewer container:
                     - For PDFs we create an <object> element and insert it into #viewer-pdf-container (renders inline in the modal).
                     - For other types (images, html) we use the iframe (#viewer-file-iframe).
                -->
                <div id="viewer-pdf-container" style="width:100%; height:100%; display:none;"></div>
                <iframe id="viewer-file-iframe" src="" style="width:100%; height:100%; border:0; display:none;" sandbox="allow-scripts allow-forms"></iframe>
                <div id="viewer-no-file" style="display:none; padding:12px;">Keine Datei vorhanden</div>
            </div>
            <div class="viewer-sidebar" style="width:360px; max-width:40%; display:flex; flex-direction:column;">
                <input type="hidden" id="viewer-inv-id">
                <div id="viewer-view-section">
                    <!-- Clean, readable invoice summary -->
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div style="font-size:1.05rem; color:#222;"><strong>Betrag:</strong> <span id="viewer-inv-amount"></span></div>
                            <div style="font-size:0.95rem; color:#444;">
                                <span id="viewer-inv-status" style="padding:4px 8px; border-radius:12px; background:#f0f0f0; font-weight:600;">&nbsp;</span>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; font-size:0.95rem; color:#333;">
                            <div><small style="color:#666">Rechnungsnummer</small><div id="viewer-inv-number-small">-</div></div>
                            <div><small style="color:#666">Typ</small><div id="viewer-inv-type">-</div></div>
                            <div><small style="color:#666">Von</small><div id="viewer-inv-sender">-</div></div>
                            <div><small style="color:#666">An</small><div id="viewer-inv-recipient">-</div></div>
                            <div><small style="color:#666">Datum</small><div id="viewer-inv-date">-</div></div>
                            <div><small style="color:#666">Fällig</small><div id="viewer-inv-due">-</div></div>
                        </div>

                        <div style="margin-top:6px;"><small style="color:#666">Beschreibung / Notizen</small>
                            <div id="viewer-inv-description" style="background:#fafafa; border-radius:6px; padding:8px; margin-top:4px; color:#222; min-height:40px;"></div>
                        </div>

                        <div id="viewer-inv-linked" style="font-size:0.9rem; color:#666; margin-top:4px;"></div>
                    </div>
                 </div>

                <form id="viewer-edit-form" style="display:none;" onsubmit="PrivateModule.saveInvoiceEdits(event)">
                    <div class="form-group">
                        <label>Rechnungsnummer</label>
                        <input id="viewer-edit-number" name="invoice_number" type="text">
                    </div>
                    <div class="form-group">
                        <label>Betrag (€)</label>
                        <input id="viewer-edit-amount" name="amount" type="number" step="0.01" min="0.01">
                    </div>
                    <div class="form-group">
                        <label>Datum</label>
                        <input id="viewer-edit-date" name="invoice_date" type="date">
                    </div>
                    <div class="form-group">
                        <label>Fälligkeitsdatum</label>
                        <input id="viewer-edit-due" name="due_date" type="date">
                    </div>
                    <div class="form-group">
                        <label>Von (Absender)</label>
                        <input id="viewer-edit-sender" name="sender" type="text">
                    </div>
                    <div class="form-group">
                        <label>An (Empfänger)</label>
                        <input id="viewer-edit-recipient" name="recipient" type="text">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="viewer-edit-status" name="status">
                            <option value="open">Offen</option>
                            <option value="paid">Bezahlt</option>
                            <option value="overdue">Überfällig</option>
                            <option value="cancelled">Storniert</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Typ</label>
                        <select id="viewer-edit-type" name="type">
                            <option value="received">Erhalten</option>
                            <option value="issued">Geschrieben</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Beschreibung</label>
                        <textarea id="viewer-edit-description" name="description" rows="4"></textarea>
                    </div>

                    <div class="modal-footer" style="margin-top:auto;">
                        <button type="button" class="btn btn-secondary" onclick="PrivateModule.openInvoiceViewer(document.getElementById('viewer-inv-id').value)">Abbrechen</button>
                        <button type="submit" class="btn btn-primary">Speichern</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Neue Transaktion -->
<div id="private-transaction-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Neue Transaktion</h3>
            <button class="modal-close" onclick="PrivateModule.closeModal()">&times;</button>
        </div>
        <form id="private-transaction-form" onsubmit="PrivateModule.submitTransaction(event)">
            <div class="form-group">
                <label for="private-tx-type">Typ</label>
                <select id="private-tx-type" name="type" required>
                    <option value="expense">Ausgabe</option>
                    <option value="income">Einnahme</option>
                </select>
            </div>

            <div class="form-group">
                <label for="private-tx-account">Konto</label>
                <select id="private-tx-account" name="account_id" required>
                    <!-- Wird dynamisch gefüllt -->
                </select>
            </div>

            <div class="form-group">
                <label for="private-tx-amount">Betrag (€)</label>
                <input type="number" 
                       id="private-tx-amount" 
                       name="amount" 
                       step="0.01" 
                       min="0.01" 
                       required>
            </div>

            <div class="form-group">
                <label for="private-tx-description">Beschreibung</label>
                <input type="text" 
                       id="private-tx-description" 
                       name="description" 
                       required>
            </div>

            <div class="form-group">
                <label for="private-tx-date">Datum</label>
                <input type="date" 
                       id="private-tx-date" 
                       name="date" 
                       required>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="PrivateModule.closeModal()">
                    Abbrechen
                </button>
                <button type="submit" class="btn btn-primary">
                    Speichern
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Transaktionsdetails -->
<div id="private-transaction-detail-modal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Transaktionsdetails</h3>
            <button class="modal-close" onclick="PrivateModule.closeTransactionDetail()">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <small style="color: #666; display: block; margin-bottom: 4px;">Typ</small>
                    <div id="tx-detail-type" style="font-weight: 600;"></div>
                </div>
                <div>
                    <small style="color: #666; display: block; margin-bottom: 4px;">Betrag</small>
                    <div id="tx-detail-amount" style="font-weight: 600; font-size: 1.2rem;"></div>
                </div>
                <div>
                    <small style="color: #666; display: block; margin-bottom: 4px;">Konto</small>
                    <div id="tx-detail-account"></div>
                </div>
                <div>
                    <small style="color: #666; display: block; margin-bottom: 4px;">Datum</small>
                    <div id="tx-detail-date"></div>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <small style="color: #666; display: block; margin-bottom: 4px;">Beschreibung</small>
                <div id="tx-detail-description" style="background: #f5f5f5; padding: 12px; border-radius: 6px; min-height: 40px;"></div>
            </div>

            <div style="margin-bottom: 16px;">
                <small style="color: #666; display: block; margin-bottom: 4px;">Kategorie</small>
                <div id="tx-detail-category"></div>
            </div>

            <div id="tx-detail-linked" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e0e0e0;">
                <!-- Wird dynamisch gefüllt mit Verknüpfungsinformationen -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="PrivateModule.closeTransactionDetail()">
                Schließen
            </button>
            <button type="button" class="btn btn-danger" onclick="PrivateModule.deleteTransactionFromDetail()">
                🗑️ Löschen
            </button>
        </div>
    </div>
</div>

<script>
// Private Modul JavaScript
const PrivateModule = {
    // Chart.js-Instanzen
    charts: {
        balance: null,
        expensesCategories: null
    },

    // Invoice pagination state
    invoiceState: {
        currentPage: 1,
        perPage: 15,
        totalPages: 1,
        currentSubtab: 'all'
    },

    // Map to keep last loaded invoices by id (used by viewer)
    lastInvoices: {},

    // Transactions pagination state
    transactionState: {
        currentPage: 1,
        perPage: 15,
        totalPages: 1
    },

    // Accounts pagination state
    accountState: {
        currentPage: 1,
        perPage: 15,
        totalPages: 1
    },

    async init() {
        console.log('Private Module initialisiert');
        // Registriere Tabs speziell für dieses Modul (stellt sicher, dass App die richtigen Tabs zeigt)
        if (typeof App !== 'undefined' && typeof App.registerTabs === 'function') {
            App.registerTabs('private', [
                { id: 'dashboard', label: 'Dashboard', icon: '📊' },
                { id: 'transactions', label: 'Transaktionen', icon: '💳' },
                { id: 'accounts', label: 'Konten', icon: '📁' },
                { id: 'invoices', label: 'Rechnungen', icon: '📄' }
            ]);
        }

        await this.loadStats();
        await this.loadTransactions();
        await this.loadTransactionsPreview();
        await this.loadAccounts();
        this.initCharts();
        await this.updateCharts();

        // Setze heutiges Datum als Standard
        const txDateEl = document.getElementById('private-tx-date');
        if (txDateEl) txDateEl.valueAsDate = new Date();
        
        const invDateEl = document.getElementById('private-inv-date');
        if (invDateEl) invDateEl.valueAsDate = new Date();
    },

    initCharts() {
        // Defensive: Chart.js prüfen
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js ist nicht geladen — Charts werden nicht angezeigt');
            return;
        }

        // Falls bereits Chart-Instanzen existieren, diese zuerst zerstören
        try {
            if (this.charts.balance && typeof this.charts.balance.destroy === 'function') {
                this.charts.balance.destroy();
                this.charts.balance = null;
            }
        } catch (e) {
            console.warn('Fehler beim Zerstören des balance-Charts', e);
        }

        try {
            if (this.charts.expensesCategories && typeof this.charts.expensesCategories.destroy === 'function') {
                this.charts.expensesCategories.destroy();
                this.charts.expensesCategories = null;
            }
        } catch (e) {
            console.warn('Fehler beim Zerstören des expensesCategories-Charts', e);
        }

        // Balance Line Chart
        const canvasBalance = document.getElementById('chart-balance');
        if (canvasBalance && canvasBalance.getContext) {
            // Zusätzlich: falls Chart.js eine bereits registrierte Chart-Instanz für dieses Canvas kennt, zerstören
            try {
                if (typeof Chart.getChart === 'function') {
                    const existing = Chart.getChart(canvasBalance);
                    if (existing && typeof existing.destroy === 'function') {
                        existing.destroy();
                    }
                } else if (canvasBalance._chartInstance && typeof canvasBalance._chartInstance.destroy === 'function') {
                    // Fallback für ältere Chart.js-Versionen
                    canvasBalance._chartInstance.destroy();
                }
            } catch (e) {
                console.warn('Fehler beim Zerstören einer vorhandenen Chart-Instanz auf #chart-balance', e);
            }

            const ctxBalance = canvasBalance.getContext('2d');
            this.charts.balance = new Chart(ctxBalance, {
                type: 'line',
                data: {
                    labels: [], // Monate
                    datasets: [{
                        label: 'Kontostand',
                        data: [],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        tension: 0.2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: false }
                    }
                }
            });

            // Für Debugging: referenz auf canvas speichern (kann bei älteren Versionen nützlich sein)
            try { canvasBalance._chartInstance = this.charts.balance; } catch (e) { /* ignore */ }
        } else {
            console.warn('Canvas #chart-balance nicht gefunden oder unterstützt kein getContext');
        }

        // Expenses by Category Doughnut
        const canvasExp = document.getElementById('chart-expenses-categories');
        if (canvasExp && canvasExp.getContext) {
            try {
                if (typeof Chart.getChart === 'function') {
                    const existing = Chart.getChart(canvasExp);
                    if (existing && typeof existing.destroy === 'function') {
                        existing.destroy();
                    }
                } else if (canvasExp._chartInstance && typeof canvasExp._chartInstance.destroy === 'function') {
                    canvasExp._chartInstance.destroy();
                }
            } catch (e) {
                console.warn('Fehler beim Zerstören einer vorhandenen Chart-Instanz auf #chart-expenses-categories', e);
            }

            const ctxExp = canvasExp.getContext('2d');
            this.charts.expensesCategories = new Chart(ctxExp, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [],
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            try { canvasExp._chartInstance = this.charts.expensesCategories; } catch (e) { /* ignore */ }
        } else {
            console.warn('Canvas #chart-expenses-categories nicht gefunden oder unterstützt kein getContext');
        }
    },

    async updateCharts() {
        if (!this.charts.balance || !this.charts.expensesCategories) return;

        // Anfrage an (noch hypothetische) API-Endpunkte
        // Falls API nicht vorhanden, verwenden wir Beispiel-Daten
        let balanceSeries;
        try {
            balanceSeries = await API.get('/api/private/stats/balance_series');
        } catch (e) {
            console.debug('Balance series API nicht erreichbar, nutze Platzhalterdaten', e);
            balanceSeries = null;
        }

        if (!balanceSeries || !Array.isArray(balanceSeries.labels)) {
            // Beispiel: letzte 12 Monate
            const labels = [];
            const data = [];
            for (let i = 11; i >= 0; i--) {
                const d = new Date();
                d.setMonth(d.getMonth() - i);
                labels.push(d.toLocaleString('de-DE', { month: 'short', year: 'numeric' }));
                data.push((Math.random() * 2000 - 500).toFixed(2));
            }
            balanceSeries = { labels, data };
        }

        // Aktualisiere Balance Chart
        this.charts.balance.data.labels = balanceSeries.labels;
        this.charts.balance.data.datasets[0].data = balanceSeries.data.map(n => Number(n));
        this.charts.balance.update();

        // Expenses by category
        let expensesByCat;
        try {
            expensesByCat = await API.get('/api/private/stats/expenses_by_category');
        } catch (e) {
            console.debug('Expenses by category API nicht erreichbar, nutze Platzhalterdaten', e);
            expensesByCat = null;
        }

        if (!expensesByCat || !Array.isArray(expensesByCat)) {
            expensesByCat = [
                { category: 'Miete', amount: 800 },
                { category: 'Lebensmittel', amount: 250 },
                { category: 'Transport', amount: 120 },
                { category: 'Freizeit', amount: 90 }
            ];
        }

        const labels = expensesByCat.map(e => e.category);
        const data = expensesByCat.map(e => e.amount);
        const colors = labels.map((_, i) => this.getColor(i));

        this.charts.expensesCategories.data.labels = labels;
        this.charts.expensesCategories.data.datasets[0].data = data;
        this.charts.expensesCategories.data.datasets[0].backgroundColor = colors;
        this.charts.expensesCategories.update();
    },

    getColor(index) {
        const palette = [
            '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316'
        ];
        return palette[index % palette.length];
    },

    async loadStats() {
        // Lade Statistiken aus lokaler DB
        const stats = await API.get('/api/private.php?action=stats');
        
        if (stats) {
            document.getElementById('private-balance').textContent = 
                this.formatCurrency(stats.balance);
            document.getElementById('private-income').textContent = 
                this.formatCurrency(stats.income);
            document.getElementById('private-expenses').textContent = 
                this.formatCurrency(stats.expenses);
        }

        // Lade Rechnungsstatistiken
        const invoiceStats = await API.get('/api/private.php?action=getInvoiceStats');
        if (invoiceStats) {
            document.getElementById('private-unlinked-invoices').textContent = 
                invoiceStats.unlinked || 0;
        }
    },

    async loadTransactionsPreview() {
        const container = document.getElementById('private-transactions-preview');
        if (!container) return;
        
        container.innerHTML = '<div class="loading">Lädt...</div>';

        let transactions = await API.get('/api/private.php?action=transactions');

        // Defensive: falls API etwas anderes als ein Array liefert, konvertieren und warnen
        if (!Array.isArray(transactions)) {
            transactions = Array.isArray(transactions?.data) ? transactions.data : [];
        }

        if (!transactions || transactions.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <p>Noch keine Transaktionen vorhanden</p>
                </div>
            `;
            return;
        }

        // Show only last 5 transactions for preview
        const previewTransactions = transactions.slice(0, 5);

        // Ensure we have a map for quick access later (so details can open without reloading full list)
        this.lastTransactions = this.lastTransactions || {};
        previewTransactions.forEach(tx => this.lastTransactions[tx.id] = tx);

        const html = previewTransactions.map(tx => `
            <div class="transaction-item ${tx.type}" onclick="PrivateModule.openLinkedTransaction(${tx.id})" style="cursor: pointer;">
                <div class="transaction-info">
                    <div class="transaction-description">${this.escapeHtml(tx.description)}</div>
                    <div class="transaction-meta">
                        ${tx.account_name} • ${this.formatDate(tx.date)}
                    </div>
                </div>
                <div class="transaction-amount ${tx.type}">
                    ${tx.type === 'income' ? '+' : '-'}${this.formatCurrency(tx.amount)}
                </div>
            </div>
        `).join('');

        container.innerHTML = html;
    },

    async loadTransactions() {
        const container = document.getElementById('private-transactions-list');
        container.innerHTML = '<div class="loading">Lädt...</div>';

        // Build limit/offset from transactionState (endpoint /api/private/transactions expects limit/offset)
        const params = {
            limit: this.transactionState.perPage,
            offset: (this.transactionState.currentPage - 1) * this.transactionState.perPage
        };

        try {
            // Prefer the more modern endpoint which supports limit/offset
            const result = await API.get('/api/private/transactions', params);
            console.debug('PrivateModule.loadTransactions -> received:', result);

            // Normalize both paginated and non-paginated responses
            let transactions = [];
            let pagination = null;

            if (Array.isArray(result)) {
                transactions = result;
            } else if (result && Array.isArray(result.transactions)) {
                transactions = result.transactions;
                pagination = result.pagination || null;
            } else if (Array.isArray(result?.data)) {
                transactions = result.data;
                pagination = result.pagination || null;
            }

            if (!transactions || transactions.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>Noch keine Transaktionen vorhanden</p>
                    </div>
                `;
                document.getElementById('private-transaction-pagination').style.display = 'none';
                return;
            }

            // Store transactions for detail view
            this.lastTransactions = {};
            transactions.forEach(tx => this.lastTransactions[tx.id] = tx);

            const html = transactions.map(tx => `
                <div class="transaction-item ${tx.type}" onclick="PrivateModule.openTransactionDetail(${tx.id})" style="cursor: pointer;">
                    <div class="transaction-info">
                        <div class="transaction-description">${this.escapeHtml(tx.description)}</div>
                        <div class="transaction-meta">
                            ${tx.account_name} • ${this.formatDate(tx.date)}
                        </div>
                    </div>
                    <div class="transaction-amount ${tx.type}">
                        ${tx.type === 'income' ? '+' : '-'}${this.formatCurrency(tx.amount)}
                    </div>
                    <div class="transaction-actions">
                        <button class="btn-icon" onclick="event.stopPropagation(); PrivateModule.deleteTransaction(${tx.id})" title="Löschen">
                            🗑️
                        </button>
                    </div>
                </div>
            `).join('');

            container.innerHTML = html;

            // If backend returned raw array (no pagination), normalize to a single page
            if (!pagination) {
                pagination = { total: transactions.length, page: 1, per_page: transactions.length, total_pages: 1 };
            }
            // Update pagination
            this.updateTransactionPagination(pagination);
         } catch (error) {
            console.error('Fehler beim Laden der Transaktionen:', error);
            container.innerHTML = `
                <div class="error-state">
                    <p>Fehler beim Laden der Transaktionen</p>
                    <button class="btn btn-small" onclick="PrivateModule.loadTransactions()">
                        Erneut versuchen
                    </button>
                </div>
            `;
        }
    },

    updateTransactionPagination(pagination) {
        if (!pagination || pagination.total === 0) {
            document.getElementById('private-transaction-pagination').style.display = 'none';
            return;
        }

        document.getElementById('private-transaction-pagination').style.display = 'flex';
        this.transactionState.totalPages = pagination.total_pages;

        const start = (pagination.page - 1) * pagination.per_page + 1;
        const end = Math.min(pagination.page * pagination.per_page, pagination.total);
        document.getElementById('private-transaction-pagination-info').textContent = `Zeige ${start}-${end} von ${pagination.total}`;

        const prevBtn = document.getElementById('private-transaction-prev-btn');
        const nextBtn = document.getElementById('private-transaction-next-btn');
        prevBtn.disabled = pagination.page <= 1;
        nextBtn.disabled = pagination.page >= pagination.total_pages;

        const pagesContainer = document.getElementById('private-transaction-pages');
        pagesContainer.innerHTML = '';
        for (let i = 1; i <= pagination.total_pages; i++) {
            if (i === 1 || i === pagination.total_pages || (i >= pagination.page - 2 && i <= pagination.page + 2)) {
                const btn = document.createElement('button');
                btn.className = 'page-btn' + (i === pagination.page ? ' active' : '');
                btn.textContent = i;
                btn.onclick = () => this.goToTransactionPage(i);
                pagesContainer.appendChild(btn);
            } else if (i === pagination.page - 3 || i === pagination.page + 3) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                dots.className = 'page-dots';
                pagesContainer.appendChild(dots);
            }
        }
    },

    goToTransactionPage(page) {
        this.transactionState.currentPage = page;
        this.loadTransactions();
    },

    nextTransactionPage() {
        if (this.transactionState.currentPage < this.transactionState.totalPages) {
            this.transactionState.currentPage++;
            this.loadTransactions();
        }
    },

    prevTransactionPage() {
        if (this.transactionState.currentPage > 1) {
            this.transactionState.currentPage--;
            this.loadTransactions();
        }
    },

    changeTransactionPerPage(perPage) {
        this.transactionState.perPage = parseInt(perPage);
        this.transactionState.currentPage = 1;
        this.loadTransactions();
    },

    showAddTransaction() {
        document.getElementById('private-transaction-modal').style.display = 'flex';
    },

    closeModal() {
        document.getElementById('private-transaction-modal').style.display = 'none';
        document.getElementById('private-transaction-form').reset();
    },

    openTransactionDetail(transactionId) {
        const tx = this.lastTransactions[transactionId];
        if (!tx) {
            console.error('Transaction not found:', transactionId);
            return;
        }

        // Populate modal with transaction data
        const typeLabel = tx.type === 'income' ? '💰 Einnahme' : '💸 Ausgabe';
        const typeColor = tx.type === 'income' ? '#10b981' : '#ef4444';

        document.getElementById('tx-detail-type').innerHTML = `<span style="color: ${typeColor};">${typeLabel}</span>`;
        document.getElementById('tx-detail-amount').innerHTML = `
            <span style="color: ${typeColor};">
                ${tx.type === 'income' ? '+' : '-'}${this.formatCurrency(tx.amount)}
            </span>
        `;
        document.getElementById('tx-detail-account').textContent = tx.account_name || '-';
        document.getElementById('tx-detail-date').textContent = this.formatDate(tx.date);
        document.getElementById('tx-detail-description').textContent = tx.description || '-';
        document.getElementById('tx-detail-category').textContent = tx.category || 'Nicht kategorisiert';

        // Check if transaction is linked to an invoice
        const linkedDiv = document.getElementById('tx-detail-linked');
        // TODO: Add invoice linking information when available from backend
        linkedDiv.innerHTML = '';

        // Store current transaction ID for delete action
        this.currentTransactionId = transactionId;

        // Show modal
        document.getElementById('private-transaction-detail-modal').style.display = 'flex';
    },

    closeTransactionDetail() {
        document.getElementById('private-transaction-detail-modal').style.display = 'none';
        this.currentTransactionId = null;
    },

    async deleteTransactionFromDetail() {
        if (!this.currentTransactionId) return;

        if (!confirm('Möchtest du diese Transaktion wirklich löschen?')) {
            return;
        }

        try {
            await API.delete('/api/private.php?action=deleteTransaction&id=' + this.currentTransactionId);
            App.showToast('Transaktion gelöscht', 'success');
            this.closeTransactionDetail();
            await this.loadTransactions();
            await this.loadStats();
        } catch (error) {
            console.error('Fehler:', error);
            App.showToast('Fehler beim Löschen: ' + error.message, 'error');
        }
    },

    async submitTransaction(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        const result = await API.post('/api/private/transactions', data);
        
        if (result) {
            App.showToast('Transaktion gespeichert', 'success');
            this.closeModal();
            await this.loadStats();
            await this.loadTransactions();
            await this.loadTransactionsPreview();
            await this.updateCharts();
        }
    },

    async deleteTransaction(id) {
        if (!await App.confirm('Transaktion wirklich löschen?')) return;

        const result = await API.delete(`/api/private/transactions/${id}`);

        if (result) {
            App.showToast('Transaktion gelöscht', 'success');
            await this.loadStats();
            await this.loadTransactions();
            await this.loadTransactionsPreview();
            await this.updateCharts();
        }
    },

    formatCurrency(amount) {
        return new Intl.NumberFormat('de-DE', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    },

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('de-DE');
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    showAddTransactionWithTab() {
        // Wechsel zum Transaktionen-Tab und öffne dann das Modal
        // Async-safe: wechsle Tab, lade die Transaktionen und öffne dann das Modal
        (async () => {
            // ensure active module is correct
            if (App.currentModule !== 'private') {
                await App.switchModule('private');
            }
            App.switchTab('private', 'transactions');
            try {
                await this.loadTransactions();
            } catch (e) {
                console.warn('loadTransactions failed before opening modal', e);
            }
            this.showAddTransaction();
        })();
    },

    async loadAccountsManagement() {
        const container = document.getElementById('private-accounts-management');
        container.innerHTML = '<div class="loading">Lädt...</div>';

        const params = {
            page: this.accountState.currentPage,
            per_page: this.accountState.perPage
        };

        try {
            // Try paginated endpoint first, fall back to REST endpoint
            let result = null;
            try {
                result = await API.get('/api/private.php?action=accounts&' + new URLSearchParams(params));
            } catch (e) {
                console.debug('Paginated accounts endpoint not available, trying /api/private/accounts', e);
                result = await API.get('/api/private/accounts');
            }

            // Normalize
            let accounts = [];
            let pagination = null;
            if (Array.isArray(result)) {
                accounts = result;
            } else if (result && Array.isArray(result.accounts)) {
                accounts = result.accounts;
                pagination = result.pagination || null;
            } else if (Array.isArray(result?.data)) {
                accounts = result.data;
                pagination = result.pagination || null;
            } else if (result && typeof result === 'object') {
                // object map
                accounts = Object.values(result);
            }

            if (!accounts || accounts.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>Noch keine Konten vorhanden</p>
                        <button class="btn btn-primary" onclick="PrivateModule.showAddAccount()">
                            Erstes Konto erstellen
                        </button>
                    </div>
                `;
                const _accPagEl = document.getElementById('private-account-pagination'); if (_accPagEl) _accPagEl.style.display = 'none';
                return;
            }

            const html = accounts.map(acc => `
                <div class="account-manage-item">
                    <div class="account-manage-info">
                        <div class="account-manage-name">${this.escapeHtml(acc.name)}</div>
                        <div class="account-manage-meta">
                            ${acc.transaction_count || 0} Transaktionen • Saldo: ${this.formatCurrency(acc.balance || 0)}
                        </div>
                    </div>
                    <div class="account-manage-actions">
                        <button class="btn btn-small btn-secondary" data-account-id="${acc.id}" onclick="PrivateModule.editAccountById(this.dataset.accountId)">
                            ✏️ Bearbeiten
                        </button>
                        <button class="btn btn-small btn-danger" onclick="PrivateModule.deleteAccount(${acc.id})">
                            🗑️ Löschen
                        </button>
                    </div>
                </div>
            `).join('');

            container.innerHTML = html;

            // Update pagination
            this.updateAccountPagination(pagination || { total: accounts.length, page: 1, per_page: accounts.length, total_pages: 1 });
        } catch (error) {
            console.error('PrivateModule.loadAccountsManagement -> error', error);
            container.innerHTML = `
                <div class="error-state">
                    <p>Fehler beim Laden der Konten</p>
                    <button class="btn btn-small" onclick="PrivateModule.loadAccountsManagement()">Erneut versuchen</button>
                </div>
            `;
        }
    },

    updateAccountPagination(pagination) {
        if (!pagination || pagination.total === 0) {
            const _accPagEl2 = document.getElementById('private-account-pagination'); if (_accPagEl2) _accPagEl2.style.display = 'none';
            return;
        }

        document.getElementById('private-account-pagination').style.display = 'flex';
        this.accountState.totalPages = pagination.total_pages;

        const start = (pagination.page - 1) * pagination.per_page + 1;
        const end = Math.min(pagination.page * pagination.per_page, pagination.total);
        document.getElementById('private-account-pagination-info').textContent = `Zeige ${start}-${end} von ${pagination.total}`;

        const prevBtn = document.getElementById('private-account-prev-btn');
        const nextBtn = document.getElementById('private-account-next-btn');
        prevBtn.disabled = pagination.page <= 1;
        nextBtn.disabled = pagination.page >= pagination.total_pages;

        const pagesContainer = document.getElementById('private-account-pages');
        pagesContainer.innerHTML = '';
        for (let i = 1; i <= pagination.total_pages; i++) {
            if (i === 1 || i === pagination.total_pages || (i >= pagination.page - 2 && i <= pagination.page + 2)) {
                const btn = document.createElement('button');
                btn.className = 'page-btn' + (i === pagination.page ? ' active' : '');
                btn.textContent = i;
                btn.onclick = () => this.goToAccountPage(i);
                pagesContainer.appendChild(btn);
            } else if (i === pagination.page - 3 || i === pagination.page + 3) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                dots.className = 'page-dots';
                pagesContainer.appendChild(dots);
            }
        }
    },

    goToAccountPage(page) {
        this.accountState.currentPage = page;
        this.loadAccountsManagement();
    },

    nextAccountPage() {
        if (this.accountState.currentPage < this.accountState.totalPages) {
            this.accountState.currentPage++;
            this.loadAccountsManagement();
        }
    },

    prevAccountPage() {
        if (this.accountState.currentPage > 1) {
            this.accountState.currentPage--;
            this.loadAccountsManagement();
        }
    },

    changeAccountPerPage(perPage) {
        this.accountState.perPage = parseInt(perPage);
        this.accountState.currentPage = 1;
        this.loadAccountsManagement();
    },

    showAddAccount() {
        document.getElementById('private-account-modal-title').textContent = 'Neues Konto';
        document.getElementById('private-acc-id').value = '';
        document.getElementById('private-acc-name').value = '';
        document.getElementById('private-account-modal').style.display = 'flex';
    },

    editAccount(id, name) {
        document.getElementById('private-account-modal-title').textContent = 'Konto bearbeiten';
        document.getElementById('private-acc-id').value = id;
        document.getElementById('private-acc-name').value = name;
        document.getElementById('private-account-modal').style.display = 'flex';
    },

    async editAccountById(id) {
        // Fetch account details from the list
        let accounts = await API.get('/api/private/accounts');
        if (!Array.isArray(accounts)) {
            console.debug('PrivateModule.editAccountById: normalizing accounts response', accounts);
            accounts = Array.isArray(accounts?.data) ? accounts.data : (accounts ? Object.values(accounts) : []);
        }
        const account = accounts.find(acc => acc.id === Number(id));
        if (account) {
            this.editAccount(id, account.name);
        }
    },

    closeAccountModal() {
        document.getElementById('private-account-modal').style.display = 'none';
        document.getElementById('private-account-form').reset();
    },

    async submitAccount(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        const id = data.id;
        let result;

        if (id) {
            // Update existing account
            result = await API.put(`/api/private/accounts/${id}`, data);
        } else {
            // Create new account
            result = await API.post('/api/private/accounts', data);
        }

        if (result) {
            App.showToast(id ? 'Konto aktualisiert' : 'Konto erstellt', 'success');
            this.closeAccountModal();
            await this.loadAccounts();
            await this.loadAccountsManagement();
        }
    },

    async deleteAccount(id) {
        if (!await App.confirm('Konto wirklich löschen? Alle zugehörigen Transaktionen werden ebenfalls gelöscht.')) return;

        const result = await API.delete(`/api/private/accounts/${id}`);

        if (result) {
            App.showToast('Konto gelöscht', 'success');
            await this.loadAccounts();
            await this.loadAccountsManagement();
            await this.loadStats();
        }
    },

    // ========== Invoice Functions ==========

    async loadInvoices() {
        const container = document.getElementById('private-invoices-list');
        container.innerHTML = '<div class="loading">Lädt...</div>';

        const type = this.invoiceState.currentSubtab === 'all' ? null : this.invoiceState.currentSubtab;
        const params = {
            page: this.invoiceState.currentPage,
            per_page: this.invoiceState.perPage
        };
        if (type) params.type = type;

        try {
            const result = await API.get('/api/private.php?action=getInvoices&' + new URLSearchParams(params));
            
            if (!result || !result.invoices || result.invoices.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>Noch keine Rechnungen vorhanden</p>
                        <button class="btn btn-primary" onclick="PrivateModule.showAddInvoice()">
                            Erste Rechnung anlegen
                        </button>
                    </div>
                `;
                document.getElementById('private-invoice-pagination').style.display = 'none';
                return;
            }

            // Store invoices locally so the viewer can access them by id
            this.lastInvoices = {};
            result.invoices.forEach(inv => this.lastInvoices[inv.id] = inv);

            const html = result.invoices.map(inv => `
                <div class="invoice-item ${inv.is_linked ? 'linked' : 'unlinked'}" onclick="PrivateModule.openInvoiceViewer(${inv.id})" style="cursor:pointer;">
                    <div class="invoice-info">
                        <div class="invoice-header">
                            <span class="invoice-number">${this.escapeHtml(inv.invoice_number || 'Ohne Nr.')}</span>
                            <span class="invoice-type-badge ${inv.type}">${inv.type === 'received' ? '📥 Erhalten' : '📤 Geschrieben'}</span>
                            <span class="invoice-status-badge ${inv.status}">${this.getStatusLabel(inv.status)}</span>
                            ${!inv.is_linked ? '<span class="unlinked-badge">⚠️ Nicht verknüpft</span>' : ''}
                        </div>
                        <div class="invoice-description">${this.escapeHtml(inv.description)}</div>
                        <div class="invoice-meta">
                            Von: ${this.escapeHtml(inv.sender)} • An: ${this.escapeHtml(inv.recipient)} • ${this.formatDate(inv.invoice_date)}
                            ${inv.due_date ? ' • Fällig: ' + this.formatDate(inv.due_date) : ''}
                        </div>
                    </div>
                    <div class="invoice-amount">
                        ${this.formatCurrency(inv.amount)}
                    </div>
                    <div class="invoice-actions">
                        ${!inv.is_linked ? `
                            <button class="btn btn-small btn-info" onclick="event.stopPropagation(); PrivateModule.showLinkModal(${inv.id})" title="Mit Überweisung verknüpfen">
                                🔗 Verknüpfen
                            </button>
                        ` : ''}

                        <button class="btn-icon" onclick="event.stopPropagation(); PrivateModule.deleteInvoice(${inv.id})" title="Löschen">
                            🗑️
                        </button>
                    </div>
                </div>
            `).join('');

            container.innerHTML = html;

            // Update pagination
            this.updateInvoicePagination(result.pagination);

        } catch (error) {
            console.error('Fehler beim Laden der Rechnungen:', error);
            container.innerHTML = `
                <div class="error-state">
                    <p>Fehler beim Laden der Rechnungen</p>
                    <button class="btn btn-small" onclick="PrivateModule.loadInvoices()">
                        Erneut versuchen
                    </button>
                </div>
            `;
        }
    },

    updateInvoicePagination(pagination) {
        if (!pagination || pagination.total === 0) {
            document.getElementById('private-invoice-pagination').style.display = 'none';
            return;
        }

        document.getElementById('private-invoice-pagination').style.display = 'flex';
        
        this.invoiceState.totalPages = pagination.total_pages;
        
        // Update info text
        const start = (pagination.page - 1) * pagination.per_page + 1;
        const end = Math.min(pagination.page * pagination.per_page, pagination.total);
        document.getElementById('private-invoice-pagination-info').textContent = 
            `Zeige ${start}-${end} von ${pagination.total}`;

        // Update buttons
        const prevBtn = document.getElementById('private-invoice-prev-btn');
        const nextBtn = document.getElementById('private-invoice-next-btn');
        
        prevBtn.disabled = pagination.page <= 1;
        nextBtn.disabled = pagination.page >= pagination.total_pages;

        // Update page numbers
        const pagesContainer = document.getElementById('private-invoice-pages');
        pagesContainer.innerHTML = '';
        
        for (let i = 1; i <= pagination.total_pages; i++) {
            if (i === 1 || i === pagination.total_pages || (i >= pagination.page - 2 && i <= pagination.page + 2)) {
                const btn = document.createElement('button');
                btn.className = 'page-btn' + (i === pagination.page ? ' active' : '');
                btn.textContent = i;
                btn.onclick = () => this.goToInvoicePage(i);
                pagesContainer.appendChild(btn);
            } else if (i === pagination.page - 3 || i === pagination.page + 3) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                dots.className = 'page-dots';
                pagesContainer.appendChild(dots);
            }
        }
    },

    switchInvoiceSubtab(subtab) {
        this.invoiceState.currentSubtab = subtab;
        this.invoiceState.currentPage = 1;

        // Update subtab buttons
        document.querySelectorAll('.invoice-subtabs .subtab-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.subtab === subtab) {
                btn.classList.add('active');
            }
        });

        // Update title
        const titles = {
            'all': 'Alle Rechnungen',
            'received': 'Erhaltene Rechnungen',
            'issued': 'Geschriebene Rechnungen'
        };
        document.getElementById('private-invoice-list-title').textContent = titles[subtab] || 'Rechnungen';

        this.loadInvoices();
    },

    goToInvoicePage(page) {
        this.invoiceState.currentPage = page;
        this.loadInvoices();
    },

    nextInvoicePage() {
        if (this.invoiceState.currentPage < this.invoiceState.totalPages) {
            this.invoiceState.currentPage++;
            this.loadInvoices();
        }
    },

    prevInvoicePage() {
        if (this.invoiceState.currentPage > 1) {
            this.invoiceState.currentPage--;
            this.loadInvoices();
        }
    },

    changeInvoicePerPage(perPage) {
        this.invoiceState.perPage = parseInt(perPage);
        this.invoiceState.currentPage = 1;
        this.loadInvoices();
    },

    showAddInvoice() {
        document.getElementById('private-invoice-modal-title').textContent = 'Neue Rechnung';
        document.getElementById('private-invoice-form').reset();
        document.getElementById('private-inv-id').value = '';
        document.getElementById('private-inv-date').valueAsDate = new Date();
        document.getElementById('private-invoice-modal').style.display = 'flex';
    },

    closeInvoiceModal() {
        document.getElementById('private-invoice-modal').style.display = 'none';
        document.getElementById('private-invoice-form').reset();
    },

    async submitInvoice(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);
        const id = formData.get('id');

        try {
            const action = id ? 'updateInvoice' : 'createInvoice';

            // Verwende API-Helper mit FormData, damit basePath beachtet wird und PHP $_POST/$_FILES gefüllt werden
            const result = await API.postForm('/api/private.php?action=' + action, formData);

            // API.postForm wirft bei Fehler eine Exception; hier gilt: wenn wir hier sind, war's erfolgreich
            App.showToast(id ? 'Rechnung aktualisiert' : 'Rechnung erstellt', 'success');
            this.closeInvoiceModal();
            await this.loadInvoices();
            await this.loadStats();
         } catch (error) {
             console.error('Fehler:', error);
             App.showToast('Fehler beim Speichern: ' + error.message, 'error');
         }
    },

    async deleteInvoice(id) {
        if (!await App.confirm('Rechnung wirklich löschen?')) return;

        try {
            const fd = new FormData();
            fd.append('id', id);
            const result = await API.postForm('/api/private.php?action=deleteInvoice', fd);

            // Wenn wir hier sind, war der Aufruf erfolgreich
            App.showToast('Rechnung gelöscht', 'success');
            await this.loadInvoices();
            await this.loadStats();
         } catch (error) {
             console.error('Fehler:', error);
             App.showToast('Fehler beim Löschen: ' + error.message, 'error');
         }
    },

    async showLinkModal(invoiceId) {
        document.getElementById('private-link-invoice-id').value = invoiceId;
        document.getElementById('private-invoice-link-modal').style.display = 'flex';

        const container = document.getElementById('private-available-transactions');
        container.innerHTML = '<div class="loading">Lädt...</div>';

        try {
            const transactions = await API.get('/api/private.php?action=getAvailableTransactions&invoice_id=' + invoiceId);

            console.log('Invoice ID:', invoiceId);
            console.log('Transactions received:', transactions);
            console.log('Transactions length:', transactions ? transactions.length : 'null');

            if (!transactions || transactions.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>Keine Transaktionen gefunden</p>
                        <small>Es wurden keine Transaktionen gefunden, die noch nicht verknüpft sind.</small>
                    </div>
                `;
                return;
            }

            // Get invoice to know the amount
            const invoice = this.lastInvoices[invoiceId];
            console.log('Invoice data:', invoice);
            const invoiceAmount = invoice ? parseFloat(invoice.amount) : 0;
            document.getElementById('private-link-invoice-amount').value = invoiceAmount;

            // Split into matching and non-matching
            const matching = transactions.filter(tx => parseInt(tx.is_matching_amount) === 1);
            const others = transactions.filter(tx => parseInt(tx.is_matching_amount) !== 1);

            let html = '';

            // Show recommended (matching amount) first
            if (matching.length > 0) {
                html += '<h4 style="margin: 12px 0 8px 0; font-size: 0.9rem; color: #666; font-weight: 600;">Empfohlen (gleicher Betrag)</h4>';
                html += matching.map(tx => `
                    <div class="transaction-item ${tx.type}" onclick="PrivateModule.linkInvoiceToTransaction(${invoiceId}, ${tx.id}, ${tx.amount}, ${invoiceAmount})">
                        <div class="transaction-info">
                            <div class="transaction-description">${this.escapeHtml(tx.description)}</div>
                            <div class="transaction-meta">
                                ${this.escapeHtml(tx.account_name)} • ${this.formatDate(tx.date)}
                            </div>
                        </div>
                        <div class="transaction-amount ${tx.type}">
                            ${tx.type === 'income' ? '+' : '-'}${this.formatCurrency(tx.amount)}
                        </div>
                    </div>
                `).join('');
            }

            // Show other transactions
            if (others.length > 0) {
                html += '<h4 style="margin: 16px 0 8px 0; font-size: 0.9rem; color: #666; font-weight: 600;">Alle anderen</h4>';
                html += others.map(tx => `
                    <div class="transaction-item ${tx.type}" onclick="PrivateModule.linkInvoiceToTransaction(${invoiceId}, ${tx.id}, ${tx.amount}, ${invoiceAmount})">
                        <div class="transaction-info">
                            <div class="transaction-description">${this.escapeHtml(tx.description)}</div>
                            <div class="transaction-meta">
                                ${this.escapeHtml(tx.account_name)} • ${this.formatDate(tx.date)}
                            </div>
                        </div>
                        <div class="transaction-amount ${tx.type}">
                            ${tx.type === 'income' ? '+' : '-'}${this.formatCurrency(tx.amount)}
                        </div>
                    </div>
                `).join('');
            }

            container.innerHTML = html;
        } catch (error) {
            console.error('Fehler:', error);
            container.innerHTML = '<div class="error-state"><p>Fehler beim Laden</p></div>';
        }
    },

    closeLinkModal() {
        document.getElementById('private-invoice-link-modal').style.display = 'none';
    },

    showLinkModalFromViewer(invoiceId) {
        // Close the invoice viewer first
        this.closeInvoiceViewer();
        // Then open the link modal with a small delay to ensure smooth transition
        setTimeout(() => {
            this.showLinkModal(invoiceId);
        }, 100);
    },

    async linkInvoiceToTransaction(invoiceId, transactionId, transactionAmount, invoiceAmount) {
        // Check if amounts match
        const txAmount = parseFloat(transactionAmount);
        const invAmount = parseFloat(invoiceAmount);

        if (Math.abs(txAmount - invAmount) > 0.01) {
            const difference = Math.abs(txAmount - invAmount);
            const message = txAmount < invAmount
                ? `Die Transaktion (${this.formatCurrency(txAmount)}) ist niedriger als der Rechnungsbetrag (${this.formatCurrency(invAmount)}). Differenz: ${this.formatCurrency(difference)}.\n\nWo ist der Rest? Möchtest du trotzdem verknüpfen?`
                : `Die Transaktion (${this.formatCurrency(txAmount)}) ist höher als der Rechnungsbetrag (${this.formatCurrency(invAmount)}). Differenz: ${this.formatCurrency(difference)}.\n\nMöchtest du trotzdem verknüpfen?`;

            if (!confirm(message)) {
                return;
            }
        }

        try {
            const fd = new FormData();
            fd.append('invoice_id', invoiceId);
            fd.append('transaction_id', transactionId);
            const result = await API.postForm('/api/private.php?action=linkInvoiceToTransaction', fd);

            this.closeLinkModal();
            await this.loadInvoices();
            await this.loadStats();

            // Show success toast
            App.showToast('✓ Erfolgreich verknüpft', 'success');

            // Highlight the linked invoice briefly
            setTimeout(() => {
                const invoiceItems = document.querySelectorAll('.invoice-item');
                invoiceItems.forEach(item => {
                    if (item.querySelector('.invoice-item')) return; // Skip if already processed
                    const itemId = item.onclick?.toString().match(/openInvoiceViewer\((\d+)\)/)?.[1];
                    if (itemId && parseInt(itemId) === parseInt(invoiceId)) {
                        item.style.transition = 'all 0.3s ease';
                        item.style.backgroundColor = '#d4edda';
                        item.style.border = '2px solid #28a745';

                        // Remove highlight after 3 seconds
                        setTimeout(() => {
                            item.style.backgroundColor = '';
                            item.style.border = '';
                        }, 1000);
                    }
                });
            }, 100);
         } catch (error) {
             console.error('Fehler:', error);
             App.showToast('Fehler beim Verknüpfen: ' + error.message, 'error');
         }
    },

    async unlinkInvoice(id) {
        if (!confirm('Möchtest du die Verknüpfung wirklich aufheben?')) {
            return;
        }

        try {
            const fd = new FormData();
            fd.append('invoice_id', id);
            await API.postForm('/api/private.php?action=unlinkInvoiceFromTransaction', fd);

            App.showToast('✂️ Verknüpfung aufgehoben', 'success');

            // Refresh invoice list and viewer
            await this.loadInvoices();
            await this.loadStats();

            // If viewer is open, refresh it
            const viewerModal = document.getElementById('private-invoice-viewer-modal');
            if (viewerModal && viewerModal.style.display === 'flex') {
                // Re-open the viewer with updated data
                const inv = this.lastInvoices[id];
                if (inv) {
                    this.openInvoiceViewer(id);
                }
            }
        } catch (error) {
            console.error('Fehler:', error);
            App.showToast('Fehler beim Entknüpfen: ' + error.message, 'error');
        }
    },

    getStatusLabel(status) {
        const labels = {
            'open': 'Offen',
            'paid': 'Bezahlt',
            'overdue': 'Überfällig',
            'cancelled': 'Storniert'
        };
        return labels[status] || status;
    },

    async openLinkedTransaction(transactionId) {
        // Close invoice viewer
        this.closeInvoiceViewer();

        // Ensure the private module is active and switch to transactions tab
        try {
            if (typeof App !== 'undefined' && App.currentModule !== 'private') {
                await App.switchModule('private');
            }
            if (typeof App !== 'undefined' && typeof App.switchTab === 'function') {
                App.switchTab('private', 'transactions');
            }
        } catch (e) {
            console.warn('Could not switch module/tab automatically', e);
        }

        // Wait a bit for the UI to update
        await new Promise(resolve => setTimeout(resolve, 300));

        // Ensure transactions are loaded
        if (!this.lastTransactions || !this.lastTransactions[transactionId]) {
            try {
                await this.loadTransactions();
                // small delay to ensure DOM is rendered
                await new Promise(resolve => setTimeout(resolve, 100));
            } catch (e) {
                console.warn('Failed to load transactions before opening detail', e);
            }
        }

        // Open transaction detail if available
        if (this.lastTransactions && this.lastTransactions[transactionId]) {
            this.openTransactionDetail(transactionId);
        } else {
            if (typeof App !== 'undefined' && typeof App.showToast === 'function') {
                App.showToast('Transaktion konnte nicht gefunden werden', 'error');
            } else {
                alert('Transaktion konnte nicht gefunden werden');
            }
        }
    },

    // Viewer: öffnet ein Fullscreen-Modal mit eingebetteter Datei und Edit-Funktion
    openInvoiceViewer(id) {
        const inv = this.lastInvoices[id];
        if (!inv) return;

        // Resolve file URL and prefix APP_BASE when necessary
        let file = inv.file_url || inv.file_path || null;
        let fileUrl = null;
        if (file) {
            if (file.startsWith('http://') || file.startsWith('https://')) {
                fileUrl = file;
            } else {
                // Ensure leading slash
                const normalized = file.startsWith('/') ? file : '/' + file;
                fileUrl = (window.APP_BASE || '') + normalized;
            }
        }

        const modal = document.getElementById('private-invoice-viewer-modal');
        document.getElementById('viewer-inv-id').value = id;
        document.getElementById('viewer-inv-number').textContent = inv.invoice_number || '';
        document.getElementById('viewer-inv-meta').textContent = `Von: ${inv.sender || ''} • An: ${inv.recipient || ''} • ${inv.invoice_date ? this.formatDate(inv.invoice_date) : ''}` + (inv.due_date ? ' • Fällig: ' + this.formatDate(inv.due_date) : '');
        document.getElementById('viewer-inv-amount').textContent = this.formatCurrency(inv.amount || 0);
        document.getElementById('viewer-inv-description').textContent = inv.description || '';

        // Additional fields introduced in the sidebar
        const setText = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value || '-'; };
        setText('viewer-inv-number-small', inv.invoice_number || '-');
        setText('viewer-inv-type', inv.type === 'issued' ? 'Geschrieben' : (inv.type === 'received' ? 'Erhalten' : '-'));
        setText('viewer-inv-sender', inv.sender || '-');
        setText('viewer-inv-recipient', inv.recipient || '-');
        setText('viewer-inv-date', inv.invoice_date ? this.formatDate(inv.invoice_date) : '-');
        setText('viewer-inv-due', inv.due_date ? this.formatDate(inv.due_date) : '-');

        // Status badge: human-readable label and subtle color
        const statusEl = document.getElementById('viewer-inv-status');
        if (statusEl) {
            const label = this.getStatusLabel(inv.status || 'open');
            statusEl.textContent = label;
            // simple color mapping
            const colors = { 'paid': '#d1fae5', 'open': '#f0f0f0', 'overdue': '#fff1f0', 'cancelled': '#f3f4f6' };
            const textColors = { 'paid': '#065f46', 'open': '#111827', 'overdue': '#9a3412', 'cancelled': '#374151' };
            const bg = colors[inv.status] || '#f0f0f0';
            const fg = textColors[inv.status] || '#111827';
            statusEl.style.background = bg;
            statusEl.style.color = fg;
        }

        // Linked transaction info
        const linkedEl = document.getElementById('viewer-inv-linked');
        if (linkedEl) {
            const linkedTransaction = inv.transaction_description || inv.linked_transaction;
            if (inv.is_linked && linkedTransaction && inv.transaction_id) {
                linkedEl.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 8px;">
                        <span>🔗 Verknüpft mit:
                            <strong style="cursor: pointer; color: #2563eb; text-decoration: underline;"
                                    onclick="PrivateModule.openLinkedTransaction(${inv.transaction_id})"
                                    title="Transaktion anzeigen">
                                ${this.escapeHtml(linkedTransaction)}
                            </strong>
                        </span>
                        <button class="btn btn-small btn-secondary" onclick="PrivateModule.unlinkInvoice(${inv.id})" title="Verknüpfung aufheben">
                            ✂️ Entknüpfen
                        </button>
                    </div>
                `;
            } else if (inv.is_linked) {
                linkedEl.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 8px;">
                        <span>🔗 Verknüpft mit einer Transaktion</span>
                        <button class="btn btn-small btn-secondary" onclick="PrivateModule.unlinkInvoice(${inv.id})" title="Verknüpfung aufheben">
                            ✂️ Entknüpfen
                        </button>
                    </div>
                `;
            } else {
                linkedEl.innerHTML = `
                    <div style="margin-top: 8px;">
                        <span style="color: #666;">⚠️ Nicht verknüpft</span>
                        <button class="btn btn-small btn-info" onclick="PrivateModule.showLinkModalFromViewer(${inv.id})" title="Mit Überweisung verknüpfen">
                            🔗 Verknüpfen
                        </button>
                    </div>
                `;
            }
        }

        const iframe = document.getElementById('viewer-file-iframe');
        const pdfContainer = document.getElementById('viewer-pdf-container');
        const noFileEl = document.getElementById('viewer-no-file');

        // Clear previous viewers
        iframe.src = '';
        iframe.style.display = 'none';
        pdfContainer.innerHTML = '';
        pdfContainer.style.display = 'none';
        noFileEl.style.display = 'none';

        if (fileUrl) {
            // Determine file extension to handle PDFs specially
            const cleanUrl = fileUrl.split('#')[0].split('?')[0];
            const ext = (cleanUrl.split('.').pop() || '').toLowerCase();

            if (ext === 'pdf') {
                // Try to embed the PDF using <object> so the browser renders it inline in the modal.
                // This avoids sandbox/iframe-origin issues that Chrome blocks for PDF plugin.
                const obj = document.createElement('object');
                obj.data = fileUrl;
                obj.type = 'application/pdf';
                obj.width = '100%';
                obj.height = '100%';
                obj.style.border = '0';
                obj.innerHTML = `PDF kann nicht eingebettet werden. <a href="${this.escapeHtml(fileUrl)}" target="_blank" rel="noopener">Öffne die PDF in einem neuen Tab</a>`;

                pdfContainer.appendChild(obj);
                pdfContainer.style.display = 'block';
            } else {
                // Non-PDF: show inside iframe (images, html, etc.). Keep sandbox for safety.
                iframe.src = fileUrl;
                iframe.style.display = 'block';
            }
        } else {
            noFileEl.style.display = 'block';
        }

        // Reset edit form if present
        document.getElementById('viewer-edit-form').style.display = 'none';
        document.getElementById('viewer-view-section').style.display = 'block';
        document.getElementById('viewer-edit-btn').style.display = 'inline-block';

        modal.style.display = 'flex';
    },

    closeInvoiceViewer() {
        const modal = document.getElementById('private-invoice-viewer-modal');
        const iframe = document.getElementById('viewer-file-iframe');
        iframe.src = '';
        modal.style.display = 'none';
    },

    toggleInvoiceEdit() {
        const view = document.getElementById('viewer-view-section');
        const form = document.getElementById('viewer-edit-form');
        const id = document.getElementById('viewer-inv-id').value;
        const inv = this.lastInvoices[id];
        if (!inv) return;

        // Show form and populate values
        document.getElementById('viewer-edit-number').value = inv.invoice_number || '';
        document.getElementById('viewer-edit-amount').value = inv.amount || '';
        document.getElementById('viewer-edit-date').value = inv.invoice_date || '';
        document.getElementById('viewer-edit-due').value = inv.due_date || '';
        document.getElementById('viewer-edit-sender').value = inv.sender || '';
        document.getElementById('viewer-edit-recipient').value = inv.recipient || '';
        document.getElementById('viewer-edit-description').value = inv.description || '';
        document.getElementById('viewer-edit-status').value = inv.status || 'open';
        document.getElementById('viewer-edit-type').value = inv.type || 'received';

        view.style.display = 'none';
        form.style.display = 'block';
        document.getElementById('viewer-edit-btn').style.display = 'none';
    },

    async saveInvoiceEdits(event) {
        event.preventDefault();
        const id = document.getElementById('viewer-inv-id').value;
        const inv = this.lastInvoices[id];
        if (!inv) return;

        const formEl = document.getElementById('viewer-edit-form');
        const formData = new FormData(formEl);
        formData.append('id', id);

        try {
            const result = await API.postForm('/api/private.php?action=updateInvoice', formData);
            App.showToast('Rechnung aktualisiert', 'success');
            // Refresh invoice list and viewer data
            await this.loadInvoices();
            // After reload, update local inv reference
            if (this.lastInvoices[id]) {
                // Merge returned fields if API liefert aktualisierte invoice
                this.lastInvoices[id] = Object.assign({}, this.lastInvoices[id], result.invoice || {});
            }
            // Re-open viewer with fresh data
            this.openInvoiceViewer(id);
        } catch (error) {
            console.error('Fehler beim Speichern der Rechnung:', error);
            App.showToast('Fehler beim Speichern: ' + (error.message || 'Unbekannter Fehler'), 'error');
        }
    },

    async loadAccounts() {
        // Populate account select used in transaction form
        const select = document.getElementById('private-tx-account');
        if (!select) return;

        try {
            // Try action-based endpoint first, fallback to REST-like path
            let accounts = null;
            try {
                accounts = await API.get('/api/private.php?action=accounts');
            } catch (e) {
                console.debug('Fallback to /api/private/accounts for loadAccounts', e);
                accounts = await API.get('/api/private/accounts');
            }

            // Normalize response formats: accept { data: [...] }, array, or object map
            if (!Array.isArray(accounts)) {
                accounts = Array.isArray(accounts?.data) ? accounts.data : (accounts ? Object.values(accounts) : []);
            }

            if (!accounts || accounts.length === 0) {
                select.innerHTML = '<option value="">-- Kein Konto --</option>';
                return;
            }

            select.innerHTML = accounts.map(acc => `<option value="${acc.id}">${this.escapeHtml(acc.name)}</option>`).join('');
        } catch (error) {
            console.error('Failed to load accounts for select:', error);
            select.innerHTML = '<option value="">-- Fehler beim Laden --</option>';
        }
    }
};

// Auto-init: erst starten wenn DOM fertig ist und API & App geladen sind
function _startPrivateModuleWhenReady() {
    function tryInit() {
        if (typeof API !== 'undefined' && typeof App !== 'undefined') {
            PrivateModule.init();
            return true;
        }
        return false;
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        if (!tryInit()) {
            // Falls noch nicht verfügbar, warte kurz
            const interval = setInterval(() => {
                if (tryInit()) clearInterval(interval);
            }, 50);
        }
    } else {
        document.addEventListener('DOMContentLoaded', () => {
            if (!tryInit()) {
                const interval = setInterval(() => {
                    if (tryInit()) clearInterval(interval);
                }, 50);
            }
        });
    }
}

_startPrivateModuleWhenReady();
</script>

</div>

