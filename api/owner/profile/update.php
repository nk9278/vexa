<?php
// api/owner/profile/update.php
require_once __DIR__ . '/../../../includes/functions.php';
requireOwner();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed.', [], 405);
}

if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    jsonResponse('error', 'Invalid security token.', [], 403);
}

$action = $_GET['action'] ?? 'profile';
$data = sanitizeInput($_POST);

try {
    $db = Database::getInstance()->getConnection();

    if ($action === 'profile') {
        $required = ['first_name'];
        $errors = validateRequiredFields($data, $required);
        if (!empty($errors)) jsonResponse('error', implode(' ', $errors));

        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, mobile = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND company_id = ?");
        $stmt->execute([
            $data['first_name'],
            $data['last_name'] ?? null,
            $data['mobile'] ?? null,
            $_SESSION['user_id'],
            $_SESSION['company_id']
        ]);

        activityLog('profile_updated', 'user', $_SESSION['user_id']);
        jsonResponse('success', 'Profile updated successfully.');

    } elseif ($action === 'password') {
        $required = ['current_password', 'new_password', 'confirm_password'];
        $errors = validateRequiredFields($data, $required);
        if (!empty($errors)) jsonResponse('error', implode(' ', $errors));

        if ($data['new_password'] !== $data['confirm_password']) {
            jsonResponse('error', 'New passwords do not match.');
        }

        if (strlen($data['new_password']) < 8) {
            jsonResponse('error', 'New password must be at least 8 characters.');
        }

        $stmt = $db->prepare("SELECT password FROM users WHERE id = ? AND company_id = ?");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['company_id']]);
        $user = $stmt->fetch();

        if (!$user || !verifyPassword($data['current_password'], $user['password'])) {
            jsonResponse('error', 'Current password is incorrect.');
        }

        $hash = hashPassword($data['new_password']);
        $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND company_id = ?");
        $stmt->execute([$hash, $_SESSION['user_id'], $_SESSION['company_id']]);

        activityLog('password_changed', 'user', $_SESSION['user_id']);
        jsonResponse('success', 'Password updated successfully.');
    } else {
        jsonResponse('error', 'Invalid action.');
    }

} catch (PDOException $e) {
    writeSysLog('error', 'Owner Profile Update Error: ' . $e->getMessage());
    jsonResponse('error', 'A database error occurred.');
}
