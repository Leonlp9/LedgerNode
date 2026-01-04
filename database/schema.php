<?php
/**
 * Datenbank-Schema Manager
 * Erstellt und aktualisiert automatisch alle benötigten Tabellen
 * Wird bei jedem Request ausgeführt (mit Caching für Performance)
 */

namespace App\Database;

use App\Core\Database;
use App\Core\Config;

class SchemaManager
{
    private Database $db;
    private static bool $initialized = false;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Initialisiert das Datenbankschema
     * Wird nur einmal pro Request ausgeführt
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        $manager = new self();

        if (Config::get('IS_SERVER')) {
            $manager->createServerTables();
        } else {
            $manager->createClientTables();
        }

        self::$initialized = true;
    }

    /**
     * Erstellt Server-Tabellen (MySQL)
     */
    private function createServerTables(): void
    {
        // Shared Accounts Tabelle
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS shared_accounts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                type ENUM('checking', 'savings', 'cash', 'credit') DEFAULT 'checking',
                description TEXT,
                initial_balance DECIMAL(10, 2) DEFAULT 0.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_type (type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Shared Transactions Tabelle
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS shared_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                account_id INT NOT NULL,
                type ENUM('income', 'expense') NOT NULL,
                amount DECIMAL(10, 2) NOT NULL,
                description TEXT NOT NULL,
                category VARCHAR(100),
                date DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (account_id) REFERENCES shared_accounts(id) ON DELETE CASCADE,
                INDEX idx_account (account_id),
                INDEX idx_type (type),
                INDEX idx_date (date),
                INDEX idx_category (category)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Shared Categories Tabelle
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS shared_categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                type ENUM('income', 'expense') NOT NULL,
                icon VARCHAR(10),
                color VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_type (type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // YouTube Income Tracking
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS shared_youtube_income (
                id INT AUTO_INCREMENT PRIMARY KEY,
                year INT NOT NULL,
                month INT NOT NULL CHECK (month >= 1 AND month <= 12),
                total_revenue DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                donations DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                members DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_month (year, month),
                INDEX idx_date (year, month)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // YouTube Expenses
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS shared_youtube_expenses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                amount DECIMAL(10, 2) NOT NULL,
                recipient VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                date DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_date (date),
                INDEX idx_recipient (recipient)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Sync Log für Clients
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS sync_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                client_id VARCHAR(100) NOT NULL,
                table_name VARCHAR(100) NOT NULL,
                action ENUM('push', 'pull') NOT NULL,
                record_count INT DEFAULT 0,
                synced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_client (client_id),
                INDEX idx_table (table_name),
                INDEX idx_synced_at (synced_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Erstellt Client-Tabellen (SQLite oder MySQL)
     */
    private function createClientTables(): void
    {
        $driver = Config::get('DB')['driver'] ?? 'sqlite';
        $isSqlite = ($driver === 'sqlite');

        // Private Accounts
        if ($isSqlite) {
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS private_accounts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    type TEXT CHECK(type IN ('checking', 'savings', 'cash', 'credit')) DEFAULT 'checking',
                    description TEXT,
                    initial_balance REAL DEFAULT 0.00,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } else {
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS private_accounts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    type ENUM('checking', 'savings', 'cash', 'credit') DEFAULT 'checking',
                    description TEXT,
                    initial_balance DECIMAL(10, 2) DEFAULT 0.00,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_type (type)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Private Transactions
        if ($isSqlite) {
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS private_transactions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    account_id INTEGER NOT NULL,
                    type TEXT CHECK(type IN ('income', 'expense')) NOT NULL,
                    amount REAL NOT NULL,
                    description TEXT NOT NULL,
                    category TEXT,
                    date TEXT NOT NULL,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (account_id) REFERENCES private_accounts(id) ON DELETE CASCADE
                )
            ");

            $this->db->execute("CREATE INDEX IF NOT EXISTS idx_transactions_account ON private_transactions(account_id)");
            $this->db->execute("CREATE INDEX IF NOT EXISTS idx_transactions_type ON private_transactions(type)");
            $this->db->execute("CREATE INDEX IF NOT EXISTS idx_transactions_date ON private_transactions(date)");
            $this->db->execute("CREATE INDEX IF NOT EXISTS idx_transactions_category ON private_transactions(category)");
        } else {
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS private_transactions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    account_id INT NOT NULL,
                    type ENUM('income', 'expense') NOT NULL,
                    amount DECIMAL(10, 2) NOT NULL,
                    description TEXT NOT NULL,
                    category VARCHAR(100),
                    date DATE NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (account_id) REFERENCES private_accounts(id) ON DELETE CASCADE,
                    INDEX idx_account (account_id),
                    INDEX idx_type (type),
                    INDEX idx_date (date),
                    INDEX idx_category (category)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Categories
        if ($isSqlite) {
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL UNIQUE,
                    type TEXT CHECK(type IN ('income', 'expense')) NOT NULL,
                    icon TEXT,
                    color TEXT,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } else {
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL UNIQUE,
                    type ENUM('income', 'expense') NOT NULL,
                    icon VARCHAR(10),
                    color VARCHAR(20),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_type (type)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Budgets
        if ($isSqlite) {
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS budgets (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    category TEXT NOT NULL,
                    amount REAL NOT NULL,
                    period TEXT CHECK(period IN ('daily', 'weekly', 'monthly', 'yearly')) DEFAULT 'monthly',
                    start_date TEXT NOT NULL,
                    end_date TEXT,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } else {
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS budgets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    category VARCHAR(100) NOT NULL,
                    amount DECIMAL(10, 2) NOT NULL,
                    period ENUM('daily', 'weekly', 'monthly', 'yearly') DEFAULT 'monthly',
                    start_date DATE NOT NULL,
                    end_date DATE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Private Invoices
        if ($isSqlite) {
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS private_invoices (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    type TEXT CHECK(type IN ('received', 'issued')) NOT NULL,
                    invoice_number TEXT,
                    invoice_date TEXT NOT NULL,
                    due_date TEXT,
                    amount REAL NOT NULL,
                    sender TEXT,
                    recipient TEXT,
                    description TEXT,
                    file_path TEXT,
                    file_name TEXT,
                    transaction_id INTEGER,
                    is_linked INTEGER DEFAULT 0,
                    status TEXT CHECK(status IN ('open', 'paid', 'overdue', 'cancelled')) DEFAULT 'open',
                    notes TEXT,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (transaction_id) REFERENCES private_transactions(id) ON DELETE SET NULL
                )
            ");

            $this->db->execute("CREATE INDEX IF NOT EXISTS idx_invoices_type ON private_invoices(type)");
            $this->db->execute("CREATE INDEX IF NOT EXISTS idx_invoices_status ON private_invoices(status)");
            $this->db->execute("CREATE INDEX IF NOT EXISTS idx_invoices_date ON private_invoices(invoice_date)");
        } else {
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS private_invoices (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    type ENUM('received', 'issued') NOT NULL,
                    invoice_number VARCHAR(100),
                    invoice_date DATE NOT NULL,
                    due_date DATE,
                    amount DECIMAL(10, 2) NOT NULL,
                    sender VARCHAR(255),
                    recipient VARCHAR(255),
                    description TEXT,
                    file_path VARCHAR(500),
                    file_name VARCHAR(255),
                    transaction_id INT,
                    is_linked TINYINT(1) DEFAULT 0,
                    status ENUM('open', 'paid', 'overdue', 'cancelled') DEFAULT 'open',
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (transaction_id) REFERENCES private_transactions(id) ON DELETE SET NULL,
                    INDEX idx_type (type),
                    INDEX idx_status (status),
                    INDEX idx_date (invoice_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    }
}

