<?php
// api/auth/forgot-password.php
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed.', [], 405);
}

if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    jsonResponse('error', 'Invalid security token.', [], 403);
}

$email = sanitizeInput($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse('error', 'Please enter a valid email address.');
}

// Rate limiting placeholder - prevent spamming forgot password
// ...

$token = generatePasswordResetToken($email);

if ($token) {
    // Send email
    $resetLink = BASE_URL . "auth/reset-password.php?token=" . urlencode($token) . "&email=" . urlencode($email);
    $htmlBody = "<p>Click the link to reset your password: <a href='{$resetLink}'>Reset Password</a></p>";

    sendMail($email, "Reset your VEXA password", $htmlBody);
}

// Always return success to prevent email enumeration
jsonResponse('success', 'If an account with that email exists, a reset link has been sent.');
