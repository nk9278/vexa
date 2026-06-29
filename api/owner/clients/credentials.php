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
        requirePermission('edit_credentials');

        $platform = trim($_POST['platform'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $login_url = trim($_POST['login_url'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($platform)) {
            jsonResponse('error', 'Platform name is required.');
        }

        // Encrypt password before storage
        $encrypted_password = encryptData($password, ENCRYPTION_KEY); // Assuming encryptData exists in security.php

        $stmt = $db->prepare("
            INSERT INTO client_credentials (company_id, client_id, platform, username, password_encrypted, login_url, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$company_id, $client_id, $platform, $username, $encrypted_password, $login_url, $notes]);

        activityLog('create_credential', 'client', $client_id, [], ['platform' => $platform]);
        jsonResponse('success', 'Credential added successfully.');

    } elseif ($action === 'delete') {
        requirePermission('edit_credentials');

        $id = $_POST['id'] ?? null;
        if (!$id) jsonResponse('error', 'Credential ID required.');

        $stmt = $db->prepare("DELETE FROM client_credentials WHERE id = ? AND company_id = ? AND client_id = ?");
        $stmt->execute([$id, $company_id, $client_id]);

        activityLog('delete_credential', 'client', $client_id, [], ['credential_id' => $id]);
        jsonResponse('success', 'Credential deleted successfully.');

    } elseif ($action === 'view_password') {
        requirePermission('view_credentials');

        $id = $_POST['id'] ?? null;
        if (!$id) jsonResponse('error', 'Credential ID required.');

        $stmt = $db->prepare("SELECT password_encrypted, platform FROM client_credentials WHERE id = ? AND company_id = ? AND client_id = ?");
        $stmt->execute([$id, $company_id, $client_id]);
        $cred = $stmt->fetch();

        if (!$cred) jsonResponse('error', 'Credential not found.');

        // Decrypt password
        $decrypted_password = decryptData($cred['password_encrypted'], ENCRYPTION_KEY);

        // Audit log the viewing of a password
        activityLog('view_password', 'client', $client_id, [], ['platform' => $cred['platform']]);

        jsonResponse('success', 'Password decrypted.', ['password' => $decrypted_password]);
    }

    jsonResponse('error', 'Invalid action.');

} catch (PDOException $e) {
    writeSysLog('error', 'Credential API Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
} catch (Exception $e) {
    writeSysLog('error', 'Encryption Error: ' . $e->getMessage());
    jsonResponse('error', 'Security processing failed.');
}
