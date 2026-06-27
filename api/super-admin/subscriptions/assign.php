<?php
// api/super-admin/subscriptions/assign.php
require_once __DIR__ . '/../../../includes/functions.php';
requireSuperAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed.', [], 405);
}

if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    jsonResponse('error', 'Invalid security token.', [], 403);
}

$data = sanitizeInput($_POST);
$required = ['company_id', 'plan_id', 'start_date'];
$errors = validateRequiredFields($data, $required);

if (!empty($errors)) {
    jsonResponse('error', implode(' ', $errors));
}

try {
    $db = Database::getInstance()->getConnection();

    // Check if company already has an active subscription
    $stmt = $db->prepare("SELECT id FROM saas_subscriptions WHERE company_id = ? AND status IN ('active', 'trial') AND deleted_at IS NULL LIMIT 1");
    $stmt->execute([$data['company_id']]);
    if ($stmt->fetch()) {
        jsonResponse('error', 'This company already has an active or trial subscription.');
    }

    // Get Plan details for logic
    $stmt = $db->prepare("SELECT plan_type FROM saas_plans WHERE id = ?");
    $stmt->execute([$data['plan_id']]);
    $plan = $stmt->fetch();
    if (!$plan) {
        jsonResponse('error', 'Invalid Plan Selected.');
    }

    // Calculate Expiry Date based on plan type
    $start = new DateTime($data['start_date']);
    $expiry = clone $start;
    $trialDays = intval($data['trial_days'] ?? 0);
    $status = $trialDays > 0 ? 'trial' : 'active';

    if ($trialDays > 0) {
        $expiry->modify("+{$trialDays} days");
    } else {
        switch ($plan['plan_type']) {
            case 'monthly': $expiry->modify('+1 month'); break;
            case 'quarterly': $expiry->modify('+3 months'); break;
            case 'half-yearly': $expiry->modify('+6 months'); break;
            case 'yearly': $expiry->modify('+1 year'); break;
            case 'lifetime': $expiry->modify('+100 years'); break;
        }
    }

    // Generate unique license key (VEXA-XXXX-XXXX-XXXX)
    $licenseKey = 'VEXA-' . strtoupper(bin2hex(random_bytes(2))) . '-' . strtoupper(bin2hex(random_bytes(2))) . '-' . strtoupper(bin2hex(random_bytes(2)));

    $stmt = $db->prepare("
        INSERT INTO saas_subscriptions (
            company_id, plan_id, license_key, status, start_date, expiry_date, trial_days, remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['company_id'], $data['plan_id'], $licenseKey, $status,
        $start->format('Y-m-d'), $expiry->format('Y-m-d'), $trialDays, $data['remarks'] ?? null
    ]);

    $newId = $db->lastInsertId();

    activityLog('assign_plan', 'saas_subscription', $newId, [], $data, $data['company_id'], $_SESSION['user_id']);

    jsonResponse('success', 'Plan successfully assigned to company.', ['redirect' => BASE_URL . 'super-admin/subscriptions/index.php']);

} catch (PDOException $e) {
    writeSysLog('error', 'Assign Subscription DB Error: ' . $e->getMessage());
    jsonResponse('error', 'A database error occurred.');
}
