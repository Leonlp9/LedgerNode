<div class="module-content">
    <?php
    // Load all tab content files
    $tabFiles = [
        'dashboard',
        'transactions',
        'accounts',
        'invoices',
        'backup'
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
