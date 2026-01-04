<div class="module-content">
    <?php
    // Load all tab content files
    $tabFiles = [
        'dashboard',
        'transactions',
        'accounts',
        'invoices',
        'youtube',
        'backup'
    ];
    
    foreach ($tabFiles as $tab) {
        include __DIR__ . '/shared/' . $tab . '.php';
    }
    
    // Load modals
    include __DIR__ . '/shared/_modals.php';
    ?>
</div>

<?php
// Load JavaScript module
include __DIR__ . '/shared/_script.php';
?>
