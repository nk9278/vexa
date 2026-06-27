<?php
// api/super-admin/plans/action.php
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
            $stmt = $db->prepare("UPDATE saas_plans SET deleted_at = ?, status = 'archived' WHERE id IN ($inQuery)");
            $params = array_merge([$now], $ids);
            break;
        case 'activate':
            $stmt = $db->prepare("UPDATE saas_plans SET status = 'active' WHERE id IN ($inQuery)");
            $params = $ids;
            break;
        case 'deactivate':
            $stmt = $db->prepare("UPDATE saas_plans SET status = 'inactive' WHERE id IN ($inQuery)");
            $params = $ids;
            break;
        default:
            jsonResponse('error', 'Unknown action.');
    }

    $stmt->execute($params);

    foreach ($ids as $id) {
        activityLog($action . '_plan', 'saas_plan', $id, [], ['action' => $action], null, $_SESSION['user_id']);
    }

    jsonResponse('success', 'Action completed successfully.');

} catch (PDOException $e) {
    writeSysLog('error', 'Plan Action DB Error: ' . $e->getMessage());
    jsonResponse('error', 'A database error occurred.');
}
