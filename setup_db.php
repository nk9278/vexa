<?php
require_once __DIR__ . '/includes/functions.php';

try {
    $db = Database::getInstance()->getConnection();

    // Create minimal schema for authentication tests
    // Using simple types to remain agnostic between MySQL and SQLite for sandbox testing
    $db->exec("
        CREATE TABLE IF NOT EXISTS saas_plans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            plan_name VARCHAR(255) NOT NULL,
            plan_code VARCHAR(50) NOT NULL UNIQUE,
            description TEXT NULL,
            plan_type VARCHAR(50) DEFAULT 'monthly',
            price DECIMAL(10,2) DEFAULT 0.00,
            currency VARCHAR(10) DEFAULT 'USD',
            gst_percentage DECIMAL(5,2) DEFAULT 0.00,
            status VARCHAR(50) DEFAULT 'active',
            color_badge VARCHAR(20) DEFAULT 'neutral',
            sort_order INT DEFAULT 0,

            -- Usage Limits
            limit_employees INT DEFAULT 0,
            limit_managers INT DEFAULT 0,
            limit_crm_users INT DEFAULT 0,
            limit_clients INT DEFAULT 0,
            limit_projects INT DEFAULT 0,
            limit_tasks INT DEFAULT 0,
            limit_storage_mb INT DEFAULT 0,
            limit_gdrive_gb INT DEFAULT 0,
            limit_file_size_mb INT DEFAULT 0,
            limit_api_requests INT DEFAULT 0,
            limit_branches INT DEFAULT 0,
            limit_custom_roles INT DEFAULT 0,
            limit_departments INT DEFAULT 0,
            limit_notifications INT DEFAULT 0,

            -- Feature Flags (JSON string or boolean columns. Using booleans for easier SQL filtering)
            feature_gdrive TINYINT(1) DEFAULT 0,
            feature_attendance TINYINT(1) DEFAULT 0,
            feature_leave TINYINT(1) DEFAULT 0,
            feature_reports TINYINT(1) DEFAULT 0,
            feature_advanced_analytics TINYINT(1) DEFAULT 0,
            feature_export TINYINT(1) DEFAULT 0,
            feature_import TINYINT(1) DEFAULT 0,
            feature_custom_branding TINYINT(1) DEFAULT 0,
            feature_rest_api TINYINT(1) DEFAULT 0,
            feature_ai_assistant TINYINT(1) DEFAULT 0,
            feature_whatsapp TINYINT(1) DEFAULT 0,
            feature_client_portal TINYINT(1) DEFAULT 0,
            feature_vendor_portal TINYINT(1) DEFAULT 0,

            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL
        );

        CREATE TABLE IF NOT EXISTS saas_subscriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            plan_id INTEGER NOT NULL,
            license_key VARCHAR(100) NOT NULL UNIQUE,
            status VARCHAR(50) DEFAULT 'pending',
            start_date DATE NOT NULL,
            expiry_date DATE NOT NULL,
            trial_days INT DEFAULT 0,
            auto_expiry TINYINT(1) DEFAULT 1,
            remarks TEXT NULL,

            -- Webhook / Future online validation sync
            last_verified_at DATETIME NULL,

            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL,

            FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
            FOREIGN KEY (plan_id) REFERENCES saas_plans(id) ON DELETE RESTRICT
        );

        CREATE TABLE IF NOT EXISTS companies (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
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

        CREATE TABLE IF NOT EXISTS roles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            role_name VARCHAR(100) NOT NULL,
            display_name VARCHAR(100) NULL,
            is_system INTEGER DEFAULT 0,
            status VARCHAR(50) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            deleted_at DATETIME NULL
        );

        CREATE TABLE IF NOT EXISTS permissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            module VARCHAR(100) NOT NULL,
            permission_key VARCHAR(100) NOT NULL UNIQUE,
            description VARCHAR(255) NULL
        );

        CREATE TABLE IF NOT EXISTS role_permissions (
            role_id INTEGER NOT NULL,
            permission_id INTEGER NOT NULL,
            PRIMARY KEY (role_id, permission_id)
        );

        CREATE TABLE IF NOT EXISTS departments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            department_name VARCHAR(100) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS company_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            setting_key VARCHAR(100) NOT NULL,
            setting_value TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS master_data (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NOT NULL,
            category VARCHAR(50) NOT NULL,
            item_value VARCHAR(100) NOT NULL,
            color_badge VARCHAR(20) DEFAULT 'neutral',
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_id INTEGER NULL,
            role_id INTEGER NULL,
            department_id INTEGER NULL,
            first_name VARCHAR(100) NULL,
            last_name VARCHAR(100) NULL,
            mobile VARCHAR(50) NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            profile_image VARCHAR(255) NULL,
            last_login DATETIME NULL,
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
