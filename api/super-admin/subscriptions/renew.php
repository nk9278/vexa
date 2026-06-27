<?php
// api/super-admin/subscriptions/renew.php
require_once __DIR__ . '/../../../includes/functions.php';
requireSuperAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed.', [], 405);
}
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    jsonResponse('error', 'Invalid security token.', [], 403);
}

$subId = $_POST['subscription_id'] ?? 0;
$extendType = $_POST['extend_type'] ?? '';
$customDate = $_POST['custom_date'] ?? '';

if (empty($subId) || empty($extendType)) {
    jsonResponse('error', 'Missing required fields.');
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT * FROM saas_subscriptions WHERE id = ?");
    $stmt->execute([$subId]);
    $sub = $stmt->fetch();

    if (!$sub) jsonResponse('error', 'Subscription not found.');

    $currentExpiry = new DateTime($sub['expiry_date']);

    if ($extendType === 'custom') {
        if (empty($customDate)) jsonResponse('error', 'Custom date is required.');
        $newExpiry = new DateTime($customDate);
    } else {
        $newExpiry = clone $currentExpiry;
        switch($extendType) {
            case '1_month': $newExpiry->modify('+1 month'); break;
            case '3_months': $newExpiry->modify('+3 months'); break;
            case '1_year': $newExpiry->modify('+1 year'); break;
        }
    }

    $stmt = $db->prepare("UPDATE saas_subscriptions SET expiry_date = ?, status = 'active', updated_at = ? WHERE id = ?");
    $now = date('Y-m-d H:i:s');
    $stmt->execute([$newExpiry->format('Y-m-d'), $now, $subId]);

    activityLog('renew_subscription', 'saas_subscription', $subId, ['expiry' => $sub['expiry_date']], ['expiry' => $newExpiry->format('Y-m-d')], $sub['company_id'], $_SESSION['user_id']);

    jsonResponse('success', 'Subscription renewed successfully.', ['redirect' => BASE_URL . 'super-admin/subscriptions/view.php?id=' . $subId]);

} catch (PDOException $e) {
    writeSysLog('error', 'Renew Sub DB Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error.');
}
