/**
 * API-Helper
 * 
 * Zentrale Stelle für alle API-Requests
 * Behandelt sowohl lokale als auch Server-API-Calls
 */

const API = {
    // Basis-Konfiguration
    baseUrl: window.location.origin,
    timeout: 30000,

    /**
     * Generischer API-Request
     */
    async request(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            timeout: this.timeout
        };

        const config = { ...defaultOptions, ...options };

        // FormData? Dann Content-Type entfernen (wird automatisch gesetzt)
        if (config.body instanceof FormData) {
            delete config.headers['Content-Type'];
        }

        try {
            App.showLoading();

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), config.timeout);

            const response = await fetch(url, {
                ...config,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            // Response parsen
            const contentType = response.headers.get('content-type');
            let data;

            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                data = await response.text();
            }

            // Fehlerbehandlung
            if (!response.ok) {
                throw new Error(data.error || data.message || `HTTP ${response.status}`);
            }

            return data;

        } catch (error) {
            if (error.name === 'AbortError') {
                App.showToast('Request-Timeout', 'error');
            } else {
                console.error('API Error:', error);
                App.showToast(error.message || 'API-Fehler', 'error');
            }
            throw error;

        } finally {
            App.hideLoading();
        }
    },

    /**
     * GET-Request
     */
    async get(endpoint, params = {}) {
        const url = new URL(this.baseUrl + endpoint);
        Object.keys(params).forEach(key => 
            url.searchParams.append(key, params[key])
        );

        return this.request(url.toString(), {
            method: 'GET'
        });
    },

    /**
     * POST-Request
     */
    async post(endpoint, data = {}) {
        return this.request(this.baseUrl + endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    /**
     * POST-Request mit FormData
     */
    async postForm(endpoint, formData) {
        return this.request(this.baseUrl + endpoint, {
            method: 'POST',
            body: formData
        });
    },

    /**
     * PUT-Request
     */
    async put(endpoint, data = {}) {
        return this.request(this.baseUrl + endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    /**
     * DELETE-Request
     */
    async delete(endpoint) {
        return this.request(this.baseUrl + endpoint, {
            method: 'DELETE'
        });
    },

    // ==========================================
    // SHARED-API (Server-API)
    // ==========================================

    /**
     * Request an Server-API (für Clients)
     * oder direkt an lokale API (für Server)
     */
    async requestShared(action, data = {}, method = 'POST') {
        const endpoint = '/api/endpoint.php';
        
        if (method === 'GET') {
            return this.get(endpoint, { action, ...data });
        } else {
            return this.post(endpoint, { action, ...data });
        }
    },

    /**
     * GET-Request an Shared-API
     */
    async getShared(action, params = {}) {
        return this.requestShared(action, params, 'GET');
    },

    /**
     * POST-Request an Shared-API
     */
    async postShared(action, data = {}) {
        return this.requestShared(action, data, 'POST');
    },

    // ==========================================
    // CONVENIENCE-METHODEN
    // ==========================================

    /**
     * Datei hochladen
     */
    async uploadFile(endpoint, file, additionalData = {}) {
        const formData = new FormData();
        formData.append('file', file);
        
        Object.keys(additionalData).forEach(key => {
            formData.append(key, additionalData[key]);
        });

        return this.postForm(endpoint, formData);
    },

    /**
     * Batch-Request (mehrere Requests parallel)
     */
    async batch(requests) {
        try {
            const results = await Promise.all(requests);
            return results;
        } catch (error) {
            console.error('Batch-Request fehlgeschlagen:', error);
            throw error;
        }
    },

    /**
     * Retry-Logic für fehlgeschlagene Requests
     */
    async retry(fn, retries = 3, delay = 1000) {
        for (let i = 0; i < retries; i++) {
            try {
                return await fn();
            } catch (error) {
                if (i === retries - 1) throw error;
                await new Promise(resolve => setTimeout(resolve, delay));
            }
        }
    }
};

// Globale Fehlerbehandlung für unbehandelte API-Errors
window.addEventListener('unhandledrejection', (event) => {
    if (event.reason && event.reason.message) {
        console.error('Unhandled API Error:', event.reason);
    }
});
