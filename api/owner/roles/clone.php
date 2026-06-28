<?php
// api/owner/roles/clone.php
require_once __DIR__ . '/../../../includes/functions.php';
requireOwner();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse('error', 'Method not allowed.', [], 405);
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) jsonResponse('error', 'Invalid security token.', [], 403);

$data = sanitizeInput($_POST);
if (empty($data['source_role_id']) || empty($data['new_role_name'])) {
    jsonResponse('error', 'Source role and new name are required.');
}

try {
    $db = Database::getInstance()->getConnection();

    // Verify source exists and belongs to company
    $stmt = $db->prepare("SELECT * FROM roles WHERE id = ? AND company_id = ?");
    $stmt->execute([$data['source_role_id'], $_SESSION['company_id']]);
    if (!$stmt->fetch()) jsonResponse('error', 'Source role not found.');

    $db->beginTransaction();

    $stmt = $db->prepare("INSERT INTO roles (company_id, role_name, display_name, is_system, status) VALUES (?, ?, ?, 0, 'active')");
    $stmt->execute([$_SESSION['company_id'], $data['new_role_name'], $data['new_role_name']]);
    $newRoleId = $db->lastInsertId();

    // Copy permissions
    $stmt = $db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
    $stmt->execute([$data['source_role_id']]);
    $perms = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $insertStmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
    foreach ($perms as $permId) {
        $insertStmt->execute([$newRoleId, $permId]);
    }

    $db->commit();
    activityLog('clone_role', 'role', $newRoleId, ['source_id' => $data['source_role_id']], $data, $_SESSION['company_id'], $_SESSION['user_id']);

    jsonResponse('success', 'Role cloned successfully.', ['redirect' => BASE_URL . 'owner/permissions/matrix.php?role_id=' . $newRoleId]);

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    writeSysLog('error', 'Clone Role Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error.');
}
