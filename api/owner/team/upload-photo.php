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
verifyCsrfToken();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Invalid method', null, 405);
}

try {
    $company_id = $_SESSION['company_id'];

    if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] === UPLOAD_ERR_NO_FILE) {
         jsonResponse('error', 'No file uploaded.');
    }

    $uploadDir = UPLOADS_PATH . '/' . $company_id . '/photos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        file_put_contents($uploadDir . '/.gitkeep', '');
        file_put_contents($uploadDir . '/index.html', ''); // Prevent directory listing
    }

    $result = handleUpload($_FILES['profile_photo'], $uploadDir, ['jpg', 'jpeg', 'png', 'webp'], 2097152); // 2MB limit

    if (!$result['success']) {
        jsonResponse('error', $result['error']);
    }

    // Return the relative path so the frontend can preview it, and submit it with the main form.
    // The main form logic will save this path into the DB.
    $relativePath = str_replace(ROOT_PATH, '', $result['path']);
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
