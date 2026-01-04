<!-- Tab: Backup -->
<div class="tab-content" id="private-tab-backup" style="display: none;">
    <div class="module-header">
        <h2>Backup & Export</h2>
        <p class="subtitle">Erstelle Backups deiner Rechnungen mit allen Dateien und Details</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Backup erstellen</h3>
        </div>
        <div class="card-body">
            <form id="private-backup-form" onsubmit="PrivateModule.generateBackup(event)">
                <div class="form-group">
                    <label for="backup-period">Zeitraum</label>
                    <select id="backup-period" name="period" onchange="PrivateModule.updateBackupOptions()">
                        <option value="month">Einzelner Monat</option>
                        <option value="year">Ganzes Jahr</option>
                        <option value="all">Alle Rechnungen</option>
                    </select>
                </div>

                <div id="backup-month-options">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="backup-year">Jahr</label>
                            <input type="number" id="backup-year" name="year" min="2000" max="2100" value="">
                        </div>
                        
                        <div class="form-group">
                            <label for="backup-month">Monat</label>
                            <select id="backup-month" name="month">
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
                </div>

                <div id="backup-year-options" style="display: none;">
                    <div class="form-group">
                        <label for="backup-year-only">Jahr</label>
                        <input type="number" id="backup-year-only" name="year_only" min="2000" max="2100" value="">
                    </div>
                </div>

                <div class="backup-info">
                    <p><strong>Was wird exportiert:</strong></p>
                    <ul>
                        <li>Alle Rechnungs-PDF-Dateien im gewählten Zeitraum</li>
                        <li>Excel-Tabelle mit allen Rechnungsdetails</li>
                        <li>Gepackt als ZIP-Datei zum einfachen Download</li>
                    </ul>
                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary btn-large">
                        💾 Backup generieren
                    </button>
                </div>
            </form>

            <div id="backup-loading" style="display: none; text-align: center; padding: 40px;">
                <div class="spinner" style="margin: 0 auto 20px;"></div>
                <p><strong>Backup wird erstellt...</strong></p>
                <p>Dies kann einige Sekunden dauern.</p>
            </div>
        </div>
    </div>
</div>
