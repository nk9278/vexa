<?php
// api/owner/roles/update.php
require_once __DIR__ . '/../../../includes/functions.php';
requireOwner();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse('error', 'Method not allowed.', [], 405);
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) jsonResponse('error', 'Invalid security token.', [], 403);

$data = sanitizeInput($_POST);
if (empty($data['id'])) jsonResponse('error', 'Role ID required.');
if (empty($data['display_name'])) jsonResponse('error', 'Display name is required.');

try {
    $db = Database::getInstance()->getConnection();

    // Check owner access
    $stmt = $db->prepare("SELECT * FROM roles WHERE id = ? AND company_id = ?");
    $stmt->execute([$data['id'], $_SESSION['company_id']]);
    $oldData = $stmt->fetch();

    if (!$oldData) jsonResponse('error', 'Role not found or access denied.');

    // Only update display_name and status to protect internal system logic
    $status = in_array($data['status'], ['active', 'inactive', 'archived']) ? $data['status'] : 'active';

    $stmt = $db->prepare("UPDATE roles SET display_name = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$data['display_name'], $status, $data['id']]);

    activityLog('update_role', 'role', $data['id'], $oldData, $data, $_SESSION['company_id'], $_SESSION['user_id']);

    jsonResponse('success', 'Role updated successfully.', ['redirect' => BASE_URL . 'owner/roles/index.php']);

} catch (PDOException $e) {
    writeSysLog('error', 'Update Role Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error.');
}
