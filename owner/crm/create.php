<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('manage_crm');

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Get CRM Roles (Assuming 'CRM' in name or description, but fetch all active for flexibility)
$stmtRoles = $db->prepare("SELECT id, display_name FROM roles WHERE company_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY display_name ASC");
$stmtRoles->execute([$company_id]);
$roles = $stmtRoles->fetchAll();

// Get Departments
$stmtDepts = $db->prepare("SELECT id, name FROM departments WHERE company_id = ? AND deleted_at IS NULL ORDER BY name ASC");
$stmtDepts->execute([$company_id]);
$departments = $stmtDepts->fetchAll();

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="/owner/crm/" class="text-gray-500 hover:text-gray-700 mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Add CRM User</h1>
        </div>

        <form id="createCrmForm" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4 md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Basic Information</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="full_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                    <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile</label>
                    <input type="text" name="mobile" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>

                <div class="md:col-span-2"></div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">System Role *</label>
                    <select name="role_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors bg-white">
                        <option value="">Select a Role</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= esc($r['display_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select name="department_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors bg-white">
                        <option value="">No Department</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= esc($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reporting Manager (Optional)</label>
                    <?php
                        $stmtMgr = $db->prepare("SELECT id, full_name, is_crm FROM team_members WHERE company_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY full_name ASC");
                        $stmtMgr->execute([$company_id]);
                        $managers = $stmtMgr->fetchAll();
                    ?>
                    <select name="reporting_manager_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors bg-white">
                        <option value="">None</option>
                        <?php foreach ($managers as $mgr): ?>
                            <option value="<?= $mgr['id'] ?>"><?= esc($mgr['full_name']) ?><?= $mgr['is_crm'] ? ' (CRM)' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Remarks / Notes</label>
                    <textarea name="remarks" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors" placeholder="Any special notes or client handling instructions..."></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                <a href="/owner/crm/" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancel</a>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium">Add CRM User</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#createCrmForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.text();

        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: '/api/owner/crm/create.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.status === 'success') {
                    window.location.href = '/owner/crm/';
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
