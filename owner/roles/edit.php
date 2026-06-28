<?php
// owner/roles/edit.php
require_once __DIR__ . '/../../includes/functions.php';
requireOwner();

$id = $_GET['id'] ?? 0;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM roles WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmt->execute([$id, $_SESSION['company_id']]);
    $role = $stmt->fetch();
} catch (PDOException $e) {
    $role = null;
}

if (!$role || $role['role_name'] === 'Owner') {
    redirect(BASE_URL . 'errors/403.php');
}

define('PAGE_TITLE', 'Edit Role');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/owner/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/owner/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center">
                    <a href="index.php" class="mr-4 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Edit Role Details</h1>
                    </div>
                </div>
                <a href="../permissions/matrix.php?role_id=<?= $role['id'] ?>" class="btn-secondary text-indigo-600 hover:bg-indigo-50 border-indigo-200">Manage Permissions</a>
            </div>

            <div class="card">
                <form id="editRoleForm">
                    <input type="hidden" name="id" value="<?= $role['id'] ?>">
                    <div class="card-body border-b border-slate-100">
                        <div class="grid grid-cols-1 gap-6">

                            <div>
                                <label class="form-label">Internal System Key</label>
                                <input type="text" class="form-input bg-slate-100 text-slate-500 cursor-not-allowed" value="<?= esc($role['role_name']) ?>" disabled>
                                <p class="text-xs text-slate-500 mt-1">Cannot be changed. Used for strict internal logic.</p>
                            </div>

                            <div>
                                <label class="form-label">Display Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="display_name" class="form-input" required value="<?= esc($role['display_name']) ?>">
                                <p class="text-xs text-slate-500 mt-1">This is how the role will appear throughout the UI.</p>
                            </div>

                            <div>
                                <label class="form-label">Status <span class="text-rose-500">*</span></label>
                                <select name="status" class="form-input" required>
                                    <option value="active" <?= $role['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $role['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="card-body">
                        <div class="flex justify-end gap-3">
                            <a href="index.php" class="btn-secondary">Cancel</a>
                            <button type="submit" class="btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
<script>
$(document).ready(function() {
    $('#editRoleForm').on('submit', function(e) {
        e.preventDefault();
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.text();
        submitBtn.html('<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>').prop('disabled', true);

        $.post('<?= BASE_URL ?>api/owner/roles/update.php', $(this).serialize(), function(response) {
            if(response.status === 'success') {
                showToast('success', response.message);
                setTimeout(() => window.location.href = response.data.redirect, 1000);
            } else {
                showToast('error', response.message);
                submitBtn.text(originalText).prop('disabled', false);
            }
        }).fail(function(xhr) {
            showToast('error', xhr.responseJSON ? xhr.responseJSON.message : 'Network error.');
            submitBtn.text(originalText).prop('disabled', false);
        });
    });
});
</script>
