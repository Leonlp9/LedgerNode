<!-- Tab: YouTube -->
<div class="tab-content" id="shared-tab-youtube" style="display: none;">
    <div class="module-header">
        <h2>YouTube Einnahmen & Ausgaben</h2>
        <p class="subtitle">Verwalte deine YouTube Kanal-Finanzen</p>
    </div>

    <!-- Sub-Tabs for YouTube -->
    <div class="invoice-subtabs">
        <button class="subtab-btn active" data-subtab="income" onclick="SharedModule.switchYouTubeSubtab('income')">
            💰 Einnahmen
        </button>
        <button class="subtab-btn" data-subtab="expenses" onclick="SharedModule.switchYouTubeSubtab('expenses')">
            💸 Ausgaben
        </button>
    </div>

    <!-- YouTube Income Sub-Tab -->
    <div id="youtube-income-section">
        <div class="actions-bar">
            <button class="btn btn-primary" onclick="SharedModule.showAddYouTubeIncome()">
                ➕ Monatliche Einnahmen hinzufügen
            </button>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Monatliche YouTube Einnahmen</h3>
                <button class="btn btn-small" onclick="SharedModule.loadYouTubeIncome()">
                    🔄 Aktualisieren
                </button>
            </div>
            <div class="card-body">
                <div id="youtube-income-list">
                    <div class="empty-state">
                        <p>Noch keine YouTube Einnahmen erfasst</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- YouTube Expenses Sub-Tab -->
    <div id="youtube-expenses-section" style="display: none;">
        <div class="actions-bar">
            <button class="btn btn-primary" onclick="SharedModule.showAddYouTubeExpense()">
                ➕ Neue Ausgabe
            </button>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>YouTube Ausgaben</h3>
                <button class="btn btn-small" onclick="SharedModule.loadYouTubeExpenses()">
                    🔄 Aktualisieren
                </button>
            </div>
            <div class="card-body">
                <div id="youtube-expenses-list">
                    <div class="empty-state">
                        <p>Noch keine YouTube Ausgaben erfasst</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
