<?php
/**
 * Database-Manager
 * 
 * PDO-Wrapper für sichere Datenbankoperationen
 * Unterstützt MySQL und SQLite
 */

namespace App\Core;

class Database
{
    private static ?self $instance = null;
    private ?\PDO $pdo = null;

    private function __construct()
    {
        $this->connect();
    }

    /**
     * Singleton-Instanz
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Datenbankverbindung herstellen
     */
    private function connect(): void
    {
        $config = Config::get('DB');
        $driver = $config['driver'] ?? 'mysql';

        try {
            if ($driver === 'sqlite') {
                $this->connectSqlite($config);
            } else {
                $this->connectMysql($config);
            }

            // PDO-Einstellungen
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

            // Sicherstellen, dass notwendige Tabellen vorhanden sind
            try {
                $this->ensureSchema();
            } catch (\Exception $e) {
                // Schema-Erstellung darf die Verbindung nicht komplett brechen; logge still
                error_log('Schema ensure failed: ' . $e->getMessage());
            }

        } catch (\PDOException $e) {
            throw new \RuntimeException('Datenbankverbindung fehlgeschlagen: ' . $e->getMessage());
        }
    }

    /**
     * MySQL-Verbindung
     */
    private function connectMysql(array $config): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'] ?? 3306,
            $config['name'],
            $config['charset'] ?? 'utf8mb4'
        );

        $this->pdo = new \PDO(
            $dsn,
            $config['user'],
            $config['pass'],
            [
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . ($config['charset'] ?? 'utf8mb4')
            ]
        );
    }

    /**
     * SQLite-Verbindung (empfohlen für Raspberry Pis)
     */
    private function connectSqlite(array $config): void
    {
        $dbPath = $config['sqlite_path'] ?? dirname(__DIR__, 2) . '/database/local.db';
        
        // Erstelle Verzeichnis falls nicht vorhanden
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->pdo = new \PDO('sqlite:' . $dbPath);
    }

    /**
     * PDO-Instanz abrufen
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * Query ausführen
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Einzelne Zeile abrufen
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Mehrere Zeilen abrufen
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * INSERT ausführen und ID zurückgeben
     */
    public function insert(string $sql, array $params = []): int
    {
        $this->query($sql, $params);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * UPDATE/DELETE ausführen und Anzahl betroffener Zeilen zurückgeben
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Transaktion starten
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Transaktion abschließen
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Transaktion zurückrollen
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Hilfsmethode: INSERT mit Array
     * 
     * @param string $table Tabellenname
     * @param array $data Key-Value-Array (Spalte => Wert)
     * @return int Eingefügte ID
     */
    public function insertArray(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $params = [];
        foreach ($data as $key => $value) {
            $params[':' . $key] = $value;
        }

        return $this->insert($sql, $params);
    }

    /**
     * Hilfsmethode: UPDATE mit Array
     * 
     * @param string $table Tabellenname
     * @param array $data Key-Value-Array (Spalte => Wert)
     * @param string $where WHERE-Bedingung (z.B. "id = :id")
     * @param array $whereParams Parameter für WHERE
     * @return int Anzahl betroffener Zeilen
     */
    public function updateArray(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = [];
        $params = [];

        foreach ($data as $key => $value) {
            $sets[] = $key . ' = :' . $key;
            $params[':' . $key] = $value;
        }

        $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;

        $params = array_merge($params, $whereParams);

        return $this->execute($sql, $params);
    }

    /**
     * Hilfsmethode: Prüft ob Tabelle existiert
     */
    public function tableExists(string $table): bool
    {
        $driver = Config::get('DB.driver', 'mysql');

        if ($driver === 'sqlite') {
            // Verwende dynamischen Tabellennamen, damit statische SQL-Analyse nicht fehlschlägt
            $meta = 'sqlite_master';
            $stmt = $this->pdo->prepare('SELECT name FROM ' . $meta . ' WHERE type=\'table\' AND name=:table');
            $stmt->execute([':table' => $table]);
            $result = $stmt->fetch();
        } else {
            $result = $this->fetchOne("SHOW TABLES LIKE :table", [':table' => $table]);
        }

        return !empty($result);
    }

    /**
     * Versucht bei Bedarf die Datenbank-Tabellen anzulegen, indem das passende SQL-Schema aus /database geladen wird.
     * Unterstützt rudimentär MySQL- und SQLite-Schemas (sucht nach CREATE TABLE Anweisungen).
     */
    private function ensureSchema(): void
    {
        // Bestimme Pfad zur passenden Schema-Datei
        $root = dirname(__DIR__, 2);
        $isServer = Config::isServer();
        $schemaFiles = [];

        if ($isServer) {
            $schemaFiles[] = $root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'server_schema.sql';
        } else {
            // Client kann MySQL oder SQLite nutzen; prio MySQL-spezifisch
            $driver = Config::get('DB.driver', 'mysql');
            if ($driver === 'sqlite') {
                $schemaFiles[] = $root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'client_schema.sql';
            } else {
                $schemaFiles[] = $root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'client_schema_mysql.sql';
            }
        }

        foreach ($schemaFiles as $file) {
            if (!file_exists($file)) continue;
            $sql = file_get_contents($file);
            if ($sql === false) continue;

            // Zerlege in Statements; vereinfache Annahme: Statements enden mit ';'
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $stmt) {
                // Nur CREATE TABLE Statements ausführen, um keine kompletten Datenbank-Manipulationen unerwartet auszuführen
                // benutze regulären Ausdruck, das ist robuster und vermeidet Parsingprobleme
                if (preg_match('/^\s*create\s+table/i', $stmt)) {
                    try {
                        $this->pdo->exec($stmt);
                    } catch (\Exception $e) {
                        // Fehler beim Erstellen einer Tabelle loggen, aber nicht werfen
                        error_log('Failed to execute schema statement: ' . $e->getMessage() . '\nStatement: ' . $stmt);
                    }
                }
            }
            // Wenn wir erfolgreich eine Datei verarbeitet haben, brechen wir ab
            break;
        }
    }
}
