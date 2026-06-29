<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';

requireLogin();
requirePermission('delete_client');
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
    $ids = $_POST['ids'] ?? [];

    if (empty($action) || empty($ids) || !is_array($ids)) {
        jsonResponse('error', 'Invalid request parameters.');
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $params = array_merge([$company_id], $ids);

    $stmt = $db->prepare("SELECT id FROM clients WHERE company_id = ? AND id IN ($placeholders)");
    $stmt->execute($params);
    $validIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($validIds)) {
        jsonResponse('error', 'No valid records found.');
    }

    $validPlaceholders = implode(',', array_fill(0, count($validIds), '?'));

    $db->beginTransaction();

    switch ($action) {
        case 'archive':
            $stmt = $db->prepare("UPDATE clients SET status = 'archived', updated_at = ? WHERE company_id = ? AND id IN ($validPlaceholders)");
            $execParams = array_merge([date('Y-m-d H:i:s'), $company_id], $validIds);
            $stmt->execute($execParams);
            activityLog('bulk_archive', 'client', null, [], ['count' => count($validIds)]);
            $message = 'Selected clients archived successfully.';
            break;

        case 'restore':
            $stmt = $db->prepare("UPDATE clients SET status = 'active', updated_at = ? WHERE company_id = ? AND id IN ($validPlaceholders)");
            $execParams = array_merge([date('Y-m-d H:i:s'), $company_id], $validIds);
            $stmt->execute($execParams);
            activityLog('bulk_restore', 'client', null, [], ['count' => count($validIds)]);
            $message = 'Selected clients restored successfully.';
            break;

        case 'delete':
            $stmt = $db->prepare("UPDATE clients SET deleted_at = ? WHERE company_id = ? AND id IN ($validPlaceholders)");
            $execParams = array_merge([date('Y-m-d H:i:s'), $company_id], $validIds);
            $stmt->execute($execParams);
            activityLog('bulk_delete', 'client', null, [], ['count' => count($validIds)]);
            $message = 'Selected clients deleted permanently.';
            break;

        case 'pause':
            $stmt = $db->prepare("UPDATE clients SET status = 'paused', updated_at = ? WHERE company_id = ? AND id IN ($validPlaceholders)");
            $execParams = array_merge([date('Y-m-d H:i:s'), $company_id], $validIds);
            $stmt->execute($execParams);
            activityLog('bulk_pause', 'client', null, [], ['count' => count($validIds)]);
            $message = 'Selected clients paused successfully.';
            break;

        case 'activate':
            $stmt = $db->prepare("UPDATE clients SET status = 'active', updated_at = ? WHERE company_id = ? AND id IN ($validPlaceholders)");
            $execParams = array_merge([date('Y-m-d H:i:s'), $company_id], $validIds);
            $stmt->execute($execParams);
            activityLog('bulk_activate', 'client', null, [], ['count' => count($validIds)]);
            $message = 'Selected clients activated successfully.';
            break;

        default:
            $db->rollBack();
            jsonResponse('error', 'Invalid action.');
    }

    $db->commit();
    jsonResponse('success', $message);

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    writeSysLog('error', 'Client Bulk Action Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
