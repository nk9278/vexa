<?php
// includes/session.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

require_once __DIR__ . '/config.php';

// Session Security Configuration based on config.php
ini_set('session.name', $sessionConfig['name']);
ini_set('session.cookie_httponly', $sessionConfig['httponly'] ? 1 : 0);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', $sessionConfig['secure'] ? 1 : 0);
ini_set('session.cookie_samesite', $sessionConfig['samesite']);
ini_set('session.gc_maxlifetime', $sessionConfig['lifetime']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Regenerates session ID periodically to prevent fixation attacks
 */
function secureSessionRegenerate() {
    global $sessionConfig;

    $regenerateTime = $sessionConfig['regenerate_time'];

    if (isset($_SESSION['last_regeneration'])) {
        if (time() - $_SESSION['last_regeneration'] >= $regenerateTime) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    } else {
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * Checks for session timeout based on inactivity
 */
function checkSessionTimeout() {
    global $sessionConfig;

    $timeoutDuration = $sessionConfig['lifetime'];

    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] >= $timeoutDuration) {
            // Session expired
            session_unset();
            session_destroy();
            return false;
        }
    }

    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
    return true;
}

// Execute checks on file load
secureSessionRegenerate();
if (!checkSessionTimeout()) {
    // Determine if it's an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

    // Only redirect if it's not an auth page to prevent redirect loops
    $currentFile = basename($_SERVER['PHP_SELF']);
    $authFiles = ['login.php', 'forgot-password.php', 'reset-password.php', 'session-expired.php'];

    if (!in_array($currentFile, $authFiles)) {
        if ($isAjax) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.', 'redirect' => BASE_URL . 'auth/session-expired.php']);
            exit;
        } else {
            if (!headers_sent()) {
                header("Location: " . BASE_URL . "auth/session-expired.php");
                exit;
            }
        }
    }
}
