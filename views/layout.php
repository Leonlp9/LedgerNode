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
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/public/css/custom-dialog.css') ?>?v=<?= $assetVersion ?>">

    <!-- Chart.js (CDN) - wird vor den Modulen geladen, damit Inline-Skripte Chart nutzen können -->
    <script src="<?= htmlspecialchars($basePath . '/public/js/chart.js') ?>?v=<?= $assetVersion ?>"></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Menü öffnen">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <!-- Logo als Bild statt Text -->
            <a href="<?= htmlspecialchars($basePath ?: '/') ?>" class="logo-link">
                <img src="<?= htmlspecialchars($basePath . '/logo.png') ?>" alt="<?= htmlspecialchars(\App\Core\Config::get('APP.name', 'Accounting')) ?>" class="logo-img">
            </a>
            <!-- Die Instance-Badge (Client/Server) wurde entfernt -->
        </div>
    </header>

    <div class="app-container">
        <!-- Sidebar Backdrop for Mobile -->
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <nav class="nav" id="tab-nav">
                <!-- Tabs werden hier dynamisch eingefügt -->
            </nav>

            <div class="module-switcher">
                <button class="module-switch-btn active" data-module="private" onclick="App.switchModule('private')">
                    <span class="nav-icon">👤</span>
                    <span>Privat</span>
                </button>
                <button class="module-switch-btn" data-module="shared" onclick="App.switchModule('shared')">
                    <span class="nav-icon">👥</span>
                    <span>Gemeinsam</span>
                </button>
            </div>

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

    <!-- Update-Popup (wird vom App-Updater gesteuert) -->
    <div id="update-modal" class="modal" style="display:none;">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update verfügbar</h3>
                <button id="update-close-btn" class="modal-close" aria-label="Schließen">×</button>
            </div>

            <div class="modal-body">
                <p id="update-status">Prüfe auf Updates...</p>
                <ul id="update-commits" class="update-commit-list"></ul>
            </div>

            <div class="modal-footer">
                <button id="update-check-btn" class="btn btn-secondary">Auf Updates prüfen</button>
                <button id="update-install-btn" class="btn btn-primary">Update installieren</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        window.APP_BASE = <?= json_encode($basePath) ?>;
        window.IS_SERVER = <?= json_encode(\App\Core\Config::isServer()) ?>;
        // SERVER_API_URL: für Clients in config.php gesetzt (nur relevant wenn dies NICHT der Server ist)
        window.SERVER_API_URL = <?= json_encode(\App\Core\Config::getApiUrl()) ?>;
        <?php if (!\App\Core\Config::isServer()): ?>
            // CLIENT_API_KEY: Wird benötigt, damit der Client API-Requests an den zentralen Server authentifiziert.
            // JA, der Key muss auf dem Client liegen (config.php). Sei dir bewusst, dass er im Browser sichtbar ist.
            window.CLIENT_API_KEY = <?= json_encode(\App\Core\Config::getApiKey()) ?>;
        <?php else: ?>
            window.CLIENT_API_KEY = null;
        <?php endif; ?>
    </script>
    <script src="<?= htmlspecialchars($basePath . '/public/js/api.js') ?>?v=<?= $assetVersion ?>"></script>
    <script src="<?= htmlspecialchars($basePath . '/public/js/custom-dialog.js') ?>?v=<?= $assetVersion ?>"></script>
    <script src="<?= htmlspecialchars($basePath . '/public/js/invoice-creator.js') ?>?v=<?= $assetVersion ?>"></script>
    <script src="<?= htmlspecialchars($basePath . '/public/js/app.js') ?>?v=<?= $assetVersion ?>"></script>

    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
