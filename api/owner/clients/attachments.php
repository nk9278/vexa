<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';
require_once '../../../includes/upload.php';

requireLogin();
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    jsonResponse('error', 'Invalid CSRF token.', null, 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Invalid method', null, 405);
}

try {
    $db = Database::getInstance()->getConnection();
    $company_id = $_SESSION['company_id'];

    $action = $_POST['action'] ?? '';
    $client_id = $_POST['client_id'] ?? null;

    if (!$client_id) {
        jsonResponse('error', 'Client ID is required.');
    }

    $stmtCheck = $db->prepare("SELECT id FROM clients WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmtCheck->execute([$client_id, $company_id]);
    if (!$stmtCheck->fetch()) {
        jsonResponse('error', 'Client not found or access denied.');
    }

    if ($action === 'upload') {
        requirePermission('edit_client');

        if (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] === UPLOAD_ERR_NO_FILE) {
             jsonResponse('error', 'No file uploaded.');
        }

        $file_type = $_POST['file_type'] ?? 'Document';

        // Allowed Mimes for Documents and Images
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv'
        ];

        // handleSecureUpload handles tenant isolation automatically
        $result = handleSecureUpload($_FILES['attachment'], $company_id, $allowedMimes, 5242880); // 5MB limit

        if (!$result['success']) {
            jsonResponse('error', $result['error']);
        }

        $relativePath = $result['path'];
        $relativePath = str_replace('\\', '/', $relativePath);
        if (strpos($relativePath, '/') !== 0) $relativePath = '/' . $relativePath;

        $stmt = $db->prepare("
            INSERT INTO client_attachments (company_id, client_id, uploaded_by, file_name, file_path, file_type, file_size)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $company_id, $client_id, $_SESSION['user_id'],
            $_FILES['attachment']['name'], $relativePath, $file_type, $_FILES['attachment']['size']
        ]);

        activityLog('upload_attachment', 'client', $client_id, [], ['file' => $_FILES['attachment']['name']]);
        jsonResponse('success', 'File uploaded successfully.');

    } elseif ($action === 'delete') {
        requirePermission('edit_client');

        $id = $_POST['id'] ?? null;
        if (!$id) jsonResponse('error', 'Attachment ID required.');

        $stmt = $db->prepare("SELECT file_path FROM client_attachments WHERE id = ? AND company_id = ? AND client_id = ?");
        $stmt->execute([$id, $company_id, $client_id]);
        $file = $stmt->fetch();

        if ($file) {
            $fullPath = ROOT_PATH . ltrim($file['file_path'], '/');
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            $db->prepare("DELETE FROM client_attachments WHERE id = ?")->execute([$id]);
            activityLog('delete_attachment', 'client', $client_id, [], ['attachment_id' => $id]);
            jsonResponse('success', 'Attachment deleted.');
        } else {
            jsonResponse('error', 'File not found.');
        }
    }

    jsonResponse('error', 'Invalid action.');

} catch (Exception $e) {
    writeSysLog('error', 'Attachment API Error: ' . $e->getMessage());
    jsonResponse('error', 'Server error occurred.');
}
