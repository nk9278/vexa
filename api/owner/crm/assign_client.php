<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';

requireLogin();
requirePermission('assign_clients');
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) { jsonResponse('error', 'Invalid CSRF token.', null, 403); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Invalid method', null, 405);
}

try {
    $db = Database::getInstance()->getConnection();
    $company_id = $_SESSION['company_id'];

    $action = $_POST['action'] ?? '';
    $crm_id = $_POST['crm_id'] ?? null;
    $client_id = $_POST['client_id'] ?? null;

    if (!$crm_id || !$client_id) {
        jsonResponse('error', 'Missing required fields.');
    }

    // Verify tenant isolation (IDOR protection)
    $stmtCrm = $db->prepare("SELECT id FROM team_members WHERE id = ? AND company_id = ? AND is_crm = 1 AND deleted_at IS NULL");
    $stmtCrm->execute([$crm_id, $company_id]);
    if (!$stmtCrm->fetch()) {
        jsonResponse('error', 'Invalid CRM user or access denied.');
    }

    $stmtClient = $db->prepare("SELECT id FROM clients WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmtClient->execute([$client_id, $company_id]);
    if (!$stmtClient->fetch()) {
        jsonResponse('error', 'Invalid Client or access denied.');
    }

    if ($action === 'assign') {
        $stmt = $db->prepare("INSERT INTO client_assignments (company_id, client_id, crm_id) VALUES (?, ?, ?)");
        $stmt->execute([$company_id, $client_id, $crm_id]);
        activityLog('assign_client', 'crm', $crm_id, [], ['client_id' => $client_id]);
        jsonResponse('success', 'Client assigned successfully.');
    }
    elseif ($action === 'remove') {
        $stmt = $db->prepare("DELETE FROM client_assignments WHERE company_id = ? AND client_id = ? AND crm_id = ?");
        $stmt->execute([$company_id, $client_id, $crm_id]);
        activityLog('remove_client', 'crm', $crm_id, [], ['client_id' => $client_id]);
        jsonResponse('success', 'Client removed successfully.');
    }

    jsonResponse('error', 'Invalid action.');

} catch (PDOException $e) {
    writeSysLog('error', 'Client Assignment Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred. It may already be assigned.');
}
