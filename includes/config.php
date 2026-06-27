<?php
// includes/config.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

require_once __DIR__ . '/constants.php';

// Set Default Timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// Database Configuration (Centralized array for easy scaling/multi-tenant mapping)
$dbConfig = [
    'default' => [
        'host'      => 'localhost',
        'dbname'    => 'vexa_saas',
        'user'      => 'root',
        'pass'      => '',
        'charset'   => 'utf8mb4',
        'driver'    => 'sqlite', // Added fallback since sandbox lacks MySQL daemon
        'path'      => BASE_PATH . '/database/vexa.db'
    ]
];

// Session Configuration
$sessionConfig = [
    'name'           => 'VEXA_SESSION',
    'lifetime'       => 28800, // 8 Hours
    'path'           => '/',
    'domain'         => '', // Can be locked to a specific domain in production
    'secure'         => (ENVIRONMENT === 'production'), // True requires HTTPS
    'httponly'       => true, // Prevent JS access to session ID
    'samesite'       => 'Lax',
    'regenerate_time'=> 1800 // Regenerate ID every 30 minutes
];

// Mail Configuration (Placeholder for Phase 11)
$mailConfig = [
    'driver'    => 'smtp',
    'host'      => 'smtp.mailtrap.io',
    'port'      => 2525,
    'username'  => '',
    'password'  => '',
    'from_name' => APP_NAME,
    'from_email'=> 'no-reply@vexa.app'
];
