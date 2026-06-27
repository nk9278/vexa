<?php
// includes/error-handler.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/logger.php';

/**
 * Custom Error Handler
 */
function vexaErrorHandler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return; // This error code is not included in error_reporting
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}

/**
 * Custom Exception Handler
 */
function vexaExceptionHandler($exception) {
    // Log the error securely
    $logMessage = "Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    writeSysLog('error', $logMessage);

    // If AJAX request, return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => ENVIRONMENT === 'development' ? $exception->getMessage() : 'An internal server error occurred.'
        ]);
        exit;
    }

    // Standard web request rendering
    http_response_code(500);
    if (ENVIRONMENT === 'development') {
        echo "<div style='font-family:sans-serif; padding: 20px; background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; border-radius: 5px;'>";
        echo "<h2>Fatal Error (Dev Mode)</h2>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . $exception->getFile() . " (Line " . $exception->getLine() . ")</p>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        echo "</div>";
    } else {
        // Redirect to safe 500 page in production safely
        require_once __DIR__ . '/helpers.php';
        redirect(BASE_URL . "errors/500.php");
    }
    exit;
}

// Register Handlers
set_error_handler("vexaErrorHandler");
set_exception_handler("vexaExceptionHandler");

// Set INI based on environment
if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/php_fatal.log');
