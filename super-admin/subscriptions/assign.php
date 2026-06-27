<?php
// super-admin/subscriptions/assign.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

define('PAGE_TITLE', 'Assign Subscription');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';

try {
    $db = Database::getInstance()->getConnection();
    // Get active companies without active subscriptions
    $stmt = $db->query("SELECT id, company_name, company_code FROM companies WHERE deleted_at IS NULL AND status = 'active'");
    $companies = $stmt->fetchAll();

    // Get active plans
    $stmt = $db->query("SELECT id, plan_name, price, currency, plan_type FROM saas_plans WHERE status = 'active' AND deleted_at IS NULL");
    $plans = $stmt->fetchAll();
} catch (PDOException $e) {
    $companies = [];
    $plans = [];
}
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center">
                    <a href="index.php" class="mr-4 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Assign Subscription</h1>
                        <p class="mt-1 text-sm text-slate-500">Generate a license key and assign a plan to a tenant.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <form id="assignPlanForm">
                    <div class="card-body">
                        <div class="grid grid-cols-1 gap-6">

                            <div>
                                <label class="form-label">Select Company <span class="text-rose-500">*</span></label>
                                <select name="company_id" class="form-input" required>
                                    <option value="">Choose a company...</option>
                                    <?php foreach($companies as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= esc($c['company_name']) ?> (<?= esc($c['company_code']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Select Plan <span class="text-rose-500">*</span></label>
                                <select name="plan_id" class="form-input" required>
                                    <option value="">Choose a plan...</option>
                                    <?php foreach($plans as $p): ?>
                                        <option value="<?= $p['id'] ?>"><?= esc($p['plan_name']) ?> - <?= esc($p['currency']) ?> <?= $p['price'] ?> / <?= $p['plan_type'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">Start Date <span class="text-rose-500">*</span></label>
                                    <input type="date" name="start_date" class="form-input" required value="<?= date('Y-m-d') ?>">
                                </div>
                                <div>
                                    <label class="form-label">Trial Days</label>
                                    <input type="number" name="trial_days" class="form-input" value="0">
                                    <p class="text-xs text-slate-500 mt-1">Status will be set to 'trial' if > 0.</p>
                                </div>
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button type="submit" class="btn-primary">Generate License & Assign</button>
                            </div>
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
    $('#assignPlanForm').on('submit', function(e) {
        e.preventDefault();
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.text();
        submitBtn.html('<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>').prop('disabled', true);

        $.ajax({
            url: '<?= BASE_URL ?>api/super-admin/subscriptions/assign.php',
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
                let res = xhr.responseJSON;
                showToast('error', res ? res.message : 'Network error.');
                submitBtn.text(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
