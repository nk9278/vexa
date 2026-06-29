<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('view_deliverables');

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /owner/deliverables/");
    exit;
}

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Get Plan & Client Info
$stmt = $db->prepare("
    SELECT p.*, c.name as client_name, c.brand_name
    FROM monthly_plans p
    JOIN clients c ON p.client_id = c.id
    WHERE p.id = ? AND p.company_id = ? AND p.deleted_at IS NULL
");
$stmt->execute([$id, $company_id]);
$plan = $stmt->fetch();

if (!$plan) {
    header("Location: /owner/deliverables/");
    exit;
}

// Get Deliverable Items
$stmtItems = $db->prepare("
    SELECT d.*, dt.name as type_name
    FROM deliverables d
    JOIN deliverable_types dt ON d.deliverable_type_id = dt.id
    WHERE d.monthly_plan_id = ? AND d.company_id = ? AND d.deleted_at IS NULL
    ORDER BY d.due_date ASC, d.created_at ASC
");
$stmtItems->execute([$id, $company_id]);
$items = $stmtItems->fetchAll();

// Group items by status for Kanban-style counting
$counts = ['Pending' => 0, 'Working' => 0, 'Review' => 0, 'Approved' => 0, 'Completed' => 0, 'Rejected' => 0, 'Delayed' => 0];
$total = count($items);
$completed = 0;
foreach($items as $i) {
    $counts[$i['status']]++;
    if($i['status'] === 'Completed') $completed++;
}
$progress = $total > 0 ? round(($completed / $total) * 100) : 0;

// Get Active Types for quick-add dropdown
$stmtTypes = $db->prepare("SELECT id, name FROM deliverable_types WHERE company_id = ? ORDER BY name ASC");
$stmtTypes->execute([$company_id]);
$types = $stmtTypes->fetchAll();

// Get Team for Assignment Dropdown
$stmtTeam = $db->prepare("
    SELECT id, full_name, is_crm
    FROM team_members
    WHERE company_id = ? AND status = 'active' AND deleted_at IS NULL
    ORDER BY full_name ASC
");
$stmtTeam->execute([$company_id]);
$team = $stmtTeam->fetchAll();


// Helper to decode assigned_team_ids and render
function renderAssignedTeam($jsonStr, $teamData) {
    if(!$jsonStr) return '<span class="text-xs text-gray-400 italic">Unassigned</span>';
    $ids = json_decode($jsonStr, true) ?? [];
    if(empty($ids)) return '<span class="text-xs text-gray-400 italic">Unassigned</span>';

    $html = '<div class="flex -space-x-2 overflow-hidden">';
    foreach($ids as $tid) {
        // Find name
        $name = '?';
        foreach($teamData as $t) {
            if($t['id'] == $tid) { $name = $t['full_name']; break; }
        }
        $initial = strtoupper(substr($name, 0, 1));
        $html .= '<div class="inline-block h-6 w-6 rounded-full ring-2 ring-white bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px] font-bold" title="'.esc($name).'">'.$initial.'</div>';
    }
    $html .= '</div>';
    return $html;
}

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center">
            <a href="/owner/deliverables/?month=<?= $plan['plan_month'] ?>" class="text-gray-500 hover:text-gray-700 mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= date('F Y', strtotime($plan['plan_month'].'-01')) ?> Plan: <?= esc($plan['client_name']) ?></h1>
                <p class="text-xs text-gray-500 mt-1">Overall Progress: <?= $completed ?>/<?= $total ?> Deliverables Completed</p>
            </div>
        </div>
        <div class="flex gap-2">
            <?php if(hasPermission('edit_deliverables')): ?>
                <select id="planStatus" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-primary text-sm font-medium <?= $plan['status'] === 'active' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' ?>">
                    <option value="draft" <?= $plan['status']==='draft'?'selected':'' ?>>Draft</option>
                    <option value="active" <?= $plan['status']==='active'?'selected':'' ?>>Active</option>
                    <option value="completed" <?= $plan['status']==='completed'?'selected':'' ?>>Completed</option>
                </select>
            <?php else: ?>
                <span class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-bold uppercase tracking-wider text-gray-700"><?= esc($plan['status']) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Progress Bar & Metrics -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex justify-between items-end mb-2">
            <span class="font-bold text-gray-800">Completion Status</span>
            <span class="text-2xl font-bold <?= $progress === 100 ? 'text-green-500' : 'text-primary' ?>"><?= $progress ?>%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 mb-6 overflow-hidden">
            <div class="bg-primary h-3 rounded-full transition-all duration-500" style="width: <?= $progress ?>%"></div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 text-center divide-x divide-gray-100 border-t border-gray-100 pt-4">
            <div><div class="text-xl font-bold text-gray-600"><?= $counts['Pending'] ?></div><div class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Pending</div></div>
            <div><div class="text-xl font-bold text-blue-500"><?= $counts['Working'] ?></div><div class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Working</div></div>
            <div><div class="text-xl font-bold text-purple-500"><?= $counts['Review'] ?></div><div class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Review</div></div>
            <div><div class="text-xl font-bold text-yellow-500"><?= $counts['Approved'] ?></div><div class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Approved</div></div>
            <div><div class="text-xl font-bold text-green-500"><?= $counts['Completed'] ?></div><div class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Completed</div></div>
            <div><div class="text-xl font-bold text-red-500"><?= $counts['Delayed'] ?></div><div class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Delayed</div></div>
            <div class="border-none"><div class="text-xl font-bold text-gray-400"><?= $counts['Rejected'] ?></div><div class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Rejected</div></div>
        </div>
    </div>

    <!-- Quick Add Bar -->
    <?php if(hasPermission('create_deliverables')): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <form id="quickAddForm" class="flex flex-col md:flex-row gap-3">
                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="plan_id" value="<?= $id ?>">

                <select name="deliverable_type_id" required class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:border-primary text-sm bg-white">
                    <option value="">Select Type...</option>
                    <?php foreach($types as $t): ?><option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option><?php endforeach; ?>
                </select>
                <input type="text" name="title" required placeholder="Deliverable Title (e.g. FB Ad #4)" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:border-primary text-sm outline-none">
                <input type="date" name="due_date" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-primary focus:border-primary text-sm outline-none" title="Due Date">

                <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-900 transition-colors font-medium text-sm whitespace-nowrap">+ Add Item</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Deliverables List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h3 class="font-bold text-gray-800">Deliverable Items</h3>
            <span class="text-xs text-gray-500 font-medium"><?= count($items) ?> Total</span>
        </div>

        <?php if(empty($items)): ?>
            <div class="p-8 text-center text-gray-500">
                <p>No deliverables exist in this plan yet.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="p-3 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50">Deliverable</th>
                            <th class="p-3 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50">Due Date</th>
                            <th class="p-3 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50">Assigned To</th>
                            <th class="p-3 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50">Status</th>
                            <th class="p-3 text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($items as $i): ?>
                            <tr class="hover:bg-gray-50 group">
                                <td class="p-3">
                                    <div class="font-bold text-gray-800 text-sm"><?= esc($i['title']) ?></div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-0.5"><?= esc($i['type_name']) ?></div>
                                </td>
                                <td class="p-3 text-sm <?= ($i['due_date'] && $i['due_date'] < date('Y-m-d') && $i['status'] !== 'Completed') ? 'text-red-500 font-bold' : 'text-gray-600' ?>">
                                    <?= $i['due_date'] ? date('M j, Y', strtotime($i['due_date'])) : '<span class="text-gray-400 italic">Not set</span>' ?>
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <?= renderAssignedTeam($i['assigned_team_ids'], $team) ?>
                                        <?php if(hasPermission('assign_team')): ?>
                                            <button class="text-xs text-indigo-500 hover:underline open-assign-modal opacity-0 group-hover:opacity-100 transition-opacity" data-id="<?= $i['id'] ?>" data-title="<?= esc($i['title']) ?>" data-current='<?= $i['assigned_team_ids'] ?: "[]" ?>'>Assign</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <?php if(hasPermission('edit_deliverables')): ?>
                                        <!-- Inline Status Update -->
                                        <select class="item-status-select border border-gray-200 rounded text-xs px-2 py-1 outline-none focus:border-primary" data-id="<?= $i['id'] ?>">
                                            <?php
                                            $itemStats = ['Pending', 'Working', 'Review', 'Approved', 'Completed', 'Delayed', 'Rejected'];
                                            foreach($itemStats as $s) {
                                                $sel = ($i['status'] === $s) ? 'selected' : '';
                                                echo "<option value=\"$s\" $sel>$s</option>";
                                            }
                                            ?>
                                        </select>
                                    <?php else: ?>
                                        <span class="text-xs font-bold text-gray-700 bg-gray-100 px-2 py-1 rounded"><?= esc($i['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 text-right">
                                    <?php if(hasPermission('delete_deliverables')): ?>
                                        <button class="text-gray-300 hover:text-red-500 delete-item-btn" data-id="<?= $i['id'] ?>">
                                            <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Assign Team Modal -->
<?php if(hasPermission('assign_team')): ?>
<div id="assignTeamModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800">Assign Team to Deliverable</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="$('#assignTeamModal').addClass('hidden')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="assignTeamForm" class="p-6">
            <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
            <input type="hidden" name="action" value="assign_team">
            <input type="hidden" name="id" id="modalItemId" value="">

            <p id="modalItemTitle" class="text-sm font-bold text-gray-700 mb-4 bg-gray-50 p-2 rounded border border-gray-200 truncate"></p>

            <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100 mb-6">
                <?php foreach ($team as $tm): ?>
                    <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="team_ids[]" value="<?= $tm['id'] ?>" class="modal-team-checkbox rounded text-primary border-gray-300 focus:ring-primary h-4 w-4">
                        <div class="ml-3 text-sm font-medium text-gray-800"><?= esc($tm['full_name']) ?> <?= $tm['is_crm'] ? '<span class="text-[10px] bg-blue-100 text-blue-700 px-1 py-0.5 rounded ml-1">CRM</span>' : '' ?></div>
                    </label>
                <?php endforeach; ?>
                <?php if(empty($team)): ?><div class="p-4 text-center text-sm text-gray-500">No active team members available.</div><?php endif; ?>
            </div>

            <div class="text-right">
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-primary-dark">Save Assignments</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
$(document).ready(function() {

    // Change overall plan status
    $('#planStatus').on('change', function() {
        const val = $(this).val();
        $.post('/api/owner/deliverables/plans.php', {
            action: 'update_status', plan_id: <?= $id ?>, status: val, csrf_token: '<?= getCsrfToken() ?>'
        }, function(res) {
            if(res.status === 'success') location.reload(); else alert(res.message);
        });
    });

    // Quick Add Form
    $('#quickAddForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('Adding...');
        $.ajax({
            url: '/api/owner/deliverables/items.php', method: 'POST', data: $(this).serialize(),
            success: function(res) { if(res.status === 'success') location.reload(); else { alert(res.message); $btn.prop('disabled', false).text('+ Add Item'); } },
            error: function() { alert('Error'); $btn.prop('disabled', false).text('+ Add Item'); }
        });
    });

    // Delete Item
    $('.delete-item-btn').on('click', function() {
        if(!confirm('Remove this deliverable permanently?')) return;
        $.post('/api/owner/deliverables/items.php', {
            action: 'delete', id: $(this).data('id'), csrf_token: '<?= getCsrfToken() ?>'
        }, function(res) {
            if(res.status === 'success') location.reload(); else alert(res.message);
        });
    });

    // Inline Status Change for Items
    $('.item-status-select').on('change', function() {
        $.post('/api/owner/deliverables/items.php', {
            action: 'update_status', id: $(this).data('id'), status: $(this).val(), csrf_token: '<?= getCsrfToken() ?>'
        }, function(res) {
            if(res.status === 'success') {
                // optional: update progress bar visually without reload, or just reload to update counts
                location.reload();
            } else alert(res.message);
        });
    });

    // Modal Assignment Logic
    $('.open-assign-modal').on('click', function() {
        const id = $(this).data('id');
        const title = $(this).data('title');
        const current = $(this).data('current'); // Expects JSON array of IDs

        $('#modalItemId').val(id);
        $('#modalItemTitle').text(title);

        // Reset checkboxes
        $('.modal-team-checkbox').prop('checked', false);

        // Check active ones
        if(current && Array.isArray(current)) {
            current.forEach(tid => {
                $(`.modal-team-checkbox[value="${tid}"]`).prop('checked', true);
            });
        }

        $('#assignTeamModal').removeClass('hidden');
    });

    $('#assignTeamForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('Saving...');
        $.ajax({
            url: '/api/owner/deliverables/items.php', method: 'POST', data: $(this).serialize(),
            success: function(res) { if(res.status === 'success') location.reload(); else { alert(res.message); $btn.prop('disabled', false).text('Save Assignments'); } },
            error: function() { alert('Error'); $btn.prop('disabled', false).text('Save Assignments'); }
        });
    });

});
</script>

<?php require_once '../../includes/footer.php'; ?>
