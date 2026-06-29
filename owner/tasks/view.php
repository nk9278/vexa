<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('view_tasks');

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /owner/tasks/");
    exit;
}

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Get Task Data
$stmt = $db->prepare("
    SELECT t.*, c.name as client_name, dt.name as type_name,
           tm.full_name as assigned_name, tm.profile_photo as assigned_photo,
           cb.first_name as created_by_first, cb.last_name as created_by_last,
           crm.full_name as crm_name
    FROM tasks t
    JOIN clients c ON t.client_id = c.id
    LEFT JOIN deliverable_types dt ON t.deliverable_type_id = dt.id
    LEFT JOIN team_members tm ON t.assigned_team_id = tm.id
    LEFT JOIN users cb ON t.assigned_by = cb.id
    LEFT JOIN team_members crm ON t.crm_id = crm.id
    WHERE t.id = ? AND t.company_id = ? AND t.deleted_at IS NULL
");
$stmt->execute([$id, $company_id]);
$task = $stmt->fetch();

if (!$task) {
    header("Location: /owner/tasks/");
    exit;
}

// Get Timeline
$stmtTime = $db->prepare("
    SELECT tl.*, u.first_name, u.last_name
    FROM task_timeline tl
    JOIN users u ON tl.user_id = u.id
    WHERE tl.task_id = ? AND tl.company_id = ?
    ORDER BY tl.created_at DESC
");
$stmtTime->execute([$id, $company_id]);
$timeline = $stmtTime->fetchAll();

// Get eligible team for reassignment
$team = [];
if (hasPermission('reassign_tasks')) {
    // For smart reassignment, we just pull the active team.
    // More complex UI could reuse the smart_assign API.
    $stmtTeam = $db->prepare("SELECT id, full_name, is_crm FROM team_members WHERE company_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY full_name ASC");
    $stmtTeam->execute([$company_id]);
    $team = $stmtTeam->fetchAll();
}

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div class="flex items-center">
                <a href="/owner/tasks/" class="text-gray-500 hover:text-gray-700 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded uppercase tracking-wider">#<?= str_pad($task['id'], 4, '0', STR_PAD_LEFT) ?></span>
                        <?php if($task['deliverable_id']): ?>
                            <a href="/owner/deliverables/" class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-bold rounded hover:underline" title="Auto-generated from Delivery Plan">Deliverable Item</a>
                        <?php endif; ?>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800"><?= esc($task['title']) ?></h1>
                </div>
            </div>

            <div class="flex gap-2">
                <?php if (hasPermission('edit_tasks')): ?>
                    <select id="taskStatusSelect" class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary text-sm font-bold bg-white">
                        <?php
                        $statuses = ['Pending', 'Assigned', 'Accepted', 'In Progress', 'Ready For Review', 'Approved', 'Rejected', 'Completed', 'Cancelled', 'On Hold'];
                        foreach($statuses as $s) {
                            $sel = ($task['status'] === $s) ? 'selected' : '';
                            echo "<option value=\"$s\" $sel>$s</option>";
                        }
                        ?>
                    </select>
                    <a href="/owner/tasks/edit.php?id=<?= $id ?>" class="bg-white text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors border border-gray-300 font-medium text-sm">Edit Task</a>
                <?php else: ?>
                    <span class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-bold uppercase tracking-wider text-gray-700 border border-gray-200"><?= esc($task['status']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left Column: Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Main Info Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-start mb-6 pb-6 border-b border-gray-100">
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Client</span>
                            <a href="/owner/clients/profile.php?id=<?= $task['client_id'] ?>" class="text-lg font-bold text-primary hover:underline"><?= esc($task['client_name']) ?></a>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Priority</span>
                            <?php
                                $pc = 'text-gray-600 bg-gray-100';
                                if($task['priority'] === 'High') $pc = 'text-orange-700 bg-orange-100 border-orange-200';
                                if($task['priority'] === 'Urgent') $pc = 'text-red-700 bg-red-100 border-red-200';
                            ?>
                            <span class="px-3 py-1 text-xs font-bold uppercase rounded border <?= $pc ?>"><?= esc($task['priority']) ?></span>
                        </div>
                    </div>

                    <div class="mb-6">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-2">Description / Brief</span>
                        <?php if($task['description']): ?>
                            <div class="text-gray-800 whitespace-pre-wrap text-sm leading-relaxed"><?= esc($task['description']) ?></div>
                        <?php else: ?>
                            <div class="text-gray-400 italic text-sm">No description provided.</div>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 rounded-lg p-4">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Due Date</span>
                            <span class="text-sm font-medium text-gray-800"><?= $task['due_date'] ? date('M j, Y', strtotime($task['due_date'])) : 'None' ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Task Type</span>
                            <span class="text-sm font-medium text-gray-800"><?= esc($task['type_name'] ?? 'Custom') ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">CRM Manager</span>
                            <span class="text-sm font-medium text-gray-800"><?= esc($task['crm_name'] ?? 'Unassigned') ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Created By</span>
                            <span class="text-sm font-medium text-gray-800"><?= esc($task['created_by_first'] . ' ' . $task['created_by_last']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Assignments & History -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Assignment Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider border-b pb-2">Assigned To</h3>

                    <?php if($task['assigned_team_id']): ?>
                        <div class="flex items-center p-3 border border-gray-200 rounded-lg bg-gray-50">
                            <?php if ($task['assigned_photo']): ?>
                                <img src="<?= esc($task['assigned_photo']) ?>" class="w-10 h-10 rounded-full object-cover border border-gray-200 mr-3">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold mr-3"><?= substr($task['assigned_name'],0,1) ?></div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <div class="font-bold text-gray-800 text-sm"><?= esc($task['assigned_name']) ?></div>
                                <div class="text-[10px] text-green-600 font-medium uppercase tracking-wider mt-0.5">Production Team</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="p-4 text-center border border-dashed border-gray-300 rounded-lg text-gray-500 text-sm">
                            Task is currently unassigned.
                        </div>
                    <?php endif; ?>

                    <?php if(hasPermission('reassign_tasks')): ?>
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <form id="reassignForm" class="flex gap-2">
                                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                                <input type="hidden" name="action" value="reassign">
                                <input type="hidden" name="ids[]" value="<?= $id ?>">

                                <select name="team_id" required class="flex-1 border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-primary text-sm outline-none">
                                    <option value="">Select Member...</option>
                                    <?php foreach($team as $tm): ?>
                                        <option value="<?= $tm['id'] ?>" <?= $task['assigned_team_id'] == $tm['id'] ? 'disabled' : '' ?>><?= esc($tm['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="bg-gray-800 text-white px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-gray-900 transition-colors">Reassign</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-6 uppercase tracking-wider">Task Timeline</h3>
                    <?php if (empty($timeline)): ?>
                        <p class="text-sm text-gray-500 text-center py-4">No activity recorded.</p>
                    <?php else: ?>
                        <div class="relative border-l-2 border-gray-200 ml-3 space-y-6 pb-2">
                            <?php foreach ($timeline as $act): ?>
                                <div class="relative pl-6">
                                    <span class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-gray-300 ring-4 ring-white"></span>
                                    <div class="text-sm font-bold text-gray-800 mb-0.5">
                                        <?= esc($act['action']) ?>
                                    </div>
                                    <div class="text-[10px] text-gray-500 uppercase tracking-wider font-medium mb-1">
                                        <?= date('M j, Y g:i A', strtotime($act['created_at'])) ?> by <?= esc($act['first_name']) ?>
                                    </div>
                                    <?php if($act['details']): ?>
                                        <div class="text-xs text-gray-600 bg-gray-50 p-2 rounded border border-gray-100 mt-1">
                                            <?= esc($act['details']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    // Status Change
    $('#taskStatusSelect').on('change', function() {
        const val = $(this).val();
        $.post('/api/owner/tasks/update.php', {
            id: <?= $id ?>,
            title: '<?= esc($task['title']) ?>', // required by API
            status: val,
            csrf_token: '<?= getCsrfToken() ?>'
        }, function(res) {
            if(res.status === 'success') location.reload(); else alert(res.message);
        });
    });

    // Reassignment
    $('#reassignForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('...');

        $.ajax({
            url: '/api/owner/tasks/action.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.status === 'success') location.reload();
                else { alert(res.message); $btn.prop('disabled', false).text('Reassign'); }
            },
            error: function() { alert('Error.'); $btn.prop('disabled', false).text('Reassign'); }
        });
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
