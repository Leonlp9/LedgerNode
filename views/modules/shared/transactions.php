<!-- Tab: Transaktionen -->
<div class="tab-content" id="shared-tab-transactions" style="display: none;">
    <div class="module-header">
        <h2>Transaktionen</h2>
        <p class="subtitle">Alle gemeinsamen Transaktionen</p>
    </div>

    <!-- Aktionen -->
    <div class="actions-bar">
        <button class="btn btn-primary" onclick="SharedModule.showAddTransaction()">
            ➕ Neue Transaktion
        </button>
    </div>

    <!-- Transaktionsliste -->
    <div class="card">
        <div class="card-header">
            <h3>Alle Transaktionen</h3>
            <button class="btn btn-small" onclick="SharedModule.loadTransactions()">
                🔄 Aktualisieren
            </button>
        </div>
        <div class="card-body">
            <div id="shared-transactions-list" class="transactions-list">
                <!-- Wird dynamisch gefüllt -->
                <div class="empty-state">
                    <p>Noch keine gemeinsamen Transaktionen vorhanden</p>
                </div>
            </div>
        </div>
    </div>
</div>
