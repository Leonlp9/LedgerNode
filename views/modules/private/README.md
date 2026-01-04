# Private Module Tabs

This directory contains individual tab files for the private module.

## Tab Files

Each file represents a separate tab in the private module:

- **dashboard.php** - Dashboard with stats and charts
- **transactions.php** - Transaction list and management
- **accounts.php** - Account management
- **invoices.php** - Invoice management
- **backup.php** - Backup and export functionality

## Special Files

- **_modals.php** - All modal dialogs used across tabs
- **_script.php** - JavaScript module logic

## Adding a New Tab

To add a new tab to the private module:

1. Create a new PHP file in this directory (e.g., `newtab.php`)
2. Add the tab content with the structure:
   ```php
   <!-- Tab: New Tab -->
   <div class="tab-content" id="private-tab-newtab" style="display: none;">
       <!-- Your tab content here -->
   </div>
   ```
3. Add the tab file name to the `$tabFiles` array in `/views/modules/private.php`
4. Register the tab in the JavaScript module in `_script.php` using `App.registerTabs()`

Example:
```php
// In private.php
$tabFiles = [
    'dashboard',
    'transactions',
    'accounts',
    'invoices',
    'backup',
    'newtab'  // <- Add your new tab here
];

// In _script.php (in the init function)
App.registerTabs('private', [
    { id: 'dashboard', label: 'Dashboard', icon: '📊' },
    { id: 'transactions', label: 'Transaktionen', icon: '💳' },
    { id: 'accounts', label: 'Konten', icon: '📁' },
    { id: 'invoices', label: 'Rechnungen', icon: '📄' },
    { id: 'backup', label: 'Backup', icon: '💾' },
    { id: 'newtab', label: 'New Tab', icon: '🆕' }  // <- Add tab registration
]);
```
