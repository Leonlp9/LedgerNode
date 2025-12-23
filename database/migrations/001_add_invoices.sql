-- Migration: Add Invoices Support
-- Date: 2025-12-23
-- Description: Adds invoices tables for both client (SQLite) and server (MySQL)

-- For SQLite (Client) - Run this on client databases
-- Rechnungen (Invoices)
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
);

CREATE INDEX IF NOT EXISTS idx_invoices_type ON private_invoices(type);
CREATE INDEX IF NOT EXISTS idx_invoices_date ON private_invoices(invoice_date);
CREATE INDEX IF NOT EXISTS idx_invoices_linked ON private_invoices(is_linked);
CREATE INDEX IF NOT EXISTS idx_invoices_status ON private_invoices(status);
CREATE INDEX IF NOT EXISTS idx_invoices_transaction ON private_invoices(transaction_id);

CREATE TRIGGER IF NOT EXISTS update_private_invoices_timestamp 
AFTER UPDATE ON private_invoices
BEGIN
    UPDATE private_invoices SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

-- For MySQL (Server) - Run this on server database
-- Gemeinsame Rechnungen (Shared Invoices)
-- CREATE TABLE IF NOT EXISTS shared_invoices (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     type ENUM('received', 'issued') NOT NULL,
--     invoice_number VARCHAR(100),
--     invoice_date DATE NOT NULL,
--     due_date DATE,
--     amount DECIMAL(10, 2) NOT NULL,
--     sender VARCHAR(255),
--     recipient VARCHAR(255),
--     description TEXT,
--     file_path VARCHAR(500),
--     file_name VARCHAR(255),
--     transaction_id INT,
--     is_linked BOOLEAN DEFAULT FALSE,
--     status ENUM('open', 'paid', 'overdue', 'cancelled') DEFAULT 'open',
--     notes TEXT,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     FOREIGN KEY (transaction_id) REFERENCES shared_transactions(id) ON DELETE SET NULL,
--     INDEX idx_type (type),
--     INDEX idx_date (invoice_date),
--     INDEX idx_linked (is_linked),
--     INDEX idx_status (status),
--     INDEX idx_transaction (transaction_id)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
