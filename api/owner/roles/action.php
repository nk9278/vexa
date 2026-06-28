<?php
// api/owner/roles/action.php
require_once __DIR__ . '/../../../includes/functions.php';
requireOwner();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse('error', 'Method not allowed.', [], 405);
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) jsonResponse('error', 'Invalid security token.', [], 403);

$action = $_POST['action'] ?? '';
$ids = isset($_POST['ids']) ? (is_array($_POST['ids']) ? $_POST['ids'] : [$_POST['ids']]) : [];

if (empty($action) || empty($ids)) jsonResponse('error', 'Action and targets are required.');

try {
    $db = Database::getInstance()->getConnection();

    // Safety check: Filter out any IDs that don't belong to this company, or are system Owner roles.
    $inQuery = implode(',', array_fill(0, count($ids), '?'));
    $params = array_merge([$_SESSION['company_id']], $ids);

    $stmt = $db->prepare("SELECT id FROM roles WHERE company_id = ? AND id IN ($inQuery) AND role_name != 'Owner'");
    $stmt->execute($params);
    $validIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($validIds)) jsonResponse('error', 'No valid roles selected. (Cannot modify Owner role).');

    $validInQuery = implode(',', array_fill(0, count($validIds), '?'));
    $now = date('Y-m-d H:i:s');

    switch ($action) {
        case 'delete':
            $stmt = $db->prepare("UPDATE roles SET deleted_at = ?, status = 'archived' WHERE id IN ($validInQuery)");
            $execParams = array_merge([$now], $validIds);
            break;
        case 'activate':
            $stmt = $db->prepare("UPDATE roles SET status = 'active' WHERE id IN ($validInQuery)");
            $execParams = $validIds;
            break;
        case 'deactivate':
            $stmt = $db->prepare("UPDATE roles SET status = 'inactive' WHERE id IN ($validInQuery)");
            $execParams = $validIds;
            break;
        default:
            jsonResponse('error', 'Unknown action.');
    }

    $stmt->execute($execParams);

    foreach ($validIds as $id) {
        activityLog($action . '_role', 'role', $id, [], ['action' => $action], $_SESSION['company_id'], $_SESSION['user_id']);
    }

    jsonResponse('success', 'Bulk action completed successfully.');

} catch (PDOException $e) {
    writeSysLog('error', 'Role Bulk Action Error: ' . $e->getMessage());
    jsonResponse('error', 'A database error occurred.');
}
