<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('edit_team');

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /owner/team/");
    exit;
}

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Get Member
$stmt = $db->prepare("SELECT * FROM team_members WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
$stmt->execute([$id, $company_id]);
$member = $stmt->fetch();

if (!$member) {
    header("Location: /owner/team/");
    exit;
}

// Get Roles
$stmtRoles = $db->prepare("SELECT id, display_name FROM roles WHERE company_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY display_name ASC");
$stmtRoles->execute([$company_id]);
$roles = $stmtRoles->fetchAll();

// Get Departments
$stmtDepts = $db->prepare("SELECT id, name FROM departments WHERE company_id = ? AND deleted_at IS NULL ORDER BY name ASC");
$stmtDepts->execute([$company_id]);
$departments = $stmtDepts->fetchAll();

// Get All Skills
$stmtSkills = $db->prepare("SELECT id, name FROM skills WHERE company_id = ? ORDER BY name ASC");
$stmtSkills->execute([$company_id]);
$skills = $stmtSkills->fetchAll();

// Get Member Skills
$stmtMemberSkills = $db->prepare("SELECT skill_id FROM team_member_skills WHERE team_member_id = ? AND company_id = ?");
$stmtMemberSkills->execute([$id, $company_id]);
$member_skills = $stmtMemberSkills->fetchAll(PDO::FETCH_COLUMN);

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <a href="/owner/team/" class="text-gray-500 hover:text-gray-700 mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Edit Team Member</h1>
            </div>
            <div>
                <?php if ($member['status'] === 'active'): ?>
                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium border border-green-200">Active</span>
                <?php else: ?>
                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-medium border border-gray-200"><?= ucfirst(esc($member['status'])) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <form id="editTeamForm" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="id" value="<?= $member['id'] ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Info -->
                <div class="space-y-4 md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Basic Information</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="full_name" value="<?= esc($member['full_name']) ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
                    <input type="text" name="display_name" value="<?= esc($member['display_name']) ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                    <input type="email" name="email" value="<?= esc($member['email']) ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile</label>
                    <input type="text" name="mobile" value="<?= esc($member['mobile']) ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <!-- Employment & Role -->
                <div class="space-y-4 md:col-span-2 mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Role & Assignment</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">System Role *</label>
                    <select name="role_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors bg-white">
                        <option value="">Select a Role</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= $member['role_id'] == $r['id'] ? 'selected' : '' ?>><?= esc($r['display_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select name="department_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors bg-white">
                        <option value="">No Department</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= $member['department_id'] == $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employment Type</label>
                    <select name="employment_type" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors bg-white">
                        <option value="Full Time" <?= $member['employment_type'] === 'Full Time' ? 'selected' : '' ?>>Full Time</option>
                        <option value="Part Time" <?= $member['employment_type'] === 'Part Time' ? 'selected' : '' ?>>Part Time</option>
                        <option value="Freelancer" <?= $member['employment_type'] === 'Freelancer' ? 'selected' : '' ?>>Freelancer</option>
                        <option value="Contract" <?= $member['employment_type'] === 'Contract' ? 'selected' : '' ?>>Contract</option>
                        <option value="Intern" <?= $member['employment_type'] === 'Intern' ? 'selected' : '' ?>>Intern</option>
                    </select>
                </div>

                <!-- Status & Availability -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors bg-white">
                        <option value="active" <?= $member['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $member['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="archived" <?= $member['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Availability</label>
                    <select name="availability_status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors bg-white">
                        <option value="Available" <?= $member['availability_status'] === 'Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Busy" <?= $member['availability_status'] === 'Busy' ? 'selected' : '' ?>>Busy</option>
                        <option value="On Leave" <?= $member['availability_status'] === 'On Leave' ? 'selected' : '' ?>>On Leave</option>
                        <option value="Offline" <?= $member['availability_status'] === 'Offline' ? 'selected' : '' ?>>Offline</option>
                    </select>
                </div>

                <!-- Skills -->
                <div class="space-y-4 md:col-span-2 mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Production Skills</h3>
                </div>

                <div class="md:col-span-2">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <?php foreach ($skills as $skill): ?>
                            <label class="flex items-center space-x-2 bg-gray-50 p-2 rounded border border-gray-200 cursor-pointer hover:bg-gray-100">
                                <input type="checkbox" name="skills[]" value="<?= $skill['id'] ?>" <?= in_array($skill['id'], $member_skills) ? 'checked' : '' ?> class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="text-sm text-gray-700"><?= esc($skill['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                <a href="/owner/team/" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancel</a>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#editTeamForm').on('submit', function(e) {
        e.preventDefault();

        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.text();

        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: '/api/owner/team/update.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.status === 'success') {
                    // Show success toast/alert here if desired
                    window.location.href = '/owner/team/';
                } else {
                    alert(res.message);
                    $btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
