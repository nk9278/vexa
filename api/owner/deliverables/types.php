<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';

requireLogin();
requirePermission('manage_deliverable_types');
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
    $name = trim($_POST['name'] ?? '');
    $id = $_POST['id'] ?? null;

    if ($action === 'create') {
        if (empty($name)) {
            jsonResponse('error', 'Type name is required.');
        }

        // Check if exists
        $stmt = $db->prepare("SELECT id FROM deliverable_types WHERE company_id = ? AND LOWER(name) = LOWER(?)");
        $stmt->execute([$company_id, $name]);
        if ($stmt->fetch()) {
            jsonResponse('error', 'Deliverable type already exists.');
        }

        $stmt = $db->prepare("INSERT INTO deliverable_types (company_id, name) VALUES (?, ?)");
        $stmt->execute([$company_id, $name]);

        jsonResponse('success', 'Deliverable type created successfully.', ['id' => $db->lastInsertId(), 'name' => $name]);

    } elseif ($action === 'delete') {
        if (!$id) jsonResponse('error', 'Type ID is required.');

        // Prevent deleting if in use
        $stmtCheck = $db->prepare("SELECT id FROM deliverables WHERE company_id = ? AND deliverable_type_id = ? LIMIT 1");
        $stmtCheck->execute([$company_id, $id]);
        if ($stmtCheck->fetch()) {
            jsonResponse('error', 'Cannot delete type because it is in use by existing deliverables.');
        }

        $stmt = $db->prepare("DELETE FROM deliverable_types WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $company_id]);

        jsonResponse('success', 'Deliverable type deleted successfully.');
    }

    jsonResponse('error', 'Invalid action.');

} catch (PDOException $e) {
    writeSysLog('error', 'Deliverable Type API Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
