# Shared Module Tabs

This directory contains individual tab files for the shared module.

## Tab Files

Each file represents a separate tab in the shared module:

- **dashboard.php** - Dashboard with stats and overview
- **transactions.php** - Shared transaction list
- **accounts.php** - Shared account management
- **invoices.php** - Shared invoice management
- **youtube.php** - YouTube income and expenses tracking
- **backup.php** - Backup and export functionality

## Special Files

- **_modals.php** - All modal dialogs used across tabs
- **_script.php** - JavaScript module logic

## Adding a New Tab

To add a new tab to the shared module:

1. Create a new PHP file in this directory (e.g., `newtab.php`)
2. Add the tab content with the structure:
   ```php
   <!-- Tab: New Tab -->
   <div class="tab-content" id="shared-tab-newtab" style="display: none;">
       <!-- Your tab content here -->
   </div>
   ```
3. Add the tab file name to the `$tabFiles` array in `/views/modules/shared.php`
4. Register the tab in the JavaScript module in `_script.php` using `App.registerTabs()`

Example:
```php
// In shared.php
$tabFiles = [
    'dashboard',
    'transactions',
    'accounts',
    'invoices',
    'youtube',
    'backup',
    'newtab'  // <- Add your new tab here
];

// In _script.php (in the init function)
App.registerTabs('shared', [
    { id: 'dashboard', label: 'Dashboard', icon: '📊' },
    { id: 'transactions', label: 'Transaktionen', icon: '💳' },
    { id: 'accounts', label: 'Konten', icon: '📁' },
    { id: 'invoices', label: 'Rechnungen', icon: '📄' },
    { id: 'youtube', label: 'YouTube', icon: '📺' },
    { id: 'backup', label: 'Backup', icon: '💾' },
    { id: 'newtab', label: 'New Tab', icon: '🆕' }  // <- Add tab registration
]);
```
