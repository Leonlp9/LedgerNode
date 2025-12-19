/**
 * Haupt-App-JavaScript
 * 
 * Verwaltet:
 * - SPA-Navigation zwischen Modulen
 * - Toast-Notifications
 * - Loading-States
 * - Globale Funktionen
 */

const App = {
    currentModule: 'private',
    currentTab: {
        private: 'dashboard',
        shared: 'dashboard'
    },
    isTransitioning: false,

    // Tab definitions for each module
    tabs: {
        private: [
            { id: 'dashboard', label: 'Dashboard', icon: '📊' },
            { id: 'transactions', label: 'Transaktionen', icon: '💳' },
            { id: 'accounts', label: 'Konten', icon: '📁' }
        ],
        shared: [
            { id: 'dashboard', label: 'Dashboard', icon: '📊' },
            { id: 'transactions', label: 'Transaktionen', icon: '💳' },
            { id: 'accounts', label: 'Konten', icon: '📁' }
        ]
    },

    /**
     * Initialisierung
     */
    init() {
        console.log('App initialisiert');
        
        // Initialize tabs for current module
        this.updateTabs();

        // Modul aus URL-Hash laden
        const hash = window.location.hash.substring(1);
        if (hash && ['private', 'shared'].includes(hash)) {
            this.switchModule(hash, false);
        }

        // Hash-Change-Listener
        window.addEventListener('hashchange', () => {
            const newHash = window.location.hash.substring(1);
            if (newHash && newHash !== this.currentModule) {
                this.switchModule(newHash, false);
            }
        });

        // Globale Error-Handler
        window.addEventListener('error', (event) => {
            console.error('Global Error:', event.error);
            this.showToast('Ein Fehler ist aufgetreten', 'error');
        });

        // Unhandled Promise Rejections
        window.addEventListener('unhandledrejection', (event) => {
            console.error('Unhandled Promise Rejection:', event.reason);
            this.showToast('Ein Fehler ist aufgetreten', 'error');
        });

        // Modal sicher ausblenden beim Start
        const modal = document.getElementById('update-modal');
        if (modal) modal.style.display = 'none';

        // Init Updater: entweder auf dem Server selbst oder auf Clients mit konfigurierter SERVER_API_URL
        const isServer = (typeof window.IS_SERVER !== 'undefined' && window.IS_SERVER === true);
        const serverUrl = (typeof window.SERVER_API_URL !== 'undefined' && window.SERVER_API_URL) ? window.SERVER_API_URL : null;
        if (isServer || (!isServer && serverUrl)) {
            Updater.init();
        }
    },

    /**
     * Zwischen Modulen wechseln (SPA-Navigation)
     * 
     * @param {string} moduleName - Name des Moduls (private|shared)
     * @param {boolean} updateHash - URL-Hash aktualisieren?
     */
    async switchModule(moduleName, updateHash = true) {
        if (this.isTransitioning) return;
        if (this.currentModule === moduleName) return;

        const validModules = ['private', 'shared'];
        if (!validModules.includes(moduleName)) {
            console.error('Ungültiges Modul:', moduleName);
            return;
        }

        this.isTransitioning = true;

        // Navigation aktualisieren
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.module === moduleName) {
                item.classList.add('active');
            }
        });

        // Module wechseln mit Animation
        const currentModuleEl = document.getElementById(`module-${this.currentModule}`);
        const newModuleEl = document.getElementById(`module-${moduleName}`);

        if (!newModuleEl) {
            console.error('Modul-Element nicht gefunden:', moduleName);
            this.isTransitioning = false;
            return;
        }

        // Fade-Out current module
        currentModuleEl.classList.add('fade-out');

        await this.wait(300); // Warte auf Animation

        // Wechsel
        currentModuleEl.classList.remove('active', 'fade-out');
        newModuleEl.classList.add('active', 'fade-in');

        await this.wait(50);

        // Fade-In new module
        newModuleEl.classList.remove('fade-in');

        // Modul initialisieren
        this.currentModule = moduleName;
        
        // Update module switcher buttons
        document.querySelectorAll('.module-switch-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.module === moduleName) {
                btn.classList.add('active');
            }
        });
        
        // Update tabs for the new module
        this.updateTabs();
        
        // Switch to the appropriate tab for this module
        this.switchTab(moduleName, this.currentTab[moduleName], false);
        
        await this.initModule(moduleName);

        // URL-Hash aktualisieren
        if (updateHash) {
            window.location.hash = moduleName;
        }

        this.isTransitioning = false;
    },

    /**
     * Update tab navigation for current module
     */
    updateTabs() {
        const tabNav = document.getElementById('tab-nav');
        if (!tabNav) return;

        const moduleTabs = this.tabs[this.currentModule] || [];
        
        tabNav.innerHTML = moduleTabs.map(tab => `
            <a href="#" 
               class="nav-item ${tab.id === this.currentTab[this.currentModule] ? 'active' : ''}" 
               data-tab="${tab.id}"
               onclick="App.switchTab('${this.currentModule}', '${tab.id}'); return false;">
                <span class="nav-icon">${tab.icon}</span>
                <span class="nav-label">${tab.label}</span>
            </a>
        `).join('');
    },

    /**
     * Switch to a specific tab within the current module
     * 
     * @param {string} moduleName - Name of the module (private|shared)
     * @param {string} tabId - ID of the tab to switch to
     * @param {boolean} animate - Whether to animate the transition
     */
    switchTab(moduleName, tabId, animate = true) {
        if (moduleName !== this.currentModule) {
            console.warn('Cannot switch tab for inactive module:', moduleName);
            return;
        }

        // Hide all tabs in the current module
        const moduleContainer = document.getElementById(`module-${moduleName}`);
        if (!moduleContainer) return;

        const allTabs = moduleContainer.querySelectorAll('.tab-content');
        allTabs.forEach(tab => {
            tab.style.display = 'none';
        });

        // Show the selected tab
        const selectedTab = document.getElementById(`${moduleName}-tab-${tabId}`);
        if (selectedTab) {
            selectedTab.style.display = 'block';
            this.currentTab[moduleName] = tabId;
            
            // Update active state in navigation
            this.updateTabs();
        }
    },

    /**
     * Modul initialisieren
     */
    async initModule(moduleName) {
        console.log('Initialisiere Modul:', moduleName);

        try {
            if (moduleName === 'private' && typeof PrivateModule !== 'undefined') {
                await PrivateModule.init();
            } else if (moduleName === 'shared' && typeof SharedModule !== 'undefined') {
                await SharedModule.init();
            }
        } catch (error) {
            console.error('Fehler beim Initialisieren des Moduls:', error);
            this.showToast('Modul konnte nicht geladen werden', 'error');
        }
    },

    /**
     * Toast-Notification anzeigen
     * 
     * @param {string} message - Nachricht
     * @param {string} type - Typ (success|error|warning|info)
     * @param {number} duration - Anzeigedauer in ms
     */
    showToast(message, type = 'info', duration = 3000) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast toast-${type} toast-enter`;

        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || icons.info}</span>
            <span class="toast-message">${this.escapeHtml(message)}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;

        container.appendChild(toast);

        // Animation
        setTimeout(() => toast.classList.remove('toast-enter'), 10);

        // Auto-Remove
        setTimeout(() => {
            toast.classList.add('toast-exit');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    /**
     * Loading-Overlay anzeigen/verstecken
     */
    showLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) overlay.style.display = 'flex';
    },

    hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) overlay.style.display = 'none';
    },

    /**
     * Hilfsfunktion: Warten
     */
    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    },

    /**
     * HTML escapen (XSS-Schutz)
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    /**
     * Confirm-Dialog
     */
    async confirm(message) {
        if (window.CustomDialog && typeof window.CustomDialog.confirm === 'function') {
            return await window.CustomDialog.confirm(message);
        }
        // Fallback to native confirm if custom dialog not available
        return window.confirm(message);
    },

    /**
     * Datum formatieren
     */
    formatDate(date, locale = 'de-DE') {
        if (typeof date === 'string') {
            date = new Date(date);
        }
        return date.toLocaleDateString(locale);
    },

    /**
     * Währung formatieren
     */
    formatCurrency(amount, currency = 'EUR', locale = 'de-DE') {
        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency
        }).format(amount);
    },

    /**
     * Formular-Daten als Objekt extrahieren
     */
    getFormData(form) {
        const formData = new FormData(form);
        return Object.fromEntries(formData);
    },

    /**
     * Element smooth scrollen
     */
    scrollToElement(element, offset = 0) {
        const top = element.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top, behavior: 'smooth' });
    }
};

// --- Updater Subsystem -------------------------------------------------
const Updater = {
    intervalMs: 1000 * 60 * 5, // 5 Minuten
    timerId: null,
    lastResult: null,

    init() {
        // Buttons
        const checkBtn = document.getElementById('update-check-btn');
        const installBtn = document.getElementById('update-install-btn');
        const closeBtn = document.getElementById('update-close-btn');

        if (checkBtn) checkBtn.addEventListener('click', () => this.checkUpdates(true));
        if (installBtn) installBtn.addEventListener('click', () => this.installUpdates());
        if (closeBtn) closeBtn.addEventListener('click', () => this.hideModal());

        // Starte periodische Prüfung (stumm, Modal nur bei Updates)
        // Start-up: prüfe im Hintergrund (Modal nur anzeigen, wenn Updates gefunden werden)
        this.checkUpdates(false).catch(() => {});
        this.timerId = setInterval(() => this.checkUpdates(false).catch(() => {}), this.intervalMs);
    },

    async checkUpdates(showModal = true) {
        try {
            document.getElementById('update-status').textContent = 'Prüfe auf Updates...';
            document.getElementById('update-commits').innerHTML = '';

            let res;
            const isServer = (typeof window.IS_SERVER !== 'undefined' && window.IS_SERVER === true);
            if (isServer) {
                // Server kann seine eigene Shared-API nutzen
                res = await API.postShared('checkUpdates', {});
            } else {
                // Client: prüft lokal via private endpoint
                res = await API.post('/api/private/check_updates', {});
            }
            // API.postShared routet über /api/endpoint.php?action=checkUpdates

            this.lastResult = res;

            if (res.updates) {
                // Nur wenn Updates vorhanden sind, zeige Modal (oder bei manueller Abfrage ebenfalls)
                document.getElementById('update-status').textContent = 'Neue Commits vorhanden:';
                const ul = document.getElementById('update-commits');
                res.commits.forEach(c => {
                    const li = document.createElement('li');
                    li.textContent = c;
                    ul.appendChild(li);
                });
                // Immer Modal zeigen, wenn Updates vorhanden.
                // Wenn die Prüfung automatisch lief (showModal === false), dann ist dies ein "autoDetected"-Fall
                // showModal expects autoDetected boolean
                this.showModal(!showModal);
                App.showToast('Update verfügbar', 'info');
            } else {
                // Kein Update
                document.getElementById('update-status').textContent = 'Repository ist auf dem neuesten Stand.';
                // Modal niemals anzeigen, wenn keine Updates vorhanden sind. Falls es offen ist, schließen.
                this.hideModal();
            }

            return res;
        } catch (err) {
            console.error('Fehler beim Prüfen auf Updates', err);
            document.getElementById('update-status').textContent = 'Fehler beim Prüfen auf Updates.';
            // Bei manueller Prüfung den Fehler im Modal zeigen, bei automatischer Prüfung nur Toast
            if (showModal === true) this.showModal();
            App.showToast('Fehler beim Prüfen auf Updates', 'error');
            throw err;
        }
    },

    async installUpdates() {
        try {
            // Use CustomDialog (async) if available
            let ok = true;
            if (window.CustomDialog && typeof window.CustomDialog.confirm === 'function') {
                ok = await window.CustomDialog.confirm('Möchten Sie die Updates jetzt installieren? Dies kann die Anwendung verändern.');
            } else {
                ok = confirm('Möchten Sie die Updates jetzt installieren? Dies kann die Anwendung verändern.');
            }
            if (!ok) return;

            document.getElementById('update-status').textContent = 'Installiere Updates...';
            const isServer = (typeof window.IS_SERVER !== 'undefined' && window.IS_SERVER === true);
            let res;
            if (isServer) {
                // server-side installation via shared API
                res = await API.postShared('installUpdates', {});
            } else {
                // client-side: execute local install endpoint (only after user confirmation)
                res = await API.post('/api/private/install_updates', {});
            }

            document.getElementById('update-status').textContent = 'Update erfolgreich installiert.';
            App.showToast('Update installiert', 'success');
            // Kurzes Delay, dann Seite neu laden, damit die neue Version sichtbar ist und das Modal geschlossen wird
            setTimeout(() => {
                try { Updater.hideModal(); } catch(e) {}
                // reload the page so the new files take effect
                window.location.reload();
            }, 800);
             return res;
        } catch (err) {
            console.error('Fehler beim Installieren der Updates', err);
            document.getElementById('update-status').textContent = 'Fehler beim Installieren der Updates.';
            App.showToast('Fehler beim Installieren des Updates', 'error');
            throw err;
        }
    },

    showModal(autoDetected = false) {
        const modal = document.getElementById('update-modal');
        if (!modal) return;

        // Sichtbarkeit des 'Auf Updates prüfen'-Buttons steuern
        const checkBtn = document.getElementById('update-check-btn');
        if (checkBtn) {
            if (autoDetected) {
                checkBtn.style.display = 'none';
            } else {
                checkBtn.style.display = '';
            }
        }

        modal.style.display = 'block';
    },

    hideModal() {
        const modal = document.getElementById('update-modal');
        if (!modal) return;

        // Stelle sicher, dass der Check-Button wieder sichtbar ist, wenn Modal geschlossen
        const checkBtn = document.getElementById('update-check-btn');
        if (checkBtn) checkBtn.style.display = '';

        modal.style.display = 'none';
    }
};

// App initialisieren wenn DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => App.init());
} else {
    App.init();
}