@echo off
REM setup.bat - Windows-Äquivalent für setup.sh (XAMPP-kompatibel)
REM Verwendung: setup.bat server  oder  setup.bat client

setlocal EnableDelayedExpansion

echo =========================================
echo Setup: %~1
echo =========================================

:: Prüfe Argument
if "%~1"=="" (
  echo Fehler: Kein Typ angegeben!
  echo Verwendung: setup.bat [server|client]
  exit /b 1
)

set INSTALL_TYPE=%~1
if /I "%INSTALL_TYPE%" neq "server" if /I "%INSTALL_TYPE%" neq "client" (
  echo Fehler: Ungueltiger Typ: %INSTALL_TYPE%
  echo Verwendung: setup.bat [server|client]
  exit /b 1
)
@REM
@REM :CHECK_PHP
@REM echo Pruefe PHP...
@REM where php >nul 2>&1
@REM if errorlevel 1 (
@REM   echo PHP wurde nicht gefunden. Bitte PHP (XAMPP) in PATH aufnehmen.
@REM   exit /b 1
@REM )
@REM for /f "delims=" %%V in ('php -r "echo PHP_VERSION;"') do set PHP_VERSION=%%V
@REM if "%PHP_VERSION%"=="" (
@REM   echo Fehler beim Ermitteln der PHP-Version
@REM   exit /b 1
@REM )
@REM echo Gefundene PHP-Version: %PHP_VERSION%
@REM php -r "exit(version_compare(PHP_VERSION, '8.0.0', '<') ? 1 : 0);" >nul 2>&1
@REM if errorlevel 1 (
@REM   echo PHP 8.0 oder hoehere Version erforderlich!
@REM   exit /b 1
@REM )
@REM echo PHP-Version OK

:: API-Key generieren (nutzt PHP)
for /f "delims=" %%K in ('php -r "echo bin2hex(random_bytes(32));"') do set API_KEY=%%K

:: Kopiere config.example.php -> config.php falls nicht vorhanden oder wenn ueber schreiben gewuenscht
if exist config.php (
    echo Achtung: config.php existiert bereits.
    choice /M "Ueberschreiben?" /N >nul
    if errorlevel 2 (
        echo Ueberschreiben verneint. Konfiguration wird uebersprungen.
        goto SKIP_CONFIG
    ) else (
        del /f /q config.php >nul 2>&1
    )
)

copy /Y config.example.php config.php >nul 2>&1
if errorlevel 1 (
  echo Fehler: config.example.php konnte nicht kopiert werden.
  exit /b 1
)

:: Set environment vars so PowerShell child processes can access them
set "ENV_API_KEY=%API_KEY%"

if /I "%INSTALL_TYPE%"=="server" (
    echo Konfiguriere Server-Instanz...
    set /p DB_HOST=MySQL Host [localhost]:
    if "%DB_HOST%"=="" set DB_HOST=localhost
    set /p DB_NAME=MySQL Datenbank [accounting_db]:
    if "%DB_NAME%"=="" set DB_NAME=accounting_db
    set /p DB_USER=MySQL User [accounting_user]:
    if "%DB_USER%"=="" set DB_USER=accounting_user
    set /p DB_PASS=MySQL Passwort (Eingabe sichtbar):
    echo.

    set "ENV_DB_HOST=%DB_HOST%"
    set "ENV_DB_NAME=%DB_NAME%"
    set "ENV_DB_USER=%DB_USER%"
    set "ENV_DB_PASS=%DB_PASS%"

    REM Verwende PowerShell zum Durchfuehren von robusten Text-Replacements in config.php
    powershell -NoProfile -Command "(Get-Content -Raw 'config.php') -replace '\'IS_SERVER\'\s*=>\s*false', '\'IS_SERVER\' => true' -replace '\'API_KEY\'\s*=>\s*\'[^\']*\'', '\'API_KEY\' => '' + $env:ENV_API_KEY + '' -replace '\'driver\'\s*=>\s*\'mysql\'', '\'driver\' => \'mysql\'' -replace '\'host\'\s*=>\s*\'[^\']*\'', '\'host\'   => '' + $env:ENV_DB_HOST + '' -replace '\'name\'\s*=>\s*\'[^\']*\'', '\'name\'   => '' + $env:ENV_DB_NAME + '' -replace '\'user\'\s*=>\s*\'[^\']*\'', '\'user\'   => '' + $env:ENV_DB_USER + '' -replace '\'pass\'\s*=>\s*\'[^\']*\'', '\'pass\'   => '' + $env:ENV_DB_PASS + '' | Set-Content 'config.php'" >nul 2>&1
    if errorlevel 1 (
        echo Fehler beim Anpassen von config.php
        exit /b 1
    )

    echo Server-Konfiguration erstellt.
    echo WICHTIG: Speichere diesen API-Key fuer Clients:
    echo %API_KEY%

) else (
    echo Konfiguriere Client-Instanz...
    set /p API_URL=Server API-URL:
    set /p PROVIDED_KEY=API-Key (vom Server):
    set "ENV_API_URL=%API_URL%"
    set "ENV_PROVIDED_KEY=%PROVIDED_KEY%"
    set "ENV_DRIVER=sqlite"

    powershell -NoProfile -Command "(Get-Content -Raw 'config.php') -replace '\'IS_SERVER\'\s*=>\s*false', '\'IS_SERVER\' => false' -replace '\'API_URL\'\s*=>\s*\'[^\']*\'', '\'API_URL\'   => '' + $env:ENV_API_URL + '' -replace '\'API_KEY\'\s*=>\s*\'[^\']*\'', '\'API_KEY\' => '' + $env:ENV_PROVIDED_KEY + '' -replace '\'driver\'\s*=>\s*\'mysql\'', '\'driver\' => \'sqlite\'' | Set-Content 'config.php'" >nul 2>&1
    if errorlevel 1 (
        echo Fehler beim Anpassen von config.php
        exit /b 1
    )
    echo Client-Konfiguration erstellt.
)

:SKIP_CONFIG

:: Berechtigungs-Hinweis (Windows hat andere Berechtigungen als Linux)
attrib +R config.php >nul 2>&1
echo Hinweis: Windows-Dateiberechtigungen sind unterschiedlich; stelle sicher, dass config.php nicht in ein oeffentliches Repo gelangt.

:: Datenbank einrichten
if /I "%INSTALL_TYPE%"=="server" (
    echo Importiere MySQL-Schema...
    if not exist database\server_schema.sql (
        echo Fehler: database\server_schema.sql nicht gefunden.
    ) else (
        where mysql >nul 2>&1
        if errorlevel 1 (
            echo mysql-Client nicht gefunden. Bitte MySQL installieren oder Pfad setzen.
        ) else (
            echo Fuehre Import aus (du wirst nach Passwort gefragt, falls erforderlich)...
            mysql -h"%DB_HOST%" -u"%DB_USER%" -p"%DB_PASS%" "%DB_NAME%" < database\server_schema.sql
            if errorlevel 1 (
                echo MySQL-Import fehlgeschlagen! Bitte manuell importieren: mysql -u %DB_USER% -p %DB_NAME% ^< database\server_schema.sql
            ) else (
                echo MySQL-Schema importiert.
            )
        )
    )
) else (
    echo Erstelle lokale SQLite-Datenbank...
    if not exist database mkdir database
    if exist database\local.db (
        choice /M "database\local.db existiert bereits. Neu erstellen?" /N >nul
        if errorlevel 2 (
            echo Belasse bestehende DB.
            goto SKIP_DB_CLIENT
        ) else (
            del /f /q database\local.db >nul 2>&1
        )
    )
    where sqlite3 >nul 2>&1
    if errorlevel 1 (
        echo sqlite3 wurde nicht gefunden. Installiere sqlite3 oder erstelle DB manuell.
    ) else (
        sqlite3 database\local.db < database\client_schema.sql
        if errorlevel 1 (
            echo Fehler beim Erstellen der SQLite-DB.
        ) else (
            echo SQLite-DB erstellt: database\local.db
        )
    )
)
:SKIP_DB_CLIENT

:: Apache/XAMPP Hinweis
echo.
if /I "%INSTALL_TYPE%"=="server" (
    set /p SERVER_NAME=ServerName (z.B. accounting.example.com):
) else (
    set SERVER_NAME=localhost
)
echo Hinweis: Unter Windows (XAMPP) bitte VirtualHost manuell in Apache config anlegen (httpd-vhosts.conf). DocumentRoot sollte auf %CD% zeigen.

:: Fertig
echo.
if /I "%INSTALL_TYPE%"=="server" (
    echo Server-Instanz erfolgreich eingerichtet!
    echo API-Key: %API_KEY%
) else (
    echo Client-Instanz erfolgreich eingerichtet!
)
echo Weitere Informationen: README.md
endlocal
exit /b 0

