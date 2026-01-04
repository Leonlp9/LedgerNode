    <!-- Tab: Dashboard -->
    <div class="tab-content" id="shared-tab-dashboard">
        <div class="module-header">
            <h2>Dashboard</h2>
            <p class="subtitle">Zentral verwaltete Finanzen</p>
            <?php if (\App\Core\Config::isClient()): ?>
                <div class="sync-status" id="sync-status">
                    <span class="status-indicator"></span>
                    <span>Verbunden mit Server</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Dashboard Cards -->
    <div class="dashboard-grid">
        <div class="card">
            <div class="card-header">
                <h3>Gesamtsaldo</h3>
                <span class="card-icon">💼</span>
            </div>
            <div class="card-body">
                <div class="stat-value" id="shared-balance">0,00 €</div>
                <div class="stat-label">Gemeinsame Mittel</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Einnahmen</h3>
                <span class="card-icon">💵</span>
            </div>
            <div class="card-body">
                <div class="stat-value stat-positive" id="shared-income">0,00 €</div>
                <div class="stat-label">Gesamt</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Ausgaben</h3>
                <span class="card-icon">💸</span>
            </div>
            <div class="card-body">
                <div class="stat-value stat-negative" id="shared-expenses">0,00 €</div>
                <div class="stat-label">Gesamt</div>
            </div>
        </div>
    </div>

    <!-- Aktionen -->
    <div class="actions-bar">
        <button class="btn btn-primary" onclick="SharedModule.showAddTransactionWithTab()">
            ➕ Neue Transaktion
        </button>
        <?php if (\App\Core\Config::isClient()): ?>
            <button class="btn btn-info" onclick="SharedModule.syncWithServer()">
                🔄 Synchronisieren
            </button>
        <?php endif; ?>
    </div>

    <!-- Gemeinsame Konten (Preview) -->
    <div class="card">
        <div class="card-header">
            <h3>Gemeinsame Konten</h3>
            <button class="btn btn-small" onclick="App.switchTab('shared', 'accounts')">
                Alle verwalten →
            </button>
        </div>
        <div class="card-body">
            <div id="shared-accounts-preview" class="accounts-grid">
                <!-- Wird dynamisch gefüllt -->
            </div>
        </div>
    </div>

    <!-- Transaktionsliste (Preview) -->
    <div class="card">
        <div class="card-header">
            <h3>Letzte Transaktionen</h3>
            <button class="btn btn-small" onclick="App.switchTab('shared', 'transactions')">
                Alle anzeigen →
            </button>
        </div>
        <div class="card-body">
            <div id="shared-transactions-preview" class="transactions-list">
                <!-- Wird dynamisch gefüllt -->
            </div>
        </div>
    </div>

