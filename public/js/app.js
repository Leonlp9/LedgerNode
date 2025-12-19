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
    isTransitioning: false,

    /**
     * Initialisierung
     */
    init() {
        console.log('App initialisiert');
        
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
        await this.initModule(moduleName);

        // URL-Hash aktualisieren
        if (updateHash) {
            window.location.hash = moduleName;
        }

        this.isTransitioning = false;
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

// App initialisieren wenn DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => App.init());
} else {
    App.init();
}
