<!-- Tab: Dashboard -->
<div class="tab-content" id="private-tab-dashboard">
    <div class="module-header">
        <h2>Dashboard</h2>
        <p class="subtitle">Deine persönlichen Finanzen</p>
    </div>

    <!-- Dashboard Cards -->
    <div class="dashboard-grid">
        <div class="card">
            <div class="card-header">
                <h3>Kontostand</h3>
                <span class="card-icon">💰</span>
            </div>
            <div class="card-body">
                <div class="stat-value" id="private-balance">0,00 €</div>
                <div class="stat-label">Aktueller Saldo</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Einnahmen</h3>
                <span class="card-icon">📈</span>
            </div>
            <div class="card-body">
                <div class="stat-value stat-positive" id="private-income">0,00 €</div>
                <div class="stat-label">Diesen Monat</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Ausgaben</h3>
                <span class="card-icon">📉</span>
            </div>
            <div class="card-body">
                <div class="stat-value stat-negative" id="private-expenses">0,00 €</div>
                <div class="stat-label">Diesen Monat</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Unverknüpfte Rechnungen</h3>
                <span class="card-icon">📄</span>
            </div>
            <div class="card-body">
                <div class="stat-value stat-warning" id="private-unlinked-invoices">0</div>
                <div class="stat-label">Noch nicht zugeordnet</div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <div class="card chart-card">
            <div class="card-header">
                <h3>Kontostandsverlauf (letzte 12 Monate)</h3>
            </div>
            <div class="card-body">
                <canvas id="chart-balance" width="600" height="250"></canvas>
            </div>
        </div>

        <div class="card chart-card">
            <div class="card-header">
                <h3>Ausgaben nach Kategorie</h3>
            </div>
            <div class="card-body">
                <canvas id="chart-expenses-categories" width="400" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Aktionen -->
    <div class="actions-bar">
        <button class="btn btn-primary" onclick="PrivateModule.showAddTransactionWithTab()">
            ➕ Neue Transaktion
        </button>
    </div>

    <!-- Transaktionsliste (Preview) -->
    <div class="card">
        <div class="card-header">
            <h3>Letzte Transaktionen</h3>
            <button class="btn btn-small" onclick="App.switchTab('private', 'transactions')">
                Alle anzeigen →
            </button>
        </div>
        <div class="card-body">
            <div id="private-transactions-preview" class="transactions-list">
                <!-- Wird dynamisch gefüllt -->
            </div>
        </div>
    </div>
</div>
