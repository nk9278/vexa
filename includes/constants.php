<?php
// includes/constants.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

// Application Meta
define('APP_NAME', 'VEXA');
define('APP_VERSION', '1.1.0');

// Environment
define('ENVIRONMENT', 'development'); // Options: development, testing, production

// Path Definitions
define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('LOGS_PATH', BASE_PATH . '/logs');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('TEMP_PATH', BASE_PATH . '/uploads/temp');

// URL Definitions (Assuming root execution)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . $host . '/');
define('ASSETS_URL', BASE_URL . 'assets/');

// Timezone
define('DEFAULT_TIMEZONE', 'UTC');

// Upload Constants
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024); // 50MB
define('ENCRYPTION_KEY', 'vexa_super_secret_sandbox_key_32');
