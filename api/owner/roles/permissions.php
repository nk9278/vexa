<?php
// api/owner/roles/permissions.php
require_once __DIR__ . '/../../../includes/functions.php';
requireOwner();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse('error', 'Method not allowed.', [], 405);
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) jsonResponse('error', 'Invalid security token.', [], 403);

$roleId = $_POST['role_id'] ?? 0;
// Checkboxes only send data if checked, so we default to empty array if none checked
$permissions = $_POST['permissions'] ?? [];

if (empty($roleId)) jsonResponse('error', 'Role ID required.');

try {
    $db = Database::getInstance()->getConnection();

    // Check owner access AND ensure they aren't locking themselves out
    $stmt = $db->prepare("SELECT role_name, is_system FROM roles WHERE id = ? AND company_id = ?");
    $stmt->execute([$roleId, $_SESSION['company_id']]);
    $role = $stmt->fetch();

    if (!$role) jsonResponse('error', 'Role not found or access denied.');
    if ($role['role_name'] === 'Owner') {
        jsonResponse('error', 'The Master Owner role permissions cannot be modified.');
    }

    $db->beginTransaction();

    // Wipe existing
    $stmt = $db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
    $stmt->execute([$roleId]);

    // Insert new
    if (!empty($permissions)) {
        $insertStmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($permissions as $permId) {
            $insertStmt->execute([$roleId, intval($permId)]);
        }
    }

    $db->commit();
    activityLog('update_permissions', 'role', $roleId, [], ['count' => count($permissions)], $_SESSION['company_id'], $_SESSION['user_id']);

    jsonResponse('success', 'Permission matrix updated successfully.');

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    writeSysLog('error', 'Update Matrix Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error.');
}
