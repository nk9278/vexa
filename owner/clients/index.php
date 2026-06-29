<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('view_client');

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Dashboard Stats
$stats = [
    'total_clients' => $db->query("SELECT COUNT(*) FROM clients WHERE company_id = $company_id AND deleted_at IS NULL")->fetchColumn(),
    'active_clients' => $db->query("SELECT COUNT(*) FROM clients WHERE company_id = $company_id AND status = 'active' AND deleted_at IS NULL")->fetchColumn(),
    'paused_clients' => $db->query("SELECT COUNT(*) FROM clients WHERE company_id = $company_id AND status = 'paused' AND deleted_at IS NULL")->fetchColumn()
];

// Filters & Search
$status_filter = $_GET['status'] ?? 'active';
$search = $_GET['search'] ?? '';

// Build Query
$query = "SELECT c.*,
            (SELECT COUNT(*) FROM client_assignments ca WHERE ca.client_id = c.id) as crm_count,
            (SELECT COUNT(*) FROM client_team_assignments cta WHERE cta.client_id = c.id) as team_count
          FROM clients c
          WHERE c.company_id = ? AND c.deleted_at IS NULL";
$params = [$company_id];

if ($status_filter !== 'all') {
    $query .= " AND c.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $query .= " AND (c.name LIKE ? OR c.brand_name LIKE ? OR c.owner_name LIKE ? OR c.email LIKE ? OR c.business_category LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, array_fill(0, 5, $searchParam));
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$clients = $stmt->fetchAll();

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Client Workspace</h1>
            <p class="text-gray-600 text-sm">Manage client portfolios, credentials, and production assignments.</p>
        </div>
        <div class="flex gap-2">
            <?php if (hasPermission('create_client')): ?>
                <a href="/owner/clients/create.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium">+ Add Client</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Dashboard Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
            <div class="p-3 rounded-lg bg-indigo-50 text-indigo-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <h3 class="text-gray-500 text-sm font-medium">Total Clients</h3>
                <div class="text-2xl font-bold text-gray-800"><?= $stats['total_clients'] ?></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
            <div class="p-3 rounded-lg bg-green-50 text-green-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h3 class="text-gray-500 text-sm font-medium">Active Accounts</h3>
                <div class="text-2xl font-bold text-gray-800"><?= $stats['active_clients'] ?></div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
            <div class="p-3 rounded-lg bg-yellow-50 text-yellow-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h3 class="text-gray-500 text-sm font-medium">Paused Accounts</h3>
                <div class="text-2xl font-bold text-gray-800"><?= $stats['paused_clients'] ?></div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-t-xl border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex gap-2">
            <form id="filterForm" class="flex gap-2 w-full md:w-auto">
                <select name="status" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-primary text-sm">
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active Clients</option>
                    <option value="lead" <?= $status_filter === 'lead' ? 'selected' : '' ?>>Leads</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="paused" <?= $status_filter === 'paused' ? 'selected' : '' ?>>Paused</option>
                    <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="closed" <?= $status_filter === 'closed' ? 'selected' : '' ?>>Closed</option>
                    <option value="archived" <?= $status_filter === 'archived' ? 'selected' : '' ?>>Archived</option>
                    <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                </select>

                <div class="relative">
                    <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Search business, owner, category..." class="border border-gray-300 rounded-lg pl-9 pr-3 py-2 w-full focus:outline-none focus:border-primary text-sm">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </form>
        </div>

        <?php if (hasPermission('edit_client')): ?>
        <div class="flex gap-2">
            <select id="bulkActionSelect" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-primary text-sm hidden">
                <option value="">Bulk Actions</option>
                <option value="activate">Activate</option>
                <option value="pause">Pause</option>
                <option value="archive">Archive</option>
                <option value="restore">Restore</option>
                <?php if (hasPermission('delete_client')): ?>
                    <option value="delete">Delete Permanently</option>
                <?php endif; ?>
            </select>
            <button id="applyBulkAction" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition-colors font-medium text-sm hidden">Apply</button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-b-xl shadow-sm overflow-hidden border border-gray-100">
        <?php if (count($clients) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <?php if (hasPermission('edit_client')): ?>
                                <th class="p-4 w-12"><input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary focus:ring-primary"></th>
                            <?php endif; ?>
                            <th class="p-4 font-medium text-gray-600 text-sm">Business Info</th>
                            <th class="p-4 font-medium text-gray-600 text-sm">Category</th>
                            <th class="p-4 font-medium text-gray-600 text-sm text-center">Assignments</th>
                            <th class="p-4 font-medium text-gray-600 text-sm">Status</th>
                            <th class="p-4 font-medium text-gray-600 text-sm text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($clients as $client): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <?php if (hasPermission('edit_client')): ?>
                                    <td class="p-4">
                                        <input type="checkbox" value="<?= $client['id'] ?>" class="row-checkbox rounded border-gray-300 text-primary focus:ring-primary">
                                    </td>
                                <?php endif; ?>
                                <td class="p-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold border border-indigo-200 shadow-sm mr-3">
                                            <?= strtoupper(substr($client['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <a href="/owner/clients/profile.php?id=<?= $client['id'] ?>" class="font-bold text-gray-800 hover:text-primary transition-colors"><?= esc($client['name']) ?></a>
                                            <?php if ($client['owner_name']): ?>
                                                <div class="text-xs text-gray-500 mt-0.5"><?= esc($client['owner_name']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="text-sm text-gray-700"><?= esc($client['business_category'] ?: 'Uncategorized') ?></span>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="text-[10px] font-medium bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full" title="CRMs Assigned"><?= $client['crm_count'] ?> CRM</span>
                                        <span class="text-[10px] font-medium bg-purple-50 text-purple-700 px-2 py-0.5 rounded-full" title="Production Team Assigned"><?= $client['team_count'] ?> Team</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <?php
                                    $s = $client['status'];
                                    $sc = 'bg-gray-100 text-gray-700';
                                    if ($s === 'active') $sc = 'bg-green-100 text-green-700';
                                    if ($s === 'lead' || $s === 'pending') $sc = 'bg-blue-100 text-blue-700';
                                    if ($s === 'paused') $sc = 'bg-yellow-100 text-yellow-700';
                                    ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $sc ?>"><?= ucfirst(esc($s)) ?></span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="/owner/clients/profile.php?id=<?= $client['id'] ?>" class="text-gray-400 hover:text-gray-600 transition-colors mx-1" title="Open Workspace">
                                        <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <?php if (hasPermission('edit_client')): ?>
                                        <a href="/owner/clients/edit.php?id=<?= $client['id'] ?>" class="text-blue-500 hover:text-blue-700 transition-colors mx-1" title="Edit">
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
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <p class="text-lg font-medium">No Clients Found</p>
                <p class="text-sm mt-1">Start by adding a client to build their workspace.</p>
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

        if (confirm(`Are you sure you want to perform this bulk action on ${ids.length} clients?`)) {
            $.ajax({
                url: '/api/owner/clients/action.php',
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
