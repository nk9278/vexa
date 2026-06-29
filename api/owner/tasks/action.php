<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';

requireLogin();
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

    $action = $_POST['action'] ?? '';
    $ids = $_POST['ids'] ?? [];

    if (empty($action) || empty($ids) || !is_array($ids)) {
        jsonResponse('error', 'Invalid request parameters.');
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $params = array_merge([$company_id], $ids);

    $stmtCheck = $db->prepare("SELECT id FROM tasks WHERE company_id = ? AND id IN ($placeholders) AND deleted_at IS NULL");
    $stmtCheck->execute($params);
    $validIds = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);

    if (empty($validIds)) {
        jsonResponse('error', 'No valid tasks found.');
    }

    $validPlaceholders = implode(',', array_fill(0, count($validIds), '?'));

    $db->beginTransaction();

    switch ($action) {
        case 'delete':
            requirePermission('delete_tasks');
            $stmt = $db->prepare("UPDATE tasks SET deleted_at = ? WHERE company_id = ? AND id IN ($validPlaceholders)");
            $execParams = array_merge([date('Y-m-d H:i:s'), $company_id], $validIds);
            $stmt->execute($execParams);
            $msg = "Tasks deleted permanently.";
            break;

        case 'cancel':
            requirePermission('edit_tasks');
            $stmt = $db->prepare("UPDATE tasks SET status = 'Cancelled', updated_at = ? WHERE company_id = ? AND id IN ($validPlaceholders)");
            $execParams = array_merge([date('Y-m-d H:i:s'), $company_id], $validIds);
            $stmt->execute($execParams);

            $stmtLog = $db->prepare("INSERT INTO task_timeline (company_id, task_id, user_id, action, details) VALUES (?, ?, ?, 'Status Changed', 'Cancelled via bulk action')");
            foreach($validIds as $vid) $stmtLog->execute([$company_id, $vid, $user_id]);

            $msg = "Tasks cancelled.";
            break;

        case 'reassign':
            requirePermission('reassign_tasks');
            $team_id = $_POST['team_id'] ?? null;
            if (!$team_id) {
                $db->rollBack();
                jsonResponse('error', 'Target team member required for reassignment.');
            }

            $stmtTeam = $db->prepare("SELECT id FROM team_members WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
            $stmtTeam->execute([$team_id, $company_id]);
            if(!$stmtTeam->fetch()) {
                $db->rollBack();
                jsonResponse('error', 'Invalid team member.');
            }

            $stmt = $db->prepare("UPDATE tasks SET assigned_team_id = ?, status = CASE WHEN status = 'Pending' THEN 'Assigned' ELSE status END, updated_at = ? WHERE company_id = ? AND id IN ($validPlaceholders)");
            $execParams = array_merge([$team_id, date('Y-m-d H:i:s'), $company_id], $validIds);
            $stmt->execute($execParams);

            $stmtLog = $db->prepare("INSERT INTO task_timeline (company_id, task_id, user_id, action, details) VALUES (?, ?, ?, 'Reassigned', 'Bulk reassigned to new member')");
            foreach($validIds as $vid) $stmtLog->execute([$company_id, $vid, $user_id]);

            $msg = "Tasks reassigned successfully.";
            break;

        default:
            $db->rollBack();
            jsonResponse('error', 'Invalid action.');
    }

    $db->commit();
    activityLog('bulk_action', 'task', null, [], ['action' => $action, 'count' => count($validIds)]);
    jsonResponse('success', $msg);

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    writeSysLog('error', 'Task Bulk Action Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
