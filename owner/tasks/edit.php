<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('edit_tasks');

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /owner/tasks/");
    exit;
}

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

$stmt = $db->prepare("SELECT * FROM tasks WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
$stmt->execute([$id, $company_id]);
$task = $stmt->fetch();

if (!$task) {
    header("Location: /owner/tasks/");
    exit;
}

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="/owner/tasks/view.php?id=<?= $id ?>" class="text-gray-500 hover:text-gray-700 mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Edit Task #<?= str_pad($task['id'], 4, '0', STR_PAD_LEFT) ?></h1>
        </div>

        <form id="editTaskForm" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
            <input type="hidden" name="id" value="<?= $task['id'] ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Task Title *</label>
                    <input type="text" name="title" value="<?= esc($task['title']) ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description / Brief</label>
                    <textarea name="description" rows="5" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors"><?= esc($task['description'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select name="priority" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors bg-white">
                        <option value="Low" <?= $task['priority'] === 'Low' ? 'selected' : '' ?>>Low</option>
                        <option value="Medium" <?= $task['priority'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="High" <?= $task['priority'] === 'High' ? 'selected' : '' ?>>High</option>
                        <option value="Urgent" <?= $task['priority'] === 'Urgent' ? 'selected' : '' ?>>Urgent</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors bg-white">
                        <?php
                        $statuses = ['Pending', 'Assigned', 'Accepted', 'In Progress', 'Ready For Review', 'Approved', 'Rejected', 'Completed', 'Cancelled', 'On Hold'];
                        foreach($statuses as $s) {
                            $sel = ($task['status'] === $s) ? 'selected' : '';
                            echo "<option value=\"$s\" $sel>$s</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                    <input type="date" name="due_date" value="<?= esc($task['due_date'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Internal Remarks</label>
                    <input type="text" name="remarks" value="<?= esc($task['remarks'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none transition-colors">
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t pt-6">
                <a href="/owner/tasks/view.php?id=<?= $id ?>" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancel</a>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#editTaskForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.text();

        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: '/api/owner/tasks/update.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.status === 'success') {
                    window.location.href = '/owner/tasks/view.php?id=<?= $id ?>';
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
