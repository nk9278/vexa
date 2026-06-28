<?php
// owner/roles/index.php
require_once __DIR__ . '/../../includes/functions.php';
requireOwner();

define('PAGE_TITLE', 'Role Management');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/owner/includes/sidebar.php';

$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$whereParams = [$_SESSION['company_id']];
$whereClause = "WHERE company_id = ? AND deleted_at IS NULL";

if (!empty($search)) {
    $whereClause .= " AND (role_name LIKE ? OR display_name LIKE ?)";
    $whereParams[] = "%{$search}%";
    $whereParams[] = "%{$search}%";
}
if (!empty($statusFilter)) {
    $whereClause .= " AND status = ?";
    $whereParams[] = $statusFilter;
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM roles $whereClause ORDER BY is_system DESC, created_at ASC");
    $stmt->execute($whereParams);
    $roles = $stmt->fetchAll();
} catch (PDOException $e) {
    $roles = [];
}
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/owner/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Role Management</h1>
                    <p class="mt-1 text-sm text-slate-500">Configure custom roles and assign precise permissions.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <a href="create.php" class="btn-primary">+ Create Custom Role</a>
                </div>
            </div>

            <!-- Filters Bar -->
            <form method="GET" action="index.php" class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 mb-6 flex flex-col sm:flex-row gap-4 items-end">
                <div class="w-full sm:w-1/3">
                    <label class="block text-xs font-medium text-slate-500 mb-1 uppercase tracking-wider">Search</label>
                    <input type="text" name="search" class="form-input text-sm" value="<?= esc($search) ?>" placeholder="Search by role name...">
                </div>
                <div class="w-full sm:w-1/4">
                    <label class="block text-xs font-medium text-slate-500 mb-1 uppercase tracking-wider">Status</label>
                    <select name="status" class="form-input text-sm">
                        <option value="">All Statuses</option>
                        <option value="active" <?= $statusFilter == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $statusFilter == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="w-full sm:w-auto flex gap-2">
                    <button type="submit" class="btn-primary w-full sm:w-auto">Filter</button>
                    <?php if(!empty($search) || !empty($statusFilter)): ?>
                        <a href="index.php" class="btn-secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="card overflow-x-auto">
                <table class="table-container">
                    <thead class="table-header">
                        <tr>
                            <th class="w-12"><input type="checkbox" class="rounded text-indigo-600 border-slate-300"></th>
                            <th>Role Name</th>
                            <th>Internal Key</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($roles)): ?>
                            <tr><td colspan="6" class="table-cell text-center py-8 text-slate-500 italic">No roles found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($roles as $r): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="table-cell">
                                    <?php if($r['role_name'] !== 'Owner'): ?>
                                    <input type="checkbox" class="row-checkbox rounded text-indigo-600 border-slate-300" value="<?= $r['id'] ?>">
                                    <?php endif; ?>
                                </td>
                                <td class="table-cell font-medium text-slate-900"><?= esc($r['display_name']) ?></td>
                                <td class="table-cell font-mono text-xs text-slate-500"><?= esc($r['role_name']) ?></td>
                                <td class="table-cell">
                                    <?php if ($r['is_system']): ?>
                                        <span class="badge badge-info">System Default</span>
                                    <?php else: ?>
                                        <span class="badge badge-neutral">Custom Role</span>
                                    <?php endif; ?>
                                </td>
                                <td class="table-cell"><?= getStatusBadge($r['status']) ?></td>
                                <td class="table-cell text-right text-sm font-medium">
                                    <?php if($r['role_name'] !== 'Owner'): ?>
                                        <a href="../permissions/matrix.php?role_id=<?= $r['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Permissions</a>
                                        <a href="edit.php?id=<?= $r['id'] ?>" class="text-slate-500 hover:text-slate-700">Edit Name</a>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic">Full Access</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="border-t border-slate-100 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <select id="bulkActionSelect" class="form-input text-xs py-1">
                            <option value="">Bulk Actions</option>
                            <option value="activate">Activate</option>
                            <option value="deactivate">Deactivate</option>
                            <option value="delete">Delete (Archive)</option>
                        </select>
                        <button id="applyBulkAction" class="btn-secondary text-xs py-1 px-2">Apply</button>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
<script>
$(document).ready(function() {
    $('#applyBulkAction').on('click', function() {
        let action = $('#bulkActionSelect').val();
        if (!action) {
            showToast('warning', 'Please select an action.');
            return;
        }

        let ids = [];
        $('.row-checkbox:checked').each(function() { ids.push($(this).val()); });

        if (ids.length === 0) {
            showToast('warning', 'Please select at least one role.');
            return;
        }

        if(confirm(`Are you sure you want to apply this action?`)) {
            $.post('<?= BASE_URL ?>api/owner/roles/action.php', { action: action, ids: ids }, function(response) {
                if(response.status === 'success') {
                    showToast('success', response.message);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast('error', response.message);
                }
            }).fail(function() { showToast('error', 'Action failed.'); });
        }
    });
});
</script>
