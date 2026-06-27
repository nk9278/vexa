<?php
// includes/upload.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

require_once __DIR__ . '/constants.php';

/**
 * Handles secure file uploads ensuring tenant isolation and type safety.
 *
 * @param array $fileArray Typically $_FILES['input_name']
 * @param int $companyId For directory isolation
 * @param array $allowedMimeTypes Array of safe mime types
 * @param int $maxSize Max file size in bytes
 * @return array ['success' => bool, 'path' => string|null, 'error' => string|null, 'original_name' => string]
 */
function handleSecureUpload($fileArray, $companyId, $allowedMimeTypes = [], $maxSize = MAX_UPLOAD_SIZE) {

    // 1. Basic Errors Check
    if (!isset($fileArray['error']) || is_array($fileArray['error'])) {
        return ['success' => false, 'error' => 'Invalid parameters.'];
    }

    switch ($fileArray['error']) {
        case UPLOAD_ERR_OK: break;
        case UPLOAD_ERR_NO_FILE: return ['success' => false, 'error' => 'No file sent.'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE: return ['success' => false, 'error' => 'Exceeded filesize limit.'];
        default: return ['success' => false, 'error' => 'Unknown errors.'];
    }

    // 2. Size Validation
    if ($fileArray['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Exceeded filesize limit.'];
    }

    // 3. MIME Type Validation (Strict check using finfo, NOT relying on $_FILES['type'])
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($fileArray['tmp_name']);

    if (!empty($allowedMimeTypes) && !in_array($mimeType, $allowedMimeTypes, true)) {
        return ['success' => false, 'error' => 'Invalid file format.'];
    }

    // 3.5 Extension Validation (Critical for RCE prevention)
    $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));

    // Hardcoded blacklist of executable extensions
    $forbiddenExtensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'sh', 'bash', 'bat', 'cmd', 'js', 'html', 'htm'];
    if (in_array($ext, $forbiddenExtensions, true)) {
        return ['success' => false, 'error' => 'Executable files are strictly prohibited.'];
    }

    // 4. Directory Management & Isolation
    $companyDir = UPLOADS_PATH . '/' . intval($companyId) . '/' . date('Y/m');
    if (!is_dir($companyDir)) {
        if (!mkdir($companyDir, 0755, true)) {
            return ['success' => false, 'error' => 'Failed to create isolated upload directory.'];
        }
    }

    // 5. Safe Naming (Prevent directory traversal & overwrite)
    $ext = pathinfo($fileArray['name'], PATHINFO_EXTENSION);
    // Remove all non-alphanumeric characters from original name, limit length
    $safeName = preg_replace("/[^a-zA-Z0-9]+/", "-", pathinfo($fileArray['name'], PATHINFO_FILENAME));
    $safeName = substr($safeName, 0, 50);

    // Append unique hash to prevent guessing or collision
    $uniqueFileName = sprintf('%s_%s.%s', $safeName, bin2hex(random_bytes(8)), $ext);
    $destination = sprintf('%s/%s', $companyDir, $uniqueFileName);

    // 6. Move File
    if (!move_uploaded_file($fileArray['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Failed to move uploaded file.'];
    }

    // Return the relative path from the uploads directory for DB storage
    $relativePath = str_replace(UPLOADS_PATH . '/', '', $destination);

    return [
        'success' => true,
        'path' => $relativePath,
        'original_name' => $fileArray['name'],
        'mime_type' => $mimeType,
        'size' => $fileArray['size']
    ];
}
