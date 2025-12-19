<?php
// Einfaches Auto-Update-Skript für Server-Instanz
// Wird asynchron vom API-Endpunkt gestartet

chdir(dirname(__DIR__)); // Projektroot

$logFile = __DIR__ . '/auto_update.log';
function logMsg($msg) {
    global $logFile;
    $line = date('c') . ' - ' . $msg . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

logMsg('Auto-update started');

// Sicherheits: führe nur aus wenn Config::isServer true
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Config;

try {
    if (!Config::isServer()) {
        logMsg('Not a server instance; aborting');
        exit;
    }
} catch (\Exception $e) {
    // Wenn Config nicht geladen werden kann, beende
    logMsg('Config load error: ' . $e->getMessage());
    exit;
}

// Schritt 1: git fetch
exec('git fetch 2>&1', $outFetch, $retFetch);
if ($retFetch !== 0) {
    logMsg('git fetch failed: ' . implode('\n', $outFetch));
    exit;
}

// Bestimme remote branch
$branch = 'origin/master';
exec('git rev-parse --abbrev-ref origin/HEAD 2>&1', $outBranch, $rBranch);
if ($rBranch === 0 && !empty($outBranch[0]) && strpos($outBranch[0], '/') !== false) {
    $parts = explode('/', trim($outBranch[0]));
    $branch = 'origin/' . end($parts);
}

// Prüfe ob es neue Commits gibt
exec("git log HEAD..{$branch} --oneline 2>&1", $outLog, $rLog);
if ($rLog !== 0) {
    logMsg('git log failed: ' . implode('\n', $outLog));
    exit;
}

if (empty($outLog)) {
    logMsg('No updates found');
    exit;
}

logMsg('Updates found: ' . count($outLog));
foreach ($outLog as $c) logMsg('  ' . $c);

// Führe Pull durch (achte auf sensible Dateien)
$exclude = ['config.php', 'config.ini', 'uploads'];
foreach ($exclude as $item) {
    exec('git update-index --assume-unchanged ' . escapeshellarg($item) . ' 2>&1');
}

exec('git checkout -- . 2>&1', $outCheckout, $rCheckout);
if ($rCheckout !== 0) {
    logMsg('git checkout failed: ' . implode('\n', $outCheckout));
    foreach ($exclude as $item) exec('git update-index --no-assume-unchanged ' . escapeshellarg($item) . ' 2>&1');
    exit;
}

exec('git pull ' . escapeshellarg($branch) . ' 2>&1', $outPull, $rPull);
foreach ($exclude as $item) {
    exec('git update-index --no-assume-unchanged ' . escapeshellarg($item) . ' 2>&1');
}

if ($rPull !== 0) {
    logMsg('git pull failed: ' . implode('\n', $outPull));
    exit;
}

logMsg('git pull successful: ' . implode('\n', $outPull));
logMsg('Auto-update finished');

?>
