<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('view_team');

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Get Filters
$status_filter = $_GET['status'] ?? 'active';
$role_filter = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';

// Build Query
$query = "SELECT t.*, r.display_name as role_name, d.name as department_name
          FROM team_members t
          LEFT JOIN roles r ON t.role_id = r.id
          LEFT JOIN departments d ON t.department_id = d.id
          WHERE t.company_id = ? AND t.deleted_at IS NULL";
$params = [$company_id];

if ($status_filter !== 'all') {
    $query .= " AND t.status = ?";
    $params[] = $status_filter;
}

if ($role_filter) {
    $query .= " AND t.role_id = ?";
    $params[] = $role_filter;
}

if ($search) {
    $query .= " AND (t.full_name LIKE ? OR t.email LIKE ? OR t.mobile LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$query .= " ORDER BY t.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$team_members = $stmt->fetchAll();

// Get Roles for Filter
$stmtRoles = $db->prepare("SELECT id, display_name FROM roles WHERE company_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY display_name ASC");
$stmtRoles->execute([$company_id]);
$roles = $stmtRoles->fetchAll();

// Dashboard Stats
$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM team_members WHERE company_id = $company_id AND deleted_at IS NULL")->fetchColumn(),
    'available' => $db->query("SELECT COUNT(*) FROM team_members WHERE company_id = $company_id AND availability_status = 'Available' AND status = 'active' AND deleted_at IS NULL")->fetchColumn(),
    'busy' => $db->query("SELECT COUNT(*) FROM team_members WHERE company_id = $company_id AND availability_status = 'Busy' AND status = 'active' AND deleted_at IS NULL")->fetchColumn(),
    'inactive' => $db->query("SELECT COUNT(*) FROM team_members WHERE company_id = $company_id AND status = 'inactive' AND deleted_at IS NULL")->fetchColumn(),
];

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Team Management</h1>
            <p class="text-gray-600 text-sm">Manage your production resources and availability</p>
        </div>
        <div class="flex gap-2">
            <?php if (hasPermission('manage_skills')): ?>
                <a href="/owner/team/skills.php" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors border border-gray-300 font-medium">Manage Skills</a>
            <?php endif; ?>
            <?php if (hasPermission('create_team')): ?>
                <a href="/owner/team/create.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium">+ Add Member</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-gray-500 text-sm font-medium mb-1">Total Members</h3>
            <div class="text-3xl font-bold text-gray-800"><?= $stats['total'] ?></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-gray-500 text-sm font-medium mb-1">Available</h3>
            <div class="text-3xl font-bold text-green-600"><?= $stats['available'] ?></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-gray-500 text-sm font-medium mb-1">Busy / Working</h3>
            <div class="text-3xl font-bold text-yellow-600"><?= $stats['busy'] ?></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-gray-500 text-sm font-medium mb-1">Inactive</h3>
            <div class="text-3xl font-bold text-red-600"><?= $stats['inactive'] ?></div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-t-xl border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex gap-2">
            <form id="filterForm" class="flex gap-2 w-full md:w-auto">
                <select name="status" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-primary text-sm">
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active Members</option>
                    <option value="archived" <?= $status_filter === 'archived' ? 'selected' : '' ?>>Archived</option>
                    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                </select>

                <select name="role" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-primary text-sm">
                    <option value="">All Roles</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $role_filter == $r['id'] ? 'selected' : '' ?>><?= esc($r['display_name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="relative">
                    <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Search name, email..." class="border border-gray-300 rounded-lg pl-9 pr-3 py-2 w-full focus:outline-none focus:border-primary text-sm">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </form>
        </div>

        <?php if (hasPermission('delete_team')): ?>
        <div class="flex gap-2">
            <select id="bulkActionSelect" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-primary text-sm hidden">
                <option value="">Bulk Actions</option>
                <option value="archive">Archive</option>
                <option value="restore">Restore</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="delete">Delete Permanently</option>
            </select>
            <button id="applyBulkAction" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition-colors font-medium text-sm hidden">Apply</button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Data Table / Grid -->
    <div class="bg-white rounded-b-xl shadow-sm overflow-hidden">
        <?php if (count($team_members) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <?php if (hasPermission('delete_team')): ?>
                                <th class="p-4 w-12"><input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary focus:ring-primary"></th>
                            <?php endif; ?>
                            <th class="p-4 font-medium text-gray-600 text-sm">Member</th>
                            <th class="p-4 font-medium text-gray-600 text-sm">Role & Dept</th>
                            <th class="p-4 font-medium text-gray-600 text-sm">Availability</th>
                            <th class="p-4 font-medium text-gray-600 text-sm">Status</th>
                            <th class="p-4 font-medium text-gray-600 text-sm text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($team_members as $member): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <?php if (hasPermission('delete_team')): ?>
                                    <td class="p-4">
                                        <input type="checkbox" value="<?= $member['id'] ?>" class="row-checkbox rounded border-gray-300 text-primary focus:ring-primary">
                                    </td>
                                <?php endif; ?>
                                <td class="p-4">
                                    <div class="flex items-center">
                                        <?php if ($member['profile_photo']): ?>
                                            <img src="<?= esc($member['profile_photo']) ?>" class="w-10 h-10 rounded-full object-cover border border-gray-200" alt="Avatar">
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold border border-primary/20">
                                                <?= strtoupper(substr($member['full_name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="ml-3">
                                            <div class="font-medium text-gray-800"><?= esc($member['full_name']) ?></div>
                                            <div class="text-xs text-gray-500"><?= esc($member['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="text-sm font-medium text-gray-800"><?= esc($member['role_name'] ?? 'Unassigned') ?></div>
                                    <div class="text-xs text-gray-500"><?= esc($member['department_name'] ?? 'No Department') ?></div>
                                </td>
                                <td class="p-4">
                                    <?php
                                    $avail = $member['availability_status'];
                                    $availColor = 'bg-gray-100 text-gray-700';
                                    if ($avail === 'Available') $availColor = 'bg-green-100 text-green-700';
                                    if ($avail === 'Busy') $availColor = 'bg-yellow-100 text-yellow-700';
                                    if ($avail === 'On Leave') $availColor = 'bg-red-100 text-red-700';
                                    ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $availColor ?>"><?= esc($avail) ?></span>
                                </td>
                                <td class="p-4">
                                    <?php if ($member['status'] === 'active'): ?>
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">Active</span>
                                    <?php elseif ($member['status'] === 'archived'): ?>
                                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">Archived</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="/owner/team/profile.php?id=<?= $member['id'] ?>" class="text-gray-400 hover:text-gray-600 transition-colors mx-1" title="View Profile">
                                        <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <?php if (hasPermission('edit_team')): ?>
                                        <a href="/owner/team/edit.php?id=<?= $member['id'] ?>" class="text-blue-500 hover:text-blue-700 transition-colors mx-1" title="Edit">
                                            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <p class="text-lg font-medium">No team members found.</p>
                <p class="text-sm mt-1">Try adjusting your filters or add a new member.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    const $selectAll = $('#selectAll');
    const $rowCheckboxes = $('.row-checkbox');
    const $bulkSelect = $('#bulkActionSelect');
    const $bulkApply = $('#applyBulkAction');

    function toggleBulkActions() {
        const checkedCount = $('.row-checkbox:checked').length;
        if (checkedCount > 0) {
            $bulkSelect.removeClass('hidden');
            $bulkApply.removeClass('hidden');
        } else {
            $bulkSelect.addClass('hidden');
            $bulkApply.addClass('hidden');
        }
    }

    $selectAll.on('change', function() {
        $rowCheckboxes.prop('checked', $(this).prop('checked'));
        toggleBulkActions();
    });

    $rowCheckboxes.on('change', function() {
        if (!$(this).prop('checked')) {
            $selectAll.prop('checked', false);
        } else if ($('.row-checkbox:checked').length === $rowCheckboxes.length) {
            $selectAll.prop('checked', true);
        }
        toggleBulkActions();
    });

    $bulkApply.on('click', function() {
        const action = $bulkSelect.val();
        if (!action) return alert('Please select an action.');

        const ids = [];
        $('.row-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length === 0) return;

        if (confirm(`Are you sure you want to perform this bulk action on ${ids.length} members?`)) {
            $.ajax({
                url: '/api/owner/team/action.php',
                method: 'POST',
                data: {
                    action: action,
                    ids: ids,
                    csrf_token: '<?= generateCsrfToken() ?>'
                },
                success: function(res) {
                    if (res.status === 'success') {
                        location.reload();
                    } else {
                        alert(res.message);
                    }
                },
                error: function() {
                    alert('An error occurred.');
                }
            });
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
