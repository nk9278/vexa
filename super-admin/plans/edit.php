<?php
// super-admin/plans/edit.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

$id = $_GET['id'] ?? 0;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM saas_plans WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    $plan = $stmt->fetch();
} catch (PDOException $e) {
    $plan = null;
}

if (!$plan) {
    redirect(BASE_URL . 'errors/404.php');
}

define('PAGE_TITLE', 'Edit Plan');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center">
                    <a href="index.php" class="mr-4 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Edit Plan: <?= esc($plan['plan_name']) ?></h1>
                    </div>
                </div>
            </div>

            <div class="card">
                <form id="editPlanForm">
                    <input type="hidden" name="id" value="<?= $plan['id'] ?>">
                    <div class="card-body border-b border-slate-100">
                        <h2 class="text-lg font-medium text-slate-900 mb-6">Plan Details</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Plan Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="plan_name" class="form-input" required value="<?= esc($plan['plan_name']) ?>">
                            </div>
                            <div>
                                <label class="form-label">Plan Code <span class="text-rose-500">*</span></label>
                                <input type="text" name="plan_code" class="form-input" required value="<?= esc($plan['plan_code']) ?>">
                            </div>
                            <div class="md:col-span-2">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-input" rows="2"><?= esc($plan['description']) ?></textarea>
                            </div>
                            <div>
                                <label class="form-label">Billing Cycle <span class="text-rose-500">*</span></label>
                                <select name="plan_type" class="form-input" required>
                                    <option value="monthly" <?= $plan['plan_type'] == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                    <option value="yearly" <?= $plan['plan_type'] == 'yearly' ? 'selected' : '' ?>>Yearly</option>
                                    <option value="lifetime" <?= $plan['plan_type'] == 'lifetime' ? 'selected' : '' ?>>Lifetime</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Price <span class="text-rose-500">*</span></label>
                                <input type="number" step="0.01" name="price" class="form-input" required value="<?= esc($plan['price']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="card-body bg-slate-50 border-b border-slate-100">
                        <h2 class="text-lg font-medium text-slate-900 mb-6">Usage Limits</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="form-label">Max Employees</label>
                                <input type="number" name="limit_employees" class="form-input" value="<?= $plan['limit_employees'] ?>">
                            </div>
                            <div>
                                <label class="form-label">Max Projects</label>
                                <input type="number" name="limit_projects" class="form-input" value="<?= $plan['limit_projects'] ?>">
                            </div>
                            <div>
                                <label class="form-label">Storage (MB)</label>
                                <input type="number" name="limit_storage_mb" class="form-input" value="<?= $plan['limit_storage_mb'] ?>">
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="flex justify-end gap-3">
                            <a href="index.php" class="btn-secondary">Cancel</a>
                            <button type="submit" class="btn-primary">Update Plan</button>
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
    $('#editPlanForm').on('submit', function(e) {
        e.preventDefault();
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.text();
        submitBtn.html('<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>').prop('disabled', true);

        $.ajax({
            url: '<?= BASE_URL ?>api/super-admin/plans/update.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.status === 'success') {
                    showToast('success', response.message);
                    setTimeout(() => window.location.href = response.data.redirect, 1500);
                } else {
                    showToast('error', response.message);
                    submitBtn.text(originalText).prop('disabled', false);
                }
            },
            error: function(xhr) {
                showToast('error', 'Network error.');
                submitBtn.text(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
