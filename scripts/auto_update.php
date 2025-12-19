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
$remote = 'origin';
$remoteBranch = null;

// Versuche Upstream der aktuellen Branch zu bestimmen
$upstream = [];$uRet = 0;
exec('git rev-parse --abbrev-ref --symbolic-full-name @{u} 2>&1', $upstream, $uRet);
if ($uRet === 0 && !empty($upstream[0])) {
    $parts = explode('/', $upstream[0], 2);
    if (count($parts) === 2) {
        $remote = $parts[0];
        $remoteBranch = $parts[1];
    } else {
        $remoteBranch = $upstream[0];
    }
} else {
    // Fallback: remote show origin -> HEAD branch
    $remoteShow = [];$rsRet = 0;
    exec('git remote show origin 2>&1', $remoteShow, $rsRet);
    foreach ($remoteShow as $line) {
        if (stripos($line, 'HEAD branch:') !== false) {
            $rb = trim(substr($line, stripos($line, ':') + 1));
            if ($rb !== '') { $remoteBranch = $rb; break; }
        }
    }
    if ($remoteBranch === null) {
        $oHead = [];$ohRet = 0;
        exec('git rev-parse --abbrev-ref origin/HEAD 2>&1', $oHead, $ohRet);
        if ($ohRet === 0 && !empty($oHead[0]) && strpos($oHead[0], '/') !== false) {
            $parts = explode('/', trim($oHead[0]));
            $remoteBranch = end($parts);
        }
    }
}

if (empty($remoteBranch)) {
    logMsg('Konnte Remote-Branch nicht bestimmen; aborting');
    exit;
}

$remoteRef = $remote . '/' . $remoteBranch;

// Prüfe ob es neue Commits gibt
exec("git log HEAD..{$remoteRef} --oneline 2>&1", $outLog, $rLog);
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

// Pull using remote + branch (sauberer Aufruf)
$outPull = [];$rPull = 0;
$pullCmd = 'git pull ' . escapeshellarg($remote) . ' ' . escapeshellarg($remoteBranch) . ' 2>&1';
exec($pullCmd, $outPull, $rPull);
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
