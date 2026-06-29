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
    $client_id = $_POST['client_id'] ?? null;

    if (!$client_id) {
        jsonResponse('error', 'Client ID is required.');
    }

    // Verify Tenant Isolation
    $stmtCheck = $db->prepare("SELECT id FROM clients WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmtCheck->execute([$client_id, $company_id]);
    if (!$stmtCheck->fetch()) {
        jsonResponse('error', 'Client not found or access denied.');
    }

    if ($action === 'create') {
        // Technically viewing client profile might show packages, but we'll use a specific edit check or base view check.
        requirePermission('edit_client');

        $package_name = trim($_POST['package_name'] ?? '');
        $package_price = trim($_POST['package_price'] ?? '0');
        $billing_cycle = trim($_POST['billing_cycle'] ?? '');
        $start_date = trim($_POST['start_date'] ?? '');
        $renewal_date = trim($_POST['renewal_date'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (empty($package_name)) {
            jsonResponse('error', 'Package name is required.');
        }

        $stmt = $db->prepare("
            INSERT INTO client_packages (company_id, client_id, package_name, package_price, billing_cycle, start_date, renewal_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $company_id, $client_id, $package_name, $package_price, $billing_cycle,
            $start_date ?: null, $renewal_date ?: null, $status
        ]);

        activityLog('create_package', 'client', $client_id, [], ['package' => $package_name]);
        jsonResponse('success', 'Package added successfully.');

    } elseif ($action === 'delete') {
        requirePermission('edit_client');

        $id = $_POST['id'] ?? null;
        if (!$id) jsonResponse('error', 'Package ID required.');

        $stmt = $db->prepare("DELETE FROM client_packages WHERE id = ? AND company_id = ? AND client_id = ?");
        $stmt->execute([$id, $company_id, $client_id]);

        activityLog('delete_package', 'client', $client_id, [], ['package_id' => $id]);
        jsonResponse('success', 'Package deleted successfully.');

    }

    jsonResponse('error', 'Invalid action.');

} catch (PDOException $e) {
    writeSysLog('error', 'Package API Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
