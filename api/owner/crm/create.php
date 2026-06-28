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

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $role_id = $_POST['role_id'] ?? null;
    $department_id = $_POST['department_id'] ?? null;
    $reporting_manager_id = $_POST['reporting_manager_id'] ?? null;
    $remarks = trim($_POST['remarks'] ?? '');

    if (empty($full_name) || empty($email) || empty($role_id)) {
        jsonResponse('error', 'Full Name, Email, and Role are required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse('error', 'Invalid email format.');
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

    // Check duplicate email
    $stmt = $db->prepare("SELECT id FROM team_members WHERE company_id = ? AND email = ? AND deleted_at IS NULL");
    $stmt->execute([$company_id, $email]);
    if ($stmt->fetch()) {
        jsonResponse('error', 'Email is already assigned to a user in this workspace.');
    }

    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO team_members (
            company_id, full_name, display_name, email, mobile,
            role_id, department_id, reporting_manager_id, remarks, status, is_crm, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 1, ?, ?)
    ");

    $now = date('Y-m-d H:i:s');
    $stmt->execute([
        $company_id, $full_name, $full_name, $email, $mobile,
        $role_id, $department_id ?: null, $reporting_manager_id ?: null, $remarks, $now, $now
    ]);

    $crmId = $db->lastInsertId();

    $db->commit();
    activityLog('create', 'crm', $crmId, [], ['full_name' => $full_name]);

    jsonResponse('success', 'CRM user added successfully.');

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    writeSysLog('error', 'CRM Create Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
