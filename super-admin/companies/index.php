<?php
// super-admin/companies/index.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

define('PAGE_TITLE', 'Company Management');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';

// Handle Search, Filter & Pagination
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$typeFilter = $_GET['business_type'] ?? '';

$whereParams = [];
$whereClause = "WHERE deleted_at IS NULL";

if (!empty($search)) {
    $whereClause .= " AND (company_name LIKE ? OR company_code LIKE ? OR owner_email LIKE ?)";
    $searchParam = "%{$search}%";
    array_push($whereParams, $searchParam, $searchParam, $searchParam);
}

if (!empty($statusFilter)) {
    $whereClause .= " AND status = ?";
    $whereParams[] = $statusFilter;
}

if (!empty($typeFilter)) {
    $whereClause .= " AND business_type = ?";
    $whereParams[] = $typeFilter;
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM companies $whereClause ORDER BY created_at DESC");
    $stmt->execute($whereParams);
    $companies = $stmt->fetchAll();
} catch (PDOException $e) {
    $companies = [];
}
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Companies</h1>
                    <p class="mt-1 text-sm text-slate-500">Manage SaaS tenants, statuses, and profiles.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-3">
                    <button type="button" class="btn-secondary" onclick="showToast('info', 'Export CSV feature placeholder.');">Export</button>
                    <a href="create.php" class="btn-primary">+ Add Company</a>
                </div>
            </div>

            <!-- Filters Bar -->
            <form method="GET" action="index.php" class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 mb-6 flex flex-col sm:flex-row gap-4 items-end">
                <div class="w-full sm:w-1/3">
                    <label class="block text-xs font-medium text-slate-500 mb-1 uppercase tracking-wider">Search</label>
                    <input type="text" name="search" class="form-input text-sm" value="<?= esc($search) ?>" placeholder="Company Name, Code, Email...">
                </div>
                <div class="w-full sm:w-1/4">
                    <label class="block text-xs font-medium text-slate-500 mb-1 uppercase tracking-wider">Status</label>
                    <select name="status" class="form-input text-sm">
                        <option value="">All Statuses</option>
                        <option value="active" <?= $statusFilter == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="demo" <?= $statusFilter == 'demo' ? 'selected' : '' ?>>Demo</option>
                        <option value="suspended" <?= $statusFilter == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        <option value="inactive" <?= $statusFilter == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="w-full sm:w-1/4">
                    <label class="block text-xs font-medium text-slate-500 mb-1 uppercase tracking-wider">Business Type</label>
                    <select name="business_type" class="form-input text-sm">
                        <option value="">All Types</option>
                        <option value="Agency" <?= $typeFilter == 'Agency' ? 'selected' : '' ?>>Agency</option>
                        <option value="Enterprise" <?= $typeFilter == 'Enterprise' ? 'selected' : '' ?>>Enterprise</option>
                    </select>
                </div>
                <div class="w-full sm:w-auto flex gap-2">
                    <button type="submit" class="btn-primary w-full sm:w-auto">Filter</button>
                    <?php if(!empty($search) || !empty($statusFilter) || !empty($typeFilter)): ?>
                        <a href="index.php" class="btn-secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="card overflow-x-auto">
                <table class="table-container">
                    <thead class="table-header">
                        <tr>
                            <th class="w-12"><input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500 border-slate-300"></th>
                            <th>Company</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($companies)): ?>
                            <tr><td colspan="6" class="table-cell text-center py-8 text-slate-500 italic">No companies found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($companies as $c): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="table-cell"><input type="checkbox" class="row-checkbox rounded text-indigo-600 focus:ring-indigo-500 border-slate-300" value="<?= $c['id'] ?>"></td>
                                <td class="table-cell">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-slate-200 rounded-full flex items-center justify-center font-bold text-slate-500">
                                            <?= substr(esc($c['company_name']), 0, 1) ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-slate-900"><?= esc($c['company_name']) ?></div>
                                            <div class="text-sm text-slate-500">Code: <?= esc($c['company_code']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-cell">
                                    <div class="text-sm text-slate-900"><?= esc($c['owner_name']) ?></div>
                                    <div class="text-sm text-slate-500"><?= esc($c['owner_email']) ?></div>
                                </td>
                                <td class="table-cell">
                                    <?= getStatusBadge($c['status']) ?>
                                </td>
                                <td class="table-cell text-sm text-slate-500">
                                    <?= formatDate($c['created_at']) ?>
                                </td>
                                <td class="table-cell text-right text-sm font-medium">
                                    <a href="view.php?id=<?= $c['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                    <a href="edit.php?id=<?= $c['id'] ?>" class="text-slate-500 hover:text-slate-700">Edit</a>
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
                            <option value="suspend">Suspend</option>
                            <option value="activate">Activate</option>
                            <option value="delete">Delete</option>
                        </select>
                        <button id="applyBulkAction" class="btn-secondary text-xs py-1 px-2">Apply</button>
                    </div>
                    <div class="flex gap-2">
                        <button class="btn-secondary text-xs">Previous</button>
                        <button class="btn-secondary text-xs">Next</button>
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
        $('.row-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length === 0) {
            showToast('warning', 'Please select at least one company.');
            return;
        }

        if(confirm(`Are you sure you want to ${action} ${ids.length} companies?`)) {
            $.post('<?= BASE_URL ?>api/super-admin/companies/action.php', {
                action: action,
                ids: ids
            }, function(response) {
                if(response.status === 'success') {
                    showToast('success', response.message);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast('error', response.message);
                }
            }).fail(function(xhr) {
                let res = xhr.responseJSON;
                showToast('error', res ? res.message : 'Action failed.');
            });
        }
    });
});
</script>
