<?php
require_once __DIR__ . '/includes/functions.php';

try {
    $db = Database::getInstance()->getConnection();

    // Create minimal schema for authentication tests
    // Using simple types to remain agnostic between MySQL and SQLite for sandbox testing
    $db->exec("
        CREATE TABLE IF NOT EXISTS companies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company_name VARCHAR(255) NOT NULL,
            company_code VARCHAR(50) NOT NULL UNIQUE,
            company_logo VARCHAR(255) NULL,
            business_type VARCHAR(100) NULL,
            industry VARCHAR(100) NULL,
            owner_name VARCHAR(255) NOT NULL,
            owner_email VARCHAR(255) NOT NULL UNIQUE,
            owner_mobile VARCHAR(50) NULL,
            office_phone VARCHAR(50) NULL,
            gst_number VARCHAR(100) NULL,
            pan_number VARCHAR(100) NULL,
            website VARCHAR(255) NULL,
            address TEXT NULL,
            city VARCHAR(100) NULL,
            state VARCHAR(100) NULL,
            country VARCHAR(100) NULL,
            postal_code VARCHAR(20) NULL,
            timezone VARCHAR(100) DEFAULT 'UTC',
            currency VARCHAR(10) DEFAULT 'USD',
            language VARCHAR(10) DEFAULT 'en',
            status VARCHAR(50) DEFAULT 'active',
            remarks TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL
        );

        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NULL,
            role_id INTEGER NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            status VARCHAR(50) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL
        );

        CREATE TABLE IF NOT EXISTS login_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP,
            success INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS user_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            type VARCHAR(50) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS password_resets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email VARCHAR(255) NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS activity_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NULL,
            user_id INTEGER NULL,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(100) NOT NULL,
            entity_id INTEGER NULL,
            old_payload TEXT NULL,
            new_payload TEXT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Insert test user
    $testEmail = 'admin@vexa.app';
    $testPass = hashPassword('securepassword123');

    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$testEmail]);
    if (!$stmt->fetch()) {
        $stmt = $db->prepare("INSERT INTO users (company_id, role_id, email, password) VALUES (1, 1, ?, ?)");
        $stmt->execute([$testEmail, $testPass]);
        echo "Test user created: {$testEmail} / securepassword123\n";
    } else {
        echo "Test user already exists.\n";
    }

    echo "Database setup complete.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
