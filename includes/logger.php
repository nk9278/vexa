<?php
// includes/logger.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/database.php'; // Required for activity logging

/**
 * File-based System Logger
 * Used for critical system errors, API failures, etc.
 *
 * @param string $level 'info', 'warning', 'error', 'critical'
 * @param string $message
 */
function writeSysLog($level, $message) {
    if (!is_dir(LOGS_PATH)) {
        mkdir(LOGS_PATH, 0755, true);
    }

    $date = date('Y-m-d');
    $time = date('Y-m-d H:i:s');
    $logFile = LOGS_PATH . "/system_{$date}.log";

    $formattedMessage = "[{$time}] [" . strtoupper($level) . "] {$message}" . PHP_EOL;
    error_log($formattedMessage, 3, $logFile);
}

/**
 * Database Activity Logger
 * Used for tracking user actions, CRUD operations, etc.
 *
 * @param string $action e.g., 'created', 'updated', 'deleted', 'login'
 * @param string $entityType e.g., 'task', 'client', 'user'
 * @param int|null $entityId The ID of the affected record
 * @param array $oldPayload The data before the change
 * @param array $newPayload The data after the change
 * @param int|null $companyId For multi-tenant isolation
 * @param int|null $userId Who performed the action
 */
function activityLog($action, $entityType, $entityId = null, $oldPayload = [], $newPayload = [], $companyId = null, $userId = null) {
    try {
        $db = Database::getInstance()->getConnection();

        // Ensure we have IDs if available via session (if not passed explicitly)
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (!$companyId && isset($_SESSION['company_id'])) {
                $companyId = $_SESSION['company_id'];
            }
            if (!$userId && isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
            }
        }

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        // NOTE: The `activity_logs` table must exist in the DB schema for this to execute successfully.
        // As this phase prevents creating tables, we catch the PDOException if the table is missing.
        $stmt = $db->prepare("
            INSERT INTO activity_logs (company_id, user_id, action, entity_type, entity_id, old_payload, new_payload, ip_address, user_agent, created_at)
            VALUES (:company_id, :user_id, :action, :entity_type, :entity_id, :old_payload, :new_payload, :ip, :ua, :created_at)
        ");

        $stmt->execute([
            ':company_id'  => $companyId,
            ':user_id'     => $userId,
            ':action'      => $action,
            ':entity_type' => $entityType,
            ':entity_id'   => $entityId,
            ':old_payload' => !empty($oldPayload) ? json_encode($oldPayload) : null,
            ':new_payload' => !empty($newPayload) ? json_encode($newPayload) : null,
            ':ip'          => $ipAddress,
            ':ua'          => $userAgent,
            ':created_at'  => date('Y-m-d H:i:s')
        ]);
        return true;
    } catch (\PDOException $e) {
        writeSysLog('error', "Failed to write activity log: " . $e->getMessage());
        return false;
    }
}
