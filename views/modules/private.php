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
    // Chart.js-Instanzen
    charts: {
        balance: null,
        expensesCategories: null
    },

    async init() {
        console.log('Private Module initialisiert');
        await this.loadStats();
        await this.loadTransactions();
        await this.loadAccounts();
        this.initCharts();
        await this.updateCharts();

        // Setze heutiges Datum als Standard
        document.getElementById('private-tx-date').valueAsDate = new Date();
    },

    initCharts() {
        // Defensive: Chart.js prüfen
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js ist nicht geladen — Charts werden nicht angezeigt');
            return;
        }

        // Balance Line Chart
        const ctxBalance = document.getElementById('chart-balance').getContext('2d');
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

        // Expenses by Category Doughnut
        const ctxExp = document.getElementById('chart-expenses-categories').getContext('2d');
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

        let transactions = await API.get('/api/private/transactions');
        console.debug('PrivateModule.loadTransactions -> received:', transactions);

        // Defensive: falls API etwas anderes als ein Array liefert, konvertieren und warnen
        if (!Array.isArray(transactions)) {
            console.warn('PrivateModule.loadTransactions: transactions is not an array, normalizing to []');
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
            await this.updateCharts();
        }
    },

    async deleteTransaction(id) {
        if (!confirm('Transaktion wirklich löschen?')) return;

        const result = await API.delete(`/api/private/transactions/${id}`);
        
        if (result) {
            App.showToast('Transaktion gelöscht', 'success');
            await this.loadStats();
            await this.loadTransactions();
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

    showAccounts() {
        App.showToast('Kontenverwaltung wird implementiert', 'info');
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
