-- ==========================================
-- Server Datenbankschema (MySQL)
-- Für zentrale Shared-Daten
-- ==========================================

-- Gemeinsame Konten
CREATE TABLE IF NOT EXISTS shared_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('general', 'savings', 'project') DEFAULT 'general',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Gemeinsame Transaktionen
CREATE TABLE IF NOT EXISTS shared_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description VARCHAR(500) NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES shared_accounts(id) ON DELETE CASCADE,
    INDEX idx_account (account_id),
    INDEX idx_type (type),
    INDEX idx_date (date),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API-Logs (optional, für Monitoring)
CREATE TABLE IF NOT EXISTS api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    action VARCHAR(100) NOT NULL,
    success BOOLEAN DEFAULT TRUE,
    error_message TEXT,
    request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip (ip_address),
    INDEX idx_action (action),
    INDEX idx_time (request_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Beispiel-Daten einfügen
INSERT INTO shared_accounts (name, type, description) VALUES
    ('Haushaltskasse', 'general', 'Gemeinsame Haushaltskasse'),
    ('Urlaub 2025', 'savings', 'Urlaubssparkonto'),
    ('Renovierung', 'project', 'Wohnungsrenovierung');

INSERT INTO shared_transactions (account_id, type, amount, description, date) VALUES
    (1, 'income', 2000.00, 'Monatliche Einzahlung', CURDATE()),
    (1, 'expense', 450.50, 'Lebensmittel', CURDATE()),
    (2, 'income', 500.00, 'Urlaubssparen', CURDATE()),
    (3, 'expense', 1200.00, 'Farbe und Material', DATE_SUB(CURDATE(), INTERVAL 5 DAY));
