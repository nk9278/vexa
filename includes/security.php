<?php
// includes/security.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

require_once __DIR__ . '/session.php';

/**
 * Security Headers - Call early in application lifecycle
 */
function setSecurityHeaders() {
    if (headers_sent()) return;

    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    // header("Content-Security-Policy: default-src 'self';"); // Can be enabled in prod after CDN assets are localized
}

/**
 * Output Escaping (XSS Prevention)
 */
function esc($string) {
    if (is_null($string)) return '';
    return htmlspecialchars((string)$string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Input Sanitization (Basic string cleaner before DB/logic)
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitizeInput($value);
        }
    } else {
        $input = trim($input);
        $input = stripslashes($input);
    }
    return $input;
}

/**
 * CSRF Token Generator
 */
function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Token Verifier
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Cryptographically Secure Token Generator
 */
function generateRandomToken($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Password Hashing Helper
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Password Verification Helper
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
