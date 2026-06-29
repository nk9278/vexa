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

        $client_id = $_POST['client_id'] ?? null;
        $package_id = $_POST['package_id'] ?? null;
        $plan_month = $_POST['plan_month'] ?? ''; // Format YYYY-MM
        $monthly_budget = $_POST['monthly_budget'] ?? 0;
        $status = $_POST['status'] ?? 'draft';

        // Target counts (array format: types[type_id] = count)
        $targets = $_POST['targets'] ?? [];

        if (!$client_id || empty($plan_month)) {
            jsonResponse('error', 'Client and Month are required.');
        }

        // Validate client
        $stmtClient = $db->prepare("SELECT id FROM clients WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
        $stmtClient->execute([$client_id, $company_id]);
        if (!$stmtClient->fetch()) {
            jsonResponse('error', 'Invalid client selected.');
        }

        // Check if plan already exists for this client and month
        $stmtCheck = $db->prepare("SELECT id FROM monthly_plans WHERE company_id = ? AND client_id = ? AND plan_month = ? AND deleted_at IS NULL");
        $stmtCheck->execute([$company_id, $client_id, $plan_month]);
        if ($stmtCheck->fetch()) {
            jsonResponse('error', 'A plan already exists for this client in ' . $plan_month . '. Please edit the existing plan.');
        }

        $db->beginTransaction();

        $stmt = $db->prepare("
            INSERT INTO monthly_plans (company_id, client_id, package_id, plan_month, status, monthly_budget)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $company_id, $client_id, $package_id ?: null, $plan_month, $status, $monthly_budget
        ]);
        $planId = $db->lastInsertId();

        // Bulk insert deliverables based on targets
        if (is_array($targets) && !empty($targets)) {
            $stmtDeliv = $db->prepare("
                INSERT INTO deliverables (company_id, client_id, monthly_plan_id, deliverable_type_id, title)
                VALUES (?, ?, ?, ?, ?)
            ");

            // Validate all types first
            $validTypes = [];
            $stmtValidType = $db->prepare("SELECT id, name FROM deliverable_types WHERE company_id = ?");
            $stmtValidType->execute([$company_id]);
            foreach ($stmtValidType->fetchAll() as $vt) {
                $validTypes[$vt['id']] = $vt['name'];
            }

            foreach ($targets as $type_id => $count) {
                $count = (int)$count;
                if ($count > 0 && isset($validTypes[$type_id])) {
                    $typeName = $validTypes[$type_id];
                    for ($i = 1; $i <= $count; $i++) {
                        $title = "{$typeName} {$i} for " . date('M Y', strtotime($plan_month . '-01'));
                        $stmtDeliv->execute([$company_id, $client_id, $planId, $type_id, $title]);
                    }
                }
            }
        }

        $db->commit();
        activityLog('create', 'monthly_plan', $planId, [], ['client_id' => $client_id, 'month' => $plan_month]);
        jsonResponse('success', 'Monthly plan created successfully.', ['id' => $planId]);

    } elseif ($action === 'update_status') {
        requirePermission('edit_deliverables');

        $plan_id = $_POST['plan_id'] ?? null;
        $status = $_POST['status'] ?? '';

        if (!$plan_id || empty($status)) jsonResponse('error', 'Missing data.');

        $stmt = $db->prepare("UPDATE monthly_plans SET status = ?, updated_at = ? WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
        $stmt->execute([$status, date('Y-m-d H:i:s'), $plan_id, $company_id]);

        activityLog('update_status', 'monthly_plan', $plan_id, [], ['status' => $status]);
        jsonResponse('success', 'Plan status updated.');

    } elseif ($action === 'archive') {
        requirePermission('delete_deliverables');

        $plan_id = $_POST['plan_id'] ?? null;
        if (!$plan_id) jsonResponse('error', 'Missing data.');

        $stmt = $db->prepare("UPDATE monthly_plans SET status = 'archived', updated_at = ? WHERE id = ? AND company_id = ?");
        $stmt->execute([date('Y-m-d H:i:s'), $plan_id, $company_id]);

        activityLog('archive', 'monthly_plan', $plan_id);
        jsonResponse('success', 'Plan archived.');
    }

    jsonResponse('error', 'Invalid action.');

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    writeSysLog('error', 'Plan API Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
