<?php
// includes/constants.php

define('APP_NAME', 'VEXA');
define('APP_VERSION', '1.0.0');

// Environment & Paths
define('ENVIRONMENT', 'development'); // development, production
define('BASE_PATH', dirname(__DIR__));

// Relative URLs (Assuming root domain execution for now, can be modified via config)
define('BASE_URL', '/');
define('ASSETS_URL', BASE_URL . 'assets/');

// Storage Paths
define('UPLOADS_PATH', BASE_PATH . '/uploads/');
define('LOGS_PATH', BASE_PATH . '/logs/');
