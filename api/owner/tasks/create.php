<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';

requireLogin();
requirePermission('create_tasks');
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    jsonResponse('error', 'Invalid CSRF token.', null, 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Invalid method', null, 405);
}

try {
    $db = Database::getInstance()->getConnection();
    $company_id = $_SESSION['company_id'];
    $user_id = $_SESSION['user_id'];

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $client_id = $_POST['client_id'] ?? null;
    $crm_id = $_POST['crm_id'] ?? null;
    $deliverable_type_id = $_POST['deliverable_type_id'] ?? null;
    $priority = $_POST['priority'] ?? 'Medium';
    $due_date = $_POST['due_date'] ?? null;
    $assigned_team_id = $_POST['assigned_team_id'] ?? null;

    if (empty($title) || !$client_id) {
        jsonResponse('error', 'Title and Client are required.');
    }

    // IDOR checks
    $stmtClient = $db->prepare("SELECT id FROM clients WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmtClient->execute([$client_id, $company_id]);
    if (!$stmtClient->fetch()) jsonResponse('error', 'Invalid client selected.');

    if ($crm_id) {
        $stmtCrm = $db->prepare("SELECT id FROM team_members WHERE id = ? AND company_id = ? AND is_crm = 1 AND deleted_at IS NULL");
        $stmtCrm->execute([$crm_id, $company_id]);
        if (!$stmtCrm->fetch()) jsonResponse('error', 'Invalid CRM user selected.');
    }

    if ($assigned_team_id) {
        $stmtTeam = $db->prepare("SELECT id FROM team_members WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
        $stmtTeam->execute([$assigned_team_id, $company_id]);
        if (!$stmtTeam->fetch()) jsonResponse('error', 'Invalid team member selected.');
    }

    $db->beginTransaction();

    $status = $assigned_team_id ? 'Assigned' : 'Pending';

    $stmt = $db->prepare("
        INSERT INTO tasks (
            company_id, client_id, crm_id, deliverable_type_id, title, description,
            priority, status, due_date, assigned_team_id, assigned_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $company_id, $client_id, $crm_id ?: null, $deliverable_type_id ?: null,
        $title, $description, $priority, $status, $due_date ?: null,
        $assigned_team_id ?: null, $user_id
    ]);

    $taskId = $db->lastInsertId();

    $stmtLog = $db->prepare("INSERT INTO task_timeline (company_id, task_id, user_id, action, details) VALUES (?, ?, ?, ?, ?)");
    $stmtLog->execute([$company_id, $taskId, $user_id, 'Created', 'Task created manually.']);

    if ($assigned_team_id) {
        $stmtLog->execute([$company_id, $taskId, $user_id, 'Assigned', "Task assigned upon creation."]);
    }

    $db->commit();
    activityLog('create', 'task', $taskId, [], ['title' => $title]);

    jsonResponse('success', 'Task created successfully.', ['id' => $taskId]);

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    writeSysLog('error', 'Task Create Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
