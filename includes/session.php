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
if (!checkSessionTimeout() && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    // If we are not on the login page and session timed out, redirect.
    // In Phase 11 we don't have a login page yet, so we just clear it.
}
