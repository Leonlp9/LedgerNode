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

    <!-- Pagination für Konten -->
    <div class="pagination-container" id="private-account-pagination" style="display: none;">
        <div class="pagination-info">
            <span id="private-account-pagination-info">Zeige 0-0 von 0</span>
        </div>
        <div class="pagination-controls">
            <button class="btn btn-small" id="private-account-prev-btn" onclick="PrivateModule.prevAccountPage()" disabled>
                ‹ Zurück
            </button>
            <span class="pagination-pages" id="private-account-pages"></span>
            <button class="btn btn-small" id="private-account-next-btn" onclick="PrivateModule.nextAccountPage()" disabled>
                Weiter ›
            </button>
        </div>
        <div class="pagination-per-page">
            <label for="private-account-per-page">Pro Seite:</label>
            <select id="private-account-per-page" onchange="PrivateModule.changeAccountPerPage(this.value)">
                <option value="15" selected>15</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>
</div>
