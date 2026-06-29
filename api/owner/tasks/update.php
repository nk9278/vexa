<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';

requireLogin();
requirePermission('edit_tasks');
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

    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'Medium';
    $status = $_POST['status'] ?? 'Pending';
    $due_date = $_POST['due_date'] ?? null;
    $remarks = trim($_POST['remarks'] ?? '');

    if (!$id || empty($title)) {
        jsonResponse('error', 'Task ID and Title are required.');
    }

    $stmtCheck = $db->prepare("SELECT id, status FROM tasks WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmtCheck->execute([$id, $company_id]);
    $task = $stmtCheck->fetch();

    if (!$task) {
        jsonResponse('error', 'Task not found.');
    }

    $db->beginTransaction();

    $stmt = $db->prepare("
        UPDATE tasks SET
            title = ?, description = ?, priority = ?, status = ?, due_date = ?, remarks = ?, updated_at = ?
        WHERE id = ? AND company_id = ?
    ");
    $stmt->execute([
        $title, $description, $priority, $status, $due_date ?: null, $remarks, date('Y-m-d H:i:s'), $id, $company_id
    ]);

    if ($status !== $task['status']) {
        $stmtLog = $db->prepare("INSERT INTO task_timeline (company_id, task_id, user_id, action, details) VALUES (?, ?, ?, ?, ?)");
        $stmtLog->execute([$company_id, $id, $user_id, 'Status Changed', "Status changed to $status"]);
    }

    $db->commit();
    activityLog('update', 'task', $id, [], ['title' => $title, 'status' => $status]);

    jsonResponse('success', 'Task updated successfully.');

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    writeSysLog('error', 'Task Update Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
