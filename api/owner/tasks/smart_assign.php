<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';

requireLogin();
requirePermission('assign_tasks');
// This is a GET endpoint fetching data for Smart Assignment, so CSRF isn't strictly required for reading,
// but we'll include it to be safe, or just rely on session + permissions. We'll use GET for standard API fetching.

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse('error', 'Invalid method', null, 405);
}

try {
    $db = Database::getInstance()->getConnection();
    $company_id = $_SESSION['company_id'];

    $client_id = $_GET['client_id'] ?? null;

    if (!$client_id) {
        jsonResponse('error', 'Client ID required to determine eligible team.');
    }

    // Verify Client
    $stmtClient = $db->prepare("SELECT id FROM clients WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmtClient->execute([$client_id, $company_id]);
    if (!$stmtClient->fetch()) {
        jsonResponse('error', 'Invalid Client.');
    }

    // Smart Assignment Logic:
    // 1. Find all team members explicitly assigned to this client via client_team_assignments.
    // 2. Calculate their current task workload (count of tasks NOT in Completed/Cancelled/Rejected).
    // 3. Return this data to the frontend for informed assignment.

    $query = "
        SELECT
            tm.id as team_member_id,
            tm.full_name,
            tm.availability_status,
            r.display_name as role_name,
            (
                SELECT COUNT(*) FROM tasks t
                WHERE t.assigned_team_id = tm.id
                  AND t.company_id = tm.company_id
                  AND t.status NOT IN ('Completed', 'Cancelled', 'Rejected')
                  AND t.deleted_at IS NULL
            ) as current_workload
        FROM client_team_assignments cta
        JOIN team_members tm ON cta.team_member_id = tm.id
        LEFT JOIN roles r ON tm.role_id = r.id
        WHERE cta.client_id = ?
          AND cta.company_id = ?
          AND tm.status = 'active'
          AND tm.deleted_at IS NULL
        ORDER BY current_workload ASC, tm.full_name ASC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([$client_id, $company_id]);
    $team = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse('success', 'Team fetched', $team);

} catch (PDOException $e) {
    writeSysLog('error', 'Smart Assign API Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
