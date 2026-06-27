<?php
// api/super-admin/companies/update.php
require_once __DIR__ . '/../../../includes/functions.php';
requireSuperAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed.', [], 405);
}

if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    jsonResponse('error', 'Invalid security token.', [], 403);
}

$data = sanitizeInput($_POST);

if (empty($data['id'])) {
    jsonResponse('error', 'Company ID is required.');
}

$required = ['company_name', 'company_code', 'owner_name', 'owner_email', 'status'];
$errors = validateRequiredFields($data, $required);

if (!empty($errors)) {
    jsonResponse('error', implode(' ', $errors));
}

try {
    $db = Database::getInstance()->getConnection();

    // Check duplicates excluding self
    $stmt = $db->prepare("SELECT id FROM companies WHERE (company_code = ? OR owner_email = ?) AND id != ? LIMIT 1");
    $stmt->execute([$data['company_code'], $data['owner_email'], $data['id']]);
    if ($stmt->fetch()) {
        jsonResponse('error', 'A company with this code or owner email already exists.');
    }

    // Get Old Payload
    $stmt = $db->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$data['id']]);
    $oldData = $stmt->fetch();
    if (!$oldData) {
        jsonResponse('error', 'Company not found.');
    }

    $logoPath = $oldData['company_logo'];
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = handleSecureUpload($_FILES['company_logo'], 0, ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']);
        if (!$uploadResult['success']) {
            jsonResponse('error', $uploadResult['error']);
        }
        $logoPath = $uploadResult['path'];
    }

    $stmt = $db->prepare("
        UPDATE companies SET
            company_name=?, company_code=?, company_logo=?, business_type=?, industry=?,
            owner_name=?, owner_email=?, owner_mobile=?, office_phone=?, gst_number=?, pan_number=?,
            website=?, address=?, city=?, state=?, country=?, postal_code=?, timezone=?, currency=?, language=?, status=?, remarks=?,
            updated_at=?
        WHERE id=?
    ");

    $now = date('Y-m-d H:i:s');
    $stmt->execute([
        $data['company_name'], $data['company_code'], $logoPath, $data['business_type'] ?? null, $data['industry'] ?? null,
        $data['owner_name'], $data['owner_email'], $data['owner_mobile'] ?? null, $data['office_phone'] ?? null,
        $data['gst_number'] ?? null, $data['pan_number'] ?? null, $data['website'] ?? null, $data['address'] ?? null,
        $data['city'] ?? null, $data['state'] ?? null, $data['country'] ?? null, $data['postal_code'] ?? null,
        $data['timezone'] ?? 'UTC', $data['currency'] ?? 'USD', $data['language'] ?? 'en', $data['status'], $data['remarks'] ?? null,
        $now,
        $data['id']
    ]);

    activityLog('update_company', 'company', $data['id'], $oldData, $data, null, $_SESSION['user_id']);

    jsonResponse('success', 'Company updated successfully.', ['redirect' => BASE_URL . 'super-admin/companies/view.php?id=' . $data['id']]);

} catch (PDOException $e) {
    writeSysLog('error', 'Update Company DB Error: ' . $e->getMessage());
    jsonResponse('error', 'A database error occurred.');
}
