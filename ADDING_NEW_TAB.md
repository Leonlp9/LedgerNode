# How to Add a New Tab - Example

This document provides a complete example of adding a new "Settings" tab to the private module.

## Step 1: Create the Tab File

Create a new file: `views/modules/private/settings.php`

```php
<!-- Tab: Settings -->
<div class="tab-content" id="private-tab-settings" style="display: none;">
    <div class="module-header">
        <h2>Einstellungen</h2>
        <p class="subtitle">Konfiguriere deine Präferenzen</p>
    </div>

    <!-- Settings Content -->
    <div class="card">
        <div class="card-header">
            <h3>Allgemeine Einstellungen</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="setting-currency">Währung</label>
                <select id="setting-currency">
                    <option value="EUR">Euro (€)</option>
                    <option value="USD">US-Dollar ($)</option>
                    <option value="GBP">Britisches Pfund (£)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="setting-language">Sprache</label>
                <select id="setting-language">
                    <option value="de">Deutsch</option>
                    <option value="en">English</option>
                </select>
            </div>
            
            <div class="actions-bar">
                <button class="btn btn-primary" onclick="PrivateModule.saveSettings()">
                    💾 Speichern
                </button>
            </div>
        </div>
    </div>
</div>
```

## Step 2: Add the Tab to the Loader

Edit `views/modules/private.php` and add 'settings' to the `$tabFiles` array:

```php
<div class="module-content">
    <?php
    // Load all tab content files
    $tabFiles = [
        'dashboard',
        'transactions',
        'accounts',
        'invoices',
        'backup',
        'settings'  // <- Add your new tab here
    ];
    
    foreach ($tabFiles as $tab) {
        include __DIR__ . '/private/' . $tab . '.php';
    }
    
    // Load modals
    include __DIR__ . '/private/_modals.php';
    ?>
</div>

<?php
// Load JavaScript module
include __DIR__ . '/private/_script.php';
?>
```

## Step 3: Register the Tab in JavaScript

Edit `views/modules/private/_script.php` and add the tab to the registration:

Find the `init()` function around line 823-834 and modify the `App.registerTabs()` call:

```javascript
async init() {
    console.log('Private Module initialisiert');
    // Registriere Tabs speziell für dieses Modul
    if (typeof App !== 'undefined' && typeof App.registerTabs === 'function') {
        App.registerTabs('private', [
            { id: 'dashboard', label: 'Dashboard', icon: '📊' },
            { id: 'transactions', label: 'Transaktionen', icon: '💳' },
            { id: 'accounts', label: 'Konten', icon: '📁' },
            { id: 'invoices', label: 'Rechnungen', icon: '📄' },
            { id: 'backup', label: 'Backup', icon: '💾' },
            { id: 'settings', label: 'Einstellungen', icon: '⚙️' }  // <- Add registration
        ]);
    }
    
    // ... rest of init code
}
```

## Step 4 (Optional): Add JavaScript Functions

If your tab needs JavaScript functionality, add it to the PrivateModule object in `views/modules/private/_script.php`:

```javascript
const PrivateModule = {
    // ... existing properties ...
    
    // Add your new function
    async saveSettings() {
        const currency = document.getElementById('setting-currency').value;
        const language = document.getElementById('setting-language').value;
        
        try {
            const result = await API.post('/api/private/settings', {
                currency,
                language
            });
            
            if (result) {
                App.showToast('Einstellungen gespeichert', 'success');
            }
        } catch (error) {
            App.showToast('Fehler beim Speichern', 'error');
        }
    },
    
    // ... rest of the module
};
```

## Result

After following these steps:

1. ✅ A new "Einstellungen" tab will appear in the navigation
2. ✅ Clicking on it will display the settings content
3. ✅ The tab's icon will be ⚙️
4. ✅ The saveSettings() function will be available

## Benefits of This Structure

- **No need to modify large files** - Just create a new small file
- **Easy to understand** - Each tab is self-contained
- **Easy to maintain** - Changes to one tab don't affect others
- **Easy to test** - You can test individual tabs independently
- **Easy to remove** - Just delete the file and remove the entry from the array

## Tips

1. **Follow the naming convention**: Use `private-tab-{name}` for the div ID
2. **Set display: none**: All tabs except dashboard should have `style="display: none;"`
3. **Use consistent styling**: Follow the existing card/form structure
4. **Add to gitignore if needed**: If your tab generates temporary files
