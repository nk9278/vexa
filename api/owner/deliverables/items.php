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

    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        requirePermission('create_deliverables');

        $plan_id = $_POST['plan_id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $type_id = $_POST['deliverable_type_id'] ?? null;
        $due_date = $_POST['due_date'] ?? null;

        if (!$plan_id || empty($title) || !$type_id) {
            jsonResponse('error', 'Missing required fields.');
        }

        // Validate plan and get client_id
        $stmtPlan = $db->prepare("SELECT client_id FROM monthly_plans WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
        $stmtPlan->execute([$plan_id, $company_id]);
        $plan = $stmtPlan->fetch();
        if (!$plan) {
            jsonResponse('error', 'Invalid monthly plan.');
        }

        $stmt = $db->prepare("
            INSERT INTO deliverables (company_id, client_id, monthly_plan_id, deliverable_type_id, title, due_date)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$company_id, $plan['client_id'], $plan_id, $type_id, $title, $due_date ?: null]);

        activityLog('create', 'deliverable', $db->lastInsertId(), [], ['plan_id' => $plan_id]);
        jsonResponse('success', 'Deliverable added successfully.');

    } elseif ($action === 'update_status') {
        requirePermission('edit_deliverables');

        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? '';

        if (!$id || empty($status)) jsonResponse('error', 'Missing data.');

        $stmt = $db->prepare("UPDATE deliverables SET status = ?, updated_at = ? WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
        $stmt->execute([$status, date('Y-m-d H:i:s'), $id, $company_id]);

        activityLog('update_status', 'deliverable', $id, [], ['status' => $status]);
        jsonResponse('success', 'Status updated.');

    } elseif ($action === 'assign_team') {
        requirePermission('assign_team');

        $id = $_POST['id'] ?? null;
        $team_ids = $_POST['team_ids'] ?? []; // Array of IDs

        if (!$id) jsonResponse('error', 'Missing deliverable ID.');

        // Validate deliverable
        $stmtDeliv = $db->prepare("SELECT id FROM deliverables WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
        $stmtDeliv->execute([$id, $company_id]);
        if (!$stmtDeliv->fetch()) {
            jsonResponse('error', 'Deliverable not found.');
        }

        // Validate team members
        $validTeam = [];
        if (!empty($team_ids) && is_array($team_ids)) {
            $placeholders = implode(',', array_fill(0, count($team_ids), '?'));
            $params = array_merge([$company_id], $team_ids);
            $stmtTeam = $db->prepare("SELECT id FROM team_members WHERE company_id = ? AND id IN ($placeholders) AND deleted_at IS NULL");
            $stmtTeam->execute($params);
            $validTeam = $stmtTeam->fetchAll(PDO::FETCH_COLUMN);
        }

        // For simplicity in this phase, we store assigned team members as a JSON array in the deliverable record
        // In a true normalized setup, this might be a pivot table, but a JSON column is acceptable for a flat assignment list.
        $jsonAssigned = json_encode($validTeam);

        $stmt = $db->prepare("UPDATE deliverables SET assigned_team_ids = ?, updated_at = ? WHERE id = ? AND company_id = ?");
        $stmt->execute([$jsonAssigned, date('Y-m-d H:i:s'), $id, $company_id]);

        activityLog('assign_team', 'deliverable', $id);
        jsonResponse('success', 'Team assigned successfully.');

    } elseif ($action === 'delete') {
        requirePermission('delete_deliverables');

        $id = $_POST['id'] ?? null;
        if (!$id) jsonResponse('error', 'Missing data.');

        $stmt = $db->prepare("UPDATE deliverables SET deleted_at = ? WHERE id = ? AND company_id = ?");
        $stmt->execute([date('Y-m-d H:i:s'), $id, $company_id]);

        activityLog('delete', 'deliverable', $id);
        jsonResponse('success', 'Deliverable removed.');
    }

    jsonResponse('error', 'Invalid action.');

} catch (PDOException $e) {
    writeSysLog('error', 'Deliverables Item API Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
