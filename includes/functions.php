<?php
// includes/functions.php
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/database.php';

/**
 * Send JSON Response for AJAX Handlers
 */
function sendJsonResponse($status, $message, $data = [], $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Placeholder Permission Check
 */
function hasPermission($permissionKey) {
    // Logic to verify user permission against active roles will go here
    return true;
}

/**
 * Placeholder File Upload Helper
 */
function handleFileUpload($fileArray, $destinationPath, $allowedMimeTypes = []) {
    // Logic for secure file uploads
    return false;
}
