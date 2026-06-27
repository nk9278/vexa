<?php
// includes/auth.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/logger.php';

/**
 * Attempts to log a user in.
 * Includes brute force protection.
 */
function attemptLogin($email, $password, $rememberMe = false) {
    try {
        $db = Database::getInstance()->getConnection();

        // 1. Brute Force Check (Max 5 attempts in 15 mins)
        // Use PHP time to remain DB agnostic between MySQL and SQLite
        $fifteenMinsAgo = date('Y-m-d H:i:s', time() - (15 * 60));
        $stmt = $db->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE email = ? AND attempt_time > ? AND success = 0");
        $stmt->execute([$email, $fifteenMinsAgo]);
        $attempts = $stmt->fetch()['attempts'];

        if ($attempts >= 5) {
            sysLog('warning', "Brute force lockout triggered for email: {$email}");
            return ['success' => false, 'message' => 'Too many failed login attempts. Please try again in 15 minutes.'];
        }

        // 2. Fetch User
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !verifyPassword($password, $user['password'])) {
            // Log failed attempt
            $stmt = $db->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
            $stmt->execute([$email, $_SERVER['REMOTE_ADDR'] ?? '']);
            activityLog('login_failed', 'user', $user ? $user['id'] : null, [], [], $user ? $user['company_id'] : null, $user ? $user['id'] : null);
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        // 3. Status Validations
        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Your account is currently ' . htmlspecialchars($user['status']) . '. Please contact support.'];
        }

        // 4. Successful Login - Establish Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['company_id'] = $user['company_id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['last_activity'] = time();
        secureSessionRegenerate(); // Prevent session fixation

        // Log success
        $stmt = $db->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 1)");
        $stmt->execute([$email, $_SERVER['REMOTE_ADDR'] ?? '']);
        activityLog('login_success', 'user', $user['id'], [], [], $user['company_id'], $user['id']);

        // 5. Remember Me Logic
        if ($rememberMe) {
            $token = generateRandomToken();
            $hash = hashPassword($token);
            $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days

            $stmt = $db->prepare("INSERT INTO user_tokens (user_id, token_hash, expires_at, type) VALUES (?, ?, ?, 'remember_me')");
            $stmt->execute([$user['id'], $hash, $expires]);

            $cookieValue = $user['id'] . ':' . $token;
            setcookie('remember_me', $cookieValue, time() + (30 * 24 * 60 * 60), '/', '', ENVIRONMENT === 'production', true);
        }

        return ['success' => true];

    } catch (PDOException $e) {
        writeSysLog('error', "Login DB Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An internal error occurred. Please try again later.'];
    }
}

/**
 * Validates 'Remember Me' cookie if session is dead.
 * Call early in bootstrap.
 */
function checkRememberMe() {
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
        $parts = explode(':', $_COOKIE['remember_me'], 2);

        if (count($parts) === 2) {
            list($userId, $token) = $parts;

            if ($userId && $token) {
            try {
                $db = Database::getInstance()->getConnection();
                $now = date('Y-m-d H:i:s');
                $stmt = $db->prepare("SELECT * FROM user_tokens WHERE user_id = ? AND type = 'remember_me' AND expires_at > ? LIMIT 1");
                $stmt->execute([$userId, $now]);
                $tokenRecord = $stmt->fetch();

                if ($tokenRecord && verifyPassword($token, $tokenRecord['token_hash'])) {
                    // Log them in
                    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND status = 'active' AND deleted_at IS NULL LIMIT 1");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch();

                    if ($user) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['company_id'] = $user['company_id'];
                        $_SESSION['role_id'] = $user['role_id'];
                        $_SESSION['last_activity'] = time();
                        secureSessionRegenerate();

                        // Rotate token for security
                        $newToken = generateRandomToken();
                        $newHash = hashPassword($newToken);
                        $stmt = $db->prepare("UPDATE user_tokens SET token_hash = ? WHERE id = ?");
                        $stmt->execute([$newHash, $tokenRecord['id']]);

                        $cookieValue = $user['id'] . ':' . $newToken;
                        setcookie('remember_me', $cookieValue, time() + (30 * 24 * 60 * 60), '/', '', ENVIRONMENT === 'production', true);
                    }
                }
            } catch (PDOException $e) {
                writeSysLog('error', "Remember Me DB Error: " . $e->getMessage());
            }
            }
        }
    }
}

/**
 * Logs out the current user.
 */
function logoutUser() {
    if (isset($_SESSION['user_id'])) {
        activityLog('logout', 'user', $_SESSION['user_id'], [], [], $_SESSION['company_id'] ?? null, $_SESSION['user_id']);

        // Remove remember me tokens from DB
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM user_tokens WHERE user_id = ? AND type = 'remember_me'");
            $stmt->execute([$_SESSION['user_id']]);
        } catch (PDOException $e) {
            // ignore
        }
    }

    // Clear cookie
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }

    session_unset();
    session_destroy();
}

/**
 * Returns true if a user is currently logged in.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Middleware: Requires the user to be logged in.
 * Redirects to login page if not.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . 'auth/login.php');
    }
}

/**
 * Middleware: Requires the user to be a GUEST (not logged in).
 * Redirects to dashboard if they are already logged in.
 */
function requireGuest() {
    if (isLoggedIn()) {
        redirect(BASE_URL . 'dashboard/'); // Standard dashboard routing logic will apply later
    }
}

/**
 * Middleware: Requires the user to be a Super Admin.
 * Redirects to 403 Access Denied if unauthorized.
 * (Assuming role_id 1 is Super Admin for the scope of this foundation)
 */
function requireSuperAdmin() {
    requireLogin();

    if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
        redirect(BASE_URL . 'errors/403.php');
    }
}

/**
 * Middleware: Requires the user to be an Owner of a Company.
 * Redirects to 403 Access Denied if unauthorized.
 */
function requireOwner() {
    requireLogin();

    // Super Admins shouldn't access tenant dashboards directly this way
    if ($_SESSION['role_id'] == 1) {
        redirect(BASE_URL . 'errors/403.php');
    }

    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT role_name FROM roles WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['role_id']]);
        $role = $stmt->fetch();

        if (!$role || strtolower($role['role_name']) !== 'owner') {
            redirect(BASE_URL . 'errors/403.php');
        }
    } catch (\PDOException $e) {
        redirect(BASE_URL . 'errors/500.php');
    }
}

/**
 * Generates a password reset token and saves it.
 */
function generatePasswordResetToken($email) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND status = 'active' AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = generateRandomToken(64);
            $hash = hashPassword($token);
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 Hour

            $stmt = $db->prepare("INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $hash, $expires]);

            activityLog('forgot_password_requested', 'user', $user['id'], [], [], null, $user['id']);
            return $token; // The raw token to be sent via email
        }
        return false;
    } catch (PDOException $e) {
        writeSysLog('error', "Reset Token DB Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Placeholder logic for future Account/Email Verification process.
 */
function accountVerificationPlaceholder($token) {
    // 1. Verify token exists in db
    // 2. Validate token is not expired
    // 3. Mark user 'email_verified_at' = NOW()
    // 4. Return success/failure boolean
    return true;
}
