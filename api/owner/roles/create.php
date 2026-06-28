<?php
// api/owner/roles/create.php
require_once __DIR__ . '/../../../includes/functions.php';
requireOwner();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse('error', 'Method not allowed.', [], 405);
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) jsonResponse('error', 'Invalid security token.', [], 403);

$data = sanitizeInput($_POST);
$required = ['role_name'];
$errors = validateRequiredFields($data, $required);
if (!empty($errors)) jsonResponse('error', implode(' ', $errors));

try {
    $db = Database::getInstance()->getConnection();

    // Check uniqueness
    $stmt = $db->prepare("SELECT id FROM roles WHERE company_id = ? AND role_name = ? AND deleted_at IS NULL");
    $stmt->execute([$_SESSION['company_id'], $data['role_name']]);
    if ($stmt->fetch()) {
        jsonResponse('error', 'A role with this name already exists.');
    }

    $db->beginTransaction();

    $stmt = $db->prepare("INSERT INTO roles (company_id, role_name, display_name, is_system, status) VALUES (?, ?, ?, 0, 'active')");
    $stmt->execute([$_SESSION['company_id'], $data['role_name'], $data['role_name']]);
    $newRoleId = $db->lastInsertId();

    // Clone template if provided
    if (!empty($data['template_id'])) {
        $stmt = $db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$data['template_id']]);
        $perms = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $insertStmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($perms as $permId) {
            $insertStmt->execute([$newRoleId, $permId]);
        }
    }

    $db->commit();
    activityLog('create_role', 'role', $newRoleId, [], $data, $_SESSION['company_id'], $_SESSION['user_id']);

    jsonResponse('success', 'Role created successfully.', ['redirect' => BASE_URL . 'owner/permissions/matrix.php?role_id=' . $newRoleId]);

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    writeSysLog('error', 'Create Role Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error.');
}
