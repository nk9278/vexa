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

    $plan_id = $_POST['plan_id'] ?? null;

    if (!$plan_id) {
        jsonResponse('error', 'Monthly Plan ID required.');
    }

    // Verify Plan IDOR
    $stmtPlan = $db->prepare("SELECT id, client_id FROM monthly_plans WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmtPlan->execute([$plan_id, $company_id]);
    $plan = $stmtPlan->fetch();

    if (!$plan) {
        jsonResponse('error', 'Invalid Monthly Plan.');
    }

    // Get assigned CRM for this client to auto-assign as watcher if needed (optional)
    $stmtCrm = $db->prepare("SELECT crm_id FROM client_assignments WHERE client_id = ? AND company_id = ? LIMIT 1");
    $stmtCrm->execute([$plan['client_id'], $company_id]);
    $crmAssigned = $stmtCrm->fetch();
    $crm_id = $crmAssigned ? $crmAssigned['crm_id'] : null;

    $db->beginTransaction();

    // Fetch deliverables for this plan
    // Only generate tasks for deliverables that are NOT yet completed/cancelled AND don't already have a linked task
    $stmtItems = $db->prepare("
        SELECT id, deliverable_type_id, title, due_date
        FROM deliverables
        WHERE monthly_plan_id = ? AND company_id = ? AND deleted_at IS NULL
          AND status NOT IN ('Completed', 'Rejected', 'Cancelled')
          AND id NOT IN (SELECT deliverable_id FROM tasks WHERE deliverable_id IS NOT NULL AND company_id = ?)
    ");
    $stmtItems->execute([$plan_id, $company_id, $company_id]);
    $items = $stmtItems->fetchAll();

    if (empty($items)) {
        $db->rollBack();
        jsonResponse('error', 'No pending deliverables found to generate tasks for. Tasks may already exist.');
    }

    $stmtInsertTask = $db->prepare("
        INSERT INTO tasks (
            company_id, client_id, crm_id, deliverable_id, deliverable_type_id, title,
            description, priority, status, due_date, assigned_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Medium', 'Pending', ?, ?)
    ");

    $stmtLog = $db->prepare("INSERT INTO task_timeline (company_id, task_id, user_id, action, details) VALUES (?, ?, ?, ?, ?)");

    $count = 0;
    foreach ($items as $item) {
        $desc = "Auto-generated task from Monthly Delivery Plan.";

        $stmtInsertTask->execute([
            $company_id, $plan['client_id'], $crm_id, $item['id'], $item['deliverable_type_id'],
            $item['title'], $desc, $item['due_date'], $user_id
        ]);

        $taskId = $db->lastInsertId();

        $stmtLog->execute([$company_id, $taskId, $user_id, 'Created', 'Auto-generated from Deliverable Plan']);
        $count++;
    }

    $db->commit();
    activityLog('auto_generate', 'task', null, [], ['plan_id' => $plan_id, 'count' => $count]);

    jsonResponse('success', "$count Tasks successfully generated from the Monthly Plan.");

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    writeSysLog('error', 'Task Generator Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred during generation.');
}
