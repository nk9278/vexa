<?php
require_once '../../../includes/session.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/helpers.php';
require_once '../../../includes/database.php';
require_once '../../../includes/security.php';
require_once '../../../includes/logger.php';

requireLogin();
requirePermission('edit_team');
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) { jsonResponse('error', 'Invalid CSRF token.', null, 403); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Invalid method', null, 405);
}

try {
    $db = Database::getInstance()->getConnection();
    $company_id = $_SESSION['company_id'];

    $id = $_POST['id'] ?? null;
    $full_name = trim($_POST['full_name'] ?? '');
    $display_name = trim($_POST['display_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $role_id = $_POST['role_id'] ?? null;
    $department_id = $_POST['department_id'] ?? null;
    $employment_type = $_POST['employment_type'] ?? 'Full Time';
    $availability_status = $_POST['availability_status'] ?? 'Available';
    $status = $_POST['status'] ?? 'active';
    $profile_photo = $_POST['profile_photo'] ?? null; // Added profile_photo
    $skills = $_POST['skills'] ?? []; // Array of skill IDs

    if (!$id || empty($full_name) || empty($email) || empty($role_id)) {
        jsonResponse('error', 'Missing required fields.');
    }

    // Verify ownership
    $stmt = $db->prepare("SELECT id FROM team_members WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmt->execute([$id, $company_id]);
    if (!$stmt->fetch()) {
        jsonResponse('error', 'Team member not found or access denied.');
    }

    // Check duplicate email (excluding current user)
    $stmt = $db->prepare("SELECT id FROM team_members WHERE company_id = ? AND email = ? AND id != ? AND deleted_at IS NULL");
    $stmt->execute([$company_id, $email, $id]);
    if ($stmt->fetch()) {
        jsonResponse('error', 'Email is already assigned to another team member.');
    }

    $db->beginTransaction();

    // Handle profile photo logic
    if ($profile_photo !== null) {
        $stmt = $db->prepare("
            UPDATE team_members SET
                full_name = ?, display_name = ?, email = ?, mobile = ?,
                role_id = ?, department_id = ?, employment_type = ?,
                availability_status = ?, status = ?, profile_photo = ?, updated_at = ?
            WHERE id = ? AND company_id = ?
        ");
        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            $full_name, $display_name ?: $full_name, $email, $mobile,
            $role_id, $department_id ?: null, $employment_type,
            $availability_status, $status, $profile_photo, $now, $id, $company_id
        ]);
    } else {
        $stmt = $db->prepare("
            UPDATE team_members SET
                full_name = ?, display_name = ?, email = ?, mobile = ?,
                role_id = ?, department_id = ?, employment_type = ?,
                availability_status = ?, status = ?, updated_at = ?
            WHERE id = ? AND company_id = ?
        ");
        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            $full_name, $display_name ?: $full_name, $email, $mobile,
            $role_id, $department_id ?: null, $employment_type,
            $availability_status, $status, $now, $id, $company_id
        ]);
    }

    // Update skills (delete old, insert new)
    $db->prepare("DELETE FROM team_member_skills WHERE team_member_id = ? AND company_id = ?")->execute([$id, $company_id]);

    if (!empty($skills) && is_array($skills)) {
        $stmtSkill = $db->prepare("INSERT INTO team_member_skills (company_id, team_member_id, skill_id) VALUES (?, ?, ?)");
        foreach ($skills as $skill_id) {
            $checkSkill = $db->prepare("SELECT id FROM skills WHERE id = ? AND company_id = ?");
            $checkSkill->execute([$skill_id, $company_id]);
            if ($checkSkill->fetch()) {
                $stmtSkill->execute([$company_id, $id, $skill_id]);
            }
        }
    }

    $db->commit();

    activityLog('update', 'team_member', $id, [], ['full_name' => $full_name, 'status' => $status]);

    jsonResponse('success', 'Team member updated successfully.');

} catch (PDOException $e) {
    if (isset($db)) $db->rollBack();
    writeSysLog('error', 'Team Member Update Error: ' . $e->getMessage());
    jsonResponse('error', 'Database error occurred.');
}
