-- ==========================================
-- Client Datenbankschema (MySQL-kompatibel)
-- Konvertiert aus der ursprünglichen SQLite-Variante
-- Für private Daten auf Raspberry Pis (MySQL / MariaDB)
-- ==========================================

-- Private Konten
CREATE TABLE IF NOT EXISTS private_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('checking', 'savings', 'cash', 'credit') NOT NULL DEFAULT 'checking',
    description TEXT,
    initial_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Private Transaktionen
CREATE TABLE IF NOT EXISTS private_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description VARCHAR(500) NOT NULL,
    category VARCHAR(255),
    date DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES private_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kategorien
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    type ENUM('income', 'expense') NOT NULL,
    icon VARCHAR(32),
    color VARCHAR(16),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Budget-Planung (optional)
CREATE TABLE IF NOT EXISTS budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    period ENUM('daily', 'weekly', 'monthly', 'yearly') NOT NULL DEFAULT 'monthly',
    start_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indizes für Performance (falls zusätzliche benötigt werden)
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
    (1, 'income', 3000.00, 'Gehalt September', 'Gehalt', CURDATE()),
    (1, 'expense', 120.50, 'Wocheneinkauf REWE', 'Lebensmittel', CURDATE()),
    (1, 'expense', 45.00, 'Tankstelle', 'Transport', DATE_SUB(CURDATE(), INTERVAL 2 DAY)),
    (3, 'expense', 15.50, 'Kino-Tickets', 'Unterhaltung', DATE_SUB(CURDATE(), INTERVAL 3 DAY));

-- Hinweis: In MySQL werden updated_at-Felder mit ON UPDATE CURRENT_TIMESTAMP gepflegt; eigene Trigger sind nicht nötig.

