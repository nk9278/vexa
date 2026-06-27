<?php
// api/super-admin/plans/update.php
require_once __DIR__ . '/../../../includes/functions.php';
requireSuperAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed.', [], 405);
}
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    jsonResponse('error', 'Invalid security token.', [], 403);
}

$data = sanitizeInput($_POST);
if (empty($data['id'])) jsonResponse('error', 'Plan ID required.');

$required = ['plan_name', 'plan_code', 'plan_type', 'price'];
$errors = validateRequiredFields($data, $required);

if (!empty($errors)) {
    jsonResponse('error', implode(' ', $errors));
}

try {
    $db = Database::getInstance()->getConnection();

    // Check code uniqueness excluding self
    $stmt = $db->prepare("SELECT id FROM saas_plans WHERE plan_code = ? AND id != ? AND deleted_at IS NULL LIMIT 1");
    $stmt->execute([$data['plan_code'], $data['id']]);
    if ($stmt->fetch()) {
        jsonResponse('error', 'Plan code already exists.');
    }

    $stmt = $db->prepare("SELECT * FROM saas_plans WHERE id = ?");
    $stmt->execute([$data['id']]);
    $oldData = $stmt->fetch();

    $stmt = $db->prepare("
        UPDATE saas_plans SET
            plan_name=?, plan_code=?, description=?, plan_type=?, price=?, currency=?,
            limit_employees=?, limit_projects=?, limit_storage_mb=?, updated_at=?
        WHERE id=?
    ");

    $now = date('Y-m-d H:i:s');
    $stmt->execute([
        $data['plan_name'], $data['plan_code'], $data['description'] ?? null, $data['plan_type'],
        $data['price'], $data['currency'] ?? 'USD',
        $data['limit_employees'] ?? 0, $data['limit_projects'] ?? 0, $data['limit_storage_mb'] ?? 0,
        $now, $data['id']
    ]);

    activityLog('update_plan', 'saas_plan', $data['id'], $oldData, $data, null, $_SESSION['user_id']);

    jsonResponse('success', 'Plan updated successfully.', ['redirect' => BASE_URL . 'super-admin/plans/index.php']);

} catch (PDOException $e) {
    writeSysLog('error', 'Update Plan DB Error: ' . $e->getMessage());
    jsonResponse('error', 'A database error occurred.');
}
