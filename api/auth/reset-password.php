<?php
// api/auth/reset-password.php
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed.', [], 405);
}

if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    jsonResponse('error', 'Invalid security token.', [], 403);
}

$email = sanitizeInput($_POST['email'] ?? '');
$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$passwordConf = $_POST['password_confirmation'] ?? '';

if (empty($email) || empty($token) || empty($password)) {
    jsonResponse('error', 'Missing required fields.');
}

if ($password !== $passwordConf) {
    jsonResponse('error', 'Passwords do not match.');
}

if (strlen($password) < 8) {
    jsonResponse('error', 'Password must be at least 8 characters long.');
}

try {
    $db = Database::getInstance()->getConnection();

    // Verify token
    $now = date('Y-m-d H:i:s');
    $stmt = $db->prepare("SELECT * FROM password_resets WHERE email = ? AND expires_at > ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$email, $now]);
    $resetRecord = $stmt->fetch();

    if (!$resetRecord || !verifyPassword($token, $resetRecord['token_hash'])) {
        jsonResponse('error', 'Invalid or expired password reset token.');
    }

    // Update password
    $hash = hashPassword($password);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ? AND status = 'active' AND deleted_at IS NULL");
    $stmt->execute([$hash, $email]);

    if ($stmt->rowCount() > 0) {
        // Expire all tokens for this email
        $stmt = $db->prepare("UPDATE password_resets SET expires_at = ? WHERE email = ?");
        $stmt->execute([$now, $email]);

        // Get user for activity log
        $stmt = $db->prepare("SELECT id, company_id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        activityLog('password_reset_completed', 'user', $user['id'], [], [], $user['company_id'], $user['id']);

        jsonResponse('success', 'Password reset successfully. You can now login.', ['redirect' => BASE_URL . 'auth/login.php']);
    } else {
        jsonResponse('error', 'User not found or account is inactive.');
    }

} catch (PDOException $e) {
    writeSysLog('error', "Password Reset Finalize DB Error: " . $e->getMessage());
    jsonResponse('error', 'An internal error occurred.');
}
