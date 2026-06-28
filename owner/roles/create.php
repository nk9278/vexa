<?php
// owner/roles/create.php
require_once __DIR__ . '/../../includes/functions.php';
requireOwner();

define('PAGE_TITLE', 'Create Role');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/owner/includes/sidebar.php';

try {
    $db = Database::getInstance()->getConnection();
    // Fetch system roles to use as templates
    $stmt = $db->prepare("SELECT id, display_name FROM roles WHERE company_id = ? AND is_system = 1 AND deleted_at IS NULL AND role_name != 'Owner'");
    $stmt->execute([$_SESSION['company_id']]);
    $templates = $stmt->fetchAll();
} catch (PDOException $e) {
    $templates = [];
}
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
                        <h1 class="text-2xl font-bold text-slate-900">Create Custom Role</h1>
                        <p class="mt-1 text-sm text-slate-500">Define a new job role. You can assign precise permissions on the next screen.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <form id="createRoleForm">
                    <div class="card-body border-b border-slate-100">
                        <div class="grid grid-cols-1 gap-6">

                            <div>
                                <label class="form-label">Role Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="role_name" class="form-input" required placeholder="e.g. Lead Designer">
                                <p class="text-xs text-slate-500 mt-1">This will be used as both the internal key and display name initially.</p>
                            </div>

                            <div>
                                <label class="form-label">Template (Optional)</label>
                                <select name="template_id" class="form-input">
                                    <option value="">Start with blank permissions</option>
                                    <optgroup label="Copy permissions from...">
                                        <?php foreach($templates as $t): ?>
                                            <option value="<?= $t['id'] ?>"><?= esc($t['display_name']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                                <p class="text-xs text-slate-500 mt-1">Cloning a template makes setting up permissions faster.</p>
                            </div>

                        </div>
                    </div>

                    <div class="card-body">
                        <div class="flex justify-end gap-3">
                            <a href="index.php" class="btn-secondary">Cancel</a>
                            <button type="submit" class="btn-primary">Create & Configure Permissions</button>
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
    $('#createRoleForm').on('submit', function(e) {
        e.preventDefault();
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.text();
        submitBtn.html('<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>').prop('disabled', true);

        $.post('<?= BASE_URL ?>api/owner/roles/create.php', $(this).serialize(), function(response) {
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
