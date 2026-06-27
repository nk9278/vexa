<?php
// includes/config.php
require_once __DIR__ . '/constants.php';

// Database Configuration
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'vexa_saas',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4'
];

// Error Reporting based on Environment
if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
    // Ensure errors are logged securely
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . 'php-error.log');
}
