<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';
require_once '../../../includes/upload.php';

requireLogin();
requirePermission('manage_skills');
verifyCsrfToken();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Invalid method', null, 405);
}

try {
    $db = Database::getInstance()->getConnection();
    $company_id = $_SESSION['company_id'];

    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $id = $_POST['id'] ?? null;

    if ($action === 'create') {
        if (empty($name)) {
            jsonResponse('error', 'Skill name is required.');
        }

        // Check if skill exists (case-insensitive for practical purposes, though unique index handles exact matches)
        $stmt = $db->prepare("SELECT id FROM skills WHERE company_id = ? AND LOWER(name) = LOWER(?)");
        $stmt->execute([$company_id, $name]);
        if ($stmt->fetch()) {
            jsonResponse('error', 'Skill already exists.');
        }

        $stmt = $db->prepare("INSERT INTO skills (company_id, name) VALUES (?, ?)");
        $stmt->execute([$company_id, $name]);
        $newId = $db->lastInsertId();

        jsonResponse('success', 'Skill created successfully.', ['id' => $newId, 'name' => $name]);
    }
    else if ($action === 'delete') {
        if (empty($id)) {
            jsonResponse('error', 'Skill ID is required.');
        }

        // Delete skill mappings first
        $db->prepare("DELETE FROM team_member_skills WHERE skill_id = ? AND company_id = ?")->execute([$id, $company_id]);

        // Delete skill
        $stmt = $db->prepare("DELETE FROM skills WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $company_id]);

        jsonResponse('success', 'Skill deleted successfully.');
    }
    else {
        jsonResponse('error', 'Invalid action.');
    }

} catch (PDOException $e) {
    writeSysLog('error', 'Skill API Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
