<div class="module-content">
    <div class="module-header">
        <h2>Private Buchhaltung</h2>
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
    </div>

    <!-- Aktionen -->
    <div class="actions-bar">
        <button class="btn btn-primary" onclick="PrivateModule.showAddTransaction()">
            ➕ Neue Transaktion
        </button>
        <button class="btn btn-secondary" onclick="PrivateModule.showAccounts()">
            📁 Konten verwalten
        </button>
    </div>

    <!-- Transaktionsliste -->
    <div class="card">
        <div class="card-header">
            <h3>Letzte Transaktionen</h3>
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

<script>
// Private Modul JavaScript
const PrivateModule = {
    async init() {
        console.log('Private Module initialisiert');
        await this.loadStats();
        await this.loadTransactions();
        await this.loadAccounts();
        
        // Setze heutiges Datum als Standard
        document.getElementById('private-tx-date').valueAsDate = new Date();
    },

    async loadStats() {
        // Lade Statistiken aus lokaler DB
        // TODO: Backend-Endpoint implementieren
        const stats = await API.get('/api/private/stats');
        
        if (stats) {
            document.getElementById('private-balance').textContent = 
                this.formatCurrency(stats.balance);
            document.getElementById('private-income').textContent = 
                this.formatCurrency(stats.income);
            document.getElementById('private-expenses').textContent = 
                this.formatCurrency(stats.expenses);
        }
    },

    async loadTransactions() {
        const container = document.getElementById('private-transactions-list');
        container.innerHTML = '<div class="loading">Lädt...</div>';

        const transactions = await API.get('/api/private/transactions');
        
        if (!transactions || transactions.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <p>Noch keine Transaktionen vorhanden</p>
                </div>
            `;
            return;
        }

        const html = transactions.map(tx => `
            <div class="transaction-item ${tx.type}">
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
                    <button class="btn-icon" onclick="PrivateModule.deleteTransaction(${tx.id})">
                        🗑️
                    </button>
                </div>
            </div>
        `).join('');

        container.innerHTML = html;
    },

    async loadAccounts() {
        const select = document.getElementById('private-tx-account');
        const accounts = await API.get('/api/private/accounts');
        
        if (accounts) {
            select.innerHTML = accounts.map(acc => 
                `<option value="${acc.id}">${this.escapeHtml(acc.name)}</option>`
            ).join('');
        }
    },

    showAddTransaction() {
        document.getElementById('private-transaction-modal').style.display = 'flex';
    },

    closeModal() {
        document.getElementById('private-transaction-modal').style.display = 'none';
        document.getElementById('private-transaction-form').reset();
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
        }
    },

    async deleteTransaction(id) {
        if (!confirm('Transaktion wirklich löschen?')) return;

        const result = await API.delete(`/api/private/transactions/${id}`);
        
        if (result) {
            App.showToast('Transaktion gelöscht', 'success');
            await this.loadStats();
            await this.loadTransactions();
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

    showAccounts() {
        App.showToast('Kontenverwaltung wird implementiert', 'info');
    }
};

// Auto-init wenn Modul aktiv wird
if (document.getElementById('module-private').classList.contains('active')) {
    PrivateModule.init();
}
</script>
