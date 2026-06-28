<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';

requireLogin();
requirePermission('assign_team');
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) { jsonResponse('error', 'Invalid CSRF token.', null, 403); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Invalid method', null, 405);
}

try {
    $db = Database::getInstance()->getConnection();
    $company_id = $_SESSION['company_id'];

    $action = $_POST['action'] ?? '';
    $client_id = $_POST['client_id'] ?? null;
    $team_member_id = $_POST['team_member_id'] ?? null;

    if (!$client_id || !$team_member_id) {
        jsonResponse('error', 'Missing required fields.');
    }

    // Verify tenant isolation (IDOR protection)
    $stmtClient = $db->prepare("SELECT id FROM clients WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmtClient->execute([$client_id, $company_id]);
    if (!$stmtClient->fetch()) {
        jsonResponse('error', 'Invalid Client or access denied.');
    }

    $stmtTeam = $db->prepare("SELECT id FROM team_members WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmtTeam->execute([$team_member_id, $company_id]);
    if (!$stmtTeam->fetch()) {
        jsonResponse('error', 'Invalid Team Member or access denied.');
    }

    if ($action === 'assign') {
        $stmt = $db->prepare("INSERT INTO client_team_assignments (company_id, client_id, team_member_id) VALUES (?, ?, ?)");
        $stmt->execute([$company_id, $client_id, $team_member_id]);
        activityLog('assign_team', 'client', $client_id, [], ['team_member_id' => $team_member_id]);
        jsonResponse('success', 'Team member assigned successfully.');
    }
    elseif ($action === 'remove') {
        $stmt = $db->prepare("DELETE FROM client_team_assignments WHERE company_id = ? AND client_id = ? AND team_member_id = ?");
        $stmt->execute([$company_id, $client_id, $team_member_id]);
        activityLog('remove_team', 'client', $client_id, [], ['team_member_id' => $team_member_id]);
        jsonResponse('success', 'Team member removed successfully.');
    }

    jsonResponse('error', 'Invalid action.');

} catch (PDOException $e) {
    writeSysLog('error', 'Team Assignment Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred. They may already be assigned.');
}
