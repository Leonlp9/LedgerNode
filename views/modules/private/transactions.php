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

    <!-- Pagination für Transaktionen -->
    <div class="pagination-container" id="private-transaction-pagination" style="display: none;">
        <div class="pagination-info">
            <span id="private-transaction-pagination-info">Zeige 0-0 von 0</span>
        </div>
        <div class="pagination-controls">
            <button class="btn btn-small" id="private-transaction-prev-btn" onclick="PrivateModule.prevTransactionPage()" disabled>
                ‹ Zurück
            </button>
            <span class="pagination-pages" id="private-transaction-pages"></span>
            <button class="btn btn-small" id="private-transaction-next-btn" onclick="PrivateModule.nextTransactionPage()" disabled>
                Weiter ›
            </button>
        </div>
        <div class="pagination-per-page">
            <label for="private-transaction-per-page">Pro Seite:</label>
            <select id="private-transaction-per-page" onchange="PrivateModule.changeTransactionPerPage(this.value)">
                <option value="15" selected>15</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>
</div>
