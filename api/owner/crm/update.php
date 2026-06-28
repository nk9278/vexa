<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';

requireLogin();
requirePermission('manage_crm');
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) { jsonResponse('error', 'Invalid CSRF token.', null, 403); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Invalid method', null, 405);
}

try {
    $db = Database::getInstance()->getConnection();
    $company_id = $_SESSION['company_id'];

    $id = $_POST['id'] ?? null;
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $role_id = $_POST['role_id'] ?? null;
    $department_id = $_POST['department_id'] ?? null;
    $reporting_manager_id = $_POST['reporting_manager_id'] ?? null;
    $remarks = trim($_POST['remarks'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if (!$id || empty($full_name) || empty($email) || empty($role_id)) {
        jsonResponse('error', 'Missing required fields.');
    }

    // Check existing CRM User
    $stmt = $db->prepare("SELECT id FROM team_members WHERE id = ? AND company_id = ? AND is_crm = 1 AND deleted_at IS NULL");
    $stmt->execute([$id, $company_id]);
    if (!$stmt->fetch()) {
        jsonResponse('error', 'CRM user not found or access denied.');
    }

    // Tenant Validation (IDOR Protection)
    $stmtRole = $db->prepare("SELECT id FROM roles WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmtRole->execute([$role_id, $company_id]);
    if (!$stmtRole->fetch()) {
        jsonResponse('error', 'Invalid role selected.');
    }

    if ($department_id) {
        $stmtDept = $db->prepare("SELECT id FROM departments WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
        $stmtDept->execute([$department_id, $company_id]);
        if (!$stmtDept->fetch()) {
            jsonResponse('error', 'Invalid department selected.');
        }
    }

    if ($reporting_manager_id) {
        $stmtMgr = $db->prepare("SELECT id FROM team_members WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
        $stmtMgr->execute([$reporting_manager_id, $company_id]);
        if (!$stmtMgr->fetch()) {
            jsonResponse('error', 'Invalid reporting manager selected.');
        }
    }

    // Check email uniqueness
    $stmt = $db->prepare("SELECT id FROM team_members WHERE company_id = ? AND email = ? AND id != ? AND deleted_at IS NULL");
    $stmt->execute([$company_id, $email, $id]);
    if ($stmt->fetch()) {
        jsonResponse('error', 'Email is already assigned to another user.');
    }

    $stmt = $db->prepare("
        UPDATE team_members SET
            full_name = ?, email = ?, mobile = ?, role_id = ?,
            department_id = ?, reporting_manager_id = ?, remarks = ?, status = ?, updated_at = ?
        WHERE id = ? AND company_id = ?
    ");

    $now = date('Y-m-d H:i:s');
    $stmt->execute([
        $full_name, $email, $mobile, $role_id,
        $department_id ?: null, $reporting_manager_id ?: null, $remarks, $status, $now,
        $id, $company_id
    ]);

    activityLog('update', 'crm', $id, [], ['full_name' => $full_name]);
    jsonResponse('success', 'CRM user updated successfully.');

} catch (PDOException $e) {
    writeSysLog('error', 'CRM Update Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
