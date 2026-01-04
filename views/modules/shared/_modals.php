<!-- Modal: Add YouTube Income -->
<div id="youtube-income-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Monatliche YouTube Einnahmen</h3>
            <button class="modal-close" onclick="SharedModule.closeYouTubeIncomeModal()">&times;</button>
        </div>
        <form id="youtube-income-form" onsubmit="SharedModule.submitYouTubeIncome(event)">
            <input type="hidden" id="yt-income-id" name="id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="yt-income-year">Jahr</label>
                    <input type="number" id="yt-income-year" name="year" min="2000" max="2100" required>
                </div>
                
                <div class="form-group">
                    <label for="yt-income-month">Monat</label>
                    <select id="yt-income-month" name="month" required>
                        <option value="1">Januar</option>
                        <option value="2">Februar</option>
                        <option value="3">März</option>
                        <option value="4">April</option>
                        <option value="5">Mai</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Dezember</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="yt-income-total">Gesamteinnahmen (€)</label>
                <input type="number" id="yt-income-total" name="total_revenue" step="0.01" min="0" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="yt-income-donations">Spenden (€)</label>
                    <input type="number" id="yt-income-donations" name="donations" step="0.01" min="0" value="0">
                </div>
                
                <div class="form-group">
                    <label for="yt-income-members">Mitglieder (€)</label>
                    <input type="number" id="yt-income-members" name="members" step="0.01" min="0" value="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="yt-income-notes">Notizen (optional)</label>
                <textarea id="yt-income-notes" name="notes" rows="2" placeholder="Zusätzliche Informationen"></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="SharedModule.closeYouTubeIncomeModal()">
                    Abbrechen
                </button>
                <button type="submit" class="btn btn-primary">
                    Speichern
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add YouTube Expense -->
<div id="youtube-expense-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>YouTube Ausgabe</h3>
            <button class="modal-close" onclick="SharedModule.closeYouTubeExpenseModal()">&times;</button>
        </div>
        <form id="youtube-expense-form" onsubmit="SharedModule.submitYouTubeExpense(event)">
            <input type="hidden" id="yt-expense-id" name="id">
            
            <div class="form-group">
                <label for="yt-expense-amount">Betrag (€)</label>
                <input type="number" id="yt-expense-amount" name="amount" step="0.01" min="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="yt-expense-recipient">An wen / Empfänger</label>
                <input type="text" id="yt-expense-recipient" name="recipient" required placeholder="z.B. Designer, Editor">
            </div>
            
            <div class="form-group">
                <label for="yt-expense-description">Wofür / Beschreibung</label>
                <textarea id="yt-expense-description" name="description" rows="3" required placeholder="Was wurde bezahlt"></textarea>
            </div>
            
            <div class="form-group">
                <label for="yt-expense-date">Datum</label>
                <input type="date" id="yt-expense-date" name="date" required>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="SharedModule.closeYouTubeExpenseModal()">
                    Abbrechen
                </button>
                <button type="submit" class="btn btn-primary">
                    Speichern
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Neue gemeinsame Transaktion -->
<div id="shared-transaction-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Neue gemeinsame Transaktion</h3>
            <button class="modal-close" onclick="SharedModule.closeModal()">&times;</button>
        </div>
        <form id="shared-transaction-form" onsubmit="SharedModule.submitTransaction(event)">
            <div class="form-group">
                <label for="shared-tx-type">Typ</label>
                <select id="shared-tx-type" name="type" required>
                    <option value="expense">Ausgabe</option>
                    <option value="income">Einnahme</option>
                </select>
            </div>

            <div class="form-group">
                <label for="shared-tx-account">Konto</label>
                <select id="shared-tx-account" name="account_id" required>
                    <!-- Wird dynamisch gefüllt -->
                </select>
            </div>

            <div class="form-group">
                <label for="shared-tx-amount">Betrag (€)</label>
                <input type="number" 
                       id="shared-tx-amount" 
                       name="amount" 
                       step="0.01" 
                       min="0.01" 
                       required>
            </div>

            <div class="form-group">
                <label for="shared-tx-description">Beschreibung</label>
                <input type="text" 
                       id="shared-tx-description" 
                       name="description" 
                       required>
            </div>

            <div class="form-group">
                <label for="shared-tx-date">Datum</label>
                <input type="date" 
                       id="shared-tx-date" 
                       name="date" 
                       required>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="SharedModule.closeModal()">
                    Abbrechen
                </button>
                <button type="submit" class="btn btn-primary">
                    Speichern
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Neues gemeinsames Konto -->
<div id="shared-account-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="shared-account-modal-title">Neues gemeinsames Konto</h3>
            <button class="modal-close" onclick="SharedModule.closeAccountModal()">&times;</button>
        </div>
        <form id="shared-account-form" onsubmit="SharedModule.submitAccount(event)">
            <input type="hidden" id="shared-acc-id" name="id">
            <div class="form-group">
                <label for="shared-acc-name">Kontoname</label>
                <input type="text" 
                       id="shared-acc-name" 
                       name="name" 
                       placeholder="z.B. Haushaltskasse"
                       required>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="SharedModule.closeAccountModal()">
                    Abbrechen
                </button>
                <button type="submit" class="btn btn-primary">
                    Erstellen
                </button>
            </div>
        </form>
    </div>
</div>
