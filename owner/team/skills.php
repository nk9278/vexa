<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('manage_skills');

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Get All Skills
$stmtSkills = $db->prepare("
    SELECT s.id, s.name, COUNT(tms.team_member_id) as users_count
    FROM skills s
    LEFT JOIN team_member_skills tms ON s.id = tms.skill_id
    WHERE s.company_id = ?
    GROUP BY s.id
    ORDER BY s.name ASC
");
$stmtSkills->execute([$company_id]);
$skills = $stmtSkills->fetchAll();

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="/owner/team/" class="text-gray-500 hover:text-gray-700 mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Manage Skills</h1>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Add New Skill</h3>
            <form id="addSkillForm" class="flex gap-4 items-end">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="create">

                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Skill Name (e.g., Photoshop, SEO, React)</label>
                    <input type="text" name="name" required placeholder="Enter skill name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium">Add Skill</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-4 font-medium text-gray-600 text-sm">Skill Name</th>
                        <th class="p-4 font-medium text-gray-600 text-sm">Assigned Members</th>
                        <th class="p-4 font-medium text-gray-600 text-sm text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="skillsTableBody">
                    <?php if (count($skills) > 0): ?>
                        <?php foreach ($skills as $skill): ?>
                            <tr class="hover:bg-gray-50 transition-colors" data-id="<?= $skill['id'] ?>">
                                <td class="p-4">
                                    <div class="font-medium text-gray-800"><?= esc($skill['name']) ?></div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none <?= $skill['users_count'] > 0 ? 'text-blue-100 bg-blue-600' : 'text-gray-100 bg-gray-400' ?> rounded-full"><?= $skill['users_count'] ?></span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="text-red-500 hover:text-red-700 transition-colors delete-skill-btn" data-id="<?= $skill['id'] ?>" title="Delete">
                                        <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="p-8 text-center text-gray-500">
                                No skills added yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#addSkillForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const $input = $(this).find('input[name="name"]');

        $btn.prop('disabled', true).text('Adding...');

        $.ajax({
            url: '/api/owner/team/skills.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.status === 'success') {
                    // Quick append to avoid reload (for better UX)
                    location.reload();
                } else {
                    alert(res.message);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Add Skill');
                $input.val('');
            }
        });
    });

    $('.delete-skill-btn').on('click', function() {
        if(!confirm('Are you sure you want to delete this skill? It will be removed from all members.')) return;

        const id = $(this).data('id');
        const $tr = $(this).closest('tr');

        $.ajax({
            url: '/api/owner/team/skills.php',
            method: 'POST',
            data: {
                action: 'delete',
                id: id,
                csrf_token: '<?= generateCsrfToken() ?>'
            },
            success: function(res) {
                if(res.status === 'success') {
                    $tr.fadeOut(300, function() { $(this).remove(); });
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
