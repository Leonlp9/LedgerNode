<?php
/**
 * Simple PSR-4 Autoloader
 * 
 * Falls Composer nicht verfügbar ist
 * Für Produktion sollte Composer verwendet werden!
 */

spl_autoload_register(function ($class) {
    // Projekt-Namespace
    $prefix = 'App\\';
    // Basisverzeichnis anpassen: vom vendor-Ordner zurück zum Projekt-Root
    $baseDir = __DIR__ . '/../src/';

    // Prüfe ob die Klasse unseren Prefix verwendet
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Relativer Klassenname
    $relativeClass = substr($class, $len);

    // Ersetze Namespace-Separator durch Directory-Separator
    // Füge .php an
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // Lade die Datei falls vorhanden
    if (file_exists($file)) {
        require $file;
    }
});
