<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('view_tasks');

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Dashboard Stats
$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM tasks WHERE company_id = $company_id AND deleted_at IS NULL")->fetchColumn(),
    'pending' => $db->query("SELECT COUNT(*) FROM tasks WHERE company_id = $company_id AND status = 'Pending' AND deleted_at IS NULL")->fetchColumn(),
    'overdue' => $db->query("SELECT COUNT(*) FROM tasks WHERE company_id = $company_id AND status NOT IN ('Completed', 'Cancelled', 'Rejected') AND due_date < CURRENT_DATE AND deleted_at IS NULL")->fetchColumn(),
    'completed' => $db->query("SELECT COUNT(*) FROM tasks WHERE company_id = $company_id AND status = 'Completed' AND deleted_at IS NULL")->fetchColumn()
];

// Filters
$status_filter = $_GET['status'] ?? 'all';
$priority_filter = $_GET['priority'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build Query
$query = "
    SELECT t.*, c.name as client_name, tm.full_name as assigned_name, dt.name as type_name
    FROM tasks t
    JOIN clients c ON t.client_id = c.id
    LEFT JOIN team_members tm ON t.assigned_team_id = tm.id
    LEFT JOIN deliverable_types dt ON t.deliverable_type_id = dt.id
    WHERE t.company_id = ? AND t.deleted_at IS NULL
";
$params = [$company_id];

if ($status_filter !== 'all') {
    $query .= " AND t.status = ?";
    $params[] = $status_filter;
}
if ($priority_filter !== 'all') {
    $query .= " AND t.priority = ?";
    $params[] = $priority_filter;
}
if ($search) {
    $query .= " AND (t.title LIKE ? OR c.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY CASE WHEN t.due_date IS NULL THEN 1 ELSE 0 END, t.due_date ASC, t.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Get Team for Bulk Assignment
$stmtTeam = $db->prepare("SELECT id, full_name, is_crm FROM team_members WHERE company_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY full_name ASC");
$stmtTeam->execute([$company_id]);
$team = $stmtTeam->fetchAll();

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Task Management Engine</h1>
            <p class="text-gray-600 text-sm">Monitor workloads and track production deliverables.</p>
        </div>
        <div class="flex gap-2">
            <?php if (hasPermission('create_tasks')): ?>
                <a href="/owner/tasks/create.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium shadow-sm">+ Manual Task</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Dashboard Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-gray-500 text-sm font-medium">Total Tasks</h3>
                <div class="text-2xl font-bold text-gray-800 mt-1"><?= $stats['total'] ?></div>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg text-gray-400"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-gray-500 text-sm font-medium">Unassigned / Pending</h3>
                <div class="text-2xl font-bold text-yellow-600 mt-1"><?= $stats['pending'] ?></div>
            </div>
            <div class="p-3 bg-yellow-50 rounded-lg text-yellow-500"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center justify-between border-l-4 border-l-red-500">
            <div>
                <h3 class="text-gray-500 text-sm font-medium">Overdue</h3>
                <div class="text-2xl font-bold text-red-600 mt-1"><?= $stats['overdue'] ?></div>
            </div>
            <div class="p-3 bg-red-50 rounded-lg text-red-500"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-gray-500 text-sm font-medium">Completed</h3>
                <div class="text-2xl font-bold text-green-600 mt-1"><?= $stats['completed'] ?></div>
            </div>
            <div class="p-3 bg-green-50 rounded-lg text-green-500"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-t-xl border-b border-gray-100 flex flex-col lg:flex-row justify-between items-center gap-4">
        <form class="flex flex-wrap gap-2 w-full lg:w-auto">
            <select name="status" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary text-sm bg-white">
                <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Assigned" <?= $status_filter === 'Assigned' ? 'selected' : '' ?>>Assigned</option>
                <option value="In Progress" <?= $status_filter === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="Review" <?= $status_filter === 'Review' ? 'selected' : '' ?>>Ready For Review</option>
                <option value="Completed" <?= $status_filter === 'Completed' ? 'selected' : '' ?>>Completed</option>
                <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>

            <select name="priority" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary text-sm bg-white">
                <option value="all" <?= $priority_filter === 'all' ? 'selected' : '' ?>>All Priorities</option>
                <option value="Low" <?= $priority_filter === 'Low' ? 'selected' : '' ?>>Low</option>
                <option value="Medium" <?= $priority_filter === 'Medium' ? 'selected' : '' ?>>Medium</option>
                <option value="High" <?= $priority_filter === 'High' ? 'selected' : '' ?>>High</option>
                <option value="Urgent" <?= $priority_filter === 'Urgent' ? 'selected' : '' ?>>Urgent</option>
            </select>

            <div class="relative flex-1 min-w-[200px]">
                <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Search tasks or clients..." class="border border-gray-300 rounded-lg pl-9 pr-3 py-2 w-full focus:ring-primary text-sm">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </form>

        <?php if (hasPermission('reassign_tasks') || hasPermission('delete_tasks')): ?>
        <div class="flex gap-2">
            <select id="bulkActionSelect" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary text-sm hidden bg-white">
                <option value="">Bulk Actions...</option>
                <?php if(hasPermission('reassign_tasks')): ?>
                    <option value="reassign">Reassign to Member</option>
                <?php endif; ?>
                <?php if(hasPermission('edit_tasks')): ?>
                    <option value="cancel">Cancel Tasks</option>
                <?php endif; ?>
                <?php if(hasPermission('delete_tasks')): ?>
                    <option value="delete">Delete Permanently</option>
                <?php endif; ?>
            </select>
            <select id="bulkTeamSelect" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary text-sm hidden bg-white">
                <option value="">Select Member...</option>
                <?php foreach($team as $tm): ?>
                    <option value="<?= $tm['id'] ?>"><?= esc($tm['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button id="applyBulkAction" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition-colors font-medium text-sm hidden">Apply</button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Task List -->
    <div class="bg-white rounded-b-xl shadow-sm border border-gray-100 overflow-hidden">
        <?php if (count($tasks) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <?php if (hasPermission('edit_tasks')): ?>
                                <th class="p-4 w-12"><input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary focus:ring-primary"></th>
                            <?php endif; ?>
                            <th class="p-4 font-medium text-gray-600 text-xs uppercase tracking-wider">Task</th>
                            <th class="p-4 font-medium text-gray-600 text-xs uppercase tracking-wider">Priority & Due</th>
                            <th class="p-4 font-medium text-gray-600 text-xs uppercase tracking-wider">Assignment</th>
                            <th class="p-4 font-medium text-gray-600 text-xs uppercase tracking-wider">Status</th>
                            <th class="p-4 font-medium text-gray-600 text-xs uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($tasks as $task): ?>
                            <?php
                                $isOverdue = ($task['due_date'] && $task['due_date'] < date('Y-m-d') && !in_array($task['status'], ['Completed', 'Cancelled', 'Rejected']));
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <?php if (hasPermission('edit_tasks')): ?>
                                    <td class="p-4">
                                        <input type="checkbox" value="<?= $task['id'] ?>" class="row-checkbox rounded border-gray-300 text-primary focus:ring-primary">
                                    </td>
                                <?php endif; ?>
                                <td class="p-4">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">#<?= str_pad($task['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                        <?php if($task['deliverable_id']): ?>
                                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-bold rounded" title="Auto-generated from Delivery Plan">AUTO</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="/owner/tasks/view.php?id=<?= $task['id'] ?>" class="font-bold text-gray-800 hover:text-primary transition-colors text-sm"><?= esc($task['title']) ?></a>
                                    <div class="text-xs text-gray-500 mt-1"><?= esc($task['client_name']) ?> <?= $task['type_name'] ? " • ".esc($task['type_name']) : '' ?></div>
                                </td>
                                <td class="p-4">
                                    <?php
                                        $pc = 'text-gray-600 bg-gray-100';
                                        if($task['priority'] === 'High') $pc = 'text-orange-700 bg-orange-100';
                                        if($task['priority'] === 'Urgent') $pc = 'text-red-700 bg-red-100';
                                    ?>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded <?= $pc ?> mb-1 inline-block"><?= esc($task['priority']) ?></span>
                                    <div class="text-sm font-medium <?= $isOverdue ? 'text-red-600 font-bold' : 'text-gray-600' ?>">
                                        <?= $task['due_date'] ? date('M j, Y', strtotime($task['due_date'])) : '<span class="text-gray-400 italic font-normal">No Date</span>' ?>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <?php if ($task['assigned_name']): ?>
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold mr-2"><?= substr($task['assigned_name'],0,1) ?></div>
                                            <span class="text-sm text-gray-700 font-medium"><?= esc($task['assigned_name']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <?php
                                        $s = $task['status'];
                                        $sc = 'bg-gray-100 text-gray-700 border-gray-200';
                                        if(in_array($s, ['Working', 'In Progress', 'Review', 'Ready For Review'])) $sc = 'bg-blue-50 text-blue-700 border-blue-200';
                                        if(in_array($s, ['Approved', 'Completed'])) $sc = 'bg-green-50 text-green-700 border-green-200';
                                        if(in_array($s, ['Cancelled', 'Rejected'])) $sc = 'bg-red-50 text-red-700 border-red-200';
                                    ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded-lg border <?= $sc ?> shadow-sm"><?= esc($s) ?></span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="/owner/tasks/view.php?id=<?= $task['id'] ?>" class="text-gray-400 hover:text-gray-600 transition-colors mx-1" title="View/Update Task">
                                        <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <?php if(hasPermission('edit_tasks')): ?>
                                    <a href="/owner/tasks/edit.php?id=<?= $task['id'] ?>" class="text-blue-500 hover:text-blue-700 transition-colors mx-1" title="Edit Details">
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
            <div class="p-12 text-center text-gray-500 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50 m-4">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <p class="text-xl font-bold text-gray-700">No Tasks Found</p>
                <p class="text-sm mt-2 text-gray-500">Auto-generate tasks from the Delivery Planner or create one manually.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    const $selectAll = $('#selectAll');
    const $rowCheckboxes = $('.row-checkbox');
    const $bulkSelect = $('#bulkActionSelect');
    const $bulkTeam = $('#bulkTeamSelect');
    const $bulkApply = $('#applyBulkAction');

    function toggleBulkActions() {
        const checkedCount = $('.row-checkbox:checked').length;
        if (checkedCount > 0) {
            $bulkSelect.removeClass('hidden');
            $bulkApply.removeClass('hidden');
        } else {
            $bulkSelect.addClass('hidden');
            $bulkTeam.addClass('hidden');
            $bulkApply.addClass('hidden');
        }
    }

    $bulkSelect.on('change', function() {
        if($(this).val() === 'reassign') {
            $bulkTeam.removeClass('hidden');
        } else {
            $bulkTeam.addClass('hidden');
        }
    });

    $selectAll.on('change', function() {
        $rowCheckboxes.prop('checked', $(this).prop('checked'));
        toggleBulkActions();
    });

    $rowCheckboxes.on('change', function() {
        if (!$(this).prop('checked')) $selectAll.prop('checked', false);
        else if ($('.row-checkbox:checked').length === $rowCheckboxes.length) $selectAll.prop('checked', true);
        toggleBulkActions();
    });

    $bulkApply.on('click', function() {
        const action = $bulkSelect.val();
        if (!action) return alert('Please select an action.');

        const ids = [];
        $('.row-checkbox:checked').each(function() { ids.push($(this).val()); });
        if (ids.length === 0) return;

        let data = { action: action, ids: ids, csrf_token: '<?= getCsrfToken() ?>' };

        if (action === 'reassign') {
            const teamId = $bulkTeam.val();
            if(!teamId) return alert('Please select a team member to reassign to.');
            data.team_id = teamId;
        }

        if (confirm(`Are you sure you want to perform this action on ${ids.length} tasks?`)) {
            $.ajax({
                url: '/api/owner/tasks/action.php',
                method: 'POST',
                data: data,
                success: function(res) {
                    if (res.status === 'success') location.reload();
                    else alert(res.message);
                },
                error: function() { alert('An error occurred.'); }
            });
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
