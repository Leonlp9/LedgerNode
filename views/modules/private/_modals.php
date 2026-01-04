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
                <label for="private-tx-category">Kategorie (optional)</label>
                <input type="text"
                       id="private-tx-category"
                       name="category"
                       placeholder="z.B. Lebensmittel, Miete">
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
