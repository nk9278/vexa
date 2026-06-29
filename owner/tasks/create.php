<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('create_tasks');

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Get Active Clients
$stmtClients = $db->prepare("SELECT id, name FROM clients WHERE company_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY name ASC");
$stmtClients->execute([$company_id]);
$clients = $stmtClients->fetchAll();

// Get Active CRMs
$stmtCrms = $db->prepare("SELECT id, full_name FROM team_members WHERE company_id = ? AND is_crm = 1 AND status = 'active' AND deleted_at IS NULL ORDER BY full_name ASC");
$stmtCrms->execute([$company_id]);
$crms = $stmtCrms->fetchAll();

// Get Deliverable Types
$stmtTypes = $db->prepare("SELECT id, name FROM deliverable_types WHERE company_id = ? ORDER BY name ASC");
$stmtTypes->execute([$company_id]);
$types = $stmtTypes->fetchAll();

// Note: Smart assignment logic is handled via AJAX when a client is selected,
// so we don't load the full team list upfront unless necessary.

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="/owner/tasks/" class="text-gray-500 hover:text-gray-700 mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Create Manual Task</h1>
                <p class="text-xs text-gray-500 mt-1">Ad-hoc production tasks outside the automated delivery planner.</p>
            </div>
        </div>

        <form id="createTaskForm" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Task Core Info -->
                <div class="space-y-4 md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Task Details</h3>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Task Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Design holiday banner" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description / Brief</label>
                    <textarea name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors" placeholder="Detailed instructions for the production team..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deliverable Type (Optional)</label>
                    <select name="deliverable_type_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors bg-white">
                        <option value="">None / Custom</option>
                        <?php foreach($types as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select name="priority" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors bg-white">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                </div>

                <!-- Assignment Info -->
                <div class="space-y-4 md:col-span-2 mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Client & Assignment</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                    <select name="client_id" id="clientSelect" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors bg-white">
                        <option value="">Select Client</option>
                        <?php foreach($clients as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CRM Manager (Optional)</label>
                    <select name="crm_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors bg-white">
                        <option value="">Select CRM</option>
                        <?php foreach($crms as $crm): ?>
                            <option value="<?= $crm['id'] ?>"><?= esc($crm['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Smart Assign Production Team</label>
                    <div id="smartAssignContainer" class="border border-gray-200 rounded-lg p-4 bg-gray-50 text-center text-sm text-gray-500">
                        Please select a client first to see eligible team members.
                    </div>
                </div>

                <div class="md:col-span-2 mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Schedule</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                    <input type="date" name="due_date" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors">
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                <a href="/owner/tasks/" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancel</a>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium">Create Task</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {

    // Smart Assignment Logic
    $('#clientSelect').on('change', function() {
        const clientId = $(this).val();
        const $container = $('#smartAssignContainer');

        if (!clientId) {
            $container.html('Please select a client first to see eligible team members.').removeClass('bg-white text-left').addClass('bg-gray-50 text-center text-gray-500');
            return;
        }

        $container.html('<div class="py-4 text-center"><svg class="animate-spin h-5 w-5 mx-auto text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>');

        $.ajax({
            url: '/api/owner/tasks/smart_assign.php',
            method: 'GET',
            data: { client_id: clientId },
            success: function(res) {
                if (res.status === 'success') {
                    if (res.data.length === 0) {
                        $container.html('<div class="text-yellow-600 font-medium bg-yellow-50 p-3 rounded">No production team members have been assigned to this client yet. Go to the Client Workspace to assign team members.</div>');
                        return;
                    }

                    let html = '<div class="space-y-2 text-left">';
                    html += '<label class="flex items-center p-2 rounded hover:bg-gray-100 cursor-pointer border border-transparent hover:border-gray-200"><input type="radio" name="assigned_team_id" value="" checked class="mr-3 text-primary focus:ring-primary h-4 w-4"> <span class="text-sm font-medium text-gray-600 italic">Leave Unassigned</span></label>';

                    res.data.forEach(tm => {
                        let badgeColor = tm.availability_status === 'Available' ? 'text-green-600 bg-green-50 border-green-200' : 'text-yellow-600 bg-yellow-50 border-yellow-200';
                        let workloadAlert = tm.current_workload > 5 ? '<span class="ml-2 text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded border border-red-200 font-bold" title="High Workload">High Load</span>' : '';

                        html += `
                            <label class="flex items-center p-3 rounded-lg border border-gray-200 bg-white hover:border-primary cursor-pointer transition-colors shadow-sm">
                                <input type="radio" name="assigned_team_id" value="${tm.team_member_id}" class="mr-4 text-primary focus:ring-primary h-4 w-4">
                                <div class="flex-1 flex justify-between items-center">
                                    <div>
                                        <div class="text-sm font-bold text-gray-800">${tm.full_name} <span class="text-xs text-gray-500 font-normal ml-1">(${tm.role_name || 'No Role'})</span> ${workloadAlert}</div>
                                        <div class="text-[10px] mt-1 text-gray-500 font-medium uppercase tracking-wider">Current Tasks: ${tm.current_workload}</div>
                                    </div>
                                    <div>
                                        <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded border ${badgeColor}">${tm.availability_status}</span>
                                    </div>
                                </div>
                            </label>
                        `;
                    });
                    html += '</div>';
                    $container.html(html).removeClass('bg-gray-50 text-center').addClass('bg-white');
                } else {
                    $container.html('<span class="text-red-500">Error loading team: ' + res.message + '</span>');
                }
            },
            error: function() {
                $container.html('<span class="text-red-500">Failed to communicate with Smart Assign engine.</span>');
            }
        });
    });

    // Form Submission
    $('#createTaskForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.text();

        $btn.prop('disabled', true).text('Creating...');

        $.ajax({
            url: '/api/owner/tasks/create.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.status === 'success') {
                    window.location.href = '/owner/tasks/view.php?id=' + res.data.id;
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
