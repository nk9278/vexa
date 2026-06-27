<?php
// api/auth/login.php
require_once __DIR__ . '/../../includes/functions.php';

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed.', [], 405);
}

// CSRF check
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    jsonResponse('error', 'Invalid security token. Please refresh the page.', [], 403);
}

// Validation
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] == '1';

if (empty($email) || empty($password)) {
    jsonResponse('error', 'Email and password are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse('error', 'Please enter a valid email address.');
}

// Attempt Login
$result = attemptLogin($email, $password, $rememberMe);

if ($result['success']) {
    jsonResponse('success', 'Login successful.', ['redirect' => BASE_URL . 'dashboard/']);
} else {
    jsonResponse('error', $result['message']);
}
