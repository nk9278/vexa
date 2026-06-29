<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('manage_deliverable_types');

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Get Types
$stmt = $db->prepare("
    SELECT t.id, t.name,
           (SELECT COUNT(*) FROM deliverables d WHERE d.deliverable_type_id = t.id AND d.company_id = t.company_id) as usage_count
    FROM deliverable_types t
    WHERE t.company_id = ?
    ORDER BY t.name ASC
");
$stmt->execute([$company_id]);
$types = $stmt->fetchAll();

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="/owner/deliverables/" class="text-gray-500 hover:text-gray-700 mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Deliverable Types</h1>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Add Custom Type</h3>
            <form id="addTypeForm" class="flex gap-4 items-end">
                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                <input type="hidden" name="action" value="create">

                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type Name (e.g., Instagram Reel, Blog Post, Ad Creative)</label>
                    <input type="text" name="name" required placeholder="Enter deliverable type" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors">
                </div>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium whitespace-nowrap">Add Type</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-4 font-medium text-gray-600 text-sm">Deliverable Type</th>
                        <th class="p-4 font-medium text-gray-600 text-sm">Active Usage</th>
                        <th class="p-4 font-medium text-gray-600 text-sm text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="typesTableBody">
                    <?php if (count($types) > 0): ?>
                        <?php foreach ($types as $t): ?>
                            <tr class="hover:bg-gray-50 transition-colors" data-id="<?= $t['id'] ?>">
                                <td class="p-4">
                                    <div class="font-medium text-gray-800"><?= esc($t['name']) ?></div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none <?= $t['usage_count'] > 0 ? 'text-blue-100 bg-blue-600' : 'text-gray-100 bg-gray-400' ?> rounded-full"><?= $t['usage_count'] ?> items</span>
                                </td>
                                <td class="p-4 text-right">
                                    <?php if($t['usage_count'] == 0): ?>
                                    <button class="text-red-500 hover:text-red-700 transition-colors delete-type-btn" data-id="<?= $t['id'] ?>" title="Delete">
                                        <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                    <?php else: ?>
                                    <span class="text-xs text-gray-400 italic">In Use</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="p-8 text-center text-gray-500">
                                No custom deliverable types added.
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
    $('#addTypeForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const $input = $(this).find('input[name="name"]');

        $btn.prop('disabled', true).text('Adding...');

        $.ajax({
            url: '/api/owner/deliverables/types.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.status === 'success') location.reload();
                else alert(res.message);
            },
            error: function() {
                alert('An error occurred.');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Add Type');
                $input.val('');
            }
        });
    });

    $('.delete-type-btn').on('click', function() {
        if(!confirm('Delete this deliverable type permanently?')) return;

        const id = $(this).data('id');
        const $tr = $(this).closest('tr');

        $.ajax({
            url: '/api/owner/deliverables/types.php',
            method: 'POST',
            data: {
                action: 'delete',
                id: id,
                csrf_token: '<?= getCsrfToken() ?>'
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
