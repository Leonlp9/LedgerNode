<div class="module-content">
    <div class="module-header">
        <h2>Gemeinsame Buchhaltung</h2>
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
        <button class="btn btn-primary" onclick="SharedModule.showAddTransaction()">
            ➕ Neue Transaktion
        </button>
        <button class="btn btn-secondary" onclick="SharedModule.showAccounts()">
            📁 Konten verwalten
        </button>
        <?php if (\App\Core\Config::isClient()): ?>
            <button class="btn btn-info" onclick="SharedModule.syncWithServer()">
                🔄 Synchronisieren
            </button>
        <?php endif; ?>
    </div>

    <!-- Gemeinsame Konten -->
    <div class="card">
        <div class="card-header">
            <h3>Gemeinsame Konten</h3>
        </div>
        <div class="card-body">
            <div id="shared-accounts-list" class="accounts-grid">
                <!-- Wird dynamisch gefüllt -->
            </div>
        </div>
    </div>

    <!-- Transaktionsliste -->
    <div class="card">
        <div class="card-header">
            <h3>Letzte Transaktionen</h3>
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
            <h3>Neues gemeinsames Konto</h3>
            <button class="modal-close" onclick="SharedModule.closeAccountModal()">&times;</button>
        </div>
        <form id="shared-account-form" onsubmit="SharedModule.submitAccount(event)">
            <div class="form-group">
                <label for="shared-acc-name">Kontoname</label>
                <input type="text" 
                       id="shared-acc-name" 
                       name="name" 
                       placeholder="z.B. Haushaltskasse"
                       required>
            </div>

            <div class="form-group">
                <label for="shared-acc-type">Kontotyp</label>
                <select id="shared-acc-type" name="type" required>
                    <option value="general">Allgemein</option>
                    <option value="savings">Sparkonto</option>
                    <option value="project">Projekt</option>
                </select>
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
        
        // Prüfe Server-Verbindung bei Clients
        if (!this.isServer) {
            await this.checkServerConnection();
        }
        
        await this.loadStats();
        await this.loadAccounts();
        await this.loadTransactions();
        
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

    async loadAccounts() {
        const container = document.getElementById('shared-accounts-list');
        const select = document.getElementById('shared-tx-account');
        
        container.innerHTML = '<div class="loading">Lädt...</div>';

        try {
            const accounts = await API.getShared('getSharedAccounts');
            
            if (!accounts || accounts.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>Noch keine gemeinsamen Konten vorhanden</p>
                        <button class="btn btn-primary" onclick="SharedModule.showAccounts()">
                            Erstes Konto erstellen
                        </button>
                    </div>
                `;
                return;
            }

            // Accounts Grid
            const html = accounts.map(acc => `
                <div class="account-card">
                    <div class="account-name">${this.escapeHtml(acc.name)}</div>
                    <div class="account-balance">${this.formatCurrency(acc.balance || 0)}</div>
                    <div class="account-meta">
                        ${acc.transaction_count || 0} Transaktionen
                    </div>
                </div>
            `).join('');

            container.innerHTML = html;

            // Select-Optionen
            select.innerHTML = accounts.map(acc => 
                `<option value="${acc.id}">${this.escapeHtml(acc.name)}</option>`
            ).join('');

        } catch (error) {
            console.error('Fehler beim Laden der Konten:', error);
            container.innerHTML = `
                <div class="error-state">
                    <p>Fehler beim Laden der Konten</p>
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
            }
        } catch (error) {
            App.showToast('Fehler beim Löschen', 'error');
        }
    },

    showAccounts() {
        document.getElementById('shared-account-modal').style.display = 'flex';
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

        try {
            const result = await API.postShared('createSharedAccount', data);
            
            if (result) {
                App.showToast('Konto erstellt', 'success');
                this.closeAccountModal();
                await this.loadAccounts();
            }
        } catch (error) {
            App.showToast('Fehler beim Erstellen', 'error');
        }
    },

    async syncWithServer() {
        App.showToast('Synchronisierung gestartet...', 'info');
        
        await Promise.all([
            this.loadStats(),
            this.loadAccounts(),
            this.loadTransactions()
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
    }
};

// Auto-init wenn Modul aktiv wird
if (document.getElementById('module-shared').classList.contains('active')) {
    SharedModule.init();
}
</script>
