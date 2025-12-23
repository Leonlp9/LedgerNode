<div class="module-content">
    <!-- Tab: Dashboard -->
    <div class="tab-content" id="shared-tab-dashboard">
        <div class="module-header">
            <h2>Dashboard</h2>
            <p class="subtitle">Zentral verwaltete Finanzen</p>
            <?php if (\App\Core\Config::isClient()): ?>
                <div class="sync-status" id="sync-status">
                    <span class="status-indicator"></span>
                    <span>Verbunden mit Server</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Dashboard Cards -->
    <div class="dashboard-grid">
        <div class="card">
            <div class="card-header">
                <h3>Gesamtsaldo</h3>
                <span class="card-icon">💼</span>
            </div>
            <div class="card-body">
                <div class="stat-value" id="shared-balance">0,00 €</div>
                <div class="stat-label">Gemeinsame Mittel</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Einnahmen</h3>
                <span class="card-icon">💵</span>
            </div>
            <div class="card-body">
                <div class="stat-value stat-positive" id="shared-income">0,00 €</div>
                <div class="stat-label">Gesamt</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Ausgaben</h3>
                <span class="card-icon">💸</span>
            </div>
            <div class="card-body">
                <div class="stat-value stat-negative" id="shared-expenses">0,00 €</div>
                <div class="stat-label">Gesamt</div>
            </div>
        </div>
    </div>

    <!-- Aktionen -->
    <div class="actions-bar">
        <button class="btn btn-primary" onclick="SharedModule.showAddTransactionWithTab()">
            ➕ Neue Transaktion
        </button>
        <?php if (\App\Core\Config::isClient()): ?>
            <button class="btn btn-info" onclick="SharedModule.syncWithServer()">
                🔄 Synchronisieren
            </button>
        <?php endif; ?>
    </div>

    <!-- Gemeinsame Konten (Preview) -->
    <div class="card">
        <div class="card-header">
            <h3>Gemeinsame Konten</h3>
            <button class="btn btn-small" onclick="App.switchTab('shared', 'accounts')">
                Alle verwalten →
            </button>
        </div>
        <div class="card-body">
            <div id="shared-accounts-preview" class="accounts-grid">
                <!-- Wird dynamisch gefüllt -->
            </div>
        </div>
    </div>

    <!-- Transaktionsliste (Preview) -->
    <div class="card">
        <div class="card-header">
            <h3>Letzte Transaktionen</h3>
            <button class="btn btn-small" onclick="App.switchTab('shared', 'transactions')">
                Alle anzeigen →
            </button>
        </div>
        <div class="card-body">
            <div id="shared-transactions-preview" class="transactions-list">
                <!-- Wird dynamisch gefüllt -->
            </div>
        </div>
    </div>
</div>

<!-- Tab: Transaktionen -->
<div class="tab-content" id="shared-tab-transactions" style="display: none;">
    <div class="module-header">
        <h2>Transaktionen</h2>
        <p class="subtitle">Alle gemeinsamen Transaktionen</p>
    </div>

    <!-- Aktionen -->
    <div class="actions-bar">
        <button class="btn btn-primary" onclick="SharedModule.showAddTransaction()">
            ➕ Neue Transaktion
        </button>
    </div>

    <!-- Transaktionsliste -->
    <div class="card">
        <div class="card-header">
            <h3>Alle Transaktionen</h3>
            <button class="btn btn-small" onclick="SharedModule.loadTransactions()">
                🔄 Aktualisieren
            </button>
        </div>
        <div class="card-body">
            <div id="shared-transactions-list" class="transactions-list">
                <!-- Wird dynamisch gefüllt -->
                <div class="empty-state">
                    <p>Noch keine gemeinsamen Transaktionen vorhanden</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab: Konten verwalten -->
<div class="tab-content" id="shared-tab-accounts" style="display: none;">
    <div class="module-header">
        <h2>Konten verwalten</h2>
        <p class="subtitle">Verwalte gemeinsame Konten</p>
    </div>

    <!-- Aktionen -->
    <div class="actions-bar">
        <button class="btn btn-primary" onclick="SharedModule.showAddAccount()">
            ➕ Neues Konto
        </button>
    </div>

    <!-- Kontenliste -->
    <div class="card">
        <div class="card-header">
            <h3>Gemeinsame Konten</h3>
            <button class="btn btn-small" onclick="SharedModule.loadAccountsManagement()">
                🔄 Aktualisieren
            </button>
        </div>
        <div class="card-body">
            <div id="shared-accounts-management" class="accounts-management-list">
                <!-- Wird dynamisch gefüllt -->
                <div class="empty-state">
                    <p>Noch keine Konten vorhanden</p>
                    <button class="btn btn-primary" onclick="SharedModule.showAddAccount()">
                        Erstes Konto erstellen
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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

<!-- Tab: YouTube -->
<div class="tab-content" id="shared-tab-youtube" style="display: none;">
    <div class="module-header">
        <h2>YouTube Einnahmen & Ausgaben</h2>
        <p class="subtitle">Verwalte deine YouTube Kanal-Finanzen</p>
    </div>

    <!-- Sub-Tabs for YouTube -->
    <div class="invoice-subtabs">
        <button class="subtab-btn active" data-subtab="income" onclick="SharedModule.switchYouTubeSubtab('income')">
            💰 Einnahmen
        </button>
        <button class="subtab-btn" data-subtab="expenses" onclick="SharedModule.switchYouTubeSubtab('expenses')">
            💸 Ausgaben
        </button>
    </div>

    <!-- YouTube Income Sub-Tab -->
    <div id="youtube-income-section">
        <div class="actions-bar">
            <button class="btn btn-primary" onclick="SharedModule.showAddYouTubeIncome()">
                ➕ Monatliche Einnahmen hinzufügen
            </button>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Monatliche YouTube Einnahmen</h3>
                <button class="btn btn-small" onclick="SharedModule.loadYouTubeIncome()">
                    🔄 Aktualisieren
                </button>
            </div>
            <div class="card-body">
                <div id="youtube-income-list">
                    <div class="empty-state">
                        <p>Noch keine YouTube Einnahmen erfasst</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- YouTube Expenses Sub-Tab -->
    <div id="youtube-expenses-section" style="display: none;">
        <div class="actions-bar">
            <button class="btn btn-primary" onclick="SharedModule.showAddYouTubeExpense()">
                ➕ Neue Ausgabe
            </button>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>YouTube Ausgaben</h3>
                <button class="btn btn-small" onclick="SharedModule.loadYouTubeExpenses()">
                    🔄 Aktualisieren
                </button>
            </div>
            <div class="card-body">
                <div id="youtube-expenses-list">
                    <div class="empty-state">
                        <p>Noch keine YouTube Ausgaben erfasst</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add YouTube Income -->
<div id="youtube-income-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Monatliche YouTube Einnahmen</h3>
            <button class="modal-close" onclick="SharedModule.closeYouTubeIncomeModal()">&times;</button>
        </div>
        <form id="youtube-income-form" onsubmit="SharedModule.submitYouTubeIncome(event)">
            <input type="hidden" id="yt-income-id" name="id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="yt-income-year">Jahr</label>
                    <input type="number" id="yt-income-year" name="year" min="2000" max="2100" required>
                </div>
                
                <div class="form-group">
                    <label for="yt-income-month">Monat</label>
                    <select id="yt-income-month" name="month" required>
                        <option value="1">Januar</option>
                        <option value="2">Februar</option>
                        <option value="3">März</option>
                        <option value="4">April</option>
                        <option value="5">Mai</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Dezember</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="yt-income-total">Gesamteinnahmen (€)</label>
                <input type="number" id="yt-income-total" name="total_revenue" step="0.01" min="0" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="yt-income-donations">Spenden (€)</label>
                    <input type="number" id="yt-income-donations" name="donations" step="0.01" min="0" value="0">
                </div>
                
                <div class="form-group">
                    <label for="yt-income-members">Mitglieder (€)</label>
                    <input type="number" id="yt-income-members" name="members" step="0.01" min="0" value="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="yt-income-notes">Notizen (optional)</label>
                <textarea id="yt-income-notes" name="notes" rows="2" placeholder="Zusätzliche Informationen"></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="SharedModule.closeYouTubeIncomeModal()">
                    Abbrechen
                </button>
                <button type="submit" class="btn btn-primary">
                    Speichern
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add YouTube Expense -->
<div id="youtube-expense-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>YouTube Ausgabe</h3>
            <button class="modal-close" onclick="SharedModule.closeYouTubeExpenseModal()">&times;</button>
        </div>
        <form id="youtube-expense-form" onsubmit="SharedModule.submitYouTubeExpense(event)">
            <input type="hidden" id="yt-expense-id" name="id">
            
            <div class="form-group">
                <label for="yt-expense-amount">Betrag (€)</label>
                <input type="number" id="yt-expense-amount" name="amount" step="0.01" min="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="yt-expense-recipient">An wen / Empfänger</label>
                <input type="text" id="yt-expense-recipient" name="recipient" required placeholder="z.B. Designer, Editor">
            </div>
            
            <div class="form-group">
                <label for="yt-expense-description">Wofür / Beschreibung</label>
                <textarea id="yt-expense-description" name="description" rows="3" required placeholder="Was wurde bezahlt"></textarea>
            </div>
            
            <div class="form-group">
                <label for="yt-expense-date">Datum</label>
                <input type="date" id="yt-expense-date" name="date" required>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="SharedModule.closeYouTubeExpenseModal()">
                    Abbrechen
                </button>
                <button type="submit" class="btn btn-primary">
                    Speichern
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Neue gemeinsame Transaktion -->
<div id="shared-transaction-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Neue gemeinsame Transaktion</h3>
            <button class="modal-close" onclick="SharedModule.closeModal()">&times;</button>
        </div>
        <form id="shared-transaction-form" onsubmit="SharedModule.submitTransaction(event)">
            <div class="form-group">
                <label for="shared-tx-type">Typ</label>
                <select id="shared-tx-type" name="type" required>
                    <option value="expense">Ausgabe</option>
                    <option value="income">Einnahme</option>
                </select>
            </div>

            <div class="form-group">
                <label for="shared-tx-account">Konto</label>
                <select id="shared-tx-account" name="account_id" required>
                    <!-- Wird dynamisch gefüllt -->
                </select>
            </div>

            <div class="form-group">
                <label for="shared-tx-amount">Betrag (€)</label>
                <input type="number" 
                       id="shared-tx-amount" 
                       name="amount" 
                       step="0.01" 
                       min="0.01" 
                       required>
            </div>

            <div class="form-group">
                <label for="shared-tx-description">Beschreibung</label>
                <input type="text" 
                       id="shared-tx-description" 
                       name="description" 
                       required>
            </div>

            <div class="form-group">
                <label for="shared-tx-date">Datum</label>
                <input type="date" 
                       id="shared-tx-date" 
                       name="date" 
                       required>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="SharedModule.closeModal()">
                    Abbrechen
                </button>
                <button type="submit" class="btn btn-primary">
                    Speichern
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Neues gemeinsames Konto -->
<div id="shared-account-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="shared-account-modal-title">Neues gemeinsames Konto</h3>
            <button class="modal-close" onclick="SharedModule.closeAccountModal()">&times;</button>
        </div>
        <form id="shared-account-form" onsubmit="SharedModule.submitAccount(event)">
            <input type="hidden" id="shared-acc-id" name="id">
            <div class="form-group">
                <label for="shared-acc-name">Kontoname</label>
                <input type="text" 
                       id="shared-acc-name" 
                       name="name" 
                       placeholder="z.B. Haushaltskasse"
                       required>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="SharedModule.closeAccountModal()">
                    Abbrechen
                </button>
                <button type="submit" class="btn btn-primary">
                    Erstellen
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Shared Modul JavaScript
const SharedModule = {
    isServer: <?= json_encode(\App\Core\Config::isServer()) ?>,
    
    async init() {
        console.log('Shared Module initialisiert');
        
        // Registriere Tabs speziell für dieses Modul (stellt sicher, dass App die richtigen Tabs zeigt)
        if (typeof App !== 'undefined' && typeof App.registerTabs === 'function') {
            App.registerTabs('shared', [
                { id: 'dashboard', label: 'Dashboard', icon: '📊' },
                { id: 'transactions', label: 'Transaktionen', icon: '💳' },
                { id: 'accounts', label: 'Konten', icon: '📁' },
                { id: 'invoices', label: 'Rechnungen', icon: '📄' },
                { id: 'youtube', label: 'YouTube', icon: '📺' }
            ]);
        }

        // Prüfe Server-Verbindung bei Clients
        if (!this.isServer) {
            await this.checkServerConnection();
        }
        
        await this.loadStats();
        await this.loadAccountsForSelect();
        await this.loadAccountsPreview();
        await this.loadTransactions();
        await this.loadTransactionsPreview();
        
        // Setze heutiges Datum als Standard
        document.getElementById('shared-tx-date').valueAsDate = new Date();
    },

    async checkServerConnection() {
        const statusEl = document.getElementById('sync-status');
        if (!statusEl) return;

        try {
            const result = await API.getShared('health');
            
            if (result && result.status === 'ok') {
                statusEl.className = 'sync-status online';
                statusEl.querySelector('span:last-child').textContent = 'Verbunden mit Server';
            } else {
                throw new Error('Ungültige Response');
            }
        } catch (error) {
            statusEl.className = 'sync-status offline';
            statusEl.querySelector('span:last-child').textContent = 'Server nicht erreichbar';
            console.error('Server-Verbindung fehlgeschlagen:', error);
        }
    },

    async loadStats() {
        try {
            const stats = await API.getShared('getSharedStats');
            
            if (stats) {
                document.getElementById('shared-balance').textContent = 
                    this.formatCurrency(stats.balance || 0);
                document.getElementById('shared-income').textContent = 
                    this.formatCurrency(stats.total_income || 0);
                document.getElementById('shared-expenses').textContent = 
                    this.formatCurrency(stats.total_expenses || 0);
            }
        } catch (error) {
            console.error('Fehler beim Laden der Stats:', error);
            App.showToast('Statistiken konnten nicht geladen werden', 'error');
        }
    },

    async loadAccountsPreview() {
        const container = document.getElementById('shared-accounts-preview');
        if (!container) return;
        
        container.innerHTML = '<div class="loading">Lädt...</div>';

        try {
            let accounts = await API.getShared('getSharedAccounts');
            console.debug('SharedModule.loadAccountsPreview -> received', accounts);

            // Defensive normalization: accept { data: [...] } or object maps
            if (!Array.isArray(accounts)) {
                console.debug('SharedModule.loadAccountsPreview: normalizing accounts response', accounts);
                accounts = Array.isArray(accounts?.data) ? accounts.data : (accounts ? Object.values(accounts) : []);
            }

            if (!accounts || accounts.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>Noch keine gemeinsamen Konten vorhanden</p>
                    </div>
                `;
                return;
            }

            // Show only first 4 accounts for preview
            const previewAccounts = accounts.slice(0, 4);

            const html = previewAccounts.map(acc => `
                <div class="account-card">
                    <div class="account-name">${this.escapeHtml(acc.name)}</div>
                    <div class="account-balance">${this.formatCurrency(acc.balance || 0)}</div>
                    <div class="account-meta">
                        ${acc.transaction_count || 0} Transaktionen
                    </div>
                </div>
            `).join('');

            container.innerHTML = html;
        } catch (error) {
            console.error('Fehler beim Laden der Konten:', error);
            container.innerHTML = `
                <div class="error-state">
                    <p>Fehler beim Laden der Konten</p>
                </div>
            `;
        }
    },

    async loadAccountsForSelect() {
        const select = document.getElementById('shared-tx-account');
        if (!select) return;

        try {
            let accounts = await API.getShared('getSharedAccounts');
            console.debug('SharedModule.loadAccountsForSelect -> received', accounts);

            if (!Array.isArray(accounts)) {
                console.debug('SharedModule.loadAccountsForSelect: normalizing accounts response', accounts);
                accounts = Array.isArray(accounts?.data) ? accounts.data : (accounts ? Object.values(accounts) : []);
            }

            if (!accounts || accounts.length === 0) {
                select.innerHTML = '<option value="">Keine Konten verfügbar</option>';
                return;
            }

            // Select-Optionen
            select.innerHTML = accounts.map(acc => 
                `<option value="${acc.id}">${this.escapeHtml(acc.name)}</option>`
            ).join('');

        } catch (error) {
            console.error('Fehler beim Laden der Konten:', error);
            select.innerHTML = '<option value="">Fehler beim Laden</option>';
        }
    },

    async loadTransactionsPreview() {
        const container = document.getElementById('shared-transactions-preview');
        if (!container) return;
        
        container.innerHTML = '<div class="loading">Lädt...</div>';

        try {
            const transactions = await API.getShared('getSharedTransactions', { limit: 5 });
            
            if (!transactions || transactions.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>Noch keine gemeinsamen Transaktionen vorhanden</p>
                    </div>
                `;
                return;
            }

            const html = transactions.map(tx => `
                <div class="transaction-item ${tx.type}">
                    <div class="transaction-info">
                        <div class="transaction-description">${this.escapeHtml(tx.description)}</div>
                        <div class="transaction-meta">
                            ${this.escapeHtml(tx.account_name || 'Unbekannt')} • ${this.formatDate(tx.date)}
                        </div>
                    </div>
                    <div class="transaction-amount ${tx.type}">
                        ${tx.type === 'income' ? '+' : '-'}${this.formatCurrency(tx.amount)}
                    </div>
                </div>
            `).join('');

            container.innerHTML = html;
        } catch (error) {
            console.error('Fehler beim Laden der Transaktionen:', error);
            container.innerHTML = `
                <div class="error-state">
                    <p>Fehler beim Laden der Transaktionen</p>
                </div>
            `;
        }
    },

    async loadTransactions() {
        const container = document.getElementById('shared-transactions-list');
        container.innerHTML = '<div class="loading">Lädt...</div>';

        try {
            const transactions = await API.getShared('getSharedTransactions', { limit: 50 });
            
            if (!transactions || transactions.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>Noch keine gemeinsamen Transaktionen vorhanden</p>
                    </div>
                `;
                return;
            }

            const html = transactions.map(tx => `
                <div class="transaction-item ${tx.type}">
                    <div class="transaction-info">
                        <div class="transaction-description">${this.escapeHtml(tx.description)}</div>
                        <div class="transaction-meta">
                            ${this.escapeHtml(tx.account_name || 'Unbekannt')} • ${this.formatDate(tx.date)}
                        </div>
                    </div>
                    <div class="transaction-amount ${tx.type}">
                        ${tx.type === 'income' ? '+' : '-'}${this.formatCurrency(tx.amount)}
                    </div>
                    <div class="transaction-actions">
                        <button class="btn-icon" 
                                onclick="SharedModule.deleteTransaction(${tx.id})"
                                title="Löschen">
                            🗑️
                        </button>
                    </div>
                </div>
            `).join('');

            container.innerHTML = html;

        } catch (error) {
            console.error('Fehler beim Laden der Transaktionen:', error);
            container.innerHTML = `
                <div class="error-state">
                    <p>Fehler beim Laden der Transaktionen</p>
                    <button class="btn btn-small" onclick="SharedModule.loadTransactions()">
                        Erneut versuchen
                    </button>
                </div>
            `;
        }
    },

    showAddTransaction() {
        document.getElementById('shared-transaction-modal').style.display = 'flex';
    },

    closeModal() {
        document.getElementById('shared-transaction-modal').style.display = 'none';
        document.getElementById('shared-transaction-form').reset();
    },

    async submitTransaction(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        try {
            const result = await API.postShared('addSharedTransaction', data);
            
            if (result) {
                App.showToast('Transaktion gespeichert', 'success');
                this.closeModal();
                await this.loadStats();
                await this.loadTransactions();
                await this.loadTransactionsPreview();
            }
        } catch (error) {
            App.showToast('Fehler beim Speichern', 'error');
        }
    },

    async deleteTransaction(id) {
        if (!await App.confirm('Transaktion wirklich löschen?')) return;

        try {
            const result = await API.postShared('deleteTransaction', { id });
            
            if (result) {
                App.showToast('Transaktion gelöscht', 'success');
                await this.loadStats();
                await this.loadTransactions();
                await this.loadTransactionsPreview();
            }
        } catch (error) {
            App.showToast('Fehler beim Löschen', 'error');
        }
    },

    showAddTransactionWithTab() {
        // Wechsel zum Transaktionen-Tab und öffne dann das Modal
        // Async-safe: wechsle Tab, lade die Transaktionen und öffne dann das Modal
        (async () => {
            // ensure active module is correct
            if (App.currentModule !== 'shared') {
                await App.switchModule('shared');
            }
            App.switchTab('shared', 'transactions');
            try {
                await this.loadTransactions();
            } catch (e) {
                console.warn('loadTransactions failed before opening modal', e);
            }
            this.showAddTransaction();
        })();
    },

    async loadAccountsManagement() {
        const container = document.getElementById('shared-accounts-management');
        container.innerHTML = '<div class="loading">Lädt...</div>';

        try {
            let accounts = await API.getShared('getSharedAccounts');
            console.debug('SharedModule.loadAccountsManagement -> received', accounts);

            if (!Array.isArray(accounts)) {
                console.debug('SharedModule.loadAccountsManagement: normalizing accounts response', accounts);
                accounts = Array.isArray(accounts?.data) ? accounts.data : (accounts ? Object.values(accounts) : []);
            }

            if (!accounts || accounts.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>Noch keine Konten vorhanden</p>
                        <button class="btn btn-primary" onclick="SharedModule.showAddAccount()">
                            Erstes Konto erstellen
                        </button>
                    </div>
                `;
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
                        <button class="btn btn-small btn-secondary" data-account-id="${acc.id}" onclick="SharedModule.editAccountById(this.dataset.accountId)">
                            ✏️ Bearbeiten
                        </button>
                        <button class="btn btn-small btn-danger" onclick="SharedModule.deleteAccount(${acc.id})">
                            🗑️ Löschen
                        </button>
                    </div>
                </div>
            `).join('');

            container.innerHTML = html;
        } catch (error) {
            console.error('Fehler beim Laden der Konten:', error);
            container.innerHTML = `
                <div class="error-state">
                    <p>Fehler beim Laden der Konten</p>
                </div>
            `;
        }
    },

    showAddAccount() {
        document.getElementById('shared-account-modal-title').textContent = 'Neues gemeinsames Konto';
        document.getElementById('shared-acc-id').value = '';
        document.getElementById('shared-acc-name').value = '';
        document.getElementById('shared-account-modal').style.display = 'flex';
    },

    editAccount(id, name) {
        document.getElementById('shared-account-modal-title').textContent = 'Konto bearbeiten';
        document.getElementById('shared-acc-id').value = id;
        document.getElementById('shared-acc-name').value = name;
        document.getElementById('shared-account-modal').style.display = 'flex';
    },

    async editAccountById(id) {
        // Fetch account details from the list
        let accounts = await API.getShared('getSharedAccounts');
        if (!Array.isArray(accounts)) {
            console.debug('SharedModule.editAccountById: normalizing accounts response', accounts);
            accounts = Array.isArray(accounts?.data) ? accounts.data : (accounts ? Object.values(accounts) : []);
        }
        const account = accounts.find(acc => acc.id === Number(id));
        if (account) {
            this.editAccount(id, account.name);
        }
    },

    showAccounts() {
        this.showAddAccount();
    },

    closeAccountModal() {
        document.getElementById('shared-account-modal').style.display = 'none';
        document.getElementById('shared-account-form').reset();
    },

    async submitAccount(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        const id = data.id;

        try {
            let result;
            if (id) {
                // Update existing account
                result = await API.postShared('updateSharedAccount', data);
            } else {
                // Create new account
                result = await API.postShared('createSharedAccount', data);
            }
            
            if (result) {
                App.showToast(id ? 'Konto aktualisiert' : 'Konto erstellt', 'success');
                this.closeAccountModal();
                await this.loadAccountsForSelect();
                await this.loadAccountsManagement();
                await this.loadAccountsPreview();
            }
        } catch (error) {
            App.showToast('Fehler beim Speichern', 'error');
        }
    },

    async deleteAccount(id) {
        if (!await App.confirm('Konto wirklich löschen? Alle zugehörigen Transaktionen werden ebenfalls gelöscht.')) return;

        try {
            const result = await API.postShared('deleteSharedAccount', { id });
            
            if (result) {
                App.showToast('Konto gelöscht', 'success');
                await this.loadAccountsForSelect();
                await this.loadAccountsManagement();
                await this.loadAccountsPreview();
                await this.loadStats();
            }
        } catch (error) {
            App.showToast('Fehler beim Löschen', 'error');
        }
    },

    async syncWithServer() {
        App.showToast('Synchronisierung gestartet...', 'info');
        
        await Promise.all([
            this.loadStats(),
            this.loadAccountsForSelect(),
            this.loadAccountsPreview(),
            this.loadTransactions(),
            this.loadTransactionsPreview()
        ]);
        
        App.showToast('Synchronisierung abgeschlossen', 'success');
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

    // ========== YouTube Functions ==========

    youtubeState: {
        currentSubtab: 'income'
    },

    switchYouTubeSubtab(subtab) {
        this.youtubeState.currentSubtab = subtab;
        
        if (subtab === 'income') {
            document.getElementById('youtube-income-section').style.display = 'block';
            document.getElementById('youtube-expenses-section').style.display = 'none';
        } else {
            document.getElementById('youtube-income-section').style.display = 'none';
            document.getElementById('youtube-expenses-section').style.display = 'block';
        }
        
        // Update active button
        document.querySelectorAll('.invoice-subtabs .subtab-btn').forEach(btn => {
            if (btn.dataset.subtab === subtab) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Load respective data
        if (subtab === 'income') {
            this.loadYouTubeIncome();
        } else {
            this.loadYouTubeExpenses();
        }
    },

    async loadYouTubeIncome() {
        const container = document.getElementById('youtube-income-list');
        container.innerHTML = '<div class="loading">Lädt...</div>';

        try {
            const incomeData = await API.postShared('getYouTubeIncome', {});
            
            if (!incomeData || incomeData.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>Noch keine YouTube Einnahmen erfasst</p>
                        <button class="btn btn-primary" onclick="SharedModule.showAddYouTubeIncome()">
                            Erste Einnahme hinzufügen
                        </button>
                    </div>
                `;
                return;
            }

            const html = incomeData.map(item => {
                const monthName = ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'][item.month - 1];
                return `
                    <div class="youtube-income-item">
                        <div class="youtube-income-header">
                            <strong>${monthName} ${item.year}</strong>
                            <div class="youtube-actions">
                                <button class="btn btn-small btn-secondary" onclick="SharedModule.editYouTubeIncome(${item.id})">
                                    ✏️ Bearbeiten
                                </button>
                                <button class="btn btn-small btn-danger" onclick="SharedModule.deleteYouTubeIncome(${item.id})">
                                    🗑️ Löschen
                                </button>
                            </div>
                        </div>
                        <div class="youtube-income-details">
                            <div class="detail-item">
                                <span class="label">Gesamteinnahmen:</span>
                                <span class="value">${this.formatCurrency(item.total_revenue)}</span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Spenden:</span>
                                <span class="value">${this.formatCurrency(item.donations)}</span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Mitglieder:</span>
                                <span class="value">${this.formatCurrency(item.members)}</span>
                            </div>
                            ${item.notes ? `<div class="detail-item"><span class="label">Notizen:</span><span class="value">${this.escapeHtml(item.notes)}</span></div>` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = html;
        } catch (error) {
            console.error('Error loading YouTube income:', error);
            container.innerHTML = `
                <div class="error-state">
                    <p>Fehler beim Laden</p>
                    <button class="btn btn-small" onclick="SharedModule.loadYouTubeIncome()">
                        Erneut versuchen
                    </button>
                </div>
            `;
        }
    },

    async loadYouTubeExpenses() {
        const container = document.getElementById('youtube-expenses-list');
        container.innerHTML = '<div class="loading">Lädt...</div>';

        try {
            const expenses = await API.postShared('getYouTubeExpenses', {});
            
            if (!expenses || expenses.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>Noch keine YouTube Ausgaben erfasst</p>
                        <button class="btn btn-primary" onclick="SharedModule.showAddYouTubeExpense()">
                            Erste Ausgabe hinzufügen
                        </button>
                    </div>
                `;
                return;
            }

            const html = expenses.map(expense => `
                <div class="transaction-item expense">
                    <div class="transaction-info">
                        <div class="transaction-description">${this.escapeHtml(expense.description)}</div>
                        <div class="transaction-meta">
                            ${this.escapeHtml(expense.recipient)} • ${this.formatDate(expense.date)}
                        </div>
                    </div>
                    <div class="transaction-amount expense">
                        -${this.formatCurrency(expense.amount)}
                    </div>
                    <div class="transaction-actions">
                        <button class="btn-icon" onclick="SharedModule.deleteYouTubeExpense(${expense.id})" title="Löschen">
                            🗑️
                        </button>
                    </div>
                </div>
            `).join('');

            container.innerHTML = html;
        } catch (error) {
            console.error('Error loading YouTube expenses:', error);
            container.innerHTML = `
                <div class="error-state">
                    <p>Fehler beim Laden</p>
                    <button class="btn btn-small" onclick="SharedModule.loadYouTubeExpenses()">
                        Erneut versuchen
                    </button>
                </div>
            `;
        }
    },

    showAddYouTubeIncome() {
        const now = new Date();
        document.getElementById('yt-income-year').value = now.getFullYear();
        document.getElementById('yt-income-month').value = now.getMonth() + 1;
        document.getElementById('yt-income-id').value = '';
        document.getElementById('youtube-income-form').reset();
        document.getElementById('youtube-income-modal').style.display = 'flex';
    },

    closeYouTubeIncomeModal() {
        document.getElementById('youtube-income-modal').style.display = 'none';
    },

    async submitYouTubeIncome(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData);

        try {
            const action = data.id ? 'updateYouTubeIncome' : 'addYouTubeIncome';
            await API.postShared(action, data);
            
            App.showToast('YouTube Einnahmen gespeichert', 'success');
            this.closeYouTubeIncomeModal();
            await this.loadYouTubeIncome();
            await this.loadStats(); // Refresh dashboard stats
        } catch (error) {
            App.showToast('Fehler beim Speichern', 'error');
        }
    },

    async deleteYouTubeIncome(id) {
        if (!await App.confirm('YouTube Einnahmen wirklich löschen?')) return;

        try {
            await API.postShared('deleteYouTubeIncome', { id });
            App.showToast('YouTube Einnahmen gelöscht', 'success');
            await this.loadYouTubeIncome();
            await this.loadStats();
        } catch (error) {
            App.showToast('Fehler beim Löschen', 'error');
        }
    },

    showAddYouTubeExpense() {
        document.getElementById('yt-expense-date').valueAsDate = new Date();
        document.getElementById('yt-expense-id').value = '';
        document.getElementById('youtube-expense-form').reset();
        document.getElementById('youtube-expense-modal').style.display = 'flex';
    },

    closeYouTubeExpenseModal() {
        document.getElementById('youtube-expense-modal').style.display = 'none';
    },

    async submitYouTubeExpense(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData);

        try {
            const action = data.id ? 'updateYouTubeExpense' : 'addYouTubeExpense';
            await API.postShared(action, data);
            
            App.showToast('YouTube Ausgabe gespeichert', 'success');
            this.closeYouTubeExpenseModal();
            await this.loadYouTubeExpenses();
        } catch (error) {
            App.showToast('Fehler beim Speichern', 'error');
        }
    },

    async deleteYouTubeExpense(id) {
        if (!await App.confirm('YouTube Ausgabe wirklich löschen?')) return;

        try {
            await API.postShared('deleteYouTubeExpense', { id });
            App.showToast('YouTube Ausgabe gelöscht', 'success');
            await this.loadYouTubeExpenses();
        } catch (error) {
            App.showToast('Fehler beim Löschen', 'error');
        }
    }
};

// Auto-init wenn Modul aktiv wird
if (document.getElementById('module-shared').classList.contains('active')) {
    SharedModule.init();
}
</script>
