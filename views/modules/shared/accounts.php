<!-- Tab: Konten verwalten -->
<div class="tab-content" id="shared-tab-accounts" style="display: none;">
    <div class="module-header">
        <h2>Konten verwalten</h2>
        <p class="subtitle">Verwalte gemeinsame Konten</p>
    </div>

    <!-- Aktionen -->
    <div class="actions-bar">
        <button class="btn btn-primary" onclick="SharedModule.showAddAccount()">
            ➕ Neues Konto
        </button>
    </div>

    <!-- Kontenliste -->
    <div class="card">
        <div class="card-header">
            <h3>Gemeinsame Konten</h3>
            <button class="btn btn-small" onclick="SharedModule.loadAccountsManagement()">
                🔄 Aktualisieren
            </button>
        </div>
        <div class="card-body">
            <div id="shared-accounts-management" class="accounts-management-list">
                <!-- Wird dynamisch gefüllt -->
                <div class="empty-state">
                    <p>Noch keine Konten vorhanden</p>
                    <button class="btn btn-primary" onclick="SharedModule.showAddAccount()">
                        Erstes Konto erstellen
                    </button>
                </div>
            </div>
        </div>
    </div>

