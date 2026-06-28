<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';

requireLogin();
requirePermission('create_team');
verifyCsrfToken();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Invalid method', null, 405);
}

try {
    $db = Database::getInstance()->getConnection();
    $company_id = $_SESSION['company_id'];

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $role_id = $_POST['role_id'] ?? null;
    $department_id = $_POST['department_id'] ?? null;
    $employment_type = $_POST['employment_type'] ?? 'Full Time';
    $skills = $_POST['skills'] ?? []; // Array of skill IDs

    if (empty($full_name) || empty($email) || empty($role_id)) {
        jsonResponse('error', 'Full Name, Email, and Role are required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse('error', 'Invalid email format.');
    }

    // Check duplicate email in same company
    $stmt = $db->prepare("SELECT id FROM team_members WHERE company_id = ? AND email = ? AND deleted_at IS NULL");
    $stmt->execute([$company_id, $email]);
    if ($stmt->fetch()) {
        jsonResponse('error', 'Email is already assigned to a team member in this workspace.');
    }

    // Verify role belongs to company
    $stmt = $db->prepare("SELECT id FROM roles WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmt->execute([$role_id, $company_id]);
    if (!$stmt->fetch()) {
        jsonResponse('error', 'Invalid role selected.');
    }

    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO team_members (
            company_id, full_name, display_name, email, mobile,
            role_id, department_id, employment_type, status, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?)
    ");

    $now = date('Y-m-d H:i:s');
    $stmt->execute([
        $company_id,
        $full_name,
        $full_name, // Default display name to full name initially
        $email,
        $mobile,
        $role_id,
        $department_id ?: null,
        $employment_type,
        $now,
        $now
    ]);

    $memberId = $db->lastInsertId();

    // Assign skills
    if (!empty($skills) && is_array($skills)) {
        $stmtSkill = $db->prepare("INSERT INTO team_member_skills (company_id, team_member_id, skill_id) VALUES (?, ?, ?)");
        foreach ($skills as $skill_id) {
            // Check if skill exists and belongs to company (basic validation)
            $checkSkill = $db->prepare("SELECT id FROM skills WHERE id = ? AND company_id = ?");
            $checkSkill->execute([$skill_id, $company_id]);
            if ($checkSkill->fetch()) {
                $stmtSkill->execute([$company_id, $memberId, $skill_id]);
            }
        }
    }

    $db->commit();

    activityLog('create', 'team_member', $memberId, [], ['full_name' => $full_name, 'email' => $email]);

    jsonResponse('success', 'Team member added successfully.');

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    writeSysLog('error', 'Team Member Create Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
