// CustomDialog: einfache, Promise-basierte Alert/Confirm-API
// Wird als globales Objekt `CustomDialog` bereitgestellt.
(function () {
    if (window.CustomDialog) return;

    const styleId = 'custom-dialog-styles';

    function injectHtml() {
        if (document.getElementById('custom-dialog-root')) return;

        const root = document.createElement('div');
        root.id = 'custom-dialog-root';
        root.innerHTML = `
            <div class="cd-overlay" id="cd-overlay" aria-hidden="true"></div>
            <div class="cd-modal" id="cd-modal" role="dialog" aria-modal="true" aria-labelledby="cd-title" style="display:none">
                <div class="cd-body">
                    <h3 id="cd-title" class="cd-title"></h3>
                    <div id="cd-message" class="cd-message"></div>
                    <div class="cd-actions" id="cd-actions"></div>
                </div>
            </div>
        `;
        document.body.appendChild(root);

        // Keyboard handling
        document.addEventListener('keydown', (e) => {
            const modal = document.getElementById('cd-modal');
            if (!modal || modal.style.display === 'none') return;
            if (e.key === 'Escape') {
                // trigger cancel if exists
                const cancel = modal.querySelector('[data-cd-action="cancel"]');
                if (cancel) cancel.click();
            }
            if (e.key === 'Enter') {
                const ok = modal.querySelector('[data-cd-action="ok"]');
                if (ok) ok.click();
            }
        });
    }

    function showModal({title = '', message = '', html = false, okText = 'OK', cancelText = 'Abbrechen', showCancel = false}) {
        injectHtml();
        const overlay = document.getElementById('cd-overlay');
        const modal = document.getElementById('cd-modal');
        const titleEl = document.getElementById('cd-title');
        const msgEl = document.getElementById('cd-message');
        const actions = document.getElementById('cd-actions');

        titleEl.textContent = title || '';
        if (html) {
            msgEl.innerHTML = message;
        } else {
            msgEl.textContent = message;
        }

        actions.innerHTML = '';

        const okBtn = document.createElement('button');
        okBtn.className = 'btn btn-primary';
        okBtn.textContent = okText;
        okBtn.setAttribute('data-cd-action', 'ok');

        actions.appendChild(okBtn);

        if (showCancel) {
            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'btn';
            cancelBtn.textContent = cancelText;
            cancelBtn.setAttribute('data-cd-action', 'cancel');
            actions.appendChild(cancelBtn);

            cancelBtn.addEventListener('click', () => {
                hideModal();
                if (currentResolve) currentResolve(false);
                currentResolve = null;
            });
        }

        okBtn.addEventListener('click', () => {
            hideModal();
            if (currentResolve) currentResolve(true);
            currentResolve = null;
        });

        overlay.style.display = 'block';
        modal.style.display = 'block';

        // Focus management
        okBtn.focus();
    }

    function hideModal() {
        const overlay = document.getElementById('cd-overlay');
        const modal = document.getElementById('cd-modal');
        if (overlay) overlay.style.display = 'none';
        if (modal) modal.style.display = 'none';
    }

    let currentResolve = null;

    window.CustomDialog = {
        alert(message, options = {}) {
            return new Promise((resolve) => {
                currentResolve = () => { resolve(); };
                showModal({
                    title: options.title || '',
                    message: message || '',
                    html: !!options.html,
                    okText: options.okText || 'OK',
                    showCancel: false
                });

                // override resolve to close and then resolve
                const prev = currentResolve;
                currentResolve = () => {
                    hideModal();
                    resolve();
                };
            });
        },

        confirm(message, options = {}) {
            return new Promise((resolve) => {
                currentResolve = resolve;
                showModal({
                    title: options.title || '',
                    message: message || '',
                    html: !!options.html,
                    okText: options.okText || 'OK',
                    cancelText: options.cancelText || 'Abbrechen',
                    showCancel: true
                });
            });
        }
    };

})();

