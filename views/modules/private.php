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
            <p>Wähle eine Überweisung mit dem gleichen Betrag:</p>
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

    // Invoice pagination state
    invoiceState: {
        currentPage: 1,
        perPage: 15,
        totalPages: 1,
        currentSubtab: 'all'
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
        document.getElementById('private-tx-date').valueAsDate = new Date();
        document.getElementById('private-inv-date').valueAsDate = new Date();
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

        const html = previewTransactions.map(tx => `
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
            </div>
        `).join('');

        container.innerHTML = html;
    },

    async loadTransactions() {
        const container = document.getElementById('private-transactions-list');
        container.innerHTML = '<div class="loading">Lädt...</div>';

        let transactions = await API.get('/api/private.php?action=transactions');
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
        let accounts = await API.get('/api/private.php?action=accounts');

        // Defensive normalization: handle cases where API returns an object/map instead of an array
        if (!Array.isArray(accounts)) {
            console.debug('PrivateModule.loadAccounts: normalizing accounts response', accounts);
            accounts = Array.isArray(accounts?.data) ? accounts.data : (accounts ? Object.values(accounts) : []);
        }

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

        let accounts = await API.get('/api/private/accounts');
        console.debug('PrivateModule.loadAccountsManagement -> received', accounts);

        // Defensive normalization: accept { data: [...] } or object maps
        if (!Array.isArray(accounts)) {
            console.debug('PrivateModule.loadAccountsManagement: normalizing accounts response', accounts);
            accounts = Array.isArray(accounts?.data) ? accounts.data : (accounts ? Object.values(accounts) : []);
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

            const html = result.invoices.map(inv => `
                <div class="invoice-item ${inv.is_linked ? 'linked' : 'unlinked'}">
                    <div class="invoice-info">
                        <div class="invoice-header">
                            <span class="invoice-number">${this.escapeHtml(inv.invoice_number || 'Ohne Nr.')}</span>
                            <span class="invoice-type-badge ${inv.type}">${inv.type === 'received' ? '📥 Erhalten' : '📤 Geschrieben'}</span>
                            <span class="invoice-status-badge ${inv.status}">${this.getStatusLabel(inv.status)}</span>
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
                            <button class="btn btn-small btn-info" onclick="PrivateModule.showLinkModal(${inv.id})" title="Mit Überweisung verknüpfen">
                                🔗 Verknüpfen
                            </button>
                        ` : `
                            <span class="linked-indicator" title="Mit Transaktion verknüpft">✓ Verknüpft</span>
                        `}
                        ${inv.file_path ? `
                            <a href="${inv.file_path}" target="_blank" class="btn btn-small btn-secondary" title="Datei anzeigen">
                                📄 Datei
                            </a>
                        ` : ''}
                        <button class="btn-icon" onclick="PrivateModule.deleteInvoice(${inv.id})" title="Löschen">
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
            
            const result = await fetch('/api/private.php?action=' + action, {
                method: 'POST',
                body: formData
            }).then(res => res.json());

            if (result.success) {
                App.showToast(id ? 'Rechnung aktualisiert' : 'Rechnung erstellt', 'success');
                this.closeInvoiceModal();
                await this.loadInvoices();
                await this.loadStats();
            } else {
                throw new Error(result.error || 'Fehler beim Speichern');
            }
        } catch (error) {
            console.error('Fehler:', error);
            App.showToast('Fehler beim Speichern: ' + error.message, 'error');
        }
    },

    async deleteInvoice(id) {
        if (!await App.confirm('Rechnung wirklich löschen?')) return;

        try {
            const result = await fetch('/api/private.php?action=deleteInvoice', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            }).then(res => res.json());

            if (result.success) {
                App.showToast('Rechnung gelöscht', 'success');
                await this.loadInvoices();
                await this.loadStats();
            } else {
                throw new Error(result.error || 'Fehler beim Löschen');
            }
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
            
            if (!transactions || transactions.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>Keine passenden Transaktionen gefunden</p>
                        <small>Es werden nur Transaktionen mit dem gleichen Betrag angezeigt, die noch nicht verknüpft sind.</small>
                    </div>
                `;
                return;
            }

            const html = transactions.map(tx => `
                <div class="transaction-item ${tx.type}" onclick="PrivateModule.linkInvoiceToTransaction(${invoiceId}, ${tx.id})">
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

            container.innerHTML = html;
        } catch (error) {
            console.error('Fehler:', error);
            container.innerHTML = '<div class="error-state"><p>Fehler beim Laden</p></div>';
        }
    },

    closeLinkModal() {
        document.getElementById('private-invoice-link-modal').style.display = 'none';
    },

    async linkInvoiceToTransaction(invoiceId, transactionId) {
        try {
            const result = await fetch('/api/private.php?action=linkInvoiceToTransaction', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `invoice_id=${invoiceId}&transaction_id=${transactionId}`
            }).then(res => res.json());

            if (result.success) {
                App.showToast('Rechnung verknüpft', 'success');
                this.closeLinkModal();
                await this.loadInvoices();
                await this.loadStats();
            } else {
                throw new Error(result.error || 'Fehler beim Verknüpfen');
            }
        } catch (error) {
            console.error('Fehler:', error);
            App.showToast('Fehler beim Verknüpfen: ' + error.message, 'error');
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
