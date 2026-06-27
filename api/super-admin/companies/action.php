<?php
// api/super-admin/companies/action.php
require_once __DIR__ . '/../../../includes/functions.php';
requireSuperAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed.', [], 405);
}

if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    jsonResponse('error', 'Invalid security token.', [], 403);
}

$action = $_POST['action'] ?? '';
$ids = isset($_POST['ids']) ? (is_array($_POST['ids']) ? $_POST['ids'] : [$_POST['ids']]) : [];

if (empty($action) || empty($ids)) {
    jsonResponse('error', 'Action and targets are required.');
}

try {
    $db = Database::getInstance()->getConnection();
    $inQuery = implode(',', array_fill(0, count($ids), '?'));
    $now = date('Y-m-d H:i:s');

    switch ($action) {
        case 'delete':
            $stmt = $db->prepare("UPDATE companies SET deleted_at = ?, status = 'deleted' WHERE id IN ($inQuery)");
            $params = array_merge([$now], $ids);
            break;
        case 'restore':
            $stmt = $db->prepare("UPDATE companies SET deleted_at = NULL, status = 'active' WHERE id IN ($inQuery)");
            $params = $ids;
            break;
        case 'suspend':
            $stmt = $db->prepare("UPDATE companies SET status = 'suspended' WHERE id IN ($inQuery)");
            $params = $ids;
            break;
        case 'activate':
            $stmt = $db->prepare("UPDATE companies SET status = 'active' WHERE id IN ($inQuery)");
            $params = $ids;
            break;
        default:
            jsonResponse('error', 'Unknown action.');
    }

    $stmt->execute($params);

    // Log the bulk action
    foreach ($ids as $id) {
        activityLog($action . '_company', 'company', $id, [], ['action' => $action], null, $_SESSION['user_id']);
    }

    jsonResponse('success', 'Action completed successfully.');

} catch (PDOException $e) {
    writeSysLog('error', 'Company Action DB Error: ' . $e->getMessage());
    jsonResponse('error', 'A database error occurred.');
}
