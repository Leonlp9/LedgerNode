-- ==========================================
-- Client Datenbankschema (SQLite)
-- Für private Daten auf Raspberry Pis
-- ==========================================

-- Private Konten
CREATE TABLE IF NOT EXISTS private_accounts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    type TEXT CHECK(type IN ('checking', 'savings', 'cash', 'credit')) DEFAULT 'checking',
    description TEXT,
    initial_balance REAL DEFAULT 0.00,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

-- Private Transaktionen
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
);

-- Kategorien
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    type TEXT CHECK(type IN ('income', 'expense')) NOT NULL,
    icon TEXT,
    color TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

-- Budget-Planung (optional)
CREATE TABLE IF NOT EXISTS budgets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category TEXT NOT NULL,
    amount REAL NOT NULL,
    period TEXT CHECK(period IN ('daily', 'weekly', 'monthly', 'yearly')) DEFAULT 'monthly',
    start_date TEXT NOT NULL,
    end_date TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

-- Indizes für Performance
CREATE INDEX IF NOT EXISTS idx_transactions_account ON private_transactions(account_id);
CREATE INDEX IF NOT EXISTS idx_transactions_type ON private_transactions(type);
CREATE INDEX IF NOT EXISTS idx_transactions_date ON private_transactions(date);
CREATE INDEX IF NOT EXISTS idx_transactions_category ON private_transactions(category);

-- Beispiel-Daten
INSERT INTO private_accounts (name, type, initial_balance) VALUES
    ('Girokonto', 'checking', 1500.00),
    ('Sparkonto', 'savings', 5000.00),
    ('Bargeld', 'cash', 200.00);

INSERT INTO categories (name, type, icon, color) VALUES
    ('Gehalt', 'income', '💰', '#10b981'),
    ('Lebensmittel', 'expense', '🛒', '#ef4444'),
    ('Transport', 'expense', '🚗', '#f59e0b'),
    ('Unterhaltung', 'expense', '🎬', '#8b5cf6'),
    ('Wohnung', 'expense', '🏠', '#ec4899'),
    ('Gesundheit', 'expense', '⚕️', '#06b6d4');

INSERT INTO private_transactions (account_id, type, amount, description, category, date) VALUES
    (1, 'income', 3000.00, 'Gehalt September', 'Gehalt', date('now')),
    (1, 'expense', 120.50, 'Wocheneinkauf REWE', 'Lebensmittel', date('now')),
    (1, 'expense', 45.00, 'Tankstelle', 'Transport', date('now', '-2 days')),
    (3, 'expense', 15.50, 'Kino-Tickets', 'Unterhaltung', date('now', '-3 days'));

-- Trigger für updated_at (simuliert ON UPDATE CURRENT_TIMESTAMP)
CREATE TRIGGER IF NOT EXISTS update_private_accounts_timestamp 
AFTER UPDATE ON private_accounts
BEGIN
    UPDATE private_accounts SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

CREATE TRIGGER IF NOT EXISTS update_private_transactions_timestamp 
AFTER UPDATE ON private_transactions
BEGIN
    UPDATE private_transactions SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;
