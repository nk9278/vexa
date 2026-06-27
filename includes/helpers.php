<?php
// includes/helpers.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/session.php';

/**
 * JSON Response Helper for AJAX
 */
function jsonResponse($status, $message, $data = [], $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Redirect Helper
 */
function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit;
    } else {
        echo "<script>window.location.href='" . htmlspecialchars($url, ENT_QUOTES) . "';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($url, ENT_QUOTES) . "'></noscript>";
        exit;
    }
}

/**
 * Flash Message Setter
 */
function setFlashMessage($type, $message) {
    // Types: success, error, warning, info
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Flash Message Getter
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Date Formatting Helper
 */
function formatDate($dateString, $format = 'M d, Y') {
    if (empty($dateString)) return '';
    $date = new DateTime($dateString);
    return $date->format($format);
}

/**
 * Currency Formatting Helper
 */
function formatCurrency($amount, $currencySymbol = '$') {
    return $currencySymbol . number_format((float)$amount, 2);
}

/**
 * Status Badge UI Helper
 */
function getStatusBadge($statusStr) {
    $statusStr = strtolower(trim($statusStr));

    $colors = [
        'active'    => 'success',
        'completed' => 'success',
        'paid'      => 'success',
        'approved'  => 'success',

        'pending'   => 'warning',
        'paused'    => 'warning',
        'draft'     => 'neutral',

        'overdue'   => 'danger',
        'rejected'  => 'danger',
        'closed'    => 'danger',

        'working'   => 'info',
        'sent'      => 'info'
    ];

    $color = $colors[$statusStr] ?? 'neutral';
    $label = ucwords($statusStr);

    return "<span class=\"badge badge-{$color}\">" . htmlspecialchars($label) . "</span>";
}

/**
 * Permission Placeholder (Will be expanded when auth tables exist)
 */
function hasPermission($permissionKey) {
    return true;
}
