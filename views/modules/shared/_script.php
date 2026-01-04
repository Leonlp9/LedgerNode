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
                { id: 'youtube', label: 'YouTube', icon: '📺' },
                { id: 'backup', label: 'Backup', icon: '💾' }
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
        
        // Initialize backup options
        this.updateBackupOptions();
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

    async editYouTubeIncome(id) {
        try {
            const incomeData = await API.postShared('getYouTubeIncome', {});
            const income = incomeData.find(item => item.id === id);
            
            if (!income) {
                App.showToast('Einnahmen nicht gefunden', 'error');
                return;
            }
            
            // Populate form
            document.getElementById('yt-income-id').value = income.id;
            document.getElementById('yt-income-year').value = income.year;
            document.getElementById('yt-income-month').value = income.month;
            document.getElementById('yt-income-total').value = income.total_revenue;
            document.getElementById('yt-income-donations').value = income.donations || 0;
            document.getElementById('yt-income-members').value = income.members || 0;
            document.getElementById('yt-income-notes').value = income.notes || '';
            
            // Show modal
            document.getElementById('youtube-income-modal').style.display = 'flex';
        } catch (error) {
            App.showToast('Fehler beim Laden', 'error');
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
    },

    // ========== Backup Functions ==========

    updateBackupOptions() {
        const period = document.getElementById('shared-backup-period').value;
        const monthOptions = document.getElementById('shared-backup-month-options');
        const yearOptions = document.getElementById('shared-backup-year-options');
        
        const now = new Date();
        
        if (period === 'month') {
            monthOptions.style.display = 'block';
            yearOptions.style.display = 'none';
            document.getElementById('shared-backup-year').value = now.getFullYear();
            document.getElementById('shared-backup-month').value = now.getMonth() + 1;
        } else if (period === 'year') {
            monthOptions.style.display = 'none';
            yearOptions.style.display = 'block';
            document.getElementById('shared-backup-year-only').value = now.getFullYear();
        } else {
            monthOptions.style.display = 'none';
            yearOptions.style.display = 'none';
        }
    },

    async generateBackup(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        // Show loading state
        document.getElementById('shared-backup-loading').style.display = 'block';
        form.style.display = 'none';
        
        try {
            // Prepare request data
            const period = data.period;
            const params = { period };
            
            if (period === 'month') {
                params.year = data.year;
                params.month = data.month;
            } else if (period === 'year') {
                params.year = data.year_only || data.year;
            }
            
            // Call API
            const result = await API.postShared('generateBackup', params);
            
            if (result && result.download_url) {
                // Download the file
                const link = document.createElement('a');
                link.href = result.download_url;
                link.download = result.filename || 'backup.zip';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                App.showToast('✅ Backup erfolgreich erstellt', 'success');
            } else {
                throw new Error('Backup konnte nicht erstellt werden');
            }
            
        } catch (error) {
            console.error('Error generating backup:', error);
            App.showToast('Fehler beim Erstellen des Backups: ' + (error.message || 'Unbekannter Fehler'), 'error');
        } finally {
            // Hide loading state
            document.getElementById('shared-backup-loading').style.display = 'none';
            form.style.display = 'block';
        }
    }
};

// Auto-init wenn Modul aktiv wird
if (document.getElementById('module-shared').classList.contains('active')) {
    SharedModule.init();
}
</script>
