<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('view_crm');

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /owner/crm/");
    exit;
}

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Get CRM
$stmt = $db->prepare("
    SELECT t.*, r.display_name as role_name, d.name as department_name
    FROM team_members t
    LEFT JOIN roles r ON t.role_id = r.id
    LEFT JOIN departments d ON t.department_id = d.id
    WHERE t.id = ? AND t.company_id = ? AND t.is_crm = 1 AND t.deleted_at IS NULL
");
$stmt->execute([$id, $company_id]);
$crm = $stmt->fetch();

if (!$crm) {
    header("Location: /owner/crm/");
    exit;
}

// Get Assigned Clients
$stmtClients = $db->prepare("
    SELECT c.id, c.name, c.status, ca.assigned_at
    FROM client_assignments ca
    JOIN clients c ON ca.client_id = c.id
    WHERE ca.crm_id = ? AND ca.company_id = ? AND c.deleted_at IS NULL
    ORDER BY ca.assigned_at DESC
");
$stmtClients->execute([$id, $company_id]);
$assigned_clients = $stmtClients->fetchAll();

// Get unassigned active clients for dropdown
$stmtAllClients = $db->prepare("
    SELECT id, name FROM clients
    WHERE company_id = ? AND status = 'active' AND deleted_at IS NULL
    AND id NOT IN (SELECT client_id FROM client_assignments WHERE crm_id = ? AND company_id = ?)
    ORDER BY name ASC
");
$stmtAllClients->execute([$company_id, $id, $company_id]);
$available_clients = $stmtAllClients->fetchAll();

// Get Production Team Members for Assignment
$stmtTeam = $db->prepare("
    SELECT t.id, t.full_name, t.availability_status, t.status as account_status, r.display_name as role_name,
           (SELECT COUNT(*) FROM client_team_assignments cta WHERE cta.team_member_id = t.id) as current_clients
    FROM team_members t
    LEFT JOIN roles r ON t.role_id = r.id
    WHERE t.company_id = ? AND t.is_crm = 0 AND t.deleted_at IS NULL
    ORDER BY t.full_name ASC
");
$stmtTeam->execute([$company_id]);
$available_team = $stmtTeam->fetchAll();

// Fetch recent activity
$stmtActivity = $db->prepare("
    SELECT action, details, created_at
    FROM system_logs
    WHERE company_id = ? AND module = 'crm' AND record_id = ?
    ORDER BY created_at DESC LIMIT 10
");
$stmtActivity->execute([$company_id, $id]);
$activities = $stmtActivity->fetchAll();


require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <a href="/owner/crm/" class="text-gray-500 hover:text-gray-700 mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">CRM Profile & Assignments</h1>
            </div>
            <div class="flex gap-2">
                <?php if (hasPermission('manage_crm')): ?>
                    <a href="/owner/crm/edit.php?id=<?= $crm['id'] ?>" class="bg-white text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors border border-gray-300 font-medium text-sm">Edit Profile</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left Column: Identity Card -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center relative">
                    <?php if ($crm['status'] === 'active'): ?>
                        <span class="absolute top-4 right-4 w-3 h-3 bg-green-500 rounded-full border-2 border-white" title="Active"></span>
                    <?php else: ?>
                        <span class="absolute top-4 right-4 w-3 h-3 bg-gray-400 rounded-full border-2 border-white" title="Inactive/Archived"></span>
                    <?php endif; ?>

                    <div class="w-24 h-24 mx-auto rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-3xl border-4 border-white shadow-sm mb-4 relative overflow-hidden">
                        <?php if ($crm['profile_photo']): ?>
                            <img src="<?= esc($crm['profile_photo']) ?>" class="w-full h-full object-cover" alt="Avatar">
                        <?php else: ?>
                            <?= strtoupper(substr($crm['full_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>

                    <h2 class="text-xl font-bold text-gray-800"><?= esc($crm['full_name']) ?></h2>
                    <p class="text-sm text-primary font-medium mt-1"><?= esc($crm['role_name'] ?? 'No Role') ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?= esc($crm['department_name'] ?? 'No Department') ?></p>

                    <div class="mt-6 border-t border-gray-100 pt-6 text-left">
                        <div class="mb-4">
                            <span class="block text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Email</span>
                            <span class="text-sm text-gray-800 break-all"><?= esc($crm['email']) ?></span>
                        </div>
                        <div class="mb-4">
                            <span class="block text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Mobile</span>
                            <span class="text-sm text-gray-800"><?= esc($crm['mobile'] ?: 'Not provided') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider">Recent Activity</h3>
                    <?php if (empty($activities)): ?>
                        <p class="text-sm text-gray-500 text-center py-4">No recent activity found.</p>
                    <?php else: ?>
                        <div class="relative border-l border-gray-200 ml-3 space-y-6 pb-4">
                            <?php foreach ($activities as $act): ?>
                                <div class="relative pl-6">
                                    <span class="absolute -left-[5px] top-1.5 w-2.5 h-2.5 rounded-full bg-gray-300 ring-4 ring-white"></span>
                                    <div class="text-sm font-medium text-gray-800 mb-0.5">
                                        <?= esc(ucwords(str_replace('_', ' ', $act['action']))) ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?= date('M j, Y, g:i a', strtotime($act['created_at'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Assignments -->
            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-center mb-6 border-b pb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Assigned Clients</h3>
                        <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-1 rounded-full"><?= count($assigned_clients) ?> Total</span>
                    </div>

                    <?php if (hasPermission('assign_clients')): ?>
                        <form id="assignClientForm" class="flex gap-3 mb-6 bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <input type="hidden" name="action" value="assign">
                            <input type="hidden" name="crm_id" value="<?= $crm['id'] ?>">

                            <select name="client_id" class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm" required>
                                <option value="">Select a client to assign...</option>
                                <?php foreach ($available_clients as $ac): ?>
                                    <option value="<?= $ac['id'] ?>"><?= esc($ac['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium text-sm whitespace-nowrap">Assign Client</button>
                        </form>
                    <?php endif; ?>

                    <?php if (count($assigned_clients) > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($assigned_clients as $client): ?>
                                <div class="border border-gray-200 rounded-lg p-4 flex justify-between items-center hover:shadow-md transition-shadow bg-white">
                                    <div>
                                        <h4 class="font-medium text-gray-800"><?= esc($client['name']) ?></h4>
                                        <div class="text-xs text-gray-500 mt-1">Assigned: <?= date('M j, Y', strtotime($client['assigned_at'])) ?></div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full <?= $client['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                                            <?= esc($client['status']) ?>
                                        </span>
                                        <?php if (hasPermission('assign_team')): ?>
                                            <button type="button" class="text-indigo-500 hover:text-indigo-700 transition-colors assign-team-btn" data-client-id="<?= $client['id'] ?>" data-client-name="<?= esc($client['name']) ?>" title="Assign Team Members">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (hasPermission('assign_clients')): ?>
                                            <button type="button" class="text-red-400 hover:text-red-600 transition-colors remove-client-btn" data-id="<?= $client['id'] ?>" title="Remove Assignment">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Team Assignments List for this client -->
                                <?php
                                    $stmtTeamAssign = $db->prepare("
                                        SELECT t.id, t.full_name, r.display_name as role_name
                                        FROM client_team_assignments cta
                                        JOIN team_members t ON cta.team_member_id = t.id
                                        LEFT JOIN roles r ON t.role_id = r.id
                                        WHERE cta.client_id = ? AND cta.company_id = ?
                                    ");
                                    $stmtTeamAssign->execute([$client['id'], $company_id]);
                                    $assignedTeam = $stmtTeamAssign->fetchAll();
                                ?>
                                <?php if(count($assignedTeam) > 0): ?>
                                    <div class="mt-2 pl-4 border-l-2 border-indigo-100 grid grid-cols-1 gap-2">
                                        <?php foreach($assignedTeam as $atm): ?>
                                            <div class="text-xs bg-gray-50 px-3 py-2 rounded border border-gray-100 flex justify-between items-center">
                                                <span><span class="font-medium text-gray-800"><?= esc($atm['full_name']) ?></span> <span class="text-gray-500">(<?= esc($atm['role_name'] ?? 'No Role') ?>)</span></span>
                                                <?php if(hasPermission('assign_team')): ?>
                                                    <button type="button" class="text-red-400 hover:text-red-600 transition-colors remove-team-btn" data-client-id="<?= $client['id'] ?>" data-team-id="<?= $atm['id'] ?>" title="Remove Team Member">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500 border-2 border-dashed border-gray-200 rounded-lg">
                            <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <p class="text-sm">No clients assigned yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Performance Placeholder -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-4">Performance Metrics</h3>
                    <div class="flex items-center justify-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                        <div class="text-center">
                            <svg class="w-10 h-10 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <p class="text-gray-500 text-sm font-medium">Deliverable monitoring coming in Phase 22.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Assign Team Modal -->
    <div id="assignTeamModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Assign Production Team</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors closeModal">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form id="assignTeamForm" class="p-6 space-y-4">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="assign">
                <input type="hidden" name="client_id" id="modalClientId" value="">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Client</label>
                    <input type="text" id="modalClientName" class="w-full border border-gray-200 rounded-lg px-4 py-2 bg-gray-50 text-gray-500 cursor-not-allowed" readonly>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Team Member *</label>
                    <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                        <?php foreach ($available_team as $tm): ?>
                            <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="team_member_id" value="<?= $tm['id'] ?>" class="text-primary border-gray-300 focus:ring-primary h-4 w-4" required>
                                <div class="ml-3 flex-1">
                                    <div class="text-sm font-medium text-gray-800"><?= esc($tm['full_name']) ?> <span class="text-gray-500 font-normal ml-1">(<?= esc($tm['role_name'] ?? 'No Role') ?>)</span></div>
                                    <div class="text-xs mt-1 flex gap-2">
                                        <?php
                                            $availColor = 'text-gray-500';
                                            if ($tm['availability_status'] === 'Available') $availColor = 'text-green-600 font-medium';
                                            if ($tm['availability_status'] === 'Busy') $availColor = 'text-yellow-600 font-medium';
                                        ?>
                                        <span class="<?= $availColor ?>"><?= esc($tm['availability_status']) ?></span>
                                        <span class="text-gray-400">• Workload: <?= $tm['current_clients'] ?> Clients</span>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                        <?php if (empty($available_team)): ?>
                            <div class="p-4 text-center text-sm text-gray-500">No active production team members available.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-4 border-t">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors closeModal">Cancel</button>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium">Assign to Client</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
$(document).ready(function() {
    $('#assignClientForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('Assigning...');

        $.ajax({
            url: '/api/owner/crm/assign_client.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.status === 'success') {
                    location.reload();
                } else {
                    alert(res.message);
                    $btn.prop('disabled', false).text('Assign Client');
                }
            },
            error: function() {
                alert('An error occurred.');
                $btn.prop('disabled', false).text('Assign Client');
            }
        });
    });

    $('.remove-client-btn').on('click', function() {
        if(!confirm('Are you sure you want to remove this client from this CRM?')) return;

        const client_id = $(this).data('id');
        const crm_id = <?= $crm['id'] ?>;

        $.ajax({
            url: '/api/owner/crm/assign_client.php',
            method: 'POST',
            data: {
                action: 'remove',
                client_id: client_id,
                crm_id: crm_id,
                csrf_token: '<?= generateCsrfToken() ?>'
            },
            success: function(res) {
                if(res.status === 'success') {
                    location.reload();
                } else {
                    alert(res.message);
                }
            },
            error: function() {
                alert('An error occurred.');
            }
        });
    });

    // Team Assignment Modal Logic
    $('.assign-team-btn').on('click', function() {
        $('#modalClientId').val($(this).data('client-id'));
        $('#modalClientName').val($(this).data('client-name'));
        $('#assignTeamModal').removeClass('hidden');
    });

    $('.closeModal').on('click', function() {
        $('#assignTeamModal').addClass('hidden');
    });

    $('#assignTeamForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('Assigning...');

        $.ajax({
            url: '/api/owner/crm/assign_team.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.status === 'success') {
                    location.reload();
                } else {
                    alert(res.message);
                    $btn.prop('disabled', false).text('Assign to Client');
                }
            },
            error: function() {
                alert('An error occurred.');
                $btn.prop('disabled', false).text('Assign to Client');
            }
        });
    });

    $('.remove-team-btn').on('click', function() {
        if(!confirm('Are you sure you want to remove this team member from the client?')) return;

        const client_id = $(this).data('client-id');
        const team_id = $(this).data('team-id');

        $.ajax({
            url: '/api/owner/crm/assign_team.php',
            method: 'POST',
            data: {
                action: 'remove',
                client_id: client_id,
                team_member_id: team_id,
                csrf_token: '<?= generateCsrfToken() ?>'
            },
            success: function(res) {
                if(res.status === 'success') {
                    location.reload();
                } else {
                    alert(res.message);
                }
            },
            error: function() {
                alert('An error occurred.');
            }
        });
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
