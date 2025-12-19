<?php
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($basePath === '.' || $basePath === '/') {
    $basePath = '';
}
$assetVersion = time();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(isset($pageTitle) ? $pageTitle : 'Buchhaltung') ?></title>

    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/css/main.css') ?>?v=<?= $assetVersion ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/css/transitions.css') ?>?v=<?= $assetVersion ?>">

    <!-- Chart.js (CDN) - wird vor den Modulen geladen, damit Inline-Skripte Chart nutzen können -->
    <script src="<?= htmlspecialchars($basePath . '/public/js/chart.js') ?>?v=<?= $assetVersion ?>"></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1 class="logo">
                <?= htmlspecialchars(\App\Core\Config::get('APP.name', 'Accounting')) ?>
            </h1>
            <div class="header-info">
                <span class="instance-badge <?= \App\Core\Config::isServer() ? 'server' : 'client' ?>">
                    <?= \App\Core\Config::isServer() ? '🖥️ Server' : '📟 Client' ?>
                </span>
            </div>
        </div>
    </header>

    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <nav class="nav">
                <a href="#" 
                   class="nav-item active" 
                   data-module="private"
                   onclick="App.switchModule('private'); return false;">
                    <span class="nav-icon">👤</span>
                    <span class="nav-label">Privat</span>
                </a>
                
                <a href="#" 
                   class="nav-item" 
                   data-module="shared"
                   onclick="App.switchModule('shared'); return false;">
                    <span class="nav-icon">👥</span>
                    <span class="nav-label">Gemeinsam</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <small>Version <?= htmlspecialchars(\App\Core\Config::get('APP.version', '1.0.0')) ?></small>
            </div>
        </aside>

        <!-- Haupt-Content-Bereich -->
        <main class="content">
            <div class="content-wrapper">
                <!-- Module werden hier dynamisch geladen -->
                <div id="module-container" class="module-container">
                    <!-- Initialer Content (Privat) -->
                    <div id="module-private" class="module active">
                        <?php include __DIR__ . '/modules/private.php'; ?>
                    </div>

                    <!-- Gemeinsam-Modul (versteckt) -->
                    <div id="module-shared" class="module">
                        <?php include __DIR__ . '/modules/shared.php'; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay" style="display: none;">
        <div class="spinner"></div>
        <p>Lädt...</p>
    </div>

    <!-- Toast Notifications -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Scripts -->
    <script>window.APP_BASE = <?= json_encode($basePath) ?>;</script>
    <script src="<?= htmlspecialchars($basePath . '/public/js/api.js') ?>?v=<?= $assetVersion ?>"></script>
    <script src="<?= htmlspecialchars($basePath . '/public/js/app.js') ?>?v=<?= $assetVersion ?>"></script>

    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
