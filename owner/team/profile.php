<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('view_team');

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /owner/team/");
    exit;
}

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Get Member
$stmt = $db->prepare("
    SELECT t.*, r.display_name as role_name, d.name as department_name
    FROM team_members t
    LEFT JOIN roles r ON t.role_id = r.id
    LEFT JOIN departments d ON t.department_id = d.id
    WHERE t.id = ? AND t.company_id = ? AND t.deleted_at IS NULL
");
$stmt->execute([$id, $company_id]);
$member = $stmt->fetch();

if (!$member) {
    header("Location: /owner/team/");
    exit;
}

// Get Member Skills
$stmtMemberSkills = $db->prepare("
    SELECT s.name
    FROM team_member_skills tms
    JOIN skills s ON tms.skill_id = s.id
    WHERE tms.team_member_id = ? AND tms.company_id = ?
");
$stmtMemberSkills->execute([$id, $company_id]);
$skills = $stmtMemberSkills->fetchAll(PDO::FETCH_COLUMN);

// Fetch recent activity logs for this member
$stmtActivity = $db->prepare("
    SELECT action, details, created_at
    FROM system_logs
    WHERE company_id = ? AND module = 'team_member' AND record_id = ?
    ORDER BY created_at DESC LIMIT 10
");
$stmtActivity->execute([$company_id, $id]);
$activities = $stmtActivity->fetchAll();


require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="max-w-5xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <a href="/owner/team/" class="text-gray-500 hover:text-gray-700 mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Team Profile</h1>
            </div>
            <div class="flex gap-2">
                <?php if (hasPermission('edit_team')): ?>
                    <a href="/owner/team/edit.php?id=<?= $member['id'] ?>" class="bg-white text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors border border-gray-300 font-medium text-sm">Edit Profile</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Left Column: Identity Card -->
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center relative">
                    <?php if ($member['status'] === 'active'): ?>
                        <span class="absolute top-4 right-4 w-3 h-3 bg-green-500 rounded-full border-2 border-white" title="Active"></span>
                    <?php else: ?>
                        <span class="absolute top-4 right-4 w-3 h-3 bg-gray-400 rounded-full border-2 border-white" title="Inactive/Archived"></span>
                    <?php endif; ?>

                    <div class="w-24 h-24 mx-auto rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-3xl border-4 border-white shadow-sm mb-4 relative overflow-hidden group">
                        <?php if ($member['profile_photo']): ?>
                            <img src="<?= esc($member['profile_photo']) ?>" class="w-full h-full object-cover" alt="Avatar">
                        <?php else: ?>
                            <?= strtoupper(substr($member['full_name'], 0, 1)) ?>
                        <?php endif; ?>

                        <?php if (hasPermission('edit_team')): ?>
                            <label class="absolute inset-0 bg-black/50 hidden group-hover:flex items-center justify-center cursor-pointer transition-opacity">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <input type="file" id="photoUploadInput" class="hidden" accept="image/*">
                            </label>
                        <?php endif; ?>
                    </div>

                    <h2 class="text-xl font-bold text-gray-800"><?= esc($member['full_name']) ?></h2>
                    <p class="text-sm text-primary font-medium mt-1"><?= esc($member['role_name'] ?? 'No Role') ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?= esc($member['department_name'] ?? 'No Department') ?></p>

                    <div class="mt-6 border-t border-gray-100 pt-6 text-left">
                        <div class="mb-4">
                            <span class="block text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Email</span>
                            <span class="text-sm text-gray-800 break-all"><?= esc($member['email']) ?></span>
                        </div>
                        <div class="mb-4">
                            <span class="block text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Mobile</span>
                            <span class="text-sm text-gray-800"><?= esc($member['mobile'] ?: 'Not provided') ?></span>
                        </div>
                        <div class="mb-4">
                            <span class="block text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Availability</span>
                            <?php
                                $avail = $member['availability_status'];
                                $availColor = 'bg-gray-100 text-gray-700';
                                if ($avail === 'Available') $availColor = 'bg-green-100 text-green-700';
                                if ($avail === 'Busy') $availColor = 'bg-yellow-100 text-yellow-700';
                                if ($avail === 'On Leave') $availColor = 'bg-red-100 text-red-700';
                            ?>
                            <span class="px-2 py-1 text-xs font-medium rounded-full inline-block mt-1 <?= $availColor ?>"><?= esc($avail) ?></span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Employment</span>
                            <span class="text-sm text-gray-800"><?= esc($member['employment_type']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Skills List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider">Production Skills</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php if (empty($skills)): ?>
                            <span class="text-sm text-gray-500">No skills assigned.</span>
                        <?php else: ?>
                            <?php foreach ($skills as $skill): ?>
                                <span class="bg-primary/10 text-primary border border-primary/20 px-3 py-1 rounded-lg text-xs font-medium">
                                    <?= esc($skill) ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Activity & Assignments -->
            <div class="md:col-span-2 space-y-6">
                <!-- Assignments Placeholder (For Future Phases) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Current Workload</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="border border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50">
                            <div class="text-2xl font-bold text-gray-400 mb-1">0</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider font-medium">Active Clients</div>
                            <div class="text-[10px] text-gray-400 mt-2">Coming in Phase 21</div>
                        </div>
                        <div class="border border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50">
                            <div class="text-2xl font-bold text-gray-400 mb-1">0</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider font-medium">Deliverables</div>
                            <div class="text-[10px] text-gray-400 mt-2">Coming in Phase 22</div>
                        </div>
                        <div class="border border-dashed border-gray-300 rounded-lg p-4 text-center bg-gray-50">
                            <div class="text-2xl font-bold text-gray-400 mb-1">0</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider font-medium">Pending Tasks</div>
                            <div class="text-[10px] text-gray-400 mt-2">Coming in Phase 22</div>
                        </div>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Recent Activity</h3>
                    <?php if (empty($activities)): ?>
                        <p class="text-sm text-gray-500 text-center py-4">No recent activity found.</p>
                    <?php else: ?>
                        <div class="relative border-l border-gray-200 ml-3 space-y-6 pb-4">
                            <?php foreach ($activities as $act): ?>
                                <div class="relative pl-6">
                                    <span class="absolute -left-[5px] top-1.5 w-2.5 h-2.5 rounded-full bg-gray-300 ring-4 ring-white"></span>
                                    <div class="text-sm font-medium text-gray-800 mb-0.5">
                                        <?php
                                            // Make action name human readable
                                            echo esc(ucwords(str_replace('_', ' ', $act['action'])));
                                        ?>
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

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#photoUploadInput').on('change', function() {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('profile_photo', file);
        formData.append('csrf_token', '<?= generateCsrfToken() ?>');

        // This is a placeholder for actual photo upload via API.
        // For the sake of Phase 20 constraints, we require the update endpoint to handle the path,
        // or a dedicated upload endpoint.

        $.ajax({
            url: '/api/owner/team/upload-photo.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    // Update database silently via another call, or just refresh to show it
                    // Assuming we need to save the path to the user profile:
                    $.post('/api/owner/team/update.php', {
                        id: <?= $member['id'] ?>,
                        profile_photo: res.data.path, // Assuming update.php handles this if passed (needs slight mod if so)
                        full_name: '<?= esc($member['full_name']) ?>', // required fields
                        email: '<?= esc($member['email']) ?>',
                        role_id: '<?= $member['role_id'] ?>',
                        csrf_token: '<?= generateCsrfToken() ?>'
                    }).done(function() {
                        location.reload();
                    });
                } else {
                    alert(res.message);
                }
            },
            error: function() {
                alert('Upload failed.');
            }
        });
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
