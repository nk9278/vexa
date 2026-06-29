<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';

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

    if ($action === 'create') {
        requirePermission('edit_client');

        $note_type = trim($_POST['note_type'] ?? 'Internal');
        $content = trim($_POST['content'] ?? '');

        if (empty($content)) {
            jsonResponse('error', 'Note content is required.');
        }

        $stmt = $db->prepare("
            INSERT INTO client_notes (company_id, client_id, created_by, note_type, content)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$company_id, $client_id, $_SESSION['user_id'], $note_type, $content]);

        activityLog('add_note', 'client', $client_id, [], ['type' => $note_type]);
        jsonResponse('success', 'Note added successfully.');

    } elseif ($action === 'delete') {
        requirePermission('edit_client');

        $id = $_POST['id'] ?? null;
        if (!$id) jsonResponse('error', 'Note ID required.');

        $stmt = $db->prepare("DELETE FROM client_notes WHERE id = ? AND company_id = ? AND client_id = ?");
        $stmt->execute([$id, $company_id, $client_id]);

        activityLog('delete_note', 'client', $client_id, [], ['note_id' => $id]);
        jsonResponse('success', 'Note deleted successfully.');
    }

    jsonResponse('error', 'Invalid action.');

} catch (PDOException $e) {
    writeSysLog('error', 'Notes API Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
