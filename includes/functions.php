<?php
// includes/functions.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

/**
 * Master Foundation Include File
 *
 * Including this single file boot-straps the entire core foundation of VEXA.
 * It establishes paths, configuration, session security, database singletons,
 * error handlers, and brings all helpers into scope.
 */

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/error-handler.php'; // Initializes error tracking early
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/mail.php';

// Apply security headers to every page that includes this master file
setSecurityHeaders();

/**
 * Generic Input Validation Wrapper
 */
function validateRequiredFields($postData, $requiredKeys) {
    $errors = [];
    foreach ($requiredKeys as $key) {
        if (!isset($postData[$key]) || trim((string)$postData[$key]) === '') {
            $errors[] = "The {$key} field is required.";
        }
    }
    return $errors;
}
