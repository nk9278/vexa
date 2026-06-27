<?php
// api/super-admin/plans/create.php
require_once __DIR__ . '/../../../includes/functions.php';
requireSuperAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed.', [], 405);
}
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    jsonResponse('error', 'Invalid security token.', [], 403);
}

$data = sanitizeInput($_POST);
$required = ['plan_name', 'plan_code', 'plan_type', 'price'];
$errors = validateRequiredFields($data, $required);

if (!empty($errors)) {
    jsonResponse('error', implode(' ', $errors));
}

try {
    $db = Database::getInstance()->getConnection();

    // Check code uniqueness
    $stmt = $db->prepare("SELECT id FROM saas_plans WHERE plan_code = ? AND deleted_at IS NULL LIMIT 1");
    $stmt->execute([$data['plan_code']]);
    if ($stmt->fetch()) {
        jsonResponse('error', 'Plan code already exists.');
    }

    $stmt = $db->prepare("
        INSERT INTO saas_plans (
            plan_name, plan_code, description, plan_type, price, currency, status,
            limit_employees, limit_managers, limit_crm_users, limit_clients, limit_projects, limit_tasks, limit_storage_mb
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['plan_name'], $data['plan_code'], $data['description'] ?? null, $data['plan_type'],
        $data['price'], $data['currency'] ?? 'USD', $data['status'] ?? 'active',
        $data['limit_employees'] ?? 0, $data['limit_managers'] ?? 0, $data['limit_crm_users'] ?? 0,
        $data['limit_clients'] ?? 0, $data['limit_projects'] ?? 0, $data['limit_tasks'] ?? 0, $data['limit_storage_mb'] ?? 0
    ]);

    $newId = $db->lastInsertId();
    activityLog('create_plan', 'saas_plan', $newId, [], $data, null, $_SESSION['user_id']);

    jsonResponse('success', 'Plan created successfully.', ['redirect' => BASE_URL . 'super-admin/plans/index.php']);

} catch (PDOException $e) {
    writeSysLog('error', 'Create Plan DB Error: ' . $e->getMessage());
    jsonResponse('error', 'A database error occurred.');
}
