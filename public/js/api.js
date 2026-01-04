/**
 * API-Helper
 * 
 * Zentrale Stelle für alle API-Requests
 * Behandelt sowohl lokale als auch Server-API-Calls
 */

const API = {
    // Basis-Konfiguration - nutze window.APP_BASE wenn gesetzt
    baseUrl: (typeof window.APP_BASE !== 'undefined' && window.APP_BASE) ? (window.location.origin + window.APP_BASE) : window.location.origin,
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

        // Wenn der globale CLIENT_API_KEY gesetzt ist und die Ziel-URL auf das API-Endpoint zeigt,
        // füge automatisch den X-API-Key Header hinzu. Dadurch funktionieren auch direkte
        // GET-Aufrufe an /api/endpoint.php?action=... aus dem Browser.
        try {
            const urlStr = (typeof url === 'string') ? url : (url instanceof URL ? url.toString() : '');
            if (typeof window !== 'undefined' && window.CLIENT_API_KEY) {
                if (urlStr.includes('/api/endpoint.php') || (typeof window.SERVER_API_URL !== 'undefined' && urlStr.startsWith(window.SERVER_API_URL))) {
                    config.headers = config.headers || {};
                    // Do not overwrite if caller set its own X-API-Key
                    if (!config.headers['X-API-Key'] && !config.headers['x-api-key']) {
                        config.headers['X-API-Key'] = window.CLIENT_API_KEY;
                    }
                }
            }
        } catch (e) {
            // ignore errors here — header addition is best-effort for local dev
        }

        // FormData? Dann Content-Type entfernen (wird automatisch gesetzt)
        if (config.body instanceof FormData) {
            delete config.headers['Content-Type'];
        }

        try {
            // Normalize: if caller passed a URL string that points directly to /api/... (and not to endpoint.php),
            // transparently route it through api/endpoint.php?path=... so it works without mod_rewrite.
            if (typeof url === 'string') {
                const asStr = url;
                if (asStr.includes('/api/') && !asStr.includes('/api/endpoint.php')) {
                    const idx = asStr.indexOf('/api/');
                    const path = asStr.substring(idx);
                    const newUrl = this.baseUrl + '/api/endpoint.php?path=' + encodeURIComponent(path);
                    console.debug('[API] Rewriting API URL ->', newUrl);
                    url = newUrl;
                }
            }

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

            // Wenn API-Wrapper { success: bool, data: ... } genutzt wird, entpacke automatisch
            if (typeof data === 'object' && data !== null && Object.prototype.hasOwnProperty.call(data, 'success')) {
                if (data.success === false) {
                    // Server-seitiger Fehler (nicht erfolgreich)
                    // Erzeuge ein Error-Objekt und hänge ggf. zusätzliche Details an
                    const msg = data.error || data.message || `API Error`;
                    const err = new Error(msg);
                    if (data.details) err.details = data.details;
                    // Manche Endpoints packen die nützlichen Infos unter data.details
                    if (data.data && data.data.details) err.details = data.data.details;
                    // Attach validation errors (common field: errors)
                    if (data.errors) {
                        err.validation = data.errors;
                    }
                    throw err;
                }

                // Erfolg: gib nur das payload zurück
                return data.data;
            }

            // HTTP-Fehler (falls Status >=400)
            if (!response.ok) {
                const msg = (data && (data.error || data.message)) ? (data.error || data.message) : `HTTP ${response.status}`;
                const err = new Error(msg);
                if (data && data.details) err.details = data.details;
                throw err;
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
     * Hilfsfunktion: Baue die tatsächliche URL für ein Endpoint
     * Wenn das Endpoint mit /api/ beginnt, routen wir es über api/endpoint.php?path=...
     */
    _buildEndpointUrl(endpoint) {
        if (typeof endpoint !== 'string') return this.baseUrl;

        if (endpoint.startsWith('/api/')) {
            // Nutze endpoint.php mit path-Parameter -> funktioniert auch wenn Rewrite nicht aktiv ist
            return this.baseUrl + '/api/endpoint.php';
        }

        return this.baseUrl + endpoint;
    },

    /**
     * GET-Request
     */
    async get(endpoint, params = {}) {
        if (endpoint.startsWith('/api/')) {
            const url = new URL(this.baseUrl + '/api/endpoint.php');
            url.searchParams.append('path', endpoint);
            Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
            console.debug('[API] GET ->', url.toString());
            return this.request(url.toString(), { method: 'GET' });
        }

        const url = new URL(this.baseUrl + endpoint);
        Object.keys(params).forEach(key =>
            url.searchParams.append(key, params[key])
        );

        console.debug('[API] GET ->', url.toString());
        return this.request(url.toString(), {
            method: 'GET'
        });
    },

    /**
     * POST-Request
     */
    async post(endpoint, data = {}) {
        if (endpoint.startsWith('/api/')) {
            const url = this.baseUrl + '/api/endpoint.php?path=' + encodeURIComponent(endpoint);
            return this.request(url, {
                method: 'POST',
                body: JSON.stringify(data)
            });
        }

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
        if (endpoint.startsWith('/api/')) {
            const url = this.baseUrl + '/api/endpoint.php?path=' + encodeURIComponent(endpoint);
            return this.request(url, {
                method: 'PUT',
                body: JSON.stringify(data)
            });
        }

        return this.request(this.baseUrl + endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    /**
     * DELETE-Request
     */
    async delete(endpoint) {
        if (endpoint.startsWith('/api/')) {
            const url = this.baseUrl + '/api/endpoint.php?path=' + encodeURIComponent(endpoint);
            return this.request(url, { method: 'DELETE' });
        }

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
        // Wenn dies ein Client ist und eine zentrale Server-URL konfiguriert ist,
        // dann rufe die zentrale Server-API auf (z.B. http(s)://server.example.com/api/endpoint.php)
        const isServer = (typeof window.IS_SERVER !== 'undefined' && window.IS_SERVER === true);
        const serverUrl = (typeof window.SERVER_API_URL !== 'undefined' && window.SERVER_API_URL) ? window.SERVER_API_URL : null;

        if (!isServer && serverUrl) {
            // Baue die Ziel-URL (Server). Akzeptiere sowohl API_URL mit als auch ohne trailing /api/endpoint.php
            let url = serverUrl;
            if (!/\/api\/endpoint\.php$/i.test(url)) {
                url = url.replace(/\/$/, '') + '/api/endpoint.php';
            }

            if (method === 'GET') {
                // GET: action + params als query
                const u = new URL(url);
                u.searchParams.append('action', action);
                Object.keys(data || {}).forEach(k => u.searchParams.append(k, data[k]));
                try {
                    return await this.request(u.toString(), { method: 'GET' });
                } catch (err) {
                    console.warn('[API] requestShared direct failed, trying local proxy fallback', err);
                    // Fallback: send through local proxy endpoint
                    const proxyBody = { __proxy: true, target_url: u.toString(), method: 'GET' };
                    if (typeof window.CLIENT_API_KEY !== 'undefined' && window.CLIENT_API_KEY) proxyBody.x_api_key = window.CLIENT_API_KEY;
                    return this.request(this.baseUrl + '/api/endpoint.php', { method: 'POST', body: JSON.stringify(proxyBody) });
                }
            }
            // POST: sende JSON body { action, ...data }
            const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
            if (typeof window.CLIENT_API_KEY !== 'undefined' && window.CLIENT_API_KEY) {
                headers['X-API-Key'] = window.CLIENT_API_KEY;
            }
            try {
                return await this.request(url, { method: 'POST', headers, body: JSON.stringify({ action, ...data }) });
            } catch (err) {
                console.warn('[API] requestShared direct POST failed, trying local proxy fallback', err);
                const proxyBody = { __proxy: true, target_url: url, method: 'POST', payload: { action, ...data } };
                if (typeof window.CLIENT_API_KEY !== 'undefined' && window.CLIENT_API_KEY) proxyBody.x_api_key = window.CLIENT_API_KEY;
                return this.request(this.baseUrl + '/api/endpoint.php', { method: 'POST', body: JSON.stringify(proxyBody) });
            }
        }

        // Fallback: lokale API (wie vorher)
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