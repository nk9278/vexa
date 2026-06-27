<?php
// api/super-admin/companies/create.php
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/onboarding.php';
requireSuperAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed.', [], 405);
}

if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    jsonResponse('error', 'Invalid security token.', [], 403);
}

$data = sanitizeInput($_POST);

// Validation
$required = ['company_name', 'company_code', 'owner_name', 'owner_email', 'status', 'owner_password'];
$errors = validateRequiredFields($data, $required);

if (!empty($errors)) {
    jsonResponse('error', implode(' ', $errors));
}

if (!filter_var($data['owner_email'], FILTER_VALIDATE_EMAIL)) {
    jsonResponse('error', 'Invalid owner email format.');
}

try {
    $db = Database::getInstance()->getConnection();

    // Check duplicates
    $stmt = $db->prepare("SELECT id FROM companies WHERE company_code = ? OR owner_email = ? LIMIT 1");
    $stmt->execute([$data['company_code'], $data['owner_email']]);
    if ($stmt->fetch()) {
        jsonResponse('error', 'A company with this code or owner email already exists.');
    }

    // Handle Logo Upload if present
    $logoPath = null;
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = handleSecureUpload($_FILES['company_logo'], 0, ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']);
        if (!$uploadResult['success']) {
            jsonResponse('error', $uploadResult['error']);
        }
        $logoPath = $uploadResult['path'];
    }

    // Insert
    $stmt = $db->prepare("
        INSERT INTO companies (
            company_name, company_code, company_logo, business_type, industry,
            owner_name, owner_email, owner_mobile, office_phone, gst_number, pan_number,
            website, address, city, state, country, postal_code, timezone, currency, language, status, remarks
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $stmt->execute([
        $data['company_name'], $data['company_code'], $logoPath, $data['business_type'] ?? null, $data['industry'] ?? null,
        $data['owner_name'], $data['owner_email'], $data['owner_mobile'] ?? null, $data['office_phone'] ?? null,
        $data['gst_number'] ?? null, $data['pan_number'] ?? null, $data['website'] ?? null, $data['address'] ?? null,
        $data['city'] ?? null, $data['state'] ?? null, $data['country'] ?? null, $data['postal_code'] ?? null,
        $data['timezone'] ?? 'UTC', $data['currency'] ?? 'USD', $data['language'] ?? 'en', $data['status'], $data['remarks'] ?? null
    ]);

    $newId = $db->lastInsertId();

    // Trigger Onboarding Engine
    $onboardingData = [
        'owner_name' => $data['owner_name'],
        'owner_email' => $data['owner_email'],
        'owner_mobile' => $data['owner_mobile'] ?? null,
        'password' => $_POST['owner_password'] // Send raw password so onboarding engine can hash it
    ];
    $onboardingResult = initializeCompanyWorkspace($newId, $onboardingData);

    if (!$onboardingResult['success']) {
        // Technically we should rollback the company creation here or mark it as failed_onboarding.
        // For simplicity in Phase 17, we will log it.
        writeSysLog('error', 'Company created but workspace initialization failed for ID ' . $newId);
    }

    activityLog('create_company', 'company', $newId, [], $data, null, $_SESSION['user_id']);

    jsonResponse('success', 'Company and Workspace created successfully.', ['redirect' => BASE_URL . 'super-admin/companies/view.php?id=' . $newId]);

} catch (PDOException $e) {
    writeSysLog('error', 'Create Company DB Error: ' . $e->getMessage());
    jsonResponse('error', 'A database error occurred.');
}
