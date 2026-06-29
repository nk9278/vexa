<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';

requireLogin();
requirePermission('edit_client');
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    jsonResponse('error', 'Invalid CSRF token.', null, 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Invalid method', null, 405);
}

try {
    $db = Database::getInstance()->getConnection();
    $company_id = $_SESSION['company_id'];

    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $brand_name = trim($_POST['brand_name'] ?? '');
    $owner_name = trim($_POST['owner_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $business_email = trim($_POST['business_email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $alternate_mobile = trim($_POST['alternate_mobile'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $business_category = trim($_POST['business_category'] ?? '');
    $business_description = trim($_POST['business_description'] ?? '');
    $gst_number = trim($_POST['gst_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $pin_code = trim($_POST['pin_code'] ?? '');
    $google_business_link = trim($_POST['google_business_link'] ?? '');
    $map_location = trim($_POST['map_location'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $status = $_POST['status'] ?? 'lead';

    if (!$id || empty($name)) {
        jsonResponse('error', 'Business Name and ID are required.');
    }

    // Verify ownership
    $stmtCheck = $db->prepare("SELECT id FROM clients WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmtCheck->execute([$id, $company_id]);
    if (!$stmtCheck->fetch()) {
        jsonResponse('error', 'Client not found or access denied.');
    }

    $db->beginTransaction();

    $stmt = $db->prepare("
        UPDATE clients SET
            name = ?, brand_name = ?, owner_name = ?, contact_person = ?, email = ?, business_email = ?,
            mobile = ?, alternate_mobile = ?, website = ?, business_category = ?, business_description = ?,
            gst_number = ?, address = ?, city = ?, state = ?, country = ?, pin_code = ?,
            google_business_link = ?, map_location = ?, status = ?, remarks = ?, updated_at = ?
        WHERE id = ? AND company_id = ?
    ");

    $now = date('Y-m-d H:i:s');
    $stmt->execute([
        $name, $brand_name, $owner_name, $contact_person, $email, $business_email,
        $mobile, $alternate_mobile, $website, $business_category, $business_description,
        $gst_number, $address, $city, $state, $country, $pin_code,
        $google_business_link, $map_location, $status, $remarks, $now,
        $id, $company_id
    ]);

    $db->commit();
    activityLog('update', 'client', $id, [], ['name' => $name, 'status' => $status]);

    jsonResponse('success', 'Client updated successfully.');

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    writeSysLog('error', 'Client Update Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
