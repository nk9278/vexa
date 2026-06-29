<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';
require_once '../../../includes/upload.php';

requireLogin();
requirePermission('edit_team'); // Added explicit permission check for security
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) { jsonResponse('error', 'Invalid CSRF token.', null, 403); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Invalid method', null, 405);
}

try {
    $company_id = $_SESSION['company_id'];

    if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] === UPLOAD_ERR_NO_FILE) {
         jsonResponse('error', 'No file uploaded.');
    }

    // MIME Types for Web Images
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

    // handleSecureUpload automatically handles creating the tenant folder
    $result = handleSecureUpload($_FILES['profile_photo'], $company_id, $allowedMimes, 2097152); // 2MB limit

    if (!$result['success']) {
        jsonResponse('error', $result['error']);
    }

    // The result from handleSecureUpload returns relative paths from root, like '/uploads/tenant/file.ext'
    $relativePath = $result['path'];
    // Normalize path separators for web
    $relativePath = str_replace('\\', '/', $relativePath);

    // Ensure it starts with a slash
    if (strpos($relativePath, '/') !== 0) {
        $relativePath = '/' . $relativePath;
    }

    jsonResponse('success', 'Photo uploaded successfully.', ['path' => $relativePath]);

} catch (Exception $e) {
    writeSysLog('error', 'Team Photo Upload Error: ' . $e->getMessage());
    jsonResponse('error', 'An error occurred during upload.');
}
